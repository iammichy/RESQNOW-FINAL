// ResQNow Service Worker — bump version to force refresh on deploy
const CACHE_VERSION = 'resqnow-v4';
const STATIC_CACHE  = `${CACHE_VERSION}-static`;
const API_CACHE     = `${CACHE_VERSION}-api`;

// Minimal precache — only files guaranteed to exist
// Using Promise.allSettled so a single 404 never blocks SW install
const PRECACHE = [
  '/public/assets/icons/icon-192x192.png',
  '/public/assets/icons/icon-512x512.png',
];

// ── Install ───────────────────────────────────────────────────────────
self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(STATIC_CACHE).then(cache =>
      Promise.allSettled(
        PRECACHE.map(url =>
          fetch(url, { credentials: 'same-origin' })
            .then(r => { if (r.ok) cache.put(url, r); })
            .catch(() => {})
        )
      )
    ).then(() => self.skipWaiting())
  );
});

// ── Activate ──────────────────────────────────────────────────────────
self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys()
      .then(keys => Promise.all(
        keys.filter(k => k !== STATIC_CACHE && k !== API_CACHE)
            .map(k => caches.delete(k))
      ))
      .then(() => self.clients.claim())
  );
});

// ── Fetch ─────────────────────────────────────────────────────────────
self.addEventListener('fetch', event => {
  const url = new URL(event.request.url);
  if (!url.protocol.startsWith('http')) return;
  if (event.request.method !== 'GET') {
    event.respondWith(
      fetch(event.request).catch(() =>
        new Response(JSON.stringify({ error: 'offline' }), {
          status: 503,
          headers: { 'Content-Type': 'application/json' },
        })
      )
    );
    return;
  }
  if (url.pathname.includes('/api/v1/')) {
    event.respondWith(networkFirst(event.request));
    return;
  }
  if (url.pathname.match(/\.(css|js|png|jpg|jpeg|svg|woff2?|ico)$/)) {
    event.respondWith(cacheFirst(event.request));
    return;
  }
  event.respondWith(networkFirstWithFallback(event.request));
});

async function cacheFirst(req) {
  const cached = await caches.match(req);
  if (cached) return cached;
  try {
    const res = await fetch(req);
    if (res.ok) (await caches.open(STATIC_CACHE)).put(req, res.clone());
    return res;
  } catch {
    return new Response('Offline', { status: 503 });
  }
}

async function networkFirst(req) {
  try {
    const res = await fetch(req);
    if (res.ok) (await caches.open(API_CACHE)).put(req, res.clone());
    return res;
  } catch {
    const cached = await caches.match(req);
    return cached || new Response(JSON.stringify({ error: 'offline' }), {
      status: 503, headers: { 'Content-Type': 'application/json' },
    });
  }
}

async function networkFirstWithFallback(req) {
  try {
    const res = await fetch(req);
    if (res.ok) (await caches.open(STATIC_CACHE)).put(req, res.clone());
    return res;
  } catch {
    const cached = await caches.match(req);
    if (cached) return cached;
    return new Response(
      '<!DOCTYPE html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Offline</title><style>body{font-family:sans-serif;display:flex;align-items:center;justify-content:center;height:100vh;margin:0;background:#f8fafc;text-align:center;padding:20px}h2{color:#dc2626}p{color:#64748b}</style></head><body><div><h2>You are offline</h2><p>Check your connection and try again.</p></div></body></html>',
      { status: 200, headers: { 'Content-Type': 'text/html' } }
    );
  }
}
