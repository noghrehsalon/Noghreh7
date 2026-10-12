<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/* ============================================================
 * شورت‌کد لیست رزروها + مرتب‌سازی + فیلتر + صفحه‌بندی
 * ============================================================ */
function noghre_shortcode_booking_list() {
	if ( ! noghre_is_admin_request() ) {
		return '';
	}

	$ver = defined( 'NOGHRE_VERSION' ) ? NOGHRE_VERSION : '1.0.0';

	/* 🔑 enqueue اسکریپت اختصاصی لیست رزروها (بدون وابستگی) */
	wp_enqueue_script(
		'noghre-booking-list',
		NOGHRE_URI . '/assets/js/booking-list.js',
		array(),
		$ver,
		true
	);

	$all_posts = get_posts( array(
		'post_type'      => 'noghre_booking',
		'post_status'    => 'any',
		'posts_per_page' => -1,
	) );

	$all_rows = array();
	foreach ( $all_posts as $post ) {
		$id = $post->ID;
		$status = get_post_meta( $id, '_status', true );
		$sms_sent = get_post_meta( $id, '_sms_sent_' . $status, true );

		$all_rows[] = array(
			'id'            => $id,
			'tracking_code' => get_post_meta( $id, '_tracking_code', true ),
			'name'          => get_post_meta( $id, '_customer_name', true ),
			'mobile'        => get_post_meta( $id, '_customer_mobile', true ),
			'branch'        => get_post_meta( $id, '_branch', true ),
			'visit_display' => get_post_meta( $id, '_visit_day_display', true ) . ' — ساعت ' . noghre_to_persian_digits( get_post_meta( $id, '_preferred_time', true ) ),
			'status'        => $status,
			'sms_status'    => $sms_sent,
			'created'       => (int) get_post_meta( $id, '_created_at', true ),
		);
	}

	$columns = array(
		'tracking_code' => 'کد پیگیری',
		'name'          => 'نام',
		'mobile'        => 'موبایل',
		'branch'        => 'شعبه',
		'visit'         => 'روز و ساعت',
		'status'        => 'وضعیت',
		'sms'           => 'پیامک',
		'created'       => 'تاریخ ثبت',
	);

	$status_filter = isset( $_GET['status_filter'] ) ? sanitize_key( $_GET['status_filter'] ) : '';

	$status_options = array(
		''          => 'همه وضعیت‌ها',
		'pending'   => 'در انتظار بررسی',
		'confirmed' => 'تایید شده',
		'edited'    => 'ویرایش شده',
		'used'      => 'استفاده‌شده',
		'unused'    => 'استفاده نشده',
	);

	if ( $status_filter && isset( $status_options[ $status_filter ] ) ) {
		$all_rows = array_values( array_filter( $all_rows, function( $r ) use ( $status_filter ) {
			return $r['status'] === $status_filter;
		} ) );
	}

	$orderby = isset( $_GET['orderby'] ) && isset( $columns[ $_GET['orderby'] ] ) ? sanitize_key( $_GET['orderby'] ) : 'created';
	$order   = ( isset( $_GET['order'] ) && strtolower( $_GET['order'] ) === 'asc' ) ? 'asc' : 'desc';

	usort( $all_rows, function ( $a, $b ) use ( $orderby, $order ) {
		if ( $orderby === 'created' ) $cmp = $a['created'] <=> $b['created'];
		elseif ( $orderby === 'visit' ) $cmp = strnatcasecmp( (string) $a['visit_display'], (string) $b['visit_display'] );
		else $cmp = strnatcasecmp( (string) ( $a[ $orderby ] ?? '' ), (string) ( $b[ $orderby ] ?? '' ) );
		return $order === 'asc' ? $cmp : -$cmp;
	} );

	$per_page    = 20;
	$paged       = max( 1, absint( $_GET['paged'] ?? 1 ) );
	$total       = count( $all_rows );
	$total_pages = max( 1, (int) ceil( $total / $per_page ) );
	$paged       = min( $paged, $total_pages );
	$page_rows   = array_slice( $all_rows, ( $paged - 1 ) * $per_page, $per_page );

	$base_url = remove_query_arg( array( 'orderby', 'order', 'paged' ) );

	$sort_link = function ( $col ) use ( $columns, $orderby, $order, $base_url, $status_filter ) {
		$next_order = ( $orderby === $col && $order === 'asc' ) ? 'desc' : 'asc';
		$args = array( 'orderby' => $col, 'order' => $next_order );
		if ( $status_filter ) $args['status_filter'] = $status_filter;
		$url = add_query_arg( $args, $base_url );
		$arrow = '';
		if ( $orderby === $col ) $arrow = $order === 'asc' ? ' ▲' : ' ▼';
		return '<a href="' . esc_url( $url ) . '" class="noghre-list-sort" data-nav="ignore">' . esc_html( $columns[ $col ] ) . $arrow . '</a>';
	};

	$check_page_url = noghre_get_check_page_url();
	$admin_key      = noghre_get_admin_access_key();

	ob_start();
	?>
	<div class="noghre-booking-list-wrap" id="noghre-booking-list-wrap">

		<div class="noghre-booking-list-toolbar">
			<input type="text" id="noghre-booking-list-search" class="noghre-booking-list-search" placeholder="جستجو در کد پیگیری، نام یا موبایل..." autocomplete="off">
			<select id="noghre-booking-list-status-filter" class="noghre-booking-list-status-filter">
				<?php foreach ( $status_options as $val => $label ) : ?>
					<option value="<?php echo esc_attr( $val ); ?>" <?php selected( $status_filter, $val ); ?>><?php echo esc_html( $label ); ?></option>
				<?php endforeach; ?>
			</select>
			<span class="noghre-booking-list-count" id="noghre-booking-list-count" data-count="<?php echo esc_attr( $total ); ?>">
				<?php echo esc_html( noghre_to_persian_digits( $total ) ); ?> رزرو
			</span>
		</div>

		<div class="noghre-booking-list-table-wrap">
			<table class="noghre-booking-list-table" id="noghre-booking-list-table">
				<thead>
					<tr>
						<?php foreach ( $columns as $key => $label ) : ?>
							<?php if ( $key === 'created' || $key === 'sms' ) continue; ?>
							<th data-col="<?php echo esc_attr( $key ); ?>"><?php echo $sort_link( $key ); ?></th>
						<?php endforeach; ?>
						<th data-col="sms">پیامک</th>
						<th data-col="created"><?php echo $sort_link( 'created' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php if ( empty( $page_rows ) ) : ?>
						<tr class="noghre-list-empty"><td colspan="<?php echo count( $columns ) + 1; ?>">هنوز رزروی ثبت نشده است.</td></tr>
					<?php else : ?>
						<?php foreach ( $page_rows as $r ) :
							$view_url = ( $check_page_url && $admin_key ) ? add_query_arg( array( 'code' => $r['tracking_code'], 'm' => $admin_key ), $check_page_url ) : '';
							?>
							<tr data-search="<?php echo esc_attr( strtolower( $r['tracking_code'] . ' ' . $r['name'] . ' ' . $r['mobile'] ) ); ?>">
								<td data-label="کد پیگیری" dir="ltr">
									<?php if ( $view_url ) : ?>
										<a href="<?php echo esc_url( $view_url ); ?>" data-nav="check" data-code="<?php echo esc_attr( $r['tracking_code'] ); ?>" class="noghre-check-link"><strong><?php echo esc_html( $r['tracking_code'] ); ?></strong></a>
									<?php else : ?>
										<strong><?php echo esc_html( $r['tracking_code'] ); ?></strong>
									<?php endif; ?>
								</td>
								<td data-label="نام"><?php echo esc_html( $r['name'] ); ?></td>
								<td data-label="موبایل" dir="ltr"><a href="tel:<?php echo esc_attr( $r['mobile'] ); ?>" data-nav="ignore"><?php echo esc_html( $r['mobile'] ); ?></a></td>
								<td data-label="شعبه"><?php echo esc_html( $r['branch'] ); ?></td>
								<td data-label="روز و ساعت"><?php echo esc_html( $r['visit_display'] ); ?></td>
								<td data-label="وضعیت"><span class="noghre-status-pill status-<?php echo esc_attr( $r['status'] ); ?>"><?php echo esc_html( noghre_booking_status_label( $r['status'] ) ); ?></span></td>
								<td data-label="پیامک" class="noghre-sms-cell"><?php echo $r['sms_status'] === '1' ? '✅' : '❌'; ?></td>
								<td data-label="تاریخ ثبت"><?php echo $r['created'] ? esc_html( noghre_jalali_date( $r['created'], 'j F Y - H:i' ) ) : '—'; ?></td>
							</tr>
						<?php endforeach; ?>
					<?php endif; ?>
					<tr class="noghre-list-no-result" style="display:none;"><td colspan="<?php echo count( $columns ) + 1; ?>">موردی مطابق جستجو یافت نشد.</td></tr>
				</tbody>
			</table>
		</div>

		<?php if ( $total_pages > 1 ) : ?>
			<div class="noghre-booking-list-pagination">
				<?php for ( $p = 1; $p <= $total_pages; $p++ ) :
					$args = array( 'orderby' => $orderby, 'order' => $order, 'paged' => $p );
					if ( $status_filter ) $args['status_filter'] = $status_filter;
					$page_url = add_query_arg( $args, $base_url );
					?>
					<a class="noghre-page-btn <?php echo $p === $paged ? 'is-active' : ''; ?>" href="<?php echo esc_url( $page_url ); ?>" data-nav="ignore" data-paged="<?php echo esc_attr( $p ); ?>"><?php echo esc_html( noghre_to_persian_digits( $p ) ); ?></a>
				<?php endfor; ?>
			</div>
		<?php endif; ?>
	</div>
	<?php
	return ob_get_clean();
}
add_shortcode( 'ariatin-noghreh-list', 'noghre_shortcode_booking_list' );

/* ============================================================
 * ریدایرکت لیست رزروها برای غیرمدیر
 * ============================================================ */
function noghre_booking_list_redirect() {
	if ( is_admin() ) return;
	if ( noghre_is_admin_request() ) return;

	$has_list_shortcode = false;
	if ( is_page() || is_singular() ) {
		$post = get_post();
		if ( $post && has_shortcode( $post->post_content, 'ariatin-noghreh-list' ) ) $has_list_shortcode = true;
	}

	$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
	$is_list_slug = ( strpos( $request_uri, '/list-reserve' ) !== false || strpos( $request_uri, '/list' ) !== false );

	if ( ! $has_list_shortcode && ! $is_list_slug ) return;

	$blog_url = get_post_type_archive_link( 'post' );
	if ( ! $blog_url ) {
		$page_for_posts = (int) get_option( 'page_for_posts' );
		$blog_url = $page_for_posts ? get_permalink( $page_for_posts ) : home_url( '/' );
	}

	wp_safe_redirect( $blog_url );
	exit;
}
add_action( 'template_redirect', 'noghre_booking_list_redirect' );

/* ============================================================
 * noindex برای لیست رزروها
 * ============================================================ */
function noghre_booking_list_noindex() {
	if ( is_404() ) { echo '<meta name="robots" content="noindex, nofollow">' . "\n"; return; }
	if ( ! is_page() && ! is_singular() ) return;
	$post = get_post();
	if ( ! $post || ! has_shortcode( $post->post_content, 'ariatin-noghreh-list' ) ) return;
	echo '<meta name="robots" content="noindex, nofollow">' . "\n";
}
add_action( 'wp_head', 'noghre_booking_list_noindex', 1 );