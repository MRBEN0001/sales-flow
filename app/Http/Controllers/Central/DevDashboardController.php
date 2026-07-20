<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPayment;
use App\Models\Tenant;
use App\Services\PaystackService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class DevDashboardController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('q', ''));

        $tenants = Tenant::query()
            ->with('domains')
            ->when($search !== '', function ($query) use ($search) {
                $query->where('id', 'like', "%{$search}%")
                    ->orWhere('shop_name', 'like', "%{$search}%")
                    ->orWhere('owner_email', 'like', "%{$search}%")
                    ->orWhere('owner_phone', 'like', "%{$search}%");
            })
            ->latest('created_at')
            ->get();

        $shops = $tenants->map(function (Tenant $tenant) use ($request) {
            $tenant->syncSubscriptionExpiry();
            $subdomain = optional($tenant->domains->first())->domain ?? $tenant->id;

            return [
                'id' => $tenant->id,
                'shop_name' => $tenant->shop_name,
                'owner_email' => $tenant->owner_email,
                'owner_phone' => $tenant->owner_phone,
                'status' => $tenant->subscription_status,
                'plan' => $tenant->planLabel(),
                'on_trial' => $tenant->isOnTrial(),
                'trial_days_left' => $tenant->trialDaysRemaining(),
                'access_ends_at' => $tenant->accessEndsAt(),
                'days_left' => $tenant->subscriptionDaysRemaining(),
                'created_at' => $tenant->created_at,
                'login_url' => tenant_shop_login_url($subdomain, $request),
            ];
        });

        $stats = [
            'total' => $shops->count(),
            'trial' => $shops->where('on_trial', true)->count(),
            'active' => $shops->where('status', Tenant::STATUS_ACTIVE)->count(),
            'expired' => $shops->where('status', Tenant::STATUS_EXPIRED)->count(),
        ];

        return view('central.dev.dashboard', [
            'shops' => $shops,
            'stats' => $stats,
            'search' => $search,
            'devAdminEmail' => config('dev.admin.email'),
            'monthlyPriceNgn' => subscription_monthly_price_ngn(),
            'yearlyPriceNgn' => subscription_yearly_price_ngn(),
        ]);
    }

    public function history(Request $request, string $tenantId, PaystackService $paystack)
    {
        $tenant = Tenant::query()->findOrFail($tenantId);

        // If Paystack has successful charges but local rows were wiped
        // (e.g. shop deleted/recreated), restore them before rendering.
        if (SubscriptionPayment::query()->where('tenant_id', $tenant->id)->where('status', SubscriptionPayment::STATUS_SUCCESS)->doesntExist()) {
            $paystack->syncTenantPayments($tenant->id);
            $tenant->refresh();
        } else {
            $latestPaid = SubscriptionPayment::query()
                ->where('tenant_id', $tenant->id)
                ->where('status', SubscriptionPayment::STATUS_SUCCESS)
                ->latest('paid_at')
                ->latest('id')
                ->first();

            if ($latestPaid && $tenant->subscription_status !== Tenant::STATUS_ACTIVE) {
                $paystack->activateTenantSubscription($latestPaid);
                $tenant->refresh();
            }
        }

        $tenant->syncSubscriptionExpiry();

        $payments = SubscriptionPayment::query()
            ->where('tenant_id', $tenant->id)
            ->orderByDesc('paid_at')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();

        $history = $payments->map(function (SubscriptionPayment $payment) {
            return [
                'type' => 'payment',
                'timestamp' => $payment->paid_at ?: $payment->created_at,
                'plan' => ucfirst($payment->plan),
                'status' => $payment->status,
                'amount' => $payment->amount,
                'channel' => $payment->channel,
                'reference' => $payment->reference,
                'period_starts_at' => $payment->period_starts_at,
                'period_ends_at' => $payment->period_ends_at,
            ];
        });

        $onPaidPlan = $tenant->subscription_status === Tenant::STATUS_ACTIVE
            && $tenant->subscription_ends_at
            && $tenant->subscription_ends_at->isFuture();

        // Trial is the first lifecycle event (appears after payments when newest-first).
        $history->push([
            'type' => 'trial',
            'timestamp' => $tenant->created_at,
            'plan' => 'Free trial',
            'status' => $onPaidPlan
                ? 'converted'
                : (($tenant->trial_ends_at && $tenant->trial_ends_at->isFuture()) ? 'active' : 'ended'),
            'amount' => 0,
            'channel' => null,
            'reference' => null,
            'period_starts_at' => $tenant->created_at,
            'period_ends_at' => $tenant->trial_ends_at,
        ]);

        $subdomain = optional($tenant->domains()->first())->domain ?? $tenant->id;

        return view('central.dev.shop-history', [
            'tenant' => $tenant->fresh(),
            'history' => $history,
            'loginUrl' => tenant_shop_login_url($subdomain, $request),
            'monthlyPriceNgn' => subscription_monthly_price_ngn(),
            'yearlyPriceNgn' => subscription_yearly_price_ngn(),
        ]);
    }

    public function activate(Request $request, string $tenantId, PaystackService $paystack)
    {
        $tenant = Tenant::query()->findOrFail($tenantId);

        $validated = $request->validate([
            'plan' => ['required', Rule::in([PaystackService::PLAN_MONTHLY, PaystackService::PLAN_YEARLY])],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $admin = Auth::guard('dev')->user();
            $payment = $paystack->manualActivate(
                $tenant,
                $validated['plan'],
                $validated['note'] ?? null,
                $admin ? ($admin->email ?? $admin->name ?? 'dev-admin') : 'dev-admin'
            );
        } catch (\Throwable $e) {
            Log::error('Manual subscription activation failed.', [
                'tenant' => $tenant->id,
                'message' => $e->getMessage(),
            ]);

            return back()->with('error', 'Could not activate subscription: '.$e->getMessage());
        }

        $tenant->refresh();
        $ends = optional($tenant->subscription_ends_at)
            ->timezone(config('app.timezone'))
            ->format('d M Y, g:i A');

        return redirect()
            ->route('dev.shops.history', ['tenantId' => $tenant->id])
            ->with(
                'success',
                ucfirst($payment->plan).' plan activated for '.($tenant->shop_name ?: $tenant->id)
                .'. Next expiry: '.$ends.'.'
            );
    }

    public function destroy(string $tenantId)
    {
        $tenant = Tenant::query()->findOrFail($tenantId);
        $shopName = $tenant->shop_name ?: $tenant->id;

        try {
            $database = $tenant->database();
            $manager = $database->manager();
            $databaseName = $database->getName();

            if ($manager->databaseExists($databaseName)) {
                $manager->deleteDatabase($tenant);
            }

            // Delete directly after dropping the DB. Foreign keys cascade to
            // domains and trial-device fingerprint records.
            DB::table('tenants')->where('id', $tenant->id)->delete();
        } catch (\Throwable $e) {
            Log::error('Failed to delete shop from dev dashboard.', [
                'tenant' => $tenant->id,
                'message' => $e->getMessage(),
            ]);

            return back()->with('error', "Could not delete {$shopName}. Please try again.");
        }

        return redirect()
            ->route('dev.dashboard')
            ->with('success', "{$shopName} and its database were deleted permanently.");
    }
}
