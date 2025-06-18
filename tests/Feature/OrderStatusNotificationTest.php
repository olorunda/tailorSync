<?php

namespace Tests\Feature;

use App\Http\Controllers\PublicOrderController;
use App\Models\Client;
use App\Models\Order;
use App\Models\User;
use App\Notifications\OrderStatusNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class OrderStatusNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_status_notification_contains_public_link()
    {
        // Create a user
        $user = User::factory()->create();

        // Create a client
        $client = Client::factory()->create([
            'user_id' => $user->id,
        ]);

        // Create an order
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'status' => 'pending',
        ]);

        // Update the order status
        $oldStatus = $order->status;
        $order->status = 'in_progress';
        $order->save();

        // Create the notification
        $notification = new OrderStatusNotification($order, $oldStatus);

        // Get the mail representation of the notification
        $mail = $notification->toMail($client);

        // Generate the expected hash
        $hash = PublicOrderController::generateHash($order->id);
        $expectedUrl = route('orders.public', ['hash' => $hash]);

        // Assert that the mail contains the public link
        $this->assertStringContainsString($expectedUrl, $mail->actionUrl);
    }

    public function test_order_status_notification_array_contains_public_link()
    {
        // Create a user
        $user = User::factory()->create();

        // Create a client
        $client = Client::factory()->create([
            'user_id' => $user->id,
        ]);

        // Create an order
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'status' => 'pending',
        ]);

        // Update the order status
        $oldStatus = $order->status;
        $order->status = 'in_progress';
        $order->save();

        // Create the notification
        $notification = new OrderStatusNotification($order, $oldStatus);

        // Get the array representation of the notification
        $array = $notification->toArray($client);

        // Generate the expected hash
        $hash = PublicOrderController::generateHash($order->id);
        $expectedUrl = route('orders.public', ['hash' => $hash]);

        // Assert that the array contains the public link
        $this->assertEquals($expectedUrl, $array['mail']['action']['url']);
    }

    public function test_order_status_notification_is_sent_to_client()
    {
        Notification::fake();

        // Create a user
        $user = User::factory()->create();

        // Create a client
        $client = Client::factory()->create([
            'user_id' => $user->id,
        ]);

        // Create an order
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'status' => 'pending',
        ]);

        // Update the order status
        $oldStatus = $order->status;
        $order->status = 'in_progress';
        $order->save();

        // Send the notification
        $client->notify(new OrderStatusNotification($order, $oldStatus));

        // Assert that the notification was sent to the client
        Notification::assertSentTo(
            $client,
            OrderStatusNotification::class,
            function ($notification, $channels) use ($order, $client) {
                $array = $notification->toArray($client);
                return $array['order_id'] === $order->id;
            }
        );
    }
}
