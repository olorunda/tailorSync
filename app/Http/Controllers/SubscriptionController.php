<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProcessSubscriptionPaymentRequest;
use App\Services\SubscriptionService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SubscriptionController extends Controller
{
    /**
     * Display the subscription plans page.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $user = Auth::user();
        $businessDetail = $user->businessDetail;
        $plans = SubscriptionService::getPlans();
        $currentPlan = $businessDetail->subscription_plan ?? 'free';
        $isActive = SubscriptionService::isActive($businessDetail);

        // Use the isDowngrade method from the service
        $isDowngrade = function($currentPlan, $newPlan) {
            return SubscriptionService::isDowngrade($currentPlan, $newPlan);
        };

        return view('subscriptions.index', compact('plans', 'currentPlan', 'isActive', 'businessDetail', 'isDowngrade'));
    }

    /**
     * Show the subscription checkout page for a specific plan.
     *
     * @param string $planKey
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function checkout($planKey)
    {
        $user = Auth::user();
        $businessDetail = $user->businessDetail;
        $plan = SubscriptionService::getPlan($planKey);
        $currentPlan = $businessDetail->subscription_plan ?? 'free';
        $isActive = SubscriptionService::isActive($businessDetail);

        if (!$plan) {
            return redirect()->route('subscriptions.index')
                ->with('error', 'Invalid subscription plan selected.');
        }

        // Check if user is trying to downgrade to a lower plan while having an active subscription
        if ($isActive && SubscriptionService::isDowngrade($currentPlan, $planKey)) {
            return redirect()->route('subscriptions.index')
                ->with('error', "You cannot downgrade to a lower plan while having an active subscription. Please wait until your current subscription expires or cancel it first.");
        }

        // If it's the free plan, subscribe directly without payment
        if ($planKey === 'free') {
            try {
                SubscriptionService::subscribe($user, 'free');
                return redirect()->route('subscriptions.index')
                    ->with('success', 'You have successfully subscribed to the Free plan.');
            } catch (Exception $e) {
                Log::error('Free subscription error: ' . $e->getMessage());
                return redirect()->route('subscriptions.index')
                    ->with('error', 'An error occurred while subscribing to the Free plan: ' . $e->getMessage());
            }
        }

        return view('subscriptions.checkout', compact('plan', 'planKey', 'businessDetail'));
    }

    /**
     * Process the subscription payment.
     *
     * @param ProcessSubscriptionPaymentRequest $request
     * @param string $planKey
     * @return \Illuminate\Http\RedirectResponse
     */
    public function processPayment(ProcessSubscriptionPaymentRequest $request, $planKey)
    {
        $user = Auth::user();

        try {
            // Initialize payment using the service
            $result = SubscriptionService::initializePayment($user, $planKey);
            $paymentData = $result['payment_data'];

            if ($paymentData['success']) {
                // Redirect to payment gateway
                return redirect($paymentData['redirect_url']);
            } else {
                return redirect()->route('subscriptions.checkout', $planKey)
                    ->with('error', 'Failed to initialize payment. Please try again.');
            }
        } catch (Exception $e) {
            Log::error('Subscription payment initialization error: ' . $e->getMessage());
            return redirect()->route('subscriptions.checkout', $planKey)
                ->with('error', 'An error occurred while initializing payment: ' . $e->getMessage());
        }
    }

    /**
     * Handle the subscription payment callback.
     *
     * @param Request $request
     * @param string $reference
     * @return \Illuminate\Http\RedirectResponse
     */
    public function handleCallback(Request $request, $reference)
    {
        try {
            // Verify payment and subscribe using the service
            $result = SubscriptionService::verifyPaymentAndSubscribe($reference);

            if ($result['success']) {
                return redirect()->route('subscriptions.index')
                    ->with('success', $result['message']);
            } else {
                return redirect()->route('subscriptions.index')
                    ->with('error', $result['message']);
            }
        } catch (Exception $e) {
            Log::error('Subscription callback error: ' . $e->getMessage());
            return redirect()->route('subscriptions.index')
                ->with('error', 'An error occurred while processing your subscription: ' . $e->getMessage());
        }
    }

    /**
     * Cancel the current subscription.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function cancel()
    {
        $user = Auth::user();

        // Cancel subscription using the service
        SubscriptionService::cancelSubscription($user);

        return redirect()->route('subscriptions.index')
            ->with('success', 'Your subscription has been cancelled. You will be downgraded to the Free plan at the end of your billing period.');
    }
}
