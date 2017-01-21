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
	protected static $forms = [];

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
	 * @param string       $id should be unique
	 * @param array|string $settings ( optional ) define form settings on construction
	 */
	public function __construct( $id, $settings = '' )
	{
		// cache/save form
		self::$forms[ $id ] = &$this;

		$this->ID = $id;

		// set settings
		$this->set_settings( $settings );
	}

	/**
	 * Set form fields
	 *
	 * @param array $fields
	 *
	 * @return void
	 */
	public function set_fields( $fields )
	{
		if ( empty( $fields ) )
		{
			return;
		}

		// loop fields
		foreach ( $fields as $field_name => $field_args )
		{
			$this->add_field( $field_name, $field_args );
		}
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
	 * @param array  $field_args
	 *
	 * @return void
	 */
	public function add_field( $field_name, $field_args )
	{
		$default = [
			'label'             => '',
			'input'             => 'text',
			'data_type'         => '',
			'data_type_options' => [],
			'attributes'        => [],
			'description'       => '',
			'section'           => '',
			'required'          => false,
		];

		/**
		 * Filter form arguments before adding
		 *
		 * @param array  $field_args
		 * @param string $field_name
		 * @param string $form_id
		 *
		 * @return array
		 */
		$this->fields[ $field_name ] = apply_filters( 'wp_sfb_form_field_args', wp_parse_args( $field_args, $default ), $field_name, $this->ID );
	}

	/**
	 * Set form sections
	 *
	 * @param array $sections
	 *
	 * @return void
	 * @throws Exception
	 */
	public function set_sections( $sections )
	{
		if ( !is_array( $sections ) || empty( $sections ) )
		{
			// skip if invalid value
			throw new Exception( 'Invalid section(s) data', 'wp_sfb_invalid_sections' );
		}

		// loop through sections
		foreach ( $sections as $section_name => $section_args )
		{
			$this->add_section( $section_name, $section_args );
		}
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
	 * @param array  $section_args
	 *
	 * @return void
	 */
	public function add_section( $section_name, $section_args )
	{
		$default = [
			'label'       => '',
			'description' => '',
		];

		/**
		 * Filter section arguments before adding
		 *
		 * @param array  $section_args
		 * @param string $section_name
		 * @param string $form_id
		 *
		 * @return array
		 */
		$this->sections[ $section_name ] = apply_filters( 'wp_sfb_form_section_args', wp_parse_args( $section_args, $default ), $section_name, $this->ID );
	}

	/**
	 * Set form settings
	 *
	 * @param array $settings
	 *
	 * @return void
	 * @throws Exception
	 */
	public function set_settings( $settings )
	{
		// default settings
		$defaults = [
			'render_engine'    => 'SFB_Render_Engine',
			'validator_engine' => 'SFB_Validator',
			'submit_hook'      => 'sfb_handler_' . $this->ID,
			'submit_redirect'  => [
				'url'    => '',
				'status' => 302,
			],
			'submit_success'   => __( 'Settings saved.', WP_SFB_TEXT_DOMAIN ),
			'option_key'       => false,
			'option_autoload'  => 'no',
			'attributes'       => [
				'action'  => '',
				'method'  => 'post',
				'enctype' => 'application/x-www-form-urlencoded',
			],
			'submit'           => [
				'name'   => 'sfb_submit_' . $this->ID,
				'text'   => __( 'Save Changes', WP_SFB_TEXT_DOMAIN ),
				'before' => '',
				'after'  => '',
			],
		];

		/**
		 * Filter form settings
		 *
		 * @param array  $settings
		 * @param string $form_id
		 *
		 * @return array
		 */
		$this->settings = apply_filters( 'wp_sfb_form_settings', wp_parse_args( $settings, $defaults ), $this->ID );

		if ( class_exists( $this->settings['render_engine'] ) )
		{
			// render engine instance
			$this->render_engine = new $this->settings['render_engine']( $this->ID, $this->settings );
		}
		else
		{
			// render engine not found
			throw new Exception( sprintf( 'Render Engine [%s] not found', $this->settings['render_engine'] ), 'wp_sfb_missing_render_engine' );
		}

		if ( class_exists( $this->settings['validator_engine'] ) )
		{
			// validator engine instance
			$this->validator_engine = new $this->settings['validator_engine']( $this->ID, $this->settings );
		}
		else
		{
			// Validation engine not found!
			throw new Exception( sprintf( 'Validation Engine [%s] not found', $this->settings['validator_engine'] ), 'wp_sfb_missing_validator_engine' );
		}

		// save form values
		$save_values_callback = [ &$this, 'save_inputs_values' ];
		if ( $this->settings['option_key'] !== false && !has_action( 'init', $save_values_callback ) )
		{
			// saving form data hook
			add_action( 'init', $save_values_callback, 15 );
		}
	}

	/**
	 * Form Settings getter
	 *
	 * @param string $option
	 *
	 * @return array
	 */
	public function get_settings( $option = '' )
	{
		return isset( $this->settings[ $option ] ) ? $this->settings[ $option ] : $this->settings;
	}

	/**
	 * Save form submitted values
	 *
	 * @return void
	 */
	public function save_inputs_values()
	{
		global $wp;

		$submit = $this->settings['submit']['name'];

		// form submit
		if ( !isset( $_POST[ $submit ] ) )
		{
			// skip as form not submitted yet
			return;
		}

		/**
		 * Pre global form submitting action
		 *
		 * @param string $form_id
		 */
		do_action( 'pre_sfb_form_submit', $this->ID );

		/**
		 * Specific form submitting
		 */
		do_action( 'pre_' . $this->settings['submit_hook'] );

		// submitted form method
		if ( 'post' === $this->settings['attributes']['method'] )
		{
			// POST
			$submitted_values = &$_POST;
		}
		else
		{
			// GET
			$submitted_values = &$_GET;
		}

		// start validating values
		$submitted_values = $this->validator_engine->walk_fields( $this->fields, $this->default_fields_values( $submitted_values ) );

		if ( is_wp_error( $submitted_values ) )
		{
			// save errors
			$_SESSION['sfb_errors'] = $submitted_values;

			// redirect
			SFB_Helpers::redirect();
		}

		// check option
		if ( false === get_option( $this->settings['option_key'] ) )
		{
			// create it
			add_option( $this->settings['option_key'], $submitted_values, '', $this->settings['option_autoload'] );
		}
		else
		{
			// update it
			update_option( $this->settings['option_key'], $submitted_values );
		}

		/**
		 * Form submission success action
		 *
		 * @param array    $submitted_values
		 * @param SFB_Form $form
		 */
		do_action_ref_array( 'sfb_form_data_saved', [ $submitted_values, &$this ] );

		// redirect URL
		$submit_redirect = $this->settings['submit_redirect']['url'];
		if ( empty( $submit_redirect ) )
		{
			// fallback to current request URI
			$submit_redirect = $wp->request;
		}

		/**
		 * Filter form success data submission redirect URL
		 *
		 * @param string   $submit_redirect
		 * @param SFB_Form $form
		 *
		 * @return string|false
		 */
		$submit_redirect = apply_filters_ref_array( 'sfb_form_submit_redirect_url', [ $submit_redirect, &$this ] );

		if ( false !== $submit_redirect )
		{
			// do the redirect
			SFB_Helpers::redirect( add_query_arg( 'success', 'yes', $submit_redirect ), $this->settings['submit_redirect']['status'] );
		}
	}

	/**
	 * Set form settings option
	 *
	 * @param string $option
	 * @param mixed  $value
	 *
	 * @return void
	 */
	public function set_settings_option( $option, $value )
	{
		$option = sanitize_key( $option );

		/**
		 * Filter form settings option
		 *
		 * @param mixed  $value
		 * @param string $option
		 * @param string $form_id
		 *
		 * @return mixed
		 */
		$this->settings[ $option ] = apply_filters( 'wp_sfb_form_settings_option', $value, $option, $this->ID );
	}

	/**
	 * Render form layout
	 *
	 * @param array ( optional ) $values form fields values
	 * @param boolean            $echo ( optional ) Wither to echo form rendered layout or not, default true
	 *
	 * @return string|void
	 */
	public function render_ouput( $values = '', $echo = true )
	{
		ob_start();

		// parse values
		$values = $this->default_fields_values( $values );

		// walk through fields
		$this->render_engine->walk_fields( $this->sections, $this->fields, $values );

		/**
		 * Filter form render output
		 *
		 * @param string $form_output
		 * @param string $form_id
		 *
		 * @return string
		 */
		$output = apply_filters( 'wp_sfb_form_output', ob_get_clean(), $this->ID );

		if ( false === $echo )
		{
			// return output
			return $output;
		}

		echo $output;
	}

	/**
	 * Default fields values
	 *
	 * @param array $values
	 *
	 * @return array
	 */
	protected function default_fields_values( $values )
	{
		if ( empty( $values ) )
		{
			$values = [];
		}

		/**
		 * Filter form fields' default values
		 *
		 * @param array  $default_values
		 * @param string $form_id
		 *
		 * @return array
		 */
		return apply_filters( 'sfb_default_fields_values', array_merge( array_fill_keys( array_keys( $this->fields ), '' ), $values ), $this->ID );
	}

	/**
	 * Get registered form instance
	 *
	 * @param string $form_id
	 *
	 * @return SFB_Form|boolean
	 */
	public static function get_form( $form_id )
	{
		return isset( self::$forms[ $form_id ] ) ? self::$forms[ $form_id ] : false;
	}

	/**
	 * Render form
	 *
	 * @param string       $form_id
	 * @param array|string $values form fields' values
	 * @param boolean      $echo ( optional ) Wither to echo form rendered layout or not, default true
	 *
	 * @return string|void
	 * @throws Exception
	 */
	public static function render_form( $form_id, $values = '', $echo = true )
	{
		$form = self::get_form( $form_id );
		if ( false === $form )
		{
			// skip
			throw new Exception( sprintf( 'Form [%s] not found!', $form_id ), 'wp_sfb_form_not_found' );
		}

		return $form->render_ouput( $values, $echo );
	}
}




















