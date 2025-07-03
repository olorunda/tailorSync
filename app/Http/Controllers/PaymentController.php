<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PlatformFee;
use App\Services\PaymentService;
use App\Services\SubscriptionService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    /**
     * Handle payment callback for invoices.
     *
     * @param Request $request
     * @param string $reference
     * @return \Illuminate\Http\RedirectResponse
     */
    public function handleInvoicePaymentCallback(Request $request, $reference)
    {
        try {
            // Extract invoice ID from reference (format: inv_123_timestamp)
            $parts = explode('_', $reference);
            if (count($parts) < 2 || $parts[0] !== 'inv') {
                throw new Exception('Invalid payment reference format.');
            }

            $invoiceId = $parts[1];
            $invoice = Invoice::findOrFail($invoiceId);

            // Verify the payment
            $paymentService = new PaymentService($invoice->user);
            $paymentData = $paymentService->verifyPayment($reference);

            if ($paymentData['success']) {
                // Create payment record
                $payment = new Payment([
                    'user_id' => $invoice->user_id,
                    'client_id' => $invoice->client_id,
                    'description' => 'Payment for Invoice #' . $invoice->invoice_number,
                    'amount' => $paymentData['amount'],
                    'currency' => $paymentData['currency'],
                    'payment_date' => $paymentData['payment_date'],
                    'payment_method' => $paymentData['gateway'],
                    'reference' => $reference,
                    'status' => 'completed',
                    'metadata' => json_encode($paymentData['metadata']),
                    'gateway_response' => $paymentData['gateway_response'],
                ]);

                // Associate payment with invoice
                $invoice->payments()->save($payment);

                // Calculate and record platform fee
                $businessDetail = $invoice->user->businessDetail;
                $feeAmount = SubscriptionService::calculatePlatformFee($businessDetail, $paymentData['amount']);

                // Create platform fee record
                PlatformFee::create([
                    'payment_id' => $payment->id,
                    'user_id' => $invoice->user_id,
                    'amount' => $paymentData['amount'],
                    'fee_amount' => $feeAmount,
                    'fee_percentage' => SubscriptionService::getTransactionFeePercentage($businessDetail->subscription_plan ?? 'free'),
                    'currency' => $paymentData['currency'],
                    'payment_reference' => $reference,
                    'status' => 'pending',
                ]);

                // Update invoice status if fully paid
                $totalPaid = $invoice->payments()->where('status', 'completed')->sum('amount');
                if ($totalPaid >= $invoice->total_amount) {
                    $invoice->status = 'paid';
                    $invoice->save();
                }

                return redirect()->route('invoices.show', $invoice->id)
                    ->with('success', 'Payment successful! Your invoice has been updated.');
            } else {
                return redirect()->route('invoices.show', $invoice->id)
                    ->with('error', 'Payment verification failed. Please contact support.');
            }
        } catch (Exception $e) {
            Log::error('Payment callback error: ' . $e->getMessage());
            return redirect()->route('invoices.index')
                ->with('error', 'An error occurred while processing your payment: ' . $e->getMessage());
        }
    }

    /**
     * Handle payment callback for store orders.
     *
     * @param Request $request
     * @param string $reference
     * @return \Illuminate\Http\RedirectResponse
     */
    public function handleOrderPaymentCallback(Request $request, $reference)
    {
        try {
            // Extract order ID from reference (format: ord_123_timestamp)
            $parts = explode('_', $reference);
            if (count($parts) < 2 || $parts[0] !== 'ord') {
                throw new Exception('Invalid payment reference format.');
            }

            $orderId = $parts[1];
            $order = Order::findOrFail($orderId);

            // Get the store owner (business owner)
            $storeOwner = $order->user;

            // Verify the payment
            $paymentService = new PaymentService($storeOwner);
            $paymentData = $paymentService->verifyPayment($reference);

            if ($paymentData['success']) {
                // Create payment record
                $payment = new Payment([
                    'user_id' => $storeOwner->id,
                    'client_id' => $order->client_id,
                    'description' => 'Payment for Order #' . $order->order_number,
                    'amount' => $paymentData['amount'],
                    'currency' => $paymentData['currency'],
                    'payment_date' => $paymentData['payment_date'],
                    'payment_method' => $paymentData['gateway'],
                    'reference' => $reference,
                    'status' => 'completed',
                    'metadata' => json_encode($paymentData['metadata']),
                    'gateway_response' => $paymentData['gateway_response'],
                ]);

                // Associate payment with order
                $order->payments()->save($payment);

                // Calculate and record platform fee
                $businessDetail = $storeOwner->businessDetail;
                $feeAmount = SubscriptionService::calculatePlatformFee($businessDetail, $paymentData['amount']);

                // Create platform fee record
                PlatformFee::create([
                    'payment_id' => $payment->id,
                    'user_id' => $storeOwner->id,
                    'amount' => $paymentData['amount'],
                    'fee_amount' => $feeAmount,
                    'fee_percentage' => SubscriptionService::getTransactionFeePercentage($businessDetail->subscription_plan ?? 'free'),
                    'currency' => $paymentData['currency'],
                    'payment_reference' => $reference,
                    'status' => 'pending',
                ]);

                // Update order status if fully paid
                $totalPaid = $order->payments()->where('status', 'completed')->sum('amount');
                if ($totalPaid >= $order->total_amount) {
                    $order->payment_status = 'paid';
                    $order->status = 'processing';
                    $order->save();
                }

                // Clear the cart session after successful payment
                \Illuminate\Support\Facades\Session::forget('cart_session_id');

                // Get the store slug from the order's user's business detail
                $storeSlug = $order->user->businessDetail->store_slug;
                return redirect()->route('storefront.order.confirmation', ['slug' => $storeSlug, 'order' => $order->id])
                    ->with('success', 'Payment successful! Your order has been confirmed.');
            } else {
                // Get the store slug from the order's user's business detail
                $storeSlug = $order->user->businessDetail->store_slug;
                return redirect()->route('storefront.order.confirmation', ['slug' => $storeSlug, 'order' => $order->id])
                    ->with('error', 'Payment verification failed. Please contact the store owner.');
            }
        } catch (Exception $e) {
            Log::error('Order payment callback error: ' . $e->getMessage());

            // Try to get the store slug from the order's user's business detail if possible
            try {
                if (isset($order) && $order) {
                    $storeSlug = $order->user->businessDetail->store_slug;
                    return redirect()->route('storefront.index', ['slug' => $storeSlug])
                        ->with('error', 'An error occurred while processing your payment: ' . $e->getMessage());
                }
            } catch (Exception $innerException) {
                Log::error('Failed to get store slug: ' . $innerException->getMessage());
            }

            // Fallback to home page if we can't get the slug or order
            return redirect()->route('home')
                ->with('error', 'An error occurred while processing your payment. Please try again later.');
        }
    }

    /**
     * Initialize payment for an invoice.
     *
     * @param Request $request
     * @param int $invoiceId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function payInvoice(Request $request, $invoiceId)
    {
        try {
            $invoice = Invoice::findOrFail($invoiceId);

            // Check if invoice is already paid
            if ($invoice->status === 'paid') {
                return redirect()->route('invoices.show', $invoice->id)
                    ->with('info', 'This invoice has already been paid.');
            }

            // Generate a unique reference
            $reference = 'inv_' . $invoice->id . '_' . time();

            // Get customer email
            $email = $invoice->client->email;
            if (empty($email)) {
                throw new Exception('Client email is required for payment processing.');
            }

            // Calculate amount to pay (remaining balance)
            $totalPaid = $invoice->payments()->where('status', 'completed')->sum('amount');
            $amountToPay = $invoice->total_amount - $totalPaid;

            // Initialize payment
            $paymentService = new PaymentService($invoice->user);
            $callbackUrl = route('payment.invoice.callback', ['reference' => $reference]);

            $metadata = [
                'invoice_id' => $invoice->id,
                'client_id' => $invoice->client_id,
                'client_name' => $invoice->client->name,
                'business_name' => $invoice->user->businessDetail->business_name,
            ];

            $paymentData = $paymentService->initializePayment(
                $amountToPay,
                $reference,
                $email,
                $callbackUrl,
                $metadata
            );

            if ($paymentData['success']) {
                // Redirect to payment gateway
                return redirect($paymentData['redirect_url']);
            } else {
                return redirect()->route('invoices.show', $invoice->id)
                    ->with('error', 'Failed to initialize payment. Please try again.');
            }
        } catch (Exception $e) {
            Log::error('Payment initialization error: ' . $e->getMessage());
            return redirect()->route('invoices.show', $invoiceId)
                ->with('error', 'An error occurred while initializing payment: ' . $e->getMessage());
        }
    }

    /**
     * Initialize payment for a store order.
     *
     * @param Request $request
     * @param int $orderId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function payOrder(Request $request, $orderId)
    {
        try {
            $order = Order::findOrFail($orderId);

            // Check if order is already paid
            if ($order->payment_status === 'paid') {
                // Get the store slug from the order's user's business detail
                $storeSlug = $order->user->businessDetail->store_slug;
                return redirect()->route('storefront.order.confirmation', ['slug' => $storeSlug, 'order' => $order->id])
                    ->with('info', 'This order has already been paid.');
            }

            // Generate a unique reference
            $reference = 'ord_' . $order->id . '_' . time();

            // Get customer email from the client relationship
            $email = $order->client->email;
            if (empty($email)) {
                throw new Exception('Customer email is required for payment processing.');
            }

            // Get the store owner (business owner)
            $storeOwner = $order->user;

            // Calculate amount to pay (remaining balance)
            $totalPaid = $order->payments()->where('status', 'completed')->sum('amount');
            $amountToPay = $order->total_amount - $totalPaid;

            // Initialize payment
            $paymentService = new PaymentService($storeOwner);
            $callbackUrl = route('payment.order.callback', ['reference' => $reference]);

            $metadata = [
                'order_id' => $order->id,
                'customer_name' => $order->client->name,
                'customer_email' => $order->client->email,
                'business_name' => $order->user->businessDetail->business_name,
            ];

            $paymentData = $paymentService->initializePayment(
                $amountToPay,
                $reference,
                $email,
                $callbackUrl,
                $metadata
            );

            if ($paymentData['success']) {
                // Redirect to payment gateway
                return redirect($paymentData['redirect_url']);
            } else {
                // Get the store slug from the order's user's business detail
                $storeSlug = $order->user->businessDetail->store_slug;
                return redirect()->route('storefront.checkout', ['slug' => $storeSlug])
                    ->with('error', 'Failed to initialize payment. Please try again.');
            }
        } catch (Exception $e) {
            Log::error('Order payment initialization error: ' . $e->getMessage());

            // Try to get the store slug from the order's user's business detail
            try {
                $storeSlug = $order->user->businessDetail->store_slug;
                return redirect()->route('storefront.checkout', ['slug' => $storeSlug])
                    ->with('error', 'An error occurred while initializing payment: ' . $e->getMessage());
            } catch (Exception $innerException) {
                // If we can't get the slug, log the error and redirect to home
                Log::error('Failed to get store slug: ' . $innerException->getMessage());
                // Redirect to home page with a generic error message
                return redirect()->route('home')
                    ->with('error', 'An error occurred while processing your payment. Please try again later.');
            }
        }
    }
}
