<?php
/**
 * Search form.
 *
 * @package Riffle
 */

$riffle_uid   = wp_unique_id( 'search-' );
$riffle_label = ! empty( $args['aria_label'] ) ? $args['aria_label'] : __( 'Search', 'riffle' );
?>
<form role="search" method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php echo esc_attr( $riffle_label ); ?>">
	<label class="screen-reader-text" for="<?php echo esc_attr( $riffle_uid ); ?>"><?php esc_html_e( 'Search for:', 'riffle' ); ?></label>
	<input type="search" id="<?php echo esc_attr( $riffle_uid ); ?>" class="search-form__field" placeholder="<?php esc_attr_e( 'Search rivers, flies, gear', 'riffle' ); ?>" value="<?php echo esc_attr( get_search_query() ); ?>" name="s" />
	<button type="submit" class="button search-form__submit"><?php esc_html_e( 'Search', 'riffle' ); ?></button>
</form>
