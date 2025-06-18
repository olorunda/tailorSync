<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Database\Seeders\AppointmentSeeder;
use Database\Seeders\ClientSeeder;
use Database\Seeders\DesignSeeder;
use Database\Seeders\DesignTagSeeder;
use Database\Seeders\ExpenseSeeder;
use Database\Seeders\InventoryItemSeeder;
use Database\Seeders\InvoiceSeeder;
use Database\Seeders\MeasurementSeeder;
use Database\Seeders\MessageSeeder;
use Database\Seeders\OrderSeeder;
use Database\Seeders\PaymentSeeder;
use Database\Seeders\TaskSeeder;
use Database\Seeders\TeamMemberSeeder;

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

        // Seed admin user
//        $this->call(UserSeeder::class);

        // Create additional test users with different roles
        $adminRole = \App\Models\Role::where('name', 'admin')->first();
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
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'role'=>'admin',
           // 'role_id' => $accountantRole->id,
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

        // Seed all other models with up to 100 records each
//        $this->call([
//            AppointmentSeeder::class,
//            ClientSeeder::class,
//            DesignSeeder::class,
//            DesignTagSeeder::class,
//            ExpenseSeeder::class,
//            InventoryItemSeeder::class,
//            InvoiceSeeder::class,
//            MeasurementSeeder::class,
//            MessageSeeder::class,
//            OrderSeeder::class,
//            PaymentSeeder::class,
//            TaskSeeder::class,
//            TeamMemberSeeder::class,
//        ]);
    }
}
