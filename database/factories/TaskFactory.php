<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Task>
 */
class TaskFactory extends Factory
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
            'title' => $this->faker->sentence(),
            'description' => $this->faker->paragraph(),
            'type' => $this->faker->randomElement(['task', 'event']),
            'due_date' => $this->faker->optional(0.8)->dateTimeBetween('now', '+30 days'),
            'priority' => $this->faker->randomElement(['low', 'medium', 'high']),
            'status' => $this->faker->randomElement(['pending', 'in_progress', 'completed']),
            'assigned_to' => $this->faker->optional(0.7)->randomElement([User::factory()]),
            'order_id' => $this->faker->optional(0.5)->randomElement([Order::factory()]),
            'notes' => $this->faker->optional(0.6)->paragraph(),
            'created_at' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'updated_at' => function (array $attributes) {
                return $this->faker->dateTimeBetween($attributes['created_at'], 'now');
            },
        ];
    }

    /**
     * Indicate that the task is pending.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function pending()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'pending',
                'completed_date' => null,
            ];
        });
    }

    /**
     * Indicate that the task is in progress.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function inProgress()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'in_progress',
                'completed_date' => null,
            ];
        });
    }

    /**
     * Indicate that the task is completed.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function completed()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'completed',
                'completed_date' => $this->faker->dateTimeBetween($attributes['created_at'], 'now'),
            ];
        });
    }

    /**
     * Indicate that the task has high priority.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function highPriority()
    {
        return $this->state(function (array $attributes) {
            return [
                'priority' => 'high',
            ];
        });
    }

    /**
     * Indicate that the task has medium priority.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function mediumPriority()
    {
        return $this->state(function (array $attributes) {
            return [
                'priority' => 'medium',
            ];
        });
    }

    /**
     * Indicate that the task has low priority.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function lowPriority()
    {
        return $this->state(function (array $attributes) {
            return [
                'priority' => 'low',
            ];
        });
    }

    /**
     * Indicate that the task is overdue.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function overdue()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'pending',
                'due_date' => $this->faker->dateTimeBetween('-30 days', '-1 day'),
            ];
        });
    }
}
