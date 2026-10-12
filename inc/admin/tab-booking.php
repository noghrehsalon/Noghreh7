<?php
/**
 * تب رزرو وقت
 * شامل ۳ زیرتب: تنظیمات رزرو + لیست رزروها + کدهای تخفیف
 *
 * @package Noghre
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/* نکته: تابع noghre_sanitize_booking_options به inc/options-helper.php منتقل شد */
/* نکته: توابع noghre_handle_booking_settings_post و noghre_handle_discount_codes_post به inc/admin/admin-helpers.php منتقل شدند */

/* ==========================================================================
 * بخش ۱ — رندر اصلی تب (زیرتب‌ها)
 * ========================================================================== */
function noghre_render_booking_page() {
	$sub = isset( $_GET['sub'] ) ? sanitize_key( $_GET['sub'] ) : 'settings';

	$subtabs = array(
		'settings' => '<span class="dashicons dashicons-admin-settings"></span> تنظیمات رزرو',
		'bookings' => '<span class="dashicons dashicons-list-view"></span> لیست رزروها',
		'discount' => '<span class="dashicons dashicons-tag"></span> کدهای تخفیف',
	);

	$sub_url = function( $key ) {
		return add_query_arg( array(
			'page' => 'noghre-panel',
			'tab'  => 'booking',
			'sub'  => $key,
		), admin_url( 'admin.php' ) );
	};
	?>
	<div class="noghre-subtabs">
		<?php foreach ( $subtabs as $key => $title ) : ?>
			<a href="<?php echo esc_url( $sub_url( $key ) ); ?>"
			   class="noghre-subtab <?php echo $sub === $key ? 'is-active' : ''; ?>">
				<?php echo $title; // phpcs:ignore -- شامل HTML امن ?>
			</a>
		<?php endforeach; ?>
	</div>

	<div class="noghre-subtab-content">
		<?php
		switch ( $sub ) {
			case 'bookings':
				noghre_render_bookings_list_subtab();
				break;

			case 'discount':
				noghre_render_discount_codes_subtab();
				break;

			case 'settings':
			default:
				noghre_render_booking_settings_subtab();
				break;
		}
		?>
	</div>
	<?php
}

/* ==========================================================================
 * بخش ۲ — زیرتب «تنظیمات رزرو»
 * ========================================================================== */
function noghre_render_booking_settings_subtab() {
	$o        = noghre_get_booking_options();
	$branches = noghre_get_booking_branches();
	$services = noghre_get_booking_services();
	$hours    = noghre_get_booking_hours();
	?>

	<h2 class="noghre-section-heading">
		<span class="dashicons dashicons-admin-settings"></span>
		تنظیمات عمومی
	</h2>
	<form method="post" action="options.php">
		<?php settings_fields( 'noghre_booking_group' ); ?>
		<table class="form-table">
			<tr>
				<th><label for="booking_description">باکس توضیحات (بالای فرم)</label></th>
				<td>
					<?php
					wp_editor( $o['booking_description'], 'booking_description', array(
						'textarea_name' => 'noghre_booking_options[booking_description]',
						'textarea_rows' => 6,
						'media_buttons' => true,
					) );
					?>
				</td>
			</tr>
			<tr>
				<th>کد تخفیف</th>
				<td><label><input type="checkbox" name="noghre_booking_options[discount_code_enabled]" value="1" <?php checked( $o['discount_code_enabled'], 1 ); ?>> نمایش فیلد «کد تخفیف» در فرم رزرو</label></td>
			</tr>
			<tr>
				<th>پرداخت آنلاین حق رزرو</th>
				<td>
					<label><input type="checkbox" name="noghre_booking_options[payment_enabled]" value="1" <?php checked( $o['payment_enabled'], 1 ); ?>> فعال‌سازی پرداخت آنلاین حق رزرو</label>
					<p class="description">در صورت فعال بودن و تکمیل بودن تنظیمات درگاه زرین‌پال، پیش از ثبت نهایی رزرو، مبلغ زیر از کاربر دریافت می‌شود.</p>
				</td>
			</tr>
			<tr>
				<th><label for="payment_amount">مبلغ حق رزرو (تومان)</label></th>
				<td><input type="number" id="payment_amount" name="noghre_booking_options[payment_amount]" value="<?php echo esc_attr( $o['payment_amount'] ); ?>" class="regular-text"></td>
			</tr>
			<tr>
				<th><label for="pay_button_text">متن دکمه (وقتی پرداخت فعال است)</label></th>
				<td><input type="text" id="pay_button_text" name="noghre_booking_options[pay_button_text]" value="<?php echo esc_attr( $o['pay_button_text'] ); ?>" class="regular-text"></td>
			</tr>
			<tr>
				<th><label for="reserve_button_text">متن دکمه (وقتی پرداخت غیرفعال است)</label></th>
				<td><input type="text" id="reserve_button_text" name="noghre_booking_options[reserve_button_text]" value="<?php echo esc_attr( $o['reserve_button_text'] ); ?>" class="regular-text"></td>
			</tr>
			<tr>
				<th><label for="booking_page_id">برگه رزرو وقت</label></th>
				<td>
					<select id="booking_page_id" name="noghre_booking_options[booking_page_id]">
						<option value="0">— انتخاب نشده —</option>
						<?php foreach ( get_pages() as $p ) : ?>
							<option value="<?php echo esc_attr( $p->ID ); ?>" <?php selected( (int) $o['booking_page_id'], $p->ID ); ?>><?php echo esc_html( $p->post_title ); ?></option>
						<?php endforeach; ?>
					</select>
					<p class="description">کد کوتاه <code>[ariatin-noghreh-nobat]</code> را داخل این برگه جای‌گذاری کنید.</p>
				</td>
			</tr>
			<tr>
				<th><label for="check_page_id">برگه پیگیری/استعلام رزرو</label></th>
				<td>
					<select id="check_page_id" name="noghre_booking_options[check_page_id]">
						<option value="0">— انتخاب نشده —</option>
						<?php foreach ( get_pages() as $p ) : ?>
							<option value="<?php echo esc_attr( $p->ID ); ?>" <?php selected( (int) $o['check_page_id'], $p->ID ); ?>><?php echo esc_html( $p->post_title ); ?></option>
						<?php endforeach; ?>
					</select>
					<p class="description">کد کوتاه <code>[ariatin-noghreh-check]</code> را داخل این برگه جای‌گذاری کنید.</p>
				</td>
			</tr>
		</table>
		<?php submit_button( 'ذخیره تنظیمات عمومی' ); ?>
	</form>

	<hr>

	<h2 class="noghre-section-heading">
		<span class="dashicons dashicons-shortcode"></span>
		شورت‌کدهای رزرو
	</h2>
	<p>این شورت‌کدها را می‌توانید داخل هر برگه یا نوشته قرار دهید:</p>
	<table class="widefat striped" style="max-width:820px;">
		<thead>
			<tr>
				<th style="width:240px;">شورت‌کد</th>
				<th>کاربرد</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td dir="ltr"><code>[ariatin-noghreh-nobat]</code></td>
				<td>فرم رزرو وقت. کافیست داخل برگه «رزرو وقت» قرار دهید.</td>
			</tr>
			<tr>
				<td dir="ltr"><code>[ariatin-noghreh-check]</code></td>
				<td>صفحه پیگیری/استعلام رزرو. کافیست داخل برگه پیگیری قرار دهید.</td>
			</tr>
			<tr>
				<td dir="ltr"><code>[ariatin-noghreh-list]</code></td>
				<td>
					لیست رزروها (از جدید به قدیم). این برگه برای کاربران عادی به صفحه مقالات وبلاگ هدایت می‌شود،
					و فقط با توکن مدیریت قابل مشاهده است.<br>
					این صفحه به‌صورت خودکار <code>noindex, nofollow</code> می‌شود تا در موتورهای جستجو ثبت نشود.
				</td>
			</tr>
		</tbody>
	</table>

	<hr>

	<h2 class="noghre-section-heading">
		<span class="dashicons dashicons-store"></span>
		شعبه‌ها
	</h2>
	<p>برای هر شعبه، شماره موبایل مدیریت آن را هم وارد کنید — این شماره فقط برای اطلاع‌رسانی رزرو به مدیریت شعبه استفاده می‌شود.</p>
	<form method="post">
		<?php wp_nonce_field( 'noghre_save_branches', 'noghre_branches_nonce' ); ?>
		<div class="noghre-repeater" id="noghre-branches-repeater">
			<div class="noghre-repeater-rows">
				<?php foreach ( $branches as $i => $b ) : ?>
					<?php noghre_booking_branch_row( $i, $b ); ?>
				<?php endforeach; ?>
			</div>
			<button type="button" class="button button-secondary noghre-add-row" data-target="#noghre-branches-repeater">افزودن شعبه</button>
		</div>
		<template id="noghre-branches-row-template">
			<?php noghre_booking_branch_row( '__INDEX__', array( 'name' => '', 'manager_mobile' => '' ) ); ?>
		</template>
		<?php submit_button( 'ذخیره شعبه‌ها' ); ?>
	</form>

	<hr>

	<h2 class="noghre-section-heading">
		<span class="dashicons dashicons-art"></span>
		خدمات
	</h2>
	<form method="post">
		<?php wp_nonce_field( 'noghre_save_services', 'noghre_services_nonce' ); ?>
		<div class="noghre-repeater" id="noghre-services-repeater">
			<div class="noghre-repeater-rows">
				<?php foreach ( $services as $i => $s ) : ?>
					<?php noghre_booking_service_row( $i, $s ); ?>
				<?php endforeach; ?>
			</div>
			<button type="button" class="button button-secondary noghre-add-row" data-target="#noghre-services-repeater">افزودن خدمت</button>
		</div>
		<template id="noghre-services-row-template">
			<?php noghre_booking_service_row( '__INDEX__', array( 'name' => '' ) ); ?>
		</template>
		<?php submit_button( 'ذخیره خدمات' ); ?>
	</form>

	<hr>

	<h2 class="noghre-section-heading">
		<span class="dashicons dashicons-clock"></span>
		ساعات فعالیت
	</h2>
	<p>هر ردیف یک گزینه در فهرست کشویی «ساعت پیشنهادی» فرم رزرو خواهد بود (مثلاً 10:00).</p>
	<form method="post">
		<?php wp_nonce_field( 'noghre_save_hours', 'noghre_hours_nonce' ); ?>
		<div class="noghre-repeater" id="noghre-hours-repeater">
			<div class="noghre-repeater-rows">
				<?php foreach ( $hours as $i => $h ) : ?>
					<?php noghre_booking_hour_row( $i, $h ); ?>
				<?php endforeach; ?>
			</div>
			<button type="button" class="button button-secondary noghre-add-row" data-target="#noghre-hours-repeater">افزودن ساعت</button>
		</div>
		<template id="noghre-hours-row-template">
			<?php noghre_booking_hour_row( '__INDEX__', array( 'time' => '' ) ); ?>
		</template>
		<?php submit_button( 'ذخیره ساعات فعالیت' ); ?>
	</form>
	<?php
}

function noghre_booking_branch_row( $index, $b ) {
	?>
	<div class="noghre-repeater-row">
		<div class="noghre-repeater-fields">
			<input type="text" placeholder="نام شعبه" name="branches[<?php echo esc_attr( $index ); ?>][name]" value="<?php echo esc_attr( $b['name'] ?? '' ); ?>" class="regular-text">
			<input type="text" placeholder="موبایل مدیریت شعبه (مثلاً 09121234567)" name="branches[<?php echo esc_attr( $index ); ?>][manager_mobile]" value="<?php echo esc_attr( $b['manager_mobile'] ?? '' ); ?>" class="regular-text" dir="ltr">
		</div>
		<button type="button" class="button-link-delete noghre-remove-row" title="حذف"><span class="dashicons dashicons-no-alt"></span></button>
	</div>
	<?php
}
function noghre_booking_service_row( $index, $s ) {
	?>
	<div class="noghre-repeater-row">
		<div class="noghre-repeater-fields">
			<input type="text" placeholder="نام خدمت" name="services[<?php echo esc_attr( $index ); ?>][name]" value="<?php echo esc_attr( $s['name'] ?? '' ); ?>" class="regular-text">
		</div>
		<button type="button" class="button-link-delete noghre-remove-row" title="حذف"><span class="dashicons dashicons-no-alt"></span></button>
	</div>
	<?php
}
function noghre_booking_hour_row( $index, $h ) {
	?>
	<div class="noghre-repeater-row">
		<div class="noghre-repeater-fields">
			<input type="time" name="hours[<?php echo esc_attr( $index ); ?>][time]" value="<?php echo esc_attr( $h['time'] ?? '' ); ?>">
		</div>
		<button type="button" class="button-link-delete noghre-remove-row" title="حذف"><span class="dashicons dashicons-no-alt"></span></button>
	</div>
	<?php
}

/* ==========================================================================
 * بخش ۳ — زیرتب «لیست رزروها»
 * ========================================================================== */
function noghre_render_bookings_list_subtab() {
	$columns    = noghre_bookings_sort_columns();
	$orderby    = isset( $_GET['orderby'] ) && isset( $columns[ $_GET['orderby'] ] ) ? sanitize_key( $_GET['orderby'] ) : 'created';
	$order      = ( isset( $_GET['order'] ) && strtolower( $_GET['order'] ) === 'asc' ) ? 'asc' : 'desc';
	$paged      = max( 1, absint( $_GET['paged'] ?? 1 ) );
	$per_page   = 20;

	$posts = get_posts( array(
		'post_type'      => 'noghre_booking',
		'post_status'    => 'any',
		'posts_per_page' => -1,
	) );

	$rows = array();
	foreach ( $posts as $post ) {
		$id = $post->ID;
		$status = get_post_meta( $id, '_status', true );
		$rows[] = array(
			'id'               => $id,
			'tracking_code'    => get_post_meta( $id, '_tracking_code', true ),
			'name'             => get_post_meta( $id, '_customer_name', true ),
			'mobile'           => get_post_meta( $id, '_customer_mobile', true ),
			'branch'           => get_post_meta( $id, '_branch', true ),
			'services'         => implode( '، ', (array) get_post_meta( $id, '_services', true ) ),
			'visit'            => get_post_meta( $id, '_visit_day_gregorian', true ) . ' ' . get_post_meta( $id, '_preferred_time', true ),
			'visit_display'    => get_post_meta( $id, '_visit_day_display', true ) . ' — ساعت ' . noghre_to_persian_digits( get_post_meta( $id, '_preferred_time', true ) ),
			'discount_code'    => get_post_meta( $id, '_discount_code', true ),
			'payment_required' => get_post_meta( $id, '_payment_required', true ),
			'payment_status'   => get_post_meta( $id, '_payment_status', true ),
			'payment_amount'   => get_post_meta( $id, '_payment_amount', true ),
			'status'           => $status,
			'sms_status'       => get_post_meta( $id, '_sms_sent_' . $status, true ),
			'created'          => (int) get_post_meta( $id, '_created_at', true ),
		);
	}

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
		$rows = array_filter( $rows, function( $r ) use ( $status_filter ) {
			return $r['status'] === $status_filter;
		} );
		$rows = array_values( $rows );
	}

	usort( $rows, function ( $a, $b ) use ( $orderby, $order ) {
		$va = $a[ $orderby ] ?? '';
		$vb = $b[ $orderby ] ?? '';
		if ( $orderby === 'created' ) {
			$cmp = $va <=> $vb;
		} elseif ( $orderby === 'payment' ) {
			$cmp = strcmp( (string) $a['payment_status'], (string) $b['payment_status'] );
		} else {
			$cmp = strnatcasecmp( (string) $va, (string) $vb );
		}
		return $order === 'asc' ? $cmp : -$cmp;
	} );

	$total       = count( $rows );
	$total_pages = max( 1, (int) ceil( $total / $per_page ) );
	$paged       = min( $paged, $total_pages );
	$page_rows   = array_slice( $rows, ( $paged - 1 ) * $per_page, $per_page );

	$check_page_url = noghre_get_check_page_url();
	$admin_key      = noghre_get_admin_access_key();

	$base_url = remove_query_arg( array( 'orderby', 'order', 'paged', 'noghre_msg' ) );

	$sort_link = function ( $col ) use ( $columns, $orderby, $order, $base_url, $status_filter ) {
		$next_order = ( $orderby === $col && $order === 'asc' ) ? 'desc' : 'asc';
		$args = array( 'orderby' => $col, 'order' => $next_order );
		if ( $status_filter ) $args['status_filter'] = $status_filter;
		$url = add_query_arg( $args, $base_url );
		$arrow = '';
		if ( $orderby === $col ) $arrow = $order === 'asc' ? ' ▲' : ' ▼';
		return '<a href="' . esc_url( $url ) . '">' . esc_html( $columns[ $col ] ) . $arrow . '</a>';
	};
	?>
	<p class="description">برای مرتب‌سازی، روی عنوان هر ستون کلیک کنید. هر صفحه ۲۰ رزرو نمایش می‌دهد.</p>

	<div class="noghre-bookings-toolbar">
		<input type="text" id="noghre-bookings-search" class="regular-text" placeholder="جستجو در کد پیگیری، نام یا موبایل..." autocomplete="off">
		<select id="noghre-bookings-status-filter" class="noghre-bookings-status-filter">
			<?php foreach ( $status_options as $val => $label ) : ?>
				<option value="<?php echo esc_attr( $val ); ?>" <?php selected( $status_filter, $val ); ?>><?php echo esc_html( $label ); ?></option>
			<?php endforeach; ?>
		</select>
		<span class="noghre-bookings-count" id="noghre-bookings-count"><?php echo esc_html( noghre_to_persian_digits( $total ) ); ?> رزرو</span>
	</div>

	<table class="widefat striped" id="noghre-bookings-table">
		<thead>
			<tr>
				<?php
				foreach ( $columns as $key => $label ) :
					if ( $key === 'created' ) continue;
					?>
					<th><?php echo $sort_link( $key ); ?></th>
				<?php endforeach; ?>
				<th>پیامک</th>
				<th><?php echo $sort_link( 'created' ); ?></th>
				<th>عملیات</th>
			</tr>
		</thead>
		<tbody>
			<?php if ( empty( $page_rows ) ) : ?>
				<tr><td colspan="<?php echo count( $columns ) + 2; ?>">هنوز رزروی ثبت نشده است.</td></tr>
			<?php else : ?>
				<?php foreach ( $page_rows as $r ) :
					$view_url = ( $check_page_url && $admin_key ) ? add_query_arg( array( 'code' => $r['tracking_code'], 'm' => $admin_key ), $check_page_url ) : '';
					$search_attr = strtolower( $r['tracking_code'] . ' ' . $r['name'] . ' ' . $r['mobile'] );
					?>
					<tr data-search="<?php echo esc_attr( $search_attr ); ?>">
						<td dir="ltr"><strong><?php echo esc_html( $r['tracking_code'] ); ?></strong></td>
						<td><?php echo esc_html( $r['name'] ); ?></td>
						<td dir="ltr"><a href="tel:<?php echo esc_attr( $r['mobile'] ); ?>"><?php echo esc_html( $r['mobile'] ); ?></a></td>
						<td><?php echo esc_html( $r['branch'] ); ?></td>
						<td><?php echo esc_html( $r['services'] ); ?></td>
						<td><?php echo esc_html( $r['visit_display'] ); ?></td>
						<td dir="ltr"><?php echo esc_html( $r['discount_code'] ?: '—' ); ?></td>
						<td>
							<?php if ( $r['payment_required'] ) : ?>
								<?php echo $r['payment_status'] === 'paid' ? 'پرداخت‌شده (' . esc_html( noghre_to_persian_digits( number_format_i18n( $r['payment_amount'] ) ) ) . ' ت)' : 'پرداخت‌نشده'; ?>
							<?php else : ?>
								—
							<?php endif; ?>
						</td>
						<td><span class="noghre-status-pill status-<?php echo esc_attr( $r['status'] ); ?>"><?php echo esc_html( noghre_booking_status_label( $r['status'] ) ); ?></span></td>
						<td class="noghre-sms-cell">
							<?php if ( $r['sms_status'] === '1' ) : ?>
								<span class="dashicons dashicons-yes-alt" style="color:#00a32a;font-size:16px;width:16px;height:16px;"></span>
							<?php else : ?>
								<span class="dashicons dashicons-dismiss" style="color:#d63638;font-size:16px;width:16px;height:16px;"></span>
							<?php endif; ?>
						</td>
						<td><?php echo $r['created'] ? esc_html( noghre_jalali_date( $r['created'], 'j F Y - H:i' ) ) : '—'; ?></td>
						<td>
							<?php if ( $view_url ) : ?>
								<a href="<?php echo esc_url( $view_url ); ?>" target="_blank">مشاهده/مدیریت</a> |
							<?php else : ?>
								<span style="color:#d63638;" title="برگه استعلام یا شناسه مدیریت تنظیم نشده">مشاهده/مدیریت (غیرفعال)</span> |
							<?php endif; ?>
							<a href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'action' => 'noghre_booking_delete', 'booking_id' => $r['id'] ), admin_url( 'admin-post.php' ) ), 'noghre_booking_delete' ) ); ?>" onclick="return confirm('آیا از حذف این رزرو مطمئن هستید؟');" style="color:#d63638;">حذف</a>
						</td>
					</tr>
				<?php endforeach; ?>
			<?php endif; ?>
			<tr class="noghre-bookings-no-result" style="display:none;"><td colspan="<?php echo count( $columns ) + 2; ?>">موردی مطابق جستجو یافت نشد.</td></tr>
		</tbody>
	</table>

	<?php if ( $total_pages > 1 ) : ?>
		<div class="tablenav"><div class="tablenav-pages">
			<span class="displaying-num"><?php echo esc_html( noghre_to_persian_digits( $total ) ); ?> رزرو</span>
			<?php for ( $p = 1; $p <= $total_pages; $p++ ) :
						$args = array( 'orderby' => $orderby, 'order' => $order, 'paged' => $p );
						if ( $status_filter ) $args['status_filter'] = $status_filter;
						$page_url = add_query_arg( $args, $base_url );
					?>
				<a class="button <?php echo $p === $paged ? 'button-primary' : ''; ?>" href="<?php echo esc_url( $page_url ); ?>" style="margin-inline-start:4px;"><?php echo esc_html( noghre_to_persian_digits( $p ) ); ?></a>
			<?php endfor; ?>
		</div></div>
	<?php endif; ?>

	<?php if ( ! $check_page_url || ! $admin_key ) : ?>
		<p class="description" style="margin-top:10px;">
			<strong>توجه:</strong> برای فعال شدن لینک «مشاهده/مدیریت»، ابتدا در تنظیمات «رزرو وقت» برگه پیگیری/استعلام را انتخاب کنید و
			از تنظیمات پیشرفته، یک «شناسه مدیریت» ایجاد کنید.
		</p>
	<?php endif; ?>
	<?php
}

/* ==========================================================================
 * بخش ۴ — زیرتب «کدهای تخفیف»
 * ========================================================================== */
function noghre_render_discount_codes_subtab() {
	$codes = noghre_get_discount_codes();

	$edit_index = isset( $_GET['edit'] ) ? absint( $_GET['edit'] ) : null;
	$editing    = ( null !== $edit_index && isset( $codes[ $edit_index ] ) ) ? $codes[ $edit_index ] : null;
	$form_data  = $editing ?: array(
		'code' => '', 'type' => 'percent', 'value' => 0, 'unlimited' => 1, 'max_uses' => 0,
		'has_expiry' => 0, 'expiry_date' => '', 'once_per_mobile' => 1, 'scope' => array( 'booking' ), 'services' => array(), 'active' => 1,
	);
	$expiry_jalali = ! empty( $form_data['expiry_date'] ) ? noghre_gregorian_string_to_jalali_string( $form_data['expiry_date'] ) : '';
	$services_list = noghre_get_booking_services();
	?>
	<h2 class="noghre-section-heading">
		<span class="dashicons dashicons-tag"></span>
		<?php echo $editing ? 'ویرایش کد تخفیف' : 'افزودن کد تخفیف جدید'; ?>
	</h2>
	<form method="post">
		<?php wp_nonce_field( 'noghre_save_discount_code', 'noghre_discount_nonce' ); ?>
		<?php if ( null !== $edit_index ) : ?>
			<input type="hidden" name="edit_index" value="<?php echo esc_attr( $edit_index ); ?>">
		<?php endif; ?>
		<table class="form-table">
			<tr>
				<th><label for="dc_code">کد تخفیف</label></th>
				<td><input type="text" id="dc_code" name="code" value="<?php echo esc_attr( $form_data['code'] ); ?>" class="regular-text" dir="ltr" style="text-transform:uppercase;" required></td>
			</tr>
			<tr>
				<th>نوع تخفیف</th>
				<td>
					<label><input type="radio" name="type" value="percent" <?php checked( $form_data['type'], 'percent' ); ?>> درصدی</label>
					&nbsp;&nbsp;
					<label><input type="radio" name="type" value="fixed" <?php checked( $form_data['type'], 'fixed' ); ?>> مبلغ ثابت (تومان)</label>
				</td>
			</tr>
			<tr>
				<th><label for="dc_value">مقدار</label></th>
				<td><input type="number" id="dc_value" name="value" value="<?php echo esc_attr( $form_data['value'] ); ?>" class="regular-text"></td>
			</tr>
			<tr>
				<th>تعداد استفاده</th>
				<td>
					<label><input type="checkbox" id="dc_unlimited" name="unlimited" value="1" <?php checked( ! empty( $form_data['unlimited'] ) ); ?>> نامحدود</label>
					<p class="noghre-dc-max-uses-row" style="<?php echo ! empty( $form_data['unlimited'] ) ? 'display:none;' : ''; ?>">
						<label>سقف تعداد استفاده: <input type="number" name="max_uses" value="<?php echo esc_attr( $form_data['max_uses'] ); ?>" min="0"></label>
					</p>
				</td>
			</tr>
			<tr>
				<th>تاریخ انقضا</th>
				<td>
					<label><input type="checkbox" id="dc_has_expiry" name="has_expiry" value="1" <?php checked( ! empty( $form_data['has_expiry'] ) ); ?>> این کد تاریخ انقضا دارد</label>
					<p>
						<input type="text" name="expiry_date_jalali" value="<?php echo esc_attr( $expiry_jalali ); ?>" placeholder="۱۴۰۵-۰۷-۱۰" dir="ltr" style="width:130px;">
						<span class="description">تاریخ شمسی به‌صورت سال-ماه-روز</span>
					</p>
				</td>
			</tr>
			<tr>
				<th>محدودیت شماره موبایل</th>
				<td><label><input type="checkbox" name="once_per_mobile" value="1" <?php checked( ! empty( $form_data['once_per_mobile'] ) ); ?>> هر شماره موبایل فقط یک‌بار</label></td>
			</tr>
			<tr>
				<th>قابل استفاده در</th>
				<td>
					<label><input type="checkbox" class="noghre-dc-scope-booking" name="scope[]" value="booking" <?php checked( in_array( 'booking', (array) $form_data['scope'], true ) ); ?>> رزرو وقت</label>
					&nbsp;&nbsp;
					<label><input type="checkbox" name="scope[]" value="store" <?php checked( in_array( 'store', (array) $form_data['scope'], true ) ); ?>> فروشگاه آنلاین (به‌زودی)</label>
				</td>
			</tr>
			<tr class="noghre-dc-services-row" style="<?php echo in_array( 'booking', (array) $form_data['scope'], true ) ? '' : 'display:none;'; ?>">
				<th>شامل چه خدماتی بشود؟</th>
				<td>
					<label style="display:block;margin-bottom:8px;"><input type="checkbox" class="noghre-dc-all-services" name="all_services" value="1" <?php checked( empty( $form_data['services'] ) ); ?>> تمام خدمات</label>
					<details class="noghre-dc-service-dropdown" style="<?php echo empty( $form_data['services'] ) ? 'display:none;' : ''; ?>">
						<summary><span class="noghre-dc-service-label">— انتخاب خدمات —</span></summary>
						<div class="noghre-dc-service-panel">
							<?php foreach ( $services_list as $s ) : ?>
								<label><input type="checkbox" name="services[]" value="<?php echo esc_attr( $s['name'] ); ?>" <?php checked( in_array( $s['name'], (array) $form_data['services'], true ) ); ?>> <?php echo esc_html( $s['name'] ); ?></label>
							<?php endforeach; ?>
						</div>
					</details>
				</td>
			</tr>
			<tr>
				<th>وضعیت</th>
				<td><label><input type="checkbox" name="active" value="1" <?php checked( ! empty( $form_data['active'] ) ); ?>> فعال</label></td>
			</tr>
		</table>
		<?php submit_button( $editing ? 'ذخیره تغییرات' : 'افزودن کد تخفیف' ); ?>
		<?php if ( $editing ) : ?>
			<a href="<?php echo esc_url( remove_query_arg( array( 'edit' ) ) ); ?>" class="button">انصراف از ویرایش</a>
		<?php endif; ?>
	</form>

	<hr>
	<h2 class="noghre-section-heading">
		<span class="dashicons dashicons-list-view"></span>
		لیست کدهای تخفیف
	</h2>
	<table class="widefat striped">
		<thead>
			<tr>
				<th>کد</th><th>نوع/مقدار</th><th>محدوده</th><th>وضعیت</th><th>دفعات استفاده</th><th>عملیات</th>
			</tr>
		</thead>
		<tbody>
			<?php if ( empty( $codes ) ) : ?>
				<tr><td colspan="6">هنوز کد تخفیفی ثبت نشده است.</td></tr>
			<?php else : ?>
				<?php foreach ( $codes as $i => $c ) : ?>
					<tr>
						<td dir="ltr"><strong><?php echo esc_html( $c['code'] ); ?></strong></td>
						<td><?php echo esc_html( noghre_discount_code_description( $c ) ); ?></td>
						<td><?php echo esc_html( implode( '، ', array_map( function( $s ) { return $s === 'booking' ? 'رزرو وقت' : 'فروشگاه'; }, (array) $c['scope'] ) ) ); ?></td>
						<td>
							<?php if ( ! empty( $c['active'] ) ) : ?>
								<span class="dashicons dashicons-yes-alt" style="color:#00a32a;font-size:16px;width:16px;height:16px;"></span> فعال
							<?php else : ?>
								<span class="dashicons dashicons-dismiss" style="color:#d63638;font-size:16px;width:16px;height:16px;"></span> غیرفعال
							<?php endif; ?>
						</td>
						<td>
							<?php echo esc_html( noghre_to_persian_digits( $c['used_count'] ?? 0 ) ); ?>
							<?php if ( empty( $c['unlimited'] ) ) : ?>
								از <?php echo esc_html( noghre_to_persian_digits( $c['max_uses'] ?? 0 ) ); ?>
							<?php else : ?>
								(نامحدود)
							<?php endif; ?>
						</td>
						<td>
							<a href="<?php echo esc_url( add_query_arg( array( 'tab' => 'booking', 'sub' => 'discount', 'edit' => $i ) ) ); ?>">ویرایش</a> |
							<a href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'tab' => 'booking', 'sub' => 'discount', 'delete' => $i ) ), 'noghre_delete_discount_code' ) ); ?>" onclick="return confirm('آیا مطمئن هستید؟');" style="color:#d63638;">حذف</a>
						</td>
					</tr>
				<?php endforeach; ?>
			<?php endif; ?>
		</tbody>
	</table>
	<?php
}