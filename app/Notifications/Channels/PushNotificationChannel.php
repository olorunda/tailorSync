<?php

namespace App\Notifications\Channels;

use App\Services\PushNotificationService;
use Illuminate\Notifications\Notification;

class PushNotificationChannel
{
    /**
     * The push notification service instance.
     *
     * @var \App\Services\PushNotificationService
     */
    protected $pushNotificationService;

    /**
     * Create a new channel instance.
     *
     * @param  \App\Services\PushNotificationService  $pushNotificationService
     * @return void
     */
    public function __construct(PushNotificationService $pushNotificationService)
    {
        $this->pushNotificationService = $pushNotificationService;
    }

    /**
     * Send the given notification.
     *
     * @param  mixed  $notifiable
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return void
     */
    public function send($notifiable, Notification $notification)
    {
        if (!method_exists($notification, 'toPushNotification')) {
            return;
        }

        // Get the push notification data from the notification
        $data = $notification->toPushNotification($notifiable);

        // Send the push notification
        $this->pushNotificationService->sendToNotifiable($notifiable, $data);
    }
}
