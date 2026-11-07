<?php

use App\Filament\Resources\AuditLogs\AuditLogResource;
use App\Models\AuditLog;
use App\Models\Category;
use App\Models\Enquiry;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\StaffPermissionSeeder;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->seed(StaffPermissionSeeder::class);
});

test('only super admin can access audit logs resource', function () {
    $role = Role::firstOrCreate(['name' => 'manager', 'guard_name' => 'web']);
    $role->givePermissionTo('view_any_user');

    $manager = User::factory()->create(['is_active' => true]);
    $manager->assignRole($role);

    $this->actingAs($manager)->get('/admin/audit-logs')->assertForbidden();

    $superAdmin = User::factory()->admin()->create();
    $this->actingAs($superAdmin)->get('/admin/audit-logs')->assertOk();
});

test('staff login and logout events generate audit logs', function () {
    $staff = User::factory()->admin()->create(['name' => 'Chioma Nkechi']);

    event(new Login('web', $staff, false));

    $loginLog = AuditLog::where('action', 'login')->latest('id')->first();
    expect($loginLog)->not->toBeNull()
        ->user_id->toBe($staff->id)
        ->description->toContain('Chioma Nkechi')
        ->description->toContain('logged in');

    event(new Logout('web', $staff));

    $logoutLog = AuditLog::where('action', 'logout')->latest('id')->first();
    expect($logoutLog)->not->toBeNull()
        ->user_id->toBe($staff->id)
        ->description->toContain('Chioma Nkechi')
        ->description->toContain('logged out');
});

test('creating and updating records records model audit logs with diffs', function () {
    $admin = User::factory()->admin()->create(['name' => 'Nnamdi Sourcing']);
    $this->actingAs($admin);

    $category = Category::create([
        'name' => 'Footwear & Bags',
        'slug' => 'footwear-bags',
        'is_active' => true,
    ]);

    $createLog = AuditLog::where('auditable_type', Category::class)
        ->where('auditable_id', $category->id)
        ->where('action', 'created')
        ->first();

    expect($createLog)->not->toBeNull()
        ->user_id->toBe($admin->id)
        ->description->toContain('Created Category: Footwear & Bags');

    // Update category
    $category->update(['name' => 'Footwear & Leather Goods']);

    $updateLog = AuditLog::where('auditable_type', Category::class)
        ->where('auditable_id', $category->id)
        ->where('action', 'updated')
        ->first();

    expect($updateLog)->not->toBeNull()
        ->old_values->toHaveKey('name', 'Footwear & Bags')
        ->new_values->toHaveKey('name', 'Footwear & Leather Goods');
});

test('enquiry status changes record dedicated status_changed audit logs', function () {
    $admin = User::factory()->admin()->create(['name' => 'Sales Officer']);
    $this->actingAs($admin);

    $enquiry = Enquiry::factory()->create([
        'name' => 'Emeka Buyer',
        'status' => \App\Enums\EnquiryStatus::New,
    ]);

    $enquiry->update(['status' => \App\Enums\EnquiryStatus::Quoted]);

    $statusLog = AuditLog::where('auditable_type', Enquiry::class)
        ->where('auditable_id', $enquiry->id)
        ->where('action', 'status_changed')
        ->first();

    expect($statusLog)->not->toBeNull()
        ->description->toContain('quoted');
});

test('audit logs cannot be created or deleted via resource permissions', function () {
    $superAdmin = User::factory()->admin()->create();

    expect(AuditLogResource::canCreate())->toBeFalse();

    $policy = new \App\Policies\AuditLogPolicy();
    $log = AuditLog::record('test', 'Test log');

    expect($policy->create($superAdmin))->toBeFalse();
    expect($policy->update($superAdmin, $log))->toBeFalse();
    expect($policy->delete($superAdmin, $log))->toBeFalse();
});

test('logo and favicon files exist and are linked in views', function () {
    expect(file_exists(public_path('img/logo.png')))->toBeTrue();
    expect(file_exists(public_path('img/favicon.png')))->toBeTrue();

    $this->get('/')
        ->assertOk()
        ->assertSee('img/logo.png')
        ->assertSee('img/favicon.png');
});

