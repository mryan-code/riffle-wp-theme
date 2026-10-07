<?php
/**
 * Theme setup: supports, menus, image sizes and widget areas.
 *
 * @package Riffle
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register theme supports, menus and image sizes.
 */
function riffle_setup() {
	load_theme_textdomain( 'riffle', RIFFLE_DIR . '/languages' );

	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'wp-block-styles' );
	add_theme_support( 'editor-styles' );
	add_theme_support(
		'html5',
		array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' )
	);
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 80,
			'width'       => 320,
			'flex-height' => true,
			'flex-width'  => true,
		)
	);
	add_theme_support( 'customize-selective-refresh-widgets' );

	add_editor_style( array( 'assets/css/editor.css' ) );

	register_nav_menus(
		array(
			'primary' => __( 'Primary navigation', 'riffle' ),
			'footer'  => __( 'Footer navigation', 'riffle' ),
		)
	);

	// Editorial image sizes. Cropped so cards and the hero never jump around.
	add_image_size( 'riffle-hero', 1600, 900, true );
	add_image_size( 'riffle-card', 720, 480, true );
	add_image_size( 'riffle-thumb', 160, 160, true );
}
add_action( 'after_setup_theme', 'riffle_setup' );

/**
 * Content width for embeds and media.
 */
function riffle_content_width() {
	$GLOBALS['content_width'] = (int) apply_filters( 'riffle_content_width', 720 );
}
add_action( 'after_setup_theme', 'riffle_content_width', 0 );

/**
 * Register widget areas.
 */
function riffle_widgets_init() {
	$shared = array(
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="widget__title">',
		'after_title'   => '</h2>',
	);

	register_sidebar(
		array_merge(
			$shared,
			array(
				'name'        => __( 'Article sidebar', 'riffle' ),
				'id'          => 'sidebar-article',
				'description' => __( 'Shown beside single stories on wide screens.', 'riffle' ),
			)
		)
	);

	for ( $i = 1; $i <= 3; $i++ ) {
		register_sidebar(
			array_merge(
				$shared,
				array(
					/* translators: %d: footer column number. */
					'name' => sprintf( __( 'Footer column %d', 'riffle' ), $i ),
					'id'   => 'footer-' . $i,
				)
			)
		);
	}
}
add_action( 'widgets_init', 'riffle_widgets_init' );

/**
 * Add useful body classes.
 *
 * @param string[] $classes Existing classes.
 * @return string[]
 */
function riffle_body_classes( $classes ) {
	if ( is_singular() && is_active_sidebar( 'sidebar-article' ) && is_single() ) {
		$classes[] = 'has-article-sidebar';
	}
	if ( is_singular() && riffle_is_members_only() ) {
		$classes[] = riffle_user_has_access() ? 'is-members-content' : 'is-gated-content';
	}
	return $classes;
}
add_filter( 'body_class', 'riffle_body_classes' );

/**
 * Feature the hero story first and keep it out of the grid below it.
 *
 * Runs only for the main posts query on the front page so archives,
 * feeds and REST are untouched.
 *
 * @param WP_Query $query Query instance.
 */
function riffle_exclude_hero_from_home( $query ) {
	if ( is_admin() || ! $query->is_main_query() || ! $query->is_home() ) {
		return;
	}

	$hero_id = riffle_get_hero_post_id();
	if ( $hero_id ) {
		$query->set( 'post__not_in', array( $hero_id ) );
	}
	$query->set( 'ignore_sticky_posts', true );
}
add_action( 'pre_get_posts', 'riffle_exclude_hero_from_home' );

/**
 * Replace the default "[...]" excerpt suffix with something calmer.
 *
 * @return string
 */
function riffle_excerpt_more() {
	return '&hellip;';
}
add_filter( 'excerpt_more', 'riffle_excerpt_more' );

/**
 * Shorter excerpts for cards.
 *
 * @return int
 */
function riffle_excerpt_length() {
	return 26;
}
add_filter( 'excerpt_length', 'riffle_excerpt_length' );
