<?php

namespace App\Services;

use App\Models\Lease;
use App\Models\RentPaymentInstallment;
use Carbon\Carbon;

class LeasePaymentScheduleGenerator
{
    /**
     * Create due installments for a lease based on payment_frequency and rent.
     * Skips dates that already have a row for this lease.
     */
    public function sync(Lease $lease, bool $replacePending = false): void
    {
        if ($replacePending) {
            $lease->rentPaymentInstallments()->where('status', RentPaymentInstallment::STATUS_PENDING)->delete();
        }

        if (! $lease->start_date || ! $lease->end_date || $lease->start_date->gt($lease->end_date)) {
            return;
        }

        $freq = $lease->payment_frequency ?? 'monthly';
        $monthly = (float) $lease->monthly_rent;

        [$stepMonths, $amount] = match ($freq) {
            'quarterly' => [3, $monthly * 3],
            'semi_annual' => [6, $monthly * 6],
            'annual' => [12, $monthly * 12],
            default => [1, $monthly],
        };

        $due = Carbon::parse($lease->start_date)->startOfDay();
        $end = Carbon::parse($lease->end_date)->startOfDay();

        while ($due->lte($end)) {
            $exists = $lease->rentPaymentInstallments()->whereDate('due_date', $due->toDateString())->exists();
            if (! $exists) {
                RentPaymentInstallment::create([
                    'user_id' => $lease->user_id,
                    'lease_id' => $lease->id,
                    'due_date' => $due->toDateString(),
                    'amount_due' => $amount,
                    'amount_paid' => 0,
                    'status' => RentPaymentInstallment::STATUS_PENDING,
                ]);
            }
            $due->addMonths($stepMonths);
        }
    }
}
