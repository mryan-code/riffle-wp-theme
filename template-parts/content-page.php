<?php
/**
 * Static page body.
 *
 * @package Riffle
 */

?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'article article--page' ); ?>>
	<header class="article-header container container--narrow">
		<?php the_title( '<h1 class="article-title">', '</h1>' ); ?>
	</header>
	<?php if ( has_post_thumbnail() ) : ?>
		<figure class="article-figure container container--wide">
			<?php the_post_thumbnail( 'riffle-hero' ); ?>
		</figure>
	<?php endif; ?>
	<div class="container container--narrow">
		<div class="entry-content">
			<?php
			the_content();
			wp_link_pages(
				array(
					'before' => '<nav class="page-links" aria-label="' . esc_attr__( 'Page sections', 'riffle' ) . '">',
					'after'  => '</nav>',
				)
			);
			?>
		</div>
	</div>
</article>
