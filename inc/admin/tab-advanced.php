<?php
/**
 * تب پیشرفته
 *
 * @package Noghre
 */

if ( ! defined( 'ABSPATH' ) ) exit;

function noghre_render_advanced_page() {
	$sub = isset( $_GET['sub'] ) ? sanitize_key( $_GET['sub'] ) : 'backup';

	$subtabs = array(
		'backup'      => '<span class="dashicons dashicons-database-export"></span> پشتیبان‌گیری',
		'errors'      => '<span class="dashicons dashicons-warning"></span> خطایابی',
		'access-key'  => '<span class="dashicons dashicons-lock"></span> شناسه مدیریت',
		'tokens'      => '<span class="dashicons dashicons-tickets-alt"></span> توکن‌های دسترسی',
		'clear-cache' => '<span class="dashicons dashicons-update"></span> پاک‌سازی کش',
		'shortcodes'  => '<span class="dashicons dashicons-editor-code"></span> راهنمای شورت‌کدها',
	);

	$sub_url = function( $key ) {
		return add_query_arg( array(
			'page' => 'noghre-panel',
			'tab'  => 'advanced',
			'sub'  => $key,
		), admin_url( 'admin.php' ) );
	};
	?>
	<div class="noghre-subtabs">
		<?php foreach ( $subtabs as $key => $title ) : ?>
			<a href="<?php echo esc_url( $sub_url( $key ) ); ?>"
			   class="noghre-subtab <?php echo $sub === $key ? 'is-active' : ''; ?>">
				<?php echo $title; // phpcs:ignore -- شامل HTML امن ?>
			</a>
		<?php endforeach; ?>
	</div>

	<div class="noghre-subtab-content">
		<?php
		switch ( $sub ) {
			case 'shortcodes':
				noghre_render_shortcodes_subtab();
				break;

			case 'errors':
				noghre_render_errors_subtab();
				break;

			case 'access-key':
				noghre_render_access_key_subtab();
				break;

			case 'tokens':
				noghre_render_tokens_subtab();
				break;

			case 'clear-cache':
				if ( function_exists( 'noghre_render_clear_cache_subtab' ) ) {
					noghre_render_clear_cache_subtab();
				} else {
					echo '<p>فایل پاک‌سازی کش یافت نشد.</p>';
				}
				break;

			case 'backup':
			default:
				noghre_render_backup_subtab();
				break;
		}
		?>
	</div>
	<?php
}

/* ==========================================================================
 * زیرتب «راهنمای شورت‌کدها»
 * ========================================================================== */
function noghre_render_shortcodes_subtab() {
	$shortcodes = array(
		array(
			'code'        => 'ariatin-noghreh-nobat',
			'title'       => 'فرم رزرو نوبت',
			'usage'       => 'در برگه «رزرو وقت» قرار دهید.',
			'description' => 'فرم رزرو نوبت را نمایش می‌دهد که شامل نام، موبایل، شعبه، خدمات، روز و ساعت مراجعه، کد تخفیف و کپچا است. پس از ثبت، کد پیگیری به کاربر داده می‌شود.',
			'parameters'  => '',
			'example'     => '[ariatin-noghreh-nobat]',
		),
		array(
			'code'        => 'ariatin-noghreh-check',
			'title'       => 'پیگیری / استعلام رزرو',
			'usage'       => 'در برگه «پیگیری رزرو» قرار دهید.',
			'description' => 'به کاربر امکان می‌دهد با وارد کردن کد پیگیری، وضعیت رزرو خود را ببیند. برای کاربران عادی، QR کد رزرو نمایش داده می‌شود. برای مدیر (با توکن معتبر)، دکمه‌های تایید، ویرایش، باطل‌کردن و ارسال پیامک نمایش داده می‌شود.',
			'parameters'  => 'code=کدپیگیری | mob=1 (نمایش در قاب اپ موبایل)',
			'example'     => '[ariatin-noghreh-check]',
		),
		array(
			'code'        => 'ariatin-noghreh-list',
			'title'       => 'لیست رزروها',
			'usage'       => 'در یک برگه جداگانه قرار دهید و آدرس آن را به عنوان «برگه پیگیری» در تنظیمات رزرو انتخاب کنید.',
			'description' => 'لیست کامل رزروها را با قابلیت جستجو، فیلتر وضعیت، مرتب‌سازی و صفحه‌بندی نمایش می‌دهد. فقط برای مدیر با توکن معتبر قابل مشاهده است.',
			'parameters'  => 'status_filter | orderby | order | paged',
			'example'     => '[ariatin-noghreh-list]',
		),
		array(
			'code'        => 'ariatin-noghreh-mobile',
			'title'       => 'اپلیکیشن موبایل مدیریت',
			'usage'       => 'در یک برگه با آدرس مثلاً «/mobile/» قرار دهید.',
			'description' => 'یک رابط کاربری کاملاً موبایلی برای مدیریت سالن. شامل هدر، باکس ثبت شماره مشتری، لیست رزروها، و منوی پایین قابل مدیریت. صفحه به‌طور کامل از ایندکس گوگل محافظت می‌شود. در اولین ورود با شناسه مدیریت، یک توکن ۲۴ ساعته ساخته می‌شود و از این به بعد بدون نیاز به وارد کردن مجدد شناسه، اپ باز می‌شود.',
			'parameters'  => 'section=mobile|list|signup|scanner',
			'example'     => '[ariatin-noghreh-mobile] — آدرس: /mobile/',
		),
		array(
			'code'        => 'ariatin-noghreh-signup',
			'title'       => 'ثبت شماره مشتری',
			'usage'       => 'می‌توانید در هر برگه‌ای قرار دهید.',
			'description' => 'باکس ساده‌ای برای ثبت نام و شماره موبایل مشتری در دفترچه باشگاه مشتریان. پس از ارسال، در تمام دفترچه‌های افرا اس ام اس جستجو می‌شود و بر اساس نتیجه، شماره منتقل یا اضافه می‌شود.',
			'parameters'  => 'mob=1 (اختیاری برای نمایش در قاب اپ)',
			'example'     => '[ariatin-noghreh-signup]',
		),
		array(
			'code'        => 'ariatin-noghreh-scanner',
			'title'       => 'اسکنر QR کد رزرو',
			'usage'       => 'می‌توانید در هر برگه‌ای قرار دهید. یا از طریق منوی اپ موبایل (section=scanner) به آن دسترسی داشته باشید.',
			'description' => 'دوربین گوشی را باز می‌کند و QR کد رزرو را اسکن می‌کند. پس از شناسایی، به صفحه استعلام رزرو منتقل می‌شود.',
			'parameters'  => 'mob=1 (اختیاری)',
			'example'     => '[ariatin-noghreh-scanner]',
		),
	);
	?>
	<p style="margin:0 0 20px;color:#646970;line-height:1.9;">
		در این بخش، تمام شورت‌کدهای قالب نقره با توضیحات کامل لیست شده‌اند.
	</p>

	<table class="noghre-shortcodes-table">
		<thead>
			<tr>
				<th style="width:220px;">کد شورت‌کد</th>
				<th>توضیحات</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ( $shortcodes as $sc ) : ?>
				<tr>
					<td>
						<code style="display:block;margin-bottom:8px;">[<?php echo esc_html( $sc['code'] ); ?>]</code>
						<strong style="display:block;margin-bottom:4px;"><?php echo esc_html( $sc['title'] ); ?></strong>
					</td>
					<td>
						<p style="margin:0 0 8px;"><strong>وظیفه:</strong> <?php echo esc_html( $sc['description'] ); ?></p>
						<?php if ( ! empty( $sc['usage'] ) ) : ?>
							<p style="margin:0 0 8px;"><strong>محل استفاده:</strong> <?php echo esc_html( $sc['usage'] ); ?></p>
						<?php endif; ?>
						<?php if ( ! empty( $sc['parameters'] ) ) : ?>
							<p style="margin:0 0 8px;"><strong>پارامترها:</strong> <code style="font-size:11px;background:#f0f0f1;padding:2px 6px;border-radius:4px;"><?php echo esc_html( $sc['parameters'] ); ?></code></p>
						<?php endif; ?>
						<?php if ( ! empty( $sc['example'] ) ) : ?>
							<p style="margin:0;color:#646970;font-size:12px;"><strong>مثال:</strong> <code style="background:#f0f0f1;padding:2px 6px;border-radius:4px;"><?php echo esc_html( $sc['example'] ); ?></code></p>
						<?php endif; ?>
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	<?php
}

function noghre_render_backup_subtab() {
	?>

	<h2 class="noghre-section-heading">
		<span class="dashicons dashicons-database-export"></span>
		دریافت فایل پشتیبان
	</h2>
	<p style="color:#646970;line-height:1.9;">
		یک فایل شامل تمام تنظیمات قالب با نام <code>ariatin-noghreh.json</code> دانلود می‌شود.
	</p>
	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<?php wp_nonce_field( 'noghre_export_settings' ); ?>
		<input type="hidden" name="action" value="noghre_export_settings">
		<?php submit_button( 'دانلود فایل تنظیمات', 'secondary' ); ?>
	</form>

	<hr>

	<h2 class="noghre-section-heading">
		<span class="dashicons dashicons-database-import"></span>
		بازگردانی از فایل پشتیبان
	</h2>
	<p style="color:#646970;line-height:1.9;">
		فایل <code>ariatin-noghreh.json</code> که قبلاً دانلود کرده‌اید را انتخاب کنید.
	</p>
	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" enctype="multipart/form-data">
		<?php wp_nonce_field( 'noghre_import_settings' ); ?>
		<input type="hidden" name="action" value="noghre_import_settings">
		<input type="file" name="noghre_import_file" accept="application/json,.json" required>
		<?php submit_button( 'بازگردانی تنظیمات', 'secondary' ); ?>
	</form>

	<hr>

	<h2 class="noghre-section-heading">
		<span class="dashicons dashicons-warning" style="color:#d63638;"></span>
		ریست کامل تنظیمات
	</h2>
	<p style="color:#646970;line-height:1.9;">
		با این کار، تمام تنظیمات قالب حذف شده و به حالت اولیه پیش‌فرض بازمی‌گردد. این کار غیرقابل بازگشت است.
	</p>
	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<?php wp_nonce_field( 'noghre_reset_settings' ); ?>
		<input type="hidden" name="action" value="noghre_reset_settings">
		<button type="submit" class="button button-secondary" style="color:#d63638;border-color:#d63638;" onclick="return confirm('آیا مطمئن هستید؟ تمام تنظیمات قالب حذف و به حالت پیش‌فرض بازمی‌گردد.');">ریست کامل تنظیمات قالب</button>
	</form>
	<?php
}

function noghre_render_errors_subtab() {
	?>
	<p style="color:#646970;line-height:1.9;">
		در این بخش، خطاهایی که هنگام ارسال پیامک، اتصال به درگاه پرداخت یا سایر بخش‌های قالب رخ می‌دهند، همراه با تاریخ و ساعت ثبت می‌شوند.
	</p>

	<?php
	$log = get_option( 'noghre_error_log', array() );
	$log = array_reverse( (array) $log );
	?>
	<?php if ( ! empty( $log ) ) : ?>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin-bottom:10px;">
			<?php wp_nonce_field( 'noghre_clear_error_log' ); ?>
			<input type="hidden" name="action" value="noghre_clear_error_log">
			<button type="submit" class="button" style="color:#d63638;border-color:#d63638;" onclick="return confirm('آیا مطمئن هستید؟');">حذف کل گزارش خطاها</button>
		</form>
	<?php endif; ?>

	<table class="widefat striped">
		<thead><tr><th style="width:160px;">تاریخ و ساعت</th><th style="width:180px;">بخش</th><th>پیام خطا</th></tr></thead>
		<tbody>
			<?php if ( empty( $log ) ) : ?>
				<tr><td colspan="3">تاکنون خطایی ثبت نشده است.</td></tr>
			<?php else : ?>
				<?php foreach ( $log as $entry ) : ?>
					<tr>
						<td><?php echo esc_html( noghre_jalali_date( $entry['time'], 'j F Y - H:i' ) ); ?></td>
						<td><?php echo esc_html( $entry['context'] ); ?></td>
						<td><?php echo esc_html( $entry['message'] ); ?></td>
					</tr>
				<?php endforeach; ?>
			<?php endif; ?>
		</tbody>
	</table>
	<?php
}

function noghre_render_access_key_subtab() {
	?>
	<p style="color:#646970;line-height:1.9;">
		این کد یک شناسه ۸ رقمی/حرفی است که امکان شناسایی مدیر را — بدون نیاز به ورود به وردپرس — فراهم می‌کند. با ورود این شناسه در اپ موبایل، یک توکن ۲۴ ساعته ساخته می‌شود که به‌طور خودکار تمدید می‌شود.
	</p>

	<table class="form-table">
		<tr>
			<th>کد فعلی</th>
			<td>
				<input type="text" value="<?php echo esc_attr( noghre_get_admin_access_key() ); ?>" class="regular-text" dir="ltr" readonly style="background:#f0f0f1;">
			</td>
		</tr>
	</table>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<?php wp_nonce_field( 'noghre_generate_admin_key' ); ?>
		<input type="hidden" name="action" value="noghre_generate_admin_key">
		<?php if ( noghre_get_admin_access_key() ) : ?>
			<button type="submit" class="button" onclick="return confirm('با تغییر شناسه مدیریتی، تمام توکن‌های فعال باطل می‌شوند و کاربران باید شناسه جدید را وارد کنند. آیا مطمئن هستید؟');">تغییر شناسه مدیریت</button>
		<?php else : ?>
			<button type="submit" class="button button-primary">ایجاد شناسه مدیریت</button>
		<?php endif; ?>
	</form>
	<?php
}

/* ==========================================================================
 * زیرتب «توکن‌های دسترسی»
 * ========================================================================== */
function noghre_render_tokens_subtab() {
	// پردازش درخواست باطل کردن
	if (
		isset( $_GET['noghre_action'] ) &&
		$_GET['noghre_action'] === 'revoke_all_tokens' &&
		isset( $_GET['_wpnonce'] ) &&
		wp_verify_nonce( $_GET['_wpnonce'], 'noghre_revoke_all_tokens' )
	) {
		if ( function_exists( 'noghre_revoke_all_access_tokens' ) ) {
			noghre_revoke_all_access_tokens();
		}
		echo '<div class="notice notice-success is-dismissible"><p>تمام توکن‌های دسترسی باطل شدند.</p></div>';
	}

	$tokens = get_option( 'noghre_access_tokens', array() );
	if ( ! is_array( $tokens ) ) $tokens = array();

	$now = time();
	$active_count = 0;
	foreach ( $tokens as $data ) {
		if ( ! empty( $data['expires_at'] ) && $data['expires_at'] > $now ) {
			$active_count++;
		}
	}
	?>
	<p style="color:#646970;line-height:1.9;">
		توکن‌های دسترسی برای ورود به اپ موبایل استفاده می‌شوند. هر توکن ۲۴ ساعت اعتبار دارد و به‌طور خودکار تمدید می‌شود.
		اگر می‌خواهید همه کاربران از اپ خارج شوند، دکمه «باطل کردن همه» را بزنید.
	</p>

	<p>
		<strong>تعداد کل توکن‌ها:</strong> <?php echo esc_html( noghre_to_persian_digits( count( $tokens ) ) ); ?>
		&nbsp;|&nbsp;
		<strong>توکن‌های فعال:</strong> <?php echo esc_html( noghre_to_persian_digits( $active_count ) ); ?>
	</p>

	<?php if ( ! empty( $tokens ) ) : ?>
		<p style="margin: 16px 0;">
			<a href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'noghre_action', 'revoke_all_tokens' ), 'noghre_revoke_all_tokens' ) ); ?>"
			   class="button button-secondary"
			   style="color:#d63638;border-color:#d63638;"
			   onclick="return confirm('آیا مطمئن هستید؟ تمام توکن‌های فعال باطل می‌شوند و کاربران باید دوباره شناسه مدیریت را وارد کنند.');">
				باطل کردن همه توکن‌ها
			</a>
		</p>

		<table class="widefat striped">
			<thead>
				<tr>
					<th>توکن (کوتاه‌شده)</th>
					<th>تاریخ ساخت</th>
					<th>تاریخ انقضا</th>
					<th>آخرین استفاده</th>
					<th>IP</th>
					<th>وضعیت</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $tokens as $token => $data ) :
					$is_active = ! empty( $data['expires_at'] ) && $data['expires_at'] > $now;
					$short_token = substr( $token, 0, 8 ) . '...' . substr( $token, -8 );
					?>
					<tr>
						<td dir="ltr" style="font-family: monospace; font-size: 12px;"><?php echo esc_html( $short_token ); ?></td>
						<td><?php echo esc_html( noghre_jalali_date( $data['created_at'] ?? 0, 'j F Y - H:i' ) ); ?></td>
						<td><?php echo esc_html( noghre_jalali_date( $data['expires_at'] ?? 0, 'j F Y - H:i' ) ); ?></td>
						<td><?php echo esc_html( noghre_jalali_date( $data['last_used'] ?? 0, 'j F Y - H:i' ) ); ?></td>
						<td dir="ltr" style="font-size: 12px;"><?php echo esc_html( $data['ip'] ?? '—' ); ?></td>
						<td>
							<?php if ( $is_active ) : ?>
								<span style="color: #00a32a; font-weight: 600;">فعال</span>
							<?php else : ?>
								<span style="color: #d63638;">منقضی</span>
							<?php endif; ?>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	<?php else : ?>
		<p style="color:#646970;">هنوز هیچ توکنی ساخته نشده است.</p>
	<?php endif; ?>
	<?php
}