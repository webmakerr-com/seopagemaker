<?php
/**
 * Schema Pro Integration Class
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Registers Salient Theme as a Plugin integration:
 * - Register as an overwrite section in Content Groups
 * - Copy / don't copy metadata to generated Pages, depending on if the integration is active
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 2.9.2
 */
class Page_Generator_Pro_Schema_Pro extends Page_Generator_Pro_Integration {

	/**
	 * Holds the base object.
	 *
	 * @since   2.9.2
	 *
	 * @var     object
	 */
	public $base;

	/**
	 * Constructor
	 *
	 * @since   2.9.2
	 *
	 * @param   object $base    Base Plugin Class.
	 */
	public function __construct( $base ) {

		// Store base class.
		$this->base = $base;

		// Set Plugin Name.
		$this->plugin_folder_filename = 'wp-schema-pro/wp-schema-pro.php';

		// Set Overwrite Setting's Key used by this Plugin.
		$this->overwrite_section = 'schema_pro';

		// Add Overwrite Section if Schema Pro enabled.
		add_filter( 'page_generator_pro_common_get_content_overwrite_sections', array( $this, 'add_overwrite_section' ) );

		// Ignore Schema Pro meta keys if overwriting is disabled for Schema Pro.
		add_filter( 'page_generator_pro_generate_set_post_meta_ignored_keys', array( $this, 'prevent_post_meta_copy_to_generated_content' ), 10, 4 );

		// Remove Plugin data from Group Settings if Plugin isn't active on Generation.
		add_filter( 'page_generator_pro_groups_get_settings_remove_orphaned_settings', array( $this, 'remove_orphaned_settings' ) );

	}

	/**
	 * Defines available content overwrite sections.
	 *
	 * @since   2.9.2
	 *
	 * @param   array $sections    Content Overwrite Sections.
	 * @return  array                Content Overwrite Sections
	 */
	public function add_overwrite_section( $sections ) {

		// Bail if Schema Pro isn't active.
		if ( ! $this->is_active() ) {
			return $sections;
		}

		// Add Schema Pro.
		$sections[ $this->overwrite_section ] = __( 'Schema Pro', 'page-generator-pro' );

		// Return.
		return $sections;

	}

	/**
	 * Removes orphaned metadata in the Group Settings during Generation,
	 * if Schema Pro is not active
	 *
	 * @since   3.3.7
	 *
	 * @param   array $settings   Group Settings.
	 * @return  array               Group Settings
	 */
	public function remove_orphaned_settings( $settings ) {

		// Don't remove settings if Schema Pro is active.
		if ( $this->is_active() ) {
			return $settings;
		}

		// Don't remove settings if Schema Pro has no Fields defined.
		$fields = $this->get_schema_groups_fields();
		if ( ! $fields ) {
			return $settings;
		}

		// Remove Schema Pro Meta Keys from the Group Settings during Generation.
		return $this->remove_orphaned_settings_metadata( $settings, $fields );

	}

	/**
	 * Returns an array of Schema Group Field Meta Key Names, in regex format
	 *
	 * @since   3.3.7
	 *
	 * @return  bool|array
	 */
	private function get_schema_groups_fields() {

		// Get all Schema Groups.
		$schema_groups = new WP_Query(
			array(
				'post_type'      => 'aiosrs-schema',
				'post_status'    => 'any',
				'posts_per_page' => -1,
				'fields'         => 'ids',
			)
		);

		// Bail if no Schema Groups exist.
		if ( ! $schema_groups->post_count ) {
			return false;
		}

		// For each Schema Group, build array of fields.
		$fields = array();
		foreach ( $schema_groups->posts as $schema_group_id ) {
			$schema_type = get_post_meta( $schema_group_id, 'bsf-aiosrs-schema-type', true ); // e.g. software-application.
			$fields[]    = '/^' . $schema_type . '-' . $schema_group_id . '-(.*)/i';
		}

		return $fields;

	}

}
