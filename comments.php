<?php
/**
 * Comments.
 *
 * @package Riffle
 */

if ( post_password_required() ) {
	return;
}
?>
<section id="comments" class="comments" aria-labelledby="comments-title">
	<?php if ( have_comments() ) : ?>
		<h2 class="comments__title" id="comments-title">
			<?php
			$riffle_count = (int) get_comments_number();
			printf(
				/* translators: 1: number of comments, 2: post title. */
				esc_html( _n( '%1$s reply to "%2$s"', '%1$s replies to "%2$s"', $riffle_count, 'riffle' ) ),
				esc_html( number_format_i18n( $riffle_count ) ),
				esc_html( get_the_title() )
			);
			?>
		</h2>

		<ol class="comment-list">
			<?php
			wp_list_comments(
				array(
					'style'       => 'ol',
					'short_ping'  => true,
					'avatar_size' => 48,
				)
			);
			?>
		</ol>

		<?php
		the_comments_navigation(
			array(
				'prev_text' => esc_html__( 'Older replies', 'riffle' ),
				'next_text' => esc_html__( 'Newer replies', 'riffle' ),
			)
		);
		?>

		<?php if ( ! comments_open() ) : ?>
			<p class="comments__closed"><?php esc_html_e( 'Replies are closed for this story.', 'riffle' ); ?></p>
		<?php endif; ?>
	<?php else : ?>
		<h2 class="comments__title" id="comments-title"><?php esc_html_e( 'Join the conversation', 'riffle' ); ?></h2>
	<?php endif; ?>

	<?php
	comment_form(
		array(
			'title_reply'        => esc_html__( 'Leave a reply', 'riffle' ),
			'title_reply_before' => '<h3 id="reply-title" class="comment-reply-title">',
			'title_reply_after'  => '</h3>',
			'class_submit'       => 'button',
		)
	);
	?>
</section>
