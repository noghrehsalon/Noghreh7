/* ==========================================================================
   Noghre Admin Panel — JS
   منطق پنل تنظیمات نقره
   وابسته به: jQuery (wp-admin)
   ========================================================================== */
jQuery(function ($) {
	'use strict';

	/* ---------- پیکربندی ---------- */
	var cfg = window.noghreAdminConfig || {};
	var ajaxUrl         = cfg.ajaxUrl || window.ajaxurl || '';
	var refreshSmsNonce = cfg.refreshSmsNonce || '';
	var testSmsNonce    = cfg.testSmsNonce || '';

	/* ---------- بستن خودکار پیام‌ها ---------- */
	$('.noghre-message[data-autoclose]').each(function () {
		var $msg = $(this);
		var timeout = parseInt($msg.data('autoclose'), 10) || 5000;

		var closeMsg = function () {
			if ($msg.hasClass('is-closing')) return;
			$msg.addClass('is-closing');
			setTimeout(function () { $msg.remove(); }, 300);
		};

		var timer = setTimeout(closeMsg, timeout);

		$msg.find('.noghre-message__close').on('click', function () {
			clearTimeout(timer);
			closeMsg();
		});
	});

	/* ---------- چک‌باکس‌ها (استاندارد وردپرس) ---------- */
	// در وردپرس چک‌باکس‌ها به‌صورت پیش‌فرض کار می‌کنن. نیازی به JS خاصی نیست.

	/* ---------- پیش‌نمایش پترن ---------- */
	function updatePatternPreview(selectId, previewId, placeholderId, textId) {
		var $select = $('#' + selectId);
		if (!$select.is('select')) return;
		var $selected = $select.find('option:selected');
		var message = $selected.data('message') || '';
		if ($selected.val() && message) {
			$('#' + textId).text(message);
			$('#' + previewId).show();
			$('#' + placeholderId).hide();
		} else {
			$('#' + previewId).hide();
			$('#' + placeholderId).show();
		}
	}

	var patternPairs = [
		['sms_pattern_code', 'preview-discount', 'placeholder-discount', 'preview-discount-text'],
		['sms_pattern_booking_customer', 'preview-customer', 'placeholder-customer', 'preview-customer-text'],
		['sms_pattern_booking_branch', 'preview-branch', 'placeholder-branch', 'preview-branch-text'],
		['sms_pattern_booking_confirm', 'preview-confirm', 'placeholder-confirm', 'preview-confirm-text'],
		['sms_pattern_booking_edit', 'preview-edit', 'placeholder-edit', 'preview-edit-text'],
		['sms_pattern_booking_void', 'preview-void', 'placeholder-void', 'preview-void-text'],
		['sms_pattern_booking_unused', 'preview-unused', 'placeholder-unused', 'preview-unused-text'],
		['sms_pattern_signup', 'preview-signup', 'placeholder-signup', 'preview-signup-text']
	];

	patternPairs.forEach(function (pair) {
		$('#' + pair[0]).on('change', function () {
			updatePatternPreview(pair[0], pair[1], pair[2], pair[3]);
		});
		updatePatternPreview(pair[0], pair[1], pair[2], pair[3]);
	});

	/* ---------- بروزرسانی لیست‌های پیامک ---------- */
	$(document).on('click', '.noghre-refresh-btn', function (e) {
		e.preventDefault();

		if (!ajaxUrl) {
			alert('آدرس AJAX تعریف نشده است.');
			return;
		}

		var $btn = $(this);
		var originalText = $btn.html();

		$btn.addClass('loading').prop('disabled', true).html('<span class="dashicons dashicons-update" style="vertical-align:middle;"></span> در حال دریافت...');

		$.post(ajaxUrl, {
			action: 'noghre_refresh_sms_lists',
			nonce: refreshSmsNonce
		}, function (response) {
			$btn.removeClass('loading').prop('disabled', false).html(originalText);

			if (!response || !response.success) {
				var msg = (response && response.data && response.data.message) ? response.data.message : 'خطا در دریافت اطلاعات.';
				alert(msg);
				return;
			}

			location.reload();
		}).fail(function () {
			$btn.removeClass('loading').prop('disabled', false).html(originalText);
			alert('خطا در ارتباط با سرور.');
		});
	});

	/* ---------- تست ارسال پیامک ---------- */
	$(document).on('click', '.noghre-test-sms-btn', function () {
		if (!ajaxUrl) {
			alert('آدرس AJAX تعریف نشده است.');
			return;
		}

		var $btn = $(this);
		var type = $btn.data('type');
		var mobile = $('#noghre-test-mobile').val().trim();
		var $result = $('#noghre-test-sms-result');

		$result.hide().removeClass('notice-success notice-error').text('');

		if (!/^09\d{9}$/.test(mobile)) {
			$result.addClass('notice notice-error').css('display', 'block').text('شماره موبایل باید ۱۱ رقم و با ۰۹ شروع شود.').show();
			return;
		}

		$btn.prop('disabled', true);
		var originalText = $btn.text();
		$btn.text('در حال ارسال...');

		$.post(ajaxUrl, {
			action: 'noghre_test_sms',
			nonce: testSmsNonce,
			mobile: mobile,
			type: type
		}, function (response) {
			$btn.prop('disabled', false).text(originalText);
			if (response && response.success) {
				$result.addClass('notice notice-success').css('display', 'block').text(response.data.message).show();
			} else {
				var msg = (response && response.data && response.data.message) ? response.data.message : 'خطا در ارسال.';
				$result.addClass('notice notice-error').css('display', 'block').text(msg).show();
			}
		}).fail(function () {
			$btn.prop('disabled', false).text(originalText);
			$result.addClass('notice notice-error').css('display', 'block').text('ارتباط با سرور برقرار نشد.').show();
		});
	});

	/* ---------- رنگ‌ها ---------- */
	$(document).on('click', '.noghre-color-grid .noghre-color-option', function () {
		var $grid = $(this).closest('.noghre-color-grid');
		$grid.find('.noghre-color-option').removeClass('active');
		$(this).addClass('active');
		$(this).find('input[type="radio"]').prop('checked', true);
	});

	$('#noghre-reset-colors').on('click', function () {
		if (!confirm('آیا از بازگشت همه رنگ‌ها به حالت پیش‌فرض مطمئن هستید؟')) return;
		$('.noghre-color-field').each(function () {
			var def = $(this).data('default-color');
			if (def) {
				$(this).val(def);
				if ($(this).hasClass('wp-color-picker')) {
					$(this).wpColorPicker('color', def);
				}
			}
		});
	});

	/* ---------- چیدمان بخش‌ها (sortable) ---------- */
	if ($('#noghre-sections-list').length) {
		$('#noghre-sections-list').sortable({
			handle: '.noghre-drag-handle',
			axis: 'y',
			update: function () {
				var order = $(this).find('.noghre-sortable-item').map(function () {
					return $(this).data('key');
				}).get().join(',');
				$('#noghre-section-order').val(order);
			}
		});
	}
});