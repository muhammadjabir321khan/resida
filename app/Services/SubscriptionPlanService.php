<?php

namespace App\Services;

use App\Models\RentalUnit;
use App\Models\User;

class SubscriptionPlanService
{
    public function currentPlanKey(User $user): ?string
    {
        if ($user->hasRole('admin')) {
            return 'business';
        }

        $subscriptionType = config('subscription.type', 'default');
        $subscription = $user->subscription($subscriptionType);

        if ($subscription === null || ! $user->hasActiveSubscription()) {
            return null;
        }

        $stripePrice = $subscription->stripe_price;

        if (is_string($stripePrice) && $stripePrice !== '') {
            foreach (config('subscription.plans', []) as $key => $plan) {
                if (($plan['price_id'] ?? null) === $stripePrice) {
                    return $key;
                }
            }
        }

        $fallback = config('subscription.fallback_plan_key');

        return is_string($fallback) && $fallback !== '' ? $fallback : null;
    }

    public function unitLimit(User $user): ?int
    {
        if ($user->hasRole('admin')) {
            return null;
        }

        $planKey = $this->currentPlanKey($user);

        if ($planKey === null) {
            return 0;
        }

        $limit = config("subscription.plans.{$planKey}.unit_limit");

        return is_int($limit) ? $limit : null;
    }

    public function unitCount(User $user): int
    {
        return (int) RentalUnit::withoutLandlordScope()
            ->where('user_id', $user->id)
            ->count();
    }

    public function remainingUnits(User $user): ?int
    {
        $limit = $this->unitLimit($user);

        if ($limit === null) {
            return null;
        }

        return max(0, $limit - $this->unitCount($user));
    }

    public function canAddUnit(User $user): bool
    {
        $limit = $this->unitLimit($user);

        if ($limit === null) {
            return true;
        }

        return $this->unitCount($user) < $limit;
    }

    /**
     * @return array{plan_key: ?string, plan_name: ?string, unit_count: int, unit_limit: ?int, remaining: ?int, at_limit: bool}
     */
    public function usageSummary(User $user): array
    {
        $planKey = $this->currentPlanKey($user);
        $unitLimit = $this->unitLimit($user);
        $unitCount = $this->unitCount($user);

        return [
            'plan_key' => $planKey,
            'plan_name' => $planKey !== null ? (config("subscription.plans.{$planKey}.name") ?? $planKey) : null,
            'unit_count' => $unitCount,
            'unit_limit' => $unitLimit,
            'remaining' => $unitLimit === null ? null : max(0, $unitLimit - $unitCount),
            'at_limit' => $unitLimit !== null && $unitCount >= $unitLimit,
        ];
    }
}
