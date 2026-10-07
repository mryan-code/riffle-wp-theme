<?php
/**
 * Author bio under a story.
 *
 * @package Riffle
 */

$riffle_bio = get_the_author_meta( 'description' );
if ( ! $riffle_bio ) {
	return;
}
?>
<section class="author-box" aria-label="<?php esc_attr_e( 'About the author', 'riffle' ); ?>">
	<?php echo get_avatar( get_the_author_meta( 'ID' ), 72, '', '', array( 'class' => 'author-box__avatar' ) ); ?>
	<div>
		<p class="author-box__name"><?php the_author_posts_link(); ?></p>
		<p class="author-box__bio"><?php echo esc_html( $riffle_bio ); ?></p>
	</div>
</section>
