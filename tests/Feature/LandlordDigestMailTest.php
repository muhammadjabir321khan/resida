<?php

namespace Tests\Feature;

use App\Mail\LandlordRentalDigestMail;
use App\Models\Lease;
use App\Models\Property;
use App\Models\RentPaymentInstallment;
use App\Models\RentalTenant;
use App\Models\RentalUnit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class LandlordDigestMailTest extends TestCase
{
    use RefreshDatabase;

    public function test_digest_command_sends_mail_when_issues_exist(): void
    {
        Mail::fake();

        Role::query()->firstOrCreate(['name' => 'landlord', 'guard_name' => 'web']);
        $user = User::factory()->create(['email' => 'owner@digest.test', 'name' => 'Owner']);
        $user->assignRole('landlord');

        $this->actingAs($user);
        $property = Property::query()->create(['name' => 'Digest Prop', 'is_active' => true]);
        $unit = RentalUnit::query()->create([
            'property_id' => $property->id,
            'label' => '1',
            'monthly_rent' => 100,
            'is_active' => true,
        ]);
        $tenant = RentalTenant::query()->create(['full_name' => 'R', 'email' => 'r@r.test']);
        $lease = Lease::query()->create([
            'property_id' => $property->id,
            'unit_id' => $unit->id,
            'tenant_id' => $tenant->id,
            'start_date' => now()->subMonth(),
            'end_date' => now()->addDays(5),
            'monthly_rent' => 400,
            'payment_frequency' => 'monthly',
            'status' => 'active',
        ]);

        RentPaymentInstallment::query()->create([
            'lease_id' => $lease->id,
            'due_date' => now()->subDay(),
            'amount_due' => 400,
            'status' => RentPaymentInstallment::STATUS_PENDING,
        ]);

        Artisan::call('rental:send-landlord-digests');

        Mail::assertSent(LandlordRentalDigestMail::class, function (LandlordRentalDigestMail $mail): bool {
            return $mail->overdueCount >= 1;
        });
    }
}
