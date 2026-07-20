<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Mail\SubscriptionActivatedMail;
use App\Models\SubscriptionPayment;
use App\Models\Tenant;
use App\Services\PaystackService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class PaystackController extends Controller
{
    public function showCheckout(string $plan, PaystackService $paystack)
    {
        if (! $paystack->isValidPlan($plan)) {
            return redirect()->route('central.plans');
        }

        return view('central.subscribe', [
            'plan' => $plan,
            'planLabel' => $paystack->planLabel($plan),
            'amountNgn' => $paystack->planAmountNgn($plan),
            'tenantDomain' => config('app.tenant_domain'),
            'monthlyPriceNgn' => subscription_monthly_price_ngn(),
            'yearlyPriceNgn' => subscription_yearly_price_ngn(),
        ]);
    }

    public function startCheckout(Request $request, string $plan, PaystackService $paystack)
    {
        if (! $paystack->isValidPlan($plan)) {
            return redirect()->route('central.plans');
        }

        $request->merge([
            'shop_address' => Str::lower(preg_replace('/[^a-z0-9]/', '', (string) $request->input('shop_address', ''))),
            'email' => Str::lower(trim((string) $request->input('email', ''))),
        ]);

        $validated = $request->validate([
            'shop_address' => ['required', 'string', 'min:3', 'max:40', 'regex:/^[a-z0-9]+$/'],
            'email' => ['required', 'email', 'max:190'],
        ], [
            'shop_address.required' => 'Enter your shop address (subdomain).',
            'shop_address.regex' => 'Shop address must be letters and numbers only.',
        ]);

        $tenant = Tenant::query()->find($validated['shop_address']);

        if (! $tenant) {
            return back()
                ->withInput()
                ->withErrors(['shop_address' => 'No shop was found with that address.']);
        }

        if (Str::lower((string) $tenant->owner_email) !== $validated['email']) {
            return back()
                ->withInput()
                ->withErrors(['email' => 'That email does not match the shop owner email on file.']);
        }

        try {
            $result = $paystack->initialize(
                $tenant,
                $plan,
                $validated['email'],
                central_url('/paystack/callback')
            );
        } catch (\Throwable $e) {
            Log::warning('Paystack checkout start failed.', [
                'tenant' => $tenant->id,
                'message' => $e->getMessage(),
            ]);

            return back()
                ->withInput()
                ->withErrors(['email' => $e->getMessage()]);
        }

        if (empty($result['authorization_url'])) {
            return back()
                ->withInput()
                ->withErrors(['email' => 'Could not start payment. Please try again.']);
        }

        return redirect()->away($result['authorization_url']);
    }

    public function callback(Request $request, PaystackService $paystack)
    {
        $reference = (string) $request->query('reference', $request->query('trxref', ''));

        if ($reference === '') {
            return redirect()
                ->route('central.plans')
                ->with('payment_error', 'Missing payment reference.');
        }

        try {
            $payment = $paystack->verifyAndActivate($reference);
        } catch (\Throwable $e) {
            Log::warning('Paystack callback verification failed.', [
                'reference' => $reference,
                'message' => $e->getMessage(),
            ]);

            return redirect()
                ->route('central.plans')
                ->with('payment_error', $e->getMessage());
        }

        $tenant = Tenant::query()->find($payment->tenant_id);
        if ($tenant) {
            $tenant->refresh();
        }

        $loginUrl = $tenant
            ? tenant_shop_login_url($tenant->id, $request)
            : route('central.login');

        if ($tenant) {
            $this->sendSubscriptionEmails($tenant, $payment, $paystack->planLabel($payment->plan), $loginUrl);
        }

        return redirect()
            ->route('central.paystack.success')
            ->with('payment_shop_name', $tenant->shop_name ?? $payment->tenant_id)
            ->with('payment_plan', $paystack->planLabel($payment->plan))
            ->with('payment_status', 'Active')
            ->with('payment_ends_at', optional($tenant->subscription_ends_at)->timezone(config('app.timezone'))->format('d M Y'))
            ->with('payment_amount', $payment->amount)
            ->with('payment_reference', $payment->reference)
            ->with('payment_login_url', $loginUrl);
    }

    public function success()
    {
        if (! session('payment_login_url')) {
            return redirect()->route('central.plans');
        }

        return view('central.payment-success', [
            'shopName' => session('payment_shop_name'),
            'planLabel' => session('payment_plan'),
            'status' => session('payment_status', 'Active'),
            'endsAt' => session('payment_ends_at'),
            'amount' => session('payment_amount'),
            'reference' => session('payment_reference'),
            'loginUrl' => session('payment_login_url'),
        ]);
    }

    public function webhook(Request $request, PaystackService $paystack)
    {
        $signature = $request->header('x-paystack-signature');
        $payload = $request->getContent();

        if (! $paystack->signatureIsValid($payload, $signature)) {
            return response()->json(['message' => 'Invalid signature'], 401);
        }

        $event = $request->input('event');
        $data = $request->input('data', []);
        $reference = $data['reference'] ?? null;

        if ($event === 'charge.success' && $reference) {
            try {
                $payment = $paystack->verifyAndActivate((string) $reference);
                $tenant = Tenant::query()->find($payment->tenant_id);
                if ($tenant) {
                    $loginUrl = tenant_shop_login_url($tenant->id, $request);
                    $this->sendSubscriptionEmails(
                        $tenant->fresh(),
                        $payment,
                        $paystack->planLabel($payment->plan),
                        $loginUrl
                    );
                }
            } catch (\Throwable $e) {
                Log::warning('Paystack webhook handling failed.', [
                    'reference' => $reference,
                    'message' => $e->getMessage(),
                ]);
            }
        }

        return response()->json(['status' => true]);
    }

    protected function sendSubscriptionEmails(
        Tenant $tenant,
        SubscriptionPayment $payment,
        string $planLabel,
        string $loginUrl
    ): void {
        $payment->refresh();
        $meta = is_array($payment->payload) ? $payment->payload : [];

        if (! empty($meta['activation_mail_sent'])) {
            return;
        }

        try {
            if ($tenant->owner_email) {
                Mail::to($tenant->owner_email)->send(
                    new SubscriptionActivatedMail($tenant, $payment, $planLabel, $loginUrl)
                );
            }
        } catch (\Throwable $e) {
            Log::warning('Failed to send subscription activated email to owner.', [
                'tenant' => $tenant->id,
                'message' => $e->getMessage(),
            ]);
        }

        $companyEmail = config('dev.admin.email');
        if ($companyEmail && strcasecmp($companyEmail, (string) $tenant->owner_email) !== 0) {
            try {
                sleep(2);
                Mail::to($companyEmail)->send(
                    new SubscriptionActivatedMail($tenant, $payment, $planLabel, $loginUrl, true)
                );
            } catch (\Throwable $e) {
                Log::warning('Failed to send subscription activated email to company.', [
                    'tenant' => $tenant->id,
                    'message' => $e->getMessage(),
                ]);
            }
        }

        $meta['activation_mail_sent'] = true;
        $payment->update(['payload' => $meta]);
    }
}
