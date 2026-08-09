( function () {
	'use strict';

	var wrap = document.querySelector( '.afq-signup-settings' );

	if ( ! wrap ) {
		return;
	}

	function syncLabel( checkbox ) {
		var state = checkbox.parentNode.querySelector( '.afq-signup-settings__state' );

		if ( state ) {
			state.textContent = checkbox.checked ? 'ضروری' : 'اختیاری';
		}
	}

	function setAll( value ) {
		wrap.querySelectorAll( 'input[name="afq_signup_required[]"]' ).forEach( function ( checkbox ) {
			checkbox.checked = value;
			syncLabel( checkbox );
		} );
	}

	wrap.addEventListener( 'change', function ( e ) {
		if ( e.target.matches( 'input[name="afq_signup_required[]"]' ) ) {
			syncLabel( e.target );
		}
	} );

	wrap.addEventListener( 'click', function ( e ) {
		if ( e.target.closest( '.afq-signup-settings__all' ) ) {
			e.preventDefault();
			setAll( true );
		}

		if ( e.target.closest( '.afq-signup-settings__none' ) ) {
			e.preventDefault();
			setAll( false );
		}
	} );
} )();
