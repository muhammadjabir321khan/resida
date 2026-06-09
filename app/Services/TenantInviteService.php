<?php

namespace App\Services;

use App\Mail\TenantPortalInviteMail;
use App\Models\RentalTenant;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

class TenantInviteService
{
    public function sendInvite(RentalTenant $tenant): void
    {
        if (blank($tenant->email)) {
            throw ValidationException::withMessages([
                'email' => __('Add an email address before sending a portal invite.'),
            ]);
        }

        if ($tenant->linked_user_id !== null) {
            throw ValidationException::withMessages([
                'invite' => __('This tenant is already linked to a portal account.'),
            ]);
        }

        $token = Str::random(64);

        $tenant->update([
            'invite_token' => $token,
            'invited_at' => now(),
        ]);

        $tenant->loadMissing('landlord');

        Mail::to($tenant->email)->send(new TenantPortalInviteMail($tenant, $token));
    }

    public function findPendingInvite(string $token): ?RentalTenant
    {
        return RentalTenant::withoutLandlordScope()
            ->where('invite_token', $token)
            ->whereNull('invite_accepted_at')
            ->first();
    }

    public function acceptInvite(string $token, User $user): RentalTenant
    {
        $tenant = $this->findPendingInvite($token);

        if ($tenant === null) {
            throw ValidationException::withMessages([
                'invite' => __('This invite link is invalid or has already been used.'),
            ]);
        }

        if (strcasecmp((string) $tenant->email, (string) $user->email) !== 0) {
            throw ValidationException::withMessages([
                'email' => __('Sign in or register with :email to accept this invite.', ['email' => $tenant->email]),
            ]);
        }

        $existingLink = RentalTenant::withoutLandlordScope()
            ->where('linked_user_id', $user->id)
            ->where('id', '!=', $tenant->id)
            ->exists();

        if ($existingLink) {
            throw ValidationException::withMessages([
                'invite' => __('Your account is already linked to another tenant profile.'),
            ]);
        }

        $tenant->update([
            'linked_user_id' => $user->id,
            'invite_accepted_at' => now(),
            'invite_token' => null,
        ]);

        Role::query()->firstOrCreate(['name' => 'tenant', 'guard_name' => 'web']);

        if (! $user->hasRole('tenant')) {
            $user->assignRole('tenant');
        }

        session()->forget('tenant_invite_token');

        return $tenant->fresh();
    }

    public function rememberInviteInSession(string $token): void
    {
        session(['tenant_invite_token' => $token]);
    }

    public function acceptInviteFromSession(User $user): ?RentalTenant
    {
        $token = session('tenant_invite_token');

        if (! is_string($token) || $token === '') {
            return null;
        }

        try {
            return $this->acceptInvite($token, $user);
        } catch (ValidationException) {
            return null;
        }
    }
}
