<?php
/**
 * Test Mode Stuff
 *
 * @package WP Form Builder
 * @since 1.1
 */

class SFB_Test_Mode 
{
	public function __construct()
	{
		// setup forms
		add_action( 'init', array( &$this, 'init' ) );

		// add admin menu item
		add_action( 'admin_menu', array( &$this, 'admin_menu' ) );

		// add shortcode
		add_shortcode( 'sfb-bootstrap-form', array( &$this, 'shortcode_output' ) );
	}

	/**
	 * TEST: WP Admin Menu
	 *
	 * @return void
	 */
	public function admin_menu()
	{
		add_management_page( 'Super Form Builder Demo', 'Super Form Builder Demo', 'manage_options', 'wp-sfb-test', array( &$this, 'admin_test_page_callback' ) );
	}
	
	/**
	 * TEST: WP Admin init
	 *
	 * @return void
	 */
	public function init()
	{
		// create form
		$form = new SFB_Form( 'demo' );

		// set settings
		$form->set_settings( array (
				'option_key' => 'wp-sfb-test',
		) );

		$form->set_sections( array (
				'basic' => array (
						'label' => 'Basic Inputs',
				),
				'advanced' => array (
						'label' => 'Advanced Inputs',
						'description' => 'Advanced Component elements',
				),
		) );

		// set form fields
		$sample_fields = array (
				'field-text' => array (
						'label' => 'Text',
						'input' => SFB_Render_Engine::INPUT_TYPE_TEXT,
						'data_type' => SFB_Validator::DATA_TYPE_TEXT,
						'data_type_options' => array (
								'min_length' => 2,
								'max_length' => 240,
						),
						'description' => 'Regular text input',
						'section' => 'basic',
						'required' => true,
				),
				'field-mail' => array (
						'label' => 'Email',
						'input' => SFB_Render_Engine::INPUT_TYPE_EMAIL,
						'data_type' => SFB_Validator::DATA_TYPE_EMAIL,
						'description' => 'Email input',
						'section' => 'basic',
						'attributes' => array (
								'placeholder' => 'Enter Your Email Address',
								'class' => 'regular-text code',
						),
						'required' => true,
				),
				'field-textarea' => array (
						'label' => 'Textarea',
						'input' => SFB_Render_Engine::INPUT_TYPE_TEXTAREA,
						'data_type' => SFB_Validator::DATA_TYPE_TEXT,
						'data_type_options' => array (
								'multiline' => true,
						),
						'section' => 'basic',
						'section' => 'basic',
						'attributes' => array (
								'rows' => '6',
								'class' => 'large-text',
						),
				),
				'field-number' => array (
						'label' => 'Number',
						'input' => SFB_Render_Engine::INPUT_TYPE_NUMBER,
						'data_type' => SFB_Validator::DATA_TYPE_NUMBER,
						'data_type_options' => array (
								'min' => 0,
								'max' => 50,
						),
						'section' => 'basic',
						'attributes' => array (
								'step' => '10',
								'max' => '50',
								'min' => '0',
								'class' => 'small-text',
						),
				),
				'field-regex' => array (
						'label' => 'Regular Expression',
						'input' => SFB_Render_Engine::INPUT_TYPE_TEXT,
						'data_type' => SFB_Validator::DATA_TYPE_TEXT,
						'data_type_options' => array (
								'regex' => '/^[a-z]{3}-\d{3,4}$/i',
						),
						'section' => 'basic',
						'attributes' => array (
								'class' => 'regular-text code',
						),
				),
				'field-checkbox' => array (
						'label' => 'Single Checkbox',
						'input' => SFB_Render_Engine::INPUT_TYPE_CHECKBOX,
						'single' => true,
						'options' => array (
								'yes' => 'Yes',
						),
						'description' => 'set <code>single</code> parameter to <code>true</code> for a single option',
						'section' => 'basic',
				),
				'field-checkboxes' => array (
						'label' => 'Multiple Checkboxes',
						'input' => SFB_Render_Engine::INPUT_TYPE_CHECKBOX,
						'single' => false,
						'options' => array (
								'one' => 'Option One',
								'two' => 'Option Two',
								'three' => 'Option Three',
								'four' => 'Option Four',
						),
						'description' => 'set <code>single</code> parameter to <code>false</code> for a multiple options',
						'section' => 'basic',
				),
				'field-radio' => array (
						'label' => 'Radio',
						'input' => SFB_Render_Engine::INPUT_TYPE_RADIO,
						'options' => array (
								'one' => 'Option One',
								'two' => 'Option Two',
								'three' => 'Option Three',
								'four' => 'Option Four',
						),
						'section' => 'basic',
				),
				'field-select' => array (
						'label' => 'Dropdown Menu',
						'input' => SFB_Render_Engine::INPUT_TYPE_SELECT,
						'options' => array (
								'one' => 'Option One',
								'two' => 'Option Two',
								'three' => 'Option Three',
								'four' => 'Option Four',
						),
						'section' => 'basic',
				),
				'field-select-multi' => array (
						'label' => 'Dropdown Menu Multiple',
						'input' => SFB_Render_Engine::INPUT_TYPE_SELECT,
						'options' => array (
								'one' => 'Option One',
								'two' => 'Option Two',
								'three' => 'Option Three',
								'four' => 'Option Four',
								'five' => 'Option Five',
								'sex' => 'Option Sex',
						),
						'attributes' => array (
								'multiple' => 'multiple',
								'size' => '3',
						),
						'section' => 'basic',
				),
				'field-hidden' => array (
						'input' => SFB_Render_Engine::INPUT_TYPE_HIDDEN,
						'value' => 'hidden_value',
						'section' => 'basic',
				),
				'field-nonce' => array (
						'input' => SFB_Render_Engine::INPUT_TYPE_NONCE,
						'action' => 'sfb_save_form',
						'referer' => true,
						'section' => 'basic',
				),
				'field-slider' => array (
						'label' => 'Slider',
						'input' => SFB_Render_Engine::INPUT_TYPE_SLIDER,
						'slider_options' => array (
								'min' => 0,
								'max' => 100,
						),
						'section' => 'advanced',
				),
				'field-slider-range' => array (
						'label' => 'Slider Range',
						'input' => SFB_Render_Engine::INPUT_TYPE_SLIDER,
						'slider_options' => array (
								'range' => true,
								'min' => 10,
								'max' => 100,
								'values' => array( 30, 70 ),
						),
						'section' => 'advanced',
				),
				'field-date' => array (
						'label' => 'Date Picker',
						'input' => SFB_Render_Engine::INPUT_TYPE_DATEPICKER,
						'data_type' => SFB_Validator::DATA_TYPE_DATE,
						'data_type_options' => array (
								'format' => 'D, d M Y',
						),
						'picker_options' => array (
								'dateFormat' => 'D, dd M yy',
						),
						'section' => 'advanced',
				),
				'field-color' => array (
						'label' => 'Color Picker',
						'input' => SFB_Render_Engine::INPUT_TYPE_COLORPICKER,
						'picker_options' => array (
								'defaultColor' => '#fff000',
						),
						'section' => 'advanced',
				),
				'field_wysiwyg' => array (
						'label' => 'TinyMCE HTML WYSIWYG editor',
						'input' => SFB_Render_Engine::INPUT_TYPE_WYSIWYG,
						'data_type' => SFB_Validator::DATA_TYPE_HTML,
						'editor_settings' => array (
								'textarea_rows' => 8,
								'teeny' => true,
						),
						'section' => 'advanced',
				),
		);
		$form->set_fields( $sample_fields );

		// new form for bootstrap test
		$bootstrap_form = new SFB_Form( 'bootstrap-form' );

		// settings
		$bootstrap_form->set_settings( array (
				'render_engine' => 'SFB_Bootstrap_Engine',
				'option_key' => 'wp-sfb-bootstarp',
		) );

		// fields sections
		$bootstrap_form->set_sections( $form->get_sections() );

		// bootstrap form fields
		$sample_fields['field_wysiwyg']['editor_settings']['wrapper_title'] = __( 'HTML Editor', WP_SFB_TEXT_DOMAIN );
		$bootstrap_form->set_fields( $sample_fields );
	}
	
	/**
	 * TEST shortcode ( bootstrap )
	 *
	 * @return string
	 */
	public function shortcode_output()
	{

		// get form
		$form = SFB_Form::get_form( 'bootstrap-form' );

		// form output with values
		return $form->render_ouput( get_option( 'wp-sfb-bootstarp', '' ), false );
	}
	
	/**
	 * TEST page callback
	 *
	 * @return void
	 */
	public function admin_test_page_callback()
	{
		// page wrapper start
		echo '<div class="wrap">';

		echo '<h2>Super Form Builder Demo</h2>';

		// get form
		$form = SFB_Form::get_form( 'demo' );

		// form output with values
		$form->render_ouput( get_option( 'wp-sfb-test' ) );

		// page wrapper end
		echo '</div>';
	}
}

new SFB_Test_Mode();
