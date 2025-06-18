<?php

namespace Database\Seeders;

use App\Models\Order;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a mix of orders with different statuses
        Order::factory()->count(40)->pending()->create();
        Order::factory()->count(30)->inProgress()->create();
        Order::factory()->count(30)->completed()->create();
    }
}
