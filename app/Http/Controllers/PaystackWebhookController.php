<?php

namespace App\Http\Controllers;

use App\Models\BusinessDetail;
use App\Models\User;
use App\Services\SubscriptionService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaystackWebhookController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // Disable CSRF verification for webhook endpoints
        $this->middleware('web')->except('handleWebhook');
    }
    /**
     * Handle Paystack webhook events.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function handleWebhook(Request $request)
    {
        // Verify that the request is from Paystack
        $paystackSignature = $request->header('x-paystack-signature');
        $payload = $request->getContent();

        // Get the Paystack secret key from config
        $secretKey = config('services.payment.subscription.paystack.secret_key');

        // Verify the signature
        $computedSignature = hash_hmac('sha512', $payload, $secretKey);

        if ($paystackSignature !== $computedSignature) {
            Log::warning('Invalid Paystack webhook signature');
            return response('Invalid signature', 401);
        }

        // Parse the payload
        $event = json_decode($payload, true);

        // Log the event for debugging
        Log::info('Paystack webhook event received', ['event' => $event['event']]);

        // Handle different event types
        switch ($event['event']) {
            case 'subscription.create':
                return $this->handleSubscriptionCreate($event['data']);

            case 'subscription.disable':
                return $this->handleSubscriptionDisable($event['data']);

            case 'subscription.enable':
                return $this->handleSubscriptionEnable($event['data']);

            case 'charge.success':
                return $this->handleChargeSuccess($event['data']);

            case 'invoice.payment_failed':
                return $this->handlePaymentFailed($event['data']);

            default:
                Log::info('Unhandled Paystack webhook event', ['event' => $event['event']]);
                return response('Webhook received', 200);
        }
    }

    /**
     * Handle subscription.create event.
     *
     * @param array $data
     * @return \Illuminate\Http\Response
     */
    protected function handleSubscriptionCreate($data)
    {
        Log::info('Subscription created', $data);

        // The subscription code
        $subscriptionCode = $data['subscription_code'];

        // Find the business detail with this subscription code
        $businessDetail = BusinessDetail::where('subscription_code', $subscriptionCode)->first();

        if (!$businessDetail) {
            Log::warning('Business detail not found for subscription code: ' . $subscriptionCode);
            return response('Webhook processed', 200);
        }

        // Update the subscription details
        $businessDetail->subscription_active = true;
        $businessDetail->subscription_end_date = Carbon::parse($data['next_payment_date']);
        $businessDetail->save();

        return response('Webhook processed', 200);
    }

    /**
     * Handle subscription.disable event.
     *
     * @param array $data
     * @return \Illuminate\Http\Response
     */
    protected function handleSubscriptionDisable($data)
    {
        Log::info('Subscription disabled', $data);

        // The subscription code
        $subscriptionCode = $data['subscription_code'];

        // Find the business detail with this subscription code
        $businessDetail = BusinessDetail::where('subscription_code', $subscriptionCode)->first();

        if (!$businessDetail) {
            Log::warning('Business detail not found for subscription code: ' . $subscriptionCode);
            return response('Webhook processed', 200);
        }

        // Update the subscription details
        $businessDetail->subscription_active = false;
        $businessDetail->save();

        return response('Webhook processed', 200);
    }

    /**
     * Handle subscription.enable event.
     *
     * @param array $data
     * @return \Illuminate\Http\Response
     */
    protected function handleSubscriptionEnable($data)
    {
        Log::info('Subscription enabled', $data);

        // The subscription code
        $subscriptionCode = $data['subscription_code'];

        // Find the business detail with this subscription code
        $businessDetail = BusinessDetail::where('subscription_code', $subscriptionCode)->first();

        if (!$businessDetail) {
            Log::warning('Business detail not found for subscription code: ' . $subscriptionCode);
            return response('Webhook processed', 200);
        }

        // Update the subscription details
        $businessDetail->subscription_active = true;
        $businessDetail->subscription_end_date = Carbon::parse($data['next_payment_date']);
        $businessDetail->save();

        return response('Webhook processed', 200);
    }

    /**
     * Handle charge.success event.
     *
     * @param array $data
     * @return \Illuminate\Http\Response
     */
    protected function handleChargeSuccess($data)
    {
        Log::info('Charge success', $data);

        // Check if this is a subscription payment
        if (!isset($data['metadata']['is_subscription']) || !$data['metadata']['is_subscription']) {
            return response('Webhook processed', 200);
        }

        // The subscription code
        $subscriptionCode = $data['metadata']['subscription_code'] ?? null;

        if (!$subscriptionCode) {
            Log::warning('No subscription code found in charge.success event');
            return response('Webhook processed', 200);
        }

        // Find the business detail with this subscription code
        $businessDetail = BusinessDetail::where('subscription_code', $subscriptionCode)->first();

        if (!$businessDetail) {
            Log::warning('Business detail not found for subscription code: ' . $subscriptionCode);
            return response('Webhook processed', 200);
        }

        // Update the subscription details
        $businessDetail->subscription_active = true;
        $businessDetail->subscription_end_date = Carbon::now()->addDays(30); // Default to 30 days if next_payment_date is not available
        $businessDetail->save();

        return response('Webhook processed', 200);
    }

    /**
     * Handle invoice.payment_failed event.
     *
     * @param array $data
     * @return \Illuminate\Http\Response
     */
    protected function handlePaymentFailed($data)
    {
        Log::info('Payment failed', $data);

        // The subscription code
        $subscriptionCode = $data['subscription']['subscription_code'] ?? null;

        if (!$subscriptionCode) {
            Log::warning('No subscription code found in invoice.payment_failed event');
            return response('Webhook processed', 200);
        }

        // Find the business detail with this subscription code
        $businessDetail = BusinessDetail::where('subscription_code', $subscriptionCode)->first();

        if (!$businessDetail) {
            Log::warning('Business detail not found for subscription code: ' . $subscriptionCode);
            return response('Webhook processed', 200);
        }

        // Log the payment failure but don't disable the subscription yet
        // Paystack will retry the payment and only disable the subscription after multiple failures
        Log::warning('Payment failed for subscription: ' . $subscriptionCode);

        return response('Webhook processed', 200);
    }
}
