/* ==========================================================================
   Noghre App — Login Page JS
   منطق فرم ورود به اپ + ممنوعیت راست‌کلیک و کپی
   وابسته به: app-shared.js
   ========================================================================== */
(function (window, document) {
	'use strict';

	function init() {
		var App = window.NoghreApp || {};

		/* 🔑 ممنوعیت انتخاب و راست‌کلیک روی صفحه ورود */
		if ( App.preventSelectionAndContextMenu ) {
			App.preventSelectionAndContextMenu();
		}

		var pageEl = document.getElementById( 'noghre-admin-login-page' );
		if ( pageEl && App.preventLinkCopy ) {
			App.preventLinkCopy( pageEl );
		}

		if ( App.removeAdminBar ) {
			App.removeAdminBar();
		}

		if ( App.stripMFromUrl ) {
			App.stripMFromUrl();
		}

		/* 🔑 استایل ممنوعیت انتخاب متن (به جز input) */
		( function injectNoSelectStyle() {
			var style = document.createElement( 'style' );
			style.textContent =
				'#noghre-admin-login-page, #noghre-admin-login-page * {' +
				'  -webkit-user-select: none; -moz-user-select: none; -ms-user-select: none; user-select: none;' +
				'  -webkit-touch-callout: none;' +
				'}' +
				'#noghre-admin-login-page input,' +
				'#noghre-admin-login-page textarea,' +
				'#noghre-admin-login-page [contenteditable="true"] {' +
				'  -webkit-user-select: text !important; -moz-user-select: text !important;' +
				'  -ms-user-select: text !important; user-select: text !important;' +
				'  -webkit-touch-callout: default !important;' +
				'}';
			document.head.appendChild( style );
		} )();

		/* 🔑 منطق فرم ورود */
		var form = document.getElementById( 'noghre-admin-login-form' );
		if ( ! form ) return;

		var input = document.getElementById( 'noghre-admin-key-input' );
		var msgEl = form.querySelector( '.noghre-admin-login-message' );
		var btn   = form.querySelector( '.noghre-admin-login-btn' );

		var cfg     = window.noghreLoginConfig || {};
		var ajaxUrl = cfg.ajaxUrl || '/wp-admin/admin-ajax.php';
		var nonce   = cfg.nonce || '';
		var baseUrl = cfg.baseUrl || '/mobile/';
		var maxLen  = parseInt( cfg.maxLen, 10 ) || 8;

		/* 🔑 جلوگیری از تایپ بیش از حد مجاز (اضافه‌کار روی maxlength) */
		input.addEventListener( 'input', function () {
			if ( this.value.length > maxLen ) {
				this.value = this.value.slice( 0, maxLen );
			}
		} );

		form.addEventListener( 'submit', function ( e ) {
			e.preventDefault();

			var key = input.value.trim().toUpperCase();
			msgEl.textContent = '';
			msgEl.className = 'noghre-admin-login-message';

			if ( key.length !== maxLen ) {
				msgEl.textContent = 'شناسه مدیریت باید ' + maxLen + ' کاراکتر باشد.';
				msgEl.classList.add( 'is-error' );
				return;
			}

			btn.disabled = true;
			btn.textContent = 'در حال بررسی...';

			/* مرحله ۱: بررسی صحت شناسه */
			var fd = new FormData();
			fd.append( 'action', 'noghre_verify_admin_key' );
			fd.append( 'key', key );
			fd.append( 'nonce', nonce );

			fetch( ajaxUrl, { method: 'POST', body: fd } )
				.then( function ( res ) { return res.json(); } )
				.then( function ( data ) {
					if ( ! data.success ) {
						btn.disabled = false;
						btn.textContent = 'ورود به پنل';
						msgEl.textContent = ( data.data && data.data.message ) || 'شناسه مدیریت اشتباه است.';
						msgEl.classList.add( 'is-error' );
						input.select();
						return;
					}

					/* مرحله ۲: ساخت توکن و ذخیره در localStorage */
					var fd2 = new FormData();
					fd2.append( 'action', 'noghre_create_token' );
					fd2.append( 'm', key );

					return fetch( ajaxUrl, { method: 'POST', body: fd2 } )
						.then( function ( res2 ) { return res2.json(); } )
						.then( function ( data2 ) {
							btn.disabled = false;
							btn.textContent = 'ورود به پنل';

							if ( ! data2.success ) {
								msgEl.textContent = ( data2.data && data2.data.message ) || 'خطا در ساخت توکن.';
								msgEl.classList.add( 'is-error' );
								return;
							}

							try {
								localStorage.setItem( 'noghre_access_token', data2.data.token );
							} catch ( e ) {}

							msgEl.textContent = 'ورود موفق. در حال انتقال...';
							msgEl.classList.add( 'is-success' );

							setTimeout( function () {
								var url = new URL( window.location.href );
								url.searchParams.delete( 'm' );
								url.searchParams.set( 'section', 'mobile' );
								window.location.href = url.toString();
							}, 400 );
						} );
				} )
				.catch( function () {
					btn.disabled = false;
					btn.textContent = 'ورود به پنل';
					msgEl.textContent = 'ارتباط با سرور برقرار نشد.';
					msgEl.classList.add( 'is-error' );
				} );
		} );
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
} )( window, document );