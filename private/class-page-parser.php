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
		'permalink' => '/',
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
		$this->window_properties['permalink'] = home_url() . '/';
		global $wp_rewrite;
		$this->original_query = $query;
		if ( ! apply_filters( 'do_parse_request', true, $this, $extra_query_vars ) ) {
			return;
		}
		$this->query_vars = array();
		$post_type_query_vars = array();
		$rewrite = $wp_rewrite->wp_rewrite_rules();
		if ( ! empty( $rewrite ) && '/?p=' !== substr( $query, 0, 4 ) && '/?s=' !== substr( $query, 0, 4 ) && ! is_array( $query ) ) {
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
		if ( 0 == count( $this->query_vars ) ) {
			if ( 'page' == get_option( 'show_on_front' ) ) {
				$this->query_vars['page_id'] = get_option( 'page_on_front' );
			}
			else {
				$is_archive = true;
				$is_home = true;
			}
		}
		if ( isset($error) )
			$this->query_vars['error'] = $error;

		$this->query_vars['posts_per_page'] = get_option( 'posts_per_page', 10 );
		if ( ! isset( $this->query_vars['preview'] ) || 'true' !== $this->query_vars['preview'] ) {
			$this->query_vars['post_status'] = 'publish';
		}
		$this->wpo = new WP_Query( $this->query_vars );
		if ( isset( $is_archive ) && true == $is_archive ) {
			$this->wpo->is_archive = true;
		}
		if ( isset( $is_home ) && true == $is_home ) {
			$this->wpo->is_home = true;
		}
		if ( $this->wpo->is_paged ) {
			$this->wpo->is_archive = true;
		}
		if ( 'search' == self::get_array_key( 'name', $this->wpo->query_vars, '' ) ) {
			$this->wpo->is_search = true;
		}
		$this->wpo->is_admin = false;
		switch ( true ) {
			case $this->wpo->is_404:
				$this->make_error_window( 'Item not Found', 'Sorry, but the item you have requested could not be found' );
				$this->window_properties['permalink'] = self::get_the_permalink( $this->wpo );
				break;

			case $this->wpo->is_archive || $this->wpo->is_search:
				$this->window_properties['icon'] = Theme_Utils::asset_path( 'images/folder.png' );
				$this->window_properties['title'] = esc_html( apply_filters( 'the_title', self::make_title_from_wp_query( $this->wpo ) ) );
				$this->window_properties['width'] = 500;
				$this->window_properties['height'] = 300;
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
				$this->window_properties['permalink'] = self::get_the_permalink( $this->wpo );
				$this->window_properties['expected_items'] = intval( $this->wpo->found_posts );
				$this->window_properties['current_items'] = 0;
				//$this->window_properties['base_query'] = array_replace_recursive( $this->original_query, $this->wpo->query );
				$this->window_properties['base_query'] = $this->wpo->query;
				$html = '';
				$html .= '<div class="sysui-window-list">';
				foreach ( $this->wpo->posts as $post ) {
					$html .= sprintf( '<a href="%s" class="sysui-window-link">', get_the_permalink( $post ) );
					if ( has_post_thumbnail( $post ) ) {
						$icon = get_the_post_thumbnail_url( $post );
					}
					else {
						$icon = Theme_Utils::asset_path( 'images/defaultapp.png' );	
					}
					$html .= sprintf( '<span class="sysui-window-icon"><img src="%s" /></span>', esc_attr( $icon ) );
					$html .= sprintf( '<span class="sysui-window-label">%s</span>', esc_html( apply_filters( 'the_title', get_the_title( $post ) ) ) );
					$html .= '</a>';
					$this->window_properties['current_items'] ++;
				}
				$html .= '</div>';
				if ( $this->wpo->is_search ) {
					$this->window_properties['icon'] = Theme_Utils::asset_path( 'images/search.png' );
					$this->window_properties['content'] = Theme_Utils::get_search_window_content( $html );
					$this->window_properties['width'] = 562;
					$this->window_properties['height'] = 400;
					$this->window_properties['base_query']['s'] = self::get_array_key( 's', $this->original_query, $this->wpo->query_vars['s'] );
				}
				else {
					$this->window_properties['content'] = $html;
				}
				//$this->window_properties['content'] .= sprintf( '<div class="sysui-text-content">%s</div>', '<pre>' . htmlentities( print_r( $this->wpo, true ) ) . '</pre>' );
				break;

			case ( $this->wpo->is_single || $this->wpo->is_page ) && is_a( $this->wpo->post, 'WP_Post' ):
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
				//$this->make_error_window( 'Debug', '<pre>' . htmlentities( print_r( $this->wpo, true ) ) . '</pre>', true );
				//return;
				$this->make_error_window( 'Item not Found', 'Sorry, but the item you have requested could not be found' );
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

	public static function paged_query_request()
	{
		$c = get_called_class();
		$data = stripslashes_deep( $_POST );
		$query = self::get_array_key( 'query', $data, '/' );
		$wp_query = new WP_Query( $query );
		$return = array();
		foreach ( $wp_query->posts as $post ) {
			if ( has_post_thumbnail( $post ) ) {
				$icon = get_the_post_thumbnail_url( $post );
			}
			else {
				$icon = Theme_Utils::asset_path( 'images/defaultapp.png' );	
			}
			$post_data = array(
				'url' => get_the_permalink( $post ),
				'icon' => $icon,
				'label' => esc_html( apply_filters( 'the_title', get_the_title( $post ) ) ),
			);
			array_push( $return, $post_data );
		}
		if ( count( $return ) > 0 ) {
			wp_send_json_success( $return );
		}
		wp_send_json_error( $return );
	}

	public static function search_query_request()
	{
		$c = get_called_class();
		$data = stripslashes_deep( $_POST );
		$search = self::get_array_key( 's', $data, '/' );
		$query = array( 's' => $search );
		$wp_query = new WP_Query( $query );
		$return_object = array();
		$return_object['title'] = esc_html( apply_filters( 'the_title', self::make_title_from_wp_query( $wp_query ) ) );
		$return_object['permalink'] = self::get_the_permalink( $wp_query );
		$return_object['expected_items'] = intval( $wp_query->found_posts );
		$return_object['current_items'] = 0;
		$return_object['base_query'] = $wp_query->query;
		$html = '';
		$html .= '<div class="sysui-window-list">';
		foreach ( $wp_query->posts as $post ) {
			$html .= sprintf( '<a href="%s" class="sysui-window-link">', get_the_permalink( $post ) );
			if ( has_post_thumbnail( $post ) ) {
				$icon = get_the_post_thumbnail_url( $post );
			}
			else {
				$icon = Theme_Utils::asset_path( 'images/defaultapp.png' );	
			}
			$html .= sprintf( '<span class="sysui-window-icon"><img src="%s" /></span>', esc_attr( $icon ) );
			$html .= sprintf( '<span class="sysui-window-label">%s</span>', esc_html( apply_filters( 'the_title', get_the_title( $post ) ) ) );
			$html .= '</a>';
			$return_object['current_items'] ++;
		}
		$html .= '</div>';
		$return_object['icon'] = Theme_Utils::asset_path( 'images/search.png' );
		$return_object['content'] = $html;
		$return_object['width'] = 562;
		$return_object['height'] = 400;
		wp_send_json_success( $return_object );
	}

	public static function add_comment_to_post()
	{
		$c = get_called_class();
		$data = stripslashes_deep( $_POST );
		$wp_user = wp_get_current_user();
		if ( ! is_user_logged_in() ) {
			wp_send_json_error( __( 'You must be logged in to comment' ) );
		}
		$comment_data = array(
			'comment_post_ID' => intval( self::get_array_key( 'post_id', $data, 0 ) ),
			'comment_parent' => intval( self::get_array_key( 'reply_to', $data, 0 ) ),
			'comment_content' => esc_html( self::get_array_key( 'content', $data, '' ) ),
			'user_id' => $wp_user->ID,
			'comment_author' => $wp_user->display_name,
			'comment_author_email' => $wp_user->user_email,
			'comment_author_url' => $wp_user->user_url,
		);
		//wp_send_json_error( print_r( $comment_data, true ) );
		$res = wp_new_comment( $comment_data, true );
		if ( is_a( $res, 'WP_Error' ) ) {
			wp_send_json_error( $res->get_error_message() );
		}
		wp_send_json_success( array(
			'post' => intval( self::get_array_key( 'post_id', $data, 0 ) ),
			'comment' => $res,
		) );
	}

	private static function get_the_permalink( WP_Query $wp_query )
	{
		switch ( true ) {
			case $wp_query->is_home:
				return home_url() . '/';
				break;

			case $wp_query->is_category:
				$term = $wp_query->get_queried_object();
				return get_category_link( $term->term_id );
				break;

			case $wp_query->is_tag:
				$term = $wp_query->get_queried_object();
				return get_tag_link( $term->term_id );
				break;

			case $wp_query->is_tax:
				$term = $wp_query->get_queried_object();
				return get_term_link( $term );
				break;

			case $wp_query->is_author:
				$term = $wp_query->get_queried_object();
				return get_author_posts_url( $term->ID );
				break;

			case $wp_query->is_archive:
				$term = $wp_query->get_queried_object();
				return get_term_link( $term );
				break;

			case $wp_query->is_search:
				return get_search_link( self::get_array_key( 's', $wp_query->query ) );
				break;

			default:
				return home_url() . '/';
				break;
		}
	}

	private static function make_title_from_wp_query( WP_Query $wp_query )
	{
		$term = $wp_query->get_queried_object();
		switch ( true ) {
			case $wp_query->is_category || $wp_query->is_tag:
				$term = $wp_query->get_queried_object();
				$title = $term->name;
				if ( $wp_query->is_category ) {
					$title = apply_filters( 'single_cat_title', $title );
				}
				if ( $wp_query->is_tag ) {
					$title = apply_filters( 'single_tag_title', $title );
				}
				return $title;
				break;

			case $wp_query->is_tax && ( $term ):
				$tax   = get_taxonomy( $term->taxonomy );
				return single_term_title( $tax->labels->name, false );
				break;

			case $wp_query->is_author:
				$author = $wp_query->get_queried_object();
				return $author->display_name;
				break;

			case $wp_query->is_archive && ! empty( $wp_query->query_vars['m'] ):
				$my_year  = substr( $wp_query->query_vars['m'], 0, 4 );
				$my_month = $wp_locale->get_month( substr( $wp_query->query_vars['m'], 4, 2 ) );
				$my_day   = intval( substr( $wp_query->query_vars['m'], 6, 2 ) );
				$title    = $my_year . ( $my_month ? $t_sep . $my_month : '' ) . ( $my_day ? $t_sep . $my_day : '' );
				return $title;
				break;

			case $wp_query->is_archive && ! empty( $wp_query->query_vars['y'] ):
				$title = $wp_query->query_vars['year'];
				if ( ! empty( $wp_query->query_vars['monthnum'] ) ) {
					$title .= $t_sep . $wp_locale->get_month( $wp_query->query_vars['monthnum'] );
				}
				if ( ! empty( $wp_query->query_vars['day'] ) ) {
					$title .= $t_sep . zeroise( $wp_query->query_vars['day'], 2 );
				}
				return $title;
				break;

			case $wp_query->is_search:
				$title = sprintf( __( 'Search Results – %s' ), strip_tags( $wp_query->query_vars['s'] ) );
				return $title;
				break;

			default:
				return sprintf( '%s – %s', get_bloginfo( 'name' ), get_bloginfo( 'description' ) );
				break;
		}
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