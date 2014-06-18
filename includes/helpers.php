<?php
/**
 * Help functions
 * 
 * @package Form_Buider/Helpers
 * @since 1.0
 */

final class SFB_Helpers
{
	/**
	 * URL Redirect 
	 * 
	 * @param string $target
	 * @param number $status
	 * @return void
	 */
	public static function redirect( $target = '', $status = 302 )
	{
		if ( '' == $target && isset( $_REQUEST['_wp_http_referer'] ) )
			$target = esc_url( $_REQUEST['_wp_http_referer'] );
	
		wp_redirect( $target, $status );
		die();
	}

	/**
	 * Modified version of sanitize_text_field with line-breaks preserved
	 *
	 * @see sanitize_text_field
	 * @since 2.9.0
	 *
	 * @param string $str
	 * @return string
	 */
	public static function sanitize_text_field_with_linebreaks( $str ) 
	{
		$filtered = wp_check_invalid_utf8( $str );

		if ( strpos($filtered, '<') !== false ) 
		{
			$filtered = wp_pre_kses_less_than( $filtered );

			// This will strip extra whitespace for us.
			$filtered = wp_strip_all_tags( $filtered, true );
		}

		$found = false;
		while ( preg_match( '/%[a-f0-9]{2}/i', $filtered, $match ) ) 
		{
			$filtered = str_replace( $match[0], '', $filtered );
			$found = true;
		}

		if ( $found ) 
		{
			// Strip out the whitespace that may now exist after removing the octets.
			$filtered = trim( preg_replace( '/ +/', ' ', $filtered ) );
		}

		/**
		 * Filter a sanitized text field string.
		 *
		 * @since 2.9.0
		 *
		 * @param string $filtered The sanitized string.
		 * @param string $str      The string prior to being sanitized.
		 */
		return apply_filters( 'sanitize_text_field_with_linebreaks', $filtered, $str );
	}

	/**
	 * Parse/Join html attributes
	 * 
	 * @param array $attrs
	 * @return string
	 */
	public static function parse_attributes( $attrs )
	{
		array_walk( $attrs, function( &$item, $key ) {
			$item = $key .'="'. esc_attr( $item ) .'"';
		} );

		return implode( ' ', $attrs );
	}
}

if( !function_exists( 'dump_data_export' ) )
{
	/**
	 * Get debug information of passed data
	 * 
	 * @param mixed $data
	 * @param boolean $type
	 * @return string
	 */
	function dump_data_export( $data, $type = false )
	{
		return '<pre>'. ( $type ? var_export( $data, true ) : print_r( $data, true ) ) .'</pre>';
	}
}

if( !function_exists( 'dump_data' ) )
{
	/**
	 * Debug data passed
	 * 
	 * @param mixed $data
	 * @param bolean $type wither to display data type or not
	 * @return void
	 */
	function dump_data( $data, $type = false )
	{
		// normal
		echo '<pre>';
		$type ? var_dump( $data ) : print_r( $data );
		echo '</pre>';
	}
}

if( !function_exists( 'multi_dump_data' ) )
{
	/**
	 * Debug multiple variables 
	 * 
	 * @param mixed $args ...
	 * @return void
	 * @uses dump_data
	 */
	function multi_dump_data( $args )
	{
		$args = func_get_args();
		foreach ( $args as $arg )
		{
			dump_data( $arg );
		}
	}
}