<?php
/**
 * پل ارتباطی بین قالب نقره و افزونه افرا اس ام اس (AfraSMS)
 *
 * @package Noghre
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/* ==========================================================================
 * بخش ۱ — بررسی وضعیت افزونه
 * ========================================================================== */

function noghre_afra_is_installed() {
	return function_exists( 'afra_sms_is_ready' );
}

function noghre_afra_is_active() {
	if ( ! function_exists( 'is_plugin_active' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}
	$active_plugins = (array) get_option( 'active_plugins', array() );
	foreach ( $active_plugins as $plugin ) {
		if ( stripos( $plugin, 'afra-sms' ) !== false ) {
			return true;
		}
	}
	if ( is_multisite() ) {
		$network_plugins = (array) get_site_option( 'active_sitewide_plugins', array() );
		foreach ( array_keys( $network_plugins ) as $plugin ) {
			if ( stripos( $plugin, 'afra-sms' ) !== false ) {
				return true;
			}
		}
	}
	return false;
}

function noghre_afra_is_ready() {
	if ( ! noghre_afra_is_installed() ) return false;
	if ( ! function_exists( 'afra_sms_is_ready' ) ) return false;
	return (bool) afra_sms_is_ready();
}

function noghre_afra_get_status() {
	$installed = noghre_afra_is_installed();
	$active    = noghre_afra_is_active();
	$ready     = $active ? noghre_afra_is_ready() : false;

	if ( ! $installed ) {
		return array(
			'installed' => false, 'active' => false, 'ready' => false, 'level' => 'error',
			'message'   => 'افزونه «افرا اس ام اس» نصب نیست. برای فعال شدن پیامک‌ها، ابتدا این افزونه را نصب و فعال کنید.',
		);
	}
	if ( ! $active ) {
		return array(
			'installed' => true, 'active' => false, 'ready' => false, 'level' => 'error',
			'message'   => 'افزونه «افرا اس ام اس» نصب است ولی فعال نیست. لطفاً از بخش افزونه‌ها آن را فعال کنید.',
		);
	}
	if ( ! $ready ) {
		return array(
			'installed' => true, 'active' => true, 'ready' => false, 'level' => 'warning',
			'message'   => 'افزونه «افرا اس ام اس» فعال است ولی به API متصل نیست. لطفاً از تنظیمات افزونه، اتصال را برقرار کنید.',
		);
	}
	return array(
		'installed' => true, 'active' => true, 'ready' => true, 'level' => 'success',
		'message'   => 'افزونه «افرا اس ام اس» فعال و آماده است.',
	);
}

/* ==========================================================================
 * بخش ۱.۵ — نرمال‌سازی شماره موبایل
 * ========================================================================== */

function noghre_sms_normalize_mobile( $number ) {
	$number = preg_replace( '/\D/', '', (string) $number );

	if ( $number === '' ) return '';

	if ( substr( $number, 0, 2 ) === '00' ) {
		$number = substr( $number, 2 );
	}

	if ( substr( $number, 0, 2 ) === '98' ) {
		$number = substr( $number, 2 );
	}

	if ( substr( $number, 0, 1 ) === '9' ) {
		$number = '0' . $number;
	}

	if ( substr( $number, 0, 1 ) !== '0' ) {
		return '';
	}

	return $number;
}

function noghre_sms_to_e164( $number ) {
	$normalized = noghre_sms_normalize_mobile( $number );
	if ( $normalized === '' ) return '';

	$without_zero = substr( $normalized, 1 );
	return '+98' . $without_zero;
}

function noghre_sms_mobile_variants( $number ) {
	$normalized = noghre_sms_normalize_mobile( $number );
	if ( $normalized === '' ) return array();

	$without_zero = substr( $normalized, 1 );

	return array(
		$normalized,
		$without_zero,
		'98' . $without_zero,
		'+98' . $without_zero,
		'0098' . $without_zero,
		'098' . $without_zero,
	);
}

/* ==========================================================================
 * بخش ۲ — Wrapper های ارسال پیامک
 * ========================================================================== */

function noghre_sms_send( $mobile, $message ) {
	if ( ! noghre_afra_is_ready() ) {
		return new WP_Error( 'noghre_sms_unavailable', 'افزونه افرا اس ام اس آماده نیست.' );
	}
	$result = afra_sms_send( $mobile, $message );
	if ( is_wp_error( $result ) ) {
		noghre_log_error( 'ارسال پیامک ساده', $result->get_error_message() );
		return $result;
	}
	return true;
}

function noghre_sms_send_pattern( $mobile, $pattern_code, $params ) {
	if ( ! noghre_afra_is_ready() ) {
		return new WP_Error( 'noghre_sms_unavailable', 'افزونه افرا اس ام اس آماده نیست.' );
	}
	if ( empty( $pattern_code ) ) {
		return new WP_Error( 'noghre_sms_no_pattern', 'کد پترن تنظیم نشده است.' );
	}

	$sms    = noghre_get_sms_options();
	$sender = ! empty( $sms['sms_sender_number'] ) ? trim( $sms['sms_sender_number'] ) : '';

	if ( ! empty( $sender ) ) {
		$result = afra_sms_send_pattern( $mobile, $pattern_code, $params, $sender );
	} else {
		$result = afra_sms_send_pattern( $mobile, $pattern_code, $params );
	}

	if ( is_wp_error( $result ) ) {
		noghre_log_error( 'ارسال پیامک پترن', $result->get_error_message() . ' (پترن: ' . $pattern_code . ')' );
		return $result;
	}
	return true;
}

/* ==========================================================================
 * بخش ۳ — Wrapper های دفترچه تلفن
 * ========================================================================== */

function noghre_sms_number_exists( $mobile, $phonebook_id ) {
	if ( ! noghre_afra_is_ready() || ! $phonebook_id ) {
		return false;
	}

	if ( function_exists( 'afra_sms_is_contact_in_phonebook' ) ) {
		$result = afra_sms_is_contact_in_phonebook( (int) $phonebook_id, $mobile );
		if ( ! is_wp_error( $result ) ) {
			return (bool) $result;
		}
	}

	if ( ! function_exists( 'afra_sms_get_phonebook_contacts' ) ) {
		return false;
	}

	$variants = noghre_sms_mobile_variants( $mobile );
	if ( empty( $variants ) ) {
		return false;
	}

	$phonebook_id = (int) $phonebook_id;
	$normalized   = noghre_sms_normalize_mobile( $mobile );

	$search_terms = array(
		$mobile,
		$normalized,
		'+98' . substr( $normalized, 1 ),
		'98' . substr( $normalized, 1 ),
	);

	foreach ( array_unique( $search_terms ) as $term ) {
		$contacts = afra_sms_get_phonebook_contacts( $phonebook_id, array( 'search' => $term ) );
		if ( is_wp_error( $contacts ) || ! is_array( $contacts ) ) continue;

		foreach ( $contacts as $contact ) {
			if ( ! is_array( $contact ) ) continue;
			$stored = '';
			foreach ( array( 'number', 'mobile', 'phone', 'contact_number' ) as $key ) {
				if ( ! empty( $contact[ $key ] ) ) { $stored = $contact[ $key ]; break; }
			}
			if ( $stored && noghre_sms_normalize_mobile( $stored ) === $normalized ) {
				return true;
			}
		}
	}

	$page = 1;
	$per_page = 500;
	$max_pages = 20;

	while ( $page <= $max_pages ) {
		$contacts = afra_sms_get_phonebook_contacts( $phonebook_id, array(
			'page'     => $page,
			'per_page' => $per_page,
		) );
		if ( is_wp_error( $contacts ) || ! is_array( $contacts ) || empty( $contacts ) ) break;

		foreach ( $contacts as $contact ) {
			if ( ! is_array( $contact ) ) continue;
			$stored = '';
			foreach ( array( 'number', 'mobile', 'phone', 'contact_number' ) as $key ) {
				if ( ! empty( $contact[ $key ] ) ) { $stored = $contact[ $key ]; break; }
			}
			if ( $stored && noghre_sms_normalize_mobile( $stored ) === $normalized ) {
				return true;
			}
		}

		if ( count( $contacts ) < $per_page ) break;
		$page++;
	}

	return false;
}

function noghre_sms_add_to_phonebook( $mobile, $name, $phonebook_id ) {
	if ( ! noghre_afra_is_ready() ) {
		return new WP_Error( 'noghre_sms_unavailable', 'افزونه افرا اس ام اس آماده نیست.' );
	}
	if ( ! $phonebook_id ) {
		return new WP_Error( 'noghre_sms_no_phonebook', 'شناسه دفترچه تنظیم نشده است.' );
	}

	$number_e164 = noghre_sms_to_e164( $mobile );
	if ( $number_e164 === '' ) {
		return new WP_Error( 'noghre_sms_invalid_mobile', 'شماره موبایل نامعتبر است.' );
	}

	$data = array(
		'number' => $number_e164,
		'name'   => $name,
	);

	$result = afra_sms_add_phonebook_contact( (int) $phonebook_id, $data );
	if ( ! is_wp_error( $result ) ) {
		return true;
	}

	noghre_log_error( 'افزودن به دفترچه - تلاش ۱ (تکی)', 'شماره: ' . $number_e164 . ' | خطا: ' . $result->get_error_message() );

	if ( function_exists( 'afra_sms_add_phonebook_contacts' ) ) {
		$list = array( array( 'number' => $number_e164, 'name' => $name ) );
		$result2 = afra_sms_add_phonebook_contacts( (int) $phonebook_id, $list );
		if ( ! is_wp_error( $result2 ) ) {
			return true;
		}
		noghre_log_error( 'افزودن به دفترچه - تلاش ۲ (گروهی)', 'شماره: ' . $number_e164 . ' | خطا: ' . $result2->get_error_message() );
		return $result2;
	}

	return $result;
}

/* ==========================================================================
 * بخش ۴ — همگام‌سازی دفترچه‌ها هنگام رزرو
 * ========================================================================== */

function noghre_sync_phonebooks_after_booking( $mobile, $name ) {
	if ( ! noghre_afra_is_ready() ) {
		noghre_log_error( 'همگام‌سازی دفترچه‌ها', 'افزونه افرا اس ام اس آماده نیست.' );
		return new WP_Error( 'noghre_sms_unavailable', 'افزونه افرا اس ام اس آماده نیست.' );
	}

	$mobile = noghre_sms_normalize_mobile( $mobile );
	if ( $mobile === '' ) {
		noghre_log_error( 'همگام‌سازی دفترچه‌ها', 'شماره موبایل نامعتبر.' );
		return new WP_Error( 'noghre_sms_invalid_mobile', 'شماره موبایل نامعتبر است.' );
	}

	$sms = noghre_get_sms_options();

	$discount_pb_id = ! empty( $sms['sms_phonebook_id'] ) ? (int) $sms['sms_phonebook_id'] : 0;
	$customer_pb_id = ! empty( $sms['sms_customer_club_phonebook_id'] ) ? (int) $sms['sms_customer_club_phonebook_id'] : 0;

	if ( ! $discount_pb_id || ! $customer_pb_id ) {
		noghre_log_error( 'همگام‌سازی دفترچه‌ها', 'دفترچه تخفیف یا مشتریان در تنظیمات انتخاب نشده.' );
		return new WP_Error( 'noghre_sms_no_phonebook', 'دفترچه‌ها در تنظیمات انتخاب نشده‌اند.' );
	}

	$in_discount = noghre_sms_number_exists( $mobile, $discount_pb_id );

	if ( true === $in_discount ) {
		if ( function_exists( 'afra_sms_move_contact' ) ) {
			$move = afra_sms_move_contact( $discount_pb_id, $customer_pb_id, $mobile, array(
				'name' => $name,
			) );

			if ( is_wp_error( $move ) ) {
				noghre_log_error( 'انتقال شماره از تخفیف به مشتریان', $move->get_error_message() );
				return $move;
			}

			noghre_log_error( 'انتقال موفق', sprintf(
				'شماره %s از دفترچه تخفیف به مشتریان منتقل شد. نتیجه: moved=%s, removed_from=%s, added_to=%s, already_in_target=%s',
				$mobile,
				! empty( $move['moved'] ) ? 'yes' : 'no',
				! empty( $move['removed_from'] ) ? 'yes' : 'no',
				! empty( $move['added_to'] ) ? 'yes' : 'no',
				! empty( $move['already_in_target'] ) ? 'yes' : 'no'
			) );

			return true;
		}

		$removed = false;
		if ( function_exists( 'afra_sms_remove_contact_by_number' ) ) {
			$rem = afra_sms_remove_contact_by_number( $discount_pb_id, $mobile );
			if ( ! is_wp_error( $rem ) ) {
				$removed = true;
			} else {
				noghre_log_error( 'حذف از تخفیف (fallback)', $rem->get_error_message() );
			}
		}

		$added = noghre_sms_add_to_phonebook( $mobile, $name, $customer_pb_id );

		if ( is_wp_error( $added ) ) {
			noghre_log_error( 'افزودن به مشتریان (fallback)', $added->get_error_message() );
			return $added;
		}

		return true;
	}

	$in_customer = noghre_sms_number_exists( $mobile, $customer_pb_id );

	if ( true === $in_customer ) {
		return true;
	}

	$added = noghre_sms_add_to_phonebook( $mobile, $name, $customer_pb_id );

	if ( is_wp_error( $added ) ) {
		noghre_log_error( 'افزودن شماره جدید به مشتریان', $added->get_error_message() );
		return $added;
	}

	noghre_log_error( 'افزودن شماره جدید', sprintf( 'شماره %s به دفترچه مشتریان اضافه شد.', $mobile ) );

	return true;
}

/* ==========================================================================
 * بخش ۵ — اعتبار و اطلاعات افزونه
 * ========================================================================== */

function noghre_sms_get_credit_toman() {
	if ( ! noghre_afra_is_ready() ) {
		return new WP_Error( 'noghre_sms_unavailable', 'افزونه افرا اس ام اس آماده نیست.' );
	}
	return afra_sms_get_credit_toman();
}

function noghre_sms_get_numbers( $force = false ) {
	if ( ! noghre_afra_is_ready() || ! function_exists( 'afra_sms_get_numbers' ) ) {
		return new WP_Error( 'noghre_sms_unavailable', 'افزونه افرا اس ام اس آماده نیست.' );
	}
	$numbers = afra_sms_get_numbers( $force );
	if ( is_wp_error( $numbers ) || ! is_array( $numbers ) ) {
		return is_wp_error( $numbers ) ? $numbers : new WP_Error( 'noghre_sms_empty', 'لیست سرشماره‌ها خالی است.' );
	}

	$result = array();
	foreach ( $numbers as $number ) {
		if ( ! is_array( $number ) ) continue;
		$value = '';
		foreach ( array( 'number', 'sender', 'value', 'from' ) as $key ) {
			if ( ! empty( $number[ $key ] ) ) { $value = (string) $number[ $key ]; break; }
		}
		if ( empty( $value ) ) continue;

		$title = '';
		foreach ( array( 'alias', 'title', 'name', 'description' ) as $key ) {
			if ( ! empty( $number[ $key ] ) ) { $title = (string) $number[ $key ]; break; }
		}
		if ( empty( $title ) ) $title = $value;

		$result[] = array( 'value' => $value, 'title' => $title );
	}
	return $result;
}

function noghre_sms_get_patterns( $force = false ) {
	if ( ! noghre_afra_is_ready() || ! function_exists( 'afra_sms_get_patterns' ) ) {
		return new WP_Error( 'noghre_sms_unavailable', 'افزونه افرا اس ام اس آماده نیست.' );
	}
	$patterns = afra_sms_get_patterns( $force );
	if ( is_wp_error( $patterns ) || ! is_array( $patterns ) ) {
		return is_wp_error( $patterns ) ? $patterns : new WP_Error( 'noghre_sms_empty', 'لیست پترن‌ها خالی است.' );
	}

	$result = array();
	foreach ( $patterns as $pattern ) {
		if ( ! is_array( $pattern ) ) continue;

		$code = '';
		foreach ( array( 'pattern_code', 'code', 'pattern', 'id' ) as $key ) {
			if ( ! empty( $pattern[ $key ] ) ) { $code = (string) $pattern[ $key ]; break; }
		}
		if ( empty( $code ) ) continue;

		$title = '';
		foreach ( array( 'pattern_title', 'title', 'name', 'description', 'subject' ) as $key ) {
			if ( ! empty( $pattern[ $key ] ) ) { $title = (string) $pattern[ $key ]; break; }
		}

		$message = '';
		foreach ( array( 'pattern_message', 'message', 'content', 'body', 'text' ) as $key ) {
			if ( ! empty( $pattern[ $key ] ) ) { $message = (string) $pattern[ $key ]; break; }
		}

		if ( empty( $title ) ) {
			if ( ! empty( $message ) ) {
				$title = mb_substr( wp_strip_all_tags( $message ), 0, 50 );
				if ( mb_strlen( $message ) > 50 ) $title .= '...';
			} else {
				$title = $code;
			}
		}

		$result[] = array( 'code' => $code, 'title' => $title, 'message' => $message );
	}
	return $result;
}

function noghre_sms_get_phonebooks( $force = false ) {
	if ( ! noghre_afra_is_ready() || ! function_exists( 'afra_sms_get_phonebooks' ) ) {
		return new WP_Error( 'noghre_sms_unavailable', 'افزونه افرا اس ام اس آماده نیست.' );
	}
	$phonebooks = afra_sms_get_phonebooks( $force );
	if ( is_wp_error( $phonebooks ) || ! is_array( $phonebooks ) ) {
		return is_wp_error( $phonebooks ) ? $phonebooks : new WP_Error( 'noghre_sms_empty', 'لیست دفترچه‌ها خالی است.' );
	}

	$result = array();
	foreach ( $phonebooks as $book ) {
		if ( ! is_array( $book ) ) continue;

		$id = 0;
		foreach ( array( 'id', 'phonebook_id' ) as $key ) {
			if ( ! empty( $book[ $key ] ) ) { $id = intval( $book[ $key ] ); break; }
		}
		if ( empty( $id ) ) continue;

		$title = '';
		foreach ( array( 'title', 'name', 'label' ) as $key ) {
			if ( ! empty( $book[ $key ] ) ) { $title = (string) $book[ $key ]; break; }
		}
		if ( empty( $title ) ) $title = 'دفترچه #' . $id;

		$count = 0;
		if ( isset( $book['count'] ) ) $count = intval( $book['count'] );

		$result[] = array( 'id' => $id, 'title' => $title, 'count' => $count );
	}
	return $result;
}

/* ==========================================================================
 * بخش ۶ — AJAX فرم باشگاه تخفیف
 * ========================================================================== */

function noghre_ajax_discount_signup() {
	check_ajax_referer( 'noghre_discount_signup', 'nonce' );

	if ( ! empty( $_POST['website'] ) ) {
		wp_send_json_success( array( 'message' => 'ثبت‌نام شما با موفقیت انجام شد.' ) );
	}

	$captcha_check = noghre_captcha_verify_request();
	if ( is_wp_error( $captcha_check ) ) {
		wp_send_json_error( array( 'message' => $captcha_check->get_error_message() ) );
	}

	$name   = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
	$mobile = isset( $_POST['mobile'] ) ? preg_replace( '/\D/', '', wp_unslash( $_POST['mobile'] ) ) : '';

	if ( mb_strlen( $name ) < 2 ) {
		wp_send_json_error( array( 'message' => 'لطفاً نام و نام خانوادگی را کامل وارد کنید.' ) );
	}
	if ( ! preg_match( '/^09\d{9}$/', $mobile ) ) {
		wp_send_json_error( array( 'message' => 'شماره موبایل باید ۱۱ رقم و با ۰۹ شروع شود.' ) );
	}

	if ( ! noghre_afra_is_ready() ) {
		$status = noghre_afra_get_status();
		wp_send_json_error( array( 'message' => $status['message'] ) );
	}

	$sms = noghre_get_sms_options();

	$sms_enabled = ! empty( $sms['sms_discount_enabled'] );

	$exists_in_discount = false;
	$exists_in_club     = false;

	if ( ! empty( $sms['sms_phonebook_id'] ) ) {
		$check_discount = noghre_sms_number_exists( $mobile, $sms['sms_phonebook_id'] );
		$exists_in_discount = ( true === $check_discount );
	}

	if ( ! empty( $sms['sms_customer_club_phonebook_id'] ) ) {
		$check_club = noghre_sms_number_exists( $mobile, $sms['sms_customer_club_phonebook_id'] );
		$exists_in_club = ( true === $check_club );
	}

	if ( $exists_in_discount ) {
		wp_send_json_error( array(
			'message' => 'این شماره موبایل قبلاً در باشگاه تخفیف نقره عضو شده است. 🌟',
		) );
	}

	if ( $exists_in_club ) {
		wp_send_json_error( array(
			'message' => 'شما از قبل مشتری نقره هستید 💎 کدهای تخفیف ویژه به‌زودی برای شما ارسال خواهد شد. نیازی به ثبت‌نام مجدد نیست.',
		) );
	}

	$phonebook_added = false;
	$phonebook_error = '';

	if ( ! empty( $sms['sms_phonebook_id'] ) ) {
		$add_result = noghre_sms_add_to_phonebook( $mobile, $name, $sms['sms_phonebook_id'] );
		if ( is_wp_error( $add_result ) ) {
			$phonebook_error = $add_result->get_error_message();
			noghre_log_error( 'افزودن به دفترچه تخفیف (AJAX)', $phonebook_error );
		} else {
			$phonebook_added = true;
		}
	}

	$sms_result = true;

	if ( $sms_enabled ) {
		$params = array();
		if ( ! empty( $sms['sms_pattern_name_var'] ) ) {
			$params[ $sms['sms_pattern_name_var'] ] = $name;
		}
		if ( ! empty( $sms['sms_pattern_discount_var'] ) && ! empty( $sms['sms_pattern_discount_val'] ) ) {
			$params[ $sms['sms_pattern_discount_var'] ] = $sms['sms_pattern_discount_val'];
		}

		$sms_result = noghre_sms_send_pattern( $mobile, $sms['sms_pattern_code'], $params );
	}

	$signups   = get_option( 'noghre_discount_signups', array() );
	$signups[] = array(
		'name'            => $name,
		'mobile'          => $mobile,
		'date'            => current_time( 'timestamp' ),
		'sms_sent'        => $sms_enabled ? ! is_wp_error( $sms_result ) : null,
		'phonebook_added' => $phonebook_added,
		'phonebook_error' => $phonebook_error,
	);
	if ( count( $signups ) > 500 ) {
		$signups = array_slice( $signups, -500 );
	}
	update_option( 'noghre_discount_signups', $signups );

	wp_send_json_success( array( 'message' => 'ثبت‌نام شما با موفقیت انجام شد.' ) );
}
add_action( 'wp_ajax_noghre_discount_signup', 'noghre_ajax_discount_signup' );
add_action( 'wp_ajax_nopriv_noghre_discount_signup', 'noghre_ajax_discount_signup' );

/* ==========================================================================
 * بخش ۷ — AJAX: بروزرسانی لیست‌ها + تست ارسال
 * ========================================================================== */

function noghre_ajax_refresh_sms_lists() {
	check_ajax_referer( 'noghre_refresh_sms_lists', 'nonce' );

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => 'دسترسی ندارید.' ) );
	}

	if ( function_exists( 'afra_sms_clear_cache' ) ) {
		afra_sms_clear_cache();
	}

	wp_send_json_success( array(
		'patterns'   => noghre_sms_get_patterns( true ),
		'numbers'    => noghre_sms_get_numbers( true ),
		'phonebooks' => noghre_sms_get_phonebooks( true ),
	) );
}
add_action( 'wp_ajax_noghre_refresh_sms_lists', 'noghre_ajax_refresh_sms_lists' );

function noghre_ajax_test_sms() {
	check_ajax_referer( 'noghre_test_sms', 'nonce' );

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => 'دسترسی غیرمجاز.' ) );
	}

	if ( ! noghre_afra_is_ready() ) {
		$status = noghre_afra_get_status();
		wp_send_json_error( array( 'message' => $status['message'] ) );
	}

	$mobile = isset( $_POST['mobile'] ) ? preg_replace( '/\D/', '', wp_unslash( $_POST['mobile'] ) ) : '';
	$type   = isset( $_POST['type'] ) ? sanitize_key( $_POST['type'] ) : '';

	if ( ! preg_match( '/^09\d{9}$/', $mobile ) ) {
		wp_send_json_error( array( 'message' => 'شماره موبایل باید ۱۱ رقم و با ۰۹ شروع شود.' ) );
	}

	$sms = noghre_get_sms_options();

	switch ( $type ) {
		case 'customer':
			$pattern = $sms['sms_pattern_booking_customer'];
			$params  = array();
			if ( ! empty( $sms['sms_pattern_customer_var_name'] ) ) {
				$params[ $sms['sms_pattern_customer_var_name'] ] = 'مشتری تست';
			}
			if ( ! empty( $sms['sms_pattern_customer_var_peygiri'] ) ) {
				$params[ $sms['sms_pattern_customer_var_peygiri'] ] = 'TEST1234';
			}
			$label = 'پترن تایید رزرو به مشتری';
			break;

		case 'branch':
			$pattern = $sms['sms_pattern_booking_branch'];
			$params  = array();
			if ( ! empty( $sms['sms_pattern_branch_var_shobe'] ) ) {
				$params[ $sms['sms_pattern_branch_var_shobe'] ] = 'شعبه تست';
			}
			if ( ! empty( $sms['sms_pattern_branch_var_peygiri'] ) ) {
				$params[ $sms['sms_pattern_branch_var_peygiri'] ] = 'TEST1234';
			}
			if ( ! empty( $sms['sms_pattern_branch_var_modir'] ) ) {
				$params[ $sms['sms_pattern_branch_var_modir'] ] = noghre_get_admin_access_key() ?: 'TESTKEY';
			}
			$label = 'پترن رزرو به مدیر شعبه';
			break;

		case 'confirm':
			$pattern = $sms['sms_pattern_booking_confirm'];
			$params  = array();
			if ( ! empty( $sms['sms_pattern_confirm_var_name'] ) ) {
				$params[ $sms['sms_pattern_confirm_var_name'] ] = 'مشتری تست';
			}
			if ( ! empty( $sms['sms_pattern_confirm_var_peygiri'] ) ) {
				$params[ $sms['sms_pattern_confirm_var_peygiri'] ] = 'TEST1234';
			}
			$label = 'پترن تایید رزرو';
			break;

		case 'edit':
			$pattern = $sms['sms_pattern_booking_edit'];
			$params  = array();
			if ( ! empty( $sms['sms_pattern_edit_var_name'] ) ) {
				$params[ $sms['sms_pattern_edit_var_name'] ] = 'مشتری تست';
			}
			if ( ! empty( $sms['sms_pattern_edit_var_peygiri'] ) ) {
				$params[ $sms['sms_pattern_edit_var_peygiri'] ] = 'TEST1234';
			}
			$label = 'پترن ویرایش رزرو';
			break;

		case 'void':
			$pattern = $sms['sms_pattern_booking_void'];
			$params  = array();
			if ( ! empty( $sms['sms_pattern_void_var_name'] ) ) {
				$params[ $sms['sms_pattern_void_var_name'] ] = 'مشتری تست';
			}
			$label = 'پترن باطل شدن رزرو';
			break;

		case 'unused':
			$pattern = $sms['sms_pattern_booking_unused'];
			$params  = array();
			if ( ! empty( $sms['sms_pattern_unused_var_name'] ) ) {
				$params[ $sms['sms_pattern_unused_var_name'] ] = 'مشتری تست';
			}
			if ( ! empty( $sms['sms_pattern_unused_discount_var'] ) && ! empty( $sms['sms_pattern_unused_discount_val'] ) ) {
				$params[ $sms['sms_pattern_unused_discount_var'] ] = $sms['sms_pattern_unused_discount_val'];
			}
			$label = 'پترن رزرو استفاده نشده';
			break;

		case 'discount':
			$pattern = $sms['sms_pattern_code'];
			$params  = array();
			if ( ! empty( $sms['sms_pattern_name_var'] ) ) {
				$params[ $sms['sms_pattern_name_var'] ] = 'مشتری تست';
			}
			if ( ! empty( $sms['sms_pattern_discount_val'] ) && ! empty( $sms['sms_pattern_discount_var'] ) ) {
				$params[ $sms['sms_pattern_discount_var'] ] = $sms['sms_pattern_discount_val'];
			}
			$label = 'پترن باشگاه تخفیف';
			break;

		case 'signup':
			$pattern = $sms['sms_pattern_signup'];
			$params  = array();
			if ( ! empty( $sms['sms_pattern_signup_var_name'] ) ) {
				$params[ $sms['sms_pattern_signup_var_name'] ] = 'مشتری تست';
			}
			$label = 'پترن ثبت شماره مشتری';
			break;

		default:
			wp_send_json_error( array( 'message' => 'نوع تست نامشخص است.' ) );
	}

	if ( empty( $pattern ) ) {
		wp_send_json_error( array( 'message' => 'پترن مربوط به «' . $label . '» انتخاب نشده است.' ) );
	}

	$result = noghre_sms_send_pattern( $mobile, $pattern, $params );

	if ( is_wp_error( $result ) ) {
		wp_send_json_error( array( 'message' => '❌ ارسال ناموفق: ' . $result->get_error_message() ) );
	}

	wp_send_json_success( array( 'message' => '✅ پیامک تست «' . $label . '» با موفقیت به ' . $mobile . ' ارسال شد.' ) );
}
add_action( 'wp_ajax_noghre_test_sms', 'noghre_ajax_test_sms' );

/* ==========================================================================
 * بخش ۸ — جستجو در تمام دفترچه‌ها
 * ========================================================================== */

/**
 * جستجوی یک شماره در تمام دفترچه‌های تلفن.
 * @return array آرایه‌ای از دفترچه‌هایی که شماره در آن‌ها پیدا شده: [ ['id'=>, 'title'=>, 'count'=>], ... ]
 */
function noghre_sms_search_in_all_phonebooks( $mobile ) {
	if ( ! noghre_afra_is_ready() ) {
		return new WP_Error( 'noghre_sms_unavailable', 'افزونه افرا اس ام اس آماده نیست.' );
	}

	$phonebooks = noghre_sms_get_phonebooks();
	if ( is_wp_error( $phonebooks ) ) return $phonebooks;

	$found = array();
	foreach ( $phonebooks as $book ) {
		if ( empty( $book['id'] ) ) continue;
		if ( noghre_sms_number_exists( $mobile, $book['id'] ) ) {
			$found[] = $book;
		}
	}
	return $found;
}

/* ==========================================================================
 * بخش ۹ — حذف شماره از یک دفترچه خاص
 * ========================================================================== */

/**
 * حذف یک شماره از یک دفترچه تلفن خاص.
 * @return true|WP_Error
 */
function noghre_sms_remove_from_phonebook( $mobile, $phonebook_id ) {
	if ( ! noghre_afra_is_ready() ) {
		return new WP_Error( 'noghre_sms_unavailable', 'افزونه افرا اس ام اس آماده نیست.' );
	}
	if ( ! $phonebook_id ) {
		return new WP_Error( 'noghre_sms_no_phonebook', 'شناسه دفترچه تنظیم نشده است.' );
	}

	// تلاش با تابع مستقیم افزونه
	if ( function_exists( 'afra_sms_remove_contact_by_number' ) ) {
		$result = afra_sms_remove_contact_by_number( (int) $phonebook_id, $mobile );
		if ( ! is_wp_error( $result ) ) {
			return true;
		}
		noghre_log_error( 'حذف از دفترچه', 'شماره: ' . $mobile . ' | دفترچه: ' . $phonebook_id . ' | خطا: ' . $result->get_error_message() );
		return $result;
	}

	// تلاش با جستجو و حذف
	if ( function_exists( 'afra_sms_get_phonebook_contacts' ) && function_exists( 'afra_sms_remove_phonebook_contact' ) ) {
		$contacts = afra_sms_get_phonebook_contacts( (int) $phonebook_id, array( 'per_page' => 500 ) );
		if ( is_wp_error( $contacts ) ) return $contacts;

		$normalized = noghre_sms_normalize_mobile( $mobile );
		foreach ( (array) $contacts as $contact ) {
			if ( ! is_array( $contact ) ) continue;
			$stored = '';
			foreach ( array( 'number', 'mobile', 'phone', 'contact_number' ) as $key ) {
				if ( ! empty( $contact[ $key ] ) ) { $stored = $contact[ $key ]; break; }
			}
			if ( $stored && noghre_sms_normalize_mobile( $stored ) === $normalized ) {
				$contact_id = $contact['id'] ?? ( $contact['contact_id'] ?? 0 );
				if ( $contact_id ) {
					$rem = afra_sms_remove_phonebook_contact( (int) $phonebook_id, (int) $contact_id );
					return is_wp_error( $rem ) ? $rem : true;
				}
			}
		}
	}

	return new WP_Error( 'noghre_sms_remove_unsupported', 'حذف از دفترچه توسط افزونه پشتیبانی نمی‌شود.' );
}

/* ==========================================================================
 * بخش ۱۰ — AJAX: ثبت شماره مشتری از اپ موبایل
 * ========================================================================== */

function noghre_ajax_customer_signup() {
	// 🔑 بررسی nonce
	$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
	if ( ! wp_verify_nonce( $nonce, 'noghre_customer_signup' ) ) {
		wp_send_json_error( array( 'message' => 'نشست شما منقضی شده است. صفحه را دوباره بارگذاری کنید.' ) );
	}

	// 🔑 بررسی دسترسی مدیر (هم m و هم توکن قابل قبول)
	if ( ! noghre_is_admin_request() ) {
		wp_send_json_error( array( 'message' => 'دسترسی غیرمجاز.' ) );
	}

	$name   = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
	$mobile = isset( $_POST['mobile'] ) ? preg_replace( '/\D/', '', wp_unslash( $_POST['mobile'] ) ) : '';

	if ( mb_strlen( $name ) < 2 ) {
		wp_send_json_error( array( 'message' => 'لطفاً نام و نام خانوادگی را کامل وارد کنید.' ) );
	}
	if ( ! preg_match( '/^09\d{9}$/', $mobile ) ) {
		wp_send_json_error( array( 'message' => 'شماره موبایل باید ۱۱ رقم و با ۰۹ شروع شود.' ) );
	}

	if ( ! noghre_afra_is_ready() ) {
		$status = noghre_afra_get_status();
		wp_send_json_error( array( 'message' => $status['message'] ) );
	}

	$sms = noghre_get_sms_options();

	$discount_pb_id = ! empty( $sms['sms_phonebook_id'] ) ? (int) $sms['sms_phonebook_id'] : 0;
	$customer_pb_id = ! empty( $sms['sms_customer_club_phonebook_id'] ) ? (int) $sms['sms_customer_club_phonebook_id'] : 0;

	if ( ! $customer_pb_id ) {
		wp_send_json_error( array( 'message' => 'دفترچه مشتریان در تنظیمات پیامک انتخاب نشده است.' ) );
	}

	// ۱. جستجو در تمام دفترچه‌ها
	$found_books = noghre_sms_search_in_all_phonebooks( $mobile );
	if ( is_wp_error( $found_books ) ) {
		wp_send_json_error( array( 'message' => 'خطا در جستجوی دفترچه‌ها: ' . $found_books->get_error_message() ) );
	}

	$in_customer_club = false;
	$in_discount      = false;
	$other_books      = array();

	foreach ( $found_books as $book ) {
		if ( (int) $book['id'] === $customer_pb_id ) {
			$in_customer_club = true;
		} elseif ( $discount_pb_id && (int) $book['id'] === $discount_pb_id ) {
			$in_discount = true;
		} else {
			$other_books[] = $book;
		}
	}

	// ۲. اگر در دفترچه دیگری (نه مشتریان و نه تخفیف) هست → نوتیفیکیشن به مدیر
	$admin_notice = '';
	if ( ! empty( $other_books ) ) {
		$titles = array_map( function( $b ) { return $b['title']; }, $other_books );
		$admin_notice = 'این شماره قبلاً در دفترچه‌های زیر ثبت شده است: ' . implode( '، ', $titles );
	}

	// ۳. اگر در دفترچه مشتریان هست → خطا
	if ( $in_customer_club ) {
		wp_send_json_error( array(
			'message' => 'این شماره قبلاً در باشگاه مشتریان ثبت شده است. 💎' . ( $admin_notice ? ' | ' . $admin_notice : '' ),
		) );
	}

	// ۴. اگر در دفترچه تخفیف هست → حذف از تخفیف و افزودن به مشتریان (بدون خطا)
	if ( $in_discount && $discount_pb_id ) {
		$removed = noghre_sms_remove_from_phonebook( $mobile, $discount_pb_id );
		if ( is_wp_error( $removed ) ) {
			noghre_log_error( 'حذف از تخفیف هنگام ثبت در اپ', $removed->get_error_message() );
			// ادامه می‌دهیم، حتی اگر حذف نشد
		}
	}

	// ۵. افزودن به دفترچه مشتریان
	$added = noghre_sms_add_to_phonebook( $mobile, $name, $customer_pb_id );
	if ( is_wp_error( $added ) ) {
		wp_send_json_error( array( 'message' => 'خطا در افزودن به دفترچه مشتریان: ' . $added->get_error_message() ) );
	}

	// ۶. ارسال پیامک ثبت‌نام به مشتری (اگر فعال باشد)
	$sms_sent = false;
	if ( ! empty( $sms['sms_signup_enabled'] ) && ! empty( $sms['sms_pattern_signup'] ) ) {
		$params = array();
		if ( ! empty( $sms['sms_pattern_signup_var_name'] ) ) {
			$params[ $sms['sms_pattern_signup_var_name'] ] = $name;
		}
		$result = noghre_sms_send_pattern( $mobile, $sms['sms_pattern_signup'], $params );
		$sms_sent = ! is_wp_error( $result );
		if ( is_wp_error( $result ) ) {
			noghre_log_error( 'پیامک ثبت شماره مشتری', $result->get_error_message() );
		}
	}

	// ۷. ثبت در لاگ
	$signups = get_option( 'noghre_customer_signups', array() );
	if ( ! is_array( $signups ) ) $signups = array();
	$signups[] = array(
		'name'       => $name,
		'mobile'     => $mobile,
		'date'       => current_time( 'timestamp' ),
		'sms_sent'   => $sms_sent,
	);
	if ( count( $signups ) > 500 ) $signups = array_slice( $signups, -500 );
	update_option( 'noghre_customer_signups', $signups );

	// پاسخ موفق
	$message = 'شماره با موفقیت ثبت شد.';
	if ( $sms_sent ) $message .= ' پیامک تایید برای مشتری ارسال شد.';
	if ( $admin_notice ) $message .= ' | توجه: ' . $admin_notice;

	wp_send_json_success( array(
		'message'      => $message,
		'admin_notice' => $admin_notice,
		'sms_sent'     => $sms_sent,
	) );
}
add_action( 'wp_ajax_noghre_customer_signup', 'noghre_ajax_customer_signup' );
add_action( 'wp_ajax_nopriv_noghre_customer_signup', 'noghre_ajax_customer_signup' );