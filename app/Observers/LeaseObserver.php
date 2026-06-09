<?php

namespace App\Observers;

use App\Models\Lease;
use App\Services\ActivityLogger;

class LeaseObserver
{
    public function created(Lease $lease): void
    {
        if (! auth()->check()) {
            return;
        }

        ActivityLogger::log(auth()->user(), 'lease.created', $lease, [
            'status' => $lease->status,
            'property_id' => $lease->property_id,
        ]);
    }

    public function updated(Lease $lease): void
    {
        if (! auth()->check()) {
            return;
        }

        $tracked = ['status', 'monthly_rent', 'start_date', 'end_date', 'tenant_id', 'unit_id'];
        $changes = collect($lease->getChanges())->only($tracked);
        if ($changes->isEmpty()) {
            return;
        }

        ActivityLogger::log(auth()->user(), 'lease.updated', $lease, [
            'changes' => $changes->all(),
        ]);
    }
}
