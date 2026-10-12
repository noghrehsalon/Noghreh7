<?php
/**
 * Template Name: برگه بدون عنوان
 * Description: مثل برگه معمولی، اما بدون نمایش عنوان در بالای محتوا.
 */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();
?>
<section class="section section-page section-page-no-title">
	<div class="container">
		<?php while ( have_posts() ) : the_post(); ?>
			<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
				<div class="entry-content">
					<?php the_content(); ?>
				</div>

				<?php if ( comments_open() || get_comments_number() ) : comments_template(); endif; ?>
			</article>
		<?php endwhile; ?>
	</div>
</section>
<?php get_footer(); ?>