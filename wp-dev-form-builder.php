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

// session
if ( '' === session_id() )
	session_start();

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
	require WP_SFB_DIR .'test-mode.php';

