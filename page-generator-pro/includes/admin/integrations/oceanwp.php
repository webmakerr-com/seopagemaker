<?php
/**
 * OceanWP Integration Class
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Registers OceanWP Theme as a Plugin integration:
 * - Enable OceanWP Metaboxes on Content Groups
 * - Register as an overwrite section in Content Groups
 * - Copy / don't copy metadata to generated Pages, depending on if the integration is active
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 4.9.3
 */
class Page_Generator_Pro_OceanWP extends Page_Generator_Pro_Integration {

	/**
	 * Holds the base object.
	 *
	 * @since   4.9.3
	 *
	 * @var     object
	 */
	public $base;

	/**
	 * Constructor
	 *
	 * @since   4.9.3
	 *
	 * @param   object $base    Base Plugin Class.
	 */
	public function __construct( $base ) {

		// Store base class.
		$this->base = $base;

		// Set Meta Keys used by this Plugin.
		$this->meta_keys = array(
			'/^ocean_(.*)/i',
		);

		// Set Overwrite Setting's Key used by this Plugin.
		$this->overwrite_section = 'oceanwp';

		// Register Post Type Support for Content Groups.
		add_action( 'ocean_main_metaboxes_post_types', array( $this, 'register_post_type_support' ) );

		// Add Overwrite Section if OceanWP enabled.
		add_filter( 'page_generator_pro_common_get_content_overwrite_sections', array( $this, 'add_overwrite_section' ) );

		// Ignore OceanWP meta keys if overwriting is disabled for OceanWP.
		add_filter( 'page_generator_pro_generate_set_post_meta_ignored_keys', array( $this, 'prevent_post_meta_copy_to_generated_content' ), 10, 4 );

		// Remove Theme data from Group Settings if Theme isn't active on Generation.
		add_filter( 'page_generator_pro_groups_get_settings_remove_orphaned_settings', array( $this, 'remove_orphaned_settings' ) );

	}

	/**
	 * Register OceanWP Meta Boxes as supported for the Content Groups Post Type
	 *
	 * @since   4.9.3
	 *
	 * @param   array $post_types     Post Types.
	 * @return  array
	 */
	public function register_post_type_support( $post_types ) {

		$post_types[] = 'page-generator-pro';
		return $post_types;

	}

	/**
	 * Defines available content overwrite sections.
	 *
	 * @since   4.9.3
	 *
	 * @param   array $sections    Content Overwrite Sections.
	 * @return  array              Content Overwrite Sections
	 */
	public function add_overwrite_section( $sections ) {

		// Bail if OceanWP isn't active.
		if ( ! $this->is_active() ) {
			return $sections;
		}

		// Add OceanWP.
		$sections[ $this->overwrite_section ] = __( 'OceanWP', 'page-generator-pro' );

		// Return.
		return $sections;

	}

	/**
	 * Adds the integration's meta keys to the array of excluded Post Meta Keys if the integration's
	 * metadata should not be overwritten on regeneration of content.
	 *
	 * @since   4.9.3
	 *
	 * @param   array $ignored_keys   Ignored Keys (preg_match() compatible regex expressions are supported).
	 * @param   int   $post_id        Generated Post ID.
	 * @param   array $settings       Group Settings.
	 * @param   array $post_args      wp_insert_post() / wp_update_post() arguments.
	 * @return  array   $ignored_keys   Ignored Keys
	 */
	public function prevent_post_meta_copy_to_generated_content( $ignored_keys, $post_id, $settings, $post_args ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter

		// Determine if we want to create/replace this integration's metdata.
		$overwrite = ( isset( $post_args['ID'] ) && ! array_key_exists( $this->overwrite_section, $settings['overwrite_sections'] ) ? false : true );

		// If overwriting is enabled, no need to exclude anything.
		if ( $overwrite ) {
			return $ignored_keys;
		}

		// If no meta keys are set by this integration, no need to exclude anything.
		if ( ! is_array( $this->meta_keys ) ) {
			return $ignored_keys;
		}

		// Add Meta Keys so they are not overwritten on the Generated Post.
		return array_merge( $ignored_keys, $this->meta_keys );

	}

	/**
	 * Removes orphaned metadata in the Group Settings during Generation,
	 * if OceanWP is not active
	 *
	 * @since   4.9.3
	 *
	 * @param   array $settings   Group Settings.
	 * @return  array               Group Settings
	 */
	public function remove_orphaned_settings( $settings ) {

		// Don't remove settings if OceanWP is active.
		if ( $this->is_active() ) {
			return $settings;
		}

		// Remove OceanWP Meta Keys from the Group Settings during Generation.
		return $this->remove_orphaned_settings_metadata( $settings, $this->meta_keys );

	}

	/**
	 * Checks if OceanWP is active
	 *
	 * @since   4.9.3
	 *
	 * @return  bool    Is Active
	 */
	public function is_active() {

		return defined( 'OCEANWP_THEME_DIR' );

	}

}
