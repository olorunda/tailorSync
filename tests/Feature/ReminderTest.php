<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Task;
use App\Models\User;
use App\Notifications\AppointmentReminderNotification;
use App\Notifications\InvoiceReminderNotification;
use App\Notifications\OrderReminderNotification;
use App\Notifications\TaskReminderNotification;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ReminderTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_sends_task_reminders_to_assigned_team_members()
    {
        Notification::fake();

        $admin = User::factory()->create(['role' => 'admin']);
        $teamMember = User::factory()->create(['role' => 'staff']);
        
        // Task due tomorrow
        $task = Task::factory()->create([
            'user_id' => $admin->id,
            'assigned_to' => $teamMember->id,
            'due_date' => Carbon::tomorrow(),
            'status' => 'pending'
        ]);

        $this->artisan('reminders:send');

        Notification::assertSentTo(
            $teamMember,
            TaskReminderNotification::class
        );
    }

    /** @test */
    public function it_sends_order_reminders_to_both_client_and_admin()
    {
        Notification::fake();

        $admin = User::factory()->create(['role' => 'admin']);
        $client = Client::factory()->create(['user_id' => $admin->id]);
        
        // Order due tomorrow
        $order = Order::factory()->create([
            'user_id' => $admin->id,
            'client_id' => $client->id,
            'due_date' => Carbon::tomorrow(),
            'status' => 'in_progress'
        ]);

        $this->artisan('reminders:send');

        // Check customer notification
        Notification::assertSentTo(
            $client,
            OrderReminderNotification::class
        );

        // Check admin notification
        Notification::assertSentTo(
            $admin,
            OrderReminderNotification::class
        );
    }

    /** @test */
    public function it_sends_appointment_reminders_to_both_client_and_admin()
    {
        Notification::fake();

        $admin = User::factory()->create(['role' => 'admin']);
        $client = Client::factory()->create(['user_id' => $admin->id]);
        
        // Appointment tomorrow
        $appointment = Appointment::factory()->create([
            'user_id' => $admin->id,
            'client_id' => $client->id,
            'start_time' => Carbon::tomorrow()->setHour(10),
            'status' => 'pending',
            'reminder_sent' => false
        ]);

        $this->artisan('reminders:send');

        // Check customer notification
        Notification::assertSentTo(
            $client,
            AppointmentReminderNotification::class
        );

        // Check admin notification
        Notification::assertSentTo(
            $admin,
            AppointmentReminderNotification::class
        );

        $this->assertTrue($appointment->fresh()->reminder_sent);
    }

    /** @test */
    public function it_sends_overdue_invoice_reminders_to_clients()
    {
        Notification::fake();

        $admin = User::factory()->create(['role' => 'admin']);
        $client = Client::factory()->create(['user_id' => $admin->id]);
        
        // Invoice overdue by 5 days
        $invoice = Invoice::factory()->create([
            'user_id' => $admin->id,
            'client_id' => $client->id,
            'due_date' => Carbon::today()->subDays(5),
            'status' => 'sent'
        ]);

        $this->artisan('reminders:send');

        Notification::assertSentTo(
            $client,
            InvoiceReminderNotification::class
        );
    }

    /** @test */
    public function it_does_not_send_duplicate_reminders_within_throttle_period()
    {
        Notification::fake();

        $admin = User::factory()->create(['role' => 'admin']);
        $client = Client::factory()->create(['user_id' => $admin->id]);
        
        // Overdue order
        $order = Order::factory()->create([
            'user_id' => $admin->id,
            'client_id' => $client->id,
            'due_date' => Carbon::today()->subDays(2),
            'status' => 'in_progress'
        ]);

        // First run
        $this->artisan('reminders:send');
        Notification::assertSentTo($client, OrderReminderNotification::class);

        // Reset fake to clear recorded notifications
        Notification::fake();

        // Second run immediately after - should NOT send
        $this->artisan('reminders:send');
        Notification::assertNotSentTo($client, OrderReminderNotification::class);
    }
}
