<?php
/**
 * Utility for adding additional fields to the Menu Editor

 * @package Jak Guru Theme
 */

defined( 'ABSPATH' ) || die( 'Sorry, but you cannot access this page directly.' );
if ( ! class_exists( 'Walker_Nav_Menu_Edit' ) ) {
	require_once sprintf( '%swp-admin/includes/nav-menu.php', ABSPATH );
}

/**
 * Adds additional fields to the WordPress Menu Manager
 */
class Additional_Menu_Fields_Utility extends \Walker_Nav_Menu_Edit {
	/**
	 * New Fields to be added

	 * @var array
	 */
	private $new_fields = array();

	/**
	 * Allowed HTML Tags

	 * @var array
	 */
	private $allowed_tags = array(
		'p'      => array(
			'class' => true,
		),
		'label'  => array(
			'for' => true,
		),
		'br'     => true,
		'input'  => array(
			'id'       => true,
			'class'    => true,
			'name'     => true,
			'value'    => true,
			'required' => true,
			'type'     => true,
			'checked'  => true,
		),
		'select' => array(
			'id'       => true,
			'class'    => true,
			'name'     => true,
			'value'    => true,
			'required' => true,
			'multiple' => true,
		),
		'option' => array(
			'value'    => true,
			'selected' => true,
		),
		'div'    => array(
			'id'    => true,
			'class' => true,
		),
		'span'   => array(
			'id'    => true,
			'class' => true,
		),
		'a'      => array(
			'href'  => true,
			'for'   => true,
			'id'    => true,
			'class' => true,
		),
	);

	/**
	 * The nonce which will be use to validate updates

	 * @var string
	 */
	private $nonce = '';

	/**
	 * Create the Object
	 */
	public function __construct() {
		$this->nonce = wp_create_nonce( 'wp_save_custom_menu_fields' );
	}

	/**
	 * Add the new properties to the nav item

	 * @param  Object $menu_item The Item to be Updated.
	 * @return Object            The Item after it has been updated
	 */
	public function setup_nav_item( $menu_item ) {
		foreach ( $this->new_fields as $key => $field_args ) {
			$value = get_post_meta( $menu_item->ID, $key, true );
			if ( ! is_string( $value ) || empty( $value ) ) {
				$value = self::get_array_key( 'default', $field_args );
			}
			$menu_item->{$key} = $value;
		}
		return $menu_item;
	}

	/**
	 * Update the value of the new properties on a nav item on save

	 * @param  Integer $menu_id The ID of the menu item being updated.
	 * @param  array   $args    Arguments being passed.
	 * @return void
	 */
	public function update_nav_item( $menu_id, $args = array() ) {
		foreach ( $this->new_fields as $key => $field_args ) {
			$validation = check_ajax_referer( 'wp_save_custom_menu_fields', 'wpcmf-nonce', false );
			if ( false === $validation ) {
				return;
			}
			$_post = stripslashes_deep( $_POST );
			if ( is_array( $_post[ $key ] ) ) {
				foreach ( $_post[ $key ] as $menu_item_db_id => $value ) {
					update_post_meta( $menu_item_db_id, $key, $value );
				}
			}
		}
	}

	/**
	 * Return the name of the Nav Menu Walker Class Used

	 * @param  Walker   $walker  A Walker.
	 * @param  Interger $menu_id The Menu ID.
	 * @return String            The name of the class
	 */
	public function nav_menu_walker( $walker, $menu_id ) {
		$c = get_called_class();
		return $c;
	}

	/**
	 * Render the custom field

	 * @param  interger $id    The field ID.
	 * @param  object   $item  The item.
	 * @param  interger $depth The field depth.
	 * @param  array    $args  Some arguments.
	 * @return void
	 */
	public function render_custom_fields( $id, $item, $depth, $args ) {
		$html = '';
		foreach ( $this->new_fields as $key => $field_args ) {
			$html_id   = sprintf( 'edit-%s-%d', $key, $item->ID );
			$html_name = sprintf( '%s[%d]', $key, $item->ID );
			$value     = get_post_meta( $item->ID, $key, true );
			if ( ! is_string( $value ) || empty( $value ) ) {
				$value = self::get_array_key( 'default', $field_args );
			}
			$html .= sprintf( '<p class="description description-wide description-id field-%s">', $key );
			$html .= sprintf( '<label for="%s">%s<br />', $html_id, self::get_array_key( 'label', $field_args ) );
			$html .= self::render_field_html( $html_id, $html_name, $value, ( true === self::get_array_key( 'required', $field_args ) ), self::get_array_key( 'type', $field_args, 'text' ), self::get_array_key( 'options', $field_args, array() ) );
			$html .= '</label>';
			$html .= '</p>';
		}
		echo wp_kses( $html, $this->allowed_tags );
	}

	/**
	 * Add a new custom field

	 * @param text  $id   the identifier / key of the field to be added.
	 * @param array $args field arguments.
	 */
	public function add_field( $id, $args = array() ) {
		$defaults                = array(
			'label'    => __( 'Custom Field' ),
			'type'     => 'text',
			'default'  => null,
			'required' => false,
		);
		$arguments               = array_replace_recursive( $defaults, $args );
		$this->new_fields[ $id ] = $arguments;
	}

	/**
	 * Print the beginning of an item

	 * @param  string  $output The HTML Output.
	 * @param  object  $item    The menu item.
	 * @param  integer $depth   The menu depth.
	 * @param  array   $args    The menu item arguments.
	 * @param  integer $id      The menu item id.
	 * @return void
	 */
	public function start_el( &$output, $item, $depth = 0, $args = array(), $id = 0 ) {
		$item_output = '';
		parent::start_el( $item_output, $item, $depth, $args, $id );
		$output .= preg_replace(
			'/(?=<(fieldset|p)[^>]+class="[^"]*field-move)/',
			$this->get_fields( $item, $depth, $args ),
			$item_output
		);
	}

	/**
	 * Get the new fields

	 * @param  object   $item  the menu item.
	 * @param  interger $depth the depth of the menu item.
	 * @param  array    $args  the menu item arguments.
	 * @param  integer  $id    the menu item id number.
	 * @return text            the field html.
	 */
	protected function get_fields( $item, $depth, $args = array(), $id = 0 ) {
		ob_start();
		echo sprintf( '<input type="hidden" name="wpcmf-nonce" value="%s" />', esc_attr( $this->nonce ) );
		do_action( 'wp_nav_menu_item_custom_fields', $item->ID, $item, $depth, $args, $id );
		return ob_get_clean();
	}

	/**
	 * Render an HTML field for custom fields

	 * @param  text    $id       the field id.
	 * @param  text    $name     the name of the field.
	 * @param  mixed   $value    the current value of the field.
	 * @param  boolean $required if the field is required or not.
	 * @param  string  $type     the field type.
	 * @param  array   $options  options for a select, radio or checkbox field.
	 * @return string            the html to be returned.
	 */
	private static function render_field_html( $id, $name, $value = null, $required = true, $type = 'text', $options = array() ) {
		$html = '';
		switch ( $type ) {
			case 'multiselect':
				$html .= sprintf(
					'<select id="%s" name="%s" class="widefat %s" %s multiple>',
					$id, $name, $id, ( true === $required ) ? 'required' : ''
				);
				if ( is_array( $options ) ) {
					foreach ( $options as $option_value => $label ) {
						$html .= sprintf( '<option value="%s" %s>%s</option>', $option_value, ( $value === $option_value ) ? 'selected' : '', $label );
					}
				}
				$html .= '</select>';
				break;

			case 'select':
				$html .= sprintf(
					'<select id="%s" name="%s" class="widefat %s" %s>',
					$id, $name, $id, ( true === $required ) ? 'required' : ''
				);
				if ( is_array( $options ) ) {
					foreach ( $options as $option_value => $label ) {
						$html .= sprintf( '<option value="%s" %s>%s</option>', $option_value, ( $value === $option_value ) ? 'selected' : '', $label );
					}
				}
				$html .= '</select>';
				break;

			case 'textarea':
				$html .= sprintf(
					'<textarea id="%s" name="%s" class="widefat %s" %s>%s</textarea>',
					$id, $name, $id, ( true === $required ) ? 'required' : '', $value
				);
				break;

			case 'image':
				$html .= '<span class="menu-image-field-wrapper">';
				$html .= sprintf( '<span id="%s" class="widefat %s menu-image-field">', $id, $id );
				$html .= '<span class="image-preview"></span>';
				$html .= sprintf(
					'<span class="image-control-wrapper"><a href="#" class="button button-primary open-image-media-manager" for="%s">%s</a><a href="#" class="button refresh-preview">%s</a></span>',
					$id, __( 'Choose Image' ), __( 'Refresh Preview' )
				);
				$html .= '</span>';
				$html .= sprintf( '<input type="text" name="%s" value="%s" class="widefat" %s />', $name, $value, ( true === $required ) ? 'required' : '' );
				$html .= '</span>';
				break;

			default:
				$html .= sprintf(
					'<input type="%s" id="%s" name="%s" class="widefat %s" value="%s" %s>',
					$type, $id, $name, $id, $value, ( true === $required ) ? 'required' : ''
				);
				break;
		}
		return $html;
	}

	/**
	 * Get the value or default value from an array by its key

	 * @param  string $key     the key to retrive the value from.
	 * @param  array  $array   the array to retrieve the value from.
	 * @param  mixed  $default the default value to be returned if the key isn't set.
	 * @return mixed           the value to be returned.
	 */
	private static function get_array_key( $key, $array, $default = null ) {
		if ( is_array( $array ) && array_key_exists( $key, $array ) ) {
			return $array[ $key ];
		}
		return $default;
	}
}
