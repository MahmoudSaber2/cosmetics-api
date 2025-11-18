<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Define all permissions
        $permissions = [
            // User Management
            'all_users',
            'create_user',
            'edit_user',
            'update_user',
            'update_user_status',
            'delete_user',

            // Client Management
            'all_clients',
            'create_client',
            'edit_client',
            'update_client',
            'delete_client',

            // Brand Management
            'all_brands',
            'create_brand',
            'edit_brand',
            'update_brand',
            'delete_brand',
            // Product Management
            'all_products',
            'create_product',
            'edit_product',
            'update_product',
            'delete_product',
            'bulk_update_product',
            // Order Management
            'all_orders',
            'create_order',
            'update_order',
            'delete_order',
            'approve_order',
            'reject_order',
            'complete_order',

            // Inventory Management
            'view_inventory',
            'update_inventory',
            'update_stock',
            'bulk_update_stock',

            // File Management
            'upload_files',
            'delete_files',
        ];

        // Create all permissions
        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create superAdmin role and assign all permissions
        $superAdmin = Role::create(['name' => 'superAdmin']);
        $superAdmin->givePermissionTo(Permission::all());

        // Create supervisor role and assign limited permissions (clients and orders)
        $supervisor = Role::create(['name' => 'supervisor']);
        $supervisor->givePermissionTo([
            'all_clients',
            'create_client',
            'update_client',
            'all_orders',
            'create_order',
            'update_order',
            'approve_order',
            'reject_order',
            'complete_order',
        ]);
    }
}
