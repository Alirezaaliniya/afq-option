( function () {
	'use strict';

	document.addEventListener( 'submit', function ( e ) {
		var form = e.target.closest( 'form.afq-signup' );
		if ( ! form ) {
			return;
		}

		e.preventDefault();

		if ( form.classList.contains( 'is-loading' ) ) {
			return;
		}

		var message = form.querySelector( '.afq-signup__message' );
		var button  = form.querySelector( '.afq-signup__submit' );

		/* Reset previous state. */
		message.className = 'afq-signup__message';
		message.textContent = '';
		form.querySelectorAll( '.afq-signup__field.has-error' ).forEach( function ( field ) {
			field.classList.remove( 'has-error' );
			field.querySelector( '.afq-signup__error' ).textContent = '';
		} );

		/* Client-side required check. */
		var firstInvalid = null;
		form.querySelectorAll( '[name]' ).forEach( function ( input ) {
			if ( 'afq_signup_website' === input.name ) {
				return;
			}
			if ( '' === input.value.trim() ) {
				var field = input.closest( '.afq-signup__field' );
				if ( field ) {
					field.classList.add( 'has-error' );
					field.querySelector( '.afq-signup__error' ).textContent = 'این فیلد ضروری است.';
					if ( ! firstInvalid ) {
						firstInvalid = field;
					}
				}
			}
		} );

		if ( firstInvalid ) {
			firstInvalid.scrollIntoView( { behavior: 'smooth', block: 'center' } );
			return;
		}

		form.classList.add( 'is-loading' );
		button.disabled = true;

		var formData = new FormData( form );
		formData.append( 'action', 'afq_signup_submit' );
		formData.append( 'nonce', afqSignupCfg.nonce );

		fetch( afqSignupCfg.ajaxUrl, {
			method: 'POST',
			credentials: 'same-origin',
			body: formData
		} )
			.then( function ( response ) {
				return response.json();
			} )
			.then( function ( json ) {

				if ( json && json.success ) {
					form.reset();
					message.classList.add( 'is-success' );
					message.textContent = json.data.message;
					message.scrollIntoView( { behavior: 'smooth', block: 'center' } );
					return;
				}

				if ( json && json.data && json.data.errors ) {
					var first = null;

					Object.keys( json.data.errors ).forEach( function ( key ) {
						var field = form.querySelector( '[data-afq-field="' + key + '"]' );
						if ( field ) {
							field.classList.add( 'has-error' );
							field.querySelector( '.afq-signup__error' ).textContent = json.data.errors[ key ];
							if ( ! first ) {
								first = field;
							}
						}
					} );

					if ( first ) {
						first.scrollIntoView( { behavior: 'smooth', block: 'center' } );
					}

					message.classList.add( 'is-error' );
					message.textContent = 'برخی فیلدها نیاز به اصلاح دارند.';
					return;
				}

				message.classList.add( 'is-error' );
				message.textContent = ( json && json.data && json.data.message ) ? json.data.message : 'خطا در ارسال. دوباره تلاش کنید.';
			} )
			.catch( function () {
				message.classList.add( 'is-error' );
				message.textContent = 'خطا در ارسال. دوباره تلاش کنید.';
			} )
			.finally( function () {
				form.classList.remove( 'is-loading' );
				button.disabled = false;
			} );
	} );
} )();
