<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/* ============================================================
 * باکس ثبت شماره مشتری (برای اپ موبایل)
 * ============================================================ */

function noghre_get_signup_box_html( $customer_count, $admin_key ) {
	$ver = defined( 'NOGHRE_VERSION' ) ? NOGHRE_VERSION : '1.0.0';

	/* 🔑 enqueue اسکریپت اختصاصی باکس ثبت شماره */
	wp_enqueue_script(
		'noghre-mobile-signup',
		NOGHRE_URI . '/assets/js/mobile-signup.js',
		array( 'noghre-app-shared' ),
		$ver,
		true
	);

	wp_localize_script( 'noghre-mobile-signup', 'noghreSignupConfig', array(
		'ajaxUrl' => admin_url( 'admin-ajax.php' ),
		'nonce'   => wp_create_nonce( 'noghre_customer_signup' ),
	) );

	ob_start();
	?>
	<section class="noghre-app-signup-box" id="noghre-app-signup-box">
		<div class="noghre-app-signup-header">
			<strong>ثبت شماره در باشگاه مشتریان</strong>
			<span class="noghre-app-signup-count" data-count="<?php echo esc_attr( (int) $customer_count ); ?>">
				<?php echo esc_html( noghre_to_persian_digits( $customer_count ) ); ?> مخاطب
			</span>
		</div>
		<div class="noghre-app-signup-form">
			<input type="text" id="noghre-signup-name" placeholder="نام و نام خانوادگی" autocomplete="off">
			<input type="tel" id="noghre-signup-mobile" placeholder="09xxxxxxxxx" inputmode="numeric" maxlength="11" dir="ltr" autocomplete="off">
			<button type="button" id="noghre-signup-submit" class="btn btn-primary">افزودن</button>
		</div>
		<p class="noghre-app-signup-message" aria-live="polite"></p>
	</section>
	<?php
	return ob_get_clean();
}

/* 🔑 این تابع دیگه لازم نیست، ولی برای سازگاری نگه داشته می‌شه */
function noghre_get_signup_inline_script() {
	return '';
}

/**
 * شورت‌کد باکس ثبت شماره (در قاب اپ)
 */
function noghre_shortcode_customer_signup() {
	if ( ! noghre_is_admin_request() ) {
		return '';
	}

	$sms = noghre_get_sms_options();
	$customer_count = 0;
	if ( ! empty( $sms['sms_customer_club_phonebook_id'] ) ) {
		$phonebooks = noghre_sms_get_phonebooks();
		if ( ! is_wp_error( $phonebooks ) ) {
			foreach ( $phonebooks as $book ) {
				if ( (int) $book['id'] === (int) $sms['sms_customer_club_phonebook_id'] ) {
					$customer_count = (int) $book['count'];
					break;
				}
			}
		}
	}
	$admin_key = noghre_get_admin_access_key();

	return noghre_wrap_in_app_frame( noghre_get_signup_box_html( $customer_count, $admin_key ), 'signup' );
}
add_shortcode( 'ariatin-noghreh-signup', 'noghre_shortcode_customer_signup' );