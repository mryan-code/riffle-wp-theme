<?php
/**
 * Category, tag, author and date archives.
 *
 * @package Riffle
 */

get_header();
?>
<div class="container section">
	<header class="page-header">
		<?php if ( is_category( riffle_members_category_slug() ) ) : ?>
			<p class="eyebrow"><?php riffle_members_badge_static(); ?></p>
		<?php else : ?>
			<p class="eyebrow"><?php echo esc_html( riffle_archive_eyebrow() ); ?></p>
		<?php endif; ?>
		<h1 class="page-title"><?php echo esc_html( wp_strip_all_tags( get_the_archive_title() ) ); ?></h1>
		<?php the_archive_description( '<div class="page-description">', '</div>' ); ?>
	</header>

	<?php if ( have_posts() ) : ?>
		<div class="card-grid">
			<?php
			while ( have_posts() ) :
				the_post();
				get_template_part( 'template-parts/content', 'card', array( 'heading' => 'h2' ) );
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
