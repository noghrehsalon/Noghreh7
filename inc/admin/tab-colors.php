<?php
/**
 * تب رنگ‌بندی
 *
 * @package Noghre
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/* نکته: توابع noghre_default_colors، noghre_sanitize_colors و noghre_hex_to_rgba به inc/options-helper.php منتقل شدند */

function noghre_color_labels() {
	return array(
		'base' => array(
			'title' => 'رنگ‌های پایه سایت',
			'icon'  => 'dashicons-admin-appearance',
			'desc'  => 'رنگ‌های اصلی که در سراسر سایت استفاده می‌شوند.',
			'items' => array(
				'color_bg'       => 'پس‌زمینه اصلی سایت',
				'color_bg_alt'   => 'پس‌زمینه بخش‌های متناوب',
				'color_ink'      => 'رنگ اصلی متن و فوتر',
				'color_ink_soft' => 'رنگ متن کم‌رنگ‌تر (توضیحات)',
				'color_line'     => 'رنگ خطوط جداکننده',
			),
		),
		'brand' => array(
			'title' => 'رنگ‌های برند',
			'icon'  => 'dashicons-art',
			'desc'  => 'رنگ‌های اختصاصی برند که در دکمه‌ها، لینک‌ها و بخش‌های مهم استفاده می‌شوند.',
			'items' => array(
				'color_accent'   => 'رنگ اصلی برند',
				'color_accent_2' => 'رنگ برند در حالت هاور',
				'color_gold'     => 'رنگ طلایی',
				'color_taupe'    => 'رنگ خنثی',
				'color_blush'    => 'رنگ صورتی ملایم',
				'color_white'    => 'سفید',
			),
		),
	);
}

function noghre_render_colors_page() {
	$colors   = noghre_get_colors();
	$groups   = noghre_color_labels();
	$defaults = noghre_default_colors();
	?>
	<p style="margin:0 0 20px;color:#646970;line-height:1.9;">
		تمامی رنگ‌های به‌کاررفته در سایت از همین‌جا قابل تغییرند. برای بازگشت به رنگ‌های پیش‌فرض قالب، دکمه زیر را بزنید و سپس ذخیره کنید.
	</p>
	<p><button type="button" id="noghre-reset-colors" class="button button-secondary">بازگشت همه به رنگ‌های پیش‌فرض</button></p>

	<form method="post" action="options.php">
		<?php settings_fields( 'noghre_colors_group' ); ?>

		<?php foreach ( $groups as $group_key => $group ) : ?>
			<h2 class="noghre-section-heading">
				<span class="dashicons <?php echo esc_attr( $group['icon'] ); ?>"></span>
				<?php echo esc_html( $group['title'] ); ?>
			</h2>
			<p style="margin:0 0 16px;color:#646970;font-size:13px;"><?php echo esc_html( $group['desc'] ); ?></p>

			<table class="form-table">
				<?php foreach ( $group['items'] as $key => $label ) : ?>
					<tr>
						<th><label for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></label></th>
						<td>
							<input type="text"
								id="<?php echo esc_attr( $key ); ?>"
								name="noghre_colors[<?php echo esc_attr( $key ); ?>]"
								value="<?php echo esc_attr( $colors[ $key ] ?? $defaults[ $key ] ); ?>"
								class="noghre-color-field"
								data-default-color="<?php echo esc_attr( $defaults[ $key ] ); ?>">
						</td>
					</tr>
				<?php endforeach; ?>
			</table>
		<?php endforeach; ?>

		<?php submit_button( 'ذخیره رنگ‌ها' ); ?>
	</form>
	<?php
}