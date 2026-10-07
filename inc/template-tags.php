<?php
/**
 * Template tags: small helpers that keep templates readable.
 *
 * Every helper escapes at the point of output. Helpers prefixed `get_`
 * return already-escaped HTML strings.
 *
 * @package Riffle
 */

defined( 'ABSPATH' ) || exit;

/**
 * Pick the story to feature in the front-page hero.
 *
 * Order of preference: the newest sticky post, then the newest post.
 * Cached per request because it is used by both the template and
 * the pre_get_posts filter.
 *
 * @return int Post ID or 0 when the site has no posts.
 */
function riffle_get_hero_post_id() {
	static $hero_id = null;

	if ( null !== $hero_id ) {
		return $hero_id;
	}

	$hero_id = 0;
	$sticky  = array_map( 'absint', (array) get_option( 'sticky_posts', array() ) );
	$args    = array(
		'post_type'           => 'post',
		'post_status'         => 'publish',
		'posts_per_page'      => 1,
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
		'fields'              => 'ids',
	);

	if ( $sticky ) {
		$ids = get_posts( array_merge( $args, array( 'post__in' => $sticky ) ) );
	}
	if ( empty( $ids ) ) {
		$ids = get_posts( $args );
	}
	if ( ! empty( $ids ) ) {
		$hero_id = (int) $ids[0];
	}

	/**
	 * Filter the post used as the front-page hero.
	 *
	 * @param int $hero_id Post ID, or 0 for none.
	 */
	$hero_id = (int) apply_filters( 'riffle_hero_post_id', $hero_id );

	return $hero_id;
}

/**
 * Print the post date as a <time> element.
 */
function riffle_posted_on() {
	printf(
		'<time class="entry-date" datetime="%1$s">%2$s</time>',
		esc_attr( get_the_date( DATE_W3C ) ),
		esc_html( get_the_date() )
	);
}

/**
 * Print the author byline.
 */
function riffle_posted_by() {
	printf(
		'<span class="byline">%1$s <a class="byline__author" href="%2$s">%3$s</a></span>',
		esc_html_x( 'By', 'post author prefix', 'riffle' ),
		esc_url( get_author_posts_url( (int) get_the_author_meta( 'ID' ) ) ),
		esc_html( get_the_author() )
	);
}

/**
 * Estimated reading time in minutes, based on the raw post content.
 *
 * @param int|WP_Post|null $post Post.
 * @return int
 */
function riffle_reading_time( $post = null ) {
	$post = get_post( $post );
	if ( ! $post ) {
		return 0;
	}
	$words = str_word_count( wp_strip_all_tags( strip_shortcodes( $post->post_content ) ) );

	/** Filter the words-per-minute rate used for reading time. */
	$wpm = max( 1, (int) apply_filters( 'riffle_words_per_minute', 230 ) );

	return max( 1, (int) ceil( $words / $wpm ) );
}

/**
 * Print the reading time label.
 */
function riffle_reading_time_label() {
	$minutes = riffle_reading_time();
	printf(
		'<span class="reading-time">%s</span>',
		/* translators: %d: minutes. */
		esc_html( sprintf( _n( '%d min read', '%d min read', $minutes, 'riffle' ), $minutes ) )
	);
}

/**
 * First category of a post, used as the "kicker" above headlines.
 *
 * @param int|null $post_id Post ID.
 * @return WP_Term|null
 */
function riffle_primary_category( $post_id = null ) {
	$cats = get_the_category( $post_id ? $post_id : get_the_ID() );
	if ( empty( $cats ) ) {
		return null;
	}
	// Never use the members category as the visible kicker; it has its own badge.
	foreach ( $cats as $cat ) {
		if ( riffle_members_category_slug() !== $cat->slug ) {
			return $cat;
		}
	}
	return $cats[0];
}

/**
 * Print the category kicker link.
 */
function riffle_kicker() {
	$cat = riffle_primary_category();
	// Posts filed only under the members category already show the badge.
	if ( ! $cat || riffle_members_category_slug() === $cat->slug ) {
		return;
	}
	printf(
		'<a class="kicker" href="%1$s">%2$s</a>',
		esc_url( get_category_link( $cat ) ),
		esc_html( $cat->name )
	);
}

/**
 * Print a "Members" badge when the current post is gated.
 */
function riffle_members_badge() {
	if ( ! riffle_is_members_only() ) {
		return;
	}
	printf(
		'<span class="badge badge--members"><svg aria-hidden="true" focusable="false" width="12" height="12" viewBox="0 0 24 24"><path fill="currentColor" d="M12 2a5 5 0 0 0-5 5v3H6a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8a2 2 0 0 0-2-2h-1V7a5 5 0 0 0-5-5Zm-3 8V7a3 3 0 1 1 6 0v3H9Z"/></svg>%s</span>',
		esc_html__( 'Members', 'riffle' )
	);
}

/**
 * Print the featured image, or a decorative fallback so grids stay even.
 *
 * @param string $size Image size.
 * @param bool   $link Wrap in a link to the post.
 */
function riffle_post_thumbnail( $size = 'riffle-card', $link = true ) {
	if ( post_password_required() ) {
		return;
	}

	$classes = 'post-thumb post-thumb--' . sanitize_html_class( $size );

	if ( has_post_thumbnail() ) {
		$image = get_the_post_thumbnail(
			null,
			$size,
			array(
				'loading' => 'riffle-hero' === $size ? 'eager' : 'lazy',
				'alt'     => '',
			)
		);
	} else {
		$classes .= ' post-thumb--fallback';
		$image    = '<span class="post-thumb__pattern" aria-hidden="true"></span>';
	}

	if ( $link ) {
		// The image is decorative here: the headline link carries the accessible name.
		printf(
			'<a class="%1$s" href="%2$s" tabindex="-1" aria-hidden="true">%3$s</a>',
			esc_attr( $classes ),
			esc_url( get_permalink() ),
			$image // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- core-generated image markup.
		);
		return;
	}

	printf(
		'<div class="%1$s">%2$s</div>',
		esc_attr( $classes ),
		$image // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- core-generated image markup.
	);
}

/**
 * Print the site branding (logo or text title).
 */
function riffle_site_branding() {
	if ( has_custom_logo() ) {
		the_custom_logo();
		return;
	}
	$tag = ( is_front_page() && ! is_paged() ) ? 'h1' : 'p';
	printf(
		'<%1$s class="site-title"><a href="%2$s" rel="home">%3$s</a></%1$s>',
		tag_escape( $tag ),
		esc_url( home_url( '/' ) ),
		esc_html( get_bloginfo( 'name' ) )
	);
}

/**
 * Related stories from the same primary category.
 *
 * @param int $count Number of posts.
 * @return WP_Query
 */
function riffle_related_query( $count = 3 ) {
	$cat  = riffle_primary_category();
	$args = array(
		'post_type'           => 'post',
		'posts_per_page'      => $count,
		'post__not_in'        => array( get_the_ID() ),
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
	);
	if ( $cat ) {
		$args['cat'] = $cat->term_id;
	}
	return new WP_Query( apply_filters( 'riffle_related_query_args', $args ) );
}

/**
 * Fallback for the primary menu so a fresh install still has navigation.
 */
function riffle_primary_menu_fallback() {
	$cats = get_categories(
		array(
			'orderby' => 'count',
			'order'   => 'DESC',
			'number'  => 5,
			'exclude' => array( (int) get_option( 'default_category' ) ),
		)
	);
	echo '<ul id="primary-menu" class="menu">';
	foreach ( $cats as $cat ) {
		printf(
			'<li class="menu-item"><a href="%1$s"%3$s>%2$s</a></li>',
			esc_url( get_category_link( $cat ) ),
			esc_html( $cat->name ),
			is_category( $cat->term_id ) ? ' aria-current="page"' : ''
		);
	}
	echo '</ul>';
}

/**
 * Members badge that does not depend on the current post (archive headers).
 */
function riffle_members_badge_static() {
	printf( '<span class="badge badge--members">%s</span>', esc_html__( 'Members only', 'riffle' ) );
}

/**
 * Small label above archive titles.
 *
 * @return string Unescaped label.
 */
function riffle_archive_eyebrow() {
	if ( is_category() ) {
		return __( 'Section', 'riffle' );
	}
	if ( is_tag() ) {
		return __( 'Topic', 'riffle' );
	}
	if ( is_author() ) {
		return __( 'Contributor', 'riffle' );
	}
	if ( is_date() ) {
		return __( 'From the archive', 'riffle' );
	}
	return __( 'Archive', 'riffle' );
}

/**
 * Strip the "Category:" style prefix from archive titles; the eyebrow says it.
 *
 * @param string $title  Archive title.
 * @param string $original Original title without prefix.
 * @return string
 */
function riffle_archive_title( $title, $original = '' ) {
	return ( is_category() || is_tag() || is_author() || is_tax() ) && $original ? $original : $title;
}
add_filter( 'get_the_archive_title', 'riffle_archive_title', 10, 2 );
