<?php

namespace App\Http\Controllers;

use App\Services\PublicOrderService;
use Illuminate\Support\Facades\Log;

class PublicOrderController extends Controller
{
    /**
     * The public order service instance.
     *
     * @var \App\Services\PublicOrderService
     */
    protected $publicOrderService;

    /**
     * Create a new controller instance.
     *
     * @param \App\Services\PublicOrderService $publicOrderService
     * @return void
     */
    public function __construct(PublicOrderService $publicOrderService)
    {
        $this->publicOrderService = $publicOrderService;
    }

    /**
     * Display the order and invoice details for public viewing.
     *
     * @param  string  $hash
     * @return \Illuminate\View\View
     */
    public function show($hash)
    {
//invoice without order
        if(str_contains($this->publicOrderService->decryptHash($hash),'_')){
            [$invoice_id,$type]=explode('_',$this->publicOrderService->decryptHash($hash));
            $invoice= $this->publicOrderService->getInvoice($invoice_id);
            return view('public.order', [
                'order' =>false,
                'invoice' => $invoice,
                'currencySymbol' => $this->publicOrderService->getCurrencySymbol($invoice->user_id),
            ]);
        }


        // Log the received hash
        Log::info('Received hash for public order view', ['hash' => $hash]);

        // Decode the hash if it's URL-encoded
        $decodedHash = urldecode($hash);
        Log::info('Decoded hash', ['decodedHash' => $decodedHash]);

        // Find the order that matches the hash
        $order = $this->publicOrderService->findOrderByHash($decodedHash);

        // If a matching order is found, log it
        if ($order) {
            Log::info('Found matching order', ['order_id' => $order->id]);
        }

        // If no order is found, return 404
        if (!$order) {
            Log::warning('No matching order found for hash', ['hash' => $hash, 'decodedHash' => $decodedHash]);
            abort(404, 'Order not found');
        }

        // Load the order relationships
        $order = $this->publicOrderService->loadOrderRelationships($order);

        // Find the invoice for this order
        $invoice = $this->publicOrderService->getInvoiceForOrder($order);

        // Get currency symbol
        $currencySymbol = $this->publicOrderService->getCurrencySymbol($order->user_id);

        // Return the view with the order and invoice
        return view('public.order', [
            'order' => $order,
            'invoice' => $invoice,
            'currencySymbol' => $currencySymbol,
        ]);
    }

    /**
     * Generate an encrypted hash for an order ID.
     *
     * @param  int  $orderId
     * @return string
     */
    public static function generateHash($order_or_invoice_Id)
    {

        // Create a new instance of the service since this is a static method
        $publicOrderService = app(PublicOrderService::class);
        return $publicOrderService->generateHash($order_or_invoice_Id);
    }
}
