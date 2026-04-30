<?php

namespace App\Notifications;

use App\Notifications\Channels\PushNotificationChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EmailMessageNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $subject;
    protected $messageContent;
    protected $senderName;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $subject, string $messageContent, string $senderName)
    {
        $this->subject = $subject;
        $this->messageContent = $messageContent;
        $this->senderName = $senderName;
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
        return (new MailMessage)
            ->subject($this->subject)
            ->greeting("Hello " . ($notifiable->name ?? $notifiable->email) . ",")
            ->line("You have received a new message from {$this->senderName}.")
            ->line("Message:")
            ->line(nl2br($this->messageContent))
            ->action('View Notifications', route('notifications.index'))
            ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        // Store the mail message components
        $mailData = [
            'subject' => $this->subject,
            'greeting' => "Hello {$notifiable->name},",
            'lines' => [
                "You have received a new message from {$this->senderName}.",
                "Message:",
                nl2br($this->messageContent),
                'Thank you for using our application!'
            ],
            'action' => [
                'text' => 'View Notifications',
                'url' => route('notifications.index')
            ]
        ];

        return [
            'subject' => $this->subject,
            'message' => $this->messageContent,
            'sender_name' => $this->senderName,
            'mail' => $mailData
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
        return [
            'title' => "New Message from {$this->senderName}",
            'body' => $this->subject,
            'icon' => '/apple-touch-icon.png',
            'badge' => '/apple-touch-icon.png',
            'tag' => 'email-message-' . md5($this->subject . $this->senderName),
            'url' => route('notifications.index'),
            'data' => [
                'sender_name' => $this->senderName,
                'type' => 'email_message'
            ]
        ];
    }
}
