<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/* ============================================================
 * عملیات مدیریت رزرو (تایید / ویرایش / باطل) — via admin-post
 * ============================================================ */

function noghre_handle_booking_admin_action( $new_status ) {
	$bypass = isset( $_POST['m'] ) && noghre_is_admin_access( wp_unslash( $_POST['m'] ) );
	if ( ! current_user_can( 'manage_options' ) && ! $bypass ) wp_die( 'دسترسی غیرمجاز' );
	check_admin_referer( 'noghre_booking_admin_action' );

	$booking_id = absint( $_POST['booking_id'] ?? 0 );
	$return_url = esc_url_raw( $_POST['return_url'] ?? home_url( '/' ) );

	if ( $booking_id && get_post_type( $booking_id ) === 'noghre_booking' ) {
		$current_status = get_post_meta( $booking_id, '_status', true );

		if ( in_array( $current_status, array( 'used', 'unused' ), true ) ) {
			wp_redirect( $return_url );
			exit;
		}

		update_post_meta( $booking_id, '_status', $new_status );

		if ( $new_status === 'edited' ) {
			$new_date_jalali = sanitize_text_field( $_POST['new_date_jalali'] ?? '' );
			$new_time        = sanitize_text_field( $_POST['new_time'] ?? '' );
			$new_date_gregorian = noghre_jalali_string_to_gregorian_string( $new_date_jalali );

			if ( $new_date_gregorian && $new_time ) {
				$ts = strtotime( $new_date_gregorian . ' ' . $new_time );
				$new_display = noghre_jalali_date( $ts, 'l Y/m/d' );

				update_post_meta( $booking_id, '_visit_day_gregorian', $new_date_gregorian );
				update_post_meta( $booking_id, '_visit_day_display', $new_display );
				update_post_meta( $booking_id, '_preferred_time', $new_time );
				update_post_meta( $booking_id, '_admin_note', '' );
			}
		}

		noghre_send_status_notification_sms( $booking_id, $new_status );
	}

	if ( ! $return_url || strpos( $return_url, 'http' ) !== 0 ) {
		$return_url = home_url( '/mobile/?section=check&code=' . urlencode( get_post_meta( $booking_id, '_tracking_code', true ) ) );
	}

	wp_redirect( $return_url );
	exit;
}
add_action( 'admin_post_noghre_booking_confirm', function() { noghre_handle_booking_admin_action( 'confirmed' ); } );
add_action( 'admin_post_noghre_booking_edit',    function() { noghre_handle_booking_admin_action( 'edited' ); } );
add_action( 'admin_post_noghre_booking_void',    function() { noghre_handle_booking_admin_action( 'used' ); } );
add_action( 'admin_post_nopriv_noghre_booking_confirm', function() { noghre_handle_booking_admin_action( 'confirmed' ); } );
add_action( 'admin_post_nopriv_noghre_booking_edit',    function() { noghre_handle_booking_admin_action( 'edited' ); } );
add_action( 'admin_post_nopriv_noghre_booking_void',    function() { noghre_handle_booking_admin_action( 'used' ); } );

/* ============================================================
 * ارسال پیامک دستی برای رزروهای استفاده نشده — via admin-post
 * ============================================================ */
function noghre_handle_send_unused_sms() {
	$bypass = isset( $_POST['m'] ) && noghre_is_admin_access( wp_unslash( $_POST['m'] ) );
	if ( ! current_user_can( 'manage_options' ) && ! $bypass ) wp_die( 'دسترسی غیرمجاز' );
	check_admin_referer( 'noghre_booking_admin_action' );

	$booking_id = absint( $_POST['booking_id'] ?? 0 );
	$return_url = esc_url_raw( $_POST['return_url'] ?? home_url( '/' ) );

	$final_status = 'failed';

	if ( $booking_id && get_post_type( $booking_id ) === 'noghre_booking' ) {
		if ( ! noghre_afra_is_ready() ) {
			$final_status = 'disabled';
		} else {
			$sms    = noghre_get_sms_options();
			$mobile = get_post_meta( $booking_id, '_customer_mobile', true );
			$name   = get_post_meta( $booking_id, '_customer_name', true );

			if ( $mobile ) {
				$pattern = '';
				$params  = array();

				if ( ! empty( $sms['sms_unused_enabled'] ) && ! empty( $sms['sms_pattern_booking_unused'] ) ) {
					$pattern = $sms['sms_pattern_booking_unused'];
					if ( ! empty( $sms['sms_pattern_unused_var_name'] ) ) $params[ $sms['sms_pattern_unused_var_name'] ] = $name;
					if ( ! empty( $sms['sms_pattern_unused_discount_var'] ) && ! empty( $sms['sms_pattern_unused_discount_val'] ) ) {
						$params[ $sms['sms_pattern_unused_discount_var'] ] = $sms['sms_pattern_unused_discount_val'];
					}
				} elseif ( ! empty( $sms['sms_void_enabled'] ) && ! empty( $sms['sms_pattern_booking_void'] ) ) {
					$pattern = $sms['sms_pattern_booking_void'];
					if ( ! empty( $sms['sms_pattern_void_var_name'] ) ) $params[ $sms['sms_pattern_void_var_name'] ] = $name;
				}

				if ( $pattern ) {
					$result = noghre_sms_send_pattern( $mobile, $pattern, $params );
					$sent = ! is_wp_error( $result );
					update_post_meta( $booking_id, '_sms_sent_unused', $sent ? '1' : '0' );
					$final_status = $sent ? 'sent' : 'failed';
				} else {
					$final_status = 'disabled';
				}
			}
		}
	}

	if ( ! $return_url || strpos( $return_url, 'http' ) !== 0 ) {
		$return_url = home_url( '/mobile/?section=check&code=' . urlencode( get_post_meta( $booking_id, '_tracking_code', true ) ) );
	}

	$return_url = add_query_arg( 'sms_unused_status', $final_status, $return_url );
	wp_redirect( $return_url );
	exit;
}
add_action( 'admin_post_noghre_booking_send_unused_sms', 'noghre_handle_send_unused_sms' );
add_action( 'admin_post_nopriv_noghre_booking_send_unused_sms', 'noghre_handle_send_unused_sms' );

/* ============================================================
 * 🔑 AJAX handlers جدید برای فرم‌های اپ موبایل
 * ============================================================ */

/**
 * بررسی مجوز و return کردن اطلاعات پایه
 * @return array|WP_Error
 */
function noghre_mobile_actions_check_auth() {
	// بررسی توکن یا m
	if ( ! noghre_is_admin_request() ) {
		return new WP_Error( 'unauthorized', 'دسترسی غیرمجاز.', array( 'code' => 'invalid_admin_key' ) );
	}

	// بررسی nonce
	$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
	if ( ! wp_verify_nonce( $nonce, 'noghre_booking_admin_action' ) ) {
		return new WP_Error( 'invalid_nonce', 'nonce نامعتبر است.', array( 'code' => 'invalid_nonce' ) );
	}

	return true;
}

/**
 * AJAX: تایید رزرو
 */
function noghre_ajax_booking_confirm() {
	$auth = noghre_mobile_actions_check_auth();
	if ( is_wp_error( $auth ) ) {
		wp_send_json_error( array(
			'message' => $auth->get_error_message(),
			'code'    => $auth->get_error_data()['code'] ?? 'error',
		) );
	}

	$booking_id = absint( $_POST['booking_id'] ?? 0 );
	if ( ! $booking_id || get_post_type( $booking_id ) !== 'noghre_booking' ) {
		wp_send_json_error( array( 'message' => 'رزرو یافت نشد.' ) );
	}

	$current_status = get_post_meta( $booking_id, '_status', true );
	if ( in_array( $current_status, array( 'used', 'unused' ), true ) ) {
		wp_send_json_error( array( 'message' => 'این رزرو قابل تغییر نیست.' ) );
	}

	update_post_meta( $booking_id, '_status', 'confirmed' );
	noghre_send_status_notification_sms( $booking_id, 'confirmed' );

	wp_send_json_success( array(
		'message' => 'رزرو با موفقیت تایید شد.',
		'status'  => 'confirmed',
	) );
}
add_action( 'wp_ajax_noghre_booking_confirm', 'noghre_ajax_booking_confirm' );
add_action( 'wp_ajax_nopriv_noghre_booking_confirm', 'noghre_ajax_booking_confirm' );

/**
 * AJAX: ویرایش رزرو
 */
function noghre_ajax_booking_edit() {
	$auth = noghre_mobile_actions_check_auth();
	if ( is_wp_error( $auth ) ) {
		wp_send_json_error( array(
			'message' => $auth->get_error_message(),
			'code'    => $auth->get_error_data()['code'] ?? 'error',
		) );
	}

	$booking_id = absint( $_POST['booking_id'] ?? 0 );
	if ( ! $booking_id || get_post_type( $booking_id ) !== 'noghre_booking' ) {
		wp_send_json_error( array( 'message' => 'رزرو یافت نشد.' ) );
	}

	$current_status = get_post_meta( $booking_id, '_status', true );
	if ( in_array( $current_status, array( 'used', 'unused' ), true ) ) {
		wp_send_json_error( array( 'message' => 'این رزرو قابل ویرایش نیست.' ) );
	}

	$new_date_jalali = sanitize_text_field( $_POST['new_date_jalali'] ?? '' );
	$new_time        = sanitize_text_field( $_POST['new_time'] ?? '' );
	$new_date_gregorian = noghre_jalali_string_to_gregorian_string( $new_date_jalali );

	if ( ! $new_date_gregorian || ! $new_time ) {
		wp_send_json_error( array( 'message' => 'تاریخ و ساعت جدید را کامل وارد کنید.' ) );
	}

	$ts = strtotime( $new_date_gregorian . ' ' . $new_time );
	$new_display = noghre_jalali_date( $ts, 'l Y/m/d' );

	update_post_meta( $booking_id, '_visit_day_gregorian', $new_date_gregorian );
	update_post_meta( $booking_id, '_visit_day_display', $new_display );
	update_post_meta( $booking_id, '_preferred_time', $new_time );
	update_post_meta( $booking_id, '_admin_note', '' );
	update_post_meta( $booking_id, '_status', 'edited' );

	noghre_send_status_notification_sms( $booking_id, 'edited' );

	wp_send_json_success( array(
		'message' => 'رزرو با موفقیت ویرایش شد.',
		'status'  => 'edited',
	) );
}
add_action( 'wp_ajax_noghre_booking_edit', 'noghre_ajax_booking_edit' );
add_action( 'wp_ajax_nopriv_noghre_booking_edit', 'noghre_ajax_booking_edit' );

/**
 * AJAX: باطل شدن (استفاده‌شده)
 */
function noghre_ajax_booking_void() {
	$auth = noghre_mobile_actions_check_auth();
	if ( is_wp_error( $auth ) ) {
		wp_send_json_error( array(
			'message' => $auth->get_error_message(),
			'code'    => $auth->get_error_data()['code'] ?? 'error',
		) );
	}

	$booking_id = absint( $_POST['booking_id'] ?? 0 );
	if ( ! $booking_id || get_post_type( $booking_id ) !== 'noghre_booking' ) {
		wp_send_json_error( array( 'message' => 'رزرو یافت نشد.' ) );
	}

	$current_status = get_post_meta( $booking_id, '_status', true );
	if ( in_array( $current_status, array( 'used', 'unused' ), true ) ) {
		wp_send_json_error( array( 'message' => 'این رزرو قبلاً بسته شده است.' ) );
	}

	update_post_meta( $booking_id, '_status', 'used' );
	noghre_send_status_notification_sms( $booking_id, 'used' );

	wp_send_json_success( array(
		'message' => 'رزرو باطل شد.',
		'status'  => 'used',
	) );
}
add_action( 'wp_ajax_noghre_booking_void', 'noghre_ajax_booking_void' );
add_action( 'wp_ajax_nopriv_noghre_booking_void', 'noghre_ajax_booking_void' );

/**
 * AJAX: ارسال پیامک برای رزرو استفاده‌نشده
 */
function noghre_ajax_booking_send_unused_sms() {
	$auth = noghre_mobile_actions_check_auth();
	if ( is_wp_error( $auth ) ) {
		wp_send_json_error( array(
			'message' => $auth->get_error_message(),
			'code'    => $auth->get_error_data()['code'] ?? 'error',
		) );
	}

	$booking_id = absint( $_POST['booking_id'] ?? 0 );
	if ( ! $booking_id || get_post_type( $booking_id ) !== 'noghre_booking' ) {
		wp_send_json_error( array( 'message' => 'رزرو یافت نشد.' ) );
	}

	if ( ! noghre_afra_is_ready() ) {
		wp_send_json_error( array( 'message' => 'افزونه افرا اس ام اس آماده نیست.' ) );
	}

	$sms    = noghre_get_sms_options();
	$mobile = get_post_meta( $booking_id, '_customer_mobile', true );
	$name   = get_post_meta( $booking_id, '_customer_name', true );

	if ( ! $mobile ) {
		wp_send_json_error( array( 'message' => 'شماره موبایل مشتری ثبت نشده است.' ) );
	}

	$pattern = '';
	$params  = array();

	if ( ! empty( $sms['sms_unused_enabled'] ) && ! empty( $sms['sms_pattern_booking_unused'] ) ) {
		$pattern = $sms['sms_pattern_booking_unused'];
		if ( ! empty( $sms['sms_pattern_unused_var_name'] ) ) $params[ $sms['sms_pattern_unused_var_name'] ] = $name;
		if ( ! empty( $sms['sms_pattern_unused_discount_var'] ) && ! empty( $sms['sms_pattern_unused_discount_val'] ) ) {
			$params[ $sms['sms_pattern_unused_discount_var'] ] = $sms['sms_pattern_unused_discount_val'];
		}
	} elseif ( ! empty( $sms['sms_void_enabled'] ) && ! empty( $sms['sms_pattern_booking_void'] ) ) {
		$pattern = $sms['sms_pattern_booking_void'];
		if ( ! empty( $sms['sms_pattern_void_var_name'] ) ) $params[ $sms['sms_pattern_void_var_name'] ] = $name;
	}

	if ( ! $pattern ) {
		wp_send_json_error( array( 'message' => 'ارسال پیامک برای رزروهای استفاده نشده خاموش است یا پترن آن تنظیم نشده.' ) );
	}

	$result = noghre_sms_send_pattern( $mobile, $pattern, $params );
	$sent = ! is_wp_error( $result );
	update_post_meta( $booking_id, '_sms_sent_unused', $sent ? '1' : '0' );

	if ( ! $sent ) {
		wp_send_json_error( array( 'message' => 'ارسال پیامک ناموفق بود: ' . $result->get_error_message() ) );
	}

	wp_send_json_success( array( 'message' => 'پیامک با موفقیت برای مشتری ارسال شد.' ) );
}
add_action( 'wp_ajax_noghre_booking_send_unused_sms', 'noghre_ajax_booking_send_unused_sms' );
add_action( 'wp_ajax_nopriv_noghre_booking_send_unused_sms', 'noghre_ajax_booking_send_unused_sms' );