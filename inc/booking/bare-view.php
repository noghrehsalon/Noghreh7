<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/* ============================================================
 * نمایش بدون قالب (bare view) — برای صفحه استعلام موبایل
 * ============================================================ */

/**
 * جلوگیری از تداخل query var «m» وردپرس با پارامتر m ما
 */
function noghre_prevent_m_query_var_collision( $query_vars ) {
	if ( isset( $_GET['m'] ) && isset( $query_vars['m'] ) ) {
		unset( $query_vars['m'] );
	}
	return $query_vars;
}
add_filter( 'request', 'noghre_prevent_m_query_var_collision' );

/**
 * بررسی اینکه درخواست فعلی، «نمای بدون قالب» هست یا نه
 * (صفحه استعلام + m معتبر + code موجود)
 */
function noghre_is_bare_admin_view() {
	if ( is_admin() ) return false;
	if ( empty( $_GET['m'] ) || ! noghre_is_admin_access( wp_unslash( $_GET['m'] ) ) ) return false;
	if ( empty( $_GET['code'] ) ) return false;

	$check_page_id = (int) noghre_get_booking_option( 'check_page_id', 0 );
	return ( $check_page_id && is_page( $check_page_id ) );
}

/**
 * اضافه کردن کلاس body برای نمای بدون قالب
 */
function noghre_bare_view_body_class( $classes ) {
	if ( noghre_is_bare_admin_view() ) {
		$classes[] = 'noghre-bare-admin-view';
	}
	return $classes;
}
add_filter( 'body_class', 'noghre_bare_view_body_class' );

/**
 * تغییر عنوان صفحه در نمای بدون قالب
 */
function noghre_bare_view_document_title( $title ) {
	if ( noghre_is_bare_admin_view() ) {
		return get_bloginfo( 'name' );
	}
	return $title;
}
add_filter( 'pre_get_document_title', 'noghre_bare_view_document_title' );

/**
 * 🔑 اجبار به استفاده از template-blank.php در دو حالت:
 *   ۱. برگه اپ موبایل (app_page_id) — همیشه بدون هدر/فوتر
 *   ۲. صفحه استعلام موبایل با mob=1 و m معتبر
 */
function noghre_force_blank_template( $template ) {
	if ( is_admin() ) return $template;

	/* 🔑 حالت ۱: برگه اپ موبایل */
	$app = noghre_get_app_options();
	$app_page_id = (int) ( $app['app_page_id'] ?? 0 );

	if ( $app_page_id > 0 && is_page( $app_page_id ) ) {
		$blank_template = NOGHRE_DIR . '/template-blank.php';
		if ( file_exists( $blank_template ) ) {
			return $blank_template;
		}
	}

	/* 🔑 حالت ۲: صفحه استعلام موبایل */
	if ( ! is_page() && ! is_singular() ) return $template;

	$check_page_id = (int) noghre_get_booking_option( 'check_page_id', 0 );
	if ( ! $check_page_id || ! is_page( $check_page_id ) ) return $template;

	$from_mobile = ( isset( $_GET['mob'] ) && $_GET['mob'] === '1' );
	if ( ! $from_mobile ) return $template;

	if ( ! noghre_is_admin_access( wp_unslash( $_GET['m'] ?? '' ) ) ) return $template;

	$blank_template = NOGHRE_DIR . '/template-blank.php';
	if ( file_exists( $blank_template ) ) {
		return $blank_template;
	}

	return $template;
}
add_filter( 'template_include', 'noghre_force_blank_template', 99 );

/**
 * 🔑 ریدایرکت به صفحه اصلی اگه برگه اپ موبایل تنظیم نشده
 * یا اگه کاربر به /mobile/ (بدون تنظیم) دسترسی پیدا کرد
 */
function noghre_redirect_if_app_not_configured() {
	if ( is_admin() ) return;

	/* فقط برای برگه‌هایی که شورت‌کد اپ دارن */
	if ( ! is_page() && ! is_singular() ) return;

	$post = get_post();
	if ( ! $post ) return;

	$has_app_shortcode = (
		has_shortcode( $post->post_content, 'ariatin-noghreh-mobile' )
	);

	if ( ! $has_app_shortcode ) return;

	/* 🔑 اگه app_page_id تنظیم نشده یا با برگه فعلی یکی نیست → ریدایرکت */
	$app = noghre_get_app_options();
	$app_page_id = (int) ( $app['app_page_id'] ?? 0 );

	if ( $app_page_id <= 0 ) {
		wp_safe_redirect( home_url( '/' ) );
		exit;
	}

	if ( (int) $post->ID !== $app_page_id ) {
		wp_safe_redirect( home_url( '/' ) );
		exit;
	}
}
add_action( 'template_redirect', 'noghre_redirect_if_app_not_configured', 1 );