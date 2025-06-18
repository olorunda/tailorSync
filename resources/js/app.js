// Import Tom Select
import TomSelect from 'tom-select';

// Register Service Worker for PWA
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/service-worker.js')
            .then(registration => {
                console.log('Service Worker registered with scope:', registration.scope);

                // Check for updates to the service worker
                registration.addEventListener('updatefound', () => {
                    const newWorker = registration.installing;
                    console.log('Service Worker update found!');

                    newWorker.addEventListener('statechange', () => {
                        console.log('Service Worker state changed:', newWorker.state);
                    });
                });

                // Handle offline form submissions
                if ('sync' in registration) {
                    document.addEventListener('submit', event => {
                        // Only handle forms with data-offline attribute
                        if (event.target.hasAttribute('data-offline') && !navigator.onLine) {
                            event.preventDefault();

                            // Store form data for later submission
                            const formData = new FormData(event.target);
                            const formAction = event.target.action;
                            const formMethod = event.target.method;

                            // Show offline message
                            const offlineMessage = document.createElement('div');
                            offlineMessage.className = 'bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-4';
                            offlineMessage.innerHTML = 'You are currently offline. Your data has been saved and will be submitted when you are back online.';
                            event.target.prepend(offlineMessage);

                            // Request sync via message to service worker
                            if (navigator.serviceWorker.controller) {
                                navigator.serviceWorker.controller.postMessage({
                                    type: 'SYNC_PENDING_REQUESTS'
                                });
                            } else {
                                // If controller is not available yet, wait for it
                                navigator.serviceWorker.ready.then(registration => {
                                    if (registration.active) {
                                        registration.active.postMessage({
                                            type: 'SYNC_PENDING_REQUESTS'
                                        });
                                    }
                                });
                            }
                        }
                    });
                }

                // Listen for navigation events to update route cache
                document.addEventListener('livewire:navigated', () => {
                    if (navigator.onLine && navigator.serviceWorker.controller) {
                        navigator.serviceWorker.controller.postMessage({
                            type: 'UPDATE_ROUTE_CACHE'
                        });
                    }
                });

                // Also listen for regular page loads
                window.addEventListener('pageshow', () => {
                    if (navigator.onLine && navigator.serviceWorker.controller) {
                        navigator.serviceWorker.controller.postMessage({
                            type: 'UPDATE_ROUTE_CACHE'
                        });
                    }
                });
            })
            .catch(error => {
                console.error('Service Worker registration failed:', error);
            });
    });
}

// Check for online/offline status
window.addEventListener('online', () => {
    console.log('Application is online');
    document.querySelectorAll('.offline-indicator').forEach(el => el.style.display = 'none');

    // Trigger sync when back online via message to service worker
    if ('serviceWorker' in navigator) {
        if (navigator.serviceWorker.controller) {
            // Sync pending requests
            navigator.serviceWorker.controller.postMessage({
                type: 'SYNC_PENDING_REQUESTS'
            });

            // Update route cache
            navigator.serviceWorker.controller.postMessage({
                type: 'UPDATE_ROUTE_CACHE'
            });
        } else {
            // If controller is not available yet, wait for it
            navigator.serviceWorker.ready.then(registration => {
                if (registration.active) {
                    // Sync pending requests
                    registration.active.postMessage({
                        type: 'SYNC_PENDING_REQUESTS'
                    });

                    // Update route cache
                    registration.active.postMessage({
                        type: 'UPDATE_ROUTE_CACHE'
                    });
                }
            });
        }
    }
});

window.addEventListener('offline', () => {
    console.log('Application is offline');

    // Show offline indicator if it exists
    document.querySelectorAll('.offline-indicator').forEach(el => el.style.display = 'block');
});

// // Function to initialize Tom Select on an element
// function initializeTomSelect(select) {
//     // Skip if already initialized or has a specific class to exclude
//     if (select.tomselect || select.classList.contains('no-tom-select')) {
//         return;
//     }
//
//     new TomSelect(select, {
//         plugins: {
//             clear_button: {
//                 title: 'Remove selection',
//             }
//         },
//
//
//         // // Allow searching within options
//         allowEmptyOption: true,
//         // // Show dropdown even with single option
//         dropdownParent: 'body',
//         // // Customize placeholder text
//         placeholder: select.getAttribute('placeholder') || 'Search...',
//         // // Enable searching
//         persist: false,
//         // // Create items when no matches
//         createOnBlur: false,
//         // create: false
//     });
// }
//
// // Function to initialize Tom Select on all select elements
// function initializeAllSelects() {
//     const selectElements = document.querySelectorAll('select');
//     selectElements.forEach(initializeTomSelect);
// }
//
// // Initialize when DOM is loaded
// document.addEventListener('DOMContentLoaded', initializeAllSelects);
//
// // Initialize when Livewire updates the DOM
// document.addEventListener('livewire:navigated', initializeAllSelects);
//
// // Initialize when Livewire updates a component
// document.addEventListener('livewire:init', () => {
//     Livewire.hook('morph.updated', ({ el }) => {
//         // Find all select elements within the updated element
//         const selectElements = el.querySelectorAll('select');
//         selectElements.forEach(initializeTomSelect);
//     });
// });
