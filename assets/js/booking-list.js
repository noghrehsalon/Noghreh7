/* ==========================================================================
   Noghre — Booking List
   منطق لیست رزروها (search, filter, sort, pagination)
   خودکفا — بدون وابستگی خارجی
   ========================================================================== */
(function (window, document) {
	'use strict';

	function toPersian(n) {
		var en = '0123456789', fa = '۰۱۲۳۴۵۶۷۸۹';
		return String(n).replace(/[0-9]/g, function (d) { return fa[en.indexOf(d)]; });
	}

	function init() {
		var input   = document.getElementById('noghre-booking-list-search');
		var table   = document.getElementById('noghre-booking-list-table');
		var countEl = document.getElementById('noghre-booking-list-count');

		/* ---------- جستجوی زنده ---------- */
		if (input && table && !input.dataset.bound) {
			input.dataset.bound = '1';
			var noResult = table.querySelector('.noghre-list-no-result');

			input.addEventListener('input', function () {
				var q = this.value.trim().toLowerCase();
				var rows = table.querySelectorAll('tbody tr[data-search]');
				var visible = 0;

				rows.forEach(function (row) {
					var hay = row.getAttribute('data-search') || '';
					var match = q === '' || hay.indexOf(q) !== -1;
					row.style.display = match ? '' : 'none';
					if (match) visible++;
				});

				if (noResult) {
					noResult.style.display = (visible === 0 && rows.length > 0) ? '' : 'none';
				}
				if (countEl) {
					countEl.setAttribute('data-count', visible);
					countEl.textContent = toPersian(visible) + ' رزرو';
				}
			});
		}

		/* ---------- فیلتر وضعیت ---------- */
		var statusFilter = document.getElementById('noghre-booking-list-status-filter');
		if (statusFilter && !statusFilter.dataset.bound) {
			statusFilter.dataset.bound = '1';
			statusFilter.addEventListener('change', function () {
				var val = this.value;

				if (window.NoghreMobileApp && window.NoghreMobileApp.loadSection) {
					window.NoghreMobileApp.loadSection('list', null, true, {
						status_filter: val
					});
				} else {
					var url = new URL(window.location.href);
					if (val) {
						url.searchParams.set('status_filter', val);
					} else {
						url.searchParams.delete('status_filter');
					}
					url.searchParams.delete('paged');
					window.location.href = url.toString();
				}
			});
		}

		/* ---------- مرتب‌سازی ستون‌ها ---------- */
		document.querySelectorAll('#noghre-booking-list-table thead a[href*="orderby"]').forEach(function (link) {
			if (link.dataset.bound) return;
			link.dataset.bound = '1';
			link.addEventListener('click', function (e) {
				if (window.NoghreMobileApp && window.NoghreMobileApp.loadSection) {
					e.preventDefault();
					var url = new URL(link.href, window.location.origin);
					window.NoghreMobileApp.loadSection('list', null, true, {
						orderby: url.searchParams.get('orderby') || '',
						order:   url.searchParams.get('order') || ''
					});
				}
			});
		});

		/* ---------- صفحه‌بندی ---------- */
		document.querySelectorAll('#noghre-booking-list-wrap .noghre-page-btn').forEach(function (btn) {
			if (btn.dataset.bound) return;
			btn.dataset.bound = '1';
			btn.addEventListener('click', function (e) {
				if (window.NoghreMobileApp && window.NoghreMobileApp.loadSection) {
					e.preventDefault();
					window.NoghreMobileApp.loadSection('list', null, true, {
						paged: btn.getAttribute('data-paged') || '1'
					});
				}
			});
		});
	}

	window.NoghreBookingListInit = init;

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})(window, document);