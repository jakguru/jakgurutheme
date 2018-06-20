<?php
defined('ABSPATH') || die('Sorry, but you cannot access this page directly.');

class Start_Menu_Nav_Walker extends \Walker_Nav_Menu
{
	function start_lvl( &$output, $depth = 0, $args = array() )
	{
		$output .= str_repeat( "\t", $depth + 1 );
		$output .= sprintf( '<nav class="sysui-start-menu-child-menu sysui-start-menu-child-menu-level-%d sysui-panel-outer"><div class="sysui-panel-inner">', $depth );
		$output .= "\r\n";
	}

	function end_lvl( &$output, $depth = 0, $args = array() )
	{
		$output .= str_repeat( "\t", $depth + 1 );
		$output .= '</nav>';
		$output .= "\r\n";
	}

	function start_el( &$output, $item, $depth = 0, $args = array(), $id = 0 )
	{
		$html = "\r\n";
		if ( $depth > 0 ) {
			$html .= str_repeat( "\t", $depth + 1 );
		}
		if ( empty( $item->title ) || is_null( $item->title ) ) {
			return;
		}
		if ( ! is_array( $item->classes ) ) {
			$item->classes = explode( ' ', $item->classes );
		}
		if ( in_array( 'seperator', $item->classes ) ) {
			$output .= str_repeat( "\t", $depth + 1 ) . '<span class="sysui-start-menu-seperator"></span>';
			return;
		}
		array_push( $item->classes, 'sysui-start-menu-item' );
		$atts = array(
			'href' => $item->url,
			'class' => implode( ' ', $item->classes ),
			'target' => $item->target,
		);
		$tag = 'a';
		if ( $args->walker->has_children ) {
			$item->has_children = true;
			unset( $atts['href'] );
			array_push( $item->classes, 'sysui-start-menu-item-with-children' );
			$tag = 'div';
		}
		$atts_array = array();
		foreach( $atts as $att => $val ) {
			if ( ! empty( $val ) ) {
				array_push( $atts_array, sprintf( '%s="%s"', $att, $val ) );
			}
		}
		$html .= sprintf( '<%s %s>', $tag, implode( ' ', $atts_array ) );
		$html .= "\r\n";
		$html .= sprintf( str_repeat( "\t", $depth + 1 ) . '<span class="sysui-start-menu-item-icon-wrapper"><img src="%s" /></span>', esc_url( $item->icon ) );
		$html .= "\r\n";
		$html .= sprintf( str_repeat( "\t", $depth + 1 ) . '<span class="sysui-start-menu-item-label">%s</span>', esc_html( $item->title ) );
		$html .= "\r\n";
		$output .= $html;
	}

	function end_el( &$output, $item, $depth = 0, $args = array() )
	{
		$tag = 'a';
		if ( $args->walker->has_children || $item->has_children ) {
			$tag = 'div';
		}
		$html = '';
		$html .= "\r\n";
		if ( $depth > 0 ) {
			$html .= str_repeat( "\t", $depth + 1 );
		}
		$html .= sprintf( '</%s>', $tag );
		$html .= "\r\n";
		$output .= $html;
	}

	private static function make_start_menu_item( $url = '#', $label = 'A Start Menu Link', $asset = 'images/link.png', $target = '', $classes = array(), $children = array() )
	{
		$item = new stdClass();
		$item->ID = 0;
		$item->post_author = 1;
		$item->post_date = date( 'Y-m-d H:i:s' );
		$item->post_date_gmt = date( 'Y-m-d H:i:s' );
		$item->post_content = '';
		$item->post_title = $label;
		$item->post_excerpt = '';
		$item->post_status = 'publish';
		$item->comment_status = 'closed';
		$item->ping_status = 'closed';
		$item->post_password = '';
		$item->post_name = 'start-menu-item';
		$item->to_ping = false;
		$item->pinged = false;
		$item->post_modified = date( 'Y-m-d H:i:s' );
		$item->post_modified_gmt = date( 'Y-m-d H:i:s' );
		$item->post_content_filtered = '';
		$item->post_parent = 0;
		$item->guid = sprintf( '%s?p=0', home_url() );
		$item->menu_order = -1;
		$item->post_type = 'nav_menu_item';
		$item->post_mime_type = '';
		$item->comment_count = 0;
		$item->filter = 'raw';
		$item->db_id = 0;
		$item->menu_item_parent = 0;
		$item->object_id = 0;
		$item->object = 'custom';
		$item->type = 'custom';
		$item->type_label = 'Custom Link';
		$item->title = $label;
		$item->url = $url;
		$item->target = $target;
		$item->attr_title = '';
		$item->description = '';
		$item->classes = array_replace( array( '', 'menu-item', 'menu-item-type-custom', 'menu-item-object-custom' ), $classes );
		$item->xfn = '';
		$item->icon = ( false === strpos( $asset, 'http://' ) && false === strpos( $asset, 'https://' ) ) ? Theme_Utils::asset_path( $asset ) : $asset;
		$item->current = '';
		$item->current_item_ancestor = '';
		$item->current_item_parent = '';
		return $item;
	}

	public static function get_start_menu_nav_items( $current = null )
	{
		if ( is_null( $current ) || empty( $current ) ) {
			$current = home_url();
		}
		$items = array();
		array_push( $items, self::make_start_menu_item( '#', __( 'Seperator' ), 'images/link.png', '', array( 'seperator' ) ) );
		array_push( $items, self::make_start_menu_item( admin_url(), __( 'Control Panel' ), 'images/controlpanel.png', '' ) );
		array_push( $items, self::make_start_menu_item( get_search_link(), __( 'Search' ), 'images/search.png', '' ) );
		array_push( $items, self::make_start_menu_item( '#', __( 'Seperator' ), 'images/link.png', '', array( 'seperator' ) ) );
		if ( is_user_logged_in() ) {
			array_push( $items, self::make_start_menu_item( admin_url( 'profile.php' ), __( 'My Profile' ), get_avatar_url( wp_get_current_user(), array( 'size' => 35, 'default' => 'mm', 'rating' => 'g' ) ) ) );
			array_push( $items, self::make_start_menu_item( wp_logout_url( $current ), __( 'Log Out' ), 'images/shutdown.png' ) );
		}
		else {
			array_push( $items, self::make_start_menu_item( wp_login_url( $current ), __( 'Log In' ), 'images/lock.png' ) );
			if ( get_option( 'users_can_register' ) ) {
				array_push( $items, self::make_start_menu_item( wp_registration_url(), __( 'Register' ), 'images/keys.png' ) );
			}
		}
		return $items;
	}

	public static function fallback( $args )
	{
		$args = (object) $args;
		$nav_menu = apply_filters( 'pre_wp_nav_menu', null, $args );
		if ( null !== $nav_menu ) {
			if ( $args->echo ) {
				echo $nav_menu;
				return;
			}
			return $nav_menu;
		}

		$args->menu = '';
		$menu_items = self::get_start_menu_nav_items();
		$nav_menu = $items = '';
		if ( $args->container ) {
			$allowed_tags = apply_filters( 'wp_nav_menu_container_allowedtags', array( 'div', 'nav' ) );
			if ( is_string( $args->container ) && in_array( $args->container, $allowed_tags ) ) {
				$show_container = true;
				$class = $args->container_class ? ' class="' . esc_attr( $args->container_class ) . '"' : ' class="menu-'. $menu->slug .'-container"';
				$id = $args->container_id ? ' id="' . esc_attr( $args->container_id ) . '"' : '';
				$nav_menu .= '<'. $args->container . $id . $class . '>';
			}

		}
		$sorted_menu_items = $menu_items_with_children = array();
		unset( $menu_items, $menu_item );
		$sorted_menu_items = apply_filters( 'wp_nav_menu_objects', $sorted_menu_items, $args );
		array_shift( $sorted_menu_items );

		$items .= walk_nav_menu_tree( $sorted_menu_items, $args->depth, $args );
		unset($sorted_menu_items);
		if ( ! empty( $args->menu_id ) ) {
			$wrap_id = $args->menu_id;
		}
		else {
			$wrap_id = 'menu-' . $menu->slug;
			while ( in_array( $wrap_id, $menu_id_slugs ) ) {
				if ( preg_match( '#-(\d+)$#', $wrap_id, $matches ) ) {
					$wrap_id = preg_replace('#-(\d+)$#', '-' . ++$matches[1], $wrap_id );
				}
				else {
					$wrap_id = $wrap_id . '-1';
				}
			}
		}
		$menu_id_slugs[] = $wrap_id;
		$wrap_class = $args->menu_class ? $args->menu_class : '';
		$items = apply_filters( 'wp_nav_menu_items', $items, $args );
		$nav_menu .= sprintf( $args->items_wrap, esc_attr( $wrap_id ), esc_attr( $wrap_class ), $items );
    	unset( $items );
    	if ( $show_container ) {
    		$nav_menu .= '</' . $args->container . '>';
    	}
    	$nav_menu = apply_filters( 'wp_nav_menu', $nav_menu, $args );
    	if ( $args->echo )
			echo $nav_menu;
		else
			return $nav_menu;
	}
}