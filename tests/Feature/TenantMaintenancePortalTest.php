<?php

namespace Tests\Feature;

use App\Models\Lease;
use App\Models\MaintenanceRequest;
use App\Models\Property;
use App\Models\RentalTenant;
use App\Models\RentalUnit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TenantMaintenancePortalTest extends TestCase
{
    use RefreshDatabase;

    public function test_tenant_can_submit_maintenance_for_their_lease(): void
    {
        Role::query()->firstOrCreate(['name' => 'landlord', 'guard_name' => 'web']);
        Role::query()->firstOrCreate(['name' => 'tenant', 'guard_name' => 'web']);

        $landlord = User::factory()->create();
        $landlord->assignRole('landlord');

        $this->actingAs($landlord);
        $property = Property::query()->create(['name' => 'Riverside', 'is_active' => true]);
        $unit = RentalUnit::query()->create([
            'property_id' => $property->id,
            'label' => '2B',
            'monthly_rent' => 800,
            'is_active' => true,
        ]);
        $renter = RentalTenant::query()->create([
            'full_name' => 'Alex Rivera',
            'email' => 'alex.renter@example.test',
        ]);
        Lease::query()->create([
            'property_id' => $property->id,
            'unit_id' => $unit->id,
            'tenant_id' => $renter->id,
            'start_date' => now()->subWeek(),
            'end_date' => now()->addYear(),
            'monthly_rent' => 800,
            'payment_frequency' => 'monthly',
            'status' => 'active',
        ]);

        $tenantUser = User::factory()->create([
            'email' => 'alex.renter@example.test',
        ]);
        $tenantUser->assignRole('tenant');

        $this->actingAs($tenantUser)
            ->post(route('tenant.maintenance.store'), [
                'property_id' => $property->id,
                'unit_id' => $unit->id,
                'title' => 'Leaky faucet',
                'description' => 'Kitchen sink',
                'priority' => 'medium',
            ])
            ->assertRedirect(route('tenant.maintenance.index'));

        $this->assertDatabaseHas('rental_maintenance_requests', [
            'property_id' => $property->id,
            'rental_tenant_id' => $renter->id,
            'user_id' => $landlord->id,
            'title' => 'Leaky faucet',
            'status' => 'open',
        ]);
    }

    public function test_tenant_cannot_access_maintenance_index_without_profile(): void
    {
        Role::query()->firstOrCreate(['name' => 'tenant', 'guard_name' => 'web']);
        $user = User::factory()->create(['email' => 'orphan@example.test']);
        $user->assignRole('tenant');

        $this->actingAs($user)
            ->get(route('tenant.maintenance.index'))
            ->assertForbidden();
    }
}
