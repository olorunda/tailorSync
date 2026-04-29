<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $order;
    protected $isOverdue;

    /**
     * Create a new notification instance.
     */
    public function __construct(Order $order, bool $isOverdue = false)
    {
        $this->order = $order;
        $this->isOverdue = $isOverdue;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $subject = $this->isOverdue 
            ? "Update on your Order #{$this->order->order_number}" 
            : "Reminder: Your Order #{$this->order->order_number} is nearly ready";

        $message = $this->isOverdue
            ? "We are working hard on your order. There has been a slight delay, but it's our top priority."
            : "This is a friendly reminder regarding the estimated completion date for your order.";

        return (new MailMessage)
            ->subject($subject)
            ->greeting("Hello {$notifiable->name},")
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
            'message' => "Reminder for order #{$this->order->order_number}",
        ];
    }
}
