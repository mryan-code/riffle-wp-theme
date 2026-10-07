<?php
/**
 * Analytics: GA4 or Google Tag Manager, plus a publisher-friendly dataLayer.
 *
 * IDs come from the Customizer and are pattern-validated on save (see
 * inc/customizer.php). Nothing loads until an ID is set.
 *
 * @package Riffle
 */

defined( 'ABSPATH' ) || exit;

/**
 * Should tracking code be printed for this request?
 *
 * @return bool
 */
function riffle_should_track() {
	$configured = riffle_mod( 'riffle_ga4_id' ) || riffle_mod( 'riffle_gtm_id' );
	$staff      = current_user_can( 'edit_posts' ) && ! riffle_mod( 'riffle_track_logged_in' );
	$track      = $configured && ! $staff && ! is_customize_preview();

	/** Filter whether analytics should be output on this request. */
	return (bool) apply_filters( 'riffle_should_track', $track );
}

/**
 * Page-level context pushed to the dataLayer before any tags fire.
 * Lets an analyst build "members vs. guests" or "content group" reports
 * without touching the theme again.
 *
 * @return array<string, string>
 */
function riffle_datalayer_context() {
	$context = array(
		'page_type'   => 'other',
		'user_status' => is_user_logged_in() ? 'logged_in' : 'guest',
	);

	if ( is_front_page() ) {
		$context['page_type'] = 'home';
	} elseif ( is_singular( 'post' ) ) {
		$post_id                     = get_queried_object_id();
		$cat                         = riffle_primary_category( $post_id );
		$context['page_type']        = 'article';
		$context['content_group']    = $cat ? $cat->name : '';
		$context['author']           = get_the_author_meta( 'display_name', (int) get_post_field( 'post_author', $post_id ) );
		$context['publish_date']     = get_the_date( 'Y-m-d', $post_id );
		$context['members_only']     = riffle_is_members_only( $post_id ) ? 'yes' : 'no';
		$context['content_unlocked'] = riffle_is_locked( $post_id ) ? 'no' : 'yes';
	} elseif ( is_page() ) {
		$context['page_type'] = 'page';
	} elseif ( is_category() || is_tag() || is_tax() ) {
		$context['page_type']     = 'section';
		$context['content_group'] = single_term_title( '', false );
	} elseif ( is_search() ) {
		$context['page_type'] = 'search';
	} elseif ( is_404() ) {
		$context['page_type'] = '404';
	} elseif ( is_archive() ) {
		$context['page_type'] = 'archive';
	}

	/**
	 * Filter the dataLayer page context.
	 *
	 * @param array $context Key/value pairs.
	 */
	return (array) apply_filters( 'riffle_datalayer', $context );
}

/**
 * Print dataLayer + GTM (preferred) or gtag.js config in <head>.
 */
function riffle_print_analytics_head() {
	if ( ! riffle_should_track() ) {
		return;
	}

	$gtm = riffle_sanitize_gtm_id( riffle_mod( 'riffle_gtm_id' ) );
	$ga4 = riffle_sanitize_ga4_id( riffle_mod( 'riffle_ga4_id' ) );
	$ctx = wp_json_encode( riffle_datalayer_context(), JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_SLASHES );

	$js = "window.dataLayer = window.dataLayer || [];\nwindow.dataLayer.push({$ctx});\n";

	if ( $gtm ) {
		$js .= sprintf(
			"(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer',%s);",
			wp_json_encode( $gtm )
		);
	} elseif ( $ga4 ) {
		$js .= sprintf(
			"function gtag(){dataLayer.push(arguments);}\ngtag('js', new Date());\ngtag('config', %1\$s, %2\$s);",
			wp_json_encode( $ga4 ),
			$ctx
		);
		printf(
			'<script async src="%s"></script>' . "\n", // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript -- must sit in <head> before config.
			esc_url( 'https://www.googletagmanager.com/gtag/js?id=' . rawurlencode( $ga4 ) )
		);
	}

	wp_print_inline_script_tag( $js, array( 'id' => 'riffle-analytics' ) );
}
add_action( 'wp_head', 'riffle_print_analytics_head', 2 );

/**
 * GTM <noscript> fallback right after <body>.
 */
function riffle_print_gtm_noscript() {
	if ( ! riffle_should_track() ) {
		return;
	}
	$gtm = riffle_sanitize_gtm_id( riffle_mod( 'riffle_gtm_id' ) );
	if ( ! $gtm ) {
		return;
	}
	printf(
		'<noscript><iframe src="%s" height="0" width="0" style="display:none;visibility:hidden" title="%s"></iframe></noscript>',
		esc_url( 'https://www.googletagmanager.com/ns.html?id=' . rawurlencode( $gtm ) ),
		esc_attr__( 'Google Tag Manager', 'riffle' )
	);
}
add_action( 'wp_body_open', 'riffle_print_gtm_noscript' );

/**
 * Lightweight event helper: any element with data-riffle-event pushes to the
 * dataLayer on click, and the members gate reports an impression.
 */
function riffle_enqueue_analytics_events() {
	if ( ! riffle_should_track() ) {
		return;
	}
	wp_enqueue_script(
		'riffle-events',
		RIFFLE_URI . '/assets/js/events.js',
		array(),
		RIFFLE_VERSION,
		array(
			'strategy'  => 'defer',
			'in_footer' => true,
		)
	);
}
add_action( 'wp_enqueue_scripts', 'riffle_enqueue_analytics_events' );
