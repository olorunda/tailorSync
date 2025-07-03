<?php

namespace App\Http\Controllers;

use App\Models\BusinessDetail;
use App\Services\PaymentService;
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

        // Define plan hierarchy for the view
        $planHierarchy = ['free', 'basic', 'premium'];

        // Create a function to check if a plan is a downgrade
        $isDowngrade = function($currentPlan, $newPlan) use ($planHierarchy) {
            $currentPlanIndex = array_search($currentPlan, $planHierarchy);
            $newPlanIndex = array_search($newPlan, $planHierarchy);

            // If current plan is not found, assume it's the lowest tier
            if ($currentPlanIndex === false) {
                $currentPlanIndex = 0;
            }

            // If new plan is not found, assume it's the lowest tier
            if ($newPlanIndex === false) {
                $newPlanIndex = 0;
            }

            // It's a downgrade if the new plan index is lower than the current plan index
            return $newPlanIndex < $currentPlanIndex;
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
        if ($isActive && $this->isDowngrade($currentPlan, $planKey)) {
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
     * @param Request $request
     * @param string $planKey
     * @return \Illuminate\Http\RedirectResponse
     */
    public function processPayment(Request $request, $planKey)
    {
        $user = Auth::user();
        $businessDetail = $user->businessDetail;
        $plan = SubscriptionService::getPlan($planKey);

        if (!$plan) {
            return redirect()->route('subscriptions.index')
                ->with('error', 'Invalid subscription plan selected.');
        }

        try {
            // Generate a unique reference
            $reference = 'sub_' . $planKey . '_' . $user->id . '_' . time();

            // Initialize payment using the subscription-specific payment service
            // This bypasses the user's subscription restrictions
            $paymentService = PaymentService::forSubscription($user);
            $callbackUrl = route('subscriptions.callback', ['reference' => $reference]);

            $metadata = [
                'plan_key' => $planKey,
                'user_id' => $user->id,
                'business_name' => $businessDetail->business_name,
                'is_subscription_payment' => true,
            ];

            $paymentData = $paymentService->initializePayment(
                $plan['price'],
                $reference,
                $user->email,
                $callbackUrl,
                $metadata
            );

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
            // Extract plan and user ID from reference (format: sub_plankey_userid_timestamp)
            $parts = explode('_', $reference);
            if (count($parts) < 3 || $parts[0] !== 'sub') {
                throw new Exception('Invalid payment reference format.');
            }

            $planKey = $parts[1];
            $userId = $parts[2];
            $user = \App\Models\User::findOrFail($userId);

            // Verify the payment using the subscription-specific payment service
            // This bypasses the user's subscription restrictions
            $paymentService = PaymentService::forSubscription($user);
            $paymentData = $paymentService->verifyPayment($reference);

            if ($paymentData['success']) {
                // Create subscription
                SubscriptionService::subscribe(
                    $user,
                    $planKey,
                    $paymentData['gateway'],
                    $reference
                );

                return redirect()->route('subscriptions.index')
                    ->with('success', 'Your subscription has been activated successfully!');
            } else {
                return redirect()->route('subscriptions.index')
                    ->with('error', 'Payment verification failed. Please contact support.');
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
        $businessDetail = $user->businessDetail;

        // Set subscription to inactive
        $businessDetail->subscription_active = false;
        $businessDetail->save();

        return redirect()->route('subscriptions.index')
            ->with('success', 'Your subscription has been cancelled. You will be downgraded to the Free plan at the end of your billing period.');
    }

    /**
     * Check if changing from current plan to new plan is a downgrade
     *
     * @param string $currentPlan
     * @param string $newPlan
     * @return bool
     */
    private function isDowngrade($currentPlan, $newPlan)
    {
        // Define plan hierarchy (higher index = higher tier)
        $planHierarchy = ['free', 'basic', 'premium'];

        $currentPlanIndex = array_search($currentPlan, $planHierarchy);
        $newPlanIndex = array_search($newPlan, $planHierarchy);

        // If current plan is not found, assume it's the lowest tier
        if ($currentPlanIndex === false) {
            $currentPlanIndex = 0;
        }

        // If new plan is not found, assume it's the lowest tier
        if ($newPlanIndex === false) {
            $newPlanIndex = 0;
        }

        // It's a downgrade if the new plan index is lower than the current plan index
        return $newPlanIndex < $currentPlanIndex;
    }
}
