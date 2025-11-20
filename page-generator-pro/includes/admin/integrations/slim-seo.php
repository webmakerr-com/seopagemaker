<?php
/**
 * Slim SEO Integration Class
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Registers Slim SEO as a Plugin integration:
 * - Register as an overwrite section in Content Groups
 * - Copy / don't copy metadata to generated Pages, depending on if the integration is active
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 5.1.0
 */
class Page_Generator_Pro_Slim_SEO extends Page_Generator_Pro_Integration {

	/**
	 * Holds the base object.
	 *
	 * @since   5.1.0
	 *
	 * @var     object
	 */
	public $base;

	/**
	 * Constructor
	 *
	 * @since   5.1.0
	 *
	 * @param   object $base    Base Plugin Class.
	 */
	public function __construct( $base ) {

		// Store base class.
		$this->base = $base;

		// Set Plugin.
		$this->plugin_folder_filename = array(
			'slim-seo/slim-seo.php',
		);

		// Set Meta Keys used by this Plugin.
		$this->meta_keys = array(
			'slim_seo',
		);

		// Set Overwrite Setting's Key used by this Plugin.
		$this->overwrite_section = 'slim_seo';

		// Content Groups: Add Overwrite Section if Slim SEO enabled.
		add_filter( 'page_generator_pro_common_get_content_overwrite_sections', array( $this, 'add_overwrite_section' ) );

		// Content Groups: Ignore Slim SEO meta keys if overwriting is disabled for Slim SEO.
		add_filter( 'page_generator_pro_generate_set_post_meta_ignored_keys', array( $this, 'prevent_post_meta_copy_to_generated_content' ), 10, 4 );

		// Remove Plugin data from Group Settings if Plugin isn't active on Generation.
		add_filter( 'page_generator_pro_groups_get_settings_remove_orphaned_settings', array( $this, 'remove_orphaned_settings' ) );

	}

	/**
	 * Defines available content overwrite sections.
	 *
	 * @since   5.1.0
	 *
	 * @param   array $sections    Content Overwrite Sections.
	 * @return  array                Content Overwrite Sections
	 */
	public function add_overwrite_section( $sections ) {

		// Bail if Slim SEO isn't active.
		if ( ! $this->is_active() ) {
			return $sections;
		}

		// Add Slim SEO.
		$sections['slim_seo'] = __( 'Slim SEO', 'page-generator-pro' );

		// Return.
		return $sections;

	}

	/**
	 * Removes orphaned Slim SEO metadata in the Group Settings during Generation,
	 * if Slim SEO is not active
	 *
	 * @since   5.1.0
	 *
	 * @param   array $settings   Group Settings.
	 * @return  array               Group Settings
	 */
	public function remove_orphaned_settings( $settings ) {

		// Don't remove settings if the Plugin is active.
		if ( $this->is_active() ) {
			return $settings;
		}

		// Remove Slim SEO Meta Keys from the Group Settings during Generation.
		return $this->remove_orphaned_settings_metadata( $settings, $this->meta_keys );

	}

}
