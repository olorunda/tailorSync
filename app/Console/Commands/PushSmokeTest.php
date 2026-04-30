<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Order;
use App\Models\Appointment;
use App\Models\Invoice;
use App\Models\Message;
use App\Models\Task;
use App\Models\BusinessDetail;
use App\Notifications\OrderStatusNotification;
use App\Notifications\OrderReminderNotification;
use App\Notifications\AppointmentConfirmationNotification;
use App\Notifications\AppointmentCreatedNotification;
use App\Notifications\AppointmentReminderNotification;
use App\Notifications\InvoiceEmailNotification;
use App\Notifications\InvoiceReminderNotification;
use App\Notifications\EmailMessageNotification;
use App\Notifications\MessageEmailNotification;
use App\Notifications\TaskReminderNotification;
use App\Notifications\SubscriptionConfirmationNotification;
use App\Notifications\SubscriptionUpgradeReminderNotification;
use App\Notifications\TeamMemberInvitation;

class PushSmokeTest extends Command
{
    protected $signature = 'push:smoke-test {user_id}';
    protected $description = 'Send one of each type of push notification to a user for testing';

    public function handle()
    {
        $userId = $this->argument('user_id');
        $user = User::find($userId);

        if (!$user) {
            $this->error("User with ID {$userId} not found.");
            return 1;
        }

        if (!$user->pushSubscriptions()->exists()) {
            $this->error("User has no push subscriptions. ID=2 in push_subscriptions table corresponds to user_id=34.");
            return 1;
        }

        $this->info("Starting smoke test for User #{$userId}...");

        // Create or find dummy data
        $order = Order::where('user_id', $userId)->first() ?: Order::factory()->create(['user_id' => $userId]);
        $appointment = Appointment::where('user_id', $userId)->first() ?: Appointment::factory()->create(['user_id' => $userId]);
        $invoice = Invoice::where('user_id', $userId)->first() ?: Invoice::factory()->create(['user_id' => $userId]);
        $message = Message::where('sender_id', $userId)->first() ?: Message::factory()->create(['sender_id' => $userId, 'status' => 'sent']);
        $task = Task::where('user_id', $userId)->first() ?: Task::factory()->create(['user_id' => $userId]);
        $businessDetail = BusinessDetail::where('user_id', $userId)->first() ?: BusinessDetail::create([
            'user_id' => $userId,
            'business_name' => 'Smoke Test Business',
            'business_address' => '123 Smoke Ave',
            'business_phone' => '1112223333',
            'business_email' => 'smoke@test.com',
            'subscription_plan' => 'free',
        ]);

        $notificationMap = [
            'Order Status' => new OrderStatusNotification($order, 'processing'),
            'Order Reminder' => new OrderReminderNotification($order),
            'Appointment Confirmed' => new AppointmentConfirmationNotification($appointment),
            'Appointment Created' => new AppointmentCreatedNotification($appointment),
            'Appointment Reminder' => new AppointmentReminderNotification($appointment),
            'Invoice Created' => new InvoiceEmailNotification($invoice),
            'Invoice Reminder' => new InvoiceReminderNotification($invoice),
            'Direct Message' => new EmailMessageNotification('New Direct Message', 'Hello from the smoke test!', 'Admin'),
            'Internal Message' => new MessageEmailNotification($message),
            'Task Reminder' => new TaskReminderNotification($task),
            'Subscription Confirmed' => new SubscriptionConfirmationNotification($businessDetail),
            'Subscription Upgrade' => new SubscriptionUpgradeReminderNotification($businessDetail),
            'Team Invitation' => new TeamMemberInvitation('secret-pass', 'ThredNix Smoke Test'),
        ];

        foreach ($notificationMap as $label => $notification) {
            $this->comment("Sending {$label}...");
            $user->notify($notification);
            $this->info("✅ Triggered {$label}");

            // Small sleep to avoid rate limiting or overwhelming the client
            usleep(200000);
        }

        $this->info("Smoke test complete! Check your device for notifications.");
        return 0;
    }
}
