/**
 * Riffle navigation: mobile menu and header search disclosure.
 * No dependencies. Uses aria-expanded as the single source of truth.
 */
( () => {
	'use strict';

	const header = document.querySelector( '.site-header' );
	if ( ! header ) {
		return;
	}

	const navToggle = header.querySelector( '.nav-toggle' );
	const searchToggle = header.querySelector( '.search-toggle' );
	const searchPanel = document.getElementById( 'header-search' );

	const setExpanded = ( button, expanded ) => {
		button.setAttribute( 'aria-expanded', String( expanded ) );
	};

	if ( navToggle ) {
		navToggle.addEventListener( 'click', () => {
			const open = navToggle.getAttribute( 'aria-expanded' ) !== 'true';
			setExpanded( navToggle, open );
			header.classList.toggle( 'is-nav-open', open );
		} );
	}

	if ( searchToggle && searchPanel ) {
		searchToggle.addEventListener( 'click', () => {
			const open = searchPanel.hidden;
			searchPanel.hidden = ! open;
			setExpanded( searchToggle, open );
			if ( open ) {
				const field = searchPanel.querySelector( 'input[type="search"]' );
				if ( field ) {
					field.focus();
				}
			}
		} );
	}

	// Escape closes whatever is open and returns focus to its toggle.
	document.addEventListener( 'keydown', ( event ) => {
		if ( event.key !== 'Escape' ) {
			return;
		}
		if ( searchPanel && ! searchPanel.hidden ) {
			searchPanel.hidden = true;
			setExpanded( searchToggle, false );
			searchToggle.focus();
		} else if ( navToggle && navToggle.getAttribute( 'aria-expanded' ) === 'true' ) {
			setExpanded( navToggle, false );
			header.classList.remove( 'is-nav-open' );
			navToggle.focus();
		}
	} );

	// Close the mobile menu if the viewport grows past the breakpoint.
	const wide = window.matchMedia( '(min-width: 60em)' );
	wide.addEventListener( 'change', ( event ) => {
		if ( event.matches && navToggle ) {
			setExpanded( navToggle, false );
			header.classList.remove( 'is-nav-open' );
		}
	} );
} )();
