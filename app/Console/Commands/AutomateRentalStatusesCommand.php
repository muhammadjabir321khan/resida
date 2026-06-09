<?php

namespace App\Console\Commands;

use App\Models\Lease;
use App\Models\RentalUnit;
use App\Models\RentPaymentInstallment;
use Illuminate\Console\Command;

class AutomateRentalStatusesCommand extends Command
{
    protected $signature = 'rental:automate-statuses';

    protected $description = 'Expire ended leases, mark overdue rent installments, and sync unit occupancy (run daily via scheduler).';

    public function handle(): int
    {
        $today = now()->toDateString();

        $expiredLeases = Lease::query()
            ->whereIn('status', ['active', 'draft'])
            ->whereNotNull('end_date')
            ->whereDate('end_date', '<', $today)
            ->update(['status' => 'expired']);

        $overduePayments = RentPaymentInstallment::query()
            ->where('status', RentPaymentInstallment::STATUS_PENDING)
            ->whereDate('due_date', '<', $today)
            ->update(['status' => RentPaymentInstallment::STATUS_OVERDUE]);

        $unitOccupancyUpdates = 0;
        RentalUnit::query()->orderBy('id')->chunkById(200, function ($units) use ($today, &$unitOccupancyUpdates): void {
            foreach ($units as $unit) {
                $occupied = Lease::query()
                    ->where('unit_id', $unit->id)
                    ->where('status', 'active')
                    ->whereNotNull('start_date')
                    ->whereNotNull('end_date')
                    ->whereDate('start_date', '<=', $today)
                    ->whereDate('end_date', '>=', $today)
                    ->exists();

                $next = $occupied ? RentalUnit::OCCUPANCY_OCCUPIED : RentalUnit::OCCUPANCY_VACANT;
                $current = $unit->occupancy_status ?? RentalUnit::OCCUPANCY_VACANT;
                if ($current !== $next) {
                    $unit->forceFill(['occupancy_status' => $next])->save();
                    $unitOccupancyUpdates++;
                }
            }
        });

        $this->info(sprintf(
            'Leases expired: %d | Installments marked overdue: %d | Unit occupancy rows updated: %d',
            $expiredLeases,
            $overduePayments,
            $unitOccupancyUpdates
        ));

        return self::SUCCESS;
    }
}
