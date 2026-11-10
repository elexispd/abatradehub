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
        'change_enquiry_status',
        'assign_enquiry',
        'export_enquiries',
        'publish_product',
        'adjust_stock',
        'import_products_csv',
        'export_products',
        'manage_price_tiers',
        'print_invoice',
        'record_payment',
    ];

    public const RESOURCE_PERMISSIONS = [
        'Category' => ['ViewAny', 'View', 'Create', 'Update', 'Delete', 'DeleteAny', 'Restore', 'ForceDelete', 'Reorder'],
        'Product' => ['ViewAny', 'View', 'Create', 'Update', 'Delete', 'DeleteAny', 'Restore', 'ForceDelete', 'Replicate', 'Reorder'],
        'Enquiry' => ['ViewAny', 'View', 'Create', 'Update', 'Delete', 'DeleteAny', 'Restore', 'ForceDelete'],
        'Sale' => ['ViewAny', 'View', 'Create', 'Update', 'Delete', 'DeleteAny', 'Restore', 'ForceDelete'],
        'Supplier' => ['ViewAny', 'View', 'Create', 'Update', 'Delete', 'DeleteAny'],
        'AuditLog' => ['ViewAny', 'View'],
        'Role' => ['ViewAny', 'View', 'Create', 'Update', 'Delete', 'DeleteAny'],
    ];

    public const WIDGET_PERMISSIONS = [
        'View:StatsOverview',
        'View:LatestEnquiries',
        'View:LowStockProducts',
    ];

    public const PAGE_PERMISSIONS = [
        'View:SalesAnalytics',
        'View:SalesInsights',
    ];

    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Custom action & staff permissions
        foreach (self::STAFF_PERMISSIONS as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        // 2. Resource permissions (Pascal:Case matching Shield)
        foreach (self::RESOURCE_PERMISSIONS as $entity => $actions) {
            foreach ($actions as $action) {
                Permission::firstOrCreate([
                    'name' => "{$action}:{$entity}",
                    'guard_name' => 'web',
                ]);
            }
        }

        // 3. Widget permissions
        foreach (self::WIDGET_PERMISSIONS as $widgetPermission) {
            Permission::firstOrCreate([
                'name' => $widgetPermission,
                'guard_name' => 'web',
            ]);
        }

        // 4. Page permissions
        foreach (self::PAGE_PERMISSIONS as $pagePermission) {
            Permission::firstOrCreate([
                'name' => $pagePermission,
                'guard_name' => 'web',
            ]);
        }

        // 5. Ensure super_admin role exists
        Role::firstOrCreate([
            'name' => 'super_admin',
            'guard_name' => 'web',
        ]);

        // 5. Seed starter roles with sensible defaults (idempotent, editable in UI)
        $contentEditor = Role::firstOrCreate(['name' => 'content_editor', 'guard_name' => 'web']);
        $contentEditor->syncPermissions([
            'ViewAny:Product', 'View:Product', 'Create:Product', 'Update:Product',
            'ViewAny:Category', 'View:Category', 'Create:Category', 'Update:Category',
            'ViewAny:Supplier', 'View:Supplier',
            'View:StatsOverview',
        ]);

        $inventoryClerk = Role::firstOrCreate(['name' => 'inventory_clerk', 'guard_name' => 'web']);
        $inventoryClerk->syncPermissions([
            'ViewAny:Product', 'View:Product',
            'adjust_stock', 'import_products_csv',
            'View:LowStockProducts', 'View:StatsOverview',
        ]);

        $sales = Role::firstOrCreate(['name' => 'sales', 'guard_name' => 'web']);
        $sales->syncPermissions([
            'ViewAny:Product', 'View:Product',
            'ViewAny:Category', 'View:Category',
            'ViewAny:Enquiry', 'View:Enquiry', 'Create:Enquiry', 'Update:Enquiry',
            'ViewAny:Sale', 'View:Sale', 'Create:Sale', 'Update:Sale',
            'View:SalesAnalytics', 'View:SalesInsights',
            'print_invoice', 'record_payment',
            'change_enquiry_status', 'assign_enquiry', 'export_enquiries',
            'View:LatestEnquiries', 'View:StatsOverview',
        ]);
    }
}


