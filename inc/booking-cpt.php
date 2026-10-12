<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function noghre_register_booking_cpt() {
	register_post_type( 'noghre_booking', array(
		'label'        => 'رزروهای نوبت',
		'labels'       => array(
			'name'          => 'رزروهای نوبت',
			'singular_name' => 'رزرو نوبت',
			'menu_name'     => 'رزروهای نوبت',
		),
		'public'       => false,
		'show_ui'      => false,
		'supports'     => array( 'title' ),
		'capability_type' => 'post',
		'map_meta_cap' => true,
	) );
}
add_action( 'init', 'noghre_register_booking_cpt' );

function noghre_generate_tracking_code() {
	do {
		$code = strtoupper( substr( wp_generate_password( 10, false, false ), 0, 8 ) );
	} while ( noghre_get_booking_by_tracking_code( $code ) );
	return $code;
}

function noghre_get_booking_by_tracking_code( $code ) {
	$code  = sanitize_text_field( $code );
	if ( ! $code ) return null;
	$query = new WP_Query( array(
		'post_type'      => 'noghre_booking',
		'post_status'    => 'any',
		'posts_per_page' => 1,
		'meta_key'       => '_tracking_code',
		'meta_value'     => $code,
		'no_found_rows'  => true,
	) );
	return $query->have_posts() ? $query->posts[0] : null;
}

function noghre_create_booking( $data ) {
	$tracking_code = noghre_generate_tracking_code();

	$post_id = wp_insert_post( array(
		'post_type'   => 'noghre_booking',
		'post_status' => 'publish',
		'post_title'  => sprintf( 'رزرو %s — %s', $tracking_code, $data['customer_name'] ),
	) );

	if ( is_wp_error( $post_id ) || ! $post_id ) return false;

	$meta = array(
		'_tracking_code'        => $tracking_code,
		'_customer_name'        => $data['customer_name'],
		'_customer_mobile'      => $data['customer_mobile'],
		'_branch'               => $data['branch'],
		'_branch_manager_mobile'=> $data['branch_manager_mobile'],
		'_services'             => $data['services'],
		'_visit_day_display'    => $data['visit_day_display'],
		'_visit_day_gregorian'  => $data['visit_day_gregorian'],
		'_preferred_time'       => $data['preferred_time'],
		'_discount_code'        => $data['discount_code'],
		'_payment_required'     => $data['payment_required'],
		'_payment_amount'       => $data['payment_amount'],
		'_payment_status'       => $data['payment_status'],
		'_zarinpal_authority'   => $data['zarinpal_authority'] ?? '',
		'_zarinpal_ref_id'      => '',
		'_status'               => 'pending',
		'_admin_note'           => '',
		'_created_at'           => current_time( 'timestamp' ),
	);

	foreach ( $meta as $key => $value ) {
		update_post_meta( $post_id, $key, $value );
	}

	return array( 'post_id' => $post_id, 'tracking_code' => $tracking_code );
}

function noghre_booking_status_label( $status ) {
	$labels = array(
		'pending'   => 'در انتظار بررسی',
		'confirmed' => 'تایید شده',
		'edited'    => 'ویرایش شده',
		'used'      => 'استفاده‌شده',
		'unused'    => 'استفاده نشده',
	);
	return $labels[ $status ] ?? $status;
}