<?php
/**
 * Form class
 * 
 * @package WP Form Builder
 * @since 1.0
 */

class SFB_Form
{
	/**
	 * Forms instances holder
	 * 
	 * @var array
	 */
	protected static $forms = array();

	/**
	 * Forms ID
	 * 
	 * @var string
	 */
	protected $ID;

	/**
	 * Forms settings
	 * 
	 * @var array
	 */
	protected $settings;

	/**
	 * Forms fields
	 * 
	 * @var array
	 */
	protected $fields;

	/**
	 * Constructor
	 * 
	 * @param string $id should be unique
	 * @param array $settings ( optional ) define form settings on construction
	 * @return void
	 */
	public function __construct( $id, $settings = '' )
	{
		// cache/save form
		self::$forms[$id] = &$this;

		$this->ID = $id;

		// set settings
		$this->set_settings( $settings );
	}

	/**
	 * Set form settings
	 * 
	 * @param array $settings
	 * @return void
	 */
	public function set_settings( $settings )
	{
		// default settings
		$defaults = array ( 
				'mode' => 'wp-admin',
				'handler_hook' => 'sfb_handler_'. $this->ID,
				'attributes' => array ( 
						'action' => '', 
						'method' => 'post', 
						'enctype' => 'application/x-www-form-urlencoded', 
				),
		);

		$this->settings = apply_filters( 'wp_sfb_form_settings', wp_parse_args( $settings, $defaults ), $this->ID );
	}

	/**
	 * Get registered form instance
	 * 
	 * @param string $form_id
	 * @return SFB_Form|boolean
	 */
	public static function get_form( $form_id )
	{
		return isset( self::$forms[ $form_id ] ) ? self::$forms[ $form_id ] : false;
	}
}




















