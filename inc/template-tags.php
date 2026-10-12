<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/* ===================== هیرو ===================== */
function noghre_render_hero() {
	$hero_img = noghre_get_option( 'hero_image_id' ) ? wp_get_attachment_image_url( noghre_get_option( 'hero_image_id' ), 'large' ) : NOGHRE_URI . '/assets/img/placeholder-hero.svg';
	$reserve_text   = noghre_get_option( 'reserve_btn_text', 'رزرو نوبت از اینستاگرام' );
	$reserve_url    = noghre_get_option( 'reserve_btn_url' ) ?: noghre_get_option( 'instagram_url' );
	$reserve_target = noghre_get_option( 'reserve_btn_target', '_blank' );

	$sparkle = (int) noghre_get_option( 'hero_sparkle_enabled', 0 );
	$badges_on = (int) noghre_get_option( 'hero_badges_enabled', 0 );
	$badges = array_filter( array(
		noghre_get_option( 'hero_badge_1' ),
		noghre_get_option( 'hero_badge_2' ),
		noghre_get_option( 'hero_badge_3' ),
	) );
	?>
	<section class="hero" data-reveal>
		<span class="hero-blob hero-blob-1" aria-hidden="true"></span>
		<span class="hero-blob hero-blob-2" aria-hidden="true"></span>
		<div class="container hero-grid">
			<div class="hero-text">
				<p class="hero-eyebrow"><?php echo esc_html( noghre_get_option( 'tagline_1' ) ); ?></p>
				<h1><?php echo esc_html( noghre_get_option( 'hero_title' ) ); ?></h1>
				<div class="hero-desc"><?php echo wp_kses_post( wpautop( noghre_get_option( 'hero_text' ) ) ); ?></div>
				<div class="hero-cta">
					<?php if ( $reserve_url ) : ?>
						<a class="btn btn-primary" href="<?php echo esc_url( $reserve_url ); ?>" target="<?php echo esc_attr( $reserve_target ); ?>" rel="noopener"><?php echo esc_html( $reserve_text ); ?></a>
					<?php endif; ?>
					<a class="btn btn-outline" href="#team">آشنایی با تیم نقره</a>
				</div>
				<p class="hero-tagline-2"><?php echo esc_html( noghre_get_option( 'tagline_2' ) ); ?></p>
			</div>
			<div class="hero-media">
				<div class="hero-media-frame<?php echo $sparkle ? ' has-sparkle' : ''; ?>">
					<img src="<?php echo esc_url( $hero_img ); ?>" alt="<?php bloginfo( 'name' ); ?>">
					<?php if ( $sparkle ) : ?>
						<span class="hero-sparkle-layer" aria-hidden="true">
							<i class="spark spark-1"></i>
							<i class="spark spark-2"></i>
							<i class="spark spark-3"></i>
							<i class="spark spark-4"></i>
							<i class="spark spark-5"></i>
							<span class="shimmer-sweep"></span>
						</span>
					<?php endif; ?>
				</div>
				<?php if ( $badges_on && ! empty( $badges ) ) : ?>
					<div class="hero-badges">
						<?php foreach ( array_values( $badges ) as $i => $text ) : ?>
							<span class="hero-badge hero-badge-<?php echo esc_attr( $i + 1 ); ?>"><?php echo esc_html( $text ); ?></span>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</section>
	<?php
}

/* ===================== ویژگی‌ها ===================== */
function noghre_render_features() {
	?>
	<section class="section section-features" data-reveal>
		<div class="container features-grid">
			<?php for ( $i = 1; $i <= 4; $i++ ) :
				$title = noghre_get_option( "feature_{$i}_title" );
				$text  = noghre_get_option( "feature_{$i}_text" );
				if ( ! $title ) continue;
				?>
				<div class="feature-card">
					<span class="feature-index"><?php echo esc_html( str_pad( $i, 2, '0', STR_PAD_LEFT ) ); ?></span>
					<h3><?php echo esc_html( $title ); ?></h3>
					<p><?php echo esc_html( $text ); ?></p>
				</div>
			<?php endfor; ?>
		</div>
	</section>
	<?php
}

/* ===================== بخش متصل به صفحه‌ساز ===================== */
function noghre_render_page_builder_section( $section = array() ) {
	$page_id = ! empty( $section['page_id'] ) ? (int) $section['page_id'] : 0;
	if ( ! $page_id ) return;

	$page = get_post( $page_id );
	if ( ! $page || $page->post_status !== 'publish' ) return;

	$content = apply_filters( 'the_content', $page->post_content );
	if ( trim( wp_strip_all_tags( $content ) ) === '' && trim( $content ) === '' ) return;
	?>
	<section class="section section-page-content" data-reveal>
		<div class="container container-narrow">
			<?php echo $content; ?>
		</div>
	</section>
	<?php
}

/* ===================== بخش تبلیغاتی اینستاگرام ===================== */
function noghre_render_instagram_promo() {
	$title    = noghre_get_option( 'instagram_promo_title' );
	$text     = noghre_get_option( 'instagram_promo_text' );
	$btn_text = noghre_get_option( 'instagram_promo_btn_text' );
	$btn_url  = noghre_get_option( 'instagram_promo_btn_url' ) ?: noghre_get_option( 'instagram_url' );
	$img_id   = noghre_get_option( 'instagram_promo_image_id' );
	$position = noghre_get_option( 'instagram_promo_position', 'right' );

	if ( ! $title && ! $text && ! $img_id ) return;
	?>
	<section class="section section-instagram-promo<?php echo ( $position === 'left' ) ? ' ig-promo-reverse' : ''; ?>" data-reveal>
		<span class="ig-promo-watermark" aria-hidden="true"><?php echo esc_html( noghre_get_option( 'instagram_promo_watermark', 'Instagram' ) ); ?></span>
		<div class="container ig-promo-grid">
			<div class="ig-promo-media">
				<?php if ( $img_id ) : ?>
					<div class="ig-promo-phone">
						<?php echo wp_get_attachment_image( $img_id, 'large', false, array( 'loading' => 'lazy' ) ); ?>
					</div>
				<?php else : ?>
					<div class="ig-promo-phone ig-promo-phone-placeholder">
						<?php echo noghre_icon( 'instagram' ); ?>
					</div>
				<?php endif; ?>
			</div>
			<div class="ig-promo-text">
				<?php if ( $title ) : ?><h2><?php echo esc_html( $title ); ?></h2><?php endif; ?>
				<?php if ( $text ) : ?><p><?php echo esc_html( $text ); ?></p><?php endif; ?>
				<?php if ( $btn_url ) : ?>
					<a class="btn btn-ig" href="<?php echo esc_url( $btn_url ); ?>" target="_blank" rel="noopener">
						<?php echo noghre_icon( 'instagram' ); ?>
						<span><?php echo esc_html( $btn_text ?: 'مشاهده اینستاگرام' ); ?></span>
					</a>
				<?php endif; ?>
			</div>
		</div>
	</section>
	<?php
}

/* ===================== تماس ===================== */
function noghre_render_contact() {
	$map_enabled = (int) noghre_get_option( 'contact_map_embed_enabled', 0 );
	$map_code    = noghre_get_option( 'contact_map_embed_code' );
	$phone       = noghre_get_option( 'phone' );
	$map_url     = noghre_get_option( 'map_url' );
	$has_map     = $map_enabled && trim( $map_code ) !== '';
	?>
	<section class="section section-contact" data-reveal>
		<div class="container">
			<h2 class="section-title">نقره منتظر شماست</h2>
			<div class="contact-grid<?php echo $has_map ? ' contact-grid-2col' : ''; ?>">
				<div class="contact-info-col">
					<div class="contact-info-top">
						<p><?php echo esc_html( noghre_get_option( 'address' ) ); ?></p>
						<div class="contact-btns">
							<?php if ( $phone ) : ?>
								<a class="btn btn-primary" href="tel:<?php echo esc_attr( preg_replace( '/\s+/', '', $phone ) ); ?>"><?php echo noghre_icon( 'phone' ); ?> <?php echo esc_html( $phone ); ?></a>
							<?php endif; ?>
							<?php if ( $map_url ) : ?>
								<a class="btn btn-outline" href="<?php echo esc_url( $map_url ); ?>" target="_blank" rel="noopener"><?php echo noghre_icon( 'map-pin' ); ?> <?php echo esc_html( noghre_get_option( 'map_link_text', 'مشاهده موقعیت روی نقشه (نشان/بلد)' ) ); ?></a>
							<?php endif; ?>
						</div>
					</div>

					<?php if ( (int) noghre_get_option( 'discount_box_enabled', 1 ) ) : ?>
						<div class="contact-info-bottom">
							<div class="discount-box">
								<p class="discount-box-title"><?php echo esc_html( noghre_get_option( 'discount_box_title' ) ); ?></p>
								<form id="noghre-discount-form" class="discount-form" novalidate>
									<input type="text" name="name" class="discount-input" placeholder="نام و نام خانوادگی" required>
									<input type="tel" name="mobile" class="discount-input" placeholder="09xxxxxxxxx" inputmode="numeric" pattern="09[0-9]{9}" maxlength="11" dir="ltr" required>
									<?php noghre_captcha_render( 'discount' ); ?>
									<button type="submit" class="btn btn-primary discount-submit"><?php echo esc_html( noghre_get_option( 'discount_box_button_text', 'ثبت‌نام' ) ); ?></button>
									<span class="noghre-honeypot" aria-hidden="true">
										<label for="discount_website">وب‌سایت (این فیلد را خالی بگذارید)</label>
										<input type="text" id="discount_website" name="website" tabindex="-1" autocomplete="off">
									</span>
								</form>
								<p class="discount-form-message" aria-live="polite"></p>
							</div>
						</div>
					<?php endif; ?>
				</div>
				<?php if ( $has_map ) : ?>
					<div class="contact-map-col">
						<div class="contact-map-embed"><?php echo $map_code; // phpcs:ignore -- کد امبد مورد اعتماد مدیر سایت ?></div>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</section>
	<?php
}

/* ===================== نمایش یک بخش صفحه اصلی بر اساس کلید ===================== */
function noghre_render_homepage_section( $section ) {
	$v = wp_parse_args( $section['visibility'] ?? array(), noghre_default_visibility() );
	$hide_classes = noghre_vis_classes( $v['desktop'], $v['tablet'], $v['mobile'] );
	$wrap = $hide_classes !== '';

	if ( $wrap ) echo '<div class="noghre-vis-wrap ' . esc_attr( $hide_classes ) . '">';

	switch ( $section['key'] ) {
		case 'hero':             noghre_render_hero(); break;
		case 'features':         noghre_render_features(); break;
		case 'team':              noghre_render_team(); break;
		case 'page_builder':      noghre_render_page_builder_section( $section ); break;
		case 'instagram_promo':  noghre_render_instagram_promo(); break;
		case 'faq':               noghre_render_faq(); break;
		case 'blog':              noghre_render_blog_widget(); break;
		case 'contact':           noghre_render_contact(); break;
	}

	if ( $wrap ) echo '</div>';
}

/* ===================== پرسنل ===================== */
function noghre_render_team() {
	$members = noghre_get_team_members();
	if ( empty( $members ) ) return;
	?>
	<section class="section section-team" id="team" data-reveal>
		<div class="container">
			<h2 class="section-title">دستان هنرمند نقره</h2>
			<p class="section-sub">تیمی متخصص برای خلق بی‌نقص‌ترین نسخه‌ی شما</p>
		</div>
		<div class="team-scroller" data-drag-scroll>
			<?php foreach ( $members as $m ) :
				$img = ! empty( $m['image_id'] ) ? wp_get_attachment_image_url( $m['image_id'], 'noghre-team' ) : NOGHRE_URI . '/assets/img/placeholder-team.svg';
				?>
				<div class="team-card">
					<div class="team-photo" style="background-image:url('<?php echo esc_url( $img ); ?>');"></div>
					<div class="team-info">
						<h3><?php echo esc_html( $m['name'] ); ?></h3>
						<?php if ( ! empty( $m['services'] ) ) : ?>
							<p><?php echo esc_html( $m['services'] ); ?></p>
						<?php endif; ?>
						<?php if ( ! empty( $m['instagram'] ) || ! empty( $m['telegram'] ) || ! empty( $m['whatsapp'] ) ) : ?>
							<div class="team-social">
								<?php if ( ! empty( $m['instagram'] ) ) : ?>
									<a href="<?php echo esc_url( $m['instagram'] ); ?>" target="_blank" rel="noopener" aria-label="اینستاگرام <?php echo esc_attr( $m['name'] ); ?>"><?php echo noghre_icon( 'instagram' ); ?></a>
								<?php endif; ?>
								<?php if ( ! empty( $m['telegram'] ) ) : ?>
									<a href="<?php echo esc_url( $m['telegram'] ); ?>" target="_blank" rel="noopener" aria-label="تلگرام <?php echo esc_attr( $m['name'] ); ?>"><?php echo noghre_icon( 'telegram' ); ?></a>
								<?php endif; ?>
								<?php if ( ! empty( $m['whatsapp'] ) ) : ?>
									<a href="<?php echo esc_url( $m['whatsapp'] ); ?>" target="_blank" rel="noopener" aria-label="واتس‌اپ <?php echo esc_attr( $m['name'] ); ?>"><?php echo noghre_icon( 'whatsapp' ); ?></a>
								<?php endif; ?>
							</div>
						<?php endif; ?>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</section>
	<?php
}

/* ===================== سوالات متداول (آکاردئون) ===================== */
function noghre_render_faq() {
	$faqs = noghre_get_faqs();
	if ( empty( $faqs ) ) return;
	?>
	<section class="section section-faq" id="faq" data-reveal>
		<div class="container container-narrow">
			<h2 class="section-title">سوالات متداول</h2>
			<div class="accordion">
				<?php foreach ( $faqs as $i => $f ) : ?>
					<div class="accordion-item">
						<button class="accordion-trigger" aria-expanded="false">
							<span><?php echo esc_html( $f['question'] ); ?></span>
							<span class="accordion-icon" aria-hidden="true"></span>
						</button>
						<div class="accordion-panel">
							<p><?php echo nl2br( esc_html( $f['answer'] ) ); ?></p>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
	<?php
}

/* ===================== مجوزها ===================== */
function noghre_render_licenses() {
	$licenses = noghre_get_licenses();
	if ( empty( $licenses ) ) return;
	?>
	<section class="section section-licenses" id="licenses" data-reveal>
		<div class="container">
			<h2 class="section-title">مجوزهای رسمی سالن</h2>
			<div class="licenses-grid">
				<?php foreach ( $licenses as $l ) :
					$type = $l['type'] ?? 'image';

					if ( $type === 'code' ) :
						if ( empty( $l['code'] ) ) continue;
						?>
						<div class="license-item license-item-code"><?php echo $l['code']; // phpcs:ignore -- کد امبد مورد اعتماد مدیر سایت ?></div>
						<?php
					else :
						$img = ! empty( $l['image_id'] ) ? wp_get_attachment_image_url( $l['image_id'], 'noghre-license' ) : '';
						if ( ! $img ) continue;
						$alt = ! empty( $l['caption'] ) ? $l['caption'] : ( $l['admin_label'] ?? '' );
						$figure = sprintf(
							'<figure class="license-item"><img src="%1$s" alt="%2$s" loading="lazy">%3$s</figure>',
							esc_url( $img ),
							esc_attr( $alt ),
							! empty( $l['caption'] ) ? '<figcaption>' . esc_html( $l['caption'] ) . '</figcaption>' : ''
						);
						if ( ! empty( $l['link_url'] ) ) {
							printf(
								'<a class="license-item-link" href="%1$s" target="%2$s" rel="noopener">%3$s</a>',
								esc_url( $l['link_url'] ),
								esc_attr( $l['link_target'] ?? '_blank' ),
								$figure
							);
						} else {
							echo $figure;
						}
					endif;
				endforeach; ?>
			</div>
		</div>
	</section>
	<?php
}

/* ===================== ستون آخرین مطالب وبلاگ (اسکرول افقی مثل پرسنل) ===================== */
function noghre_render_blog_widget() {
	if ( ! post_type_exists( 'post' ) ) return;

	$count = (int) noghre_get_option( 'blog_posts_count', 3 );
	$q = new WP_Query( array(
		'post_type'      => 'post',
		'post_status'    => 'publish',
		'posts_per_page' => max( $count, 6 ),
		'ignore_sticky_posts' => true,
	) );
	if ( ! $q->have_posts() ) return;
	?>
	<section class="section section-blog" id="blog" data-reveal>
		<div class="container">
			<h2 class="section-title"><?php echo esc_html( noghre_get_option( 'blog_section_title', 'آخرین مطالب وبلاگ نقره' ) ); ?></h2>
		</div>
		<div class="blog-scroller" data-drag-scroll>
			<?php while ( $q->have_posts() ) : $q->the_post(); ?>
				<a href="<?php the_permalink(); ?>" class="blog-card">
					<div class="blog-card-photo">
						<?php if ( has_post_thumbnail() ) : the_post_thumbnail( 'noghre-team' ); else : ?>
							<div class="blog-card-photo-placeholder"></div>
						<?php endif; ?>
					</div>
					<div class="blog-card-info">
						<h3><?php the_title(); ?></h3>
					</div>
				</a>
			<?php endwhile; wp_reset_postdata(); ?>
		</div>
	</section>
	<?php
}

/* ===================== چرخش مجوزها در فوتر ===================== */
function noghre_render_footer_license_rotator() {
	$licenses = array_values( array_filter( noghre_get_licenses(), function( $l ) {
		$type = $l['type'] ?? 'image';
		if ( $type === 'code' ) return ! empty( $l['code'] );
		return ! empty( $l['image_id'] );
	} ) );
	if ( empty( $licenses ) ) return;

	$interval = max( 1, (int) noghre_get_option( 'footer_licenses_interval', 3 ) ) * 1000;
	?>
	<div class="footer-license-rotator" data-interval="<?php echo esc_attr( $interval ); ?>">
		<?php foreach ( $licenses as $i => $l ) :
			$type = $l['type'] ?? 'image';
			?>
			<div class="footer-license-slide<?php echo $i === 0 ? ' is-active' : ''; ?>">
				<?php if ( $type === 'code' ) : ?>
					<div class="footer-license-embed"><?php echo $l['code']; // phpcs:ignore -- کد امبد مورد اعتماد مدیر سایت ?></div>
				<?php else :
					$img = wp_get_attachment_image_url( $l['image_id'], 'noghre-license' );
					?>
					<img src="<?php echo esc_url( $img ); ?>" alt="<?php echo esc_attr( $l['caption'] ?? $l['admin_label'] ?? '' ); ?>" loading="lazy">
					<?php if ( ! empty( $l['caption'] ) ) : ?><span><?php echo esc_html( $l['caption'] ); ?></span><?php endif; ?>
				<?php endif; ?>
			</div>
		<?php endforeach; ?>
	</div>
	<?php
}

/* ===================== تبلیغات بین متن مطلب و دیدگاه‌ها ===================== */
function noghre_render_post_ads() {
	$enabled = (int) noghre_get_option( 'ad_enabled', 0 );
	if ( ! $enabled ) return;

	$col1_img = noghre_get_option( 'ad_col1_image_id' ) ? wp_get_attachment_image_url( noghre_get_option( 'ad_col1_image_id' ), 'noghre-ad-banner' ) : '';
	$col2_img = noghre_get_option( 'ad_col2_image_id' ) ? wp_get_attachment_image_url( noghre_get_option( 'ad_col2_image_id' ), 'noghre-ad-banner' ) : '';
	if ( ! $col1_img && ! $col2_img ) return;

	$classes = noghre_vis_classes(
		noghre_get_option( 'ad_v_desktop', 1 ),
		noghre_get_option( 'ad_v_tablet', 1 ),
		noghre_get_option( 'ad_v_mobile', 1 )
	);
	$col1_link = noghre_get_option( 'ad_col1_link' );
	$col2_link = noghre_get_option( 'ad_col2_link' );
	?>
	<div class="post-ads <?php echo esc_attr( $classes ); ?>">
		<?php if ( $col1_img ) : ?>
			<div class="post-ad-col">
				<?php if ( $col1_link ) : ?><a href="<?php echo esc_url( $col1_link ); ?>" target="_blank" rel="noopener nofollow sponsored"><?php endif; ?>
				<img src="<?php echo esc_url( $col1_img ); ?>" alt="تبلیغات" loading="lazy">
				<?php if ( $col1_link ) : ?></a><?php endif; ?>
			</div>
		<?php endif; ?>
		<?php if ( $col2_img ) : ?>
			<div class="post-ad-col">
				<?php if ( $col2_link ) : ?><a href="<?php echo esc_url( $col2_link ); ?>" target="_blank" rel="noopener nofollow sponsored"><?php endif; ?>
				<img src="<?php echo esc_url( $col2_img ); ?>" alt="تبلیغات" loading="lazy">
				<?php if ( $col2_link ) : ?></a><?php endif; ?>
			</div>
		<?php endif; ?>
	</div>
	<?php
}

/* ===================== نقشه راه (بردکرامب) خطی برای مطالب ===================== */
function noghre_render_breadcrumb() {
	$items = array();
	$items[] = array( 'text' => 'خانه', 'url' => home_url( '/' ) );

	$blog_url = get_post_type_archive_link( 'post' );
	if ( ! $blog_url ) {
		$page_for_posts = (int) get_option( 'page_for_posts' );
		$blog_url = $page_for_posts ? get_permalink( $page_for_posts ) : home_url( '/' );
	}
	$items[] = array( 'text' => 'وبلاگ', 'url' => $blog_url );

	$cats = get_the_category();
	if ( ! empty( $cats ) ) {
		$cat = $cats[0];
		$items[] = array( 'text' => $cat->name, 'url' => get_category_link( $cat->term_id ) );
	}
	?>
	<nav class="noghre-breadcrumb" aria-label="مسیر صفحه">
		<?php foreach ( $items as $i => $item ) : ?>
			<?php if ( $i > 0 ) : ?><span class="crumb-sep">›</span><?php endif; ?>
			<a href="<?php echo esc_url( $item['url'] ); ?>"><?php echo esc_html( $item['text'] ); ?></a>
		<?php endforeach; ?>
	</nav>
	<?php
}

/* ===================== آیکون‌های SVG داخلی (بدون وابستگی خارجی) ===================== */
function noghre_icon( $name ) {
	$icons = array(
		'instagram' => '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.2" cy="6.8" r="1"/></svg>',
		'telegram'  => '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M21 4 3 11.5l6 2m12-9.5-3.5 16-8.5-6.5m12-9.5-11 8"/></svg>',
		'whatsapp'  => '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 20l1.4-4.2A8 8 0 1 1 9 18.6L4 20z"/><path d="M9 10.5c0 3 2.5 5.5 5.5 5.5.6 0 1-.5.8-1l-.6-1.4c-.1-.4-.6-.6-1-.4l-.7.3c-.9-.5-1.6-1.2-2.1-2.1l.3-.7c.2-.4 0-.9-.4-1L9.9 9.2c-.5-.2-1 .2-1 .8z"/></svg>',
		'phone'     => '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 5c0 8 7 15 15 15l3-4-6-3-2 2c-2-1-4-3-5-5l2-2-3-6-4 1z"/></svg>',
		'mobile'    => '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="7" y="2" width="10" height="20" rx="2.5"/><path d="M11 18h2"/></svg>',
		'map-pin'   => '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 21s7-6.5 7-12a7 7 0 1 0-14 0c0 5.5 7 12 7 12z"/><circle cx="12" cy="9" r="2.3"/></svg>',
		'chevron'   => '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg>',
		'menu'      => '<svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 6h18M3 12h18M3 18h18"/></svg>',
		'close'     => '<svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 6l12 12M18 6 6 18"/></svg>',
	);
	return $icons[ $name ] ?? '';
}

/* ===================== صفحه‌بندی ساده ===================== */
function noghre_pagination() {
	the_posts_pagination( array(
		'mid_size'  => 1,
		'prev_text' => 'قبلی',
		'next_text' => 'بعدی',
	) );
}
