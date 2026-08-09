( function () {
	'use strict';

	var CFG    = window.afqSignupCfg || {};
	var I18N   = CFG.i18n || {};
	var CITIES = window.afqIranCities || {};

	var OTHER_VALUE = '__other__';

	/* The Jalali date fields are handled by the shared jalali-picker.js. */

	function clearFieldError( el ) {
		var field = el.closest( '.afq-signup__field' );

		if ( ! field ) {
			return;
		}

		field.classList.remove( 'has-error' );

		var msg = field.querySelector( '.afq-signup__error' );

		if ( msg ) {
			msg.textContent = '';
		}
	}

	/* =====================================================================
	 * Province -> city dependent selects
	 * ================================================================== */

	function cityParts( select ) {
		var field = select.closest( '.afq-signup__field' );

		return {
			field: field,
			key: field ? field.getAttribute( 'data-afq-field' ) : '',
			required: field ? '1' === field.getAttribute( 'data-afq-required' ) : false,
			other: field ? field.querySelector( '.afq-signup__city-other' ) : null
		};
	}

	/**
	 * Show the free-text box when «سایر» is picked, and move the field name
	 * onto whichever control is actually in use so only one is submitted.
	 */
	function syncOther( select ) {
		var parts = cityParts( select );

		if ( ! parts.other ) {
			return;
		}

		if ( OTHER_VALUE === select.value ) {
			select.removeAttribute( 'name' );
			select.required = false;

			parts.other.style.display = '';
			parts.other.name = parts.key;
			parts.other.required = parts.required;
		} else {
			parts.other.style.display = 'none';
			parts.other.removeAttribute( 'name' );
			parts.other.required = false;
			parts.other.value = '';

			select.name = parts.key;
			select.required = parts.required;
		}
	}

	function fillCities( select ) {
		var parts = cityParts( select );
		var form = select.form;

		if ( ! form ) {
			return;
		}

		var province = form.querySelector( '[name="' + select.getAttribute( 'data-afq-city-of' ) + '"]' );
		var list = ( province && province.value && CITIES[ province.value ] ) ? CITIES[ province.value ] : [];
		var previous = select.value;

		select.innerHTML = '';

		var placeholder = document.createElement( 'option' );
		placeholder.value = '';
		placeholder.textContent = list.length ? ( I18N.chooseCity || 'شهر را انتخاب کنید' ) : ( I18N.chooseProvince || 'ابتدا استان را انتخاب کنید' );
		select.appendChild( placeholder );

		list.forEach( function ( city ) {
			var opt = document.createElement( 'option' );
			opt.value = city;
			opt.textContent = city;
			select.appendChild( opt );
		} );

		if ( list.length ) {
			var other = document.createElement( 'option' );
			other.value = OTHER_VALUE;
			other.textContent = I18N.otherCity || 'سایر (وارد کردن دستی)';
			select.appendChild( other );
		}

		/* A disabled control is not submitted, which is what we want while
		 * no province is chosen. */
		select.disabled = ! list.length;

		/* Keep the previous choice when it still exists in the new list. */
		if ( previous && Array.prototype.some.call( select.options, function ( o ) {
			return o.value === previous;
		} ) ) {
			select.value = previous;
		} else {
			select.value = '';
			if ( parts.other ) {
				parts.other.value = '';
			}
		}

		syncOther( select );
	}

	function initCities( form ) {
		form.querySelectorAll( '.afq-signup__city' ).forEach( function ( select ) {
			fillCities( select );
		} );
	}

	document.addEventListener( 'change', function ( e ) {
		var form = e.target.form;

		if ( ! form || ! form.classList.contains( 'afq-signup' ) ) {
			return;
		}

		/* A province changed: refresh every city select bound to it. */
		if ( e.target.name ) {
			form.querySelectorAll( '.afq-signup__city[data-afq-city-of="' + e.target.name + '"]' ).forEach( function ( select ) {
				fillCities( select );
			} );
		}

		if ( e.target.classList.contains( 'afq-signup__city' ) ) {
			syncOther( e.target );
		}

		clearFieldError( e.target );
	} );

	function initForms() {
		document.querySelectorAll( 'form.afq-signup' ).forEach( initCities );
	}

	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', initForms );
	} else {
		initForms();
	}

	document.addEventListener( 'reset', function ( e ) {
		if ( e.target.classList && e.target.classList.contains( 'afq-signup' ) ) {
			/* Let the browser finish resetting before repopulating. */
			window.setTimeout( function () {
				initCities( e.target );
			}, 0 );
		}
	} );

	/* =====================================================================
	 * Submit
	 * ================================================================== */

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

		/*
		 * Client-side check of the fields the admin marked as required.
		 * A disabled city select is skipped: its province has not been
		 * chosen yet, so the province is what needs filling in first.
		 */
		var firstInvalid = null;

		form.querySelectorAll( '[required]' ).forEach( function ( input ) {
			if ( input.disabled || 'afq_signup_website' === input.name ) {
				return;
			}

			if ( '' === String( input.value ).trim() ) {
				var field = input.closest( '.afq-signup__field' );
				if ( field ) {
					field.classList.add( 'has-error' );
					field.querySelector( '.afq-signup__error' ).textContent = I18N.required || 'این فیلد ضروری است.';
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
		formData.append( 'nonce', CFG.nonce );

		fetch( CFG.ajaxUrl, {
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
					initCities( form );
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
