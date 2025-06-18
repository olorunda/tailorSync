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

    /**
     * Create a new notification instance.
     */
    public function __construct(Appointment $appointment)
    {
        $this->appointment = $appointment;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
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
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $formattedDate = $this->appointment->start_time->format('l, F j, Y');
        $formattedTime = $this->appointment->start_time->format('g:i A');
        $endTime = $this->appointment->end_time ? $this->appointment->end_time->format('g:i A') : null;

        $timeRange = $endTime ? "{$formattedTime} - {$endTime}" : $formattedTime;

        $orderInfo = '';
        if ($this->appointment->order) {
            $orderInfo = "This appointment is related to your order #{$this->appointment->order->order_number}.";
        }

        // Prepare lines for the mail message
        $lines = [
            "This is a friendly reminder about your upcoming appointment.",
            "**Appointment Details:**",
            "**Title:** {$this->appointment->title}",
            "**Date:** {$formattedDate}",
            "**Time:** {$timeRange}",
            "**Location:** " . ($this->appointment->location ?: 'Our store')
        ];

        // Add description if available
        if ($this->appointment->description) {
            $lines[] = "**Description:** {$this->appointment->description}";
        }

        // Add order info if available
        if ($orderInfo) {
            $lines[] = $orderInfo;
        }

        $lines[] = "Please let us know if you need to reschedule or have any questions.";
        $lines[] = "Thank you for your business!";

        // Store the mail message components
        $mailData = [
            'subject' => "Reminder: Upcoming Appointment on {$formattedDate}",
            'greeting' => "Hello {$notifiable->name},",
            'lines' => $lines,
            'action' => [
                'text' => 'View Appointment Details',
                'url' => url('/appointments/' . $this->appointment->id)
            ]
        ];

        return [
            'appointment_id' => $this->appointment->id,
            'title' => $this->appointment->title,
            'start_time' => $this->appointment->start_time->toIso8601String(),
            'end_time' => $this->appointment->end_time ? $this->appointment->end_time->toIso8601String() : null,
            'location' => $this->appointment->location,
            'order_id' => $this->appointment->order_id,
            'mail' => $mailData
        ];
    }
}
