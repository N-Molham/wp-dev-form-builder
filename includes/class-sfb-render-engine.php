<?php

/**
 * Default Form render engine ( WordPress Dashboard )
 *
 * @package WP Form Builder
 * @since 1.0
 */
class SFB_Render_Engine
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
	 * Input Type: Text
	 *
	 * @var string
	 */
	const INPUT_TYPE_TEXT = 'text';

	/**
	 * Input Type: Email Address
	 *
	 * @var string
	 */
	const INPUT_TYPE_EMAIL = 'email';

	/**
	 * Input Type: Text Area
	 *
	 * @var string
	 */
	const INPUT_TYPE_TEXTAREA = 'textarea';

	/**
	 * Input Type: Number
	 *
	 * @var string
	 */
	const INPUT_TYPE_NUMBER = 'number';

	/**
	 * Input Type: Checkbox(s)
	 *
	 * @var string
	 */
	const INPUT_TYPE_CHECKBOX = 'checkbox';

	/**
	 * Input Type: Radio Buttons
	 *
	 * @var string
	 */
	const INPUT_TYPE_RADIO = 'radio';

	/**
	 * Input Type: Select Dropdown Menu
	 *
	 * @var string
	 */
	const INPUT_TYPE_SELECT = 'select';

	/**
	 * Input Type: Hidden
	 *
	 * @var string
	 */
	const INPUT_TYPE_HIDDEN = 'hidden';

	/**
	 * Input Type: WordPress Nonce Field
	 *
	 * @var string
	 */
	const INPUT_TYPE_NONCE = 'nonce';

	/**
	 * Input Type: jQuery Slider
	 *
	 * @var string
	 */
	const INPUT_TYPE_SLIDER = 'slider';

	/**
	 * Input Type: jQuery Date Picker
	 *
	 * @var string
	 */
	const INPUT_TYPE_DATEPICKER = 'datepicker';

	/**
	 * Input Type: WordPress Color Picker
	 *
	 * @var string
	 */
	const INPUT_TYPE_COLORPICKER = 'colorpicker';

	/**
	 * Input Type: TinyMCE wysiwyg editor
	 *
	 * @var string
	 */
	const INPUT_TYPE_WYSIWYG = 'wysiwyg';

	/**
	 * Constructor
	 *
	 * @param string       $form_id
	 * @param string|array $form_settings
	 */
	public function __construct( $form_id, $form_settings = '' )
	{
		$this->form_id       = $form_id;
		$this->form_settings = $form_settings;
	}

	/**
	 * Form fields walker
	 *
	 * @param array $form_sections
	 * @param array $form_fields
	 * @param array $values
	 *
	 * @return void
	 */
	public function walk_fields( &$form_sections, &$form_fields, $values )
	{
		// display messages
		$this->form_messages();

		// start form
		$this->start_form();

		if ( empty( $form_sections ) )
		{
			// no form sections

			// start wrapper
			$this->start_fields_wrapper();

			// fields loop
			foreach ( $form_fields as $field_name => $field_args )
			{
				$this->field_layout( $field_name, $field_args, $values[ $field_name ] );
			}

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
				$section_fields = array_filter( $form_fields, function ( $field ) use ( $section_name ) {
					return $section_name === $field['section'];
				} );

				// fields loop
				foreach ( $section_fields as $field_name => $field_args )
				{
					$this->field_layout( $field_name, $field_args, $values[ $field_name ] );
				}

				// end wrapper
				$this->end_fields_wrapper();
			}
		}

		$this->submit_button( $this->form_settings['submit'] );

		// end form
		$this->end_form();
	}

	/**
	 * Display messages if any
	 *
	 * @return void
	 */
	public function form_messages()
	{
		// error messages
		if ( isset( $_SESSION['sfb_errors'] ) && is_wp_error( $_SESSION['sfb_errors'] ) && !empty( $_SESSION['sfb_errors']->errors ) )
		{
			/* @var $form_errors WP_Error */
			$form_errors = &$_SESSION['sfb_errors'];

			foreach ( $form_errors->get_error_messages() as $error )
			{
				$this->display_message( $error, 'error' );
			}

			// clear errors
			$form_errors->errors = [];
		}

		// success message
		if ( isset( $_GET['success'] ) && 'yes' === $_GET['success'] )
		{
			$this->display_message( $this->form_settings['submit_success'] );
		}

		// trigger messages action hook
		do_action( 'sfb_form_messages' );
	}

	/**
	 * Display a message
	 *
	 * @param string $message
	 * @param string $type
	 *
	 * @return void
	 */
	public function display_message( $message, $type = 'success' )
	{
		switch ( $type )
		{
			case 'success':
				$type = 'updated';
				break;
		}

		echo '<div class="', $type, '"><p>', $message, '</p></div>';
	}

	/**
	 * Field layout
	 *
	 * @param string $field_name
	 * @param array  $field_args
	 * @param mixed  $field_value
	 *
	 * @return void
	 */
	public function field_layout( $field_name, $field_args, $field_value )
	{
		/**
		 * Filter hidden field types
		 *
		 * @param array  $hidden_fields
		 * @param string $form_id
		 *
		 * @return array
		 */
		$hidden_fields = apply_filters( 'sfb_render_engine_hidden_fields', [ 'hidden', 'nonce' ], $this->form_id );

		// layout start
		echo '<tr', ( in_array( $field_args['input'], $hidden_fields, true ) ? ' class="hidden"' : '' ), '>';

		// title
		echo '<th scope="row">';
		$this->field_label( $field_args['label'], $field_name, $field_args );
		echo '</th>';

		// input
		echo '<td>';

		// check input existence
		$input_method = 'input_' . sanitize_key( $field_args['input'] );
		if ( method_exists( $this, $input_method ) )
		{
			$this->$input_method( $field_name, $field_args, $field_value );
		}
		else
		{
			_e( 'Unknown input', WP_SFB_TEXT_DOMAIN );
		}

		// field description
		$this->field_description( $field_args['description'], $field_name, $field_args );

		// layout end
		echo '</td></tr>';
	}

	/**
	 * Field input: text
	 *
	 * @param string $name
	 * @param array  $args
	 * @param string $value
	 *
	 * @return void
	 */
	public function input_text( $name, $args, $value )
	{
		// default attributes
		$attrs = wp_parse_args( $args['attributes'], [
			'class' => 'regular-text',
		] );

		// input layout
		echo '<input name="', $name, '" type="text" id="', $name, '" value="', esc_attr( $value ), '" ', SFB_Helpers::parse_attributes( $attrs ), ' />';
	}

	/**
	 * Field input: email
	 *
	 * @param string $name
	 * @param array  $args
	 * @param string $value
	 *
	 * @return void
	 */
	public function input_email( $name, $args, $value )
	{
		// default attributes
		$attrs = wp_parse_args( $args['attributes'], [
			'class' => 'regular-text',
		] );

		// input layout
		echo '<input name="', $name, '" type="email" id="', $name, '" value="', esc_attr( $value ), '" ', SFB_Helpers::parse_attributes( $attrs ), ' />';
	}

	/**
	 * Field input: number
	 *
	 * @param string $name
	 * @param array  $args
	 * @param string $value
	 *
	 * @return void
	 */
	public function input_number( $name, $args, $value )
	{
		// default attributes
		$attrs = wp_parse_args( $args['attributes'], [
			'step'  => '1',
			'class' => 'small-text',
		] );

		// input layout
		echo '<input name="', $name, '" type="number" id="', $name, '" value="', esc_attr( $value ), '" ', SFB_Helpers::parse_attributes( $attrs ), ' />';
	}

	/**
	 * Field input: textarea
	 *
	 * @param string $name
	 * @param array  $args
	 * @param string $value
	 *
	 * @return void
	 */
	public function input_textarea( $name, $args, $value )
	{
		// default attributes
		$attrs = wp_parse_args( $args['attributes'], [
			'cols'  => '24',
			'rows'  => '8',
			'class' => 'large-text',
		] );

		// input layout
		echo '<textarea name="', $name, '" id="', $name, '" ', SFB_Helpers::parse_attributes( $attrs ), '>', $value, '</textarea>';
	}

	/**
	 * Field input: checkbox
	 *
	 * @param string $name
	 * @param array  $args
	 * @param string $value
	 *
	 * @return void
	 */
	public function input_checkbox( $name, $args, $value )
	{
		// default arguments
		$args = wp_parse_args( $args, [
			'options' => [],
			'single'  => false,
		] );

		if ( empty( $value ) && false === $args['single'] )
		{
			$value = [];
		}

		// input layout
		echo '<fieldset><legend class="screen-reader-text"><span>', $args['label'], '</span></legend>';

		$attrs = SFB_Helpers::parse_attributes( $args['attributes'] );

		// options loop
		foreach ( $args['options'] as $option_value => $option_label )
		{
			echo '<label><input type="checkbox" name="', $name, ( $args['single'] ? '' : '[]' ), '" value="', $option_value, '"';

			if ( $args['single'] )
			{
				echo $option_value === $value ? ' checked="checked"' : '';
			}
			else
			{
				echo in_array( $option_value, $value, true ) ? ' checked="checked"' : '';
			}

			echo $attrs, '> <span>', $option_label, '</span></label><br/>';
		}

		echo '</fieldset>';
	}

	/**
	 * Field input: radio
	 *
	 * @param string $name
	 * @param array  $args
	 * @param string $value
	 *
	 * @return void
	 */
	public function input_radio( $name, $args, $value )
	{
		// default arguments
		$args = wp_parse_args( $args, [
			'options'    => [],
			'attributes' => [],
		] );

		$attrs = SFB_Helpers::parse_attributes( $args['attributes'] );

		// input layout
		echo '<fieldset><legend class="screen-reader-text"><span>', $args['label'], '</span></legend>';

		// options loop
		foreach ( $args['options'] as $option_value => $option_label )
		{
			echo '<label><input type="radio" name="', $name, '" value="', $option_value, '"';

			echo $option_value === $value ? ' checked="checked"' : '';

			echo $attrs, '> <span>', $option_label, '</span></label><br/>';
		}

		echo '</fieldset>';
	}

	/**
	 * Field input: select
	 *
	 * @param string $name
	 * @param array  $args
	 * @param string $value
	 *
	 * @return void
	 */
	public function input_select( $name, $args, $value )
	{
		// default arguments
		$args = wp_parse_args( $args, [
			'options'    => [],
			'attributes' => [],
		] );

		$is_multiple = isset( $args['attributes']['multiple'] );
		if ( $is_multiple && !is_array( $value ) )
		{
			$value = [];
		}

		// input layout
		echo '<select name="', $name, ( $is_multiple ? '[]' : '' ), '" id="', $name, '" ', SFB_Helpers::parse_attributes( $args['attributes'] ), '>';

		// options loop
		foreach ( $args['options'] as $option_value => $option_label )
		{
			echo '<option value="', $option_value, '"';

			if ( $is_multiple )
			{
				echo in_array( $option_value, $value, true ) ? ' selected' : '';
			}
			else
			{
				echo $option_value === $value ? ' selected' : '';
			}

			echo '>', $option_label, '</option>';
		}

		echo '</select>';
	}

	/**
	 * Field input: hidden
	 *
	 * @param string $name
	 * @param array  $args
	 * @param string $value
	 *
	 * @return void
	 */
	public function input_hidden( $name, $args, $value )
	{
		// default arguments
		$args = wp_parse_args( $args, [
			'value' => '',
		] );

		// input value
		$value = empty( $value ) ? $args['value'] : $value;

		echo '<input name="', $name, '" type="hidden" id="', $name, '" value="', esc_attr( $value ), '" ', SFB_Helpers::parse_attributes( $args['attributes'] ), ' />';
	}

	/**
	 * Field input: WordPress Nonce Field
	 *
	 * @param string $name
	 * @param array  $args
	 *
	 * @return void
	 */
	public function input_nonce( $name, $args )
	{
		// default arguments
		$args = wp_parse_args( $args, [
			'action'  => '',
			'referer' => true,
		] );

		wp_nonce_field( $args['action'], $name, $args['referer'] );
	}

	/**
	 * Field input: TinyMCE wysiwyg editor
	 *
	 * Editor id ( field name ) must be lowercase characters only, As of 3.6.1 you can use underscores in the ID
	 *
	 * @see http://codex.wordpress.org/Function_Reference/wp_editor#Notes
	 *
	 * @param string $name
	 * @param array  $args
	 * @param string $value
	 *
	 * @return void
	 */
	public function input_wysiwyg( $name, $args, $value )
	{
		// default arguments
		$args = wp_parse_args( $args, [
			'editor_settings' => [],
		] );

		if ( !class_exists( '_WP_Editors' ) )
		{
			require ABSPATH . WPINC . '/class-wp-editor.php';
		}

		wp_editor( $value, $name, $args['editor_settings'] );
	}

	/**
	 * Field input: color picker
	 *
	 * @param string $name
	 * @param array  $args
	 * @param string $value
	 *
	 * @return void
	 */
	public function input_colorpicker( $name, $args, $value )
	{
		if ( !isset( $args['picker_options'] ) )
		{
			$args['picker_options'] = [];
		}

		// default color picker settings
		$args['picker_options'] = wp_parse_args( $args['picker_options'], [
			'defaultColor' => false,
			'change'       => false,
			'clear'        => false,
			'hide'         => true,
			'palettes'     => true,
		] );

		if ( $args['picker_options']['defaultColor'] )
		{
			// add default color
			$args['attributes']['data-default-color'] = $args['picker_options']['defaultColor'];

			if ( empty( $value ) )
			{
				$value = $args['picker_options']['defaultColor'];
			}
		}

		// enqueues
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'wp-color-picker' );

		// input field
		echo '<input name="', $name, '" type="text" id="', $name, '" value="', esc_attr( $value ), '" ', SFB_Helpers::parse_attributes( $args['attributes'] ), ' />';

		// js handler
		echo '<script>( function( window ) { ',
		'jQuery( function( $ ) {',
		'$( "#', $name, '" ).wpColorPicker( ', json_encode( $args['picker_options'] ), ' );',
		'} ); } )( window );</script>';
	}

	/**
	 * Field input: date picker
	 *
	 * @see http://api.jqueryui.com/datepicker/ for detailed datepicker options
	 *
	 * @param string $name
	 * @param array  $args
	 * @param string $value
	 *
	 * @return void
	 */
	public function input_datepicker( $name, $args, $value )
	{
		// default date picker settings
		$args = wp_parse_args( $args, [
			'picker_options' => [],
		] );

		/**
		 * Filter jQuery UI style CSS
		 *
		 * @param string $jquery_ui_css_url
		 *
		 * @return string
		 */
		wp_enqueue_style( 'sfb-jquery-ui', apply_filters( 'sfb_jquery_ui_css', '//ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/themes/smoothness/jquery-ui.css' ) );
		wp_enqueue_script( 'jquery-ui-datepicker' );

		// input field
		echo '<input name="', $name, '" type="text" id="', $name, '" value="', esc_attr( $value ), '" ', SFB_Helpers::parse_attributes( $args['attributes'] ), ' />';

		// js handler
		echo '<script>( function( window ) { ',
		'jQuery( function( $ ) {',
		'$( "#', $name, '" ).datepicker( ', json_encode( $args['picker_options'] ), ' );',
		'} );',
		'} )( window );</script>';
	}

	/**
	 * Field input: slider
	 *
	 * @see http://api.jqueryui.com/slider/ for detailed slider options
	 *
	 * @param string       $name
	 * @param array        $args
	 * @param string|array $value
	 *
	 * @return void
	 */
	public function input_slider( $name, $args, $value )
	{
		if ( !isset( $args['slider_options'] ) )
		{
			$args['slider_options'] = [];
		}

		// default date picker settings
		$args['slider_options'] = wp_parse_args( $args['slider_options'], [
			'range'  => false,
			'min'    => 0,
			'max'    => 100,
			'value'  => 0,
			'values' => null,
		] );

		/**
		 * Filter jQuery UI style CSS
		 *
		 * @param string $jquery_ui_css_url
		 *
		 * @return string
		 */
		wp_enqueue_style( 'sfb-jquery-ui', apply_filters( 'sfb_jquery_ui_css', '//ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/themes/smoothness/jquery-ui.css' ) );
		wp_enqueue_script( 'jquery-ui-slider' );

		// input field
		if ( $args['slider_options']['range'] && !empty( $args['slider_options']['values'] ) )
		{
			// range slider
			if ( is_array( $value ) )
			{
				// get from values
				$args['slider_options']['values'][0] = isset( $value['min'] ) ? $value['min'] : $args['slider_options']['values'][0];
				$args['slider_options']['values'][1] = isset( $value['max'] ) ? $value['max'] : $args['slider_options']['values'][1];
			}

			echo '<input type="hidden" name="', $name, '[min]" id="', $name, '-min" value="', esc_attr( $args['slider_options']['values'][0] ), '" />';
			echo '<input type="hidden" name="', $name, '[max]" id="', $name, '-max" value="', esc_attr( $args['slider_options']['values'][1] ), '" />';
		}
		else
		{
			// single value
			$args['slider_options']['value'] = $value;
			echo '<input type="hidden" name="', $name, '" id="', $name, '" value="', esc_attr( $value ), '" />';
		}

		// slider holder
		echo '<div id="', $name, '-slider"></div>';

		// js handler
		echo '<script>( function( window ) { ',
		'jQuery( function( $ ) {',
		'var options = ', json_encode( $args['slider_options'] ), ';',
		'options.slide = function( e, ui ) { ',
		'if ( typeof ui.values === "undefined" ) { ',
		'$( "#', $name, '" ).val( ui.value );',
		'} else {',
		'$( "#', $name, '-min" ).val( ui.values[0] );',
		'$( "#', $name, '-max" ).val( ui.values[1] );',
		'}',
		'};',
		'$( "#', $name, '-slider" ).slider( options );',
		'} );',
		'} )( window );</script>';
	}

	/**
	 * Field description
	 *
	 * @param string $description
	 * @param string $field_name
	 * @param array  $field_args
	 *
	 * @return void
	 */
	public function field_description( $description, $field_name, $field_args )
	{
		if ( !empty( $description ) )
		{
			echo '<p class="description">', $description, '</p>';
		}
	}

	/**
	 * Field label
	 *
	 * @param string $label
	 * @param string $field_name
	 * @param array  $field_args
	 *
	 * @return void
	 */
	public function field_label( $label, $field_name, $field_args )
	{
		echo '<label for="', $field_name, '">', $label, '</label>';
		echo $field_args['required'] ? '<p class="description">( ' . __( 'Required', WP_SFB_TEXT_DOMAIN ) . ' )</p>' : '';
	}

	/**
	 * Form submit button
	 *
	 * @param array $args
	 *
	 * @return void
	 */
	public function submit_button( $args )
	{
		$args = wp_parse_args( $args, [
			'type'       => 'primary',
			'wrap'       => true,
			'attributes' => null,
		] );

		// before submit button
		echo $args['before'];

		// submit button itself
		submit_button( $args['text'], $args['type'], $args['name'], $args['wrap'], $args['attributes'] );

		// after submit button
		echo $args['after'];
	}

	/**
	 * Display Section layout
	 *
	 * @param string $section_name
	 * @param array  $section_args
	 *
	 * @return void
	 */
	public function section_layout( $section_name, $section_args )
	{
		// title/label
		echo '<h3 class="title ' . esc_attr( $section_name ) . '">', $section_args['label'], '</h3>';

		// description
		if ( !empty( $section_args['description'] ) )
		{
			$this->field_description( $section_args['description'], '', [] );
		}
	}

	/**
	 * Fields wrapper start
	 *
	 * @return void
	 */
	public function start_fields_wrapper()
	{
		echo '<table class="form-table"><tbody>';
	}

	/**
	 * Fields wrapper end
	 *
	 * @return void
	 */
	public function end_fields_wrapper()
	{
		echo '</tbody></table>';
	}

	/**
	 * Start form layout
	 *
	 * @return void
	 */
	public function start_form()
	{
		// form tag
		echo '<form ', SFB_Helpers::parse_attributes( $this->form_settings['attributes'] ), '>';
	}

	/**
	 * End form layout
	 *
	 * @return void
	 */
	public function end_form()
	{
		// form tag end
		echo '</form>';
	}
}