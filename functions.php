<?php
defined('ABSPATH') || die('Sorry, but you cannot access this page directly.');

if ( ! class_exists( 'WP_List_Table' ) ){
   require_once sprintf( '%swp-admin/includes/class-wp-list-table.php', ABSPATH );
}

if ( defined( 'JGT_THEME_BASE' ) ) {
	throw new Exception( 'There is a conflict which prevents using this theme' );
}

define( 'JGT_THEME_BASE', realpath( dirname( __FILE__ ) ) );