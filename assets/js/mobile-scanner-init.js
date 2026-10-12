/* ==========================================================================
   Noghre App — QR Scanner Init
   مقداردهی اسکنر QR در اپ موبایل
   وابسته به: app-shared.js, app-qr-scanner.js
   ========================================================================== */
(function (window, document) {
	'use strict';

	function init() {
		var startBtn = document.getElementById('noghre-qr-start');
		var stopBtn  = document.getElementById('noghre-qr-stop');
		var statusEl = document.getElementById('noghre-qr-status');
		var scanner  = null;

		if (!startBtn) return;

		/* 🔑 اگه قبلاً bind شده، اول unbind کن */
		if (startBtn.dataset.bound === '1') {
			// کلون کردن دکمه برای حذف همه event listenerها
			var newStartBtn = startBtn.cloneNode(true);
			startBtn.parentNode.replaceChild(newStartBtn, startBtn);
			startBtn = newStartBtn;

			if (stopBtn) {
				var newStopBtn = stopBtn.cloneNode(true);
				stopBtn.parentNode.replaceChild(newStopBtn, stopBtn);
				stopBtn = newStopBtn;
			}
		}
		startBtn.dataset.bound = '1';

		var cfg      = window.noghreScannerConfig || {};
		var adminKey = cfg.adminKey || '';
		var baseUrl  = cfg.baseUrl || '/mobile/';

		/* 🔑 اگر کتابخانه اسکنر لود نشده، یک بار رفرش کن */
		if (!window.NoghreQRScanner) {
			if (statusEl) {
				statusEl.textContent = 'کتابخانه اسکنر در حال بارگذاری مجدد...';
				statusEl.className = 'noghre-qr-scanner-status is-error';
			}
			startBtn.disabled = true;

			var refreshKey = 'noghre_scanner_refreshed';
			try {
				if (!sessionStorage.getItem(refreshKey)) {
					sessionStorage.setItem(refreshKey, '1');
					window.addEventListener('load', function () {
						setTimeout(function () {
							window.location.reload();
						}, 1000);
					});
				} else {
					sessionStorage.removeItem(refreshKey);
					if (statusEl) {
						statusEl.textContent = 'کتابخانه اسکنر بارگذاری نشد. لطفاً صفحه را دستی رفرش کنید.';
					}
				}
			} catch (e) {
				if (statusEl) {
					statusEl.textContent = 'کتابخانه اسکنر بارگذاری نشد. لطفاً صفحه را دستی رفرش کنید.';
				}
			}
			return;
		}

		var hasJsQR    = (typeof window.jsQR === 'function');
		var hasBarcode = ('BarcodeDetector' in window);

		if (!hasJsQR && !hasBarcode) {
			if (statusEl) {
				statusEl.textContent = 'هیچ کتابخانه QR در دسترس نیست.';
				statusEl.className = 'noghre-qr-scanner-status is-error';
			}
			startBtn.disabled = true;
			return;
		}

		/* 🔑 شروع اسکن */
		startBtn.addEventListener('click', function () {
			if (!scanner) {
				scanner = new window.NoghreQRScanner({
					videoId:  'noghre-qr-video',
					canvasId: 'noghre-qr-canvas',
					statusId: 'noghre-qr-status',
					onResult: function (value) {
						if (scanner) scanner.stop();
						if (startBtn) startBtn.style.display = '';
						if (stopBtn) stopBtn.style.display = 'none';

						var code = '';
						try {
							var u = new URL(value, window.location.origin);
							code = u.searchParams.get('code') || '';
						} catch (e) {}

						if (statusEl) {
							statusEl.textContent = 'کد شناسایی شد، در حال انتقال...';
							statusEl.className = 'noghre-qr-scanner-status is-success';
						}

						if (code) {
							if (window.NoghreMobileApp && typeof window.NoghreMobileApp.loadSection === 'function') {
								window.NoghreMobileApp.loadSection('check', code, true);
							} else {
								window.location.href = baseUrl + '?section=check&code=' + encodeURIComponent(code);
							}
						}
					},
					onError: function () {
						if (startBtn) startBtn.style.display = '';
						if (stopBtn) stopBtn.style.display = 'none';
					}
				});
			}
			startBtn.style.display = 'none';
			if (stopBtn) stopBtn.style.display = '';
			scanner.start();
		});

		/* 🔑 توقف اسکن */
		if (stopBtn) {
			stopBtn.addEventListener('click', function () {
				if (scanner) scanner.stop();
				startBtn.style.display = '';
				stopBtn.style.display = 'none';
				if (statusEl) {
					statusEl.textContent = 'اسکن متوقف شد.';
					statusEl.className = 'noghre-qr-scanner-status';
				}
			});
		}
	}

	/* 🔑 برای اجرا از داخل اپ (بعد از AJAX) هم قابل استفاده باشه */
	window.NoghreScannerInit = init;

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})(window, document);