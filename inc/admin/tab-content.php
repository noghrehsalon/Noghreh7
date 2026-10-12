<?php
/**
 * تب مدیریت محتوا
 * شامل ۴ زیرتب: چیدمان + پرسنل + سوالات + مجوزها
 *
 * @package Noghre
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/* نکته: تابع noghre_handle_content_post به inc/admin/admin-helpers.php منتقل شد */

/* ==========================================================================
 * رندر اصلی تب (زیرتب‌ها)
 * ========================================================================== */
function noghre_render_content_page() {
	$sub = isset( $_GET['sub'] ) ? sanitize_key( $_GET['sub'] ) : 'sections';

	$subtabs = array(
		'sections' => '<span class="dashicons dashicons-layout"></span> چیدمان صفحه اصلی',
		'team'     => '<span class="dashicons dashicons-businessperson"></span> پرسنل سالن',
		'faq'      => '<span class="dashicons dashicons-editor-help"></span> سوالات متداول',
		'licenses' => '<span class="dashicons dashicons-awards"></span> مجوزها',
	);

	$sub_url = function( $key ) {
		return add_query_arg( array(
			'page' => 'noghre-panel',
			'tab'  => 'content',
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
			case 'team':      noghre_render_team_subtab(); break;
			case 'faq':       noghre_render_faq_subtab(); break;
			case 'licenses':  noghre_render_licenses_subtab(); break;
			case 'sections':
			default:          noghre_render_sections_subtab(); break;
		}
		?>
	</div>
	<?php
}

/* ==========================================================================
 * زیرتب ۱ — چیدمان صفحه اصلی
 * ========================================================================== */
function noghre_render_sections_subtab() {
	$sections   = noghre_get_sections();
	$labels     = noghre_section_labels();
	$pages      = get_pages( array( 'sort_column' => 'post_title' ) );
	$repeatable = noghre_repeatable_section_types();
	?>
	<h2 class="noghre-section-heading">
		<span class="dashicons dashicons-layout"></span>
		چیدمان بخش‌های صفحه اصلی
	</h2>
	<p style="margin:0 0 20px;color:#646970;line-height:1.9;">
		هر بخش را می‌توانید فعال/غیرفعال کنید، مشخص کنید در چه دستگاه‌هایی نمایش داده شود، و با درگ‌ودراپ، ترتیب نمایش آن را در صفحه اصلی تغییر دهید.
	</p>

	<form method="post">
		<?php wp_nonce_field( 'noghre_save_sections', 'noghre_sections_nonce' ); ?>
		<input type="hidden" name="section_order" id="noghre-section-order" value="<?php echo esc_attr( implode( ',', wp_list_pluck( $sections, 'id' ) ) ); ?>">

		<ul class="noghre-sortable" id="noghre-sections-list">
			<?php foreach ( $sections as $s ) : ?>
				<?php echo noghre_section_row( $s, $labels, $pages, $repeatable ); ?>
			<?php endforeach; ?>
		</ul>

		<p>
			<button type="button" class="button button-secondary" id="noghre-add-page-builder">افزودن بخش «محتوای برگه متصل» دیگر</button>
		</p>

		<template id="noghre-page-builder-row-template">
			<?php echo noghre_section_row( array( 'id' => '__ID__', 'key' => 'page_builder', 'enabled' => 1, 'page_id' => 0, 'visibility' => noghre_default_visibility() ), $labels, $pages, $repeatable ); ?>
		</template>

		<?php submit_button( 'ذخیره چیدمان' ); ?>
	</form>
	<?php
}

function noghre_section_row( $s, $labels, $pages, $repeatable ) {
	$id   = $s['id'];
	$type = $s['key'];
	$v    = wp_parse_args( $s['visibility'] ?? array(), noghre_default_visibility() );
	$is_repeatable = in_array( $type, $repeatable, true );
	ob_start();
	?>
	<li class="noghre-sortable-item" data-key="<?php echo esc_attr( $id ); ?>">
		<span class="noghre-drag-handle dashicons dashicons-menu-alt" title="جابه‌جایی"></span>
		<input type="hidden" name="section_type[<?php echo esc_attr( $id ); ?>]" value="<?php echo esc_attr( $type ); ?>">
		<label class="noghre-section-label">
			<input type="checkbox" name="enabled[<?php echo esc_attr( $id ); ?>]" value="1" <?php checked( ! empty( $s['enabled'] ) ); ?>>
			<?php echo esc_html( $labels[ $type ] ?? $type ); ?>
		</label>

		<span class="noghre-section-visibility">
			<label><input type="checkbox" name="visibility[<?php echo esc_attr( $id ); ?>][desktop]" value="1" <?php checked( $v['desktop'], 1 ); ?>> دسکتاپ</label>
			<label><input type="checkbox" name="visibility[<?php echo esc_attr( $id ); ?>][tablet]" value="1" <?php checked( $v['tablet'], 1 ); ?>> تبلت</label>
			<label><input type="checkbox" name="visibility[<?php echo esc_attr( $id ); ?>][mobile]" value="1" <?php checked( $v['mobile'], 1 ); ?>> موبایل</label>
		</span>

		<?php if ( $type === 'page_builder' ) : ?>
			<span class="noghre-section-extra">
				متصل به برگه:
				<select name="page_builder_page_id[<?php echo esc_attr( $id ); ?>]">
					<option value="0">— انتخاب نشده —</option>
					<?php foreach ( $pages as $p ) : ?>
						<option value="<?php echo esc_attr( $p->ID ); ?>" <?php selected( (int) ( $s['page_id'] ?? 0 ), $p->ID ); ?>><?php echo esc_html( $p->post_title ); ?></option>
					<?php endforeach; ?>
				</select>
			</span>
		<?php endif; ?>

		<?php if ( $is_repeatable ) : ?>
			<button type="button" class="button-link-delete noghre-remove-row" title="حذف این بخش"><span class="dashicons dashicons-no-alt"></span></button>
		<?php endif; ?>
	</li>
	<?php
	return ob_get_clean();
}

/* ==========================================================================
 * زیرتب ۲ — پرسنل سالن
 * ========================================================================== */
function noghre_render_team_subtab() {
	$members = noghre_get_team_members();
	?>
	<h2 class="noghre-section-heading">
		<span class="dashicons dashicons-businessperson"></span>
		پرسنل سالن
	</h2>
	<p style="margin:0 0 20px;color:#646970;line-height:1.9;">
		نام، نوع خدمات و لینک شبکه‌های اجتماعی هر یک از پرسنل سالن را اضافه کنید. آیکون هر شبکه فقط زمانی در سایت نمایش داده می‌شود که لینک آن پر شده باشد.
	</p>

	<form method="post">
		<?php wp_nonce_field( 'noghre_save_team', 'noghre_team_nonce' ); ?>
		<div class="noghre-repeater" id="noghre-team-repeater">
			<div class="noghre-repeater-rows">
				<?php foreach ( $members as $i => $m ) : ?>
					<?php noghre_team_row( $i, $m ); ?>
				<?php endforeach; ?>
			</div>
			<button type="button" class="button button-secondary noghre-add-row" data-target="#noghre-team-repeater">افزودن پرسنل جدید</button>
		</div>

		<template id="noghre-team-row-template">
			<?php noghre_team_row( '__INDEX__', array( 'name' => '', 'services' => '', 'instagram' => '', 'telegram' => '', 'whatsapp' => '', 'image_id' => 0 ) ); ?>
		</template>

		<?php submit_button( 'ذخیره لیست پرسنل' ); ?>
	</form>
	<?php
}

function noghre_team_row( $index, $m ) {
	$img = ! empty( $m['image_id'] ) ? wp_get_attachment_image_url( $m['image_id'], 'thumbnail' ) : '';
	?>
	<div class="noghre-repeater-row">
		<div class="noghre-media-field">
			<img src="<?php echo esc_url( $img ); ?>" class="noghre-media-preview" style="<?php echo $img ? '' : 'display:none;'; ?>width:80px;height:80px;object-fit:cover;border-radius:4px;">
			<input type="hidden" class="noghre-media-id" name="team[<?php echo esc_attr( $index ); ?>][image_id]" value="<?php echo esc_attr( $m['image_id'] ?? 0 ); ?>">
			<button type="button" class="button button-small noghre-media-select">انتخاب عکس</button>
			<p class="description" style="max-width:100px;">سایز پیشنهادی: ۵۰۰×۶۲۰</p>
		</div>
		<div class="noghre-repeater-fields">
			<input type="text" placeholder="نام پرسنل" name="team[<?php echo esc_attr( $index ); ?>][name]" value="<?php echo esc_attr( $m['name'] ?? '' ); ?>" class="regular-text">
			<input type="text" placeholder="نوع خدمات (مثلاً: رنگ و لایت مو)" name="team[<?php echo esc_attr( $index ); ?>][services]" value="<?php echo esc_attr( $m['services'] ?? '' ); ?>" class="regular-text">
			<input type="url" placeholder="لینک اینستاگرام" name="team[<?php echo esc_attr( $index ); ?>][instagram]" value="<?php echo esc_attr( $m['instagram'] ?? '' ); ?>" class="regular-text" dir="ltr">
			<input type="url" placeholder="لینک تلگرام" name="team[<?php echo esc_attr( $index ); ?>][telegram]" value="<?php echo esc_attr( $m['telegram'] ?? '' ); ?>" class="regular-text" dir="ltr">
			<input type="url" placeholder="لینک واتس‌اپ" name="team[<?php echo esc_attr( $index ); ?>][whatsapp]" value="<?php echo esc_attr( $m['whatsapp'] ?? '' ); ?>" class="regular-text" dir="ltr">
		</div>
		<button type="button" class="button-link-delete noghre-remove-row" title="حذف"><span class="dashicons dashicons-no-alt"></span></button>
	</div>
	<?php
}

/* ==========================================================================
 * زیرتب ۳ — سوالات متداول
 * ========================================================================== */
function noghre_render_faq_subtab() {
	$faqs = noghre_get_faqs();
	?>
	<h2 class="noghre-section-heading">
		<span class="dashicons dashicons-editor-help"></span>
		سوالات متداول
	</h2>
	<p style="margin:0 0 20px;color:#646970;line-height:1.9;">
		این سوالات به‌صورت آکاردئونی در صفحه اصلی سایت نمایش داده می‌شوند.
	</p>

	<form method="post">
		<?php wp_nonce_field( 'noghre_save_faq', 'noghre_faq_nonce' ); ?>
		<div class="noghre-repeater" id="noghre-faq-repeater">
			<div class="noghre-repeater-rows">
				<?php foreach ( $faqs as $i => $f ) : ?>
					<?php noghre_faq_row( $i, $f ); ?>
				<?php endforeach; ?>
			</div>
			<button type="button" class="button button-secondary noghre-add-row" data-target="#noghre-faq-repeater">افزودن سوال جدید</button>
		</div>

		<template id="noghre-faq-row-template">
			<?php noghre_faq_row( '__INDEX__', array( 'question' => '', 'answer' => '' ) ); ?>
		</template>

		<?php submit_button( 'ذخیره سوالات متداول' ); ?>
	</form>
	<?php
}

function noghre_faq_row( $index, $f ) {
	?>
	<div class="noghre-repeater-row noghre-repeater-row-stacked">
		<div class="noghre-repeater-fields">
			<input type="text" placeholder="متن سوال" name="faq[<?php echo esc_attr( $index ); ?>][question]" value="<?php echo esc_attr( $f['question'] ?? '' ); ?>" class="large-text">
			<textarea placeholder="متن پاسخ" name="faq[<?php echo esc_attr( $index ); ?>][answer]" rows="2" class="large-text"><?php echo esc_textarea( $f['answer'] ?? '' ); ?></textarea>
		</div>
		<button type="button" class="button-link-delete noghre-remove-row" title="حذف"><span class="dashicons dashicons-no-alt"></span></button>
	</div>
	<?php
}

/* ==========================================================================
 * زیرتب ۴ — مجوزها
 * ========================================================================== */
function noghre_render_licenses_subtab() {
	$licenses = noghre_get_licenses();
	?>
	<h2 class="noghre-section-heading">
		<span class="dashicons dashicons-awards"></span>
		مجوزها
	</h2>
	<p style="margin:0 0 20px;color:#646970;line-height:1.9;">
		هر مجوز می‌تواند به‌صورت «تصویر + لینک» یا «کد امبد» (مثلاً کد نماد اعتماد/ساماندهی) ثبت شود.
	</p>

	<form method="post">
		<?php wp_nonce_field( 'noghre_save_licenses', 'noghre_licenses_nonce' ); ?>
		<div class="noghre-repeater" id="noghre-licenses-repeater">
			<div class="noghre-repeater-rows">
				<?php foreach ( $licenses as $i => $l ) : ?>
					<?php noghre_license_row( $i, $l ); ?>
				<?php endforeach; ?>
			</div>
			<button type="button" class="button button-secondary noghre-add-row" data-target="#noghre-licenses-repeater">افزودن مجوز جدید</button>
		</div>

		<template id="noghre-licenses-row-template">
			<?php noghre_license_row( '__INDEX__', array( 'type' => 'image', 'admin_label' => '', 'image_id' => 0, 'caption' => '', 'link_url' => '', 'link_target' => '_blank', 'code' => '' ) ); ?>
		</template>

		<?php submit_button( 'ذخیره مجوزها' ); ?>
	</form>
	<?php
}

function noghre_license_row( $index, $l ) {
	$img  = ! empty( $l['image_id'] ) ? wp_get_attachment_image_url( $l['image_id'], 'thumbnail' ) : '';
	$type = $l['type'] ?? 'image';
	?>
	<div class="noghre-repeater-row noghre-repeater-row-stacked noghre-license-row">
		<div class="noghre-repeater-fields">
			<p>
				<label>نوع ثبت مجوز:
					<select class="noghre-license-type" name="licenses[<?php echo esc_attr( $index ); ?>][type]">
						<option value="image" <?php selected( $type, 'image' ); ?>>تصویر + لینک</option>
						<option value="code" <?php selected( $type, 'code' ); ?>>کد (امبد)</option>
					</select>
				</label>
			</p>
			<input type="text" placeholder="نام مجوز (فقط برای نمایش در مدیریت)" name="licenses[<?php echo esc_attr( $index ); ?>][admin_label]" value="<?php echo esc_attr( $l['admin_label'] ?? '' ); ?>" class="large-text">

			<div class="noghre-license-image-fields">
				<div class="noghre-media-field" style="text-align:right;margin:10px 0;">
					<img src="<?php echo esc_url( $img ); ?>" class="noghre-media-preview" style="<?php echo $img ? '' : 'display:none;'; ?>width:80px;height:80px;object-fit:cover;border-radius:4px;">
					<input type="hidden" class="noghre-media-id" name="licenses[<?php echo esc_attr( $index ); ?>][image_id]" value="<?php echo esc_attr( $l['image_id'] ?? 0 ); ?>">
					<button type="button" class="button button-small noghre-media-select">انتخاب تصویر مجوز</button>
					<p class="description" style="max-width:120px;">سایز پیشنهادی: ۴۰۰×۵۰۰</p>
				</div>
				<input type="text" placeholder="عنوان نمایشی زیر تصویر (اختیاری)" name="licenses[<?php echo esc_attr( $index ); ?>][caption]" value="<?php echo esc_attr( $l['caption'] ?? '' ); ?>" class="large-text">
				<input type="url" placeholder="لینک مجوز (اختیاری)" name="licenses[<?php echo esc_attr( $index ); ?>][link_url]" value="<?php echo esc_attr( $l['link_url'] ?? '' ); ?>" class="large-text">
				<p>
					<label><input type="radio" name="licenses[<?php echo esc_attr( $index ); ?>][link_target]" value="_blank" <?php checked( ( $l['link_target'] ?? '_blank' ), '_blank' ); ?>> باز شدن لینک در صفحه جدید</label>
					&nbsp;&nbsp;
					<label><input type="radio" name="licenses[<?php echo esc_attr( $index ); ?>][link_target]" value="_self" <?php checked( ( $l['link_target'] ?? '_blank' ), '_self' ); ?>> باز شدن لینک در همان صفحه</label>
				</p>
			</div>

			<div class="noghre-license-code-fields">
				<textarea placeholder="کد مجوز برای نمایش در صفحه اصلی (مثلاً کد نماد اعتماد الکترونیکی)" name="licenses[<?php echo esc_attr( $index ); ?>][code]" rows="4" class="large-text code"><?php echo esc_textarea( $l['code'] ?? '' ); ?></textarea>
			</div>
		</div>
		<button type="button" class="button-link-delete noghre-remove-row" title="حذف"><span class="dashicons dashicons-no-alt"></span></button>
	</div>
	<?php
}