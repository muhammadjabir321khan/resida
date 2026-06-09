<?php

namespace App\Models;

use App\Models\Concerns\BelongsToLandlord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Property extends Model
{
    use BelongsToLandlord;

    protected $fillable = [
        'name',
        'address_line_1',
        'address_line_2',
        'city',
        'state',
        'postal_code',
        'country',
        'property_type',
        'units_count',
        'market_value',
        'owner_display_name',
        'photo_path',
        'notes',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'units_count' => 'integer',
            'market_value' => 'decimal:2',
        ];
    }

    public function landlord(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function leases(): HasMany
    {
        return $this->hasMany(Lease::class);
    }

    public function maintenanceRequests(): HasMany
    {
        return $this->hasMany(MaintenanceRequest::class);
    }

    public function rentalTransactions(): HasMany
    {
        return $this->hasMany(RentalTransaction::class);
    }

    public function rentalUnits(): HasMany
    {
        return $this->hasMany(RentalUnit::class, 'property_id');
    }
}
