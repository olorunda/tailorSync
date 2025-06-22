<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define permissions by category
        $permissionsByCategory = [
            'settings' => [
                ['name' => 'view_profile', 'description' => 'View profile settings'],
                ['name' => 'edit_profile', 'description' => 'Edit profile settings'],
                ['name' => 'change_password', 'description' => 'Change password'],
                ['name' => 'manage_appearance', 'description' => 'Manage appearance settings'],
                ['name' => 'manage_roles_permissions', 'description' => 'Manage roles and permissions'],
                ['name' => 'manage_store', 'description' => 'Manage store settings and configuration'],
            ],
            'clients' => [
                ['name' => 'view_clients', 'description' => 'View clients'],
                ['name' => 'create_clients', 'description' => 'Create new clients'],
                ['name' => 'edit_clients', 'description' => 'Edit existing clients'],
                ['name' => 'delete_clients', 'description' => 'Delete clients'],
            ],
            'measurements' => [
                ['name' => 'view_measurements', 'description' => 'View client measurements'],
                ['name' => 'create_measurements', 'description' => 'Create new measurements'],
                ['name' => 'edit_measurements', 'description' => 'Edit existing measurements'],
                ['name' => 'delete_measurements', 'description' => 'Delete measurements'],
                ['name' => 'manage_measurements', 'description' => 'Manage custom measurement types'],
            ],
            'orders' => [
                ['name' => 'view_orders', 'description' => 'View orders'],
                ['name' => 'create_orders', 'description' => 'Create new orders'],
                ['name' => 'edit_orders', 'description' => 'Edit existing orders'],
                ['name' => 'delete_orders', 'description' => 'Delete orders'],
            ],
            'designs' => [
                ['name' => 'view_designs', 'description' => 'View designs'],
                ['name' => 'create_designs', 'description' => 'Create new designs'],
                ['name' => 'edit_designs', 'description' => 'Edit existing designs'],
                ['name' => 'delete_designs', 'description' => 'Delete designs'],
            ],
            'inventory' => [
                ['name' => 'view_inventory', 'description' => 'View inventory'],
                ['name' => 'create_inventory', 'description' => 'Add new inventory items'],
                ['name' => 'edit_inventory', 'description' => 'Edit existing inventory items'],
                ['name' => 'delete_inventory', 'description' => 'Delete inventory items'],
            ],
            'appointments' => [
                ['name' => 'view_appointments', 'description' => 'View appointments'],
                ['name' => 'create_appointments', 'description' => 'Create new appointments'],
                ['name' => 'edit_appointments', 'description' => 'Edit existing appointments'],
                ['name' => 'delete_appointments', 'description' => 'Delete appointments'],
            ],
            'messages' => [
                ['name' => 'view_messages', 'description' => 'View messages'],
                ['name' => 'send_messages', 'description' => 'Send new messages'],
                ['name' => 'delete_messages', 'description' => 'Delete messages'],
            ],
            'finance' => [
                ['name' => 'view_invoices', 'description' => 'View invoices'],
                ['name' => 'create_invoices', 'description' => 'Create new invoices'],
                ['name' => 'edit_invoices', 'description' => 'Edit existing invoices'],
                ['name' => 'delete_invoices', 'description' => 'Delete invoices'],
                ['name' => 'view_payments', 'description' => 'View payments'],
                ['name' => 'create_payments', 'description' => 'Record new payments'],
                ['name' => 'delete_payments', 'description' => 'Delete payments'],
                ['name' => 'view_expenses', 'description' => 'View expenses'],
                ['name' => 'create_expenses', 'description' => 'Record new expenses'],
                ['name' => 'edit_expenses', 'description' => 'Edit existing expenses'],
                ['name' => 'delete_expenses', 'description' => 'Delete expenses'],
            ],
            'team' => [
                ['name' => 'view_team', 'description' => 'View team members'],
                ['name' => 'create_team', 'description' => 'Add new team members'],
                ['name' => 'edit_team', 'description' => 'Edit existing team members'],
                ['name' => 'delete_team', 'description' => 'Delete team members'],
                ['name' => 'view_tasks', 'description' => 'View tasks'],
                ['name' => 'create_tasks', 'description' => 'Create new tasks'],
                ['name' => 'edit_tasks', 'description' => 'Edit existing tasks'],
                ['name' => 'delete_tasks', 'description' => 'Delete tasks'],
            ],
            'store' => [
                ['name' => 'view_store_products', 'description' => 'View store products'],
                ['name' => 'create_store_products', 'description' => 'Create new store products'],
                ['name' => 'edit_store_products', 'description' => 'Edit existing store products'],
                ['name' => 'delete_store_products', 'description' => 'Delete store products'],
                ['name' => 'view_store_orders', 'description' => 'View store orders'],
                ['name' => 'create_store_orders', 'description' => 'Create new store orders'],
                ['name' => 'edit_store_orders', 'description' => 'Edit existing store orders'],
                ['name' => 'delete_store_orders', 'description' => 'Delete store orders'],
                ['name' => 'manage_store_orders', 'description' => 'Manage store orders (legacy)'],
                ['name' => 'view_store_purchases', 'description' => 'View store purchases'],
                ['name' => 'create_store_purchases', 'description' => 'Create new store purchases'],
                ['name' => 'edit_store_purchases', 'description' => 'Edit existing store purchases'],
                ['name' => 'delete_store_purchases', 'description' => 'Delete store purchases'],
                ['name' => 'manage_store_purchases', 'description' => 'Manage store purchases (legacy)'],
            ],
        ];

        // Create all permissions
        foreach ($permissionsByCategory as $category => $permissions) {
            foreach ($permissions as $permission) {
                Permission::updateOrCreate(
                    ['name' => $permission['name']],
                    [
                        'description' => $permission['description'],
                        'category' => $category
                    ]
                );
            }
        }
    }
}
