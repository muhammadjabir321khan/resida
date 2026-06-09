<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserSetting;
use Illuminate\Database\Seeder;

class LandlordSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $landlord = User::query()->where('email', 'landlord@demo.test')->first();
        if ($landlord === null) {
            return;
        }

        $defaults = [
            'default_currency' => 'USD',
            'locale' => 'en',
            'timezone' => 'America/Chicago',
            'company_name' => 'Demo Landlord LLC',
            'date_format' => 'Y-m-d',
            'stripe_mode' => 'test',
            'mail_from_name' => 'Demo Landlord',
            'mail_from_address' => 'noreply@demo-landlord.test',
        ];

        foreach ($defaults as $key => $value) {
            UserSetting::query()->updateOrCreate(
                ['user_id' => $landlord->id, 'key' => $key],
                ['value' => $value],
            );
        }
    }
}
