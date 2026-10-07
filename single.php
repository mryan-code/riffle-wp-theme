<?php
/**
 * Single story.
 *
 * @package Riffle
 */

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<?php get_template_part( 'template-parts/content', 'single' ); ?>

	<?php
	$riffle_related = riffle_related_query( 3 );
	if ( $riffle_related->have_posts() ) :
		?>
		<section class="section section--tinted" aria-labelledby="related-heading">
			<div class="container">
				<header class="section-header">
					<h2 class="section-title" id="related-heading"><?php esc_html_e( 'Keep reading', 'riffle' ); ?></h2>
				</header>
				<div class="card-grid card-grid--three">
					<?php
					while ( $riffle_related->have_posts() ) :
						$riffle_related->the_post();
						get_template_part( 'template-parts/content', 'card' );
					endwhile;
					wp_reset_postdata();
					?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<?php
	// Members discussion stays behind the gate too.
	if ( ( comments_open() || get_comments_number() ) && ! riffle_is_locked() ) :
		?>
		<div class="container container--narrow section">
			<?php comments_template(); ?>
		</div>
	<?php endif; ?>
	<?php
endwhile;

get_footer();
