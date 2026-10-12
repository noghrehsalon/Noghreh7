/*
 * Noghre Service Worker
 * استراتژی:
 *   - فایل‌های استاتیک (CSS/JS/تصاویر): Cache First
 *   - صفحات HTML: Network First با fallback به cache
 */

var CACHE_NAME = 'noghre-pwa-v1';
var OFFLINE_URL = '/mobile/';

var PRECACHE_URLS = [
	'/',
	'/mobile/'
];

// نصب
self.addEventListener('install', function (event) {
	event.waitUntil(
		caches.open(CACHE_NAME).then(function (cache) {
			return cache.addAll(PRECACHE_URLS).catch(function () {
				// اگر یکی از URLها fail شد، ادامه بده
				return Promise.resolve();
			});
		}).then(function () {
			return self.skipWaiting();
		})
	);
});

// فعال‌سازی — پاک‌سازی کش قدیمی
self.addEventListener('activate', function (event) {
	event.waitUntil(
		caches.keys().then(function (keys) {
			return Promise.all(
				keys.map(function (key) {
					if (key !== CACHE_NAME) {
						return caches.delete(key);
					}
				})
			);
		}).then(function () {
			return self.clients.claim();
		})
	);
});

// fetch
self.addEventListener('fetch', function (event) {
	var request = event.request;

	// فقط GET
	if (request.method !== 'GET') return;

	var url = new URL(request.url);

	// فقط همان دامنه
	if (url.origin !== location.origin) return;

	// از کش برای فایل‌های استاتیک
	var isStatic = /\.(css|js|woff2?|ttf|png|jpe?g|gif|svg|webp|ico)$/i.test(url.pathname);

	if (isStatic) {
		event.respondWith(
			caches.match(request).then(function (cached) {
				if (cached) return cached;

				return fetch(request).then(function (response) {
					if (!response || response.status !== 200) return response;

					var responseClone = response.clone();
					caches.open(CACHE_NAME).then(function (cache) {
						cache.put(request, responseClone);
					});
					return response;
				});
			})
		);
		return;
	}

	// برای HTML — Network First
	if (request.headers.get('accept') && request.headers.get('accept').indexOf('text/html') !== -1) {
		event.respondWith(
			fetch(request).then(function (response) {
				var responseClone = response.clone();
				caches.open(CACHE_NAME).then(function (cache) {
					cache.put(request, responseClone);
				});
				return response;
			}).catch(function () {
				return caches.match(request).then(function (cached) {
					return cached || caches.match(OFFLINE_URL);
				});
			})
		);
		return;
	}

	// بقیه: Network با fallback به cache
	event.respondWith(
		fetch(request).catch(function () {
			return caches.match(request);
		})
	);
});