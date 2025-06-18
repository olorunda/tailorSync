<?php

namespace Database\Seeders;

use App\Models\TeamMember;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TeamMemberSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a mix of team members with different roles
        TeamMember::factory()->count(30)->tailor()->create();
        TeamMember::factory()->count(20)->designer()->create();
        TeamMember::factory()->count(50)->create(); // Other random roles
    }
}
