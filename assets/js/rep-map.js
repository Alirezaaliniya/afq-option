( function () {
	"use strict";

	var isLoading = false;

	document.addEventListener( "click", function ( e ) {
		var spot = e.target.closest( ".afq-repmap__spot" );
		if ( ! spot || isLoading ) {
			return;
		}

		var wrap    = spot.closest( ".afq-repmap" );
		var results = wrap.querySelector( ".afq-repmap__results" );
		var termId  = spot.getAttribute( "data-afq-term" );

		wrap.querySelectorAll( ".afq-repmap__spot.is-active" ).forEach( function ( el ) {
			el.classList.remove( "is-active" );
		} );
		spot.classList.add( "is-active" );

		results.innerHTML = "<div class=\"afq-repmap__loading\">در حال دریافت نمایندگان...</div>";
		isLoading = true;

		var formData = new FormData();
		formData.append( "action", "afq_rep_filter" );
		formData.append( "nonce", afqRepMapCfg.nonce );
		formData.append( "term_id", termId );

		fetch( afqRepMapCfg.ajaxUrl, {
			method: "POST",
			credentials: "same-origin",
			body: formData
		} )
			.then( function ( response ) {
				return response.json();
			} )
			.then( function ( json ) {
				if ( json && json.success ) {
					results.innerHTML = json.data;
				} else {
					results.innerHTML = "<p class=\"afq-repmap__no-result\">خطا در دریافت اطلاعات. دوباره تلاش کنید.</p>";
				}
			} )
			.catch( function () {
				results.innerHTML = "<p class=\"afq-repmap__no-result\">خطا در دریافت اطلاعات. دوباره تلاش کنید.</p>";
			} )
			.finally( function () {
				isLoading = false;

				if ( window.innerWidth < 768 ) {
					results.scrollIntoView( { behavior: "smooth", block: "start" } );
				}
			} );
	} );
} )();
