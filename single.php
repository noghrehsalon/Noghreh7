<?php
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();
?>
<section class="section section-single">
	<div class="container container-narrow">
		<?php while ( have_posts() ) : the_post(); ?>
			<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
				<?php noghre_render_breadcrumb(); ?>
				<header class="entry-header">
					<h1 class="entry-title"><?php the_title(); ?></h1>
				</header>

				<?php if ( has_post_thumbnail() ) : ?>
					<div class="entry-thumb entry-thumb-centered featured-special">
						<?php the_post_thumbnail( 'noghre-single-featured' ); ?>
					</div>
				<?php endif; ?>

				<div class="entry-content">
					<?php the_content(); ?>
				</div>

				<?php
				$tags      = get_the_tags();
				$show_date = (int) noghre_get_option( 'show_post_date', 1 );
				if ( $tags || $show_date ) :
					?>
					<div class="entry-meta-row">
						<?php if ( $tags ) : ?>
							<div class="entry-hashtags">
								<?php foreach ( $tags as $tag ) : ?>
									<a href="<?php echo esc_url( get_tag_link( $tag ) ); ?>" class="hashtag-pill">#<?php echo esc_html( $tag->name ); ?></a>
								<?php endforeach; ?>
							</div>
						<?php endif; ?>
						<?php if ( $show_date ) : ?>
							<div class="entry-date"><?php echo esc_html( noghre_jalali_date( get_the_time( 'U' ), 'j F Y' ) ); ?></div>
						<?php endif; ?>
					</div>
				<?php endif; ?>
			</article>

			<?php noghre_render_post_ads(); ?>

			<nav class="post-nav">
				<div><?php previous_post_link( '%link', '← %title' ); ?></div>
				<div><?php next_post_link( '%link', '%title →' ); ?></div>
			</nav>

			<?php if ( comments_open() || get_comments_number() ) : comments_template(); endif; ?>
		<?php endwhile; ?>
	</div>
</section>
<?php get_footer(); ?>
