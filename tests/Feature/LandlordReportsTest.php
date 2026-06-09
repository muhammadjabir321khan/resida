<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\LandlordReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Cashier\Subscription;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class LandlordReportsTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_view_reports(): void
    {
        $this->get(route('rental.reports.index'))
            ->assertRedirect();
    }

    public function test_landlord_without_subscription_redirects_from_reports(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('rental.reports.index'))
            ->assertRedirect(route('billing.plans'));
    }

    public function test_subscribed_user_can_view_reports_and_download_csv(): void
    {
        $user = User::factory()->create();
        Subscription::factory()->for($user)->create();

        $this->actingAs($user)
            ->get(route('rental.reports.index'))
            ->assertOk()
            ->assertSee(__('Reports'), false);

        $this->actingAs($user)
            ->get(route('rental.reports.export', [
                'type' => LandlordReportService::TYPE_TENANT_DIRECTORY,
                'format' => 'csv',
            ]))
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    public function test_subscribed_user_can_download_pdf(): void
    {
        $user = User::factory()->create();
        Subscription::factory()->for($user)->create();

        $this->actingAs($user)
            ->get(route('rental.reports.export', [
                'type' => LandlordReportService::TYPE_PROPERTY_PORTFOLIO,
                'format' => 'pdf',
            ]))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_invalid_export_type_returns_validation_error(): void
    {
        $user = User::factory()->create();
        Subscription::factory()->for($user)->create();

        $this->actingAs($user)
            ->from(route('rental.reports.index'))
            ->get(route('rental.reports.export', [
                'type' => 'not_a_report',
                'format' => 'csv',
            ]))
            ->assertSessionHasErrors(['type']);
    }

    public function test_tenant_user_cannot_access_reports(): void
    {
        Role::query()->firstOrCreate(['name' => 'tenant', 'guard_name' => 'web']);
        $user = User::factory()->create();
        $user->assignRole('tenant');

        $this->actingAs($user)
            ->get(route('rental.reports.index'))
            ->assertRedirect(route('dashboard'));
    }
}
