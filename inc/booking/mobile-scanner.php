<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/* ============================================================
 * اسکنر QR کد رزرو
 * ============================================================ */

function noghre_render_qr_scanner_block( $admin_key ) {
	$ver = defined( 'NOGHRE_VERSION' ) ? NOGHRE_VERSION : '1.0.0';

	$jsqr_url  = NOGHRE_URI . '/plugins/qrcode-scanner/jsQR.js';
	$jsqr_path = NOGHRE_DIR . '/plugins/qrcode-scanner/jsQR.js';
	$has_jsqr  = file_exists( $jsqr_path );

	/* 🔑 enqueue اسکریپت‌ها */
	if ( $has_jsqr ) {
		wp_enqueue_script(
			'noghre-jsqr',
			$jsqr_url,
			array(),
			$ver,
			true
		);
	}

	wp_enqueue_script(
		'noghre-qr-scanner',
		NOGHRE_URI . '/assets/js/app-qr-scanner.js',
		array(),
		$ver,
		true
	);

	wp_enqueue_script(
		'noghre-mobile-scanner-init',
		NOGHRE_URI . '/assets/js/mobile-scanner-init.js',
		array( 'noghre-app-shared', 'noghre-qr-scanner' ),
		$ver,
		true
	);

	wp_localize_script( 'noghre-mobile-scanner-init', 'noghreScannerConfig', array(
		'baseUrl'  => home_url( '/mobile/' ),
		'adminKey' => $admin_key,
	) );

	ob_start();
	?>
	<section class="noghre-qr-scanner" id="noghre-qr-scanner">
		<div class="noghre-qr-scanner-header">
			<strong>اسکن QR کد رزرو</strong>
			<p>دوربین گوشی را به سمت QR کد رزرو بگیرید تا به‌طور خودکار باز شود.</p>
		</div>
		<div class="noghre-qr-scanner-video-wrap">
			<video id="noghre-qr-video" playsinline muted autoplay></video>
			<canvas id="noghre-qr-canvas" style="display:none;"></canvas>
			<div class="noghre-qr-scanner-frame"></div>
		</div>
		<p class="noghre-qr-scanner-status" id="noghre-qr-status">برای شروع، روی دکمه زیر بزنید.</p>
		<button type="button" class="noghre-qr-scanner-btn" id="noghre-qr-start">شروع اسکن</button>
		<button type="button" class="noghre-qr-scanner-btn is-secondary" id="noghre-qr-stop" style="display:none;">توقف اسکن</button>
	</section>
	<?php
	return ob_get_clean();
}

/**
 * شورت‌کد اسکنر QR
 */
function noghre_shortcode_qr_scanner() {
	if ( ! noghre_is_admin_request() ) {
		return '';
	}
	$content = noghre_render_qr_scanner_block( noghre_get_admin_access_key() );
	return noghre_wrap_in_app_frame( $content, 'scanner' );
}
add_shortcode( 'ariatin-noghreh-scanner', 'noghre_shortcode_qr_scanner' );