// ZawajAfrica Service Worker
const CACHE_NAME = 'zawajafrica-v1';
const urlsToCache = [
  '/',
  '/dashboard',
  '/messages',
  '/images/fav.png',
  '/manifest.json'
];

// Install event
self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => {
        console.log('Opened cache');
        return cache.addAll(urlsToCache);
      })
  );
});

// Fetch event
self.addEventListener('fetch', event => {
  // Only handle GET requests from our own origin – allow others (like Adsterra) to bypass the service worker
  if (event.request.method !== 'GET') {
    return; // Let non-GET requests pass through without interception
  }

  const requestUrl = new URL(event.request.url);

  // Completely bypass service worker for third-party requests
  if (requestUrl.origin !== self.location.origin) {
    return; // Don't interfere with third-party scripts/assets (Adsterra, etc.)
  }

  // Don't intercept requests that might be problematic
  // Skip service worker for API calls, external resources, etc.
  if (requestUrl.pathname.startsWith('/api/') || 
      requestUrl.pathname.includes('adsterra') ||
      requestUrl.pathname.includes('highperformanceformat') ||
      requestUrl.pathname.includes('highcpmrevenuegate')) {
    return; // Let these pass through directly
  }

  // Handle navigation requests with network-first strategy to avoid redirect issues
  if (event.request.mode === 'navigate') {
    event.respondWith(
      fetch(event.request, {
        redirect: 'follow',
        credentials: 'same-origin',
        cache: 'no-store' // Don't use browser cache for navigation requests
      }).then(response => {
        // Only cache successful non-redirect responses (status 200-299)
        // Don't cache redirect responses (type === 'opaqueredirect')
        if (response.ok && response.status >= 200 && response.status < 300 && 
            response.type !== 'opaqueredirect' && response.type !== 'opaque') {
          const responseToCache = response.clone();
          caches.open(CACHE_NAME).then(cache => {
            cache.put(event.request, responseToCache);
          });
        }
        return response;
      }).catch(error => {
        // Fallback to cache if network fails
        return caches.match(event.request).then(cachedResponse => {
          if (cachedResponse) {
            return cachedResponse;
          }
          // Final fallback
          return caches.match('/dashboard') || caches.match('/');
        });
      })
    );
    return;
  }

  // For non-navigation requests, use cache-first strategy
  event.respondWith(
    caches.match(event.request).then(cachedResponse => {
      if (cachedResponse) {
        return cachedResponse;
      }

      return fetch(event.request, {
        redirect: 'follow',
        credentials: 'same-origin'
      }).then(response => {
        // Cache successful responses (status 200-299)
        // Don't cache redirect responses
        if (response.ok && response.status >= 200 && response.status < 300 && 
            response.type !== 'opaqueredirect' && response.type !== 'opaque') {
          const responseToCache = response.clone();
          caches.open(CACHE_NAME).then(cache => {
            cache.put(event.request, responseToCache);
          });
        }
        return response;
      }).catch(error => {
        // Silently handle fetch errors
        return new Response('', { status: 503, statusText: 'Service Unavailable' });
      });
    })
  );
});

// Background sync for badge updates
self.addEventListener('sync', event => {
  if (event.tag === 'badge-update') {
    event.waitUntil(updateBadge());
  }
});

// Update app badge
async function updateBadge() {
  try {
    // This would typically fetch the latest unread count from your API
    const response = await fetch('/api/message-badge');
    const data = await response.json();
    
    if ('setAppBadge' in navigator) {
      if (data.total_unread > 0) {
        await navigator.setAppBadge(data.total_unread);
      } else {
        await navigator.clearAppBadge();
      }
    }
  } catch (error) {
    console.error('Failed to update badge:', error);
  }
}

// Handle push notifications (for future implementation)
self.addEventListener('push', event => {
  const options = {
    body: event.data ? event.data.text() : 'New message received!',
    icon: '/images/fav.png',
    badge: '/images/fav.png',
    vibrate: [100, 50, 100],
    data: {
      dateOfArrival: Date.now(),
      primaryKey: 1
    },
    actions: [
      {
        action: 'explore',
        title: 'View Messages',
        icon: '/images/fav.png'
      },
      {
        action: 'close',
        title: 'Close',
        icon: '/images/fav.png'
      }
    ]
  };

  event.waitUntil(
    self.registration.showNotification('ZawajAfrica', options)
  );
});

// Handle notification clicks
self.addEventListener('notificationclick', event => {
  event.notification.close();

  if (event.action === 'explore') {
    // Open the messages page
    event.waitUntil(
      clients.openWindow('/messages')
    );
  } else if (event.action === 'close') {
    // Just close the notification
    return;
  } else {
    // Default action - open the app
    event.waitUntil(
      clients.openWindow('/dashboard')
    );
  }
});

console.log('ZawajAfrica Service Worker loaded');
