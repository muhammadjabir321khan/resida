<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class AdminModuleSeeder extends Seeder
{
    /**
     * Ensures at least one admin account exists for the admin UI (beyond test@example.com if present).
     */
    public function run(): void
    {
        $role = Role::query()->firstOrCreate(
            ['name' => 'admin', 'guard_name' => 'web'],
        );

        $admin = User::query()->firstOrCreate(
            ['email' => 'admin@demo.test'],
            [
                'name' => 'Demo Admin',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ],
        );

        if (! $admin->hasRole($role)) {
            $admin->assignRole($role);
        }
    }
}
