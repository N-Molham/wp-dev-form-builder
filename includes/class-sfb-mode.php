<?php
/**
 * Default Form render engine ( WordPress Dashboard )
 *
 * @package WP Form Builder
 * @since 1.0
 */

class SFB_Mode
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
	 * Form fields walk
	 * 
	 * @param array $form_sections
	 * @param array $form_fields
	 * @param arrau $values
	 * @return void
	 */
	public function walk_fields( $form_sections, $form_fields, $values )
	{
		// start form
		$this->start_form();

		if ( empty( $form_sections ) )
		{
			// no form sections

			// start wrapper
			$this->start_fields_wrapper();

			// fields loop
			foreach ( $form_fields as $field_name => $field_args )
				$this->field_layout( $field_name, $field_args );

			// end wrapper
			$this->end_fields_wrapper();
		}
		else
		{
			// form has sections
			foreach ( $form_sections as $section_name => $section_args )
			{
				// section layout
				$this->section_layout( $section_name, $section_args );

				// start wrapper
				$this->start_fields_wrapper();

				// get fields of this section only
				$section_fields = array_filter( $form_fields, function( $field ) use ( $section_name ) {
					return $section_name === $field['section'];
				} );

				// fields loop
				foreach ( $section_fields as $field_name => $field_args )
					$this->field_layout( $field_name, $field_args );

				// end wrapper
				$this->end_fields_wrapper();
			}
		}

		// end form
		$this->end_form();
	}

	/**
	 * Field layout
	 * 
	 * @param string $field_name
	 * @param array $field_args
	 * @return void
	 */
	protected function field_layout( $field_name, $field_args )
	{
		// layout start
		echo '<tr><th scope="row">';

		// title
		echo '<label for="', $field_name ,'">', $field_args['label'] ,'</label></th><td>';

		// input
		dump_data( $field_args['input'] );
		// echo '<input name="', $field_name ,'" type="text" id="', $field_name ,'" value="Test WP" class="regular-text">';

		// layout end
		echo '</td></tr>';
	}

	/**
	 * Display Section layout
	 * 
	 * @param string $section_name
	 * @param array $section_args
	 * @return void
	 */
	protected function section_layout( $section_name, $section_args )
	{
		// title/label
		echo '<h3 class="title '. esc_attr( $section_name ) .'">', $section_args['label'] ,'</h3>';

		// description
		if ( !empty( $section_args['description'] ) )
			echo '<p>', $section_args['description'] ,'</p>';
	}

	/**
	 * Fields wrapper start
	 * 
	 * @return void
	 */
	protected function start_fields_wrapper()
	{
		echo '<table class="form-table"><tbody>';
	}

	/**
	 * Fields wrapper end
	 *
	 * @return void
	 */
	protected function end_fields_wrapper()
	{
		echo '</tbody></table>';
	}

	/**
	 * Start form layout
	 * 
	 * @return string
	 */
	protected function start_form()
	{
		// form tag
		echo '<form ', SFB_Helpers::parse_attributes( $this->form_settings['attributes'] ) ,'>';
	}

	/**
	 * End form layout
	 * 
	 * @return string
	 */
	protected function end_form()
	{
		// form tag end
		echo '</form>';
	}
}


