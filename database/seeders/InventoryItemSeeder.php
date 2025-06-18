<?php

namespace Database\Seeders;

use App\Models\InventoryItem;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class InventoryItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create 80 regular inventory items
        InventoryItem::factory()->count(80)->create();

        // Create 20 low stock inventory items
        InventoryItem::factory()->count(20)->lowStock()->create();
    }
}
