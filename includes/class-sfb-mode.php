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
				$this->field_layout( $field_name, $field_args, $values[$field_name] );

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
					$this->field_layout( $field_name, $field_args, $values[$field_name] );

				// end wrapper
				$this->end_fields_wrapper();
			}
		}

		$this->submit_button( $this->form_settings['submit'] );

		// end form
		$this->end_form();
	}

	/**
	 * Field layout
	 * 
	 * @param string $field_name
	 * @param array $field_args
	 * @param mixed $field_value
	 * @return void
	 */
	protected function field_layout( $field_name, $field_args, $field_value )
	{
		$hidden_fields = apply_filters( 'sfb_mode_hidden_fields', array( 'hidden', 'nonce' ), $this->form_id );

		// layout start
		echo '<tr', ( in_array( $field_args['input'], $hidden_fields ) ? ' class="hidden"' :'' ) ,'><th scope="row">';

		// title
		echo '<label for="', $field_name ,'">', $field_args['label'] ,'</label></th><td>';

		// input
		// check input existance
		$input_method = 'input_'. sanitize_key( $field_args['input'] );
		if ( method_exists( $this, $input_method ) )
			$this->$input_method( $field_name, $field_args, $field_value );
		else
			echo 'Unknown input';

		// field description
		$this->field_description( $field_args['description'], $field_name, $field_args );

		// layout end
		echo '</td></tr>';
	}

	/**
	 * Field input: text
	 * 
	 * @param string $name
	 * @param array $args
	 * @param string $value
	 * @return void
	 */
	protected function input_text( $name, $args, $value )
	{
		// default attributes
		$attrs = wp_parse_args( $args['attributes'], array ( 
				'class' => 'regular-text',
		) );

		// input layout
		echo '<input name="', $name ,'" type="text" id="', $name ,'" value="', esc_attr( $value ) ,'" ', SFB_Helpers::parse_attributes( $attrs ) ,' />';
	}

	/**
	 * Field input: email
	 * 
	 * @param string $name
	 * @param array $args
	 * @param string $value
	 * @return void
	 */
	protected function input_email( $name, $args, $value )
	{
		// default attributes
		$attrs = wp_parse_args( $args['attributes'], array ( 
				'class' => 'regular-text',
		) );

		// input layout
		echo '<input name="', $name ,'" type="email" id="', $name ,'" value="', esc_attr( $value ) ,'" ', SFB_Helpers::parse_attributes( $attrs ) ,' />';
	}

	/**
	 * Field input: number
	 * 
	 * @param string $name
	 * @param array $args
	 * @param string $value
	 * @return void
	 */
	protected function input_number( $name, $args, $value )
	{
		// default attributes
		$attrs = wp_parse_args( $args['attributes'], array ( 
				'step' => '1',
				'class' => 'small-text',
		) );

		// input layout
		echo '<input name="', $name ,'" type="number" id="', $name ,'" value="', esc_attr( $value ) ,'" ', SFB_Helpers::parse_attributes( $attrs ) ,' />';
	}

	/**
	 * Field input: textarea
	 * 
	 * @param string $name
	 * @param array $args
	 * @param string $value
	 * @return void
	 */
	protected function input_textarea( $name, $args, $value )
	{
		// default attributes
		$attrs = wp_parse_args( $args['attributes'], array ( 
				'cols' => '24',
				'rows' => '8',
				'class' => 'large-text',
		) );

		// input layout
		echo '<textarea name="', $name ,'" id="', $name ,'" ', SFB_Helpers::parse_attributes( $attrs ) ,'>', $value ,'</textarea>';
	}

	/**
	 * Field input: checkbox
	 * 
	 * @param string $name
	 * @param array $args
	 * @param string $value
	 * @return void
	 */
	protected function input_checkbox( $name, $args, $value )
	{
		// default arguments
		$args = wp_parse_args( $args, array ( 
				'options' => array(),
				'single' => false, 
		) );

		if ( !$args['single'] && empty( $value ) )
			$value = array();

		// input layout
		echo '<fieldset><legend class="screen-reader-text"><span>', $args['label'] ,'</span></legend>';

		$attrs = SFB_Helpers::parse_attributes( $args['attributes'] );

		// options loop
		foreach ( $args['options'] as $option_value => $option_label )
		{
			echo '<label><input type="checkbox" name="', $name, ( $args['single'] ? '' : '[]' ) ,'" value="', $option_value ,'"';

			if ( $args['single'] )
				echo $option_value === $value ? ' checked="checked"' : '';
			else 
				echo in_array( $option_value, $value ) ? ' checked="checked"' : '';

			echo $attrs, '> <span>', $option_label ,'</span></label><br/>';
		}

		echo '</fieldset>';
	}

	/**
	 * Field input: radio
	 * 
	 * @param string $name
	 * @param array $args
	 * @param string $value
	 * @return void
	 */
	protected function input_radio( $name, $args, $value )
	{
		// default arguments
		$args = wp_parse_args( $args, array ( 
				'options' => array(),
				'attributes' => array(), 
		) );

		$attrs = SFB_Helpers::parse_attributes( $args['attributes'] );

		// input layout
		echo '<fieldset><legend class="screen-reader-text"><span>', $args['label'] ,'</span></legend>';

		// options loop
		foreach ( $args['options'] as $option_value => $option_label )
		{
			echo '<label><input type="radio" name="', $name ,'" value="', $option_value ,'"';

			echo $option_value === $value ? ' checked="checked"' : '';

			echo $attrs, '> <span>', $option_label ,'</span></label><br/>';
		}

		echo '</fieldset>';
	}

	/**
	 * Field input: select
	 * 
	 * @param string $name
	 * @param array $args
	 * @param string $value
	 * @return void
	 */
	protected function input_select( $name, $args, $value )
	{
		// default arguments
		$args = wp_parse_args( $args, array ( 
				'options' => array(),
				'attributes' => array(), 
		) );

		$is_single = !is_array( $value );

		// input layout
		echo '<select name="', $name ,'" id="', $name ,'" ', SFB_Helpers::parse_attributes( $args['attributes'] ) ,'>';

		// options loop
		foreach ( $args['options'] as $option_value => $option_label )
		{
			echo '<option value="', $option_value ,'"';

			if ( $is_single )
				echo $option_value === $value ? ' selected' : '';
			else
				echo in_array( $option_value, $value ) ? ' selected' : '';

			echo '>', $option_label ,'</option>';
		}

		echo '</select>';
	}

	/**
	 * Field input: hidden
	 * 
	 * @param string $name
	 * @param array $args
	 * @param string $value
	 * @return void
	 */
	protected function input_hidden( $name, $args, $value )
	{
		// default arguments
		$args = wp_parse_args( $args, array ( 
				'value' => '', 
		) );

		// input value
		$value = empty( $value ) ? $args['value'] : $value;

		echo '<input name="', $name ,'" type="hidden" id="', $name ,'" value="', esc_attr( $value ) ,'" ', SFB_Helpers::parse_attributes( $args['attributes'] ) ,' />';
	}

	/**
	 * Field input: nonce
	 * 
	 * @param string $name
	 * @param array $args
	 * @param string $value
	 * @return void
	 */
	protected function input_nonce( $name, $args )
	{
		// default arguments
		$args = wp_parse_args( $args, array ( 
				'action' => '', 
				'referer' => true, 
		) );

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
	 * @param array $args
	 * @param string $value
	 * @return void
	 */
	protected function input_wysiwyg( $name, $args, $value )
	{
		// default arguments
		$args = wp_parse_args( $args, array ( 
				'editor_settings' => array(), 
		) );

		if ( ! class_exists( '_WP_Editors' ) )
			require( ABSPATH . WPINC . '/class-wp-editor.php' );

		wp_editor( $value, $name, $args['editor_settings'] );
	}

	/**
	 * Field input: color picker
	 * 
	 * @param string $name
	 * @param array $args
	 * @param string $value
	 * @return void
	 */
	protected function input_color( $name, $args, $value )
	{
		if ( !isset( $args['picker_options'] ) )
			$args['picker_options'] = array();

		// default color picker settings
		$args['picker_options'] = wp_parse_args( $args['picker_options'], array ( 
				'defaultColor' => false,
				'change' => false, 
				'clear' => false, 
				'hide' => true, 
				'palettes' => true,
		) );

		if ( $args['picker_options']['defaultColor'] )
		{
			// add default color
			$args['attributes']['data-default-color'] = $args['picker_options']['defaultColor'];

			if ( empty( $value ) )
				$value = $args['picker_options']['defaultColor'];
		}

		// enqueues
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'wp-color-picker' );

		// input field
		echo '<input name="', $name ,'" type="text" id="', $name ,'" value="', esc_attr( $value ) ,'" ', SFB_Helpers::parse_attributes( $args['attributes'] ) ,' />';

		// js handler
		echo '<script>( function( window ) { jQuery( function( $ ) {';
		echo '$( "#', $name ,'" ).wpColorPicker( ', json_encode( $args['picker_options'] ) ,' );';
		echo '} ); } )( window );</script>';
	}

	/**
	 * Field input: date picker
	 * 
	 * @see http://api.jqueryui.com/datepicker/ for detailed datepicker options
	 * 
	 * @param string $name
	 * @param array $args
	 * @param string $value
	 * @return void
	 */
	protected function input_datepicker( $name, $args, $value )
	{
		// default date picker settings
		$args = wp_parse_args( $args, array ( 
				'picker_options' => array(),
		) );

		// enqueues
		wp_enqueue_style( 'sfb-jquery-ui-datepicker', apply_filters( 'sfb_jquery_ui_datepicker_css', '//ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/themes/smoothness/jquery-ui.css' ) );
		wp_enqueue_script( 'jquery-ui-datepicker' );

		// input field
		echo '<input name="', $name ,'" type="text" id="', $name ,'" value="', esc_attr( $value ) ,'" ', SFB_Helpers::parse_attributes( $args['attributes'] ) ,' />';

		// js handler
		echo '<script>( function( window ) { jQuery( function( $ ) {';
		echo '$( "#', $name ,'" ).datepicker( ', json_encode( $args['picker_options'] ) ,' );';
		echo '} ); } )( window );</script>';
	}

	/**
	 * Field input: slider
	 * 
	 * @see http://api.jqueryui.com/slider/ for detailed slider options
	 * 
	 * @param string $name
	 * @param array $args
	 * @param string $value
	 * @return void
	 */
	protected function input_slider( $name, $args, $value )
	{
		if ( !isset( $args['slider_options'] ) )
			$args['slider_options'] = array();

		// default date picker settings
		$args['slider_options'] = wp_parse_args( $args['slider_options'], array ( 
				'range' => false,
				'min' => 0,
				'max' => 100,
				'value' => 0,
				'values' => null,
		) );

		// enqueues
		wp_enqueue_style( 'sfb-jquery-ui-datepicker', apply_filters( 'sfb_jquery_ui_datepicker_css', '//ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/themes/smoothness/jquery-ui.css' ) );
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

			echo '<input type="hidden" name="', $name ,'[min]" id="', $name ,'-min" value="', esc_attr( $args['slider_options']['values'][0] ) ,'" />';
			echo '<input type="hidden" name="', $name ,'[max]" id="', $name ,'-max" value="', esc_attr( $args['slider_options']['values'][1] ) ,'" />';
		}
		else
		{
			// single value
			$args['slider_options']['value'] = $value;
			echo '<input type="hidden" name="', $name ,'" id="', $name ,'" value="', esc_attr( $value ) ,'" />';
		}

		// slider holder
		echo '<div id="', $name ,'-slider"></div>';

		// js handler
		echo '<script>( function( window ) { jQuery( function( $ ) {';
		echo 'var options = ', json_encode( $args['slider_options'] ) ,';';
		echo 'options.slide = function( e, ui ) { ';
		echo 'if ( typeof ui.values === "undefined" ) { ';
		echo '$( "#', $name ,'" ).val( ui.value );';
		echo ' } else { ';
		echo '$( "#', $name ,'-min" ).val( ui.values[0] );';
		echo '$( "#', $name ,'-max" ).val( ui.values[1] );';
		echo ' } };';
		echo '$( "#', $name ,'-slider" ).slider( options );';
		echo '} ); } )( window );</script>';
	}

	/**
	 * Field description
	 * 
	 * @param string $description
	 * @param string $field_name
	 * @param array $field_args
	 * @return void
	 */
	protected function field_description( $description, $field_name, $field_args )
	{
		if ( !empty( $description ) )
			echo '<p class="description">', $description ,'</p>';
	}

	/**
	 * Form submit button
	 * 
	 * @param array $args
	 * @return void
	 */
	protected function submit_button( $args )
	{
		$args = wp_parse_args( $args, array ( 
				'type' => 'primary',
				'wrap' => true,
				'other_attributes' => null,
		) );

		// before submit button
		echo $args['before'];

		// submit button itself
		submit_button( $args['text'], $args['type'], $args['name'], $args['wrap'], $args['other_attributes'] );

		// after submit button
		echo $args['after'];
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


