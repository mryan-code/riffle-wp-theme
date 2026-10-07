<?php
/**
 * Compact search result row.
 *
 * @package Riffle
 */

?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'result' ); ?>>
	<?php if ( has_post_thumbnail() ) : ?>
		<?php riffle_post_thumbnail( 'riffle-thumb' ); ?>
	<?php endif; ?>
	<div class="result__body">
		<div class="card__meta-top">
			<?php if ( 'post' === get_post_type() ) : ?>
				<?php riffle_kicker(); ?>
				<?php riffle_members_badge(); ?>
			<?php endif; ?>
		</div>
		<h2 class="result__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
		<div class="result__excerpt"><?php the_excerpt(); ?></div>
	</div>
</article>
