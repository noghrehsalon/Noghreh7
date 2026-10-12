<?php
/**
 * Template Name: عریض (تمام‌عرض)
 * Description: مثل برگه معمولی، اما بدون محدودیت عرض محتوا؛ مناسب برای صفحاتی که با صفحه‌ساز کار شده‌اند.
 */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();
?>
<section class="section section-page section-page-fullwidth">
	<div class="container">
		<?php while ( have_posts() ) : the_post(); ?>
			<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
				<?php if ( ! is_front_page() ) : ?>
					<header class="page-header">
						<h1 class="page-title"><?php the_title(); ?></h1>
					</header>
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
