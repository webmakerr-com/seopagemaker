<?php
/**
 * The SEO Framework Integration Class
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Registers The SEO Framework as a Plugin integration:
 * - Register as an overwrite section in Content Groups
 * - Copy / don't copy metadata to generated Pages, depending on if the integration is active
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 4.5.4
 */
class Page_Generator_Pro_The_SEO_Framework extends Page_Generator_Pro_Integration {

	/**
	 * Holds the base object.
	 *
	 * @since   4.5.4
	 *
	 * @var     object
	 */
	public $base;

	/**
	 * Constructor
	 *
	 * @since   4.5.4
	 *
	 * @param   object $base    Base Plugin Class.
	 */
	public function __construct( $base ) {

		// Store base class.
		$this->base = $base;

		// Set Plugin.
		$this->plugin_folder_filename = array(
			'autodescription/autodescription.php',
		);

		// Set Meta Keys used by this Plugin.
		$this->meta_keys = array(
			'/^_genesis_(.*)/i',
			'/^_open_graph_(.*)/i',
			'/^_twitter_(.*)/i',
			'/^_tsf_(.*)/i',
			'redirect',
			'_social_image_url',
			'exclude_from_archive',
			'exclude_local_search',
		);

		// Set Overwrite Setting's Key used by this Plugin.
		$this->overwrite_section = 'the_seo_framework';

		// Content Groups: Add Overwrite Section if The SEO Framework enabled.
		add_filter( 'page_generator_pro_common_get_content_overwrite_sections', array( $this, 'add_overwrite_section' ) );

		// Content Groups: Ignore The SEO Framework meta keys if overwriting is disabled for The SEO Framework.
		add_filter( 'page_generator_pro_generate_set_post_meta_ignored_keys', array( $this, 'prevent_post_meta_copy_to_generated_content' ), 10, 4 );

		// Remove Plugin data from Group Settings if Plugin isn't active on Generation.
		add_filter( 'page_generator_pro_groups_get_settings_remove_orphaned_settings', array( $this, 'remove_orphaned_settings' ) );

	}

	/**
	 * Defines available content overwrite sections.
	 *
	 * @since   4.5.4
	 *
	 * @param   array $sections    Content Overwrite Sections.
	 * @return  array                Content Overwrite Sections
	 */
	public function add_overwrite_section( $sections ) {

		// Bail if The SEO Framework isn't active.
		if ( ! $this->is_active() ) {
			return $sections;
		}

		// Add The SEO Framework.
		$sections['the_seo_framework'] = __( 'The SEO Framework', 'page-generator-pro' );

		// Return.
		return $sections;

	}

	/**
	 * Adds the integration's meta keys to the array of excluded Post Meta Keys if the integration's
	 * metadata should not be overwritten on regeneration of content.
	 *
	 * @since   4.5.4
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

		// If Genesis is active, don't add The SEO Framework meta keys to the array of ignored keys,
		// as Genesis uses the same _genesis_* meta keys.
		if ( Page_Generator_Pro()->get_class( 'genesis' )->is_active() ) {
			return $ignored_keys;
		}

		// Add Meta Keys so they are not overwritten on the Generated Post.
		return array_merge( $ignored_keys, $this->meta_keys );

	}

	/**
	 * Removes orphaned The SEO Framework metadata in the Group Settings during Generation,
	 * if The SEO Framework is not active
	 *
	 * @since   4.5.4
	 *
	 * @param   array $settings   Group Settings.
	 * @return  array               Group Settings
	 */
	public function remove_orphaned_settings( $settings ) {

		// Don't remove settings if the Plugin is active.
		if ( $this->is_active() ) {
			return $settings;
		}

		// Don't remove if Genesis is active.
		// This is deliberate, because Genesis also uses _genesis_* meta keys.
		if ( Page_Generator_Pro()->get_class( 'genesis' )->is_active() ) {
			return $settings;
		}

		// Remove The SEO Framework Meta Keys from the Group Settings during Generation.
		return $this->remove_orphaned_settings_metadata( $settings, $this->meta_keys );

	}

}
