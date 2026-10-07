<?php
/**
 * Asset loading.
 *
 * @package Riffle
 */

defined( 'ABSPATH' ) || exit;

/**
 * Enqueue front-end styles and scripts.
 */
function riffle_enqueue_assets() {
	wp_enqueue_style( 'riffle-style', get_stylesheet_uri(), array(), RIFFLE_VERSION );

	// Tiny, dependency-free navigation script. Deferred so it never blocks render.
	wp_enqueue_script(
		'riffle-navigation',
		RIFFLE_URI . '/assets/js/navigation.js',
		array(),
		RIFFLE_VERSION,
		array(
			'strategy'  => 'defer',
			'in_footer' => true,
		)
	);

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'riffle_enqueue_assets' );

/**
 * Preload the two variable fonts used above the fold.
 */
function riffle_preload_fonts() {
	$fonts = array( 'fraunces-latin-wght-normal.woff2', 'inter-latin-wght-normal.woff2' );
	foreach ( $fonts as $font ) {
		printf(
			'<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n",
			esc_url( RIFFLE_URI . '/assets/fonts/' . $font )
		);
	}
}
add_action( 'wp_head', 'riffle_preload_fonts', 1 );
