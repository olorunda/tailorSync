<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Expense>
 */
class ExpenseFactory extends Factory
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
            'category' => $this->faker->randomElement(['Materials', 'Utilities', 'Rent', 'Salaries', 'Equipment', 'Marketing', 'Transportation', 'Maintenance', 'Other']),
            'vendor' => $this->faker->company(),
            'amount' => $this->faker->randomFloat(2, 10, 1000),
            'date' => $this->faker->date(),
            'description' => $this->faker->sentence(),
            'payment_method' => $this->faker->randomElement(['cash', 'bank_transfer', 'credit_card', 'mobile_money', 'other']),
            'created_at' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'updated_at' => function (array $attributes) {
                return $this->faker->dateTimeBetween($attributes['created_at'], 'now');
            },
        ];
    }
}
