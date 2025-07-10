<?php

namespace App\Traits;

use App\Models\PushSubscription;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasPushSubscriptions
{
    /**
     * Get all push subscriptions for the model.
     */
    public function pushSubscriptions(): MorphMany
    {
        return $this->morphMany(PushSubscription::class, 'subscribable');
    }

    /**
     * Create a new push subscription.
     *
     * @param array $subscription
     * @return \App\Models\PushSubscription
     */
    public function createPushSubscription(array $subscription): PushSubscription
    {
        return $this->pushSubscriptions()->create([
            'endpoint' => $subscription['endpoint'],
            'public_key' => $subscription['keys']['p256dh'] ?? null,
            'auth_token' => $subscription['keys']['auth'] ?? null,
            'content_encoding' => $subscription['contentEncoding'] ?? 'aes128gcm',
        ]);
    }

    /**
     * Delete a push subscription by endpoint.
     *
     * @param string $endpoint
     * @return bool
     */
    public function deletePushSubscription(string $endpoint): bool
    {
        return $this->pushSubscriptions()
            ->where('endpoint', $endpoint)
            ->delete() > 0;
    }

    /**
     * Delete all push subscriptions.
     *
     * @return bool
     */
    public function deleteAllPushSubscriptions(): bool
    {
        return $this->pushSubscriptions()->delete() > 0;
    }

    /**
     * Get a push subscription by endpoint.
     *
     * @param string $endpoint
     * @return \App\Models\PushSubscription|null
     */
    public function getPushSubscription(string $endpoint): ?PushSubscription
    {
        return $this->pushSubscriptions()
            ->where('endpoint', $endpoint)
            ->first();
    }

    /**
     * Check if the model has a push subscription with the given endpoint.
     *
     * @param string $endpoint
     * @return bool
     */
    public function hasPushSubscription(string $endpoint): bool
    {
        return $this->pushSubscriptions()
            ->where('endpoint', $endpoint)
            ->exists();
    }
}
