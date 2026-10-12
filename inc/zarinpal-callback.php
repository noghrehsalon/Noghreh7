<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * وقتی کاربر از درگاه زرین‌پال بازمی‌گردد، این تابع تراکنش را تایید می‌کند
 * و رزرو مرتبط را نهایی می‌کند.
 */
function noghre_handle_zarinpal_callback() {
	if ( empty( $_GET['noghre_zarinpal_callback'] ) ) return;

	$booking_id = absint( $_GET['booking_id'] ?? 0 );
	$authority  = isset( $_GET['Authority'] ) ? sanitize_text_field( wp_unslash( $_GET['Authority'] ) ) : '';
	$status     = isset( $_GET['Status'] ) ? sanitize_text_field( wp_unslash( $_GET['Status'] ) ) : '';

	$check_page_url   = noghre_get_check_page_url();
	$booking_page_url = noghre_get_booking_page_url();
	if ( ! $check_page_url )   $check_page_url   = home_url( '/' );
	if ( ! $booking_page_url ) $booking_page_url = home_url( '/' );

	if ( ! $booking_id || get_post_type( $booking_id ) !== 'noghre_booking' ) {
		wp_redirect( add_query_arg( 'noghre_booking_msg', 'payment_failed', $booking_page_url ) );
		exit;
	}

	if ( $status !== 'OK' ) {
		update_post_meta( $booking_id, '_payment_status', 'failed' );
		wp_redirect( add_query_arg( 'noghre_booking_msg', 'payment_failed', $booking_page_url ) );
		exit;
	}

	$amount = (int) get_post_meta( $booking_id, '_payment_amount', true );
	$result = noghre_zarinpal_verify( $amount, $authority );

	if ( is_wp_error( $result ) ) {
		update_post_meta( $booking_id, '_payment_status', 'failed' );
		noghre_log_error( 'تایید پرداخت زرین‌پال', $result->get_error_message() );
		wp_redirect( add_query_arg( 'noghre_booking_msg', 'payment_failed', $booking_page_url ) );
		exit;
	}

	update_post_meta( $booking_id, '_payment_status', 'paid' );
	update_post_meta( $booking_id, '_zarinpal_ref_id', $result['ref_id'] );

	$discount_code = get_post_meta( $booking_id, '_discount_code', true );
	if ( $discount_code ) {
		noghre_consume_discount_code( $discount_code, get_post_meta( $booking_id, '_customer_mobile', true ) );
	}

	// ========== همگام‌سازی دفترچه‌ها (بعد از تایید پرداخت) ==========
	$customer_name   = get_post_meta( $booking_id, '_customer_name', true );
	$customer_mobile = get_post_meta( $booking_id, '_customer_mobile', true );
	if ( $customer_mobile ) {
		noghre_sync_phonebooks_after_booking( $customer_mobile, $customer_name );
	}

	noghre_send_booking_notifications( $booking_id );

	$tracking_code = get_post_meta( $booking_id, '_tracking_code', true );
	wp_redirect( add_query_arg( 'code', $tracking_code, $check_page_url ) );
	exit;
}
add_action( 'template_redirect', 'noghre_handle_zarinpal_callback' );