<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;
use function Laravel\Prompts\password;
use function Laravel\Prompts\text;

class CreateSuperAdminCommand extends Command
{
    protected $signature = 'staff:create-super-admin {--name=} {--email=} {--password=}';

    protected $description = 'Create a new super admin staff user';

    public function handle(): int
    {
        $name = $this->option('name') ?: text(
            label: 'Full Name',
            placeholder: 'e.g. Obinna Okafor',
            required: true,
        );

        $email = $this->option('email') ?: text(
            label: 'Email address',
            placeholder: 'admin@abatradehub.test',
            required: true,
            validate: fn (string $value) => match (true) {
                ! filter_var($value, FILTER_VALIDATE_EMAIL) => 'The email address is invalid.',
                User::where('email', $value)->exists() => 'A user with this email address already exists.',
                default => null,
            },
        );

        // In non-interactive or option mode, validate email
        $validator = Validator::make(['email' => $email], [
            'email' => 'required|email|unique:users,email',
        ]);

        if ($validator->fails()) {
            $this->error($validator->errors()->first());

            return self::FAILURE;
        }

        $plainPassword = $this->option('password') ?: password(
            label: 'Password',
            placeholder: 'Minimum 8 characters',
            required: true,
            validate: fn (string $value) => strlen($value) < 8 ? 'Password must be at least 8 characters.' : null,
        );

        $superAdminRole = Role::firstOrCreate([
            'name' => 'super_admin',
            'guard_name' => 'web',
        ]);

        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($plainPassword),
            'is_admin' => true,
            'is_active' => true,
            'force_password_change' => false,
            'email_verified_at' => now(),
        ]);

        $user->assignRole($superAdminRole);

        $this->info("Super Admin [{$user->name}] ({$user->email}) created successfully!");
        $this->info('Login URL: '.url('/admin/login'));

        return self::SUCCESS;
    }
}

