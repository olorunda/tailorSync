<?php

namespace App\Services;

use App\Models\BusinessDetail;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;

class SubscriptionService
{
    /**
     * Available subscription plans
     */
    const PLANS = [
        'free' => [
            'name' => 'Free',
            'price' => 0,
            'duration' => 30, // days
            'features' => [
                'max_products' => 0,
                'max_designs' => 5,
                'transaction_fee' => 0.0, // 0%
                'store_enabled' => false,
                'custom_domain' => false,
                'payment_gateways' => ['paystack'],
                'max_team_members' => 1,
                'appointments_enabled' => false,
                'public_appointments_enabled' => false,
                'tax_reports_enabled' => false,
                'payment_integration_enabled' => false,
            ],
        ],
        'basic' => [
            'name' => 'Basic',
            'price' => 2999,
            'duration' => 30, // days
            'features' => [
                'max_products' => 50,
                'max_designs' => 20,
                'transaction_fee' => 0.0, // 0%
                'store_enabled' => true,
                'custom_domain' => false,
                'payment_gateways' => ['paystack', 'flutterwave'],
                'max_team_members' => 5,
                'appointments_enabled' => true,
                'public_appointments_enabled' => true,
                'tax_reports_enabled' => false,
                'payment_integration_enabled' => true,
            ],
        ],
        'premium' => [
            'name' => 'Premium',
            'price' => 5999,
            'duration' => 30, // days
            'features' => [
                'max_products' => 'unlimited',
                'max_designs' => 'unlimited',
                'transaction_fee' => 0.0, // 0%
                'store_enabled' => true,
                'custom_domain' => true,
                'payment_gateways' => ['paystack', 'flutterwave', 'stripe'],
                'max_team_members' => 'unlimited',
                'appointments_enabled' => true,
                'public_appointments_enabled' => true,
                'tax_reports_enabled' => true,
                'payment_integration_enabled' => true,
            ],
        ],
    ];

    /**
     * Get all available subscription plans
     *
     * @return array
     */
    public static function getPlans()
    {
        return self::PLANS;
    }

    /**
     * Get a specific plan by its key
     *
     * @param string $planKey
     * @return array|null
     */
    public static function getPlan($planKey)
    {
        return self::PLANS[$planKey] ?? null;
    }

    /**
     * Get the transaction fee percentage for a given plan
     *
     * @param string $planKey
     * @return float
     */
    public static function getTransactionFeePercentage($planKey)
    {
        $plan = self::getPlan($planKey);
        return $plan ? $plan['features']['transaction_fee'] : 5.0; // Default to 5% if plan not found
    }

    /**
     * Create or update a subscription for a business
     *
     * @param User $user
     * @param string $planKey
     * @param string $paymentMethod
     * @param string $paymentId
     * @return BusinessDetail
     */
    public static function subscribe(User $user, $planKey, $paymentMethod = null, $paymentId = null)
    {
        $plan = self::getPlan($planKey);

        if (!$plan) {
            throw new Exception("Invalid subscription plan: {$planKey}");
        }

        $businessDetail = $user->businessDetail;

        if (!$businessDetail) {
            throw new Exception("User does not have business details");
        }

        $now = Carbon::now();
        $endDate = $now->copy()->addDays($plan['duration']);

        $businessDetail->subscription_plan = $planKey;
        $businessDetail->subscription_start_date = $now;
        $businessDetail->subscription_end_date = $endDate;
        $businessDetail->subscription_active = true;

        if ($paymentMethod) {
            $businessDetail->subscription_payment_method = $paymentMethod;
        }

        if ($paymentId) {
            $businessDetail->subscription_payment_id = $paymentId;
        }

        $businessDetail->save();

        return $businessDetail;
    }

    /**
     * Check if a subscription is active
     *
     * @param BusinessDetail $businessDetail
     * @return bool
     */
    public static function isActive(BusinessDetail $businessDetail)
    {
        // Free plan is always active
        if ($businessDetail->subscription_plan === 'free') {
            return true;
        }

        // Check if subscription is marked as active and not expired
        return $businessDetail->subscription_active &&
               $businessDetail->subscription_end_date &&
               Carbon::now()->lt($businessDetail->subscription_end_date);
    }

    /**
     * Check if a business can use a specific feature
     *
     * @param BusinessDetail $businessDetail
     * @param string $feature
     * @return bool|mixed
     */
    public static function canUseFeature(BusinessDetail $businessDetail, $feature)
    {
        $planKey = $businessDetail->subscription_plan ?? 'free';
        $plan = self::getPlan($planKey);

        if (!$plan) {
            return false;
        }

        // Check if subscription is active
        if (!self::isActive($businessDetail) && $planKey !== 'free') {
            return false;
        }

        // Check if feature exists in plan
        if (!isset($plan['features'][$feature])) {
            return false;
        }

        return $plan['features'][$feature];
    }

    /**
     * Calculate platform fee for a transaction
     *
     * @param BusinessDetail $businessDetail
     * @param float $amount
     * @return float
     */
    public static function calculatePlatformFee(BusinessDetail $businessDetail, $amount)
    {
        $planKey = $businessDetail->subscription_plan ?? 'free';
        $feePercentage = self::getTransactionFeePercentage($planKey);

        return round(($amount * $feePercentage) / 100, 2);
    }
}
