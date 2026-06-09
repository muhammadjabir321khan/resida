<?php

namespace Tests\Feature;

use App\Models\Lease;
use App\Models\Property;
use App\Models\RentPaymentInstallment;
use App\Models\RentalTenant;
use App\Models\RentalTransaction;
use App\Models\RentalUnit;
use App\Models\User;
use App\Services\RentPaymentRecorderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TenantRentPaymentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::query()->firstOrCreate(['name' => 'landlord', 'guard_name' => 'web']);
        Role::query()->firstOrCreate(['name' => 'tenant', 'guard_name' => 'web']);
    }

    public function test_tenant_cannot_checkout_another_tenants_installment(): void
    {
        config(['rental.stripe.use_platform_fallback' => false]);

        $landlord = User::factory()->create();
        $landlord->assignRole('landlord');

        $this->actingAs($landlord);
        $property = Property::query()->create(['name' => 'Pay Prop', 'is_active' => true]);
        $unit = RentalUnit::query()->create([
            'property_id' => $property->id,
            'label' => '1',
            'monthly_rent' => 500,
            'is_active' => true,
        ]);
        $tenantA = RentalTenant::query()->create(['full_name' => 'A', 'email' => 'a@pay.test']);
        $tenantB = RentalTenant::query()->create(['full_name' => 'B', 'email' => 'b@pay.test']);
        $lease = Lease::query()->create([
            'property_id' => $property->id,
            'unit_id' => $unit->id,
            'tenant_id' => $tenantA->id,
            'start_date' => now()->subMonth(),
            'end_date' => now()->addYear(),
            'monthly_rent' => 500,
            'payment_frequency' => 'monthly',
            'status' => 'active',
        ]);
        $installment = RentPaymentInstallment::query()->create([
            'lease_id' => $lease->id,
            'due_date' => now()->addDays(3),
            'amount_due' => 500,
            'status' => RentPaymentInstallment::STATUS_PENDING,
        ]);

        $userB = User::factory()->create(['email' => 'b@pay.test']);
        $userB->assignRole('tenant');
        $tenantB->update(['linked_user_id' => $userB->id]);

        $this->actingAs($userB)
            ->post(route('tenant.rent.checkout', $installment->id))
            ->assertForbidden();
    }

    public function test_mark_paid_from_stripe_records_installment_and_income(): void
    {
        $landlord = User::factory()->create();
        $landlord->assignRole('landlord');

        $this->actingAs($landlord);
        $property = Property::query()->create(['name' => 'Income Prop', 'is_active' => true]);
        $unit = RentalUnit::query()->create([
            'property_id' => $property->id,
            'label' => '2',
            'monthly_rent' => 750,
            'is_active' => true,
        ]);
        $tenant = RentalTenant::query()->create(['full_name' => 'Payer', 'email' => 'payer@pay.test']);
        $lease = Lease::query()->create([
            'property_id' => $property->id,
            'unit_id' => $unit->id,
            'tenant_id' => $tenant->id,
            'start_date' => now()->subMonth(),
            'end_date' => now()->addYear(),
            'monthly_rent' => 750,
            'payment_frequency' => 'monthly',
            'status' => 'active',
        ]);
        $installment = RentPaymentInstallment::query()->create([
            'lease_id' => $lease->id,
            'due_date' => now(),
            'amount_due' => 750,
            'status' => RentPaymentInstallment::STATUS_PENDING,
        ]);

        app(RentPaymentRecorderService::class)->markPaidFromStripe(
            $installment,
            750.00,
            'cs_test_rent_123',
            'pi_test_rent_123',
        );

        $installment->refresh();
        $this->assertSame(RentPaymentInstallment::STATUS_PAID, $installment->status);
        $this->assertSame(750.0, (float) $installment->amount_paid);
        $this->assertSame('stripe', $installment->payment_method);
        $this->assertSame('cs_test_rent_123', $installment->stripe_checkout_session_id);

        $this->assertDatabaseHas('rental_transactions', [
            'user_id' => $landlord->id,
            'direction' => 'income',
            'reference' => 'stripe:cs_test_rent_123',
        ]);
    }

    public function test_tenant_dashboard_shows_pay_now_when_stripe_configured(): void
    {
        config(['cashier.secret' => 'sk_test_fake', 'rental.stripe.use_platform_fallback' => true]);

        $landlord = User::factory()->create();
        $landlord->assignRole('landlord');

        $this->actingAs($landlord);
        $property = Property::query()->create(['name' => 'UI Prop', 'is_active' => true]);
        $unit = RentalUnit::query()->create([
            'property_id' => $property->id,
            'label' => '3',
            'monthly_rent' => 400,
            'is_active' => true,
        ]);
        $tenant = RentalTenant::query()->create([
            'full_name' => 'UI Tenant',
            'email' => 'ui.tenant@pay.test',
            'linked_user_id' => null,
        ]);
        $lease = Lease::query()->create([
            'property_id' => $property->id,
            'unit_id' => $unit->id,
            'tenant_id' => $tenant->id,
            'start_date' => now()->subMonth(),
            'end_date' => now()->addYear(),
            'monthly_rent' => 400,
            'payment_frequency' => 'monthly',
            'status' => 'active',
        ]);
        RentPaymentInstallment::query()->create([
            'lease_id' => $lease->id,
            'due_date' => now()->addDays(2),
            'amount_due' => 400,
            'status' => RentPaymentInstallment::STATUS_PENDING,
        ]);

        $tenantUser = User::factory()->create(['email' => 'ui.tenant@pay.test']);
        $tenantUser->assignRole('tenant');
        $tenant->update(['linked_user_id' => $tenantUser->id]);

        $this->actingAs($tenantUser)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee(__('Pay now'));
    }
}
