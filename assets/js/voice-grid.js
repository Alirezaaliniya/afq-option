( function () {
	'use strict';

	/* ---------------- Audio players ---------------- */

	var activePlayer = null;

	function formatTime( seconds ) {
		if ( ! isFinite( seconds ) ) {
			return '0:00';
		}
		var m = Math.floor( seconds / 60 );
		var s = Math.floor( seconds % 60 );
		return m + ':' + ( s < 10 ? '0' + s : s );
	}

	function getAudio( player ) {
		var audio = player._afqAudio;

		if ( ! audio ) {
			audio = new Audio( player.getAttribute( 'data-afq-audio' ) );
			audio.preload = 'metadata';
			player._afqAudio = audio;

			var progress = player.querySelector( '.afq-voice-player__progress' );
			var time     = player.querySelector( '.afq-voice-player__time' );

			audio.addEventListener( 'loadedmetadata', function () {
				time.textContent = formatTime( audio.duration );
			} );

			audio.addEventListener( 'timeupdate', function () {
				if ( audio.duration ) {
					progress.style.width = ( audio.currentTime / audio.duration * 100 ) + '%';
				}
				time.textContent = formatTime( audio.currentTime );
			} );

			audio.addEventListener( 'ended', function () {
				player.classList.remove( 'is-playing' );
				progress.style.width = '0';
				time.textContent = formatTime( audio.duration );
				if ( activePlayer === player ) {
					activePlayer = null;
				}
			} );
		}

		return audio;
	}

	function pausePlayer( player ) {
		if ( player && player._afqAudio ) {
			player._afqAudio.pause();
			player.classList.remove( 'is-playing' );
		}
	}

	document.addEventListener( 'click', function ( e ) {

		/* Play / pause */
		var btn = e.target.closest( '.afq-voice-player__btn' );
		if ( btn ) {
			var player = btn.closest( '.afq-voice-player' );
			var audio  = getAudio( player );

			if ( audio.paused ) {
				if ( activePlayer && activePlayer !== player ) {
					pausePlayer( activePlayer );
				}
				audio.play();
				player.classList.add( 'is-playing' );
				activePlayer = player;
			} else {
				audio.pause();
				player.classList.remove( 'is-playing' );
			}
			return;
		}

		/* Seek */
		var track = e.target.closest( '.afq-voice-player__track' );
		if ( track ) {
			var seekPlayer = track.closest( '.afq-voice-player' );
			var seekAudio  = getAudio( seekPlayer );

			if ( seekAudio.duration ) {
				var rect  = track.getBoundingClientRect();
				var ratio = ( e.clientX - rect.left ) / rect.width;

				/* RTL pages fill from the right visually; ratio stays LTR because progress uses inset-inline-start. */
				if ( 'rtl' === getComputedStyle( track ).direction ) {
					ratio = 1 - ratio;
				}

				seekAudio.currentTime = Math.min( Math.max( ratio, 0 ), 1 ) * seekAudio.duration;
			}
			return;
		}

		/* Open video modal */
		var videoBtn = e.target.closest( '.afq-voice-card__video-btn' );
		if ( videoBtn ) {
			var wrap  = videoBtn.closest( '.afq-voices' );
			var modal = wrap.querySelector( '.afq-voice-modal' );
			var body  = modal.querySelector( '.afq-voice-modal__body' );
			var title = modal.querySelector( '.afq-voice-modal__title' );
			var url   = videoBtn.getAttribute( 'data-afq-video' );

			title.textContent = videoBtn.getAttribute( 'data-afq-video-name' ) || '';

			if ( /\.(mp4|webm|ogv|ogg|m4v|mov)(\?.*)?$/i.test( url ) ) {
				var video = document.createElement( 'video' );
				video.src = url;
				video.controls = true;
				video.autoplay = true;
				video.playsInline = true;
				body.innerHTML = '';
				body.appendChild( video );
			} else {
				var iframe = document.createElement( 'iframe' );
				iframe.src = url;
				iframe.allow = 'autoplay; fullscreen; picture-in-picture';
				iframe.setAttribute( 'allowfullscreen', '' );
				body.innerHTML = '';
				body.appendChild( iframe );
			}

			if ( activePlayer ) {
				pausePlayer( activePlayer );
			}

			modal.classList.add( 'is-open' );
			modal.setAttribute( 'aria-hidden', 'false' );
			document.body.classList.add( 'afq-voice-lock' );
			return;
		}

		/* Close video modal */
		var closer = e.target.closest( '[data-afq-vclose]' );
		if ( closer ) {
			closeModal( closer.closest( '.afq-voice-modal' ) );
		}
	} );

	function closeModal( modal ) {
		if ( ! modal ) {
			return;
		}
		modal.classList.remove( 'is-open' );
		modal.setAttribute( 'aria-hidden', 'true' );
		modal.querySelector( '.afq-voice-modal__body' ).innerHTML = '';
		document.body.classList.remove( 'afq-voice-lock' );
	}

	document.addEventListener( 'keydown', function ( e ) {
		if ( 'Escape' === e.key ) {
			closeModal( document.querySelector( '.afq-voice-modal.is-open' ) );
		}
	} );

} )();
