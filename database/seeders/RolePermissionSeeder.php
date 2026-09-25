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

            /*
            |--------------------------------------------------------------------------
            | Categories
            |--------------------------------------------------------------------------
            */

            'categories.view',
            'categories.create',
            'categories.update',
            'categories.delete',

            /*
            |--------------------------------------------------------------------------
            | Products
            |--------------------------------------------------------------------------
            */

            'products.view',
            'products.create',
            'products.update',
            'products.delete',

            /*
            |--------------------------------------------------------------------------
            | Home Banners
            |--------------------------------------------------------------------------
            */

            'home_banners.view',
            'home_banners.create',
            'home_banners.update',
            'home_banners.delete',

            /*
            |--------------------------------------------------------------------------
            | Feeds
            |--------------------------------------------------------------------------
            */

            'feeds.view',
            'feeds.create',
            'feeds.update',
            'feeds.delete',

            /*
            |--------------------------------------------------------------------------
            | Inventory
            |--------------------------------------------------------------------------
            */

            'inventory.view',
            'inventory.adjust',

            /*
            |--------------------------------------------------------------------------
            | Orders
            |--------------------------------------------------------------------------
            */

            'orders.view',
            'orders.update',
            'orders.cancel',
            'orders.refund',

            /*
            |--------------------------------------------------------------------------
            | Payment Methods
            |--------------------------------------------------------------------------
            */

            'payment_methods.view',
            'payment_methods.create',
            'payment_methods.update',
            'payment_methods.delete',

            /*
            |--------------------------------------------------------------------------
            | Locations
            |--------------------------------------------------------------------------
            */

            'locations.view',
            'locations.create',
            'locations.update',

            /*
            |--------------------------------------------------------------------------
            | Customers
            |--------------------------------------------------------------------------
            */

            'customers.view',
            'customers.update',
            'customers.block',

            /*
            |--------------------------------------------------------------------------
            | Chat
            |--------------------------------------------------------------------------
            */

            'chat.view',
            'chat.reply',

            /*
            |--------------------------------------------------------------------------
            | Reports
            |--------------------------------------------------------------------------
            */

            'reports.view',
            'reports.export',

            /*
            |--------------------------------------------------------------------------
            | Staff
            |--------------------------------------------------------------------------
            */

            'staff.view',
            'staff.manage',

            /*
            |--------------------------------------------------------------------------
            | Notifications
            |--------------------------------------------------------------------------
            */

            'notifications.view',
            'notifications.send',

            /*
            |--------------------------------------------------------------------------
            | Settings
            |--------------------------------------------------------------------------
            */

            'settings.manage',
        ];

        /*
        |--------------------------------------------------------------------------
        | Create Permissions
        |--------------------------------------------------------------------------
        */

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Roles
        |--------------------------------------------------------------------------
        */

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

        /*
        |--------------------------------------------------------------------------
        | Super Admin
        |--------------------------------------------------------------------------
        |
        | Super Admin receives every registered admin permission.
        |--------------------------------------------------------------------------
        */

        $superAdmin->syncPermissions($permissions);

        /*
        |--------------------------------------------------------------------------
        | Admin
        |--------------------------------------------------------------------------
        |
        | Admin receives every registered admin permission, including
        | Payment Method management.
        |--------------------------------------------------------------------------
        */

        $admin->syncPermissions($permissions);

        /*
        |--------------------------------------------------------------------------
        | Product Manager
        |--------------------------------------------------------------------------
        */

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

            /*
             * Product managers can manage Home carousel merchandising.
             */
            'home_banners.view',
            'home_banners.create',
            'home_banners.update',
            'home_banners.delete',

            'feeds.view',
            'feeds.create',
            'feeds.update',
            'feeds.delete',

            'inventory.view',
            'inventory.adjust',

            'reports.view',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Order Manager
        |--------------------------------------------------------------------------
        |
        | Payment Method configuration is intentionally not included here.
        |
        | A dedicated payment review permission will be added later when the
        | Payments Management / Payment Verification module is implemented.
        |--------------------------------------------------------------------------
        */

        $orderManager->syncPermissions([
            'dashboard.view',

            'orders.view',
            'orders.update',
            'orders.cancel',
            'orders.refund',

            'customers.view',

            'reports.view',
            'reports.export',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Customer Support
        |--------------------------------------------------------------------------
        */

        $customerSupport->syncPermissions([
            'dashboard.view',

            'orders.view',

            'customers.view',
            'customers.update',
            'customers.block',

            'chat.view',
            'chat.reply',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Clear Permission Cache
        |--------------------------------------------------------------------------
        */

        app(PermissionRegistrar::class)
            ->forgetCachedPermissions();
    }
}
