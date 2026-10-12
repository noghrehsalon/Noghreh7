<?php if ( ! defined( 'ABSPATH' ) ) exit; ?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5">
<link rel="profile" href="https://gmpg.org/xfn/11">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<div id="page" class="site">

	<a class="skip-link screen-reader-text" href="#content">رفتن به محتوای اصلی</a>

	<?php
	$topbar_enabled = (int) noghre_get_option( 'topbar_enabled', 0 );
	if ( $topbar_enabled ) :
		$topbar_classes = noghre_vis_classes(
			noghre_get_option( 'topbar_v_desktop', 1 ),
			noghre_get_option( 'topbar_v_tablet', 1 ),
			noghre_get_option( 'topbar_v_mobile', 1 )
		);
		$address       = noghre_get_option( 'address' );
		$mobile_phone  = noghre_get_option( 'mobile_phone' );
		$landline      = noghre_get_option( 'phone' );
		?>
		<div id="noghre-topbar" class="noghre-topbar <?php echo esc_attr( $topbar_classes ); ?>">
			<div class="container topbar-inner">
				<?php if ( $address ) : ?>
					<div class="topbar-address"><?php echo noghre_icon( 'map-pin' ); ?><span><?php echo esc_html( $address ); ?></span></div>
				<?php endif; ?>
				<div class="topbar-phones">
					<?php if ( $mobile_phone ) : ?>
						<a href="tel:<?php echo esc_attr( preg_replace( '/\s+/', '', $mobile_phone ) ); ?>" class="topbar-phone">
							<?php echo noghre_icon( 'mobile' ); ?><span><?php echo esc_html( $mobile_phone ); ?></span>
						</a>
					<?php endif; ?>
					<?php if ( $landline ) : ?>
						<a href="tel:<?php echo esc_attr( preg_replace( '/\s+/', '', $landline ) ); ?>" class="topbar-phone">
							<?php echo noghre_icon( 'phone' ); ?><span><?php echo esc_html( $landline ); ?></span>
						</a>
					<?php endif; ?>
				</div>
			</div>
		</div>
	<?php endif; ?>

	<header id="masthead" class="site-header<?php echo (int) noghre_get_option( 'sticky_header_enabled', 1 ) ? ' is-sticky' : ' not-sticky'; ?>">
		<div class="container header-inner">

			<div class="site-branding">
				<?php
				$logo_id = noghre_get_option( 'site_logo_id' );
				if ( $logo_id ) :
					?>
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="site-logo-link">
						<?php echo wp_get_attachment_image( $logo_id, 'medium', false, array( 'class' => 'site-logo-img' ) ); ?>
					</a>
					<?php
				elseif ( has_custom_logo() ) :
					the_custom_logo();
				else :
					?>
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="site-logo-link">
						<span class="site-logo-mark" aria-hidden="true">ن</span>
						<span class="site-title-text"><?php bloginfo( 'name' ); ?></span>
					</a>
					<?php
				endif;
				?>
			</div>

			<nav id="primary-menu" class="primary-navigation" aria-label="منوی اصلی">
				<?php
				$header_menu_id = (int) noghre_get_option( 'header_menu_id', 0 );
				if ( $header_menu_id ) {
					wp_nav_menu( array(
						'menu'        => $header_menu_id,
						'container'   => false,
						'menu_class'  => 'primary-menu',
						'fallback_cb' => false,
					) );
				} elseif ( has_nav_menu( 'primary' ) ) {
					wp_nav_menu( array(
						'theme_location' => 'primary',
						'container'      => false,
						'menu_class'     => 'primary-menu',
					) );
				} else {
					noghre_fallback_menu();
				}
				?>
			</nav>

			<div class="header-actions">
				<?php
				$hb_enabled = (int) noghre_get_option( 'header_btn_enabled', 0 );
				if ( $hb_enabled ) :
					$hb_classes = noghre_vis_classes(
						noghre_get_option( 'header_btn_v_desktop', 1 ),
						noghre_get_option( 'header_btn_v_tablet', 1 ),
						noghre_get_option( 'header_btn_v_mobile', 1 )
					);
					$hb_text   = noghre_get_option( 'header_btn_text', 'تماس با ما' );
					$hb_url    = noghre_get_option( 'header_btn_url' );
					$hb_target = noghre_get_option( 'header_btn_target', '_self' );
					if ( $hb_url ) :
						?>
						<a class="btn btn-primary header-btn <?php echo esc_attr( $hb_classes ); ?>" href="<?php echo esc_url( $hb_url ); ?>" target="<?php echo esc_attr( $hb_target ); ?>" <?php echo ( $hb_target === '_blank' ) ? 'rel="noopener"' : ''; ?>><?php echo esc_html( $hb_text ); ?></a>
						<?php
					endif;
				endif;
				?>
				<button class="menu-toggle" aria-controls="primary-menu" aria-expanded="false">
					<?php echo noghre_icon( 'menu' ); ?>
				</button>
			</div>

		</div>
	</header>
	<div class="site-header-spacer" aria-hidden="true"></div>

	<div id="content" class="site-content">
