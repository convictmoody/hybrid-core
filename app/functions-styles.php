<?php
/**
 * Functions for handling styles in the framework.
 *
 * @package    HybridCore
 * @subpackage Includes
 * @author     Justin Tadlock <justintadlock@gmail.com>
 * @copyright  Copyright (c) 2008 - 2017, Justin Tadlock
 * @link       https://themehybrid.com/hybrid-core
 * @license    http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

namespace Hybrid;

# Register Hybrid Core styles.
add_action( 'wp_enqueue_scripts',    __NAMESPACE__ . '\register_styles', 0 );
add_action( 'enqueue_embed_scripts', __NAMESPACE__ . '\register_styles', 0 );
add_action( 'login_enqueue_scripts', __NAMESPACE__ . '\register_styles', 0 );
add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\register_styles', 0 );

# Active theme style filters.
add_filter( 'stylesheet_uri', __NAMESPACE__ . '\min_stylesheet_uri', 5, 2 );

# Filters the WP locale stylesheet.
add_filter( 'locale_stylesheet_uri', __NAMESPACE__ . '\locale_stylesheet_uri', 5 );

# Remove the default emoji styles. We'll handle this in the stylesheet.
remove_action( 'wp_print_styles', 'print_emoji_styles' );

/**
 * Registers stylesheets for the framework.  This function merely registers styles with WordPress using
 * the wp_register_style() function.  It does not load any stylesheets on the site.  If a theme wants to
 * register its own custom styles, it should do so on the 'wp_enqueue_scripts' hook.
 *
 * @since  1.5.0
 * @access public
 * @global object  $wp_styles
 * @return void
 */
function register_styles() {
	global $wp_styles;

	$suffix = get_min_suffix();

	// Register styles for use by themes.
	wp_register_style( 'hybrid-one-five', app()->uri . "resources/styles/one-five{$suffix}.css", array(), app()->version );
	wp_register_style( 'hybrid-gallery',  app()->uri . "resources/styles/gallery{$suffix}.css",  array(), app()->version );
	wp_register_style( 'hybrid-parent',   get_parent_stylesheet_uri() );
	wp_register_style( 'hybrid-style',    get_stylesheet_uri()        );

	// Use the RTL style for the hybrid-one-five style.
	$wp_styles->add_data( 'hybrid-one-five', 'rtl', 'replace' );

	// Adds the suffix for the hybrid-one-five RTL style.
	if ( $suffix )
		$wp_styles->add_data( 'hybrid-one-five', 'suffix', $suffix );
}

/**
 * Returns the parent theme stylesheet URI.  Will return the active theme's stylesheet URI if no child
 * theme is active. Be sure to check `is_child_theme()` when using.
 *
 * @since  3.0.0
 * @access public
 * @return string
 */
function get_parent_stylesheet_uri() {

	// Get the minified suffix.
	$suffix = get_min_suffix();

	// Get the parent theme stylesheet.
	$stylesheet_uri = app()->parent_uri . 'style.css';

	// If a '.min' version of the parent theme stylesheet exists, use it.
	if ( $suffix && file_exists( app()->parent_dir . "style{$suffix}.css" ) )
		$stylesheet_uri = app()->parent_uri . "style{$suffix}.css";

	return apply_filters( 'hybrid_get_parent_stylesheet_uri', $stylesheet_uri );
}

/**
 * Filters the 'stylesheet_uri' to allow theme developers to offer a minimized version of their main
 * 'style.css' file.  It will detect if a 'style.min.css' file is available and use it if SCRIPT_DEBUG
 * is disabled.
 *
 * @since  1.5.0
 * @access public
 * @param  string  $stylesheet_uri      The URI of the active theme's stylesheet.
 * @param  string  $stylesheet_dir_uri  The directory URI of the active theme's stylesheet.
 * @return string  $stylesheet_uri
 */
function min_stylesheet_uri( $stylesheet_uri, $stylesheet_dir_uri ) {

	// Get the minified suffix.
	$suffix = get_min_suffix();

	// Use the .min stylesheet if available.
	if ( $suffix ) {

		// Remove the stylesheet directory URI from the file name.
		$stylesheet = str_replace( trailingslashit( $stylesheet_dir_uri ), '', $stylesheet_uri );

		// Change the stylesheet name to 'style.min.css'.
		$stylesheet = str_replace( '.css', "{$suffix}.css", $stylesheet );

		// If the stylesheet exists in the stylesheet directory, set the stylesheet URI to the dev stylesheet.
		if ( file_exists( app()->child_dir . $stylesheet ) )
			$stylesheet_uri = esc_url( trailingslashit( $stylesheet_dir_uri ) . $stylesheet );
	}

	// Return the theme stylesheet.
	return $stylesheet_uri;
}

/**
 * Filters `locale_stylesheet_uri` with a more robust version for checking locale/language/region/direction
 * stylesheets.
 *
 * @since  2.0.0
 * @access public
 * @param  string  $stylesheet_uri
 * @return string
 */
function locale_stylesheet_uri( $stylesheet_uri ) {

	$locale_style = get_locale_style();

	return $locale_style ? esc_url( $locale_style ) : $stylesheet_uri;
}

/**
 * Searches for a locale stylesheet.  This function looks for stylesheets in the `css` folder in the following
 * order:  1) $lang-$region.css, 2) $region.css, 3) $lang.css, and 4) $text_direction.css.  It first checks
 * the child theme for these files.  If they are not present, it will check the parent theme.  This is much
 * more robust than the WordPress locale stylesheet, allowing for multiple variations and a more flexible
 * hierarchy.
 *
 * @since  2.0.0
 * @access public
 * @return string
 */
function get_locale_style() {

	$styles = array();

	// Get the locale, language, and region.
	$locale = strtolower( str_replace( '_', '-', get_locale() ) );
	$lang   = strtolower( get_language() );
	$region = strtolower( get_region() );

	$styles[] = "css/{$locale}.css";

	if ( $region !== $locale )
		$styles[] = "css/{$region}.css";

	if ( $lang !== $locale )
		$styles[] = "css/{$lang}.css";

	$styles[] = is_rtl() ? 'css/rtl.css' : 'css/ltr.css';

	return locate_theme_file( $styles );
}