<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RentReminderLog extends Model
{
    public const TYPE_UPCOMING_PREFIX = 'upcoming_';

    public const TYPE_DUE_TODAY = 'due_today';

    public const TYPE_OVERDUE_PREFIX = 'overdue_';

    protected $fillable = [
        'rent_payment_installment_id',
        'reminder_type',
        'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
        ];
    }

    public function installment(): BelongsTo
    {
        return $this->belongsTo(RentPaymentInstallment::class, 'rent_payment_installment_id');
    }

    public static function upcomingType(int $daysBefore): string
    {
        return self::TYPE_UPCOMING_PREFIX.$daysBefore;
    }

    public static function overdueType(int $daysAfter): string
    {
        return self::TYPE_OVERDUE_PREFIX.$daysAfter;
    }
}
