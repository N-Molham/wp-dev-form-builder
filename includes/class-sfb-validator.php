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
	 * Data Type: array
	 *
	 * @var string
	 */
	const DATA_TYPE_ARRAY = 'array';

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
			// check required
			if ( $field_args['required'] && empty( $values[ $field_name ] ) )
			{
				$errors->add( $field_name .'-required', sprintf( __( '%s is required.', WP_SFB_TEXT_DOMAIN ), $field_args['label'] ) );
				continue;
			}

			// field data type based validation
			$data_type_method = 'data_type_'. sanitize_key( $field_args['data_type'] );
			if ( method_exists( $this, $data_type_method ) )
			{
				$result = $this->$data_type_method( $field_name, $field_args, $values[ $field_name ] );
				if ( is_wp_error( $result ) )
				{
					$errors->add( $result->get_error_code(), $result->get_error_message() );
					continue;
				}
			}

			// field input type based validation
			$input_method = 'input_'. sanitize_key( $field_args['input'] );
			if ( method_exists( $this, $input_method ) )
			{
				$result = $this->$input_method( $field_name, $field_args, $values[ $field_name ] );
				if ( is_wp_error( $result ) )
				{
					$errors->add( $result->get_error_code(), $result->get_error_message() );
					continue;
				}
			}

		}

		// check if there are errors
		if ( !empty( $errors->errors ) )
			return $errors;

		// return final values
		return $values;
	}

	/**
	 * Validate: text
	 * 
	 * @param string $field_name
	 * @param array $field_args
	 * @param mixed $field_value
	 * @return boolean|WP_Error returns true if value checks out or WP_Error otherwise
	 */
	public function data_type_text( $field_name, $field_args, $field_value )
	{
		dump_data( $field_args );
	}

}



