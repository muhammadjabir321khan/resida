<?php

namespace App\Models;

use App\Models\Concerns\BelongsToLandlord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Lease extends Model
{
    use BelongsToLandlord;

    protected $table = 'rental_leases';

    protected $fillable = [
        'property_id',
        'unit_id',
        'tenant_id',
        'start_date',
        'end_date',
        'monthly_rent',
        'payment_frequency',
        'security_deposit',
        'status',
        'document_path',
        'terms_notes',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'monthly_rent' => 'decimal:2',
            'security_deposit' => 'decimal:2',
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

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(RentalTenant::class, 'tenant_id');
    }

    public function rentalTransactions(): HasMany
    {
        return $this->hasMany(RentalTransaction::class);
    }

    public function rentPaymentInstallments(): HasMany
    {
        return $this->hasMany(RentPaymentInstallment::class, 'lease_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(LeaseDocument::class, 'lease_id');
    }

    public function messageThread(): HasOne
    {
        return $this->hasOne(MessageThread::class, 'lease_id');
    }
}
