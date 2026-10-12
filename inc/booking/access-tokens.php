<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/* ============================================================
 * 🎫 سیستم توکن دسترسی
 * ============================================================ */

function noghre_create_access_token( $admin_key ) {
	$token = bin2hex( random_bytes( 32 ) );

	$tokens = get_option( 'noghre_access_tokens', array() );
	if ( ! is_array( $tokens ) ) $tokens = array();

	$now = time();
	$tokens[ $token ] = array(
		'created_at' => $now,
		'expires_at' => $now + ( 24 * HOUR_IN_SECONDS ),
		'admin_key'  => $admin_key,
		'last_used'  => $now,
		'ip'         => isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( $_SERVER['REMOTE_ADDR'] ) : '',
		'ua'         => isset( $_SERVER['HTTP_USER_AGENT'] ) ? substr( sanitize_text_field( $_SERVER['HTTP_USER_AGENT'] ), 0, 200 ) : '',
	);

	foreach ( $tokens as $t => $data ) {
		if ( ( $now - ( $data['created_at'] ?? 0 ) ) > ( 30 * DAY_IN_SECONDS ) ) {
			unset( $tokens[ $t ] );
		}
	}

	if ( count( $tokens ) > 20 ) {
		uasort( $tokens, function( $a, $b ) {
			return ( $a['last_used'] ?? 0 ) <=> ( $b['last_used'] ?? 0 );
		} );
		$tokens = array_slice( $tokens, -20, 20, true );
	}

	update_option( 'noghre_access_tokens', $tokens, false );

	return $token;
}

function noghre_verify_access_token( $token ) {
	if ( empty( $token ) || ! is_string( $token ) ) {
		return new WP_Error( 'invalid_token', 'توکن نامعتبر است.' );
	}

	$tokens = get_option( 'noghre_access_tokens', array() );
	if ( ! is_array( $tokens ) || empty( $tokens[ $token ] ) ) {
		return new WP_Error( 'token_not_found', 'توکن یافت نشد.' );
	}

	$data = $tokens[ $token ];
	$now  = time();

	if ( ! empty( $data['expires_at'] ) && $now > $data['expires_at'] ) {
		$current_key = noghre_get_admin_access_key();
		if ( $data['admin_key'] !== $current_key ) {
			noghre_revoke_access_token( $token );
			return new WP_Error( 'admin_key_changed', 'شناسه مدیریت تغییر کرده است.' );
		}
		return new WP_Error( 'token_expired', 'توکن منقضی شده است.' );
	}

	$current_key = noghre_get_admin_access_key();
	if ( $data['admin_key'] !== $current_key ) {
		noghre_revoke_access_token( $token );
		return new WP_Error( 'admin_key_changed', 'شناسه مدیریت تغییر کرده است.' );
	}

	$tokens[ $token ]['last_used'] = $now;
	update_option( 'noghre_access_tokens', $tokens, false );

	return $data;
}

function noghre_refresh_access_token( $token ) {
	$tokens = get_option( 'noghre_access_tokens', array() );
	if ( ! is_array( $tokens ) || empty( $tokens[ $token ] ) ) {
		return new WP_Error( 'token_not_found', 'توکن یافت نشد.' );
	}

	$data = $tokens[ $token ];
	$current_key = noghre_get_admin_access_key();

	if ( $data['admin_key'] !== $current_key ) {
		noghre_revoke_access_token( $token );
		return new WP_Error( 'admin_key_changed', 'شناسه مدیریت تغییر کرده است.' );
	}

	$now = time();
	$tokens[ $token ]['expires_at'] = $now + ( 24 * HOUR_IN_SECONDS );
	$tokens[ $token ]['last_used']  = $now;

	update_option( 'noghre_access_tokens', $tokens, false );

	return true;
}

function noghre_revoke_access_token( $token ) {
	$tokens = get_option( 'noghre_access_tokens', array() );
	if ( ! is_array( $tokens ) ) return;
	if ( isset( $tokens[ $token ] ) ) {
		unset( $tokens[ $token ] );
		update_option( 'noghre_access_tokens', $tokens, false );
	}
}

function noghre_revoke_all_access_tokens() {
	update_option( 'noghre_access_tokens', array(), false );
}

function noghre_is_app_request_authenticated() {
	$token = '';

	if ( ! empty( $_POST['access_token'] ) ) {
		$token = sanitize_text_field( wp_unslash( $_POST['access_token'] ) );
	}
	elseif ( ! empty( $_SERVER['HTTP_X_NOGHRE_TOKEN'] ) ) {
		$token = sanitize_text_field( $_SERVER['HTTP_X_NOGHRE_TOKEN'] );
	}
	elseif ( ! empty( $_COOKIE['noghre_access_token'] ) ) {
		$token = sanitize_text_field( $_COOKIE['noghre_access_token'] );
	}

	if ( empty( $token ) ) return false;

	$result = noghre_verify_access_token( $token );
	return ! is_wp_error( $result );
}

function noghre_is_admin_request() {
	if ( ! empty( $_GET['m'] ) && noghre_is_admin_access( wp_unslash( $_GET['m'] ) ) ) {
		return true;
	}
	if ( ! empty( $_POST['m'] ) && noghre_is_admin_access( wp_unslash( $_POST['m'] ) ) ) {
		return true;
	}
	if ( noghre_is_app_request_authenticated() ) {
		return true;
	}
	return false;
}

/* ============================================================
 * AJAX: مدیریت توکن
 * ============================================================ */

function noghre_ajax_create_token() {
	$admin_key = isset( $_POST['m'] ) ? sanitize_text_field( wp_unslash( $_POST['m'] ) ) : '';

	if ( ! noghre_is_admin_access( $admin_key ) ) {
		wp_send_json_error( array(
			'message' => 'شناسه مدیریت اشتباه است.',
			'code'    => 'invalid_admin_key',
		) );
	}

	$token = noghre_create_access_token( $admin_key );

	setcookie(
		'noghre_access_token',
		$token,
		array(
			'expires'  => time() + ( 24 * HOUR_IN_SECONDS ),
			'path'     => '/',
			'domain'   => '',
			'secure'   => is_ssl(),
			'httponly' => true,
			'samesite' => 'Lax',
		)
	);

	wp_send_json_success( array(
		'token'      => $token,
		'expires_at' => time() + ( 24 * HOUR_IN_SECONDS ),
	) );
}
add_action( 'wp_ajax_noghre_create_token', 'noghre_ajax_create_token' );
add_action( 'wp_ajax_nopriv_noghre_create_token', 'noghre_ajax_create_token' );

function noghre_ajax_verify_token() {
	$token = isset( $_POST['token'] ) ? sanitize_text_field( wp_unslash( $_POST['token'] ) ) : '';

	if ( empty( $token ) ) {
		wp_send_json_error( array(
			'message' => 'توکن ارسال نشده است.',
			'code'    => 'no_token',
		) );
	}

	$result = noghre_verify_access_token( $token );

	if ( is_wp_error( $result ) ) {
		$error_code = $result->get_error_code();

		if ( $error_code === 'token_expired' ) {
			$refresh = noghre_refresh_access_token( $token );
			if ( ! is_wp_error( $refresh ) ) {
				wp_send_json_success( array(
					'valid'     => true,
					'refreshed' => true,
					'message'   => 'توکن تمدید شد.',
				) );
			}
			$error_code = $refresh->get_error_code();
		}

		wp_send_json_error( array(
			'message' => $result->get_error_message(),
			'code'    => $error_code,
		) );
	}

	$now = time();
	$needs_refresh = ( $result['expires_at'] - $now ) < ( 2 * HOUR_IN_SECONDS );

	if ( $needs_refresh ) {
		noghre_refresh_access_token( $token );
	}

	wp_send_json_success( array(
		'valid'      => true,
		'refreshed'  => $needs_refresh,
		'expires_at' => $needs_refresh ? ( $now + ( 24 * HOUR_IN_SECONDS ) ) : $result['expires_at'],
	) );
}
add_action( 'wp_ajax_noghre_verify_token', 'noghre_ajax_verify_token' );
add_action( 'wp_ajax_nopriv_noghre_verify_token', 'noghre_ajax_verify_token' );

function noghre_ajax_revoke_token() {
	$token = isset( $_POST['token'] ) ? sanitize_text_field( wp_unslash( $_POST['token'] ) ) : '';
	if ( $token ) {
		noghre_revoke_access_token( $token );
	}
	wp_send_json_success( array( 'message' => 'خارج شدید.' ) );
}
add_action( 'wp_ajax_noghre_revoke_token', 'noghre_ajax_revoke_token' );
add_action( 'wp_ajax_nopriv_noghre_revoke_token', 'noghre_ajax_revoke_token' );