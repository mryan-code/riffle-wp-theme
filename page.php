<?php
/**
 * Static page.
 *
 * @package Riffle
 */

get_header();

while ( have_posts() ) :
	the_post();
	get_template_part( 'template-parts/content', 'page' );

	if ( comments_open() || get_comments_number() ) :
		?>
		<div class="container container--narrow section">
			<?php comments_template(); ?>
		</div>
		<?php
	endif;
endwhile;

get_footer();
