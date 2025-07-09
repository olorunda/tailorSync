# Subscription Already Exists Fix

## Issue
When a user tries to subscribe to a plan they're already subscribed to, the system was displaying an error message instead of activating the existing subscription.

## Solution
The solution involves modifying the `verifyPaystackSubscriptionPayment` method in the `PaymentService` class to handle the case when a subscription already exists. Instead of throwing an exception, the system now retrieves the existing subscription details and activates it.

### Changes Made

1. Modified the error handling in `verifyPaystackSubscriptionPayment` to check for the specific error message "This subscription is already" from Paystack.
2. Implemented a new method `fetchExistingSubscription` that retrieves the existing subscription details from Paystack using the customer email and plan code.
3. When a subscription already exists, the system now retrieves its details and returns them instead of throwing an exception.

### Code Changes

The main changes were made in the `PaymentService.php` file:

1. Added error message checking to detect when a subscription already exists:
```php
if (stripos($errorMessage, 'This subscription is already') !== false) {
    // Subscription already exists, try to fetch it
    $customerEmail = $verificationResult['raw_response']['customer']['email'];
    
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
```

2. Implemented the `fetchExistingSubscription` method to retrieve existing subscription details:
```php
protected function fetchExistingSubscription($customerEmail, $planCode, $secretKey)
{
    // First, list all subscriptions for this customer
    $url = "https://api.paystack.co/subscription?customer=" . urlencode($customerEmail) . "&perPage=100";
    
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
```

## Testing
The changes have been tested to ensure that when a user tries to subscribe to a plan they're already subscribed to, the system now retrieves the existing subscription details and activates it instead of displaying an error message.
