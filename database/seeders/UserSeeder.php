<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the admin role
        $adminRole = \App\Models\Role::where('name', 'admin')->first();

        if (!$adminRole) {
            // If admin role doesn't exist, create it first
            $this->call(RoleSeeder::class);
            $adminRole = \App\Models\Role::where('name', 'admin')->first();
        }

        // Check if admin user already exists
        $adminExists = User::where('email', 'admin@example.com')->exists();

        if (!$adminExists) {
            // Create admin user
            User::create([
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'password' => Hash::make('password'), // Default password, should be changed after first login
                'role_id' => $adminRole->id,
                'currency' => 'USD',
            ]);

            $this->command->info('Admin user created successfully.');
        } else {
            $this->command->info('Admin user already exists.');
        }
    }
}
