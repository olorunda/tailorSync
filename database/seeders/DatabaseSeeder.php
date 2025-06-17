<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed roles and permissions first
        $this->call([
            RoleSeeder::class,
            PermissionSeeder::class,
            RolePermissionSeeder::class,
        ]);

        // Create admin user
        $adminRole = \App\Models\Role::where('name', 'admin')->first();

        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'role_id' => $adminRole->id,
        ]);

        // Create additional test users with different roles
        $managerRole = \App\Models\Role::where('name', 'manager')->first();
        $tailorRole = \App\Models\Role::where('name', 'tailor')->first();
        $staffRole = \App\Models\Role::where('name', 'staff')->first();
        $accountantRole = \App\Models\Role::where('name', 'accountant')->first();

        User::factory()->create([
            'name' => 'Manager User',
            'email' => 'manager@example.com',
            'role_id' => $managerRole->id,
        ]);

        User::factory()->create([
            'name' => 'Tailor User',
            'email' => 'tailor@example.com',
            'role_id' => $tailorRole->id,
        ]);

        User::factory()->create([
            'name' => 'Staff User',
            'email' => 'staff@example.com',
            'role_id' => $staffRole->id,
        ]);

        User::factory()->create([
            'name' => 'Accountant User',
            'email' => 'accountant@example.com',
            'role_id' => $accountantRole->id,
        ]);
    }
}
