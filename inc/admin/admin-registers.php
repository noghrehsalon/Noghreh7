<?php
/**
 * ثبت همه‌ی register_setting ها در admin_init
 * (این فایل باید همیشه لود بشه، نه فقط وقتی تب فعاله)
 *
 * @package Noghre
 */

if ( ! defined( 'ABSPATH' ) ) exit;

function noghre_register_all_settings() {

	/* ============ تنظیمات عمومی ============ */
	register_setting(
		'noghre_general_group',
		'noghre_general_options',
		'noghre_sanitize_general_options'
	);

	/* ============ رنگ‌بندی ============ */
	register_setting(
		'noghre_colors_group',
		'noghre_colors',
		'noghre_sanitize_colors'
	);

	/* ============ اپلیکیشن ============ */
	register_setting(
		'noghre_app_group',
		'noghre_app_options',
		'noghre_sanitize_app_options'
	);

	/* ============ پیامک ============ */
	register_setting(
		'noghre_sms_group',
		'noghre_sms_options',
		'noghre_sanitize_sms_options'
	);

	/* ============ درگاه پرداخت ============ */
	register_setting(
		'noghre_zarinpal_group',
		'noghre_zarinpal_options',
		'noghre_sanitize_zarinpal_options'
	);

	/* ============ رزرو وقت ============ */
	register_setting(
		'noghre_booking_group',
		'noghre_booking_options',
		'noghre_sanitize_booking_options'
	);
}
add_action( 'admin_init', 'noghre_register_all_settings', 5 );

/* ==========================================================================
 * نمایش پیام موفقیت بعد از ذخیره تنظیمات فرم‌های options.php
 * ========================================================================== */
function noghre_settings_saved_notice() {
	if ( ! isset( $_GET['page'] ) || $_GET['page'] !== 'noghre-panel' ) return;
	if ( ! isset( $_GET['settings-updated'] ) || $_GET['settings-updated'] !== 'true' ) return;

	// پاک کردن پیام‌های پیش‌فرض وردپرس
	add_filter( 'wp_settings_errors', function( $errors ) {
		return array();
	} );

	printf(
		'<div class="noghre-message noghre-message--success" data-autoclose="5000" style="margin: 16px 0 0;">
			<span class="noghre-message__icon dashicons dashicons-yes-alt"></span>
			<span class="noghre-message__text">تنظیمات با موفقیت ذخیره شد.</span>
			<button type="button" class="noghre-message__close" aria-label="بستن">×</button>
		</div>'
	);
}
add_action( 'noghre_after_panel_header', 'noghre_settings_saved_notice' );