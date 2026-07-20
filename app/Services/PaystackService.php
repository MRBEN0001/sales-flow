<?php

namespace App\Services;

use App\Models\SubscriptionPayment;
use App\Models\Tenant;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;

class PaystackService
{
    public const PLAN_MONTHLY = 'monthly';
    public const PLAN_YEARLY = 'yearly';

    public function planAmountNgn(string $plan): int
    {
        if ($plan === self::PLAN_YEARLY) {
            return subscription_yearly_price_ngn();
        }

        if ($plan === self::PLAN_MONTHLY) {
            return subscription_monthly_price_ngn();
        }

        throw new RuntimeException('Invalid subscription plan.');
    }

    public function planLabel(string $plan): string
    {
        return $plan === self::PLAN_YEARLY ? 'Yearly' : 'Monthly';
    }

    public function isValidPlan(string $plan): bool
    {
        return in_array($plan, [self::PLAN_MONTHLY, self::PLAN_YEARLY], true);
    }

    /**
     * Create a pending payment and return Paystack authorization URL.
     */
    public function initialize(Tenant $tenant, string $plan, string $email, string $callbackUrl): array
    {
        if (! $this->isValidPlan($plan)) {
            throw new RuntimeException('Invalid subscription plan.');
        }

        $secret = config('paystack.secret_key');
        if (! $secret) {
            throw new RuntimeException('Paystack secret key is not configured.');
        }

        $amountNgn = $this->planAmountNgn($plan);
        $reference = $this->makeReference($tenant->id, $plan);

        $payment = SubscriptionPayment::query()->create([
            'tenant_id' => $tenant->id,
            'plan' => $plan,
            'amount' => $amountNgn,
            'currency' => 'NGN',
            'reference' => $reference,
            'status' => SubscriptionPayment::STATUS_PENDING,
            'paid_email' => Str::lower($email),
        ]);

        $response = Http::withToken($secret)
            ->acceptJson()
            ->post(rtrim((string) config('paystack.payment_url'), '/').'/transaction/initialize', [
                'email' => $email,
                'amount' => $amountNgn * 100, // Paystack expects kobo
                'currency' => 'NGN',
                'reference' => $reference,
                'callback_url' => $callbackUrl,
                'metadata' => [
                    'tenant_id' => $tenant->id,
                    'shop_name' => $tenant->shop_name,
                    'plan' => $plan,
                    'payment_id' => $payment->id,
                ],
            ]);

        if (! $response->successful() || ! ($response->json('status') === true)) {
            $payment->update([
                'status' => SubscriptionPayment::STATUS_FAILED,
                'payload' => $response->json(),
            ]);

            Log::warning('Paystack initialize failed.', [
                'tenant' => $tenant->id,
                'body' => $response->json(),
            ]);

            throw new RuntimeException(
                $response->json('message') ?: 'Could not start Paystack payment.'
            );
        }

        $data = $response->json('data') ?? [];

        $payment->update([
            'payload' => $data,
        ]);

        return [
            'authorization_url' => $data['authorization_url'] ?? null,
            'access_code' => $data['access_code'] ?? null,
            'reference' => $reference,
            'payment' => $payment,
        ];
    }

    /**
     * Verify a Paystack reference and activate the shop when successful.
     * If the local payment row was deleted (shop recreate), rebuild it from Paystack.
     */
    public function verifyAndActivate(string $reference): SubscriptionPayment
    {
        $secret = config('paystack.secret_key');
        $response = Http::withToken($secret)
            ->acceptJson()
            ->get(rtrim((string) config('paystack.payment_url'), '/').'/transaction/verify/'.rawurlencode($reference));

        $body = $response->json() ?? [];
        $data = $body['data'] ?? [];

        if (! $response->successful() || ($body['status'] ?? false) !== true) {
            throw new RuntimeException($body['message'] ?? 'Payment verification failed.');
        }

        if (($data['status'] ?? '') !== 'success') {
            throw new RuntimeException('Payment was not successful.');
        }

        $payment = SubscriptionPayment::query()
            ->where('reference', $reference)
            ->first();

        if (! $payment) {
            $payment = $this->createPaymentFromPaystackData($data, $body);
        }

        if ($payment->status === SubscriptionPayment::STATUS_SUCCESS
            && $payment->period_starts_at
            && $payment->period_ends_at) {
            $this->activateTenantSubscription($payment);

            return $payment->fresh();
        }

        $expectedKobo = $payment->amount * 100;
        $paidKobo = (int) ($data['amount'] ?? 0);

        if ($paidKobo < $expectedKobo) {
            $payment->update([
                'status' => SubscriptionPayment::STATUS_FAILED,
                'paystack_status' => 'amount_mismatch',
                'payload' => $body,
            ]);

            throw new RuntimeException('Paid amount does not match the selected plan.');
        }

        $paidAt = ! empty($data['paid_at'])
            ? \Carbon\Carbon::parse($data['paid_at'])->timezone(config('app.timezone'))
            : now();

        $payment->update([
            'status' => SubscriptionPayment::STATUS_SUCCESS,
            'paystack_status' => $data['status'] ?? 'success',
            'channel' => $data['channel'] ?? null,
            'paid_email' => $data['customer']['email'] ?? $payment->paid_email,
            'paid_at' => $paidAt,
            'payload' => $body,
        ]);

        $this->activateTenantSubscription($payment->fresh());

        return $payment->fresh();
    }

    /**
     * Pull successful Paystack charges for a shop and restore local history/status.
     */
    public function syncTenantPayments(string $tenantId): int
    {
        $secret = config('paystack.secret_key');
        if (! $secret) {
            return 0;
        }

        $response = Http::withToken($secret)
            ->acceptJson()
            ->get(rtrim((string) config('paystack.payment_url'), '/').'/transaction', [
                'perPage' => 50,
                'status' => 'success',
            ]);

        if (! $response->successful()) {
            Log::warning('Paystack transaction sync failed.', [
                'tenant' => $tenantId,
                'body' => $response->json(),
            ]);

            return 0;
        }

        $restored = 0;

        foreach ($response->json('data') ?? [] as $tx) {
            $meta = $tx['metadata'] ?? [];
            $metaTenant = (string) ($meta['tenant_id'] ?? '');

            if ($metaTenant === '' || strcasecmp($metaTenant, $tenantId) !== 0) {
                continue;
            }

            $reference = (string) ($tx['reference'] ?? '');
            if ($reference === '') {
                continue;
            }

            try {
                $this->verifyAndActivate($reference);
                $restored++;
            } catch (\Throwable $e) {
                Log::warning('Could not restore Paystack payment for shop.', [
                    'tenant' => $tenantId,
                    'reference' => $reference,
                    'message' => $e->getMessage(),
                ]);
            }
        }

        return $restored;
    }

    protected function createPaymentFromPaystackData(array $data, array $body): SubscriptionPayment
    {
        $meta = $data['metadata'] ?? [];
        $tenantId = (string) ($meta['tenant_id'] ?? '');
        $plan = (string) ($meta['plan'] ?? self::PLAN_MONTHLY);
        $reference = (string) ($data['reference'] ?? '');

        if ($tenantId === '' || $reference === '') {
            throw new RuntimeException('Payment reference not found locally and Paystack metadata is incomplete.');
        }

        if (! $this->isValidPlan($plan)) {
            $plan = self::PLAN_MONTHLY;
        }

        $amountNgn = (int) round(((int) ($data['amount'] ?? 0)) / 100);
        if ($amountNgn <= 0) {
            $amountNgn = $this->planAmountNgn($plan);
        }

        $paidAt = ! empty($data['paid_at'])
            ? \Carbon\Carbon::parse($data['paid_at'])->timezone(config('app.timezone'))
            : now();

        return SubscriptionPayment::query()->create([
            'tenant_id' => $tenantId,
            'plan' => $plan,
            'amount' => $amountNgn,
            'currency' => $data['currency'] ?? 'NGN',
            'reference' => $reference,
            'status' => SubscriptionPayment::STATUS_SUCCESS,
            'paystack_status' => $data['status'] ?? 'success',
            'channel' => $data['channel'] ?? null,
            'paid_email' => Str::lower((string) ($data['customer']['email'] ?? '')),
            'paid_at' => $paidAt,
            'payload' => $body,
        ]);
    }

    public function activateTenantSubscription(SubscriptionPayment $payment): void
    {
        $tenant = Tenant::query()->find($payment->tenant_id);

        if (! $tenant) {
            throw new RuntimeException('Shop not found for this payment.');
        }

        $tenant->refresh();
        $payment->refresh();

        // A stored period makes retries/webhooks idempotent: the same payment
        // can repair activation, but can never extend the shop twice.
        if ($payment->period_starts_at && $payment->period_ends_at) {
            $endsAt = $payment->period_ends_at->copy();
        } else {
            $months = $payment->plan === self::PLAN_YEARLY ? 12 : 1;
            $paidAt = $payment->paid_at ? $payment->paid_at->copy() : now();

            $startsAt = ($tenant->subscription_status === Tenant::STATUS_ACTIVE
                && $tenant->subscription_ends_at
                && $tenant->subscription_ends_at->isFuture()
                && $tenant->subscription_ends_at->greaterThan($paidAt))
                ? $tenant->subscription_ends_at->copy()
                : $paidAt;

            $endsAt = $startsAt->copy()->addMonthsNoOverflow($months);

            $payment->forceFill([
                'period_starts_at' => $startsAt,
                'period_ends_at' => $endsAt,
            ])->save();
        }

        $tenant->forceFill([
            'subscription_status' => Tenant::STATUS_ACTIVE,
            'subscription_plan' => $payment->plan,
            'subscription_ends_at' => $tenant->subscription_ends_at
                && $tenant->subscription_ends_at->greaterThan($endsAt)
                    ? $tenant->subscription_ends_at
                    : $endsAt,
        ])->save();

        $tenant = $tenant->fresh();

        Log::info('Shop subscription activated after payment.', [
            'tenant' => $tenant->id,
            'plan' => $payment->plan,
            'reference' => $payment->reference,
            'subscription_status' => $tenant->subscription_status,
            'subscription_ends_at' => optional($tenant->subscription_ends_at)->toDateTimeString(),
        ]);
    }

    public function makeReference(string $tenantId, string $plan): string
    {
        return strtoupper('SF_'.$tenantId.'_'.$plan.'_'.Str::random(10));
    }

    /**
     * Manually activate/extend a shop subscription (admin recovery tool).
     * Creates a history row with channel=manual so it appears in the timeline.
     */
    public function manualActivate(
        Tenant $tenant,
        string $plan,
        ?string $note = null,
        ?string $activatedBy = null
    ): SubscriptionPayment {
        if (! $this->isValidPlan($plan)) {
            throw new RuntimeException('Invalid subscription plan.');
        }

        $payment = SubscriptionPayment::query()->create([
            'tenant_id' => $tenant->id,
            'plan' => $plan,
            'amount' => $this->planAmountNgn($plan),
            'currency' => 'NGN',
            'reference' => strtoupper('SF_MANUAL_'.$tenant->id.'_'.$plan.'_'.Str::random(8)),
            'status' => SubscriptionPayment::STATUS_SUCCESS,
            'paystack_status' => 'manual',
            'channel' => 'manual',
            'paid_email' => $tenant->owner_email,
            'paid_at' => now(),
            'payload' => [
                'manual' => true,
                'note' => $note,
                'activated_by' => $activatedBy,
            ],
        ]);

        $this->activateTenantSubscription($payment);

        Log::info('Shop subscription manually activated by admin.', [
            'tenant' => $tenant->id,
            'plan' => $plan,
            'reference' => $payment->reference,
            'activated_by' => $activatedBy,
            'note' => $note,
        ]);

        return $payment->fresh();
    }

    public function signatureIsValid(string $payload, ?string $signature): bool
    {
        $secret = (string) config('paystack.webhook_secret');

        if ($secret === '' || ! $signature) {
            return false;
        }

        $computed = hash_hmac('sha512', $payload, $secret);

        return hash_equals($computed, $signature);
    }
}
