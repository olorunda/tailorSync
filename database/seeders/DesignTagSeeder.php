<?php

namespace Database\Seeders;

use App\Models\Design;
use App\Models\DesignTag;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DesignTagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create 100 design tags
        // We'll create a mix of fabric and style tags
        DesignTag::factory()->count(50)->fabric()->create();
        DesignTag::factory()->count(50)->style()->create();
    }
}
