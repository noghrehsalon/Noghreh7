<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/* ==========================================================================
 * تنظیمات عمومی
 * ========================================================================== */
function noghre_default_general_options() {
	return array(
		'hero_title'          => 'درخشش تو، امضای شخصی توست؛ ما فقط آن را آشکار می‌کنیم.',
		'hero_text'           => 'سالن زیبایی نقره در قلب هروی (میدان هروی)، فقط یک سالن آرایشگاهی نیست؛ بلکه گالریِ هنرِ زیبایی تهرانی‌های خاص‌پسند است. ما در «نقره» باور داریم که زیبایی واقعی، در گرو سلامت است. به همین دلیل، وسواس ما در استفاده از متریال ۱۰۰٪ اورجینال و لوکس، و رعایت سخت‌گیرانه‌ترین پروتکل‌های بهداشتی، تنها یک تعریف نیست؛ بلکه تضمین ماست.',
		'hero_image_id'       => 0,
		'tagline_1'           => 'فکر می‌کنی زیبایی فقط ژنتیکه؟ سخت در اشتباهی! زیبایی یعنی اینکه بدونی کدوم ویژگی صورتت رو باید برجسته کنی تا همه خیره بمونن.',
		'tagline_2'           => 'زیبایی، یک سفر است؛ مقصد آن «سالن نقره» در هروی.',
		'address'             => 'تهران، میدان هروی، سالن زیبایی نقره',
		'phone'               => '021-00000000',
		'map_url'             => '',
		'instagram_url'       => 'https://instagram.com/',
		'telegram_url'        => 'https://t.me/',
		'whatsapp_url'        => '',
		'blog_posts_count'    => 3,
		'maintenance_enabled' => 0,
		'maintenance_message' => 'سایت در دست تعمیر می‌باشد. به‌زودی برمی‌گردیم.',
		'feature_1_title'     => 'کادر مجرب',
		'feature_1_text'      => 'تمامی خدمات در سالن زیبایی نقره توسط پرسنل فوق‌تخصصی و با سابقه‌های درخشان انجام می‌شود.',
		'feature_2_title'     => 'قیمت مناسب',
		'feature_2_text'      => 'سالن زیبایی نقره، با داشتن مجوزهای رسمی از اتحادیه آرایشگران، خدمات خود را طبق نرخ اتحادیه به مشتریان ارائه می‌دهد.',
		'feature_3_title'     => 'کوتاه‌ترین زمان',
		'feature_3_text'      => 'سالن زیبایی نقره، زمان را یکی از معیارهای اصلی رضایت مشتریان می‌داند لذا تمامی خدمات در سریع‌ترین زمان ممکن انجام می‌شود.',
		'feature_4_title'     => 'محیط سالم',
		'feature_4_text'      => 'سالن زیبایی نقره، همواره بر برقراری محیط سالم و آرام برای مشتریان خود تاکید داشته است.',

		'site_logo_id'        => 0,
		'header_menu_id'      => 0,
		'footer_menu_id'      => 0,

		'reserve_btn_text'    => 'رزرو نوبت از اینستاگرام',
		'reserve_btn_url'     => '',
		'reserve_btn_target'  => '_blank',

		'blog_section_title'  => 'آخرین مطالب وبلاگ نقره',

		'topbar_enabled'   => 0,
		'mobile_phone'     => '',
		'topbar_v_desktop' => 1,
		'topbar_v_tablet'  => 1,
		'topbar_v_mobile'  => 1,

		'header_btn_enabled' => 0,
		'header_btn_text'    => 'تماس با ما',
		'header_btn_url'     => '',
		'header_btn_target'  => '_self',
		'header_btn_v_desktop' => 1,
		'header_btn_v_tablet'  => 1,
		'header_btn_v_mobile'  => 1,

		'ad_enabled'       => 0,
		'ad_col1_image_id' => 0,
		'ad_col1_link'     => '',
		'ad_col2_image_id' => 0,
		'ad_col2_link'     => '',
		'ad_v_desktop'     => 1,
		'ad_v_tablet'      => 1,
		'ad_v_mobile'      => 1,

		'working_hours'            => "شنبه تا چهارشنبه: ۹:۰۰ تا ۲۰:۰۰\nپنجشنبه: ۹:۰۰ تا ۱۸:۰۰\nجمعه: تعطیل",
		'footer_licenses_interval' => 3,

		'footer_rights_enabled' => 1,
		'footer_rights_text'    => 'تمامی حقوق مادی و معنوی این وب‌سایت متعلق به سالن زیبایی نقره می‌باشد و هر گونه کپی‌برداری پیگرد قانونی دارد.',
		'map_link_text'         => 'مشاهده موقعیت روی نقشه (نشان/بلد)',

		'decorative_bg_enabled' => 1,
		'hero_sparkle_enabled'  => 0,

		'hero_badges_enabled' => 0,
		'hero_badge_1'        => 'رضایت ۱۰۰٪ مشتریان',
		'hero_badge_2'        => 'متریال اورجینال',
		'hero_badge_3'        => 'مجوز رسمی اتحادیه',

		'instagram_promo_title'     => 'ما را در اینستاگرام دنبال کنید',
		'instagram_promo_text'      => 'جدیدترین کارها، تخفیف‌ها و نوبت‌دهی آنلاین را در صفحه اینستاگرام نقره ببینید.',
		'instagram_promo_btn_text'  => 'دنبال کردن در اینستاگرام',
		'instagram_promo_image_id'  => 0,
		'instagram_promo_position'  => 'right',
		'instagram_promo_watermark' => 'Instagram',

		'sticky_header_enabled' => 1,

		'contact_map_embed_enabled' => 0,
		'contact_map_embed_code'    => '',

		'show_post_date' => 1,

		'scroll_top_enabled' => 1,

		'discount_box_enabled'     => 1,
		'discount_box_title'       => 'می‌خوای از تخفیفات ما باخبر بشی؟ شماره موبایلت رو ثبت کن',
		'discount_box_button_text' => 'ثبت‌نام',

		'captcha_enabled' => 1,
		'captcha_type'    => 'math',

		'salon_name_for_qr' => 'سالن زیبایی نقره',

		/* 🔑 فونت‌ها */
		'site_font_id'  => '',
		'admin_font_id' => '',
	);
}

/**
 * 🔑 پاک‌سازی تنظیمات عمومی
 */
function noghre_sanitize_general_options( $input ) {
	$current = get_option( 'noghre_general_options', array() );
	if ( ! is_array( $current ) ) {
		$current = array();
	}

	if ( isset( $current['admin_access_key'] ) ) {
		unset( $current['admin_access_key'] );
	}

	if ( ! is_array( $input ) ) {
		return $current;
	}

	$defaults = noghre_default_general_options();

	$text_fields = array(
		'hero_title', 'tagline_1', 'tagline_2',
		'address', 'phone', 'mobile_phone', 'map_link_text',
		'blog_section_title',
		'reserve_btn_text', 'header_btn_text', 'header_btn_url',
		'hero_badge_1', 'hero_badge_2', 'hero_badge_3',
		'instagram_promo_title', 'instagram_promo_btn_text',
		'instagram_promo_watermark',
		'discount_box_title', 'discount_box_button_text',
		'salon_name_for_qr',
		'feature_1_title', 'feature_2_title', 'feature_3_title', 'feature_4_title',
	);
	$textarea_fields = array(
		'hero_text', 'maintenance_message', 'footer_rights_text',
		'working_hours', 'instagram_promo_text',
		'feature_1_text', 'feature_2_text', 'feature_3_text', 'feature_4_text',
	);
	$url_fields = array(
		'map_url', 'instagram_url', 'telegram_url', 'whatsapp_url',
		'reserve_btn_url', 'instagram_promo_btn_url',
		'ad_col1_link', 'ad_col2_link',
	);
	$int_fields = array(
		'hero_image_id', 'site_logo_id', 'header_menu_id', 'footer_menu_id',
		'ad_col1_image_id', 'ad_col2_image_id',
		'instagram_promo_image_id',
	);
	$checkbox_fields = array(
		'maintenance_enabled', 'topbar_enabled', 'header_btn_enabled',
		'ad_enabled', 'sticky_header_enabled', 'footer_rights_enabled',
		'decorative_bg_enabled', 'hero_sparkle_enabled', 'hero_badges_enabled',
		'contact_map_embed_enabled', 'show_post_date', 'scroll_top_enabled',
		'discount_box_enabled', 'captcha_enabled',
		'topbar_v_desktop', 'topbar_v_tablet', 'topbar_v_mobile',
		'header_btn_v_desktop', 'header_btn_v_tablet', 'header_btn_v_mobile',
		'ad_v_desktop', 'ad_v_tablet', 'ad_v_mobile',
	);

	foreach ( $text_fields as $key ) {
		if ( array_key_exists( $key, $input ) ) {
			$current[ $key ] = sanitize_text_field( $input[ $key ] );
		}
	}
	foreach ( $textarea_fields as $key ) {
		if ( array_key_exists( $key, $input ) ) {
			if ( in_array( $key, array( 'hero_text', 'maintenance_message' ), true ) ) {
				$current[ $key ] = wp_kses_post( $input[ $key ] );
			} else {
				$current[ $key ] = sanitize_textarea_field( $input[ $key ] );
			}
		}
	}
	foreach ( $url_fields as $key ) {
		if ( array_key_exists( $key, $input ) ) {
			$current[ $key ] = esc_url_raw( $input[ $key ] );
		}
	}
	foreach ( $int_fields as $key ) {
		if ( array_key_exists( $key, $input ) ) {
			$current[ $key ] = absint( $input[ $key ] );
		}
	}
	foreach ( $checkbox_fields as $key ) {
		if ( array_key_exists( $key, $input ) ) {
			$current[ $key ] = ! empty( $input[ $key ] ) ? 1 : 0;
		}
	}

	if ( array_key_exists( 'reserve_btn_target', $input ) ) {
		$current['reserve_btn_target'] = ( $input['reserve_btn_target'] === '_self' ) ? '_self' : '_blank';
	}
	if ( array_key_exists( 'header_btn_target', $input ) ) {
		$current['header_btn_target'] = ( $input['header_btn_target'] === '_blank' ) ? '_blank' : '_self';
	}
	if ( array_key_exists( 'instagram_promo_position', $input ) ) {
		$current['instagram_promo_position'] = ( $input['instagram_promo_position'] === 'left' ) ? 'left' : 'right';
	}
	if ( array_key_exists( 'blog_posts_count', $input ) ) {
		$current['blog_posts_count'] = max( 1, absint( $input['blog_posts_count'] ) );
	}
	if ( array_key_exists( 'footer_licenses_interval', $input ) ) {
		$current['footer_licenses_interval'] = max( 1, absint( $input['footer_licenses_interval'] ) );
	}
	if ( array_key_exists( 'contact_map_embed_code', $input ) ) {
		$current['contact_map_embed_code'] = wp_unslash( $input['contact_map_embed_code'] );
	}

	if ( array_key_exists( 'captcha_type', $input ) ) {
		$allowed_captcha_types = array( 'math', 'riddle', 'longpress' );
		$current['captcha_type'] = in_array( $input['captcha_type'], $allowed_captcha_types, true )
			? $input['captcha_type']
			: 'math';
	}

	if ( array_key_exists( 'site_font_id', $input ) ) {
		$current['site_font_id'] = sanitize_key( $input['site_font_id'] );
	}
	if ( array_key_exists( 'admin_font_id', $input ) ) {
		$current['admin_font_id'] = sanitize_key( $input['admin_font_id'] );
	}

	foreach ( $defaults as $key => $default_value ) {
		if ( ! array_key_exists( $key, $current ) ) {
			$current[ $key ] = $default_value;
		}
	}

	return $current;
}

/* ==========================================================================
 * تنظیمات اپلیکیشن
 * ========================================================================== */
function noghre_default_app_options() {
	$default_menus = array(
		array( 'id' => 'mobile',  'title' => 'صفحه اصلی', 'icon' => 'dashicons-admin-home',    'url_type' => 'internal', 'url' => '', 'shortcode' => 'ariatin-noghreh-mobile',  'sort_order' => 1, 'active' => 1 ),
		array( 'id' => 'list',    'title' => 'رزروها',    'icon' => 'dashicons-list-view',     'url_type' => 'internal', 'url' => '', 'shortcode' => 'ariatin-noghreh-list',    'sort_order' => 2, 'active' => 1 ),
		array( 'id' => 'signup',  'title' => 'ثبت شماره', 'icon' => 'dashicons-admin-users',   'url_type' => 'internal', 'url' => '', 'shortcode' => 'ariatin-noghreh-signup',  'sort_order' => 3, 'active' => 1 ),
		array( 'id' => 'scanner', 'title' => 'اسکنر QR',  'icon' => 'dashicons-camera',        'url_type' => 'internal', 'url' => '', 'shortcode' => 'ariatin-noghreh-scanner', 'sort_order' => 4, 'active' => 1 ),
	);

	return array(
		'app_title'           => 'پنل مدیریت نقره',
		'app_subtitle'        => 'نسخه موبایل',
		'app_logo_text'       => 'ن',
		'app_logo_id'         => 0,
		'app_page_id'         => 0,
		'default_nav_id'      => 'mobile',
		'default_section'     => 'mobile',
		/* 🔑 محتوای صفحه اصلی اپ (section=mobile) */
		'mobile_section_view' => 'both', // both | list | signup | scanner | check
		'app_bottom_nav'      => $default_menus,
		'pwa_enabled'         => 1,
		'pwa_short_name'      => 'نقره',
		'pwa_name'            => 'سالن زیبایی نقره',
		'pwa_theme_color'     => '#7A2E38',
		'pwa_bg_color'        => '#FBF7F4',
		'pwa_icon_id'         => 0,
		'pwa_display'         => 'standalone',
		'pwa_orientation'     => 'portrait',
	);
}

function noghre_sanitize_app_options( $input ) {
	$defaults = noghre_default_app_options();
	$out      = array();

	$out['app_title']     = sanitize_text_field( $input['app_title'] ?? $defaults['app_title'] );
	$out['app_subtitle']  = sanitize_text_field( $input['app_subtitle'] ?? $defaults['app_subtitle'] );
	$out['app_logo_text'] = sanitize_text_field( $input['app_logo_text'] ?? $defaults['app_logo_text'] );
	$out['app_logo_id']   = absint( $input['app_logo_id'] ?? 0 );
	$out['app_page_id']   = absint( $input['app_page_id'] ?? 0 );

	$allowed_sections = array( 'mobile', 'list', 'signup', 'scanner' );
	$out['default_section'] = in_array( $input['default_section'] ?? '', $allowed_sections, true )
			? $input['default_section']
			: 'mobile';

	/* 🔑 محتوای صفحه اصلی اپ */
	$allowed_views = array( 'both', 'list', 'signup', 'scanner', 'check' );
	$out['mobile_section_view'] = in_array( $input['mobile_section_view'] ?? '', $allowed_views, true )
			? $input['mobile_section_view']
			: 'both';

	/* 🔑 منوهای پایین اپ */
	$out['app_bottom_nav'] = array();
	if ( ! empty( $input['app_bottom_nav'] ) && is_array( $input['app_bottom_nav'] ) ) {
		foreach ( $input['app_bottom_nav'] as $item ) {
			$title = sanitize_text_field( $item['title'] ?? '' );
			if ( $title === '' ) continue;

			$icon = $item['icon'] ?? 'dashicons-admin-generic';
			if ( is_numeric( $icon ) ) {
				$icon = absint( $icon );
			} else {
				$icon = sanitize_text_field( $icon );
				if ( strpos( $icon, 'dashicons-' ) !== 0 ) {
					$icon = 'dashicons-admin-generic';
				}
			}

			$out['app_bottom_nav'][] = array(
				'id'         => sanitize_key( $item['id'] ?? 'menu_' . wp_rand( 1000, 9999 ) ),
				'title'      => $title,
				'icon'       => $icon,
				'url_type'   => in_array( $item['url_type'] ?? '', array( 'internal', 'home', 'admin', 'external' ), true ) ? $item['url_type'] : 'internal',
				'url'        => esc_url_raw( $item['url'] ?? '' ),
				'shortcode'  => sanitize_text_field( $item['shortcode'] ?? '' ),
				'sort_order' => absint( $item['sort_order'] ?? 0 ),
				'active'     => isset( $item['active'] ) ? 1 : 0,
			);
		}
	}

	/* 🔑 ذخیره منوی پیش‌فرض */
	$allowed_nav_ids = wp_list_pluck( $out['app_bottom_nav'], 'id' );
	$requested_default = isset( $input['default_nav_id'] ) ? sanitize_key( $input['default_nav_id'] ) : '';
	$out['default_nav_id'] = ( $requested_default && in_array( $requested_default, $allowed_nav_ids, true ) )
			? $requested_default
			: ( ! empty( $allowed_nav_ids[0] ) ? $allowed_nav_ids[0] : 'mobile' );

	$out['pwa_enabled']     = isset( $input['pwa_enabled'] ) ? 1 : 0;
	$out['pwa_short_name']  = sanitize_text_field( $input['pwa_short_name'] ?? $defaults['pwa_short_name'] );
	$out['pwa_name']        = sanitize_text_field( $input['pwa_name'] ?? $defaults['pwa_name'] );
	$out['pwa_theme_color'] = sanitize_hex_color( $input['pwa_theme_color'] ?? $defaults['pwa_theme_color'] );
	$out['pwa_bg_color']    = sanitize_hex_color( $input['pwa_bg_color'] ?? $defaults['pwa_bg_color'] );
	$out['pwa_icon_id']     = absint( $input['pwa_icon_id'] ?? 0 );

	$allowed_display = array( 'standalone', 'fullscreen', 'minimal-ui', 'browser' );
	$out['pwa_display'] = in_array( $input['pwa_display'] ?? '', $allowed_display, true ) ? $input['pwa_display'] : 'standalone';

	$allowed_orientation = array( 'portrait', 'landscape', 'any' );
	$out['pwa_orientation'] = in_array( $input['pwa_orientation'] ?? '', $allowed_orientation, true ) ? $input['pwa_orientation'] : 'portrait';

	return $out;
}

function noghre_get_app_options() {
	$defaults = noghre_default_app_options();
	$saved    = get_option( 'noghre_app_options', array() );
	if ( ! is_array( $saved ) ) $saved = array();
	return wp_parse_args( $saved, $defaults );
}

function noghre_render_app_menu_icon( $icon ) {
	if ( is_numeric( $icon ) && $icon > 0 ) {
		$url = wp_get_attachment_image_url( (int) $icon, 'thumbnail' );
		if ( $url ) {
			return '<img src="' . esc_url( $url ) . '" alt="" style="width:22px;height:22px;object-fit:contain;display:block;">';
		}
	}

	// dashicon
	if ( is_string( $icon ) && strpos( $icon, 'dashicons-' ) === 0 ) {
		return '<span class="' . esc_attr( $icon ) . '"></span>';
	}

	// fallback
	return '<span class="dashicons dashicons-admin-generic"></span>';
}

/* ==========================================================================
 * تنظیمات پیامک
 * ========================================================================== */
function noghre_default_sms_options() {
	return array(
		'sms_sender_number' => '',
		'sms_discount_enabled' => 1,
		'sms_customer_enabled' => 1,
		'sms_branch_enabled'   => 1,
		'sms_pattern_code'               => '',
		'sms_pattern_name_var'           => 'name',
		'sms_pattern_discount_var'       => 'discount',
		'sms_pattern_discount_val'       => '',
		'sms_phonebook_id'               => '',
		'sms_customer_club_phonebook_id' => '',
		'sms_pattern_booking_branch'     => '',
		'sms_pattern_branch_var_shobe'   => 'shobe',
		'sms_pattern_branch_var_peygiri' => 'peygiri',
		'sms_pattern_branch_var_modir'   => 'modir',
		'sms_pattern_booking_customer'      => '',
		'sms_pattern_customer_var_name'     => 'name',
		'sms_pattern_customer_var_peygiri'  => 'peygiri',
		'sms_confirm_enabled'              => 1,
		'sms_pattern_booking_confirm'      => '',
		'sms_pattern_confirm_var_name'     => 'name',
		'sms_pattern_confirm_var_peygiri'  => 'peygiri',
		'sms_edit_enabled'              => 1,
		'sms_pattern_booking_edit'      => '',
		'sms_pattern_edit_var_name'     => 'name',
		'sms_pattern_edit_var_peygiri'  => 'peygiri',
		'sms_void_enabled'              => 1,
		'sms_pattern_booking_void'      => '',
		'sms_pattern_void_var_name'     => 'name',
		'sms_unused_enabled'             => 1,
		'sms_pattern_booking_unused'     => '',
		'sms_pattern_unused_var_name'    => 'name',
		'sms_pattern_unused_discount_var'=> 'discount',
		'sms_pattern_unused_discount_val'=> '',
		'sms_signup_enabled'          => 1,
		'sms_pattern_signup'          => '',
		'sms_pattern_signup_var_name' => 'name',
	);
}

function noghre_sanitize_sms_options( $input ) {
	return array(
		'sms_sender_number' => sanitize_text_field( $input['sms_sender_number'] ?? '' ),
		'sms_discount_enabled' => isset( $input['sms_discount_enabled'] ) ? 1 : 0,
		'sms_customer_enabled' => isset( $input['sms_customer_enabled'] ) ? 1 : 0,
		'sms_branch_enabled'   => isset( $input['sms_branch_enabled'] ) ? 1 : 0,
		'sms_pattern_code'         => sanitize_text_field( $input['sms_pattern_code'] ?? '' ),
		'sms_pattern_name_var'     => sanitize_text_field( $input['sms_pattern_name_var'] ?? 'name' ),
		'sms_pattern_discount_var' => sanitize_text_field( $input['sms_pattern_discount_var'] ?? 'discount' ),
		'sms_pattern_discount_val' => sanitize_text_field( $input['sms_pattern_discount_val'] ?? '' ),
		'sms_phonebook_id'               => sanitize_text_field( $input['sms_phonebook_id'] ?? '' ),
		'sms_customer_club_phonebook_id' => sanitize_text_field( $input['sms_customer_club_phonebook_id'] ?? '' ),
		'sms_pattern_booking_branch'     => sanitize_text_field( $input['sms_pattern_booking_branch'] ?? '' ),
		'sms_pattern_branch_var_shobe'   => sanitize_text_field( $input['sms_pattern_branch_var_shobe'] ?? 'shobe' ),
		'sms_pattern_branch_var_peygiri' => sanitize_text_field( $input['sms_pattern_branch_var_peygiri'] ?? 'peygiri' ),
		'sms_pattern_branch_var_modir'   => sanitize_text_field( $input['sms_pattern_branch_var_modir'] ?? 'modir' ),
		'sms_pattern_booking_customer'      => sanitize_text_field( $input['sms_pattern_booking_customer'] ?? '' ),
		'sms_pattern_customer_var_name'     => sanitize_text_field( $input['sms_pattern_customer_var_name'] ?? 'name' ),
		'sms_pattern_customer_var_peygiri'  => sanitize_text_field( $input['sms_pattern_customer_var_peygiri'] ?? 'peygiri' ),
		'sms_confirm_enabled'              => isset( $input['sms_confirm_enabled'] ) ? 1 : 0,
		'sms_pattern_booking_confirm'      => sanitize_text_field( $input['sms_pattern_booking_confirm'] ?? '' ),
		'sms_pattern_confirm_var_name'     => sanitize_text_field( $input['sms_pattern_confirm_var_name'] ?? 'name' ),
		'sms_pattern_confirm_var_peygiri'  => sanitize_text_field( $input['sms_pattern_confirm_var_peygiri'] ?? 'peygiri' ),
		'sms_edit_enabled'              => isset( $input['sms_edit_enabled'] ) ? 1 : 0,
		'sms_pattern_booking_edit'      => sanitize_text_field( $input['sms_pattern_booking_edit'] ?? '' ),
		'sms_pattern_edit_var_name'     => sanitize_text_field( $input['sms_pattern_edit_var_name'] ?? 'name' ),
		'sms_pattern_edit_var_peygiri'  => sanitize_text_field( $input['sms_pattern_edit_var_peygiri'] ?? 'peygiri' ),
		'sms_void_enabled'              => isset( $input['sms_void_enabled'] ) ? 1 : 0,
		'sms_pattern_booking_void'      => sanitize_text_field( $input['sms_pattern_booking_void'] ?? '' ),
		'sms_pattern_void_var_name'     => sanitize_text_field( $input['sms_pattern_void_var_name'] ?? 'name' ),
		'sms_unused_enabled'              => isset( $input['sms_unused_enabled'] ) ? 1 : 0,
		'sms_pattern_booking_unused'      => sanitize_text_field( $input['sms_pattern_booking_unused'] ?? '' ),
		'sms_pattern_unused_var_name'     => sanitize_text_field( $input['sms_pattern_unused_var_name'] ?? 'name' ),
		'sms_pattern_unused_discount_var' => sanitize_text_field( $input['sms_pattern_unused_discount_var'] ?? 'discount' ),
		'sms_pattern_unused_discount_val' => sanitize_text_field( $input['sms_pattern_unused_discount_val'] ?? '' ),
		'sms_signup_enabled'          => isset( $input['sms_signup_enabled'] ) ? 1 : 0,
		'sms_pattern_signup'          => sanitize_text_field( $input['sms_pattern_signup'] ?? '' ),
		'sms_pattern_signup_var_name' => sanitize_text_field( $input['sms_pattern_signup_var_name'] ?? 'name' ),
	);
}

function noghre_get_sms_options() {
	$defaults = noghre_default_sms_options();
	$saved    = get_option( 'noghre_sms_options', array() );
	if ( ! is_array( $saved ) ) $saved = array();
	return wp_parse_args( $saved, $defaults );
}

function noghre_get_sms_option( $key, $fallback = '' ) {
	$options = noghre_get_sms_options();
	return isset( $options[ $key ] ) && $options[ $key ] !== '' ? $options[ $key ] : $fallback;
}

/* ==========================================================================
 * تنظیمات رنگ‌بندی
 * ========================================================================== */
function noghre_default_colors() {
	return array(
		'color_bg'       => '#FBF7F4',
		'color_bg_alt'   => '#F3ECE6',
		'color_ink'      => '#241F1E',
		'color_ink_soft' => '#5B524F',
		'color_accent'   => '#7A2E38',
		'color_accent_2' => '#5C2129',
		'color_taupe'    => '#C9C0B8',
		'color_blush'    => '#EFDCD9',
		'color_gold'     => '#B08D57',
		'color_white'    => '#FFFFFF',
		'color_line'     => '#241F1E',
	);
}

function noghre_sanitize_colors( $input ) {
	$defaults = noghre_default_colors();
	$out      = array();
	foreach ( $defaults as $key => $default ) {
		$val = isset( $input[ $key ] ) ? sanitize_hex_color( $input[ $key ] ) : '';
		$out[ $key ] = $val ? $val : $default;
	}
	return $out;
}

function noghre_hex_to_rgba( $hex, $alpha = 1 ) {
	$hex = ltrim( (string) $hex, '#' );
	if ( strlen( $hex ) === 3 ) {
		$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
	}
	if ( strlen( $hex ) !== 6 ) {
		return 'rgba(0,0,0,' . floatval( $alpha ) . ')';
	}
	$r = hexdec( substr( $hex, 0, 2 ) );
	$g = hexdec( substr( $hex, 2, 2 ) );
	$b = hexdec( substr( $hex, 4, 2 ) );
	return "rgba({$r},{$g},{$b}," . floatval( $alpha ) . ")";
}

/* ==========================================================================
 * تنظیمات درگاه پرداخت
 * ========================================================================== */
function noghre_default_zarinpal_options() {
	return array(
		'zarinpal_merchant_id' => '',
		'zarinpal_sandbox'     => 0,
	);
}

function noghre_sanitize_zarinpal_options( $input ) {
	return array(
		'zarinpal_merchant_id' => sanitize_text_field( $input['zarinpal_merchant_id'] ?? '' ),
		'zarinpal_sandbox'     => isset( $input['zarinpal_sandbox'] ) ? 1 : 0,
	);
}

/* ==========================================================================
 * تنظیمات رزرو
 * ========================================================================== */
function noghre_default_booking_options() {
	return array(
		'booking_description'   => '<p>برای رزرو نوبت، فرم زیر را تکمیل کنید.</p>',
		'discount_code_enabled' => 1,
		'payment_enabled'       => 0,
		'payment_amount'        => 100000,
		'pay_button_text'       => 'پرداخت و رزرو وقت',
		'reserve_button_text'   => 'رزرو وقت',
		'booking_page_id'       => 0,
		'check_page_id'         => 0,
	);
}

function noghre_sanitize_booking_options( $input ) {
	$defaults = noghre_default_booking_options();
	return array(
		'booking_description'   => wp_kses_post( $input['booking_description'] ?? $defaults['booking_description'] ),
		'discount_code_enabled' => isset( $input['discount_code_enabled'] ) ? 1 : 0,
		'payment_enabled'       => isset( $input['payment_enabled'] ) ? 1 : 0,
		'payment_amount'        => max( 0, absint( $input['payment_amount'] ?? 0 ) ),
		'pay_button_text'       => sanitize_text_field( $input['pay_button_text'] ?? $defaults['pay_button_text'] ),
		'reserve_button_text'   => sanitize_text_field( $input['reserve_button_text'] ?? $defaults['reserve_button_text'] ),
		'booking_page_id'       => absint( $input['booking_page_id'] ?? 0 ),
		'check_page_id'         => absint( $input['check_page_id'] ?? 0 ),
	);
}

/* ==========================================================================
 * توابع دسترسی
 * ========================================================================== */
function noghre_get_general_options() {
	$defaults = noghre_default_general_options();
	$saved    = get_option( 'noghre_general_options', array() );
	if ( ! is_array( $saved ) ) $saved = array();
	return wp_parse_args( $saved, $defaults );
}

function noghre_get_option( $key, $fallback = '' ) {
	$options = noghre_get_general_options();
	return isset( $options[ $key ] ) && $options[ $key ] !== '' ? $options[ $key ] : $fallback;
}

function noghre_get_colors() {
	$defaults = noghre_default_colors();
	$saved    = get_option( 'noghre_colors', array() );
	if ( ! is_array( $saved ) ) $saved = array();
	return wp_parse_args( $saved, $defaults );
}

function noghre_get_zarinpal_options() {
	$defaults = noghre_default_zarinpal_options();
	$saved    = get_option( 'noghre_zarinpal_options', array() );
	if ( ! is_array( $saved ) ) $saved = array();
	return wp_parse_args( $saved, $defaults );
}

function noghre_get_booking_options() {
	$defaults = noghre_default_booking_options();
	$saved    = get_option( 'noghre_booking_options', array() );
	if ( ! is_array( $saved ) ) $saved = array();
	return wp_parse_args( $saved, $defaults );
}

function noghre_get_booking_option( $key, $fallback = '' ) {
	$o = noghre_get_booking_options();
	return isset( $o[ $key ] ) && $o[ $key ] !== '' ? $o[ $key ] : $fallback;
}

function noghre_get_booking_branches() {
	$items = get_option( 'noghre_booking_branches', array() );
	return is_array( $items ) ? array_values( $items ) : array();
}

function noghre_get_booking_services() {
	$items = get_option( 'noghre_booking_services', array() );
	return is_array( $items ) ? array_values( $items ) : array();
}

function noghre_get_booking_hours() {
	$items = get_option( 'noghre_booking_hours', array() );
	return is_array( $items ) ? array_values( $items ) : array();
}

function noghre_get_booking_page_url() {
	$id = (int) noghre_get_booking_option( 'booking_page_id', 0 );
	return $id ? get_permalink( $id ) : '';
}

function noghre_get_check_page_url() {
	$id = (int) noghre_get_booking_option( 'check_page_id', 0 );
	return $id ? get_permalink( $id ) : '';
}

function noghre_get_team_members() {
	$items = get_option( 'noghre_team_members', array() );
	return is_array( $items ) ? array_values( $items ) : array();
}

function noghre_get_faqs() {
	$items = get_option( 'noghre_faqs', array() );
	if ( ! is_array( $items ) || empty( $items ) ) {
		return array(
			array(
				'question' => 'برای رزرو نوبت باید چه کار کنم؟',
				'answer'   => 'کافیست از طریق شماره تماس یا آیدی اینستاگرام سالن با ما در ارتباط باشید تا نوبت شما هماهنگ شود.',
			),
		);
	}
	return array_values( $items );
}

function noghre_get_licenses() {
	$items = get_option( 'noghre_licenses', array() );
	return is_array( $items ) ? array_values( $items ) : array();
}

function noghre_default_visibility() {
	return array( 'desktop' => 1, 'tablet' => 1, 'mobile' => 1 );
}

function noghre_vis_classes( $desktop = 1, $tablet = 1, $mobile = 1 ) {
	$classes = array();
	if ( ! $desktop ) $classes[] = 'noghre-hide-desktop';
	if ( ! $tablet )  $classes[] = 'noghre-hide-tablet';
	if ( ! $mobile )  $classes[] = 'noghre-hide-mobile';
	return implode( ' ', $classes );
}

function noghre_default_sections() {
	$v = noghre_default_visibility();
	return array(
		array( 'id' => 'hero',             'key' => 'hero',             'enabled' => 1, 'visibility' => $v ),
		array( 'id' => 'features',         'key' => 'features',         'enabled' => 1, 'visibility' => $v ),
		array( 'id' => 'team',             'key' => 'team',             'enabled' => 1, 'visibility' => $v ),
		array( 'id' => 'page_builder_1',   'key' => 'page_builder',     'enabled' => 1, 'page_id' => 0, 'visibility' => $v ),
		array( 'id' => 'instagram_promo',  'key' => 'instagram_promo',  'enabled' => 1, 'visibility' => $v ),
		array( 'id' => 'faq',              'key' => 'faq',              'enabled' => 1, 'visibility' => $v ),
		array( 'id' => 'blog',             'key' => 'blog',             'enabled' => 1, 'visibility' => $v ),
		array( 'id' => 'contact',          'key' => 'contact',          'enabled' => 1, 'visibility' => $v ),
	);
}

function noghre_section_labels() {
	return array(
		'hero'            => 'بخش معرفی (هیرو)',
		'features'        => 'ویژگی‌های سالن (۴ ستون)',
		'team'            => 'پرسنل سالن',
		'page_builder'    => 'محتوای برگه متصل (صفحه‌ساز / گوتنبرگ)',
		'instagram_promo' => 'بخش تبلیغاتی اینستاگرام',
		'faq'             => 'سوالات متداول',
		'blog'            => 'آخرین مطالب وبلاگ',
		'contact'         => 'تماس با ما',
	);
}

function noghre_repeatable_section_types() {
	return array( 'page_builder' );
}

function noghre_get_sections() {
	$saved = get_option( 'noghre_sections', array() );
	if ( ! is_array( $saved ) || empty( $saved ) ) return noghre_default_sections();

	foreach ( $saved as &$row ) {
		if ( empty( $row['id'] ) ) $row['id'] = $row['key'];
	}
	unset( $row );

	$known_ids = wp_list_pluck( $saved, 'id' );
	foreach ( noghre_default_sections() as $def ) {
		if ( in_array( $def['key'], noghre_repeatable_section_types(), true ) ) continue;
		if ( ! in_array( $def['id'], $known_ids, true ) ) $saved[] = $def;
	}
	foreach ( $saved as &$row ) {
		if ( empty( $row['visibility'] ) || ! is_array( $row['visibility'] ) ) {
			$row['visibility'] = noghre_default_visibility();
		}
	}
	unset( $row );
	return array_values( $saved );
}

function noghre_get_enabled_sections() {
	return array_filter( noghre_get_sections(), function( $s ) {
		return ! empty( $s['enabled'] );
	} );
}