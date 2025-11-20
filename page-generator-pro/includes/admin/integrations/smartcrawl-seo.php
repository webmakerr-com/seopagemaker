<?php
/**
 * SmartCrawl SEO Integration Class
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Registers SmartCrawl SEO as a Plugin integration:
 * - Registers metaboxes in Content Groups
 * - Register as an overwrite section in Content Groups
 * - Copy / don't copy metadata to generated Pages, depending on if the integration is active
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 4.3.4
 */
class Page_Generator_Pro_SmartCrawl_SEO extends Page_Generator_Pro_Integration {

	/**
	 * Holds the base object.
	 *
	 * @since   4.3.4
	 *
	 * @var     object
	 */
	public $base;

	/**
	 * Holds settings for the Group we're editing
	 *
	 * @since   4.3.4
	 *
	 * @var     array
	 */
	public $settings = array();

	/**
	 * Constructor
	 *
	 * @since   4.3.4
	 *
	 * @param   object $base    Base Plugin Class.
	 */
	public function __construct( $base ) {

		// Store base class.
		$this->base = $base;

		// Set Plugin.
		$this->plugin_folder_filename = array(
			'smartcrawl-seo/wpmu-dev-seo.php',
			'smartcrawl-pro-seo/wpmu-dev-seo.php',
			'SmartCrawl-Pro-seo/wpmu-dev-seo.php',
			'wpmu-dev-seo/wpmu-dev-seo.php',
		);

		// Set Meta Keys used by this Plugin.
		$this->meta_keys = array(
			'/^_wds_(.*)/i',
		);

		// Set Overwrite Setting's Key used by this Plugin.
		$this->overwrite_section = 'smartcrawl_seo';

		// Add Overwrite Section if SmartCrawl SEO enabled.
		add_filter( 'page_generator_pro_common_get_content_overwrite_sections', array( $this, 'add_overwrite_section' ) );

		// Ignore SmartCrawl SEO meta keys if overwriting is disabled for SmartCrawl SEO.
		add_filter( 'page_generator_pro_generate_set_post_meta_ignored_keys', array( $this, 'prevent_post_meta_copy_to_generated_content' ), 10, 4 );

		// Remove Plugin data from Group Settings if Plugin isn't active on Generation.
		add_filter( 'page_generator_pro_groups_get_settings_remove_orphaned_settings', array( $this, 'remove_orphaned_settings' ) );

	}

	/**
	 * Defines available content overwrite sections.
	 *
	 * @since   4.3.4
	 *
	 * @param   array $sections    Content Overwrite Sections.
	 * @return  array                Content Overwrite Sections
	 */
	public function add_overwrite_section( $sections ) {

		// Bail if SmartCrawl SEO isn't active.
		if ( ! $this->is_active() ) {
			return $sections;
		}

		// Add SmartCrawl SEO.
		$sections[ $this->overwrite_section ] = __( 'SmartCrawl SEO', 'page-generator-pro' );

		// Return.
		return $sections;

	}

	/**
	 * Removes orphaned SmartCrawl SEO metadata in the Group Settings during Generation,
	 * if SmartCrawl SEO is not active
	 *
	 * @since   4.3.4
	 *
	 * @param   array $settings   Group Settings.
	 * @return  array               Group Settings
	 */
	public function remove_orphaned_settings( $settings ) {

		// Don't remove settings if the Plugin is active.
		if ( $this->is_active() ) {
			return $settings;
		}

		// Remove SmartCrawl SEO Meta Keys from the Group Settings during Generation.
		return $this->remove_orphaned_settings_metadata( $settings, $this->meta_keys );

	}

}
