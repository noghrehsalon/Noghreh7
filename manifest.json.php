<?php
/**
 * فایل داینامیک manifest.json برای PWA
 * از تنظیمات PWA + برگه اپ در پنل اپلیکیشن استفاده می‌کند.
 * آدرس فراخوانی: home_url('/?noghre_pwa_manifest=1')
 *
 * @package Noghre
 */

if ( ! defined( 'ABSPATH' ) ) exit;

function noghre_render_pwa_manifest() {
	if ( ! isset( $_GET['noghre_pwa_manifest'] ) ) return;

	$app = noghre_get_app_options();
	if ( empty( $app['pwa_enabled'] ) ) {
		status_header( 404 );
		exit;
	}

	// آیکون
	$icon_url = '';
	if ( ! empty( $app['pwa_icon_id'] ) ) {
		$icon_url = wp_get_attachment_image_url( (int) $app['pwa_icon_id'], 'full' );
	}
	if ( ! $icon_url && ! empty( $app['app_logo_id'] ) ) {
		$icon_url = wp_get_attachment_image_url( (int) $app['app_logo_id'], 'full' );
	}
	if ( ! $icon_url ) {
		$site_logo_id = (int) noghre_get_option( 'site_logo_id', 0 );
		if ( $site_logo_id ) {
			$icon_url = wp_get_attachment_image_url( $site_logo_id, 'full' );
		}
	}
	if ( ! $icon_url ) {
		$icon_url = NOGHRE_URI . '/assets/img/placeholder-hero.svg';
	}

	// start_url: برگه اپ انتخاب‌شده
	$start_url = '';
	if ( ! empty( $app['app_page_id'] ) ) {
		$page_url = get_permalink( (int) $app['app_page_id'] );
		if ( $page_url ) {
			$start_url = $page_url;
		}
	}
	if ( ! $start_url ) {
		$start_url = home_url( '/mobile/' );
	}

	// scope: ریشه سایت (تا service worker روی همه‌جا کار کند)
	$scope = home_url( '/' );

	$manifest = array(
		'name'             => $app['pwa_name'],
		'short_name'       => $app['pwa_short_name'],
		'description'      => $app['app_subtitle'],
		'start_url'        => $start_url,
		'scope'            => $scope,
		'display'          => $app['pwa_display'],
		'orientation'      => $app['pwa_orientation'],
		'theme_color'      => $app['pwa_theme_color'],
		'background_color' => $app['pwa_bg_color'],
		'lang'             => 'fa',
		'dir'              => 'rtl',
		'icons'            => array(
			array(
				'src'     => $icon_url,
				'sizes'   => '192x192',
				'type'    => 'image/png',
				'purpose' => 'any',
			),
			array(
				'src'     => $icon_url,
				'sizes'   => '512x512',
				'type'    => 'image/png',
				'purpose' => 'any',
			),
			array(
				'src'     => $icon_url,
				'sizes'   => '512x512',
				'type'    => 'image/png',
				'purpose' => 'maskable',
			),
		),
	);

	header( 'Content-Type: application/manifest+json; charset=utf-8' );
	header( 'Cache-Control: public, max-age=3600' );
	echo wp_json_encode( $manifest, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
	exit;
}
add_action( 'template_redirect', 'noghre_render_pwa_manifest', 1 );