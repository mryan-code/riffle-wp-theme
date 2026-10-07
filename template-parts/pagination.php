<?php
/**
 * Numbered pagination for archives.
 *
 * @package Riffle
 */

the_posts_pagination(
	array(
		'mid_size'           => 1,
		'prev_text'          => '<span aria-hidden="true">&larr;</span> ' . esc_html__( 'Newer', 'riffle' ),
		'next_text'          => esc_html__( 'Older', 'riffle' ) . ' <span aria-hidden="true">&rarr;</span>',
		'screen_reader_text' => esc_html__( 'Stories navigation', 'riffle' ),
		'aria_label'         => esc_attr__( 'Stories', 'riffle' ),
		'class'              => 'pagination',
	)
);
