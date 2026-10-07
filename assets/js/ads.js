/**
 * Riffle ads: lazy Google Publisher Tag loader.
 *
 * gpt.js itself is not requested until the first slot is within ~one
 * viewport of being seen. After that, GPT's own lazy loading handles
 * fetching and rendering the remaining slots.
 */
( () => {
	'use strict';

	const slots = Array.from( document.querySelectorAll( '[data-riffle-ad]' ) );
	if ( ! slots.length ) {
		return;
	}

	const config = window.riffleAds || {};
	let loaded = false;

	const parse = ( value, fallback ) => {
		try {
			return JSON.parse( value );
		} catch ( e ) {
			return fallback;
		}
	};

	const load = () => {
		if ( loaded ) {
			return;
		}
		loaded = true;

		window.googletag = window.googletag || { cmd: [] };

		const script = document.createElement( 'script' );
		script.src = 'https://securepubads.g.doubleclick.net/tag/js/gpt.js';
		script.async = true;
		script.crossOrigin = 'anonymous';
		document.head.appendChild( script );

		window.googletag.cmd.push( () => {
			const { googletag } = window;
			const pubads = googletag.pubads();

			pubads.enableLazyLoad( {
				fetchMarginPercent: 100,
				renderMarginPercent: 50,
				mobileScaling: 2,
			} );
			pubads.collapseEmptyDivs();

			Object.entries( config.targeting || {} ).forEach( ( [ key, value ] ) => {
				if ( value ) {
					pubads.setTargeting( key, String( value ) );
				}
			} );

			slots.forEach( ( el ) => {
				const sizes = parse( el.dataset.sizes, [] );
				const mobile = parse( el.dataset.mobileSizes, sizes );
				const mapping = googletag
					.sizeMapping()
					.addSize( [ 768, 0 ], sizes )
					.addSize( [ 0, 0 ], mobile )
					.build();
				const slot = googletag.defineSlot( el.dataset.unit, sizes.concat( mobile ), el.id );
				if ( slot ) {
					slot.defineSizeMapping( mapping ).addService( pubads );
				}
			} );

			googletag.enableServices();
			slots.forEach( ( el ) => googletag.display( el.id ) );
		} );
	};

	if ( ! ( 'IntersectionObserver' in window ) ) {
		load();
		return;
	}

	const observer = new IntersectionObserver(
		( entries ) => {
			if ( entries.some( ( entry ) => entry.isIntersecting ) ) {
				observer.disconnect();
				load();
			}
		},
		{ rootMargin: '100% 0px' }
	);
	slots.forEach( ( el ) => observer.observe( el ) );
} )();
