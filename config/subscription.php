<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cashier subscription type
    |--------------------------------------------------------------------------
    |
    | Must match the "type" column used with newSubscription() / subscribed().
    |
    */

    'type' => 'default',

    /*
    |--------------------------------------------------------------------------
    | Fallback plan when stripe_price does not match (e.g. demo seed data)
    |--------------------------------------------------------------------------
    */

    'fallback_plan_key' => env('SUBSCRIPTION_FALLBACK_PLAN', 'growth'),

    /*
    |--------------------------------------------------------------------------
    | Plans (Stripe recurring Price IDs)
    |--------------------------------------------------------------------------
    |
    | Create three recurring prices in Stripe Dashboard and paste their IDs
    | into .env (STRIPE_PRICE_*). Display fields are for the UI only.
    |
    */

    'plans' => [
        'starter' => [
            'name' => 'Starter',
            'description' => 'For small portfolios getting started.',
            'price_id' => env('STRIPE_PRICE_STARTER'),
            'amount_label' => '$29',
            'interval_label' => '/ month',
            'unit_limit' => 25,
            'features' => [
                'Up to 25 units',
                'Core rental tracking',
                'Email support',
            ],
        ],
        'growth' => [
            'name' => 'Growth',
            'description' => 'For growing landlords and small teams.',
            'price_id' => env('STRIPE_PRICE_GROWTH'),
            'amount_label' => '$79',
            'interval_label' => '/ month',
            'unit_limit' => null,
            'features' => [
                'Unlimited units',
                'Maintenance & payments',
                'Priority support',
            ],
        ],
        'business' => [
            'name' => 'Business',
            'description' => 'Maximum capacity and reporting.',
            'price_id' => env('STRIPE_PRICE_BUSINESS'),
            'amount_label' => '$149',
            'interval_label' => '/ month',
            'unit_limit' => null,
            'features' => [
                'Everything in Growth',
                'Advanced reporting (coming soon)',
                'Dedicated success channel',
            ],
        ],
    ],

];
