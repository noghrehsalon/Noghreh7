<?php
/**
 * توابع کمکی مشترک پنل ادمین و فرانت
 *
 * @package Noghre
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/* ==========================================================================
 * بخش ۱ — کدهای تخفیف
 * ========================================================================== */
function noghre_get_discount_codes() {
	$items = get_option( 'noghre_discount_codes', array() );
	return is_array( $items ) ? array_values( $items ) : array();
}

function noghre_validate_discount_code( $code, $mobile, $scope = 'booking', $services = array() ) {
	$code = strtoupper( trim( $code ) );
	if ( ! $code ) return new WP_Error( 'noghre_discount_empty', 'کد تخفیف را وارد کنید.' );

	$codes = noghre_get_discount_codes();
	foreach ( $codes as $index => $c ) {
		if ( strtoupper( $c['code'] ) !== $code ) continue;

		if ( empty( $c['active'] ) ) {
			return new WP_Error( 'noghre_discount_inactive', 'این کد تخفیف غیرفعال است.' );
		}
		if ( ! in_array( $scope, (array) ( $c['scope'] ?? array() ), true ) ) {
			return new WP_Error( 'noghre_discount_scope', 'این کد تخفیف برای این بخش معتبر نیست.' );
		}
		if ( ! empty( $c['has_expiry'] ) && ! empty( $c['expiry_date'] ) ) {
			if ( strtotime( $c['expiry_date'] . ' 23:59:59' ) < current_time( 'timestamp' ) ) {
				return new WP_Error( 'noghre_discount_expired', 'این کد تخفیف منقضی شده است.' );
			}
		}
		if ( empty( $c['unlimited'] ) && (int) ( $c['used_count'] ?? 0 ) >= (int) ( $c['max_uses'] ?? 0 ) ) {
			return new WP_Error( 'noghre_discount_maxed', 'ظرفیت استفاده از این کد تخفیف تکمیل شده است.' );
		}
		if ( ! empty( $c['once_per_mobile'] ) && $mobile && in_array( $mobile, (array) ( $c['used_mobiles'] ?? array() ), true ) ) {
			return new WP_Error( 'noghre_discount_used', 'شما قبلاً از این کد تخفیف استفاده کرده‌اید.' );
		}

		$code_services = (array) ( $c['services'] ?? array() );
		if ( ! empty( $code_services ) ) {
			$overlap = array_intersect( array_map( 'sanitize_text_field', (array) $services ), $code_services );
			if ( empty( $overlap ) ) {
				return new WP_Error(
					'noghre_discount_service_mismatch',
					'این کد تخفیف فقط در خدمات [ ' . implode( ' - ', $code_services ) . ' ] قابل استفاده می‌باشد.'
				);
			}
		}

		return array( 'index' => $index, 'code' => $c );
	}

	return new WP_Error( 'noghre_discount_notfound', 'کد تخفیف معتبر نیست.' );
}

function noghre_consume_discount_code( $code, $mobile ) {
	$code = strtoupper( trim( $code ) );
	if ( ! $code ) return;
	$codes = noghre_get_discount_codes();
	foreach ( $codes as &$c ) {
		if ( strtoupper( $c['code'] ) !== $code ) continue;
		$c['used_count'] = (int) ( $c['used_count'] ?? 0 ) + 1;
		if ( $mobile ) {
			$c['used_mobiles']   = (array) ( $c['used_mobiles'] ?? array() );
			$c['used_mobiles'][] = $mobile;
		}
		break;
	}
	unset( $c );
	update_option( 'noghre_discount_codes', $codes );
}

function noghre_discount_code_description( $c ) {
	if ( $c['type'] === 'percent' ) {
		return noghre_to_persian_digits( $c['value'] ) . '٪ تخفیف';
	}
	return noghre_to_persian_digits( number_format_i18n( $c['value'] ) ) . ' تومان تخفیف';
}

/* ==========================================================================
 * بخش ۲ — لاگ خطاها
 * ========================================================================== */
function noghre_log_error( $context, $message ) {
	$log = get_option( 'noghre_error_log', array() );
	if ( ! is_array( $log ) ) $log = array();

	$log[] = array(
		'time'    => current_time( 'timestamp' ),
		'context' => sanitize_text_field( $context ),
		'message' => is_string( $message ) ? $message : wp_json_encode( $message ),
	);

	if ( count( $log ) > 300 ) {
		$log = array_slice( $log, -300 );
	}
	update_option( 'noghre_error_log', $log );
}

/* ==========================================================================
 * بخش ۳ — شناسه مدیریت
 * ========================================================================== */

function noghre_get_admin_access_key() {
	$key = get_option( 'noghre_admin_access_key', '' );

	// سازگاری با نسخه قدیمی
	if ( empty( $key ) ) {
		$general = get_option( 'noghre_general_options', array() );
		if ( is_array( $general ) && ! empty( $general['admin_access_key'] ) ) {
			$key = $general['admin_access_key'];
		}
	}

	return is_string( $key ) ? $key : '';
}

function noghre_set_admin_access_key( $key ) {
	$key = is_string( $key ) ? $key : '';
	update_option( 'noghre_admin_access_key', $key, false );

	// پاک کردن مقدار قدیمی از general_options
	$general = get_option( 'noghre_general_options', array() );
	if ( is_array( $general ) && isset( $general['admin_access_key'] ) ) {
		unset( $general['admin_access_key'] );
		update_option( 'noghre_general_options', $general );
	}
}

function noghre_is_admin_access( $provided ) {
	$key = noghre_get_admin_access_key();
	return ( $key !== '' && is_string( $provided ) && hash_equals( $key, $provided ) );
}

function noghre_generate_admin_access_key() {
	$chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
	$key = '';
	for ( $i = 0; $i < 8; $i++ ) {
		$key .= $chars[ wp_rand( 0, strlen( $chars ) - 1 ) ];
	}
	return $key;
}

/* ==========================================================================
 * بخش ۴ — توابع کمکی نمایش لیست رزروها
 * ========================================================================== */
function noghre_bookings_sort_columns() {
	return array(
		'tracking_code' => 'کد پیگیری',
		'name'          => 'نام',
		'mobile'        => 'موبایل',
		'branch'        => 'شعبه',
		'services'      => 'خدمات',
		'visit'         => 'روز و ساعت',
		'discount_code' => 'کد تخفیف',
		'payment'       => 'پرداخت',
		'status'        => 'وضعیت',
		'created'       => 'تاریخ ثبت',
	);
}

/* ==========================================================================
 * بخش ۵ — پردازش فرم‌های رزرو (شعبه/خدمات/ساعات)
 * ========================================================================== */
function noghre_handle_booking_settings_post() {
	if ( ! is_admin() ) return;
	if ( ! current_user_can( 'manage_options' ) ) return;
	if ( ! isset( $_GET['page'] ) || $_GET['page'] !== 'noghre-panel' ) return;
	if ( ! isset( $_GET['tab'] ) || $_GET['tab'] !== 'booking' ) return;

	/* ============ ذخیره شعبه‌ها ============ */
	if ( isset( $_POST['noghre_branches_nonce'] ) && wp_verify_nonce( $_POST['noghre_branches_nonce'], 'noghre_save_branches' ) ) {
		$rows = array();
		if ( ! empty( $_POST['branches'] ) && is_array( $_POST['branches'] ) ) {
			foreach ( $_POST['branches'] as $row ) {
				$name = sanitize_text_field( $row['name'] ?? '' );
				if ( $name === '' ) continue;
				$rows[] = array(
					'name'           => $name,
					'manager_mobile' => preg_replace( '/\D/', '', $row['manager_mobile'] ?? '' ),
				);
			}
		}
		update_option( 'noghre_booking_branches', $rows );

		wp_safe_redirect( add_query_arg(
			array( 'page' => 'noghre-panel', 'tab' => 'booking', 'sub' => 'settings', 'noghre_msg' => 'branches_saved' ),
			admin_url( 'admin.php' )
		) );
		exit;
	}

	/* ============ ذخیره خدمات ============ */
	if ( isset( $_POST['noghre_services_nonce'] ) && wp_verify_nonce( $_POST['noghre_services_nonce'], 'noghre_save_services' ) ) {
		$rows = array();
		if ( ! empty( $_POST['services'] ) && is_array( $_POST['services'] ) ) {
			foreach ( $_POST['services'] as $row ) {
				$name = sanitize_text_field( $row['name'] ?? '' );
				if ( $name === '' ) continue;
				$rows[] = array( 'name' => $name );
			}
		}
		update_option( 'noghre_booking_services', $rows );

		wp_safe_redirect( add_query_arg(
			array( 'page' => 'noghre-panel', 'tab' => 'booking', 'sub' => 'settings', 'noghre_msg' => 'services_saved' ),
			admin_url( 'admin.php' )
		) );
		exit;
	}

	/* ============ ذخیره ساعات ============ */
	if ( isset( $_POST['noghre_hours_nonce'] ) && wp_verify_nonce( $_POST['noghre_hours_nonce'], 'noghre_save_hours' ) ) {
		$rows = array();
		if ( ! empty( $_POST['hours'] ) && is_array( $_POST['hours'] ) ) {
			foreach ( $_POST['hours'] as $row ) {
				$time = sanitize_text_field( $row['time'] ?? '' );
				if ( $time === '' ) continue;
				$rows[] = array( 'time' => $time );
			}
		}
		update_option( 'noghre_booking_hours', $rows );

		wp_safe_redirect( add_query_arg(
			array( 'page' => 'noghre-panel', 'tab' => 'booking', 'sub' => 'settings', 'noghre_msg' => 'hours_saved' ),
			admin_url( 'admin.php' )
		) );
		exit;
	}
}
add_action( 'admin_init', 'noghre_handle_booking_settings_post', 5 );

/* ==========================================================================
 * بخش ۶ — پردازش فرم‌های کد تخفیف
 * ========================================================================== */
function noghre_handle_discount_codes_post() {
	if ( ! is_admin() ) return;
	if ( ! current_user_can( 'manage_options' ) ) return;
	if ( ! isset( $_GET['page'] ) || $_GET['page'] !== 'noghre-panel' ) return;
	if ( ! isset( $_GET['tab'] ) || $_GET['tab'] !== 'booking' ) return;

	if ( isset( $_GET['sub'] ) && $_GET['sub'] === 'discount' && isset( $_POST['noghre_discount_nonce'] ) && wp_verify_nonce( $_POST['noghre_discount_nonce'], 'noghre_save_discount_code' ) ) {
		$codes = noghre_get_discount_codes();
		$edit_index = isset( $_POST['edit_index'] ) && $_POST['edit_index'] !== '' ? absint( $_POST['edit_index'] ) : null;
		$new_code = array(
			'code'            => strtoupper( sanitize_text_field( $_POST['code'] ?? '' ) ),
			'type'            => ( ( $_POST['type'] ?? '' ) === 'fixed' ) ? 'fixed' : 'percent',
			'value'           => max( 0, (float) ( $_POST['value'] ?? 0 ) ),
			'unlimited'       => isset( $_POST['unlimited'] ) ? 1 : 0,
			'max_uses'        => isset( $_POST['unlimited'] ) ? 0 : max( 0, absint( $_POST['max_uses'] ?? 0 ) ),
			'has_expiry'      => isset( $_POST['has_expiry'] ) ? 1 : 0,
			'expiry_date'     => noghre_jalali_string_to_gregorian_string( $_POST['expiry_date_jalali'] ?? '' ),
			'once_per_mobile' => isset( $_POST['once_per_mobile'] ) ? 1 : 0,
			'scope'           => array_values( array_intersect( (array) ( $_POST['scope'] ?? array() ), array( 'booking', 'store' ) ) ),
			'services'        => isset( $_POST['all_services'] ) ? array() : array_values( array_map( 'sanitize_text_field', (array) ( $_POST['services'] ?? array() ) ) ),
			'active'          => isset( $_POST['active'] ) ? 1 : 0,
			'used_count'      => 0,
			'used_mobiles'    => array(),
		);

		if ( $new_code['code'] !== '' ) {
			if ( null !== $edit_index && isset( $codes[ $edit_index ] ) ) {
				$new_code['used_count']   = $codes[ $edit_index ]['used_count'] ?? 0;
				$new_code['used_mobiles'] = $codes[ $edit_index ]['used_mobiles'] ?? array();
				$codes[ $edit_index ] = $new_code;
			} else {
				$codes[] = $new_code;
			}
			update_option( 'noghre_discount_codes', $codes );

			wp_safe_redirect( add_query_arg(
				array( 'page' => 'noghre-panel', 'tab' => 'booking', 'sub' => 'discount', 'noghre_msg' => 'discount_saved' ),
				admin_url( 'admin.php' )
			) );
			exit;
		}
	}

	if ( isset( $_GET['sub'] ) && $_GET['sub'] === 'discount' && isset( $_GET['delete'] ) && isset( $_GET['_wpnonce'] ) && wp_verify_nonce( $_GET['_wpnonce'], 'noghre_delete_discount_code' ) ) {
		$codes = noghre_get_discount_codes();
		$del_index = absint( $_GET['delete'] );
		if ( isset( $codes[ $del_index ] ) ) {
			unset( $codes[ $del_index ] );
			update_option( 'noghre_discount_codes', array_values( $codes ) );

			wp_safe_redirect( add_query_arg(
				array( 'page' => 'noghre-panel', 'tab' => 'booking', 'sub' => 'discount', 'noghre_msg' => 'discount_deleted' ),
				admin_url( 'admin.php' )
			) );
			exit;
		}
	}
}
add_action( 'admin_init', 'noghre_handle_discount_codes_post', 5 );

/* ==========================================================================
 * بخش ۷ — پردازش فرم‌های محتوا (چیدمان / پرسنل / سوالات / مجوزها)
 * ========================================================================== */
function noghre_handle_content_post() {
	if ( ! is_admin() ) return;
	if ( ! current_user_can( 'manage_options' ) ) return;
	if ( ! isset( $_GET['page'] ) || $_GET['page'] !== 'noghre-panel' ) return;
	if ( ! isset( $_GET['tab'] ) || $_GET['tab'] !== 'content' ) return;

	$sub = isset( $_GET['sub'] ) ? sanitize_key( $_GET['sub'] ) : 'sections';

	if ( $sub === 'sections' && isset( $_POST['noghre_sections_nonce'] ) && wp_verify_nonce( $_POST['noghre_sections_nonce'], 'noghre_save_sections' ) ) {
		$order  = isset( $_POST['section_order'] ) ? array_filter( array_map( 'sanitize_text_field', explode( ',', $_POST['section_order'] ) ) ) : array();
		$types  = isset( $_POST['section_type'] ) && is_array( $_POST['section_type'] ) ? $_POST['section_type'] : array();
		$labels = noghre_section_labels();
		$rows   = array();

		foreach ( $order as $id ) {
			$type = sanitize_key( $types[ $id ] ?? '' );
			if ( ! isset( $labels[ $type ] ) ) continue;
			$row = array(
				'id'      => sanitize_key( $id ),
				'key'     => $type,
				'enabled' => isset( $_POST['enabled'][ $id ] ) ? 1 : 0,
				'visibility' => array(
					'desktop' => isset( $_POST['visibility'][ $id ]['desktop'] ) ? 1 : 0,
					'tablet'  => isset( $_POST['visibility'][ $id ]['tablet'] ) ? 1 : 0,
					'mobile'  => isset( $_POST['visibility'][ $id ]['mobile'] ) ? 1 : 0,
				),
			);
			if ( $type === 'page_builder' ) {
				$row['page_id'] = absint( $_POST['page_builder_page_id'][ $id ] ?? 0 );
			}
			$rows[] = $row;
		}
		update_option( 'noghre_sections', $rows );

		wp_safe_redirect( add_query_arg(
			array( 'page' => 'noghre-panel', 'tab' => 'content', 'sub' => 'sections', 'noghre_msg' => 'sections_saved' ),
			admin_url( 'admin.php' )
		) );
		exit;
	}

	if ( $sub === 'team' && isset( $_POST['noghre_team_nonce'] ) && wp_verify_nonce( $_POST['noghre_team_nonce'], 'noghre_save_team' ) ) {
		$rows = array();
		if ( ! empty( $_POST['team'] ) && is_array( $_POST['team'] ) ) {
			foreach ( $_POST['team'] as $row ) {
				$name = sanitize_text_field( $row['name'] ?? '' );
				if ( $name === '' ) continue;
				$rows[] = array(
					'name'      => $name,
					'services'  => sanitize_text_field( $row['services'] ?? '' ),
					'instagram' => esc_url_raw( $row['instagram'] ?? '' ),
					'telegram'  => esc_url_raw( $row['telegram'] ?? '' ),
					'whatsapp'  => esc_url_raw( $row['whatsapp'] ?? '' ),
					'image_id'  => absint( $row['image_id'] ?? 0 ),
				);
			}
		}
		update_option( 'noghre_team_members', $rows );

		wp_safe_redirect( add_query_arg(
			array( 'page' => 'noghre-panel', 'tab' => 'content', 'sub' => 'team', 'noghre_msg' => 'team_saved' ),
			admin_url( 'admin.php' )
		) );
		exit;
	}

	if ( $sub === 'faq' && isset( $_POST['noghre_faq_nonce'] ) && wp_verify_nonce( $_POST['noghre_faq_nonce'], 'noghre_save_faq' ) ) {
		$rows = array();
		if ( ! empty( $_POST['faq'] ) && is_array( $_POST['faq'] ) ) {
			foreach ( $_POST['faq'] as $row ) {
				$q = sanitize_text_field( $row['question'] ?? '' );
				if ( $q === '' ) continue;
				$rows[] = array(
					'question' => $q,
					'answer'   => sanitize_textarea_field( $row['answer'] ?? '' ),
				);
			}
		}
		update_option( 'noghre_faqs', $rows );

		wp_safe_redirect( add_query_arg(
			array( 'page' => 'noghre-panel', 'tab' => 'content', 'sub' => 'faq', 'noghre_msg' => 'faq_saved' ),
			admin_url( 'admin.php' )
		) );
		exit;
	}

	if ( $sub === 'licenses' && isset( $_POST['noghre_licenses_nonce'] ) && wp_verify_nonce( $_POST['noghre_licenses_nonce'], 'noghre_save_licenses' ) ) {
		$rows = array();
		if ( ! empty( $_POST['licenses'] ) && is_array( $_POST['licenses'] ) ) {
			foreach ( $_POST['licenses'] as $row ) {
				$type  = ( isset( $row['type'] ) && $row['type'] === 'code' ) ? 'code' : 'image';
				$label = sanitize_text_field( $row['admin_label'] ?? '' );
				$img   = absint( $row['image_id'] ?? 0 );
				$code  = isset( $row['code'] ) ? wp_unslash( $row['code'] ) : '';

				if ( $label === '' && ! $img && $code === '' ) continue;

				$rows[] = array(
					'type'        => $type,
					'admin_label' => $label,
					'image_id'    => $img,
					'caption'     => sanitize_text_field( $row['caption'] ?? '' ),
					'link_url'    => esc_url_raw( $row['link_url'] ?? '' ),
					'link_target' => ( isset( $row['link_target'] ) && $row['link_target'] === '_self' ) ? '_self' : '_blank',
					'code'        => $code,
				);
			}
		}
		update_option( 'noghre_licenses', $rows );

		wp_safe_redirect( add_query_arg(
			array( 'page' => 'noghre-panel', 'tab' => 'content', 'sub' => 'licenses', 'noghre_msg' => 'licenses_saved' ),
			admin_url( 'admin.php' )
		) );
		exit;
	}
}
add_action( 'admin_init', 'noghre_handle_content_post', 5 );

/* ==========================================================================
 * بخش ۸ — پاک‌کردن تاریخچه باشگاه تخفیف
 * ========================================================================== */
function noghre_handle_clear_discount_signups() {
	if ( ! current_user_can( 'manage_options' ) ) wp_die( 'دسترسی غیرمجاز' );
	check_admin_referer( 'noghre_clear_discount_signups' );
	delete_option( 'noghre_discount_signups' );

	wp_safe_redirect( add_query_arg(
		array( 'page' => 'noghre-panel', 'tab' => 'sms', 'noghre_msg' => 'history_cleared' ),
		admin_url( 'admin.php' )
	) );
	exit;
}
add_action( 'admin_post_noghre_clear_discount_signups', 'noghre_handle_clear_discount_signups' );

/* ==========================================================================
 * بخش ۹ — حذف رزرو
 * ========================================================================== */
function noghre_handle_booking_delete() {
	if ( ! current_user_can( 'manage_options' ) ) wp_die( 'دسترسی غیرمجاز' );
	check_admin_referer( 'noghre_booking_delete' );

	$booking_id = absint( $_GET['booking_id'] ?? 0 );
	if ( $booking_id && get_post_type( $booking_id ) === 'noghre_booking' ) {
		wp_delete_post( $booking_id, true );
	}

	wp_safe_redirect( add_query_arg(
		array( 'page' => 'noghre-panel', 'tab' => 'booking', 'sub' => 'bookings', 'noghre_msg' => 'booking_deleted' ),
		admin_url( 'admin.php' )
	) );
	exit;
}
add_action( 'admin_post_noghre_booking_delete', 'noghre_handle_booking_delete' );

/* ==========================================================================
 * بخش ۱۰ — تولید شناسه مدیریت
 * ========================================================================== */
function noghre_handle_generate_admin_key() {
	if ( ! current_user_can( 'manage_options' ) ) wp_die( 'دسترسی غیرمجاز' );
	check_admin_referer( 'noghre_generate_admin_key' );

	$new_key = noghre_generate_admin_access_key();

	// ✅ ذخیره در option جداگانه
	noghre_set_admin_access_key( $new_key );

	// ✅ باطل کردن همه توکن‌های فعال
	if ( function_exists( 'noghre_revoke_all_access_tokens' ) ) {
		noghre_revoke_all_access_tokens();
	}

	wp_safe_redirect( add_query_arg(
		array( 'page' => 'noghre-panel', 'tab' => 'advanced', 'sub' => 'access_key', 'noghre_msg' => 'admin_key_generated' ),
		admin_url( 'admin.php' )
	) );
	exit;
}
add_action( 'admin_post_noghre_generate_admin_key', 'noghre_handle_generate_admin_key' );

/* ==========================================================================
 * بخش ۱۱ — حذف کل گزارش خطاها
 * ========================================================================== */
function noghre_handle_clear_error_log() {
	if ( ! current_user_can( 'manage_options' ) ) wp_die( 'دسترسی غیرمجاز' );
	check_admin_referer( 'noghre_clear_error_log' );
	delete_option( 'noghre_error_log' );

	wp_safe_redirect( add_query_arg(
		array( 'page' => 'noghre-panel', 'tab' => 'advanced', 'sub' => 'errors', 'noghre_msg' => 'error_log_cleared' ),
		admin_url( 'admin.php' )
	) );
	exit;
}
add_action( 'admin_post_noghre_clear_error_log', 'noghre_handle_clear_error_log' );

/* ==========================================================================
 * بخش ۱۲ — پشتیبان‌گیری جامع
 * ========================================================================== */

function noghre_get_all_theme_options() {
	global $wpdb;
	$options = array();
	$results = $wpdb->get_results( "SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE 'noghre_%'", ARRAY_A );
	foreach ( $results as $row ) {
		$key = $row['option_name'];
		$options[ $key ] = get_option( $key, null );
	}
	return $options;
}

function noghre_get_all_theme_posts() {
	$posts = array();
	$query = new WP_Query( array(
		'post_type'      => 'noghre_booking',
		'post_status'    => 'any',
		'posts_per_page' => -1,
	) );
	foreach ( $query->posts as $post ) {
		$meta = get_post_meta( $post->ID );
		$posts[] = array(
			'ID'           => $post->ID,
			'post_title'   => $post->post_title,
			'post_content' => $post->post_content,
			'post_date'    => $post->post_date,
			'post_status'  => $post->post_status,
			'meta'         => $meta,
		);
	}
	return $posts;
}

function noghre_handle_export() {
	if ( ! current_user_can( 'manage_options' ) ) wp_die( 'دسترسی غیرمجاز' );
	check_admin_referer( 'noghre_export_settings' );

	$data = array(
		'version'     => defined( 'NOGHRE_VERSION' ) ? NOGHRE_VERSION : '1.0.0',
		'exported_at' => current_time( 'timestamp' ),
		'options'     => noghre_get_all_theme_options(),
		'bookings'    => noghre_get_all_theme_posts(),
	);

	nocache_headers();
	header( 'Content-Type: application/json; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename="ariatin-noghreh-backup-' . date( 'Y-m-d-His' ) . '.json"' );
	echo wp_json_encode( $data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT );
	exit;
}
add_action( 'admin_post_noghre_export_settings', 'noghre_handle_export' );

/**
 * 🔑 ذخیره مستقیم یک option با دور زدن sanitize_callback
 *
 * این تابع از دیتابیس به‌طور مستقیم استفاده می‌کنه تا مطمئن بشه مقادیر
 * دقیقاً همونطور که در فایل بکاپ هستن ذخیره می‌شن و هیچ sanitize یا filter
 * روی اون‌ها اعمال نمی‌شه.
 */
function noghre_import_option_direct( $key, $value ) {
	global $wpdb;

	// مقدار رو serialize کن (مثل خود وردپرس)
	$serialized_value = maybe_serialize( $value );

	// بررسی وجود option
	$option_id = $wpdb->get_var( $wpdb->prepare(
		"SELECT option_id FROM {$wpdb->options} WHERE option_name = %s LIMIT 1",
		$key
	) );

	if ( $option_id ) {
		// update
		$wpdb->update(
			$wpdb->options,
			array( 'option_value' => $serialized_value ),
			array( 'option_id' => $option_id ),
			array( '%s' ),
			array( '%d' )
		);
	} else {
		// insert
		$wpdb->insert(
			$wpdb->options,
			array(
				'option_name'  => $key,
				'option_value' => $serialized_value,
				'autoload'     => 'yes',
			),
			array( '%s', '%s', '%s' )
		);
	}

	// پاک کردن کش
	wp_cache_delete( $key, 'options' );
	wp_cache_delete( 'alloptions', 'options' );
	wp_cache_delete( 'notoptions', 'options' );

	// اجرای action استاندارد وردپرس (برای سازگاری با سایر بخش‌ها)
	do_action( 'update_option', $key, $value, $value );
}

function noghre_handle_import() {
	if ( ! current_user_can( 'manage_options' ) ) wp_die( 'دسترسی غیرمجاز' );
	check_admin_referer( 'noghre_import_settings' );

	if ( empty( $_FILES['noghre_import_file']['tmp_name'] ) ) {
		wp_safe_redirect( add_query_arg( array( 'page' => 'noghre-panel', 'tab' => 'advanced', 'sub' => 'backup', 'noghre_msg' => 'import_empty' ), admin_url( 'admin.php' ) ) );
		exit;
	}

	$content = file_get_contents( $_FILES['noghre_import_file']['tmp_name'] );
	$data    = json_decode( $content, true );

	if ( ! is_array( $data ) ) {
		wp_safe_redirect( add_query_arg( array( 'page' => 'noghre-panel', 'tab' => 'advanced', 'sub' => 'backup', 'noghre_msg' => 'import_invalid' ), admin_url( 'admin.php' ) ) );
		exit;
	}

	$restored_count = 0;

	// 🔑 حالت اول: ساختار جدید با کلید options
	if ( isset( $data['options'] ) && is_array( $data['options'] ) ) {
		foreach ( $data['options'] as $key => $value ) {
			if ( strpos( $key, 'noghre_' ) !== 0 ) continue;
			noghre_import_option_direct( $key, $value );
			$restored_count++;
		}
	}
	// 🔑 حالت دوم: ساختار قدیمی که کلیدها در ریشه فایل بودن
	else {
		foreach ( noghre_backup_option_keys() as $key ) {
			if ( array_key_exists( $key, $data ) ) {
				noghre_import_option_direct( $key, $data[ $key ] );
				$restored_count++;
			}
		}
	}

	// بازگردانی رزروها
	if ( isset( $data['bookings'] ) && is_array( $data['bookings'] ) ) {
		foreach ( $data['bookings'] as $booking ) {
			if ( empty( $booking['ID'] ) ) continue;

			$existing = get_post( $booking['ID'] );
			if ( $existing ) {
				wp_update_post( array(
					'ID'           => $booking['ID'],
					'post_title'   => $booking['post_title'],
					'post_content' => $booking['post_content'],
					'post_status'  => $booking['post_status'],
				) );
			} else {
				$new_id = wp_insert_post( array(
					'import_id'    => $booking['ID'],
					'post_type'    => 'noghre_booking',
					'post_title'   => $booking['post_title'],
					'post_content' => $booking['post_content'],
					'post_date'    => $booking['post_date'],
					'post_status'  => $booking['post_status'],
				) );
				if ( is_wp_error( $new_id ) ) continue;
				$booking['ID'] = $new_id;
			}

			if ( ! empty( $booking['meta'] ) && is_array( $booking['meta'] ) ) {
				foreach ( $booking['meta'] as $meta_key => $meta_values ) {
					delete_post_meta( $booking['ID'], $meta_key );
					foreach ( (array) $meta_values as $meta_value ) {
						add_post_meta( $booking['ID'], $meta_key, maybe_unserialize( $meta_value ) );
					}
				}
			}
		}
	}

	wp_safe_redirect( add_query_arg( array(
		'page'       => 'noghre-panel',
		'tab'        => 'advanced',
		'sub'        => 'backup',
		'noghre_msg' => 'import_success',
		'restored'   => $restored_count,
	), admin_url( 'admin.php' ) ) );
	exit;
}
add_action( 'admin_post_noghre_import_settings', 'noghre_handle_import' );

function noghre_handle_reset() {
	if ( ! current_user_can( 'manage_options' ) ) wp_die( 'دسترسی غیرمجاز' );
	check_admin_referer( 'noghre_reset_settings' );

	foreach ( noghre_backup_option_keys() as $key ) {
		delete_option( $key );
	}

	wp_safe_redirect( add_query_arg( array( 'page' => 'noghre-panel', 'tab' => 'advanced', 'sub' => 'backup', 'noghre_msg' => 'reset_success' ), admin_url( 'admin.php' ) ) );
	exit;
}
add_action( 'admin_post_noghre_reset_settings', 'noghre_handle_reset' );

/**
 * 🔑 لیست کامل optionهایی که در پشتیبان‌گیری و بازگردانی در نظر گرفته می‌شن
 * (نسخه قبلی ناقص بود و چندین option مهم رو نداشت)
 */
function noghre_backup_option_keys() {
	return array(
		'noghre_general_options',
		'noghre_admin_access_key',
		'noghre_sections',
		'noghre_team_members',
		'noghre_faqs',
		'noghre_licenses',
		'noghre_sms_options',
		'noghre_colors',
		'noghre_booking_options',
		'noghre_booking_branches',
		'noghre_booking_services',
		'noghre_booking_hours',
		'noghre_zarinpal_options',
		'noghre_discount_codes',
		'noghre_access_tokens',
		'noghre_app_options',
		'noghre_uploaded_fonts',
		'noghre_discount_signups',
		'noghre_customer_signups',
		'noghre_error_log',
	);
}