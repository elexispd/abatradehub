<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\StaffPermissionSeeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->seed(StaffPermissionSeeder::class);
});

test('a user with only view category permission can only access categories and gets 403 on others', function () {
    $role = Role::firstOrCreate(['name' => 'operations', 'guard_name' => 'web']);
    $role->syncPermissions(['View:Category']);

    $staff = User::factory()->create(['is_active' => true]);
    $staff->assignRole($role);

    $this->actingAs($staff);

    // Can access panel dashboard and categories list
    $this->get('/admin')->assertOk();
    $this->get('/admin/categories')->assertOk();

    // Forbidden from creating categories
    $this->get('/admin/categories/create')->assertForbidden();

    // Forbidden from all other resources
    $this->get('/admin/products')->assertForbidden();
    $this->get('/admin/products/create')->assertForbidden();
    $this->get('/admin/enquiries')->assertForbidden();
    $this->get('/admin/suppliers')->assertForbidden();
    $this->get('/admin/users')->assertForbidden();
    $this->get('/admin/shield/roles')->assertForbidden();
    $this->get('/admin/audit-logs')->assertForbidden();
});

test('only super admin can access shield roles resource', function () {
    $staffRole = Role::firstOrCreate(['name' => 'editor', 'guard_name' => 'web']);
    $staffRole->syncPermissions(['ViewAny:Role', 'View:Role']);

    $staff = User::factory()->create(['is_active' => true]);
    $staff->assignRole($staffRole);

    // Non super admin gets 403 even if ViewAny:Role was somehow assigned
    $this->actingAs($staff)->get('/admin/shield/roles')->assertForbidden();

    // Super admin can access roles
    $superAdmin = User::factory()->admin()->create();
    $this->actingAs($superAdmin)->get('/admin/shield/roles')->assertOk();
});

test('super_admin role cannot be edited or deleted via policy', function () {
    $superAdmin = User::factory()->admin()->create();
    $superAdminRole = Role::where('name', 'super_admin')->first();

    $policy = new \App\Policies\RolePolicy();

    expect($policy->update($superAdmin, $superAdminRole))->toBeFalse();
    expect($policy->delete($superAdmin, $superAdminRole))->toBeFalse();
    expect($policy->forceDelete($superAdmin, $superAdminRole))->toBeFalse();
});

test('starter roles have expected boundaries', function () {
    // 1. Content Editor
    $editor = User::factory()->create(['is_active' => true]);
    $editor->assignRole('content_editor');

    $this->actingAs($editor);
    $this->get('/admin/products')->assertOk();
    $this->get('/admin/categories')->assertOk();
    $this->get('/admin/suppliers')->assertOk();
    $this->get('/admin/enquiries')->assertForbidden();
    $this->get('/admin/users')->assertForbidden();
    $this->get('/admin/shield/roles')->assertForbidden();

    // 2. Inventory Clerk
    $clerk = User::factory()->create(['is_active' => true]);
    $clerk->assignRole('inventory_clerk');

    $this->actingAs($clerk);
    $this->get('/admin/products')->assertOk();
    $this->get('/admin/categories')->assertForbidden();
    $this->get('/admin/enquiries')->assertForbidden();
    $this->get('/admin/users')->assertForbidden();

    // 3. Sales
    $sales = User::factory()->create(['is_active' => true]);
    $sales->assignRole('sales');

    $this->actingAs($sales);
    $this->get('/admin/enquiries')->assertOk();
    $this->get('/admin/products')->assertOk();
    $this->get('/admin/categories')->assertOk();
    $this->get('/admin/suppliers')->assertForbidden();
    $this->get('/admin/users')->assertForbidden();
    $this->get('/admin/shield/roles')->assertForbidden();
});
