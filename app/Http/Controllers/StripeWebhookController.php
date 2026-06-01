<?php

namespace App\Http\Controllers;

use App\Models\BusinessDetail;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Stripe\Event;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;

class StripeWebhookController extends Controller
{
    /**
     * Handle incoming Stripe webhook events.
     *
     * @param Request $request
     * @return Response
     */
    public function handleWebhook(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $webhookSecret = config('services.payment.subscription.stripe.webhook_secret');

        if (!$webhookSecret) {
            Log::error('Stripe webhook secret not configured');
            return response('Webhook secret not configured', 500);
        }

        try {
            // Verify the webhook signature
            $event = Webhook::constructEvent($payload, $sigHeader, $webhookSecret);
        } catch (SignatureVerificationException $e) {
            Log::error('Stripe webhook signature verification failed: ' . $e->getMessage());
            return response('Invalid signature', 400);
        } catch (Exception $e) {
            Log::error('Stripe webhook error: ' . $e->getMessage());
            return response('Webhook error', 400);
        }

        // Log the webhook event
        Log::info('Stripe webhook received', [
            'event_type' => $event->type,
            'event_id' => $event->id
        ]);

        try {
            // Handle the event
            switch ($event->type) {
                case 'customer.subscription.updated':
                    $this->handleSubscriptionUpdated($event->data->object);
                    break;

                case 'customer.subscription.deleted':
                    $this->handleSubscriptionDeleted($event->data->object);
                    break;

                case 'invoice.payment_succeeded':
                    $this->handleInvoicePaymentSucceeded($event->data->object);
                    break;

                case 'invoice.payment_failed':
                    $this->handleInvoicePaymentFailed($event->data->object);
                    break;

                default:
                    Log::info('Unhandled Stripe webhook event type: ' . $event->type);
            }

            return response('Webhook handled successfully', 200);
        } catch (Exception $e) {
            Log::error('Error handling Stripe webhook: ' . $e->getMessage(), [
                'event_type' => $event->type,
                'event_id' => $event->id,
                'error' => $e->getMessage()
            ]);
            return response('Internal server error', 500);
        }
    }

    /**
     * Handle subscription updated event.
     *
     * @param object $subscription
     * @return void
     */
    protected function handleSubscriptionUpdated($subscription)
    {
        $businessDetail = $this->findBusinessDetailBySubscriptionId($subscription->id);

        if (!$businessDetail) {
            Log::warning('Business detail not found for subscription: ' . $subscription->id);
            return;
        }

        $oldStatus = $businessDetail->subscription_active;
        $newStatus = in_array($subscription->status, ['active', 'trialing']);

        // Update subscription status
        $businessDetail->subscription_active = $newStatus;
        $businessDetail->subscription_end_date = isset($subscription->current_period_end)
            ? date('Y-m-d H:i:s', $subscription->current_period_end)
            : null;

        $businessDetail->save();

        Log::info('Subscription status updated via webhook', [
            'subscription_id' => $subscription->id,
            'business_id' => $businessDetail->id,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'stripe_status' => $subscription->status
        ]);
    }

    /**
     * Handle subscription deleted/canceled event.
     *
     * @param object $subscription
     * @return void
     */
    protected function handleSubscriptionDeleted($subscription)
    {
        $businessDetail = $this->findBusinessDetailBySubscriptionId($subscription->id);

        if (!$businessDetail) {
            Log::warning('Business detail not found for subscription: ' . $subscription->id);
            return;
        }

        // Mark subscription as inactive
        $businessDetail->subscription_active = false;
        $businessDetail->save();

        Log::info('Subscription canceled via webhook', [
            'subscription_id' => $subscription->id,
            'business_id' => $businessDetail->id,
            'canceled_at' => isset($subscription->canceled_at)
                ? date('Y-m-d H:i:s', $subscription->canceled_at)
                : now()
        ]);
    }

    /**
     * Handle successful invoice payment.
     *
     * @param object $invoice
     * @return void
     */
    protected function handleInvoicePaymentSucceeded($invoice)
    {
        if (!$invoice->subscription) {
            return; // Not a subscription invoice
        }

        $businessDetail = $this->findBusinessDetailBySubscriptionId($invoice->subscription);

        if (!$businessDetail) {
            Log::warning('Business detail not found for subscription: ' . $invoice->subscription);
            return;
        }

        // Ensure subscription is active after successful payment
        $businessDetail->subscription_active = true;
        $businessDetail->save();

        Log::info('Subscription payment succeeded via webhook', [
            'subscription_id' => $invoice->subscription,
            'business_id' => $businessDetail->id,
            'invoice_id' => $invoice->id,
            'amount_paid' => $invoice->amount_paid / 100 // Convert from cents
        ]);
    }

    /**
     * Handle failed invoice payment.
     *
     * @param object $invoice
     * @return void
     */
    protected function handleInvoicePaymentFailed($invoice)
    {
        if (!$invoice->subscription) {
            return; // Not a subscription invoice
        }

        $businessDetail = $this->findBusinessDetailBySubscriptionId($invoice->subscription);

        if (!$businessDetail) {
            Log::warning('Business detail not found for subscription: ' . $invoice->subscription);
            return;
        }

        Log::warning('Subscription payment failed via webhook', [
            'subscription_id' => $invoice->subscription,
            'business_id' => $businessDetail->id,
            'invoice_id' => $invoice->id,
            'attempt_count' => $invoice->attempt_count ?? 1
        ]);

        // Note: We don't immediately deactivate the subscription here
        // as Stripe may retry the payment. The subscription will be
        // deactivated when Stripe sends a subscription.deleted event
    }

    /**
     * Find business detail by Stripe subscription ID.
     *
     * @param string $subscriptionId
     * @return BusinessDetail|null
     */
    protected function findBusinessDetailBySubscriptionId($subscriptionId)
    {
        return BusinessDetail::where('subscription_code', $subscriptionId)
            ->where('subscription_payment_method', 'stripe')
            ->first();
    }
}
