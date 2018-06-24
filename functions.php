<?php
defined( 'ABSPATH' ) || die( 'Sorry, but you cannot access this page directly.' );

if ( ! class_exists( 'WP_List_Table' ) ){
   require_once sprintf( '%swp-admin/includes/class-wp-list-table.php', ABSPATH );
}

if ( defined( 'JGT_THEME_BASE' ) ) {
	throw new Exception( 'There is a conflict which prevents using this theme' );
}

define( 'JGT_THEME_BASE', realpath( dirname( __FILE__ ) ) );
define( 'JGT_MUTHEME_PATH', get_template_directory_uri() );

/**
 * Load Theme Private Files
 */
$private_files_to_be_loaded = array(
	'class-theme-utils.php',
	'class-wp-customize-utility.php',
	'class-additional-menu-fields-utility.php',
	'class-page-parser.php',
	'class-custom-comment-walker.php',
	'menu-walkers/class-quick-links-nav-walker.php',
	'menu-walkers/class-sysui-notifications-area-nav-walker.php',
	'menu-walkers/class-start-menu-nav-walker.php',
	'menu-walkers/class-desktop-nav-walker.php',
);

foreach ( $private_files_to_be_loaded as $filename ) {
	$filepath = sprintf( '%s/private/%s', JGT_THEME_BASE, $filename );
	if ( file_exists( $filepath ) ) {
		require_once $filepath;
	}
	else {
		throw new \Exception( sprintf( 'Missing Required File "%s"', $filepath ), 1);
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

function get_application_script_localizations() {
	$query = ( isset( $_SERVER['REQUEST_URI'] ) && strlen( $_SERVER['REQUEST_URI'] ) > 0 ) ? $_SERVER['REQUEST_URI'] : '/';
	return array(
		'site_path' => get_bloginfo( 'url' ),
		'site_title' => Theme_Utils::get_page_title(),
		'asset_path' => sprintf( '%s/assets/', JGT_MUTHEME_PATH ),
		'ajax_url' => admin_url( 'admin-ajax.php' ),
		'legal' => array(
			'show_cookie_policy_notification' => get_theme_mod( 'show_cookie_policy_notification', true ),
			'cookie_policy_notification_text' => __( 'This site uses cookies and other tracking technologies to assist with navigation and your ability to provide feedback, analyse your use of our products and services, assist with our promotional and marketing efforts, and provide content from third parties.' ),
			'privacy_policy_url' => get_the_permalink( get_option( 'wp_page_for_privacy_policy' ) ),
			'click_to_dismiss' => __( 'Click Here to Dismiss' ),
		),
		'moment' => array(
			'datetimeformat' => Theme_Utils::phpDateFormatToMomentFormat( sprintf( '%s %s', get_option('date_format'), get_option('time_format') ) ),
			'dateformat' => Theme_Utils::phpDateFormatToMomentFormat( get_option('date_format') ),
			'timeformat' => Theme_Utils::phpDateFormatToMomentFormat( get_option('time_format') ),
        ),
        'terms' => array(
        	'minimize' => __( 'Minimize' ),
        	'maximize' => __( 'Maximize' ),
        	'close' => __( 'Close' ),
        	'leave_a_comment' => __( 'Leave a Comment on %s'),
        	'comment_successful' => __( 'Your comment was saved successfully' ),
        ),
        'defaultwindows' => array(
        	'comment' => array(
        		'icon' => Theme_Utils::asset_path( 'images/notepad.png' ),
        		'title' => __( 'Leave a Comment' ),
        		'minimize' => true,
        		'maximize' => true,
        		'close' => true,
        		'width' => 562,
        		'height' => 400,
        		'menus' => array(
        			array(
        				'title' => __( 'File' ),
        				'items' => array(
        					array(
        						'title' => __( 'Save Comment' ),
        						'href' => '#',
        						'class' => 'sysui-save-comment',
        					),
        					array(
        						'title' => __( 'Close' ),
        						'href' => '#',
        						'class' => 'sysui-close-window',
        					),
        				),
        			),
        		),
        		'content' => sprintf( '<form class="sysui-comment-form" action="%s" method="POST"><textarea name="comment" class="form-control full-height-no-resize" placeholder="%s" autofocus required></textarea></form>', admin_url( 'admin-ajax.php' ), __( 'Write your comment here and save from the File menu' ) ),
        		'permalink' => home_url(),
        		'maximized' => false,
        	),
        ),
        'defaultnotifications' => array(
        	'clipboardsuccess' => array(
        		'icon' => Theme_Utils::asset_path( 'images/info.png' ),
        		'content' => __( 'Copied to Clipboard successfully' ),
        	),
        	'clipboarderror' => array(
        		'icon' => Theme_Utils::asset_path( 'images/stop.png' ),
        		'content' => __( 'Errory Copying to Clipboard' ),
        	),
        ),
        'title_format' => Theme_Utils::get_title_format(),
	);
}

add_action('wp_enqueue_scripts', function () {
	call_user_func_array( 'wp_enqueue_style', Theme_Utils::get_wp_enqueue_style_args( 'css/app.min.css' ) );
	call_user_func_array( 'wp_enqueue_script', Theme_Utils::get_wp_enqueue_script_args( 'js/app.min.js', array( 'jquery' ) ) );
	wp_localize_script( 'js/app.min.js', 'app', get_application_script_localizations() );
});

add_action('customize_preview_init', function () {
	call_user_func_array( 'wp_enqueue_script', Theme_Utils::get_wp_enqueue_script_args( 'js/app.min.js', array( 'jquery' ) ) );
	wp_localize_script( 'js/app.min.js', 'app', get_application_script_localizations() );
});

/** Add the admin stylesheet and scripts */
add_action( 'admin_enqueue_scripts', function() {
	wp_enqueue_media();
	call_user_func_array( 'wp_enqueue_style', Theme_Utils::get_wp_enqueue_style_args( 'admin/admin.css' ) );
	call_user_func_array( 'wp_enqueue_script', Theme_Utils::get_wp_enqueue_script_args( 'admin/admin.js', array( 'jquery' ) ) );
	wp_localize_script( 'admin/admin.js', 'jgt', array(
		'terms' => array(
			'chooseicon' => __( 'Choose an Icon' ),
		),
	) );
});

/** Add Theme Supports */
add_action('after_setup_theme', function () {
	add_theme_support( 'title-tag' );
	register_nav_menus( array(
		'quick_links' => __( 'Quick Links' ),
		'notification_area' => __( 'Notification Area' ),
		'start_menu' => __( 'Start Menu' ),
		'desktop' => __( 'Desktop' ),
	) );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'caption', 'comment-form', 'comment-list', 'gallery', 'search-form' ) );
	add_theme_support( 'customize-selective-refresh-widgets' );
	add_theme_support( 'custom-logo', array(
		'height' => 14,
		'width' => 14,
		'flex-width' => false,
	) );
	add_theme_support( 'custom-background', array(
		'default-size' => 'cover',
		'default-color' => '#fff',
	) );
	add_theme_support( 'automatic-feed-links' );
}, 20 );

/** Add New Customize Controls */
$wp_customize_utility = new WP_Customize_Utility();

$wp_customize_utility->enqueue_control( 'enable_quicklaunch', array(
	'label' => __( 'Enable Quick Launch Menu' ),
	'description' => __( 'Choose to display the Quick Launch Menu' ),
	'section' => array(
		'id' => 'system-ui',
		'title' => __( 'System UI' ),
		'description' => __( 'Settings for how the System UI acts' ),
	),
	'type' => 'checkbox',
	'default' => true,
) );
$wp_customize_utility->enqueue_control( 'show_post_meta_info', array(
	'label' => __( 'Show Post Meta Information' ),
	'description' => __( 'Show Author Information and Post Date / Time' ),
	'section' => array(
		'id' => 'system-ui',
		'title' => __( 'System UI' ),
		'description' => __( 'Settings for how the System UI acts' ),
	),
	'type' => 'checkbox',
	'default' => true,
) );
$wp_customize_utility->enqueue_control( 'default_menu_icon', array(
	'label' => __( 'Default Menu Icon' ),
	'description' => __( 'Default Menu Icon' ),
	'section' => array(
		'id' => 'system-ui',
		'title' => __( 'System UI' ),
		'description' => __( 'Settings for how the System UI acts' ),
	),
	'class' => 'WP_Customize_Image_Control',
	'default' => Theme_Utils::asset_path( 'images/defaultapp.png' ),
) );
$wp_customize_utility->enqueue_control( 'background_color', array(
	'label' => __( 'Background Color' ),
	'description' => __( 'Background Color' ),
	'section' => 'colors',
	'class' => 'WP_Customize_Color_Control',
	'default' => '#346DA1',
) );
$wp_customize_utility->enqueue_control( 'desktop_text_color', array(
	'label' => __( 'Desktop Text Color' ),
	'description' => __( 'Desktop Text Color' ),
	'section' => 'colors',
	'class' => 'WP_Customize_Color_Control',
	'default' => '#fff',
) );
$wp_customize_utility->enqueue_control( 'show_cookie_policy_notification', array(
	'label' => __( 'Show Cookie Policy Notification' ),
	'description' => __( 'Show a Notification to regarding Cookie Utilization' ),
	'section' => array(
		'id' => 'legal',
		'title' => __( 'Legal' ),
		'description' => __( 'Legal and Regulatory Compliance' ),
		'priority' => 210,
	),
	'type' => 'checkbox',
	'default' => true,
) );
$wp_customize_utility->enqueue_control( 'cookie_policy_notification_text', array(
	'label' => __( 'Cookie Policy Notification Text' ),
	'description' => __( 'Text to be shown in the notification to the visitor' ),
	'section' => array(
		'id' => 'legal',
		'title' => __( 'Legal' ),
		'description' => __( 'Legal and Regulatory Compliance' ),
		'priority' => 210,
	),
	'type' => 'text',
	'default' => __( 'This site uses cookies and other tracking technologies to assist with navigation and your ability to provide feedback, analyse your use of our products and services, assist with our promotional and marketing efforts, and provide content from third parties.' ),
) );

$sharing_destinations = array(
	'facebook' => __( 'Facebook' ),
	'google_plus' => __( 'Google Plus' ),
	'linkedin' => __( 'LinkedIn' ),
	'twitter' => __( 'Twitter' ),
	'reddit' => __( 'Reddit' ),
	'tumbler' => __( 'Tumbler' ),
	'pintrest' => __( 'Pinterest' ),
	'telegram' => __( 'Telegram' ),
	'whatsapp' => __( 'Whatsapp' ),
	'email' => __( 'Email' ),
	'other' => __( 'Other Destinations' ),
);

foreach ( $sharing_destinations as $key => $name ) {
	$wp_customize_utility->enqueue_control( sprintf( 'allow_share_%s', $key ), array(
		'label' => sprintf( __( 'Allow Sharing to %s' ), $name ),
		'description' => sprintf( __( 'Add a sharing link for %s to the sharing menu' ), $name ),
		'section' => array(
			'id' => 'sharing',
			'title' => __( 'Sharing' ),
			'description' => __( 'Options for Sharing Content on other platforms' ),
			'priority' => 220,
		),
		'type' => 'checkbox',
		'default' => true,
	) );
}

add_action( 'customize_register', array( $wp_customize_utility, 'register' ) );
add_action( 'wp_enqueue_scripts', function() {
	wp_add_inline_style( 'css/app.min.css', sprintf( '.sysui-desktop-link>.sysui-desktop-label{color:%s}', wp_strip_all_tags( get_theme_mod( 'desktop_text_color', '#fff' ) ) ) );
} );

/** Add Additional Fields to Menu Editor */
$additional_menu_fields_utility = new Additional_Menu_Fields_Utility();

$additional_menu_fields_utility->add_field( 'icon', array(
	'label' => __( 'Icon' ),
	'type' => 'image',
	'default' => get_theme_mod( 'default_menu_icon', Theme_Utils::asset_path( 'images/defaultapp.png' ) ),
	'required' => true,
) );

add_filter( 'wp_setup_nav_menu_item', array( $additional_menu_fields_utility, 'setup_nav_item' ) );
add_filter( 'wp_edit_nav_menu_walker', array( $additional_menu_fields_utility, 'nav_menu_walker' ), 10, 2 );
add_action( 'wp_update_nav_menu_item', array( $additional_menu_fields_utility, 'update_nav_item' ) );
add_action( 'wp_nav_menu_item_custom_fields', array( $additional_menu_fields_utility, 'render_custom_fields' ), 10, 4 );

/** Add Additional Items to Start Menu */

add_filter( 'wp_nav_menu_objects', function( $sorted_menu_items, $args ) {
	if ( 'start_menu' == $args->theme_location ) {
		$additional_items = Start_Menu_Nav_Walker::get_start_menu_nav_items();
		$sorted_menu_items = array_merge( $sorted_menu_items, $additional_items );
	}
	return $sorted_menu_items;
}, 30, 2 );

/** Add Handler for Front-End AJAX Requests */
add_action( 'wp_ajax_page_request', array( 'Page_Parser', 'parse' ) );
add_action( 'wp_ajax_nopriv_page_request', array( 'Page_Parser', 'parse' ) );
add_action( 'wp_ajax_paged_query_request', array( 'Page_Parser', 'paged_query_request' ) );
add_action( 'wp_ajax_nopriv_paged_query_request', array( 'Page_Parser', 'paged_query_request' ) );
add_action( 'wp_ajax_search_query_request', array( 'Page_Parser', 'search_query_request' ) );
add_action( 'wp_ajax_nopriv_search_query_request', array( 'Page_Parser', 'search_query_request' ) );
add_action( 'wp_ajax_add_comment_to_post', array( 'Page_Parser', 'add_comment_to_post' ) );
add_action( 'wp_ajax_nopriv_add_comment_to_post', array( 'Page_Parser', 'add_comment_to_post' ) );