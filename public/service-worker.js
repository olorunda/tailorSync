// Service Worker for TailorFit PWA

const CACHE_NAME = 'tailorfit-cache-v1';
const OFFLINE_URL = '/offline';
const ASSETS_TO_CACHE = [
  '/',
  '/offline',
  '/manifest.json',
  '/favicon.ico',
  '/favicon.svg',
  '/apple-touch-icon.png',
  '/build/assets/app.css',
  '/build/assets/app.js'
];

// All routes to cache
const ROUTES_TO_CACHE = [
  '/dashboard',
  '/notifications',
  '/settings/profile',
  '/settings/password',
  '/settings/appearance',
  '/settings/roles',
  '/settings/permissions',
  '/clients',
  '/clients/create',
  '/clients/import',
  '/orders',
  '/orders/create',
  '/designs',
  '/designs/create',
  '/inventory',
  '/inventory/create',
  '/inventory/import',
  '/appointments',
  '/appointments/create',
  '/messages',
  '/messages/create',
  '/invoices',
  '/invoices/create',
  '/payments',
  '/payments/create',
  '/expenses',
  '/expenses/create',
  '/team',
  '/team/create',
  '/tasks',
  '/tasks/create'
];

// Install event - cache assets and routes
self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then((cache) => {
        console.log('Opened cache');

        // Cache static assets
        const assetPromises = ASSETS_TO_CACHE.map(url => {
          return cache.add(url).catch(error => {
            console.warn('Failed to cache asset:', url, error);
            // Continue despite the failure
            return Promise.resolve();
          });
        });

        // Cache routes
        const routePromises = ROUTES_TO_CACHE.map(url => {
          return cache.add(url).catch(error => {
            console.warn('Failed to cache route:', url, error);
            // Continue despite the failure
            return Promise.resolve();
          });
        });

        // Combine all promises
        return Promise.all([...assetPromises, ...routePromises]);
      })
      .then(() => self.skipWaiting())
  );
});

// Activate event - clean up old caches and register sync
self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((cacheNames) => {
      return Promise.all(
        cacheNames.map((cacheName) => {
          if (cacheName !== CACHE_NAME) {
            console.log('Deleting old cache:', cacheName);
            return caches.delete(cacheName);
          }
        })
      );
    })
    .then(() => self.clients.claim())
    .then(() => {
      // Register background sync if online and sync is supported
      if (navigator.onLine && 'sync' in self.registration) {
        return self.registration.sync.register('sync-pending-requests')
          .catch(error => console.error('Sync registration failed:', error));
      }
    })
  );
});

// Fetch event - serve from cache or network
self.addEventListener('fetch', (event) => {
  // Skip cross-origin requests
  if (!event.request.url.startsWith(self.location.origin)) {
    return;
  }

  // Skip non-GET requests
  if (event.request.method !== 'GET') {
    // For POST requests, try to send later if offline
    if (event.request.method === 'POST') {
      event.respondWith(
        fetch(event.request.clone())
          .catch((error) => {
            // If offline, save the POST request to IndexedDB for later
            savePostRequestForLater(event.request.clone());
            return new Response(JSON.stringify({
              offline: true,
              message: 'Your data has been saved and will be sent when you are back online.'
            }), {
              headers: { 'Content-Type': 'application/json' }
            });
          })
      );
    }
    return;
  }

  // For HTML pages - Network first, fallback to cache, then offline page
  if (event.request.headers.get('Accept').includes('text/html')) {
    // Check if this is a route that should be cached
    const url = new URL(event.request.url);
    const isRoute = ROUTES_TO_CACHE.includes(url.pathname);

    event.respondWith(
      fetch(event.request)
        .then((response) => {
          // Cache the latest version
          const responseToCache = response.clone();
          caches.open(CACHE_NAME)
            .then((cache) => {
              cache.put(event.request, responseToCache)
                .catch(error => {
                  console.warn('Failed to cache HTML response:', error);
                  // Continue despite the failure
                });

              // If this is a route, also cache it with just the pathname
              if (isRoute) {
                cache.put(new Request(url.pathname), responseToCache.clone())
                  .catch(error => {
                    console.warn('Failed to cache route:', url.pathname, error);
                    // Continue despite the failure
                  });
              }
            })
            .catch(error => {
              console.warn('Failed to open cache:', error);
              // Continue despite the failure
            });
          return response;
        })
        .catch(() => {
          return caches.match(event.request)
            .then((cachedResponse) => {
              if (cachedResponse) {
                return cachedResponse;
              }

              // If not found with full URL, try with just the pathname
              if (isRoute) {
                return caches.match(new Request(url.pathname))
                  .then((pathResponse) => {
                    if (pathResponse) {
                      return pathResponse;
                    }
                    return caches.match(OFFLINE_URL);
                  });
              }

              return caches.match(OFFLINE_URL);
            });
        })
    );
    return;
  }

  // For other requests - Cache first, fallback to network
  event.respondWith(
    caches.match(event.request)
      .then((cachedResponse) => {
        if (cachedResponse) {
          // Return cached response and update cache in background
          fetchAndUpdateCache(event.request);
          return cachedResponse;
        }
        return fetchAndUpdateCache(event.request);
      })
  );
});

// Helper function to fetch and update cache
function fetchAndUpdateCache(request) {
  return fetch(request)
    .then((response) => {
      if (!response || response.status !== 200 || response.type !== 'basic') {
        return response;
      }

      const responseToCache = response.clone();
      caches.open(CACHE_NAME)
        .then((cache) => {
          cache.put(request, responseToCache)
            .catch(error => {
              console.warn('Failed to cache response in fetchAndUpdateCache:', error);
              // Continue despite the failure
            });
        })
        .catch(error => {
          console.warn('Failed to open cache in fetchAndUpdateCache:', error);
          // Continue despite the failure
        });

      return response;
    })
    .catch((error) => {
      console.error('Fetch failed:', error);
      throw error;
    });
}

// IndexedDB for storing offline requests
const DB_NAME = 'tailorfit-offline-requests';
const DB_VERSION = 1;
const STORE_NAME = 'offline-requests';

// Open IndexedDB
function openDB() {
  return new Promise((resolve, reject) => {
    const request = indexedDB.open(DB_NAME, DB_VERSION);

    request.onerror = (event) => {
      reject('IndexedDB error: ' + event.target.errorCode);
    };

    request.onsuccess = (event) => {
      resolve(event.target.result);
    };

    request.onupgradeneeded = (event) => {
      const db = event.target.result;
      if (!db.objectStoreNames.contains(STORE_NAME)) {
        db.createObjectStore(STORE_NAME, { keyPath: 'id', autoIncrement: true });
      }
    };
  });
}

// Save POST request for later
async function savePostRequestForLater(request) {
  try {
    const db = await openDB();
    const transaction = db.transaction(STORE_NAME, 'readwrite');
    const store = transaction.objectStore(STORE_NAME);

    const requestData = await request.clone().text();
    const url = request.url;

    store.add({
      url,
      method: request.method,
      headers: Array.from(request.headers.entries()),
      body: requestData,
      timestamp: Date.now()
    });

    console.log('Saved request for later submission');
  } catch (error) {
    console.error('Error saving request:', error);
  }
}

// Sync event - try to send stored requests
self.addEventListener('sync', (event) => {
  if (event.tag === 'sync-pending-requests') {
    event.waitUntil(syncPendingRequests());
  }
});

// Function to sync pending requests
async function syncPendingRequests() {
  try {
    const db = await openDB();
    const transaction = db.transaction(STORE_NAME, 'readwrite');
    const store = transaction.objectStore(STORE_NAME);

    const requests = await getAllRequests(store);

    for (const request of requests) {
      try {
        const response = await fetch(request.url, {
          method: request.method,
          headers: new Headers(request.headers),
          body: request.body
        });

        if (response.ok) {
          // If successful, delete from IndexedDB
          store.delete(request.id);
          console.log('Successfully synced request:', request.id);
        }
      } catch (error) {
        console.error('Error syncing request:', error);
      }
    }
  } catch (error) {
    console.error('Error during sync:', error);
  }
}

// Helper to get all requests from store
function getAllRequests(store) {
  return new Promise((resolve, reject) => {
    const requests = [];
    const cursorRequest = store.openCursor();

    cursorRequest.onsuccess = (event) => {
      const cursor = event.target.result;
      if (cursor) {
        requests.push(cursor.value);
        cursor.continue();
      } else {
        resolve(requests);
      }
    };

    cursorRequest.onerror = (event) => {
      reject(event.target.error);
    };
  });
}

// Add a message event listener to handle sync requests from the client
self.addEventListener('message', (event) => {
  if (event.data && event.data.type === 'SYNC_PENDING_REQUESTS') {
    // Only try to register sync if the service worker is active
    if ('sync' in self.registration) {
      self.registration.sync.register('sync-pending-requests')
        .catch(error => console.error('Sync registration failed:', error));
    }
  } else if (event.data && event.data.type === 'UPDATE_ROUTE_CACHE') {
    // Update the cache for all routes
    updateRouteCache();
  }
});

// Function to update the cache for all routes
async function updateRouteCache() {
  if (!navigator.onLine) return;

  try {
    const cache = await caches.open(CACHE_NAME);

    // Update cache for each route
    for (const route of ROUTES_TO_CACHE) {
      try {
        const response = await fetch(route);
        if (response.ok) {
          await cache.put(new Request(route), response);
          console.log('Updated cache for route:', route);
        }
      } catch (error) {
        console.warn('Failed to update cache for route:', route, error);
      }
    }

    console.log('Route cache update completed');
  } catch (error) {
    console.error('Error updating route cache:', error);
  }
}

// Periodically update the route cache when online
setInterval(() => {
  if (navigator.onLine) {
    updateRouteCache();
  }
}, 3600000); // Update every hour
