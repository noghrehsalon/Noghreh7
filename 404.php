<?php
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();
?>
<section class="section section-404">
	<div class="container container-narrow" style="text-align:center;">
		<h1 class="page-title">404</h1>
		<p>صفحه مورد نظر یافت نشد.</p>
		<a class="btn btn-primary" href="<?php echo esc_url( home_url( '/' ) ); ?>">بازگشت به صفحه اصلی</a>
	</div>
</section>
<?php get_footer(); ?>
