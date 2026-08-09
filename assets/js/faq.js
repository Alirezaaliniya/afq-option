( function () {
	"use strict";

	document.addEventListener( "click", function ( e ) {
		var btn = e.target.closest( ".afq-faq__question" );
		if ( ! btn ) {
			return;
		}

		var item = btn.closest( ".afq-faq__item" );
		var list = btn.closest( ".afq-faq" );
		var open = item.classList.contains( "is-open" );

		/* Close other items in the same list (accordion behavior). */
		list.querySelectorAll( ".afq-faq__item.is-open" ).forEach( function ( openItem ) {
			if ( openItem !== item ) {
				openItem.classList.remove( "is-open" );
				openItem.querySelector( ".afq-faq__question" ).setAttribute( "aria-expanded", "false" );
			}
		} );

		item.classList.toggle( "is-open", ! open );
		btn.setAttribute( "aria-expanded", open ? "false" : "true" );
	} );
} )();
