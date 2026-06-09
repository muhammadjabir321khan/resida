<?php

namespace App\Console\Commands;

use App\Mail\LandlordRentalDigestMail;
use App\Models\Lease;
use App\Models\MaintenanceRequest;
use App\Models\RentPaymentInstallment;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;

class SendLandlordRentalDigestsCommand extends Command
{
    protected $signature = 'rental:send-landlord-digests';

    protected $description = 'Email landlords a daily summary when there are overdue rents, ending leases, or open maintenance (once per day per user).';

    public function handle(): int
    {
        $today = now()->toDateString();

        User::query()
            ->whereNotNull('email')
            ->where(function ($q): void {
                $q->whereHas('properties')
                    ->orWhereHas('leases');
            })
            ->chunkById(50, function ($users) use ($today): void {
                foreach ($users as $user) {
                    if ($user->hasRole('tenant') && ! $user->hasAnyRole(['landlord', 'user', 'admin'])) {
                        continue;
                    }

                    $cacheKey = 'landlord_rental_digest:v1:'.$user->id.':'.$today;
                    if (Cache::has($cacheKey)) {
                        continue;
                    }

                    $uid = $user->id;

                    $overdueCount = RentPaymentInstallment::query()
                        ->where('user_id', $uid)
                        ->where(function ($q): void {
                            $q->where('status', RentPaymentInstallment::STATUS_OVERDUE)
                                ->orWhere(function ($q2): void {
                                    $q2->where('status', RentPaymentInstallment::STATUS_PENDING)
                                        ->whereDate('due_date', '<', now());
                                });
                        })
                        ->count();

                    $leasesEndingCount = Lease::query()
                        ->where('user_id', $uid)
                        ->whereIn('status', ['active', 'draft'])
                        ->whereNotNull('end_date')
                        ->whereBetween('end_date', [now()->toDateString(), now()->addDays(30)->toDateString()])
                        ->count();

                    $openMaintenanceCount = MaintenanceRequest::query()
                        ->where('user_id', $uid)
                        ->whereIn('status', ['open', 'in_progress'])
                        ->count();

                    if ($overdueCount + $leasesEndingCount + $openMaintenanceCount === 0) {
                        continue;
                    }

                    $overdueLines = RentPaymentInstallment::query()
                        ->where('user_id', $uid)
                        ->where(function ($q): void {
                            $q->where('status', RentPaymentInstallment::STATUS_OVERDUE)
                                ->orWhere(function ($q2): void {
                                    $q2->where('status', RentPaymentInstallment::STATUS_PENDING)
                                        ->whereDate('due_date', '<', now());
                                });
                        })
                        ->with(['lease.property'])
                        ->orderBy('due_date')
                        ->limit(5)
                        ->get()
                        ->map(fn (RentPaymentInstallment $i) => ($i->lease?->property?->name ?? __('Property'))
                            .' — '.__('Due').' '.($i->due_date?->toDateString() ?? '—'))
                        ->all();

                    $leaseEndingLines = Lease::query()
                        ->where('user_id', $uid)
                        ->whereIn('status', ['active', 'draft'])
                        ->whereNotNull('end_date')
                        ->whereBetween('end_date', [now()->toDateString(), now()->addDays(30)->toDateString()])
                        ->with('property')
                        ->orderBy('end_date')
                        ->limit(5)
                        ->get()
                        ->map(fn (Lease $l) => ($l->property?->name ?? __('Property'))
                            .' — '.__('Ends').' '.($l->end_date?->toDateString() ?? '—'))
                        ->all();

                    $maintenanceLines = MaintenanceRequest::query()
                        ->where('user_id', $uid)
                        ->whereIn('status', ['open', 'in_progress'])
                        ->with('property')
                        ->orderByDesc('reported_on')
                        ->limit(5)
                        ->get()
                        ->map(fn (MaintenanceRequest $m) => ($m->property?->name ?? __('Property'))
                            .' — '.$m->title)
                        ->all();

                    Mail::to($user->email)->send(new LandlordRentalDigestMail(
                        $user->name ?? $user->email,
                        $overdueCount,
                        $leasesEndingCount,
                        $openMaintenanceCount,
                        $overdueLines,
                        $leaseEndingLines,
                        $maintenanceLines,
                    ));

                    Cache::put($cacheKey, true, now()->addDay());
                    $this->line("Digest sent to {$user->email}");
                }
            });

        return self::SUCCESS;
    }
}
