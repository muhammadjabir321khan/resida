<?php

namespace Tests\Feature;

use App\Models\Property;
use App\Models\RentalUnit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Cashier\Subscription;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PlanLimitTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::query()->firstOrCreate(['name' => 'landlord', 'guard_name' => 'web']);
    }

    public function test_starter_plan_blocks_unit_creation_at_limit(): void
    {
        config(['subscription.plans.starter.price_id' => 'price_starter_test']);

        $landlord = User::factory()->create();
        $landlord->assignRole('landlord');

        Subscription::factory()->for($landlord)->create([
            'stripe_price' => 'price_starter_test',
            'stripe_status' => 'active',
        ]);

        $this->actingAs($landlord);

        $property = Property::query()->create(['name' => 'Limit Prop', 'is_active' => true]);

        for ($i = 1; $i <= 25; $i++) {
            RentalUnit::query()->create([
                'property_id' => $property->id,
                'label' => 'Unit '.$i,
                'monthly_rent' => 100,
                'is_active' => true,
            ]);
        }

        $response = $this->post(route('rental.units.store'), [
            'property_id' => $property->id,
            'label' => 'Unit 26',
            'monthly_rent' => 100,
            'is_active' => 1,
        ]);

        $response->assertRedirect(route('billing.plans'));
        $this->assertSame(25, RentalUnit::query()->count());
    }

    public function test_growth_plan_allows_unlimited_units(): void
    {
        config(['subscription.plans.growth.price_id' => 'price_growth_test']);

        $landlord = User::factory()->create();
        $landlord->assignRole('landlord');

        Subscription::factory()->for($landlord)->create([
            'stripe_price' => 'price_growth_test',
            'stripe_status' => 'active',
        ]);

        $this->actingAs($landlord);

        $property = Property::query()->create(['name' => 'Growth Prop', 'is_active' => true]);

        for ($i = 1; $i <= 30; $i++) {
            RentalUnit::query()->create([
                'property_id' => $property->id,
                'label' => 'Unit '.$i,
                'monthly_rent' => 100,
                'is_active' => true,
            ]);
        }

        $this->post(route('rental.units.store'), [
            'property_id' => $property->id,
            'label' => 'Unit 31',
            'monthly_rent' => 100,
            'is_active' => 1,
        ])->assertRedirect(route('rental.units.index'));

        $this->assertSame(31, RentalUnit::query()->count());
    }
}
