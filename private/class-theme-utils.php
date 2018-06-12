<?php
defined('ABSPATH') || die('Sorry, but you cannot access this page directly.');

/**
 * Utilities to be used in various locations throughout the theme
 */
class Theme_Utils
{
	public static function get_page_title( $seperator = '&nbsp;&raquo;&nbsp;' )
	{
		$title = wp_title( $seperator, false, 'right' );
		if ( 0 == strlen( $title ) ) {
			$title = get_bloginfo( 'name' );
			$title = apply_filters( 'wp_title', $title );
		}
		if ( false === strpos( $title, get_bloginfo( 'name' ) ) ) {
			$title = sprintf( '%s%s%s', $title, $seperator, get_bloginfo( 'name' ) );
			$title = apply_filters( 'wp_title', $title );
		}
		$title = trim( $title );
		return $title;
	}

	public static function asset_path( $asset, $default = '#' )
	{
		$filepath = sprintf( '%s/assets/%s', JGT_THEME_BASE, $asset );
		if ( file_exists( $filepath ) ) {
			return sprintf( '%s/assets/%s', JGT_MUTHEME_PATH, $asset );
		}
		return $default;
	}

	public static function asset_version( $asset )
	{
		$filepath = sprintf( '%s/assets/%s', JGT_THEME_BASE, $asset );
		if ( file_exists( $filepath ) ) {
			return filemtime( $filepath );
		}
		return false;
	}

	public static function get_wp_enqueue_style_args( $file, $dependancies = array(), $media = 'all' )
	{
		return array( $file, self::asset_path( $file ), $dependancies, self::asset_version( $file ), $media );
	}

	public static function get_wp_enqueue_script_args( $file, $dependancies = array(), $in_footer = true )
	{
		return array( $file, self::asset_path( $file ), $dependancies, self::asset_version( $file ), ( false !== $in_footer ) );
	}
}