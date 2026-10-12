<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/* ============================================================
 * اپ موبایل — قاب کلی + AJAX بارگذاری بخش‌ها
 * ============================================================ */

/* 🔑 صف‌بندی assets اختصاصی اپ موبایل */
function noghre_enqueue_app_assets() {
	$ver = defined( 'NOGHRE_VERSION' ) ? NOGHRE_VERSION : '1.0.0';

	/* 🔑 صف‌بندی dashicons برای آیکون‌های اپ */
	wp_enqueue_style( 'dashicons' );

	wp_enqueue_style(
		'noghre-app-shared',
		NOGHRE_URI . '/assets/css/app-shared.css',
		array( 'dashicons' ),
		$ver
	);

	wp_enqueue_style(
		'noghre-app-admin',
		NOGHRE_URI . '/assets/css/app-admin.css',
		array( 'noghre-app-shared' ),
		$ver
	);

	wp_enqueue_script(
		'noghre-app-shared',
		NOGHRE_URI . '/assets/js/app-shared.js',
		array(),
		$ver,
		true
	);

	wp_enqueue_script(
		'noghre-app-admin',
		NOGHRE_URI . '/assets/js/app-admin.js',
		array( 'noghre-app-shared' ),
		$ver,
		true
	);

	$admin_key = noghre_get_admin_access_key();
	$nonce     = wp_create_nonce( 'noghre_booking_admin_action' );

	wp_localize_script( 'noghre-app-shared', 'noghreAppConfig', array(
		'ajaxUrl'   => admin_url( 'admin-ajax.php' ),
		'baseUrl'   => home_url( '/mobile/' ),
		'adminKey'  => $admin_key,
		'nonce'     => $nonce,
		'homeUrl'   => home_url( '/' ),
	) );
}

/* 🔑 هوک: فقط در صفحاتی که شورت‌کد اپ موبایل دارند assetها لود بشن */
function noghre_maybe_enqueue_app_assets() {
	if ( is_admin() ) return;

	if ( is_page() || is_singular() ) {
		$post = get_post();
		if ( $post && (
			has_shortcode( $post->post_content, 'ariatin-noghreh-mobile' ) ||
			has_shortcode( $post->post_content, 'ariatin-noghreh-signup' ) ||
			has_shortcode( $post->post_content, 'ariatin-noghreh-scanner' )
		) ) {
			noghre_enqueue_app_assets();
		}
	}
}
add_action( 'wp_enqueue_scripts', 'noghre_maybe_enqueue_app_assets', 20 );

/* ============================================================
 * 🔑 helper: تبدیل shortcode به section
 * ============================================================ */
function noghre_section_from_shortcode( $shortcode ) {
	switch ( $shortcode ) {
		case 'ariatin-noghreh-list':     return 'list';
		case 'ariatin-noghreh-signup':   return 'signup';
		case 'ariatin-noghreh-scanner':  return 'scanner';
		case 'ariatin-noghreh-mobile':   return 'mobile';
		default:                          return 'mobile';
	}
}

/* ============================================================
 * 🔑 helper: رندر محتوای بخش
 * ============================================================ */
function noghre_render_section_content( $section, $customer_count, $admin_key ) {
	if ( $section === 'signup' ) {
		return noghre_get_signup_box_html( $customer_count, $admin_key );
	} elseif ( $section === 'scanner' ) {
		return noghre_render_qr_scanner_block( $admin_key );
	} elseif ( $section === 'list' ) {
		return noghre_shortcode_booking_list();
	} elseif ( $section === 'check' ) {
		return noghre_shortcode_booking_check();
	} elseif ( $section === 'mobile' ) {
		/* 🔑 محتوای صفحه اصلی اپ بر اساس تنظیمات کاربر */
		$app  = noghre_get_app_options();
		$view = ! empty( $app['mobile_section_view'] ) ? $app['mobile_section_view'] : 'both';

		switch ( $view ) {
			case 'list':
				return noghre_shortcode_booking_list();
			case 'signup':
				return noghre_get_signup_box_html( $customer_count, $admin_key );
			case 'scanner':
				return noghre_render_qr_scanner_block( $admin_key );
			case 'check':
				return noghre_shortcode_booking_check();
			case 'both':
			default:
				return noghre_get_signup_box_html( $customer_count, $admin_key ) . noghre_shortcode_booking_list();
		}
	} else {
		/* fallback: محتوای پیش‌فرض */
		return noghre_get_signup_box_html( $customer_count, $admin_key ) . noghre_shortcode_booking_list();
	}
}

/* ============================================================
 * 🔑 helper: حل کردن بخش پیش‌فرض
 * ============================================================ */
function noghre_resolve_default_section( $app ) {
	$default_nav_id = ! empty( $app['default_nav_id'] ) ? $app['default_nav_id'] : 'mobile';

	if ( ! empty( $app['app_bottom_nav'] ) && is_array( $app['app_bottom_nav'] ) ) {
		foreach ( $app['app_bottom_nav'] as $m ) {
			if ( ( $m['id'] ?? '' ) === $default_nav_id ) {
				return noghre_section_from_shortcode( $m['shortcode'] ?? '' );
			}
		}
	}

	return 'mobile';
}

/* ============================================================
 * 🔑 helper: شمارش تعداد مخاطبان باشگاه مشتریان
 * ============================================================ */
function noghre_get_customer_count() {
	$customer_count = 0;
	$sms = noghre_get_sms_options();
	if ( ! empty( $sms['sms_customer_club_phonebook_id'] ) ) {
		$phonebooks = noghre_sms_get_phonebooks();
		if ( ! is_wp_error( $phonebooks ) ) {
			foreach ( $phonebooks as $book ) {
				if ( (int) $book['id'] === (int) $sms['sms_customer_club_phonebook_id'] ) {
					$customer_count = (int) $book['count'];
					break;
				}
			}
		}
	}
	return $customer_count;
}

/* ============================================================
 * 🔑 بررسی مجوز اجرای اپ (app_page_id تنظیم شده؟)
 * ============================================================ */
function noghre_is_app_authorized() {
	$app = noghre_get_app_options();
	return ! empty( $app['app_page_id'] ) && (int) $app['app_page_id'] > 0;
}

/* ============================================================
 * شورت‌کد اصلی اپ موبایل
 * ============================================================ */
function noghre_shortcode_mobile_app() {
	$app       = noghre_get_app_options();
	$admin_key = noghre_get_admin_access_key();

	if ( $admin_key === '' ) {
		return '<div class="noghre-app-no-access">'
		     . '<h2>شناسه مدیریت تعریف نشده</h2>'
		     . '<p>لطفاً ابتدا از تنظیمات نقره → پیشرفته → شناسه مدیریت، یک شناسه ایجاد کنید.</p>'
		     . '</div>';
	}

	/* 🔑 بررسی مجوز: اگه برگه اپ تنظیم نشده → ریدایرکت به صفحه اصلی */
	if ( ! noghre_is_app_authorized() ) {
		if ( ! is_admin() ) {
			wp_safe_redirect( home_url( '/' ) );
			exit;
		}
	}

	/* 🔑 اگه برگه اپ تنظیم شده، فقط همون برگه مجاز به اجرا هست */
	$app_page_id = (int) $app['app_page_id'];
	if ( $app_page_id > 0 ) {
		$current_page_id = get_queried_object_id();
		if ( $current_page_id && $current_page_id !== $app_page_id ) {
			wp_safe_redirect( home_url( '/' ) );
			exit;
		}
	}

	$m_param   = isset( $_GET['m'] ) ? sanitize_text_field( wp_unslash( $_GET['m'] ) ) : '';
	$has_token = noghre_is_app_request_authenticated();

	if ( $has_token ) {
		/* 🔑 بخش درخواستی */
		$requested_section = isset( $_GET['section'] ) ? sanitize_key( $_GET['section'] ) : '';
		$code = isset( $_GET['code'] ) ? sanitize_text_field( wp_unslash( $_GET['code'] ) ) : '';

		$customer_count = noghre_get_customer_count();

		/* 🔑 تعیین بخش */
		$actual_section = '';
		if ( $requested_section !== '' ) {
			$actual_section = $requested_section;
		} elseif ( $code !== '' ) {
			$actual_section = 'check';
		} else {
			/* 🔑 بخش پیش‌فرض از default_nav_id */
			$actual_section = noghre_resolve_default_section( $app );
		}

		$content = noghre_render_section_content( $actual_section, $customer_count, $admin_key );

		return noghre_wrap_in_app_frame( $content, $actual_section );
	}

	if ( $m_param !== '' && ! noghre_is_admin_access( $m_param ) ) {
		?>
		<script>
		try { localStorage.removeItem('noghre_access_token'); } catch (e) {}
		</script>
		<?php
		$login_html   = noghre_get_admin_login_html();
		$login_script = noghre_get_admin_login_script();
		return $login_html . $login_script;
	}

	if ( $m_param !== '' && noghre_is_admin_access( $m_param ) ) {
		return noghre_get_token_creation_html( $m_param );
	}

	$login_html   = noghre_get_admin_login_html();
	$login_script = noghre_get_admin_login_script();
	return $login_html . $login_script;
}
add_shortcode( 'ariatin-noghreh-mobile', 'noghre_shortcode_mobile_app' );

/* ============================================================
 * HTML صفحه ایجاد توکن
 * ============================================================ */
function noghre_get_token_creation_html( $m_param ) {
	ob_start();
	?>
	<div class="noghre-app-loading">
		<div class="noghre-app-loading-inner">
			<div class="noghre-app-loading-spinner"></div>
			<p class="noghre-app-loading-text">در حال آماده‌سازی اپلیکیشن...</p>
		</div>
	</div>
	<script>
	(function(){
		var fd = new FormData();
		fd.append('action', 'noghre_create_token');
		fd.append('m', '<?php echo esc_js( $m_param ); ?>');

		fetch('<?php echo esc_js( admin_url( 'admin-ajax.php' ) ); ?>', {
			method: 'POST',
			body: fd
		})
			.then(function(res){ return res.json(); })
			.then(function(data){
				if (data.success) {
					try {
						localStorage.setItem('noghre_access_token', data.data.token);
					} catch (e) {}

					var url = new URL(window.location.href);
					url.searchParams.delete('m');
					window.location.replace(url.toString());
				} else {
					alert('خطا در ساخت توکن. لطفاً دوباره تلاش کنید.');
					window.location.href = window.location.pathname;
				}
			})
			.catch(function(){
				alert('ارتباط با سرور برقرار نشد.');
			});
	})();
	</script>
	<?php
	return ob_get_clean();
}

/* ============================================================
 * داده‌های هدر اپ
 * ============================================================ */
function noghre_get_mobile_header_data( $active_section ) {
	$base_url = home_url( '/mobile/' );

	$list_url    = add_query_arg( array( 'section' => 'list' ), $base_url );
	$signup_url  = add_query_arg( array( 'section' => 'signup' ), $base_url );
	$scanner_url = add_query_arg( array( 'section' => 'scanner' ), $base_url );
	$home_url    = add_query_arg( array( 'section' => 'mobile' ), $base_url );

	$back_url = '';
	switch ( $active_section ) {
		case 'mobile':
			$back_url = '';
			break;
		case 'list':
		case 'signup':
		case 'scanner':
			$back_url = $home_url;
			break;
		case 'check':
			$back_url = $list_url;
			break;
		default:
			$back_url = $home_url;
			break;
	}

	return array(
		'back_url'    => $back_url,
		'show_search' => in_array( $active_section, array( 'mobile', 'list' ), true ),
	);
}

/* ============================================================
 * داده‌های نوار پایین اپ
 * ============================================================ */
function noghre_get_mobile_bottom_nav_data( $active_section ) {
	$app      = noghre_get_app_options();
	$base_url = home_url( '/mobile/' );

	$list_url    = add_query_arg( array( 'section' => 'list' ), $base_url );
	$signup_url  = add_query_arg( array( 'section' => 'signup' ), $base_url );
	$scanner_url = add_query_arg( array( 'section' => 'scanner' ), $base_url );
	$home_url    = add_query_arg( array( 'section' => 'mobile' ), $base_url );

	$menus_data = array();
	if ( ! empty( $app['app_bottom_nav'] ) && is_array( $app['app_bottom_nav'] ) ) {
		$menus = $app['app_bottom_nav'];
		usort( $menus, function( $a, $b ) {
			return ( $a['sort_order'] ?? 0 ) <=> ( $b['sort_order'] ?? 0 );
		} );
		foreach ( $menus as $m ) {
			if ( empty( $m['active'] ) ) continue;
			if ( empty( $m['title'] ) ) continue;

			$menu_url = '#';
			$menu_section = '';
			switch ( $m['url_type'] ) {
				case 'home':    $menu_url = home_url( '/' ); break;
				case 'admin':   $menu_url = admin_url( 'admin.php?page=noghre-panel' ); break;
				case 'external': $menu_url = ! empty( $m['url'] ) ? $m['url'] : home_url( '/' ); break;
				default:
					$sc = ! empty( $m['shortcode'] ) ? $m['shortcode'] : 'ariatin-noghreh-mobile';
					switch ( $sc ) {
						case 'ariatin-noghreh-signup':   $menu_url = $signup_url; $menu_section = 'signup'; break;
						case 'ariatin-noghreh-list':     $menu_url = $list_url; $menu_section = 'list'; break;
						case 'ariatin-noghreh-scanner':  $menu_url = $scanner_url; $menu_section = 'scanner'; break;
						default:                          $menu_url = $home_url; $menu_section = 'mobile'; break;
					}
					break;
			}

			$menus_data[] = array(
				'id'        => $m['id'] ?? '',
				'title'     => $m['title'],
				'icon_html' => noghre_render_app_menu_icon( $m['icon'] ?? 'dashicons-admin-generic' ),
				'url'       => $menu_url,
				'section'   => $menu_section,
				'active'    => ( $menu_section === $active_section ) ? 1 : 0,
			);
		}
	}

	return $menus_data;
}

/* ============================================================
 * AJAX: بارگذاری بخش اپ
 * ============================================================ */
function noghre_ajax_mobile_section() {
	if ( ! noghre_is_admin_request() ) {
		wp_send_json_error( array(
			'message'     => 'دسترسی غیرمجاز.',
			'code'        => 'invalid_admin_key',
			'redirect_to' => home_url( '/mobile/' ),
		) );
	}

	$section = isset( $_POST['section'] ) ? sanitize_key( $_POST['section'] ) : 'mobile';
	$code    = isset( $_POST['code'] ) ? sanitize_text_field( wp_unslash( $_POST['code'] ) ) : '';

	$admin_key = noghre_get_admin_access_key();
	$app       = noghre_get_app_options();

	$_GET['m'] = $admin_key;
	if ( $section === 'check' && $code ) {
		$_GET['code'] = $code;
	}

	$orderby = isset( $_POST['orderby'] ) ? sanitize_key( $_POST['orderby'] ) : '';
	$order   = isset( $_POST['order'] ) ? sanitize_key( $_POST['order'] ) : '';
	$paged   = isset( $_POST['paged'] ) ? absint( $_POST['paged'] ) : 1;
	$status_filter = isset( $_POST['status_filter'] ) ? sanitize_key( $_POST['status_filter'] ) : '';

	if ( $orderby )       $_GET['orderby']       = $orderby;
	if ( $order )         $_GET['order']         = $order;
	if ( $paged )         $_GET['paged']         = $paged;

	$_GET['status_filter'] = $status_filter;

	$customer_count = noghre_get_customer_count();

	/* 🔑 تعیین بخش */
	$actual_section = '';
	if ( $section === 'default' ) {
		$actual_section = noghre_resolve_default_section( $app );
	} elseif ( $section !== '' ) {
		$actual_section = $section;
	} else {
		$actual_section = noghre_resolve_default_section( $app );
	}

	$content = noghre_render_section_content( $actual_section, $customer_count, $admin_key );

	wp_send_json_success( array(
		'section' => $actual_section,
		'code'    => $code,
		'content' => $content,
		'header'  => noghre_get_mobile_header_data( $actual_section ),
		'nav'     => noghre_get_mobile_bottom_nav_data( $actual_section ),
	) );
}
add_action( 'wp_ajax_noghre_mobile_section', 'noghre_ajax_mobile_section' );
add_action( 'wp_ajax_nopriv_noghre_mobile_section', 'noghre_ajax_mobile_section' );

/* ============================================================
 * قاب کلی اپ (فقط ساختار HTML)
 * ============================================================ */
function noghre_wrap_in_app_frame( $content, $active_section = '' ) {
	$admin_key = noghre_get_admin_access_key();
	$app       = noghre_get_app_options();

	$header_data  = noghre_get_mobile_header_data( $active_section );
	$bottom_menus = noghre_get_mobile_bottom_nav_data( $active_section );

	$current_code = isset( $_GET['code'] ) ? sanitize_text_field( wp_unslash( $_GET['code'] ) ) : '';
	$nonce        = wp_create_nonce( 'noghre_booking_admin_action' );

	ob_start();
	?>
	<meta name="noghre-admin-key" content="<?php echo esc_attr( $admin_key ); ?>">
	<meta name="noghre-actions-nonce" content="<?php echo esc_attr( $nonce ); ?>">
	<div class="noghre-mobile-app" id="noghre-mobile-app"
	     data-admin-key="<?php echo esc_attr( $admin_key ); ?>"
	     data-section="<?php echo esc_attr( $active_section ); ?>"
	     data-code="<?php echo esc_attr( $current_code ); ?>"
	     data-nonce="<?php echo esc_attr( $nonce ); ?>">

		<header class="noghre-app-header">
			<div class="noghre-app-header-inner">
				<div class="noghre-app-brand">
					<span class="noghre-app-logo"><?php echo esc_html( $app['app_logo_text'] ); ?></span>
					<div>
						<strong><?php echo esc_html( $app['app_title'] ); ?></strong>
						<small><?php echo esc_html( $app['app_subtitle'] ); ?></small>
					</div>
				</div>

				<?php if ( ! empty( $header_data['back_url'] ) ) : ?>
					<a href="<?php echo esc_url( $header_data['back_url'] ); ?>" class="noghre-app-back" data-nav="back" title="بازگشت" aria-label="بازگشت" onclick="return false;">
						<span class="dashicons dashicons-arrow-left-alt2"></span>
					</a>
				<?php else : ?>
					<span class="noghre-app-back noghre-app-back-hidden" aria-hidden="true"></span>
				<?php endif; ?>
			</div>

			<div class="noghre-app-search-wrap"<?php echo $header_data['show_search'] ? '' : ' style="display:none;"'; ?>>
				<input type="text" id="noghre-app-search" placeholder="جستجو: کد پیگیری / نام / موبایل..." autocomplete="off">
				<select id="noghre-app-status">
					<option value="">همه وضعیت‌ها</option>
					<option value="pending">در انتظار بررسی</option>
					<option value="confirmed">تایید شده</option>
					<option value="edited">ویرایش شده</option>
					<option value="used">استفاده‌شده</option>
					<option value="unused">استفاده نشده</option>
				</select>
			</div>
		</header>

		<main class="noghre-app-body" id="noghre-app-body">
			<?php echo $content; ?>
		</main>

		<nav class="noghre-app-bottom-bar" id="noghre-app-nav">
			<?php foreach ( $bottom_menus as $menu ) : ?>
				<a href="<?php echo esc_url( $menu['url'] ); ?>" class="noghre-app-nav-item<?php echo $menu['active'] ? ' is-active' : ''; ?>" data-nav="menu" data-section="<?php echo esc_attr( $menu['section'] ); ?>" onclick="return false;">
					<span><?php echo $menu['icon_html']; ?></span>
					<small><?php echo esc_html( $menu['title'] ); ?></small>
				</a>
			<?php endforeach; ?>
		</nav>

		<?php if ( ! empty( $app['pwa_enabled'] ) ) : ?>
			<button type="button" class="noghre-pwa-install-btn" id="noghre-pwa-install" style="display:none;">
				<span class="dashicons dashicons-download"></span> نصب اپلیکیشن
			</button>
		<?php endif; ?>
	</div>

	<div class="noghre-offline-overlay" id="noghre-offline-overlay" aria-hidden="true">
		<div class="noghre-offline-content">
			<svg class="noghre-offline-icon" viewBox="0 0 24 24" width="80" height="80" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
				<line x1="1" y1="1" x2="23" y2="23"></line>
				<path d="M16.72 11.06A10.94 10.94 0 0 1 19 12.55"></path>
				<path d="M5 12.55a10.94 10.94 0 0 1 5.17-2.39"></path>
				<path d="M10.71 5.05A16 16 0 0 1 22.58 9"></path>
				<path d="M1.42 9a15.91 15.91 0 0 1 4.7-2.88"></path>
				<path d="M8.53 16.11a6 6 0 0 1 6.95 0"></path>
				<line x1="12" y1="20" x2="12.01" y2="20"></line>
			</svg>
			<p class="noghre-offline-text">اتصال برقرار نشد</p>
			<p class="noghre-offline-subtext">لطفاً اینترنت خود را بررسی کنید</p>
			<p class="noghre-offline-retry">در حال تلاش برای اتصال مجدد...</p>
		</div>
	</div>
	<?php
	return ob_get_clean();
}

/* ============================================================
 * noindex برای صفحات اپ
 * ============================================================ */
function noghre_mobile_app_noindex() {
	if ( is_page() || is_singular() ) {
		$post = get_post();
		if ( $post && (
			has_shortcode( $post->post_content, 'ariatin-noghreh-mobile' ) ||
			has_shortcode( $post->post_content, 'ariatin-noghreh-signup' ) ||
			has_shortcode( $post->post_content, 'ariatin-noghreh-scanner' )
		) ) {
			echo '<meta name="robots" content="noindex, nofollow, noarchive, nosnippet">' . "\n";
			echo '<meta name="googlebot" content="noindex, nofollow">' . "\n";
		}
	}
}
add_action( 'wp_head', 'noghre_mobile_app_noindex', 1 );