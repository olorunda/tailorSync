<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\User;
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
            'user_id' => User::factory(),
            'name' => $this->faker->word() . ' Measurements',
            'measurements' => [
                'chest' => $this->faker->numberBetween(80, 120),
                'waist' => $this->faker->numberBetween(60, 100),
                'hip' => $this->faker->numberBetween(80, 120),
                'shoulder' => $this->faker->numberBetween(40, 60),
                'sleeve' => $this->faker->numberBetween(50, 70),
            ],
            'photos' => [],
            'notes' => $this->faker->paragraph(),
            'measurement_date' => $this->faker->date(),
        ];
    }
}
