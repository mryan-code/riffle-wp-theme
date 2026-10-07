/**
 * Riffle events: tiny dataLayer helpers.
 *
 * - Any element with data-riffle-event pushes { event, location } on click.
 * - The members gate pushes a single impression when it is actually seen.
 */
( () => {
	'use strict';

	const push = ( payload ) => {
		window.dataLayer = window.dataLayer || [];
		window.dataLayer.push( payload );
	};

	document.addEventListener( 'click', ( event ) => {
		const el = event.target.closest( '[data-riffle-event]' );
		if ( el ) {
			push( {
				event: el.dataset.riffleEvent,
				location: el.dataset.riffleLocation || '',
			} );
		}
	} );

	const gate = document.querySelector( '[data-riffle-gate]' );
	if ( gate && 'IntersectionObserver' in window ) {
		const observer = new IntersectionObserver( ( entries ) => {
			if ( entries.some( ( entry ) => entry.isIntersecting ) ) {
				observer.disconnect();
				push( { event: 'members_gate_view' } );
			}
		}, { threshold: 0.5 } );
		observer.observe( gate );
	}
} )();
