<?php
/**
 * Default Form validation engine
 *
 * @package WP Form Builder
 * @since 1.0
 */

class SFB_Validator
{
	/**
	 * Connected form ID
	 * 
	 * @var string
	 */
	protected $form_id;

	/**
	 * Connected form settings
	 * @var string
	 */
	protected $form_settings;

	/**
	 * Data Type: Text
	 *
	 * @var string
	 */
	const DATA_TYPE_TEXT = 'text';

	/**
	 * Data Type: email address
	 *
	 * @var string
	 */
	const DATA_TYPE_EMAIL = 'email';

	/**
	 * Data Type: number ( integer, float )
	 *
	 * @var string
	 */
	const DATA_TYPE_NUMBER = 'number';

	/**
	 * Data Type: date
	 *
	 * @var string
	 */
	const DATA_TYPE_DATE = 'date';

	/**
	 * Data Type: HTML markup
	 *
	 * @var string
	 */
	const DATA_TYPE_HTML = 'html';

	/**
	 * Constructor
	 * 
	 * @param string $form_id
	 * @param string $settings
	 * @return void
	 */
	public function __construct( $form_id, $form_settings = '' )
	{
		$this->form_id = $form_id;
		$this->form_settings = $form_settings;
	}

	/**
	 * Form fields walker
	 * 
	 * @param array $form_fields
	 * @param arrau $values
	 * @return array|WP_Error return final values list if all fields check out or WP_Error otherwise
	 */
	public function walk_fields( &$form_fields, $values )
	{
		$errors = new WP_Error();

		// fields loop
		foreach ( $form_fields as $field_name => $field_args )
		{
			$field_value = &$values[ $field_name ];

			// check required
			if ( $field_args['required'] && empty( $field_value ) )
			{
				$errors->add( 'required', sprintf( __( '%s is required.', WP_SFB_TEXT_DOMAIN ), $field_args['label'] ) );
				continue;
			}

			// field data type based validation
			$data_type_method = 'data_type_'. sanitize_key( $field_args['data_type'] );
			if ( method_exists( $this, $data_type_method ) )
			{
				$result = $this->$data_type_method( $field_value, $field_name, $field_args );
				if ( is_wp_error( $result ) )
				{
					$errors->add( $result->get_error_code(), $result->get_error_message() );
					continue;
				}
				else
					$field_value = $result;
			}

			// field input type based validation
			$input_method = 'input_'. sanitize_key( $field_args['input'] );
			if ( method_exists( $this, $input_method ) )
			{
				$result = $this->$input_method( $field_value, $field_name, $field_args );
				if ( is_wp_error( $result ) )
				{
					$errors->add( $result->get_error_code(), $result->get_error_message() );
					continue;
				}
				else
					$field_value = $result;
			}

			// filter final value
			$field_value = apply_filters( 'wp_sfb_field_value', $field_value, $field_name, $field_args );
		}

		// check if there are errors
		if ( !empty( $errors->errors ) )
			return $errors;

		// return final values
		return apply_filters( 'wp_sfb_form_values', $values, $form_fields, $this->form_id );
	}

	/**
	 * Validate input: color picker
	 * 
	 * @param mixed $field_value
	 * @param string $field_name
	 * @param array $field_args
	 * @return mixed|WP_Error returns final value if it checks out or WP_Error otherwise
	 */
	public function input_colorpicker( $field_value, $field_name, $field_args )
	{
		$picker_options = wp_parse_args( $field_args['picker_options'], array (
				'defaultColor' => '#ff0000',
		) );

		if ( preg_match( '/^#[a-f0-9]{3}([a-f0-9]{3})?\z/i', $field_value ) !== 1 )
		{
			if ( $field_args['required'] )
				return new WP_Error( 'color-invalid', sprintf( __( '%s has invalid color value.' ), $field_args['label'] ) );
			else
				$field_value = $picker_options['defaultColor'];
		}

		return $field_value;
	}

	/**
	 * Validate input: slider
	 * 
	 * @param mixed $field_value
	 * @param string $field_name
	 * @param array $field_args
	 * @return mixed|WP_Error returns final value if it checks out or WP_Error otherwise
	 */
	public function input_slider( $field_value, $field_name, $field_args )
	{
		$slider_options = wp_parse_args( $field_args['slider_options'], array ( 
				'range' => false,
				'min' => 0,
				'max' => 100,
		) );

		if ( $slider_options['range'] && is_array( $field_value ) && isset( $field_value['min'], $field_value['max'] ) )
			$selected_range = array_map( 'floatval', $field_value );
		else
			$selected_range = array( 'min' => floatval( $field_value ), 'max' => floatval( $field_value ) );

		if ( 
			$selected_range['min'] > $selected_range['max']	||	// selected min greater than selected max
			$selected_range['min'] < $slider_options['min'] || 	// selected min less than range min
			$selected_range['min'] > $slider_options['max'] || 	// selected min greater than range max
			$selected_range['max'] < $slider_options['min'] || 	// selected max less then range min
			$selected_range['max'] > $slider_options['max']  	// selected max greater than range max
		)
			return new WP_Error( 'slider-range', sprintf( __( '%s value must be between %s and %s.' ), $field_args['label'], $slider_options['min'], $slider_options['ma'] ) );

		return $field_value;
	}

	/**
	 * Validate input: radio
	 * 
	 * @param mixed $field_value
	 * @param string $field_name
	 * @param array $field_args
	 * @return mixed|WP_Error returns final value if it checks out or WP_Error otherwise
	 */
	public function input_radio( $field_value, $field_name, $field_args )
	{
		$field_args = wp_parse_args( $field_args, array ( 
				'options' => array(),
		) );

		if ( $field_args['required'] && !isset( $field_args['options'][ $field_value ] ) )
			return new WP_Error( 'required', sprintf( __( '%s is required.' ), $field_args['label'] ) );

		return $field_value;
	}

	/**
	 * Validate input: select
	 * 
	 * @param mixed $field_value
	 * @param string $field_name
	 * @param array $field_args
	 * @return mixed|WP_Error returns final value if it checks out or WP_Error otherwise
	 */
	public function input_select( $field_value, $field_name, $field_args )
	{
		$field_args = wp_parse_args( $field_args, array ( 
				'attributes' => array(),
				'options' => array(),
		) );

		if ( isset( $field_args['attributes']['multiple'] ) )
		{
			if ( $field_args['required'] && empty( $field_value ) )
				return new WP_Error( 'required', sprintf( __( '%s is required.' ), $field_args['label'] ) );

			// options list
			$options = array_keys( $field_args['options'] );
			$diff = array_diff( $options, $field_value );

			if ( $diff === $options )
				return new WP_Error( 'invalid-selection', sprintf( __( '%s has invalid selection.' ), $field_args['label'] ) );
		}
		else
		{
			if ( $field_args['required'] && !isset( $field_args['options'][ $field_value ] ) )
				return new WP_Error( 'required', sprintf( __( '%s is required.' ), $field_args['label'] ) );
		}

		return $field_value;
	}

	/**
	 * Validate input: checkbox
	 * 
	 * @param mixed $field_value
	 * @param string $field_name
	 * @param array $field_args
	 * @return mixed|WP_Error returns final value if it checks out or WP_Error otherwise
	 */
	public function input_checkbox( $field_value, $field_name, $field_args )
	{
		$field_args = wp_parse_args( $field_args, array ( 
				'single' => false,
				'options' => array(),
		) );

		if ( $field_args['single'] )
		{
			if ( $field_args['required'] && !isset( $field_args['options'][ $field_value ] ) )
				return new WP_Error( 'required', sprintf( __( '%s is required.' ), $field_args['label'] ) );
		}
		else
		{
			if ( $field_args['required'] && empty( $field_value ) )
				return new WP_Error( 'required', sprintf( __( '%s is required.' ), $field_args['label'] ) );

			// options list
			$options = array_keys( $field_args['options'] );
			$diff = array_diff( $options, $field_value );

			if ( $diff === $options )
				return new WP_Error( 'invalid-selection', sprintf( __( '%s has invalid selection.' ), $field_args['label'] ) );
		}

		return $field_value;
	}

	/**
	 * Validate: HTML
	 * 
	 * @param mixed $field_value
	 * @param string $field_name
	 * @param array $field_args
	 * @return string|WP_Error returns final value if it checks out or WP_Error otherwise
	 */
	public function data_type_html( $field_value, $field_name, $field_args )
	{
		$editor_settings = wp_parse_args( $field_args['data_type_options'], array ( 
				'allowed_html' => array_merge( wp_kses_allowed_html(), array ( 
						'h1' => array(),
						'h3' => array(),
						'h2' => array(),
						'p' => array(),
						'ol' => array(),
						'ul' => array(),
						'li' => array(),
						'pre' => array(),
				) ),
				'allowed_protocols' => array( 'http', 'https' ),
		) );

		return wp_kses( $field_value, $editor_settings['allowed_html'], $editor_settings['allowed_protocols'] );
	}

	/**
	 * Validate: date
	 * 
	 * @param mixed $field_value
	 * @param string $field_name
	 * @param array $field_args
	 * @return string|WP_Error returns final value if it checks out or WP_Error otherwise
	 */
	public function data_type_date( $field_value, $field_name, $field_args )
	{
		$type_options = wp_parse_args( $field_args['data_type_options'], array ( 
				'format' => 'Y-m-d',
		) );

		// check date
		$datetime = DateTime::createFromFormat( $type_options['format'], $field_value );
		if ( !$datetime )
			return new WP_Error( 'date-invalid', sprintf( __( '%s is an invalid date.' ), $field_args['label'] ) );

		return $field_value;
	}

	/**
	 * Validate: number
	 * 
	 * @param mixed $field_value
	 * @param string $field_name
	 * @param array $field_args
	 * @return string|WP_Error returns final value if it checks out or WP_Error otherwise
	 */
	public function data_type_number( $field_value, $field_name, $field_args )
	{
		$type_options = wp_parse_args( $field_args['data_type_options'], array ( 
				'min' => null,
				'max' => null,
				'float' => false,
		) );

		// check number
		$field_value = filter_var( $field_value, $type_options['float'] ? FILTER_SANITIZE_NUMBER_FLOAT : FILTER_SANITIZE_NUMBER_INT );
		if ( empty( $field_value ) )
			return new WP_Error( 'number-invalid', sprintf( __( '%s is an invalid number.' ), $field_args['label'] ) );

		// min & max check based in type
		if ( $type_options['float'] )
		{
			// float
			$field_value = floatval( $field_value );

			if ( $type_options['min'] !== null && $field_value < $type_options['min'] )
				return new WP_Error( 'number-range', sprintf( __( '%s minimum is %s.' ), $field_args['label'], $type_options['min'] ) );

			if ( $type_options['max'] !== null && $field_value > $type_options['max'] )
				return new WP_Error( 'number-range', sprintf( __( '%s maximum is %s.' ), $field_args['label'], $type_options['max'] ) );
		}
		else
		{
			// integer
			$validate_options = array();

			if ( $type_options['min'] !== null && false === filter_var( $field_value, FILTER_VALIDATE_INT, array( 'options' => array( 'min_range' => $type_options['min'] ) ) ) )
				return new WP_Error( 'number-range', sprintf( __( '%s minimum is %s.' ), $field_args['label'], $type_options['min'] ) ); 

			if ( $type_options['max'] !== null && false === filter_var( $field_value, FILTER_VALIDATE_INT, array( 'options' => array( 'max_range' => $type_options['max'] ) ) ) )
				return new WP_Error( 'number-range', sprintf( __( '%s maximum is %s.' ), $field_args['label'], $type_options['max'] ) ); 
		}

		return $field_value;
	}

	/**
	 * Validate: email address
	 * 
	 * @param mixed $field_value
	 * @param string $field_name
	 * @param array $field_args
	 * @return string|WP_Error returns final value if it checks out or WP_Error otherwise
	 */
	public function data_type_email( $field_value, $field_name, $field_args )
	{
		// sanitize
		$field_value = sanitize_email( $field_value );

		if ( is_email( $field_value ) )
			return $field_value;
		else
			return new WP_Error( 'invalid-email', sprintf( __( '%s is invalid email address.', WP_SFB_TEXT_DOMAIN ), $field_args['label'] ) );
	}

	/**
	 * Validate: text
	 * 
	 * @param mixed $field_value
	 * @param string $field_name
	 * @param array $field_args
	 * @return string|WP_Error returns final value if it checks out or WP_Error otherwise
	 */
	public function data_type_text( $field_value, $field_name, $field_args )
	{
		$type_options = wp_parse_args( $field_args['data_type_options'], array ( 
				'min_length' => null,
				'max_length' => null,
				'multiline' => false,
				'regex' => null,
		) );

		// sanitize text
		if ( $type_options['multiline'] )
			$field_value = SFB_Helpers::sanitize_text_field_with_linebreaks( $field_value );
		else
			$field_value = sanitize_text_field( $field_value );

		$length = strlen( $field_value );

		// check min length
		if ( $type_options['min_length'] !== null && $length < $type_options['min_length'] )
			return new WP_Error( 'text-min-length', sprintf( __( '%s must be at least %s characters.', WP_SFB_TEXT_DOMAIN ), $field_args['label'], $type_options['min_length'] ) );

		// check max length
		if ( $type_options['max_length'] !== null && $length > $type_options['max_length'] )
			return new WP_Error( 'text-max-length', sprintf( __( '%s must have maximum %s characters.', WP_SFB_TEXT_DOMAIN ), $field_args['label'], $type_options['max_length'] ) );

		// check regex
		if ( $type_options['regex'] !== null && preg_match( $type_options['regex'], $field_value ) !== 1 )
			return new WP_Error( 'text-regex', sprintf( __( '%s is invalid format.', WP_SFB_TEXT_DOMAIN ), $field_args['label'] ) );

		return $field_value;
	}

}

