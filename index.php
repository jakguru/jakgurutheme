<?php
defined( 'ABSPATH' ) || die( 'Sorry, but you cannot access this page directly.' );
get_header();
if ( has_nav_menu( 'desktop' ) ) {
	wp_nav_menu( array(
		'theme_location' => 'desktop',
		'container' => false,
		'menu_class' => 'desktop-nav',
		'depth' => 1,
		'items_wrap' => '<nav id="%1$s" class="%2$s">%3$s</nav>',
		'walker' => new Desktop_Nav_Walker(),
	) );
}
get_footer();