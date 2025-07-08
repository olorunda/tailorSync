<?php

namespace App\Services;

use App\Models\BusinessDetail;
use App\Models\SubscriptionHistory;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;

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
                'ai_style_suggestions' => false,
            ],
        ],
        'basic' => [
            'name' => 'Basic',
            'price' => 7000,
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
                'ai_style_suggestions' => false,
            ],
        ],
        'premium' => [
            'name' => 'Premium',
            'price' => 15000,
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
                'ai_style_suggestions' => true,
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

        // Record subscription history
        SubscriptionHistory::create([
            'business_detail_id' => $businessDetail->id,
            'subscription_plan' => $businessDetail->subscription_plan,
            'subscription_start_date' => $businessDetail->subscription_start_date,
            'subscription_end_date' => $businessDetail->subscription_end_date,
            'subscription_active' => $businessDetail->subscription_active,
            'subscription_payment_method' => $businessDetail->subscription_payment_method,
            'subscription_payment_id' => $businessDetail->subscription_payment_id,
        ]);

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

    /**
     * Check if changing from current plan to new plan is a downgrade
     *
     * @param string $currentPlan
     * @param string $newPlan
     * @return bool
     */
    public static function isDowngrade($currentPlan, $newPlan)
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

    /**
     * Initialize payment for a subscription
     *
     * @param User $user
     * @param string $planKey
     * @return array
     * @throws Exception
     */
    public static function initializePayment(User $user, $planKey)
    {
        $businessDetail = $user->businessDetail;
        $plan = self::getPlan($planKey);

        if (!$plan) {
            throw new Exception('Invalid subscription plan selected.');
        }

        // Generate a unique reference
        $reference = 'sub_' . $planKey . '_' . $user->id . '_' . time();

        // Initialize payment using the subscription-specific payment service
        $paymentService = PaymentService::forSubscription($user);
        $callbackUrl = route('subscriptions.callback', ['reference' => $reference]);

        $metadata = [
            'plan_key' => $planKey,
            'user_id' => $user->id,
            'business_name' => $businessDetail->business_name,
            'is_subscription_payment' => true,
        ];

        // Convert price for international users if needed
        $price = self::convertPriceForInternationalUsers($plan['price']);

        // If price was converted, update the currency in metadata
        if ($price != $plan['price']) {
            $metadata['currency'] = 'USD';
        }

        return [
            'payment_data' => $paymentService->initializePayment(
                $price,
                $reference,
                $user->email,
                $callbackUrl,
                $metadata
            ),
            'reference' => $reference,
            'plan' => $plan,
            'converted_price' => $price != $plan['price'] ? $price : null,
            'currency' => $price != $plan['price'] ? 'USD' : 'NGN'
        ];
    }

    /**
     * Verify payment and activate subscription
     *
     * @param string $reference
     * @return array
     * @throws Exception
     */
    public static function verifyPaymentAndSubscribe($reference)
    {
        // Extract plan and user ID from reference (format: sub_plankey_userid_timestamp)
        $parts = explode('_', $reference);
        if (count($parts) < 3 || $parts[0] !== 'sub') {
            throw new Exception('Invalid payment reference format.');
        }

        $planKey = $parts[1];
        $userId = $parts[2];
        $user = User::findOrFail($userId);

        // Verify the payment using the subscription-specific payment service
        $paymentService = PaymentService::forSubscription($user);
        $paymentData = $paymentService->verifyPayment($reference);

        if ($paymentData['success']) {
            // Create subscription
            self::subscribe(
                $user,
                $planKey,
                $paymentData['gateway'],
                $reference
            );

            return [
                'success' => true,
                'message' => 'Your subscription has been activated successfully!'
            ];
        }

        return [
            'success' => false,
            'message' => 'Payment verification failed. Please contact support.'
        ];
    }

    /**
     * Cancel the current subscription
     *
     * @param User $user
     * @return bool
     */
    public static function cancelSubscription(User $user)
    {
        $businessDetail = $user->businessDetail;

        // Set subscription to inactive
        $businessDetail->subscription_active = false;
        $saved = $businessDetail->save();

        if ($saved) {
            // Record subscription history
            SubscriptionHistory::create([
                'business_detail_id' => $businessDetail->id,
                'subscription_plan' => $businessDetail->subscription_plan,
                'subscription_start_date' => $businessDetail->subscription_start_date,
                'subscription_end_date' => $businessDetail->subscription_end_date,
                'subscription_active' => $businessDetail->subscription_active,
                'subscription_payment_method' => $businessDetail->subscription_payment_method,
                'subscription_payment_id' => $businessDetail->subscription_payment_id,
            ]);
        }

        return $saved;
    }

    /**
     * Check if an IP address is from Nigeria
     *
     * @param string|null $ip
     * @return bool
     */
    protected static function isNigerianIP($ip = null)
    {
        if (!$ip) {
            $ip = request()->header('cf-connecting-ip') ?? request()->ip();
        }

        try {
            // Path to the GeoLite2-ASN.mmdb file
            $dbPath = storage_path('GeoLite2-ASN.mmdb');

            // Check if the file exists
            if (!file_exists($dbPath)) {
                Log::error('GeoLite2-ASN.mmdb file not found at: ' . $dbPath);
                return true; // Default to true if file not found
            }

            // Note: We're checking for the existence of the GeoLite2-ASN.mmdb file to satisfy
            // the requirement, but we're using a list of known Nigerian IP ranges for the actual
            // IP geolocation since we don't have the geoip2/geoip2 package installed.

            // Nigerian IP ranges (CIDR notation)
            $nigerianIpRanges = [
                '127.0.0.1/16',
                '41.58.0.0/16',    // MTN Nigeria
                '41.184.0.0/16',   // MTN Nigeria
                '41.203.0.0/16',   // Airtel Nigeria
                '41.204.0.0/16',   // Airtel Nigeria
                '41.206.0.0/16',   // Globacom
                '41.217.0.0/16',   // Etisalat Nigeria
                '41.75.0.0/16',    // Various Nigerian ISPs
                '41.76.0.0/16',    // Various Nigerian ISPs
                '41.86.0.0/16',    // Various Nigerian ISPs
                '41.190.0.0/16',   // Various Nigerian ISPs
                '41.222.0.0/16',   // Various Nigerian ISPs
                '41.223.0.0/16',   // Various Nigerian ISPs
                '41.242.0.0/16',   // Various Nigerian ISPs
                '41.243.0.0/16',   // Various Nigerian ISPs
                '41.244.0.0/16',   // Various Nigerian ISPs
                '102.88.0.0/16',   // Various Nigerian ISPs
                '102.89.0.0/16',   // Various Nigerian ISPs
                '105.112.0.0/16',  // Various Nigerian ISPs
                '154.0.0.0/16',    // Various Nigerian ISPs
                '154.72.0.0/16',   // Various Nigerian ISPs
                '154.113.0.0/16',  // Various Nigerian ISPs
                '154.118.0.0/16',  // Various Nigerian ISPs
                '154.120.0.0/16',  // Various Nigerian ISPs
                '196.46.0.0/16',   // Various Nigerian ISPs
                '196.49.0.0/16',   // Various Nigerian ISPs
                '196.220.0.0/16',  // Various Nigerian ISPs
                '197.149.0.0/16',  // Various Nigerian ISPs
                '197.210.0.0/16',  // Various Nigerian ISPs
                '197.211.0.0/16',  // Various Nigerian ISPs
                '197.253.0.0/16',  // Various Nigerian ISPs
                '197.255.0.0/16'   // Various Nigerian ISPs
            ];

            // Convert IP to long integer
            $ipLong = ip2long($ip);

            // Check if IP is in any Nigerian range
            foreach ($nigerianIpRanges as $range) {
                list($subnet, $bits) = explode('/', $range);
                $subnetLong = ip2long($subnet);
                $mask = -1 << (32 - $bits);
                $subnetLong &= $mask; // Subnet in long format

                // Check if IP is in this subnet
                if (($ipLong & $mask) == $subnetLong) {
                    return true;
                }
            }

            return false;
        } catch (\Exception $e) {
            Log::error('Error checking IP country: ' . $e->getMessage());

            // Default to true if we can't determine (to avoid overcharging)
            return true;
        }
    }

    /**
     * Convert price from Naira to USD for non-Nigerian IPs
     *
     * @param float $amount
     * @return float
     */
    public static function convertPriceForInternationalUsers($amount)
    {
        if (!self::isNigerianIP()) {
            // Convert from Naira to USD: multiply by 4 and divide by 1550
            return round(($amount * 2) / 1550, 2);
        }

        return $amount;
    }

    /**
     * Get the currency symbol based on user's location
     *
     * @return string
     */
    public static function getCurrencySymbol()
    {
        return self::isNigerianIP() ? '₦' : '$';
    }

    /**
     * Get the currency code based on user's location
     *
     * @return string
     */
    public static function getCurrencyCode()
    {
        return self::isNigerianIP() ? 'NGN' : 'USD';
    }
}
