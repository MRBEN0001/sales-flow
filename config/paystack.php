<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Paystack API keys
    |--------------------------------------------------------------------------
    |
    | Use pk_test_ / sk_test_ for local testing, and pk_live_ / sk_live_
    | in production. Never commit live secret keys.
    |
    */

    'public_key' => env('PAYSTACK_PUBLIC_KEY'),

    'secret_key' => env('PAYSTACK_SECRET_KEY'),

    'payment_url' => env('PAYSTACK_PAYMENT_URL', 'https://api.paystack.co'),

    /*
    |--------------------------------------------------------------------------
    | Webhook secret (optional)
    |--------------------------------------------------------------------------
    |
    | If set, webhook requests must include a matching signature header.
    | Paystack signs with your secret key by default (HMAC SHA512).
    |
    */

    'webhook_secret' => env('PAYSTACK_SECRET_KEY'),

];
