<?php
/**
 * پنل مدیریت یکپارچه قالب نقره
 * تمام تنظیمات در یک صفحه با تب‌های مرتب
 *
 * @package Noghre
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/* ==========================================================================
 * منوی اصلی
 * ========================================================================== */
function noghre_admin_menu() {
	add_menu_page(
		'تنظیمات قالب نقره',
		'تنظیمات نقره',
		'manage_options',
		'noghre-panel',
		'noghre_render_admin_panel',
		'dashicons-star-filled',
		59
	);

	remove_submenu_page( 'noghre-panel', 'noghre-panel' );
}
add_action( 'admin_menu', 'noghre_admin_menu', 9 );

/* ==========================================================================
 * تعریف تب‌ها
 * ========================================================================== */
function noghre_admin_tabs() {
	return array(
		'general'    => array( 'title' => 'عمومی',        'icon' => 'dashicons-admin-home',   'file' => 'tab-general.php' ),
		'appearance' => array( 'title' => 'ظاهر',         'icon' => 'dashicons-art',          'file' => 'tab-appearance.php' ),
		'app'        => array( 'title' => 'اپلیکیشن',     'icon' => 'dashicons-smartphone',   'file' => 'tab-app.php' ),
		'sms'        => array( 'title' => 'پیامک',        'icon' => 'dashicons-email',        'file' => 'tab-sms.php' ),
		'zarinpal'   => array( 'title' => 'درگاه پرداخت', 'icon' => 'dashicons-money-alt',    'file' => 'tab-zarinpal.php' ),
		'booking'    => array( 'title' => 'رزرو وقت',     'icon' => 'dashicons-calendar-alt', 'file' => 'tab-booking.php' ),
		'content'    => array( 'title' => 'مدیریت محتوا', 'icon' => 'dashicons-groups',       'file' => 'tab-content.php' ),
		'advanced'   => array( 'title' => 'پیشرفته',      'icon' => 'dashicons-admin-tools',  'file' => 'tab-advanced.php' ),
	);
}

/* ==========================================================================
 * URL تب فعال
 * ========================================================================== */
function noghre_tab_url( $tab, $extra = array() ) {
	$args = array_merge( array(
		'page' => 'noghre-panel',
		'tab'  => $tab,
	), $extra );

	return add_query_arg( $args, admin_url( 'admin.php' ) );
}

/* ==========================================================================
 * رندر پنل
 * ========================================================================== */
function noghre_render_admin_panel() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'دسترسی غیرمجاز' );
	}

	$tabs       = noghre_admin_tabs();
	$active_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'general';

	if ( ! isset( $tabs[ $active_tab ] ) ) {
		$active_tab = 'general';
	}
	?>
	<div class="wrap noghre-panel-wrap">

		<h1 class="wp-heading-inline">تنظیمات قالب نقره</h1>
		<hr class="wp-header-end">

		<div class="noghre-panel-header">
			<div class="noghre-panel-header__brand">
				<span class="noghre-panel-header__icon dashicons dashicons-star-filled"></span>
				<div>
					<p class="noghre-panel-header__subtitle">مدیریت کامل سایت از یک پنل یکپارچه</p>
				</div>
			</div>
			<div class="noghre-panel-header__meta">
				<span class="noghre-panel-header__version">نسخه <?php echo esc_html( defined( 'NOGHRE_VERSION' ) ? NOGHRE_VERSION : '1.0.0' ); ?></span>
			</div>
		</div>

		<?php do_action( 'noghre_after_panel_header' ); ?>

		<nav class="noghre-tabs nav-tab-wrapper" role="tablist">
			<?php foreach ( $tabs as $key => $tab ) : ?>
				<a href="<?php echo esc_url( noghre_tab_url( $key ) ); ?>"
				   class="noghre-tab nav-tab <?php echo $active_tab === $key ? 'nav-tab-active' : ''; ?>"
				   role="tab"
				   aria-selected="<?php echo $active_tab === $key ? 'true' : 'false'; ?>">
					<span class="dashicons <?php echo esc_attr( $tab['icon'] ); ?> noghre-tab__icon"></span>
					<span class="noghre-tab__title"><?php echo esc_html( $tab['title'] ); ?></span>
				</a>
			<?php endforeach; ?>
		</nav>

		<div class="noghre-messages-wrap">
			<?php if ( isset( $_GET['noghre_msg'] ) ) : ?>
				<?php noghre_render_admin_message( sanitize_key( $_GET['noghre_msg'] ) ); ?>
			<?php endif; ?>
		</div>

		<div class="noghre-tab-content">
			<?php
			$tab_file = NOGHRE_DIR . '/inc/admin/' . $tabs[ $active_tab ]['file'];

			if ( file_exists( $tab_file ) ) {
				include $tab_file;

				$render_function = 'noghre_render_' . str_replace( '-', '_', $active_tab ) . '_page';

				if ( function_exists( $render_function ) ) {
					call_user_func( $render_function );
				} else {
					printf(
						'<div class="notice notice-error inline"><p>تابع <code>%s</code> در فایل تب پیدا نشد.</p></div>',
						esc_html( $render_function )
					);
				}
			} else {
				printf(
					'<div class="notice notice-warning inline"><p>فایل این تب هنوز ساخته نشده: <code>%s</code></p></div>',
					esc_html( $tabs[ $active_tab ]['file'] )
				);
			}
			?>
		</div>

	</div>
	<?php
}

/* ==========================================================================
 * پیام‌های سیستمی
 * ========================================================================== */
function noghre_render_admin_message( $msg ) {
	$messages = array(
		'admin_key_generated' => array( 'success', 'شناسه مدیریت جدید ایجاد شد.' ),
		'settings_saved'      => array( 'success', 'تنظیمات با موفقیت ذخیره شد.' ),
		'history_cleared'     => array( 'success', 'تاریخچه ثبت‌نام‌های باشگاه تخفیف حذف شد.' ),
		'error_log_cleared'   => array( 'success', 'گزارش خطاها پاک شد.' ),
		'backup_done'         => array( 'success', 'فایل پشتیبان دانلود شد.' ),
		'import_success'      => array( 'success', 'تنظیمات با موفقیت بازگردانی شد.' ),
		'import_empty'        => array( 'error',   'فایلی برای بازگردانی انتخاب نشده بود.' ),
		'import_invalid'      => array( 'error',   'فایل انتخاب‌شده معتبر نیست.' ),
		'reset_success'       => array( 'success', 'تنظیمات به حالت پیش‌فرض بازگشت.' ),
		'booking_deleted'     => array( 'success', 'رزرو حذف شد.' ),
		'branches_saved'      => array( 'success', 'شعبه‌ها ذخیره شد.' ),
		'services_saved'      => array( 'success', 'خدمات ذخیره شد.' ),
		'hours_saved'         => array( 'success', 'ساعات فعالیت ذخیره شد.' ),
		'discount_saved'      => array( 'success', 'کد تخفیف ذخیره شد.' ),
		'discount_deleted'    => array( 'success', 'کد تخفیف حذف شد.' ),
		'sections_saved'      => array( 'success', 'چیدمان صفحه اصلی ذخیره شد.' ),
		'team_saved'          => array( 'success', 'لیست پرسنل ذخیره شد.' ),
		'faq_saved'           => array( 'success', 'سوالات متداول ذخیره شد.' ),
		'licenses_saved'      => array( 'success', 'مجوزها ذخیره شد.' ),
	);

	if ( ! isset( $messages[ $msg ] ) ) return;

	list( $type, $text ) = $messages[ $msg ];

	$icon_map = array(
		'success' => 'dashicons-yes-alt',
		'error'   => 'dashicons-dismiss',
		'warning' => 'dashicons-warning',
		'info'    => 'dashicons-info',
	);
	$icon = isset( $icon_map[ $type ] ) ? $icon_map[ $type ] : 'dashicons-info';

	printf(
		'<div class="noghre-message noghre-message--%s" data-autoclose="5000">
			<span class="noghre-message__icon dashicons %s"></span>
			<span class="noghre-message__text">%s</span>
			<button type="button" class="noghre-message__close" aria-label="بستن">×</button>
		</div>',
		esc_attr( $type ),
		esc_attr( $icon ),
		esc_html( $text )
	);
}

/* ==========================================================================
 * لود assets ادمین
 * ========================================================================== */
function noghre_panel_admin_assets( $hook ) {
	if ( strpos( $hook, 'noghre-panel' ) === false ) return;

	$ver = defined( 'NOGHRE_VERSION' ) ? NOGHRE_VERSION : '1.0.0';

	wp_enqueue_media();
	wp_enqueue_style( 'wp-color-picker' );
	wp_enqueue_script( 'wp-color-picker' );
	wp_enqueue_script( 'jquery-ui-sortable' );
	wp_enqueue_style( 'dashicons' );

	/* CSS پنل ادمین */
	wp_enqueue_style( 'noghre-admin', NOGHRE_URI . '/assets/css/admin.css', array(), $ver );
	wp_enqueue_style( 'noghre-admin-panel', NOGHRE_URI . '/assets/css/admin-panel.css', array( 'noghre-admin' ), $ver );

	/* JS پنل ادمین */
	wp_enqueue_script( 'noghre-admin', NOGHRE_URI . '/assets/js/admin.js', array( 'jquery', 'jquery-ui-sortable', 'wp-color-picker' ), $ver, true );
	wp_enqueue_script( 'noghre-admin-panel', NOGHRE_URI . '/assets/js/admin-panel.js', array( 'jquery', 'noghre-admin' ), $ver, true );

	/* 🔑 Localize with all needed data for admin panel */
	wp_localize_script( 'noghre-admin-panel', 'noghreAdminConfig', array(
		'ajaxUrl'          => admin_url( 'admin-ajax.php' ),
		'refreshSmsNonce'  => wp_create_nonce( 'noghre_refresh_sms_lists' ),
		'testSmsNonce'     => wp_create_nonce( 'noghre_test_sms' ),
		'generalNonce'     => wp_create_nonce( 'noghre_save_general' ),
	) );

	/* 🔑 Backward compatibility: تعریف window.ajaxurl و window.noghreAdminNonce */
	wp_add_inline_script( 'noghre-admin-panel',
		'window.ajaxurl = ' . wp_json_encode( admin_url( 'admin-ajax.php' ) ) . ';' .
		'window.noghreAdminNonce = ' . wp_json_encode( wp_create_nonce( 'noghre_refresh_sms_lists' ) ) . ';' .
		'window.noghreTestSmsNonce = ' . wp_json_encode( wp_create_nonce( 'noghre_test_sms' ) ) . ';',
		'before'
	);

	/* JS مخصوص تب‌ها */
	$active_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : '';

	if ( $active_tab === 'app' ) {
		wp_enqueue_script( 'noghre-tab-app', NOGHRE_URI . '/assets/js/tab-app.js', array( 'jquery', 'noghre-admin' ), $ver, true );
	}

	if ( $active_tab === 'appearance' ) {
		wp_enqueue_script( 'noghre-tab-colors', NOGHRE_URI . '/assets/js/tab-colors.js', array( 'jquery', 'wp-color-picker' ), $ver, true );
	}

	if ( $active_tab === 'booking' ) {
		wp_enqueue_script( 'noghre-tab-booking', NOGHRE_URI . '/assets/js/tab-booking.js', array( 'jquery' ), $ver, true );
	}
}
add_action( 'admin_enqueue_scripts', 'noghre_panel_admin_assets' );

/* ==========================================================================
 * ریدایرکت از URLهای قدیمی به پنل جدید
 * ========================================================================== */
function noghre_redirect_old_admin_pages() {
	if ( ! is_admin() ) return;
	if ( ! isset( $_GET['page'] ) ) return;

	$old_pages = array(
		'noghre-settings'       => 'general',
		'noghre-colors'         => 'appearance',
		'noghre-sms'            => 'sms',
		'noghre-pay'            => 'zarinpal',
		'noghre-error-log'      => 'advanced',
		'noghre-content'        => 'content',
		'noghre-team'           => 'content',
		'noghre-faq'            => 'content',
		'noghre-licenses'       => 'content',
		'noghre-booking'        => 'booking',
		'noghre-bookings'       => 'booking',
		'noghre-discount-codes' => 'booking',
		'noghre-backup'         => 'advanced',
	);

	$current_page = sanitize_key( $_GET['page'] );

	if ( isset( $old_pages[ $current_page ] ) ) {
		$new_url = add_query_arg( array(
			'page' => 'noghre-panel',
			'tab'  => $old_pages[ $current_page ],
		), admin_url( 'admin.php' ) );

		if ( isset( $_GET['noghre_msg'] ) ) {
			$new_url = add_query_arg( 'noghre_msg', sanitize_key( $_GET['noghre_msg'] ), $new_url );
		}

		wp_safe_redirect( $new_url );
		exit;
	}
}
add_action( 'admin_init', 'noghre_redirect_old_admin_pages' );