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

	public function __construct( $query ) {
		global $wp_rewrite;
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
				$this->window_properties['icon'] = Theme_Utils::asset_path( 'images/stop.png' );
				$this->window_properties['title'] = __( 'Item Not Found' );
				$this->window_properties['minimize'] = false;
				$this->window_properties['maximize'] = false;
				$this->window_properties['width'] = 320;
				$this->window_properties['height'] = 76;
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
        		$this->window_properties['content'] = sprintf( '<div class="sysui-text-content"><p>%s</p></div>', __( 'Sorry, the item you have requested could not be found' ) );
				break;

			case $this->wpo->is_single || $this->wpo->is_page:
				$post = $this->wpo->post;
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
				$this->window_properties['title'] = apply_filters( 'the_title', get_the_title( $post ) );
				$post->post_content = apply_filters( 'the_content', $post->post_content );
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
				$this->window_properties['icon'] = Theme_Utils::asset_path( 'images/stop.png' );
				$this->window_properties['title'] = __( 'Item Not Found' );
				$this->window_properties['minimize'] = false;
				$this->window_properties['maximize'] = false;
				$this->window_properties['width'] = 320;
				$this->window_properties['height'] = 76;
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
        		$this->window_properties['content'] = sprintf( '<div class="sysui-text-content"><p>%s</p></div>', __( 'Sorry, the item you have requested could not be found' ) );
				break;
		}
	}

	public function get_window_object() {
		$obj = new stdClass();
		foreach ( $this->window_properties as $key => $value ) {
			$obj->{ $key } = $value;
		}
		return $obj;
	}

	public static function parse() {
		$c = get_called_class();
		$data = stripslashes_deep( $_POST );
		$query = self::get_array_key( 'query', $data, '/' );
		$obj = new $c( $query );
		wp_send_json_success( $obj->get_window_object() );
	}

	private static function get_array_key( $key, $array, $default = null )
	{
		if ( is_array( $array ) && array_key_exists( $key, $array ) ) {
			return $array[ $key ];
		}
		return $default;
	}
}