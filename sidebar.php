<?php
/**
 * Article sidebar.
 *
 * @package Riffle
 */

if ( ! is_active_sidebar( 'sidebar-article' ) && ! riffle_show_ads( 'sidebar' ) ) {
	return;
}
?>
<aside class="article-sidebar" aria-label="<?php esc_attr_e( 'Sidebar', 'riffle' ); ?>">
	<div class="article-sidebar__sticky">
		<?php dynamic_sidebar( 'sidebar-article' ); ?>
		<?php riffle_ad_slot( 'sidebar' ); ?>
	</div>
</aside>
