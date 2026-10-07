<?php
/**
 * Site header.
 *
 * @package Riffle
 */

?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link screen-reader-text" href="#content"><?php esc_html_e( 'Skip to content', 'riffle' ); ?></a>

<div class="site">
	<header class="site-header" role="banner">
		<div class="site-header__inner container">
			<div class="site-branding">
				<?php riffle_site_branding(); ?>
				<?php if ( get_bloginfo( 'description' ) ) : ?>
					<p class="site-description"><?php bloginfo( 'description' ); ?></p>
				<?php endif; ?>
			</div>

			<nav class="primary-nav" id="primary-nav" aria-label="<?php esc_attr_e( 'Primary', 'riffle' ); ?>">
				<button class="nav-toggle" type="button" aria-expanded="false" aria-controls="primary-menu-wrap">
					<span class="nav-toggle__bars" aria-hidden="true"></span>
					<span class="nav-toggle__label"><?php esc_html_e( 'Menu', 'riffle' ); ?></span>
				</button>
				<div class="primary-nav__panel" id="primary-menu-wrap">
					<?php
					wp_nav_menu(
						array(
							'theme_location' => 'primary',
							'menu_id'        => 'primary-menu',
							'container'      => false,
							'depth'          => 1,
							'fallback_cb'    => 'riffle_primary_menu_fallback',
						)
					);
					?>
					<div class="header-actions">
						<?php if ( is_user_logged_in() ) : ?>
							<a class="header-actions__link" href="<?php echo esc_url( wp_logout_url( home_url( '/' ) ) ); ?>"><?php esc_html_e( 'Log out', 'riffle' ); ?></a>
						<?php else : ?>
							<a class="header-actions__link" href="<?php echo esc_url( wp_login_url( is_singular() ? get_permalink() : home_url( '/' ) ) ); ?>"><?php esc_html_e( 'Log in', 'riffle' ); ?></a>
							<?php $riffle_join = riffle_members_join_url(); ?>
							<?php if ( $riffle_join ) : ?>
								<a class="button button--small" href="<?php echo esc_url( $riffle_join ); ?>" data-riffle-event="members_cta_click" data-riffle-location="header"><?php esc_html_e( 'Become a member', 'riffle' ); ?></a>
							<?php endif; ?>
						<?php endif; ?>
						<button class="search-toggle" type="button" aria-expanded="false" aria-controls="header-search">
							<svg aria-hidden="true" focusable="false" width="20" height="20" viewBox="0 0 24 24"><path fill="currentColor" d="M10 2a8 8 0 0 1 6.32 12.9l5.39 5.4-1.41 1.41-5.4-5.39A8 8 0 1 1 10 2Zm0 2a6 6 0 1 0 0 12 6 6 0 0 0 0-12Z"/></svg>
							<span class="screen-reader-text"><?php esc_html_e( 'Search', 'riffle' ); ?></span>
						</button>
					</div>
				</div>
			</nav>
		</div>
		<div class="header-search" id="header-search" hidden>
			<div class="container">
				<?php get_search_form( array( 'aria_label' => __( 'Site search', 'riffle' ) ) ); ?>
			</div>
		</div>
	</header>

	<main id="content" class="site-main" tabindex="-1">
