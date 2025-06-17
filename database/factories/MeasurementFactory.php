<?php

namespace Database\Factories;

use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Measurement>
 */
class MeasurementFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'client_id' => Client::factory(),
            'chest' => $this->faker->randomFloat(2, 30, 50),
            'waist' => $this->faker->randomFloat(2, 25, 45),
            'hips' => $this->faker->randomFloat(2, 30, 50),
            'shoulder' => $this->faker->randomFloat(2, 15, 25),
            'sleeve_length' => $this->faker->randomFloat(2, 20, 30),
            'inseam' => $this->faker->randomFloat(2, 25, 35),
            'notes' => $this->faker->paragraph(),
            'measurement_date' => $this->faker->date(),
        ];
    }
}
