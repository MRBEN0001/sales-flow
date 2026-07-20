<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Dev access emails
    |--------------------------------------------------------------------------
    |
    | Change these emails here if needed. Passwords are hardcoded in the
    | seeders (DevShopsAdminUserSeeder / DevDashboardUserSeeder).
    |
    | admin     -> global dev admin seeded into EVERY shop (full admin rights).
    | dashboard -> login for the system dev dashboard at /dev.
    |
    */

    'admin' => [
        'name' => 'SalesFlow Support',
        'email' => 'salesflowsupport@gmail.com',
    ],

    'dashboard' => [
        'name' => 'SalesFlow Admin',
        'email' => 'salesflowsupport@gmail.com',
    ],

    /*
    |--------------------------------------------------------------------------
    | WhatsApp support number (international format, no + or leading 0)
    |--------------------------------------------------------------------------
    |
    | Shown as the floating chat-support button across the app.
    |
    */

    'whatsapp_support' => '2348136323444',

];
