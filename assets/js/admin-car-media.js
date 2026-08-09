( function( $ ) {
	'use strict';

	/* ---------------- Single image fields ---------------- */

	$( document ).on( 'click', '.afq-image-upload', function( e ) {
		e.preventDefault();

		var $button  = $( this );
		var $wrapper = $button.closest( '.afq-media-card' );
		var $input   = $wrapper.find( '.afq-image-id' );
		var $preview = $wrapper.find( '.afq-image-preview' );

		var frame = wp.media( {
			title: 'انتخاب تصویر',
			button: { text: 'استفاده از این تصویر' },
			library: { type: 'image' },
			multiple: false
		} );

		frame.on( 'select', function() {
			var attachment = frame.state().get( 'selection' ).first().toJSON();
			var url = ( attachment.sizes && attachment.sizes.medium ) ? attachment.sizes.medium.url : attachment.url;

			$input.val( attachment.id );
			$preview.addClass( 'has-image' ).find( 'img' ).remove();
			$preview.prepend( '<img src="' + url + '" alt="" />' );
			$wrapper.find( '.afq-image-remove' ).show();
		} );

		frame.open();
	} );

	$( document ).on( 'click', '.afq-image-remove', function( e ) {
		e.preventDefault();

		var $button  = $( this );
		var $wrapper = $button.closest( '.afq-media-card' );

		$wrapper.find( '.afq-image-id' ).val( '' );
		$wrapper.find( '.afq-image-preview' ).removeClass( 'has-image' ).find( 'img' ).remove();
		$button.hide();
	} );

	/* ---------------- Gallery ---------------- */

	function afqGallerySync( $gallery ) {
		var ids = [];

		$gallery.find( '.afq-gallery__item' ).each( function() {
			ids.push( $( this ).data( 'id' ) );
		} );

		$gallery.find( '.afq-gallery-ids' ).val( ids.join( ',' ) );
		$gallery.find( '.afq-gallery__empty' ).toggle( ids.length === 0 );
	}

	$( document ).on( 'click', '.afq-gallery-add', function( e ) {
		e.preventDefault();

		var $gallery = $( this ).closest( '.afq-gallery' );
		var $grid    = $gallery.find( '.afq-gallery__grid' );

		var frame = wp.media( {
			title: 'افزودن تصاویر به گالری',
			button: { text: 'افزودن به گالری' },
			library: { type: 'image' },
			multiple: 'add'
		} );

		frame.on( 'select', function() {
			var selection = frame.state().get( 'selection' );

			selection.each( function( attachment ) {
				attachment = attachment.toJSON();

				if ( $grid.find( '.afq-gallery__item[data-id="' + attachment.id + '"]' ).length ) {
					return;
				}

				var url = ( attachment.sizes && attachment.sizes.thumbnail )
					? attachment.sizes.thumbnail.url
					: attachment.url;

				$grid.append(
					'<li class="afq-gallery__item" data-id="' + attachment.id + '">' +
						'<img src="' + url + '" alt="" />' +
						'<button type="button" class="afq-gallery__remove" aria-label="حذف">&times;</button>' +
					'</li>'
				);
			} );

			afqGallerySync( $gallery );
		} );

		frame.open();
	} );

	$( document ).on( 'click', '.afq-gallery__remove', function( e ) {
		e.preventDefault();

		var $gallery = $( this ).closest( '.afq-gallery' );

		$( this ).closest( '.afq-gallery__item' ).remove();
		afqGallerySync( $gallery );
	} );

	$( function() {
		$( '.afq-gallery__grid' ).sortable( {
			items: '.afq-gallery__item',
			tolerance: 'pointer',
			update: function() {
				afqGallerySync( $( this ).closest( '.afq-gallery' ) );
			}
		} );
	} );

	/* ---------------- Term screen: reset after AJAX add ---------------- */

	$( document ).on( 'ajaxComplete', function( event, xhr, settings ) {
		if ( settings.data && settings.data.indexOf( 'action=add-tag' ) !== -1 ) {
			var $field = $( '.afq-term-image-field' );
			$field.find( '.afq-image-id' ).val( '' );
			$field.find( '.afq-image-preview' ).removeClass( 'has-image' ).find( 'img' ).remove();
			$field.find( '.afq-image-remove' ).hide();
		}
	} );

} )( jQuery );
