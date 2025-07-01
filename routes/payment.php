<?php

use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Payment Routes
|--------------------------------------------------------------------------
|
| Here is where you can register payment routes for your application.
|
*/

// Invoice payment routes
Route::get('/invoices/{invoiceId}/pay', [PaymentController::class, 'payInvoice'])
    ->name('payment.invoice.pay')
    ->middleware(['auth']);

Route::get('/payment/invoice/callback/{reference}', [PaymentController::class, 'handleInvoicePaymentCallback'])
    ->name('payment.invoice.callback');

// Store order payment routes
Route::get('/orders/{orderId}/pay', [PaymentController::class, 'payOrder'])
    ->name('payment.order.pay');

Route::get('/payment/order/callback/{reference}', [PaymentController::class, 'handleOrderPaymentCallback'])
    ->name('payment.order.callback');
