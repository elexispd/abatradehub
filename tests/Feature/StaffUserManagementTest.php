<?php

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Mail\StaffWelcomeNotification;
use App\Models\User;
use App\Policies\UserPolicy;
use Database\Seeders\StaffPermissionSeeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->seed(StaffPermissionSeeder::class);
});

test('a user with no roles cannot access the admin panel', function () {
    $user = User::factory()->create(['is_active' => true]);

    $this->actingAs($user)->get('/' . \App\Support\Admin::path())->assertForbidden();
});

test('a disabled user cannot access the admin panel', function () {
    $user = User::factory()->admin()->create(['is_active' => false]);

    $this->actingAs($user)->get('/' . \App\Support\Admin::path())->assertForbidden();
});

test('a user without view_any_user cannot access staff user list', function () {
    $role = Role::firstOrCreate(['name' => 'sales', 'guard_name' => 'web']);
    $staff = User::factory()->create(['is_active' => true]);
    $staff->assignRole($role);

    $this->actingAs($staff)->get('/' . \App\Support\Admin::path('users'))->assertForbidden();
});

test('a user without create_user gets 403 on the create page', function () {
    $role = Role::firstOrCreate(['name' => 'viewer', 'guard_name' => 'web']);
    $role->givePermissionTo('view_any_user');

    $staff = User::factory()->create(['is_active' => true]);
    $staff->assignRole($role);

    $this->actingAs($staff)->get('/' . \App\Support\Admin::path('users/create'))->assertForbidden();
});

test('a user with create_user but not assign_roles cannot set roles', function () {
    $role = Role::firstOrCreate(['name' => 'hr_manager', 'guard_name' => 'web']);
    $role->givePermissionTo(['view_any_user', 'create_user']);

    $staff = User::factory()->create(['is_active' => true]);
    $staff->assignRole($role);

    $targetRole = Role::firstOrCreate(['name' => 'editor', 'guard_name' => 'web']);

    $this->actingAs($staff);

    // Livewire attempt to inject roles
    Livewire::test(CreateUser::class)
        ->assertFormFieldIsHidden('roles')
        ->set('data.name', 'New Employee')
        ->set('data.email', 'employee@abatradehub.test')
        ->set('data.phone', '+2348011223344')
        ->set('data.password', 'SecretPassword123!')
        ->set('data.roles', [$targetRole->id]) // Tampering
        ->call('create')
        ->assertHasNoFormErrors();

    $newUser = User::where('email', 'employee@abatradehub.test')->first();
    expect($newUser)->not->toBeNull();
    // Roles should NOT be assigned because the user lacks assign_roles permission
    expect($newUser->roles)->toBeEmpty();
});

test('a user cannot change their own roles or status from staff module', function () {
    $superAdmin = User::factory()->admin()->create();

    $this->actingAs($superAdmin);

    // Edit own record: roles and is_active should be disabled
    Livewire::test(EditUser::class, ['record' => $superAdmin->id])
        ->assertFormFieldIsDisabled('roles')
        ->assertFormFieldIsDisabled('is_active');
});

test('the last super admin cannot be disabled or deleted', function () {
    // Ensure only one super admin exists
    User::role('super_admin')->delete();
    $lastSuperAdmin = User::factory()->admin()->create(['is_active' => true]);

    expect(UserPolicy::isLastSuperAdmin($lastSuperAdmin))->toBeTrue();

    // Policy check directly
    $policy = new UserPolicy();
    expect($policy->delete($lastSuperAdmin, $lastSuperAdmin))->toBeFalse();
    expect($policy->disable($lastSuperAdmin, $lastSuperAdmin))->toBeFalse();

    // Model lifecycle guard check
    expect(function () use ($lastSuperAdmin) {
        $lastSuperAdmin->update(['is_active' => false]);
    })->toThrow(\Illuminate\Auth\Access\AuthorizationException::class);

    expect(function () use ($lastSuperAdmin) {
        $lastSuperAdmin->delete();
    })->toThrow(\Illuminate\Auth\Access\AuthorizationException::class);
});

test('only a super admin can assign the super admin role', function () {
    $managerRole = Role::firstOrCreate(['name' => 'manager', 'guard_name' => 'web']);
    $managerRole->givePermissionTo(['view_any_user', 'create_user', 'assign_roles']);

    $manager = User::factory()->create(['is_active' => true]);
    $manager->assignRole($managerRole);

    $this->actingAs($manager);

    $component = Livewire::test(CreateUser::class)
        ->assertFormFieldIsVisible('roles');

    $rolesComponent = $component->instance()->form->getComponent('roles');
    $options = $rolesComponent ? $rolesComponent->getOptions() : [];

    // The roles field should NOT list super_admin for a non-super admin
    expect(array_key_exists('super_admin', $options))->toBeFalse();
});

test('a non-super-admin cannot see super_admin users in the list', function () {
    $managerRole = Role::firstOrCreate(['name' => 'auditor', 'guard_name' => 'web']);
    $managerRole->givePermissionTo(['view_any_user']);

    $manager = User::factory()->create(['is_active' => true]);
    $manager->assignRole($managerRole);

    $superAdmin = User::factory()->admin()->create(['name' => 'Invisible SuperAdmin']);

    $this->actingAs($manager);

    Livewire::test(ListUsers::class)
        ->assertDontSee('Invisible SuperAdmin');
});

test('disabling a user invalidates their active database session and remember token', function () {
    $user = User::factory()->create([
        'is_active' => true,
        'remember_token' => 'original-token-123',
    ]);

    // Simulate session in database
    if (Illuminate\Support\Facades\Schema::hasTable('sessions')) {
        DB::table('sessions')->insert([
            'id' => 'test-session-id',
            'user_id' => $user->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit',
            'payload' => 'payload-data',
            'last_activity' => time(),
        ]);
    }

    $user->update(['is_active' => false]);

    expect($user->fresh()->remember_token)->not->toBe('original-token-123');

    if (Illuminate\Support\Facades\Schema::hasTable('sessions')) {
        expect(DB::table('sessions')->where('user_id', $user->id)->count())->toBe(0);
    }
});

test('welcome email is queued when new staff user is created', function () {
    Mail::fake();

    $superAdmin = User::factory()->admin()->create();
    $this->actingAs($superAdmin);

    Livewire::test(CreateUser::class)
        ->set('data.name', 'Kelechi Nwosu')
        ->set('data.email', 'kelechi@abatradehub.test')
        ->set('data.phone', '+2348098765432')
        ->set('data.password', 'ValidSecretPassword1!')
        ->call('create')
        ->assertHasNoFormErrors();

    Mail::assertQueued(StaffWelcomeNotification::class, function ($mail) {
        return $mail->hasTo('kelechi@abatradehub.test');
    });
});

test('php artisan staff:create-super-admin creates a super admin correctly', function () {
    $this->artisan('staff:create-super-admin', [
        '--name' => 'Emeka Okafor',
        '--email' => 'emeka@abatradehub.test',
        '--password' => 'SecurePass123!',
    ])->assertSuccessful();

    $user = User::where('email', 'emeka@abatradehub.test')->first();
    expect($user)->not->toBeNull()
        ->name->toBe('Emeka Okafor')
        ->is_active->toBeTrue()
        ->isSuperAdmin()->toBeTrue();
});
