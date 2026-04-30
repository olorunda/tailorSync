<?php

namespace App\Notifications;

use App\Models\Order;
use App\Notifications\Channels\PushNotificationChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $order;
    protected $isOverdue;
    protected $target; // 'admin' or 'customer'

    /**
     * Create a new notification instance.
     */
    public function __construct(Order $order, bool $isOverdue = false, string $target = 'customer')
    {
        $this->order = $order;
        $this->isOverdue = $isOverdue;
        $this->target = $target;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $channels = ['mail', 'database'];

        // Add push notification channel if the notifiable has push subscriptions
        if (method_exists($notifiable, 'pushSubscriptions') && $notifiable->pushSubscriptions()->exists()) {
            $channels[] = PushNotificationChannel::class;
        }

        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $subject = $this->isOverdue 
            ? "Overdue Order: #{$this->order->order_number}" 
            : "Reminder: Order #{$this->order->order_number} Due Soon";

        $greeting = $this->target === 'admin' ? "Hello Team," : "Hello {$notifiable->name},";
        
        $message = $this->isOverdue
            ? "This is a reminder that Order #{$this->order->order_number} is past its due date."
            : "This is a friendly reminder that Order #{$this->order->order_number} is due soon.";

        return (new MailMessage)
            ->subject($subject)
            ->greeting($greeting)
            ->line($message)
            ->line("**Order Details:**")
            ->line("**Order Number:** #{$this->order->order_number}")
            ->line("**Design:** " . ($this->order->design_name ?: ($this->order->design->name ?? 'Custom Design')))
            ->line("**Due Date:** " . ($this->order->due_date ? $this->order->due_date->format('F j, Y') : 'Not specified'))
            ->line("**Status:** " . ucfirst($this->order->status))
            ->action('View Order Details', url('/orders/public/' . \App\Http\Controllers\PublicOrderController::generateHash($this->order->id)))
            ->line('Thank you for choosing us!');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'due_date' => $this->order->due_date ? $this->order->due_date->toIso8601String() : null,
            'is_overdue' => $this->isOverdue,
            'target' => $this->target,
            'message' => $this->target === 'admin' 
                ? "Internal Reminder: Order #{$this->order->order_number} " . ($this->isOverdue ? 'is overdue' : 'due soon')
                : "Reminder for order #{$this->order->order_number}",
        ];
    }
    /**
     * Get the push notification representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toPushNotification($notifiable): array
    {
        $title = $this->isOverdue ? "Overdue Order #{$this->order->order_number}" : "Order Reminder #{$this->order->order_number}";
        $body = $this->isOverdue ? "This order is past its due date." : "Your order is due soon. Check the details.";
        $hash = \App\Http\Controllers\PublicOrderController::generateHash($this->order->id);

        return [
            'title' => $title,
            'body' => $body,
            'icon' => '/apple-touch-icon.png',
            'badge' => '/apple-touch-icon.png',
            'tag' => 'order-reminder-' . $this->order->id,
            'url' => url('/orders/public/' . $hash),
            'data' => [
                'order_id' => $this->order->id,
                'type' => 'order_reminder'
            ]
        ];
    }
}
