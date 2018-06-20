<?php
defined('ABSPATH') || die('Sorry, but you cannot access this page directly.');

class Desktop_Nav_Walker extends \Walker_Nav_Menu
{
	function start_lvl( &$output, $depth = 0, $args = array() )
	{

	}

	function end_lvl( &$output, $depth = 0, $args = array() )
	{

	}

	function start_el( &$output, $item, $depth = 0, $args = array(), $id = 0 )
	{
		$output .= sprintf( '<a href="%s" class="sysui-desktop-link">', esc_url( $item->url ) );
		$output .= sprintf( '<span class="sysui-desktop-icon"><img src="%s" /></span>', esc_attr( $item->icon ) );
		$output .= sprintf( '<span class="sysui-desktop-label">%s</span>', esc_html( $item->title ) );
		$output .= '</a>';
	}

	function end_el( &$output, $item, $depth = 0, $args = array() )
	{
		
	}
}