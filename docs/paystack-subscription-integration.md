# Paystack Subscription Integration

This document describes the integration with the Paystack recurring payment API for managing platform-level user subscriptions.

## Features

- Automatic creation of Paystack subscription plans based on the available plans in the system
- Recurring subscription management using Paystack's subscription API
- Webhook handling for subscription events (creation, cancellation, payment success/failure)

## Commands

### Create Paystack Plans

To create Paystack subscription plans based on the available plans in the system and update the `PAYSTACK_PLAN_CODES` constant:

```bash
php artisan app:create-paystack-plans
```

This command will:
1. Get all available subscription plans from `SubscriptionService::PLANS`
2. Filter out the free plan
3. Create each paid plan in Paystack
4. Update the `PAYSTACK_PLAN_CODES` constant in `PaymentService.php` with the plan codes returned by Paystack

### Webhook Setup

Make sure to set up the Paystack webhook in your Paystack dashboard to point to:

```
https://yourdomain.com/webhooks/paystack
```

This will allow Paystack to notify your application about subscription events.

## Implementation Details

### Subscription Flow

1. User selects a subscription plan
2. System initializes a Paystack subscription using the plan code
3. User is redirected to Paystack to complete the payment
4. Paystack redirects back to the callback URL
5. System verifies the payment and activates the subscription
6. Paystack sends webhook events for subscription lifecycle events

### Files Modified

- `app/Services/PaymentService.php`: Added methods for creating and managing Paystack subscription plans
- `app/Services/SubscriptionService.php`: Updated to handle recurring subscriptions
- `app/Http/Controllers/PaystackWebhookController.php`: Added to handle Paystack webhook events
- `app/Console/Commands/CreatePaystackPlansCommand.php`: Added to create Paystack plans and update plan codes
- `routes/web.php`: Added webhook route
- Database migrations: Added subscription_code field to business_details and subscription_histories tables

## Usage Example

Here's how to use the subscription system:

1. First, run the command to create Paystack plans:

```bash
php artisan app:create-paystack-plans
```

2. After the plans are created, users can subscribe to them through the subscription page.

3. When a user subscribes to a plan, the system will use the Paystack recurring subscription API to handle the payment and subscription management.

4. Paystack will send webhook events to your application to notify about subscription events (creation, cancellation, payment success/failure).

## Troubleshooting

If you encounter issues with the Paystack integration, check the following:

1. Make sure your Paystack API keys are correctly configured in your .env file:

```
PAYSTACK_PUBLIC_KEY=your_public_key
PAYSTACK_SECRET_KEY=your_secret_key
```

2. Ensure the webhook URL is correctly set up in your Paystack dashboard.

3. Check the Laravel logs for any errors related to Paystack API calls or webhook handling.
