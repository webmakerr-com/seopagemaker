<?php
/*
Plugin Name: Page Generator Pro: Performance Addon
Plugin URI: http://www.wpzinc.com/plugins/page-generator-pro
Description: Runs the Page Generator Pro Generation Routine in Performance Mode, which loads minimal resources for faster generation times.
Version: 4.5.6
Author: WP Zinc
Author URI: http://www.wpzinc.com
*/

/**
 * Page Generator Pro: Performance Addon
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Page Generator Pro: Performance Addon
 *
 * @package   Page_Generator_Pro
 * @author    Tim Carr
 * @version   1.0.0
 * @copyright WP Zinc
 */
class Page_Generator_Pro_Performance_Addon {

	/**
	 * Holds the Plugins to load when in Generation Mode
	 *
	 * Settings may add to this array, but these will always be loaded
	 *
	 * @since   1.9.7
	 *
	 * @var     array
	 */
	public $required_plugins = array(
		// ACF is required for Overwrite Sections setting to be honored.
		'advanced-custom-fields-pro/acf.php',
		'advanced-custom-fields/acf.php',

		// Category Tag Pages, so Taxonomies are registered.
		'category-tag-pages/category-tag-pages.php',

		// Page Builders.
		'cornerstone/cornerstone.php',
		'elementor/elementor.php',
		'elementor-pro/elementor-pro.php',
		'divi-builder/divi-builder.php',

		// FIFU, so fake Attachments are created for Featured Images to work.
		'featured-image-from-url/featured-image-from-url.php',

		// Our own Plugin, obviously.
		'page-generator-pro/page-generator-pro.php',

		// i18n.
		'polylang/polylang.php',
		'sitepress-multilingual-cms/sitepress.php',

		// Search Exclude.
		'search-exclude/search-exclude.php',
	);

	/**
	 * Defines the AJAX actions that support using this Plugin
	 *
	 * @since   1.9.7
	 *
	 * @var     array
	 */
	public $supported_actions = array(
		'page_generator_pro_generate_content',
		'wp_ajax_page_generator_pro_generate_term',
	);

	/**
	 * Register actions and filters
	 *
	 * @since   1.9.7
	 */
	public function __construct() {

		add_filter( 'option_active_plugins', array( $this, 'maybe_unload_plugins' ) );

	}

	/**
	 * Don't load other Plugins if we're running the Generation AJAX routine.
	 *
	 * @since   1.9.7
	 *
	 * @param   array $plugins    Plugins to Load.
	 * @return  array               Plugins to Load
	 */
	public function maybe_unload_plugins( $plugins ) {

		// Bail if we're not performing an AJAX request.
		if ( ! defined( 'DOING_AJAX' ) ) {
			return $plugins;
		}

		// Bail if no action is specified.
		if ( ! isset( $_REQUEST['action'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			return $plugins;
		}

		// Bail if the action isn't one that is supported by this Plugin.
		if ( ! in_array( sanitize_text_field( $_REQUEST['action'] ), $this->supported_actions, true ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			return $plugins;
		}

		// Bail if the settings haven't enabled using the mu-plugin.
		$settings = get_option( 'page-generator-pro-generate' );
		if ( ! is_array( $settings ) ) {
			return $plugins;
		}
		if ( ! isset( $settings['use_mu_plugin'] ) ) {
			return $plugins;
		}
		if ( ! $settings['use_mu_plugin'] ) {
			return $plugins;
		}

		// If any Plugins are specified in settings to be loaded, include them in $required_plugins now.
		if ( isset( $settings['use_mu_active_plugins'] ) && is_array( $settings['use_mu_active_plugins'] ) ) {
			$this->required_plugins = array_merge( $settings['use_mu_active_plugins'], $this->required_plugins );
		}

		// Deactivate all Plugins, other than the required Plugins, for this specific request.
		foreach ( $plugins as $key => $plugin ) {
			// Don't unload this Plugin if it's required.
			if ( in_array( $plugin, $this->required_plugins, true ) ) {
				continue;
			}

			unset( $plugins[ $key ] );
		}

		// Return the Plugins to keep active.
		return $plugins;

	}

}

// Initialize plugin.
$page_generator_pro_performance_addon = new Page_Generator_Pro_Performance_Addon();
