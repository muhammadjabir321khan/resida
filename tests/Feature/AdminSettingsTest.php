<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_view_settings(): void
    {
        $this->get(route('settings.edit'))->assertRedirect();
    }

    public function test_non_admin_cannot_view_settings(): void
    {
        Role::query()->firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('settings.edit'))
            ->assertForbidden();
    }

    public function test_admin_can_save_and_read_settings(): void
    {
        Role::query()->firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $user = User::factory()->create();
        $user->assignRole('admin');

        $this->actingAs($user)->get(route('settings.edit'))->assertOk();

        $payload = [
            'default_currency' => 'EUR',
            'locale' => 'en',
            'timezone' => 'UTC',
            'company_name' => 'Acme Rentals',
            'date_format' => 'Y-m-d',
            'stripe_publishable_key' => 'pk_test_123',
            'stripe_secret_key' => 'sk_test_secret_value',
            'stripe_webhook_secret' => 'whsec_test',
            'stripe_mode' => 'test',
            'mail_from_address' => 'noreply@example.com',
            'mail_from_name' => 'Acme',
            'smtp_host' => 'smtp.example.com',
            'smtp_port' => 587,
            'smtp_username' => 'user',
            'smtp_password' => 'smtp-secret',
            'smtp_encryption' => 'tls',
            'tax_id' => 'TAX-1',
            'business_phone' => '+1 555',
        ];

        $this->actingAs($user)->put(route('settings.update'), $payload)->assertRedirect(route('settings.edit'));

        $this->assertSame('EUR', UserSetting::getValue('default_currency'));
        $this->assertSame('sk_test_secret_value', UserSetting::getValue('stripe_secret_key'));
        $this->assertSame('smtp-secret', UserSetting::getValue('smtp_password'));

        $row = UserSetting::withoutLandlordScope()->where('user_id', $user->id)->where('key', 'stripe_secret_key')->first();
        $this->assertNotNull($row);
        $this->assertStringStartsNotWith('sk_test', $row->value);
    }
}
