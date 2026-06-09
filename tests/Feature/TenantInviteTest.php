<?php

namespace Tests\Feature;

use App\Mail\TenantPortalInviteMail;
use App\Models\RentalTenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Laravel\Cashier\Subscription;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TenantInviteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::query()->firstOrCreate(['name' => 'landlord', 'guard_name' => 'web']);
        Role::query()->firstOrCreate(['name' => 'tenant', 'guard_name' => 'web']);
    }

    public function test_landlord_can_send_portal_invite(): void
    {
        Mail::fake();

        $landlord = User::factory()->create();
        $landlord->assignRole('landlord');
        Subscription::factory()->for($landlord)->create();

        $this->actingAs($landlord);

        $tenant = RentalTenant::query()->create([
            'full_name' => 'Alex Renter',
            'email' => 'alex.invite@example.com',
        ]);

        $this->post(route('rental.tenants.invite', $tenant))
            ->assertRedirect();

        Mail::assertSent(TenantPortalInviteMail::class, function (TenantPortalInviteMail $mail): bool {
            return $mail->tenant->email === 'alex.invite@example.com';
        });

        $tenant->refresh();
        $this->assertNotNull($tenant->invite_token);
        $this->assertNotNull($tenant->invited_at);
    }

    public function test_tenant_can_register_via_invite_and_get_linked(): void
    {
        $landlord = User::factory()->create();
        $landlord->assignRole('landlord');
        Subscription::factory()->for($landlord)->create();

        $this->actingAs($landlord);

        $tenant = RentalTenant::query()->create([
            'full_name' => 'Portal Tenant',
            'email' => 'portal.tenant@example.com',
        ]);

        $this->post(route('rental.tenants.invite', $tenant));
        $token = $tenant->fresh()->invite_token;
        $this->assertNotNull($token);

        auth()->logout();

        $this->get(route('tenant.invite.show', $token))->assertOk();

        $this->post(route('register'), [
            'name' => 'Portal Tenant',
            'email' => 'portal.tenant@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'tenant',
            'invite' => $token,
        ])->assertRedirect(route('dashboard'));

        $user = User::query()->where('email', 'portal.tenant@example.com')->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->hasRole('tenant'));

        $tenant->refresh();
        $this->assertSame($user->id, $tenant->linked_user_id);
        $this->assertNotNull($tenant->invite_accepted_at);
    }
}
