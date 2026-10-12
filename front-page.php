<?php
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();

/* در صورت انتخاب صفحه اصلی ثابت، پست جاری اسکن می‌شود ولی محتوای آن از طریق
   بخش «محتوای برگه متصل» در تنظیمات چیدمان صفحه اصلی مدیریت می‌شود، نه اینجا. */
foreach ( noghre_get_enabled_sections() as $section ) {
	noghre_render_homepage_section( $section );
}

get_footer();
