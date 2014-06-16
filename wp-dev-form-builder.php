<?php
/*
Plugin Name: WP Super Form Builder for Developers
Plugin URI: http://nabeel.molham.me/wp-plugins/super-form-builder
Description: A form builder engine for WordPress developers to use as a plugin or as separate library
Version: 1.0
Author: Nabeel Molham
Author URI: http://nabeel.molham.me
Text Domain: wp-super-form-builder
Domain Path: /languages
License: GNU General Public License, version 2, http://www.gnu.org/licenses/gpl-2.0.html
*/

/**
 * Library physical path
 */
if ( !defined( 'WP_SFB_DIR' ) )
	define( 'WP_SFB_DIR', plugin_dir_path( __FILE__ ) );

/**
 * library URI
 */ 
if ( !defined( 'WP_SFB_URI' ) )
{
	// check location first
	if ( strpos( WP_SFB_DIR, get_option( 'template' ) ) !== false )
	{
		// used in theme

		// library location
		$plugin_path = str_replace( '\\', '/', WP_SFB_DIR );

		// theme location
		$theme_path = str_replace( '\\', '/', get_template_directory() );

		// build library URI
		define( 'WP_SFB_URI', get_template_directory_uri() . str_replace( $theme_path, '', $plugin_path ) );
	}
	else
	{
		// used a plug-in or as a plug-in
		define( 'WP_SFB_URI', plugin_dir_url( __FILE__ ) );
	}
}

/**
 * language text domain
 */
if ( !defined( 'WP_SFB_TEXT_DOMAIN' ) )
	define( 'WP_SFB_TEXT_DOMAIN', 'super-form-builder' );

/**
 * language files directory
 */
if ( !defined( 'WP_SFB_LANG_DIR' ) )
	define( 'WP_SFB_LANG_DIR', WP_SFB_DIR . 'languages/' );

/**
 * Includes
 */
require_once WP_SFB_DIR . 'includes/helpers.php';

spl_autoload_register( 'wp_sfb_autoload' );
/**
 * Autoload class files on demand
 *
 * `Super_Form_Builder` becomes => class-super-form-builder.php
 * `Super_Form_Builder_Fields` becomes => class-super-form-builder-fields.php
 *
 * @param string $class requested class name
 * @return void
 */
function wp_sfb_autoload( $class_name )
{
	$prefix = 'SFB';

	if ( stripos( $class_name, $prefix ) !== false )
	{
		$class_name = str_replace( '_', '-', strtolower( $class_name ) );
		$file_path = WP_SFB_DIR . 'includes/class-' . $class_name . '.php';

		// check class file
		if ( !file_exists( $file_path ) )
			wp_die( 'Class file not found, '. $class_name );

		require_once $file_path;
	}
}

add_action( 'plugins_loaded', 'wp_sfb_load_language' );
/**
 * Language file loading
 */
function wp_sfb_load_language()
{
	if ( WP_SFB_LANG_DIR )
		load_plugin_textdomain( WP_SFB_TEXT_DOMAIN, false, WP_SFB_LANG_DIR );
}

// display only if test mode enabled
if ( defined( 'SUPER_FB_DEMO_MODE' ) )
{
	add_action( 'init', 'wp_sfb_test_admin_init' );
	add_action( 'admin_menu', 'wp_sfb_test_admin_menu' );
}

/**
 * TEST: WP Admin Menu
 * 
 * @return void
 */
function wp_sfb_test_admin_menu()
{
	add_management_page( 'Super Form Builder Demo', 'Super Form Builder Demo', 'manage_options', 'wp-sfb-test', 'wp_sfb_test_callback' );
}

/**
 * TEST: WP Admin init
 * 
 * @return void
 */
function wp_sfb_test_admin_init()
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
	$form->set_fields( array ( 
			'field-text' => array ( 
					'label' => 'Text',
					'input' => SFB_Render_Engine::INPUT_TYPE_TEXT,
					'data_type' => SFB_Validator::DATA_TYPE_TEXT,
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
					'section' => 'basic',
					'attributes' => array ( 
							'step' => '10',
							'min' => '0',
							'class' => 'small-text',
					),
			),
			'field-checkbox' => array ( 
					'label' => 'Single Checkbox',
					'input' => SFB_Render_Engine::INPUT_TYPE_CHECKBOX,
					'data_type' => SFB_Validator::DATA_TYPE_TEXT,
					'data_type_options' => array (
							'from_options' => true,
					),
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
					'data_type' => SFB_Validator::DATA_TYPE_ARRAY,
					'data_type_options' => array (
							'from_options' => true,
					),
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
					'data_type' => SFB_Validator::DATA_TYPE_TEXT,
					'data_type_options' => array (
							'from_options' => true,
					),
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
					'data_type' => SFB_Validator::DATA_TYPE_TEXT,
					'data_type_options' => array (
							'from_options' => true,
					),
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
					'data_type' => SFB_Validator::DATA_TYPE_ARRAY,
					'data_type_options' => array (
							'from_options' => true,
					),
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
							'defaultColor' => '#ff0000',
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
	) );
}

/**
 * TEST page callback
 *
 * @param array $args
 */
function wp_sfb_test_callback()
{
	// page wrapper start
	echo '<div class="wrap">';

	echo '<h2>Super Form Builder Demo</h2>';

	// get form
	$form = SFB_Form::get_form( 'demo' );

	// form output with values
	$form->render_ouput();

	// page wrapper end
	echo '</div>';
}






















