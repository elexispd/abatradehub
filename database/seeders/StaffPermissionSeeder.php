<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class StaffPermissionSeeder extends Seeder
{
    public const STAFF_PERMISSIONS = [
        'view_any_user',
        'view_user',
        'create_user',
        'update_user',
        'delete_user',
        'restore_user',
        'disable_user',
        'assign_roles',
        'reset_user_password',
    ];

    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        foreach (self::STAFF_PERMISSIONS as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        // Ensure super_admin role exists
        Role::firstOrCreate([
            'name' => 'super_admin',
            'guard_name' => 'web',
        ]);
    }
}

