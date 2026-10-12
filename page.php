<?php
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();
?>
<section class="section section-page">
	<div class="container container-narrow">
		<?php while ( have_posts() ) : the_post(); ?>
			<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
				<?php if ( ! is_front_page() ) : ?>
					<header class="page-header">
						<h1 class="page-title"><?php the_title(); ?></h1>
					</header>
				<?php endif; ?>

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
