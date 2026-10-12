<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * بررسی رزروهای منقضی
 *
 * هر بار که سایت لود می‌شه، رزروهای pending/confirmed/edited که از تاریخشون گذشته
 * رو به unused تبدیل می‌کنه.
 */
function noghre_check_expired_bookings() {
	$posts = get_posts( array(
		'post_type'      => 'noghre_booking',
		'post_status'    => 'any',
		'posts_per_page' => -1,
		'meta_query'     => array(
			array(
				'key'     => '_status',
				'value'   => array( 'pending', 'confirmed', 'edited' ),
				'compare' => 'IN',
			),
		),
	) );

	if ( empty( $posts ) ) return;

	$now = current_time( 'timestamp' );

	foreach ( $posts as $post ) {
		$id             = $post->ID;
		$visit_day      = get_post_meta( $id, '_visit_day_gregorian', true );
		$preferred_time = get_post_meta( $id, '_preferred_time', true );

		if ( ! $visit_day ) continue;

		$visit_ts = strtotime( $visit_day . ' ' . ( $preferred_time ?: '00:00' ) );
		if ( ! $visit_ts ) continue;

		if ( ( $now - $visit_ts ) > DAY_IN_SECONDS ) {
			update_post_meta( $id, '_status', 'unused' );
		}
	}
}
add_action( 'init', 'noghre_check_expired_bookings' );