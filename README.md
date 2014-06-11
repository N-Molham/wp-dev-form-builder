# WordPress Super Form Builder for Developers

A Plugin library for building forms in WordPress easily and fast, it can be used with plugins or themes and it's highly customizable and can be used in both font and back end.

## Usage ##
- As a separate plugin
	- copy `wp-dev-form-builder` directory into your plugins directory
	- activate the plugin
- As a library in your plugin or theme project
	- copy `wp-dev-form-builder` directory into anywhere in your project directory
	- include the main file `require_once 'path/to/wp-dev-form-builder/wp-dev-form-builder.php';`
- Either ways after doing those steps, you are ready get use of it as shown in the following example

## Example ##

	```php 
	add_action( 'init', 'wp_init' );
	
	/**
	 * WP init
	 */
	function wp_init()
	{
		// create form
		$form = new SFB_Form( 'demo' );
	
		// set settings
		$form->set_settings( array ( 
				'mode_engine' => 'SFB_Mode', // default render engine
		) );

		// you can add section if you want
		$form->set_sections( array ( 
				'one' => array ( 
						'label' => 'Some Section Title',
				),
				'two' => array ( 
						'label' => 'Some Section Title',
						'description' => 'Section description',
				),
		) );
	
		// set form fields
		$form->set_fields( array ( 
				'field-text' => array ( 
						'label' => 'Text',
						'input' => SFB_Mode::INPUT_TYPE_TEXT,
						'data_type' => 'text',
						'description' => 'Regular text input',
						'section' => 'one',
				),
				'field-mail' => array ( 
						'label' => 'Email',
						'input' => SFB_Mode::INPUT_TYPE_EMAIL,
						'data_type' => 'email',
						'description' => 'Email input',
						'section' => 'one',
						'attributes' => array ( 
								'placeholder' => 'Enter Your Email Address',
								'class' => 'regular-text code',
						),
				),
				'field-select' => array ( 
						'label' => 'Dropdown Menu',
						'input' => SFB_Mode::INPUT_TYPE_SELECT,
						'options' => array ( 
								'one' => 'Option One',
								'two' => 'Option Two',
								'three' => 'Option Three',
								'four' => 'Option Four',
						),
						'section' => 'one',
				),
				'field-select-multi' => array ( 
						'label' => 'Dropdown Menu Multiple',
						'input' => SFB_Mode::INPUT_TYPE_SELECT,
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
						'section' => 'one',
				),
				'field-nonce' => array ( 
						'input' => SFB_Mode::INPUT_TYPE_NONCE,
						'action' => 'sfb_save_form',
						'referer' => true,
						'section' => 'one',
				),
				'field-slider-range' => array ( 
						'label' => 'Slider Range',
						'input' => SFB_Mode::INPUT_TYPE_SLIDER,
						'slider_options' => array ( 
								'range' => true,
								'min' => 10,
								'max' => 100,
								'values' => array( 30, 70 ),
						),
						'section' => 'two',
				),
				'field-date' => array ( 
						'label' => 'Date Picker',
						'input' => SFB_Mode::INPUT_TYPE_DATEPICKER,
						'picker_options' => array ( 
								'dateFormat' => 'D, dd M yy',
						),
						'section' => 'two',
				),
				'field-color' => array ( 
						'label' => 'Color Picker',
						'input' => SFB_Mode::INPUT_TYPE_COLORPICKER,
						'picker_options' => array ( 
								'defaultColor' => '#ff0000',
						),
						'section' => 'two',
				),
				'field_wysiwyg' => array ( 
						'label' => 'TinyMCE HTML WYSIWYG editor',
						'input' => SFB_Mode::INPUT_TYPE_WYSIWYG,
						'editor_settings' => array ( 
								'textarea_rows' => 8,
								'teeny' => true,
						),
						'section' => 'two',
				),
		) );
	}
	
	/**
	 * Display form in the target location
	 */
	function display_form()
	{
		// get form
		$form = SFB_Form::get_form( 'demo' );
	
		// form output with values
		$form->render_ouput( array ( 
				'field-text' => 'Prefield value',
				'field-textarea' => 'People tend to read writing. Whoever evaluates your text cannot evaluate the way you write. Humans are creative beings.',
				'field_wysiwyg' => '<h1>HTML Ipsum Presents</h1>
	<p><strong>Pellentesque habitant morbi tristique</strong> senectus et netus et malesuada fames ac turpis egestas. Vestibulum tortor quam, feugiat vitae <em>Aenean ultricies mi vitae est.</em></p>
	
	<ol>
		<li>Lorem ipsum dolor sit amet, consectetuer adipiscing elit.</li>
		<li>Aliquam tincidunt mauris eu risus.</li>
	</ol>
	
	<blockquote><p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus magna. Cras in mi at felis aliquet congue. Ut a est eget ligula molestie gravida. Curabitur massa. Donec eleifend, libero at sagittis mollis, tellus est malesuada tellus, at luctus turpis elit sit amet quam. Vivamus pretium ornare est.</p></blockquote>',
				'field-color' => '#00a008',
				'field-date' => 'Fri, 13 Jun 2014',
				'field-slider-range' => array( 'min' => 10, 'max' => 90 ),
		) );
	}
	```

## Form Output Example ###
- [Basic Inputs](http://nabeel.molham.me/blog/wp-content/uploads/2014/06/form-builder-demo-1.png)
- [Advanced Inputs](http://nabeel.molham.me/blog/wp-content/uploads/2014/06/form-builder-demo-2.png)

## Live Preview ###
- Download the plugin.
- and define this constant `SUPER_FB_DEMO_MODE` wuth `true` in your `wp-config.php` file.
- Go to WordPress Dashboard > Tools > Super Form Builder Demo.

    ```php
	// wp-config.php
	
	// demo enabled
	define( 'SUPER_FB_DEMO_MODE', true );
	
	/* That's all, stop editing! Happy blogging. */
	```

## WP Hooks ##
Note : remember to user those hooks before creating an instance

### filters ###
- `wp_sfb_form_field_args`
	- parameters: `$field_args`, `$field_name`, `$form_id`
    - adding form field arguments 
- `wp_sfb_form_section_args`
	- parameters: `$section_args`, `$section_name`, `$form_id`
    - adding form section arguments
- `wp_sfb_form_settings`
	- parameters: `$settings` , `$form_id`
    - form settings
- `wp_sfb_form_settings_option`
	- parameters: `$option_value`, `$option_name`, `$form_id`
    - setting single form setting options\
- `wp_sfb_form_output`
	- parameters: `$form_output` , `$form_id`
    - final form output which will be displayed

Example:
	```php
	add_filter( 'wp_sfb_form_output', function( $form_output, $form_id ) {
		return '<div id="form-'. $form_id .'">'. $form_output .'</div>';
	} );
	```

** Contact if there are any problems **

Hope you find it helpful :)

License: GNU General Public License v2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
