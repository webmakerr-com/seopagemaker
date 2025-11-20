<?php
/**
 * Ovic Addon Toolkit Integration Class
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Registers Ovic Addon Toolkit as a Plugin integration:
 * - Register metabox(es) on Content Groups
 * - Copy / don't copy metadata to generated Pages, depending on if the integration is active
 *
 * Supports all KuteThemes (e.g. Stuno Theme).
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 3.3.7
 */
class Page_Generator_Pro_Ovic extends Page_Generator_Pro_Integration {

	/**
	 * Holds the base object.
	 *
	 * @since   3.3.7
	 *
	 * @var     object
	 */
	public $base;

	/**
	 * Constructor
	 *
	 * @since   3.3.7
	 *
	 * @param   object $base    Base Plugin Class.
	 */
	public function __construct( $base ) {

		// Store base class.
		$this->base = $base;

		// Set Meta Keys used by this Plugin.
		$this->meta_keys = array(
			'/^rs_(.*)/i',
			'_custom_page_side_options',
			'_custom_metabox_theme_options',
		);

		add_filter( 'ovic_options_metabox', array( $this, 'register_ovic_toolkit_support' ) );

		// Remove Plugin data from Group Settings if Plugin isn't active on Generation.
		add_filter( 'page_generator_pro_groups_get_settings_remove_orphaned_settings', array( $this, 'remove_orphaned_settings' ) );

	}

	/**
	 * Allows KuteThemes that use the OVIC Toolkit to register its metaboxes into Page Generator Pro's Groups.
	 *
	 * @since   2.4.4
	 *
	 * @param   array $options   Metaboxes' Configuration Options.
	 */
	public function register_ovic_toolkit_support( $options ) {

		// Bail if no options.
		if ( empty( $options ) ) {
			return $options;
		}

		// Make a copy of the options, setting the Post Type of each to Page Generator Pro's Content Groups.
		$content_group_options = array();
		foreach ( $options as $index => $option ) {
			$option['post_type']     = 'page-generator-pro';
			$content_group_options[] = $option;
		}

		// Merge options.
		$options = array_merge( $options, $content_group_options );

		// Return.
		return $options;

	}

	/**
	 * Removes orphaned XX metadata in the Group Settings during Generation,
	 * if XX is not active
	 *
	 * @since   3.3.7
	 *
	 * @param   array $settings   Group Settings.
	 * @return  array               Group Settings
	 */
	public function remove_orphaned_settings( $settings ) {

		// Don't remove settings if the Plugin is active.
		if ( $this->is_active() ) {
			return $settings;
		}

		// Remove XX Meta Keys from the Group Settings during Generation.
		return $this->remove_orphaned_settings_metadata( $settings, $this->meta_keys );

	}

	/**
	 * Checks if Ovic is active
	 *
	 * @since   3.3.7
	 *
	 * @return  bool    Plugin is Active
	 */
	public function is_active() {

		// We don't check for a Plugin activation status, as Ovic can be bundled within a Theme.
		if ( ! class_exists( 'OVIC_Metabox' ) ) {
			return false;
		}

		return true;
	}

}
