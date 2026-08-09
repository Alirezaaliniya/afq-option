/**
 * Shared behaviour for the plugin's settings screens: keeps the
 * ضروری/اختیاری label in sync with its switch and powers the bulk buttons.
 */
( function () {
	'use strict';

	var wrap = document.querySelector( '.afq-settings' );

	if ( ! wrap ) {
		return;
	}

	/* Every switch keeps its label in sync... */
	var ANY_SWITCH = '.afq-settings__switch input[type="checkbox"]';

	/* ...but the bulk buttons only touch the required/optional ones. */
	var SELECTOR = 'input[data-afq-toggle]';

	function syncLabel( checkbox ) {
		var state = checkbox.parentNode.querySelector( '.afq-settings__state' );

		if ( state ) {
			state.textContent = checkbox.checked
				? ( state.getAttribute( 'data-on' ) || 'ضروری' )
				: ( state.getAttribute( 'data-off' ) || 'اختیاری' );
		}
	}

	function setAll( value ) {
		wrap.querySelectorAll( SELECTOR ).forEach( function ( checkbox ) {
			checkbox.checked = value;
			syncLabel( checkbox );
		} );
	}

	wrap.addEventListener( 'change', function ( e ) {
		if ( e.target.matches( ANY_SWITCH ) ) {
			syncLabel( e.target );
		}
	} );

	wrap.addEventListener( 'click', function ( e ) {
		if ( e.target.closest( '.afq-settings__all' ) ) {
			e.preventDefault();
			setAll( true );
		}

		if ( e.target.closest( '.afq-settings__none' ) ) {
			e.preventDefault();
			setAll( false );
		}
	} );
} )();
