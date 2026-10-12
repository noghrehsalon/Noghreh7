<?php
/**
 * تب ظاهر — روتر بین زیرتب‌های رنگ‌بندی و فونت
 *
 * @package Noghre
 */

if ( ! defined( 'ABSPATH' ) ) exit;

function noghre_render_appearance_page() {
	$sub = isset( $_GET['sub'] ) ? sanitize_key( $_GET['sub'] ) : 'colors';

	$subtabs = array(
		'colors' => '<span class="dashicons dashicons-art"></span> رنگ‌بندی',
		'fonts'  => '<span class="dashicons dashicons-editor-textcolor"></span> فونت',
	);

	$sub_url = function( $key ) {
		return add_query_arg( array(
			'page' => 'noghre-panel',
			'tab'  => 'appearance',
			'sub'  => $key,
		), admin_url( 'admin.php' ) );
	};
	?>
	<div class="noghre-subtabs">
		<?php foreach ( $subtabs as $key => $title ) : ?>
			<a href="<?php echo esc_url( $sub_url( $key ) ); ?>"
			   class="noghre-subtab <?php echo $sub === $key ? 'is-active' : ''; ?>">
				<?php echo $title; // phpcs:ignore ?>
			</a>
		<?php endforeach; ?>
	</div>

	<div class="noghre-subtab-content">
		<?php
		switch ( $sub ) {
			case 'fonts':
				if ( function_exists( 'noghre_render_fonts_subtab' ) ) {
					noghre_render_fonts_subtab();
				} else {
					echo '<p>فایل تنظیمات فونت یافت نشد.</p>';
				}
				break;

			case 'colors':
			default:
				if ( function_exists( 'noghre_render_colors_page' ) ) {
					noghre_render_colors_page();
				} else {
					echo '<p>فایل تنظیمات رنگ یافت نشد.</p>';
				}
				break;
		}
		?>
	</div>
	<?php
}