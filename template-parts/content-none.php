<?php
/**
 * Empty state.
 *
 * @package Riffle
 */

?>
<section class="empty-state">
	<h2 class="section-title section-title--small"><?php esc_html_e( 'Nothing here yet', 'riffle' ); ?></h2>
	<?php if ( is_search() ) : ?>
		<p><?php esc_html_e( 'No stories matched that search. Try a river, a species or a fly pattern.', 'riffle' ); ?></p>
	<?php elseif ( current_user_can( 'publish_posts' ) && is_home() ) : ?>
		<p>
			<?php
			printf(
				/* translators: %s: link to the new post screen. */
				wp_kses( __( 'Ready to publish your first story? <a href="%s">Get started here</a>.', 'riffle' ), array( 'a' => array( 'href' => array() ) ) ),
				esc_url( admin_url( 'post-new.php' ) )
			);
			?>
		</p>
	<?php else : ?>
		<p><?php esc_html_e( 'There is nothing to show here right now. A search might help.', 'riffle' ); ?></p>
		<?php get_search_form(); ?>
	<?php endif; ?>
</section>
