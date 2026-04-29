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
    protected $target; // 'customer' or 'admin'

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
        if ($this->target === 'admin') {
            $subject = $this->isOverdue 
                ? "ACTION REQUIRED: Order #{$this->order->order_number} is OVERDUE" 
                : "UPCOMING DUE DATE: Order #{$this->order->order_number}";

            $message = $this->isOverdue
                ? "This is an internal reminder that Order #{$this->order->order_number} has passed its due date. Please update the customer and adjust the schedule if necessary."
                : "This is an internal reminder that Order #{$this->order->order_number} is approaching its due date.";

            return (new MailMessage)
                ->subject($subject)
                ->greeting("Hello Team,")
                ->line($message)
                ->line("**Order Details:**")
                ->line("**Order Number:** #{$this->order->order_number}")
                ->line("**Customer:** " . ($this->order->customer->name ?? 'N/A'))
                ->line("**Design:** " . ($this->order->design_name ?: ($this->order->design->name ?? 'Custom Design')))
                ->line("**Due Date:** " . ($this->order->due_date ? $this->order->due_date->format('F j, Y') : 'Not specified'))
                ->line("**Status:** " . ucfirst($this->order->status))
                ->action('Manage Order', url('/orders/' . $this->order->id))
                ->line('Keep up the great work!');
        }

        // Default to Customer content
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
            'target' => $this->target,
            'message' => $this->target === 'admin' 
                ? "Internal Reminder: Order #{$this->order->order_number} " . ($this->isOverdue ? 'is overdue' : 'due soon')
                : "Reminder for order #{$this->order->order_number}",
        ];
    }
}
