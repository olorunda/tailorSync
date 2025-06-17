<?php

namespace Tests\Feature;

use App\Models\Payment;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_payments_page_is_displayed(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('payments.index'));
        $response->assertStatus(200);
    }

    public function test_payments_can_be_created(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = Volt::test('payments.create')
            ->set('description', 'Test Payment')
            ->set('amount', 250.75)
            ->set('date', now()->format('Y-m-d'))
            ->set('payment_method', 'bank_transfer')
            ->set('reference_number', 'PAY123')
            ->set('status', 'completed')
            ->set('notes', 'Test payment notes')
            ->call('save');

        $response->assertHasNoErrors();

        $this->assertDatabaseHas('payments', [
            'user_id' => $user->id,
            'description' => 'Test Payment',
            'amount' => 250.75,
            'payment_method' => 'bank_transfer',
            'reference_number' => 'PAY123',
            'status' => 'completed',
            'notes' => 'Test payment notes',
        ]);
    }

    public function test_payment_validation_works(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = Volt::test('payments.create')
            ->set('description', '')
            ->set('amount', -10)
            ->set('date', '')
            ->set('payment_method', '')
            ->set('status', 'invalid')
            ->call('save');

        $response->assertHasErrors(['description', 'amount', 'date', 'payment_method', 'status']);
    }

    public function test_payment_can_be_linked_to_client(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user);

        $response = Volt::test('payments.create')
            ->set('description', 'Client Payment')
            ->set('amount', 300)
            ->set('date', now()->format('Y-m-d'))
            ->set('payment_method', 'cash')
            ->set('client_id', $client->id)
            ->set('status', 'completed')
            ->call('save');

        $response->assertHasNoErrors();

        $this->assertDatabaseHas('payments', [
            'user_id' => $user->id,
            'description' => 'Client Payment',
            'amount' => 300,
            'client_id' => $client->id,
        ]);
    }

    public function test_payment_can_be_linked_to_invoice(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['user_id' => $user->id]);
        $invoice = Invoice::factory()->create([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'total_amount' => 500
        ]);

        $this->actingAs($user);

        $response = Volt::test('payments.create')
            ->set('description', 'Invoice Payment')
            ->set('amount', 500)
            ->set('date', now()->format('Y-m-d'))
            ->set('payment_method', 'credit_card')
            ->set('client_id', $client->id)
            ->set('invoice_id', $invoice->id)
            ->set('status', 'completed')
            ->call('save');

        $response->assertHasNoErrors();

        $this->assertDatabaseHas('payments', [
            'user_id' => $user->id,
            'description' => 'Invoice Payment',
            'amount' => 500,
            'client_id' => $client->id,
            'invoice_id' => $invoice->id,
        ]);
    }

    public function test_payment_methods_are_valid(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $validPaymentMethods = [
            'cash',
            'bank_transfer',
            'credit_card',
            'mobile_money',
            'other'
        ];

        foreach ($validPaymentMethods as $method) {
            $response = Volt::test('payments.create')
                ->set('description', 'Test Payment')
                ->set('amount', 100)
                ->set('date', now()->format('Y-m-d'))
                ->set('payment_method', $method)
                ->set('status', 'completed')
                ->call('save');

            $response->assertHasNoErrors('payment_method');
        }
    }

    public function test_payment_statuses_are_valid(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $validStatuses = [
            'pending',
            'completed',
            'failed'
        ];

        foreach ($validStatuses as $status) {
            $response = Volt::test('payments.create')
                ->set('description', 'Test Payment')
                ->set('amount', 100)
                ->set('date', now()->format('Y-m-d'))
                ->set('payment_method', 'cash')
                ->set('status', $status)
                ->call('save');

            $response->assertHasNoErrors('status');
        }

        // Test invalid status
        $response = Volt::test('payments.create')
            ->set('description', 'Test Payment')
            ->set('amount', 100)
            ->set('date', now()->format('Y-m-d'))
            ->set('payment_method', 'cash')
            ->set('status', 'invalid-status')
            ->call('save');

        $response->assertHasErrors('status');
    }
}
