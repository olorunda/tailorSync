<?php

namespace Database\Seeders;

use App\Models\MeasurementType;
use App\Models\User;
use Illuminate\Database\Seeder;

class MeasurementTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Custom measurements to seed:
     * - Sleeve Length (already in standard as 'sleeve')
     * - Off Shoulder
     * - Shoulder to Nipple
     * - Shoulder To UnderBurst
     * - Nipple to Nipple
     * - Half Length
     * - Blouse Length
     * - Shoulder to Hip
     * - Hip Length
     * - Gown Length
     * - Crouch
     * - Neck (already in standard)
     * - Inseam (already in standard)
     * - OutSeam
     */
    public function run(): void
    {
        // Get all users
        $users = User::all();

        // Define custom measurement types
        // Note: Sleeve, Neck, and Inseam are already in standard measurements
        $measurementTypes = [
            [
                'name' => 'Off Shoulder',
                'description' => 'Measurement from one shoulder edge to the other',
                'unit' => 'cm',
                'is_active' => true,
            ],
            [
                'name' => 'Shoulder to Nipple',
                'description' => 'Measurement from shoulder to nipple',
                'unit' => 'cm',
                'is_active' => true,
            ],
            [
                'name' => 'Shoulder To UnderBurst',
                'description' => 'Measurement from shoulder to under bust',
                'unit' => 'cm',
                'is_active' => true,
            ],
            [
                'name' => 'Nipple to Nipple',
                'description' => 'Measurement between nipples',
                'unit' => 'cm',
                'is_active' => true,
            ],
            [
                'name' => 'Half Length',
                'description' => 'Half of the body length',
                'unit' => 'cm',
                'is_active' => true,
            ],
            [
                'name' => 'Blouse Length',
                'description' => 'Length of a blouse',
                'unit' => 'cm',
                'is_active' => true,
            ],
            [
                'name' => 'Shoulder to Hip',
                'description' => 'Measurement from shoulder to hip',
                'unit' => 'cm',
                'is_active' => true,
            ],
            [
                'name' => 'Hip Length',
                'description' => 'Length of the hip area',
                'unit' => 'cm',
                'is_active' => true,
            ],
            [
                'name' => 'Gown Length',
                'description' => 'Length of a gown',
                'unit' => 'cm',
                'is_active' => true,
            ],
            [
                'name' => 'Crouch',
                'description' => 'Measurement of the crouch area',
                'unit' => 'cm',
                'is_active' => true,
            ],
            [
                'name' => 'OutSeam',
                'description' => 'Measurement from waist to ankle along the outside of the leg',
                'unit' => 'cm',
                'is_active' => true,
            ],
        ];

        // Create measurement types for each user
        foreach ($users as $user) {
            foreach ($measurementTypes as $type) {
                MeasurementType::create([
                    'user_id' => $user->id,
                    'name' => $type['name'],
                    'description' => $type['description'],
                    'unit' => $type['unit'],
                    'is_active' => $type['is_active'],
                ]);
            }
        }
    }
}
