<?php

namespace App\Services;

use App\Models\RentalTenant;
use App\Models\User;

class TenantProfileResolver
{
    public static function forUser(User $user): ?RentalTenant
    {
        $linked = RentalTenant::withoutLandlordScope()
            ->where('linked_user_id', $user->id)
            ->orderByDesc('id')
            ->first();

        if ($linked !== null) {
            return $linked;
        }

        return RentalTenant::withoutLandlordScope()
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->whereRaw('LOWER(email) = ?', [strtolower((string) $user->email)])
            ->orderByDesc('id')
            ->first();
    }

    public static function userForProfile(RentalTenant $profile): ?User
    {
        if ($profile->linked_user_id !== null) {
            return User::query()->find($profile->linked_user_id);
        }

        if (blank($profile->email)) {
            return null;
        }

        return User::query()
            ->whereRaw('LOWER(email) = ?', [strtolower((string) $profile->email)])
            ->first();
    }
}
