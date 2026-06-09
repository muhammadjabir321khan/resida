<?php

namespace App\Models;

use App\Models\Concerns\BelongsToLandlord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * People who rent from the landlord (not the SaaS "tenant" table used by some billing packages).
 */
class RentalTenant extends Model
{
    use BelongsToLandlord;

    protected $table = 'rental_tenants';

    protected $fillable = [
        'full_name',
        'email',
        'phone',
        'national_id',
        'nationality',
        'registered_on',
        'emergency_contact_name',
        'emergency_contact_phone',
        'notes',
        'linked_user_id',
        'invite_token',
        'invited_at',
        'invite_accepted_at',
    ];

    public function landlord(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function linkedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'linked_user_id');
    }

    public function leases(): HasMany
    {
        return $this->hasMany(Lease::class, 'tenant_id');
    }

    protected function casts(): array
    {
        return [
            'registered_on' => 'date',
            'invited_at' => 'datetime',
            'invite_accepted_at' => 'datetime',
        ];
    }

    public function isPortalLinked(): bool
    {
        return $this->linked_user_id !== null;
    }

    public function hasPendingInvite(): bool
    {
        return $this->invite_token !== null && $this->invite_accepted_at === null;
    }

    public function portalStatusLabel(): string
    {
        if ($this->isPortalLinked()) {
            return __('Linked');
        }

        if ($this->hasPendingInvite()) {
            return __('Invite pending');
        }

        return __('Not invited');
    }
}
