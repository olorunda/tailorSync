<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all roles
        $adminRole = Role::where('name', 'admin')->first();
        $managerRole = Role::where('name', 'manager')->first();
        $tailorRole = Role::where('name', 'tailor')->first();
        $staffRole = Role::where('name', 'staff')->first();
        $accountantRole = Role::where('name', 'accountant')->first();

        // Get all permissions
        $allPermissions = Permission::all();

        // Admin gets all permissions
        $adminRole->permissions()->sync($allPermissions->pluck('id')->toArray());

        // Manager permissions
        $managerPermissions = Permission::where(function ($query) {
            $query->where('category', '!=', 'settings')
                  ->orWhere(function ($q) {
                      $q->where('category', 'settings')
                        ->whereNotIn('name', ['manage_roles_permissions']);
                  });
        })->get();
        $managerRole->permissions()->sync($managerPermissions->pluck('id')->toArray());

        // Tailor permissions
        $tailorPermissions = Permission::where(function ($query) {
            $query->whereIn('category', ['designs', 'measurements', 'orders'])
                  ->orWhere(function ($q) {
                      $q->where('category', 'settings')
                        ->whereIn('name', ['view_profile', 'edit_profile', 'change_password', 'manage_appearance']);
                  });
        })->get();
        $tailorRole->permissions()->sync($tailorPermissions->pluck('id')->toArray());

        // Staff permissions
        $staffPermissions = Permission::where(function ($query) {
            $query->whereIn('category', ['clients', 'appointments', 'messages'])
                  ->orWhere(function ($q) {
                      $q->where('category', 'settings')
                        ->whereIn('name', ['view_profile', 'edit_profile', 'change_password', 'manage_appearance']);
                  });
        })->get();
        $staffRole->permissions()->sync($staffPermissions->pluck('id')->toArray());

        // Accountant permissions
        $accountantPermissions = Permission::where(function ($query) {
            $query->whereIn('category', ['finance'])
                  ->orWhere(function ($q) {
                      $q->where('category', 'settings')
                        ->whereIn('name', ['view_profile', 'edit_profile', 'change_password', 'manage_appearance']);
                  });
        })->get();
        $accountantRole->permissions()->sync($accountantPermissions->pluck('id')->toArray());
    }
}
