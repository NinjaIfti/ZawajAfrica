// ZawajAfrica Service Worker
const CACHE_NAME = 'zawajafrica-v2'; // Updated version to force cache refresh
const urlsToCache = [
  '/',
  '/images/fav.png',
  '/manifest.json'
  // Removed /dashboard and /messages from initial cache - they should be fetched fresh
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
  // Force the waiting service worker to become the active service worker
  self.skipWaiting();
});

// Activate event - clean up old caches
self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys().then(cacheNames => {
      return Promise.all(
        cacheNames.map(cacheName => {
          if (cacheName !== CACHE_NAME) {
            console.log('Deleting old cache:', cacheName);
            return caches.delete(cacheName);
          }
        })
      );
    })
  );
  // Take control of all pages immediately
  return self.clients.claim();
});

// Fetch event
self.addEventListener('fetch', event => {
  // Skip non-GET requests
  if (event.request.method !== 'GET') {
    return;
  }

  const url = new URL(event.request.url);

  // Completely bypass service worker for third-party requests
  if (url.origin !== self.location.origin) {
    return; // Don't interfere with third-party scripts/assets (Adsterra, etc.)
  }

  // Don't cache these routes - let browser handle them directly
  // This prevents redirect issues with authentication
  const excludedPaths = [
    '/dashboard',
    '/login',
    '/logout',
    '/api',
    '/mobile-login',
    '/register',
    '/password',
    '/email/verify',
    '/admin'
  ];
  
  const shouldExclude = excludedPaths.some(path => url.pathname.startsWith(path));

  // Also exclude external/ad scripts
  if (url.pathname.includes('adsterra') ||
      url.pathname.includes('highperformanceformat') ||
      url.pathname.includes('highcpmrevenuegate') ||
      url.pathname.includes('effectivegatecpm')) {
    return; // Let these pass through directly
  }

  // If route should be excluded, let browser handle it normally
  if (shouldExclude) {
    return; // Let browser handle these routes directly without service worker interference
  }

  // For other routes, use cache-first strategy
  event.respondWith(
    caches.match(event.request).then(response => {
      return response || fetch(event.request, {
        redirect: 'follow' // Explicitly handle redirects
      }).then(fetchResponse => {
        // Only cache successful responses for static assets
        if (fetchResponse.ok && fetchResponse.status >= 200 && fetchResponse.status < 300) {
          const responseToCache = fetchResponse.clone();
          caches.open(CACHE_NAME).then(cache => {
            cache.put(event.request, responseToCache);
          });
        }
        return fetchResponse;
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
