<?php

namespace App\Notifications;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AppointmentReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $appointment;
    protected $target; // 'customer' or 'admin'

    /**
     * Create a new notification instance.
     */
    public function __construct(Appointment $appointment, string $target = 'customer')
    {
        $this->appointment = $appointment;
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
        $formattedDate = $this->appointment->start_time->format('l, F j, Y');
        $formattedTime = $this->appointment->start_time->format('g:i A');
        $endTime = $this->appointment->end_time ? $this->appointment->end_time->format('g:i A') : null;
        $timeRange = $endTime ? "{$formattedTime} - {$endTime}" : $formattedTime;

        if ($this->target === 'admin') {
            return (new MailMessage)
                ->subject("Staff Reminder: Upcoming Appointment with " . ($this->appointment->client->name ?? 'Client'))
                ->greeting("Hello Team,")
                ->line("This is a reminder about an upcoming appointment on your schedule.")
                ->line("**Appointment Details:**")
                ->line("**Client:** " . ($this->appointment->client->name ?? 'N/A'))
                ->line("**Title:** {$this->appointment->title}")
                ->line("**Date:** {$formattedDate}")
                ->line("**Time:** {$timeRange}")
                ->line("**Location:** " . ($this->appointment->location ?: 'Our store'))
                ->action('Manage Appointment', url('/appointments/' . $this->appointment->id))
                ->line('Have a productive meeting!');
        }

        // Customer content
        $orderInfo = '';
        if ($this->appointment->order) {
            $orderInfo = "This appointment is related to your order #{$this->appointment->order->order_number}.";
        }

        return (new MailMessage)
            ->subject("Reminder: Upcoming Appointment on {$formattedDate}")
            ->greeting("Hello {$notifiable->name},")
            ->line("This is a friendly reminder about your upcoming appointment.")
            ->line("**Appointment Details:**")
            ->line("**Title:** {$this->appointment->title}")
            ->line("**Date:** {$formattedDate}")
            ->line("**Time:** {$timeRange}")
            ->line("**Location:** " . ($this->appointment->location ?: 'Our store'))
            ->when($this->appointment->description, function ($message) {
                return $message->line("**Description:** {$this->appointment->description}");
            })
            ->when($orderInfo, function ($message) use ($orderInfo) {
                return $message->line($orderInfo);
            })
            ->line("Please let us know if you need to reschedule or have any questions.")
            ->action('View Appointment Details', url('/appointments/' . $this->appointment->id))
            ->line('Thank you for your business!');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'appointment_id' => $this->appointment->id,
            'title' => $this->appointment->title,
            'start_time' => $this->appointment->start_time->toIso8601String(),
            'target' => $this->target,
            'message' => $this->target === 'admin'
                ? "Internal Reminder: Appointment with " . ($this->appointment->client->name ?? 'Client')
                : "Reminder for your appointment: {$this->appointment->title}",
        ];
    }
}
