<?php
/**
 * Bootstrap render engine
 *
 * @see SFB_Render_Engine
 * @package WP Form Builder
 * @since 1.0
 */

class SFB_Bootstrap_Engine extends SFB_Render_Engine
{
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
		$hidden_fields = apply_filters( 'sfb_render_engine_hidden_fields', array( 'hidden', 'nonce' ), $this->form_id );

		// layout start
		echo '<div class="form-group ', ( in_array( $field_args['input'], $hidden_fields ) ? ' hidden' :'' ) ,'">';

		// title
		echo '<label for="', $field_name ,'" class="col-sm-2 control-label">', $this->field_label( $field_args['label'], $field_name, $field_args ) ,'</label>';

		// input
		echo '<div class="col-sm-10">';

		// attributes
		if ( !isset( $field_args['attributes'] ) )
			$field_args['attributes'] = array();

		// check required css class
		if ( !isset( $field_args['attributes']['class'] ) )
			$field_args['attributes']['class'] = 'form-control';

		if ( strpos( $field_args['attributes']['class'], 'form-control' ) === false )
			$field_args['attributes']['class'] .= ' form-control';

		// check input existence
		$input_method = 'input_'. sanitize_key( $field_args['input'] );
		if ( method_exists( $this, $input_method ) )
			$this->$input_method( $field_name, $field_args, $field_value );
		else
			_e( 'Unknown input', WP_SFB_TEXT_DOMAIN );

		// field description
		$this->field_description( $field_args['description'], $field_name, $field_args );

		// layout end
		echo '</div></div>';
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
		$args['editor_settings'] = wp_parse_args( $args['editor_settings'], array ( 
				'wrapper_title' => '',
		) );

		// wrapper start
		echo '<div class="panel panel-default">';

		// wrapper title
		if ( !empty( $args['editor_settings']['wrapper_title'] ) )
			echo '<div class="panel-heading">', $args['editor_settings']['wrapper_title'] ,'</div>';

		// editor input
		echo '<div class="panel-body">';
		parent::input_wysiwyg( $name, $args, $value );
		echo '</div>';

		// wrapper end
		echo '</div>';
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

		// remove form control class
		$args['attributes']['class'] = str_replace( 'form-control', '', $args['attributes']['class'] );

		// parse additional attributes
		$attrs = SFB_Helpers::parse_attributes( $args['attributes'] );

		// options loop
		foreach ( $args['options'] as $option_value => $option_label )
		{
			echo '<div class="checkbox"><label>';
			echo '<input type="checkbox" name="', $name, ( $args['single'] ? '' : '[]' ) ,'" value="', $option_value ,'"';

			if ( $args['single'] )
				echo $option_value === $value ? ' checked="checked"' : '';
			else
				echo in_array( $option_value, $value ) ? ' checked="checked"' : '';

			echo $attrs, '> ', $option_label ,'</label></div>';
		}
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

		// remove form control class
		$args['attributes']['class'] = str_replace( 'form-control', '', $args['attributes']['class'] );

		// parse additional attributes
		$attrs = SFB_Helpers::parse_attributes( $args['attributes'] );

		// options loop
		foreach ( $args['options'] as $option_value => $option_label )
		{
			echo '<div class="radio"><label>';
			echo '<input type="radio" name="', $name ,'" value="', $option_value ,'"';

			echo $option_value === $value ? ' checked="checked"' : '';

			echo $attrs, '> ', $option_label ,'</label></div>';
		}
	}

	/**
	 * Field input: color picker
	 * 
	 * @see http://bgrins.github.io/spectrum For more information
	 * 
	 * @param string $name
	 * @param array $args
	 * @param string $value
	 * @return void
	 */
	protected function input_colorpicker( $name, $args, $value )
	{
		if ( !isset( $args['picker_options'] ) )
			$args['picker_options'] = array();

		/**
		 * default color picker settings
		 * 
		 * @see http://bgrins.github.io/spectrum/#options
		 */ 
		$args['picker_options'] = wp_parse_args( $args['picker_options'], array ( 
				'polyfill' => false,
				'color' => false,
				'flat' => false,
				'showInput' => true,
				'allowEmpty' => false,
				'showButtons' => true,
				'clickoutFiresChange' => false,
				'showInitial' => true,
				'showPalette' => false,
				'showPaletteOnly' => false,
				'showSelectionPalette' => true,
				'localStorageKey' => false,
				'appendTo' => 'body',
				'maxSelectionSize' => 7,
				'cancelText' => __( 'cancel', WP_SFB_TEXT_DOMAIN ),
				'chooseText' => __( 'choose', WP_SFB_TEXT_DOMAIN ),
				'clearText' => __( 'Clear Color Selection', WP_SFB_TEXT_DOMAIN ),
				'preferredFormat' => 'rgb',
				'containerClassName' => '',
				'replacerClassName' => '',
				'showAlpha' => true,
				'theme' => 'sp-light',
				'palette' => array( array( '#ffffff', '#000000', '#ff0000', '#ff8000', '#ffff00', '#008000', '#0000ff', '#4b0082', '#9400d3' ) ),
				'selectionPalette' => array(),
				'disabled' => false
		) );

		// enqueues
		wp_enqueue_style( 'spectrum-style', WP_SFB_URI .'css/spectrum.min.css' );
		wp_enqueue_script( 'spectrum', WP_SFB_URI .'js/spectrum.min.js', array( 'jquery' ), false, true );

		// input field
		echo '<input name="', $name ,'" type="', ( $args['picker_options']['polyfill'] ? 'color' : 'text' ) ,'" id="', $name ,'" value="', esc_attr( $value ) ,'" ', SFB_Helpers::parse_attributes( $args['attributes'] ) ,' />';

		// js handler
		echo '<script>( function( window ) { ';
		echo 'jQuery( function( $ ) {';
		echo '$( "#', $name ,'" ).spectrum( ', json_encode( $args['picker_options'] ) ,' );';
		echo '} );';
		echo '} )( window );</script>';
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
		echo '<h3 id="'. esc_attr( $section_name ) .'">', $section_args['label'] ,'</h3>';

		// description
		if ( !empty( $section_args['description'] ) )
			$this->field_description( $section_args['description'], '', '' );
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
				'attributes' => array ( 
						'class' => 'btn btn-primary',
				),
		) );

		// before submit button
		echo $args['before'];

		echo '<div class="form-group"><div class="col-sm-offset-2 col-sm-10">';

		// submit button itself
		echo '<button type="submit" name="', $args['name'] ,'" id="', $args['name'] ,'" ', SFB_Helpers::parse_attributes( $args['attributes'] ) ,'>', $args['text'] ,'</button>';

		echo '</div></div>';

		// after submit button
		echo $args['after'];
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
			echo '<p class="help-block">', $description ,'</p>';
	}

	/**
	 * Fields wrapper start
	 *
	 * @return void
	 */
	protected function start_fields_wrapper()
	{
		echo '';
	}

	/**
	 * Fields wrapper end
	 *
	 * @return void
	 */
	protected function end_fields_wrapper()
	{
		echo '';
	}

	/**
	 * Start form layout
	 *
	 * @return string
	 */
	protected function start_form()
	{
		$attrs = wp_parse_args( $this->form_settings['attributes'], array ( 
				'class' => 'form-horizontal',
				'role' => 'form',
		) );

		// form tag
		echo '<form ', SFB_Helpers::parse_attributes( $attrs ) ,'>';
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





