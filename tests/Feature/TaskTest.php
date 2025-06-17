<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class TaskTest extends TestCase
{
    use RefreshDatabase;

    public function test_tasks_page_is_displayed(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('tasks.index'));
        $response->assertStatus(200);
    }

    public function test_tasks_can_be_created(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = Volt::test('tasks.create')
            ->set('title', 'Test Task')
            ->set('description', 'Test task description')
            ->set('due_date', now()->addDay()->format('Y-m-d'))
            ->set('priority', 'medium')
            ->set('status', 'pending')
            ->call('save');

        $response->assertHasNoErrors();

        $this->assertDatabaseHas('tasks', [
            'user_id' => $user->id,
            'title' => 'Test Task',
            'description' => 'Test task description',
            'priority' => 'medium',
            'status' => 'pending',
        ]);
    }

    public function test_task_can_be_updated(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->create([
            'user_id' => $user->id,
            'title' => 'Test Task',
            'description' => 'Test task description',
            'priority' => 'medium',
            'status' => 'pending',
        ]);

        $this->actingAs($user);

        $response = Volt::test('tasks.edit', ['task' => $task])
            ->set('title', 'Updated Task')
            ->set('description', 'Updated task description')
            ->set('priority', 'high')
            ->set('status', 'in_progress')
            ->call('save');

        $response->assertHasNoErrors();

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => 'Updated Task',
            'description' => 'Updated task description',
            'priority' => 'high',
            'status' => 'in_progress',
        ]);
    }

    public function test_task_validation_works(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = Volt::test('tasks.create')
            ->set('title', '')
            ->set('description', '')
            ->set('priority', 'invalid')
            ->call('save');

        $response->assertHasErrors(['title', 'description', 'priority']);
    }

    public function test_task_can_be_assigned_to_team_member(): void
    {
        $user = User::factory()->create();
        $teamMember = User::factory()->create();

        $this->actingAs($user);

        $response = Volt::test('tasks.create')
            ->set('title', 'Team Task')
            ->set('description', 'Task for team member')
            ->set('due_date', now()->addDay()->format('Y-m-d'))
            ->set('priority', 'medium')
            ->set('status', 'pending')
            ->set('assigned_to', $teamMember->id)
            ->call('save');

        $response->assertHasNoErrors();

        $this->assertDatabaseHas('tasks', [
            'user_id' => $user->id,
            'title' => 'Team Task',
            'assigned_to' => $teamMember->id,
        ]);
    }

    public function test_task_can_be_linked_to_order(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user);

        $response = Volt::test('tasks.create')
            ->set('title', 'Order Task')
            ->set('description', 'Task for order')
            ->set('due_date', now()->addDay()->format('Y-m-d'))
            ->set('priority', 'medium')
            ->set('status', 'pending')
            ->set('order_id', $order->id)
            ->call('save');

        $response->assertHasNoErrors();

        $this->assertDatabaseHas('tasks', [
            'user_id' => $user->id,
            'title' => 'Order Task',
            'order_id' => $order->id,
        ]);
    }

    public function test_user_cannot_edit_others_tasks(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $task = Task::factory()->create([
            'user_id' => $user1->id,
            'title' => 'Test Task',
        ]);

        $this->actingAs($user2);

        $response = $this->get(route('tasks.edit', $task));
        $response->assertStatus(403);
    }
}
