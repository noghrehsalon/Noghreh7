<?php
/**
 * توابع اصلی قالب سالن زیبایی نقره
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'NOGHRE_VERSION', '1.0.0' );
define( 'NOGHRE_DIR', get_template_directory() );
define( 'NOGHRE_URI', get_template_directory_uri() );

/* ---------------------------------------------------------------------- *
 * Cache Buster
 * ---------------------------------------------------------------------- */
function noghre_get_cache_buster_version() {
	$bust = (int) get_option( 'noghre_cache_buster', 0 );
	if ( $bust > 0 ) {
		return NOGHRE_VERSION . '.' . $bust;
	}
	return NOGHRE_VERSION;
}

/* ---------------------------------------------------------------------- *
 * راه‌اندازی اولیه قالب
 * ---------------------------------------------------------------------- */
function noghre_setup() {
	load_theme_textdomain( 'noghre', NOGHRE_DIR . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );
	add_theme_support( 'custom-logo', array(
		'height'      => 120,
		'width'       => 240,
		'flex-height' => true,
		'flex-width'  => true,
	) );
	add_theme_support( 'align-wide' );
	add_theme_support( 'responsive-embeds' );

	set_post_thumbnail_size( 900, 600, true );
	add_image_size( 'noghre-team', 500, 620, true );
	add_image_size( 'noghre-blog-thumb', 160, 160, true );
	add_image_size( 'noghre-license', 400, 500, true );
	add_image_size( 'noghre-single-featured', 960, 540, true );
	add_image_size( 'noghre-ad-banner', 600, 200, true );

	register_nav_menus( array(
		'primary' => 'منوی اصلی هدر',
		'footer'  => 'منوی فوتر',
	) );
}
add_action( 'after_setup_theme', 'noghre_setup' );

/* ---------------------------------------------------------------------- *
 * بارگذاری استایل و اسکریپت (frontend)
 * ---------------------------------------------------------------------- */
function noghre_assets() {
	$version = noghre_get_cache_buster_version();

	$o = function_exists( 'noghre_get_general_options' ) ? noghre_get_general_options() : array();
	$site_font_id = ! empty( $o['site_font_id'] ) ? $o['site_font_id'] : '';
	$site_font_name = '';

	if ( ! empty( $site_font_id ) && function_exists( 'noghre_get_uploaded_fonts' ) ) {
		$fonts = noghre_get_uploaded_fonts();
		if ( isset( $fonts[ $site_font_id ] ) && ! empty( $fonts[ $site_font_id ]['name'] ) ) {
			$has_files = ! empty( $fonts[ $site_font_id ]['files'] ) && is_array( $fonts[ $site_font_id ]['files'] );
			if ( $has_files ) {
				$site_font_name = $fonts[ $site_font_id ]['name'];
			}
		}
	}

	if ( empty( $site_font_name ) ) {
		wp_enqueue_style(
			'noghre-fonts',
			'https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;500;600;700;800;900&display=swap',
			array(),
			null
		);
		$main_css_deps = array( 'noghre-fonts' );
	} else {
		$main_css_deps = array();
	}

	wp_enqueue_style(
		'noghre-main',
		NOGHRE_URI . '/assets/css/main.css',
		$main_css_deps,
		$version
	);

	if ( function_exists( 'noghre_get_font_face_css' ) ) {
		$font_face_css = noghre_get_font_face_css();
		if ( ! empty( $font_face_css ) ) {
			wp_add_inline_style( 'noghre-main', $font_face_css );
		}
	}

	if ( ! empty( $site_font_name ) ) {
		$font_css = 'body, button, input, select, textarea, .noghre-mobile-app, .noghre-admin-login-page { font-family: \'' . esc_html( $site_font_name ) . '\', Tahoma, sans-serif !important; }';
		wp_add_inline_style( 'noghre-main', $font_css );
	}

	if ( function_exists( 'noghre_get_colors' ) ) {
		$c = noghre_get_colors();
		$color_css  = ':root{';
		$color_css .= '--c-bg:'       . esc_html( $c['color_bg'] )       . ';';
		$color_css .= '--c-bg-alt:'   . esc_html( $c['color_bg_alt'] )   . ';';
		$color_css .= '--c-ink:'      . esc_html( $c['color_ink'] )      . ';';
		$color_css .= '--c-ink-soft:' . esc_html( $c['color_ink_soft'] ) . ';';
		$color_css .= '--c-accent:'   . esc_html( $c['color_accent'] )   . ';';
		$color_css .= '--c-accent-2:' . esc_html( $c['color_accent_2'] ) . ';';
		$color_css .= '--c-taupe:'    . esc_html( $c['color_taupe'] )    . ';';
		$color_css .= '--c-blush:'    . esc_html( $c['color_blush'] )    . ';';
		$color_css .= '--c-gold:'     . esc_html( $c['color_gold'] )     . ';';
		$color_css .= '--c-white:'    . esc_html( $c['color_white'] )    . ';';
		$color_css .= '--c-line:'     . noghre_hex_to_rgba( $c['color_line'], 0.10 ) . ';';
		$color_css .= '}';
		wp_add_inline_style( 'noghre-main', $color_css );
	}

	wp_enqueue_script(
		'noghre-main',
		NOGHRE_URI . '/assets/js/main.js',
		array(),
		$version,
		true
	);

	wp_localize_script( 'noghre-main', 'noghreAjax', array(
		'url'          => admin_url( 'admin-ajax.php' ),
		'nonce'        => wp_create_nonce( 'noghre_discount_signup' ),
		'bookingNonce' => wp_create_nonce( 'noghre_booking_submit' ),
	) );

	if ( is_singular() && comments_open() ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'noghre_assets' );

/* ---------------------------------------------------------------------- *
 * 🔑 اعمال فونت سفارشی به پنل مدیریت وردپرس
 *
 * فونت فقط روی متن‌ها اعمال می‌شه — آیکون‌ها (dashicons) و هر عنصر
 * آیکونی دیگه‌ای مستثنا هستن تا سالم بمونن.
 * ---------------------------------------------------------------------- */
function noghre_apply_admin_font() {
	if ( ! function_exists( 'noghre_get_general_options' ) ) return;

	$o = noghre_get_general_options();
	$admin_font_id = ! empty( $o['admin_font_id'] ) ? $o['admin_font_id'] : '';

	if ( empty( $admin_font_id ) ) return;

	$fonts = function_exists( 'noghre_get_uploaded_fonts' ) ? noghre_get_uploaded_fonts() : array();
	if ( ! isset( $fonts[ $admin_font_id ] ) ) return;

	if ( empty( $fonts[ $admin_font_id ]['files'] ) || ! is_array( $fonts[ $admin_font_id ]['files'] ) ) {
		return;
	}

	$admin_font_name = $fonts[ $admin_font_id ]['name'];

	$font_face_css = function_exists( 'noghre_get_single_font_face_css' )
		? noghre_get_single_font_face_css( $admin_font_id )
		: '';

	?>
	<style id="noghre-admin-font">
		<?php echo $font_face_css; ?>

		/* 🔑 فونت روی عناصر متنی و ورودی‌ها اعمال می‌شه */
		body.wp-admin,
		body.wp-admin p,
		body.wp-admin span:not([class*="dashicons"]),
		body.wp-admin div:not([class*="dashicons"]):not([class*="ab-icon"]),
		body.wp-admin h1:not([class*="dashicons"]),
		body.wp-admin h2:not([class*="dashicons"]),
		body.wp-admin h3:not([class*="dashicons"]),
		body.wp-admin h4:not([class*="dashicons"]),
		body.wp-admin h5:not([class*="dashicons"]),
		body.wp-admin h6:not([class*="dashicons"]),
		body.wp-admin a:not([class*="dashicons"]):not([class*="ab-item"]),
		body.wp-admin li:not([class*="dashicons"]),
		body.wp-admin label,
		body.wp-admin td:not([class*="dashicons"]),
		body.wp-admin th:not([class*="dashicons"]),
		body.wp-admin input:not([type="checkbox"]):not([type="radio"]):not([type="submit"]):not([type="button"]),
		body.wp-admin select,
		body.wp-admin textarea,
		body.wp-admin button:not([class*="dashicons"]),
		body.wp-admin .button,
		body.wp-admin .button-primary,
		body.wp-admin .button-secondary {
			font-family: '<?php echo esc_html( $admin_font_name ); ?>', Tahoma, sans-serif !important;
		}

		/* 🔑 استثنا: آیکون‌ها و عناصر گرافیکی حتماً فونت خودشون رو نگه دارن */
		body.wp-admin .dashicons,
		body.wp-admin .dashicons::before,
		body.wp-admin .dashicons-before::before,
		body.wp-admin [class*="dashicons-"]::before,
		body.wp-admin #wpadminbar .ab-icon::before,
		body.wp-admin #wpadminbar .ab-item::before,
		body.wp-admin #wpadminbar .ab-empty-item::before,
		body.wp-admin #wpadminbar .blavatar,
		body.wp-admin #wpadminbar .ab-icon,
		body.wp-admin .wp-menu-image::before,
		body.wp-admin #adminmenu .wp-menu-image::before,
		body.wp-admin .wp-menu-arrow,
		body.wp-admin .wp-menu-arrow div,
		body.wp-admin #adminmenu li.wp-has-submenu.wp-not-current-submenu .wp-menu-arrow::after,
		body.wp-admin .update-plugins .update-count,
		body.wp-admin #wpadminbar .ab-label,
		body.wp-admin #wpadminbar .screen-reader-text,
		body.wp-admin [class*="wp-menu-image"],
		body.wp-admin [class*="wp-menu-arrow"] {
			font-family: dashicons !important;
		}

		/* 🔑 عناصر استاتیک وردپرس که فونت خودشون رو دارن */
		body.wp-admin .wp-core-ui .button,
		body.wp-admin .wp-core-ui .button-primary,
		body.wp-admin .wp-core-ui .button-secondary,
		body.wp-admin .wp-core-ui .button-link,
		body.wp-admin .wp-core-ui .button-link-delete {
			font-family: inherit;
		}
	</style>
	<?php
}
add_action( 'admin_head', 'noghre_apply_admin_font', 100 );

/* ---------------------------------------------------------------------- *
 * شامل کردن فایل‌های داخلی
 * ---------------------------------------------------------------------- */

require_once NOGHRE_DIR . '/inc/options-helper.php';
require_once NOGHRE_DIR . '/inc/jalali-helper.php';
require_once NOGHRE_DIR . '/inc/admin/admin-helpers.php';
require_once NOGHRE_DIR . '/inc/admin/admin-registers.php';
require_once NOGHRE_DIR . '/inc/admin/clear-cache.php';
require_once NOGHRE_DIR . '/inc/admin/tab-colors.php';
require_once NOGHRE_DIR . '/inc/admin/tab-fonts.php';

require_once NOGHRE_DIR . '/inc/afra-sms-bridge.php';
require_once NOGHRE_DIR . '/inc/anti-spam.php';
require_once NOGHRE_DIR . '/inc/zarinpal-integration.php';
require_once NOGHRE_DIR . '/inc/zarinpal-callback.php';
require_once NOGHRE_DIR . '/inc/booking-cpt.php';
require_once NOGHRE_DIR . '/inc/template-tags.php';
require_once NOGHRE_DIR . '/inc/maintenance-mode.php';

require_once NOGHRE_DIR . '/inc/booking/expired-bookings.php';
require_once NOGHRE_DIR . '/inc/booking/access-tokens.php';
require_once NOGHRE_DIR . '/inc/booking/notifications.php';
require_once NOGHRE_DIR . '/inc/booking/booking-form.php';
require_once NOGHRE_DIR . '/inc/booking/booking-check.php';
require_once NOGHRE_DIR . '/inc/booking/booking-actions.php';
require_once NOGHRE_DIR . '/inc/booking/booking-list.php';
require_once NOGHRE_DIR . '/inc/booking/bare-view.php';
require_once NOGHRE_DIR . '/inc/booking/mobile-login.php';
require_once NOGHRE_DIR . '/inc/booking/mobile-signup.php';
require_once NOGHRE_DIR . '/inc/booking/mobile-scanner.php';
require_once NOGHRE_DIR . '/inc/booking/mobile-app.php';

if ( file_exists( NOGHRE_DIR . '/manifest.json.php' ) ) {
	require_once NOGHRE_DIR . '/manifest.json.php';
}

require_once NOGHRE_DIR . '/inc/admin/admin-panel.php';

/* ---------------------------------------------------------------------- *
 * PWA
 * ---------------------------------------------------------------------- */
function noghre_pwa_head() {
	$app = noghre_get_app_options();
	if ( empty( $app['pwa_enabled'] ) ) return;

	$manifest_url = home_url( '/?noghre_pwa_manifest=1' );
	$icon_url     = noghre_get_pwa_icon_url();
	?>
	<link rel="manifest" href="<?php echo esc_url( $manifest_url ); ?>">
	<meta name="theme-color" content="<?php echo esc_attr( $app['pwa_theme_color'] ); ?>">
	<link rel="apple-touch-icon" href="<?php echo esc_url( $icon_url ); ?>">
	<meta name="apple-mobile-web-app-capable" content="yes">
	<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
	<meta name="apple-mobile-web-app-title" content="<?php echo esc_attr( $app['pwa_short_name'] ); ?>">
	<meta name="mobile-web-app-capable" content="yes">
	<?php
}
add_action( 'wp_head', 'noghre_pwa_head', 5 );

function noghre_pwa_footer() {
	$app = noghre_get_app_options();
	if ( empty( $app['pwa_enabled'] ) ) return;

	wp_enqueue_script(
		'noghre-pwa',
		NOGHRE_URI . '/assets/js/noghre-pwa.js',
		array(),
		noghre_get_cache_buster_version(),
		true
	);

	wp_localize_script( 'noghre-pwa', 'noghrePWA', array(
		'swUrl' => NOGHRE_URI . '/service-worker.js',
		'scope' => home_url( '/mobile/' ),
	) );
}
add_action( 'wp_enqueue_scripts', 'noghre_pwa_footer' );

function noghre_get_pwa_icon_url() {
	$app = noghre_get_app_options();

	if ( ! empty( $app['pwa_icon_id'] ) ) {
		$url = wp_get_attachment_image_url( (int) $app['pwa_icon_id'], 'full' );
		if ( $url ) return $url;
	}
	if ( ! empty( $app['app_logo_id'] ) ) {
		$url = wp_get_attachment_image_url( (int) $app['app_logo_id'], 'full' );
		if ( $url ) return $url;
	}
	$site_logo_id = (int) noghre_get_option( 'site_logo_id', 0 );
	if ( $site_logo_id ) {
		$url = wp_get_attachment_image_url( $site_logo_id, 'full' );
		if ( $url ) return $url;
	}
	return NOGHRE_URI . '/assets/img/placeholder-hero.svg';
}

/* ---------------------------------------------------------------------- *
 * ویجت فوتر
 * ---------------------------------------------------------------------- */
function noghre_widgets_init() {
	register_sidebar( array(
		'name'          => 'ستون فوتر',
		'id'            => 'footer-1',
		'before_widget' => '<div class="footer-widget">',
		'after_widget'  => '</div>',
		'before_title'  => '<h4 class="footer-widget-title">',
		'after_title'   => '</h4>',
	) );
}
add_action( 'widgets_init', 'noghre_widgets_init' );

function noghre_excerpt_length( $length ) {
	return 20;
}
add_filter( 'excerpt_length', 'noghre_excerpt_length' );

function noghre_excerpt_more( $more ) {
	return '…';
}
add_filter( 'excerpt_more', 'noghre_excerpt_more' );

function noghre_fallback_menu() {
	echo '<ul class="primary-menu"><li><a href="' . esc_url( home_url( '/' ) ) . '">خانه</a></li></ul>';
}

function noghre_body_classes( $classes ) {
	if ( (int) noghre_get_option( 'decorative_bg_enabled', 1 ) ) {
		$classes[] = 'has-decorative-bg';
	}
	return $classes;
}
add_filter( 'body_class', 'noghre_body_classes' );

/* ==========================================================================
 * غیرفعال‌سازی کامل Twemoji
 * ========================================================================== */

add_action( 'init', function() {
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'admin_print_styles', 'print_emoji_styles' );
	remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
	remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
	remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );

	add_filter( 'tiny_mce_plugins', function( $plugins ) {
		if ( is_array( $plugins ) ) {
			return array_diff( $plugins, array( 'wpemoji' ) );
		}
		return array();
	} );
}, 1 );

add_action( 'wp_enqueue_scripts', function() {
	wp_deregister_script( 'wp-emoji' );
	wp_deregister_script( 'wp-emoji-loader' );
	wp_dequeue_script( 'wp-emoji' );
	wp_dequeue_script( 'wp-emoji-loader' );
}, 9999 );

add_action( 'admin_enqueue_scripts', function() {
	wp_deregister_script( 'wp-emoji' );
	wp_deregister_script( 'wp-emoji-loader' );
	wp_dequeue_script( 'wp-emoji' );
	wp_dequeue_script( 'wp-emoji-loader' );
}, 9999 );

add_filter( 'print_emoji_detection_script', '__return_empty_string' );

add_filter( 'script_loader_tag', function( $tag, $handle ) {
	if ( 'wp-emoji' === $handle || 'wp-emoji-loader' === $handle ) {
		return '';
	}
	return $tag;
}, 10, 2 );

add_filter( 'script_loader_src', function( $src, $handle ) {
	if ( 'wp-emoji' === $handle || 'wp-emoji-loader' === $handle ) {
		return false;
	}
	return $src;
}, 10, 2 );

add_filter( 'emoji_svg_url', '__return_false' );

add_action( 'template_redirect', function() {
	if ( is_admin() ) return;
	ob_start( function( $buffer ) {
		$buffer = preg_replace(
			'#<script[^>]*id=["\']wp-emoji[^>]*>.*?</script>#is',
			'',
			$buffer
		);
		$buffer = preg_replace(
			'#<script[^>]*wp-emoji-release[^>]*>.*?</script>#is',
			'',
			$buffer
		);
		$buffer = preg_replace(
			'#https://cdn\.jsdelivr\.net/gh/jdecked/twemoji[^\'"]*#i',
			'',
			$buffer
		);
		$buffer = preg_replace(
			'#wp-emoji-loader\.min\.js[^\'"]*#i',
			'',
			$buffer
		);
		return $buffer;
	} );
}, 1 );