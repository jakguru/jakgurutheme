<?php
defined('ABSPATH') || die('Sorry, but you cannot access this page directly.');

class Sysui_Notifications_Area_Nav_Walker extends \Walker_Nav_Menu
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
			'<a href="%s" class="sysui-notification-link"><span><img src="%s" data-notification-text="%s" /></span></a>',
			$item->url,
			$item->icon,
			$item->title
		);
	}

	function end_el( &$output, $item, $depth = 0, $args = array() )
	{
		
	}
}