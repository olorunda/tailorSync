<?php

namespace App\Notifications;

use App\Models\Task;
use App\Notifications\Channels\PushNotificationChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $task;
    protected $isOverdue;

    /**
     * Create a new notification instance.
     */
    public function __construct(Task $task, bool $isOverdue = false)
    {
        $this->task = $task;
        $this->isOverdue = $isOverdue;
    }

    /**
     * Get the notification's delivery channels.
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
            ? "URGENT: Task Overdue - {$this->task->title}" 
            : "Reminder: Task Due Soon - {$this->task->title}";

        $message = $this->isOverdue
            ? "The following task assigned to you is now past its due date."
            : "The following task assigned to you is approaching its due date.";

        return (new MailMessage)
            ->subject($subject)
            ->greeting("Hello {$notifiable->name},")
            ->line($message)
            ->line("**Task Details:**")
            ->line("**Title:** {$this->task->title}")
            ->line("**Due Date:** " . ($this->task->due_date ? $this->task->due_date->format('F j, Y') : 'Not specified'))
            ->line("**Priority:** " . ucfirst($this->task->priority))
            ->when($this->task->description, function ($mail) {
                return $mail->line("**Description:** {$this->task->description}");
            })
            ->action('View Task', url('/tasks'))
            ->line('Please update the task status once completed.');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'task_id' => $this->task->id,
            'title' => $this->task->title,
            'due_date' => $this->task->due_date ? $this->task->due_date->toIso8601String() : null,
            'is_overdue' => $this->isOverdue,
            'message' => $this->isOverdue 
                ? "Task '{$this->task->title}' is overdue." 
                : "Task '{$this->task->title}' is due soon.",
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
        $title = $this->isOverdue ? "URGENT: Task Overdue" : "Task Reminder";
        $body = $this->isOverdue ? "Task '{$this->task->title}' is overdue." : "Task '{$this->task->title}' is due soon.";

        return [
            'title' => $title,
            'body' => $body,
            'icon' => '/apple-touch-icon.png',
            'badge' => '/apple-touch-icon.png',
            'tag' => 'task-reminder-' . $this->task->id,
            'url' => url('/tasks'),
            'data' => [
                'task_id' => $this->task->id,
                'type' => 'task_reminder'
            ]
        ];
    }
}
