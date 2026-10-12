/* ==========================================================================
   Noghre — Booking Check Page
   منطق صفحه استعلام رزرو (QR + toggle edit + print + confirm)
   خودکفا — بدون وابستگی خارجی
   ========================================================================== */
(function (window, document) {
	'use strict';

	function init() {
		/* ---------- toggle فرم ویرایش ---------- */
		var toggleBtn = document.getElementById('noghre-toggle-edit');
		var editForm  = document.getElementById('noghre-edit-form');

		if (toggleBtn && editForm && !toggleBtn.dataset.bound) {
			toggleBtn.dataset.bound = '1';
			toggleBtn.addEventListener('click', function (e) {
				e.preventDefault();
				if (editForm.style.display === 'none' || editForm.style.display === '') {
					editForm.style.display = 'block';
					// فوکوس روی اولین فیلد
					var firstField = editForm.querySelector('input, select, textarea');
					if (firstField) {
						setTimeout(function () { firstField.focus(); }, 100);
					}
				} else {
					editForm.style.display = 'none';
				}
			});
		}

		/* ---------- QR code ---------- */
		var qrCanvas = document.getElementById('noghre-qr-canvas');
		if (qrCanvas && qrCanvas.dataset.qrUrl && !qrCanvas.dataset.qrDrawn) {
			if (typeof window.QRCode === 'function') {
				qrCanvas.dataset.qrDrawn = '1';
				try {
					new window.QRCode('noghre-qr-canvas', {
						text: qrCanvas.dataset.qrUrl,
						width: 180,
						height: 180,
						correctLevel: 0
					});
				} catch (e) {}
			} else {
				setTimeout(function () {
					if (typeof window.QRCode === 'function' && !qrCanvas.dataset.qrDrawn) {
						qrCanvas.dataset.qrDrawn = '1';
						try {
							new window.QRCode('noghre-qr-canvas', {
								text: qrCanvas.dataset.qrUrl,
								width: 180,
								height: 180,
								correctLevel: 0
							});
						} catch (e) {}
					}
				}, 300);
			}
		}

		/* ---------- دکمه پرینت ---------- */
		var printBtn = document.getElementById('noghre-print-btn');
		if (printBtn && !printBtn.dataset.bound) {
			printBtn.dataset.bound = '1';
			printBtn.addEventListener('click', function () {
				window.print();
			});
		}

		/* ---------- تأیید قبل از submit فرم‌های حساس ---------- */
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
	}

	window.NoghreBookingCheckInit = init;

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})(window, document);