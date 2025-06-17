<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class AppointmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_appointments_page_is_displayed(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('appointments.index'));
        $response->assertStatus(200);
    }

    public function test_appointments_can_be_created(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = Volt::test('appointments.create')
            ->set('title', 'Test Appointment')
            ->set('description', 'Test appointment description')
            ->set('date', now()->addDay()->format('Y-m-d'))
            ->set('time', '14:00')
            ->set('location', 'Test Location')
            ->set('status', 'scheduled')
            ->call('save');

        $response->assertHasNoErrors();

        $this->assertDatabaseHas('appointments', [
            'user_id' => $user->id,
            'title' => 'Test Appointment',
            'description' => 'Test appointment description',
            'location' => 'Test Location',
            'status' => 'scheduled',
        ]);
    }

    public function test_appointment_can_be_updated(): void
    {
        $user = User::factory()->create();
        $appointment = Appointment::factory()->create([
            'user_id' => $user->id,
            'title' => 'Test Appointment',
            'description' => 'Test appointment description',
            'status' => 'scheduled',
        ]);

        $this->actingAs($user);

        $response = Volt::test('appointments.edit', ['appointment' => $appointment])
            ->set('title', 'Updated Appointment')
            ->set('description', 'Updated appointment description')
            ->set('status', 'completed')
            ->call('save');

        $response->assertHasNoErrors();

        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'title' => 'Updated Appointment',
            'description' => 'Updated appointment description',
            'status' => 'completed',
        ]);
    }

    public function test_appointment_validation_works(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = Volt::test('appointments.create')
            ->set('title', '')
            ->set('date', '')
            ->set('time', '')
            ->set('status', 'invalid')
            ->call('save');

        $response->assertHasErrors(['title', 'date', 'time', 'status']);
    }

    public function test_appointment_can_be_linked_to_client(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user);

        $response = Volt::test('appointments.create')
            ->set('title', 'Client Appointment')
            ->set('description', 'Appointment with client')
            ->set('date', now()->addDay()->format('Y-m-d'))
            ->set('time', '14:00')
            ->set('client_id', $client->id)
            ->set('status', 'scheduled')
            ->call('save');

        $response->assertHasNoErrors();

        $this->assertDatabaseHas('appointments', [
            'user_id' => $user->id,
            'title' => 'Client Appointment',
            'client_id' => $client->id,
        ]);
    }

    public function test_user_cannot_edit_others_appointments(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $appointment = Appointment::factory()->create([
            'user_id' => $user1->id,
            'title' => 'Test Appointment',
        ]);

        $this->actingAs($user2);

        $response = $this->get(route('appointments.edit', $appointment));
        $response->assertStatus(403);
    }

    public function test_past_appointments_are_marked_as_completed(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $pastDate = now()->subDay()->format('Y-m-d');
        $pastTime = '14:00';

        $appointment = Appointment::factory()->create([
            'user_id' => $user->id,
            'title' => 'Past Appointment',
            'date' => $pastDate . ' ' . $pastTime . ':00',
            'status' => 'scheduled',
        ]);

        $response = $this->get(route('appointments.index'));
        $response->assertStatus(200);

        // The view should show the appointment as completed
        $response->assertSee('Past Appointment');
        $response->assertSee('Completed');
    }
}
