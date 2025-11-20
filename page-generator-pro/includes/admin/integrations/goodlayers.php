<?php
/**
 * GoodLayers Integration Class
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Registers GoodLayers as a Plugin integration:
 * - Register as an overwrite section in Content Groups
 * - Copy / don't copy metadata to generated Pages, depending on if the integration is active
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 3.3.9
 */
class Page_Generator_Pro_GoodLayers extends Page_Generator_Pro_Integration {

	/**
	 * Holds the base object.
	 *
	 * @since   3.3.9
	 *
	 * @var     object
	 */
	public $base;

	/**
	 * Constructor
	 *
	 * @since   3.3.9
	 *
	 * @param   object $base    Base Plugin Class.
	 */
	public function __construct( $base ) {

		// Store base class.
		$this->base = $base;

		// Set Plugin.
		$this->plugin_folder_filename = 'goodlayers-core/goodlayers-core.php';

		// Set Meta Keys used by the GoodLayers Page Builder.
		$this->meta_keys = array(
			'gdlr-core-page-builder',
		);

		// Set Overwrite Setting's Key used by this Plugin.
		$this->overwrite_section = 'goodlayers_page_options';

		// Content Groups: Add Overwrite Section if Yoast SEO enabled.
		add_filter( 'page_generator_pro_common_get_content_overwrite_sections', array( $this, 'add_overwrite_section' ) );

		// Content Groups: Ignore meta keys if overwriting is disabled for Page Options.
		add_filter( 'page_generator_pro_generate_set_post_meta_ignored_keys', array( $this, 'prevent_post_meta_copy_to_generated_content' ), 10, 4 );

		// Remove Page Builder data from Group Settings if overwriting content is disabled, and an existing generated page already exists.
		add_filter( 'page_generator_pro_generate_remove_content_from_settings_ignored_keys', array( $this, 'remove_post_meta_from_content_group' ), 10, 2 );

		// Remove Plugin data from Group Settings if Plugin isn't active on Generation.
		add_filter( 'page_generator_pro_groups_get_settings_remove_orphaned_settings', array( $this, 'remove_orphaned_settings' ) );

	}

	/**
	 * Defines available content overwrite sections.
	 *
	 * @since   3.3.9
	 *
	 * @param   array $sections    Content Overwrite Sections.
	 * @return  array                Content Overwrite Sections
	 */
	public function add_overwrite_section( $sections ) {

		// Bail if GoodLayers Page Options isn't active.
		if ( ! $this->is_active() ) {
			return $sections;
		}

		// Add GoodLayers Page Options.
		$sections[ $this->overwrite_section ] = __( 'GoodLayers: Page Options', 'page-generator-pro' );

		// Return.
		return $sections;

	}

	/**
	 * Removes orphaned GoodLayers metadata in the Group Settings during Generation,
	 * if GoodLayers is not active
	 *
	 * @since   3.3.9
	 *
	 * @param   array $settings   Group Settings.
	 * @return  array               Group Settings
	 */
	public function remove_orphaned_settings( $settings ) {

		// Don't remove settings if GoodLayers is active.
		if ( $this->is_active() ) {
			return $settings;
		}

		// Remove GoodLayers Meta Keys from the Group Settings during Generation.
		// We don't read $this->meta_keys as that contains Meta Keys for the Page Builder, not both the Page Builder and Page Options
		// - and we want to remove both.
		return $this->remove_orphaned_settings_metadata(
			$settings,
			array(
				'/^gdlr-(.*)/i',
				'/^gdlr_(.*)/i',
			)
		);

	}

}
