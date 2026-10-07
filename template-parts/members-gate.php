<?php
/**
 * Members-only call to action, shown after the teaser.
 *
 * Accepts `$args['post_id']`. Copy comes from the Customizer.
 *
 * @package Riffle
 */

$riffle_post_id = isset( $args['post_id'] ) ? absint( $args['post_id'] ) : get_the_ID();
$riffle_join    = riffle_members_join_url();
$riffle_login   = wp_login_url( get_permalink( $riffle_post_id ) );
?>
<aside class="members-gate" aria-labelledby="members-gate-title-<?php echo esc_attr( $riffle_post_id ); ?>" data-riffle-gate>
	<div class="members-gate__icon" aria-hidden="true">
		<svg width="28" height="28" viewBox="0 0 24 24"><path fill="currentColor" d="M12 2a5 5 0 0 0-5 5v3H6a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8a2 2 0 0 0-2-2h-1V7a5 5 0 0 0-5-5Zm-3 8V7a3 3 0 1 1 6 0v3H9Z"/></svg>
	</div>
	<h2 class="members-gate__title" id="members-gate-title-<?php echo esc_attr( $riffle_post_id ); ?>">
		<?php echo esc_html( riffle_mod( 'riffle_members_cta_title' ) ); ?>
	</h2>
	<p class="members-gate__text"><?php echo esc_html( riffle_mod( 'riffle_members_cta_text' ) ); ?></p>
	<div class="members-gate__actions">
		<?php if ( $riffle_join ) : ?>
			<a class="button" href="<?php echo esc_url( $riffle_join ); ?>" data-riffle-event="members_cta_click" data-riffle-location="gate"><?php esc_html_e( 'Become a member', 'riffle' ); ?></a>
		<?php endif; ?>
		<a class="button button--ghost" href="<?php echo esc_url( $riffle_login ); ?>" data-riffle-event="members_login_click" data-riffle-location="gate"><?php esc_html_e( 'I\'m already a member', 'riffle' ); ?></a>
	</div>
</aside>
