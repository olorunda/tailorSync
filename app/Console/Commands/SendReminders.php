<?php

namespace App\Console\Commands;

use App\Models\Appointment;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\ReminderLog;
use App\Models\Task;
use App\Notifications\AppointmentReminderNotification;
use App\Notifications\InvoiceReminderNotification;
use App\Notifications\OrderReminderNotification;
use App\Notifications\TaskReminderNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reminders:send';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send due date reminders for tasks, orders, appointments, and invoices';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting reminder process...');

        $this->sendTaskReminders();
        $this->sendOrderReminders();
        $this->sendAppointmentReminders();
        $this->sendInvoiceReminders();

        $this->info('Reminder process completed.');
    }

    /**
     * Check if a reminder should be sent.
     */
    protected function shouldRemind($model, $type)
    {
        $baseQuery = ReminderLog::where('remindable_id', $model->id)
            ->where('remindable_type', get_class($model));

        // Limit total reminders (upcoming + overdue) to 2 to avoid spamming
        if ((clone $baseQuery)->count() >= 2) {
            return false;
        }

        $query = $baseQuery->where('reminder_type', $type);

        // If it's an overdue reminder, we might want to resend it after 3 days
        if ($type === 'overdue') {
            return !$query->where('sent_at', '>', now()->subDays(3))->exists();
        }

        // For other types (like upcoming), send only once
        return !$query->exists();
    }

    /**
     * Log that a reminder was sent.
     */
    protected function logReminder($model, $type)
    {
        ReminderLog::create([
            'remindable_id' => $model->id,
            'remindable_type' => get_class($model),
            'reminder_type' => $type,
            'sent_at' => now(),
        ]);
    }

    /**
     * Send reminders to team members for tasks due tomorrow or overdue.
     */
    protected function sendTaskReminders()
    {
        $this->info('Processing tasks...');
        
        $tasks = Task::whereNotIn('status', ['completed', 'cancelled'])
            ->whereNotNull('assigned_to')
            ->where(function ($query) {
                $query->whereDate('due_date', Carbon::tomorrow())
                    ->orWhereDate('due_date', '<', Carbon::today());
            })
            ->get();

        foreach ($tasks as $task) {
            if ($task->teamMember) {
                $isOverdue = $task->due_date && $task->due_date->isPast();
                $type = $isOverdue ? 'overdue' : 'upcoming';

                if ($this->shouldRemind($task, $type)) {
                    $task->teamMember->notify(new TaskReminderNotification($task, $isOverdue));
                    $this->logReminder($task, $type);
                    $this->line(" - Task Reminder ({$type}) sent to {$task->teamMember->name} for '{$task->title}'");
                }
            }
        }
    }

    /**
     * Send reminders to customers and admins for orders due tomorrow or overdue.
     */
    protected function sendOrderReminders()
    {
        $this->info('Processing orders...');

        $orders = Order::whereNotIn('status', ['completed', 'delivered', 'cancelled'])
            ->whereNotNull('client_id')
            ->where(function ($query) {
                $query->whereDate('due_date', Carbon::tomorrow())
                    ->orWhereDate('due_date', '<', Carbon::today());
            })
            ->get();

        foreach ($orders as $order) {
            $isOverdue = $order->due_date && $order->due_date->isPast();
            $type = $isOverdue ? 'overdue' : 'upcoming';

            if ($this->shouldRemind($order, $type)) {
                // Notify Customer
                if ($order->client) {
                    $order->client->notify(new OrderReminderNotification($order, $isOverdue, 'customer'));
                    $this->line(" - Order Reminder ({$type}) sent to client {$order->client->name} for Order #{$order->order_number}");
                }

                // Notify Admin (Business Owner)
                if ($order->user) {
                    $order->user->notify(new OrderReminderNotification($order, $isOverdue, 'admin'));
                    $this->line(" - Order Reminder ({$type}) sent to admin {$order->user->name} for Order #{$order->order_number}");
                }

                $this->logReminder($order, $type);
            }
        }
    }

    /**
     * Send reminders to customers and admins for upcoming appointments.
     */
    protected function sendAppointmentReminders()
    {
        $this->info('Processing appointments...');

        $appointments = Appointment::where('status', 'pending')
            ->where('reminder_sent', false)
            ->whereDate('start_time', Carbon::tomorrow())
            ->get();

        foreach ($appointments as $appointment) {
            // Notify Customer
            if ($appointment->client) {
                $appointment->client->notify(new AppointmentReminderNotification($appointment, 'customer'));
                $this->line(" - Appointment Reminder sent to client {$appointment->client->name} for '{$appointment->title}'");
            }

            // Notify Admin (Business Owner) - Using order user if available, else appointment user
            $admin = ($appointment->order && $appointment->order->user) ? $appointment->order->user : $appointment->user;
            if ($admin) {
                $admin->notify(new AppointmentReminderNotification($appointment, 'admin'));
                $this->line(" - Appointment Reminder sent to admin {$admin->name} for '{$appointment->title}'");
            }

            $appointment->update(['reminder_sent' => true]);
            $this->logReminder($appointment, 'upcoming');
        }
    }

    /**
     * Send reminders to customers for invoices due tomorrow or overdue.
     */
    protected function sendInvoiceReminders()
    {
        $this->info('Processing invoices...');

        $invoices = Invoice::whereNotIn('status', ['paid', 'cancelled'])
            ->whereNotNull('client_id')
            ->where(function ($query) {
                $query->whereDate('due_date', Carbon::tomorrow())
                    ->orWhereDate('due_date', '<', Carbon::today());
            })
            ->get();

        foreach ($invoices as $invoice) {
            if ($invoice->client) {
                $isOverdue = $invoice->due_date && $invoice->due_date->isPast();
                $type = $isOverdue ? 'overdue' : 'upcoming';

                if ($this->shouldRemind($invoice, $type)) {
                    $invoice->client->notify(new InvoiceReminderNotification($invoice, $isOverdue));
                    $this->logReminder($invoice, $type);
                    $this->line(" - Invoice Reminder ({$type}) sent to client {$invoice->client->name} for Invoice #{$invoice->invoice_number}");
                }
            }
        }
    }
}
