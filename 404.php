<?php
/**
 * Not found.
 *
 * @package Riffle
 */

get_header();
?>
<div class="container container--narrow section error-404">
	<p class="eyebrow"><?php esc_html_e( 'Error 404', 'riffle' ); ?></p>
	<h1 class="page-title"><?php esc_html_e( 'This pool looks fished out.', 'riffle' ); ?></h1>
	<p class="page-description"><?php esc_html_e( 'The page you were looking for has moved or never existed. Try a search, or pick up one of the latest stories below.', 'riffle' ); ?></p>
	<?php get_search_form(); ?>

	<?php
	$riffle_recent = new WP_Query(
		array(
			'posts_per_page'      => 4,
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
		)
	);
	if ( $riffle_recent->have_posts() ) :
		?>
		<h2 class="section-title section-title--small"><?php esc_html_e( 'Latest stories', 'riffle' ); ?></h2>
		<ul class="link-list">
			<?php
			while ( $riffle_recent->have_posts() ) :
				$riffle_recent->the_post();
				?>
				<li><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></li>
				<?php
			endwhile;
			wp_reset_postdata();
			?>
		</ul>
	<?php endif; ?>
</div>
<?php
get_footer();
