<?php

namespace Tests\Feature;

use App\Http\Controllers\PublicOrderController;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicOrderControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_order_page_can_be_viewed_with_valid_hash()
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

        // Generate a hash for the order ID
        $hash = PublicOrderController::generateHash($order->id);

        // Visit the public order page with the hash
        $response = $this->get(route('orders.public', ['hash' => $hash]));

        // Assert that the page loads successfully
        $response->assertStatus(200);

        // Assert that the page contains the order and invoice information
        $response->assertSee('Order #' . $order->id);
        $response->assertSee('Invoice #' . $invoice->invoice_number);
    }

    public function test_public_order_page_returns_404_with_invalid_hash()
    {
        // Generate an invalid hash
        $hash = 'invalid-hash';

        // Visit the public order page with the invalid hash
        $response = $this->get(route('orders.public', ['hash' => $hash]));

        // Assert that the page returns a 404 error
        $response->assertStatus(404);
    }

    public function test_public_order_page_shows_order_without_invoice()
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

        // Generate a hash for the order ID
        $hash = PublicOrderController::generateHash($order->id);

        // Visit the public order page with the hash
        $response = $this->get(route('orders.public', ['hash' => $hash]));

        // Assert that the page loads successfully
        $response->assertStatus(200);

        // Assert that the page contains the order information but not invoice information
        $response->assertSee('Order #' . $order->id);
        $response->assertDontSee('Invoice #');
    }
}
