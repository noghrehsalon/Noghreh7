<?php
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();
?>
<section class="section section-archive">
	<div class="container">
		<header class="archive-header">
			<h1 class="page-title"><?php the_archive_title(); ?></h1>
			<?php the_archive_description( '<div class="archive-desc">', '</div>' ); ?>
		</header>

		<?php if ( have_posts() ) : ?>
			<div class="archive-grid">
				<?php while ( have_posts() ) : the_post(); ?>
					<article id="post-<?php the_ID(); ?>" <?php post_class( 'archive-card' ); ?>>
						<a href="<?php the_permalink(); ?>" class="archive-card-thumb">
							<?php if ( has_post_thumbnail() ) : the_post_thumbnail( 'medium_large' ); else : ?>
								<div class="archive-card-thumb-placeholder"></div>
							<?php endif; ?>
						</a>
						<div class="archive-card-body">
							<h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
							<p><?php echo esc_html( wp_trim_words( get_the_excerpt(), 20 ) ); ?></p>
							<a class="read-more" href="<?php the_permalink(); ?>">ادامه مطلب</a>
						</div>
					</article>
				<?php endwhile; ?>
			</div>
			<?php noghre_pagination(); ?>
		<?php else : ?>
			<p>مطلبی یافت نشد.</p>
		<?php endif; ?>
	</div>
</section>
<?php get_footer(); ?>
