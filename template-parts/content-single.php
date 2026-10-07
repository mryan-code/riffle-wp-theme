<?php
/**
 * Single story body.
 *
 * @package Riffle
 */

$riffle_locked = riffle_is_locked();
$riffle_gated  = riffle_is_members_only();
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'article' ); ?>>
	<header class="article-header container article-header--with-sidebar">
		<div class="article-header__inner">
			<div class="card__meta-top">
				<?php riffle_kicker(); ?>
				<?php riffle_members_badge(); ?>
			</div>
			<?php the_title( '<h1 class="article-title">', '</h1>' ); ?>
			<?php if ( has_excerpt() ) : ?>
				<p class="article-dek"><?php echo esc_html( get_the_excerpt() ); ?></p>
			<?php endif; ?>
			<p class="article-meta">
				<?php echo get_avatar( get_the_author_meta( 'ID' ), 40, '', '', array( 'class' => 'article-meta__avatar' ) ); ?>
				<span>
					<?php riffle_posted_by(); ?><br>
					<?php riffle_posted_on(); ?>
					<span aria-hidden="true">&middot;</span>
					<?php riffle_reading_time_label(); ?>
				</span>
			</p>
		</div>
	</header>

	<?php if ( has_post_thumbnail() ) : ?>
		<figure class="article-figure container container--wide">
			<?php
			the_post_thumbnail(
				'riffle-hero',
				array(
					'loading'       => 'eager',
					'fetchpriority' => 'high',
				)
			);
			?>
			<?php if ( get_the_post_thumbnail_caption() ) : ?>
				<figcaption><?php the_post_thumbnail_caption(); ?></figcaption>
			<?php endif; ?>
		</figure>
	<?php endif; ?>

	<div class="article-layout container">
		<div class="entry-content<?php echo $riffle_gated ? ' entry-content--members' : ''; ?><?php echo $riffle_locked ? ' is-locked' : ''; ?>">
			<?php
			the_content();

			if ( ! $riffle_locked ) {
				wp_link_pages(
					array(
						'before' => '<nav class="page-links" aria-label="' . esc_attr__( 'Story pages', 'riffle' ) . '">',
						'after'  => '</nav>',
					)
				);
			}
			?>

			<?php if ( ! $riffle_locked ) : ?>
				<footer class="article-footer">
					<?php the_tags( '<ul class="tag-list"><li>', '</li><li>', '</li></ul>' ); ?>
					<?php get_template_part( 'template-parts/author-box' ); ?>
				</footer>
			<?php endif; ?>
		</div>
		<?php get_sidebar(); ?>
	</div>
</article>
