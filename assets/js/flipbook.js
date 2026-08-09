/**
 * [afq_flipbook] — renders a PDF as a page-turning book.
 *
 * Pages are rasterised with PDF.js into canvases; the turn itself is a CSS 3D
 * rotation of a "leaf" whose two faces hold the outgoing and incoming pages.
 *
 * Spread layout, with the spine in the middle:
 *   half "a" = the side pages flip away from (right in LTR, left in RTL)
 *   half "b" = the side they land on
 * so a spread [p, p+1] always puts the lower page on "b" and the higher on "a".
 */
( function () {
	'use strict';

	var CFG = window.afqFlipbookCfg || {};
	var I18N = CFG.i18n || {};

	var FLIP_MS = 620;
	var SPREAD_MIN_WIDTH = 680;
	var CACHE_LIMIT = 10;
	var MAX_DPR = 2;

	function toFa( value ) {
		return String( value ).replace( /\d/g, function ( d ) {
			return '۰۱۲۳۴۵۶۷۸۹'.charAt( Number( d ) );
		} );
	}

	function toEn( value ) {
		return String( value )
			.replace( /[۰-۹]/g, function ( d ) {
				return String( '۰۱۲۳۴۵۶۷۸۹'.indexOf( d ) );
			} )
			.replace( /[٠-٩]/g, function ( d ) {
				return String( '٠١٢٣٤٥٦٧٨٩'.indexOf( d ) );
			} );
	}

	/* =====================================================================
	 * One book instance
	 * ================================================================== */

	function Book( root ) {

		var self = this;

		this.root = root;
		this.url = root.getAttribute( 'data-afq-pdf' );
		this.rtl = '1' === root.getAttribute( 'data-afq-rtl' );
		this.wantSpread = '1' === root.getAttribute( 'data-afq-spread' );
		this.start = parseInt( root.getAttribute( 'data-afq-start' ), 10 ) || 1;

		this.stage = root.querySelector( '[data-afq-stage]' );
		this.viewport = root.querySelector( '[data-afq-viewport]' );
		this.sheetA = root.querySelector( '[data-afq-sheet="a"]' );
		this.sheetB = root.querySelector( '[data-afq-sheet="b"]' );
		this.leaf = root.querySelector( '[data-afq-leaf]' );
		this.faceFront = root.querySelector( '[data-afq-face="front"]' );
		this.faceBack = root.querySelector( '[data-afq-face="back"]' );
		this.statusText = root.querySelector( '[data-afq-status-text]' );
		this.input = root.querySelector( '[data-afq-book-input]' );
		this.totalEl = root.querySelector( '[data-afq-book-total]' );

		this.pdf = null;
		this.total = 0;
		this.aspect = 0.707;          /* w/h — replaced by the real page 1 ratio */
		this.spreads = [];
		this.index = 0;
		this.single = false;
		this.busy = false;
		this.endFlip = null;
		this.cache = new Map();
		this.tasks = new Map();
		this.pageWidth = 0;

		this.onResize = debounce( function () {
			self.layout();
		}, 180 );

		this.bind();
		this.load();
	}

	function debounce( fn, wait ) {
		var timer = null;

		return function () {
			window.clearTimeout( timer );
			timer = window.setTimeout( fn, wait );
		};
	}

	/* ---- Loading ------------------------------------------------------ */

	Book.prototype.fail = function ( message ) {
		this.root.classList.add( 'has-error' );
		this.root.classList.remove( 'is-ready' );
		this.statusText.textContent = message;
	};

	Book.prototype.load = function () {

		var self = this;

		if ( ! window.pdfjsLib ) {
			this.fail( I18N.libError || 'کتابخانه نمایش PDF بارگذاری نشد.' );
			return;
		}

		window.pdfjsLib.GlobalWorkerOptions.workerSrc = CFG.worker;

		var task = window.pdfjsLib.getDocument( {
			url: this.url,
			standardFontDataUrl: CFG.fonts,
			isEvalSupported: false,
			disableAutoFetch: true,
			disableStream: false
		} );

		task.onProgress = function ( p ) {
			if ( p && p.total ) {
				var pct = Math.min( 99, Math.round( ( p.loaded / p.total ) * 100 ) );
				self.statusText.textContent = ( I18N.loading || 'در حال بارگذاری کتاب…' ) + ' ' + toFa( pct ) + '٪';
			}
		};

		task.promise.then(
			function ( pdf ) {
				self.pdf = pdf;
				self.total = pdf.numPages;
				self.totalEl.textContent = toFa( self.total );

				return pdf.getPage( 1 ).then( function ( page ) {
					var vp = page.getViewport( { scale: 1 } );
					self.aspect = vp.width / vp.height;
					self.ready();
				} );
			},
			function ( err ) {
				if ( err && 'PasswordException' === err.name ) {
					self.fail( I18N.locked || 'این فایل PDF رمز دارد و قابل نمایش نیست.' );
					return;
				}

				self.fail( I18N.loadError || 'بارگذاری فایل PDF ممکن نشد. آدرس فایل را بررسی کنید.' );
			}
		);
	};

	Book.prototype.ready = function () {

		this.buildSpreads();
		this.index = this.spreadOfPage( this.start );

		this.root.classList.add( 'is-ready' );

		this.layout();

		window.addEventListener( 'resize', this.onResize );
		window.addEventListener( 'orientationchange', this.onResize );
	};

	/* ---- Spread model ------------------------------------------------- */

	/**
	 * Cover alone, then pairs: [1], [2,3], [4,5], … and a lone final page when
	 * the count works out even. Single-page mode is one page per spread.
	 */
	Book.prototype.buildSpreads = function () {

		var list = [];
		var i;

		if ( this.single ) {
			for ( i = 1; i <= this.total; i++ ) {
				list.push( [ i ] );
			}

			this.spreads = list;
			return;
		}

		list.push( [ 1 ] );

		for ( i = 2; i <= this.total; i += 2 ) {
			if ( i + 1 <= this.total ) {
				list.push( [ i, i + 1 ] );
			} else {
				list.push( [ i ] );
			}
		}

		this.spreads = list;
	};

	Book.prototype.spreadOfPage = function ( page ) {

		for ( var i = 0; i < this.spreads.length; i++ ) {
			if ( -1 !== this.spreads[ i ].indexOf( page ) ) {
				return i;
			}
		}

		return 0;
	};

	/** Page shown on the "a" half of a spread (null when that half is empty). */
	Book.prototype.pageA = function ( i ) {
		var s = this.spreads[ i ];

		if ( ! s ) {
			return null;
		}

		if ( 2 === s.length ) {
			return s[ 1 ];
		}

		/* A lone spread is the cover (sits on "a") or the tail page (on "b"). */
		return 0 === i ? s[ 0 ] : null;
	};

	Book.prototype.pageB = function ( i ) {
		var s = this.spreads[ i ];

		if ( ! s ) {
			return null;
		}

		if ( 2 === s.length ) {
			return s[ 0 ];
		}

		return 0 === i ? null : s[ 0 ];
	};

	/* ---- Layout ------------------------------------------------------- */

	Book.prototype.maxHeight = function () {

		var probe = document.createElement( 'div' );

		probe.style.cssText = 'position:absolute;visibility:hidden;pointer-events:none;height:var(--afq-book-h);';
		this.root.appendChild( probe );

		var h = probe.offsetHeight;

		this.root.removeChild( probe );

		return h || 600;
	};

	Book.prototype.layout = function () {

		if ( ! this.pdf ) {
			return;
		}

		var stageWidth = this.stage.clientWidth;

		if ( ! stageWidth ) {
			return;
		}

		var single = ! this.wantSpread || stageWidth < SPREAD_MIN_WIDTH;

		if ( single !== this.single ) {
			var current = this.spreads.length ? this.spreads[ this.index ][ 0 ] : this.start;

			this.single = single;
			this.buildSpreads();
			this.index = this.spreadOfPage( current );
			this.root.classList.toggle( 'is-single', single );
		}

		var ratio = single ? this.aspect : this.aspect * 2;
		var maxH = this.maxHeight();

		if ( document.fullscreenElement === this.root && this.stage.clientHeight ) {
			maxH = this.stage.clientHeight;
		}

		var height = Math.min( maxH, stageWidth / ratio );
		var width = height * ratio;

		if ( width > stageWidth ) {
			width = stageWidth;
			height = width / ratio;
		}

		this.viewport.style.width = Math.round( width ) + 'px';
		this.viewport.style.height = Math.round( height ) + 'px';

		this.pageWidth = single ? width : width / 2;

		this.paint();
	};

	/* ---- Page rendering ----------------------------------------------- */

	Book.prototype.needScaleWidth = function () {
		return Math.round( this.pageWidth * Math.min( MAX_DPR, window.devicePixelRatio || 1 ) );
	};

	/**
	 * Resolve a page to a canvas, rendering it if it is missing or was drawn
	 * at a noticeably smaller size than the current layout needs.
	 */
	Book.prototype.getCanvas = function ( num ) {

		var self = this;
		var need = this.needScaleWidth();
		var hit = this.cache.get( num );

		if ( hit && hit.width >= need * 0.92 ) {
			/* Touch for LRU ordering. */
			this.cache.delete( num );
			this.cache.set( num, hit );
			return Promise.resolve( hit.canvas );
		}

		var pending = this.tasks.get( num );

		if ( pending && pending.width >= need * 0.92 ) {
			return pending.promise;
		}

		/* A resize asked for a sharper copy: drop the render already running.
		 * The new one draws onto its own canvas, so PDF.js is never asked to
		 * paint the same canvas twice at once. */
		if ( pending && pending.task ) {
			try {
				pending.task.cancel();
			} catch ( e ) {}
		}

		var entry = { width: need, task: null };

		entry.promise = this.pdf.getPage( num ).then( function ( page ) {

			var base = page.getViewport( { scale: 1 } );
			var vp = page.getViewport( { scale: need / base.width } );

			var canvas = document.createElement( 'canvas' );
			var ctx = canvas.getContext( '2d', { alpha: false } );

			canvas.width = Math.floor( vp.width );
			canvas.height = Math.floor( vp.height );

			entry.task = page.render( { canvasContext: ctx, viewport: vp } );

			return entry.task.promise.then( function () {
				if ( self.tasks.get( num ) === entry ) {
					self.tasks.delete( num );
				}

				self.cache.delete( num );
				self.cache.set( num, { canvas: canvas, width: canvas.width } );
				self.trimCache();

				return canvas;
			} );
		} ).catch( function () {
			if ( self.tasks.get( num ) === entry ) {
				self.tasks.delete( num );
			}

			return null;
		} );

		this.tasks.set( num, entry );

		return entry.promise;
	};

	/** Drop the least recently used canvases, never one that is on screen. */
	Book.prototype.trimCache = function () {

		if ( this.cache.size <= CACHE_LIMIT ) {
			return;
		}

		var self = this;

		Array.from( this.cache.keys() ).forEach( function ( key ) {

			if ( self.cache.size <= CACHE_LIMIT ) {
				return;
			}

			var entry = self.cache.get( key );

			if ( entry && entry.canvas.parentNode ) {
				return;
			}

			self.cache.delete( key );
		} );
	};

	/** Put a page (or a blank) into a slot element. */
	Book.prototype.fill = function ( slot, num ) {

		var self = this;

		if ( ! num ) {
			slot.classList.add( 'is-blank' );

			while ( slot.firstChild ) {
				slot.removeChild( slot.firstChild );
			}

			return Promise.resolve();
		}

		slot.classList.remove( 'is-blank' );

		return this.getCanvas( num ).then( function ( canvas ) {
			if ( ! canvas ) {
				return;
			}

			/* Guard against a slow render landing after the user moved on. */
			if ( slot.getAttribute( 'data-afq-page' ) !== String( num ) ) {
				return;
			}

			if ( canvas.parentNode !== slot ) {
				while ( slot.firstChild ) {
					slot.removeChild( slot.firstChild );
				}
				slot.appendChild( canvas );
			}

			self.trimCache();
		} );
	};

	Book.prototype.assign = function ( slot, num ) {
		slot.setAttribute( 'data-afq-page', num ? String( num ) : '' );
		return this.fill( slot, num );
	};

	/** Draw the current spread into the static halves. */
	Book.prototype.paint = function () {

		var self = this;
		var current = this.spreads[ this.index ];

		if ( ! current ) {
			return;
		}

		if ( this.single ) {
			this.assign( this.sheetB, current[ 0 ] );
			this.assign( this.sheetA, null );
		} else {
			this.assign( this.sheetB, this.pageB( this.index ) );
			this.assign( this.sheetA, this.pageA( this.index ) );
		}

		this.syncControls();

		/* Warm the neighbouring spread so the next turn has no blank frame. */
		window.setTimeout( function () {
			[ self.index - 1, self.index + 1 ].forEach( function ( i ) {
				var s = self.spreads[ i ];

				if ( s ) {
					s.forEach( function ( num ) {
						self.getCanvas( num );
					} );
				}
			} );
		}, 120 );
	};

	Book.prototype.syncControls = function () {

		var page = this.spreads.length ? this.spreads[ this.index ][ 0 ] : 1;

		if ( document.activeElement !== this.input ) {
			this.input.value = toFa( page );
		}

		var atStart = 0 === this.index;
		var atEnd = this.index >= this.spreads.length - 1;

		this.root.querySelectorAll( '[data-afq-book-prev], [data-afq-book-first]' ).forEach( function ( b ) {
			b.disabled = atStart;
		} );

		this.root.querySelectorAll( '[data-afq-book-next], [data-afq-book-last]' ).forEach( function ( b ) {
			b.disabled = atEnd;
		} );
	};

	/* ---- Turning ------------------------------------------------------ */

	Book.prototype.angle = function ( deg ) {
		return 'rotateY(' + ( this.rtl ? deg : -deg ) + 'deg)';
	};

	Book.prototype.finish = function () {
		if ( this.endFlip ) {
			var fn = this.endFlip;
			this.endFlip = null;
			fn();
		}
	};

	Book.prototype.go = function ( target ) {

		if ( ! this.pdf || ! this.spreads.length ) {
			return;
		}

		this.finish();

		target = Math.max( 0, Math.min( this.spreads.length - 1, target ) );

		if ( target === this.index ) {
			return;
		}

		/* Jumps of more than one spread skip the animation. */
		if ( Math.abs( target - this.index ) > 1 ) {
			this.index = target;
			this.paint();
			return;
		}

		if ( this.single ) {
			this.flipSingle( target );
		} else {
			this.flipSpread( target );
		}
	};

	Book.prototype.animate = function ( from, to, done ) {

		var self = this;
		var root = this.root;
		var timer = null;

		root.classList.add( 'is-flipping' );
		this.leaf.style.transform = from;

		/* Force the start transform to be applied before the transition. */
		void this.leaf.offsetWidth;

		root.classList.add( 'is-animating' );
		this.leaf.style.transform = to;

		var end = function ( e ) {

			/* The page-shading fades bubble up from the faces — only the
			 * leaf's own transform means the turn is over. */
			if ( e && ( e.target !== self.leaf || 'transform' !== e.propertyName ) ) {
				return;
			}

			window.clearTimeout( timer );
			self.leaf.removeEventListener( 'transitionend', end );

			root.classList.remove( 'is-flipping', 'is-animating' );
			self.leaf.style.transform = '';
			self.busy = false;

			done();
		};

		timer = window.setTimeout( end, FLIP_MS + 120 );
		this.leaf.addEventListener( 'transitionend', end );

		this.busy = true;
		this.endFlip = end;
	};

	Book.prototype.flipSpread = function ( target ) {

		var self = this;
		var forward = target > this.index;
		var from = forward ? this.index : target;
		var to = forward ? target : this.index;

		/* One leaf sits between spread `from` and spread `to`: its front is the
		 * "a" page of `from`, its back the "b" page of `to`. */
		var front = this.pageA( from );
		var back = this.pageB( to );

		this.assign( this.faceFront, front );
		this.assign( this.faceBack, back );

		/* Behind the leaf: the halves that stay put during the turn. */
		this.assign( this.sheetA, this.pageA( to ) );
		this.assign( this.sheetB, this.pageB( from ) );

		var flat = this.angle( 0 );
		var turned = this.angle( 180 );

		this.animate(
			forward ? flat : turned,
			forward ? turned : flat,
			function () {
				self.index = target;
				self.clearFaces();
				self.paint();
			}
		);
	};

	Book.prototype.flipSingle = function ( target ) {

		var self = this;
		var forward = target > this.index;

		/* Forward: the current page swings away and reveals the next one.
		 * Back: the previous page swings in over the current one. */
		var face = forward ? this.spreads[ this.index ][ 0 ] : this.spreads[ target ][ 0 ];

		this.assign( this.faceFront, face );
		this.assign( this.faceBack, null );
		this.assign( this.sheetB, this.spreads[ forward ? target : this.index ][ 0 ] );

		var flat = this.angle( 0 );
		var lifted = this.angle( 92 );

		this.animate(
			forward ? flat : lifted,
			forward ? lifted : flat,
			function () {
				self.index = target;
				self.clearFaces();
				self.paint();
			}
		);
	};

	Book.prototype.clearFaces = function () {
		this.assign( this.faceFront, null );
		this.assign( this.faceBack, null );
	};

	Book.prototype.next = function () {
		this.go( this.index + 1 );
	};

	Book.prototype.prev = function () {
		this.go( this.index - 1 );
	};

	Book.prototype.goToPage = function ( page ) {
		page = Math.max( 1, Math.min( this.total, page ) );
		this.go( this.spreadOfPage( page ) );
	};

	/* ---- Input -------------------------------------------------------- */

	Book.prototype.bind = function () {

		var self = this;
		var root = this.root;

		root.addEventListener( 'click', function ( e ) {

			if ( e.target.closest( '[data-afq-book-next]' ) ) {
				self.next();
			} else if ( e.target.closest( '[data-afq-book-prev]' ) ) {
				self.prev();
			} else if ( e.target.closest( '[data-afq-book-first]' ) ) {
				self.go( 0 );
			} else if ( e.target.closest( '[data-afq-book-last]' ) ) {
				self.go( self.spreads.length - 1 );
			} else if ( e.target.closest( '[data-afq-book-full]' ) ) {
				self.toggleFullscreen();
			}
		} );

		/* Tapping a half turns that way, like a real book. */
		this.viewport.addEventListener( 'click', function ( e ) {

			if ( self.busy || ! self.pdf ) {
				return;
			}

			var box = self.viewport.getBoundingClientRect();
			var onA = self.rtl
				? ( e.clientX - box.left ) < box.width / 2
				: ( e.clientX - box.left ) > box.width / 2;

			if ( self.single || onA ) {
				self.next();
			} else {
				self.prev();
			}
		} );

		/* Swipe. */
		var startX = 0;
		var startY = 0;
		var tracking = false;

		this.viewport.addEventListener( 'touchstart', function ( e ) {
			if ( 1 !== e.touches.length ) {
				return;
			}

			tracking = true;
			startX = e.touches[ 0 ].clientX;
			startY = e.touches[ 0 ].clientY;
		}, { passive: true } );

		this.viewport.addEventListener( 'touchend', function ( e ) {

			if ( ! tracking || ! e.changedTouches.length ) {
				return;
			}

			tracking = false;

			var dx = e.changedTouches[ 0 ].clientX - startX;
			var dy = e.changedTouches[ 0 ].clientY - startY;

			if ( Math.abs( dx ) < 45 || Math.abs( dx ) < Math.abs( dy ) ) {
				return;
			}

			/* Dragging toward the spine side advances the book. */
			var forward = self.rtl ? dx > 0 : dx < 0;

			if ( forward ) {
				self.next();
			} else {
				self.prev();
			}
		}, { passive: true } );

		/* Page number box. */
		this.input.addEventListener( 'keydown', function ( e ) {
			if ( 'Enter' === e.key ) {
				e.preventDefault();
				self.goToPage( parseInt( toEn( self.input.value ), 10 ) || 1 );
				self.input.blur();
			}
		} );

		this.input.addEventListener( 'blur', function () {
			self.syncControls();
		} );

		/* Arrow keys, once the book has been interacted with or is on screen. */
		document.addEventListener( 'keydown', function ( e ) {

			if ( ! self.pdf || self.input === document.activeElement ) {
				return;
			}

			/* Only steal the arrow keys while this book has focus. */
			var focused = root.contains( document.activeElement ) || document.fullscreenElement === root;

			if ( ! focused ) {
				return;
			}

			if ( 'ArrowRight' === e.key ) {
				e.preventDefault();
				if ( self.rtl ) {
					self.prev();
				} else {
					self.next();
				}
			} else if ( 'ArrowLeft' === e.key ) {
				e.preventDefault();
				if ( self.rtl ) {
					self.next();
				} else {
					self.prev();
				}
			}
		} );

		document.addEventListener( 'fullscreenchange', function () {
			if ( document.fullscreenElement === root || root.classList.contains( 'is-full' ) ) {
				root.classList.toggle( 'is-full', document.fullscreenElement === root );
				self.onResize();
			}
		} );
	};

	Book.prototype.toggleFullscreen = function () {

		if ( document.fullscreenElement === this.root ) {
			if ( document.exitFullscreen ) {
				document.exitFullscreen();
			}
			return;
		}

		if ( this.root.requestFullscreen ) {
			this.root.requestFullscreen().catch( function () {} );
		}
	};

	/* =====================================================================
	 * Boot — books initialise when they first scroll into view.
	 * ================================================================== */

	function boot() {

		var nodes = document.querySelectorAll( '[data-afq-book]' );

		if ( ! nodes.length ) {
			return;
		}

		if ( ! ( 'IntersectionObserver' in window ) ) {
			nodes.forEach( function ( node ) {
				new Book( node );
			} );
			return;
		}

		var observer = new IntersectionObserver( function ( entries ) {
			entries.forEach( function ( entry ) {
				if ( ! entry.isIntersecting ) {
					return;
				}

				observer.unobserve( entry.target );
				new Book( entry.target );
			} );
		}, { rootMargin: '250px' } );

		nodes.forEach( function ( node ) {
			observer.observe( node );
		} );
	}

	/* Exposed so a theme can drive a book: afqBooks[0].goToPage(5). */
	window.AfqFlipbook = Book;

	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', boot );
	} else {
		boot();
	}
} )();
