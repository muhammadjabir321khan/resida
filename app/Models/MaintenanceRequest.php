<?php

namespace App\Models;

use App\Models\Concerns\BelongsToLandlord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaintenanceRequest extends Model
{
    use BelongsToLandlord;

    protected $table = 'rental_maintenance_requests';

    protected $fillable = [
        'user_id',
        'property_id',
        'unit_id',
        'rental_tenant_id',
        'category',
        'title',
        'description',
        'status',
        'priority',
        'estimated_cost',
        'actual_cost',
        'technician_name',
        'reported_on',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'reported_on' => 'date',
            'completed_at' => 'datetime',
            'estimated_cost' => 'decimal:2',
            'actual_cost' => 'decimal:2',
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

    public function unit(): BelongsTo
    {
        return $this->belongsTo(RentalUnit::class, 'unit_id');
    }

    public function rentalTenant(): BelongsTo
    {
        return $this->belongsTo(RentalTenant::class, 'rental_tenant_id');
    }
}
