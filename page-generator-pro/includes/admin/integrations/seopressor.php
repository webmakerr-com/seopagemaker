<?php
/**
 * SEOPressor Integration Class
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Registers SEOPressor as a Plugin integration:
 * - Register as an overwrite section in Content Groups
 * - Copy / don't copy metadata to generated Pages, depending on if the integration is active
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 2.9.6
 */
class Page_Generator_Pro_SEOPressor extends Page_Generator_Pro_Integration {

	/**
	 * Holds the base object.
	 *
	 * @since   2.9.6
	 *
	 * @var     object
	 */
	public $base;

	/**
	 * Constructor
	 *
	 * @since   2.9.6
	 *
	 * @param   object $base    Base Plugin Class.
	 */
	public function __construct( $base ) {

		// Store base class.
		$this->base = $base;

		// Set Plugin.
		$this->plugin_folder_filename = 'seo-pressor/seo-pressor.php';

		// Set Meta Keys used by this Plugin.
		$this->meta_keys = array(
			'/^_seop_(.*)/i',
		);

		// Set Overwrite Setting's Key used by this Plugin.
		$this->overwrite_section = 'seopressor';

		// Add Overwrite Section if SEOPressor enabled.
		add_filter( 'page_generator_pro_common_get_content_overwrite_sections', array( $this, 'add_overwrite_section' ) );

		// Ignore SEOPressor meta keys if overwriting is disabled for SEOPressor.
		add_filter( 'page_generator_pro_generate_set_post_meta_ignored_keys', array( $this, 'prevent_post_meta_copy_to_generated_content' ), 10, 4 );

		// Remove Plugin data from Group Settings if Plugin isn't active on Generation.
		add_filter( 'page_generator_pro_groups_get_settings_remove_orphaned_settings', array( $this, 'remove_orphaned_settings' ) );

	}

	/**
	 * Defines available content overwrite sections.
	 *
	 * @since   2.9.6
	 *
	 * @param   array $sections    Content Overwrite Sections.
	 * @return  array                Content Overwrite Sections
	 */
	public function add_overwrite_section( $sections ) {

		// Bail if SEOPressor isn't active.
		if ( ! $this->is_active() ) {
			return $sections;
		}

		// Add SEOPressor.
		$sections[ $this->overwrite_section ] = __( 'SEOPressor', 'page-generator-pro' );

		// Return.
		return $sections;

	}

	/**
	 * Removes orphaned SEOPressor metadata in the Group Settings during Generation,
	 * if SEOPressor is not active.
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

		// Remove SEOPressor Meta Keys from the Group Settings during Generation.
		return $this->remove_orphaned_settings_metadata( $settings, $this->meta_keys );

	}

}
