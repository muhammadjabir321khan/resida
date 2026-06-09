<?php

namespace Tests\Feature;

use App\Models\Lease;
use App\Models\Property;
use App\Models\RentPaymentInstallment;
use App\Models\RentalTenant;
use App\Models\RentalUnit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class RentalAutomationCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_automate_command_expires_leases_marks_overdue_and_sets_unit_vacant(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $property = Property::query()->create([
            'name' => 'Test Prop',
            'is_active' => true,
        ]);
        $unit = RentalUnit::query()->create([
            'property_id' => $property->id,
            'label' => 'A1',
            'monthly_rent' => 500,
            'is_active' => true,
        ]);
        $tenant = RentalTenant::query()->create([
            'full_name' => 'Jane Doe',
        ]);
        $lease = Lease::query()->create([
            'property_id' => $property->id,
            'unit_id' => $unit->id,
            'tenant_id' => $tenant->id,
            'start_date' => now()->subDays(10),
            'end_date' => now()->subDay(),
            'monthly_rent' => 500,
            'payment_frequency' => 'monthly',
            'status' => 'active',
        ]);

        RentPaymentInstallment::query()->create([
            'lease_id' => $lease->id,
            'due_date' => now()->subDays(5),
            'amount_due' => 100,
            'status' => RentPaymentInstallment::STATUS_PENDING,
        ]);

        Artisan::call('rental:automate-statuses');

        $lease->refresh();
        $unit->refresh();
        $installment = RentPaymentInstallment::query()->where('lease_id', $lease->id)->first();

        $this->assertSame('expired', $lease->status);
        $this->assertNotNull($installment);
        $this->assertSame(RentPaymentInstallment::STATUS_OVERDUE, $installment->status);
        $this->assertSame(RentalUnit::OCCUPANCY_VACANT, $unit->occupancy_status);
    }

    public function test_active_lease_marks_unit_occupied(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $property = Property::query()->create([
            'name' => 'P',
            'is_active' => true,
        ]);
        $unit = RentalUnit::query()->create([
            'property_id' => $property->id,
            'label' => 'B2',
            'monthly_rent' => 400,
            'is_active' => true,
        ]);
        $tenant = RentalTenant::query()->create(['full_name' => 'Bob']);
        Lease::query()->create([
            'property_id' => $property->id,
            'unit_id' => $unit->id,
            'tenant_id' => $tenant->id,
            'start_date' => now()->subWeek(),
            'end_date' => now()->addMonth(),
            'monthly_rent' => 400,
            'payment_frequency' => 'monthly',
            'status' => 'active',
        ]);

        Artisan::call('rental:automate-statuses');

        $unit->refresh();
        $this->assertSame(RentalUnit::OCCUPANCY_OCCUPIED, $unit->occupancy_status);
    }
}
