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
		add_action( 'init', [ &$this, 'init' ] );

		// add admin menu item
		add_action( 'admin_menu', [ &$this, 'admin_menu' ] );

		// add shortcode
		add_shortcode( 'sfb-bootstrap-form', [ &$this, 'shortcode_output' ] );
	}

	/**
	 * TEST: WP Admin Menu
	 *
	 * @return void
	 */
	public function admin_menu()
	{
		add_management_page( 'Super Form Builder Demo', 'Super Form Builder Demo', 'manage_options', 'wp-sfb-test', [
			&$this,
			'admin_test_page_callback',
		] );
	}

	/**
	 * TEST: WP Admin init
	 *
	 * @throws Exception
	 *
	 * @return void
	 */
	public function init()
	{
		// create form
		$form = new SFB_Form( 'demo' );

		// set settings
		$form->set_settings( [
			'option_key' => 'wp-sfb-test',
		] );

		$form->set_sections( [
			'basic'    => [
				'label' => 'Basic Inputs',
			],
			'advanced' => [
				'label'       => 'Advanced Inputs',
				'description' => 'Advanced Component elements',
			],
		] );

		// set form fields
		$sample_fields = [
			'field-text'         => [
				'label'             => 'Text',
				'input'             => SFB_Render_Engine::INPUT_TYPE_TEXT,
				'data_type'         => SFB_Validator::DATA_TYPE_TEXT,
				'data_type_options' => [
					'min_length' => 2,
					'max_length' => 240,
				],
				'description'       => 'Regular text input',
				'section'           => 'basic',
				'required'          => true,
			],
			'field-mail'         => [
				'label'       => 'Email',
				'input'       => SFB_Render_Engine::INPUT_TYPE_EMAIL,
				'data_type'   => SFB_Validator::DATA_TYPE_EMAIL,
				'description' => 'Email input',
				'section'     => 'basic',
				'attributes'  => [
					'placeholder' => 'Enter Your Email Address',
					'class'       => 'regular-text code',
				],
				'required'    => true,
			],
			'field-textarea'     => [
				'label'             => 'Textarea',
				'input'             => SFB_Render_Engine::INPUT_TYPE_TEXTAREA,
				'data_type'         => SFB_Validator::DATA_TYPE_TEXT,
				'data_type_options' => [
					'multiline' => true,
				],
				'section'           => 'basic',
				'attributes'        => [
					'rows'  => '6',
					'class' => 'large-text',
				],
			],
			'field-number'       => [
				'label'             => 'Number',
				'input'             => SFB_Render_Engine::INPUT_TYPE_NUMBER,
				'data_type'         => SFB_Validator::DATA_TYPE_NUMBER,
				'data_type_options' => [
					'min' => 0,
					'max' => 50,
				],
				'section'           => 'basic',
				'attributes'        => [
					'step'  => '10',
					'max'   => '50',
					'min'   => '0',
					'class' => 'small-text',
				],
			],
			'field-regex'        => [
				'label'             => 'Regular Expression',
				'input'             => SFB_Render_Engine::INPUT_TYPE_TEXT,
				'data_type'         => SFB_Validator::DATA_TYPE_TEXT,
				'data_type_options' => [
					'regex' => '/^[a-z]{3}-\d{3,4}$/i',
				],
				'section'           => 'basic',
				'attributes'        => [
					'class' => 'regular-text code',
				],
			],
			'field-checkbox'     => [
				'label'       => 'Single Checkbox',
				'input'       => SFB_Render_Engine::INPUT_TYPE_CHECKBOX,
				'single'      => true,
				'options'     => [
					'yes' => 'Yes',
				],
				'description' => 'set <code>single</code> parameter to <code>true</code> for a single option',
				'section'     => 'basic',
			],
			'field-checkboxes'   => [
				'label'       => 'Multiple Checkboxes',
				'input'       => SFB_Render_Engine::INPUT_TYPE_CHECKBOX,
				'single'      => false,
				'options'     => [
					'one'   => 'Option One',
					'two'   => 'Option Two',
					'three' => 'Option Three',
					'four'  => 'Option Four',
				],
				'description' => 'set <code>single</code> parameter to <code>false</code> for a multiple options',
				'section'     => 'basic',
			],
			'field-radio'        => [
				'label'   => 'Radio',
				'input'   => SFB_Render_Engine::INPUT_TYPE_RADIO,
				'options' => [
					'one'   => 'Option One',
					'two'   => 'Option Two',
					'three' => 'Option Three',
					'four'  => 'Option Four',
				],
				'section' => 'basic',
			],
			'field-select'       => [
				'label'   => 'Dropdown Menu',
				'input'   => SFB_Render_Engine::INPUT_TYPE_SELECT,
				'options' => [
					'one'   => 'Option One',
					'two'   => 'Option Two',
					'three' => 'Option Three',
					'four'  => 'Option Four',
				],
				'section' => 'basic',
			],
			'field-select-multi' => [
				'label'      => 'Dropdown Menu Multiple',
				'input'      => SFB_Render_Engine::INPUT_TYPE_SELECT,
				'options'    => [
					'one'   => 'Option One',
					'two'   => 'Option Two',
					'three' => 'Option Three',
					'four'  => 'Option Four',
					'five'  => 'Option Five',
					'sex'   => 'Option Sex',
				],
				'attributes' => [
					'multiple' => 'multiple',
					'size'     => '3',
				],
				'section'    => 'basic',
			],
			'field-hidden'       => [
				'input'   => SFB_Render_Engine::INPUT_TYPE_HIDDEN,
				'value'   => 'hidden_value',
				'section' => 'basic',
			],
			'field-nonce'        => [
				'input'   => SFB_Render_Engine::INPUT_TYPE_NONCE,
				'action'  => 'sfb_save_form',
				'referer' => true,
				'section' => 'basic',
			],
			'field-slider'       => [
				'label'          => 'Slider',
				'input'          => SFB_Render_Engine::INPUT_TYPE_SLIDER,
				'slider_options' => [
					'min' => 0,
					'max' => 100,
				],
				'section'        => 'advanced',
			],
			'field-slider-range' => [
				'label'          => 'Slider Range',
				'input'          => SFB_Render_Engine::INPUT_TYPE_SLIDER,
				'slider_options' => [
					'range'  => true,
					'min'    => 10,
					'max'    => 100,
					'values' => [ 30, 70 ],
				],
				'section'        => 'advanced',
			],
			'field-date'         => [
				'label'             => 'Date Picker',
				'input'             => SFB_Render_Engine::INPUT_TYPE_DATEPICKER,
				'data_type'         => SFB_Validator::DATA_TYPE_DATE,
				'data_type_options' => [
					'format' => 'D, d M Y',
				],
				'picker_options'    => [
					'dateFormat' => 'D, dd M yy',
				],
				'section'           => 'advanced',
			],
			'field-color'        => [
				'label'          => 'Color Picker',
				'input'          => SFB_Render_Engine::INPUT_TYPE_COLORPICKER,
				'picker_options' => [
					'defaultColor' => '#fff000',
				],
				'section'        => 'advanced',
			],
			'field_wysiwyg'      => [
				'label'           => 'TinyMCE HTML WYSIWYG editor',
				'input'           => SFB_Render_Engine::INPUT_TYPE_WYSIWYG,
				'data_type'       => SFB_Validator::DATA_TYPE_HTML,
				'editor_settings' => [
					'textarea_rows' => 8,
					'teeny'         => true,
				],
				'section'         => 'advanced',
			],
		];
		$form->set_fields( $sample_fields );

		// new form for bootstrap test
		$bootstrap_form = new SFB_Form( 'bootstrap-form' );

		// settings
		$bootstrap_form->set_settings( [
			'render_engine' => 'SFB_Bootstrap_Engine',
			'option_key'    => 'wp-sfb-bootstarp',
		] );

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
		return $form->render_ouput( get_option( $form->get_settings( 'option_key' ) ), false );
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
