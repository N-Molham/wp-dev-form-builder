<?php
/**
 * Form class
 * 
 * @package WP Form Builder
 * @since 1.0
 */

class SFB_Form
{
	/**
	 * Forms instances holder
	 * 
	 * @var array
	 */
	protected static $forms = array();

	/**
	 * Form ID
	 * 
	 * @var string
	 */
	protected $ID;

	/**
	 * Form settings
	 * 
	 * @var array
	 */
	protected $settings;

	/**
	 * Form fields
	 * 
	 * @var array
	 */
	protected $fields;

	/**
	 * Form sections
	 * 
	 * @var array
	 */
	protected $sections;

	/**
	 * Form Mode engine
	 * 
	 * @var SFB_Mode
	 */
	protected $mode;

	/**
	 * Constructor
	 * 
	 * @param string $id should be unique
	 * @param array $settings ( optional ) define form settings on construction
	 * @return void
	 */
	public function __construct( $id, $settings = '' )
	{
		// cache/save form
		self::$forms[$id] = &$this;

		$this->ID = $id;

		// set settings
		$this->set_settings( $settings );

		// mode engine instance
		if ( class_exists( $this->settings['mode_engine'] ) )
			$this->mode = new $this->settings['mode_engine']( $id, $this->settings );
		else
			throw new Exception( 'Mode Engine not found' );
	}

	/**
	 * Set form fields
	 * 
	 * @param array $fields
	 * @return void
	 */
	public function set_fields( $fields )
	{
		if ( empty( $fields ) )
			return;

		// loop fields
		foreach ( $fields as $field_name => $field_args )
			$this->add_field( $field_name, $field_args );
	}

	/**
	 * Get form fields
	 * 
	 * @return array
	 */
	public function get_fields()
	{
		return $this->fields;
	}

	/**
	 * Add field to form field set
	 * 
	 * @param string $field_name
	 * @param array $field_args
	 * @return void
	 */
	public function add_field( $field_name, $field_args )
	{
		$default = array ( 
				'label' => '',
				'input' => 'text',
				'data_type' => 'text',
				'attributes' => array(),
				'description' => '',
				'section' => '',
		);

		// add to field set
		$this->fields[ $field_name ] = apply_filters( 'wp_sfb_form_field_args', wp_parse_args( $field_args, $default ), $field_name, $this->ID );
	}

	/**
	 * Set form sections
	 * 
	 * @param array $sections
	 * @return void
	 */
	public function set_sections( $sections )
	{
		if ( empty( $sections ) )
			return;

		// loop fields
		foreach ( $sections as $section_name => $section_args )
			$this->add_section( $section_name, $section_args );
	}

	/**
	 * Add section to form sections set
	 *
	 * @param string $section_name
	 * @param array $section_args
	 * @return void
	 */
	public function add_section( $section_name, $section_args )
	{
		$default = array (
				'label' => '',
				'description' => '',
		);

		// add to field set
		$this->sections[ $section_name ] = apply_filters( 'wp_sfb_form_section_args', wp_parse_args( $section_args, $default ), $section_name, $this->ID );
	}

	/**
	 * Set form settings
	 * 
	 * @param array $settings
	 * @return void
	 */
	public function set_settings( $settings )
	{
		// default settings
		$defaults = array ( 
				'mode_engine' => 'SFB_Mode',
				'handler_hook' => 'sfb_handler_'. $this->ID,
				'page' => '',
				'attributes' => array ( 
						'action' => '', 
						'method' => 'post', 
						'enctype' => 'application/x-www-form-urlencoded', 
				),
		);

		$this->settings = apply_filters( 'wp_sfb_form_settings', wp_parse_args( $settings, $defaults ), $this->ID );
	}

	/**
	 * Set form settings option
	 * 
	 * @param string $option
	 * @param mixed $value
	 * @return void
	 */
	public function set_settings_option( $option, $value )
	{
		$option = sanitize_key( $option );
		if ( !isset( $this->settings[ $option ] ) )
			return;

		$this->settings[ $option ] = apply_filters( 'wp_sfb_form_settings_option', $value, $option, $this->ID );
	}

	/**
	 * Render form layout
	 * 
	 * @param array ( optional ) $values form fields values
	 * @param boolean $echo ( optional ) Wither to echo form rendered layout or not, default true
	 * @return void|string
	 */
	public function render_ouput( $values = '', $echo = true )
	{
		ob_start();

		if ( empty( $values ) )
			$values = array();

		// parse values
		$values = array_merge( array_fill_keys( array_keys( $this->fields ), '' ), $values );

		// walk through fields
		$this->mode->walk_fields( $this->sections, $this->fields, $values );

		// final form output
		$output = apply_filters( 'wp_sfb_form_output', ob_get_clean(), $this->ID );

		if ( $echo )
			echo $output;
		else
			return $output;
	}

	/**
	 * Get registered form instance
	 * 
	 * @param string $form_id
	 * @return SFB_Form|boolean
	 */
	public static function get_form( $form_id )
	{
		return isset( self::$forms[ $form_id ] ) ? self::$forms[ $form_id ] : false;
	}

	/**
	 * Render form
	 * 
	 * @param string $form_id
	 * @param array ( optional ) $values form fields' values
	 * @param boolean $echo ( optional ) Wither to echo form rendered layout or not, default true
	 * @return void|string
	 */
	public static function render_form( $form_id, $values = '', $echo = true )
	{
		$form = self::get_form( $form_id );
		if ( $form )
			return $form->render_ouput( $values, $echo );
	}
}




















