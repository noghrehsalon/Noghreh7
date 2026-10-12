/*
 * NoghreQRScanner v3 — اسکنر QR با پشتیبانی همزمان از BarcodeDetector و jsQR
 * هر دو روش به‌صورت موازی اجرا می‌شوند تا هر کدام زودتر نتیجه داد استفاده شود.
 */
(function (window) {
	'use strict';

	function NoghreQRScanner(options) {
		this.videoEl  = document.getElementById(options.videoId);
		this.canvasEl = document.getElementById(options.canvasId);
		this.statusEl = document.getElementById(options.statusId);
		this.onResult = options.onResult || function () {};
		this.onError  = options.onError  || function () {};

		this.stream    = null;
		this.scanning  = false;
		this.detector  = null;
		this.rafId     = null;
		this.ctx       = null;
		this.useBarcodeDetector = false;
		this.lastTick  = 0;
		this.tickInterval = 100; // ms — هر ۱۰۰ میلی‌ثانیه یکبار فریم بگیر
	}

	NoghreQRScanner.prototype.checkSupport = function () {
		var isSecure = (window.isSecureContext === true) ||
		               (location.protocol === 'https:') ||
		               (location.hostname === 'localhost') ||
		               (location.hostname === '127.0.0.1');

		if (!isSecure) {
			return { ok: false, reason: 'https', message: 'دسترسی به دوربین فقط روی HTTPS امکان‌پذیر است.' };
		}
		if (!navigator.mediaDevices || typeof navigator.mediaDevices.getUserMedia !== 'function') {
			return { ok: false, reason: 'no-mediadevices', message: 'مرورگر از دوربین پشتیبانی نمی‌کند.' };
		}

		if ('BarcodeDetector' in window) {
			try {
				this.detector = new window.BarcodeDetector({ formats: ['qr_code'] });
				this.useBarcodeDetector = true;
			} catch (e) {
				this.useBarcodeDetector = false;
			}
		}

		var hasJsQR = typeof window.jsQR === 'function';

		if (!this.useBarcodeDetector && !hasJsQR) {
			return { ok: false, reason: 'no-decoder', message: 'هیچ کتابخانه QR در دسترس نیست.' };
		}

		return { ok: true };
	};

	NoghreQRScanner.prototype.start = function () {
		var self = this;

		var support = self.checkSupport();
		if (!support.ok) {
			if (self.statusEl) {
				self.statusEl.textContent = support.message;
				self.statusEl.className = 'noghre-qr-scanner-status is-error';
			}
			self.onError(support.reason, support.message);
			return;
		}

		if (self.statusEl) {
			self.statusEl.textContent = 'در حال فعال‌سازی دوربین...';
			self.statusEl.className = 'noghre-qr-scanner-status';
		}

		var constraints = {
			audio: false,
			video: {
				facingMode: { ideal: 'environment' },
				width:  { ideal: 1280 },
				height: { ideal: 720 }
			}
		};

		navigator.mediaDevices.getUserMedia(constraints)
			.then(function (stream) {
				self.stream = stream;

				var video = self.videoEl;
				video.srcObject = stream;
				video.setAttribute('playsinline', 'true');
				video.setAttribute('autoplay', 'true');
				video.setAttribute('muted', 'true');
				video.muted = true;

				var onReady = function () {
					video.removeEventListener('loadedmetadata', onReady);
					video.play().then(function () {
						if (self.statusEl) {
							self.statusEl.textContent = 'دوربین فعال شد. QR کد را در کادر قرار دهید.';
							self.statusEl.className = 'noghre-qr-scanner-status';
						}
						self.scanning = true;
						self.tick();
					}).catch(function (err) {
						if (self.statusEl) {
							self.statusEl.textContent = 'خطا در پخش ویدیو: ' + (err.message || 'نامشخص');
							self.statusEl.className = 'noghre-qr-scanner-status is-error';
						}
						self.onError('play-error', err && err.message);
					});
				};

				if (video.readyState >= 1) {
					onReady();
				} else {
					video.addEventListener('loadedmetadata', onReady);
				}
			})
			.catch(function (err) {
				var msg = 'دسترسی به دوربین ممکن نشد.';
				if (err && err.name) {
					switch (err.name) {
						case 'NotAllowedError':
						case 'PermissionDeniedError':
							msg = 'دسترسی به دوربین رد شد.'; break;
						case 'NotFoundError':
						case 'DevicesNotFoundError':
							msg = 'دوربینی پیدا نشد.'; break;
						case 'NotReadableError':
						case 'TrackStartError':
							msg = 'دوربین توسط برنامه دیگری استفاده می‌شود.'; break;
						case 'OverconstrainedError':
							msg = 'تنظیمات دوربین سازگار نیست.'; break;
						case 'SecurityError':
							msg = 'دسترسی به دلایل امنیتی مسدود است.'; break;
					}
				}
				if (self.statusEl) {
					self.statusEl.textContent = msg;
					self.statusEl.className = 'noghre-qr-scanner-status is-error';
				}
				self.onError(err && err.name ? err.name : 'camera-error', msg);
			});
	};

	NoghreQRScanner.prototype.tick = function () {
		var self = this;
		if (!self.scanning) return;

		var now = Date.now();
		if (now - self.lastTick < self.tickInterval) {
			self.rafId = window.requestAnimationFrame(function () { self.tick(); });
			return;
		}
		self.lastTick = now;

		var video = self.videoEl;

		if (video.readyState !== video.HAVE_ENOUGH_DATA || video.videoWidth === 0) {
			self.rafId = window.requestAnimationFrame(function () { self.tick(); });
			return;
		}

		// ====== روش ۱: BarcodeDetector ======
		if (self.useBarcodeDetector && self.detector) {
			self.detector.detect(video)
				.then(function (barcodes) {
					if (barcodes && barcodes.length > 0) {
						var value = barcodes[0].rawValue || '';
						if (value) {
							self.scanning = false;
							self.onResult(value);
							return;
						}
					}
				})
				.catch(function () { /* خطا مهم نیست، jsQR هم امتحان می‌شود */ });
		}

		// ====== روش ۲: jsQR ======
		if (typeof window.jsQR === 'function') {
			try {
				var canvas = self.canvasEl;
				var w = video.videoWidth;
				var h = video.videoHeight;
				canvas.width  = w;
				canvas.height = h;
				if (!self.ctx) self.ctx = canvas.getContext('2d', { willReadFrequently: true });
				self.ctx.drawImage(video, 0, 0, w, h);
				var imageData = self.ctx.getImageData(0, 0, w, h);

				// تلاش اول: بدون inversion
				var code = window.jsQR(imageData.data, imageData.width, imageData.height, {
					inversionAttempts: 'dontInvert'
				});

				// تلاش دوم: با inversion (برای QR های پس‌زمینه تیره)
				if (!code) {
					code = window.jsQR(imageData.data, imageData.width, imageData.height, {
						inversionAttempts: 'attemptBoth'
					});
				}

				if (code && code.data) {
					self.scanning = false;
					self.onResult(code.data);
					return;
				}
			} catch (e) {
				// خطا - ادامه بده
			}
		}

		self.rafId = window.requestAnimationFrame(function () { self.tick(); });
	};

	NoghreQRScanner.prototype.stop = function () {
		this.scanning = false;
		if (this.rafId) {
			window.cancelAnimationFrame(this.rafId);
			this.rafId = null;
		}
		if (this.stream) {
			this.stream.getTracks().forEach(function (t) { t.stop(); });
			this.stream = null;
		}
		if (this.videoEl) {
			this.videoEl.srcObject = null;
		}
	};

	window.NoghreQRScanner = NoghreQRScanner;
})(window);