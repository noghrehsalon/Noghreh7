/* ==========================================================================
   Noghre Admin — Tab: Booking
   منطق تب رزرو وقت (کد تخفیف، انتخاب خدمات)
   وابسته به: jQuery
   ========================================================================== */
jQuery(function ($) {
	'use strict';

	function toggleServicesRow() {
		$('.noghre-dc-services-row').toggle($('.noghre-dc-scope-booking').is(':checked'));
	}
	function toggleServiceDropdown() {
		$('.noghre-dc-service-dropdown').toggle(!$('.noghre-dc-all-services').is(':checked'));
	}
	function toggleMaxUses() {
		$('.noghre-dc-max-uses-row').toggle(!$('#dc_unlimited').is(':checked'));
	}

	$('.noghre-dc-scope-booking').on('change', toggleServicesRow);
	$('.noghre-dc-all-services').on('change', toggleServiceDropdown);
	$('#dc_unlimited').on('change', toggleMaxUses);
});