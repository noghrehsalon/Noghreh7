<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/* ============================================================
 * صفحه ورود با شناسه مدیریت
 * ============================================================ */

function noghre_get_admin_login_html() {
	$app = noghre_get_app_options();
	$ver = defined( 'NOGHRE_VERSION' ) ? NOGHRE_VERSION : '1.0.0';

	/* 🔑 enqueue اسکریپت اختصاصی صفحه ورود */
	wp_enqueue_script(
		'noghre-mobile-login',
		NOGHRE_URI . '/assets/js/mobile-login.js',
		array( 'noghre-app-shared' ),
		$ver,
		true
	);

	wp_localize_script( 'noghre-mobile-login', 'noghreLoginConfig', array(
		'ajaxUrl'  => admin_url( 'admin-ajax.php' ),
		'nonce'    => wp_create_nonce( 'noghre_verify_admin_key' ),
		'baseUrl'  => home_url( '/mobile/' ),
		'maxLen'   => 8,
	) );

	ob_start();
	?>
	<div class="noghre-admin-login-page" id="noghre-admin-login-page">
		<div class="noghre-admin-login-card">
			<div class="noghre-admin-login-logo">
				<span><?php echo esc_html( $app['app_logo_text'] ); ?></span>
			</div>
			<h1 class="noghre-admin-login-title"><?php echo esc_html( $app['app_title'] ); ?></h1>
			<p class="noghre-admin-login-subtitle">برای ورود، شناسه مدیریت را وارد کنید</p>

			<form id="noghre-admin-login-form" method="post">
				<div class="noghre-admin-login-field">
					<label for="noghre-admin-key-input">شناسه مدیریت</label>
					<input
						type="text"
						id="noghre-admin-key-input"
						name="admin_key"
						placeholder="XXXXXXXX"
						dir="ltr"
						autocomplete="off"
						maxlength="8"
						required>
				</div>

				<button type="submit" class="noghre-admin-login-btn">ورود به پنل</button>

				<p class="noghre-admin-login-message" aria-live="polite"></p>
			</form>

			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="noghre-admin-login-back">بازگشت به سایت</a>
		</div>
	</div>
	<?php
	return ob_get_clean();
}

/* 🔑 این تابع دیگه لازم نیست، ولی برای سازگاری نگه داشته می‌شه */
function noghre_get_admin_login_script() {
	return '';
}

/**
 * AJAX: بررسی صحت شناسه مدیریت
 */
function noghre_ajax_verify_admin_key() {
	$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
	if ( ! wp_verify_nonce( $nonce, 'noghre_verify_admin_key' ) ) {
		wp_send_json_error( array( 'message' => 'نشست شما منقضی شده است. صفحه را دوباره بارگذاری کنید.' ) );
	}

	$key = isset( $_POST['key'] ) ? sanitize_text_field( wp_unslash( $_POST['key'] ) ) : '';
	$key = strtoupper( $key );

	if ( ! $key ) {
		wp_send_json_error( array( 'message' => 'شناسه مدیریت را وارد کنید.' ) );
	}

	if ( strlen( $key ) !== 8 ) {
		wp_send_json_error( array( 'message' => 'شناسه مدیریت باید ۸ کاراکتر باشد.' ) );
	}

	$current_key = noghre_get_admin_access_key();

	if ( $current_key === '' ) {
		wp_send_json_error( array( 'message' => 'شناسه مدیریتی در تنظیمات تعریف نشده است.' ) );
	}

	if ( ! hash_equals( $current_key, $key ) ) {
		wp_send_json_error( array( 'message' => 'شناسه مدیریت اشتباه است. لطفاً دوباره تلاش کنید.' ) );
	}

	wp_send_json_success( array( 'message' => 'شناسه معتبر است.' ) );
}
add_action( 'wp_ajax_noghre_verify_admin_key', 'noghre_ajax_verify_admin_key' );
add_action( 'wp_ajax_nopriv_noghre_verify_admin_key', 'noghre_ajax_verify_admin_key' );