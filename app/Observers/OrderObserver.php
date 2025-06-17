<?php

namespace App\Observers;

use App\Models\Order;
use App\Notifications\OrderStatusNotification;

class OrderObserver
{
    /**
     * Handle the Order "updating" event.
     */
    public function updating(Order $order): void
    {
        // Check if the status is being changed
        if ($order->isDirty('status')) {
            $oldStatus = $order->getOriginal('status');

            // Store the old status to use in the notification
            $order->old_status = $oldStatus;
        }
    }

    /**
     * Handle the Order "updated" event.
     */
    public function updated(Order $order): void
    {
        // Check if the status was changed
        if ($order->wasChanged('status')) {
            $oldStatus = $order->old_status ?? null;

            // Notify the client
            if ($order->client) {
                $order->client->notify(new OrderStatusNotification($order, $oldStatus));
            }

            // Notify the user (tailor/business owner)
            if ($order->user) {
                $order->user->notify(new OrderStatusNotification($order, $oldStatus));
            }
        }
    }
}
