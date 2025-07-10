# Push Notification System Documentation

## Overview

This document describes the push notification system implemented for TailorFit to:
1. Send notifications of order status changes to customers
2. Send appointment reminders to both customers and clients
3. Allow users to subscribe to push notifications in their browser

## Components

### Backend Components

#### Notification Classes

1. **OrderStatusNotification**
   - Located at: `app/Notifications/OrderStatusNotification.php`
   - Purpose: Notifies customers when an order's status changes
   - Channels: Email, Database, Push Notifications
   - Contains customized messages for different status types

2. **AppointmentReminderNotification**
   - Located at: `app/Notifications/AppointmentReminderNotification.php`
   - Purpose: Sends reminders for upcoming appointments
   - Channels: Email, Database, Push Notifications
   - Includes appointment details and related order information if available

#### Push Notification Channel

1. **PushNotificationChannel**
   - Located at: `app/Notifications/Channels/PushNotificationChannel.php`
   - Purpose: Custom notification channel for sending push notifications
   - Uses the PushNotificationService to send notifications to subscribed devices

#### Push Notification Service

1. **PushNotificationService**
   - Located at: `app/Services/PushNotificationService.php`
   - Purpose: Handles the sending of push notifications to subscribed devices
   - Implements VAPID authentication for Web Push Protocol
   - Provides methods for sending notifications to specific subscriptions or notifiable entities

#### Models and Traits

1. **PushSubscription Model**
   - Located at: `app/Models/PushSubscription.php`
   - Purpose: Stores push subscription information
   - Contains endpoint, public key, auth token, and content encoding

2. **HasPushSubscriptions Trait**
   - Located at: `app/Traits/HasPushSubscriptions.php`
   - Purpose: Provides methods for managing push subscriptions
   - Used by User and Client models
   - Provides methods for creating, deleting, and retrieving push subscriptions

#### API Controllers

1. **PushSubscriptionController**
   - Located at: `app/Http/Controllers/Api/PushSubscriptionController.php`
   - Purpose: Handles API requests for managing push subscriptions
   - Provides endpoints for creating and deleting push subscriptions

### Frontend Components

1. **Service Worker**
   - Located at: `public/service-worker.js`
   - Purpose: Handles push notifications on the client side
   - Registers event listeners for push events and notification clicks
   - Displays notifications to the user

2. **Push Notification Manager**
   - Located at: `public/js/push-notifications.js`
   - Purpose: Manages push notification subscriptions on the client side
   - Provides methods for subscribing to and unsubscribing from push notifications
   - Sends subscription information to the server

3. **Push Notification Toggle Component**
   - Located at: `resources/views/components/push-notification-toggle.blade.php`
   - Purpose: Provides a UI for subscribing to and unsubscribing from push notifications
   - Displays different buttons based on the current subscription status

## Configuration

### Environment Variables

The following environment variables are used for push notifications:

```
VAPID_PUBLIC_KEY=BPYPNmhsTWxmR7Hy_bJYpQQQAZeE0_0RhN9QY5qLVaJKTlJ0I-bCWQnzAFwgkxYOuMU9jcJQCXFnpUYRTJCzA_I
VAPID_PRIVATE_KEY=3KzvKasA2SoCxsp0iIG_o_2GSEXG3ysP8cW6gOsirow
```

These keys are used for VAPID authentication in the Web Push Protocol.

### Service Configuration

The push notification service is configured in `config/services.php`:

```php
'push_notifications' => [
    'public_key' => env('VAPID_PUBLIC_KEY'),
    'private_key' => env('VAPID_PRIVATE_KEY'),
],
```

## How It Works

### Subscription Process

1. The user clicks the "Enable Push Notifications" button in the sidebar
2. The browser requests permission to show notifications
3. If granted, the service worker is registered and a push subscription is created
4. The subscription is sent to the server and stored in the database
5. The user can now receive push notifications

### Sending Notifications

1. When a notification needs to be sent (e.g., order status change), the appropriate notification class is instantiated
2. The notification class determines which channels to use based on the notifiable entity
3. If the notifiable entity has push subscriptions, the notification is sent through the PushNotificationChannel
4. The PushNotificationService sends the notification to all subscribed devices
5. The service worker on the client side receives the push event and displays the notification

### Handling Notification Clicks

1. When a user clicks on a notification, the service worker's `notificationclick` event is triggered
2. The event handler closes the notification and opens the appropriate page in the application
3. If the application is already open, it focuses the existing window instead of opening a new one

## Testing

To test the push notification system:

1. **Enable Push Notifications**:
   - Log in to the application
   - Click the "Enable Push Notifications" button in the sidebar
   - Grant permission when prompted

2. **Test Order Status Notifications**:
   - Update an order's status and verify that a push notification is received

3. **Test Appointment Reminders**:
   - Create an appointment within the next 24 hours and verify that a reminder notification is received

## Troubleshooting

Common issues and their solutions:

1. **Notifications not being received**:
   - Check if the browser supports push notifications
   - Verify that notification permission has been granted
   - Check if the service worker is registered correctly
   - Verify that the VAPID keys are configured correctly

2. **Subscription fails**:
   - Check browser console for errors
   - Verify that the application is being served over HTTPS (required for push notifications)
   - Check if the VAPID public key is being correctly passed to the client

## Maintenance

- The notification templates can be customized in the respective notification classes
- Additional notification types can be added by creating new notification classes
- The service worker can be updated to handle additional notification actions
