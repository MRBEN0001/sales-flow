<?php

namespace App\Http\Controllers;

use App\Services\PaystackService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class SubscriptionController extends Controller
{
    public function expired()
    {
        $tenant = tenant();

        return view('subscription.expired', [
            'shopName' => $tenant ? $tenant->shop_name : config('app.name'),
            'monthlyPriceNgn' => subscription_monthly_price_ngn(),
            'yearlyPriceNgn' => subscription_yearly_price_ngn(),
            'yearlyFullPriceNgn' => subscription_yearly_full_price_ngn(),
            'yearlyDiscountPercent' => subscription_yearly_discount_percent(),
            'yearlySavingsNgn' => subscription_yearly_savings_ngn(),
        ]);
    }

    public function checkout(Request $request, PaystackService $paystack)
    {
        $tenant = tenant();

        if (! $tenant) {
            return redirect()->route('central.plans');
        }

        $validated = $request->validate([
            'plan' => ['required', Rule::in([PaystackService::PLAN_MONTHLY, PaystackService::PLAN_YEARLY])],
        ]);

        $email = $tenant->owner_email;

        if (! $email) {
            return back()->withErrors([
                'plan' => 'This shop has no owner email on file. Contact support.',
            ]);
        }

        try {
            $result = $paystack->initialize(
                $tenant,
                $validated['plan'],
                $email,
                central_url('/paystack/callback')
            );
        } catch (\Throwable $e) {
            Log::warning('Tenant Paystack checkout failed.', [
                'tenant' => $tenant->id,
                'message' => $e->getMessage(),
            ]);

            return back()->withErrors([
                'plan' => $e->getMessage(),
            ]);
        }

        if (empty($result['authorization_url'])) {
            return back()->withErrors([
                'plan' => 'Could not start payment. Please try again.',
            ]);
        }

        return redirect()->away($result['authorization_url']);
    }
}
