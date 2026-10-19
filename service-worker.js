/* D&A Shop Service Worker — customer-facing only (admin pages unregister this) */
const CACHE_VERSION = 'da-shop-v1';
const OFFLINE_URL = 'offline.html';

const PRECACHE = [
  'offline.html',
  'assets/css/style.min.css',
  'assets/css/v4.css',
  'assets/js/main.min.js',
  'favicon.svg',
  'icons/icon-192.png',
  'icons/icon-512.png',
  'manifest.json',
];

function baseFromScope(scope) {
  try {
    const u = new URL(scope);
    let p = u.pathname;
    if (!p.endsWith('/')) p += '/';
    return p === '/' ? '' : p.replace(/\/$/, '');
  } catch (e) {
    return '';
  }
}

function withBase(base, path) {
  const clean = path.replace(/^\//, '');
  return base ? base + '/' + clean : '/' + clean;
}

self.addEventListener('install', function (event) {
  const base = baseFromScope(self.registration.scope);
  event.waitUntil(
    caches.open(CACHE_VERSION).then(function (cache) {
      return cache.addAll(PRECACHE.map(function (p) { return withBase(base, p); })).catch(function () {
        return null;
      });
    }).then(function () { return self.skipWaiting(); })
  );
});

self.addEventListener('activate', function (event) {
  event.waitUntil(
    caches.keys().then(function (keys) {
      return Promise.all(
        keys.filter(function (k) { return k !== CACHE_VERSION; }).map(function (k) { return caches.delete(k); })
      );
    }).then(function () { return self.clients.claim(); })
  );
});

self.addEventListener('fetch', function (event) {
  const req = event.request;
  const url = new URL(req.url);

  if (req.method !== 'GET') return;
  if (url.pathname.includes('config.php') || url.pathname.includes('submit-order') || url.pathname.includes('logout.php') || url.pathname.includes('login.php')) {
    return;
  }

  if (req.headers.get('purpose') === 'prefetch' || req.headers.get('sec-purpose') === 'prefetch') {
    return;
  }

  var accept = req.headers.get('accept') || '';
  var isDynamicPage = url.pathname.endsWith('.php') || accept.indexOf('text/html') !== -1;
  if (isDynamicPage) {
    event.respondWith(fetch(req));
    return;
  }

  if (req.mode === 'navigate') {
    event.respondWith(
      fetch(req).catch(function () {
        const base = baseFromScope(self.registration.scope);
        return caches.match(withBase(base, OFFLINE_URL)).then(function (r) {
          return r || new Response('', { status: 200, headers: { 'Content-Type': 'text/html' } });
        });
      })
    );
    return;
  }

  event.respondWith(
    caches.match(req).then(function (cached) {
      if (cached) return cached;
      return fetch(req).then(function (res) {
        if (res && res.status === 200 && res.type === 'basic') {
          const clone = res.clone();
          caches.open(CACHE_VERSION).then(function (c) { c.put(req, clone); });
        }
        return res;
      }).catch(function () {
        if (req.destination === 'document') {
          const base = baseFromScope(self.registration.scope);
          return caches.match(withBase(base, OFFLINE_URL)).then(function (r) {
            return r || new Response('', { status: 200, headers: { 'Content-Type': 'text/html' } });
          });
        }
        return new Response('', { status: 200 });
      });
    })
  );
});
