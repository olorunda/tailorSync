# Notification System Documentation

## Overview

This document describes the notification system implemented for TailorFit to:
1. Send notifications of order status changes to customers
2. Send appointment reminders to both customers and clients

## Components

### Notification Classes

1. **OrderStatusNotification**
   - Located at: `app/Notifications/OrderStatusNotification.php`
   - Purpose: Notifies customers when an order's status changes
   - Channels: Email
   - Contains customized messages for different status types

2. **AppointmentReminderNotification**
   - Located at: `app/Notifications/AppointmentReminderNotification.php`
   - Purpose: Sends reminders for upcoming appointments
   - Channels: Email
   - Includes appointment details and related order information if available

### Observer

1. **OrderObserver**
   - Located at: `app/Observers/OrderObserver.php`
   - Purpose: Observes changes to the Order model and sends notifications when status changes
   - Registered in: `app/Providers/AppServiceProvider.php`

### Command

1. **SendAppointmentReminders**
   - Located at: `app/Console/Commands/SendAppointmentReminders.php`
   - Purpose: Finds appointments within 24 hours and sends reminders
   - Scheduled to run daily at 8 AM in `app/Console/Kernel.php`

## Model Updates

1. **Client Model**
   - Added the `Notifiable` trait to enable sending notifications to clients

## How It Works

### Order Status Notifications

1. When an order's status is updated, the `OrderObserver` captures the change
2. The observer sends a notification to both the client and the user (tailor/business owner)
3. The notification includes:
   - The order number
   - The new status with a customized message
   - Order details
   - A link to view the order

### Appointment Reminders

1. The `SendAppointmentReminders` command runs daily at 8 AM
2. It finds appointments that:
   - Are scheduled within the next 24 hours
   - Haven't had reminders sent yet
   - Aren't cancelled
3. For each appointment, it sends a notification to both the client and the user
4. The notification includes:
   - Appointment title
   - Date and time
   - Location
   - Description (if available)
   - Related order information (if available)
   - A link to view the appointment details

## Testing

To test the notification system:

1. **Order Status Notifications**:
   - Update an order's status and verify that notifications are sent to the client and user

2. **Appointment Reminders**:
   - Run the command manually: `php artisan app:send-appointment-reminders`
   - Create an appointment within the next 24 hours and verify that reminders are sent

## Maintenance

- The notification templates can be customized in the respective notification classes
- Additional notification channels (SMS, push notifications, etc.) can be added by updating the `via()` method in the notification classes
