(function () {
	'use strict';

	document.addEventListener( 'DOMContentLoaded', function () {

		/* ---------------- منوی موبایل ---------------- */
		var toggle = document.querySelector( '.menu-toggle' );
		var body   = document.body;

		if ( toggle ) {
			toggle.addEventListener( 'click', function () {
				var isOpen = body.classList.toggle( 'nav-open' );
				toggle.setAttribute( 'aria-expanded', isOpen ? 'true' : 'false' );
			} );

			document.addEventListener( 'click', function ( e ) {
				if ( body.classList.contains( 'nav-open' ) &&
					! e.target.closest( '.primary-navigation' ) &&
					! e.target.closest( '.menu-toggle' ) ) {
					body.classList.remove( 'nav-open' );
					toggle.setAttribute( 'aria-expanded', 'false' );
				}
			} );

			document.addEventListener( 'keyup', function ( e ) {
				if ( e.key === 'Escape' ) {
					body.classList.remove( 'nav-open' );
					toggle.setAttribute( 'aria-expanded', 'false' );
				}
			} );
		}

		/* ---------------- هدر مگنتی + سایه هنگام اسکرول ---------------- */
		var header = document.querySelector( '.site-header' );
		if ( header ) {
			var topbarEl   = document.querySelector( '.noghre-topbar' );
			var spacerEl   = document.querySelector( '.site-header-spacer' );
			var isSticky   = header.classList.contains( 'is-sticky' );
			var ticking    = false;

			var updateHeader = function () {
				var scrollY = window.scrollY || window.pageYOffset;
				header.classList.toggle( 'is-scrolled', scrollY > 12 );

				if ( isSticky ) {
					var topbarHeight = ( topbarEl && topbarEl.offsetHeight ) ? topbarEl.offsetHeight : 0;
					if ( scrollY >= topbarHeight ) {
						if ( ! header.classList.contains( 'js-fixed' ) ) {
							header.classList.add( 'js-fixed' );
							if ( spacerEl ) spacerEl.style.height = header.offsetHeight + 'px';
						}
					} else if ( header.classList.contains( 'js-fixed' ) ) {
						header.classList.remove( 'js-fixed' );
						if ( spacerEl ) spacerEl.style.height = '0px';
					}
				}
				ticking = false;
			};

			var onScroll = function () {
				if ( ! ticking ) {
					window.requestAnimationFrame( updateHeader );
					ticking = true;
				}
			};

			window.addEventListener( 'scroll', onScroll, { passive: true } );
			window.addEventListener( 'resize', onScroll );
			window.addEventListener( 'load', updateHeader );
			updateHeader();
		}

		/* ---------------- نمایان‌شدن تدریجی بخش‌ها ---------------- */
		var revealEls = document.querySelectorAll( '[data-reveal]' );
		if ( 'IntersectionObserver' in window && revealEls.length ) {
			var reduceMotion = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;
			if ( reduceMotion ) {
				revealEls.forEach( function ( el ) { el.classList.add( 'is-visible' ); } );
			} else {
				var io = new IntersectionObserver( function ( entries ) {
					entries.forEach( function ( entry ) {
						if ( entry.isIntersecting ) {
							entry.target.classList.add( 'is-visible' );
							io.unobserve( entry.target );
						}
					} );
				}, { threshold: 0.12, rootMargin: '0px 0px -60px 0px' } );
				revealEls.forEach( function ( el ) { io.observe( el ); } );
			}
		} else {
			revealEls.forEach( function ( el ) { el.classList.add( 'is-visible' ); } );
		}

		/* ---------------- درگ برای اسکرول افقی (پرسنل + وبلاگ) ----------------
		 *
		 * این نسخه برای هر دو ماوس و تاچ کار می‌کنه:
		 * - در دسکتاپ: کلیک و درگ با ماوس
		 * - در موبایل: تاچ معمولی (بدون مداخله)
		 * - event ها روی document bind می‌شن تا اگه موس از element خارج شد، درگ ادامه پیدا کنه
		 * - از Pointer Events API استفاده می‌کنیم که هم ماوس و هم تاچ و هم pen رو پشتیبانی می‌کنه
		 *
		 * نکته مهم: کلیک روی لینک‌ها باید کار کنه — فقط اگر درگ واقعی انجام شد، از کلیک جلوگیری می‌شه.
		 */
		document.querySelectorAll( '[data-drag-scroll]' ).forEach( function ( scroller ) {

			var isDown    = false;
			var startX    = 0;
			var scrollStart = 0;
			var moved     = false;
			var pointerId = null;

			var isMousePointer = function ( e ) {
				return e.pointerType === 'mouse';
			};

			scroller.addEventListener( 'pointerdown', function ( e ) {
				if ( ! isMousePointer( e ) ) return;
				if ( e.button !== 0 ) return;

				isDown = true;
				moved = false;
				pointerId = e.pointerId;
				startX = e.clientX;
				scrollStart = scroller.scrollLeft;
				scroller.classList.add( 'is-dragging' );

				if ( scroller.setPointerCapture ) {
					try {
						scroller.setPointerCapture( e.pointerId );
					} catch ( err ) {}
				}
			} );

			scroller.addEventListener( 'pointermove', function ( e ) {
				if ( ! isDown || e.pointerId !== pointerId ) return;

				var dx = e.clientX - startX;
				if ( Math.abs( dx ) > 4 ) moved = true;

				scroller.scrollLeft = scrollStart - dx;

				// فقط وقتی درگ واقعی انجام شده از رفتار پیش‌فرض جلوگیری کن
				if ( moved ) {
					e.preventDefault();
				}
			} );

			var endDrag = function ( e ) {
				if ( ! isDown ) return;
				if ( e && e.pointerId !== undefined && e.pointerId !== pointerId ) return;

				isDown = false;
				pointerId = null;
				scroller.classList.remove( 'is-dragging' );

				if ( scroller.releasePointerCapture && e && e.pointerId !== undefined ) {
					try {
						scroller.releasePointerCapture( e.pointerId );
					} catch ( err ) {}
				}
			};

			scroller.addEventListener( 'pointerup', endDrag );
			scroller.addEventListener( 'pointercancel', endDrag );
			scroller.addEventListener( 'pointerleave', endDrag );

			// جلوگیری از کلیک ناخواسته روی کارت‌ها فقط بعد از درگ واقعی
			scroller.addEventListener( 'click', function ( e ) {
				if ( moved ) {
					e.preventDefault();
					e.stopPropagation();
					moved = false;
				}
			}, true );

			// جلوگیری از drag پیش‌فرض تصاویر
			scroller.addEventListener( 'dragstart', function ( e ) {
				e.preventDefault();
			} );

			// جلوگیری از انتخاب متن هنگام درگ
			scroller.addEventListener( 'selectstart', function ( e ) {
				if ( isDown ) e.preventDefault();
			} );
		} );

		/* ---------------- آکاردئون سوالات متداول ---------------- */
		var triggers = document.querySelectorAll( '.accordion-trigger' );
		triggers.forEach( function ( btn ) {
			btn.addEventListener( 'click', function () {
				var item = btn.closest( '.accordion-item' );
				var isOpen = item.classList.contains( 'is-open' );

				item.parentElement.querySelectorAll( '.accordion-item.is-open' ).forEach( function ( openItem ) {
					if ( openItem !== item ) {
						openItem.classList.remove( 'is-open' );
						openItem.querySelector( '.accordion-trigger' ).setAttribute( 'aria-expanded', 'false' );
					}
				} );

				item.classList.toggle( 'is-open', ! isOpen );
				btn.setAttribute( 'aria-expanded', ! isOpen ? 'true' : 'false' );
			} );
		} );

		/* ---------------- چرخش مجوزها در فوتر ---------------- */
		var rotator = document.querySelector( '.footer-license-rotator' );
		if ( rotator ) {
			var slides = rotator.querySelectorAll( '.footer-license-slide' );
			if ( slides.length > 1 ) {
				var interval = parseInt( rotator.getAttribute( 'data-interval' ), 10 ) || 3000;
				var current = 0;
				setInterval( function () {
					slides[ current ].classList.remove( 'is-active' );
					current = ( current + 1 ) % slides.length;
					slides[ current ].classList.add( 'is-active' );
				}, interval );
			}
		}

		/* ---------------- دکمه بازگشت به بالا ---------------- */
		var scrollTopBtn = document.getElementById( 'noghre-scroll-top' );
		if ( scrollTopBtn ) {
			var toggleScrollBtn = function () {
				scrollTopBtn.classList.toggle( 'is-visible', window.scrollY > 400 );
			};
			window.addEventListener( 'scroll', toggleScrollBtn, { passive: true } );
			toggleScrollBtn();
			scrollTopBtn.addEventListener( 'click', function () {
				window.scrollTo( { top: 0, behavior: 'smooth' } );
			} );
		}

		/* ---------------- دیدگاه‌ها: کشویی ---------------- */
		var commentsToggle = document.querySelector( '.comments-toggle' );
		var commentsPanel  = document.querySelector( '.comments-drawer-panel' );
		if ( commentsToggle && commentsPanel ) {
			var openComments = function () {
				commentsPanel.classList.add( 'is-open' );
				commentsToggle.setAttribute( 'aria-expanded', 'true' );
			};
			commentsToggle.addEventListener( 'click', function () {
				var isOpen = commentsPanel.classList.toggle( 'is-open' );
				commentsToggle.setAttribute( 'aria-expanded', isOpen ? 'true' : 'false' );
			} );
			if ( /^#(comment|respond|comments)/.test( window.location.hash ) ) {
				openComments();
			}
		}

		/* ---------------- بازخوانی سوال امنیتی ---------------- */
		var noghreRefreshCaptcha = function ( formEl ) {
			if ( ! formEl || ! window.noghreAjax ) return;
			var fd = new FormData();
			fd.append( 'action', 'noghre_refresh_captcha' );
			fetch( noghreAjax.url, { method: 'POST', body: fd } )
				.then( function ( res ) { return res.json(); } )
				.then( function ( data ) {
					if ( ! data.success ) return;
					if ( data.data.longpress ) {
						var okEl = formEl.querySelector( '.noghre-longpress-ok' );
						var fillBtn = formEl.querySelector( '.noghre-longpress-fill' );
						if ( okEl ) okEl.value = '0';
						if ( fillBtn ) fillBtn.classList.remove( 'is-filling' );
						return;
					}
					var qEl = formEl.querySelector( '.noghre-captcha-question' );
					var hEl = formEl.querySelector( '.noghre-captcha-hash' );
					var aEl = formEl.querySelector( '.noghre-captcha-input' );
					if ( qEl && data.data.question ) qEl.textContent = data.data.question;
					if ( hEl && data.data.hash ) hEl.value = data.data.hash;
					if ( aEl ) aEl.value = '';
				} )
				.catch( function () {} );
		};

		/* ---------------- دکمه تغییر دستی سوال امنیتی ---------------- */
		document.querySelectorAll( '.noghre-captcha-refresh' ).forEach( function ( btn ) {
			btn.addEventListener( 'click', function ( e ) {
				e.preventDefault();
				var form = btn.closest( 'form' );
				noghreRefreshCaptcha( form );
			} );
		} );

		/* ---------------- بررسی کد تخفیف در فرم رزرو ---------------- */
		var discountCheckBtn = document.querySelector( '.noghre-discount-check-btn' );
		if ( discountCheckBtn && window.noghreAjax ) {
			discountCheckBtn.addEventListener( 'click', function () {
				var codeInput = document.getElementById( 'noghre-booking-discount-code' );
				var msgEl = document.querySelector( '.noghre-discount-check-message' );
				var mobileInput = document.querySelector( '#noghre-booking-form [name="customer_mobile"]' );
				var code = codeInput ? codeInput.value.trim() : '';

				msgEl.textContent = '';
				msgEl.classList.remove( 'is-error', 'is-success' );

				if ( ! code ) {
					msgEl.textContent = 'کد تخفیف را وارد کنید.';
					msgEl.classList.add( 'is-error' );
					return;
				}

				discountCheckBtn.disabled = true;
				var fd = new FormData();
				fd.append( 'action', 'noghre_check_discount_code' );
				fd.append( 'nonce', noghreAjax.bookingNonce );
				fd.append( 'code', code );
				fd.append( 'mobile', mobileInput ? mobileInput.value.trim() : '' );
				document.querySelectorAll( '#noghre-booking-form input[name="services[]"]:checked' ).forEach( function ( cb ) {
					fd.append( 'services[]', cb.value );
				} );

				fetch( noghreAjax.url, { method: 'POST', body: fd } )
					.then( function ( res ) { return res.json(); } )
					.then( function ( data ) {
						discountCheckBtn.disabled = false;
						if ( data.success ) {
							msgEl.textContent = data.data.message;
							msgEl.classList.add( 'is-success' );
						} else {
							msgEl.textContent = ( data.data && data.data.message ) || 'کد تخفیف معتبر نیست.';
							msgEl.classList.add( 'is-error' );
						}
					} )
					.catch( function () {
						discountCheckBtn.disabled = false;
						msgEl.textContent = 'ارتباط با سرور برقرار نشد.';
						msgEl.classList.add( 'is-error' );
					} );
			} );
		}

		/* ---------------- کپچای لمس طولانی ---------------- */
		document.querySelectorAll( '.noghre-captcha-longpress' ).forEach( function ( wrap ) {
			var form = wrap.closest( 'form' );
			if ( ! form ) return;
			var okField = wrap.querySelector( '.noghre-longpress-ok' );
			var submitBtn = form.querySelector( 'button[type="submit"], input[type="submit"]' );
			if ( ! submitBtn ) return;

			var holdTimer = null;
			submitBtn.classList.add( 'noghre-longpress-fill' );

			var startHold = function ( e ) {
				if ( okField.value === '1' ) return;
				e.preventDefault();
				submitBtn.classList.remove( 'is-filling' );
				void submitBtn.offsetWidth;
				submitBtn.classList.add( 'is-filling' );
				holdTimer = setTimeout( function () {
					okField.value = '1';
					if ( typeof submitBtn.click === 'function' ) submitBtn.click();
				}, 2000 );
			};
			var cancelHold = function () {
				if ( okField.value === '1' ) return;
				clearTimeout( holdTimer );
				submitBtn.classList.remove( 'is-filling' );
			};

			submitBtn.addEventListener( 'mousedown', startHold );
			submitBtn.addEventListener( 'touchstart', startHold, { passive: false } );
			submitBtn.addEventListener( 'mouseup', cancelHold );
			submitBtn.addEventListener( 'mouseleave', cancelHold );
			submitBtn.addEventListener( 'touchend', cancelHold );

			form.addEventListener( 'submit', function ( e ) {
				if ( okField.value !== '1' ) {
					e.preventDefault();
					e.stopImmediatePropagation();
				}
			}, true );
		} );

		/* ---------------- فهرست کشویی چندانتخابی خدمات ---------------- */
		document.querySelectorAll( '.noghre-multiselect' ).forEach( function ( details ) {
			var labelEl = details.querySelector( '.noghre-multiselect-label' );
			var checkboxes = details.querySelectorAll( 'input[type="checkbox"]' );
			var updateLabel = function () {
				var checked = Array.prototype.filter.call( checkboxes, function ( c ) { return c.checked; } );
				if ( ! checked.length ) {
					labelEl.textContent = '— انتخاب کنید —';
				} else if ( checked.length === 1 ) {
					labelEl.textContent = checked[0].value;
				} else {
					labelEl.textContent = checked.length + ' خدمت انتخاب شده';
				}
			};
			checkboxes.forEach( function ( c ) { c.addEventListener( 'change', updateLabel ); } );
			document.addEventListener( 'click', function ( e ) {
				if ( details.hasAttribute( 'open' ) && ! details.contains( e.target ) ) {
					details.removeAttribute( 'open' );
				}
			} );
		} );

		/* ---------------- فرم باشگاه تخفیف ---------------- */
		var discountForm = document.getElementById( 'noghre-discount-form' );
		if ( discountForm && window.noghreAjax ) {
			var msgEl = discountForm.parentElement.querySelector( '.discount-form-message' );
			discountForm.addEventListener( 'submit', function ( e ) {
				e.preventDefault();
				var name   = discountForm.querySelector( '[name="name"]' ).value.trim();
				var mobile = discountForm.querySelector( '[name="mobile"]' ).value.trim();
				var submitBtn = discountForm.querySelector( '.discount-submit' );

				msgEl.textContent = '';
				msgEl.classList.remove( 'is-error', 'is-success' );

				if ( name.length < 2 ) {
					msgEl.textContent = 'لطفاً نام و نام خانوادگی را کامل وارد کنید.';
					msgEl.classList.add( 'is-error' );
					return;
				}
				if ( ! /^09\d{9}$/.test( mobile ) ) {
					msgEl.textContent = 'شماره موبایل باید ۱۱ رقم و با ۰۹ شروع شود.';
					msgEl.classList.add( 'is-error' );
					return;
				}

				submitBtn.disabled = true;
				var originalText = submitBtn.textContent;
				submitBtn.textContent = 'در حال ارسال...';

				var formData = new FormData( discountForm );
				formData.append( 'action', 'noghre_discount_signup' );
				formData.append( 'nonce', noghreAjax.nonce );

				fetch( noghreAjax.url, { method: 'POST', body: formData } )
					.then( function ( res ) { return res.json(); } )
					.then( function ( data ) {
						submitBtn.disabled = false;
						submitBtn.textContent = originalText;
						if ( data.success ) {
							msgEl.textContent = data.data.message || 'ثبت‌نام با موفقیت انجام شد.';
							msgEl.classList.add( 'is-success' );
							discountForm.reset();
						} else {
							msgEl.textContent = ( data.data && data.data.message ) || 'خطایی رخ داد، دوباره تلاش کنید.';
							msgEl.classList.add( 'is-error' );
						}
						noghreRefreshCaptcha( discountForm );
					} )
					.catch( function () {
						submitBtn.disabled = false;
						submitBtn.textContent = originalText;
						msgEl.textContent = 'ارتباط با سرور برقرار نشد.';
						msgEl.classList.add( 'is-error' );
					} );
			} );
		}

		/* ---------------- فرم رزرو وقت ---------------- */
		var bookingForm = document.getElementById( 'noghre-booking-form' );
		if ( bookingForm && window.noghreAjax ) {
			var bookingMsgEl = bookingForm.querySelector( '.noghre-booking-message' );
			bookingForm.addEventListener( 'submit', function ( e ) {
				e.preventDefault();
				var submitBtn = bookingForm.querySelector( '.noghre-booking-submit' );
				bookingMsgEl.textContent = '';
				bookingMsgEl.classList.remove( 'is-error', 'is-success' );

				submitBtn.disabled = true;
				var originalText = submitBtn.textContent;
				submitBtn.textContent = 'در حال ثبت...';

				var formData = new FormData( bookingForm );
				formData.append( 'action', 'noghre_booking_submit' );
				formData.append( 'nonce', noghreAjax.bookingNonce );

				fetch( noghreAjax.url, { method: 'POST', body: formData } )
					.then( function ( res ) { return res.json(); } )
					.then( function ( data ) {
						if ( data.success ) {
							if ( data.data.redirect_url ) {
								window.location = data.data.redirect_url;
								return;
							}
							submitBtn.disabled = false;
							submitBtn.textContent = originalText;
							bookingMsgEl.classList.add( 'is-success' );
							var html = data.data.message + ' کد پیگیری شما: <strong dir="ltr">' + data.data.tracking_code + '</strong>';
							if ( data.data.tracking_url ) {
								html += '<br><a href="' + data.data.tracking_url + '">مشاهده وضعیت رزرو</a>';
							}
							bookingMsgEl.innerHTML = html;
							bookingForm.reset();
						} else {
							submitBtn.disabled = false;
							submitBtn.textContent = originalText;
							bookingMsgEl.classList.add( 'is-error' );
							bookingMsgEl.textContent = ( data.data && data.data.message ) || 'خطایی رخ داد، دوباره تلاش کنید.';
						}
						noghreRefreshCaptcha( bookingForm );
					} )
					.catch( function () {
						submitBtn.disabled = false;
						submitBtn.textContent = originalText;
						bookingMsgEl.classList.add( 'is-error' );
						bookingMsgEl.textContent = 'ارتباط با سرور برقرار نشد.';
						noghreRefreshCaptcha( bookingForm );
					} );
			} );
		}

	} );
} )();