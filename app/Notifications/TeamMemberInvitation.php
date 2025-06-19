<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TeamMemberInvitation extends Notification
{
    use Queueable;

    /**
     * The password for the team member.
     *
     * @var string
     */
    protected $password;

    /**
     * The business name.
     *
     * @var string
     */
    protected $businessName;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $password, string $businessName)
    {
        $this->password = $password;
        $this->businessName = $businessName;
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
        return (new MailMessage)
            ->subject('You have been invited to join ' . $this->businessName)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('You have been invited to join ' . $this->businessName . ' on TailorFit.')
            ->line('Your account has been created with the following credentials:')
            ->line('Email: ' . $notifiable->email)
            ->line('Password: ' . $this->password)
            ->line('Please change your password after your first login for security reasons.')
            ->action('Login to TailorFit', url('/login'))
            ->line('Thank you for using TailorFit!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
