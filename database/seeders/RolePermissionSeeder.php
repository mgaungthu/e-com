<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)
            ->forgetCachedPermissions();

        $permissions = [
            'dashboard.view',

            'categories.view',
            'categories.create',
            'categories.update',
            'categories.delete',

            'products.view',
            'products.create',
            'products.update',
            'products.delete',

            'inventory.view',
            'inventory.adjust',

            'orders.view',
            'orders.update',
            'orders.cancel',
            'orders.refund',

            'customers.view',
            'customers.update',
            'customers.block',

            'staff.view',
            'staff.manage',

            'settings.manage',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        $superAdmin = Role::firstOrCreate([
            'name' => 'super_admin',
            'guard_name' => 'web',
        ]);

        $admin = Role::firstOrCreate([
            'name' => 'admin',
            'guard_name' => 'web',
        ]);

        $productManager = Role::firstOrCreate([
            'name' => 'product_manager',
            'guard_name' => 'web',
        ]);

        $orderManager = Role::firstOrCreate([
            'name' => 'order_manager',
            'guard_name' => 'web',
        ]);

        $customerSupport = Role::firstOrCreate([
            'name' => 'customer_support',
            'guard_name' => 'web',
        ]);

        Role::firstOrCreate([
            'name' => 'customer',
            'guard_name' => 'web',
        ]);

        $superAdmin->syncPermissions($permissions);

        $admin->syncPermissions($permissions);

        $productManager->syncPermissions([
            'dashboard.view',
            'categories.view',
            'categories.create',
            'categories.update',
            'categories.delete',
            'products.view',
            'products.create',
            'products.update',
            'products.delete',
            'inventory.view',
            'inventory.adjust',
        ]);

        $orderManager->syncPermissions([
            'dashboard.view',
            'orders.view',
            'orders.update',
            'orders.cancel',
            'orders.refund',
            'customers.view',
        ]);

        $customerSupport->syncPermissions([
            'dashboard.view',
            'orders.view',
            'customers.view',
            'customers.update',
            'customers.block',
        ]);
    }
}
