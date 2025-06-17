<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Message>
 */
class MessageFactory extends Factory
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
            'subject' => $this->faker->sentence(),
            'body' => $this->faker->paragraphs(3, true),
            'is_read' => $this->faker->boolean(70),
            'direction' => $this->faker->randomElement(['incoming', 'outgoing']),
            'message_type' => $this->faker->randomElement(['email', 'sms', 'system']),
            'created_at' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'updated_at' => function (array $attributes) {
                return $this->faker->dateTimeBetween($attributes['created_at'], 'now');
            },
        ];
    }

    /**
     * Indicate that the message is read.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function read()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_read' => true,
                'read_at' => $this->faker->dateTimeBetween($attributes['created_at'], 'now'),
            ];
        });
    }

    /**
     * Indicate that the message is unread.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function unread()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_read' => false,
                'read_at' => null,
            ];
        });
    }

    /**
     * Indicate that the message is incoming.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function incoming()
    {
        return $this->state(function (array $attributes) {
            return [
                'direction' => 'incoming',
            ];
        });
    }

    /**
     * Indicate that the message is outgoing.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function outgoing()
    {
        return $this->state(function (array $attributes) {
            return [
                'direction' => 'outgoing',
            ];
        });
    }

    /**
     * Indicate that the message is an email.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function email()
    {
        return $this->state(function (array $attributes) {
            return [
                'message_type' => 'email',
            ];
        });
    }

    /**
     * Indicate that the message is an SMS.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function sms()
    {
        return $this->state(function (array $attributes) {
            return [
                'message_type' => 'sms',
            ];
        });
    }
}
