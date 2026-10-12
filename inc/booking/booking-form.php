<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/* ============================================================
 * شورت‌کد فرم رزرو
 * ============================================================ */
function noghre_shortcode_booking_form() {
	$o        = noghre_get_booking_options();
	$branches = noghre_get_booking_branches();
	$services = noghre_get_booking_services();
	$hours    = noghre_get_booking_hours();

	ob_start();

	if ( isset( $_GET['noghre_booking_msg'] ) && $_GET['noghre_booking_msg'] === 'payment_failed' ) {
		echo '<div class="noghre-booking-notice is-error">پرداخت ناموفق بود یا توسط شما لغو شد. لطفاً دوباره تلاش کنید.</div>';
	}
	?>
	<div class="noghre-booking-wrap">
		<div class="noghre-booking-desc"><?php echo wp_kses_post( $o['booking_description'] ); ?></div>

		<form id="noghre-booking-form" class="noghre-booking-form" novalidate>
			<div class="noghre-booking-grid">
				<div class="noghre-booking-col">
					<p>
						<label>نام و نام خانوادگی</label>
						<input type="text" name="customer_name" required>
					</p>
					<p>
						<label>شماره موبایل</label>
						<input type="tel" name="customer_mobile" inputmode="numeric" pattern="09[0-9]{9}" maxlength="11" dir="ltr" required>
					</p>
					<p>
						<label>روز مراجعه</label>
						<select name="visit_day" required>
							<option value="">— انتخاب کنید —</option>
							<?php for ( $i = 0; $i <= 6; $i++ ) :
								$ts = strtotime( "+{$i} day", current_time( 'timestamp' ) );
								$display = noghre_jalali_date( $ts, 'l Y/m/d' );
								$greg    = date( 'Y-m-d', $ts );
								?>
								<option value="<?php echo esc_attr( $greg ); ?>|<?php echo esc_attr( $display ); ?>"><?php echo esc_html( $display ); ?></option>
							<?php endfor; ?>
						</select>
					</p>
					<?php if ( (int) $o['discount_code_enabled'] ) : ?>
						<p>
							<label>کد تخفیف (اختیاری)</label>
							<span class="noghre-discount-check-row">
								<input type="text" name="discount_code" id="noghre-booking-discount-code" dir="ltr">
								<button type="button" class="btn btn-outline noghre-discount-check-btn">بررسی</button>
							</span>
							<span class="noghre-discount-check-message" aria-live="polite"></span>
						</p>
					<?php endif; ?>
				</div>

				<div class="noghre-booking-col">
					<p>
						<label>شعبه</label>
						<select name="branch" required>
							<option value="">— انتخاب کنید —</option>
							<?php foreach ( $branches as $b ) : ?>
								<option value="<?php echo esc_attr( $b['name'] ); ?>"><?php echo esc_html( $b['name'] ); ?></option>
							<?php endforeach; ?>
						</select>
					</p>
					<p>
						<label>خدمات درخواستی</label>
						<details class="noghre-multiselect">
							<summary><span class="noghre-multiselect-label">— انتخاب کنید —</span></summary>
							<div class="noghre-multiselect-panel">
								<?php foreach ( $services as $s ) : ?>
									<label class="noghre-multiselect-option">
										<input type="checkbox" name="services[]" value="<?php echo esc_attr( $s['name'] ); ?>">
										<?php echo esc_html( $s['name'] ); ?>
									</label>
								<?php endforeach; ?>
							</div>
						</details>
					</p>
					<p>
						<label>ساعت پیشنهادی شما</label>
						<select name="preferred_time" required>
							<option value="">— انتخاب کنید —</option>
							<?php foreach ( $hours as $h ) : ?>
								<option value="<?php echo esc_attr( $h['time'] ); ?>"><?php echo esc_html( noghre_to_persian_digits( $h['time'] ) ); ?></option>
							<?php endforeach; ?>
						</select>
					</p>
				</div>
			</div>

			<?php if ( (int) $o['payment_enabled'] && (int) $o['payment_amount'] > 0 ) : ?>
				<p class="noghre-booking-payment-note">
					پرداخت مبلغ <?php echo esc_html( noghre_to_persian_digits( number_format_i18n( $o['payment_amount'] ) ) ); ?> تومان جهت رزرو نوبت الزامی است.
				</p>
			<?php endif; ?>

			<?php noghre_captcha_render( 'booking' ); ?>

			<span class="noghre-honeypot" aria-hidden="true">
				<label for="booking_website">وب‌سایت</label>
				<input type="text" id="booking_website" name="website" tabindex="-1" autocomplete="off">
			</span>

			<button type="submit" class="btn btn-primary noghre-booking-submit">
				<?php echo ( (int) $o['payment_enabled'] && (int) $o['payment_amount'] > 0 )
					? esc_html( $o['pay_button_text'] )
					: esc_html( $o['reserve_button_text'] ); ?>
			</button>

			<p class="noghre-booking-message" aria-live="polite"></p>
		</form>
	</div>
	<?php
	return ob_get_clean();
}
add_shortcode( 'ariatin-noghreh-nobat', 'noghre_shortcode_booking_form' );

/* ============================================================
 * AJAX: ثبت رزرو
 * ============================================================ */
function noghre_ajax_booking_submit() {
	check_ajax_referer( 'noghre_booking_submit', 'nonce' );

	if ( ! empty( $_POST['website'] ) ) {
		wp_send_json_error( array( 'message' => 'درخواست نامعتبر است.' ) );
	}

	$captcha_check = noghre_captcha_verify_request();
	if ( is_wp_error( $captcha_check ) ) {
		wp_send_json_error( array( 'message' => $captcha_check->get_error_message() ) );
	}

	$name   = isset( $_POST['customer_name'] ) ? sanitize_text_field( wp_unslash( $_POST['customer_name'] ) ) : '';
	$mobile = isset( $_POST['customer_mobile'] ) ? preg_replace( '/\D/', '', wp_unslash( $_POST['customer_mobile'] ) ) : '';
	$branch = isset( $_POST['branch'] ) ? sanitize_text_field( wp_unslash( $_POST['branch'] ) ) : '';
	$services = isset( $_POST['services'] ) && is_array( $_POST['services'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['services'] ) ) : array();
	$visit_day_raw = isset( $_POST['visit_day'] ) ? sanitize_text_field( wp_unslash( $_POST['visit_day'] ) ) : '';
	$preferred_time = isset( $_POST['preferred_time'] ) ? sanitize_text_field( wp_unslash( $_POST['preferred_time'] ) ) : '';
	$discount_code = isset( $_POST['discount_code'] ) ? sanitize_text_field( wp_unslash( $_POST['discount_code'] ) ) : '';

	if ( mb_strlen( $name ) < 2 ) wp_send_json_error( array( 'message' => 'لطفاً نام و نام خانوادگی را کامل وارد کنید.' ) );
	if ( ! preg_match( '/^09\d{9}$/', $mobile ) ) wp_send_json_error( array( 'message' => 'شماره موبایل باید ۱۱ رقم و با ۰۹ شروع شود.' ) );
	if ( ! $branch ) wp_send_json_error( array( 'message' => 'لطفاً شعبه را انتخاب کنید.' ) );
	if ( empty( $services ) ) wp_send_json_error( array( 'message' => 'لطفاً حداقل یک خدمت انتخاب کنید.' ) );
	if ( ! $visit_day_raw || strpos( $visit_day_raw, '|' ) === false ) wp_send_json_error( array( 'message' => 'لطفاً روز مراجعه را انتخاب کنید.' ) );
	if ( ! $preferred_time ) wp_send_json_error( array( 'message' => 'لطفاً ساعت پیشنهادی را انتخاب کنید.' ) );

	if ( $discount_code ) {
		$dc_check = noghre_validate_discount_code( $discount_code, $mobile, 'booking', $services );
		if ( is_wp_error( $dc_check ) ) {
			wp_send_json_error( array( 'message' => $dc_check->get_error_message() ) );
		}
	}

	list( $visit_day_gregorian, $visit_day_display ) = explode( '|', $visit_day_raw, 2 );

	$branch_manager_mobile = '';
	foreach ( noghre_get_booking_branches() as $b ) {
		if ( $b['name'] === $branch ) { $branch_manager_mobile = $b['manager_mobile']; break; }
	}

	$o = noghre_get_booking_options();
	$payment_required = (int) $o['payment_enabled'] && (int) $o['payment_amount'] > 0;

	$booking = noghre_create_booking( array(
		'customer_name'         => $name,
		'customer_mobile'       => $mobile,
		'branch'                => $branch,
		'branch_manager_mobile' => $branch_manager_mobile,
		'services'              => $services,
		'visit_day_display'     => sanitize_text_field( $visit_day_display ),
		'visit_day_gregorian'   => sanitize_text_field( $visit_day_gregorian ),
		'preferred_time'        => $preferred_time,
		'discount_code'         => $discount_code,
		'payment_required'      => $payment_required ? 1 : 0,
		'payment_amount'        => $o['payment_amount'],
		'payment_status'        => $payment_required ? 'pending' : 'none',
	) );

	if ( ! $booking ) {
		wp_send_json_error( array( 'message' => 'ثبت رزرو با خطا مواجه شد، دوباره تلاش کنید.' ) );
	}

	$check_page_url   = noghre_get_check_page_url();
	$booking_page_url = noghre_get_booking_page_url();

	noghre_sync_phonebooks_after_booking( $mobile, $name );

	if ( $payment_required ) {
		$callback_url = add_query_arg( array(
			'noghre_zarinpal_callback' => 1,
			'booking_id'               => $booking['post_id'],
		), home_url( '/' ) );

		$result = noghre_zarinpal_request( $o['payment_amount'], 'پرداخت حق رزرو نوبت سالن نقره', $callback_url, $mobile );

		if ( is_wp_error( $result ) ) {
			noghre_log_error( 'درخواست پرداخت زرین‌پال', $result->get_error_message() );
			wp_send_json_error( array( 'message' => 'اتصال به درگاه پرداخت ناموفق بود: ' . $result->get_error_message() ) );
		}

		update_post_meta( $booking['post_id'], '_zarinpal_authority', $result['authority'] );
		wp_send_json_success( array( 'redirect_url' => $result['redirect_url'] ) );
	}

	if ( $discount_code ) {
		noghre_consume_discount_code( $discount_code, $mobile );
	}

	noghre_send_booking_notifications( $booking['post_id'] );

	$tracking_url = $check_page_url ? add_query_arg( 'code', $booking['tracking_code'], $check_page_url ) : '';

	if ( $tracking_url ) {
		wp_send_json_success( array( 'redirect_url' => $tracking_url ) );
	}

	wp_send_json_success( array(
		'message'       => 'رزرو شما با موفقیت ثبت شد. کد پیگیری شما: ' . $booking['tracking_code'],
		'tracking_code' => $booking['tracking_code'],
	) );
}
add_action( 'wp_ajax_noghre_booking_submit', 'noghre_ajax_booking_submit' );
add_action( 'wp_ajax_nopriv_noghre_booking_submit', 'noghre_ajax_booking_submit' );

/* ============================================================
 * AJAX: بررسی کد تخفیف
 * ============================================================ */
function noghre_ajax_check_discount_code() {
	check_ajax_referer( 'noghre_booking_submit', 'nonce' );

	$code     = isset( $_POST['code'] ) ? sanitize_text_field( wp_unslash( $_POST['code'] ) ) : '';
	$mobile   = isset( $_POST['mobile'] ) ? preg_replace( '/\D/', '', wp_unslash( $_POST['mobile'] ) ) : '';
	$services = isset( $_POST['services'] ) && is_array( $_POST['services'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['services'] ) ) : array();

	if ( $code === '' ) {
		wp_send_json_error( array( 'message' => 'کد تخفیف را وارد کنید.' ) );
	}

	$result = noghre_validate_discount_code( $code, $mobile, 'booking', $services );

	if ( is_wp_error( $result ) ) {
		wp_send_json_error( array( 'message' => $result->get_error_message() ) );
	}

	$c = $result['code'];
	$desc = noghre_discount_code_description( $c );

	wp_send_json_success( array( 'message' => 'کد تخفیف معتبر است: ' . $desc ) );
}
add_action( 'wp_ajax_noghre_check_discount_code', 'noghre_ajax_check_discount_code' );
add_action( 'wp_ajax_nopriv_noghre_check_discount_code', 'noghre_ajax_check_discount_code' );