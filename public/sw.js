const CACHE_NAME = 'checkpraia-cache-v2';
const ASSETS_TO_CACHE = [
  '/',
  '/favicon.ico',
  '/manifest.json',
  'https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap',
  'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css',
  'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js'
];

// Install Service Worker
self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => {
        return cache.addAll(ASSETS_TO_CACHE);
      })
      .then(() => self.skipWaiting())
  );
});

// Activate Service Worker
self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys().then(cacheNames => {
      return Promise.all(
        cacheNames.map(cache => {
          if (cache !== CACHE_NAME) {
            return caches.delete(cache);
          }
        })
      );
    }).then(() => self.clients.claim())
  );
});

// Fetch Request Interception
self.addEventListener('fetch', event => {
  if (event.request.method !== 'GET' || event.request.url.includes('/livewire/')) {
    return;
  }

  const isNavigation = event.request.mode === 'navigate';
  const isStatic = event.request.url.match(/\.(css|js|png|jpg|jpeg|gif|ico|svg|woff2?)$/);

  if (isNavigation) {
    event.respondWith(
      fetch(event.request)
        .then(response => {
          const cloned = response.clone();
          caches.open(CACHE_NAME).then(cache => cache.put(event.request, cloned));
          return response;
        })
        .catch(() => caches.match(event.request).then(cached => cached || caches.match('/')))
    );
  } else if (isStatic) {
    event.respondWith(
      caches.match(event.request).then(cached => {
        const fetchAndUpdate = fetch(event.request).then(response => {
          if (response && response.status === 200) {
            const cloned = response.clone();
            caches.open(CACHE_NAME).then(cache => cache.put(event.request, cloned));
          }
          return response;
        }).catch(() => cached);
        return cached || fetchAndUpdate;
      })
    );
  } else {
    event.respondWith(
      fetch(event.request)
        .then(response => response)
        .catch(() => caches.match(event.request))
    );
  }
});
