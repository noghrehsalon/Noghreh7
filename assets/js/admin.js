jQuery( function ( $ ) {
	'use strict';

	var rowCounter = Date.now();

	/* ---------------- آپلودر رسانه (تصویر معرفی، پرسنل، مجوزها) ---------------- */
	function bindMedia( context ) {
		$( context ).find( '.noghre-media-select' ).off( 'click.noghre' ).on( 'click.noghre', function ( e ) {
			e.preventDefault();
			var wrap  = $( this ).closest( '.noghre-media-field' );
			var frame = wp.media( { title: 'انتخاب تصویر', multiple: false } );

			frame.on( 'select', function () {
				var attachment = frame.state().get( 'selection' ).first().toJSON();
				wrap.find( '.noghre-media-id' ).val( attachment.id );
				wrap.find( '.noghre-media-preview' )
					.attr( 'src', ( attachment.sizes && attachment.sizes.thumbnail ) ? attachment.sizes.thumbnail.url : attachment.url )
					.show();
			} );

			frame.open();
		} );

		$( context ).find( '.noghre-media-remove' ).off( 'click.noghre' ).on( 'click.noghre', function ( e ) {
			e.preventDefault();
			var wrap = $( this ).closest( '.noghre-media-field' );
			wrap.find( '.noghre-media-id' ).val( 0 );
			wrap.find( '.noghre-media-preview' ).hide().attr( 'src', '' );
		} );
	}

	bindMedia( document );

	/* ---------------- افزودن ردیف جدید در تکرارگرها ---------------- */
	$( '.noghre-add-row' ).on( 'click', function () {
		var target = $( $( this ).data( 'target' ) );
		var wrapperId  = target.attr( 'id' );
		var templateSelector = '#' + wrapperId.replace( '-repeater', '' ) + '-row-template';
		var template = $( templateSelector ).html();

		if ( ! template ) return;

		rowCounter++;
		var html = template.replace( /__INDEX__/g, 'new_' + rowCounter );
		var $row = $( html );

		target.find( '.noghre-repeater-rows' ).append( $row );
		bindMedia( $row );
	} );

	/* ---------------- حذف ردیف (تکرارگرهای عمومی + لیست بخش‌های صفحه اصلی) ---------------- */
	$( document ).on( 'click', '.noghre-remove-row', function () {
		var $repeaterRow = $( this ).closest( '.noghre-repeater-row' );
		if ( $repeaterRow.length ) {
			$repeaterRow.remove();
			return;
		}
		var $sectionItem = $( this ).closest( '.noghre-sortable-item' );
		if ( $sectionItem.length ) {
			$sectionItem.remove();
			$( document ).trigger( 'noghre:sectionsChanged' );
		}
	} );

	/* ---------------- تغییر نوع مجوز: تصویر/کد ---------------- */
	function toggleLicenseType( row ) {
		var type = row.find( '.noghre-license-type' ).val();
		row.find( '.noghre-license-image-fields' ).toggle( type === 'image' );
		row.find( '.noghre-license-code-fields' ).toggle( type === 'code' );
	}
	$( document ).on( 'change', '.noghre-license-type', function () {
		toggleLicenseType( $( this ).closest( '.noghre-repeater-row' ) );
	} );
	$( '.noghre-license-type' ).each( function () {
		toggleLicenseType( $( this ).closest( '.noghre-repeater-row' ) );
	} );

	/* ---------------- درگ‌ودراپ چیدمان صفحه اصلی + افزودن بخش‌های صفحه‌ساز ---------------- */
	var sectionsList = $( '#noghre-sections-list' );
	if ( sectionsList.length ) {
		function updateSectionOrder() {
			var order = sectionsList.find( '.noghre-sortable-item' ).map( function () {
				return $( this ).data( 'key' );
			} ).get().join( ',' );
			$( '#noghre-section-order' ).val( order );
		}
		sectionsList.sortable( {
			handle: '.noghre-drag-handle',
			axis: 'y',
			update: updateSectionOrder
		} );
		updateSectionOrder();

		$( document ).on( 'noghre:sectionsChanged', updateSectionOrder );

		$( '#noghre-add-page-builder' ).on( 'click', function () {
			var template = $( '#noghre-page-builder-row-template' ).html();
			if ( ! template ) return;
			rowCounter++;
			var newId = 'page_builder_' + rowCounter;
			var html = template.replace( /__ID__/g, newId );
			sectionsList.append( html );
			updateSectionOrder();
		} );
	}

} );