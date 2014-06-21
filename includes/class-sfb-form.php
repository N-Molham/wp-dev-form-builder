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
	 * Form render engine
	 * 
	 * @var SFB_Render_Engine
	 */
	protected $render_engine;

	/**
	 * Form validation engine
	 * 
	 * @var SFB_Validator
	 */
	protected $validator_engine;

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
				'data_type' => '',
				'data_type_options' => array(),
				'attributes' => array(),
				'description' => '',
				'section' => '',
				'required' => false,
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
	 * Form Sections getter
	 *
	 * @return array
	 */
	public function get_sections()
	{
		return $this->sections;
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
				'render_engine' => 'SFB_Render_Engine', 
				'validator_engine' => 'SFB_Validator', 
				'submit_hook' => 'sfb_handler_'. $this->ID, 
				'submit_redirect' => array ( 
						'url' => '',
						'status' => 302,
				),
				'option_key' => false, 
				'option_autoload' => 'no',
				'attributes' => array ( 
						'action' => '', 
						'method' => 'post', 
						'enctype' => 'application/x-www-form-urlencoded', 
				), 
				'submit' => array ( 
						'name' => 'sfb_submit', 
						'text' => __( 'Save Changes', WP_SFB_TEXT_DOMAIN ), 
						'before' => '', 
						'after' => '',
				),
		);

		$this->settings = apply_filters( 'wp_sfb_form_settings', wp_parse_args( $settings, $defaults ), $this->ID );

		// render engine instance
		if ( class_exists( $this->settings['render_engine'] ) )
			$this->render_engine = new $this->settings['render_engine']( $this->ID, $this->settings );
		else
			throw new Exception( 'Render Engine not found' );

		// validator engine instance
		if ( class_exists( $this->settings['validator_engine'] ) )
			$this->validator_engine = new $this->settings['validator_engine']( $this->ID, $this->settings );
		else
			throw new Exception( 'Validation Engine not found' );

		// save form values
		$save_values_callback = array( &$this, 'save_inputs_values' );
		if ( $this->settings['option_key'] !== false && !has_action( 'init', $save_values_callback ) )
			add_action( 'init', $save_values_callback, 15 );
	}

	/**
	 * Form Settings getter
	 * 
	 * @return array
	 */
	public function get_settings()
	{
		return $this->settings;
	}

	/**
	 * Save form submitted values
	 * 
	 * @return void
	 */
	public function save_inputs_values()
	{
		$submit = $this->settings['submit']['name'];

		// form submit
		if ( !isset( $_POST[ $submit ] ) )
			return;

		// global form submitting action
		do_action( 'sfb_form_submit', $this->ID );

		// specific form submitting
		do_action( $this->settings['submit_hook'] );

		// submitted form values
		if ( 'post' === $this->settings['attributes']['method'] )
			$submitted_values = &$_POST;
		else
			$submitted_values = &$_GET;

		$submitted_values = $this->validator_engine->walk_fields( $this->fields, $this->default_fields_values( $submitted_values ) );

		// check option
		if ( false === get_option( $this->settings['option_key'] ) )
			add_option( $this->settings['option_key'], $submitted_values, '', $this->settings['option_autoload'] );
		else 
			update_option( $this->settings['option_key'], $submitted_values );

		// form data saved
		do_action_ref_array( 'sfb_form_data_saved', array( $submitted_values, &$this ) );

		// redirect
		SFB_Helpers::redirect( $this->settings['submit_redirect']['url'], $this->settings['submit_redirect']['status'] );
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

		// parse values
		$values = $this->default_fields_values( $values );

		// walk through fields
		$this->render_engine->walk_fields( $this->sections, $this->fields, $values );

		// final form output
		$output = apply_filters( 'wp_sfb_form_output', ob_get_clean(), $this->ID );

		if ( $echo )
			echo $output;
		else
			return $output;
	}

	/**
	 * Default fields values
	 * 
	 * @param array $values
	 * @return array
	 */
	protected function default_fields_values( $values )
	{
		if ( empty( $values ) )
			$values = array();

		return apply_filters( 'sfb_default_fields_values', array_merge( array_fill_keys( array_keys( $this->fields ), '' ), $values ), $this->ID );
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




















