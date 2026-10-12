<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/* ============================================================
 * شورت‌کد استعلام رزرو + QR
 * ============================================================ */

function noghre_find_shortcode_page_url( $shortcode_tag ) {
	$cache_key = 'noghre_shortcode_url_' . $shortcode_tag;
	$cached = get_transient( $cache_key );
	if ( $cached ) return $cached;

	$query = new WP_Query( array(
		'post_type'      => array( 'page', 'post' ),
		'post_status'    => 'publish',
		'posts_per_page' => 1,
		's'              => '[' . $shortcode_tag,
		'no_found_rows'  => true,
	) );
	if ( $query->have_posts() ) {
		$url = get_permalink( $query->posts[0] );
		set_transient( $cache_key, $url, HOUR_IN_SECONDS );
		return $url;
	}
	return '';
}

function noghre_shortcode_booking_check() {
	$ver = defined( 'NOGHRE_VERSION' ) ? NOGHRE_VERSION : '1.0.0';

	$code = isset( $_GET['code'] ) ? sanitize_text_field( wp_unslash( $_GET['code'] ) ) : '';
	$msg  = isset( $_GET['noghre_booking_msg'] ) ? sanitize_key( $_GET['noghre_booking_msg'] ) : '';
	$sms_unused_status = isset( $_GET['sms_unused_status'] ) ? sanitize_key( $_GET['sms_unused_status'] ) : '';
	$is_admin_view = current_user_can( 'manage_options' ) || noghre_is_admin_request();

	$nonce = wp_create_nonce( 'noghre_booking_admin_action' );

	/* 🔑 enqueue اسکریپت اختصاصی صفحه استعلام (بدون وابستگی) */
	wp_enqueue_script(
		'noghre-booking-check',
		NOGHRE_URI . '/assets/js/booking-check.js',
		array(),
		$ver,
		true
	);

	/* 🔑 اگه QR نیاز داریم، کتابخانه QR رو هم لود کن */
	if ( $code && ! $is_admin_view ) {
		wp_enqueue_script(
			'noghre-qrcode',
			NOGHRE_URI . '/plugins/qrcode/qrcode.js',
			array(),
			$ver,
			true
		);
	}

	/* 🔑 اگه کاربر مدیر هست، app-shared.js رو لود کن
	   (برای bindAppForms و showNotice) */
	if ( $is_admin_view && ! wp_script_is( 'noghre-app-shared', 'enqueued' ) ) {
		wp_enqueue_script(
			'noghre-app-shared',
			NOGHRE_URI . '/assets/js/app-shared.js',
			array(),
			$ver,
			true
		);
	}

	/* 🔑 localize برای قالب اصلی */
	wp_localize_script( 'noghre-booking-check', 'noghreCheckConfig', array(
		'ajaxUrl' => admin_url( 'admin-ajax.php' ),
		'nonce'   => $nonce,
		'code'    => $code,
	) );

	/* 🔑 meta tag برای nonce */
	add_action( 'wp_head', function() use ( $nonce ) {
		echo '<meta name="noghre-actions-nonce" content="' . esc_attr( $nonce ) . '">' . "\n";
	}, 5 );

	ob_start();

	/* پیام‌های URL */
	if ( $msg === 'payment_failed' ) {
		echo '<div class="noghre-booking-notice is-error">پرداخت ناموفق بود یا توسط شما لغو شد. لطفاً دوباره تلاش کنید.</div>';
	}

	if ( $sms_unused_status === 'sent' ) {
		echo '<div class="noghre-booking-notice is-success">پیامک با موفقیت برای مشتری ارسال شد.</div>';
	} elseif ( $sms_unused_status === 'failed' ) {
		echo '<div class="noghre-booking-notice is-error">ارسال پیامک ناموفق بود. لطفاً دوباره تلاش کنید یا تنظیمات پترن را بررسی کنید.</div>';
	} elseif ( $sms_unused_status === 'disabled' ) {
		echo '<div class="noghre-booking-notice is-warning">ارسال پیامک برای رزروهای استفاده نشده خاموش است یا پترن آن تنظیم نشده. لطفاً از تنظیمات پیامک بررسی کنید.</div>';
	}

	/* فرم جستجوی کد پیگیری */
	if ( ! $code ) {
		?>
		<div class="noghre-booking-lookup">
			<form method="get" action="">
				<p>برای مشاهده وضعیت رزرو خود، کد پیگیری را وارد کنید:</p>
				<div class="noghre-booking-lookup-row">
					<input type="text" name="code" placeholder="کد پیگیری" dir="ltr" required>
					<button type="submit" class="btn btn-primary">پیگیری</button>
				</div>
			</form>
		</div>
		<?php
		return ob_get_clean();
	}

	/* پیدا کردن رزرو */
	$post = noghre_get_booking_by_tracking_code( $code );
	if ( ! $post ) {
		echo '<div class="noghre-booking-notice is-error">کد پیگیری یافت نشد.</div>';
		return ob_get_clean();
	}

	$post_id  = $post->ID;
	$status   = get_post_meta( $post_id, '_status', true );
	$name     = get_post_meta( $post_id, '_customer_name', true );
	$mobile   = get_post_meta( $post_id, '_customer_mobile', true );
	$branch   = get_post_meta( $post_id, '_branch', true );
	$services = (array) get_post_meta( $post_id, '_services', true );
	$day      = get_post_meta( $post_id, '_visit_day_display', true );
	$time     = get_post_meta( $post_id, '_preferred_time', true );
	$discount_code   = get_post_meta( $post_id, '_discount_code', true );
	$payment_required = get_post_meta( $post_id, '_payment_required', true );
	$payment_status   = get_post_meta( $post_id, '_payment_status', true );
	$payment_amount   = get_post_meta( $post_id, '_payment_amount', true );

	$discount_info = null;
	$discount_services = array();
	if ( $discount_code ) {
		$codes = noghre_get_discount_codes();
		foreach ( $codes as $c ) {
			if ( strtoupper( $c['code'] ) === strtoupper( $discount_code ) ) {
				$discount_info = $c;
				$discount_services = (array) ( $c['services'] ?? array() );
				break;
			}
		}
	}

	$in_mobile_app = ( isset( $_GET['mob'] ) && $_GET['mob'] === '1' ) || ( isset( $_GET['section'] ) && $_GET['section'] === 'check' );

	if ( $in_mobile_app ) {
		$return_url = home_url( '/mobile/?section=check&code=' . urlencode( $code ) );
	} else {
		$return_url = add_query_arg( 'code', $code, noghre_get_check_page_url() );
	}

	$base_url = $return_url;
	?>
	<div class="noghre-booking-status">
		<h3>وضعیت رزرو شما</h3>
		<p class="noghre-status-badge status-<?php echo esc_attr( $status ); ?>"><?php echo esc_html( noghre_booking_status_label( $status ) ); ?></p>

		<?php if ( $status === 'confirmed' ) : ?>
			<p class="noghre-booking-notice is-success">این نوبت تایید شده است.</p>
		<?php elseif ( $status === 'edited' ) : ?>
			<p class="noghre-booking-notice is-info">در ساعت و تاریخ هماهنگ شده مراجعه نمایید.</p>
		<?php elseif ( $status === 'used' ) : ?>
			<p class="noghre-booking-notice is-warning">شما از خدمات سالن استفاده نموده‌اید.</p>
		<?php elseif ( $status === 'unused' ) : ?>
			<p class="noghre-booking-notice is-error">شما در زمان تعیین شده به سالن مراجعه نکردید.</p>
		<?php endif; ?>

		<?php if ( ! $is_admin_view ) :
			$qr_url = home_url( add_query_arg( array( 'code' => $code ) ) );
			?>
			<div class="noghre-booking-qr">
				<div id="noghre-qr-canvas" data-qr-url="<?php echo esc_attr( $qr_url ); ?>"></div>
				<p class="noghre-booking-qr-caption">
					جهت مراجعه به <strong><?php echo esc_html( noghre_get_option( 'salon_name_for_qr', 'سالن زیبایی نقره' ) ); ?></strong>،
					ارائه این تصویر الزامی می‌باشد.
				</p>
				<button type="button" class="btn btn-outline noghre-booking-print-btn" id="noghre-print-btn">ذخیره (پرینت)</button>
			</div>
		<?php endif; ?>

		<table class="noghre-booking-table">
			<tr><th>نام</th><td><?php echo esc_html( $name ); ?></td></tr>
			<?php if ( $is_admin_view && $mobile ) : ?>
				<tr><th>شماره موبایل مشتری</th><td><a href="tel:<?php echo esc_attr( $mobile ); ?>" dir="ltr"><?php echo esc_html( $mobile ); ?></a></td></tr>
			<?php endif; ?>
			<tr><th>شعبه</th><td><?php echo esc_html( $branch ); ?></td></tr>
			<tr>
				<th>خدمات</th>
				<td>
					<?php
					if ( empty( $discount_info ) || empty( $discount_services ) ) {
						echo '<strong>' . esc_html( implode( '، ', $services ) ) . '</strong>';
					} else {
						$parts = array();
						foreach ( $services as $s ) {
							if ( in_array( $s, $discount_services, true ) ) {
								$parts[] = '<strong>' . esc_html( $s ) . '</strong>';
							} else {
								$parts[] = esc_html( $s );
							}
						}
						echo implode( '، ', $parts );
					}
					?>
				</td>
			</tr>
			<tr><th>روز و ساعت مراجعه</th><td><?php echo esc_html( $day . ' — ساعت ' . noghre_to_persian_digits( $time ) ); ?></td></tr>

			<?php if ( $discount_info ) : ?>
				<tr>
					<th>کد تخفیف</th>
					<td>
						<?php echo esc_html( $discount_info['code'] ); ?>
						— <?php echo esc_html( noghre_discount_code_description( $discount_info ) ); ?>
						<?php if ( ! empty( $discount_services ) ) : ?>
							<br><span class="description">شامل خدمات: <?php echo esc_html( implode( '، ', $discount_services ) ); ?></span>
						<?php else : ?>
							<br><span class="description">شامل تمام خدمات</span>
						<?php endif; ?>
					</td>
				</tr>
			<?php endif; ?>

			<?php if ( $payment_required ) : ?>
				<tr><th>پرداخت</th><td>
					<?php if ( $payment_status === 'paid' ) : ?>
						پرداخت شده (<?php echo esc_html( noghre_to_persian_digits( number_format_i18n( $payment_amount ) ) ); ?> تومان)
					<?php else : ?>
						پرداخت نشده
					<?php endif; ?>
				</td></tr>
			<?php endif; ?>
		</table>

		<?php if ( $is_admin_view ) : ?>
			<div class="noghre-booking-admin-actions">
				<h4>عملیات مدیریت</h4>

				<?php if ( $status !== 'confirmed' && $status !== 'used' && $status !== 'unused' && $status !== 'edited' ) : ?>
					<form data-noghre-app-form="1" style="display:inline-block;">
						<input type="hidden" name="code" value="<?php echo esc_attr( $code ); ?>">
						<input type="hidden" name="action" value="noghre_booking_confirm">
						<input type="hidden" name="booking_id" value="<?php echo esc_attr( $post_id ); ?>">
						<button type="submit" class="btn btn-primary">تایید رزرو</button>
					</form>
				<?php endif; ?>

				<?php if ( $status !== 'used' && $status !== 'unused' ) : ?>
					<button type="button" class="btn btn-outline" id="noghre-toggle-edit">ویرایش رزرو</button>
					<form id="noghre-edit-form" style="display:none;margin-top:10px;" data-noghre-app-form="1">
						<input type="hidden" name="code" value="<?php echo esc_attr( $code ); ?>">
						<input type="hidden" name="action" value="noghre_booking_edit">
						<input type="hidden" name="booking_id" value="<?php echo esc_attr( $post_id ); ?>">
						<p>
							<label>تاریخ جدید (شمسی):</label><br>
							<input type="text" name="new_date_jalali" placeholder="۱۴۰۵/۰۶/۲۹" dir="ltr" style="width:130px;" value="<?php echo esc_attr( noghre_gregorian_string_to_jalali_string( get_post_meta( $post_id, '_visit_day_gregorian', true ) ) ); ?>">
						</p>
						<p>
							<label>ساعت جدید:</label><br>
							<select name="new_time">
								<?php foreach ( noghre_get_booking_hours() as $h ) : ?>
									<option value="<?php echo esc_attr( $h['time'] ); ?>" <?php selected( $h['time'], $time ); ?>><?php echo esc_html( noghre_to_persian_digits( $h['time'] ) ); ?></option>
								<?php endforeach; ?>
							</select>
						</p>
						<button type="submit" class="btn btn-primary">ثبت ویرایش</button>
					</form>
				<?php endif; ?>

				<?php if ( $status !== 'used' && $status !== 'unused' ) : ?>
					<form data-noghre-app-form="1" style="display:inline-block;" data-confirm="آیا مطمئن هستید؟ این کد رهگیری به‌عنوان استفاده‌شده علامت‌گذاری می‌شود.">
						<input type="hidden" name="code" value="<?php echo esc_attr( $code ); ?>">
						<input type="hidden" name="action" value="noghre_booking_void">
						<input type="hidden" name="booking_id" value="<?php echo esc_attr( $post_id ); ?>">
						<button type="submit" class="button" style="color:#b32d2e;border-color:#b32d2e;">باطل شدن (استفاده‌شده)</button>
					</form>
				<?php endif; ?>

				<?php if ( $status === 'unused' ) : ?>
					<form data-noghre-app-form="1" style="display:inline-block;">
						<input type="hidden" name="code" value="<?php echo esc_attr( $code ); ?>">
						<input type="hidden" name="action" value="noghre_booking_send_unused_sms">
						<input type="hidden" name="booking_id" value="<?php echo esc_attr( $post_id ); ?>">
						<button type="submit" class="btn btn-primary">ارسال پیامک</button>
					</form>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	</div>
	<?php
	return ob_get_clean();
}
add_shortcode( 'ariatin-noghreh-check', 'noghre_shortcode_booking_check' );