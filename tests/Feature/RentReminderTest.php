<?php

namespace Tests\Feature;

use App\Mail\TenantRentReminderMail;
use App\Models\Lease;
use App\Models\Property;
use App\Models\RentPaymentInstallment;
use App\Models\RentalTenant;
use App\Models\RentalUnit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class RentReminderTest extends TestCase
{
    use RefreshDatabase;

    public function test_rent_reminder_command_sends_upcoming_email(): void
    {
        Mail::fake();

        $landlord = User::factory()->create();
        $this->actingAs($landlord);

        $property = Property::query()->create(['name' => 'Reminder Prop', 'is_active' => true]);
        $unit = RentalUnit::query()->create([
            'property_id' => $property->id,
            'label' => '1A',
            'monthly_rent' => 500,
            'is_active' => true,
        ]);
        $tenant = RentalTenant::query()->create([
            'full_name' => 'Reminder Tenant',
            'email' => 'reminder.tenant@example.com',
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

        RentPaymentInstallment::query()->create([
            'lease_id' => $lease->id,
            'due_date' => now()->addDays(3)->toDateString(),
            'amount_due' => 500,
            'status' => RentPaymentInstallment::STATUS_PENDING,
        ]);

        Artisan::call('rental:send-rent-reminders');

        Mail::assertSent(TenantRentReminderMail::class, function (TenantRentReminderMail $mail): bool {
            return $mail->reminderType === 'upcoming_3';
        });
    }

    public function test_rent_reminder_is_not_sent_twice_for_same_type(): void
    {
        Mail::fake();

        $landlord = User::factory()->create();
        $this->actingAs($landlord);

        $property = Property::query()->create(['name' => 'Once Prop', 'is_active' => true]);
        $unit = RentalUnit::query()->create([
            'property_id' => $property->id,
            'label' => '2A',
            'monthly_rent' => 500,
            'is_active' => true,
        ]);
        $tenant = RentalTenant::query()->create([
            'full_name' => 'Once Tenant',
            'email' => 'once.tenant@example.com',
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

        RentPaymentInstallment::query()->create([
            'lease_id' => $lease->id,
            'due_date' => now()->toDateString(),
            'amount_due' => 500,
            'status' => RentPaymentInstallment::STATUS_PENDING,
        ]);

        Artisan::call('rental:send-rent-reminders');
        Artisan::call('rental:send-rent-reminders');

        Mail::assertSent(TenantRentReminderMail::class, 1);
    }
}
