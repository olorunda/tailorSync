<div id="push-notification-toggle" class="hidden fixed bottom-10 right-10 z-50">
    <button id="enable-push-notifications" class="flex items-center justify-center px-4 py-3 text-sm font-medium text-white bg-orange-600 border border-transparent rounded-full shadow-lg hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-300 animate-pulse">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 20 20" fill="currentColor">
            <path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z" />
        </svg>
    </button>

    <button id="disable-push-notifications" class="hidden flex items-center justify-center px-4 py-3 text-sm font-medium text-white bg-red-600 border border-transparent rounded-full shadow-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-all duration-300">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M3.707 2.293a1 1 0 00-1.414 1.414l14 14a1 1 0 001.414-1.414l-1.473-1.473A10.014 10.014 0 0019 10.5V8.75a.75.75 0 00-.75-.75h-2.5a.75.75 0 00-.75.75v1.75c0 .69-.071 1.363-.206 2.014l-1.36-1.36A7.97 7.97 0 0014 10.5V8a4 4 0 00-4-4c-.48 0-.943.085-1.371.243l-1.48-1.48A6.975 6.975 0 0110 2a6 6 0 016 6v3.586l.293.293a1 1 0 01.707 1.707l-1.414 1.414a1 1 0 01-1.414 0l-.586-.586V15a3 3 0 01-6 0v-1.586l-6.293-6.293z" clip-rule="evenodd" />
        </svg>
    </button>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const enableButton = document.getElementById('enable-push-notifications');
    const disableButton = document.getElementById('disable-push-notifications');
    const toggleContainer = document.getElementById('push-notification-toggle');

    // Check if push notifications are supported
    if ('serviceWorker' in navigator && 'PushManager' in window) {
        // Check if already subscribed
        navigator.serviceWorker.ready.then(registration => {
            registration.pushManager.getSubscription().then(subscription => {
                if (subscription === null) {
                    // Only show the toggle if not subscribed
                    toggleContainer.classList.remove('hidden');
                }
                updateButtonState(subscription !== null);
            });
        });

        // Add event listeners
        enableButton.addEventListener('click', function() {
            window.pushNotificationManager.subscribe()
                .then(() => {
                    updateButtonState(true);
                    showNotification('Push notifications enabled', 'success');

                    // Hide the toggle container after a short delay
                    setTimeout(() => {
                        toggleContainer.classList.add('hidden');
                    }, 1500);
                })
                .catch(error => {
                    console.error('Error subscribing to push notifications:', error);
                    showNotification('Failed to enable push notifications', 'error');
                });
        });

        disableButton.addEventListener('click', function() {
            window.pushNotificationManager.unsubscribe()
                .then(() => {
                    updateButtonState(false);
                    showNotification('Push notifications disabled', 'success');

                    // Show the toggle container again
                    toggleContainer.classList.remove('hidden');
                })
                .catch(error => {
                    console.error('Error unsubscribing from push notifications:', error);
                    showNotification('Failed to disable push notifications', 'error');
                });
        });

        // Listen for subscription state changes
        window.addEventListener('push-subscription-state-changed', function(event) {
            updateButtonState(event.detail.isSubscribed);

            // Hide or show the toggle container based on subscription status
            if (event.detail.isSubscribed) {
                setTimeout(() => {
                    toggleContainer.classList.add('hidden');
                }, 1500);
            } else {
                toggleContainer.classList.remove('hidden');
            }
        });
    }

    function updateButtonState(isSubscribed) {
        if (isSubscribed) {
            enableButton.classList.add('hidden');
            disableButton.classList.remove('hidden');
        } else {
            enableButton.classList.remove('hidden');
            disableButton.classList.add('hidden');
        }
    }

    function showNotification(message, type) {
        if (window.Swal) {
            Swal.fire({
                icon: type === 'success' ? 'success' : 'error',
                title: message,
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000
            });
        } else {
            alert(message);
        }
    }
});
</script>
