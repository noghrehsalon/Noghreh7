/* ==========================================================================
   Noghre App — Customer Signup
   باکس ثبت شماره در باشگاه مشتریان
   وابسته به: app-shared.js
   ========================================================================== */
(function (window, document) {
	'use strict';

	function init() {
		var box       = document.getElementById('noghre-app-signup-box');
		var signupBtn = document.getElementById('noghre-signup-submit');
		var signupName = document.getElementById('noghre-signup-name');
		var signupMobile = document.getElementById('noghre-signup-mobile');
		var signupMsg = box ? box.querySelector('.noghre-app-signup-message') : null;

		if (!signupBtn || signupBtn.dataset.bound) return;
		signupBtn.dataset.bound = '1';

		var cfg     = window.noghreSignupConfig || {};
		var ajaxUrl = cfg.ajaxUrl || '/wp-admin/admin-ajax.php';
		var nonce   = cfg.nonce || '';

		/* 🔑 فقط اعداد در فیلد موبایل */
		signupMobile.addEventListener('input', function () {
			this.value = this.value.replace(/\D/g, '').slice(0, 11);
		});

		/* 🔑 نمایش پیام (بعد از ۵ ثانیه محو می‌شه) */
		var showMessage = function (text, type) {
			if (!signupMsg) return;
			signupMsg.textContent = text;
			signupMsg.className = 'noghre-app-signup-message is-' + type;
			setTimeout(function () {
				signupMsg.textContent = '';
				signupMsg.className = 'noghre-app-signup-message';
			}, 5000);
		};

		/* 🔑 کلیک روی دکمه افزودن */
		signupBtn.addEventListener('click', function () {
			var name   = signupName.value.trim();
			var mobile = signupMobile.value.trim();

			if (signupMsg) {
				signupMsg.textContent = '';
				signupMsg.className = 'noghre-app-signup-message';
			}

			if (name.length < 2) {
				showMessage('لطفاً نام و نام خانوادگی را کامل وارد کنید.', 'error');
				return;
			}
			if (!/^09\d{9}$/.test(mobile)) {
				showMessage('شماره موبایل باید ۱۱ رقم و با ۰۹ شروع شود.', 'error');
				return;
			}

			signupBtn.disabled = true;
			signupBtn.textContent = 'در حال ثبت...';

			var App = window.NoghreApp || {};
			var token = App.getToken ? App.getToken() : '';

			var fd = new FormData();
			fd.append('action', 'noghre_customer_signup');
			fd.append('nonce', nonce);
			fd.append('access_token', token);
			fd.append('name', name);
			fd.append('mobile', mobile);

			fetch(ajaxUrl, { method: 'POST', body: fd })
				.then(function (res) { return res.json(); })
				.then(function (data) {
					signupBtn.disabled = false;
					signupBtn.textContent = 'افزودن';
					signupName.value = '';
					signupMobile.value = '';

					if (data.success) {
						var msg = data.data.message || 'ثبت شد.';
						showMessage(msg, data.data.admin_notice ? 'warning' : 'success');

						var countEl = box ? box.querySelector('.noghre-app-signup-count') : null;
						if (countEl) {
							var current = parseInt(countEl.getAttribute('data-count') || '0', 10);
							var newCount = current + 1;
							countEl.setAttribute('data-count', newCount);
							countEl.textContent = App.toPersian
								? App.toPersian(newCount) + ' مخاطب'
								: newCount + ' مخاطب';
						}
					} else {
						showMessage((data.data && data.data.message) || 'خطا در ثبت.', 'error');
					}
					signupName.focus();
				})
				.catch(function () {
					signupBtn.disabled = false;
					signupBtn.textContent = 'افزودن';
					showMessage('ارتباط با سرور برقرار نشد.', 'error');
					signupName.focus();
				});
		});
	}

	/* 🔑 قابل فراخوانی از بیرون (مثلاً بعد از AJAX در اپ) */
	window.NoghreSignupInit = init;

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})(window, document);