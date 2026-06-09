<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Laravel\Cashier\Subscription;
use Stripe\Subscription as StripeSubscription;

class LandlordSubscriptionSeeder extends Seeder
{
    /**
     * Creates a local "active" subscription so demo landlord can use the app without Stripe webhooks.
     */
    public function run(): void
    {
        $landlord = User::query()->where('email', 'landlord@demo.test')->first();
        if ($landlord === null) {
            return;
        }

        if ($landlord->subscriptions()->where('type', 'default')->exists()) {
            return;
        }

        Subscription::forceCreate([
            'user_id' => $landlord->id,
            'type' => 'default',
            'stripe_id' => 'sub_seed_'.uniqid(),
            'stripe_status' => StripeSubscription::STATUS_ACTIVE,
            'stripe_price' => 'price_seed_demo',
            'quantity' => null,
            'trial_ends_at' => null,
            'ends_at' => null,
        ]);
    }
}
