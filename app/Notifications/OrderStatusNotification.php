<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderStatusNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $order;
    protected $oldStatus;
    protected $newStatus;

    /**
     * Create a new notification instance.
     */
    public function __construct(Order $order, ?string $oldStatus = null)
    {
        $this->order = $order;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $order->status;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $statusMessages = [
            'pending' => 'Your order has been received and is pending processing.',
            'processing' => 'Your order is now being processed.',
            'in_progress' => 'Work has begun on your order.',
            'ready_for_fitting' => 'Your order is ready for fitting. Please schedule an appointment.',
            'completed' => 'Your order has been completed and is ready for pickup.',
            'delivered' => 'Your order has been delivered. Thank you for your business!',
            'cancelled' => 'Your order has been cancelled.',
        ];

        $statusMessage = $statusMessages[$this->newStatus] ?? "Your order status has been updated to {$this->newStatus}.";

        return (new MailMessage)
            ->subject("Order #{$this->order->order_number} Status Update")
            ->greeting("Hello {$notifiable->name},")
            ->line("We're writing to inform you about an update to your order #{$this->order->order_number}.")
            ->line($statusMessage)
            ->line("Order Details:")
            ->line("Design: {$this->order->design_name}")
            ->line("Due Date: " . ($this->order->due_date ? $this->order->due_date->format('F j, Y') : 'Not specified'))
            ->action('View Order Details', url('/orders/' . $this->order->id))
            ->line('Thank you for your business!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'old_status' => $this->oldStatus,
            'new_status' => $this->newStatus,
        ];
    }
}
