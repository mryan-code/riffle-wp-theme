<?php
/**
 * Story card used in grids.
 *
 * Accepts `$args['heading']` ('h2' or 'h3') so the heading outline stays
 * correct whether or not the grid sits under a section heading.
 *
 * @package Riffle
 */

$riffle_heading = isset( $args['heading'] ) && in_array( $args['heading'], array( 'h2', 'h3' ), true ) ? $args['heading'] : 'h3';
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'card' ); ?>>
	<?php riffle_post_thumbnail( 'riffle-card' ); ?>
	<div class="card__body">
		<div class="card__meta-top">
			<?php riffle_kicker(); ?>
			<?php riffle_members_badge(); ?>
		</div>
		<<?php echo tag_escape( $riffle_heading ); ?> class="card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></<?php echo tag_escape( $riffle_heading ); ?>>
		<div class="card__excerpt"><?php the_excerpt(); ?></div>
		<p class="card__meta">
			<?php riffle_posted_on(); ?>
			<span aria-hidden="true">&middot;</span>
			<?php riffle_reading_time_label(); ?>
		</p>
	</div>
</article>
