/* ==========================================================================
   Noghre App — Admin JS
   منطق اختصاصی اپ مدیر (لیست رزروها، لود بخش‌ها)
   وابسته به: app-shared.js
   ========================================================================== */
(function (window, document) {
	'use strict';

	if (!window.NoghreApp) {
		console.warn('NoghreApp: app-shared.js is required before app-admin.js');
		return;
	}

	var App = window.NoghreApp;

	function init() {
		var appEl = document.getElementById('noghre-mobile-app');
		if (!appEl) return;

		var bodyEl       = document.getElementById('noghre-app-body');
		var navEl        = document.getElementById('noghre-app-nav');
		var headerBackEl = appEl.querySelector('.noghre-app-back');
		var searchWrap   = appEl.querySelector('.noghre-app-search-wrap');
		var adminKey     = appEl.getAttribute('data-admin-key');
		var ajaxUrl      = window.noghreAppConfig ? noghreAppConfig.ajaxUrl : '/wp-admin/admin-ajax.php';
		var baseUrl      = window.noghreAppConfig ? noghreAppConfig.baseUrl : '/mobile/';
		var loading      = false;

		App.removeAdminBar();
		App.stripMFromUrl();
		App.preventLinkCopy(appEl);
		App.preventSelectionAndContextMenu();
		App.initOfflineHandling('noghre-offline-overlay');
		App.initPWAInstall('noghre-pwa-install');

		window.addEventListener('storage', function (e) {
			if (e.key === 'noghre_access_token' && !e.newValue) {
				App.removeToken();
				window.location.href = baseUrl;
			}
		});

		/* =========================================================
		 * 🔑 اتصال جستجوی هدر به جستجوی لیست
		 * ========================================================= */
		function connectSearchToList() {
			var appSearch  = document.getElementById('noghre-app-search');
			var appStatus  = document.getElementById('noghre-app-status');
			var listSearch = document.getElementById('noghre-booking-list-search');
			var listStatus = document.getElementById('noghre-booking-list-status-filter');

			if (appSearch && listSearch) {
				appSearch.oninput = null;
				appSearch.oninput = function () {
					listSearch.value = this.value;
					var q = this.value.trim().toLowerCase();
					var table = document.getElementById('noghre-booking-list-table');
					if (table) {
						var rows = table.querySelectorAll('tbody tr[data-search]');
						var visible = 0;
						rows.forEach(function (row) {
							var hay = row.getAttribute('data-search') || '';
							var match = q === '' || hay.indexOf(q) !== -1;
							row.style.display = match ? '' : 'none';
							if (match) visible++;
						});
						var noResult = table.querySelector('.noghre-list-no-result');
						if (noResult) noResult.style.display = (visible === 0 && rows.length > 0) ? '' : 'none';
						var countEl = document.getElementById('noghre-booking-list-count');
						if (countEl) countEl.textContent = App.toPersian(visible) + ' رزرو';
					}
				};

				if (listSearch.value) {
					appSearch.value = listSearch.value;
				}
			}

			if (appStatus && listStatus) {
				appStatus.onchange = null;
				appStatus.onchange = function () {
					listStatus.value = this.value;
					listStatus.dispatchEvent(new Event('change', { bubbles: true }));
				};

				if (listStatus.value) {
					appStatus.value = listStatus.value;
				}
			}
		}

		/* =========================================================
		 * 🔑 کنترل‌های لیست رزروها
		 * ========================================================= */
		function bindListControls() {
			var listSearch = document.getElementById('noghre-booking-list-search');
			var listStatus = document.getElementById('noghre-booking-list-status-filter');
			var table      = document.getElementById('noghre-booking-list-table');
			var countEl    = document.getElementById('noghre-booking-list-count');

			if (listSearch && table) {
				listSearch.oninput = null;
				listSearch.oninput = function () {
					var q = this.value.trim().toLowerCase();
					var rows = table.querySelectorAll('tbody tr[data-search]');
					var visible = 0;

					rows.forEach(function (row) {
						var hay = row.getAttribute('data-search') || '';
						var match = q === '' || hay.indexOf(q) !== -1;
						row.style.display = match ? '' : 'none';
						if (match) visible++;
					});

					var noResult = table.querySelector('.noghre-list-no-result');
					if (noResult) noResult.style.display = (visible === 0 && rows.length > 0) ? '' : 'none';
					if (countEl) countEl.textContent = App.toPersian(visible) + ' رزرو';
				};
			}

			if (listStatus) {
				listStatus.onchange = null;
				listStatus.onchange = function () {
					loadSection('list', null, true, { status_filter: this.value });
				};
			}

			document.querySelectorAll('#noghre-booking-list-table thead a[href*="orderby"]').forEach(function (link) {
				if (link.dataset.bound) return;
				link.dataset.bound = '1';
				link.addEventListener('click', function (e) {
					e.preventDefault();
					var url = new URL(link.href, window.location.origin);
					loadSection('list', null, true, {
						orderby: url.searchParams.get('orderby') || '',
						order: url.searchParams.get('order') || ''
					});
				});
			});

			document.querySelectorAll('#noghre-booking-list-wrap .noghre-page-btn').forEach(function (btn) {
				if (btn.dataset.bound) return;
				btn.dataset.bound = '1';
				btn.addEventListener('click', function (e) {
					e.preventDefault();
					loadSection('list', null, true, { paged: btn.getAttribute('data-paged') || '1' });
				});
			});
		}

		/* =========================================================
		 * 🔑 لود بخش با AJAX
		 * ========================================================= */
		function loadSection(section, code, pushState, extra, force) {
			if (loading && !force) return;

			var currentSection = appEl.getAttribute('data-section');
			var currentCode    = appEl.getAttribute('data-code') || '';

			var statusFilter = extra && typeof extra.status_filter !== 'undefined' ? extra.status_filter : null;
			var orderby      = extra && extra.orderby ? extra.orderby : '';
			var order        = extra && extra.order ? extra.order : '';
			var paged        = extra && extra.paged ? extra.paged : '';

			var isFilterRequest = extra && typeof extra.status_filter !== 'undefined';

			if (!force
			     && !isFilterRequest
			     && section === currentSection
			     && (!code || code === currentCode)
			     && !orderby && !paged) {
				return;
			}

			loading = true;
			bodyEl.classList.add('is-loading');

			var token = App.getToken();
			var fd = new FormData();
			fd.append('action', 'noghre_mobile_section');
			fd.append('access_token', token);
			fd.append('m', adminKey);
			fd.append('section', section);
			if (code) fd.append('code', code);
			if (statusFilter !== null) fd.append('status_filter', statusFilter);
			if (orderby) fd.append('orderby', orderby);
			if (order) fd.append('order', order);
			if (paged) fd.append('paged', paged);

			fetch(ajaxUrl, { method: 'POST', body: fd })
				.then(function (res) { return res.json(); })
				.then(function (data) {
					loading = false;
					bodyEl.classList.remove('is-loading');
					if (App.Offline) App.Offline.hide();

					if (!data.success) {
						if (data.data && data.data.code === 'invalid_admin_key') {
							App.removeToken();
							window.location.href = data.data.redirect_to || baseUrl;
							return;
						}
						return;
					}

					var payload = data.data;

					bodyEl.innerHTML = payload.content;
					appEl.setAttribute('data-section', payload.section);
					appEl.setAttribute('data-code', payload.code || '');

					if (headerBackEl) {
						if (payload.header.back_url) {
							headerBackEl.setAttribute('href', payload.header.back_url);
							headerBackEl.setAttribute('data-nav', 'back');
							headerBackEl.classList.remove('noghre-app-back-hidden');
						} else {
							headerBackEl.removeAttribute('href');
							headerBackEl.setAttribute('data-nav', '');
							headerBackEl.classList.add('noghre-app-back-hidden');
						}
					}

					if (searchWrap) {
						searchWrap.style.display = payload.header.show_search ? '' : 'none';
						if (!payload.header.show_search) {
							var s = document.getElementById('noghre-app-search');
							var f = document.getElementById('noghre-app-status');
							if (s) s.value = '';
							if (f) f.value = '';
						}
					}

					if (navEl && payload.nav) {
						var navHtml = '';
						payload.nav.forEach(function (menu) {
							var activeClass = menu.active ? ' is-active' : '';
							navHtml += '<a href="' + menu.url + '" class="noghre-app-nav-item' + activeClass + '" data-nav="menu" data-section="' + menu.section + '" onclick="return false;"><span>' + menu.icon_html + '</span><small>' + menu.title + '</small></a>';
						});
						navEl.innerHTML = navHtml;
					}

					App.executeScripts(bodyEl);
					connectSearchToList();
					bindListControls();
					App.bindAppForms(bodyEl);

					/* 🔑 ری‌اینیشیالایز همه کامپوننت‌ها */
					if (typeof window.NoghreBookingCheckInit === 'function') {
						window.NoghreBookingCheckInit();
					}
					if (typeof window.NoghreBookingListInit === 'function') {
						window.NoghreBookingListInit();
					}
					if (typeof window.NoghreSignupInit === 'function') {
						window.NoghreSignupInit();
					}
					if (typeof window.NoghreScannerInit === 'function') {
						window.NoghreScannerInit();
					}

					if (pushState) {
						var newUrl = baseUrl + '?section=' + payload.section;
						if (payload.code) newUrl += '&code=' + encodeURIComponent(payload.code);
						if (statusFilter) newUrl += '&status_filter=' + encodeURIComponent(statusFilter);
						history.pushState({ section: payload.section, code: payload.code || '', status_filter: statusFilter || '' }, '', newUrl);
					}

					window.scrollTo({ top: 0, behavior: 'smooth' });
				})
				.catch(function () {
					loading = false;
					bodyEl.classList.remove('is-loading');
					if (App.Offline) App.Offline.show();
				});
		}

		function forceLoadSection(section, code) {
			loading = false;
			bodyEl.classList.remove('is-loading');
			loadSection(section, code, false, null, true);
		}

		/* ---------- کلیک سراسری ---------- */
		document.addEventListener('click', function (e) {
			var link = e.target.closest('[data-nav]');
			if (!link) return;

			var navType = link.getAttribute('data-nav');
			if (navType === 'ignore' || navType === 'link' || navType === 'pagination' || navType === '') return;

			e.preventDefault();
			e.stopPropagation();

			if (navType === 'menu') {
				loadSection(link.getAttribute('data-section'), null, true);
			} else if (navType === 'back') {
				var backHref = link.getAttribute('href');
				if (backHref) {
					var backSection = 'mobile';
					try {
						var u = new URL(backHref, window.location.origin);
						backSection = u.searchParams.get('section') || 'mobile';
					} catch (err) {}
					loadSection(backSection, null, true);
				}
			} else if (navType === 'check') {
				var code = link.getAttribute('data-code') || '';
				if (code) loadSection('check', code, true);
			}
		}, true);

		/* ---------- تاریخچه ---------- */
		window.addEventListener('popstate', function (e) {
			var section = 'mobile', code = '', statusFilter = null;
			if (e.state) {
				section = e.state.section || 'mobile';
				code = e.state.code || '';
				statusFilter = e.state.status_filter || null;
			} else {
				var params = new URLSearchParams(window.location.search);
				section = params.get('section') || 'mobile';
				code = params.get('code') || '';
				statusFilter = params.get('status_filter') || null;
			}
			loading = false;
			bodyEl.classList.remove('is-loading');
			loadSection(section, code || null, false, statusFilter !== null ? { status_filter: statusFilter } : null);
		});

		/* ---------- اجرای اولیه ---------- */
		var cleanUrl = baseUrl + '?section=' + appEl.getAttribute('data-section');
		var initialCode = appEl.getAttribute('data-code') || '';
		if (initialCode) cleanUrl += '&code=' + encodeURIComponent(initialCode);

		history.replaceState({
			section: appEl.getAttribute('data-section'),
			code: initialCode
		}, '', cleanUrl);

		connectSearchToList();
		bindListControls();
		App.bindAppForms(bodyEl);

		window.NoghreMobileApp = {
			loadSection: loadSection,
			forceLoadSection: forceLoadSection,
			reloadCurrentSection: function (newUrl) { window.location.href = newUrl; }
		};
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})(window, document);