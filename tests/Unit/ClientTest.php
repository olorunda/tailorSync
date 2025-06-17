<?php

namespace Tests\Unit;

use App\Models\Client;
use App\Models\User;
use App\Models\Measurement;
use App\Models\Order;
use App\Models\Appointment;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Message;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientTest extends TestCase
{
    use RefreshDatabase;

    public function test_client_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $client->user);
        $this->assertEquals($user->id, $client->user->id);
    }

    public function test_client_has_many_measurements(): void
    {
        $client = Client::factory()->create();
        $measurement1 = Measurement::factory()->create(['client_id' => $client->id]);
        $measurement2 = Measurement::factory()->create(['client_id' => $client->id]);

        $this->assertInstanceOf(Measurement::class, $client->measurements->first());
        $this->assertCount(2, $client->measurements);
    }

    public function test_client_has_latest_measurement(): void
    {
        $client = Client::factory()->create();
        $oldMeasurement = Measurement::factory()->create([
            'client_id' => $client->id,
            'created_at' => now()->subDays(2)
        ]);
        $newMeasurement = Measurement::factory()->create([
            'client_id' => $client->id,
            'created_at' => now()
        ]);

        $this->assertInstanceOf(Measurement::class, $client->latestMeasurement);
        $this->assertEquals($newMeasurement->id, $client->latestMeasurement->id);
    }

    public function test_client_has_many_orders(): void
    {
        $client = Client::factory()->create();
        $order1 = Order::factory()->create(['client_id' => $client->id]);
        $order2 = Order::factory()->create(['client_id' => $client->id]);

        $this->assertInstanceOf(Order::class, $client->orders->first());
        $this->assertCount(2, $client->orders);
    }

    public function test_client_has_many_appointments(): void
    {
        $client = Client::factory()->create();
        $appointment1 = Appointment::factory()->create(['client_id' => $client->id]);
        $appointment2 = Appointment::factory()->create(['client_id' => $client->id]);

        $this->assertInstanceOf(Appointment::class, $client->appointments->first());
        $this->assertCount(2, $client->appointments);
    }

    public function test_client_has_many_invoices(): void
    {
        $client = Client::factory()->create();
        $invoice1 = Invoice::factory()->create(['client_id' => $client->id]);
        $invoice2 = Invoice::factory()->create(['client_id' => $client->id]);

        $this->assertInstanceOf(Invoice::class, $client->invoices->first());
        $this->assertCount(2, $client->invoices);
    }

    public function test_client_has_many_payments(): void
    {
        $client = Client::factory()->create();
        $payment1 = Payment::factory()->create(['client_id' => $client->id]);
        $payment2 = Payment::factory()->create(['client_id' => $client->id]);

        $this->assertInstanceOf(Payment::class, $client->payments->first());
        $this->assertCount(2, $client->payments);
    }

    public function test_client_has_many_messages(): void
    {
        $client = Client::factory()->create();
        $message1 = Message::factory()->create(['client_id' => $client->id]);
        $message2 = Message::factory()->create(['client_id' => $client->id]);

        $this->assertInstanceOf(Message::class, $client->messages->first());
        $this->assertCount(2, $client->messages);
    }
}
