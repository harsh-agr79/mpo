var staticCacheName = "pwa-v" + new Date().getTime();
var filesToCache = [
    '/offline',
    '/css/app.css',
    '/js/app.js',
    "/storage/01JTR389KBW06XQ6WJ2PZHX974.png",
    "/storage/01JTR3FZA82ZQF775RKPMZ1F49.png",
    "/storage/01JTR3FZABCRV9QBTXW0TJJFJ4.png",
    "/storage/01JTR3FZAE60Z4CFMRWZ69HAMF.png",
    "/storage/01JTR3FZAJ40T9S1EGJW75ZGYC.png",
    "/storage/01JTR3FZANTKHABY098BRQ2CHH.png",
    "/storage/01JTR3FZAS0JRTAFXF1HR5S3WF.png",
    "/storage/01JTR3FZAWCSSKZR6BF4N1B9CV.png"
];

// Cache on install
self.addEventListener("install", event => {
    this.skipWaiting();
    event.waitUntil(
        caches.open(staticCacheName)
            .then(cache => {
                return cache.addAll(filesToCache);
            })
    )
});

// Clear cache on activate
self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(cacheNames => {
            return Promise.all(
                cacheNames
                    .filter(cacheName => (cacheName.startsWith("pwa-")))
                    .filter(cacheName => (cacheName !== staticCacheName))
                    .map(cacheName => caches.delete(cacheName))
            );
        })
    );
});

// Serve from Cache
self.addEventListener("fetch", event => {
    event.respondWith(
        caches.match(event.request)
            .then(response => {
                return response || fetch(event.request);
            })
            .catch(() => {
                return caches.match('offline');
            })
    )
});
