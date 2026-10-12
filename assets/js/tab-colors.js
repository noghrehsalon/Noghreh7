/* ==========================================================================
   Noghre Admin — Tab: Colors
   منطق تب رنگ‌بندی
   وابسته به: jQuery, wp-color-picker
   ========================================================================== */
jQuery(function ($) {
	'use strict';

	function initColorPickers() {
		$('.noghre-color-field').each(function () {
			var $field = $(this);
			if ($field.hasClass('wp-color-picker-processed')) return;
			if (!$.fn.wpColorPicker) return;

			$field.addClass('wp-color-picker-processed');
			$field.wpColorPicker();
		});
	}

	initColorPickers();

	// در صورتی که تب‌های دیگر بعداً بارگذاری شدند
	$(document).on('click', '.noghre-subtab', function () {
		setTimeout(initColorPickers, 100);
	});

	$('#noghre-reset-colors').on('click', function () {
		$('.noghre-color-field').each(function () {
			var def = $(this).data('defaultColor') || $(this).data('default-color');
			if (def) {
				$(this).val(def);
				if ($(this).hasClass('wp-color-picker-processed')) {
					$(this).wpColorPicker('color', def);
				}
			}
		});
	});
});