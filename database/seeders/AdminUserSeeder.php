<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('ADMIN_EMAIL', 'admin@abatradehub.test');

        $user = User::firstOrNew(['email' => $email]);
        $user->name = env('ADMIN_NAME', 'AbaTradeHub Admin');
        $user->is_admin = true;
        $user->email_verified_at ??= now();

        if (! $user->exists) {
            $user->password = Hash::make(env('ADMIN_PASSWORD', 'password'));
        }

        $user->save();

        $superAdminRole = \Spatie\Permission\Models\Role::firstOrCreate([
            'name' => 'super_admin',
            'guard_name' => 'web',
        ]);

        if (! $user->hasRole($superAdminRole)) {
            $user->assignRole($superAdminRole);
        }
    }
}
