<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Rent reminder schedule
    |--------------------------------------------------------------------------
    |
    | upcoming_days: send a heads-up email this many days before due_date.
    | overdue_days: send escalating overdue notices on these days after due_date.
    |
    */

    'rent_reminders' => [
        'upcoming_days' => [3],
        'overdue_days' => [1, 3, 7],
    ],

    /*
    |--------------------------------------------------------------------------
    | Online rent collection (Stripe)
    |--------------------------------------------------------------------------
    |
    | When a landlord has no stripe_secret_key in settings, fall back to the
    | platform STRIPE_SECRET from config/cashier.php (useful for local demos).
    |
    */

    'stripe' => [
        'use_platform_fallback' => env('RENT_STRIPE_USE_PLATFORM_FALLBACK', true),
    ],

];
