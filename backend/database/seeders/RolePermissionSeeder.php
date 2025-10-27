<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create Permissions for BOTH guards
        $permissions = [
            'view users',
            'edit users',
            'delete users',
            'ban users',
            'create service',
            'edit service',
            'delete service',
            'view all services',
            'create booking',
            'cancel booking',
            'view bookings',
            'manage bookings',
            'moderate content',
            'view reports',
            'manage reports',
        ];

        foreach ($permissions as $permission) {
            // Create for sanctum guard
            Permission::create(['name' => $permission, 'guard_name' => 'sanctum']);
        }

        // Create Roles for sanctum guard
        $admin = Role::create(['name' => 'admin', 'guard_name' => 'sanctum']);
        $admin->givePermissionTo(Permission::all());

        $moderator = Role::create(['name' => 'moderator', 'guard_name' => 'sanctum']);
        $moderator->givePermissionTo([
            'view users',
            'ban users',
            'view all services',
            'moderate content',
            'view reports',
            'manage reports',
            'view bookings',
        ]);

        $serviceProvider = Role::create(['name' => 'service provider', 'guard_name' => 'sanctum']);
        $serviceProvider->givePermissionTo([
            'create service',
            'edit service',
            'delete service',
            'view bookings',
            'manage bookings',
        ]);

        $serviceSeeker = Role::create(['name' => 'service seeker', 'guard_name' => 'sanctum']);
        $serviceSeeker->givePermissionTo([
            'create booking',
            'cancel booking',
            'view bookings',
        ]);
    }
}
