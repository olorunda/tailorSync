<?php

namespace App\Notifications;

use App\Models\Message;
use App\Notifications\Channels\PushNotificationChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MessageEmailNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $message;

    /**
     * Create a new notification instance.
     */
    public function __construct(Message $message)
    {
        $this->message = $message;
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
        $sender = $this->message->sender;
        $senderName = $sender ? $sender->name : 'Unknown';

        return (new MailMessage)
            ->subject($this->message->subject)
            ->greeting("Hello {$notifiable->name},")
            ->line("You have received a new message from {$senderName}.")
            ->line("Message:")
            ->line(nl2br($this->message->message))
            ->action('View Message', route('messages.index'))
            ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $sender = $this->message->sender;
        $senderName = $sender ? $sender->name : 'Unknown';

        // Store the mail message components
        $mailData = [
            'subject' => $this->message->subject,
            'greeting' => "Hello {$notifiable->name},",
            'lines' => [
                "You have received a new message from {$senderName}.",
                "Message:",
                nl2br($this->message->message),
                'Thank you for using our application!'
            ],
            'action' => [
                'text' => 'View Message',
                'url' => route('messages.index')
            ]
        ];

        return [
            'message_id' => $this->message->id,
            'subject' => $this->message->subject,
            'sender_id' => $this->message->sender_id,
            'sender_name' => $senderName,
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
        $sender = $this->message->sender;
        $senderName = $sender ? $sender->name : 'Unknown';

        return [
            'title' => "New Message from {$senderName}",
            'body' => $this->message->subject,
            'icon' => '/apple-touch-icon.png',
            'badge' => '/apple-touch-icon.png',
            'tag' => 'message-' . $this->message->id,
            'url' => route('messages.index'),
            'data' => [
                'message_id' => $this->message->id,
                'type' => 'new_message'
            ]
        ];
    }
}
