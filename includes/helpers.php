<?php
/**
 * Help functions
 * 
 * @package Form_Buider/Helpers
 * @since 1.0
 */

final class SFB_Helpers
{
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