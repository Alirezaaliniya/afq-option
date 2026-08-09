( function( $ ) {
	'use strict';

	/* Customer image */

	$( document ).on( 'click', '.afq-voice-image-upload', function( e ) {
		e.preventDefault();

		var $wrapper = $( this ).closest( '.afq-voice-image-field' );
		var $input   = $wrapper.find( '.afq-voice-image-id' );
		var $preview = $wrapper.find( '.afq-voice-admin__preview' );

		var frame = wp.media( {
			title: 'انتخاب تصویر مشتری',
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
			$wrapper.find( '.afq-voice-image-remove' ).show();
		} );

		frame.open();
	} );

	$( document ).on( 'click', '.afq-voice-image-remove', function( e ) {
		e.preventDefault();

		var $wrapper = $( this ).closest( '.afq-voice-image-field' );

		$wrapper.find( '.afq-voice-image-id' ).val( '' );
		$wrapper.find( '.afq-voice-admin__preview' ).removeClass( 'has-image' ).find( 'img' ).remove();
		$( this ).hide();
	} );

	/* Audio / video URL pickers */

	$( document ).on( 'click', '.afq-voice-media-select', function( e ) {
		e.preventDefault();

		var $wrapper = $( this ).closest( '.afq-voice-media-field' );
		var $input   = $wrapper.find( '.afq-voice-media-url' );
		var type     = $wrapper.data( 'media-type' ) || '';

		var frame = wp.media( {
			title: 'انتخاب فایل',
			button: { text: 'استفاده از این فایل' },
			library: type ? { type: type } : {},
			multiple: false
		} );

		frame.on( 'select', function() {
			var attachment = frame.state().get( 'selection' ).first().toJSON();

			$input.val( attachment.url );
			$wrapper.find( '.afq-voice-media-clear' ).show();
		} );

		frame.open();
	} );

	$( document ).on( 'click', '.afq-voice-media-clear', function( e ) {
		e.preventDefault();

		var $wrapper = $( this ).closest( '.afq-voice-media-field' );

		$wrapper.find( '.afq-voice-media-url' ).val( '' );
		$( this ).hide();
	} );

	$( document ).on( 'input', '.afq-voice-media-url', function() {
		var $wrapper = $( this ).closest( '.afq-voice-media-field' );
		$wrapper.find( '.afq-voice-media-clear' ).toggle( '' !== $.trim( $( this ).val() ) );
	} );

} )( jQuery );
