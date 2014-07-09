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

		// Add support for PHP <5.3, create each static method
		
		static public function add_ui(){
			$arguments = func_get_args();
			self::__callStatic( 'add_ui', $arguments );
		}

		static public function add_js(){
			$arguments = func_get_args();
			self::__callStatic( 'add_js', $arguments );
		}

		static public function add_css(){
			$arguments = func_get_args();
			self::__callStatic( 'add_css', $arguments );
		}

		static public function add_admin_js_or_css(){
			$arguments = func_get_args();
			self::__callStatic( 'add_admin_js_or_css', $arguments );
		}

		static public function pointer(){
			$arguments = func_get_args();
			self::__callStatic( 'pointer', $arguments );
		}

		static public function message(){
			$arguments = func_get_args();
			self::__callStatic( 'message', $arguments );
		}

		static public function load_textdomain(){
			$arguments = func_get_args();
			self::__callStatic( 'load_textdomain', $arguments );
		}

	};
}

$dirname = dirname( __FILE__ ) . '/';
if ( version_compare(PHP_VERSION, '5.3.0') >= 0 ) {
	if ( file_exists( $dirname . 'functions-wpmulib.php' ) ) {
		require_once( $dirname . 'functions-wpmulib.php' );
	}
}
else {
	if ( file_exists( $dirname . 'functions-wpmulib-5.2.php' ) ) {
		require_once( $dirname . 'functions-wpmulib-5.2.php' );
	}
}