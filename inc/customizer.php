<?php
/**
 * Customizer settings.
 *
 * Every setting has an explicit sanitize_callback. IDs that end up inside
 * script tags (GA4, GTM, GPT) are validated against strict patterns rather
 * than just "sanitized as text", so nothing unexpected can reach the page.
 *
 * @package Riffle
 */

defined( 'ABSPATH' ) || exit;

/**
 * Default values for theme mods.
 *
 * @return array<string, mixed>
 */
function riffle_mod_defaults() {
	return array(
		'riffle_ga4_id'            => '',
		'riffle_gtm_id'            => '',
		'riffle_track_logged_in'   => false,
		'riffle_gpt_network'       => '',
		'riffle_ad_placeholders'   => true,
		'riffle_members_join_url'  => '',
		'riffle_members_cta_title' => __( 'This story is for members', 'riffle' ),
		'riffle_members_cta_text'  => __( 'Members get every field report, gear test and long read, with fewer ads and a quieter reading experience.', 'riffle' ),
		'riffle_footer_text'       => '',
	);
}

/**
 * Get a theme mod with the theme's default.
 *
 * @param string $key Theme mod key.
 * @return mixed
 */
function riffle_mod( $key ) {
	$defaults = riffle_mod_defaults();
	return get_theme_mod( $key, $defaults[ $key ] ?? '' );
}

/**
 * Sanitize a GA4 measurement ID (G-XXXXXXX).
 *
 * @param string $value Raw value.
 * @return string
 */
function riffle_sanitize_ga4_id( $value ) {
	$value = strtoupper( trim( (string) $value ) );
	return preg_match( '/^G-[A-Z0-9]{4,16}$/', $value ) ? $value : '';
}

/**
 * Sanitize a GTM container ID (GTM-XXXXXX).
 *
 * @param string $value Raw value.
 * @return string
 */
function riffle_sanitize_gtm_id( $value ) {
	$value = strtoupper( trim( (string) $value ) );
	return preg_match( '/^GTM-[A-Z0-9]{4,12}$/', $value ) ? $value : '';
}

/**
 * Sanitize a Google Ad Manager network code (digits only, optional child network).
 *
 * @param string $value Raw value.
 * @return string
 */
function riffle_sanitize_gpt_network( $value ) {
	$value = trim( (string) $value, " \t\n\r\0\x0B/" );
	return preg_match( '/^\d{3,12}(,\d{3,12})?$/', $value ) ? $value : '';
}

/**
 * Sanitize a checkbox.
 *
 * @param mixed $value Raw value.
 * @return bool
 */
function riffle_sanitize_checkbox( $value ) {
	return (bool) $value;
}

/**
 * Register customizer panels, sections, settings and controls.
 *
 * @param WP_Customize_Manager $wp_customize Customizer manager.
 */
function riffle_customize_register( $wp_customize ) {
	$defaults = riffle_mod_defaults();

	$wp_customize->get_setting( 'blogname' )->transport        = 'postMessage';
	$wp_customize->get_setting( 'blogdescription' )->transport = 'postMessage';

	$wp_customize->add_panel(
		'riffle',
		array(
			'title'    => __( 'Riffle theme', 'riffle' ),
			'priority' => 30,
		)
	);

	// Analytics.
	$wp_customize->add_section(
		'riffle_analytics',
		array(
			'title'       => __( 'Analytics', 'riffle' ),
			'panel'       => 'riffle',
			'description' => __( 'Use either a GA4 measurement ID or a Google Tag Manager container. If both are set, GTM wins and GA4 should be configured inside GTM.', 'riffle' ),
		)
	);

	$fields = array(
		'riffle_ga4_id' => array( __( 'GA4 measurement ID', 'riffle' ), 'G-XXXXXXXXXX', 'riffle_sanitize_ga4_id' ),
		'riffle_gtm_id' => array( __( 'GTM container ID', 'riffle' ), 'GTM-XXXXXXX', 'riffle_sanitize_gtm_id' ),
	);
	foreach ( $fields as $id => $field ) {
		$wp_customize->add_setting(
			$id,
			array(
				'default'           => $defaults[ $id ],
				'sanitize_callback' => $field[2],
			)
		);
		$wp_customize->add_control(
			$id,
			array(
				'label'       => $field[0],
				'section'     => 'riffle_analytics',
				'type'        => 'text',
				'input_attrs' => array( 'placeholder' => $field[1] ),
			)
		);
	}

	$wp_customize->add_setting(
		'riffle_track_logged_in',
		array(
			'default'           => $defaults['riffle_track_logged_in'],
			'sanitize_callback' => 'riffle_sanitize_checkbox',
		)
	);
	$wp_customize->add_control(
		'riffle_track_logged_in',
		array(
			'label'   => __( 'Also track logged-in editors and admins', 'riffle' ),
			'section' => 'riffle_analytics',
			'type'    => 'checkbox',
		)
	);

	// Advertising.
	$wp_customize->add_section(
		'riffle_ads',
		array(
			'title'       => __( 'Advertising', 'riffle' ),
			'panel'       => 'riffle',
			'description' => __( 'Google Ad Manager (GPT). The library only loads when the first ad slot is about to scroll into view.', 'riffle' ),
		)
	);
	$wp_customize->add_setting(
		'riffle_gpt_network',
		array(
			'default'           => $defaults['riffle_gpt_network'],
			'sanitize_callback' => 'riffle_sanitize_gpt_network',
		)
	);
	$wp_customize->add_control(
		'riffle_gpt_network',
		array(
			'label'       => __( 'Ad Manager network code', 'riffle' ),
			'section'     => 'riffle_ads',
			'type'        => 'text',
			'input_attrs' => array( 'placeholder' => '1234567' ),
		)
	);
	$wp_customize->add_setting(
		'riffle_ad_placeholders',
		array(
			'default'           => $defaults['riffle_ad_placeholders'],
			'sanitize_callback' => 'riffle_sanitize_checkbox',
		)
	);
	$wp_customize->add_control(
		'riffle_ad_placeholders',
		array(
			'label'   => __( 'Show labelled placeholders when no network code is set', 'riffle' ),
			'section' => 'riffle_ads',
			'type'    => 'checkbox',
		)
	);

	// Membership.
	$wp_customize->add_section(
		'riffle_members',
		array(
			'title' => __( 'Membership', 'riffle' ),
			'panel' => 'riffle',
		)
	);
	$wp_customize->add_setting(
		'riffle_members_join_url',
		array(
			'default'           => $defaults['riffle_members_join_url'],
			'sanitize_callback' => 'esc_url_raw',
		)
	);
	$wp_customize->add_control(
		'riffle_members_join_url',
		array(
			'label'       => __( 'Join / subscribe page URL', 'riffle' ),
			'description' => __( 'Leave empty to use the WordPress registration page (if registration is open).', 'riffle' ),
			'section'     => 'riffle_members',
			'type'        => 'url',
		)
	);
	$wp_customize->add_setting(
		'riffle_members_cta_title',
		array(
			'default'           => $defaults['riffle_members_cta_title'],
			'sanitize_callback' => 'sanitize_text_field',
			'transport'         => 'postMessage',
		)
	);
	$wp_customize->add_control(
		'riffle_members_cta_title',
		array(
			'label'   => __( 'Gate headline', 'riffle' ),
			'section' => 'riffle_members',
			'type'    => 'text',
		)
	);
	$wp_customize->add_setting(
		'riffle_members_cta_text',
		array(
			'default'           => $defaults['riffle_members_cta_text'],
			'sanitize_callback' => 'sanitize_textarea_field',
		)
	);
	$wp_customize->add_control(
		'riffle_members_cta_text',
		array(
			'label'   => __( 'Gate message', 'riffle' ),
			'section' => 'riffle_members',
			'type'    => 'textarea',
		)
	);

	// Footer.
	$wp_customize->add_section(
		'riffle_footer',
		array(
			'title' => __( 'Footer', 'riffle' ),
			'panel' => 'riffle',
		)
	);
	$wp_customize->add_setting(
		'riffle_footer_text',
		array(
			'default'           => $defaults['riffle_footer_text'],
			'sanitize_callback' => 'wp_kses_post',
			'transport'         => 'postMessage',
		)
	);
	$wp_customize->add_control(
		'riffle_footer_text',
		array(
			'label'   => __( 'Footer note', 'riffle' ),
			'section' => 'riffle_footer',
			'type'    => 'textarea',
		)
	);

	if ( isset( $wp_customize->selective_refresh ) ) {
		$wp_customize->selective_refresh->add_partial(
			'riffle_footer_text',
			array(
				'selector'        => '.site-footer__note',
				'render_callback' => 'riffle_render_footer_note',
			)
		);
		$wp_customize->selective_refresh->add_partial(
			'blogname',
			array(
				'selector'        => '.site-title a',
				'render_callback' => static function () {
					return esc_html( get_bloginfo( 'name' ) );
				},
			)
		);
	}
}
add_action( 'customize_register', 'riffle_customize_register' );

/**
 * Footer note output, shared by footer.php and the selective-refresh partial.
 *
 * @return string Escaped HTML.
 */
function riffle_render_footer_note() {
	$text = (string) riffle_mod( 'riffle_footer_text' );
	if ( '' === trim( $text ) ) {
		$text = sprintf(
			/* translators: 1: year, 2: site name. */
			__( '&copy; %1$s %2$s. Independent writing about rivers, fish and the people who look after them.', 'riffle' ),
			gmdate( 'Y' ),
			get_bloginfo( 'name' )
		);
	}
	return wp_kses_post( $text );
}
