<?php
/**
 * Template Name: برگه پیش‌فرض بدون عنوان
 * Description: دقیقاً مثل برگه پیش‌فرض (با هدر، فوتر، تصویر شاخص و دیدگاه‌ها)، اما بدون نمایش عنوان در بالای محتوا.
 */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();
?>
<section class="section section-page section-page-no-title">
	<div class="container container-narrow">
		<?php while ( have_posts() ) : the_post(); ?>
			<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

				<?php if ( has_post_thumbnail() && ! is_page_template( 'elementor_header_footer' ) ) : ?>
					<div class="page-thumb"><?php the_post_thumbnail( 'large' ); ?></div>
				<?php endif; ?>

				<div class="entry-content">
					<?php the_content(); ?>
				</div>

				<?php if ( comments_open() || get_comments_number() ) : comments_template(); endif; ?>
			</article>
		<?php endwhile; ?>
	</div>
</section>
<?php get_footer(); ?>