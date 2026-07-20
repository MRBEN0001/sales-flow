<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Trial period (days)
    |--------------------------------------------------------------------------
    */

    'trial_days' => (int) env('SUBSCRIPTION_TRIAL_DAYS', 30),

    /*
    |--------------------------------------------------------------------------
    | Monthly subscription amount (NGN)
    |--------------------------------------------------------------------------
    |
    | Change this value to update the monthly price everywhere.
    | Example: 7500 = ₦7,500 / month
    |
    */

    'monthly_price_ngn' => 7500,

    /*
    |--------------------------------------------------------------------------
    | Yearly plan discount (%)
    |--------------------------------------------------------------------------
    |
    | Yearly price = (monthly_price_ngn × 12) × (100 − yearly_discount_percent) / 100
    |
    | Example with monthly ₦7,500 and 40% discount:
    |   Full year = 7,500 × 12 = ₦90,000
    |   Yearly plan = 90,000 × 0.60 = ₦54,000
    |
    | Change this percentage to adjust the yearly plan price.
    |
    */

    'yearly_discount_percent' => 40,

];
