<?php
/**
 * زیرتب تنظیمات فونت — سیستم فونت اختصاصی قالب
 * تشخیص خودکار خانواده و وزن از نام فایل (مثل وردپرس و Elementor)
 *
 * @package Noghre
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/* ============================================================
 * توابع پایه
 * ============================================================ */

function noghre_get_fonts_dir() {
	return NOGHRE_DIR . '/fonts';
}

function noghre_get_fonts_url() {
	return NOGHRE_URI . '/fonts';
}

function noghre_ensure_fonts_dir() {
	$dir = noghre_get_fonts_dir();
	if ( ! file_exists( $dir ) ) {
		wp_mkdir_p( $dir );
	}
	return $dir;
}

/**
 * لیست وزن‌های پشتیبانی‌شده
 */
function noghre_font_weights() {
	return array(
		100 => 'Thin',
		200 => 'ExtraLight',
		300 => 'Light',
		400 => 'Regular',
		500 => 'Medium',
		600 => 'SemiBold',
		700 => 'Bold',
		800 => 'ExtraBold',
		900 => 'Black',
	);
}

/**
 * نقشه کلمات کلیدی وزن (فارسی + انگلیسی + اعداد)
 */
function noghre_font_weight_keywords() {
	return array(
		'100' => 100, '200' => 200, '300' => 300, '400' => 400, '500' => 500,
		'600' => 600, '700' => 700, '800' => 800, '900' => 900,
		'thin'       => 100,
		'hairline'   => 100,
		'extralight' => 200,
		'ultralight' => 200,
		'light'      => 300,
		'regular'    => 400,
		'normal'     => 400,
		'book'       => 400,
		'roman'      => 400,
		'medium'     => 500,
		'semibold'   => 600,
		'demibold'   => 600,
		'bold'       => 700,
		'extrabold'  => 800,
		'ultrabold'  => 800,
		'heavy'      => 800,
		'black'      => 900,
		'extrablack' => 900,
	);
}

/* ============================================================
 * تشخیص خودکار نام فونت و وزن از نام فایل
 * ============================================================ */

function noghre_parse_font_filename( $filename ) {
	$name = preg_replace( '/\.[a-z0-9]+$/i', '', $filename );
	$name = preg_replace( '/[\-_\s]+/', ' ', $name );
	$words = preg_split( '/\s+/', trim( $name ) );
	$words = array_filter( $words );

	$weight_keywords = noghre_font_weight_keywords();

	$family_words = array();
	$detected_weight = 0;
	$detected_style  = 'normal';

	foreach ( $words as $word ) {
		$lower = strtolower( $word );

		if ( $lower === 'italic' || $lower === 'oblique' ) {
			$detected_style = 'italic';
			continue;
		}

		if ( isset( $weight_keywords[ $lower ] ) ) {
			$detected_weight = $weight_keywords[ $lower ];
			continue;
		}

		$family_words[] = $word;
	}

	if ( $detected_weight === 0 ) {
		$detected_weight = 400;
	}

	$family = ! empty( $family_words ) ? implode( ' ', $family_words ) : 'فونت بدون نام';
	$family = noghre_normalize_font_family_name( $family );

	return array(
		'family' => $family,
		'weight' => $detected_weight,
		'style'  => $detected_style,
	);
}

function noghre_normalize_font_family_name( $name ) {
	$name = trim( $name );
	if ( $name === '' ) return 'فونت بدون نام';

	if ( strtolower( $name ) === $name || strtoupper( $name ) === $name ) {
		$name = ucwords( strtolower( $name ) );
	}

	$name = preg_replace( '/\s+/', ' ', $name );

	return $name;
}

/* ============================================================
 * مدیریت داده‌ها
 * ============================================================ */

function noghre_get_uploaded_fonts() {
	$fonts = get_option( 'noghre_uploaded_fonts', array() );
	if ( ! is_array( $fonts ) ) return array();

	$migrated = false;
	foreach ( $fonts as $id => $font ) {
		if ( ! isset( $font['files'] ) ) {
			$old_file   = $font['file'] ?? '';
			$old_format = $font['format'] ?? 'woff2';
			$new_files  = array();

			if ( $old_file ) {
				$new_files['400_normal'] = array(
					'file'   => $old_file,
					'format' => $old_format,
				);
			}

			$fonts[ $id ] = array(
				'id'       => $font['id'] ?? $id,
				'name'     => $font['name'] ?? 'فونت بدون نام',
				'files'    => $new_files,
				'uploaded' => $font['uploaded'] ?? current_time( 'timestamp' ),
			);
			$migrated = true;
		}
	}

	if ( $migrated ) {
		update_option( 'noghre_uploaded_fonts', $fonts );
	}

	return $fonts;
}

function noghre_save_uploaded_fonts( $fonts ) {
	update_option( 'noghre_uploaded_fonts', $fonts );
}

function noghre_font_family_id( $family_name ) {
	$id = sanitize_title( $family_name );
	if ( empty( $id ) ) {
		$id = 'font_' . wp_rand( 1000, 9999 );
	}
	return $id;
}

/* ============================================================
 * آپلود چند فایل همزمان با تشخیص خودکار
 * ============================================================ */

function noghre_handle_font_upload() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'دسترسی غیرمجاز' );
	}
	check_admin_referer( 'noghre_upload_font' );

	$redirect_base = add_query_arg(
		array(
			'page' => 'noghre-panel',
			'tab'  => 'appearance',
			'sub'  => 'fonts',
		),
		admin_url( 'admin.php' )
	);

	if ( empty( $_FILES['font_files'] ) || empty( $_FILES['font_files']['name'][0] ) ) {
		wp_safe_redirect( add_query_arg( 'noghre_font_msg', 'no_file', $redirect_base ) );
		exit;
	}

	$allowed_ext = array( 'woff2', 'woff', 'ttf', 'otf' );
	$dir         = noghre_ensure_fonts_dir();
	$fonts       = noghre_get_uploaded_fonts();

	$files          = $_FILES['font_files'];
	$uploaded_count = 0;
	$updated_count  = 0;
	$error_count    = 0;
	$errors_detail  = array();

	$file_count = count( $files['name'] );

	for ( $i = 0; $i < $file_count; $i++ ) {
		if ( empty( $files['tmp_name'][ $i ] ) ) continue;
		if ( $files['error'][ $i ] !== UPLOAD_ERR_OK ) {
			$error_count++;
			$errors_detail[] = $files['name'][ $i ] . ': خطای آپلود (' . $files['error'][ $i ] . ')';
			continue;
		}

		$original_name = sanitize_file_name( $files['name'][ $i ] );
		$ext = strtolower( pathinfo( $original_name, PATHINFO_EXTENSION ) );

		if ( ! in_array( $ext, $allowed_ext, true ) ) {
			$error_count++;
			$errors_detail[] = $original_name . ': فرمت پشتیبانی نمی‌شه';
			continue;
		}

		if ( $files['size'][ $i ] > 5 * 1024 * 1024 ) {
			$error_count++;
			$errors_detail[] = $original_name . ': حجم بیش از ۵ مگابایت';
			continue;
		}

		$parsed = noghre_parse_font_filename( $original_name );
		$family_name = $parsed['family'];
		$weight      = $parsed['weight'];
		$style       = $parsed['style'];

		$family_id = noghre_font_family_id( $family_name );

		$safe_file_name = sanitize_file_name( $family_id . '-' . $weight . '-' . $style . '.' . $ext );
		$file_path = trailingslashit( $dir ) . $safe_file_name;

		$counter = 1;
		while ( file_exists( $file_path ) ) {
			$safe_file_name = sanitize_file_name( $family_id . '-' . $weight . '-' . $style . '-' . $counter . '.' . $ext );
			$file_path = trailingslashit( $dir ) . $safe_file_name;
			$counter++;
		}

		if ( ! move_uploaded_file( $files['tmp_name'][ $i ], $file_path ) ) {
			$error_count++;
			$errors_detail[] = $original_name . ': خطا در ذخیره فایل';
			continue;
		}

		if ( ! isset( $fonts[ $family_id ] ) ) {
			$fonts[ $family_id ] = array(
				'id'       => $family_id,
				'name'     => $family_name,
				'files'    => array(),
				'uploaded' => current_time( 'timestamp' ),
			);
		} else {
			$fonts[ $family_id ]['name'] = $family_name;
		}

		$key = $weight . '_' . $style;

		if ( ! empty( $fonts[ $family_id ]['files'][ $key ]['file'] ) ) {
			$old_file = $fonts[ $family_id ]['files'][ $key ]['file'];
			$old_path = trailingslashit( $dir ) . $old_file;
			if ( file_exists( $old_path ) && $old_file !== $safe_file_name ) {
				@unlink( $old_path );
			}
			$updated_count++;
		} else {
			$uploaded_count++;
		}

		$fonts[ $family_id ]['files'][ $key ] = array(
			'file'   => $safe_file_name,
			'format' => $ext,
		);
	}

	noghre_save_uploaded_fonts( $fonts );

	$msg_parts = array();
	if ( $uploaded_count > 0 ) {
		$msg_parts[] = noghre_to_persian_digits( $uploaded_count ) . ' فایل جدید آپلود شد';
	}
	if ( $updated_count > 0 ) {
		$msg_parts[] = noghre_to_persian_digits( $updated_count ) . ' فایل به‌روزرسانی شد';
	}
	if ( $error_count > 0 ) {
		$msg_parts[] = noghre_to_persian_digits( $error_count ) . ' خطا';
	}

	$final_msg = ! empty( $msg_parts ) ? implode( ' — ', $msg_parts ) : 'هیچ فایلی آپلود نشد';

	if ( ! empty( $errors_detail ) ) {
		set_transient( 'noghre_font_upload_errors_' . get_current_user_id(), $errors_detail, 60 );
	}

	wp_safe_redirect( add_query_arg( array(
		'noghre_font_msg'  => $error_count > 0 && $uploaded_count === 0 && $updated_count === 0 ? 'upload_failed' : 'uploaded',
		'noghre_font_info' => urlencode( $final_msg ),
	), $redirect_base ) );
	exit;
}
add_action( 'admin_post_noghre_upload_font', 'noghre_handle_font_upload' );

/* ============================================================
 * حذف یک فایل وزن
 * ============================================================ */

function noghre_handle_font_weight_delete() {
	if ( ! current_user_can( 'manage_options' ) ) wp_die( 'دسترسی غیرمجاز' );
	check_admin_referer( 'noghre_delete_font_weight' );

	$redirect_base = add_query_arg(
		array(
			'page' => 'noghre-panel',
			'tab'  => 'appearance',
			'sub'  => 'fonts',
		),
		admin_url( 'admin.php' )
	);

	$font_id = isset( $_GET['font_id'] ) ? sanitize_key( $_GET['font_id'] ) : '';
	$key     = isset( $_GET['key'] ) ? sanitize_key( $_GET['key'] ) : '';

	if ( empty( $font_id ) || empty( $key ) ) {
		wp_safe_redirect( add_query_arg( 'noghre_font_msg', 'invalid_id', $redirect_base ) );
		exit;
	}

	$fonts = noghre_get_uploaded_fonts();
	if ( ! isset( $fonts[ $font_id ] ) || empty( $fonts[ $font_id ]['files'][ $key ] ) ) {
		wp_safe_redirect( add_query_arg( 'noghre_font_msg', 'not_found', $redirect_base ) );
		exit;
	}

	$dir = noghre_get_fonts_dir();
	$file_name = $fonts[ $font_id ]['files'][ $key ]['file'];
	$file_path = trailingslashit( $dir ) . $file_name;
	if ( file_exists( $file_path ) ) {
		@unlink( $file_path );
	}

	unset( $fonts[ $font_id ]['files'][ $key ] );
	noghre_save_uploaded_fonts( $fonts );

	wp_safe_redirect( add_query_arg( 'noghre_font_msg', 'weight_deleted', $redirect_base ) );
	exit;
}
add_action( 'admin_post_noghre_delete_font_weight', 'noghre_handle_font_weight_delete' );

/* ============================================================
 * حذف کل خانواده فونت
 * ============================================================ */

function noghre_handle_font_family_delete() {
	if ( ! current_user_can( 'manage_options' ) ) wp_die( 'دسترسی غیرمجاز' );
	check_admin_referer( 'noghre_delete_font_family' );

	$redirect_base = add_query_arg(
		array(
			'page' => 'noghre-panel',
			'tab'  => 'appearance',
			'sub'  => 'fonts',
		),
		admin_url( 'admin.php' )
	);

	$font_id = isset( $_GET['font_id'] ) ? sanitize_key( $_GET['font_id'] ) : '';

	if ( empty( $font_id ) ) {
		wp_safe_redirect( add_query_arg( 'noghre_font_msg', 'invalid_id', $redirect_base ) );
		exit;
	}

	$fonts = noghre_get_uploaded_fonts();
	if ( ! isset( $fonts[ $font_id ] ) ) {
		wp_safe_redirect( add_query_arg( 'noghre_font_msg', 'not_found', $redirect_base ) );
		exit;
	}

	$dir = noghre_get_fonts_dir();
	if ( ! empty( $fonts[ $font_id ]['files'] ) && is_array( $fonts[ $font_id ]['files'] ) ) {
		foreach ( $fonts[ $font_id ]['files'] as $file_data ) {
			if ( ! empty( $file_data['file'] ) ) {
				$path = trailingslashit( $dir ) . $file_data['file'];
				if ( file_exists( $path ) ) {
					@unlink( $path );
				}
			}
		}
	}

	unset( $fonts[ $font_id ] );
	noghre_save_uploaded_fonts( $fonts );

	$general = get_option( 'noghre_general_options', array() );
	if ( ! is_array( $general ) ) $general = array();

	if ( isset( $general['site_font_id'] ) && $general['site_font_id'] === $font_id ) {
		$general['site_font_id'] = '';
	}
	if ( isset( $general['admin_font_id'] ) && $general['admin_font_id'] === $font_id ) {
		$general['admin_font_id'] = '';
	}
	update_option( 'noghre_general_options', $general );

	wp_safe_redirect( add_query_arg( 'noghre_font_msg', 'family_deleted', $redirect_base ) );
	exit;
}
add_action( 'admin_post_noghre_delete_font_family', 'noghre_handle_font_family_delete' );

/* ============================================================
 * ذخیره تنظیمات فونت فعال
 * ============================================================ */

function noghre_handle_save_font_settings() {
	if ( ! current_user_can( 'manage_options' ) ) wp_die( 'دسترسی غیرمجاز' );
	check_admin_referer( 'noghre_save_font_settings' );

	$redirect_base = add_query_arg(
		array(
			'page' => 'noghre-panel',
			'tab'  => 'appearance',
			'sub'  => 'fonts',
		),
		admin_url( 'admin.php' )
	);

	$general = get_option( 'noghre_general_options', array() );
	if ( ! is_array( $general ) ) $general = array();

	$general['site_font_id']  = isset( $_POST['site_font_id'] ) ? sanitize_key( wp_unslash( $_POST['site_font_id'] ) ) : '';
	$general['admin_font_id'] = isset( $_POST['admin_font_id'] ) ? sanitize_key( wp_unslash( $_POST['admin_font_id'] ) ) : '';

	update_option( 'noghre_general_options', $general );

	wp_safe_redirect( add_query_arg( 'noghre_font_msg', 'settings_saved', $redirect_base ) );
	exit;
}
add_action( 'admin_post_noghre_save_font_settings', 'noghre_handle_save_font_settings' );

/* ============================================================
 * پیام‌ها
 * ============================================================ */

function noghre_render_font_message( $msg, $extra_info = '' ) {
	$messages = array(
		'uploaded'       => array( 'success', 'آپلود انجام شد.' ),
		'family_deleted' => array( 'success', 'خانواده فونت و همه فایل‌های آن حذف شد.' ),
		'weight_deleted' => array( 'success', 'فایل وزن حذف شد.' ),
		'settings_saved' => array( 'success', 'تنظیمات فونت ذخیره شد.' ),
		'no_file'        => array( 'error', 'فایلی برای آپلود انتخاب نشده است.' ),
		'invalid_type'   => array( 'error', 'فرمت فایل پشتیبانی نمی‌شود.' ),
		'too_large'      => array( 'error', 'حجم فایل بیش از حد مجاز است.' ),
		'upload_failed'  => array( 'error', 'خطا در آپلود فایل‌ها.' ),
		'invalid_id'     => array( 'error', 'شناسه نامعتبر است.' ),
		'not_found'      => array( 'error', 'مورد نظر یافت نشد.' ),
	);

	if ( ! isset( $messages[ $msg ] ) ) return;

	list( $type, $text ) = $messages[ $msg ];
	$display_text = $extra_info ? $extra_info : $text;

	$icon_map = array(
		'success' => 'dashicons-yes-alt',
		'error'   => 'dashicons-dismiss',
		'warning' => 'dashicons-warning',
	);
	$icon = isset( $icon_map[ $type ] ) ? $icon_map[ $type ] : 'dashicons-info';

	printf(
		'<div class="noghre-message noghre-message--%s" data-autoclose="7000">
			<span class="noghre-message__icon dashicons %s"></span>
			<span class="noghre-message__text">%s</span>
			<button type="button" class="noghre-message__close" aria-label="بستن">×</button>
		</div>',
		esc_attr( $type ),
		esc_attr( $icon ),
		esc_html( $display_text )
	);

	$errors = get_transient( 'noghre_font_upload_errors_' . get_current_user_id() );
	if ( $errors && is_array( $errors ) ) {
		delete_transient( 'noghre_font_upload_errors_' . get_current_user_id() );
		echo '<div class="notice notice-warning inline">';
		echo '<p><strong>جزئیات خطاها:</strong></p>';
		echo '<ul style="margin:0;padding-inline-start:24px;line-height:1.9;font-size:13px;">';
		foreach ( $errors as $err ) {
			echo '<li>' . esc_html( $err ) . '</li>';
		}
		echo '</ul></div>';
	}
}

/* ============================================================
 * رندر زیرتب فونت
 * ============================================================ */

function noghre_render_fonts_subtab() {
	$o = noghre_get_general_options();
	$fonts = noghre_get_uploaded_fonts();

	$site_font_id  = isset( $o['site_font_id'] ) ? $o['site_font_id'] : '';
	$admin_font_id = isset( $o['admin_font_id'] ) ? $o['admin_font_id'] : '';

	$current_msg  = isset( $_GET['noghre_font_msg'] ) ? sanitize_key( $_GET['noghre_font_msg'] ) : '';
	$current_info = isset( $_GET['noghre_font_info'] ) ? sanitize_text_field( urldecode( wp_unslash( $_GET['noghre_font_info'] ) ) ) : '';

	$weights = noghre_font_weights();
	$fonts_url = noghre_get_fonts_url();
	$fonts_dir = noghre_get_fonts_dir();
	?>

	<?php if ( $current_msg ) : ?>
		<?php noghre_render_font_message( $current_msg, $current_info ); ?>
	<?php endif; ?>

	<h2 class="noghre-section-heading">
		<span class="dashicons dashicons-editor-textcolor"></span>
		فونت‌های فعال
	</h2>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<?php wp_nonce_field( 'noghre_save_font_settings' ); ?>
		<input type="hidden" name="action" value="noghre_save_font_settings">

		<table class="form-table">
			<tr>
				<th><label for="site_font_id">فونت سایت</label></th>
				<td>
					<select id="site_font_id" name="site_font_id" style="min-width:300px;">
						<option value="">— فونت پیش‌فرض قالب (وزیرمتن) —</option>
						<?php foreach ( $fonts as $font_id => $font ) : ?>
							<option value="<?php echo esc_attr( $font_id ); ?>" <?php selected( $site_font_id, $font_id ); ?>>
								<?php echo esc_html( $font['name'] ); ?>
							</option>
						<?php endforeach; ?>
					</select>
					<p class="description">روی تمام صفحات سایت (به جز پیشخوان وردپرس) اعمال می‌شود.</p>
				</td>
			</tr>
			<tr>
				<th><label for="admin_font_id">فونت پنل مدیریت</label></th>
				<td>
					<select id="admin_font_id" name="admin_font_id" style="min-width:300px;">
						<option value="">— فونت پیش‌فرض وردپرس —</option>
						<?php foreach ( $fonts as $font_id => $font ) : ?>
							<option value="<?php echo esc_attr( $font_id ); ?>" <?php selected( $admin_font_id, $font_id ); ?>>
								<?php echo esc_html( $font['name'] ); ?>
							</option>
						<?php endforeach; ?>
					</select>
					<p class="description">فقط روی پیشخوان وردپرس اعمال می‌شود.</p>
				</td>
			</tr>
		</table>
		<?php submit_button( 'ذخیره تنظیمات فونت' ); ?>
	</form>

	<hr>

	<h2 class="noghre-section-heading">
		<span class="dashicons dashicons-upload"></span>
		آپلود فونت‌ها
	</h2>

	<div class="notice notice-info inline" style="margin:12px 0;">
		<p>
			<strong>نکته مهم:</strong> می‌تونید <strong>چند فایل فونت رو همزمان</strong> انتخاب کنید. سیستم بر اساس نام فایل‌ها، خانواده و وزن هر فونت رو <strong>خودکار تشخیص می‌ده</strong>.
		</p>

		<form method="post"
			  action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"
			  enctype="multipart/form-data"
			  id="noghre-font-upload-form"
			  style="margin-top:12px;">
			<?php wp_nonce_field( 'noghre_upload_font' ); ?>
			<input type="hidden" name="action" value="noghre_upload_font">

			<div id="noghre-font-dropzone" class="noghre-font-dropzone" style="border:2px dashed #c3c4c7;border-radius:4px;background:#fff;padding:32px 20px;text-align:center;cursor:pointer;transition:all .2s ease;margin-bottom:12px;">
				<span class="dashicons dashicons-upload" style="font-size:48px;width:48px;height:48px;color:#2271b1;margin-bottom:8px;"></span>
				<p style="margin:0 0 4px;font-size:15px;font-weight:600;color:#1d2327;">فایل‌های فونت را اینجا رها کنید</p>
				<p style="margin:0;font-size:13px;color:#646970;">یا برای انتخاب کلیک کنید — می‌توانید چند فایل را همزمان انتخاب کنید</p>
				<p style="margin:8px 0 0;font-size:12px;color:#8c8f94;">فرمت‌های مجاز: woff2، woff، ttf، otf — حداکثر ۵ مگابایت برای هر فایل</p>
				<input type="file"
					   name="font_files[]"
					   id="noghre-font-input"
					   accept=".woff2,.woff,.ttf,.otf"
					   multiple
					   style="display:none;">
			</div>

			<div id="noghre-font-selected" class="noghre-font-selected" style="display:none;margin-bottom:14px;">
				<p style="margin:0 0 8px;font-size:13px;font-weight:600;color:#1d2327;">
					فایل‌های انتخاب‌شده (<span id="noghre-font-count">0</span>):
				</p>
				<ul id="noghre-font-list" style="margin:0;padding:0;list-style:none;max-height:220px;overflow-y:auto;"></ul>
			</div>

			<p style="margin:0;">
				<button type="submit" class="button button-primary" id="noghre-font-upload-btn" disabled>
					آپلود فونت‌ها
				</button>
			</p>
		</form>
	</div>

	<hr>

	<h2 class="noghre-section-heading">
		<span class="dashicons dashicons-editor-textcolor"></span>
		خانواده‌های فونت موجود
	</h2>

	<?php if ( empty( $fonts ) ) : ?>
		<p style="color:#646970;">هنوز فونتی آپلود نشده است.</p>
	<?php else : ?>

		<?php foreach ( $fonts as $font_id => $font ) : ?>
			<?php
			$delete_family_url = wp_nonce_url(
				add_query_arg(
					array( 'action' => 'noghre_delete_font_family', 'font_id' => $font_id ),
					admin_url( 'admin-post.php' )
				),
				'noghre_delete_font_family'
			);
			$files = ! empty( $font['files'] ) ? $font['files'] : array();
			$files_count = count( $files );

			$used_in = array();
			if ( $site_font_id === $font_id ) $used_in[] = 'سایت';
			if ( $admin_font_id === $font_id ) $used_in[] = 'پنل مدیریت';
			?>

			<div class="noghre-font-family-card">

				<div class="noghre-font-family-header">
					<div>
						<h3><?php echo esc_html( $font['name'] ); ?></h3>
						<p>
							<?php echo esc_html( noghre_to_persian_digits( $files_count ) ); ?> وزن
							<?php if ( ! empty( $used_in ) ) : ?>
								| <span class="noghre-font-active-label">فعال در: <?php echo esc_html( implode( '، ', $used_in ) ); ?></span>
							<?php endif; ?>
						</p>
					</div>
					<a href="<?php echo esc_url( $delete_family_url ); ?>"
					   class="button button-secondary"
					   style="color:#d63638;border-color:#d63638;"
					   onclick="return confirm('آیا از حذف کل این خانواده فونت و همه فایل‌های آن مطمئن هستید؟');">
						حذف کل خانواده
					</a>
				</div>

				<table class="widefat">
					<thead>
						<tr>
							<th style="width:130px;padding:8px 10px;">وزن</th>
							<th style="width:90px;padding:8px 10px;">استایل</th>
							<th style="padding:8px 10px;">فایل</th>
							<th style="width:180px;padding:8px 10px;">پیش‌نمایش</th>
							<th style="width:110px;padding:8px 10px;">عملیات</th>
						</tr>
					</thead>
					<tbody>
						<?php
						ksort( $files, SORT_NATURAL );

						foreach ( $files as $key => $file_data ) :
							$parts = explode( '_', $key, 2 );
							$weight_num = isset( $parts[0] ) ? (int) $parts[0] : 400;
							$style      = isset( $parts[1] ) ? $parts[1] : 'normal';

							$weight_label = isset( $weights[ $weight_num ] ) ? $weights[ $weight_num ] : $weight_num;
							$style_label  = $style === 'italic' ? 'ایتالیک' : 'معمولی';

							$file_url  = trailingslashit( $fonts_url ) . $file_data['file'];
							$file_path = trailingslashit( $fonts_dir ) . $file_data['file'];
							$file_exists = file_exists( $file_path );
							$file_size = $file_exists ? size_format( filesize( $file_path ), 1 ) : '';

							$delete_url = wp_nonce_url(
								add_query_arg(
									array(
										'action'  => 'noghre_delete_font_weight',
										'font_id' => $font_id,
										'key'     => $key,
									),
									admin_url( 'admin-post.php' )
								),
								'noghre_delete_font_weight'
							);
							?>
							<tr>
								<td style="padding:8px 10px;">
									<strong><?php echo esc_html( $weight_label ); ?></strong>
									<span style="color:#646970;font-size:11px;">(<?php echo esc_html( noghre_to_persian_digits( $weight_num ) ); ?>)</span>
								</td>
								<td style="padding:8px 10px;"><?php echo esc_html( $style_label ); ?></td>
								<td style="padding:8px 10px;">
									<code dir="ltr" style="font-size:11px;background:#f0f0f1;padding:2px 6px;border-radius:3px;display:inline-block;">
										<?php echo esc_html( $file_data['file'] ); ?>
									</code>
									<?php if ( $file_size ) : ?>
										<span style="color:#646970;font-size:11px;">— <?php echo esc_html( $file_size ); ?></span>
									<?php endif; ?>
								</td>
								<td style="padding:8px 10px;">
									<?php if ( $file_exists ) : ?>
										<style>
											@font-face {
												font-family: '<?php echo esc_attr( 'NoghrePreview_' . $font_id ); ?>';
												src: url('<?php echo esc_url( $file_url ); ?>');
												font-weight: <?php echo esc_attr( $weight_num ); ?>;
												font-style: <?php echo esc_attr( $style ); ?>;
												font-display: swap;
											}
										</style>
										<div style="font-family: '<?php echo esc_attr( 'NoghrePreview_' . $font_id ); ?>', Tahoma, sans-serif; font-weight: <?php echo esc_attr( $weight_num ); ?>; font-style: <?php echo esc_attr( $style ); ?>; font-size: 17px; color:#1d2327; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 170px;">
											نقره — درخشش تو
										</div>
									<?php else : ?>
										<span style="color:#d63638;font-size:11px;">
											<span class="dashicons dashicons-warning" style="font-size:14px;width:14px;height:14px;"></span> فایل یافت نشد
										</span>
									<?php endif; ?>
								</td>
								<td style="padding:8px 10px;">
									<a href="<?php echo esc_url( $delete_url ); ?>"
									   class="button button-small"
									   style="color:#d63638;border-color:#d63638;"
									   onclick="return confirm('آیا از حذف این فایل مطمئن هستید؟');">
										حذف
									</a>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>

			</div>
		<?php endforeach; ?>

	<?php endif; ?>

	<h2 class="noghre-section-heading">
		<span class="dashicons dashicons-info-outline"></span>
		راهنمای نام‌گذاری فایل‌ها
	</h2>
	<div style="background:#f6f7f7;border:1px solid #c3c4c7;border-radius:4px;padding:16px 20px;max-width:820px;line-height:1.9;font-size:13px;color:#50575e;">
		<p style="margin:0 0 12px;">سیستم به‌طور خودکار از نام فایل، خانواده و وزن فونت را تشخیص می‌ده. برای اینکه تشخیص درست باشه، فایل‌ها رو با یکی از این الگوها نام‌گذاری کنید:</p>

		<table class="widefat" style="background:#fff;">
			<thead>
				<tr>
					<th style="padding:8px 12px;text-align:right;">نام فایل</th>
					<th style="padding:8px 12px;text-align:right;">تشخیص سیستم</th>
				</tr>
			</thead>
			<tbody>
				<tr><td style="padding:6px 12px;" dir="ltr"><code>Vazirmatn-Regular.woff2</code></td><td style="padding:6px 12px;">خانواده: Vazirmatn — Regular (400)</td></tr>
				<tr><td style="padding:6px 12px;" dir="ltr"><code>Vazirmatn-Bold.woff2</code></td><td style="padding:6px 12px;">خانواده: Vazirmatn — Bold (700)</td></tr>
				<tr><td style="padding:6px 12px;" dir="ltr"><code>Vazirmatn-Light.woff2</code></td><td style="padding:6px 12px;">خانواده: Vazirmatn — Light (300)</td></tr>
				<tr><td style="padding:6px 12px;" dir="ltr"><code>IRANSans-Medium.woff</code></td><td style="padding:6px 12px;">خانواده: IRANSans — Medium (500)</td></tr>
				<tr><td style="padding:6px 12px;" dir="ltr"><code>Estedad-Bold-Italic.ttf</code></td><td style="padding:6px 12px;">خانواده: Estedad — Bold (700) + ایتالیک</td></tr>
				<tr><td style="padding:6px 12px;" dir="ltr"><code>Shabnam-400.woff2</code></td><td style="padding:6px 12px;">خانواده: Shabnam — Regular (400)</td></tr>
			</tbody>
		</table>

		<p style="margin:14px 0 0;padding:10px 14px;background:#fcf9e8;border-radius:4px;color:#8a6100;">
			<strong>نکته:</strong> همه فایل‌های یک خانواده رو می‌تونید <strong>یکجا انتخاب کنید</strong> و آپلود کنید. سیستم به‌طور خودکار اون‌ها رو به درستی گروه‌بندی می‌کنه.
		</p>
	</div>

	<script>
	(function() {
		var dropzone = document.getElementById('noghre-font-dropzone');
		var input    = document.getElementById('noghre-font-input');
		var list     = document.getElementById('noghre-font-list');
		var count    = document.getElementById('noghre-font-count');
		var selected = document.getElementById('noghre-font-selected');
		var btn      = document.getElementById('noghre-font-upload-btn');

		if (!dropzone || !input) return;

		function renderFiles(files) {
			if (!files || !files.length) {
				selected.style.display = 'none';
				btn.disabled = true;
				count.textContent = '0';
				list.innerHTML = '';
				return;
			}

			selected.style.display = 'block';
			btn.disabled = false;
			count.textContent = toPersian(files.length);

			var html = '';
			for (var i = 0; i < files.length; i++) {
				var file = files[i];
				var sizeKb = (file.size / 1024).toFixed(1);
				html += '<li style="display:flex;align-items:center;justify-content:space-between;padding:6px 10px;background:#fff;border:1px solid #dcdcde;border-radius:4px;margin-bottom:4px;font-size:12px;">';
				html += '<span style="display:flex;align-items:center;gap:8px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" dir="ltr"><span class="dashicons dashicons-media-default" style="font-size:14px;width:14px;height:14px;color:#2271b1;"></span> ' + escapeHtml(file.name) + '</span>';
				html += '<span style="color:#646970;flex-shrink:0;">' + toPersian(sizeKb) + ' کیلوبایت</span>';
				html += '</li>';
			}
			list.innerHTML = html;
		}

		function toPersian(n) {
			var en = '0123456789', fa = '۰۱۲۳۴۵۶۷۸۹';
			return String(n).replace(/[0-9]/g, function(d) { return fa[en.indexOf(d)]; });
		}

		function escapeHtml(str) {
			return String(str).replace(/[&<>"']/g, function(c) {
				return { '&':'&amp;', '<':'&lt;', '>':'&gt;', '"':'&quot;', "'":'&#39;' }[c];
			});
		}

		dropzone.addEventListener('click', function() {
			input.click();
		});

		input.addEventListener('change', function() {
			renderFiles(this.files);
		});

		['dragenter', 'dragover'].forEach(function(evt) {
			dropzone.addEventListener(evt, function(e) {
				e.preventDefault();
				e.stopPropagation();
				dropzone.style.borderColor = '#2271b1';
				dropzone.style.background = '#f0f6fc';
			});
		});

		['dragleave', 'drop'].forEach(function(evt) {
			dropzone.addEventListener(evt, function(e) {
				e.preventDefault();
				e.stopPropagation();
				dropzone.style.borderColor = '#c3c4c7';
				dropzone.style.background = '#fff';
			});
		});

		dropzone.addEventListener('drop', function(e) {
			e.preventDefault();
			if (e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files.length) {
				input.files = e.dataTransfer.files;
				renderFiles(input.files);
			}
		});
	})();
	</script>

	<?php
}

/* ============================================================
 * تولید CSS @font-face برای یک فونت خاص
 * ============================================================ */

function noghre_get_single_font_face_css( $font_id ) {
	$fonts = noghre_get_uploaded_fonts();
	if ( ! isset( $fonts[ $font_id ] ) ) return '';

	$font = $fonts[ $font_id ];

	if ( empty( $font['files'] ) || ! is_array( $font['files'] ) ) return '';
	if ( empty( $font['name'] ) ) return '';

	$fonts_url = noghre_get_fonts_url();
	$fonts_dir = noghre_get_fonts_dir();
	$css = '';

	foreach ( $font['files'] as $key => $file_data ) {
		if ( empty( $file_data['file'] ) ) continue;

		$file_path = trailingslashit( $fonts_dir ) . $file_data['file'];
		if ( ! file_exists( $file_path ) ) continue;

		$file_url = trailingslashit( $fonts_url ) . $file_data['file'];

		$parts = explode( '_', $key, 2 );
		$weight = isset( $parts[0] ) ? (int) $parts[0] : 400;
		$style  = isset( $parts[1] ) ? $parts[1] : 'normal';

		$format_map = array(
			'woff2' => 'woff2',
			'woff'  => 'woff',
			'ttf'   => 'truetype',
			'otf'   => 'opentype',
		);
		$ext = strtolower( $file_data['format'] ?? 'woff2' );
		$format = isset( $format_map[ $ext ] ) ? $format_map[ $ext ] : 'truetype';

		$css .= "@font-face {\n";
		$css .= "  font-family: '" . esc_attr( $font['name'] ) . "';\n";
		$css .= "  src: url('" . esc_url( $file_url ) . "') format('" . esc_attr( $format ) . "');\n";
		$css .= "  font-weight: " . (int) $weight . ";\n";
		$css .= "  font-style: " . ( $style === 'italic' ? 'italic' : 'normal' ) . ";\n";
		$css .= "  font-display: swap;\n";
		$css .= "}\n\n";
	}

	return $css;
}

function noghre_get_font_face_css() {
	$o = noghre_get_general_options();
	$site_font_id = ! empty( $o['site_font_id'] ) ? $o['site_font_id'] : '';

	if ( empty( $site_font_id ) ) return '';

	return noghre_get_single_font_face_css( $site_font_id );
}