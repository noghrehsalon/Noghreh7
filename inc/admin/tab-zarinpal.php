<?php
/**
 * تب درگاه پرداخت زرین‌پال
 *
 * @package Noghre
 */

if ( ! defined( 'ABSPATH' ) ) exit;

function noghre_render_zarinpal_page() {
	$o = noghre_get_zarinpal_options();
	?>
	<h2 class="noghre-section-heading">
		<span class="dashicons dashicons-money-alt"></span>
		تنظیمات درگاه پرداخت
	</h2>

	<p style="margin:0 0 20px;color:#646970;line-height:1.9;">
		این تنظیمات برای دریافت آنلاین «حق رزرو نوبت» در صفحه رزرو وقت استفاده می‌شود.
		مستندات رسمی زرین‌پال:
		<a href="https://www.zarinpal.com/docs/paymentGateway/connectToGateway.html" target="_blank" rel="noopener">راهنمای اتصال به درگاه</a> —
		<a href="https://www.zarinpal.com/docs/apiDocs/guide.html" target="_blank" rel="noopener">مستندات API</a>
	</p>

	<form method="post" action="options.php">
		<?php settings_fields( 'noghre_zarinpal_group' ); ?>
		<table class="form-table">
			<tr>
				<th><label for="zarinpal_merchant_id">کد پذیرنده (Merchant ID)</label></th>
				<td>
					<input type="text" id="zarinpal_merchant_id" name="noghre_zarinpal_options[zarinpal_merchant_id]" value="<?php echo esc_attr( $o['zarinpal_merchant_id'] ); ?>" class="regular-text" dir="ltr">
					<p class="description">کد ۳۶ کاراکتری اختصاصی پذیرنده که از پنل کاربری زرین‌پال دریافت می‌کنید.</p>
				</td>
			</tr>
			<tr>
				<th>حالت آزمایشی (Sandbox)</th>
				<td>
					<label><input type="checkbox" name="noghre_zarinpal_options[zarinpal_sandbox]" value="1" <?php checked( $o['zarinpal_sandbox'], 1 ); ?>> فعال‌سازی حالت تست بدون پرداخت واقعی</label>
					<p class="description">برای تست بدون کسر وجه واقعی؛ پیش از راه‌اندازی نهایی، این گزینه را غیرفعال کنید.</p>
				</td>
			</tr>
		</table>
		<?php submit_button( 'ذخیره تنظیمات درگاه پرداخت' ); ?>
	</form>
	<?php
}