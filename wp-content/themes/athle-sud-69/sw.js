const CACHE  = 'athle-sud-69-v1';
const ASSETS = [
  '/',
  '/wp-content/themes/athle-sud-69/style.css',
  '/wp-content/themes/athle-sud-69/assets/app.js',
];

self.addEventListener('install', e => {
  e.waitUntil(caches.open(CACHE).then(c => c.addAll(ASSETS)));
  self.skipWaiting();
});

self.addEventListener('activate', e => {
  e.waitUntil(
    caches.keys().then(keys =>
      Promise.all(keys.filter(k => k !== CACHE).map(k => caches.delete(k)))
    )
  );
  self.clients.claim();
});

// Stale-while-revalidate pour les assets statiques, network-first pour le reste
self.addEventListener('fetch', e => {
  const url = new URL(e.request.url);

  // Ignorer les requêtes non-GET et les requêtes admin WordPress
  if (e.request.method !== 'GET' || url.pathname.startsWith('/wp-admin')) return;

  if (url.pathname.match(/\.(css|js|png|jpg|svg|webp|woff2?)$/)) {
    // Cache-first pour les assets
    e.respondWith(
      caches.match(e.request).then(cached =>
        cached ?? fetch(e.request).then(res => {
          if (res.ok) caches.open(CACHE).then(c => c.put(e.request, res.clone()));
          return res;
        })
      )
    );
  } else {
    // Network-first pour les pages
    e.respondWith(
      fetch(e.request).catch(() => caches.match(e.request) ?? caches.match('/'))
    );
  }
});
