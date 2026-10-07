<?php
/**
 * Site footer.
 *
 * @package Riffle
 */

?>
	</main>

	<footer class="site-footer">
		<div class="container">
			<?php if ( is_active_sidebar( 'footer-1' ) || is_active_sidebar( 'footer-2' ) || is_active_sidebar( 'footer-3' ) ) : ?>
				<div class="site-footer__widgets">
					<?php for ( $riffle_i = 1; $riffle_i <= 3; $riffle_i++ ) : ?>
						<?php if ( is_active_sidebar( 'footer-' . $riffle_i ) ) : ?>
							<div class="site-footer__col">
								<?php dynamic_sidebar( 'footer-' . $riffle_i ); ?>
							</div>
						<?php endif; ?>
					<?php endfor; ?>
				</div>
			<?php endif; ?>

			<div class="site-footer__bottom">
				<p class="site-footer__brand"><?php bloginfo( 'name' ); ?></p>
				<?php if ( has_nav_menu( 'footer' ) ) : ?>
					<nav aria-label="<?php esc_attr_e( 'Footer', 'riffle' ); ?>">
						<?php
						wp_nav_menu(
							array(
								'theme_location' => 'footer',
								'menu_class'     => 'footer-menu',
								'container'      => false,
								'depth'          => 1,
							)
						);
						?>
					</nav>
				<?php endif; ?>
				<p class="site-footer__note"><?php echo riffle_render_footer_note(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped with wp_kses_post() in the helper. ?></p>
			</div>
		</div>
	</footer>
</div>

<?php wp_footer(); ?>
</body>
</html>
