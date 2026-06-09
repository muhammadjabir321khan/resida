<?php

namespace App\Http\Controllers;

use App\Models\Lease;
use App\Models\MaintenanceRequest;
use App\Models\Property;
use App\Models\RentalTenant;
use App\Models\RentalTransaction;
use App\Models\RentalUnit;
use App\Models\RentPaymentInstallment;
use App\Models\User;
use App\Services\TenantProfileResolver;
use Illuminate\View\View;
use Laravel\Cashier\Subscription;
use Spatie\Permission\Models\Role;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = request()->user();

        if ($user->hasRole('admin')) {
            return view('dashboard', [
                'dashboardRole' => 'admin',
                'adminStats' => $this->adminStats(),
                'adminChart' => $this->adminUsersPerMonthChart(),
            ]);
        }

        if ($user->hasRole('tenant')) {
            $tenantContext = $this->tenantContext($user);

            return view('dashboard', [
                'dashboardRole' => 'tenant',
                'tenantContext' => $tenantContext,
                'tenantChart' => $this->tenantPaymentStatusChart($tenantContext),
            ]);
        }

        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();

        $stats = [
            'properties' => Property::count(),
            'units' => RentalUnit::count(),
            'active_leases' => Lease::where('status', 'active')->count(),
            'open_maintenance' => MaintenanceRequest::whereIn('status', ['open', 'in_progress'])->count(),
            'monthly_rent_roll' => (float) Lease::where('status', 'active')->sum('monthly_rent'),
            'income_mtd' => (float) RentalTransaction::where('direction', RentalTransaction::DIRECTION_INCOME)
                ->whereBetween('transaction_date', [$startOfMonth, $endOfMonth])
                ->sum('amount'),
            'expense_mtd' => (float) RentalTransaction::where('direction', RentalTransaction::DIRECTION_EXPENSE)
                ->whereBetween('transaction_date', [$startOfMonth, $endOfMonth])
                ->sum('amount'),
            'overdue_installments' => RentPaymentInstallment::where('status', RentPaymentInstallment::STATUS_PENDING)
                ->where('due_date', '<', now()->toDateString())
                ->count(),
        ];

        return view('dashboard', [
            'dashboardRole' => 'landlord',
            'stats' => $stats,
            'landlordChart' => $this->landlordIncomeExpenseChart(),
        ]);
    }

    /**
     * @return array{user_count: int, role_count: int, active_subscriptions: int, landlord_count: int}
     */
    private function adminStats(): array
    {
        return [
            'user_count' => User::count(),
            'role_count' => Role::query()->where('guard_name', 'web')->count(),
            'active_subscriptions' => Subscription::query()->whereIn('stripe_status', ['active', 'trialing'])->count(),
            'landlord_count' => User::query()
                ->whereHas('roles', fn ($q) => $q->where('guard_name', 'web')->whereIn('name', ['user', 'landlord']))
                ->count(),
        ];
    }

    /**
     * @return array{labels: list<string>, series: list<int>}
     */
    private function adminUsersPerMonthChart(): array
    {
        $labels = [];
        $series = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $labels[] = $month->format('M Y');
            $series[] = (int) User::query()
                ->whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->count();
        }

        return ['labels' => $labels, 'series' => $series];
    }

    /**
     * @return array{labels: list<string>, income: list<float>, expense: list<float>}
     */
    private function landlordIncomeExpenseChart(): array
    {
        $labels = [];
        $income = [];
        $expense = [];

        for ($i = 5; $i >= 0; $i--) {
            $start = now()->subMonths($i)->startOfMonth();
            $end = now()->subMonths($i)->endOfMonth();
            $labels[] = $start->format('M Y');
            $income[] = (float) RentalTransaction::query()
                ->where('direction', RentalTransaction::DIRECTION_INCOME)
                ->whereBetween('transaction_date', [$start, $end])
                ->sum('amount');
            $expense[] = (float) RentalTransaction::query()
                ->where('direction', RentalTransaction::DIRECTION_EXPENSE)
                ->whereBetween('transaction_date', [$start, $end])
                ->sum('amount');
        }

        return ['labels' => $labels, 'income' => $income, 'expense' => $expense];
    }

    /**
     * @return array{profile: ?RentalTenant, leases: \Illuminate\Database\Eloquent\Collection<int, Lease>, upcoming: \Illuminate\Support\Collection, maintenance_open: int, paid_history: \Illuminate\Support\Collection<int, RentPaymentInstallment>, recent_maintenance: \Illuminate\Support\Collection<int, MaintenanceRequest>}
     */
    private function tenantContext(User $user): array
    {
        $profile = TenantProfileResolver::forUser($user);

        if ($profile === null) {
            return [
                'profile' => null,
                'leases' => collect(),
                'upcoming' => collect(),
                'maintenance_open' => 0,
                'paid_history' => collect(),
                'recent_maintenance' => collect(),
            ];
        }

        $leases = Lease::withoutLandlordScope()
            ->where('tenant_id', $profile->id)
            ->with(['property', 'unit'])
            ->orderByDesc('start_date')
            ->get();

        $leaseIds = $leases->pluck('id')->all();

        $upcoming = $leaseIds === []
            ? collect()
            : RentPaymentInstallment::withoutLandlordScope()
                ->whereIn('lease_id', $leaseIds)
                ->whereIn('status', [RentPaymentInstallment::STATUS_PENDING, RentPaymentInstallment::STATUS_OVERDUE])
                ->with('lease')
                ->orderBy('due_date')
                ->limit(8)
                ->get();

        $maintenanceOpen = MaintenanceRequest::withoutLandlordScope()
            ->whereIn('status', ['open', 'in_progress'])
            ->where('rental_tenant_id', $profile->id)
            ->count();

        $paidHistory = $leaseIds === []
            ? collect()
            : RentPaymentInstallment::withoutLandlordScope()
                ->whereIn('lease_id', $leaseIds)
                ->where('status', RentPaymentInstallment::STATUS_PAID)
                ->with(['lease.property'])
                ->orderByDesc('paid_date')
                ->orderByDesc('due_date')
                ->limit(12)
                ->get();

        $recentMaintenance = MaintenanceRequest::withoutLandlordScope()
            ->where('rental_tenant_id', $profile->id)
            ->with(['property', 'unit'])
            ->orderByDesc('created_at')
            ->limit(8)
            ->get();

        return [
            'profile' => $profile,
            'leases' => $leases,
            'upcoming' => $upcoming,
            'maintenance_open' => $maintenanceOpen,
            'paid_history' => $paidHistory,
            'recent_maintenance' => $recentMaintenance,
        ];
    }

    /**
     * @param  array{profile: ?RentalTenant, leases: mixed, upcoming: mixed, maintenance_open: int}  $ctx
     * @return array{labels: list<string>, series: list<int>}
     */
    private function tenantPaymentStatusChart(array $ctx): array
    {
        $profile = $ctx['profile'];
        if ($profile === null) {
            return ['labels' => [], 'series' => []];
        }

        $leaseIds = Lease::withoutLandlordScope()->where('tenant_id', $profile->id)->pluck('id');
        if ($leaseIds->isEmpty()) {
            return ['labels' => [], 'series' => []];
        }

        $base = RentPaymentInstallment::withoutLandlordScope()->whereIn('lease_id', $leaseIds);

        $paid = (clone $base)->where('status', RentPaymentInstallment::STATUS_PAID)->count();
        $pending = (clone $base)->where('status', RentPaymentInstallment::STATUS_PENDING)->count();
        $overdue = (clone $base)->where('status', RentPaymentInstallment::STATUS_OVERDUE)->count();
        $waived = (clone $base)->where('status', RentPaymentInstallment::STATUS_WAIVED)->count();

        return [
            'labels' => [__('Paid'), __('Pending'), __('Overdue'), __('Waived')],
            'series' => [$paid, $pending, $overdue, $waived],
        ];
    }
}
