<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/* نکته: توابع noghre_default_zarinpal_options و noghre_sanitize_zarinpal_options به inc/options-helper.php منتقل شدند */

function noghre_register_zarinpal_settings() {
	register_setting( 'noghre_zarinpal_group', 'noghre_zarinpal_options', 'noghre_sanitize_zarinpal_options' );
}
add_action( 'admin_init', 'noghre_register_zarinpal_settings' );

function noghre_zarinpal_base_url() {
	$o = noghre_get_zarinpal_options();
	return $o['zarinpal_sandbox'] ? 'https://sandbox.zarinpal.com' : 'https://payment.zarinpal.com';
}

/**
 * ایجاد یک درخواست پرداخت جدید نزد زرین‌پال.
 *
 * @param int    $amount_toman مبلغ به تومان
 * @param string $description  توضیحات تراکنش
 * @param string $callback_url آدرس بازگشت
 * @param string $mobile       شماره موبایل خریدار (اختیاری)
 * @return array|WP_Error ['authority'=>..., 'redirect_url'=>...] یا خطا
 */
function noghre_zarinpal_request( $amount_toman, $description, $callback_url, $mobile = '' ) {
	$o = noghre_get_zarinpal_options();
	if ( ! $o['zarinpal_merchant_id'] ) {
		return new WP_Error( 'noghre_zarinpal_not_configured', 'درگاه پرداخت تنظیم نشده است.' );
	}

	$body = array(
		'merchant_id'  => $o['zarinpal_merchant_id'],
		'amount'       => (int) $amount_toman,
		'currency'     => 'IRT',
		'description'  => $description,
		'callback_url' => $callback_url,
	);
	if ( $mobile ) {
		$body['metadata'] = array( 'mobile' => $mobile );
	}

	$response = wp_remote_post( noghre_zarinpal_base_url() . '/pg/v4/payment/request.json', array(
		'timeout' => 15,
		'headers' => array(
			'Content-Type' => 'application/json',
			'Accept'       => 'application/json',
		),
		'body'    => wp_json_encode( $body ),
	) );

	if ( is_wp_error( $response ) ) {
		return $response;
	}

	$data = json_decode( wp_remote_retrieve_body( $response ), true );

	if ( ! empty( $data['data']['code'] ) && (int) $data['data']['code'] === 100 ) {
		$authority = $data['data']['authority'];
		return array(
			'authority'    => $authority,
			'redirect_url' => noghre_zarinpal_base_url() . '/pg/StartPay/' . $authority,
		);
	}

	$message = $data['errors']['message'] ?? ( $data['data']['message'] ?? 'خطا در برقراری ارتباط با درگاه پرداخت.' );
	return new WP_Error( 'noghre_zarinpal_request_failed', $message );
}

/**
 * تایید یک تراکنش پرداخت‌شده نزد زرین‌پال.
 *
 * @return array|WP_Error ['ref_id'=>..., 'card_pan'=>...] یا خطا
 */
function noghre_zarinpal_verify( $amount_toman, $authority ) {
	$o = noghre_get_zarinpal_options();
	if ( ! $o['zarinpal_merchant_id'] ) {
		return new WP_Error( 'noghre_zarinpal_not_configured', 'درگاه پرداخت تنظیم نشده است.' );
	}

	$body = array(
		'merchant_id' => $o['zarinpal_merchant_id'],
		'amount'      => (int) $amount_toman,
		'authority'   => $authority,
	);

	$response = wp_remote_post( noghre_zarinpal_base_url() . '/pg/v4/payment/verify.json', array(
		'timeout' => 15,
		'headers' => array(
			'Content-Type' => 'application/json',
			'Accept'       => 'application/json',
		),
		'body'    => wp_json_encode( $body ),
	) );

	if ( is_wp_error( $response ) ) {
		return $response;
	}

	$data = json_decode( wp_remote_retrieve_body( $response ), true );
	$code = isset( $data['data']['code'] ) ? (int) $data['data']['code'] : 0;

	if ( $code === 100 || $code === 101 ) {
		return array(
			'ref_id'   => $data['data']['ref_id'] ?? '',
			'card_pan' => $data['data']['card_pan'] ?? '',
		);
	}

	$message = $data['errors']['message'] ?? ( $data['data']['message'] ?? 'تایید پرداخت ناموفق بود.' );
	return new WP_Error( 'noghre_zarinpal_verify_failed', $message );
}