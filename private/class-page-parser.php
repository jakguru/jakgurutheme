<?php
defined('ABSPATH') || die('Sorry, but you cannot access this page directly.');

/**
 * Utility used to parse pages from their URLs to return the feedback needed for a System UI Window
 */
class Page_Parser
{
	private $window_properties = array(
		'icon' => '',
		'title' => '',
		'minimize' => true,
		'maximize' => true,
		'close' => true,
		'menus' => array(),
		'content' => '',
		'width' => 500,
		'height' => 300,
		'autoopen' => true,
		'maximized' => false,
		'page_id' => 0,
		'permalink' => '',
	);
	private $error = '404';
	private $query_vars = array();
	private $request = '';
	private $matched_rule;
	private $public_query_vars = array('m', 'p', 'posts', 'w', 'cat', 'withcomments', 'withoutcomments', 's', 'search', 'exact', 'sentence', 'calendar', 'page', 'paged', 'more', 'tb', 'pb', 'author', 'order', 'orderby', 'year', 'monthnum', 'day', 'hour', 'minute', 'second', 'name', 'category_name', 'tag', 'feed', 'author_name', 'static', 'pagename', 'page_id', 'error', 'attachment', 'attachment_id', 'subpost', 'subpost_id', 'preview', 'robots', 'taxonomy', 'term', 'cpage', 'post_type', 'embed', 'post__in' );
	private $private_query_vars = array( 'offset', 'posts_per_page', 'posts_per_archive_page', 'showposts', 'nopaging', 'post_type', 'post_status', 'category__in', 'category__not_in', 'category__and', 'tag__in', 'tag__not_in', 'tag__and', 'tag_slug__in', 'tag_slug__and', 'tag_id', 'post_mime_type', 'perm', 'comments_per_page', 'post__in', 'post__not_in', 'post_parent', 'post_parent__in', 'post_parent__not_in', 'title', 'fields' );
	private $wpo = null;
	private $original_query = '';

	public function __construct( $query, $password = '' )
	{
		global $wp_rewrite;
		$this->original_query = $query;
		if ( ! apply_filters( 'do_parse_request', true, $this, $extra_query_vars ) ) {
			return;
		}
		$this->query_vars = array();
		$post_type_query_vars = array();
		$rewrite = $wp_rewrite->wp_rewrite_rules();
		if ( ! empty( $rewrite ) ) {
			$requested_file = trim( $query, '/' );
			$request_match = trim( $query, '/' );
			if ( empty( $request_match ) ) {
				if ( isset( $rewrite['$'] ) ) {
					$this->matched_rule = '$';
					$query = $rewrite['$'];
					$matches = array('');
				}
			}
			else {
				foreach ( (array) $rewrite as $match => $query ) {
					if (
						! empty( $requested_file )
						&& strpos($match, $requested_file) === 0
						&& $requested_file != $requested_path
					) {
						$request_match = $requested_file . '/' . $requested_path;
					}
					if (
						preg_match( "#^$match#", $request_match, $matches )
						|| preg_match( "#^$match#", urldecode($request_match), $matches )
					) {
						if ( $wp_rewrite->use_verbose_page_rules && preg_match( '/pagename=\$matches\[([0-9]+)\]/', $query, $varmatch ) ) {
							$page = get_page_by_path( $matches[ $varmatch[1] ] );
							if ( ! $page ) {
						 		continue;
							}
							$post_status_obj = get_post_status_object( $page->post_status );
							if ( ! $post_status_obj->public && ! $post_status_obj->protected
								&& ! $post_status_obj->private && $post_status_obj->exclude_from_search ) {
								continue;
							}
						}
						$this->matched_rule = $match;
						break;
					}
				}
			}
			if ( ! empty( $this->matched_rule ) ) {
				$query = preg_replace("!^.+\?!", '', $query);
				$query = addslashes( WP_MatchesMapRegex::apply( $query, $matches ) );
				$this->matched_query = $query;
			}
		}
		else {
			$query = preg_replace("!^.+\?!", '', $query);
		}
		parse_str( $query, $perma_query_vars );
		$this->public_query_vars = apply_filters( 'query_vars', $this->public_query_vars );
		foreach ( get_post_types( array(), 'objects' ) as $post_type => $t ) {
			if ( is_post_type_viewable( $t ) && $t->query_var ) {
				$post_type_query_vars[$t->query_var] = $post_type;
			}
		}
		foreach ( $this->public_query_vars as $wpvar ) {
			if ( isset( $perma_query_vars[ $wpvar ] ) ) {
				$this->query_vars[ $wpvar ] = $perma_query_vars[ $wpvar ];
			}
		}
		foreach ( get_taxonomies( array() , 'objects' ) as $taxonomy => $t )
			if ( $t->query_var && isset( $this->query_vars[$t->query_var] ) )
				$this->query_vars[$t->query_var] = str_replace( ' ', '+', $this->query_vars[$t->query_var] );

		if ( ! is_admin() ) {
			foreach ( get_taxonomies( array( 'publicly_queryable' => false ), 'objects' ) as $taxonomy => $t ) {
				if ( isset( $this->query_vars['taxonomy'] ) && $taxonomy === $this->query_vars['taxonomy'] ) {
					unset( $this->query_vars['taxonomy'], $this->query_vars['term'] );
				}
			}
		}
		if ( isset( $this->query_vars['post_type']) ) {
			$queryable_post_types = get_post_types( array('publicly_queryable' => true) );
			if ( ! is_array( $this->query_vars['post_type'] ) ) {
				if ( ! in_array( $this->query_vars['post_type'], $queryable_post_types ) )
					unset( $this->query_vars['post_type'] );
			} else {
				$this->query_vars['post_type'] = array_intersect( $this->query_vars['post_type'], $queryable_post_types );
			}
		}
		$this->query_vars = wp_resolve_numeric_slug_conflicts( $this->query_vars );

		if ( isset($error) )
			$this->query_vars['error'] = $error;

		$this->wpo = new WP_Query( $this->query_vars );
		switch ( true ) {
			case $this->wpo->is_404:
				$this->make_error_window( 'Item not Found', 'Sorry, but the item you have requested could not be found' );
				break;

			case $this->wpo->is_single || $this->wpo->is_page:
				$post = $this->wpo->post;
				if ( ! empty( $post->post_password ) && $post->post_password !== $password ) {
					$this->make_password_request_window();
					return;
				}
				$file_menu = array( 'title' => __( 'File' ), 'items' => array() );
				$edit_menu = array( 'title' => __( 'Edit' ), 'items' => array() );
				if ( current_user_can( 'edit_posts', $post->ID ) ) {
					array_push( $edit_menu['items'], array( 'title' => __( 'Open in Editor' ), 'href' => admin_url( sprintf( 'post.php?%s', http_build_query( array(
						'post' => $post->ID,
						'action' => 'edit',
					) ) ) ), 'class' => 'sysui-close-window' ) );	
				}
				if ( comments_open( $post->ID ) ) {
					array_push( $file_menu['items'], array( 'title' => __( 'Add a Comment' ), 'href' => '#', 'class' => 'sysui-add-comment-link' ) );	
				}
				array_push( $file_menu['items'], array( 'title' => __( 'Close' ), 'href' => '#', 'class' => 'sysui-close-window' ) );
				array_push( $this->window_properties['menus'], $file_menu );
				if ( count( $edit_menu['items'] ) > 0 ) {
					array_push( $this->window_properties['menus'], $edit_menu );
				}
				$this->window_properties['title'] = esc_html( apply_filters( 'the_title', get_the_title( $post ) ) );
				if ( true == get_theme_mod( 'show_post_meta_info', true ) && ! $this->wpo->is_page ) {
					$original_content = apply_filters( 'the_content', $post->post_content );
					$post->post_content = sprintf(
						__( 'Posted on %s by %s under %s' ),
						date( get_option('date_format') . ' ' . get_option( 'time_format' ), strtotime( $post->post_date_gmt ) ),
						self::get_author_display_html( $post->post_author ),
						self::get_post_category_display_html( $post->ID )
					);
					$post->post_content .= '<hr />';
					$post->post_content .= $original_content;
				}
				else {
					$post->post_content = apply_filters( 'the_content', $post->post_content );
				}
				if ( get_comments_number( $post ) > 0 ) {
					$comments = get_comments( array( 'post_id' => $post->ID ) );
					$post->post_content .= '<hr />';
					$post->post_content .= '<section class="comment-list">';
					$post->post_content .= wp_list_comments( array( 'echo' => false, 'walker' => new Custom_Comment_Walker() ), $comments );
					$post->post_content .= '</section>';
				}
				$this->window_properties['content'] = sprintf( '<div class="sysui-text-content">%s</div>', $post->post_content );
				if ( has_post_thumbnail( $post ) ) {
					$this->window_properties['icon'] = get_the_post_thumbnail_url( $post );
				}
				else {
					$this->window_properties['icon'] = Theme_Utils::asset_path( 'images/defaultapp.png' );	
				}
				$this->window_properties['page_id'] = $post->ID;
				$this->window_properties['permalink'] = get_the_permalink( $post );
				break;
			
			default:
				wp_send_json_error( $this->wpo );
				
				break;
		}
	}

	public function get_window_object()
	{
		$obj = new stdClass();
		foreach ( $this->window_properties as $key => $value ) {
			$obj->{ $key } = $value;
		}
		return $obj;
	}

	private function make_error_window( $title = 'An Error has Occured', $text = 'An Error has occured with your request', $translated = false )
	{
		$this->window_properties['icon'] = Theme_Utils::asset_path( 'images/stop.png' );
		$this->window_properties['title'] = ( true === $translated ) ? $title : __( $title );
		$this->window_properties['minimize'] = false;
		$this->window_properties['maximize'] = false;
		$this->window_properties['width'] = 320;
		$this->window_properties['height'] = 150;
		$this->window_properties['menus'] = array(
			array(
				'title' => __( 'File' ),
				'items' => array(
					array(
						'title' => __( 'Close' ),
						'href' => '#',
						'class' => 'sysui-close-window',
					),
				),
			),
		);
		$this->window_properties['content'] = sprintf( '<div class="sysui-text-content"><p>%s</p></div>', ( true === $translated ) ? $text : __( $text ) );
	}

	private function make_password_request_window( $title = 'Authentication Required', $text = 'Sorry, but the resource you have requested requires additional authentication to be viewed', $translated = false )
	{
		$this->window_properties['icon'] = Theme_Utils::asset_path( 'images/lock.png' );
		$this->window_properties['title'] = ( true === $translated ) ? $title : __( $title );
		$this->window_properties['minimize'] = false;
		$this->window_properties['maximize'] = false;
		$this->window_properties['width'] = 320;
		$this->window_properties['height'] = 180;
		$this->window_properties['menus'] = array(
			array(
				'title' => __( 'File' ),
				'items' => array(
					array(
						'title' => __( 'Submit' ),
						'href' => '#',
						'class' => 'sysui-submit-window-form',
					),
					array(
						'title' => __( 'Close' ),
						'href' => '#',
						'class' => 'sysui-close-window',
					),
				),
			),
		);
		$this->window_properties['content'] = sprintf(
			'<form action="%s" method="POST" class="sysui-password-form"><input type="hidden" name="action" value="page_request" /><input type="hidden" name="query" value="%s" /><p>%s</p><div class="form-group"><label>%s</label><div class="sysui-form-control-wrapper"><input type="password" name="password" class="form-control form-control-sm" required /></div></div><div class="sysui-form-submit-wrapper"><button class="sysui-button-outer" role="submit" type="submit"><div class="sysui-button-inner"><span class="sysui-button-label">%s</span></div></button></div></form>',
			admin_url( 'admin-ajax.php' ),
			$this->original_query,
			( true === $translated ) ? $text : __( $text ),
			__( 'Resource Password' ),
			__( 'Submit' )
		);	
	}

	public static function parse()
	{
		$c = get_called_class();
		$data = stripslashes_deep( $_POST );
		$query = self::get_array_key( 'query', $data, '/' );
		$password = self::get_array_key( 'password', $data, '' );
		$obj = new $c( $query, $password );
		wp_send_json_success( $obj->get_window_object() );
	}

	private static function get_array_key( $key, $array, $default = null )
	{
		if ( is_array( $array ) && array_key_exists( $key, $array ) ) {
			return $array[ $key ];
		}
		return $default;
	}

	private static function get_author_display_html( $author_id = 0 ) 
	{
		$url = get_the_author_meta( 'user_url', $author_id );
		if ( is_null( $url ) || empty( $url ) ) {
			$url = get_author_posts_url( $author_id );
		}
		return sprintf( '<a href="%s" class="author-display-link">%s</a>', $url, get_the_author_meta( 'display_name', $author_id ) );
	}

	private static function get_post_category_display_html( $post_id = 0 )
	{
		$terms = wp_get_post_categories( $post_id, array( 'fields' => 'all' ) );
		$list = array();
		foreach ( $terms as $term ) {
			$url = get_category_link( $term->term_id );
			array_push( $list, sprintf( '<a href="%s" class="category-display-link">%s</a>', $url, $term->name ) );
		}
		if ( count( $list ) > 1 ) {
			$last = array_pop( $list );
		}
		$html = implode( ', ', $list );
		if ( isset( $last ) ) {
			$html .= ' ' . __( 'and' ) . ' ' . $last;
		}
		return $html;
	}
}