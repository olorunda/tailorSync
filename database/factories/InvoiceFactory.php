<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Invoice>
 */
class InvoiceFactory extends Factory
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
            'order_id' => Order::factory(),
            'invoice_number' => 'INV-' . $this->faker->unique()->numberBetween(1000, 9999),
            'total_amount' => $this->faker->randomFloat(2, 100, 5000),
            'tax_amount' => $this->faker->randomFloat(2, 10, 500),
            'subtotal' => $this->faker->randomFloat(2, 10, 500),
            'discount_amount' => $this->faker->randomFloat(2, 0, 200),
            'due_date' => $this->faker->dateTimeBetween('now', '+30 days'),
            'status' => $this->faker->randomElement(['draft', 'sent', 'paid', 'overdue']),
            'notes' => $this->faker->paragraph(),
            'created_at' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'issue_date' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'updated_at' => function (array $attributes) {
                return $this->faker->dateTimeBetween($attributes['created_at'], 'now');
            },
        ];
    }

    /**
     * Indicate that the invoice is paid.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function paid()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'paid',
                'paid_date' => $this->faker->dateTimeBetween('-30 days', 'now'),
            ];
        });
    }

    /**
     * Indicate that the invoice is overdue.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function overdue()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'overdue',
                'due_date' => $this->faker->dateTimeBetween('-30 days', '-1 day'),
            ];
        });
    }

    /**
     * Indicate that the invoice is a draft.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function draft()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'draft',
            ];
        });
    }

    /**
     * Indicate that the invoice has been sent.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function sent()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'sent',
                'sent_date' => $this->faker->dateTimeBetween('-30 days', 'now'),
            ];
        });
    }
}
