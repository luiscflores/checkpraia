const CACHE = 'checkpraia-v2';
const STATIC_ASSETS = [
  '/',
  '/offline',
  '/manifest.json',
];

const TILE_CACHE = 'checkpraia-tiles-v2';

self.addEventListener('install', (event) => {
  self.skipWaiting();
  event.waitUntil(
    caches.open(CACHE).then((cache) => cache.addAll(STATIC_ASSETS))
  );
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((keys) =>
      Promise.all(
        keys
          .filter((key) => key !== CACHE && key !== TILE_CACHE)
          .map((key) => caches.delete(key))
      )
    )
  );
  self.clients.claim();
});

function isTileRequest(url) {
  try {
    const u = new URL(url);
    return (
      u.hostname === 'tile.openstreetmap.org' ||
      u.hostname === 'server.arcgisonline.com' ||
      u.pathname.includes('/tile/') ||
      u.pathname.includes('/maps/tile')
    );
  } catch {
    return false;
  }
}

function isApiRequest(url) {
  const path = new URL(url).pathname;
  return path.includes('/livewire/') || path.includes('/up');
}

self.addEventListener('fetch', (event) => {
  const url = event.request.url;

  if (isTileRequest(url)) {
    event.respondWith(
      caches.open(TILE_CACHE).then((cache) =>
        cache.match(event.request).then((cached) => {
          const fetched = fetch(event.request).then((response) => {
            if (response.ok) cache.put(event.request, response.clone());
            return response;
          });
          return cached || fetched;
        })
      )
    );
    return;
  }

  if (isApiRequest(url)) {
    event.respondWith(
      fetch(event.request).catch(() => new Response(null, { status: 503 }))
    );
    return;
  }

  // Network-first for pages: online → always fresh, offline → cached fallback
  event.respondWith(
    fetch(event.request).then((response) => {
      const copy = response.clone();
      caches.open(CACHE).then((cache) => cache.put(event.request, copy));
      return response;
    }).catch(() => caches.match(event.request))
  );
});

self.addEventListener('push', (event) => {
  let data = { title: 'CheckPraia', body: '' };
  try {
    if (event.data) data = event.data.json();
  } catch {}

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
