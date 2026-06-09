<?php

namespace App\Models;

use App\Models\Concerns\BelongsToLandlord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RentPaymentInstallment extends Model
{
    use BelongsToLandlord;

    public const STATUS_PENDING = 'pending';

    public const STATUS_PAID = 'paid';

    public const STATUS_OVERDUE = 'overdue';

    public const STATUS_WAIVED = 'waived';

    protected $fillable = [
        'user_id',
        'lease_id',
        'due_date',
        'amount_due',
        'amount_paid',
        'status',
        'paid_date',
        'receipt_number',
        'payment_method',
        'notes',
        'stripe_checkout_session_id',
        'stripe_payment_intent_id',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'amount_due' => 'decimal:2',
            'amount_paid' => 'decimal:2',
            'paid_date' => 'date',
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

    public function reminderLogs(): HasMany
    {
        return $this->hasMany(RentReminderLog::class, 'rent_payment_installment_id');
    }

    public function displayStatus(): string
    {
        if ($this->status === self::STATUS_PENDING && $this->due_date && $this->due_date->isPast()) {
            return self::STATUS_OVERDUE;
        }

        return $this->status;
    }

    public function amountRemaining(): float
    {
        return max(0, (float) $this->amount_due - (float) $this->amount_paid);
    }

    public function isPayableOnline(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_OVERDUE], true)
            && $this->amountRemaining() > 0;
    }
}
