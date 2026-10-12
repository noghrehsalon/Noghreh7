<?php
/**
 * تب اپلیکیشن — شامل دو زیرتب:
 *   ۱. مدیریت (اپ مدیر سالن — تنظیمات PWA و منوها)
 *   ۲. کاربران (به‌زودی)
 *
 * @package Noghre
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/* ==========================================================================
 * لیست dashiconهای پرکاربرد برای انتخاب
 * ========================================================================== */
function noghre_available_dashicons() {
	return array(
		'dashicons-admin-home'      => 'خانه',
		'dashicons-admin-generic'   => 'تنظیمات',
		'dashicons-admin-site'      => 'سایت',
		'dashicons-admin-users'     => 'کاربران',
		'dashicons-admin-tools'     => 'ابزار',
		'dashicons-dashboard'       => 'داشبورد',
		'dashicons-calendar-alt'    => 'تقویم',
		'dashicons-list-view'       => 'لیست',
		'dashicons-email'           => 'ایمیل',
		'dashicons-email-alt'       => 'ایمیل ۲',
		'dashicons-smartphone'      => 'موبایل',
		'dashicons-camera'          => 'دوربین',
		'dashicons-search'          => 'جستجو',
		'dashicons-plus'            => 'افزودن',
		'dashicons-yes'             => 'تایید',
		'dashicons-no'              => 'رد',
		'dashicons-info'            => 'اطلاعات',
		'dashicons-warning'         => 'هشدار',
		'dashicons-star-filled'     => 'ستاره',
		'dashicons-heart'           => 'قلب',
		'dashicons-tag'             => 'برچسب',
		'dashicons-cart'            => 'سبد خرید',
		'dashicons-money-alt'       => 'پول',
		'dashicons-chart-bar'       => 'نمودار',
		'dashicons-chart-line'      => 'خط نمودار',
		'dashicons-groups'          => 'گروه‌ها',
		'dashicons-businessperson'  => 'شخص',
		'dashicons-phone'           => 'تلفن',
		'dashicons-format-image'    => 'تصویر',
		'dashicons-format-gallery'  => 'گالری',
		'dashicons-visibility'      => 'مشاهده',
		'dashicons-location'        => 'مکان',
		'dashicons-clock'           => 'ساعت',
		'dashicons-lock'            => 'قفل',
		'dashicons-unlock'          => 'باز کردن',
		'dashicons-editor-help'     => 'راهنما',
		'dashicons-download'        => 'دانلود',
		'dashicons-upload'          => 'آپلود',
		'dashicons-share'           => 'اشتراک‌گذاری',
		'dashicons-menu'            => 'منو',
		'dashicons-screenoptions'   => 'گزینه‌ها',
	);
}

/* ==========================================================================
 * لیست شورت‌کدهای قابل استفاده در منوهای اپ
 * ========================================================================== */
function noghre_app_menu_shortcodes() {
	return array(
		''                        => '— انتخاب شورت‌کد —',
		'ariatin-noghreh-mobile'  => '[ariatin-noghreh-mobile] — صفحه اصلی اپ',
		'ariatin-noghreh-list'    => '[ariatin-noghreh-list] — لیست رزروها',
		'ariatin-noghreh-signup'  => '[ariatin-noghreh-signup] — ثبت شماره مشتری',
		'ariatin-noghreh-scanner' => '[ariatin-noghreh-scanner] — اسکنر QR کد',
	);
}

/* ==========================================================================
 * رندر اصلی تب (زیرتب‌ها)
 * ========================================================================== */
function noghre_render_app_page() {
	$sub = isset( $_GET['sub'] ) ? sanitize_key( $_GET['sub'] ) : 'management';

	$subtabs = array(
		'management' => '<span class="dashicons dashicons-admin-generic"></span> مدیریت',
		'users'      => '<span class="dashicons dashicons-admin-users"></span> کاربران',
	);

	$sub_url = function( $key ) {
		return add_query_arg( array(
			'page' => 'noghre-panel',
			'tab'  => 'app',
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
			case 'users':
				noghre_render_app_users_subtab();
				break;

			case 'management':
			default:
				noghre_render_app_management_subtab();
				break;
		}
		?>
	</div>
	<?php
}

/* ==========================================================================
 * زیرتب ۱ — مدیریت (اپ مدیر سالن)
 * ========================================================================== */
function noghre_render_app_management_subtab() {
	$o = noghre_get_app_options();

	/* 🔑 برگه‌های سایت برای صفحه‌ساز */
	$site_pages = get_pages( array( 'sort_column' => 'post_title' ) );

	/* 🔑 لیست منوهای پایین اپ برای dropdown پیش‌فرض */
	$bottom_menus = is_array( $o['app_bottom_nav'] ) ? $o['app_bottom_nav'] : array();
	usort( $bottom_menus, function( $a, $b ) {
		return ( $a['sort_order'] ?? 0 ) <=> ( $b['sort_order'] ?? 0 );
	} );
	$default_nav_id = ! empty( $o['default_nav_id'] ) ? $o['default_nav_id'] : 'mobile';
	$mobile_section_view = ! empty( $o['mobile_section_view'] ) ? $o['mobile_section_view'] : 'both';
	?>
	<form method="post" action="options.php">
		<?php settings_fields( 'noghre_app_group' ); ?>

		<h2 class="noghre-section-heading">
			<span class="dashicons dashicons-smartphone"></span>
			اطلاعات کلی اپلیکیشن
		</h2>
		<table class="form-table">
			<tr>
				<th><label for="app_title">عنوان اپ</label></th>
				<td>
					<input type="text" id="app_title" name="noghre_app_options[app_title]" value="<?php echo esc_attr( $o['app_title'] ); ?>" class="regular-text">
				</td>
			</tr>
			<tr>
				<th><label for="app_subtitle">زیرعنوان اپ</label></th>
				<td>
					<input type="text" id="app_subtitle" name="noghre_app_options[app_subtitle]" value="<?php echo esc_attr( $o['app_subtitle'] ); ?>" class="regular-text">
				</td>
			</tr>
			<tr>
				<th><label for="app_logo_text">متن لوگو</label></th>
				<td>
					<input type="text" id="app_logo_text" name="noghre_app_options[app_logo_text]" value="<?php echo esc_attr( $o['app_logo_text'] ); ?>" class="small-text" maxlength="3">
					<p class="description">یک حرف یا کاراکتر کوتاه که در دایره لوگوی اپ نمایش داده می‌شود.</p>
				</td>
			</tr>
			<tr>
				<th><label for="app_page_id">برگه اپ موبایل (مجوز اجرا)</label></th>
				<td>
					<select id="app_page_id" name="noghre_app_options[app_page_id]" class="regular-text">
						<option value="0">— انتخاب کنید —</option>
						<?php foreach ( $site_pages as $p ) : ?>
							<option value="<?php echo esc_attr( $p->ID ); ?>" <?php selected( (int) $o['app_page_id'], $p->ID ); ?>>
								<?php echo esc_html( $p->post_title ); ?>
							</option>
						<?php endforeach; ?>
					</select>
					<p class="description">
						<strong>مهم:</strong> برگه‌ای که شورت‌کد <code>[ariatin-noghreh-mobile]</code> در آن قرار دارد. تا زمانی که این برگه انتخاب نشود، آدرس <code>/mobile/</code> به صفحه اصلی سایت هدایت می‌شود.
						این برگه به‌عنوان <code>start_url</code> در اپ نصب‌شده (PWA) استفاده می‌شود و به‌صورت خودکار بدون هدر و فوتر سایت باز می‌شود.
					</p>
				</td>
			</tr>
			<?php if ( ! empty( $o['app_page_id'] ) ) :
				$app_page_url = get_permalink( (int) $o['app_page_id'] );
				?>
				<tr>
					<th>آدرس برگه اپ</th>
					<td>
						<input type="text" value="<?php echo esc_attr( $app_page_url ); ?>" class="regular-text" dir="ltr" readonly style="background:#f0f0f1;">
						<p class="description">این آدرس به‌عنوان <code>start_url</code> در manifest قرار می‌گیرد.</p>
					</td>
				</tr>
			<?php else : ?>
				<tr>
					<th></th>
					<td>
						<div class="notice notice-warning inline" style="margin:0;">
							<p>
								<strong>توجه:</strong> هنوز هیچ برگه‌ای به‌عنوان برگه اپ انتخاب نشده. تا زمانی که این برگه انتخاب نشود، آدرس <code>/mobile/</code> به صفحه اصلی سایت هدایت می‌شود.
							</p>
						</div>
					</td>
				</tr>
			<?php endif; ?>
			<tr>
				<th><label for="default_nav_id">صفحه پیش‌فرض اپ</label></th>
				<td>
					<select id="default_nav_id" name="noghre_app_options[default_nav_id]" class="regular-text">
						<?php if ( empty( $bottom_menus ) ) : ?>
							<option value="">— هیچ منویی تعریف نشده —</option>
						<?php else : ?>
							<?php foreach ( $bottom_menus as $menu ) : ?>
								<option value="<?php echo esc_attr( $menu['id'] ); ?>" <?php selected( $default_nav_id, $menu['id'] ); ?>>
									<?php echo esc_html( $menu['title'] ); ?>
									<?php if ( ! empty( $menu['shortcode'] ) ) : ?>
										— <?php echo esc_html( $menu['shortcode'] ); ?>
									<?php endif; ?>
								</option>
							<?php endforeach; ?>
						<?php endif; ?>
					</select>
					<p class="description">
						از بین منوهای پایین اپ، کدام یک به‌عنوان صفحه پیش‌فرض نمایش داده شود؟ وقتی کاربر بدون پارامتر وارد اپ شود، این منو اول لود می‌شود.
					</p>
				</td>
			</tr>
			<tr>
				<th><label for="mobile_section_view">محتوای صفحه اصلی اپ (section=mobile)</label></th>
				<td>
					<select id="mobile_section_view" name="noghre_app_options[mobile_section_view]" class="regular-text">
						<option value="both"    <?php selected( $mobile_section_view, 'both' );    ?>>باکس ثبت شماره + لیست رزروها</option>
						<option value="list"    <?php selected( $mobile_section_view, 'list' );    ?>>فقط لیست رزروها</option>
						<option value="signup"  <?php selected( $mobile_section_view, 'signup' );  ?>>فقط باکس ثبت شماره</option>
						<option value="scanner" <?php selected( $mobile_section_view, 'scanner' ); ?>>فقط اسکنر QR</option>
						<option value="check"   <?php selected( $mobile_section_view, 'check' );   ?>>فقط صفحه پیگیری رزرو</option>
					</select>
					<p class="description">
						وقتی کاربر به <code>?section=mobile</code> وارد می‌شود (مثلاً با کلیک روی منوی «صفحه اصلی»)، این محتوا نمایش داده می‌شود.
					</p>
				</td>
			</tr>
		</table>

		<h2 class="noghre-section-heading">
			<span class="dashicons dashicons-download"></span>
			تنظیمات PWA (نصب اپ روی موبایل)
		</h2>
		<p style="margin:0 0 16px;color:#646970;line-height:1.9;">
			با فعال کردن این بخش، کاربران می‌توانند اپ را از مرورگر روی صفحه اصلی موبایل نصب کنند.
		</p>
		<table class="form-table">
			<tr>
				<th>فعال‌سازی PWA</th>
				<td>
					<label>
						<input type="checkbox" name="noghre_app_options[pwa_enabled]" value="1" <?php checked( $o['pwa_enabled'], 1 ); ?>>
						فعال‌سازی PWA
					</label>
				</td>
			</tr>
			<tr>
				<th><label for="pwa_short_name">نام کوتاه اپ</label></th>
				<td>
					<input type="text" id="pwa_short_name" name="noghre_app_options[pwa_short_name]" value="<?php echo esc_attr( $o['pwa_short_name'] ); ?>" class="regular-text" maxlength="12">
					<p class="description">حداکثر ۱۲ کاراکتر — زیر آیکون در صفحه اصلی نمایش داده می‌شود.</p>
				</td>
			</tr>
			<tr>
				<th><label for="pwa_name">نام کامل اپ</label></th>
				<td>
					<input type="text" id="pwa_name" name="noghre_app_options[pwa_name]" value="<?php echo esc_attr( $o['pwa_name'] ); ?>" class="regular-text">
					<p class="description">هنگام نصب اپ نمایش داده می‌شود.</p>
				</td>
			</tr>
			<tr>
				<th><label for="pwa_theme_color">رنگ تم</label></th>
				<td>
					<input type="text" id="pwa_theme_color" name="noghre_app_options[pwa_theme_color]" value="<?php echo esc_attr( $o['pwa_theme_color'] ); ?>" class="noghre-color-field" data-default-color="#7A2E38">
					<p class="description">رنگ نوار بالای اپ در حالت نصب‌شده.</p>
				</td>
			</tr>
			<tr>
				<th><label for="pwa_bg_color">رنگ پس‌زمینه</label></th>
				<td>
					<input type="text" id="pwa_bg_color" name="noghre_app_options[pwa_bg_color]" value="<?php echo esc_attr( $o['pwa_bg_color'] ); ?>" class="noghre-color-field" data-default-color="#FBF7F4">
					<p class="description">رنگ صفحه اسپلش (بارگذاری اولیه).</p>
				</td>
			</tr>
			<tr>
				<th>آیکون اپ</th>
				<td>
					<?php
					$icon_url = ! empty( $o['pwa_icon_id'] ) ? wp_get_attachment_image_url( (int) $o['pwa_icon_id'], 'medium' ) : '';
					?>
					<div class="noghre-media-field">
						<img src="<?php echo esc_url( $icon_url ); ?>" class="noghre-media-preview" style="<?php echo $icon_url ? '' : 'display:none;'; ?>max-width:120px;height:auto;display:block;margin-bottom:8px;">
						<input type="hidden" name="noghre_app_options[pwa_icon_id]" class="noghre-media-id" value="<?php echo esc_attr( $o['pwa_icon_id'] ); ?>">
						<button type="button" class="button noghre-media-select">انتخاب تصویر</button>
						<button type="button" class="button noghre-media-remove">حذف تصویر</button>
					</div>
					<p class="description"><strong>سایز استاندارد:</strong> مربع ۵۱۲×۵۱۲ پیکسل، فرمت PNG. اگر انتخاب نشود، از لوگوی سایت استفاده می‌شود.</p>
				</td>
			</tr>
			<tr>
				<th><label for="pwa_display">حالت نمایش</label></th>
				<td>
					<select id="pwa_display" name="noghre_app_options[pwa_display]">
						<option value="standalone"  <?php selected( $o['pwa_display'], 'standalone' );  ?>>Standalone (توصیه‌شده — شبیه اپ واقعی)</option>
						<option value="fullscreen"  <?php selected( $o['pwa_display'], 'fullscreen' );  ?>>Fullscreen (تمام صفحه)</option>
						<option value="minimal-ui"  <?php selected( $o['pwa_display'], 'minimal-ui' );  ?>>Minimal UI (با نوار آدرس کوچک)</option>
						<option value="browser"     <?php selected( $o['pwa_display'], 'browser' );     ?>>Browser (در مرورگر عادی)</option>
					</select>
				</td>
			</tr>
			<tr>
				<th><label for="pwa_orientation">جهت صفحه</label></th>
				<td>
					<select id="pwa_orientation" name="noghre_app_options[pwa_orientation]">
						<option value="portrait"  <?php selected( $o['pwa_orientation'], 'portrait' );  ?>>Portrait (عمودی)</option>
						<option value="landscape" <?php selected( $o['pwa_orientation'], 'landscape' ); ?>>Landscape (افقی)</option>
						<option value="any"       <?php selected( $o['pwa_orientation'], 'any' );       ?>>Any (هر جهتی)</option>
					</select>
				</td>
			</tr>
		</table>

		<h2 class="noghre-section-heading">
			<span class="dashicons dashicons-menu"></span>
			منوهای پایین اپلیکیشن
		</h2>
		<p style="margin:0 0 16px;color:#646970;line-height:1.9;">
			هر منو شامل عنوان، آیکون (از لیست dashiconها)، نوع مقصد و شورت‌کد متصل است.
			صفحه پیش‌فرض اپ از فیلد بالای همین بخش انتخاب می‌شود.
		</p>

		<div id="noghre-app-menus-container">
			<?php
			foreach ( $bottom_menus as $i => $menu ) :
				noghre_app_menu_row( $i, $menu );
			endforeach;
			?>
		</div>

		<p>
			<button type="button" class="button button-secondary" id="noghre-app-add-menu">افزودن منوی جدید</button>
		</p>

		<template id="noghre-app-menu-row-template">
			<?php noghre_app_menu_row( '__INDEX__', array(
				'id'         => '',
				'title'      => '',
				'icon'       => 'dashicons-admin-generic',
				'url_type'   => 'internal',
				'url'        => '',
				'shortcode'  => '',
				'sort_order' => 0,
				'active'     => 1,
			) ); ?>
		</template>

		<?php submit_button( 'ذخیره تنظیمات اپلیکیشن' ); ?>
	</form>
	<?php
}

/**
 * رندر یک ردیف منو
 */
function noghre_app_menu_row( $index, $menu ) {
	$id         = $menu['id'] ?? '';
	$title      = $menu['title'] ?? '';
	$icon       = $menu['icon'] ?? 'dashicons-admin-generic';
	$url_type   = $menu['url_type'] ?? 'internal';
	$url        = $menu['url'] ?? '';
	$shortcode  = $menu['shortcode'] ?? '';
	$sort_order = $menu['sort_order'] ?? 0;
	$active     = ! empty( $menu['active'] );

	$icon_is_image = is_numeric( $icon ) && (int) $icon > 0;
	$icon_value    = $icon_is_image ? (string) $icon : sanitize_text_field( $icon );
	$icon_url      = $icon_is_image ? wp_get_attachment_image_url( (int) $icon, 'thumbnail' ) : '';

	$dashicons  = noghre_available_dashicons();
	$shortcodes = noghre_app_menu_shortcodes();
	?>
	<div class="noghre-app-menu-row">
		<input type="hidden" name="noghre_app_options[app_bottom_nav][<?php echo esc_attr( $index ); ?>][id]" value="<?php echo esc_attr( $id ); ?>">
		<input type="hidden" name="noghre_app_options[app_bottom_nav][<?php echo esc_attr( $index ); ?>][sort_order]" value="<?php echo esc_attr( $sort_order ); ?>" class="noghre-app-sort-order">

		<div class="noghre-app-icon-wrap">
			<?php if ( $icon_is_image ) : ?>
				<img src="<?php echo esc_url( $icon_url ); ?>" class="noghre-app-icon-preview" alt="">
				<input type="hidden" name="noghre_app_options[app_bottom_nav][<?php echo esc_attr( $index ); ?>][icon]" value="<?php echo esc_attr( $icon_value ); ?>" class="noghre-app-icon-input">
				<button type="button" class="button button-small noghre-app-icon-clear" title="حذف عکس و بازگشت به dashicon">
					<span class="dashicons dashicons-no-alt"></span>
				</button>
			<?php else : ?>
				<span class="noghre-app-icon-preview-dashicon dashicons <?php echo esc_attr( $icon_value ); ?>"></span>
				<select name="noghre_app_options[app_bottom_nav][<?php echo esc_attr( $index ); ?>][icon]" class="noghre-app-icon-select">
					<?php foreach ( $dashicons as $class => $label ) : ?>
						<option value="<?php echo esc_attr( $class ); ?>" <?php selected( $icon_value, $class ); ?>>
							<?php echo esc_html( $label ); ?>
						</option>
					<?php endforeach; ?>
				</select>
				<input type="hidden" class="noghre-app-icon-input" value="">
			<?php endif; ?>

			<button type="button" class="button button-small noghre-app-icon-picker" title="انتخاب عکس به‌جای dashicon">
				<span class="dashicons dashicons-format-image"></span>
			</button>
		</div>

		<input type="text" name="noghre_app_options[app_bottom_nav][<?php echo esc_attr( $index ); ?>][title]" value="<?php echo esc_attr( $title ); ?>" placeholder="عنوان منو" class="regular-text">

		<select name="noghre_app_options[app_bottom_nav][<?php echo esc_attr( $index ); ?>][url_type]" class="noghre-app-url-type">
			<option value="internal" <?php selected( $url_type, 'internal' ); ?>>شورت‌کد</option>
			<option value="home"     <?php selected( $url_type, 'home' ); ?>>صفحه اصلی سایت</option>
			<option value="admin"    <?php selected( $url_type, 'admin' ); ?>>پنل مدیریت</option>
			<option value="external" <?php selected( $url_type, 'external' ); ?>>لینک خارجی</option>
		</select>

		<span class="noghre-app-shortcode-wrap" style="flex:1;min-width:0;">
			<select name="noghre_app_options[app_bottom_nav][<?php echo esc_attr( $index ); ?>][shortcode]" class="noghre-app-shortcode-input" style="width:100%;max-width:none;">
				<?php foreach ( $shortcodes as $value => $label ) : ?>
					<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $shortcode, $value ); ?>>
						<?php echo esc_html( $label ); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</span>

		<span class="noghre-app-url-wrap" style="flex:1;min-width:0;display:none;">
			<input type="url" name="noghre_app_options[app_bottom_nav][<?php echo esc_attr( $index ); ?>][url]" value="<?php echo esc_attr( $url ); ?>" placeholder="https://..." class="noghre-app-url-input" style="width:100%;">
		</span>

		<label class="noghre-app-active-label">
			<input type="checkbox" name="noghre_app_options[app_bottom_nav][<?php echo esc_attr( $index ); ?>][active]" value="1" <?php checked( $active ); ?>>
			فعال
		</label>

		<button type="button" class="button-link-delete noghre-remove-menu" title="حذف"><span class="dashicons dashicons-no-alt"></span></button>
	</div>
	<?php
}

/* ==========================================================================
 * زیرتب ۲ — کاربران (به‌زودی)
 * ========================================================================== */
function noghre_render_app_users_subtab() {
	?>
	<div style="text-align:center;padding:60px 20px;color:#646970;">
		<div style="display:inline-flex;align-items:center;justify-content:center;width:80px;height:80px;border-radius:50%;background:#f0f0f1;margin-bottom:20px;">
			<span class="dashicons dashicons-admin-users" style="font-size:40px;width:40px;height:40px;color:#2271b1;"></span>
		</div>
		<h2 style="font-size:20px;color:#1d2327;margin:0 0 10px;font-weight:600;">مدیریت کاربران اپلیکیشن</h2>
		<p style="font-size:14px;line-height:1.9;max-width:480px;margin:0 auto;">
			این بخش به‌زودی راه‌اندازی می‌شود.<br>
			در این قسمت می‌توانید کاربران اپلیکیشن را مدیریت کنید، دسترسی‌ها را تعیین کنید و اطلاعات آن‌ها را ببینید.
		</p>
	</div>
	<?php
}