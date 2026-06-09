<?php

namespace App\Models;

use App\Models\Concerns\BelongsToLandlord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RentalUnit extends Model
{
    use BelongsToLandlord;

    public const OCCUPANCY_VACANT = 'vacant';

    public const OCCUPANCY_OCCUPIED = 'occupied';

    protected $table = 'rental_units';

    protected $fillable = [
        'property_id',
        'label',
        'unit_type',
        'bedrooms',
        'bathrooms',
        'monthly_rent',
        'amenities',
        'notes',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'bedrooms' => 'integer',
            'bathrooms' => 'decimal:1',
            'monthly_rent' => 'decimal:2',
            'amenities' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function landlord(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function leases(): HasMany
    {
        return $this->hasMany(Lease::class, 'unit_id');
    }
}
