<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class ClientTest extends TestCase
{
    use RefreshDatabase;

    public function test_clients_page_is_displayed(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('clients.index'));
        $response->assertStatus(200);
    }

    public function test_clients_can_be_created(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = Volt::test('clients.create')
            ->set('name', 'Test Client')
            ->set('email', 'client@example.com')
            ->set('phone', '1234567890')
            ->set('address', '123 Test St')
            ->set('notes', 'Test notes')
            ->call('save');

        $response->assertHasNoErrors();

        $this->assertDatabaseHas('clients', [
            'user_id' => $user->id,
            'name' => 'Test Client',
            'email' => 'client@example.com',
            'phone' => '1234567890',
            'address' => '123 Test St',
            'notes' => 'Test notes',
        ]);
    }

    public function test_client_details_can_be_viewed(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create([
            'user_id' => $user->id,
            'name' => 'Test Client',
            'email' => 'client@example.com',
        ]);

        $this->actingAs($user);

        $response = $this->get(route('clients.show', $client));
        $response->assertStatus(200);
        $response->assertSee('Test Client');
        $response->assertSee('client@example.com');
    }

    public function test_client_can_be_updated(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create([
            'user_id' => $user->id,
            'name' => 'Test Client',
            'email' => 'client@example.com',
        ]);

        $this->actingAs($user);

        $response = Volt::test('clients.edit', ['client' => $client])
            ->set('name', 'Updated Client')
            ->set('email', 'updated@example.com')
            ->call('save');

        $response->assertHasNoErrors();

        $this->assertDatabaseHas('clients', [
            'id' => $client->id,
            'name' => 'Updated Client',
            'email' => 'updated@example.com',
        ]);
    }

    public function test_client_validation_works(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = Volt::test('clients.create')
            ->set('name', '')
            ->set('email', 'not-an-email')
            ->call('save');

        $response->assertHasErrors(['name', 'email']);
    }

    public function test_user_cannot_view_others_clients(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $client = Client::factory()->create([
            'user_id' => $user1->id,
            'name' => 'Test Client',
        ]);

        $this->actingAs($user2);

        $response = $this->get(route('clients.show', $client));
        $response->assertStatus(403);
    }
}
