# TailorFit Progressive Web App (PWA)

This document explains the Progressive Web App (PWA) functionality implemented in TailorFit and how to use it.

## Features

- **Offline Access**: Access the application even when offline
- **Installable**: Install the app on your device's home screen
- **Background Sync**: Submit forms even when offline, data will be sent when back online
- **Automatic Updates**: The app will automatically update when new versions are available

## How It Works

### Caching Strategy

The PWA uses different caching strategies for different types of resources:

1. **HTML Pages**: Network-first strategy with fallback to cache
   - The app tries to fetch the latest version from the network
   - If that fails, it serves the cached version
   - If no cached version exists, it shows the offline page

2. **Static Assets**: Cache-first strategy with background updates
   - The app serves static assets (CSS, JS, images) from the cache first for faster loading
   - It updates the cache in the background when online

### Offline Form Submission

Forms with the `data-offline` attribute will work offline:

1. When offline, form submissions are stored in IndexedDB
2. When the device comes back online, the stored submissions are automatically sent
3. Users receive visual feedback that their data will be sent later

## How to Use

### Making Forms Work Offline

Use the `<x-offline-form>` component instead of regular `<form>` tags:

```blade
<x-offline-form :action="route('clients.store')">
    <!-- Form fields -->
    <input type="text" name="name">
    
    <button type="submit">Submit</button>
</x-offline-form>
```

This automatically adds the `data-offline` attribute to the form.

### Offline Indicator

An offline indicator will automatically appear at the top of the screen when the user goes offline.

### Testing Offline Functionality

To test offline functionality:

1. Open the application in Chrome
2. Open Chrome DevTools (F12 or Ctrl+Shift+I)
3. Go to the Network tab
4. Check the "Offline" checkbox
5. Try navigating the app and submitting forms

## Technical Implementation

The PWA functionality is implemented with the following components:

1. **Service Worker** (`/public/service-worker.js`): Handles caching, offline access, and background sync
2. **Manifest** (`/public/manifest.json`): Provides metadata for installation
3. **Offline Page** (`/resources/views/offline.blade.php`): Shown when a page isn't available offline
4. **JavaScript** (`/resources/js/app.js`): Registers the service worker and handles offline form submissions
5. **Offline Form Component** (`/resources/views/components/offline-form.blade.php`): Blade component for offline-enabled forms

## Browser Support

PWA features are supported in:

- Chrome (Android & Desktop)
- Firefox (Android & Desktop)
- Safari (iOS 11.3+ & macOS)
- Edge (Windows)

Some features may have limited support in certain browsers.
