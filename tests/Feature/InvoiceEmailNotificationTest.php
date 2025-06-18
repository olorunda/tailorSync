<?php

namespace Tests\Feature;

use App\Http\Controllers\PublicOrderController;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\User;
use App\Notifications\InvoiceEmailNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class InvoiceEmailNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_invoice_email_notification_contains_public_link()
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
        ]);

        // Create an invoice
        $invoice = Invoice::factory()->create([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'order_id' => $order->id,
        ]);

        // Create the notification
        $notification = new InvoiceEmailNotification($invoice);

        // Get the mail representation of the notification
        $mail = $notification->toMail($client);

        // Generate the expected hash
        $hash = PublicOrderController::generateHash($order->id);
        $expectedUrl = route('orders.public', ['hash' => $hash]);

        // Assert that the mail contains the public link
        $this->assertStringContainsString($expectedUrl, $mail->actionUrl);
    }

    public function test_invoice_email_notification_array_contains_public_link()
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
        ]);

        // Create an invoice
        $invoice = Invoice::factory()->create([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'order_id' => $order->id,
        ]);

        // Create the notification
        $notification = new InvoiceEmailNotification($invoice);

        // Get the array representation of the notification
        $array = $notification->toArray($client);

        // Generate the expected hash
        $hash = PublicOrderController::generateHash($order->id);
        $expectedUrl = route('orders.public', ['hash' => $hash]);

        // Assert that the array contains the public link
        $this->assertEquals($expectedUrl, $array['mail']['action']['url']);
    }

    public function test_invoice_email_notification_is_sent_to_client()
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
        ]);

        // Create an invoice
        $invoice = Invoice::factory()->create([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'order_id' => $order->id,
        ]);

        // Send the notification
        $client->notify(new InvoiceEmailNotification($invoice));

        // Assert that the notification was sent to the client
        Notification::assertSentTo(
            $client,
            InvoiceEmailNotification::class,
            function ($notification, $channels) use ($invoice) {
                return $notification->invoice->id === $invoice->id;
            }
        );
    }
}
