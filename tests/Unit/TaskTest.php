<?php

namespace Tests\Unit;

use App\Models\Task;
use App\Models\User;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskTest extends TestCase
{
    use RefreshDatabase;

    public function test_task_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $task->user);
        $this->assertEquals($user->id, $task->user->id);
    }

    public function test_task_belongs_to_team_member(): void
    {
        $user = User::factory()->create();
        $teamMember = User::factory()->create();
        $task = Task::factory()->create([
            'user_id' => $user->id,
            'assigned_to' => $teamMember->id
        ]);

        $this->assertInstanceOf(User::class, $task->teamMember);
        $this->assertEquals($teamMember->id, $task->teamMember->id);
    }

    public function test_task_belongs_to_order(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);
        $task = Task::factory()->create([
            'user_id' => $user->id,
            'order_id' => $order->id
        ]);

        $this->assertInstanceOf(Order::class, $task->order);
        $this->assertEquals($order->id, $task->order->id);
    }

    public function test_task_scope_status(): void
    {
        $user = User::factory()->create();
        $pendingTask = Task::factory()->create([
            'user_id' => $user->id,
            'status' => 'pending'
        ]);
        $inProgressTask = Task::factory()->create([
            'user_id' => $user->id,
            'status' => 'in_progress'
        ]);
        $completedTask = Task::factory()->create([
            'user_id' => $user->id,
            'status' => 'completed'
        ]);

        $pendingTasks = Task::status('pending')->get();
        $inProgressTasks = Task::status('in_progress')->get();
        $completedTasks = Task::status('completed')->get();

        $this->assertCount(1, $pendingTasks);
        $this->assertCount(1, $inProgressTasks);
        $this->assertCount(1, $completedTasks);
        $this->assertEquals($pendingTask->id, $pendingTasks->first()->id);
        $this->assertEquals($inProgressTask->id, $inProgressTasks->first()->id);
        $this->assertEquals($completedTask->id, $completedTasks->first()->id);
    }

    public function test_task_scope_priority(): void
    {
        $user = User::factory()->create();
        $lowTask = Task::factory()->create([
            'user_id' => $user->id,
            'priority' => 'low'
        ]);
        $mediumTask = Task::factory()->create([
            'user_id' => $user->id,
            'priority' => 'medium'
        ]);
        $highTask = Task::factory()->create([
            'user_id' => $user->id,
            'priority' => 'high'
        ]);

        $lowTasks = Task::priority('low')->get();
        $mediumTasks = Task::priority('medium')->get();
        $highTasks = Task::priority('high')->get();

        $this->assertCount(1, $lowTasks);
        $this->assertCount(1, $mediumTasks);
        $this->assertCount(1, $highTasks);
        $this->assertEquals($lowTask->id, $lowTasks->first()->id);
        $this->assertEquals($mediumTask->id, $mediumTasks->first()->id);
        $this->assertEquals($highTask->id, $highTasks->first()->id);
    }

    public function test_task_scope_overdue(): void
    {
        $user = User::factory()->create();
        $overdueTask = Task::factory()->create([
            'user_id' => $user->id,
            'status' => 'pending',
            'due_date' => now()->subDays(2)
        ]);
        $upcomingTask = Task::factory()->create([
            'user_id' => $user->id,
            'status' => 'pending',
            'due_date' => now()->addDays(2)
        ]);
        $completedOverdueTask = Task::factory()->create([
            'user_id' => $user->id,
            'status' => 'completed',
            'due_date' => now()->subDays(2)
        ]);

        $overdueTasks = Task::overdue()->get();

        $this->assertCount(1, $overdueTasks);
        $this->assertEquals($overdueTask->id, $overdueTasks->first()->id);
        $this->assertNotContains($upcomingTask->id, $overdueTasks->pluck('id'));
        $this->assertNotContains($completedOverdueTask->id, $overdueTasks->pluck('id'));
    }

    public function test_task_is_overdue_method(): void
    {
        $overdueTask = Task::factory()->create([
            'status' => 'pending',
            'due_date' => now()->subDays(2)
        ]);
        $upcomingTask = Task::factory()->create([
            'status' => 'pending',
            'due_date' => now()->addDays(2)
        ]);
        $completedOverdueTask = Task::factory()->create([
            'status' => 'completed',
            'due_date' => now()->subDays(2)
        ]);

        $this->assertTrue($overdueTask->isOverdue());
        $this->assertFalse($upcomingTask->isOverdue());
        $this->assertFalse($completedOverdueTask->isOverdue());
    }

    public function test_task_complete_method(): void
    {
        $task = Task::factory()->create([
            'status' => 'pending',
            'completed_date' => null
        ]);

        $task->complete();

        $this->assertEquals('completed', $task->status);
        $this->assertNotNull($task->completed_date);
        $this->assertInstanceOf(\Carbon\Carbon::class, $task->completed_date);
    }
}
