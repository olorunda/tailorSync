<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;

class PublicOrderController extends Controller
{
    /**
     * Display the order and invoice details for public viewing.
     *
     * @param  string  $hash
     * @return \Illuminate\View\View
     */
    public function show($hash)
    {
        // Log the received hash
        \Log::info('Received hash for public order view', ['hash' => $hash]);

        // Decode the hash if it's URL-encoded
        $decodedHash = urldecode($hash);
        \Log::info('Decoded hash', ['decodedHash' => $decodedHash]);

        // Find the order that matches the hash using the optimized method
        $order = $this->findOrderByHash($decodedHash);

        // If a matching order is found, log it
        if ($order) {
            \Log::info('Found matching order', ['order_id' => $order->id]);
        }

        // If no order is found, return 404
        if (!$order) {
            \Log::warning('No matching order found for hash', ['hash' => $hash, 'decodedHash' => $decodedHash]);
            abort(404, 'Order not found');
        }

        // Load the order relationships
        $order->load('client', 'design');

        // Find the invoice for this order
        $invoice = Invoice::where('order_id', $order->id)->first();

        // Return the view with the order and invoice
        return view('public.order', [
            'order' => $order,
            'invoice' => $invoice,
        ]);
    }

    /**
     * Generate an encrypted hash for an order ID using Crypt with a salt.
     *
     * @param  int  $orderId
     * @return string
     */
    public static function generateHash($orderId)
    {
        return Crypt::encrypt($orderId);
    }

    /**
     * Find an order by its encrypted hash.
     *
     * @param  string  $hash
     * @return \App\Models\Order|null
     */
    private function findOrderByHash($hash)
    {
        try {
            // Decrypt the hash to get the order ID
            $orderId = Crypt::decrypt($hash);

            // Find the order with the decrypted ID
            return Order::find($orderId);
        } catch (\Exception $e) {
            \Log::error('Error decrypting hash', [
                'hash' => $hash,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }
}
