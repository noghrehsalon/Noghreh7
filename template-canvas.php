<?php
/**
 * Template Name: بوم خالی (بدون هدر و فوتر)
 * Description: مناسب برای صفحاتی که با صفحه‌ساز (مثل المنتور) به‌طور کامل طراحی می‌شوند.
 */
if ( ! defined( 'ABSPATH' ) ) exit;
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php wp_head(); ?>
</head>
<body <?php body_class( 'noghre-canvas' ); ?>>
<?php wp_body_open(); ?>
<?php while ( have_posts() ) : the_post(); the_content(); endwhile; ?>
<?php wp_footer(); ?>
</body>
</html>
