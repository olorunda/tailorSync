/**
 * Push Notifications Handler for ThreadNix
 *
 * This file handles:
 * 1. Checking for service worker and push notification support
 * 2. Requesting notification permission
 * 3. Registering the service worker
 * 4. Subscribing to push notifications
 * 5. Sending the subscription to the server
 */

class PushNotificationManager {
  constructor() {
    this.swRegistration = null;
    this.isSubscribed = false;
    this.applicationServerPublicKey = null;
  }

  /**
   * Initialize the push notification system
   * @param {string} applicationServerPublicKey - VAPID public key from the server
   */
  init(applicationServerPublicKey) {
    // Store the application server public key
    this.applicationServerPublicKey = applicationServerPublicKey;

    // Check if service workers and push messaging are supported
    if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
      console.warn('Push notifications are not supported in this browser');
      return false;
    }

    // Register the service worker
    navigator.serviceWorker.register('/service-worker.js')
      .then(registration => {
        console.log('Service Worker registered with scope:', registration.scope);
        this.swRegistration = registration;

        // Check if already subscribed
        this.checkSubscription();

        return true;
      })
      .catch(error => {
        console.error('Service Worker registration failed:', error);
        return false;
      });
  }

  /**
   * Check if the user is already subscribed to push notifications
   */
  checkSubscription() {
    if (!this.swRegistration) return Promise.resolve(false);

    return this.swRegistration.pushManager.getSubscription()
      .then(subscription => {
        this.isSubscribed = subscription !== null;
        console.log('User is' + (this.isSubscribed ? '' : ' not') + ' subscribed to push notifications');

        // Dispatch an event to notify the application of the subscription status
        window.dispatchEvent(new CustomEvent('push-subscription-state-changed', {
          detail: { isSubscribed: this.isSubscribed }
        }));

        return this.isSubscribed;
      });
  }

  /**
   * Request permission and subscribe to push notifications
   */
  subscribe() {
    if (!this.swRegistration) {
      console.error('Service Worker not registered');
      return Promise.reject('Service Worker not registered');
    }

    return new Promise((resolve, reject) => {
      // Request notification permission
      Notification.requestPermission()
        .then(permission => {
          if (permission !== 'granted') {
            console.warn('Notification permission denied');
            reject('Notification permission denied');
            return;
          }

          // Convert the application server public key to array buffer
          const applicationServerKey = this.urlB64ToUint8Array(this.applicationServerPublicKey);

          // Subscribe to push notifications
          this.swRegistration.pushManager.subscribe({
            userVisibleOnly: true,
            applicationServerKey: applicationServerKey
          })
            .then(subscription => {
              console.log('User is subscribed to push notifications');
              this.isSubscribed = true;

              // Send the subscription to the server
              this.sendSubscriptionToServer(subscription);

              // Dispatch an event to notify the application of the subscription status
              window.dispatchEvent(new CustomEvent('push-subscription-state-changed', {
                detail: { isSubscribed: true }
              }));

              resolve(subscription);
            })
            .catch(error => {
              console.error('Failed to subscribe to push notifications:', error);
              reject(error);
            });
        });
    });
  }

  /**
   * Unsubscribe from push notifications
   */
  unsubscribe() {
    if (!this.swRegistration) {
      console.error('Service Worker not registered');
      return Promise.reject('Service Worker not registered');
    }

    return new Promise((resolve, reject) => {
      this.swRegistration.pushManager.getSubscription()
        .then(subscription => {
          if (!subscription) {
            console.warn('No subscription to unsubscribe from');
            this.isSubscribed = false;

            // Dispatch an event to notify the application of the subscription status
            window.dispatchEvent(new CustomEvent('push-subscription-state-changed', {
              detail: { isSubscribed: false }
            }));

            resolve();
            return;
          }

          // Send the unsubscription to the server
          this.sendUnsubscriptionToServer(subscription);

          // Unsubscribe
          subscription.unsubscribe()
            .then(() => {
              console.log('User is unsubscribed from push notifications');
              this.isSubscribed = false;

              // Dispatch an event to notify the application of the subscription status
              window.dispatchEvent(new CustomEvent('push-subscription-state-changed', {
                detail: { isSubscribed: false }
              }));

              resolve();
            })
            .catch(error => {
              console.error('Failed to unsubscribe from push notifications:', error);
              reject(error);
            });
        });
    });
  }

  /**
   * Send the subscription to the server
   * @param {PushSubscription} subscription - The push subscription object
   */
  sendSubscriptionToServer(subscription) {
    // Convert the subscription to a simple object
    const subscriptionJson = subscription.toJSON();

    // Send the subscription to the server
    fetch('/api/push-subscriptions', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
      },
      body: JSON.stringify({
        endpoint: subscriptionJson.endpoint,
        keys: subscriptionJson.keys
      })
    })
      .then(response => {
        if (!response.ok) {
          throw new Error('Failed to save subscription on server');
        }
        console.log('Subscription saved on server');
      })
      .catch(error => {
        console.error('Error saving subscription on server:', error);
      });
  }

  /**
   * Send the unsubscription to the server
   * @param {PushSubscription} subscription - The push subscription object
   */
  sendUnsubscriptionToServer(subscription) {
    // Convert the subscription to a simple object
    const subscriptionJson = subscription.toJSON();

    // Send the unsubscription to the server
    fetch('/api/push-subscriptions', {
      method: 'DELETE',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
      },
      body: JSON.stringify({
        endpoint: subscriptionJson.endpoint
      })
    })
      .then(response => {
        if (!response.ok) {
          throw new Error('Failed to delete subscription on server');
        }
        console.log('Subscription deleted on server');
      })
      .catch(error => {
        console.error('Error deleting subscription on server:', error);
      });
  }

  /**
   * Convert a base64 string to a Uint8Array
   * @param {string} base64String - The base64 string to convert
   * @returns {Uint8Array} - The converted Uint8Array
   */
  urlB64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - base64String.length % 4) % 4);
    const base64 = (base64String + padding)
      .replace(/\-/g, '+')
      .replace(/_/g, '/');

    const rawData = window.atob(base64);
    const outputArray = new Uint8Array(rawData.length);

    for (let i = 0; i < rawData.length; ++i) {
      outputArray[i] = rawData.charCodeAt(i);
    }

    return outputArray;
  }
}

// Create a global instance of the PushNotificationManager
window.pushNotificationManager = new PushNotificationManager();

// Initialize the push notification manager when the DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
  // Check if the VAPID public key is available in the page
  const vapidPublicKey = document.querySelector('meta[name="vapid-public-key"]')?.getAttribute('content');

  if (vapidPublicKey) {
    window.pushNotificationManager.init(vapidPublicKey);
  } else {
    console.warn('VAPID public key not found. Push notifications will not be initialized.');
  }
});
