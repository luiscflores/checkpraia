const VERSION = 'checkpraia-v4';
const TILE_CACHE = 'checkpraia-tiles-v4';
const STATIC_CACHE = 'checkpraia-static-v4';
const PAGE_CACHE = 'checkpraia-pages-v4';

const MAX_TILES = 500;

const PRECACHE_URLS = [
  '/',
  '/offline',
  '/manifest.json',
];

self.addEventListener('install', (event) => {
  self.skipWaiting();
  event.waitUntil(
    caches.open(PAGE_CACHE).then((cache) => cache.addAll(PRECACHE_URLS))
  );
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((keys) =>
      Promise.all(
        keys
          .filter((key) => key !== TILE_CACHE && key !== STATIC_CACHE && key !== PAGE_CACHE)
          .map((key) => caches.delete(key))
      )
    )
  );
  self.clients.claim();
});

// ── Helpers ─────────────────────────────────────────────────────────────

function isTileRequest(url) {
  try {
    const u = new URL(url);
    return (
      u.hostname === 'tile.openstreetmap.org' ||
      u.hostname === 'server.arcgisonline.com' ||
      u.hostname === 'basemaps.cartocdn.com' ||
      u.pathname.includes('/tile/') ||
      u.pathname.includes('/maps/tile')
    );
  } catch {
    return false;
  }
}

function isLivewireRequest(url) {
  try {
    return new URL(url).pathname.includes('/livewire/');
  } catch {
    return false;
  }
}

function isStaticAsset(url) {
  try {
    const u = new URL(url);
    return (
      u.pathname.startsWith('/build/') ||
      u.pathname.startsWith('/storage/') ||
      u.pathname.startsWith('/icon-') ||
      u.pathname === '/favicon.ico' ||
      u.pathname === '/manifest.json' ||
      u.pathname === '/logo.png'
    );
  } catch {
    return false;
  }
}

function isNavigationalRequest(request) {
  return request.mode === 'navigate' ||
    (request.method === 'GET' && request.destination === 'document');
}

async function limitCacheSize(cache, maxItems) {
  const keys = await cache.keys();
  if (keys.length > maxItems) {
    const toDelete = keys.slice(0, keys.length - maxItems);
    await Promise.all(toDelete.map((key) => cache.delete(key)));
  }
}

// ── Fetch handler ────────────────────────────────────────────────────────

self.addEventListener('fetch', (event) => {
  const { request } = event;
  const url = request.url;

  // 1. Map tiles: cache-first with LRU limit
  if (isTileRequest(url)) {
    event.respondWith(
      caches.open(TILE_CACHE).then(async (cache) => {
        const cached = await cache.match(request);
        const fetchPromise = fetch(request).then((response) => {
          if (response.ok) {
            cache.put(request, response.clone());
            limitCacheSize(cache, MAX_TILES);
          }
          return response;
        }).catch(() => cached);
        return cached || fetchPromise || new Response('', { status: 504 });
      })
    );
    return;
  }

  // 2. Livewire API: network-only (must be fresh)
  if (isLivewireRequest(url)) {
    event.respondWith(
      fetch(request).catch(() => new Response(null, { status: 503 }))
    );
    return;
  }

  // 3. Static build assets: cache-first (immutable, hashed filenames)
  if (isStaticAsset(url)) {
    event.respondWith(
      caches.open(STATIC_CACHE).then((cache) =>
        cache.match(request).then((cached) => {
          const fetched = fetch(request).then((response) => {
            if (response.ok) cache.put(request, response.clone());
            return response;
          }).catch(() => cached);
          return cached || fetched || new Response('', { status: 504 });
        })
      )
    );
    return;
  }

  // 4. Navigational requests (pages): network-first
  if (isNavigationalRequest(request)) {
    event.respondWith(
      fetch(request)
        .then((response) => {
          if (response.ok) {
            const cloned = response.clone();
            caches.open(PAGE_CACHE).then((cache) => cache.put(request, cloned));
            return response;
          }
          return caches.match(request).then((cached) => {
            return cached || response;
          });
        })
        .catch(() => {
          return caches.match(request).then((cached) => {
            return cached || caches.match('/offline') || new Response('', { status: 504 });
          });
        })
    );
    return;
  }

  // 5. Everything else (images, fonts, etc.): network-first
  event.respondWith(
    fetch(request).then((response) => {
      if (response.ok && request.method === 'GET') {
        const cloned = response.clone();
        caches.open(PAGE_CACHE).then((cache) => cache.put(request, cloned)).catch(() => {});
      }
      return response;
    }).catch(() =>
      caches.match(request).then((cached) => cached || new Response('', { status: 504 }))
    )
  );
});

// ── Push notifications ──────────────────────────────────────────────────

self.addEventListener('push', (event) => {
  let data = { title: 'CheckPraia', body: '' };
  try { if (event.data) data = event.data.json(); } catch { /* empty */ }

  const options = {
    body: data.body || '',
    icon: data.icon || '/icon-192.png',
    badge: data.badge || '/icon-192.png',
    vibrate: data.vibrate || [200, 100, 200],
    data: data.data || {},
  };

  event.waitUntil(self.registration.showNotification(data.title, options));
});

self.addEventListener('notificationclick', (event) => {
  event.notification.close();
  const url = event.notification.data?.url || '/';
  event.waitUntil(
    clients.matchAll({ type: 'window', includeUncontrolled: true }).then((windowClients) => {
      const existing = windowClients.find((c) => c.url === url && 'focus' in c);
      if (existing) return existing.focus();
      return clients.openWindow(url);
    })
  );
});
