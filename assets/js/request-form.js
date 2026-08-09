( function () {
	'use strict';

	var CFG    = window.afqRequestCfg || {};
	var I18N   = CFG.i18n || {};
	var CITIES = window.afqIranCities || {};
	var MODELS = CFG.models || {};

	var OTHER_VALUE = '__other__';

	function fieldOf( el ) {
		return el.closest ? el.closest( '.afq-voc__field' ) : null;
	}

	function setError( field, text ) {
		if ( ! field ) {
			return;
		}
		field.classList.add( 'has-error' );
		var msg = field.querySelector( '.afq-voc__error' );
		if ( msg ) {
			msg.textContent = text;
		}
	}

	function clearError( field ) {
		if ( ! field ) {
			return;
		}
		field.classList.remove( 'has-error' );
		var msg = field.querySelector( '.afq-voc__error' );
		if ( msg ) {
			msg.textContent = '';
		}
	}

	/* =====================================================================
	 * Dependent selects
	 * ================================================================== */

	function fillSelect( select, list, placeholder, withOther ) {
		var previous = select.value;

		select.innerHTML = '';

		var first = document.createElement( 'option' );
		first.value = '';
		first.textContent = placeholder;
		select.appendChild( first );

		list.forEach( function ( item ) {
			var opt = document.createElement( 'option' );
			opt.value = item;
			opt.textContent = item;
			select.appendChild( opt );
		} );

		if ( withOther && list.length ) {
			var other = document.createElement( 'option' );
			other.value = OTHER_VALUE;
			other.textContent = I18N.otherCity || 'سایر (وارد کردن دستی)';
			select.appendChild( other );
		}

		select.disabled = ! list.length;

		if ( previous && Array.prototype.some.call( select.options, function ( o ) {
			return o.value === previous;
		} ) ) {
			select.value = previous;
		} else {
			select.value = '';
		}
	}

	function syncCityOther( select ) {
		var field = fieldOf( select );

		if ( ! field ) {
			return;
		}

		var other = field.querySelector( '.afq-voc__city-other' );

		if ( ! other ) {
			return;
		}

		var key = field.getAttribute( 'data-afq-field' );
		var required = '1' === field.getAttribute( 'data-afq-required' );

		if ( OTHER_VALUE === select.value ) {
			select.removeAttribute( 'name' );
			select.required = false;
			other.style.display = '';
			other.name = key;
			other.required = required;
		} else {
			other.style.display = 'none';
			other.removeAttribute( 'name' );
			other.required = false;
			other.value = '';
			select.name = key;
			select.required = required;
		}
	}

	function refreshCity( select ) {
		var form = select.form;

		if ( ! form ) {
			return;
		}

		var source = form.querySelector( '[name="' + select.getAttribute( 'data-afq-city-of' ) + '"]' );
		var list = ( source && source.value && CITIES[ source.value ] ) ? CITIES[ source.value ] : [];

		fillSelect(
			select,
			list,
			list.length ? ( I18N.chooseCity || 'شهر را انتخاب کنید' ) : ( I18N.chooseProvince || 'ابتدا استان را انتخاب کنید' ),
			true
		);

		syncCityOther( select );
	}

	function refreshModel( select ) {
		var form = select.form;

		if ( ! form ) {
			return;
		}

		var source = form.querySelector( '[name="' + select.getAttribute( 'data-afq-model-of' ) + '"]' );
		var brand = source ? source.value : '';
		var list = [];

		if ( brand && MODELS[ brand ] ) {
			list = MODELS[ brand ];
		} else if ( ! brand ) {
			/* No brand chosen yet: offer every known model. */
			Object.keys( MODELS ).forEach( function ( b ) {
				list = list.concat( MODELS[ b ] );
			} );
			list = list.filter( function ( v, i, a ) {
				return a.indexOf( v ) === i;
			} ).sort();
		}

		fillSelect( select, list, I18N.chooseModel || 'انتخاب مدل', false );
	}

	function initForm( form ) {
		form.querySelectorAll( '.afq-voc__city' ).forEach( refreshCity );
		form.querySelectorAll( '.afq-voc__model' ).forEach( refreshModel );
		form.querySelectorAll( '[data-afq-counter]' ).forEach( function ( counter ) {
			var area = counter.parentNode.querySelector( 'textarea' );
			if ( area ) {
				updateCounter( area );
			}
		} );
	}

	/* =====================================================================
	 * Description counter
	 * ================================================================== */

	function updateCounter( area ) {
		var field = fieldOf( area );

		if ( ! field ) {
			return;
		}

		var counter = field.querySelector( '[data-afq-counter]' );

		if ( ! counter ) {
			return;
		}

		var len = area.value.length;
		var max = area.getAttribute( 'maxlength' ) || '';
		var min = parseInt( area.getAttribute( 'data-afq-min' ), 10 );

		counter.textContent = len + ' / ' + max;
		counter.classList.toggle( 'is-short', ! isNaN( min ) && len > 0 && len < min );
	}

	/* =====================================================================
	 * File input
	 * ================================================================== */

	function validateFile( input ) {
		var field = fieldOf( input );

		clearError( field );

		if ( ! input.files || ! input.files.length ) {
			return true;
		}

		var file = input.files[ 0 ];
		var exts = CFG.uploadExts || [];
		var maxMb = CFG.uploadMaxMb || 10;
		var ext = ( file.name.split( '.' ).pop() || '' ).toLowerCase();

		if ( exts.length && exts.indexOf( ext ) === -1 ) {
			setError( field, I18N.badFormat || 'فرمت این فایل مجاز نیست.' );
			input.value = '';
			showFileName( input, '' );
			return false;
		}

		if ( file.size > maxMb * 1024 * 1024 ) {
			setError( field, I18N.tooBig || 'حجم فایل بیشتر از حد مجاز است.' );
			input.value = '';
			showFileName( input, '' );
			return false;
		}

		showFileName( input, file.name );
		return true;
	}

	function showFileName( input, name ) {
		var drop = input.closest( '[data-afq-drop]' );

		if ( ! drop ) {
			return;
		}

		var label = drop.querySelector( '[data-afq-drop-name]' );

		if ( label ) {
			label.textContent = name;
		}

		drop.classList.toggle( 'has-file', '' !== name );
	}

	document.addEventListener( 'change', function ( e ) {
		var target = e.target;
		var form = target.form;

		if ( ! form || ! form.classList.contains( 'afq-voc__form' ) ) {
			return;
		}

		if ( target.name ) {
			form.querySelectorAll( '.afq-voc__city[data-afq-city-of="' + target.name + '"]' ).forEach( refreshCity );
			form.querySelectorAll( '.afq-voc__model[data-afq-model-of="' + target.name + '"]' ).forEach( refreshModel );
		}

		if ( target.classList.contains( 'afq-voc__city' ) ) {
			syncCityOther( target );
		}

		if ( 'file' === target.type ) {
			validateFile( target );
			return;
		}

		clearError( fieldOf( target ) );
	} );

	document.addEventListener( 'input', function ( e ) {
		if ( 'TEXTAREA' === e.target.tagName && e.target.form && e.target.form.classList.contains( 'afq-voc__form' ) ) {
			updateCounter( e.target );
		}
	} );

	/* Drag & drop onto the upload box. */
	document.addEventListener( 'dragover', function ( e ) {
		var drop = e.target.closest ? e.target.closest( '[data-afq-drop]' ) : null;
		if ( drop ) {
			e.preventDefault();
			drop.classList.add( 'is-dragging' );
		}
	} );

	document.addEventListener( 'dragleave', function ( e ) {
		var drop = e.target.closest ? e.target.closest( '[data-afq-drop]' ) : null;
		if ( drop ) {
			drop.classList.remove( 'is-dragging' );
		}
	} );

	document.addEventListener( 'drop', function ( e ) {
		var drop = e.target.closest ? e.target.closest( '[data-afq-drop]' ) : null;

		if ( ! drop ) {
			return;
		}

		e.preventDefault();
		drop.classList.remove( 'is-dragging' );

		var input = drop.querySelector( 'input[type="file"]' );

		if ( input && e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files.length ) {
			input.files = e.dataTransfer.files;
			validateFile( input );
		}
	} );

	/* =====================================================================
	 * Success modal
	 *
	 * The modal is moved to <body> on boot: an ancestor with a transform
	 * (common in Elementor sections) would otherwise contain position:fixed.
	 * ================================================================== */

	var lastFocused = null;

	function modalOf( wrap ) {
		if ( ! wrap ) {
			return null;
		}

		return document.querySelector( '.afq-voc__modal[data-afq-modal-for="' + wrap.id + '"]' ) ||
			wrap.querySelector( '.afq-voc__modal' );
	}

	function wrapOf( modal ) {
		return document.getElementById( modal.getAttribute( 'data-afq-modal-for' ) );
	}

	function detachModal( wrap ) {
		var modal = wrap.querySelector( '.afq-voc__modal' );

		if ( modal && wrap.id && modal.parentNode !== document.body ) {
			document.body.appendChild( modal );
		}
	}

	function openSuccess( wrap, data ) {
		var modal = modalOf( wrap );

		if ( ! modal ) {
			return;
		}

		modal.querySelector( '.afq-voc__success-title' ).textContent = data.title || '';
		modal.querySelector( '.afq-voc__success-text' ).textContent = data.message || '';
		modal.querySelector( '[data-afq-success-code]' ).textContent = data.code || '';

		lastFocused = document.activeElement;

		modal.classList.add( 'is-open' );
		modal.setAttribute( 'aria-hidden', 'false' );
		document.body.classList.add( 'afq-lock' );

		var close = modal.querySelector( '.afq-voc__success-close' );

		if ( close ) {
			close.focus();
		}
	}

	function closeSuccess( modal ) {
		if ( ! modal || ! modal.classList.contains( 'is-open' ) ) {
			return;
		}

		modal.classList.remove( 'is-open' );
		modal.setAttribute( 'aria-hidden', 'true' );
		document.body.classList.remove( 'afq-lock' );

		if ( lastFocused && lastFocused.focus ) {
			lastFocused.focus();
		}

		lastFocused = null;
	}

	/* Close button, overlay, and "submit another request". */
	document.addEventListener( 'click', function ( e ) {
		if ( ! e.target.closest ) {
			return;
		}

		var hit = e.target.closest( '[data-afq-success-close], .afq-voc__again' );

		if ( ! hit ) {
			return;
		}

		var modal = hit.closest( '.afq-voc__modal' );

		if ( ! modal ) {
			return;
		}

		closeSuccess( modal );

		/* "Submit another request" also returns the user to the blank form. */
		if ( hit.classList.contains( 'afq-voc__again' ) ) {
			var wrap = wrapOf( modal );

			if ( wrap ) {
				wrap.scrollIntoView( { behavior: 'smooth', block: 'start' } );
			}
		}
	} );

	document.addEventListener( 'keydown', function ( e ) {
		if ( 'Escape' !== e.key ) {
			return;
		}

		var open = document.querySelector( '.afq-voc__modal.is-open' );

		if ( open ) {
			closeSuccess( open );
		}
	} );

	/* =====================================================================
	 * Submit
	 * ================================================================== */

	function resetErrors( form ) {
		form.querySelectorAll( '.afq-voc__field.has-error' ).forEach( clearError );
	}

	function validateClientSide( form ) {
		var firstInvalid = null;

		/* Required single controls. */
		form.querySelectorAll( '[required]' ).forEach( function ( input ) {
			if ( input.disabled || 'afq_request_website' === input.name ) {
				return;
			}

			if ( '' === String( input.value ).trim() ) {
				var field = fieldOf( input );
				setError( field, I18N.required || 'این فیلد ضروری است.' );
				if ( ! firstInvalid ) {
					firstInvalid = field;
				}
			}
		} );

		/* Minimum length on the description. */
		form.querySelectorAll( 'textarea[data-afq-min]' ).forEach( function ( area ) {
			var min = parseInt( area.getAttribute( 'data-afq-min' ), 10 );
			var len = area.value.trim().length;

			if ( ! isNaN( min ) && len > 0 && len < min ) {
				var field = fieldOf( area );
				setError( field, ( I18N.minChars || 'حداقل %d کاراکتر لازم است.' ).replace( '%d', min ) );
				if ( ! firstInvalid ) {
					firstInvalid = field;
				}
			}
		} );

		/* Required checkbox groups. */
		form.querySelectorAll( '.afq-voc__field[data-afq-required="1"]' ).forEach( function ( field ) {
			var boxes = field.querySelectorAll( '.afq-voc__checks input[type="checkbox"]' );

			if ( ! boxes.length ) {
				return;
			}

			var checked = Array.prototype.some.call( boxes, function ( b ) {
				return b.checked;
			} );

			if ( ! checked ) {
				setError( field, 'حداقل یک گزینه را انتخاب کنید.' );
				if ( ! firstInvalid ) {
					firstInvalid = field;
				}
			}
		} );

		/* Consent. */
		var terms = form.querySelector( '[name="afq_request_terms"]' );

		if ( terms && ! terms.checked ) {
			var termsField = fieldOf( terms );
			setError( termsField, I18N.terms || 'پذیرش قوانین و شرایط الزامی است.' );
			if ( ! firstInvalid ) {
				firstInvalid = termsField;
			}
		}

		return firstInvalid;
	}

	document.addEventListener( 'submit', function ( e ) {
		var form = e.target.closest( 'form.afq-voc__form' );

		if ( ! form ) {
			return;
		}

		e.preventDefault();

		if ( form.classList.contains( 'is-loading' ) ) {
			return;
		}

		var wrap = form.closest( '.afq-voc' );
		var message = form.querySelector( '.afq-voc__message' );
		var button = form.querySelector( '.afq-voc__submit' );

		message.className = 'afq-voc__message';
		message.textContent = '';
		resetErrors( form );

		var firstInvalid = validateClientSide( form );

		if ( firstInvalid ) {
			firstInvalid.scrollIntoView( { behavior: 'smooth', block: 'center' } );
			return;
		}

		form.classList.add( 'is-loading' );
		button.disabled = true;

		var formData = new FormData( form );
		formData.append( 'action', 'afq_request_submit' );
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
					initForm( form );
					showFileName( form.querySelector( 'input[type="file"]' ) || document.createElement( 'input' ), '' );

					openSuccess( wrap, json.data || {} );
					return;
				}

				if ( json && json.data && json.data.errors ) {
					var first = null;

					Object.keys( json.data.errors ).forEach( function ( key ) {
						var field = form.querySelector( '[data-afq-field="' + key + '"]' );
						if ( field ) {
							setError( field, json.data.errors[ key ] );
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
				message.textContent = ( json && json.data && json.data.message ) ? json.data.message : ( I18N.genericError || 'خطا در ارسال. دوباره تلاش کنید.' );
			} )
			.catch( function () {
				message.classList.add( 'is-error' );
				message.textContent = I18N.genericError || 'خطا در ارسال. دوباره تلاش کنید.';
			} )
			.finally( function () {
				form.classList.remove( 'is-loading' );
				button.disabled = false;
			} );
	} );

	/* =====================================================================
	 * Tracking form
	 * ================================================================== */

	document.addEventListener( 'submit', function ( e ) {
		var form = e.target.closest( 'form.afq-track__form' );

		if ( ! form ) {
			return;
		}

		e.preventDefault();

		if ( form.classList.contains( 'is-loading' ) ) {
			return;
		}

		var message = form.querySelector( '.afq-track__message' );
		var result = form.querySelector( '.afq-track__result' );
		var button = form.querySelector( '.afq-track__submit' );

		message.className = 'afq-track__message';
		message.textContent = '';
		result.hidden = true;

		var mobile = form.querySelector( '[name="mobile"]' ).value.trim();
		var code = form.querySelector( '[name="code"]' ).value.trim();

		if ( '' === mobile || '' === code ) {
			message.classList.add( 'is-error' );
			message.textContent = 'شماره موبایل و کد رهگیری را وارد کنید.';
			return;
		}

		form.classList.add( 'is-loading' );
		button.disabled = true;

		var formData = new FormData();
		formData.append( 'action', 'afq_request_track' );
		formData.append( 'nonce', CFG.trackNonce );
		formData.append( 'mobile', mobile );
		formData.append( 'code', code );

		fetch( CFG.ajaxUrl, {
			method: 'POST',
			credentials: 'same-origin',
			body: formData
		} )
			.then( function ( response ) {
				return response.json();
			} )
			.then( function ( json ) {

				if ( ! json || ! json.success ) {
					message.classList.add( 'is-error' );
					message.textContent = ( json && json.data && json.data.message ) ? json.data.message : ( I18N.genericError || 'خطا در ارسال. دوباره تلاش کنید.' );
					return;
				}

				var d = json.data;

				form.querySelector( '[data-afq-track-code]' ).textContent = d.code || '';
				form.querySelector( '[data-afq-track-date]' ).textContent = d.date || '';
				form.querySelector( '[data-afq-track-type]' ).textContent = d.type || '';
				form.querySelector( '[data-afq-track-subject]' ).textContent = d.subject || '';
				form.querySelector( '[data-afq-track-reply]' ).textContent = d.reply || '';

				var badge = form.querySelector( '[data-afq-track-status]' );
				badge.textContent = d.status || '';
				badge.style.color = d.color || '';
				badge.style.background = d.bg || '';

				/* Hide the rows that have nothing to show. */
				[ 'type', 'subject', 'reply' ].forEach( function ( key ) {
					var row = form.querySelector( '[data-afq-track-row="' + key + '"]' );
					if ( row ) {
						row.hidden = ! d[ key ];
					}
				} );

				result.hidden = false;
			} )
			.catch( function () {
				message.classList.add( 'is-error' );
				message.textContent = I18N.genericError || 'خطا در ارسال. دوباره تلاش کنید.';
			} )
			.finally( function () {
				form.classList.remove( 'is-loading' );
				button.disabled = false;
			} );
	} );

	/* =====================================================================
	 * Boot
	 * ================================================================== */

	function boot() {
		document.querySelectorAll( '.afq-voc' ).forEach( detachModal );
		document.querySelectorAll( 'form.afq-voc__form' ).forEach( initForm );
	}

	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', boot );
	} else {
		boot();
	}

	document.addEventListener( 'reset', function ( e ) {
		if ( e.target.classList && e.target.classList.contains( 'afq-voc__form' ) ) {
			window.setTimeout( function () {
				initForm( e.target );
			}, 0 );
		}
	} );
} )();
