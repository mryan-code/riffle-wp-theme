<?php
/**
 * Front-page featured story.
 *
 * @package Riffle
 */

?>
<section class="hero" aria-labelledby="hero-title">
	<div class="hero__media">
		<?php riffle_post_thumbnail( 'riffle-hero', false ); ?>
	</div>
	<div class="hero__content container">
		<div class="hero__panel">
			<div class="card__meta-top">
				<span class="eyebrow eyebrow--light"><?php esc_html_e( 'Featured', 'riffle' ); ?></span>
				<?php riffle_kicker(); ?>
				<?php riffle_members_badge(); ?>
			</div>
			<h2 class="hero__title" id="hero-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
			<div class="hero__excerpt"><?php the_excerpt(); ?></div>
			<p class="hero__meta">
				<?php riffle_posted_by(); ?>
				<span aria-hidden="true">&middot;</span>
				<?php riffle_reading_time_label(); ?>
			</p>
		</div>
	</div>
</section>
