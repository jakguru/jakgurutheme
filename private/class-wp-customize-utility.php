<?php
defined( 'ABSPATH' ) || die( 'Sorry, but you cannot access this page directly.' );

/**
 * Toolkit for working with WP Customize API
 */
class WP_Customize_Utility
{
	private $registered = false;
	private $wp_customize_sections = array();
	private $wp_customize_settings = array();
	private $wp_customize_controls = array();
	private $wp_customize_control_classes = array(
		'WP_Customize_Control',
		'WP_Customize_Color_Control',
		'WP_Customize_Upload_Control',
		'WP_Customize_Image_Control',
		'WP_Customize_Background_Image_Control',
		'WP_Customize_Header_Image_Control',
	);

	public function register( \WP_Customize_Manager $wp_customize )
	{
		$wp_customize;
		$sections = $wp_customize->sections();
		$this->wp_customize_sections = array_keys( $sections );
		$settings = $wp_customize->settings();
		$this->wp_customize_settings = array_keys( $settings );
		foreach ( $this->wp_customize_controls as $id => $args ) {
			$section = self::get_array_key( 'section', $args );
			switch ( true ) {
				case is_array( $section ):
					$section_id = self::get_array_key( 'id', $section );
					if (
						! is_string( $section_id )
						|| strlen( $section_id ) === 0
					) {
						continue 2;
					}
					else if ( ! array_key_exists( $section_id, $this->wp_customize_sections ) ) {
						unset( $section['id'] );
						$res = call_user_func( array( $wp_customize, 'add_section' ), $section_id, $section );
						array_push( $this->wp_customize_sections, $section_id );
						$args['section'] = $section_id;
						if ( ! is_a( $res, 'WP_Customize_Section' ) ) {
							continue 2;
						}
					}
					break;

				case is_string( $section ):
					if ( ! in_array( $section, $this->wp_customize_sections ) ) {
						continue 2;
					}
					break;
				
				default:
					continue 2;
					break;
			}
			if ( ! array_key_exists( $id, $this->wp_customize_settings ) ) {
				$default = self::get_array_key( 'default', $args );
				$res = call_user_func( array( $wp_customize, 'add_setting' ), $id, array(
					'default' => $default,
				) );
				if ( ! is_a( $res, 'WP_Customize_Setting' ) ) {
					continue;
				}
				array_push( $this->wp_customize_settings, $id );
			}
			unset( $args['default'] );
			if ( ! in_array( self::get_array_key( 'class', $args ), $this->wp_customize_control_classes ) ) {
				$args['class'] = 'WP_Customize_Control';
			}
			if ( 'WP_Customize_Control' !== $args['class'] ) {
				unset( $args['type'] );
				$new_args = new $args['class']( $wp_customize, $id, $args );
				$args = $new_args;
				$res = call_user_func( array( $wp_customize, 'add_control' ), $args );
			}
			else {
				$res = call_user_func( array( $wp_customize, 'add_control' ), $id, $args );
			}
			if ( is_a( $res, 'WP_Customize_Control' ) ) {
				array_push( $this->wp_customize_controls, $id );
			}
		}
		$this->registered = true;
	}

	public function enqueue_control( $id, $args = array() )
	{
		if ( true == $this->registered ) {
			return false;
		}
		$defaults = array(
			'label' => __( 'Customize Control' ),
			'description' => __( 'A Customize Control' ),
			'section' => array(
				'id' => '',
				'title' => __( 'Customize' ),
				'priority' => 80,
				'description' => __( 'A Customize Section' ),
				'active_callback' => null,
			),
			'priority' => 0,
			'type' => 'text',
			'class' => 'WP_Customize_Control',
		);
		$arguments = array_replace_recursive( $defaults, $args );
		$this->wp_customize_controls[ $id ] = $arguments;
	}

	private static function get_array_key( $key, $array, $default = null )
	{
		if ( is_array( $array ) && array_key_exists( $key, $array ) ) {
			return $array[ $key ];
		}
		return $default;
	}
}