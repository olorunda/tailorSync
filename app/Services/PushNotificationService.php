<?php

namespace App\Services;

use App\Models\PushSubscription;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PushNotificationService
{
    /**
     * VAPID keys for Web Push
     */
    protected $publicKey;
    protected $privateKey;

    /**
     * Create a new service instance.
     */
    public function __construct()
    {
        $this->publicKey = config('services.push_notifications.public_key');
        $this->privateKey = config('services.push_notifications.private_key');
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
            $webPushData = $subscription->toWebPushFormat();

            // Create the notification payload
            $payload = json_encode($data);

            // Generate the necessary headers
            $headers = $this->getHeaders($subscription->endpoint);

            // Send the push notification
            $response = Http::withHeaders($headers)
                ->withBody($payload, 'application/json')
                ->post($subscription->endpoint);

            // Check if the notification was sent successfully
            if ($response->successful()) {
                return true;
            }

            // If the subscription is no longer valid, delete it
            if ($response->status() === 404 || $response->status() === 410) {
                $subscription->delete();
            }

            Log::warning('Failed to send push notification', [
                'endpoint' => $subscription->endpoint,
                'status' => $response->status(),
                'response' => $response->body(),
            ]);

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

    /**
     * Generate the headers for the Web Push Protocol.
     *
     * @param string $endpoint
     * @return array
     */
    protected function getHeaders(string $endpoint): array
    {
        $headers = [
            'Content-Type' => 'application/json',
            'TTL' => 2419200, // 4 weeks
        ];

        // Add VAPID headers if keys are configured
        if ($this->publicKey && $this->privateKey) {
            $vapidHeaders = $this->getVapidHeaders($endpoint);
            $headers = array_merge($headers, $vapidHeaders);
        }

        return $headers;
    }

    /**
     * Generate VAPID headers for Web Push Protocol.
     *
     * @param string $endpoint
     * @return array
     */
    protected function getVapidHeaders(string $endpoint): array
    {
        $parsedUrl = parse_url($endpoint);
        $audience = $parsedUrl['scheme'] . '://' . $parsedUrl['host'];

        // Current time and expiration (24 hours)
        $currentTime = time();
        $expirationTime = $currentTime + 86400;

        // JWT Header
        $header = json_encode([
            'typ' => 'JWT',
            'alg' => 'ES256'
        ]);

        // JWT Payload
        $payload = json_encode([
            'aud' => $audience,
            'exp' => $expirationTime,
            'sub' => 'mailto:' . config('mail.from.address')
        ]);

        // Encode header and payload
        $base64UrlHeader = $this->base64UrlEncode($header);
        $base64UrlPayload = $this->base64UrlEncode($payload);

        // Create signature using ECDSA with P-256 curve and SHA-256 hash
        $unsignedToken = $base64UrlHeader . '.' . $base64UrlPayload;
        $signature = $this->createSignature($unsignedToken);
        $base64UrlSignature = $this->base64UrlEncode($signature);

        // Create JWT token
        $token = $unsignedToken . '.' . $base64UrlSignature;

        return [
            'Authorization' => 'vapid t=' . $token . ', k=' . $this->publicKey,
        ];
    }

    /**
     * Base64 URL encode a string
     *
     * @param string $data
     * @return string
     */
    protected function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Create a signature for the JWT token
     *
     * @param string $unsignedToken
     * @return string
     */
    protected function createSignature(string $unsignedToken): string
    {
        // For a proper implementation, you would use openssl_sign with the ECDSA algorithm
        // This is a simplified version that should be replaced with a proper implementation
        // or by using the web-push-php/web-push library

        $privateKeyBinary = base64_decode($this->privateKey);
        $result = '';

        if (function_exists('openssl_sign')) {
            $pkey = openssl_pkey_get_private($privateKeyBinary);
            openssl_sign($unsignedToken, $result, $pkey, OPENSSL_ALGO_SHA256);
            openssl_pkey_free($pkey);
        } else {
            // Fallback if openssl_sign is not available
            // This is not secure and should be replaced with a proper implementation
            $result = hash_hmac('sha256', $unsignedToken, $privateKeyBinary, true);
        }

        return $result;
    }
}
