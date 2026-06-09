<?php

namespace App\View\Composers;

use App\Models\Lease;
use App\Models\MaintenanceRequest;
use App\Models\RentPaymentInstallment;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class HeaderNotificationsComposer
{
    public function compose(View $view): void
    {
        $user = auth()->user();

        if ($user === null || $this->shouldSkipRentalInsights($user)) {
            $view->with([
                'headerNotificationItems' => collect(),
                'headerNotificationCount' => 0,
            ]);

            return;
        }

        $overdueCount = $this->overdueInstallmentsQuery()->count();
        $leaseCount = $this->leasesEndingSoonQuery()->count();
        $maintCount = $this->openMaintenanceQuery()->count();
        $count = min(99, $overdueCount + $leaseCount + $maintCount);

        $items = Collection::make();

        $this->overdueInstallmentsQuery()
            ->with(['lease.property', 'lease.tenant'])
            ->orderBy('due_date')
            ->limit(5)
            ->get()
            ->each(function (RentPaymentInstallment $row) use ($items): void {
                $prop = $row->lease?->property?->name ?? __('Property');
                $items->push([
                    'key' => 'payment-'.$row->id,
                    'title' => __('Overdue or pending rent'),
                    'subtitle' => $prop.' · '.__('Due').' '.($row->due_date?->toDateString() ?? '—'),
                    'url' => route('rental.payments.edit', $row),
                    'tone' => 'danger',
                ]);
            });

        $this->leasesEndingSoonQuery()
            ->with(['property', 'tenant'])
            ->orderBy('end_date')
            ->limit(4)
            ->get()
            ->each(function (Lease $lease) use ($items): void {
                $prop = $lease->property?->name ?? __('Property');
                $items->push([
                    'key' => 'lease-'.$lease->id,
                    'title' => __('Lease ending soon'),
                    'subtitle' => $prop.' · '.__('Ends').' '.($lease->end_date?->toDateString() ?? '—'),
                    'url' => route('rental.leases.edit', $lease),
                    'tone' => 'warning',
                ]);
            });

        $this->openMaintenanceQuery()
            ->with('property')
            ->orderByDesc('reported_on')
            ->limit(4)
            ->get()
            ->each(function (MaintenanceRequest $row) use ($items): void {
                $prop = $row->property?->name ?? __('Property');
                $items->push([
                    'key' => 'maint-'.$row->id,
                    'title' => $row->title ?: __('Maintenance request'),
                    'subtitle' => $prop.' · '.__('Status').': '.$row->status,
                    'url' => route('rental.maintenance-requests.edit', $row),
                    'tone' => 'primary',
                ]);
            });

        $view->with([
            'headerNotificationItems' => $items->take(10)->values(),
            'headerNotificationCount' => $count,
        ]);
    }

    /**
     * @return Builder<RentPaymentInstallment>
     */
    private function overdueInstallmentsQuery(): Builder
    {
        return RentPaymentInstallment::query()
            ->where(function ($q): void {
                $q->where('status', RentPaymentInstallment::STATUS_OVERDUE)
                    ->orWhere(function ($q2): void {
                        $q2->where('status', RentPaymentInstallment::STATUS_PENDING)
                            ->whereDate('due_date', '<', now());
                    });
            });
    }

    /**
     * @return Builder<Lease>
     */
    private function leasesEndingSoonQuery(): Builder
    {
        return Lease::query()
            ->whereIn('status', ['active', 'draft'])
            ->whereNotNull('end_date')
            ->whereBetween('end_date', [now()->toDateString(), now()->addDays(30)->toDateString()]);
    }

    /**
     * @return Builder<MaintenanceRequest>
     */
    private function openMaintenanceQuery(): Builder
    {
        return MaintenanceRequest::query()
            ->whereIn('status', ['open', 'in_progress']);
    }

    private function shouldSkipRentalInsights(User $user): bool
    {
        return $user->hasRole('tenant') && ! $user->hasAnyRole(['admin', 'user', 'landlord']);
    }
}
