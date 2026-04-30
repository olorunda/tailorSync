<?php

namespace App\Services;

use App\Models\PushSubscription;
use Illuminate\Support\Facades\Log;
use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;

class PushNotificationService
{
    /**
     * The WebPush instance.
     *
     * @var \Minishlink\WebPush\WebPush
     */
    protected $webPush;

    /**
     * Create a new service instance.
     */
    public function __construct()
    {
        $auth = [
            'VAPID' => [
                'subject' => 'mailto:' . config('mail.from.address'),
                'publicKey' => config('services.push_notifications.public_key'),
                'privateKey' => config('services.push_notifications.private_key'),
            ],
        ];

        $this->webPush = new WebPush($auth);
    }

    /**
     * Send a push notification to a specific subscription.
     *
     * @param PushSubscription $subscription
     * @param array $data
     * @return bool
     */
    public function sendToSubscription(PushSubscription $subscription, array $data): bool
    {
        try {
            $webPushSubscription = Subscription::create([
                'endpoint' => $subscription->endpoint,
                'publicKey' => $subscription->public_key,
                'authToken' => $subscription->auth_token,
                'contentEncoding' => $subscription->content_encoding ?? 'aes128gcm',
            ]);

            $this->webPush->queueNotification(
                $webPushSubscription,
                json_encode($data)
            );

            $results = $this->webPush->flush();

            foreach ($results as $report) {
                if ($report->isSuccess()) {
                    return true;
                }

                $endpoint = $report->getEndpoint();
                $status = $report->getResponse()->getStatusCode();

                // If the subscription is no longer valid, delete it
                if ($status === 404 || $status === 410) {
                    $subscription->delete();
                }

                Log::warning('Failed to send push notification', [
                    'endpoint' => $endpoint,
                    'status' => $status,
                    'reason' => $report->getReason(),
                ]);
            }

            return false;
        } catch (\Exception $e) {
            Log::error('Error sending push notification', [
                'endpoint' => $subscription->endpoint,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Send a push notification to a user.
     *
     * @param \App\Models\User|\App\Models\Client $notifiable
     * @param array $data
     * @return int Number of successful notifications sent
     */
    public function sendToNotifiable($notifiable, array $data): int
    {
        $successCount = 0;

        foreach ($notifiable->pushSubscriptions as $subscription) {
            if ($this->sendToSubscription($subscription, $data)) {
                $successCount++;
            }
        }

        return $successCount;
    }
}
