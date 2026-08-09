( function( $ ) {
	'use strict';

	/* ---------------- Catalog file ---------------- */

	$( document ).on( 'click', '.afq-file-upload', function( e ) {
		e.preventDefault();

		var $wrapper = $( this ).closest( '.afq-file-field' );
		var $input   = $wrapper.find( '.afq-file-id' );
		var $box     = $wrapper.find( '.afq-file-box' );

		var frame = wp.media( {
			title: 'انتخاب فایل کاتالوگ',
			button: { text: 'استفاده از این فایل' },
			multiple: false
		} );

		frame.on( 'select', function() {
			var attachment = frame.state().get( 'selection' ).first().toJSON();

			$input.val( attachment.id );
			$box.addClass( 'has-file' ).find( '.afq-file-name' ).text( attachment.filename || attachment.title );
			$wrapper.find( '.afq-file-remove' ).show();
		} );

		frame.open();
	} );

	$( document ).on( 'click', '.afq-file-remove', function( e ) {
		e.preventDefault();

		var $wrapper = $( this ).closest( '.afq-file-field' );

		$wrapper.find( '.afq-file-id' ).val( '' );
		$wrapper.find( '.afq-file-box' ).removeClass( 'has-file' ).find( '.afq-file-name' ).text( 'فایلی انتخاب نشده' );
		$( this ).hide();
	} );

	/* ---------------- Video ---------------- */

	$( document ).on( 'click', '.afq-video-select', function( e ) {
		e.preventDefault();

		var $wrapper = $( this ).closest( '.afq-video-field' );
		var $input   = $wrapper.find( '.afq-video-url' );

		var frame = wp.media( {
			title: 'انتخاب ویدیوی معرفی',
			button: { text: 'استفاده از این ویدیو' },
			library: { type: 'video' },
			multiple: false
		} );

		frame.on( 'select', function() {
			var attachment = frame.state().get( 'selection' ).first().toJSON();

			$input.val( attachment.url );
			$wrapper.find( '.afq-video-clear' ).show();
		} );

		frame.open();
	} );

	$( document ).on( 'click', '.afq-video-clear', function( e ) {
		e.preventDefault();

		var $wrapper = $( this ).closest( '.afq-video-field' );

		$wrapper.find( '.afq-video-url' ).val( '' );
		$( this ).hide();
	} );

	$( document ).on( 'input', '.afq-video-url', function() {
		var $wrapper = $( this ).closest( '.afq-video-field' );
		$wrapper.find( '.afq-video-clear' ).toggle( '' !== $.trim( $( this ).val() ) );
	} );

} )( jQuery );
