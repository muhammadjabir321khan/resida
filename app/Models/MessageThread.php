<?php

namespace App\Models;

use App\Models\Concerns\BelongsToLandlord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class MessageThread extends Model
{
    use BelongsToLandlord;

    protected $fillable = [
        'lease_id',
        'rental_tenant_id',
        'subject',
        'last_message_at',
        'landlord_last_read_at',
        'tenant_last_read_at',
    ];

    protected function casts(): array
    {
        return [
            'last_message_at' => 'datetime',
            'landlord_last_read_at' => 'datetime',
            'tenant_last_read_at' => 'datetime',
        ];
    }

    public function landlord(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function lease(): BelongsTo
    {
        return $this->belongsTo(Lease::class, 'lease_id');
    }

    public function rentalTenant(): BelongsTo
    {
        return $this->belongsTo(RentalTenant::class, 'rental_tenant_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class)->orderBy('created_at');
    }

    public function latestMessage(): HasOne
    {
        return $this->hasOne(Message::class)->latestOfMany();
    }

    public function displaySubject(): string
    {
        if (filled($this->subject)) {
            return $this->subject;
        }

        $property = $this->lease?->property?->name ?? __('Lease');

        return __(':property — :tenant', [
            'property' => $property,
            'tenant' => $this->rentalTenant?->full_name ?? __('Tenant'),
        ]);
    }

    public function unreadCountForLandlord(): int
    {
        $since = $this->landlord_last_read_at ?? $this->created_at;

        return $this->messages()
            ->where('sender_user_id', '!=', $this->user_id)
            ->where('created_at', '>', $since)
            ->count();
    }

    public function unreadCountForTenant(int $tenantUserId): int
    {
        $since = $this->tenant_last_read_at ?? $this->created_at;

        return $this->messages()
            ->where('sender_user_id', '!=', $tenantUserId)
            ->where('created_at', '>', $since)
            ->count();
    }

    public function resolveRouteBinding($value, $field = null): ?self
    {
        return static::withoutLandlordScope()
            ->where($field ?? $this->getRouteKeyName(), $value)
            ->first();
    }
}
