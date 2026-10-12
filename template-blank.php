<?php
/**
 * قالب خالی — فقط محتوای صفحه را نمایش می‌دهد، بدون هدر و فوتر قالب اصلی
 * برای صفحه استعلام موبایل (با mob=1) و سایر صفحات اپ.
 *
 * @package Noghre
 */

if ( ! defined( 'ABSPATH' ) ) exit;
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5, viewport-fit=cover">
<meta name="robots" content="noindex, nofollow">
<?php wp_head(); ?>
</head>
<body <?php body_class( 'noghre-blank-template' ); ?>>
<?php wp_body_open(); ?>

<div id="page" class="site">
	<div id="content" class="site-content">
		<?php
		while ( have_posts() ) :
			the_post();
			?>
			<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
				<div class="entry-content">
					<?php the_content(); ?>
				</div>
			</article>
			<?php
		endwhile;
		?>
	</div>
</div>

<?php wp_footer(); ?>
</body>
</html>