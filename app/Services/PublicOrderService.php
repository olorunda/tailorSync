<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

class PublicOrderService
{
    /**
     * Generate an encrypted hash for an order ID using Crypt.
     *
     * @param int $orderId
     * @return string
     */
    public function generateHash(int $orderId): string
    {
        return Crypt::encrypt($orderId);
    }

    /**
     * Find an order by its encrypted hash.
     *
     * @param string $hash
     * @return Order|null
     */
    public function findOrderByHash(string $hash): ?Order
    {
        try {
            // Decrypt the hash to get the order ID
            $orderId = Crypt::decrypt($hash);

            // Find the order with the decrypted ID
            return Order::find($orderId);
        } catch (\Exception $e) {
            Log::error('Error decrypting hash', [
                'hash' => $hash,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Get the invoice for an order.
     *
     * @param Order $order
     * @return Invoice|null
     */
    public function getInvoiceForOrder(Order $order): ?Invoice
    {
        return Invoice::where('order_id', $order->id)->first();
    }

    /**
     * Get the currency symbol for the store owner.
     *
     * @param int $userId
     * @return string
     */
    public function getCurrencySymbol(int $userId): string
    {
        $user = User::find($userId);
        return $user ? $user->getCurrencySymbol() : '$';
    }

    /**
     * Load relationships for an order.
     *
     * @param Order $order
     * @return Order
     */
    public function loadOrderRelationships(Order $order): Order
    {
        return $order->load('client', 'design', 'orderItems.product', 'products');
    }
}
