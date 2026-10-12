<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/* ============================================================
 * پیامک‌های رزرو
 * ============================================================ */

/**
 * ارسال پیامک‌های اولیه رزرو
 *  - پیامک به مدیریت شعبه
 *  - پیامک به مشتری
 */
function noghre_send_booking_notifications( $post_id ) {
	if ( ! noghre_afra_is_ready() ) {
		noghre_log_error( 'اعلان رزرو', 'افزونه افرا اس ام اس آماده نیست.' );
		return;
	}

	$sms = noghre_get_sms_options();
	$tracking_code = get_post_meta( $post_id, '_tracking_code', true );
	$name   = get_post_meta( $post_id, '_customer_name', true );
	$mobile = get_post_meta( $post_id, '_customer_mobile', true );
	$branch = get_post_meta( $post_id, '_branch', true );
	$branch_mobile = get_post_meta( $post_id, '_branch_manager_mobile', true );

	if ( ! empty( $sms['sms_branch_enabled'] ) && $branch_mobile && ! empty( $sms['sms_pattern_booking_branch'] ) ) {
		$params = array();
		if ( ! empty( $sms['sms_pattern_branch_var_shobe'] ) ) $params[ $sms['sms_pattern_branch_var_shobe'] ] = $branch;
		if ( ! empty( $sms['sms_pattern_branch_var_peygiri'] ) ) $params[ $sms['sms_pattern_branch_var_peygiri'] ] = $tracking_code;
		if ( ! empty( $sms['sms_pattern_branch_var_modir'] ) ) $params[ $sms['sms_pattern_branch_var_modir'] ] = noghre_get_admin_access_key();

		$result = noghre_sms_send_pattern( $branch_mobile, $sms['sms_pattern_booking_branch'], $params );
		if ( is_wp_error( $result ) ) noghre_log_error( 'پیامک رزرو به مدیریت شعبه', $result->get_error_message() );
	}

	if ( ! empty( $sms['sms_customer_enabled'] ) && $mobile && ! empty( $sms['sms_pattern_booking_customer'] ) ) {
		$params = array();
		if ( ! empty( $sms['sms_pattern_customer_var_name'] ) ) $params[ $sms['sms_pattern_customer_var_name'] ] = $name;
		if ( ! empty( $sms['sms_pattern_customer_var_peygiri'] ) ) $params[ $sms['sms_pattern_customer_var_peygiri'] ] = $tracking_code;

		$result = noghre_sms_send_pattern( $mobile, $sms['sms_pattern_booking_customer'], $params );
		if ( is_wp_error( $result ) ) noghre_log_error( 'پیامک رزرو به مشتری', $result->get_error_message() );
	}
}

/**
 * ارسال پیامک هنگام تغییر وضعیت رزرو
 *  - تایید (confirmed)
 *  - ویرایش (edited)
 *  - باطل/استفاده‌شده (used)
 */
function noghre_send_status_notification_sms( $post_id, $new_status ) {
	if ( ! noghre_afra_is_ready() ) return;

	$sms    = noghre_get_sms_options();
	$mobile = get_post_meta( $post_id, '_customer_mobile', true );
	$name   = get_post_meta( $post_id, '_customer_name', true );
	$tracking_code = get_post_meta( $post_id, '_tracking_code', true );

	if ( ! $mobile ) return;

	$sent = false;

	if ( $new_status === 'confirmed' && ! empty( $sms['sms_confirm_enabled'] ) && ! empty( $sms['sms_pattern_booking_confirm'] ) ) {
		$params = array();
		if ( ! empty( $sms['sms_pattern_confirm_var_name'] ) ) $params[ $sms['sms_pattern_confirm_var_name'] ] = $name;
		if ( ! empty( $sms['sms_pattern_confirm_var_peygiri'] ) ) $params[ $sms['sms_pattern_confirm_var_peygiri'] ] = $tracking_code;

		$result = noghre_sms_send_pattern( $mobile, $sms['sms_pattern_booking_confirm'], $params );
		if ( ! is_wp_error( $result ) ) $sent = true;
	}

	if ( $new_status === 'edited' && ! empty( $sms['sms_edit_enabled'] ) && ! empty( $sms['sms_pattern_booking_edit'] ) ) {
		$params = array();
		if ( ! empty( $sms['sms_pattern_edit_var_name'] ) ) $params[ $sms['sms_pattern_edit_var_name'] ] = $name;
		if ( ! empty( $sms['sms_pattern_edit_var_peygiri'] ) ) $params[ $sms['sms_pattern_edit_var_peygiri'] ] = $tracking_code;

		$result = noghre_sms_send_pattern( $mobile, $sms['sms_pattern_booking_edit'], $params );
		if ( ! is_wp_error( $result ) ) $sent = true;
	}

	if ( $new_status === 'used' && ! empty( $sms['sms_void_enabled'] ) && ! empty( $sms['sms_pattern_booking_void'] ) ) {
		$params = array();
		if ( ! empty( $sms['sms_pattern_void_var_name'] ) ) $params[ $sms['sms_pattern_void_var_name'] ] = $name;

		$result = noghre_sms_send_pattern( $mobile, $sms['sms_pattern_booking_void'], $params );
		if ( ! is_wp_error( $result ) ) $sent = true;
	}

	update_post_meta( $post_id, '_sms_sent_' . $new_status, $sent ? '1' : '0' );
}