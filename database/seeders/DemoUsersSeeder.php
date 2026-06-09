<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoUsersSeeder extends Seeder
{
    /**
     * Demo accounts for each role (password for all: "password").
     */
    public function run(): void
    {
        $password = Hash::make('password');

        $landlord = User::query()->updateOrCreate(
            ['email' => 'landlord@demo.test'],
            [
                'name' => 'Demo Landlord',
                'password' => $password,
                'email_verified_at' => now(),
            ],
        );
        $landlord->syncRoles(['landlord', 'user']);

        $tenantUser = User::query()->updateOrCreate(
            ['email' => 'tenant@demo.test'],
            [
                'name' => 'Demo Tenant',
                'password' => $password,
                'email_verified_at' => now(),
            ],
        );
        $tenantUser->syncRoles(['tenant']);
    }
}
