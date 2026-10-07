<?php
/**
 * Advertising: Google Publisher Tag (Ad Manager) slots.
 *
 * Slots are plain template parts with reserved space (no layout shift).
 * assets/js/ads.js loads gpt.js only when the first slot nears the
 * viewport, then lets GPT lazy-load the rest.
 *
 * @package Riffle
 */

defined( 'ABSPATH' ) || exit;

/**
 * Slot definitions. Filter to add, remove or resize placements.
 *
 * @return array<string, array>
 */
function riffle_ad_slots() {
	$slots = array(
		'leaderboard' => array(
			'label'  => __( 'Leaderboard', 'riffle' ),
			'sizes'  => array( array( 728, 90 ), array( 970, 90 ) ),
			'mobile' => array( array( 320, 50 ), array( 300, 50 ) ),
		),
		'in-article'  => array(
			'label'  => __( 'In-article', 'riffle' ),
			'sizes'  => array( array( 300, 250 ), array( 336, 280 ) ),
			'mobile' => array( array( 300, 250 ) ),
		),
		'sidebar'     => array(
			'label'  => __( 'Sidebar', 'riffle' ),
			'sizes'  => array( array( 300, 250 ), array( 300, 600 ) ),
			'mobile' => array( array( 300, 250 ) ),
		),
	);

	/**
	 * Filter ad slot definitions.
	 *
	 * @param array $slots Slot name => label, desktop sizes, mobile sizes.
	 */
	return (array) apply_filters( 'riffle_ad_slots', $slots );
}

/**
 * Should ads render on this request?
 *
 * Example: give paying members an ad-light experience.
 *
 *     add_filter( 'riffle_show_ads', fn ( $show ) => $show && ! my_is_paid_member() );
 *
 * @param string $slot Slot name.
 * @return bool
 */
function riffle_show_ads( $slot = '' ) {
	$show = ! is_404() && ! is_search() && ! post_password_required();

	if ( ! riffle_mod( 'riffle_gpt_network' ) && ! riffle_mod( 'riffle_ad_placeholders' ) ) {
		$show = false;
	}

	/**
	 * Filter whether an ad slot should render.
	 *
	 * @param bool   $show Whether to show the slot.
	 * @param string $slot Slot name.
	 */
	return (bool) apply_filters( 'riffle_show_ads', $show, $slot );
}

/**
 * Print an ad slot.
 *
 * @param string $slot Slot name from riffle_ad_slots().
 */
function riffle_ad_slot( $slot ) {
	$slots = riffle_ad_slots();
	if ( empty( $slots[ $slot ] ) || ! riffle_show_ads( $slot ) ) {
		return;
	}
	get_template_part(
		'template-parts/ad-slot',
		null,
		array(
			'slot'   => $slot,
			'config' => $slots[ $slot ],
		)
	);
}

/**
 * Full GPT ad unit path for a slot, e.g. /1234567/riffle/leaderboard.
 *
 * @param string $slot Slot name.
 * @return string
 */
function riffle_ad_unit_path( $slot ) {
	$network = riffle_sanitize_gpt_network( riffle_mod( 'riffle_gpt_network' ) );
	$prefix  = sanitize_title( (string) apply_filters( 'riffle_ad_unit_prefix', get_bloginfo( 'name' ) ) );
	return '/' . $network . '/' . $prefix . '/' . sanitize_key( $slot );
}

/**
 * Enqueue the GPT loader once, with page-level targeting.
 */
function riffle_enqueue_ads_script() {
	if ( wp_script_is( 'riffle-ads', 'enqueued' ) || ! riffle_mod( 'riffle_gpt_network' ) ) {
		return;
	}

	$targeting = array( 'page_type' => is_singular() ? 'article' : 'index' );
	if ( is_singular( 'post' ) ) {
		$cat                  = riffle_primary_category( get_queried_object_id() );
		$targeting['section'] = $cat ? $cat->slug : '';
		$targeting['members'] = riffle_is_members_only( get_queried_object_id() ) ? 'yes' : 'no';
	}

	wp_enqueue_script(
		'riffle-ads',
		RIFFLE_URI . '/assets/js/ads.js',
		array(),
		RIFFLE_VERSION,
		array(
			'strategy'  => 'defer',
			'in_footer' => true,
		)
	);
	wp_add_inline_script(
		'riffle-ads',
		'window.riffleAds = ' . wp_json_encode(
			array( 'targeting' => apply_filters( 'riffle_ad_targeting', $targeting ) ),
			JSON_HEX_TAG | JSON_HEX_AMP
		) . ';',
		'before'
	);
}

/**
 * Insert the in-article slot after the Nth paragraph of long stories.
 *
 * @param string $content Post content.
 * @return string
 */
function riffle_insert_in_article_ad( $content ) {
	if ( ! is_singular( 'post' ) || ! in_the_loop() || ! is_main_query() || doing_filter( 'get_the_excerpt' ) ) {
		return $content;
	}
	if ( get_queried_object_id() !== get_the_ID() || riffle_is_locked() ) {
		return $content;
	}

	$after = max( 1, (int) apply_filters( 'riffle_in_article_ad_after', 3 ) );
	$parts = explode( '</p>', $content );

	// Only for stories long enough that the ad does not dominate.
	if ( count( $parts ) < $after + 3 ) {
		return $content;
	}

	ob_start();
	riffle_ad_slot( 'in-article' );
	$ad = (string) ob_get_clean();

	if ( '' === $ad ) {
		return $content;
	}

	$parts[ $after - 1 ] .= '</p>' . $ad;
	$head                 = implode( '</p>', array_slice( $parts, 0, $after ) );
	$tail                 = implode( '</p>', array_slice( $parts, $after ) );

	return $head . $tail;
}
add_filter( 'the_content', 'riffle_insert_in_article_ad', 30 );
