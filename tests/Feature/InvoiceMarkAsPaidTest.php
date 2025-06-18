<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use App\Models\Client;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class InvoiceMarkAsPaidTest extends TestCase
{
    use RefreshDatabase;

    public function test_marking_invoice_as_paid_creates_payment_record(): void
    {
        // Create a user
        $user = User::factory()->create();

        // Create a client
        $client = Client::factory()->create([
            'user_id' => $user->id
        ]);

        // Create an invoice
        $invoice = Invoice::factory()->create([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'status' => 'pending',
            'total' => 500.00,
            'invoice_number' => 'INV-123'
        ]);

        $this->actingAs($user);

        // Test the markAsPaid method in the show component
        $response = Volt::test('invoices.show', ['invoice' => $invoice])
            ->call('markAsPaid');

        // Assert that the invoice status was updated to 'paid'
        $this->assertEquals('paid', $invoice->fresh()->status);

        // Assert that a payment record was created with the correct details
        $this->assertDatabaseHas('payments', [
            'user_id' => $user->id,
            'client_id' => $client->id,
            'invoice_id' => $invoice->id,
            'amount' => 500.00,
            'payment_method' => 'manual',
            'status' => 'completed',
            'description' => 'Payment for Invoice #INV-123'
        ]);
    }
}
