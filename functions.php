<?php
defined('ABSPATH') || die('Sorry, but you cannot access this page directly.');

if ( ! class_exists( 'WP_List_Table' ) ){
   require_once sprintf( '%swp-admin/includes/class-wp-list-table.php', ABSPATH );
}

if ( defined( 'JGT_THEME_BASE' ) ) {
	throw new Exception( 'There is a conflict which prevents using this theme' );
}

define( 'JGT_THEME_BASE', realpath( dirname( __FILE__ ) ) );
define( 'JGT_MUTHEME_PATH', ( str_replace( get_bloginfo( 'url' ), '', get_template_directory_uri() ) ) );

/**
 * Load Theme Private Files
 */
$private_files_to_be_loaded = array(
	'class-theme-utils.php',
	'class-theme-core.php',
);

foreach ( $private_files_to_be_loaded as $filename ) {
	$filepath = sprintf( '%s/private/%s', JGT_THEME_BASE, $filename );
	if ( file_exists( $filepath ) ) {
		require_once $filepath;
	}
}

/**
 * Add Actions and Filters
 */

/** Add the Title Tag */
add_action( 'wp_head', function() {
	$title = Theme_Utils::get_page_title();
	echo sprintf( '<title>%s</title>', esc_html( $title ) );
});

/** Remove the WordPress Admin Bar */
add_filter('show_admin_bar', '__return_false');

/** Remove WP Emojis */
add_action( 'init', function() {
	remove_action( 'admin_print_styles', 'print_emoji_styles' );
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
	remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
	remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
	add_filter( 'tiny_mce_plugins', function( $plugins ) {
		if ( is_array( $plugins ) ) {
			return array_diff( $plugins, array( 'wpemoji' ) );
		} else {
			return array();
		}
	} );
	add_filter( 'emoji_svg_url', '__return_false' );
} );

/** Remove WP Generator Information */
add_action( 'init', function() {
	$types = array('html', 'xhtml', 'atom', 'rss2', 'rdf', 'comment', 'export');
	foreach ( $types as $type ) {
		add_filter('get_the_generator_'.$type, function(){
			return '';
		});
	}
});

/** Add the application stylesheet and scripts */
add_action('wp_enqueue_scripts', function () {
	call_user_func_array( 'wp_enqueue_style', Theme_Utils::get_wp_enqueue_style_args( 'css/app.min.css' ) );
	call_user_func_array( 'wp_enqueue_script', Theme_Utils::get_wp_enqueue_script_args( 'js/app.min.js', array( 'jquery' ) ) );
	wp_localize_script( 'js/app.min.js', 'app', array(
		'asset_path' => sprintf( '%s/assets/', JGT_MUTHEME_PATH ),
	) );
});

/** Add Theme Supports */
add_action('after_setup_theme', function () {
	add_theme_support( 'title-tag' );
	register_nav_menus( array(
		'quick_links' => __( 'Quick Links' ),
	) );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'caption', 'comment-form', 'comment-list', 'gallery', 'search-form' ) );
	add_theme_support( 'customize-selective-refresh-widgets' );
	add_theme_support( 'custom-logo' );
	add_theme_support( 'custom-background', array(
		'default-size' => 'cover',
		'default-color' => '#fff',
	) );
	add_theme_support( 'automatic-feed-links' );
	add_editor_style( Theme_Utils::asset_path( 'css/app.min.css' ) );
}, 20 );