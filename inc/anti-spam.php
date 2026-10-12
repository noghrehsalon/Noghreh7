<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * کپچای داخلی (بدون نیاز به هیچ سرویس خارجی) با سه نوع قابل انتخاب از تنظیمات:
 * math (معادله ریاضی)، riddle (سوال معمایی)، longpress (لمس طولانی دکمه).
 * پاسخ صحیح هرگز مستقیم در HTML قرار نمی‌گیرد؛ فقط هش امضاشده‌ی آن در یک فیلد
 * مخفی فرستاده می‌شود و در سرور دوباره محاسبه و مقایسه می‌شود.
 */

function noghre_captcha_hash( $answer ) {
	return wp_hash( 'noghre_captcha_v1_' . $answer );
}

function noghre_captcha_riddles() {
	return array(
		array( 'q' => 'رنگ آسمان در یک روز صاف چیست؟', 'a' => 'آبی' ),
		array( 'q' => 'آب در چه دمایی (سانتی‌گراد) یخ می‌زند؟', 'a' => '0' ),
		array( 'q' => 'اولین روز هفته در تقویم ایران چه نام دارد؟', 'a' => 'شنبه' ),
		array( 'q' => 'رنگ برگ درختان در فصل بهار چیست؟', 'a' => 'سبز' ),
		array( 'q' => 'یک هفته چند روز دارد؟', 'a' => '7' ),
		array( 'q' => 'خورشید از کدام جهت طلوع می‌کند؟', 'a' => 'شرق' ),
		array( 'q' => 'رنگ خون چیست؟', 'a' => 'قرمز' ),
		array( 'q' => 'یک ساعت چند دقیقه است؟', 'a' => '60' ),
	);
}

function noghre_normalize_captcha_text( $text ) {
	$text = trim( (string) $text );
	$text = mb_strtolower( $text, 'UTF-8' );
	$text = str_replace( array( 'ي', 'ك' ), array( 'ی', 'ک' ), $text );
	$text = preg_replace( '/\s+/u', '', $text );
	return $text;
}

/**
 * رندر سوال امنیتی مطابق نوع انتخاب‌شده در تنظیمات. اگر غیرفعال باشد، چیزی نمایش نمی‌دهد.
 */
function noghre_captcha_render( $id_prefix = 'noghre' ) {
	if ( ! (int) noghre_get_option( 'captcha_enabled', 1 ) ) return;

	$type = noghre_get_option( 'captcha_type', 'math' );
	$id   = esc_attr( $id_prefix ) . '_captcha_answer';

	if ( $type === 'longpress' ) {
		?>
		<div class="noghre-captcha noghre-captcha-longpress">
			<input type="hidden" name="noghre_captcha_hash" class="noghre-captcha-hash" value="longpress">
			<input type="hidden" name="noghre_longpress_ok" class="noghre-longpress-ok" value="0">
			<p class="noghre-longpress-hint">برای جلوگیری از اسپم، دکمه ثبت را حداقل ۲ ثانیه نگه دارید.</p>
		</div>
		<?php
		return;
	}

	if ( $type === 'riddle' ) {
		$riddles = noghre_captcha_riddles();
		$riddle  = $riddles[ array_rand( $riddles ) ];
		$hash    = noghre_captcha_hash( noghre_normalize_captcha_text( $riddle['a'] ) );
		?>
		<div class="noghre-captcha">
			<label for="<?php echo esc_attr( $id ); ?>">
				برای جلوگیری از اسپم پاسخ دهید: <strong class="noghre-captcha-question"><?php echo esc_html( $riddle['q'] ); ?></strong>
				<button type="button" class="noghre-captcha-refresh" title="تغییر سوال امنیتی" aria-label="تغییر سوال امنیتی">↻</button>
			</label>
			<input type="text" autocomplete="off" id="<?php echo esc_attr( $id ); ?>" name="noghre_captcha_answer" class="noghre-captcha-input" required>
			<input type="hidden" name="noghre_captcha_hash" class="noghre-captcha-hash" value="<?php echo esc_attr( $hash ); ?>">
			<input type="hidden" name="noghre_captcha_kind" value="riddle">
		</div>
		<?php
		return;
	}

	/* پیش‌فرض: معادله ریاضی */
	$a      = wp_rand( 1, 9 );
	$b      = wp_rand( 1, 9 );
	$hash   = noghre_captcha_hash( (string) ( $a + $b ) );
	?>
	<div class="noghre-captcha">
		<label for="<?php echo esc_attr( $id ); ?>">
			برای جلوگیری از اسپم، حاصل جمع را وارد کنید: <strong class="noghre-captcha-question"><?php echo esc_html( $a ); ?> + <?php echo esc_html( $b ); ?> = ؟</strong>
			<button type="button" class="noghre-captcha-refresh" title="تغییر سوال امنیتی" aria-label="تغییر سوال امنیتی">↻</button>
		</label>
		<input type="text" inputmode="numeric" autocomplete="off" id="<?php echo esc_attr( $id ); ?>" name="noghre_captcha_answer" class="noghre-captcha-input" required>
		<input type="hidden" name="noghre_captcha_hash" class="noghre-captcha-hash" value="<?php echo esc_attr( $hash ); ?>">
		<input type="hidden" name="noghre_captcha_kind" value="math">
	</div>
	<?php
}

/**
 * بررسی پاسخ سوال امنیتی (فقط برای انواع math/riddle)
 * @return true|WP_Error
 */
function noghre_captcha_verify( $answer, $hash ) {
	$answer = trim( (string) $answer );
	if ( $answer === '' || ! $hash ) {
		return new WP_Error( 'noghre_captcha_empty', 'لطفاً پاسخ سوال امنیتی را وارد کنید.' );
	}
	$normalized = is_numeric( $answer ) ? $answer : noghre_normalize_captcha_text( $answer );
	if ( ! hash_equals( noghre_captcha_hash( $normalized ), $hash ) ) {
		return new WP_Error( 'noghre_captcha_wrong', 'پاسخ سوال امنیتی درست نیست، دوباره تلاش کنید.' );
	}
	return true;
}

/**
 * تابع یکپارچه بررسی کپچا بر اساس تنظیمات فعلی سایت — برای استفاده در همه فرم‌ها.
 * @return true|WP_Error
 */
function noghre_captcha_verify_request() {
	if ( ! (int) noghre_get_option( 'captcha_enabled', 1 ) ) return true;

	$type = noghre_get_option( 'captcha_type', 'math' );

	if ( $type === 'longpress' ) {
		$ok = isset( $_POST['noghre_longpress_ok'] ) && $_POST['noghre_longpress_ok'] === '1';
		return $ok ? true : new WP_Error( 'noghre_captcha_longpress', 'لطفاً دکمه ثبت را طبق راهنما نگه دارید.' );
	}

	$answer = isset( $_POST['noghre_captcha_answer'] ) ? wp_unslash( $_POST['noghre_captcha_answer'] ) : '';
	$hash   = isset( $_POST['noghre_captcha_hash'] ) ? wp_unslash( $_POST['noghre_captcha_hash'] ) : '';
	return noghre_captcha_verify( $answer, $hash );
}

/**
 * تولید یک سوال امنیتی جدید (برای بازخوانی از طریق AJAX پس از هر بار ارسال فرم)
 */
function noghre_ajax_refresh_captcha() {
	if ( ! (int) noghre_get_option( 'captcha_enabled', 1 ) ) {
		wp_send_json_success( array( 'question' => '', 'hash' => '' ) );
	}
	$type = noghre_get_option( 'captcha_type', 'math' );

	if ( $type === 'longpress' ) {
		wp_send_json_success( array( 'longpress' => true ) );
	}

	if ( $type === 'riddle' ) {
		$riddles = noghre_captcha_riddles();
		$riddle  = $riddles[ array_rand( $riddles ) ];
		$hash    = noghre_captcha_hash( noghre_normalize_captcha_text( $riddle['a'] ) );
		wp_send_json_success( array( 'question' => $riddle['q'], 'hash' => $hash ) );
	}

	$a    = wp_rand( 1, 9 );
	$b    = wp_rand( 1, 9 );
	$hash = noghre_captcha_hash( (string) ( $a + $b ) );
	wp_send_json_success( array(
		'question' => sprintf( '%d + %d = ؟', $a, $b ),
		'hash'     => $hash,
	) );
}
add_action( 'wp_ajax_noghre_refresh_captcha', 'noghre_ajax_refresh_captcha' );
add_action( 'wp_ajax_nopriv_noghre_refresh_captcha', 'noghre_ajax_refresh_captcha' );

/* ==========================================================================
 * هانی‌پات + کپچا برای فرم دیدگاه‌های وردپرس
 * ========================================================================== */
function noghre_comment_form_antispam_fields() {
	?>
	<p class="noghre-honeypot" aria-hidden="true">
		<label for="noghre_website">وب‌سایت (این فیلد را خالی بگذارید)</label>
		<input type="text" id="noghre_website" name="noghre_website" tabindex="-1" autocomplete="off" value="">
	</p>
	<p class="noghre-captcha-field">
		<?php noghre_captcha_render( 'comment' ); ?>
	</p>
	<?php
}
add_action( 'comment_form_after_fields', 'noghre_comment_form_antispam_fields' );
add_action( 'comment_form_logged_in_after', 'noghre_comment_form_antispam_fields' );

function noghre_check_comment_antispam( $commentdata ) {
	// هانی‌پات: اگر پر شده باشد قطعاً ربات است
	if ( ! empty( $_POST['noghre_website'] ) ) {
		wp_die( 'درخواست نامعتبر شناسایی شد.', 'خطا', array( 'response' => 403 ) );
	}

	$result = noghre_captcha_verify_request();
	if ( is_wp_error( $result ) ) {
		wp_die( esc_html( $result->get_error_message() ), 'خطای امنیتی', array( 'response' => 403, 'back_link' => true ) );
	}

	return $commentdata;
}
add_filter( 'preprocess_comment', 'noghre_check_comment_antispam' );
