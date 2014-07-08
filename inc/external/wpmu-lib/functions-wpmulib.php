<?php
namespace wpmulib_v1;

// Based on Jigsaw plugin by Jared Novack (http://jigsaw.upstatement.com/)

/**
 * Namespace version; should be identical with plugin version.
 */
const VERSION = 1.0;

/**
 * Returns the full URL to an internal CSS file of the code library.
 *
 * @since  1.0.0
 * @param  string $file The filename, relative to this plugins folder.
 * @return string
 */
function css_url( $file ) {
	static $Url = null;
	if ( null === $Url ) {
		$basedir = dirname( __FILE__ ) . '/';
		$basedir = str_replace( ABSPATH, \site_url() . '/', $basedir );
		$Url = $basedir . 'css/';
	}
	return $Url . $file;
}

/**
 * Returns the full URL to an internal JS file of the code library.
 *
 * @since  1.0.0
 * @param  string $file The filename, relative to this plugins folder.
 * @return string
 */
function js_url( $file ) {
	static $Url = null;
	if ( null === $Url ) {
		$basedir = dirname( __FILE__ ) . '/';
		$basedir = str_replace( ABSPATH, \site_url() . '/', $basedir );
		$Url = $basedir . 'js/';
	}
	return $Url . $file;
}

/**
 * Returns the full path to an internal php partial of the code library.
 *
 * @since  1.0.0
 * @param  string $file The filename, relative to this plugins folder.
 * @return string
 */
function include_path( $file ) {
	static $Path = null;
	if ( null === $Path ) {
		$basedir = dirname( __FILE__ ) . '/';
		$Path = $basedir . 'inc/';
	}
	return $Path . $file;
}

/**
 * Enqueue core UI files (CSS/JS).
 *
 * Defined modules:
 *  - core
 *  - scrollbar
 *  - select
 *
 * @since  1.0.0
 * @param  string $modules The module to load.
 * @param  string $onpage A page hook; files will only be loaded on this page.
 */
function add_ui( $module = 'core', $onpage = null ) {
	switch ( $module ) {
		case 'core':
			\TheLib::add_css( css_url( 'wpmu-ui.css' ), $onpage );
			\TheLib::add_js( js_url( 'wpmu-ui.min.js' ), $onpage );
			break;

		case 'scrollbar':
			\TheLib::add_js( js_url( 'tiny-scrollbar.min.js' ), $onpage );
			break;

		case 'select':
			\TheLib::add_css( css_url( 'select2.css' ), $onpage );
			\TheLib::add_js( js_url( 'select2.min.js' ), $onpage );
			break;

		default:
			$ext = strrchr( $module, '.' );
			if ( '.css' === $ext ) {
				\TheLib::add_css( $module, $onpage );
			} else if ( '.js' === $ext ) {
				\TheLib::add_js( $module, $onpage );
			}
	}
}
\TheLib::bind( 'add_ui', __NAMESPACE__ . '\add_ui' );

/**
 * Enqueue a javascript file.
 *
 * @since  1.0.0
 * @param  string $url Full URL to the javascript file.
 * @param  string $onpage A page hook; files will only be loaded on this page.
 */
function add_js( $url, $onpage ) {
	\TheLib::add_admin_js_or_css( $url, 'js', $onpage = null );
}
\TheLib::bind( 'add_js', __NAMESPACE__ . '\add_js' );

/**
 * Enqueue a css file.
 *
 * @since  1.0.0
 * @param  string $url Full URL to the css filename.
 * @param  string $onpage A page hook; files will only be loaded on this page.
 */
function add_css( $url, $onpage ) {
	\TheLib::add_admin_js_or_css( $url, 'css', $onpage = null );
}
\TheLib::bind( 'add_css', __NAMESPACE__ . '\add_css' );

/**
 * Enqueues either a css or javascript file
 *
 * @since  1.0.0
 * @param  string $url Full URL to the CSS or Javascript file.
 * @param  string $type File-type [css|js]
 * @param  string $onpage A page hook; files will only be loaded on this page.
 */
function add_admin_js_or_css( $url, $type = 'css', $onpage = null ) {
	if ( ! is_admin() ) {
		return;
	}

	// Get the filename from the URL, then sanitize it and prefix "wpmu-"
	$urlparts = explode( '?', $url, 2 );
	$alias = 'wpmu-' . sanitize_title( basename( reset( $urlparts ) ) );

	if ( 'css' == $type || 'style' == $type ) {
		add_action(
			'admin_enqueue_scripts',
			function( $hook ) use ( $alias, $url, $onpage ) {
				if ( null !== $onpage && $hook != $onpage ) { return; }
				wp_enqueue_style( $alias, $url );
			}
		);
	} else {
		add_action(
			'admin_enqueue_scripts',
			function( $hook ) use ( $alias, $url, $onpage ) {
				if ( null !== $onpage && $hook != $onpage ) { return; }
				wp_enqueue_script( $alias, $url, array( 'jquery' ), false, true );
			}
		);
	}
}
\TheLib::bind( 'add_admin_js_or_css', __NAMESPACE__ . '\add_admin_js_or_css' );

/**
 * Displays a WordPress pointer on the current admin screen.
 *
 * @since  1.0.0
 * @param  string $pointer_id Internal ID of the pointer, make sure it is unique!
 * @param  string $html_el HTML element to point to (e.g. '#menu-appearance')
 * @param  string $title The title of the pointer.
 * @param  string $body Text of the pointer.
 */
function pointer( $pointer_id, $html_el, $title, $body ) {
	if ( ! is_admin() ) {
		return;
	}

	add_action(
		'admin_enqueue_scripts',
		function() use ( $pointer_id, $html_el, $title, $body ) {
			// Find out which pointer IDs this user has already seen.
			$seen = (string) get_user_meta(
				\get_current_user_id(),
				'dismissed_wp_pointers',
				true
			);
			$seen_list = explode( ',', $seen );
			// Handle our first pointer announcing the plugin's new settings screen.
			if ( ! in_array( $pointer_id, $seen_list ) ) {
				add_action(
					'admin_print_footer_scripts',
					function() use ( $pointer_id, $html_el, $title, $body ) {
						include include_path( 'pointer.php' );
					}
				);
				// Load the JS/CSS for WP Pointers
				wp_enqueue_script( 'wp-pointer' );
				wp_enqueue_style( 'wp-pointer' );
			}
		}
	);
}
\TheLib::bind( 'pointer', __NAMESPACE__ . '\pointer' );

/**
 * Display an admin notice.
 *
 * @since  1.0.0
 * @param  string $text Text to display.
 * @param  string $class Message-type [updated|error]
 */
function message( $text, $class = 'updated' ){
	if ( 'green' == $class || 'update' == $class || 'ok' == $class ) {
		$class = 'updated';
	}
	if ( 'red' == $class || 'err' == $class ) {
		$class = 'error';
	}
	add_action(
		'admin_notices',
		function() use ( $text, $class ) {
			echo '<div class="' . $class . '"><p>' . $text . '</p></div>';
		},
		1
	);
}
\TheLib::bind( 'message', __NAMESPACE__ . '\message' );

/**
 * Short way to load the textdomain of a plugin.
 *
 * @since  1.0.0
 * @param  string $domain Translations will be mapped to this domain.
 * @param  string $rel_dir Path to the dictionary folder; relative to ABSPATH.
 */
function load_textdomain( $domain, $rel_dir ) {
	add_action(
		'plugins_loaded',
		function() use ( $domain, $rel_dir ) {
			load_plugin_textdomain( $domain, false, $rel_dir );
		}
	);
}
\TheLib::bind( 'load_textdomain', __NAMESPACE__ . '\load_textdomain' );
