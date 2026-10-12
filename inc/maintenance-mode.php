<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * حالت در دست تعمیر
 * روی هوک init با اولویت ۱ اجرا می‌شه تا زودتر از هر هوک دیگه‌ای فعال بشه
 */
function noghre_maintenance_mode() {
	// درخواست‌های AJAX رو نادیده بگیر
	if ( wp_doing_ajax() ) return;

	// درخواست‌های پنل ادمین رو نادیده بگیر
	if ( is_admin() ) return;

	// درخواست‌های REST API رو نادیده بگیر
	if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) return;

	// درخواست‌های cron رو نادیده بگیر
	if ( defined( 'DOING_CRON' ) && DOING_CRON ) return;

	// کاربران مدیر همیشه دسترسی کامل دارن
	if ( current_user_can( 'manage_options' ) ) return;

	$enabled = (int) noghre_get_option( 'maintenance_enabled', 0 );
	if ( ! $enabled ) return;

	// جلوگیری از اجرای مکرر
	static $already_shown = false;
	if ( $already_shown ) return;
	$already_shown = true;

	$message = noghre_get_option( 'maintenance_message', 'سایت در دست تعمیر می‌باشد. به‌زودی برمی‌گردیم.' );

	$ver = defined( 'NOGHRE_VERSION' ) ? NOGHRE_VERSION : '1.0.0';
	$maintenance_css_url = NOGHRE_URI . '/assets/css/maintenance.css';

	// پاک کردن هر output buffer قبلی
	while ( ob_get_level() > 0 ) {
		ob_end_clean();
	}

	status_header( 503 );
	header( 'Content-Type: text/html; charset=utf-8' );
	header( 'Retry-After: 3600' );
	?>
	<!DOCTYPE html>
	<html <?php language_attributes(); ?>>
	<head>
		<meta charset="<?php bloginfo( 'charset' ); ?>">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>در دست تعمیر | <?php bloginfo( 'name' ); ?></title>
		<link rel="preconnect" href="https://fonts.googleapis.com">
		<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
		<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@400;600;700&display=swap">
		<link rel="stylesheet" href="<?php echo esc_url( $maintenance_css_url . '?ver=' . urlencode( $ver ) ); ?>">
	</head>
	<body>
		<div class="noghre-maintenance-box">
			<h1 class="noghre-maintenance-title"><?php bloginfo( 'name' ); ?></h1>
			<div class="noghre-maintenance-line"></div>
			<div class="noghre-maintenance-content editor-content"><?php echo wp_kses_post( $message ); ?></div>
		</div>
	</body>
	</html>
	<?php
	exit;
}
add_action( 'init', 'noghre_maintenance_mode', 1 );