<?php
defined('ABSPATH') || die('Sorry, but you cannot access this page directly.');

/**
 * A Custom class extending Walker_Comment which handles how html for comments is displayed
 */

class Custom_Comment_Walker extends Walker_Comment
{
	function start_lvl( &$output, $depth = 0, $args = array() )
	{
		$output .= '<section class="comment-list">';
	}

	function end_lvl( &$output, $depth = 0, $args = array() )
	{
		$output .= '</section>';
	}

	function start_el( &$output, $item, $depth = 0, $args = array(), $id = 0 )
	{
		$output .= '<article class="comment-item">';
		$output .= sprintf( '<img class="comment-item-avatar" src="%s" alt="%s" />', get_avatar_url( $item->comment_author_email, array( 'size' => 12 ) ), $item->comment_author );
		$output .= sprintf( '<h5 class="comment-title">%s</h5>', sprintf(
				__( 'On %s %s said:' ),
				date( get_option('date_format') . ' ' . get_option( 'time_format' ), strtotime( $item->comment_date_gmt ) ),
				$item->comment_author
			)
		);
		$output .= '<div class="comment-body">';
		$output .= apply_filters( 'comment_text', $item->comment_content, $item );
		$output .= '</div>';
		$output .= '<div class="comment-reply-link">';
		$output .= sprintf( '<a href="#" class="sysui-add-comment-link" data-reply-to="%s">%s</a>', $item->comment_ID, __( 'Reply' ) );
		$output .= '</div>';
		//$output .= '<pre>' . print_r( $item, true ) . '</pre>';
	}

	function end_el( &$output, $item, $depth = 0, $args = array() )
	{
		$output .= '</article>';
	}
}