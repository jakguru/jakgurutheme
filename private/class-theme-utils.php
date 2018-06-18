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

    public static function get_title_format( $title = '%s', $seperator = '&nbsp;&raquo;&nbsp;' )
    {
        $title = sprintf( '%s%s%s', $title, $seperator, get_bloginfo( 'name' ) );
        $title = apply_filters( 'wp_title', $title );
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

	public static function phpDateFormatToMomentFormat( $input )
	{
        $replacements = array(
            'd' => 'DD',
            'D' => 'ddd',
            'j' => 'D',
            'l' => 'dddd',
            'N' => 'E',
            'S' => 'o',
            'w' => 'e',
            'z' => 'DDD',
            'W' => 'W',
            'F' => 'MMMM',
            'm' => 'MM',
            'M' => 'MMM',
            'n' => 'M',
            't' => '', // no equivalent
            'L' => '', // no equivalent
            'o' => 'YYYY',
            'Y' => 'YYYY',
            'y' => 'YY',
            'a' => 'a',
            'A' => 'A',
            'B' => '', // no equivalent
            'g' => 'h',
            'G' => 'H',
            'h' => 'hh',
            'H' => 'HH',
            'i' => 'mm',
            's' => 'ss',
            'u' => 'SSS',
            'e' => 'zz', // deprecated since version 1.6.0 of moment.js
            'I' => '', // no equivalent
            'O' => '', // no equivalent
            'P' => '', // no equivalent
            'T' => '', // no equivalent
            'Z' => '', // no equivalent
            'c' => '', // no equivalent
            'r' => '', // no equivalent
            'U' => 'X',
        );
        $output = strtr( $input, $replacements );
        return $output;
    }
}