<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Cashier\Subscription;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SubscriptionGateTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_redirects_to_plans_without_subscription(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertRedirect(route('billing.plans'));
    }

    public function test_dashboard_loads_with_active_subscription(): void
    {
        $user = User::factory()->create();
        Subscription::factory()->for($user)->create();

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk();
    }

    public function test_plans_page_reachable_without_subscription(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('billing.plans'))
            ->assertOk();
    }

    public function test_billing_success_without_session_redirects_to_plans(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('billing.success'))
            ->assertRedirect(route('billing.plans'));
    }

    public function test_tenant_can_open_dashboard_without_subscription(): void
    {
        Role::query()->firstOrCreate(['name' => 'tenant', 'guard_name' => 'web']);
        $user = User::factory()->create();
        $user->assignRole('tenant');

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk();
    }

    public function test_admin_can_open_dashboard_without_subscription(): void
    {
        Role::query()->firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $user = User::factory()->create();
        $user->assignRole('admin');

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk();
    }
}
