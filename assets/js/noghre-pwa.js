/*
 * Noghre PWA v3
 * - ثبت service worker
 * - مدیریت توکن دسترسی (به‌جای m)
 * - مدیریت نصب اپ
 * - تشخیص حالت standalone
 */
(function () {
	'use strict';

	/* =========================================================
	 * ۱. اگر در اپ هستیم و توکن در localStorage هست، به URL اضافه نکن
	 *    (m در URL نباید ظاهر شود)
	 * ========================================================= */
	function isMobileAppPage() {
		return !!document.getElementById('noghre-mobile-app')
			|| !!document.querySelector('.noghre-mobile-app')
			|| document.body.classList.contains('noghre-blank-template');
	}

	function getStoredToken() {
		try {
			return localStorage.getItem('noghre_access_token') || '';
		} catch (e) {
			return '';
		}
	}

	function setStoredToken(token) {
		try {
			localStorage.setItem('noghre_access_token', token);
		} catch (e) {}
	}

	function removeStoredToken() {
		try {
			localStorage.removeItem('noghre_access_token');
		} catch (e) {}
	}

	/**
	 * اگر m در URL بود (اولین ورود)، آن را پاک کن
	 * (توکن ساخت توسط خود سرور انجام می‌شود)
	 */
	function stripMFromUrl() {
		try {
			var url = new URL(window.location.href);
			if (url.searchParams.has('m')) {
				url.searchParams.delete('m');
				var newUrl = url.pathname + (url.search ? url.search : '') + url.hash;
				history.replaceState(history.state || {}, '', newUrl);
			}
		} catch (e) {}
	}

	// اجرا در ابتدای بارگذاری
	stripMFromUrl();

	/* =========================================================
	 * ۲. Service Worker
	 * ========================================================= */
	if (!('serviceWorker' in navigator)) return;

	var pwaData = window.noghrePWA || {};
	if (!pwaData.swUrl) return;

	function isStandalone() {
		return window.matchMedia('(display-mode: standalone)').matches ||
		       window.navigator.standalone === true ||
		       document.referrer.indexOf('android-app://') === 0;
	}

	window.addEventListener('load', function () {
		navigator.serviceWorker.register(pwaData.swUrl, {
			scope: pwaData.scope || '/'
		}).then(function (registration) {
			registration.addEventListener('updatefound', function () {
				var newWorker = registration.installing;
				if (!newWorker) return;
				newWorker.addEventListener('statechange', function () {
					if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
						console.log('Noghre PWA: New version available. Refresh to update.');
					}
				});
			});
		}).catch(function (err) {
			console.warn('Noghre PWA: SW registration failed', err);
		});
	});

	/* =========================================================
	 * ۳. دکمه نصب
	 * ========================================================= */
	var installBtn = document.getElementById('noghre-pwa-install');
	var deferredPrompt = null;

	if (isStandalone()) {
		if (installBtn) installBtn.style.display = 'none';
		return;
	}

	window.addEventListener('beforeinstallprompt', function (e) {
		e.preventDefault();
		deferredPrompt = e;
		if (installBtn) installBtn.style.display = 'flex';
	});

	if (installBtn) {
		installBtn.addEventListener('click', function () {
			if (!deferredPrompt) return;
			deferredPrompt.prompt();
			deferredPrompt.userChoice.then(function () {
				deferredPrompt = null;
				installBtn.style.display = 'none';
			});
		});
	}

	window.addEventListener('appinstalled', function () {
		if (installBtn) installBtn.style.display = 'none';
		deferredPrompt = null;
	});

	/* =========================================================
	 * ۴. تزریق توکن به درخواست‌های fetch (برای AJAX)
	 * ========================================================= */
	if (window.fetch && !window.__noghreFetchPatched) {
		window.__noghreFetchPatched = true;

		var originalFetch = window.fetch;

		window.fetch = function (input, init) {
			init = init || {};

			// فقط به درخواست‌های هم‌دامنه
			var url = (typeof input === 'string') ? input : (input && input.url ? input.url : '');
			var isSameOrigin = url && url.indexOf(window.location.origin) === 0;

			var token = getStoredToken();

			if (token && isSameOrigin) {
				// اگر FormData است، توکن را به آن اضافه کن
				if (init.body instanceof FormData) {
					if (!init.body.has('access_token')) {
						init.body.append('access_token', token);
					}
				} else {
					// در غیر این صورت، هدر اضافه کن
					init.headers = init.headers || {};
					if (init.headers instanceof Headers) {
						if (!init.headers.has('X-Noghre-Token')) {
							init.headers.set('X-Noghre-Token', token);
						}
					} else if (typeof init.headers === 'object') {
						if (!init.headers['X-Noghre-Token']) {
							init.headers['X-Noghre-Token'] = token;
						}
					}
				}
			}

			return originalFetch.call(this, input, init);
		};
	}

	/* =========================================================
	 * ۵. بررسی خودکار توکن (هر ۱۰ دقیقه یک‌بار)
	 * ========================================================= */
	if (isMobileAppPage()) {
		var tokenCheckInterval = 10 * 60 * 1000; // 10 دقیقه
		var ajaxUrl = (pwaData.ajaxUrl || '/wp-admin/admin-ajax.php');

		function verifyToken() {
			var token = getStoredToken();
			if (!token) return;

			var fd = new FormData();
			fd.append('action', 'noghre_verify_token');
			fd.append('token', token);

			fetch(ajaxUrl, { method: 'POST', body: fd })
				.then(function (res) { return res.json(); })
				.then(function (data) {
					if (data.success) {
						// اگر توکن تمدید شد، ذخیره کن
						if (data.data && data.data.refreshed) {
							// توکن همان است، فقط expires_at آپدیت شده
							console.log('Noghre: Token refreshed automatically.');
						}
					} else {
						// توکن نامعتبر → پاک کن و به صفحه ورود برو
						if (data.data && data.data.code === 'admin_key_changed') {
							removeStoredToken();
							window.location.reload();
						} else if (data.data && (data.data.code === 'token_expired' || data.data.code === 'token_not_found')) {
							removeStoredToken();
							window.location.href = window.location.pathname;
						}
					}
				})
				.catch(function () {
					// خطای شبکه، نادیده بگیر
				});
		}

		// بررسی اولیه بعد از ۳۰ ثانیه
		setTimeout(verifyToken, 30 * 1000);

		// بررسی دوره‌ای
		setInterval(verifyToken, tokenCheckInterval);
	}

})();