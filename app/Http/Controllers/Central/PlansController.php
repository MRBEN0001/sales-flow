<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;

class PlansController extends Controller
{
    public function index()
    {
        return view('central.plans', [
            'trialDays' => config('subscription.trial_days'),
            'monthlyPriceNgn' => subscription_monthly_price_ngn(),
            'yearlyPriceNgn' => subscription_yearly_price_ngn(),
            'yearlyFullPriceNgn' => subscription_yearly_full_price_ngn(),
            'yearlyDiscountPercent' => subscription_yearly_discount_percent(),
            'yearlySavingsNgn' => subscription_yearly_savings_ngn(),
        ]);
    }
}
