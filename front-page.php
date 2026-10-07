<?php
/**
 * Front page: featured story, latest stories grid and a members band.
 *
 * If the site uses a static front page, defer to the normal page layout.
 *
 * @package Riffle
 */

if ( 'page' === get_option( 'show_on_front' ) ) {
	require get_page_template();
	return;
}

get_header();

$riffle_paged   = max( 1, (int) get_query_var( 'paged' ) );
$riffle_hero_id = riffle_get_hero_post_id();
?>

<?php if ( 1 === $riffle_paged && $riffle_hero_id ) : ?>
	<?php
	// Swap the global post for the hero so standard template tags work inside the part.
	$post = get_post( $riffle_hero_id ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- restored by wp_reset_postdata() below.
	setup_postdata( $post );
	get_template_part( 'template-parts/content', 'hero' );
	wp_reset_postdata();
	?>
<?php endif; ?>

<div class="container">
	<?php riffle_ad_slot( 'leaderboard' ); ?>
</div>

<section class="section container" aria-labelledby="latest-heading">
	<header class="section-header">
		<h2 class="section-title" id="latest-heading">
			<?php
			if ( $riffle_paged > 1 ) {
				/* translators: %d: page number. */
				echo esc_html( sprintf( __( 'Latest stories, page %d', 'riffle' ), $riffle_paged ) );
			} else {
				esc_html_e( 'Latest stories', 'riffle' );
			}
			?>
		</h2>
	</header>

	<?php if ( have_posts() ) : ?>
		<div class="card-grid">
			<?php
			while ( have_posts() ) :
				the_post();
				get_template_part( 'template-parts/content', 'card' );
			endwhile;
			?>
		</div>
		<?php get_template_part( 'template-parts/pagination' ); ?>
	<?php else : ?>
		<?php get_template_part( 'template-parts/content', 'none' ); ?>
	<?php endif; ?>
</section>

<?php
if ( 1 === $riffle_paged ) :
	$riffle_members = riffle_members_query( 3 );
	if ( $riffle_members->have_posts() ) :
		$riffle_members_cat = get_category_by_slug( riffle_members_category_slug() );
		?>
		<section class="members-band" aria-labelledby="members-heading">
			<div class="container">
				<header class="section-header section-header--inverse">
					<p class="eyebrow"><?php esc_html_e( 'For members', 'riffle' ); ?></p>
					<h2 class="section-title" id="members-heading"><?php esc_html_e( 'The Members\' Journal', 'riffle' ); ?></h2>
					<?php if ( $riffle_members_cat ) : ?>
						<a class="section-header__more" href="<?php echo esc_url( get_category_link( $riffle_members_cat ) ); ?>"><?php esc_html_e( 'All members stories', 'riffle' ); ?> <span aria-hidden="true">&rarr;</span></a>
					<?php endif; ?>
				</header>
				<div class="card-grid card-grid--three">
					<?php
					while ( $riffle_members->have_posts() ) :
						$riffle_members->the_post();
						get_template_part( 'template-parts/content', 'card' );
					endwhile;
					wp_reset_postdata();
					?>
				</div>
			</div>
		</section>
		<?php
	endif;
endif;

get_footer();
