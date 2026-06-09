<?php

namespace App\Models;

use App\Models\Concerns\BelongsToLandlord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RentalTransaction extends Model
{
    use BelongsToLandlord;

    public const DIRECTION_INCOME = 'income';

    public const DIRECTION_EXPENSE = 'expense';

    protected $fillable = [
        'property_id',
        'lease_id',
        'unit_id',
        'direction',
        'category',
        'amount',
        'transaction_date',
        'description',
        'reference',
        'vendor_name',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'transaction_date' => 'date',
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

    public function lease(): BelongsTo
    {
        return $this->belongsTo(Lease::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(RentalUnit::class, 'unit_id');
    }
}
