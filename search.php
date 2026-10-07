<?php
/**
 * Search results.
 *
 * @package Riffle
 */

get_header();

global $wp_query;
?>
<div class="container section">
	<header class="page-header">
		<p class="eyebrow"><?php esc_html_e( 'Search', 'riffle' ); ?></p>
		<h1 class="page-title">
			<?php
			printf(
				/* translators: %s: search query. */
				esc_html__( 'Results for "%s"', 'riffle' ),
				'<span class="page-title__query">' . esc_html( get_search_query() ) . '</span>'
			);
			?>
		</h1>
		<p class="page-description" role="status">
			<?php
			echo esc_html(
				sprintf(
					/* translators: %d: number of results. */
					_n( '%d story found.', '%d stories found.', (int) $wp_query->found_posts, 'riffle' ),
					(int) $wp_query->found_posts
				)
			);
			?>
		</p>
		<?php get_search_form( array( 'aria_label' => __( 'Refine search', 'riffle' ) ) ); ?>
	</header>

	<?php if ( have_posts() ) : ?>
		<div class="result-list">
			<?php
			while ( have_posts() ) :
				the_post();
				get_template_part( 'template-parts/content', 'result' );
			endwhile;
			?>
		</div>
		<?php get_template_part( 'template-parts/pagination' ); ?>
	<?php else : ?>
		<?php get_template_part( 'template-parts/content', 'none' ); ?>
	<?php endif; ?>
</div>
<?php
get_footer();
