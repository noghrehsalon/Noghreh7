<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/* ==========================================================================
 * ابزار پاک‌سازی کامل کش و کوکی
 * ========================================================================== */

/**
 * پردازش درخواست پاک‌سازی
 */
function noghre_handle_clear_all_cache() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'دسترسی غیرمجاز' );
	}
	check_admin_referer( 'noghre_clear_all_cache' );

	$results = array();

	/* ۱. پاک‌سازی Object Cache وردپرس */
	wp_cache_flush();
	$results[] = 'Object Cache وردپرس پاک شد.';

	/* ۲. پاک‌سازی Memcached */
	if ( class_exists( 'Memcached' ) ) {
		try {
			$m = new Memcached();
			$m->addServer( '127.0.0.1', 11211 );
			$m->flush();
			$results[] = 'Memcached پاک شد.';
		} catch ( Exception $e ) {
			$results[] = 'خطا در پاک‌سازی Memcached: ' . $e->getMessage();
		}
	}

	/* ۳. پاک‌سازی Redis (اگه فعال باشه) */
	if ( class_exists( 'Redis' ) ) {
		try {
			$redis = new Redis();
			$redis->connect( '127.0.0.1', 6379 );
			$redis->flushAll();
			$results[] = 'Redis پاک شد.';
		} catch ( Exception $e ) {
			$results[] = 'خطا در پاک‌سازی Redis: ' . $e->getMessage();
		}
	}

	/* ۴. پاک‌سازی Transient ها */
	global $wpdb;
	$deleted = $wpdb->query(
		"DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_%' OR option_name LIKE '_site_transient_%'"
	);
	$results[] = 'Transient ها پاک شدند (' . (int) $deleted . ' مورد).';

	/* ۵. پاک‌سازی کش پلاگین‌های معروف */
	if ( function_exists( 'rocket_clean_domain' ) ) {
		rocket_clean_domain();
		$results[] = 'کش WP Rocket پاک شد.';
	}
	if ( class_exists( 'LiteSpeed_Cache_API' ) && method_exists( 'LiteSpeed_Cache_API', 'purge_all' ) ) {
		LiteSpeed_Cache_API::purge_all();
		$results[] = 'کش LiteSpeed پاک شد.';
	}
	if ( function_exists( 'w3tc_flush_all' ) ) {
		w3tc_flush_all();
		$results[] = 'کش W3 Total Cache پاک شد.';
	}
	if ( function_exists( 'wp_cache_clear_cache' ) ) {
		wp_cache_clear_cache();
		$results[] = 'کش WP Super Cache پاک شد.';
	}
	if ( class_exists( 'autoptimizeCache' ) && method_exists( 'autoptimizeCache', 'clearall' ) ) {
		autoptimizeCache::clearall();
		$results[] = 'کش Autoptimize پاک شد.';
	}
	if ( function_exists( 'sg_cachepress_purge_cache' ) ) {
		sg_cachepress_purge_cache();
		$results[] = 'کش SiteGround پاک شد.';
	}
	if ( class_exists( 'CF\WordPress\Hooks' ) ) {
		do_action( 'cloudflare_purge_everything' );
		$results[] = 'کش Cloudflare پاک شد.';
	}

	/* ۶. پاک‌سازی کش قالب (transient های خود قالب) */
	$noghre_transients = array(
		'noghre_shortcode_url_ariatin-noghreh-list',
		'noghre_shortcode_url_ariatin-noghreh-check',
		'noghre_shortcode_url_ariatin-noghreh-nobat',
		'noghre_shortcode_url_ariatin-noghreh-mobile',
	);
	foreach ( $noghre_transients as $t ) {
		delete_transient( $t );
	}
	$results[] = 'کش اختصاصی قالب نقره پاک شد.';

	/* ۷. به‌روزرسانی Option برای ریست کش در فرانت */
	update_option( 'noghre_cache_buster', time(), false );

	/* ۸. ذخیره نتایج در transient برای نمایش بعد از redirect */
	set_transient( 'noghre_clear_cache_results', $results, 60 );

	/* ۹. ریدایرکت با پیام موفق */
	wp_safe_redirect( add_query_arg(
		array(
			'page' => 'noghre-panel',
			'tab'  => 'advanced',
			'sub'  => 'clear-cache',
			'done' => '1',
		),
		admin_url( 'admin.php' )
	) );
	exit;
}
add_action( 'admin_post_noghre_clear_all_cache', 'noghre_handle_clear_all_cache' );

/**
 * رندر زیرتب «پاک‌سازی کش»
 */
function noghre_render_clear_cache_subtab() {
	$done    = isset( $_GET['done'] ) && $_GET['done'] === '1';
	$results = $done ? get_transient( 'noghre_clear_cache_results' ) : false;

	if ( $done && $results ) {
		delete_transient( 'noghre_clear_cache_results' );
	}

	// اطلاعات سیستم
	$memcached_active = class_exists( 'Memcached' );
	$redis_active     = class_exists( 'Redis' );
	$opcache_active   = function_exists( 'opcache_get_status' ) && opcache_get_status( false ) !== false;
	$object_cache     = wp_using_ext_object_cache();
	$wp_cache_const   = defined( 'WP_CACHE' ) && WP_CACHE;

	// پلاگین‌های کش فعال
	$cache_plugins = array();
	if ( function_exists( 'rocket_clean_domain' ) ) $cache_plugins[] = 'WP Rocket';
	if ( class_exists( 'LiteSpeed_Cache_API' ) ) $cache_plugins[] = 'LiteSpeed Cache';
	if ( function_exists( 'w3tc_flush_all' ) ) $cache_plugins[] = 'W3 Total Cache';
	if ( function_exists( 'wp_cache_clear_cache' ) ) $cache_plugins[] = 'WP Super Cache';
	if ( class_exists( 'autoptimizeCache' ) ) $cache_plugins[] = 'Autoptimize';
	if ( function_exists( 'sg_cachepress_purge_cache' ) ) $cache_plugins[] = 'SiteGround Optimizer';
	if ( class_exists( 'CF\WordPress\Hooks' ) ) $cache_plugins[] = 'Cloudflare';
	?>

	<h2 class="noghre-section-heading">
		<span class="dashicons dashicons-info-outline"></span>
		وضعیت فعلی سیستم
	</h2>

	<p class="noghre-clear-cache-desc">
		با این ابزار، همه لایه‌های کش (Object Cache، Memcached، Redis، Transient ها، پلاگین‌های کش، کش قالب) پاک می‌شن.
		بعد از پاک‌سازی، یک نسخه جدید از فایل‌های CSS و JS هم لود می‌شه تا مرورگر کاربران نسخه قدیمی رو نشون نده.
	</p>

	<table class="widefat striped noghre-clear-cache-table" style="margin-top:16px;">
		<tbody>
			<tr>
				<td class="noghre-clear-cache-label"><strong>Object Cache وردپرس</strong></td>
				<td>
					<?php if ( $object_cache ) : ?>
						<span class="noghre-status-badge noghre-status-active">
							<span class="dashicons dashicons-yes-alt"></span> فعال
						</span>
					<?php else : ?>
						<span class="noghre-status-badge noghre-status-inactive">
							<span class="dashicons dashicons-marker"></span> غیرفعال
						</span>
					<?php endif; ?>
				</td>
			</tr>
			<tr>
				<td class="noghre-clear-cache-label"><strong>Memcached</strong></td>
				<td>
					<?php if ( $memcached_active ) : ?>
						<span class="noghre-status-badge noghre-status-active">
							<span class="dashicons dashicons-yes-alt"></span> نصب شده
						</span>
					<?php else : ?>
						<span class="noghre-status-badge noghre-status-inactive">
							<span class="dashicons dashicons-marker"></span> نصب نیست
						</span>
					<?php endif; ?>
				</td>
			</tr>
			<tr>
				<td class="noghre-clear-cache-label"><strong>Redis</strong></td>
				<td>
					<?php if ( $redis_active ) : ?>
						<span class="noghre-status-badge noghre-status-active">
							<span class="dashicons dashicons-yes-alt"></span> نصب شده
						</span>
					<?php else : ?>
						<span class="noghre-status-badge noghre-status-inactive">
							<span class="dashicons dashicons-marker"></span> نصب نیست
						</span>
					<?php endif; ?>
				</td>
			</tr>
			<tr>
				<td class="noghre-clear-cache-label"><strong>OPcache</strong></td>
				<td>
					<?php if ( $opcache_active ) : ?>
						<span class="noghre-status-badge noghre-status-active">
							<span class="dashicons dashicons-yes-alt"></span> فعال
						</span>
					<?php else : ?>
						<span class="noghre-status-badge noghre-status-inactive">
							<span class="dashicons dashicons-marker"></span> غیرفعال
						</span>
					<?php endif; ?>
				</td>
			</tr>
			<tr>
				<td class="noghre-clear-cache-label"><strong>WP_CACHE در wp-config</strong></td>
				<td>
					<?php if ( $wp_cache_const ) : ?>
						<span class="noghre-status-badge noghre-status-active">
							<span class="dashicons dashicons-yes-alt"></span> فعال
						</span>
					<?php else : ?>
						<span class="noghre-status-badge noghre-status-inactive">
							<span class="dashicons dashicons-marker"></span> غیرفعال
						</span>
					<?php endif; ?>
				</td>
			</tr>
			<tr>
				<td class="noghre-clear-cache-label"><strong>پلاگین کش فعال</strong></td>
				<td>
					<?php if ( empty( $cache_plugins ) ) : ?>
						<span class="noghre-status-badge noghre-status-inactive">
							<span class="dashicons dashicons-marker"></span> هیچ پلاگین کش شناخته‌شده‌ای فعال نیست
						</span>
					<?php else : ?>
						<span class="noghre-status-badge noghre-status-active">
							<span class="dashicons dashicons-yes-alt"></span> <?php echo esc_html( implode( '، ', $cache_plugins ) ); ?>
						</span>
					<?php endif; ?>
				</td>
			</tr>
		</tbody>
	</table>

	<h2 class="noghre-section-heading">
		<span class="dashicons dashicons-update"></span>
		عملیات پاک‌سازی
	</h2>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" id="noghre-clear-cache-form">
		<?php wp_nonce_field( 'noghre_clear_all_cache' ); ?>
		<input type="hidden" name="action" value="noghre_clear_all_cache">
		<p>
			<button type="submit" class="button button-primary button-hero" onclick="return confirm('آیا از پاک‌سازی کامل کش مطمئن هستید؟ این عملیات همه کش‌ها را پاک می‌کند.');">
				<span class="dashicons dashicons-update"></span>
				پاک‌سازی کامل کش و کوکی
			</button>
		</p>
	</form>

	<?php if ( $done && $results ) : ?>
		<div class="notice notice-success inline" style="margin-top:16px;">
			<p><strong>پاک‌سازی با موفقیت انجام شد</strong></p>
			<ul style="margin:8px 0 0 20px;padding:0;list-style:disc;">
				<?php foreach ( $results as $r ) : ?>
					<li><?php echo esc_html( $r ); ?></li>
				<?php endforeach; ?>
			</ul>
		</div>
	<?php endif; ?>

	<h2 class="noghre-section-heading">
		<span class="dashicons dashicons-editor-help"></span>
		راهنما
	</h2>
	<p class="noghre-clear-cache-desc">
		اگر بعد از پاک‌سازی، باز هم نسخه قدیمی رو می‌بینی، این مراحل رو به ترتیب انجام بده:
	</p>
	<ol class="noghre-clear-cache-help-list">
		<li>مرورگر رو با <code>Ctrl + Shift + R</code> (یا <code>Cmd + Shift + R</code> در مک) رفرش کن.</li>
		<li>در مرورگر، یه پنجره <strong>Incognito</strong> (ناشناس) باز کن و سایت رو ببین.</li>
		<li>اگه از Cloudflare استفاده می‌کنی، از پنل Cloudflare کش رو purge کن.</li>
		<li>اگه از CDN دیگه‌ای استفاده می‌کنی، از پنلش کش رو پاک کن.</li>
		<li>اگه بازم مشکل حل نشد، از پشتیبانی هاست بخواه کش سطح سرور (Varnish, LiteSpeed Server) رو پاک کنه.</li>
	</ol>
	<?php
}