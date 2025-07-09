<?php

namespace App\Services;

use App\Models\BusinessDetail;
use App\Models\User;
use App\Services\SubscriptionService;
use Exception;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    protected $user;
    protected $businessDetail;
    protected $gateway;
    protected $settings;
    protected $isSubscriptionPayment = false;

//    // Paystack subscription plan codes
//    const PAYSTACK_PLAN_CODES = [
//        'basic' => 'PLN_xeoss63j95oyltt', // To be set in the Paystack dashboard or via API
//        'premium' => 'PLN_3pd70p5zxhjvzns', // To be set in the Paystack dashboard or via API
//    ];

    const PAYSTACK_PLAN_CODES = [
        'basic' => 'PLN_43eyp3qcgyktjee', // To be set in the Paystack dashboard or via API
        'premium' => 'PLN_d79vgtirvzdbmjw', // To be set in the Paystack dashboard or via API
    ];

    /**
     * Create a new payment service instance.
     *
     * @param User $user
     * @param bool $isSubscriptionPayment Whether this is a subscription payment
     * @return void
     */
    public function __construct(User $user, bool $isSubscriptionPayment = false)
    {

        $this->user = $user;
        $this->businessDetail = $user->businessDetail;
        $this->isSubscriptionPayment = $isSubscriptionPayment;

        if ($isSubscriptionPayment) {
            // For subscription payments, use platform-level payment gateway
            $this->gateway = Config::get('services.payment.subscription.gateway', 'paystack');
            $this->settings = [
                'paystack' => Config::get('services.payment.subscription.paystack'),
                'flutterwave' => Config::get('services.payment.subscription.flutterwave'),
                'stripe' => Config::get('services.payment.subscription.stripe'),
            ];

            if (empty($this->settings[$this->gateway]['secret_key'])) {
                throw new Exception('Subscription payment gateway is not properly configured.');
            }
            return;
        }
            // For regular payments, use business payment gateway with subscription restrictions
            if (!$this->businessDetail || !$this->businessDetail->payment_enabled) {
                throw new Exception('Payment processing is not enabled for this business.');
            }

            $this->gateway = $this->businessDetail->default_payment_gateway;
            $this->settings = $this->businessDetail->payment_settings;

            if ($this->gateway === 'none' || empty($this->settings)) {
                throw new Exception('No payment gateway is configured.');
            }

            // Check if the subscription plan allows the selected payment gateway
            $planKey = $this->businessDetail->subscription_plan ?? 'free';
            $plan = SubscriptionService::getPlan($planKey);

            if (!$plan) {
                throw new Exception('Invalid subscription plan.');
            }

            // Check if subscription is active (except for free plan)
            if ($planKey !== 'free' && !SubscriptionService::isActive($this->businessDetail)) {
                throw new Exception('Your subscription is inactive. Please renew your subscription to process payments.');
            }

            // Check if the selected gateway is allowed for the subscription plan
            $allowedGateways = $plan['features']['payment_gateways'] ?? ['paystack'];

            if (!in_array($this->gateway, $allowedGateways)) {
                throw new Exception("Your current subscription plan does not support the {$this->gateway} payment gateway. Please upgrade your plan or switch to a supported gateway: " . implode(', ', $allowedGateways));
            }

    }

    /**
     * Create a new payment service instance for subscription payments.
     *
     * @param User $user
     * @return PaymentService
     */
    public static function forSubscription(User $user): PaymentService
    {
        return new self($user, true);
    }

    /**
     * Check if the current gateway is Paystack.
     *
     * @return bool
     */
    public function isPaystackGateway(): bool
    {
        return $this->gateway === 'paystack';
    }

    /**
     * Initialize a payment transaction.
     *
     * @param float $amount
     * @param string $reference
     * @param string $email
     * @param string $callbackUrl
     * @param array $metadata
     * @return array
     */
    public function initializePayment($amount, $reference, $email, $callbackUrl, $metadata = [])
    {
        switch ($this->gateway) {
            case 'paystack':
                return $this->initializePaystackPayment($amount, $reference, $email, $callbackUrl, $metadata);
            case 'flutterwave':
                return $this->initializeFlutterwavePayment($amount, $reference, $email, $callbackUrl, $metadata);
            case 'stripe':
                return $this->initializeStripePayment($amount, $reference, $email, $callbackUrl, $metadata);
            default:
                throw new Exception('Unsupported payment gateway.');
        }
    }

    /**
     * Verify a payment transaction.
     *
     * @param string $reference
     * @return array
     */
    public function verifyPayment($reference)
    {
        switch ($this->gateway) {
            case 'paystack':
                return $this->verifyPaystackPayment($reference);
            case 'flutterwave':
                return $this->verifyFlutterwavePayment($reference);
            case 'stripe':
                return $this->verifyStripePayment($reference);
            default:
                throw new Exception('Unsupported payment gateway.');
        }
    }

    /**
     * Initialize a Paystack payment.
     *
     * @param float $amount
     * @param string $reference
     * @param string $email
     * @param string $callbackUrl
     * @param array $metadata
     * @return array
     */
    protected function initializePaystackPayment($amount, $reference, $email, $callbackUrl, $metadata = [])
    {
        $publicKey = $this->settings['paystack']['public_key'] ?? null;
        $secretKey = $this->settings['paystack']['secret_key'] ?? null;

        if (!$publicKey || !$secretKey) {
            throw new Exception('Paystack API keys are not configured.');
        }

        // Convert amount to kobo (Paystack uses the smallest currency unit)
        $amount = $amount * 100;

        $url = "https://api.paystack.co/transaction/initialize";
        $fields = [
            'email' => $email,
            'amount' => round($amount),
            'reference' => $reference,
            'callback_url' => $callbackUrl,
            'metadata' => $metadata
        ];

        // Set currency if specified in metadata
        if (isset($metadata['currency'])) {
            $fields['currency'] = $metadata['currency'];
        }

        $headers = [
            'Authorization: Bearer ' . $secretKey,
            'Content-Type: application/json',
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        if ($err) {
            Log::error('Paystack API Error: ' . $err);
            throw new Exception('Error communicating with Paystack: ' . $err);
        }

        $result = json_decode($response, true);

        if (!$result['status']) {
            Log::error('Paystack Payment Error: ' . ($result['message'] ?? 'Unknown error'));
            throw new Exception('Error initializing Paystack payment: ' . ($result['message'] ?? 'Unknown error'));
        }

        return [
            'success' => true,
            'redirect_url' => $result['data']['authorization_url'],
            'reference' => $reference,
            'gateway' => 'paystack'
        ];
    }

    /**
     * Verify a Paystack payment.
     *
     * @param string $reference
     * @return array
     */
    protected function verifyPaystackPayment($reference)
    {
        $secretKey = $this->settings['paystack']['secret_key'] ?? null;

        if (!$secretKey) {
            throw new Exception('Paystack API keys are not configured.');
        }

        $url = "https://api.paystack.co/transaction/verify/" . rawurlencode($reference);
        $headers = [
            'Authorization: Bearer ' . $secretKey,
            'Content-Type: application/json',
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        if ($err) {
            Log::error('Paystack API Error: ' . $err);
            throw new Exception('Error communicating with Paystack: ' . $err);
        }

        $result = json_decode($response, true);

        if (!$result['status']) {
            Log::error('Paystack Verification Error: ' . ($result['message'] ?? 'Unknown error'));
            throw new Exception('Error verifying Paystack payment: ' . ($result['message'] ?? 'Unknown error'));
        }

        $paymentData = $result['data'];
        $success = $paymentData['status'] === 'success';

        return [
            'success' => $success,
            'reference' => $reference,
            'gateway' => 'paystack',
            'amount' => $paymentData['amount'] / 100, // Convert from kobo to naira
            'currency' => $paymentData['currency'],
            'payment_date' => $paymentData['paid_at'],
            'metadata' => $paymentData['metadata'] ?? [],
            'gateway_response' => $paymentData['gateway_response'] ?? '',
            'raw_response' => $paymentData
        ];
    }

    /**
     * Initialize a Flutterwave payment.
     *
     * @param float $amount
     * @param string $reference
     * @param string $email
     * @param string $callbackUrl
     * @param array $metadata
     * @return array
     */
    protected function initializeFlutterwavePayment($amount, $reference, $email, $callbackUrl, $metadata = [])
    {
        $publicKey = $this->settings['flutterwave']['public_key'] ?? null;
        $secretKey = $this->settings['flutterwave']['secret_key'] ?? null;

        if (!$publicKey || !$secretKey) {
            throw new Exception('Flutterwave API keys are not configured.');
        }

        $url = "https://api.flutterwave.com/v3/payments";
        $fields = [
            'tx_ref' => $reference,
            'amount' => $amount,
            'currency' => isset($metadata['currency']) ? $metadata['currency'] : 'NGN',
            'redirect_url' => $callbackUrl,
            'customer' => [
                'email' => $email
            ],
            'meta' => $metadata,
            'customizations' => [
                'title' => $this->businessDetail->business_name . ' Payment',
                'description' => 'Payment for services'
            ]
        ];

        $headers = [
            'Authorization: Bearer ' . $secretKey,
            'Content-Type: application/json',
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        if ($err) {
            Log::error('Flutterwave API Error: ' . $err);
            throw new Exception('Error communicating with Flutterwave: ' . $err);
        }

        $result = json_decode($response, true);

        if ($result['status'] !== 'success') {
            Log::error('Flutterwave Payment Error: ' . ($result['message'] ?? 'Unknown error'));
            throw new Exception('Error initializing Flutterwave payment: ' . ($result['message'] ?? 'Unknown error'));
        }

        return [
            'success' => true,
            'redirect_url' => $result['data']['link'],
            'reference' => $reference,
            'gateway' => 'flutterwave'
        ];
    }

    /**
     * Verify a Flutterwave payment.
     *
     * @param string $reference
     * @return array
     */
    protected function verifyFlutterwavePayment($reference)
    {
        $secretKey = $this->settings['flutterwave']['secret_key'] ?? null;

        if (!$secretKey) {
            throw new Exception('Flutterwave API keys are not configured.');
        }

        $url = "https://api.flutterwave.com/v3/transactions/verify_by_reference?tx_ref=" . rawurlencode($reference);
        $headers = [
            'Authorization: Bearer ' . $secretKey,
            'Content-Type: application/json',
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        if ($err) {
            Log::error('Flutterwave API Error: ' . $err);
            throw new Exception('Error communicating with Flutterwave: ' . $err);
        }

        $result = json_decode($response, true);

        if ($result['status'] !== 'success') {
            Log::error('Flutterwave Verification Error: ' . ($result['message'] ?? 'Unknown error'));
            throw new Exception('Error verifying Flutterwave payment: ' . ($result['message'] ?? 'Unknown error'));
        }

        $paymentData = $result['data'];
        $success = $paymentData['status'] === 'successful';

        return [
            'success' => $success,
            'reference' => $reference,
            'gateway' => 'flutterwave',
            'amount' => $paymentData['amount'],
            'currency' => $paymentData['currency'],
            'payment_date' => $paymentData['created_at'],
            'metadata' => $paymentData['meta'] ?? [],
            'gateway_response' => $paymentData['processor_response'] ?? '',
            'raw_response' => $paymentData
        ];
    }

    /**
     * Initialize a Stripe payment.
     *
     * @param float $amount
     * @param string $reference
     * @param string $email
     * @param string $callbackUrl
     * @param array $metadata
     * @return array
     */
    protected function initializeStripePayment($amount, $reference, $email, $callbackUrl, $metadata = [])
    {
        $publicKey = $this->settings['stripe']['public_key'] ?? null;
        $secretKey = $this->settings['stripe']['secret_key'] ?? null;

        if (!$publicKey || !$secretKey) {
            throw new Exception('Stripe API keys are not configured.');
        }

        // Convert amount to cents (Stripe uses the smallest currency unit)
        $amount = $amount * 100;

        $url = "https://api.stripe.com/v1/checkout/sessions";
        $fields = [
            'payment_method_types[]' => 'card',
            'line_items[0][price_data][currency]' => 'usd',
            'line_items[0][price_data][unit_amount]' => $amount,
            'line_items[0][price_data][product_data][name]' => $this->businessDetail->business_name . ' Payment',
            'line_items[0][quantity]' => 1,
            'mode' => 'payment',
            'success_url' => $callbackUrl . '?session_id={CHECKOUT_SESSION_ID}&reference=' . $reference,
            'cancel_url' => $callbackUrl . '?canceled=true&reference=' . $reference,
            'client_reference_id' => $reference,
            'customer_email' => $email,
            'metadata[reference]' => $reference
        ];

        // Add custom metadata
        foreach ($metadata as $key => $value) {
            $fields['metadata[' . $key . ']'] = $value;
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fields));
        curl_setopt($ch, CURLOPT_USERPWD, $secretKey . ":");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        if ($err) {
            Log::error('Stripe API Error: ' . $err);
            throw new Exception('Error communicating with Stripe: ' . $err);
        }

        $result = json_decode($response, true);

        if (isset($result['error'])) {
            Log::error('Stripe Payment Error: ' . ($result['error']['message'] ?? 'Unknown error'));
            throw new Exception('Error initializing Stripe payment: ' . ($result['error']['message'] ?? 'Unknown error'));
        }

        return [
            'success' => true,
            'redirect_url' => $result['url'],
            'reference' => $reference,
            'gateway' => 'stripe',
            'session_id' => $result['id']
        ];
    }

    /**
     * Verify a Stripe payment.
     *
     * @param string $reference
     * @return array
     */
    protected function verifyStripePayment($reference)
    {
        $secretKey = $this->settings['stripe']['secret_key'] ?? null;

        if (!$secretKey) {
            throw new Exception('Stripe API keys are not configured.');
        }

        // First, find the session ID from the reference
        $url = "https://api.stripe.com/v1/checkout/sessions?limit=1";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url . '&client_reference_id=' . rawurlencode($reference));
        curl_setopt($ch, CURLOPT_USERPWD, $secretKey . ":");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        if ($err) {
            Log::error('Stripe API Error: ' . $err);
            throw new Exception('Error communicating with Stripe: ' . $err);
        }

        $result = json_decode($response, true);

        if (isset($result['error'])) {
            Log::error('Stripe Session Error: ' . ($result['error']['message'] ?? 'Unknown error'));
            throw new Exception('Error retrieving Stripe session: ' . ($result['error']['message'] ?? 'Unknown error'));
        }

        if (empty($result['data'])) {
            throw new Exception('No Stripe session found for reference: ' . $reference);
        }

        $session = $result['data'][0];
        $sessionId = $session['id'];

        // Now retrieve the payment intent
        $paymentIntentId = $session['payment_intent'];
        if (!$paymentIntentId) {
            throw new Exception('No payment intent found for Stripe session: ' . $sessionId);
        }

        $url = "https://api.stripe.com/v1/payment_intents/" . $paymentIntentId;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_USERPWD, $secretKey . ":");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        if ($err) {
            Log::error('Stripe API Error: ' . $err);
            throw new Exception('Error communicating with Stripe: ' . $err);
        }

        $paymentIntent = json_decode($response, true);

        if (isset($paymentIntent['error'])) {
            Log::error('Stripe Payment Intent Error: ' . ($paymentIntent['error']['message'] ?? 'Unknown error'));
            throw new Exception('Error retrieving Stripe payment intent: ' . ($paymentIntent['error']['message'] ?? 'Unknown error'));
        }

        $success = $paymentIntent['status'] === 'succeeded';

        return [
            'success' => $success,
            'reference' => $reference,
            'gateway' => 'stripe',
            'amount' => $paymentIntent['amount'] / 100, // Convert from cents to dollars
            'currency' => $paymentIntent['currency'],
            'payment_date' => date('Y-m-d H:i:s', $paymentIntent['created']),
            'metadata' => $paymentIntent['metadata'] ?? [],
            'gateway_response' => $paymentIntent['status'],
            'raw_response' => $paymentIntent
        ];
    }

    /**
     * Create a Paystack subscription plan.
     *
     * @param string $name Plan name
     * @param float $amount Plan amount in the smallest currency unit (kobo for NGN)
     * @param string $interval Plan interval (daily, weekly, monthly, quarterly, biannually, annually)
     * @param string $description Plan description
     * @return array
     */
    public function createPaystackPlan($name, $amount, $interval = 'monthly', $description = '')
    {
        $secretKey = $this->settings['paystack']['secret_key'] ?? null;

        if (!$secretKey) {
            throw new Exception('Paystack API keys are not configured.');
        }

        $url = "https://api.paystack.co/plan";
        $fields = [
            'name' => $name,
            'amount' => round($amount),
            'interval' => $interval,
            'description' => $description,
        ];

        $headers = [
            'Authorization: Bearer ' . $secretKey,
            'Content-Type: application/json',
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        if ($err) {
            Log::error('Paystack API Error: ' . $err);
            throw new Exception('Error communicating with Paystack: ' . $err);
        }

        $result = json_decode($response, true);

        if (!$result['status']) {
            Log::error('Paystack Plan Creation Error: ' . ($result['message'] ?? 'Unknown error'));
            throw new Exception('Error creating Paystack plan: ' . ($result['message'] ?? 'Unknown error'));
        }

        return [
            'success' => true,
            'plan_id' => $result['data']['id'],
            'plan_code' => $result['data']['plan_code'],
            'name' => $result['data']['name'],
            'amount' => $result['data']['amount'] / 100, // Convert from kobo to naira
            'interval' => $result['data']['interval'],
            'raw_response' => $result['data']
        ];
    }

    /**
     * Initialize a Paystack subscription.
     *
     * @param string $planCode Paystack plan code
     * @param string $email Customer email
     * @param string $reference Transaction reference
     * @param string $callbackUrl Callback URL
     * @param array $metadata Additional metadata
     * @return array
     */
    public function initializePaystackSubscription($planCode, $email, $reference, $callbackUrl, $metadata = [])
    {
        $secretKey = $this->settings['paystack']['secret_key'] ?? null;
        $publicKey = $this->settings['paystack']['public_key'] ?? null;

        if (!$secretKey || !$publicKey) {
            throw new Exception('Paystack API keys are not configured.');
        }

        // First, initialize a transaction to get authorization code
        $url = "https://api.paystack.co/transaction/initialize";
        $fields = [
            'email' => $email,
            'amount' => 100, // Charge a small amount (1 NGN) to get authorization
            'reference' => $reference,
            'callback_url' => $callbackUrl,
            'metadata' => array_merge($metadata, ['is_subscription' => true, 'plan_code' => $planCode]),
            'plan' => $planCode // This will make it a subscription payment
        ];

        // Set currency if specified in metadata
        if (isset($metadata['currency'])) {
            $fields['currency'] = $metadata['currency'];
        }

        $headers = [
            'Authorization: Bearer ' . $secretKey,
            'Content-Type: application/json',
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        if ($err) {
            Log::error('Paystack API Error: ' . $err);
            throw new Exception('Error communicating with Paystack: ' . $err);
        }

        $result = json_decode($response, true);

        if (!$result['status']) {
            Log::error('Paystack Subscription Error: ' . ($result['message'] ?? 'Unknown error'));
            throw new Exception('Error initializing Paystack subscription: ' . ($result['message'] ?? 'Unknown error'));
        }

        return [
            'success' => true,
            'redirect_url' => $result['data']['authorization_url'],
            'reference' => $reference,
            'gateway' => 'paystack',
            'subscription_code' => $result['data']['subscription_code'] ?? null,
            'raw_response' => $result['data']
        ];
    }

    /**
     * Verify a Paystack subscription payment.
     *
     * @param string $reference Transaction reference
     * @return array
     */
    public function verifyPaystackSubscriptionPayment($reference)
    {
        $secretKey = $this->settings['paystack']['secret_key'] ?? null;

        if (!$secretKey) {
            throw new Exception('Paystack API keys are not configured.');
        }

        // First verify the transaction
        $verificationResult = $this->verifyPaystackPayment($reference);

        if (!$verificationResult['success']) {
            return $verificationResult;
        }

        // Extract metadata from the verification result
        $metadata = $verificationResult['metadata'] ?? [];
        $planCode = $metadata['plan_code'] ?? null;

        if (!$planCode || !isset($metadata['is_subscription']) || !$metadata['is_subscription']) {
            // This is not a subscription payment
            return $verificationResult;
        }

        // Get the authorization code from the transaction
        $authorizationCode = $verificationResult['raw_response']['authorization']['authorization_code'] ?? null;

        if (!$authorizationCode) {
            throw new Exception('No authorization code found in Paystack transaction.');
        }

        // Create a subscription using the authorization code
        $url = "https://api.paystack.co/subscription";
        $fields = [
            'customer' => $verificationResult['raw_response']['customer']['email'],
            'plan' => $planCode,
            'authorization' => $authorizationCode,
        ];

        $headers = [
            'Authorization: Bearer ' . $secretKey,
            'Content-Type: application/json',
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        if ($err) {
            Log::error('Paystack API Error: ' . $err);
            throw new Exception('Error communicating with Paystack: ' . $err);
        }

        $result = json_decode($response, true);

        if (!$result['status']) {
            $errorMessage = $result['message'] ?? 'Unknown error';
            Log::error('Paystack Subscription Creation Error: ' . $errorMessage);

            // Check if the error is because the subscription already exists
            if (stripos($errorMessage, 'This subscription is already') !== false) {
                // Subscription already exists, try to fetch it
                $customerEmail = auth()->user()->email;// $verificationResult['raw_response']['customer']['email'];

                // Fetch existing subscriptions for this customer
                $existingSubscription = $this->fetchExistingSubscription($customerEmail, $planCode, $secretKey);

                if ($existingSubscription) {
                    // Add subscription details to the verification result
                    $verificationResult['subscription_code'] = $existingSubscription['subscription_code'];
                    $verificationResult['subscription_status'] = $existingSubscription['status'];
                    $verificationResult['next_payment_date'] = $existingSubscription['next_payment_date'] ?? null;
                    $verificationResult['subscription_raw_response'] = $existingSubscription;

                    return $verificationResult;
                }
            }

            // If we couldn't handle the error or it's a different error, throw exception
            throw new Exception('Error creating Paystack subscription: ' . $errorMessage);
        }

        // Add subscription details to the verification result
        $verificationResult['subscription_code'] = $result['data']['subscription_code'];
        $verificationResult['subscription_status'] = $result['data']['status'];
        $verificationResult['next_payment_date'] = $result['data']['next_payment_date'];
        $verificationResult['subscription_raw_response'] = $result['data'];

        return $verificationResult;
    }

    /**
     * Fetch existing subscription for a customer and plan.
     *
     * @param string $customerEmail Customer email
     * @param string $planCode Paystack plan code
     * @param string $secretKey Paystack secret key
     * @return array|null
     */
    protected function fetchExistingSubscription($customerEmail, $planCode, $secretKey)
    {
        // First, list all subscriptions for this customer
        $url = "https://api.paystack.co/subscription?email=" . ($customerEmail) . "&perPage=100";

        $headers = [
            'Authorization: Bearer ' . $secretKey,
            'Content-Type: application/json',
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        if ($err) {
            Log::error('Paystack API Error: ' . $err);
            return null;
        }

        $result = json_decode($response, true);

        if (!$result['status'] || empty($result['data'])) {
            return null;
        }

        // Find the subscription with the matching plan code
        foreach ($result['data'] as $subscription) {
            if ($subscription['plan']['plan_code'] === $planCode) {
                return $subscription;
            }
        }

        return null;
    }


    private function getEmailToken($secretKey,$subscriptionCode)
    {
        $response= Http::withToken($secretKey)->get('https://api.paystack.co/subscription/'.$subscriptionCode);
        return  $response->json()['data']['email_token'] ?? '';

    }
    /**
     * Cancel a Paystack subscription.
     *
     * @param string $subscriptionCode Paystack subscription code
     * @param string $email Customer email
     * @return array
     */
    public function cancelPaystackSubscription($subscriptionCode, $email)
    {
        $secretKey = $this->settings['paystack']['secret_key'] ?? null;


        if (!$secretKey) {
            throw new Exception('Paystack API keys are not configured.');
        }

        $url = "https://api.paystack.co/subscription/disable";
        $fields = [
            'code' => $subscriptionCode,
            'token' => $this->getEmailToken($secretKey,$subscriptionCode)
        ];

        $headers = [
            'Authorization: Bearer ' . $secretKey,
            "Cache-Control: no-cache",
            "Content-Type: application/x-www-form-urlencoded"
        ];


        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fields));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        if ($err) {
            Log::error('Paystack API Error: ' . $err);
            throw new Exception('Error communicating with Paystack: ' . $err);
        }

        $result = json_decode($response, true);

        if (!$result['status']) {
            Log::error('Paystack Subscription Cancellation Error: ' . ($result['message'] ?? 'Unknown error'));
            throw new Exception('Error cancelling Paystack subscription: ' . ($result['message'] ?? 'Unknown error'));
        }

        return [
            'success' => true,
            'message' => 'Subscription cancelled successfully',
            'raw_response' => $result['data']
        ];
    }
}
