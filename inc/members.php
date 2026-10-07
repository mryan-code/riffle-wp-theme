<?php
/**
 * Members-only content gate.
 *
 * A post is "members only" when it has the `_riffle_members_only` flag
 * (editable from a meta box or the REST API) or sits in the members
 * category. Visitors without access get the opening paragraphs plus a
 * call to action; the rest of the post never leaves the server, including
 * through feeds and the REST API.
 *
 * Access defaults to "logged in", and is filterable so a membership plugin
 * (MemberPress, Restrict Content Pro, WooCommerce Memberships, a custom
 * Stripe integration...) can make the real decision:
 *
 *     add_filter( 'riffle_user_has_access', function ( $has_access, $user_id, $post ) {
 *         return my_membership_is_active( $user_id );
 *     }, 10, 3 );
 *
 * @package Riffle
 */

defined( 'ABSPATH' ) || exit;

const RIFFLE_MEMBERS_META = '_riffle_members_only';

/**
 * Slug of the category that marks content as members only.
 *
 * @return string
 */
function riffle_members_category_slug() {
	return (string) apply_filters( 'riffle_members_category', 'members' );
}

/**
 * Register the post meta so it is typed, defaulted and editable via REST.
 */
function riffle_register_members_meta() {
	register_post_meta(
		'post',
		RIFFLE_MEMBERS_META,
		array(
			'type'              => 'boolean',
			'single'            => true,
			'default'           => false,
			'show_in_rest'      => true,
			'sanitize_callback' => 'rest_sanitize_boolean',
			'auth_callback'     => static function ( $allowed, $meta_key, $post_id ) {
				return current_user_can( 'edit_post', $post_id );
			},
		)
	);
}
add_action( 'init', 'riffle_register_members_meta' );

/**
 * Is a post members only?
 *
 * @param int|WP_Post|null $post Post (defaults to the current post).
 * @return bool
 */
function riffle_is_members_only( $post = null ) {
	$post = get_post( $post );
	if ( ! $post || 'post' !== $post->post_type ) {
		return false;
	}

	$gated = (bool) get_post_meta( $post->ID, RIFFLE_MEMBERS_META, true )
		|| has_category( riffle_members_category_slug(), $post );

	/**
	 * Filter whether a post is members only.
	 *
	 * @param bool    $gated Whether the post is gated.
	 * @param WP_Post $post  Post object.
	 */
	return (bool) apply_filters( 'riffle_is_members_only', $gated, $post );
}

/**
 * Can the current visitor read members-only content?
 *
 * @param int|WP_Post|null $post Post being viewed.
 * @return bool
 */
function riffle_user_has_access( $post = null ) {
	$post    = get_post( $post );
	$user_id = get_current_user_id();

	/**
	 * Filter whether the current user can read members-only content.
	 *
	 * @param bool         $has_access Default: true for any logged-in user.
	 * @param int          $user_id    Current user ID (0 when logged out).
	 * @param WP_Post|null $post       Post being viewed.
	 */
	return (bool) apply_filters( 'riffle_user_has_access', $user_id > 0, $user_id, $post );
}

/**
 * Should the current post be shown as a teaser?
 *
 * @param int|WP_Post|null $post Post.
 * @return bool
 */
function riffle_is_locked( $post = null ) {
	return riffle_is_members_only( $post ) && ! riffle_user_has_access( $post );
}

/**
 * Build the teaser: the first few paragraphs of already-rendered content.
 *
 * @param string $content Rendered post content.
 * @return string
 */
function riffle_build_teaser( $content ) {
	$count = max( 1, (int) apply_filters( 'riffle_teaser_paragraphs', 2 ) );

	if ( preg_match_all( '#<p\b[^>]*>.*?</p>#is', $content, $matches ) && ! empty( $matches[0] ) ) {
		$teaser = implode( "\n", array_slice( $matches[0], 0, $count ) );
	} else {
		$teaser = wpautop( wp_trim_words( wp_strip_all_tags( $content ), 60 ) );
	}

	return '<div class="entry-teaser">' . $teaser . '</div>';
}

/**
 * Render the members CTA template part to a string.
 *
 * @return string
 */
function riffle_get_members_gate() {
	ob_start();
	get_template_part( 'template-parts/members-gate', null, array( 'post_id' => get_the_ID() ) );
	return (string) ob_get_clean();
}

/**
 * Swap full content for teaser + CTA on single views.
 *
 * Scoped to the main singular loop so excerpts, widgets and related-post
 * cards (which also run `the_content` internally) are never affected.
 *
 * @param string $content Post content.
 * @return string
 */
function riffle_gate_the_content( $content ) {
	if ( ! is_singular( 'post' ) || ! in_the_loop() || ! is_main_query() || doing_filter( 'get_the_excerpt' ) ) {
		return $content;
	}
	if ( get_queried_object_id() !== get_the_ID() || ! riffle_is_locked() ) {
		return $content;
	}
	return riffle_build_teaser( $content ) . riffle_get_members_gate();
}
add_filter( 'the_content', 'riffle_gate_the_content', 99 );

/**
 * Do not leak members content through RSS/Atom feeds.
 *
 * @param string $content Feed content.
 * @return string
 */
function riffle_gate_feed_content( $content ) {
	if ( ! riffle_is_members_only() ) {
		return $content;
	}
	return riffle_build_teaser( $content ) . sprintf(
		'<p><a href="%1$s">%2$s</a></p>',
		esc_url( get_permalink() ),
		esc_html__( 'Members only: continue reading on the site.', 'riffle' )
	);
}
add_filter( 'the_content_feed', 'riffle_gate_feed_content' );

/**
 * Do not leak members content through the REST API either.
 *
 * @param WP_REST_Response $response Response.
 * @param WP_Post          $post     Post.
 * @return WP_REST_Response
 */
function riffle_gate_rest_content( $response, $post ) {
	if ( ! riffle_is_locked( $post ) ) {
		return $response;
	}
	$data = $response->get_data();
	if ( isset( $data['content']['rendered'] ) ) {
		$data['content']['rendered']  = riffle_build_teaser( $data['content']['rendered'] );
		$data['content']['protected'] = true;
		$data['riffle_members_only']  = true;
	}
	$response->set_data( $data );
	return $response;
}
add_filter( 'rest_prepare_post', 'riffle_gate_rest_content', 10, 2 );

/**
 * Structured data so search engines understand the gate is a paywall,
 * not cloaking. See Google's "subscription and paywalled content" docs.
 */
function riffle_members_structured_data() {
	if ( ! is_singular( 'post' ) || ! riffle_is_members_only( get_queried_object_id() ) ) {
		return;
	}
	$post = get_post( get_queried_object_id() );
	$data = array(
		'@context'            => 'https://schema.org',
		'@type'               => 'NewsArticle',
		'headline'            => wp_strip_all_tags( get_the_title( $post ) ),
		'datePublished'       => get_the_date( DATE_W3C, $post ),
		'dateModified'        => get_the_modified_date( DATE_W3C, $post ),
		'author'              => array(
			'@type' => 'Person',
			'name'  => get_the_author_meta( 'display_name', (int) $post->post_author ),
		),
		'isAccessibleForFree' => false,
		'hasPart'             => array(
			'@type'               => 'WebPageElement',
			'isAccessibleForFree' => false,
			'cssSelector'         => '.entry-content--members',
		),
	);
	if ( has_post_thumbnail( $post ) ) {
		$data['image'] = get_the_post_thumbnail_url( $post, 'riffle-hero' );
	}
	wp_print_inline_script_tag(
		wp_json_encode( $data, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP ),
		array( 'type' => 'application/ld+json' )
	);
}
add_action( 'wp_head', 'riffle_members_structured_data' );

/**
 * Join URL: customizer value, else core registration if it is open.
 *
 * @return string Raw URL (escape on output) or empty string.
 */
function riffle_members_join_url() {
	$url = (string) riffle_mod( 'riffle_members_join_url' );
	if ( '' === $url && get_option( 'users_can_register' ) ) {
		$url = wp_registration_url();
	}
	return (string) apply_filters( 'riffle_members_join_url', $url );
}

/**
 * Query for the front-page "Members' Journal" band.
 *
 * @param int $count Number of posts.
 * @return WP_Query
 */
function riffle_members_query( $count = 3 ) {
	return new WP_Query(
		array(
			'post_type'           => 'post',
			'posts_per_page'      => $count,
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
			'post__not_in'        => array_filter( array( riffle_get_hero_post_id() ) ),
			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query, WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- small, cached front-page query.
			'tax_query'           => array(
				array(
					'taxonomy' => 'category',
					'field'    => 'slug',
					'terms'    => riffle_members_category_slug(),
				),
			),
		)
	);
}

/*
 * Editor UI: a small meta box. It works in both the block editor (as a
 * legacy panel) and the classic editor, with a nonce and capability check.
 */

/**
 * Register the meta box.
 */
function riffle_add_members_meta_box() {
	add_meta_box(
		'riffle-members',
		__( 'Membership', 'riffle' ),
		'riffle_render_members_meta_box',
		'post',
		'side',
		'high'
	);
}
add_action( 'add_meta_boxes', 'riffle_add_members_meta_box' );

/**
 * Render the meta box.
 *
 * @param WP_Post $post Post being edited.
 */
function riffle_render_members_meta_box( $post ) {
	wp_nonce_field( 'riffle_members_save', 'riffle_members_nonce' );
	$checked = (bool) get_post_meta( $post->ID, RIFFLE_MEMBERS_META, true );
	?>
	<p>
		<label>
			<input type="checkbox" name="riffle_members_only" value="1" <?php checked( $checked ); ?> />
			<?php esc_html_e( 'Members only', 'riffle' ); ?>
		</label>
	</p>
	<p class="description">
		<?php
		printf(
			/* translators: %s: category slug. */
			esc_html__( 'Logged-out readers see the opening paragraphs and a join prompt. Posts in the "%s" category are gated automatically.', 'riffle' ),
			esc_html( riffle_members_category_slug() )
		);
		?>
	</p>
	<?php
}

/**
 * Save the meta box value.
 *
 * @param int $post_id Post ID.
 */
function riffle_save_members_meta_box( $post_id ) {
	if ( ! isset( $_POST['riffle_members_nonce'] ) ) {
		return;
	}
	if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['riffle_members_nonce'] ) ), 'riffle_members_save' ) ) {
		return;
	}
	if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || wp_is_post_revision( $post_id ) ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$value = ! empty( $_POST['riffle_members_only'] );
	if ( $value ) {
		update_post_meta( $post_id, RIFFLE_MEMBERS_META, true );
	} else {
		delete_post_meta( $post_id, RIFFLE_MEMBERS_META );
	}
}
add_action( 'save_post_post', 'riffle_save_members_meta_box' );
