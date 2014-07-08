<?php
/*
Plugin Name: WPMU Dev code library
Plugin URI:  http://premium.wpmudev.org/
Description: Framework to support creating WordPress plugins and themes.
Version:     1.0
Author:      WPMU DEV
Author URI:  http://premium.wpmudev.org/
Textdomain:  wpmu-lib
*/

if ( ! class_exists( 'TheLib' ) ) {
	class TheLib {
		static $methods = array();

		static public function bind( $name, $callback ) {
			self::$methods[ $name ] = $callback;
		}

		static public function __callStatic( $name, $arguments ) {
			if ( isset( self::$methods[ $name ] ) ) {
				return call_user_func_array( self::$methods[ $name ], $arguments );
			} else {
				throw new Exception( 'Function "' . $name . '" is not defined.', 1 );
			}
		}
	};
}

$dirname = dirname( __FILE__ ) . '/';
if ( file_exists( $dirname . 'functions-wpmulib.php' ) ) {
	require_once( $dirname . 'functions-wpmulib.php' );
}