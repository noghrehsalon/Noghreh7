<?php
/**
 * تب پیامک
 *
 * @package Noghre
 */

if ( ! defined( 'ABSPATH' ) ) exit;

function noghre_render_sms_page() {
	$o      = noghre_get_sms_options();
	$status = noghre_afra_get_status();

	$numbers    = noghre_sms_get_numbers();
	$patterns   = noghre_sms_get_patterns();
	$phonebooks = noghre_sms_get_phonebooks();

	$has_numbers    = is_array( $numbers ) && ! empty( $numbers );
	$has_patterns   = is_array( $patterns ) && ! empty( $patterns );
	$has_phonebooks = is_array( $phonebooks ) && ! empty( $phonebooks );

	$total_numbers    = $has_numbers ? count( $numbers ) : 0;
	$total_patterns   = $has_patterns ? count( $patterns ) : 0;
	$total_phonebooks = $has_phonebooks ? count( $phonebooks ) : 0;
	?>

	<h2 class="noghre-section-heading">
		<span class="dashicons dashicons-info-outline"></span>
		وضعیت افزونه افرا اس ام اس
	</h2>

	<?php
	$status_class = 'notice-info';
	if ( $status['ready'] ) {
		$status_class = 'notice-success';
	} elseif ( ! $status['installed'] || ! $status['active'] ) {
		$status_class = 'notice-error';
	} else {
		$status_class = 'notice-warning';
	}
	?>
	<div class="notice <?php echo esc_attr( $status_class ); ?> inline" style="margin:12px 0;">
		<p style="margin:0;font-size:14px;">
			<strong>وضعیت:</strong>
			<?php echo esc_html( $status['message'] ); ?>
		</p>
		<?php
		$credit = noghre_sms_get_credit_toman();
		if ( ! is_wp_error( $credit ) ) :
			?>
			<p style="margin:10px 0 0;font-size:14px;">
				<strong>اعتبار فعلی پنل پیامک:</strong>
				<?php echo esc_html( noghre_to_persian_digits( number_format_i18n( $credit ) ) ); ?> تومان
			</p>
		<?php endif; ?>
	</div>

	<?php if ( ! $status['ready'] ) : ?>
		<p class="description">
			تا زمانی که افزونه افرا اس ام اس نصب، فعال و متصل نباشد، تنظیمات این صفحه کار نخواهند کرد.
			برای نصب و پیکربندی به
			<a href="https://afrasms.ir" target="_blank" rel="noopener">afrasms.ir</a>
			مراجعه کنید.
		</p>
		<?php
		return;
	endif;
	?>

	<form method="post" action="options.php">
		<?php settings_fields( 'noghre_sms_group' ); ?>

		<h2 class="noghre-section-heading">
			<span class="dashicons dashicons-phone"></span>
			سرشماره پیش‌فرض
		</h2>
		<table class="form-table">
			<tr>
				<th><label for="sms_sender_number">سرشماره</label></th>
				<td>
					<?php if ( $has_numbers ) : ?>
						<select id="sms_sender_number" name="noghre_sms_options[sms_sender_number]" class="noghre-pattern-select">
							<option value="">— بدون سرشماره (استفاده از پیش‌فرض افزونه) —</option>
							<?php foreach ( $numbers as $num ) : ?>
								<option value="<?php echo esc_attr( $num['value'] ); ?>" <?php selected( $o['sms_sender_number'], $num['value'] ); ?>>
									<?php echo esc_html( $num['title'] ); ?> — (<?php echo esc_html( $num['value'] ); ?>)
								</option>
							<?php endforeach; ?>
						</select>
						<div class="noghre-field-desc">
							<span class="noghre-pattern-count"><?php echo esc_html( noghre_to_persian_digits( $total_numbers ) ); ?> سرشماره موجود.</span>
							<button type="button" class="button button-secondary noghre-refresh-btn" data-target="numbers">
								<span class="dashicons dashicons-update"></span> بروزرسانی لیست
							</button>
						</div>
					<?php else : ?>
						<input type="text" id="sms_sender_number" name="noghre_sms_options[sms_sender_number]" value="<?php echo esc_attr( $o['sms_sender_number'] ); ?>" class="regular-text" dir="ltr" placeholder="مثلاً: +983000505">
						<div class="noghre-field-desc">
							<strong style="color:#dba617;">لیست سرشماره‌ها در دسترس نیست.</strong>
							<button type="button" class="button button-secondary noghre-refresh-btn" data-target="numbers">
								<span class="dashicons dashicons-update"></span> دریافت لیست سرشماره‌ها
							</button>
						</div>
					<?php endif; ?>
				</td>
			</tr>
		</table>

		<h2 class="noghre-section-heading">
			<span class="dashicons dashicons-tag"></span>
			۱. پیامک باشگاه تخفیف
		</h2>
		<table class="form-table">
			<tr>
				<th>فعال‌سازی ارسال</th>
				<td>
					<label>
						<input type="checkbox" name="noghre_sms_options[sms_discount_enabled]" value="1" <?php checked( $o['sms_discount_enabled'], 1 ); ?>>
						ارسال پیامک باشگاه تخفیف
					</label>
				</td>
			</tr>
			<tr>
				<th><label for="sms_pattern_code">کد پترن</label></th>
				<td>
					<?php if ( $has_patterns ) : ?>
						<select id="sms_pattern_code" name="noghre_sms_options[sms_pattern_code]" class="noghre-pattern-select">
							<option value="">— انتخاب پترن —</option>
							<?php foreach ( $patterns as $p ) : ?>
								<option value="<?php echo esc_attr( $p['code'] ); ?>"
									data-message="<?php echo esc_attr( $p['message'] ); ?>"
									<?php selected( $o['sms_pattern_code'], $p['code'] ); ?>>
									<?php echo esc_html( $p['code'] . ( $p['title'] && $p['title'] !== $p['code'] ? ' — ' . $p['title'] : '' ) ); ?>
								</option>
							<?php endforeach; ?>
						</select>
						<div class="noghre-field-desc">
							<span class="noghre-pattern-count"><?php echo esc_html( noghre_to_persian_digits( $total_patterns ) ); ?> پترن موجود.</span>
							<button type="button" class="button button-secondary noghre-refresh-btn" data-target="patterns">
								<span class="dashicons dashicons-update"></span> بروزرسانی لیست
							</button>
						</div>
					<?php else : ?>
						<input type="text" id="sms_pattern_code" name="noghre_sms_options[sms_pattern_code]" value="<?php echo esc_attr( $o['sms_pattern_code'] ); ?>" class="regular-text" dir="ltr">
					<?php endif; ?>
				</td>
			</tr>
			<tr>
				<th>پیش‌نمایش متن پترن</th>
				<td>
					<div class="noghre-pattern-preview" id="preview-discount" style="display:none;">
						<strong>پیش‌نمایش متن پترن:</strong>
						<div class="noghre-pattern-message" id="preview-discount-text"></div>
					</div>
					<div class="noghre-pattern-preview-placeholder" id="placeholder-discount">پس از انتخاب پترن، متن آن در اینجا نمایش داده می‌شود.</div>
				</td>
			</tr>
			<tr>
				<th><label for="sms_pattern_name_var">نام متغیر نام کاربر</label></th>
				<td>
					<input type="text" id="sms_pattern_name_var" name="noghre_sms_options[sms_pattern_name_var]" value="<?php echo esc_attr( $o['sms_pattern_name_var'] ); ?>" class="regular-text" dir="ltr" placeholder="name">
				</td>
			</tr>
			<tr>
				<th><label for="sms_pattern_discount_var">نام متغیر کد تخفیف</label></th>
				<td>
					<input type="text" id="sms_pattern_discount_var" name="noghre_sms_options[sms_pattern_discount_var]" value="<?php echo esc_attr( $o['sms_pattern_discount_var'] ); ?>" class="regular-text" dir="ltr" placeholder="discount">
				</td>
			</tr>
			<tr>
				<th><label for="sms_pattern_discount_val">مقدار کد تخفیف</label></th>
				<td>
					<input type="text" id="sms_pattern_discount_val" name="noghre_sms_options[sms_pattern_discount_val]" value="<?php echo esc_attr( $o['sms_pattern_discount_val'] ); ?>" class="regular-text" dir="ltr" placeholder="مثلاً: OFF-WELCOME">
				</td>
			</tr>
			<tr>
				<th><label for="sms_phonebook_id">دفترچه تلفن باشگاه تخفیف</label></th>
				<td>
					<?php if ( $has_phonebooks ) : ?>
						<select id="sms_phonebook_id" name="noghre_sms_options[sms_phonebook_id]" class="noghre-pattern-select">
							<option value="">— انتخاب دفترچه —</option>
							<?php foreach ( $phonebooks as $book ) : ?>
								<option value="<?php echo esc_attr( $book['id'] ); ?>" <?php selected( (string) $o['sms_phonebook_id'], (string) $book['id'] ); ?>>
									<?php echo esc_html( $book['title'] ); ?> — (<?php echo esc_html( noghre_to_persian_digits( $book['count'] ) ); ?> مخاطب)
								</option>
							<?php endforeach; ?>
						</select>
						<div class="noghre-field-desc">
							<span class="noghre-pattern-count"><?php echo esc_html( noghre_to_persian_digits( $total_phonebooks ) ); ?> دفترچه موجود.</span>
							<button type="button" class="button button-secondary noghre-refresh-btn" data-target="phonebooks">
								<span class="dashicons dashicons-update"></span> بروزرسانی لیست
							</button>
						</div>
					<?php else : ?>
						<input type="text" id="sms_phonebook_id" name="noghre_sms_options[sms_phonebook_id]" value="<?php echo esc_attr( $o['sms_phonebook_id'] ); ?>" class="regular-text" dir="ltr">
					<?php endif; ?>
				</td>
			</tr>
			<tr>
				<th><label for="sms_customer_club_phonebook_id">دفترچه تلفن باشگاه مشتریان</label></th>
				<td>
					<?php if ( $has_phonebooks ) : ?>
						<select id="sms_customer_club_phonebook_id" name="noghre_sms_options[sms_customer_club_phonebook_id]" class="noghre-pattern-select">
							<option value="">— انتخاب دفترچه —</option>
							<?php foreach ( $phonebooks as $book ) : ?>
								<option value="<?php echo esc_attr( $book['id'] ); ?>" <?php selected( (string) $o['sms_customer_club_phonebook_id'], (string) $book['id'] ); ?>>
									<?php echo esc_html( $book['title'] ); ?> — (<?php echo esc_html( noghre_to_persian_digits( $book['count'] ) ); ?> مخاطب)
								</option>
							<?php endforeach; ?>
						</select>
					<?php else : ?>
						<input type="text" id="sms_customer_club_phonebook_id" name="noghre_sms_options[sms_customer_club_phonebook_id]" value="<?php echo esc_attr( $o['sms_customer_club_phonebook_id'] ); ?>" class="regular-text" dir="ltr">
					<?php endif; ?>
				</td>
			</tr>
		</table>

		<h2 class="noghre-section-heading">
			<span class="dashicons dashicons-email-alt"></span>
			۲. پیامک تایید رزرو (به مشتری)
		</h2>
		<table class="form-table">
			<tr>
				<th>فعال‌سازی ارسال</th>
				<td>
					<label>
						<input type="checkbox" name="noghre_sms_options[sms_customer_enabled]" value="1" <?php checked( $o['sms_customer_enabled'], 1 ); ?>>
						ارسال پیامک به مشتری
					</label>
				</td>
			</tr>
			<tr>
				<th><label for="sms_pattern_booking_customer">کد پترن</label></th>
				<td>
					<?php if ( $has_patterns ) : ?>
						<select id="sms_pattern_booking_customer" name="noghre_sms_options[sms_pattern_booking_customer]" class="noghre-pattern-select">
							<option value="">— انتخاب پترن —</option>
							<?php foreach ( $patterns as $p ) : ?>
								<option value="<?php echo esc_attr( $p['code'] ); ?>"
									data-message="<?php echo esc_attr( $p['message'] ); ?>"
									<?php selected( $o['sms_pattern_booking_customer'], $p['code'] ); ?>>
									<?php echo esc_html( $p['code'] . ( $p['title'] && $p['title'] !== $p['code'] ? ' — ' . $p['title'] : '' ) ); ?>
								</option>
							<?php endforeach; ?>
						</select>
						<div class="noghre-field-desc">
							<span class="noghre-pattern-count"><?php echo esc_html( noghre_to_persian_digits( $total_patterns ) ); ?> پترن موجود.</span>
							<button type="button" class="button button-secondary noghre-refresh-btn" data-target="patterns">
								<span class="dashicons dashicons-update"></span> بروزرسانی لیست
							</button>
						</div>
					<?php else : ?>
						<input type="text" id="sms_pattern_booking_customer" name="noghre_sms_options[sms_pattern_booking_customer]" value="<?php echo esc_attr( $o['sms_pattern_booking_customer'] ); ?>" class="regular-text" dir="ltr">
					<?php endif; ?>
				</td>
			</tr>
			<tr>
				<th>پیش‌نمایش متن پترن</th>
				<td>
					<div class="noghre-pattern-preview" id="preview-customer" style="display:none;">
						<strong>پیش‌نمایش متن پترن:</strong>
						<div class="noghre-pattern-message" id="preview-customer-text"></div>
					</div>
					<div class="noghre-pattern-preview-placeholder" id="placeholder-customer">پس از انتخاب پترن، متن آن در اینجا نمایش داده می‌شود.</div>
				</td>
			</tr>
			<tr>
				<th>متغیرهای پترن</th>
				<td>
					<p>
						<label>متغیر نام مشتری:
							<input type="text" name="noghre_sms_options[sms_pattern_customer_var_name]" value="<?php echo esc_attr( $o['sms_pattern_customer_var_name'] ); ?>" dir="ltr" style="width:130px;">
						</label>
					</p>
					<p>
						<label>متغیر کد پیگیری:
							<input type="text" name="noghre_sms_options[sms_pattern_customer_var_peygiri]" value="<?php echo esc_attr( $o['sms_pattern_customer_var_peygiri'] ); ?>" dir="ltr" style="width:130px;">
						</label>
					</p>
				</td>
			</tr>
		</table>

		<h2 class="noghre-section-heading">
			<span class="dashicons dashicons-store"></span>
			۳. پیامک اطلاع‌رسانی رزرو (به مدیر شعبه)
		</h2>
		<table class="form-table">
			<tr>
				<th>فعال‌سازی ارسال</th>
				<td>
					<label>
						<input type="checkbox" name="noghre_sms_options[sms_branch_enabled]" value="1" <?php checked( $o['sms_branch_enabled'], 1 ); ?>>
						ارسال پیامک به مدیر شعبه
					</label>
				</td>
			</tr>
			<tr>
				<th><label for="sms_pattern_booking_branch">کد پترن</label></th>
				<td>
					<?php if ( $has_patterns ) : ?>
						<select id="sms_pattern_booking_branch" name="noghre_sms_options[sms_pattern_booking_branch]" class="noghre-pattern-select">
							<option value="">— انتخاب پترن —</option>
							<?php foreach ( $patterns as $p ) : ?>
								<option value="<?php echo esc_attr( $p['code'] ); ?>"
									data-message="<?php echo esc_attr( $p['message'] ); ?>"
									<?php selected( $o['sms_pattern_booking_branch'], $p['code'] ); ?>>
									<?php echo esc_html( $p['code'] . ( $p['title'] && $p['title'] !== $p['code'] ? ' — ' . $p['title'] : '' ) ); ?>
								</option>
							<?php endforeach; ?>
						</select>
						<div class="noghre-field-desc">
							<span class="noghre-pattern-count"><?php echo esc_html( noghre_to_persian_digits( $total_patterns ) ); ?> پترن موجود.</span>
							<button type="button" class="button button-secondary noghre-refresh-btn" data-target="patterns">
								<span class="dashicons dashicons-update"></span> بروزرسانی لیست
							</button>
						</div>
					<?php else : ?>
						<input type="text" id="sms_pattern_booking_branch" name="noghre_sms_options[sms_pattern_booking_branch]" value="<?php echo esc_attr( $o['sms_pattern_booking_branch'] ); ?>" class="regular-text" dir="ltr">
					<?php endif; ?>
				</td>
			</tr>
			<tr>
				<th>پیش‌نمایش متن پترن</th>
				<td>
					<div class="noghre-pattern-preview" id="preview-branch" style="display:none;">
						<strong>پیش‌نمایش متن پترن:</strong>
						<div class="noghre-pattern-message" id="preview-branch-text"></div>
					</div>
					<div class="noghre-pattern-preview-placeholder" id="placeholder-branch">پس از انتخاب پترن، متن آن در اینجا نمایش داده می‌شود.</div>
				</td>
			</tr>
			<tr>
				<th>متغیرهای پترن</th>
				<td>
					<p>
						<label>متغیر نام شعبه:
							<input type="text" name="noghre_sms_options[sms_pattern_branch_var_shobe]" value="<?php echo esc_attr( $o['sms_pattern_branch_var_shobe'] ); ?>" dir="ltr" style="width:130px;">
						</label>
					</p>
					<p>
						<label>متغیر کد پیگیری:
							<input type="text" name="noghre_sms_options[sms_pattern_branch_var_peygiri]" value="<?php echo esc_attr( $o['sms_pattern_branch_var_peygiri'] ); ?>" dir="ltr" style="width:130px;">
						</label>
					</p>
					<p>
						<label>متغیر شناسه مدیریت:
							<input type="text" name="noghre_sms_options[sms_pattern_branch_var_modir]" value="<?php echo esc_attr( $o['sms_pattern_branch_var_modir'] ); ?>" dir="ltr" style="width:130px;">
						</label>
					</p>
				</td>
			</tr>
		</table>

		<h2 class="noghre-section-heading">
			<span class="dashicons dashicons-yes-alt"></span>
			۴. پیامک تایید رزرو (هنگام تایید مدیریت)
		</h2>
		<table class="form-table">
			<tr>
				<th>فعال‌سازی ارسال</th>
				<td>
					<label>
						<input type="checkbox" name="noghre_sms_options[sms_confirm_enabled]" value="1" <?php checked( $o['sms_confirm_enabled'], 1 ); ?>>
						ارسال پیامک تایید
					</label>
				</td>
			</tr>
			<tr>
				<th><label for="sms_pattern_booking_confirm">کد پترن</label></th>
				<td>
					<?php if ( $has_patterns ) : ?>
						<select id="sms_pattern_booking_confirm" name="noghre_sms_options[sms_pattern_booking_confirm]" class="noghre-pattern-select">
							<option value="">— انتخاب پترن —</option>
							<?php foreach ( $patterns as $p ) : ?>
								<option value="<?php echo esc_attr( $p['code'] ); ?>"
									data-message="<?php echo esc_attr( $p['message'] ); ?>"
									<?php selected( $o['sms_pattern_booking_confirm'], $p['code'] ); ?>>
									<?php echo esc_html( $p['code'] . ( $p['title'] && $p['title'] !== $p['code'] ? ' — ' . $p['title'] : '' ) ); ?>
								</option>
							<?php endforeach; ?>
						</select>
						<div class="noghre-field-desc">
							<span class="noghre-pattern-count"><?php echo esc_html( noghre_to_persian_digits( $total_patterns ) ); ?> پترن موجود.</span>
							<button type="button" class="button button-secondary noghre-refresh-btn" data-target="patterns">
								<span class="dashicons dashicons-update"></span> بروزرسانی لیست
							</button>
						</div>
					<?php else : ?>
						<input type="text" id="sms_pattern_booking_confirm" name="noghre_sms_options[sms_pattern_booking_confirm]" value="<?php echo esc_attr( $o['sms_pattern_booking_confirm'] ); ?>" class="regular-text" dir="ltr">
					<?php endif; ?>
				</td>
			</tr>
			<tr>
				<th>پیش‌نمایش متن پترن</th>
				<td>
					<div class="noghre-pattern-preview" id="preview-confirm" style="display:none;">
						<strong>پیش‌نمایش متن پترن:</strong>
						<div class="noghre-pattern-message" id="preview-confirm-text"></div>
					</div>
					<div class="noghre-pattern-preview-placeholder" id="placeholder-confirm">پس از انتخاب پترن، متن آن در اینجا نمایش داده می‌شود.</div>
				</td>
			</tr>
			<tr>
				<th>متغیرهای پترن</th>
				<td>
					<p>
						<label>متغیر نام مشتری:
							<input type="text" name="noghre_sms_options[sms_pattern_confirm_var_name]" value="<?php echo esc_attr( $o['sms_pattern_confirm_var_name'] ); ?>" dir="ltr" style="width:130px;">
						</label>
					</p>
					<p>
						<label>متغیر کد پیگیری:
							<input type="text" name="noghre_sms_options[sms_pattern_confirm_var_peygiri]" value="<?php echo esc_attr( $o['sms_pattern_confirm_var_peygiri'] ); ?>" dir="ltr" style="width:130px;">
						</label>
					</p>
				</td>
			</tr>
		</table>

		<h2 class="noghre-section-heading">
			<span class="dashicons dashicons-edit"></span>
			۵. پیامک ویرایش رزرو
		</h2>
		<table class="form-table">
			<tr>
				<th>فعال‌سازی ارسال</th>
				<td>
					<label>
						<input type="checkbox" name="noghre_sms_options[sms_edit_enabled]" value="1" <?php checked( $o['sms_edit_enabled'], 1 ); ?>>
						ارسال پیامک ویرایش
					</label>
				</td>
			</tr>
			<tr>
				<th><label for="sms_pattern_booking_edit">کد پترن</label></th>
				<td>
					<?php if ( $has_patterns ) : ?>
						<select id="sms_pattern_booking_edit" name="noghre_sms_options[sms_pattern_booking_edit]" class="noghre-pattern-select">
							<option value="">— انتخاب پترن —</option>
							<?php foreach ( $patterns as $p ) : ?>
								<option value="<?php echo esc_attr( $p['code'] ); ?>"
									data-message="<?php echo esc_attr( $p['message'] ); ?>"
									<?php selected( $o['sms_pattern_booking_edit'], $p['code'] ); ?>>
									<?php echo esc_html( $p['code'] . ( $p['title'] && $p['title'] !== $p['code'] ? ' — ' . $p['title'] : '' ) ); ?>
								</option>
							<?php endforeach; ?>
						</select>
						<div class="noghre-field-desc">
							<span class="noghre-pattern-count"><?php echo esc_html( noghre_to_persian_digits( $total_patterns ) ); ?> پترن موجود.</span>
							<button type="button" class="button button-secondary noghre-refresh-btn" data-target="patterns">
								<span class="dashicons dashicons-update"></span> بروزرسانی لیست
							</button>
						</div>
					<?php else : ?>
						<input type="text" id="sms_pattern_booking_edit" name="noghre_sms_options[sms_pattern_booking_edit]" value="<?php echo esc_attr( $o['sms_pattern_booking_edit'] ); ?>" class="regular-text" dir="ltr">
					<?php endif; ?>
				</td>
			</tr>
			<tr>
				<th>پیش‌نمایش متن پترن</th>
				<td>
					<div class="noghre-pattern-preview" id="preview-edit" style="display:none;">
						<strong>پیش‌نمایش متن پترن:</strong>
						<div class="noghre-pattern-message" id="preview-edit-text"></div>
					</div>
					<div class="noghre-pattern-preview-placeholder" id="placeholder-edit">پس از انتخاب پترن، متن آن در اینجا نمایش داده می‌شود.</div>
				</td>
			</tr>
			<tr>
				<th>متغیرهای پترن</th>
				<td>
					<p>
						<label>متغیر نام مشتری:
							<input type="text" name="noghre_sms_options[sms_pattern_edit_var_name]" value="<?php echo esc_attr( $o['sms_pattern_edit_var_name'] ); ?>" dir="ltr" style="width:130px;">
						</label>
					</p>
					<p>
						<label>متغیر کد پیگیری:
							<input type="text" name="noghre_sms_options[sms_pattern_edit_var_peygiri]" value="<?php echo esc_attr( $o['sms_pattern_edit_var_peygiri'] ); ?>" dir="ltr" style="width:130px;">
						</label>
					</p>
				</td>
			</tr>
		</table>

		<h2 class="noghre-section-heading">
			<span class="dashicons dashicons-no"></span>
			۶. پیامک باطل شدن رزرو
		</h2>
		<table class="form-table">
			<tr>
				<th>فعال‌سازی ارسال</th>
				<td>
					<label>
						<input type="checkbox" name="noghre_sms_options[sms_void_enabled]" value="1" <?php checked( $o['sms_void_enabled'], 1 ); ?>>
						ارسال پیامک باطل شدن
					</label>
				</td>
			</tr>
			<tr>
				<th><label for="sms_pattern_booking_void">کد پترن</label></th>
				<td>
					<?php if ( $has_patterns ) : ?>
						<select id="sms_pattern_booking_void" name="noghre_sms_options[sms_pattern_booking_void]" class="noghre-pattern-select">
							<option value="">— انتخاب پترن —</option>
							<?php foreach ( $patterns as $p ) : ?>
								<option value="<?php echo esc_attr( $p['code'] ); ?>"
									data-message="<?php echo esc_attr( $p['message'] ); ?>"
									<?php selected( $o['sms_pattern_booking_void'], $p['code'] ); ?>>
									<?php echo esc_html( $p['code'] . ( $p['title'] && $p['title'] !== $p['code'] ? ' — ' . $p['title'] : '' ) ); ?>
								</option>
							<?php endforeach; ?>
						</select>
						<div class="noghre-field-desc">
							<span class="noghre-pattern-count"><?php echo esc_html( noghre_to_persian_digits( $total_patterns ) ); ?> پترن موجود.</span>
							<button type="button" class="button button-secondary noghre-refresh-btn" data-target="patterns">
								<span class="dashicons dashicons-update"></span> بروزرسانی لیست
							</button>
						</div>
					<?php else : ?>
						<input type="text" id="sms_pattern_booking_void" name="noghre_sms_options[sms_pattern_booking_void]" value="<?php echo esc_attr( $o['sms_pattern_booking_void'] ); ?>" class="regular-text" dir="ltr">
					<?php endif; ?>
				</td>
			</tr>
			<tr>
				<th>پیش‌نمایش متن پترن</th>
				<td>
					<div class="noghre-pattern-preview" id="preview-void" style="display:none;">
						<strong>پیش‌نمایش متن پترن:</strong>
						<div class="noghre-pattern-message" id="preview-void-text"></div>
					</div>
					<div class="noghre-pattern-preview-placeholder" id="placeholder-void">پس از انتخاب پترن، متن آن در اینجا نمایش داده می‌شود.</div>
				</td>
			</tr>
			<tr>
				<th>متغیرهای پترن</th>
				<td>
					<p>
						<label>متغیر نام مشتری:
							<input type="text" name="noghre_sms_options[sms_pattern_void_var_name]" value="<?php echo esc_attr( $o['sms_pattern_void_var_name'] ); ?>" dir="ltr" style="width:130px;">
						</label>
					</p>
				</td>
			</tr>
		</table>

		<h2 class="noghre-section-heading">
			<span class="dashicons dashicons-dismiss"></span>
			۷. پیامک رزرو استفاده نشده
		</h2>
		<table class="form-table">
			<tr>
				<th>فعال‌سازی ارسال</th>
				<td>
					<label>
						<input type="checkbox" name="noghre_sms_options[sms_unused_enabled]" value="1" <?php checked( $o['sms_unused_enabled'], 1 ); ?>>
						ارسال پیامک رزرو استفاده‌نشده
					</label>
				</td>
			</tr>
			<tr>
				<th><label for="sms_pattern_booking_unused">کد پترن</label></th>
				<td>
					<?php if ( $has_patterns ) : ?>
						<select id="sms_pattern_booking_unused" name="noghre_sms_options[sms_pattern_booking_unused]" class="noghre-pattern-select">
							<option value="">— انتخاب پترن —</option>
							<?php foreach ( $patterns as $p ) : ?>
								<option value="<?php echo esc_attr( $p['code'] ); ?>"
									data-message="<?php echo esc_attr( $p['message'] ); ?>"
									<?php selected( $o['sms_pattern_booking_unused'], $p['code'] ); ?>>
									<?php echo esc_html( $p['code'] . ( $p['title'] && $p['title'] !== $p['code'] ? ' — ' . $p['title'] : '' ) ); ?>
								</option>
							<?php endforeach; ?>
						</select>
						<div class="noghre-field-desc">
							<span class="noghre-pattern-count"><?php echo esc_html( noghre_to_persian_digits( $total_patterns ) ); ?> پترن موجود.</span>
							<button type="button" class="button button-secondary noghre-refresh-btn" data-target="patterns">
								<span class="dashicons dashicons-update"></span> بروزرسانی لیست
							</button>
						</div>
					<?php else : ?>
						<input type="text" id="sms_pattern_booking_unused" name="noghre_sms_options[sms_pattern_booking_unused]" value="<?php echo esc_attr( $o['sms_pattern_booking_unused'] ); ?>" class="regular-text" dir="ltr">
					<?php endif; ?>
				</td>
			</tr>
			<tr>
				<th>پیش‌نمایش متن پترن</th>
				<td>
					<div class="noghre-pattern-preview" id="preview-unused" style="display:none;">
						<strong>پیش‌نمایش متن پترن:</strong>
						<div class="noghre-pattern-message" id="preview-unused-text"></div>
					</div>
					<div class="noghre-pattern-preview-placeholder" id="placeholder-unused">پس از انتخاب پترن، متن آن در اینجا نمایش داده می‌شود.</div>
				</td>
			</tr>
			<tr>
				<th>متغیرهای پترن</th>
				<td>
					<p>
						<label>متغیر نام مشتری:
							<input type="text" name="noghre_sms_options[sms_pattern_unused_var_name]" value="<?php echo esc_attr( $o['sms_pattern_unused_var_name'] ); ?>" dir="ltr" style="width:130px;">
						</label>
					</p>
					<p>
						<label>متغیر کد تخفیف:
							<input type="text" name="noghre_sms_options[sms_pattern_unused_discount_var]" value="<?php echo esc_attr( $o['sms_pattern_unused_discount_var'] ); ?>" dir="ltr" style="width:130px;">
						</label>
					</p>
					<p>
						<label>مقدار کد تخفیف:
							<input type="text" name="noghre_sms_options[sms_pattern_unused_discount_val]" value="<?php echo esc_attr( $o['sms_pattern_unused_discount_val'] ); ?>" dir="ltr" style="width:200px;" placeholder="مثلاً: OFF-RETURN">
						</label>
					</p>
				</td>
			</tr>
		</table>

		<h2 class="noghre-section-heading">
			<span class="dashicons dashicons-admin-users"></span>
			۸. پیامک ثبت شماره مشتری (اپ موبایل)
		</h2>
		<p style="margin:0 0 14px;color:#646970;font-size:13px;">
			این پیامک زمانی ارسال می‌شود که مدیر از طریق اپ موبایل، شماره یک مشتری را در دفترچه «باشگاه مشتریان» ثبت کند.
		</p>
		<table class="form-table">
			<tr>
				<th>فعال‌سازی ارسال</th>
				<td>
					<label>
						<input type="checkbox" name="noghre_sms_options[sms_signup_enabled]" value="1" <?php checked( $o['sms_signup_enabled'], 1 ); ?>>
						ارسال پیامک ثبت شماره
					</label>
				</td>
			</tr>
			<tr>
				<th><label for="sms_pattern_signup">کد پترن</label></th>
				<td>
					<?php if ( $has_patterns ) : ?>
						<select id="sms_pattern_signup" name="noghre_sms_options[sms_pattern_signup]" class="noghre-pattern-select">
							<option value="">— انتخاب پترن —</option>
							<?php foreach ( $patterns as $p ) : ?>
								<option value="<?php echo esc_attr( $p['code'] ); ?>"
									data-message="<?php echo esc_attr( $p['message'] ); ?>"
									<?php selected( $o['sms_pattern_signup'], $p['code'] ); ?>>
									<?php echo esc_html( $p['code'] . ( $p['title'] && $p['title'] !== $p['code'] ? ' — ' . $p['title'] : '' ) ); ?>
								</option>
							<?php endforeach; ?>
						</select>
						<div class="noghre-field-desc">
							<span class="noghre-pattern-count"><?php echo esc_html( noghre_to_persian_digits( $total_patterns ) ); ?> پترن موجود.</span>
							<button type="button" class="button button-secondary noghre-refresh-btn" data-target="patterns">
								<span class="dashicons dashicons-update"></span> بروزرسانی لیست
							</button>
						</div>
					<?php else : ?>
						<input type="text" id="sms_pattern_signup" name="noghre_sms_options[sms_pattern_signup]" value="<?php echo esc_attr( $o['sms_pattern_signup'] ); ?>" class="regular-text" dir="ltr">
					<?php endif; ?>
				</td>
			</tr>
			<tr>
				<th>پیش‌نمایش متن پترن</th>
				<td>
					<div class="noghre-pattern-preview" id="preview-signup" style="display:none;">
						<strong>پیش‌نمایش متن پترن:</strong>
						<div class="noghre-pattern-message" id="preview-signup-text"></div>
					</div>
					<div class="noghre-pattern-preview-placeholder" id="placeholder-signup">پس از انتخاب پترن، متن آن در اینجا نمایش داده می‌شود.</div>
				</td>
			</tr>
			<tr>
				<th>متغیر نام مشتری</th>
				<td>
					<input type="text" name="noghre_sms_options[sms_pattern_signup_var_name]" value="<?php echo esc_attr( $o['sms_pattern_signup_var_name'] ); ?>" class="regular-text" dir="ltr" placeholder="name">
				</td>
			</tr>
		</table>

		<?php submit_button( 'ذخیره تنظیمات پیامک' ); ?>
	</form>

	<h2 class="noghre-section-heading">
		<span class="dashicons dashicons-email"></span>
		تست ارسال پیامک
	</h2>
	<div class="notice notice-info inline" style="margin:12px 0;">
		<table class="form-table" style="margin:0;">
			<tr>
				<th style="width:180px;"><label for="noghre-test-mobile">شماره تست</label></th>
				<td><input type="text" id="noghre-test-mobile" class="regular-text" dir="ltr" placeholder="09xxxxxxxxx" maxlength="11" inputmode="numeric" style="max-width:200px;"></td>
			</tr>
			<tr>
				<th>عملیات</th>
				<td>
					<button type="button" class="button button-primary noghre-test-sms-btn" data-type="discount">تست پترن تخفیف</button>
					<button type="button" class="button noghre-test-sms-btn" data-type="customer" style="margin-inline-start:8px;">تست پترن مشتری</button>
					<button type="button" class="button noghre-test-sms-btn" data-type="branch" style="margin-inline-start:8px;">تست پترن مدیر</button>
					<button type="button" class="button noghre-test-sms-btn" data-type="confirm" style="margin-inline-start:8px;">تست پترن تایید</button>
					<button type="button" class="button noghre-test-sms-btn" data-type="edit" style="margin-inline-start:8px;">تست پترن ویرایش</button>
					<button type="button" class="button noghre-test-sms-btn" data-type="void" style="margin-inline-start:8px;">تست پترن باطل</button>
					<button type="button" class="button noghre-test-sms-btn" data-type="unused" style="margin-inline-start:8px;">تست پترن استفاده‌نشده</button>
					<button type="button" class="button noghre-test-sms-btn" data-type="signup" style="margin-inline-start:8px;">تست پترن ثبت شماره</button>
				</td>
			</tr>
			<tr>
				<th></th>
				<td><div id="noghre-test-sms-result" style="margin-top:10px;padding:10px 14px;border-radius:4px;display:none;"></div></td>
			</tr>
		</table>
	</div>

	<h2 class="noghre-section-heading">
		<span class="dashicons dashicons-admin-users"></span>
		ثبت‌نام‌های اخیر باشگاه تخفیف
	</h2>
	<?php
	$signups = get_option( 'noghre_discount_signups', array() );
	$signups = array_reverse( (array) $signups );
	$signups = array_slice( $signups, 0, 50 );
	?>
	<?php if ( ! empty( $signups ) ) : ?>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin-bottom:10px;">
			<?php wp_nonce_field( 'noghre_clear_discount_signups' ); ?>
			<input type="hidden" name="action" value="noghre_clear_discount_signups">
			<button type="submit" class="button" style="color:#d63638;border-color:#d63638;" onclick="return confirm('آیا مطمئن هستید؟');">حذف تاریخچه</button>
		</form>
	<?php endif; ?>
	<table class="widefat striped" style="max-width:680px;">
		<thead><tr><th>نام</th><th>موبایل</th><th>تاریخ</th><th>وضعیت ارسال پیامک</th></tr></thead>
		<tbody>
			<?php if ( empty( $signups ) ) : ?>
				<tr><td colspan="4">هنوز ثبت‌نامی انجام نشده است.</td></tr>
			<?php else : ?>
				<?php foreach ( $signups as $s ) : ?>
					<tr>
						<td><?php echo esc_html( $s['name'] ?? '' ); ?></td>
						<td dir="ltr"><?php echo esc_html( $s['mobile'] ?? '' ); ?></td>
						<td><?php echo esc_html( is_numeric( $s['date'] ?? '' ) ? noghre_jalali_date( $s['date'], 'j F Y - H:i' ) : ( $s['date'] ?? '' ) ); ?></td>
						<td>
							<?php if ( ! empty( $s['sms_sent'] ) ) : ?>
								<span class="dashicons dashicons-yes-alt" style="color:#00a32a;font-size:16px;width:16px;height:16px;"></span> ارسال شد
							<?php elseif ( $s['sms_sent'] === null ) : ?>
								<span class="dashicons dashicons-marker" style="color:#646970;font-size:16px;width:16px;height:16px;"></span> غیرفعال
							<?php else : ?>
								<span class="dashicons dashicons-dismiss" style="color:#d63638;font-size:16px;width:16px;height:16px;"></span> ناموفق
							<?php endif; ?>
						</td>
					</tr>
				<?php endforeach; ?>
			<?php endif; ?>
		</tbody>
	</table>
	<?php
}