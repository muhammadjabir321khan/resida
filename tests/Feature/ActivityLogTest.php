<?php

namespace Tests\Feature;

use App\Models\Lease;
use App\Models\Property;
use App\Models\RentalTenant;
use App\Models\RentalUnit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ActivityLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_lease_update_is_logged_when_authenticated(): void
    {
        Role::query()->firstOrCreate(['name' => 'landlord', 'guard_name' => 'web']);
        $user = User::factory()->create();
        $user->assignRole('landlord');

        $this->actingAs($user);

        $property = Property::query()->create(['name' => 'P1', 'is_active' => true]);
        $unit = RentalUnit::query()->create([
            'property_id' => $property->id,
            'label' => '1',
            'monthly_rent' => 100,
            'is_active' => true,
        ]);
        $tenant = RentalTenant::query()->create(['full_name' => 'T', 'email' => 't@t.test']);
        $lease = Lease::query()->create([
            'property_id' => $property->id,
            'unit_id' => $unit->id,
            'tenant_id' => $tenant->id,
            'start_date' => now()->subMonth(),
            'end_date' => now()->addMonth(),
            'monthly_rent' => 500,
            'payment_frequency' => 'monthly',
            'status' => 'active',
        ]);

        $lease->update(['status' => 'expired']);

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $user->id,
            'action' => 'lease.updated',
            'subject_id' => $lease->id,
        ]);
    }
}
