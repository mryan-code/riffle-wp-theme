<?php
/**
 * Riffle functions and definitions.
 *
 * Kept deliberately thin: each concern lives in its own file under /inc so
 * the theme reads like a small, well-organised codebase rather than one
 * thousand-line functions.php.
 *
 * @package Riffle
 */

defined( 'ABSPATH' ) || exit;

define( 'RIFFLE_VERSION', '1.0.0' );
define( 'RIFFLE_DIR', get_template_directory() );
define( 'RIFFLE_URI', get_template_directory_uri() );

$riffle_includes = array(
	'inc/setup.php',         // Theme supports, menus, image sizes, widget areas.
	'inc/enqueue.php',       // Front-end and editor assets.
	'inc/template-tags.php', // Small, reusable markup helpers.
	'inc/customizer.php',    // Customizer settings with strict sanitizers.
	'inc/members.php',       // Members-only content gate.
	'inc/analytics.php',     // GA4 / GTM output and dataLayer context.
	'inc/ads.php',           // Google Publisher Tag ad slots.
);

foreach ( $riffle_includes as $riffle_file ) {
	require_once RIFFLE_DIR . '/' . $riffle_file;
}
unset( $riffle_includes, $riffle_file );
