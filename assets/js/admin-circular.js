( function( $ ) {
	'use strict';

	var $wrap = $( '.afq-circular-admin' );

	if ( ! $wrap.length ) {
		return;
	}

	var $rows     = $wrap.find( '.afq-circular-rows' );
	var $empty    = $wrap.find( '.afq-circular-empty' );
	var template  = $( '#afq-circular-row-template' ).html();

	function toggleEmpty() {
		$empty.toggle( 0 === $rows.children( '.afq-circular-row' ).length );
	}

	$wrap.on( 'click', '.afq-circular-add-row', function( e ) {
		e.preventDefault();
		$rows.append( template );
		toggleEmpty();
	} );

	$wrap.on( 'click', '.afq-circular-remove-row', function( e ) {
		e.preventDefault();
		$( this ).closest( '.afq-circular-row' ).remove();
		toggleEmpty();
	} );

	$rows.sortable( {
		items: '.afq-circular-row',
		handle: '.afq-circular-row__handle',
		tolerance: 'pointer'
	} );

} )( jQuery );
