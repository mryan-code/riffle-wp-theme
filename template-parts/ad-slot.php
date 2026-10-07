<?php
/**
 * Ad slot (Google Publisher Tag).
 *
 * Space is reserved with min-height so the page does not shift when the
 * creative arrives. Without a network code a labelled placeholder renders
 * instead, which is handy for layout reviews.
 *
 * @package Riffle
 */

if ( empty( $args['slot'] ) || empty( $args['config'] ) ) {
	return;
}

$riffle_slot    = sanitize_key( $args['slot'] );
$riffle_config  = $args['config'];
$riffle_sizes   = array_values( (array) $riffle_config['sizes'] );
$riffle_mobile  = array_values( (array) ( $riffle_config['mobile'] ?? $riffle_sizes ) );
$riffle_min_h   = (int) min( array_map( static fn( $s ) => (int) $s[1], $riffle_sizes ) );
$riffle_min_h_m = (int) min( array_map( static fn( $s ) => (int) $s[1], $riffle_mobile ) );
$riffle_id      = wp_unique_id( 'riffle-ad-' . $riffle_slot . '-' );
$riffle_live    = (bool) riffle_mod( 'riffle_gpt_network' );
$riffle_style   = sprintf( '--ad-min-h:%dpx;--ad-min-h-mobile:%dpx;', $riffle_min_h, $riffle_min_h_m );

if ( $riffle_live ) {
	riffle_enqueue_ads_script();
}
?>
<aside class="ad-slot ad-slot--<?php echo esc_attr( $riffle_slot ); ?>" aria-label="<?php echo esc_attr( sprintf( /* translators: %s: ad placement name. */ __( 'Advertisement: %s', 'riffle' ), $riffle_config['label'] ) ); ?>" style="<?php echo esc_attr( $riffle_style ); ?>">
	<span class="ad-slot__label"><?php esc_html_e( 'Advertisement', 'riffle' ); ?></span>
	<?php if ( $riffle_live ) : ?>
		<div
			id="<?php echo esc_attr( $riffle_id ); ?>"
			class="ad-slot__unit"
			data-riffle-ad
			data-unit="<?php echo esc_attr( riffle_ad_unit_path( $riffle_slot ) ); ?>"
			data-sizes="<?php echo esc_attr( wp_json_encode( $riffle_sizes ) ); ?>"
			data-mobile-sizes="<?php echo esc_attr( wp_json_encode( $riffle_mobile ) ); ?>"
		></div>
	<?php else : ?>
		<div class="ad-slot__unit ad-slot__placeholder" aria-hidden="true">
			<?php
			echo esc_html(
				sprintf(
					'%1$s - %2$s',
					$riffle_config['label'],
					implode( ', ', array_map( static fn( $s ) => $s[0] . 'x' . $s[1], $riffle_sizes ) )
				)
			);
			?>
		</div>
	<?php endif; ?>
</aside>
