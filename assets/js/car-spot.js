( function () {
	"use strict";

	function openModal( modal ) {
		modal.classList.add( "is-open" );
		modal.setAttribute( "aria-hidden", "false" );
		document.body.classList.add( "afq-spot-lock" );

		var close = modal.querySelector( ".afq-spot-modal__close" );
		if ( close ) {
			close.focus();
		}
	}

	function closeModal( modal ) {
		modal.classList.remove( "is-open" );
		modal.setAttribute( "aria-hidden", "true" );

		if ( ! document.querySelector( ".afq-spot-modal.is-open" ) ) {
			document.body.classList.remove( "afq-spot-lock" );
		}
	}

	document.addEventListener( "click", function ( e ) {
		var btn = e.target.closest( ".afq-spot__btn" );
		if ( btn ) {
			var modal = document.getElementById( btn.getAttribute( "data-afq-modal" ) );
			if ( modal ) {
				openModal( modal );
			}
			return;
		}

		var closer = e.target.closest( "[data-afq-close]" );
		if ( closer ) {
			var openModalEl = closer.closest( ".afq-spot-modal" );
			if ( openModalEl ) {
				closeModal( openModalEl );
			}
		}
	} );

	document.addEventListener( "keydown", function ( e ) {
		if ( "Escape" !== e.key ) {
			return;
		}

		var open = document.querySelector( ".afq-spot-modal.is-open" );
		if ( open ) {
			closeModal( open );
		}
	} );
} )();
