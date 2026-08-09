/**
 * Shared Jalali (Shamsi) date picker.
 *
 * Binds to any input carrying [data-afq-jalali] and writes YYYY/MM/DD back
 * into it. Used by both [afq_signup_form] and [afq_request_form].
 *
 * The calendar maths is a port of the same Khayyam/Birashk algorithm used
 * server-side in afq_jalali_cal(), so both agree on leap years and month
 * lengths.
 */
( function () {
	'use strict';

	var MONTHS = [
		'فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور',
		'مهر', 'آبان', 'آذر', 'دی', 'بهمن', 'اسفند'
	];

	var WEEKDAYS = [ 'ش', 'ی', 'د', 'س', 'چ', 'پ', 'ج' ];

	function div( a, b ) {
		return Math.trunc( a / b );
	}

	function mod( a, b ) {
		return a - Math.trunc( a / b ) * b;
	}

	function jalCal( jy ) {
		var breaks = [ -61, 9, 38, 199, 426, 686, 756, 818, 1111, 1181, 1210, 1635, 1701, 1866, 2020, 2053, 2400, 3178 ];
		var bl = breaks.length;
		var gy = jy + 621;
		var leapJ = -14;
		var jp = breaks[ 0 ];
		var jump = 0;
		var jm, leap, leapG, march, n, i;

		if ( jy < jp || jy >= breaks[ bl - 1 ] ) {
			return null;
		}

		for ( i = 1; i < bl; i++ ) {
			jm = breaks[ i ];
			jump = jm - jp;
			if ( jy < jm ) {
				break;
			}
			leapJ = leapJ + div( jump, 33 ) * 8 + div( mod( jump, 33 ), 4 );
			jp = jm;
		}

		n = jy - jp;
		leapJ = leapJ + div( n, 33 ) * 8 + div( mod( n, 33 ) + 3, 4 );

		if ( mod( jump, 33 ) === 4 && jump - n === 4 ) {
			leapJ += 1;
		}

		leapG = div( gy, 4 ) - div( ( div( gy, 100 ) + 1 ) * 3, 4 ) - 150;
		march = 20 + leapJ - leapG;

		if ( jump - n < 6 ) {
			n = n - jump + div( jump + 4, 33 ) * 33;
		}

		leap = mod( mod( n + 1, 33 ) - 1, 4 );

		if ( leap === -1 ) {
			leap = 4;
		}

		return { leap: leap, gy: gy, march: march };
	}

	function g2d( gy, gm, gd ) {
		var d = div( ( gy + div( gm - 8, 6 ) + 100100 ) * 1461, 4 )
			+ div( 153 * mod( gm + 9, 12 ) + 2, 5 )
			+ gd - 34840408;
		return d - div( div( gy + 100100 + div( gm - 8, 6 ), 100 ) * 3, 4 ) + 752;
	}

	function d2g( jdn ) {
		var j = 4 * jdn + 139361631;
		j = j + div( div( 4 * jdn + 183187720, 146097 ) * 3, 4 ) * 4 - 3908;
		var i = div( mod( j, 1461 ), 4 ) * 5 + 308;
		var gd = div( mod( i, 153 ), 5 ) + 1;
		var gm = mod( div( i, 153 ), 12 ) + 1;
		var gy = div( j, 1461 ) - 100100 + div( 8 - gm, 6 );
		return { gy: gy, gm: gm, gd: gd };
	}

	function j2d( jy, jm, jd ) {
		var r = jalCal( jy );
		return g2d( r.gy, 3, r.march ) + ( jm - 1 ) * 31 - div( jm, 7 ) * ( jm - 7 ) + jd - 1;
	}

	function d2j( jdn ) {
		var g = d2g( jdn );
		var gy = g.gy;
		var jy = gy - 621;
		var r = jalCal( jy );
		var k = jdn - g2d( gy, 3, r.march );

		if ( k >= 0 ) {
			if ( k <= 185 ) {
				return { jy: jy, jm: 1 + div( k, 31 ), jd: mod( k, 31 ) + 1 };
			}
			k -= 186;
		} else {
			jy -= 1;
			k += 179;
			if ( r.leap === 1 ) {
				k += 1;
			}
		}

		return { jy: jy, jm: 7 + div( k, 30 ), jd: mod( k, 30 ) + 1 };
	}

	function isLeapJalali( jy ) {
		var r = jalCal( jy );
		return !! r && r.leap === 0;
	}

	function monthLength( jy, jm ) {
		if ( jm <= 6 ) {
			return 31;
		}
		if ( jm <= 11 ) {
			return 30;
		}
		return isLeapJalali( jy ) ? 30 : 29;
	}

	function todayJalali() {
		var d = new Date();
		return d2j( g2d( d.getFullYear(), d.getMonth() + 1, d.getDate() ) );
	}

	function pad2( n ) {
		return n < 10 ? '0' + n : '' + n;
	}

	function toFa( value ) {
		return String( value ).replace( /\d/g, function ( d ) {
			return '۰۱۲۳۴۵۶۷۸۹'.charAt( Number( d ) );
		} );
	}

	function parseJalali( value ) {
		var m = /^(\d{4})\/(\d{2})\/(\d{2})$/.exec( String( value || '' ).trim() );

		if ( ! m ) {
			return null;
		}

		var jy = Number( m[ 1 ] );
		var jm = Number( m[ 2 ] );
		var jd = Number( m[ 3 ] );

		if ( jm < 1 || jm > 12 || jd < 1 || jd > monthLength( jy, jm ) ) {
			return null;
		}

		return { jy: jy, jm: jm, jd: jd };
	}

	/* ---------------------------------------------------------------- */

	var picker = null;
	var activeInput = null;
	var viewYear = 0;
	var viewMonth = 1;
	var minYear = 1300;
	var maxYear = todayJalali().jy;

	/**
	 * An input may cap the selectable range, e.g. a visit date that cannot
	 * be in the future vs. a birth date that reaches back to 1300.
	 */
	function inputMinYear( input ) {
		var v = parseInt( input.getAttribute( 'data-afq-min-year' ), 10 );
		return isNaN( v ) ? 1300 : v;
	}

	function buildPicker() {
		var el = document.createElement( 'div' );
		el.className = 'afq-jdp';
		el.setAttribute( 'aria-hidden', 'true' );

		el.innerHTML =
			'<div class="afq-jdp__overlay" data-afq-jdp-close></div>' +
			'<div class="afq-jdp__card" role="dialog" aria-modal="true" aria-label="انتخاب تاریخ">' +
				'<div class="afq-jdp__header">' +
					'<button type="button" class="afq-jdp__nav" data-afq-jdp-step="-1" aria-label="ماه قبل">&#8249;</button>' +
					'<div class="afq-jdp__selects">' +
						'<select class="afq-jdp__month" aria-label="ماه"></select>' +
						'<select class="afq-jdp__year" aria-label="سال"></select>' +
					'</div>' +
					'<button type="button" class="afq-jdp__nav" data-afq-jdp-step="1" aria-label="ماه بعد">&#8250;</button>' +
				'</div>' +
				'<div class="afq-jdp__week"></div>' +
				'<div class="afq-jdp__grid"></div>' +
				'<div class="afq-jdp__footer">' +
					'<button type="button" class="afq-jdp__btn" data-afq-jdp-today>امروز</button>' +
					'<button type="button" class="afq-jdp__btn" data-afq-jdp-clear>پاک کردن</button>' +
					'<button type="button" class="afq-jdp__btn afq-jdp__btn--primary" data-afq-jdp-close>بستن</button>' +
				'</div>' +
			'</div>';

		document.body.appendChild( el );

		var week = el.querySelector( '.afq-jdp__week' );
		WEEKDAYS.forEach( function ( day ) {
			var span = document.createElement( 'span' );
			span.textContent = day;
			week.appendChild( span );
		} );

		var monthSel = el.querySelector( '.afq-jdp__month' );
		MONTHS.forEach( function ( name, index ) {
			var opt = document.createElement( 'option' );
			opt.value = index + 1;
			opt.textContent = name;
			monthSel.appendChild( opt );
		} );

		monthSel.addEventListener( 'change', function () {
			viewMonth = Number( monthSel.value );
			renderPicker();
		} );

		el.querySelector( '.afq-jdp__year' ).addEventListener( 'change', function ( e ) {
			viewYear = Number( e.target.value );
			renderPicker();
		} );

		el.addEventListener( 'click', onPickerClick );

		return el;
	}

	function fillYears( from ) {
		var yearSel = picker.querySelector( '.afq-jdp__year' );
		yearSel.innerHTML = '';

		/* Newest first: recent years need the least scrolling. */
		for ( var y = maxYear; y >= from; y-- ) {
			var opt = document.createElement( 'option' );
			opt.value = y;
			opt.textContent = toFa( y );
			yearSel.appendChild( opt );
		}
	}

	function onPickerClick( e ) {
		var step = e.target.closest( '[data-afq-jdp-step]' );

		if ( step ) {
			shiftMonth( Number( step.getAttribute( 'data-afq-jdp-step' ) ) );
			return;
		}

		var day = e.target.closest( '[data-afq-jdp-day]' );

		if ( day ) {
			commitDate( viewYear, viewMonth, Number( day.getAttribute( 'data-afq-jdp-day' ) ) );
			return;
		}

		if ( e.target.closest( '[data-afq-jdp-today]' ) ) {
			var t = todayJalali();
			commitDate( t.jy, t.jm, t.jd );
			return;
		}

		if ( e.target.closest( '[data-afq-jdp-clear]' ) ) {
			if ( activeInput ) {
				activeInput.value = '';
				fireChange( activeInput );
			}
			closePicker();
			return;
		}

		if ( e.target.closest( '[data-afq-jdp-close]' ) ) {
			closePicker();
		}
	}

	function shiftMonth( delta ) {
		viewMonth += delta;

		if ( viewMonth > 12 ) {
			viewMonth = 1;
			viewYear += 1;
		} else if ( viewMonth < 1 ) {
			viewMonth = 12;
			viewYear -= 1;
		}

		viewYear = Math.min( maxYear, Math.max( minYear, viewYear ) );
		renderPicker();
	}

	function renderPicker() {
		picker.querySelector( '.afq-jdp__month' ).value = viewMonth;
		picker.querySelector( '.afq-jdp__year' ).value = viewYear;

		var grid = picker.querySelector( '.afq-jdp__grid' );
		var length = monthLength( viewYear, viewMonth );

		/* Weekday of the 1st: JDN -> Gregorian weekday -> Saturday-first index. */
		var lead = mod( mod( j2d( viewYear, viewMonth, 1 ) + 1, 7 ) + 1, 7 );

		var today = todayJalali();
		var selected = activeInput ? parseJalali( activeInput.value ) : null;

		grid.innerHTML = '';

		for ( var i = 0; i < lead; i++ ) {
			var blank = document.createElement( 'span' );
			blank.className = 'afq-jdp__day afq-jdp__day--empty';
			grid.appendChild( blank );
		}

		for ( var d = 1; d <= length; d++ ) {
			var btn = document.createElement( 'button' );
			btn.type = 'button';
			btn.className = 'afq-jdp__day';
			btn.setAttribute( 'data-afq-jdp-day', d );
			btn.textContent = toFa( d );

			if ( today.jy === viewYear && today.jm === viewMonth && today.jd === d ) {
				btn.classList.add( 'is-today' );
			}

			if ( selected && selected.jy === viewYear && selected.jm === viewMonth && selected.jd === d ) {
				btn.classList.add( 'is-selected' );
			}

			grid.appendChild( btn );
		}
	}

	function fireChange( input ) {
		input.dispatchEvent( new Event( 'input', { bubbles: true } ) );
		input.dispatchEvent( new Event( 'change', { bubbles: true } ) );
	}

	function commitDate( jy, jm, jd ) {
		if ( activeInput ) {
			activeInput.value = jy + '/' + pad2( jm ) + '/' + pad2( jd );
			fireChange( activeInput );
		}

		closePicker();
	}

	function openPicker( input ) {
		if ( ! picker ) {
			picker = buildPicker();
		}

		activeInput = input;
		minYear = inputMinYear( input );
		fillYears( minYear );

		var current = parseJalali( input.value ) || todayJalali();

		viewYear = Math.min( maxYear, Math.max( minYear, current.jy ) );
		viewMonth = current.jm;

		renderPicker();

		picker.classList.add( 'is-open' );
		picker.setAttribute( 'aria-hidden', 'false' );
		document.body.classList.add( 'afq-lock' );
	}

	function closePicker() {
		if ( ! picker ) {
			return;
		}

		picker.classList.remove( 'is-open' );
		picker.setAttribute( 'aria-hidden', 'true' );
		document.body.classList.remove( 'afq-lock' );

		if ( activeInput ) {
			activeInput.focus();
		}

		activeInput = null;
	}

	document.addEventListener( 'click', function ( e ) {
		var input = e.target.closest ? e.target.closest( '[data-afq-jalali]' ) : null;

		if ( input ) {
			e.preventDefault();
			openPicker( input );
		}
	} );

	document.addEventListener( 'keydown', function ( e ) {
		if ( 'Escape' === e.key && picker && picker.classList.contains( 'is-open' ) ) {
			closePicker();
			return;
		}

		if ( 'Enter' === e.key || ' ' === e.key ) {
			var input = e.target.closest ? e.target.closest( '[data-afq-jalali]' ) : null;
			if ( input ) {
				e.preventDefault();
				openPicker( input );
			}
		}
	} );
} )();
