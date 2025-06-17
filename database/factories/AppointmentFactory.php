<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Appointment>
 */
class AppointmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'client_id' => Client::factory(),
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(),
            'start_time' => $this->faker->dateTimeBetween('-10 days', '+30 days'),
            'end_time' => function (array $attributes) {
                return (clone $attributes['start_time'])->modify('+1 hour');
            },
            'type' => $this->faker->randomElement(['fitting', 'consultation', 'delivery', 'other']),
            'status' => $this->faker->randomElement(['scheduled', 'completed', 'cancelled']),
            'created_at' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'updated_at' => function (array $attributes) {
                return $this->faker->dateTimeBetween($attributes['created_at'], 'now');
            },
        ];
    }

    /**
     * Indicate that the appointment is scheduled.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function scheduled()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'scheduled',
                'date' => $this->faker->dateTimeBetween('now', '+30 days'),
            ];
        });
    }

    /**
     * Indicate that the appointment is completed.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function completed()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'completed',
                'date' => $this->faker->dateTimeBetween('-30 days', '-1 day'),
            ];
        });
    }

    /**
     * Indicate that the appointment is cancelled.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function cancelled()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'cancelled',
            ];
        });
    }
}
