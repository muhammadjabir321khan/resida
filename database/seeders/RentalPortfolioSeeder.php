<?php

namespace Database\Seeders;

use App\Models\Lease;
use App\Models\MaintenanceRequest;
use App\Models\Property;
use App\Models\RentalTenant;
use App\Models\RentalTransaction;
use App\Models\RentalUnit;
use App\Models\RentPaymentInstallment;
use App\Models\User;
use App\Services\LeasePaymentScheduleGenerator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RentalPortfolioSeeder extends Seeder
{
    public function run(): void
    {
        $landlord = User::query()->where('email', 'landlord@demo.test')->first();
        if ($landlord === null) {
            $this->command?->warn('Run DemoUsersSeeder first (landlord@demo.test missing).');

            return;
        }

        Auth::login($landlord);

        try {
            DB::transaction(function () use ($landlord): void {
                $this->seedPortfolio($landlord);
            });
        } finally {
            Auth::logout();
        }
    }

    private function seedPortfolio(User $landlord): void
    {
        $propertyA = Property::query()->firstWhere(['user_id' => $landlord->id, 'name' => 'Sunset Gardens'])
            ?? Property::forceCreate([
                'user_id' => $landlord->id,
                'name' => 'Sunset Gardens',
                'address_line_1' => '100 Main Street',
                'city' => 'Springfield',
                'state' => 'IL',
                'postal_code' => '62701',
                'country' => 'US',
                'property_type' => 'residential',
                'units_count' => 2,
                'market_value' => 425000,
                'owner_display_name' => 'Sunset Holdings LLC',
                'is_active' => true,
            ]);

        $propertyB = Property::query()->firstWhere(['user_id' => $landlord->id, 'name' => 'Riverside Lofts'])
            ?? Property::forceCreate([
                'user_id' => $landlord->id,
                'name' => 'Riverside Lofts',
                'address_line_1' => '22 River Road',
                'city' => 'Madison',
                'state' => 'WI',
                'postal_code' => '53703',
                'country' => 'US',
                'property_type' => 'mixed_use',
                'units_count' => 1,
                'market_value' => 310000,
                'is_active' => true,
            ]);

        $unitA1 = RentalUnit::query()->firstWhere(['property_id' => $propertyA->id, 'label' => '101'])
            ?? RentalUnit::forceCreate([
                'user_id' => $landlord->id,
                'property_id' => $propertyA->id,
                'label' => '101',
                'unit_type' => 'apartment',
                'bedrooms' => 2,
                'bathrooms' => 1.0,
                'monthly_rent' => 1450,
                'is_active' => true,
            ]);

        $unitA2 = RentalUnit::query()->firstWhere(['property_id' => $propertyA->id, 'label' => '102'])
            ?? RentalUnit::forceCreate([
                'user_id' => $landlord->id,
                'property_id' => $propertyA->id,
                'label' => '102',
                'unit_type' => 'apartment',
                'bedrooms' => 1,
                'bathrooms' => 1.0,
                'monthly_rent' => 1200,
                'is_active' => true,
            ]);

        $unitB1 = RentalUnit::query()->firstWhere(['property_id' => $propertyB->id, 'label' => 'Ground'])
            ?? RentalUnit::forceCreate([
                'user_id' => $landlord->id,
                'property_id' => $propertyB->id,
                'label' => 'Ground',
                'unit_type' => 'retail',
                'bedrooms' => 0,
                'bathrooms' => 1.0,
                'monthly_rent' => 2200,
                'is_active' => true,
            ]);

        $tenantAlex = RentalTenant::query()->firstWhere(['user_id' => $landlord->id, 'email' => 'alex.renter@example.com'])
            ?? RentalTenant::forceCreate([
                'user_id' => $landlord->id,
                'full_name' => 'Alex Rivera',
                'email' => 'alex.renter@example.com',
                'phone' => '+1 555-0101',
                'registered_on' => now()->subMonths(6)->toDateString(),
            ]);

        $tenantDemo = RentalTenant::query()->firstWhere(['user_id' => $landlord->id, 'email' => 'tenant@demo.test'])
            ?? RentalTenant::forceCreate([
                'user_id' => $landlord->id,
                'full_name' => 'Demo Tenant (portal)',
                'email' => 'tenant@demo.test',
                'phone' => '+1 555-0102',
                'registered_on' => now()->subMonth()->toDateString(),
            ]);

        $tenantPortalUser = User::query()->where('email', 'tenant@demo.test')->first();
        if ($tenantPortalUser !== null && $tenantDemo->linked_user_id !== $tenantPortalUser->id) {
            $tenantDemo->update([
                'linked_user_id' => $tenantPortalUser->id,
                'invite_accepted_at' => $tenantDemo->invite_accepted_at ?? now(),
            ]);
        }

        $leaseActive = Lease::query()->where('user_id', $landlord->id)
            ->where('property_id', $propertyA->id)
            ->where('unit_id', $unitA1->id)
            ->where('tenant_id', $tenantAlex->id)
            ->whereDate('start_date', now()->subMonths(3)->toDateString())
            ->first()
            ?? Lease::forceCreate([
                'user_id' => $landlord->id,
                'property_id' => $propertyA->id,
                'unit_id' => $unitA1->id,
                'tenant_id' => $tenantAlex->id,
                'start_date' => now()->subMonths(3)->toDateString(),
                'end_date' => now()->addMonths(9)->toDateString(),
                'monthly_rent' => 1450,
                'payment_frequency' => 'monthly',
                'security_deposit' => 1450,
                'status' => 'active',
                'terms_notes' => 'Demo lease with generated rent schedule.',
            ]);

        app(LeasePaymentScheduleGenerator::class)->sync($leaseActive, replacePending: true);

        $firstPending = RentPaymentInstallment::query()
            ->where('lease_id', $leaseActive->id)
            ->where('status', RentPaymentInstallment::STATUS_PENDING)
            ->orderBy('due_date')
            ->first();
        if ($firstPending) {
            $firstPending->update([
                'amount_paid' => $firstPending->amount_due,
                'status' => RentPaymentInstallment::STATUS_PAID,
                'paid_date' => now()->subWeek()->toDateString(),
                'payment_method' => 'ach',
                'receipt_number' => 'RCPT-1001',
            ]);
        }

        $draftLease = Lease::query()->where('user_id', $landlord->id)
            ->where('property_id', $propertyA->id)
            ->where('unit_id', $unitA2->id)
            ->where('tenant_id', $tenantDemo->id)
            ->first();
        if ($draftLease === null) {
            $draftLease = Lease::forceCreate([
                'user_id' => $landlord->id,
                'property_id' => $propertyA->id,
                'unit_id' => $unitA2->id,
                'tenant_id' => $tenantDemo->id,
                'start_date' => now()->subMonths(2)->toDateString(),
                'end_date' => now()->addMonths(10)->toDateString(),
                'monthly_rent' => 1200,
                'payment_frequency' => 'monthly',
                'security_deposit' => 1200,
                'status' => 'active',
                'terms_notes' => 'Demo tenant portal lease for messaging and rent.',
            ]);
        } elseif ($draftLease->status !== 'active') {
            $draftLease->update([
                'status' => 'active',
                'start_date' => $draftLease->start_date?->isFuture() ? now()->subMonths(2)->toDateString() : $draftLease->start_date,
            ]);
        }

        app(LeasePaymentScheduleGenerator::class)->sync($draftLease, replacePending: true);

        $this->ensureMaintenance(
            $landlord,
            $propertyA,
            $unitA1,
            'HVAC filter replacement',
            [
                'rental_tenant_id' => $tenantAlex->id,
                'category' => 'hvac',
                'description' => 'Replace MERV 11 filters in both returns.',
                'status' => 'open',
                'priority' => 'medium',
                'estimated_cost' => 85,
                'reported_on' => now()->subDays(5)->toDateString(),
            ],
        );

        $this->ensureMaintenance(
            $landlord,
            $propertyB,
            $unitB1,
            'Leaking faucet — restroom',
            [
                'category' => 'plumbing',
                'description' => 'Slow drip under sink; tenant reported.',
                'status' => 'in_progress',
                'priority' => 'high',
                'estimated_cost' => 150,
                'technician_name' => 'Pat\'s Plumbing',
                'reported_on' => now()->subDays(2)->toDateString(),
            ],
        );

        if (! RentalTransaction::query()->where('user_id', $landlord->id)->where('reference', 'DEMO-INC-001')->exists()) {
            RentalTransaction::forceCreate([
                'user_id' => $landlord->id,
                'property_id' => $propertyA->id,
                'lease_id' => $leaseActive->id,
                'direction' => RentalTransaction::DIRECTION_INCOME,
                'category' => 'rent',
                'amount' => 1450,
                'transaction_date' => now()->subMonth()->toDateString(),
                'description' => 'Monthly rent — unit 101',
                'reference' => 'DEMO-INC-001',
            ]);
        }

        if (! RentalTransaction::query()->where('user_id', $landlord->id)->where('reference', 'DEMO-EXP-001')->exists()) {
            RentalTransaction::forceCreate([
                'user_id' => $landlord->id,
                'property_id' => $propertyA->id,
                'lease_id' => null,
                'unit_id' => $unitA1->id,
                'direction' => RentalTransaction::DIRECTION_EXPENSE,
                'category' => 'maintenance',
                'amount' => 220,
                'transaction_date' => now()->subWeeks(2)->toDateString(),
                'description' => 'Minor electrical repair',
                'reference' => 'DEMO-EXP-001',
                'vendor_name' => 'Bright Spark Electric',
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function ensureMaintenance(User $landlord, Property $property, RentalUnit $unit, string $title, array $data): void
    {
        $exists = MaintenanceRequest::query()
            ->where('user_id', $landlord->id)
            ->where('property_id', $property->id)
            ->where('title', $title)
            ->exists();
        if ($exists) {
            return;
        }

        MaintenanceRequest::forceCreate(array_merge([
            'user_id' => $landlord->id,
            'property_id' => $property->id,
            'unit_id' => $unit->id,
            'title' => $title,
        ], $data));
    }
}
