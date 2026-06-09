<?php

namespace App\Services;

use App\Models\UserSetting;
use Stripe\StripeClient;

class LandlordStripeService
{
    public function isConfigured(int $landlordUserId): bool
    {
        return $this->secretKey($landlordUserId) !== null;
    }

    public function secretKey(int $landlordUserId): ?string
    {
        $landlordSecret = UserSetting::getValueForUser($landlordUserId, 'stripe_secret_key');

        if (is_string($landlordSecret) && $landlordSecret !== '') {
            return $landlordSecret;
        }

        if (config('rental.stripe.use_platform_fallback', true)) {
            $platformSecret = config('cashier.secret');

            return is_string($platformSecret) && $platformSecret !== '' ? $platformSecret : null;
        }

        return null;
    }

    public function currency(int $landlordUserId): string
    {
        $currency = UserSetting::getValueForUser($landlordUserId, 'default_currency', 'USD');

        return strtolower(is_string($currency) && $currency !== '' ? $currency : 'usd');
    }

    public function client(int $landlordUserId): StripeClient
    {
        $secret = $this->secretKey($landlordUserId);

        if ($secret === null) {
            throw new \RuntimeException(__('Online rent payments are not configured for this landlord.'));
        }

        return new StripeClient($secret);
    }
}
