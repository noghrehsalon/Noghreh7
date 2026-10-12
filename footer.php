<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
	</div><!-- #content -->

	<footer id="colophon" class="site-footer">
		<div class="container footer-grid">

			<div class="footer-col footer-about">
				<?php if ( has_custom_logo() ) : the_custom_logo(); else : ?>
					<h3 class="footer-logo"><?php bloginfo( 'name' ); ?></h3>
				<?php endif; ?>
				<p><?php echo esc_html( noghre_get_option( 'tagline_2', 'زیبایی، یک سفر است؛ مقصد آن «سالن نقره» در هروی.' ) ); ?></p>
				<div class="footer-social">
					<?php $insta = noghre_get_option( 'instagram_url' ); ?>
					<?php $tg    = noghre_get_option( 'telegram_url' ); ?>
					<?php $wa    = noghre_get_option( 'whatsapp_url' ); ?>
					<?php if ( $insta ) : ?>
						<a href="<?php echo esc_url( $insta ); ?>" target="_blank" rel="noopener" aria-label="اینستاگرام"><?php echo noghre_icon( 'instagram' ); ?></a>
					<?php endif; ?>
					<?php if ( $tg ) : ?>
						<a href="<?php echo esc_url( $tg ); ?>" target="_blank" rel="noopener" aria-label="تلگرام"><?php echo noghre_icon( 'telegram' ); ?></a>
					<?php endif; ?>
					<?php if ( $wa ) : ?>
						<a href="<?php echo esc_url( $wa ); ?>" target="_blank" rel="noopener" aria-label="واتس‌اپ"><?php echo noghre_icon( 'whatsapp' ); ?></a>
					<?php endif; ?>
				</div>

				<?php
				$footer_menu_id = (int) noghre_get_option( 'footer_menu_id', 0 );
				if ( $footer_menu_id || has_nav_menu( 'footer' ) ) :
					?>
					<hr class="footer-divider">
					<?php
					if ( $footer_menu_id ) {
						wp_nav_menu( array(
							'menu'        => $footer_menu_id,
							'container'   => false,
							'menu_class'  => 'footer-inline-menu',
							'fallback_cb' => false,
						) );
					} elseif ( has_nav_menu( 'footer' ) ) {
						wp_nav_menu( array(
							'theme_location' => 'footer',
							'container'      => false,
							'menu_class'     => 'footer-inline-menu',
						) );
					}
					?>
				<?php endif; ?>
			</div>

			<div class="footer-col footer-col-licenses">
				<h4>مجوزهای رسمی</h4>
				<?php noghre_render_footer_license_rotator(); ?>
			</div>

			<div class="footer-col">
				<h4>ارتباط با ما</h4>
				<ul class="footer-contact">
					<?php $address = noghre_get_option( 'address' ); ?>
					<?php $phone   = noghre_get_option( 'phone' ); ?>
					<?php $mobile  = noghre_get_option( 'mobile_phone' ); ?>
					<?php $map     = noghre_get_option( 'map_url' ); ?>
					<?php if ( $address ) : ?>
						<li><?php echo noghre_icon( 'map-pin' ); ?><span><?php echo esc_html( $address ); ?></span></li>
					<?php endif; ?>
					<?php if ( $mobile ) : ?>
						<li><?php echo noghre_icon( 'mobile' ); ?><a href="tel:<?php echo esc_attr( preg_replace( '/\s+/', '', $mobile ) ); ?>"><?php echo esc_html( $mobile ); ?></a></li>
					<?php endif; ?>
					<?php if ( $phone ) : ?>
						<li><?php echo noghre_icon( 'phone' ); ?><a href="tel:<?php echo esc_attr( preg_replace( '/\s+/', '', $phone ) ); ?>"><?php echo esc_html( $phone ); ?></a></li>
					<?php endif; ?>
					<?php if ( $map ) : ?>
						<li class="footer-map-link"><a href="<?php echo esc_url( $map ); ?>" target="_blank" rel="noopener"><?php echo esc_html( noghre_get_option( 'map_link_text', 'مشاهده موقعیت روی نقشه (نشان/بلد)' ) ); ?></a></li>
					<?php endif; ?>
				</ul>
			</div>

			<div class="footer-col">
				<h4>ساعات کاری</h4>
				<ul class="footer-hours">
					<?php
					$hours = noghre_get_option( 'working_hours' );
					foreach ( array_filter( array_map( 'trim', explode( "\n", $hours ) ) ) as $line ) :
						?>
						<li><?php echo esc_html( $line ); ?></li>
					<?php endforeach; ?>
				</ul>
			</div>

		</div>

		<div class="footer-bottom">
			<div class="container footer-bottom-inner">
				<?php if ( (int) noghre_get_option( 'footer_rights_enabled', 1 ) ) : ?>
					<p class="footer-rights"><?php echo esc_html( noghre_get_option( 'footer_rights_text' ) ); ?></p>
				<?php endif; ?>
				<p class="footer-credit">طراحی و توسعه: <a href="https://ariatin.ir/?noghresalon.ir" target="_blank" rel="noopener">آریاتین</a></p>
			</div>
		</div>
	</footer>

	<?php if ( (int) noghre_get_option( 'scroll_top_enabled', 1 ) ) : ?>
		<button id="noghre-scroll-top" class="scroll-top-btn" aria-label="بازگشت به بالای صفحه">
			<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 19V5M5 12l7-7 7 7"/></svg>
		</button>
	<?php endif; ?>

</div><!-- #page -->

<?php wp_footer(); ?>
</body>
</html>
