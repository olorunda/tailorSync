<?php

namespace App\Observers;

use App\Models\Order;
use App\Notifications\OrderStatusNotification;

class OrderObserver
{
    // Store old status values temporarily
    protected static $oldStatusValues = [];

    /**
     * Handle the Order "updating" event.
     */
    public function updating(Order $order): void
    {
        // Check if the status is being changed
        if ($order->isDirty('status')) {
            $oldStatus = $order->getOriginal('status');

            // Store the old status in a static property instead of on the model
            self::$oldStatusValues[$order->id] = $oldStatus;
        }
    }

    /**
     * Handle the Order "updated" event.
     */
    public function updated(Order $order): void
    {
        // Check if the status was changed
        if ($order->wasChanged('status')) {
            // Get the old status from our static property
            $oldStatus = self::$oldStatusValues[$order->id] ?? null;

            // Clean up after use
            if (isset(self::$oldStatusValues[$order->id])) {
                unset(self::$oldStatusValues[$order->id]);
            }

            // If the order is cancelled, restore the product quantities
            if ($order->status === 'cancelled') {
                foreach ($order->orderItems as $orderItem) {
                    $product = $orderItem->product;
                    if ($product) {
                        $product->stock_quantity += $orderItem->quantity;
                        $product->save();
                    }
                }
            }

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
