<?php
/**
 * WPSSO Integration Class
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Registers WPSSO as a Plugin integration:
 * - Register Content Groups for use in WPSSO
 * - Copy / don't copy metadata to generated Pages, depending on if the integration is active
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 3.2.0
 */
class Page_Generator_Pro_WPSSO extends Page_Generator_Pro_Integration {

	/**
	 * Holds the base object.
	 *
	 * @since   3.2.0
	 *
	 * @var     object
	 */
	public $base;

	/**
	 * Constructor
	 *
	 * @since   3.2.0
	 *
	 * @param   object $base    Base Plugin Class.
	 */
	public function __construct( $base ) {

		// Store base class.
		$this->base = $base;

		// Set Plugin.
		$this->plugin_folder_filename = 'wpsso/wpsso.php';

		// Set Meta Keys used by this Plugin.
		$this->meta_keys = array(
			'/^_wpsso_(.*)/i',
		);

		// Set Overwrite Setting's Key used by this Plugin.
		$this->overwrite_section = 'wpsso';

		// Register support fpr WPSSO.
		add_filter( 'sucom_get_post_types', array( $this, 'register_support' ) );

		// Add Overwrite Section if WPSSO enabled.
		add_filter( 'page_generator_pro_common_get_content_overwrite_sections', array( $this, 'add_overwrite_section' ) );

		// Ignore WPSSO meta keys if overwriting is disabled for WPSSO.
		add_filter( 'page_generator_pro_generate_set_post_meta_ignored_keys', array( $this, 'prevent_post_meta_copy_to_generated_content' ), 10, 4 );

		// Remove Plugin data from Group Settings if Plugin isn't active on Generation.
		add_filter( 'page_generator_pro_groups_get_settings_remove_orphaned_settings', array( $this, 'remove_orphaned_settings' ) );

	}

	/**
	 * Registers Content Groups with WPSSO, so that WPSSO's metaboxes are output
	 * on Content Groups
	 *
	 * @since   3.2.0
	 *
	 * @param   array $post_types     Post Types.
	 * @return  array                   Post Types
	 */
	public function register_support( $post_types ) {

		$post_types['page-generator-pro'] = 'page-generator-pro';
		return $post_types;

	}

	/**
	 * Defines available content overwrite sections.
	 *
	 * @since   3.2.0
	 *
	 * @param   array $sections    Content Overwrite Sections.
	 * @return  array                Content Overwrite Sections
	 */
	public function add_overwrite_section( $sections ) {

		// Bail if WPSSO isn't active.
		if ( ! $this->is_active() ) {
			return $sections;
		}

		// Add WPSSO.
		$sections[ $this->overwrite_section ] = __( 'WPSSO', 'page-generator-pro' );

		// Return.
		return $sections;

	}

	/**
	 * Removes orphaned WPSSO metadata in the Group Settings during Generation,
	 * if WPSSO is not active
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

		// Remove WPSSO Meta Keys from the Group Settings during Generation.
		return $this->remove_orphaned_settings_metadata( $settings, $this->meta_keys );

	}

}
