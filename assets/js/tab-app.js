/* ==========================================================================
   Noghre Admin — Tab: Application
   منطق تب اپلیکیشن (منوهای پایین اپ)
   وابسته به: jQuery, wp.media
   ========================================================================== */
jQuery(function ($) {
	'use strict';

	/* ---------- افزودن ردیف جدید ---------- */
	$('#noghre-app-add-menu').on('click', function () {
		var template = $('#noghre-app-menu-row-template').html();
		if (!template) return;
		var newIndex = 'new_' + Date.now();
		var html = template.replace(/__INDEX__/g, newIndex);
		$('#noghre-app-menus-container').append(html);
	});

	/* ---------- حذف ردیف ---------- */
	$(document).on('click', '.noghre-app-menu-row .noghre-remove-menu', function () {
		if (!confirm('آیا از حذف این منو مطمئن هستید؟')) return;
		$(this).closest('.noghre-app-menu-row').remove();
	});

	/* ---------- نمایش/پنهان فیلدهای نوع URL ---------- */
	function toggleMenuFields(row) {
		var type = row.find('.noghre-app-url-type').val();
		var $shortcode = row.find('.noghre-app-shortcode-wrap');
		var $url = row.find('.noghre-app-url-wrap');

		if (type === 'internal') {
			$shortcode.show();
			$url.hide();
		} else if (type === 'external') {
			$shortcode.hide();
			$url.show();
		} else {
			$shortcode.hide();
			$url.hide();
		}
	}

	$(document).on('change', '.noghre-app-url-type', function () {
		toggleMenuFields($(this).closest('.noghre-app-menu-row'));
	});

	$('.noghre-app-menu-row').each(function () {
		toggleMenuFields($(this));
	});

	/* ---------- پیش‌نمایش زنده dashicon ---------- */
	$(document).on('change', '.noghre-app-icon-select', function () {
		var $row = $(this).closest('.noghre-app-menu-row');
		var iconClass = $(this).val();

		var $preview = $row.find('.noghre-app-icon-preview-dashicon');
		if ($preview.length) {
			$preview.attr('class', 'noghre-app-icon-preview-dashicon dashicons ' + iconClass);
		}

		// پاک کردن مقدار عکس (چون dashicon انتخاب شده)
		$row.find('.noghre-app-icon-input').val('');
	});

	/* ---------- انتخاب عکس به‌جای dashicon ---------- */
	$(document).on('click', '.noghre-app-icon-picker', function (e) {
		e.preventDefault();
		var $row = $(this).closest('.noghre-app-menu-row');

		var frame = wp.media({
			title: 'انتخاب آیکون از کتابخانه رسانه',
			multiple: false,
			library: { type: 'image' }
		});

		frame.on('select', function () {
			var att = frame.state().get('selection').first().toJSON();
			var url = (att.sizes && att.sizes.thumbnail) ? att.sizes.thumbnail.url : att.url;

			var $iconWrap = $row.find('.noghre-app-icon-wrap');

			// پاک کردن حالت dashicon قبلی
			$iconWrap.find('.noghre-app-icon-preview-dashicon').remove();
			$iconWrap.find('.noghre-app-icon-select').remove();
			$iconWrap.find('.noghre-app-icon-preview').remove();
			$iconWrap.find('.noghre-app-icon-clear').remove();
			$iconWrap.find('.noghre-app-icon-input').remove();

			// اضافه کردن پیش‌نمایش عکس
			var $img = $('<img>', {
				src: url,
				class: 'noghre-app-icon-preview',
				alt: ''
			});

			var $input = $('<input>', {
				type: 'hidden',
				name: getIconFieldName($row),
				value: att.id,
				class: 'noghre-app-icon-input'
			});

			var $clearBtn = $('<button>', {
				type: 'button',
				class: 'button button-small noghre-app-icon-clear',
				title: 'حذف عکس و بازگشت به dashicon'
			}).html('<span class="dashicons dashicons-no-alt"></span>');

			// اضافه کردن پیش از دکمه انتخاب عکس
			var $pickerBtn = $iconWrap.find('.noghre-app-icon-picker');
			$pickerBtn.before($clearBtn);
			$pickerBtn.before($input);
			$pickerBtn.before($img);
		});

		frame.open();
	});

	/* ---------- حذف عکس و بازگشت به dashicon ---------- */
	$(document).on('click', '.noghre-app-icon-clear', function (e) {
		e.preventDefault();
		var $row = $(this).closest('.noghre-app-menu-row');
		var $iconWrap = $row.find('.noghre-app-icon-wrap');

		// حذف المان‌های عکس
		$iconWrap.find('.noghre-app-icon-preview').remove();
		$iconWrap.find('.noghre-app-icon-input').remove();
		$iconWrap.find('.noghre-app-icon-clear').remove();

		// اضافه کردن select dashicon
		var fieldName = getIconFieldName($row);
		var $select = $('<select>', {
			name: fieldName,
			class: 'noghre-app-icon-select'
		});

		var defaultIcons = getDashiconsList();
		$.each(defaultIcons, function (val, label) {
			var $opt = $('<option>', { value: val, text: label });
			if (val === 'dashicons-admin-generic') $opt.attr('selected', 'selected');
			$select.append($opt);
		});

		var $preview = $('<span>', {
			class: 'noghre-app-icon-preview-dashicon dashicons dashicons-admin-generic'
		});

		var $pickerBtn = $iconWrap.find('.noghre-app-icon-picker');
		$pickerBtn.before($select);
		$pickerBtn.before($preview);
	});

	/* ---------- helper: گرفتن نام فیلد آیکون ---------- */
	function getIconFieldName($row) {
		var idName = $row.find('input[name*="[id]"]').attr('name') || '';
		return idName.replace('[id]', '[icon]');
	}

	/* ---------- helper: لیست dashiconها (همون لیست PHP) ---------- */
	function getDashiconsList() {
		return {
			'dashicons-admin-home': 'خانه',
			'dashicons-admin-generic': 'تنظیمات',
			'dashicons-admin-site': 'سایت',
			'dashicons-admin-users': 'کاربران',
			'dashicons-admin-tools': 'ابزار',
			'dashicons-dashboard': 'داشبورد',
			'dashicons-calendar-alt': 'تقویم',
			'dashicons-list-view': 'لیست',
			'dashicons-email': 'ایمیل',
			'dashicons-email-alt': 'ایمیل ۲',
			'dashicons-smartphone': 'موبایل',
			'dashicons-camera': 'دوربین',
			'dashicons-search': 'جستجو',
			'dashicons-plus': 'افزودن',
			'dashicons-yes': 'تایید',
			'dashicons-no': 'رد',
			'dashicons-info': 'اطلاعات',
			'dashicons-warning': 'هشدار',
			'dashicons-star-filled': 'ستاره',
			'dashicons-heart': 'قلب',
			'dashicons-tag': 'برچسب',
			'dashicons-cart': 'سبد خرید',
			'dashicons-money-alt': 'پول',
			'dashicons-chart-bar': 'نمودار',
			'dashicons-chart-line': 'خط نمودار',
			'dashicons-groups': 'گروه‌ها',
			'dashicons-businessperson': 'شخص',
			'dashicons-phone': 'تلفن',
			'dashicons-format-image': 'تصویر',
			'dashicons-format-gallery': 'گالری',
			'dashicons-visibility': 'مشاهده',
			'dashicons-location': 'مکان',
			'dashicons-clock': 'ساعت',
			'dashicons-lock': 'قفل',
			'dashicons-unlock': 'باز کردن',
			'dashicons-editor-help': 'راهنما',
			'dashicons-download': 'دانلود',
			'dashicons-upload': 'آپلود',
			'dashicons-share': 'اشتراک‌گذاری',
			'dashicons-menu': 'منو',
			'dashicons-screenoptions': 'گزینه‌ها'
		};
	}

	/* ---------- راه‌اندازی color picker ---------- */
	if ($.fn.wpColorPicker) {
		$('.noghre-color-field').wpColorPicker();
	}
});