<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\BusinessDetail;
use App\Models\Invoice;
use App\Models\Message;
use App\Models\Order;
use App\Models\Task;
use App\Models\User;
use App\Notifications\AppointmentConfirmationNotification;
use App\Notifications\AppointmentCreatedNotification;
use App\Notifications\AppointmentReminderNotification;
use App\Notifications\Channels\PushNotificationChannel;
use App\Notifications\EmailMessageNotification;
use App\Notifications\InvoiceEmailNotification;
use App\Notifications\InvoiceReminderNotification;
use App\Notifications\MessageEmailNotification;
use App\Notifications\OrderReminderNotification;
use App\Notifications\OrderStatusNotification;
use App\Notifications\SubscriptionConfirmationNotification;
use App\Notifications\SubscriptionUpgradeReminderNotification;
use App\Notifications\TaskReminderNotification;
use App\Notifications\TeamMemberInvitation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class NotificationsPushTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        
        // Create a push subscription for the user so PushNotificationChannel is included in via()
        $this->user->pushSubscriptions()->create([
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/test-endpoint',
            'public_key' => 'test-public-key',
            'auth_token' => 'test-auth-token',
            'content_encoding' => 'aes128gcm',
        ]);

        $this->actingAs($this->user);
    }

    public function test_all_notifications_have_push_support()
    {
        $order = Order::factory()->create(['user_id' => $this->user->id]);
        $appointment = Appointment::factory()->create(['user_id' => $this->user->id]);
        $invoice = Invoice::factory()->create(['user_id' => $this->user->id]);
        $message = Message::factory()->create(['sender_id' => $this->user->id, 'status' => 'sent']);
        $task = Task::factory()->create(['user_id' => $this->user->id]);
        $businessDetail = BusinessDetail::create([
            'user_id' => $this->user->id,
            'business_name' => 'Test Business',
            'business_address' => '123 Test St',
            'business_phone' => '1234567890',
            'business_email' => 'test@example.com',
            'subscription_plan' => 'free',
        ]);

        $notifications = [
            new OrderStatusNotification($order, 'processing'),
            new OrderReminderNotification($order),
            new AppointmentConfirmationNotification($appointment),
            new AppointmentCreatedNotification($appointment),
            new AppointmentReminderNotification($appointment),
            new InvoiceEmailNotification($invoice),
            new InvoiceReminderNotification($invoice),
            new EmailMessageNotification('Subject', 'Body', 'Sender'),
            new MessageEmailNotification($message),
            new TaskReminderNotification($task),
            new SubscriptionConfirmationNotification($businessDetail),
            new SubscriptionUpgradeReminderNotification($businessDetail),
            new TeamMemberInvitation('password', 'Business Name'),
        ];

        foreach ($notifications as $notification) {
            $className = get_class($notification);
            
            // 1. Check if PushNotificationChannel is in via()
            $via = $notification->via($this->user);
            $this->assertContains(
                PushNotificationChannel::class, 
                $via, 
                "Notification {$className} is missing PushNotificationChannel in via()."
            );

            // 2. Check if toPushNotification returns the correct structure
            $pushData = $notification->toPushNotification($this->user);
            
            $this->assertIsArray($pushData, "{$className}::toPushNotification() must return an array.");
            $this->assertArrayHasKey('title', $pushData, "{$className} push data missing 'title'.");
            $this->assertArrayHasKey('body', $pushData, "{$className} push data missing 'body'.");
            $this->assertArrayHasKey('url', $pushData, "{$className} push data missing 'url'.");
            
            $this->assertNotEmpty($pushData['title'], "{$className} push title is empty.");
            $this->assertNotEmpty($pushData['body'], "{$className} push body is empty.");
        }
    }
}
