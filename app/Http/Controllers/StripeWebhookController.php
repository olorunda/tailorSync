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
                case 'charge.succeeded':
                case 'payment_intent.succeeded':
                    $this->handleSuccessfulPayment($event->data->object, $event->type);
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
        $businessDetail = $this->findBusinessDetail($subscription->id, $subscription, 'customer.subscription.updated');

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

        // Try to update plan if present in metadata
        $planKey = $subscription->metadata['plan_key'] ?? $subscription->metadata->plan_key ?? null;
        if ($planKey) {
            $businessDetail->subscription_plan = $planKey;
        }

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
        $businessDetail = $this->findBusinessDetail($subscription->id, $subscription, 'customer.subscription.deleted');

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
     * Handle successful payment event (invoice, charge, or payment intent).
     *
     * @param object $object
     * @param string $eventType
     * @return void
     */
    protected function handleSuccessfulPayment($object, $eventType)
    {
        $invoiceId = $object->invoice ?? null;
        if ($eventType === 'invoice.payment_succeeded') {
            $invoiceId = $object->id;
        }

        if (!$invoiceId) {
            Log::warning("No invoice ID found for event: {$eventType}");
            return;
        }

        $businessDetail = $this->findBusinessDetail(null, $object, $eventType);

        if (!$businessDetail) {
            Log::warning("Business detail not found for successful payment event: {$eventType}");
            return;
        }

        // Ensure subscription is active after successful payment
        $businessDetail->subscription_active = true;

        // Retrieve invoice from Stripe to find subscription ID and details
        $stripeInvoice = $this->stripeApiRequest("invoices/{$invoiceId}");
        if ($stripeInvoice && !isset($stripeInvoice['error'])) {
            $subscriptionId = $stripeInvoice['subscription'] ?? null;
            if ($subscriptionId) {
                $businessDetail->subscription_code = $subscriptionId;

                // Retrieve subscription from Stripe to get period dates and metadata
                $stripeSubscription = $this->stripeApiRequest("subscriptions/{$subscriptionId}");
                if ($stripeSubscription && !isset($stripeSubscription['error'])) {
                    $businessDetail->subscription_end_date = isset($stripeSubscription['current_period_end'])
                        ? date('Y-m-d H:i:s', $stripeSubscription['current_period_end'])
                        : null;
                    $planKey = $stripeSubscription['metadata']['plan_key'] ?? null;
                    if ($planKey) {
                        $businessDetail->subscription_plan = $planKey;
                    }
                }
            }
        }

        $businessDetail->save();

        Log::info("Subscription payment succeeded via webhook ({$eventType})", [
            'business_id' => $businessDetail->id,
            'invoice_id' => $invoiceId,
            'subscription_code' => $businessDetail->subscription_code
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

        $businessDetail = $this->findBusinessDetail($invoice->subscription, $invoice, 'invoice.payment_failed');

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
    }

    /**
     * Helper to perform signed requests to Stripe API.
     *
     * @param string $endpoint
     * @return array|null
     */
    protected function stripeApiRequest($endpoint)
    {
        $secretKey = config('services.payment.subscription.stripe.secret_key');
        if (!$secretKey) {
            Log::error('Stripe secret key not configured in webhook controller');
            return null;
        }

        $url = "https://api.stripe.com/v1/" . ltrim($endpoint, '/');

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_USERPWD, $secretKey . ":");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        if ($err) {
            Log::error('Stripe API Error in Webhook: ' . $err);
            return null;
        }

        return json_decode($response, true);
    }

    /**
     * Find business detail by various identifiers.
     *
     * @param string|null $subscriptionId
     * @param object|null $eventObject
     * @param string|null $eventType
     * @return BusinessDetail|null
     */
    protected function findBusinessDetail($subscriptionId, $eventObject = null, $eventType = null)
    {
        // 1. First, search by subscription_code in DB
        if ($subscriptionId) {
            $businessDetail = BusinessDetail::where('subscription_code', $subscriptionId)
                ->where('subscription_payment_method', 'stripe')
                ->first();
            if ($businessDetail) {
                return $businessDetail;
            }
        }

        // 2. If not found by subscription_code, try to retrieve subscription from Stripe to get metadata
        if ($subscriptionId) {
            Log::info('Subscription not found in DB. Retrieving subscription from Stripe: ' . $subscriptionId);
            $stripeSubscription = $this->stripeApiRequest("subscriptions/{$subscriptionId}");
            if ($stripeSubscription && !isset($stripeSubscription['error'])) {
                // Check if user_id is in subscription metadata
                $userId = $stripeSubscription['metadata']['user_id'] ?? null;
                if ($userId) {
                    $businessDetail = BusinessDetail::where('user_id', $userId)->first();
                    if ($businessDetail) {
                        Log::info('Found business detail via Stripe subscription metadata user_id', [
                            'user_id' => $userId,
                            'business_id' => $businessDetail->id
                        ]);
                        // Update subscription_code so subsequent lookups are fast
                        $businessDetail->subscription_code = $subscriptionId;
                        $businessDetail->subscription_payment_method = 'stripe';
                        $businessDetail->save();
                        return $businessDetail;
                    }
                }

                // If not found by user_id, check reference in metadata
                $reference = $stripeSubscription['metadata']['reference'] ?? null;
                if ($reference) {
                    $parts = explode('_', $reference);
                    if (count($parts) >= 3 && $parts[0] === 'sub') {
                        $userId = $parts[2];
                        $businessDetail = BusinessDetail::where('user_id', $userId)->first();
                        if ($businessDetail) {
                            Log::info('Found business detail via Stripe subscription metadata reference', [
                                'reference' => $reference,
                                'user_id' => $userId,
                                'business_id' => $businessDetail->id
                            ]);
                            $businessDetail->subscription_code = $subscriptionId;
                            $businessDetail->subscription_payment_method = 'stripe';
                            $businessDetail->save();
                            return $businessDetail;
                        }
                    }
                }
            }
        }

        // 3. Try to locate via invoice ID if available
        $invoiceId = $eventObject->invoice ?? null;
        if ($eventType === 'invoice.payment_succeeded' && $eventObject) {
            $invoiceId = $eventObject->id;
        }

        if (!$subscriptionId && $invoiceId) {
            Log::info('No subscription ID provided. Retrieving invoice from Stripe: ' . $invoiceId);
            $stripeInvoice = $this->stripeApiRequest("invoices/{$invoiceId}");
            if ($stripeInvoice && !isset($stripeInvoice['error'])) {
                $subId = $stripeInvoice['subscription'] ?? null;
                if ($subId) {
                    // Recursively lookup using the retrieved subscription ID
                    return $this->findBusinessDetail($subId, $eventObject, $eventType);
                }
            }
        }

        // 4. Fallback: Search by customer email
        $email = null;
        if ($eventObject) {
            if (isset($eventObject->customer_email)) {
                $email = $eventObject->customer_email;
            } elseif (isset($eventObject->billing_details->email)) {
                $email = $eventObject->billing_details->email;
            } elseif (isset($eventObject->email)) {
                $email = $eventObject->email;
            }
        }

        // If we still don't have email but have customer ID, fetch customer from Stripe
        $customerId = $eventObject->customer ?? null;
        if (!$email && $customerId) {
            Log::info('Retrieving customer from Stripe: ' . $customerId);
            $stripeCustomer = $this->stripeApiRequest("customers/{$customerId}");
            if ($stripeCustomer && !isset($stripeCustomer['error'])) {
                $email = $stripeCustomer['email'] ?? null;
            }
        }

        if ($email) {
            $user = User::where('email', $email)->first();
            if ($user && $user->businessDetail) {
                Log::info('Found business detail via customer email', [
                    'email' => $email,
                    'business_id' => $user->businessDetail->id
                ]);

                // Associate subscription ID if available
                if ($subscriptionId) {
                    $businessDetail = $user->businessDetail;
                    $businessDetail->subscription_code = $subscriptionId;
                    $businessDetail->subscription_payment_method = 'stripe';
                    $businessDetail->save();
                }

                return $user->businessDetail;
            }
        }

        return null;
    }
}
