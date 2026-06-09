<?php

namespace App\Observers;

use App\Models\RentPaymentInstallment;
use App\Services\ActivityLogger;

class RentPaymentInstallmentObserver
{
    public function updated(RentPaymentInstallment $installment): void
    {
        if (! auth()->check()) {
            return;
        }

        $tracked = ['status', 'amount_due', 'amount_paid', 'paid_date', 'due_date'];
        $changes = collect($installment->getChanges())->only($tracked);
        if ($changes->isEmpty()) {
            return;
        }

        ActivityLogger::log(auth()->user(), 'rent_installment.updated', $installment, [
            'changes' => $changes->all(),
            'lease_id' => $installment->lease_id,
        ]);
    }
}
