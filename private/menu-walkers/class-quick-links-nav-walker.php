<?php
defined('ABSPATH') || die('Sorry, but you cannot access this page directly.');

class Quick_Links_Nav_Walker extends \Walker_Nav_Menu
{
	function start_lvl( &$output, $depth = 0, $args = array() )
	{

	}

	function end_lvl( &$output, $depth = 0, $args = array() )
	{

	}

	function start_el( &$output, $item, $depth = 0, $args = array(), $id = 0 )
	{
		$output .= sprintf(
			'<a href="%s" class="sysui-quick-link"><span><img src="%s" title="%s" /></span></a>',
			esc_url( $item->url ),
			esc_url( $item->icon ),
			esc_html( $item->title )
		);
	}

	function end_el( &$output, $item, $depth = 0, $args = array() )
	{
		
	}
}