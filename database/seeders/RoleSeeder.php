<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create default roles
        $roles = [
            [
                'name' => 'admin',
                'description' => 'Administrator with full access to all features',
            ],
            [
                'name' => 'manager',
                'description' => 'Manager with access to most features except system settings',
            ],
            [
                'name' => 'tailor',
                'description' => 'Tailor with access to designs, measurements, and orders',
            ],
            [
                'name' => 'staff',
                'description' => 'Staff with limited access to client management and appointments',
            ],
            [
                'name' => 'accountant',
                'description' => 'Accountant with access to financial features',
            ],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(
                ['name' => $role['name']],
                ['description' => $role['description']]
            );
        }
    }
}
