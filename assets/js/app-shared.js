/* ==========================================================================
   Noghre App — Shared JS
   توابع و رفتارهای مشترک بین اپ مدیر، اپ کاربران، و صفحه check در قالب اصلی
   ========================================================================== */
(function (window, document) {
	'use strict';

	var NoghreApp = window.NoghreApp || {};

	/* ---------- توابع کمکی ---------- */
	NoghreApp.toPersian = function (n) {
		var en = '0123456789', fa = '۰۱۲۳۴۵۶۷۸۹';
		return String(n).replace(/[0-9]/g, function (d) { return fa[en.indexOf(d)]; });
	};

	NoghreApp.getToken = function () {
		try { return localStorage.getItem('noghre_access_token') || ''; } catch (e) { return ''; }
	};

	NoghreApp.removeToken = function () {
		try { localStorage.removeItem('noghre_access_token'); } catch (e) {}
	};

	/* ---------- نوتیفیکیشن شناور (با فونت درست) ---------- */
	var noticeEl = null;
	var noticeTimer = null;

	NoghreApp.showNotice = function (text, type) {
		type = type || 'success';

		if (noticeEl && noticeEl.parentNode) {
			noticeEl.parentNode.removeChild(noticeEl);
		}
		if (noticeTimer) clearTimeout(noticeTimer);

		noticeEl = document.createElement('div');
		noticeEl.className = 'noghre-floating-notice is-' + type;
		noticeEl.textContent = text;

		// 🔑 استایل inline برای فونت — اطمینان از اعمال فونت سفارشی
		var fontFamily = getSiteFontFamily();
		noticeEl.style.fontFamily = fontFamily;

		document.body.appendChild(noticeEl);

		requestAnimationFrame(function () {
			noticeEl.classList.add('is-visible');
		});

		noticeTimer = setTimeout(function () {
			if (noticeEl) {
				noticeEl.classList.remove('is-visible');
				setTimeout(function () {
					if (noticeEl && noticeEl.parentNode) {
						noticeEl.parentNode.removeChild(noticeEl);
					}
					noticeEl = null;
				}, 300);
			}
		}, 4000);
	};

	/**
	 * 🔑 گرفتن نام فونت فعال سایت از متغیرهای CSS
	 */
	function getSiteFontFamily() {
		try {
			var bodyFont = window.getComputedStyle(document.body).fontFamily;
			if (bodyFont && bodyFont !== '') {
				return bodyFont;
			}
		} catch (e) {}
		return 'Vazirmatn, Tahoma, sans-serif';
	}

	/* ---------- ممنوعیت راست‌کلیک و انتخاب متن ---------- */
	NoghreApp.isEditableElement = function (el) {
		if (!el) return false;
		var tag = el.tagName ? el.tagName.toLowerCase() : '';
		return tag === 'input' || tag === 'textarea' || el.isContentEditable === true;
	};

	NoghreApp.preventSelectionAndContextMenu = function () {
		document.addEventListener('contextmenu', function (e) {
			if (NoghreApp.isEditableElement(e.target)) return;
			e.preventDefault();
			return false;
		}, { capture: true });

		document.addEventListener('selectstart', function (e) {
			if (NoghreApp.isEditableElement(e.target)) return;
			e.preventDefault();
			return false;
		}, { capture: true });

		document.addEventListener('dragstart', function (e) {
			if (NoghreApp.isEditableElement(e.target)) return;
			e.preventDefault();
			return false;
		}, { capture: true });

		var pressTimer = null;
		document.addEventListener('touchstart', function (e) {
			if (NoghreApp.isEditableElement(e.target)) return;
			pressTimer = setTimeout(function () { e.preventDefault(); }, 400);
		}, { passive: false, capture: true });

		document.addEventListener('touchend', function () {
			if (pressTimer) { clearTimeout(pressTimer); pressTimer = null; }
		}, { capture: true });

		document.addEventListener('touchcancel', function () {
			if (pressTimer) { clearTimeout(pressTimer); pressTimer = null; }
		}, { capture: true });

		document.addEventListener('touchmove', function () {
			if (pressTimer) { clearTimeout(pressTimer); pressTimer = null; }
		}, { capture: true });

		document.addEventListener('keydown', function (e) {
			if (NoghreApp.isEditableElement(e.target)) return;
			if ((e.ctrlKey || e.metaKey) &&
			    (e.key === 'a' || e.key === 'A' ||
			     e.key === 'c' || e.key === 'C' ||
			     e.key === 'x' || e.key === 'X')) {
				e.preventDefault();
				return false;
			}
		}, { capture: true });

		document.addEventListener('copy', function (e) {
			if (NoghreApp.isEditableElement(e.target)) return;
			e.preventDefault();
			return false;
		}, { capture: true });

		document.addEventListener('cut', function (e) {
			if (NoghreApp.isEditableElement(e.target)) return;
			e.preventDefault();
			return false;
		}, { capture: true });
	};

	/* ---------- ممنوعیت کپی لینک ---------- */
	NoghreApp.preventLinkCopy = function (rootEl) {
		if (!rootEl) return;
		rootEl.addEventListener('contextmenu', function (e) {
			var link = e.target.closest('a[href]');
			if (link) { e.preventDefault(); return false; }
		});
		rootEl.addEventListener('touchstart', function (e) {
			var link = e.target.closest('a[href]');
			if (!link) return;
			var longPressTimer = setTimeout(function () { e.preventDefault(); }, 500);
			var cancelLongPress = function () {
				clearTimeout(longPressTimer);
				link.removeEventListener('touchend', cancelLongPress);
				link.removeEventListener('touchcancel', cancelLongPress);
				link.removeEventListener('touchmove', cancelLongPress);
			};
			link.addEventListener('touchend', cancelLongPress);
			link.addEventListener('touchcancel', cancelLongPress);
			link.addEventListener('touchmove', cancelLongPress);
		}, { passive: false });
	};

	/* ---------- مخفی کردن نوار ادمین وردپرس ---------- */
	NoghreApp.removeAdminBar = function () {
		var adminBar = document.getElementById('wpadminbar');
		if (adminBar) adminBar.style.display = 'none';

		document.documentElement.style.marginTop = '0';
		document.body.style.marginTop = '0';
		document.body.classList.remove('admin-bar');
		document.documentElement.classList.remove('wp-toolbar');
	};

	/* ---------- حذف query param m ---------- */
	NoghreApp.stripMFromUrl = function () {
		try {
			var url = new URL(window.location.href);
			if (url.searchParams.has('m')) {
				url.searchParams.delete('m');
				var newUrl = url.pathname + (url.search ? url.search : '') + url.hash;
				history.replaceState(history.state || {}, '', newUrl);
			}
		} catch (e) {}
	};

	/* ---------- مدیریت آفلاین ---------- */
	NoghreApp.initOfflineHandling = function (overlayId) {
		var overlay = document.getElementById(overlayId || 'noghre-offline-overlay');

		function show() { if (overlay) overlay.classList.add('is-visible'); }
		function hide() { if (overlay) overlay.classList.remove('is-visible'); }

		if (!navigator.onLine) show();
		window.addEventListener('online', hide);
		window.addEventListener('offline', show);

		NoghreApp.Offline = { show: show, hide: hide };
		return NoghreApp.Offline;
	};

	/* ---------- اجرای اسکریپت‌های inline بعد از AJAX ---------- */
	NoghreApp.executeScripts = function (container) {
		var scripts = container.querySelectorAll('script');
		scripts.forEach(function (oldScript) {
			var newScript = document.createElement('script');
			for (var i = 0; i < oldScript.attributes.length; i++) {
				var attr = oldScript.attributes[i];
				newScript.setAttribute(attr.name, attr.value);
			}
			newScript.textContent = oldScript.textContent;
			oldScript.parentNode.replaceChild(newScript, oldScript);
		});
	};

	/* ---------- مدیریت PWA (نصب) ---------- */
	NoghreApp.initPWAInstall = function (btnId) {
		var installBtn = document.getElementById(btnId || 'noghre-pwa-install');
		var deferredPrompt = null;

		var isStandalone = window.matchMedia('(display-mode: standalone)').matches ||
			window.navigator.standalone === true ||
			document.referrer.indexOf('android-app://') === 0;

		if (installBtn && isStandalone) {
			installBtn.style.display = 'none';
			return;
		}

		if (!installBtn) return;

		window.addEventListener('beforeinstallprompt', function (e) {
			e.preventDefault();
			deferredPrompt = e;
			installBtn.style.display = 'flex';
		});

		installBtn.addEventListener('click', function () {
			if (!deferredPrompt) return;
			deferredPrompt.prompt();
			deferredPrompt.userChoice.then(function () {
				deferredPrompt = null;
				installBtn.style.display = 'none';
			});
		});

		window.addEventListener('appinstalled', function () {
			installBtn.style.display = 'none';
			deferredPrompt = null;
		});
	};

	/* =========================================================
	 * 🔑 bind فرم‌های admin-ajax (مشترک بین اپ و قالب اصلی)
	 *    برای صفحه check در قالب اصلی هم کار می‌کنه
	 *
	 * تغییرات:
	 *  - دکمه‌های ویرایش/تایید/باطل حالا هم با فرم و هم با کلیک روی
	 *    دکمه‌های داده شده کار می‌کنن.
	 *  - رفع مشکل «دکمه ویرایش رزرو کار نمی‌کنه»
	 * ========================================================= */
	NoghreApp.bindAppForms = function (rootEl) {
		rootEl = rootEl || document;

		/* ---------- ۱. bind فرم‌ها ---------- */
		var forms = rootEl.querySelectorAll('form[data-noghre-app-form="1"]');
		forms.forEach(function (form) {
			if (form.dataset.appBound) return;
			form.dataset.appBound = '1';

			form.addEventListener('submit', function (e) {
				e.preventDefault();

				var submitBtn = form.querySelector('button[type="submit"], input[type="submit"]');
				if (submitBtn && submitBtn.disabled) return;

				var originalText = submitBtn ? submitBtn.textContent : '';
				if (submitBtn) {
					submitBtn.disabled = true;
					submitBtn.textContent = 'در حال انجام...';
				}

				var fd = new FormData(form);
				var token = NoghreApp.getToken();
				if (token) fd.append('access_token', token);

				var appEl = document.getElementById('noghre-mobile-app');
				if (appEl) {
					fd.append('m', appEl.getAttribute('data-admin-key') || '');
					fd.append('nonce', appEl.getAttribute('data-nonce') || '');
				}

				if (!appEl) {
					var urlParams = new URLSearchParams(window.location.search);
					var m = urlParams.get('m') || '';
					if (m) fd.append('m', m);

					var nonceMeta = document.querySelector('meta[name="noghre-actions-nonce"]');
					if (nonceMeta) {
						fd.append('nonce', nonceMeta.getAttribute('content'));
					} else {
						var cfg = window.noghreCheckConfig || {};
						if (cfg.nonce) fd.append('nonce', cfg.nonce);
					}
				}

				var formAction = fd.get('action') || '';
				var ajaxAction = '';
				if (formAction === 'noghre_booking_confirm') ajaxAction = 'noghre_booking_confirm';
				else if (formAction === 'noghre_booking_edit') ajaxAction = 'noghre_booking_edit';
				else if (formAction === 'noghre_booking_void') ajaxAction = 'noghre_booking_void';
				else if (formAction === 'noghre_booking_send_unused_sms') ajaxAction = 'noghre_booking_send_unused_sms';

				fd.set('action', ajaxAction);

				var ajaxUrl = '/wp-admin/admin-ajax.php';
				if (window.noghreAppConfig && window.noghreAppConfig.ajaxUrl) {
					ajaxUrl = window.noghreAppConfig.ajaxUrl;
				} else if (window.noghreCheckConfig && window.noghreCheckConfig.ajaxUrl) {
					ajaxUrl = window.noghreCheckConfig.ajaxUrl;
				}

				fetch(ajaxUrl, {
					method: 'POST',
					body: fd,
					credentials: 'same-origin'
				})
				.then(function (res) { return res.json(); })
				.then(function (data) {
					if (submitBtn) {
						submitBtn.disabled = false;
						submitBtn.textContent = originalText;
					}

					if (!data || !data.success) {
						var msg = (data && data.data && data.data.message) ? data.data.message : 'خطا در انجام عملیات.';
						NoghreApp.showNotice(msg, 'error');
						return;
					}

					NoghreApp.showNotice(data.data.message || 'عملیات با موفقیت انجام شد.', 'success');

					var section = 'check';
					var code = '';

					if (appEl) {
						section = appEl.getAttribute('data-section') || 'check';
						code = appEl.getAttribute('data-code') || '';
					} else {
						var urlParams = new URLSearchParams(window.location.search);
						code = urlParams.get('code') || '';
					}

					setTimeout(function () {
						if (window.NoghreMobileApp && window.NoghreMobileApp.forceLoadSection) {
							window.NoghreMobileApp.forceLoadSection(section, code);
						} else {
							window.location.reload();
						}
					}, 500);
				})
				.catch(function () {
					if (submitBtn) {
						submitBtn.disabled = false;
						submitBtn.textContent = originalText;
					}
					NoghreApp.showNotice('ارتباط با سرور برقرار نشد.', 'error');
				});
			});
		});

		/* ---------- ۲. bind دکمه toggle edit ---------- */
		var toggleBtns = rootEl.querySelectorAll('#noghre-toggle-edit');
		toggleBtns.forEach(function (btn) {
			if (btn.dataset.toggleBound) return;
			btn.dataset.toggleBound = '1';

			btn.addEventListener('click', function (e) {
				e.preventDefault();
				var editForm = document.getElementById('noghre-edit-form');
				if (!editForm) return;

				if (editForm.style.display === 'none' || editForm.style.display === '') {
					editForm.style.display = 'block';
				} else {
					editForm.style.display = 'none';
				}
			});
		});

		/* ---------- ۳. bind دکمه‌های data-confirm (تأیید قبل از submit) ---------- */
		document.querySelectorAll('form[data-noghre-app-form="1"][data-confirm]').forEach(function (form) {
			if (form.dataset.confirmBound) return;
			form.dataset.confirmBound = '1';
			form.addEventListener('submit', function (e) {
				var msg = form.getAttribute('data-confirm');
				if (msg && !window.confirm(msg)) {
					e.preventDefault();
					e.stopImmediatePropagation();
					return false;
				}
			}, true);
		});
	};

	/* 🔑 اجرای خودکار bindAppForms */
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', function () {
			NoghreApp.bindAppForms(document);
		});
	} else {
		NoghreApp.bindAppForms(document);
	}

	window.NoghreApp = NoghreApp;
})(window, document);