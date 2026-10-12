<?php
/**
 * تب عمومی — تنظیمات عمومی سایت
 *
 * @package Noghre
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/* نکته: تابع noghre_sanitize_general_options به inc/options-helper.php منتقل شد */

/* ==========================================================================
 * قالب‌های کمکی فیلد
 * ========================================================================== */
function noghre_field_text( $name, $label, $value, $type = 'text', $desc = '' ) {
	printf(
		'<tr><th><label for="%1$s">%2$s</label></th><td><input type="%3$s" id="%1$s" name="noghre_general_options[%1$s]" value="%4$s" class="regular-text">%5$s</td></tr>',
		esc_attr( $name ), esc_html( $label ), esc_attr( $type ), esc_attr( $value ),
		$desc ? '<p class="description">' . esc_html( $desc ) . '</p>' : ''
	);
}
function noghre_field_textarea( $name, $label, $value, $rows = 4, $desc = '' ) {
	printf(
		'<tr><th><label for="%1$s">%2$s</label></th><td><textarea id="%1$s" name="noghre_general_options[%1$s]" rows="%3$d" class="large-text">%4$s</textarea>%5$s</td></tr>',
		esc_attr( $name ), esc_html( $label ), (int) $rows, esc_textarea( $value ),
		$desc ? '<p class="description">' . esc_html( $desc ) . '</p>' : ''
	);
}
function noghre_field_checkbox( $name, $label, $checked, $desc = '' ) {
	printf(
		'<tr><th>%2$s</th><td><label><input type="checkbox" name="noghre_general_options[%1$s]" value="1" %3$s> %4$s</label>%5$s</td></tr>',
		esc_attr( $name ), esc_html( $label ), checked( $checked, 1, false ), esc_html( $desc ),
		$desc ? '' : ''
	);
}
function noghre_field_media( $name, $label, $image_id, $hint = '' ) {
	$img = $image_id ? wp_get_attachment_image_url( $image_id, 'medium' ) : '';
	printf(
		'<tr><th>%2$s</th><td><div class="noghre-media-field" style="text-align:right;">
			<img src="%3$s" class="noghre-media-preview" style="%4$smax-width:220px;height:auto;display:block;margin-bottom:8px;border:1px solid #dcdcde;border-radius:4px;padding:4px;background:#f0f0f1;">
			<input type="hidden" name="noghre_general_options[%1$s]" class="noghre-media-id" value="%5$s">
			<button type="button" class="button noghre-media-select">انتخاب تصویر</button>
			<button type="button" class="button noghre-media-remove">حذف تصویر</button>
		</div>%6$s</td></tr>',
		esc_attr( $name ), esc_html( $label ), esc_url( $img ), $img ? '' : 'display:none;', esc_attr( $image_id ),
		$hint ? '<p class="description"><strong>سایز استاندارد پیشنهادی:</strong> ' . esc_html( $hint ) . '</p>' : ''
	);
}
function noghre_field_visibility( $prefix, $o ) {
	printf(
		'<tr><th>نمایش در دستگاه‌ها</th><td>
			<label style="margin-left:16px;"><input type="checkbox" name="noghre_general_options[%1$s_v_desktop]" value="1" %2$s> دسکتاپ</label>
			<label style="margin-left:16px;"><input type="checkbox" name="noghre_general_options[%1$s_v_tablet]" value="1" %3$s> تبلت</label>
			<label><input type="checkbox" name="noghre_general_options[%1$s_v_mobile]" value="1" %4$s> موبایل</label>
		</td></tr>',
		esc_attr( $prefix ),
		checked( $o[ "{$prefix}_v_desktop" ] ?? 1, 1, false ),
		checked( $o[ "{$prefix}_v_tablet" ] ?? 1, 1, false ),
		checked( $o[ "{$prefix}_v_mobile" ] ?? 1, 1, false )
	);
}

/* ==========================================================================
 * رندر صفحه تنظیمات عمومی
 * ========================================================================== */
function noghre_render_general_page() {
	$o = noghre_get_general_options();
	$menus = wp_get_nav_menus();
	?>
	<form method="post" action="options.php">
		<?php settings_fields( 'noghre_general_group' ); ?>

		<h2 class="noghre-section-heading">
			<span class="dashicons dashicons-align-center"></span>
			هدر و فوتر
		</h2>
		<table class="form-table">
			<?php noghre_field_checkbox( 'sticky_header_enabled', 'هدر مگنتی (چسبیدن به بالای صفحه هنگام اسکرول)', $o['sticky_header_enabled'] ); ?>
			<?php noghre_field_media( 'site_logo_id', 'لوگوی سایت (نمایش در هدر)', $o['site_logo_id'], 'تصویر افقی با پس‌زمینه شفاف (PNG/SVG)، هم‌عرض با نوار منو — حدود ۳۰۰ تا ۴۰۰ در ۶۰ تا ۸۰ پیکسل.' ); ?>
			<tr>
				<th><label for="header_menu_id">منوی هدر</label></th>
				<td>
					<select id="header_menu_id" name="noghre_general_options[header_menu_id]">
						<option value="0">— پیش‌فرض (محل منوی «هدر» در نمایش ← منوها) —</option>
						<?php foreach ( $menus as $menu ) : ?>
							<option value="<?php echo esc_attr( $menu->term_id ); ?>" <?php selected( $o['header_menu_id'], $menu->term_id ); ?>><?php echo esc_html( $menu->name ); ?></option>
						<?php endforeach; ?>
					</select>
					<p class="description">فهرست بالای سایت.</p>
				</td>
			</tr>
			<tr>
				<th><label for="footer_menu_id">منوی فوتر</label></th>
				<td>
					<select id="footer_menu_id" name="noghre_general_options[footer_menu_id]">
						<option value="0">— پیش‌فرض (محل منوی «فوتر» در نمایش ← منوها) —</option>
						<?php foreach ( $menus as $menu ) : ?>
							<option value="<?php echo esc_attr( $menu->term_id ); ?>" <?php selected( $o['footer_menu_id'], $menu->term_id ); ?>><?php echo esc_html( $menu->name ); ?></option>
						<?php endforeach; ?>
					</select>
					<p class="description">فهرست کوچک کنار کپی‌رایت در پایین‌ترین نوار سایت.</p>
				</td>
			</tr>
		</table>

		<h2 class="noghre-section-heading">
			<span class="dashicons dashicons-menu"></span>
			نوار باریک بالای هدر
		</h2>
		<table class="form-table">
			<?php noghre_field_checkbox( 'topbar_enabled', 'فعال‌سازی نوار بالای هدر', $o['topbar_enabled'], 'سمت راست: آدرس سالن — سمت چپ: شماره موبایل و تلفن ثابت.' ); ?>
			<?php noghre_field_visibility( 'topbar', $o ); ?>
		</table>

		<h2 class="noghre-section-heading">
			<span class="dashicons dashicons-button"></span>
			دکمه هدر
		</h2>
		<table class="form-table">
			<?php noghre_field_checkbox( 'header_btn_enabled', 'فعال‌سازی دکمه در هدر', $o['header_btn_enabled'] ); ?>
			<?php noghre_field_text( 'header_btn_text', 'متن دکمه', $o['header_btn_text'] ); ?>
			<?php noghre_field_text( 'header_btn_url', 'لینک دکمه', $o['header_btn_url'], 'url' ); ?>
			<tr>
				<th>نحوه باز شدن لینک</th>
				<td>
					<label><input type="radio" name="noghre_general_options[header_btn_target]" value="_self" <?php checked( $o['header_btn_target'], '_self' ); ?>> در همان صفحه</label>
					&nbsp;&nbsp;
					<label><input type="radio" name="noghre_general_options[header_btn_target]" value="_blank" <?php checked( $o['header_btn_target'], '_blank' ); ?>> در تب/صفحه جدید</label>
				</td>
			</tr>
			<?php noghre_field_visibility( 'header_btn', $o ); ?>
		</table>

		<h2 class="noghre-section-heading">
			<span class="dashicons dashicons-cover-image"></span>
			معرفی سالن (بخش هیرو)
		</h2>
		<table class="form-table">
			<?php noghre_field_text( 'hero_title', 'عنوان اصلی', $o['hero_title'] ); ?>
			<?php noghre_field_textarea( 'hero_text', 'متن معرفی سالن', $o['hero_text'], 6 ); ?>
			<?php noghre_field_media( 'hero_image_id', 'تصویر معرفی سالن', $o['hero_image_id'], 'قاب عمودی — حدود ۸۰۰ در ۱۰۰۰ پیکسل.' ); ?>
			<?php noghre_field_text( 'tagline_1', 'شعار اول', $o['tagline_1'] ); ?>
			<?php noghre_field_text( 'tagline_2', 'شعار دوم', $o['tagline_2'] ); ?>
			<?php noghre_field_checkbox( 'hero_sparkle_enabled', 'جلوه اکلیلی روی تصویر معرفی', $o['hero_sparkle_enabled'] ); ?>
			<?php noghre_field_checkbox( 'hero_badges_enabled', 'نمایش حباب‌های شعار دور تصویر', $o['hero_badges_enabled'] ); ?>
			<?php noghre_field_text( 'hero_badge_1', 'متن حباب ۱', $o['hero_badge_1'] ); ?>
			<?php noghre_field_text( 'hero_badge_2', 'متن حباب ۲', $o['hero_badge_2'] ); ?>
			<?php noghre_field_text( 'hero_badge_3', 'متن حباب ۳', $o['hero_badge_3'] ); ?>
		</table>

		<h2 class="noghre-section-heading">
			<span class="dashicons dashicons-calendar-alt"></span>
			دکمه رزرو نوبت
		</h2>
		<table class="form-table">
			<?php noghre_field_text( 'reserve_btn_text', 'متن دکمه', $o['reserve_btn_text'] ); ?>
			<?php noghre_field_text( 'reserve_btn_url', 'لینک دکمه', $o['reserve_btn_url'], 'url' ); ?>
			<tr>
				<th>نحوه باز شدن لینک</th>
				<td>
					<label><input type="radio" name="noghre_general_options[reserve_btn_target]" value="_blank" <?php checked( $o['reserve_btn_target'], '_blank' ); ?>> در تب/صفحه جدید</label>
					&nbsp;&nbsp;
					<label><input type="radio" name="noghre_general_options[reserve_btn_target]" value="_self" <?php checked( $o['reserve_btn_target'], '_self' ); ?>> در همان صفحه</label>
				</td>
			</tr>
		</table>

		<h2 class="noghre-section-heading">
			<span class="dashicons dashicons-grid-view"></span>
			بخش‌های معرفی (۴ ویژگی)
		</h2>
		<table class="form-table">
			<?php for ( $i = 1; $i <= 4; $i++ ) : ?>
				<?php noghre_field_text( "feature_{$i}_title", "عنوان ویژگی {$i}", $o["feature_{$i}_title"] ); ?>
				<?php noghre_field_textarea( "feature_{$i}_text", "متن ویژگی {$i}", $o["feature_{$i}_text"], 2 ); ?>
			<?php endfor; ?>
		</table>

		<h2 class="noghre-section-heading">
			<span class="dashicons dashicons-instagram"></span>
			بخش تبلیغاتی اینستاگرام
		</h2>
		<table class="form-table">
			<?php noghre_field_text( 'instagram_promo_title', 'عنوان', $o['instagram_promo_title'] ); ?>
			<?php noghre_field_textarea( 'instagram_promo_text', 'متن', $o['instagram_promo_text'], 3 ); ?>
			<?php noghre_field_text( 'instagram_promo_btn_text', 'متن دکمه', $o['instagram_promo_btn_text'] ); ?>
			<?php noghre_field_text( 'instagram_promo_btn_url', 'لینک دکمه', $o['instagram_promo_btn_url'], 'url' ); ?>
			<?php noghre_field_text( 'instagram_promo_watermark', 'متن واترمارک', $o['instagram_promo_watermark'] ); ?>
			<?php noghre_field_media( 'instagram_promo_image_id', 'تصویر موبایل', $o['instagram_promo_image_id'], 'قاب موبایل با پس‌زمینه شفاف (PNG).' ); ?>
			<tr>
				<th>موقعیت تصویر موبایل</th>
				<td>
					<label><input type="radio" name="noghre_general_options[instagram_promo_position]" value="right" <?php checked( $o['instagram_promo_position'], 'right' ); ?>> سمت راست</label>
					&nbsp;&nbsp;
					<label><input type="radio" name="noghre_general_options[instagram_promo_position]" value="left" <?php checked( $o['instagram_promo_position'], 'left' ); ?>> سمت چپ</label>
				</td>
			</tr>
		</table>

		<h2 class="noghre-section-heading">
			<span class="dashicons dashicons-images-alt2"></span>
			تبلیغات وبلاگ
		</h2>
		<table class="form-table">
			<?php noghre_field_checkbox( 'ad_enabled', 'فعال‌سازی بخش تبلیغات', $o['ad_enabled'] ); ?>
			<?php noghre_field_media( 'ad_col1_image_id', 'بنر ستون اول', $o['ad_col1_image_id'], 'بنر افقی — حدود ۶۰۰ در ۲۰۰ پیکسل.' ); ?>
			<?php noghre_field_text( 'ad_col1_link', 'لینک بنر ستون اول', $o['ad_col1_link'], 'url' ); ?>
			<?php noghre_field_media( 'ad_col2_image_id', 'بنر ستون دوم', $o['ad_col2_image_id'], 'بنر افقی — حدود ۶۰۰ در ۲۰۰ پیکسل.' ); ?>
			<?php noghre_field_text( 'ad_col2_link', 'لینک بنر ستون دوم', $o['ad_col2_link'], 'url' ); ?>
			<?php noghre_field_visibility( 'ad', $o ); ?>
		</table>

		<h2 class="noghre-section-heading">
			<span class="dashicons dashicons-phone"></span>
			آدرس، تماس و شبکه‌های اجتماعی
		</h2>
		<table class="form-table">
			<?php noghre_field_text( 'address', 'آدرس سالن', $o['address'] ); ?>
			<?php noghre_field_text( 'mobile_phone', 'شماره موبایل', $o['mobile_phone'] ); ?>
			<?php noghre_field_text( 'phone', 'شماره تلفن ثابت', $o['phone'] ); ?>
			<?php noghre_field_text( 'map_url', 'لینک لوکیشن', $o['map_url'], 'url' ); ?>
			<?php noghre_field_text( 'map_link_text', 'متن نمایشی لینک نقشه', $o['map_link_text'] ); ?>
			<?php noghre_field_text( 'instagram_url', 'آیدی/لینک اینستاگرام', $o['instagram_url'], 'url' ); ?>
			<?php noghre_field_text( 'telegram_url', 'آیدی/لینک تلگرام', $o['telegram_url'], 'url' ); ?>
			<?php noghre_field_text( 'whatsapp_url', 'لینک واتس‌اپ', $o['whatsapp_url'], 'url' ); ?>
		</table>

		<h2 class="noghre-section-heading">
			<span class="dashicons dashicons-tag"></span>
			باشگاه تخفیف
		</h2>
		<table class="form-table">
			<?php noghre_field_checkbox( 'discount_box_enabled', 'فعال‌سازی باکس ثبت‌نام تخفیف', $o['discount_box_enabled'] ); ?>
			<?php noghre_field_text( 'discount_box_title', 'متن عنوان باکس', $o['discount_box_title'] ); ?>
			<?php noghre_field_text( 'discount_box_button_text', 'متن دکمه ثبت‌نام', $o['discount_box_button_text'] ); ?>
		</table>

		<h2 class="noghre-section-heading">
			<span class="dashicons dashicons-shield"></span>
			امنیت و ضد اسپم
		</h2>
		<table class="form-table">
			<?php noghre_field_checkbox( 'captcha_enabled', 'فعال‌سازی سوال امنیتی', $o['captcha_enabled'] ); ?>
			<tr>
				<th>نوع سوال امنیتی</th>
				<td>
					<label style="display:block;margin-bottom:8px;"><input type="radio" name="noghre_general_options[captcha_type]" value="math" <?php checked( $o['captcha_type'], 'math' ); ?>> معادلات ریاضی ساده</label>
					<label style="display:block;margin-bottom:8px;"><input type="radio" name="noghre_general_options[captcha_type]" value="riddle" <?php checked( $o['captcha_type'], 'riddle' ); ?>> سوالات معمایی ساده</label>
					<label style="display:block;"><input type="radio" name="noghre_general_options[captcha_type]" value="longpress" <?php checked( $o['captcha_type'], 'longpress' ); ?>> لمس طولانی دکمه ثبت</label>
				</td>
			</tr>
		</table>

		<h2 class="noghre-section-heading">
			<span class="dashicons dashicons-qr"></span>
			QR کد صفحه پیگیری رزرو
		</h2>
		<table class="form-table">
			<?php noghre_field_text( 'salon_name_for_qr', 'نام سالن (برای متن زیر QR کد)', $o['salon_name_for_qr'] ); ?>
		</table>

		<h2 class="noghre-section-heading">
			<span class="dashicons dashicons-location"></span>
			بخش «نقره منتظر شماست» — نقشه
		</h2>
		<table class="form-table">
			<?php noghre_field_checkbox( 'contact_map_embed_enabled', 'نمایش دو ستونه همراه نقشه', $o['contact_map_embed_enabled'] ); ?>
			<?php noghre_field_textarea( 'contact_map_embed_code', 'کد امبد نقشه', $o['contact_map_embed_code'], 4 ); ?>
		</table>

		<h2 class="noghre-section-heading">
			<span class="dashicons dashicons-admin-post"></span>
			وبلاگ
		</h2>
		<table class="form-table">
			<?php noghre_field_text( 'blog_section_title', 'عنوان بخش وبلاگ در صفحه اصلی', $o['blog_section_title'] ); ?>
			<?php noghre_field_text( 'blog_posts_count', 'تعداد مطالب قابل بارگذاری', $o['blog_posts_count'], 'number' ); ?>
		</table>

		<h2 class="noghre-section-heading">
			<span class="dashicons dashicons-clock"></span>
			ساعات کاری فوتر
		</h2>
		<table class="form-table">
			<?php noghre_field_textarea( 'working_hours', 'ساعات کاری (هر خط یک ردیف)', $o['working_hours'], 4 ); ?>
		</table>

		<h2 class="noghre-section-heading">
			<span class="dashicons dashicons-awards"></span>
			چرخش مجوزها در فوتر
		</h2>
		<table class="form-table">
			<?php noghre_field_text( 'footer_licenses_interval', 'فاصله زمانی ورق‌خوردن (ثانیه)', $o['footer_licenses_interval'], 'number' ); ?>
		</table>

		<h2 class="noghre-section-heading">
			<span class="dashicons dashicons-editor-paragraph"></span>
			متن کپی‌رایت فوتر
		</h2>
		<table class="form-table">
			<?php noghre_field_checkbox( 'footer_rights_enabled', 'نمایش متن حقوق کپی‌رایت', $o['footer_rights_enabled'] ); ?>
			<?php noghre_field_textarea( 'footer_rights_text', 'متن حقوق کپی‌رایت', $o['footer_rights_text'], 3 ); ?>
		</table>

		<h2 class="noghre-section-heading">
			<span class="dashicons dashicons-art"></span>
			پس‌زمینه تزئینی سایت
		</h2>
		<table class="form-table">
			<?php noghre_field_checkbox( 'decorative_bg_enabled', 'نمایش اشکال کمرنگ تزئینی در پس‌زمینه سایت', $o['decorative_bg_enabled'] ); ?>
		</table>

		<h2 class="noghre-section-heading">
			<span class="dashicons dashicons-admin-page"></span>
			صفحه تک‌مطلب وبلاگ
		</h2>
		<table class="form-table">
			<?php noghre_field_checkbox( 'show_post_date', 'نمایش تاریخ انتشار مطلب', $o['show_post_date'] ); ?>
		</table>

		<h2 class="noghre-section-heading">
			<span class="dashicons dashicons-arrow-up-alt"></span>
			دکمه بازگشت به بالا
		</h2>
		<table class="form-table">
			<?php noghre_field_checkbox( 'scroll_top_enabled', 'فعال‌سازی دکمه شناور بازگشت به بالای صفحه', $o['scroll_top_enabled'] ); ?>
		</table>

		<h2 class="noghre-section-heading">
			<span class="dashicons dashicons-hammer"></span>
			حالت در دست تعمیر
		</h2>
		<table class="form-table">
			<?php noghre_field_checkbox( 'maintenance_enabled', 'فعال‌سازی حالت در دست تعمیر', $o['maintenance_enabled'] ); ?>
			<tr>
				<th><label for="maintenance_message">متن نمایشی در حالت تعمیر</label></th>
				<td>
					<?php
					wp_editor( $o['maintenance_message'], 'maintenance_message', array(
						'textarea_name' => 'noghre_general_options[maintenance_message]',
						'textarea_rows' => 6,
						'media_buttons' => false,
						'teeny'         => true,
					) );
					?>
				</td>
			</tr>
		</table>

		<?php submit_button( 'ذخیره تنظیمات' ); ?>
	</form>
	<?php
}