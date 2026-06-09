<?php

namespace Tests\Feature;

use App\Mail\NewRentalMessageMail;
use App\Models\Lease;
use App\Models\LeaseDocument;
use App\Models\MessageThread;
use App\Models\Property;
use App\Models\RentalTenant;
use App\Models\RentalUnit;
use App\Models\User;
use App\Services\MessageThreadService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Laravel\Cashier\Subscription;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PhaseThreeMessagingDocumentsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::query()->firstOrCreate(['name' => 'landlord', 'guard_name' => 'web']);
        Role::query()->firstOrCreate(['name' => 'tenant', 'guard_name' => 'web']);
    }

    public function test_landlord_can_upload_lease_document(): void
    {
        Storage::fake('public');

        $landlord = User::factory()->create();
        $landlord->assignRole('landlord');
        Subscription::factory()->for($landlord)->create();

        $this->actingAs($landlord);
        $property = Property::query()->create(['name' => 'Doc Prop', 'is_active' => true]);
        $unit = RentalUnit::query()->create([
            'property_id' => $property->id,
            'label' => '1',
            'monthly_rent' => 500,
            'is_active' => true,
        ]);
        $tenant = RentalTenant::query()->create(['full_name' => 'Doc Tenant', 'email' => 'doc.tenant@test']);
        $lease = Lease::query()->create([
            'property_id' => $property->id,
            'unit_id' => $unit->id,
            'tenant_id' => $tenant->id,
            'start_date' => now()->subMonth(),
            'end_date' => now()->addYear(),
            'monthly_rent' => 500,
            'payment_frequency' => 'monthly',
            'status' => 'active',
        ]);

        $this->post(route('rental.leases.documents.store', $lease), [
            'title' => 'Signed lease',
            'category' => LeaseDocument::CATEGORY_LEASE,
            'file' => UploadedFile::fake()->create('lease.pdf', 100, 'application/pdf'),
            'is_visible_to_tenant' => '1',
        ])->assertRedirect(route('rental.leases.edit', $lease));

        $this->assertDatabaseHas('lease_documents', [
            'lease_id' => $lease->id,
            'title' => 'Signed lease',
            'is_visible_to_tenant' => true,
        ]);
    }

    public function test_tenant_can_view_shared_documents(): void
    {
        Storage::fake('public');

        $landlord = User::factory()->create();
        $landlord->assignRole('landlord');
        Subscription::factory()->for($landlord)->create();

        $this->actingAs($landlord);
        $property = Property::query()->create(['name' => 'View Prop', 'is_active' => true]);
        $unit = RentalUnit::query()->create([
            'property_id' => $property->id,
            'label' => '2',
            'monthly_rent' => 500,
            'is_active' => true,
        ]);
        $tenant = RentalTenant::query()->create([
            'full_name' => 'View Tenant',
            'email' => 'view.tenant@test',
        ]);
        $lease = Lease::query()->create([
            'property_id' => $property->id,
            'unit_id' => $unit->id,
            'tenant_id' => $tenant->id,
            'start_date' => now()->subMonth(),
            'end_date' => now()->addYear(),
            'monthly_rent' => 500,
            'payment_frequency' => 'monthly',
            'status' => 'active',
        ]);

        $path = 'lease-documents/'.$lease->id.'/lease.pdf';
        Storage::disk('public')->put($path, 'pdf-content');

        LeaseDocument::query()->create([
            'user_id' => $landlord->id,
            'lease_id' => $lease->id,
            'title' => 'Lease PDF',
            'category' => LeaseDocument::CATEGORY_LEASE,
            'file_path' => $path,
            'original_filename' => 'lease.pdf',
            'is_visible_to_tenant' => true,
        ]);

        $tenantUser = User::factory()->create(['email' => 'view.tenant@test']);
        $tenantUser->assignRole('tenant');
        $tenant->update(['linked_user_id' => $tenantUser->id]);

        $this->actingAs($tenantUser)
            ->get(route('tenant.documents.index'))
            ->assertOk()
            ->assertSee('Lease PDF');
    }

    public function test_landlord_and_tenant_can_exchange_messages(): void
    {
        Mail::fake();

        $landlord = User::factory()->create(['email' => 'landlord.msg@test']);
        $landlord->assignRole('landlord');
        Subscription::factory()->for($landlord)->create();

        $this->actingAs($landlord);
        $property = Property::query()->create(['name' => 'Msg Prop', 'is_active' => true]);
        $unit = RentalUnit::query()->create([
            'property_id' => $property->id,
            'label' => '3',
            'monthly_rent' => 500,
            'is_active' => true,
        ]);
        $tenant = RentalTenant::query()->create([
            'full_name' => 'Msg Tenant',
            'email' => 'tenant.msg@test',
        ]);
        $lease = Lease::query()->create([
            'property_id' => $property->id,
            'unit_id' => $unit->id,
            'tenant_id' => $tenant->id,
            'start_date' => now()->subMonth(),
            'end_date' => now()->addYear(),
            'monthly_rent' => 500,
            'payment_frequency' => 'monthly',
            'status' => 'active',
        ]);

        $tenantUser = User::factory()->create(['email' => 'tenant.msg@test']);
        $tenantUser->assignRole('tenant');
        $tenant->update(['linked_user_id' => $tenantUser->id]);

        $this->post(route('rental.leases.message.start', $lease))
            ->assertRedirect();

        $thread = MessageThread::withoutLandlordScope()->first();
        $this->assertNotNull($thread);

        app(MessageThreadService::class)->sendMessage($thread, $landlord, 'Hello tenant');

        Mail::assertSent(NewRentalMessageMail::class);

        $this->actingAs($tenantUser)
            ->post(route('tenant.messages.store', $thread), ['body' => 'Hello landlord'])
            ->assertRedirect(route('tenant.messages.show', $thread));

        $this->assertDatabaseHas('messages', ['body' => 'Hello landlord']);
    }

    public function test_tenant_receives_email_when_matched_by_email_without_link(): void
    {
        Mail::fake();

        $landlord = User::factory()->create();
        $landlord->assignRole('landlord');
        Subscription::factory()->for($landlord)->create();

        $this->actingAs($landlord);
        $property = Property::query()->create(['name' => 'Email Prop', 'is_active' => true]);
        $unit = RentalUnit::query()->create([
            'property_id' => $property->id,
            'label' => '4',
            'monthly_rent' => 500,
            'is_active' => true,
        ]);
        $tenant = RentalTenant::query()->create([
            'full_name' => 'Email Tenant',
            'email' => 'email.tenant@test',
        ]);
        $lease = Lease::query()->create([
            'property_id' => $property->id,
            'unit_id' => $unit->id,
            'tenant_id' => $tenant->id,
            'start_date' => now()->subMonth(),
            'end_date' => now()->addYear(),
            'monthly_rent' => 500,
            'payment_frequency' => 'monthly',
            'status' => 'active',
        ]);

        $tenantUser = User::factory()->create(['email' => 'email.tenant@test']);
        $tenantUser->assignRole('tenant');

        $thread = app(MessageThreadService::class)->findOrCreateForLease($lease);
        app(MessageThreadService::class)->sendMessage($thread, $landlord, 'Hello via email match');

        Mail::assertSent(NewRentalMessageMail::class);
    }
}
