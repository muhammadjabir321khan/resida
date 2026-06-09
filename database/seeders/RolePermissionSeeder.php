<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'manage users',
            'manage roles',
            'manage permissions',
        ];

        foreach ($permissions as $name) {
            Permission::firstOrCreate(
                ['name' => $name, 'guard_name' => 'web'],
            );
        }

        $admin = Role::firstOrCreate(
            ['name' => 'admin', 'guard_name' => 'web'],
        );
        $admin->syncPermissions(Permission::query()->pluck('name')->all());

        Role::firstOrCreate(
            ['name' => 'user', 'guard_name' => 'web'],
        );

        Role::firstOrCreate(
            ['name' => 'landlord', 'guard_name' => 'web'],
        );

        Role::firstOrCreate(
            ['name' => 'tenant', 'guard_name' => 'web'],
        );

        $user = User::query()->where('email', 'test@example.com')->first();
        if ($user && ! $user->hasRole('admin')) {
            $user->assignRole('admin');
        }
    }
}
