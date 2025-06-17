<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Payment>
 */
class PaymentFactory extends Factory
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
            'invoice_id' => Invoice::factory(),
            'amount' => $this->faker->randomFloat(2, 50, 2000),
            'payment_date' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'payment_method' => $this->faker->randomElement(['cash', 'bank_transfer', 'credit_card', 'mobile_money', 'other']),
            'reference_number' => $this->faker->bothify('PAY-####-????'),
            'notes' => $this->faker->paragraph(),
            'created_at' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'updated_at' => function (array $attributes) {
                return $this->faker->dateTimeBetween($attributes['created_at'], 'now');
            },
        ];
    }

    /**
     * Indicate that the payment is completed.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function completed()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'completed',
            ];
        });
    }

    /**
     * Indicate that the payment is pending.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function pending()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'pending',
            ];
        });
    }

    /**
     * Indicate that the payment has failed.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function failed()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'failed',
            ];
        });
    }

    /**
     * Indicate that the payment was made with cash.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function cash()
    {
        return $this->state(function (array $attributes) {
            return [
                'payment_method' => 'Cash',
            ];
        });
    }

    /**
     * Indicate that the payment was made with a credit card.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function creditCard()
    {
        return $this->state(function (array $attributes) {
            return [
                'payment_method' => 'Credit Card',
            ];
        });
    }
}
