<?php
/**
 * Platinum SEO Integration Class
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Registers Platinum SEO as a Plugin integration:
 * - Register as an overwrite section in Content Groups
 * - Copy / don't copy metadata to generated Pages, depending on if the integration is active
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 3.2.5
 */
class Page_Generator_Pro_Platinum_SEO extends Page_Generator_Pro_Integration {

	/**
	 * Holds the base object.
	 *
	 * @since   3.2.5
	 *
	 * @var     object
	 */
	public $base;

	/**
	 * Constructor
	 *
	 * @since   3.2.5
	 *
	 * @param   object $base    Base Plugin Class.
	 */
	public function __construct( $base ) {

		// Store base class.
		$this->base = $base;

		// Set Plugin.
		$this->plugin_folder_filename = 'platinum-seo-pack/platinum-seo-pack.php';

		// Set Meta Keys used by this Plugin.
		$this->meta_keys = array(
			'/^_techblissonline_psp_(.*)/i',
		);

		// Set Overwrite Setting's Key used by this Plugin.
		$this->overwrite_section = 'platinum_seo';

		// Add Overwrite Section if Platinum SEO enabled.
		add_filter( 'page_generator_pro_common_get_content_overwrite_sections', array( $this, 'add_overwrite_section' ) );

		// Ignore Platinum SEO meta keys if overwriting is disabled for Platinum SEO.
		add_filter( 'page_generator_pro_generate_set_post_meta_ignored_keys', array( $this, 'prevent_post_meta_copy_to_generated_content' ), 10, 4 );

		// Remove Plugin data from Group Settings if Plugin isn't active on Generation.
		add_filter( 'page_generator_pro_groups_get_settings_remove_orphaned_settings', array( $this, 'remove_orphaned_settings' ) );

		// Content Groups: Fetch Post Meta, which is stored in Platinum SEO's own meta table, not wp_postmeta.
		add_filter( 'page_generator_pro_groups_get_post_meta', array( $this, 'groups_content_get_post_meta' ), 10, 2 );

		// Content Groups: Set Post Meta in Platinum SEO's own meta table.
		add_action( 'page_generator_pro_generate_set_post_meta', array( $this, 'groups_content_set_post_meta' ), 10, 5 );

		// Term Groups: Change Term Meta Key Names.
		add_action( 'page_generator_pro_generate_set_term_meta', array( $this, 'groups_terms_set_term_meta' ), 10, 4 );

	}

	/**
	 * Defines available content overwrite sections.
	 *
	 * @since   3.2.5
	 *
	 * @param   array $sections    Content Overwrite Sections.
	 * @return  array                Content Overwrite Sections
	 */
	public function add_overwrite_section( $sections ) {

		// Bail if Platinum SEO isn't active.
		if ( ! $this->is_active() ) {
			return $sections;
		}

		// Add Platinum SEO.
		$sections[ $this->overwrite_section ] = __( 'Platinum SEO', 'page-generator-pro' );

		// Return.
		return $sections;

	}

	/**
	 * Removes orphaned Platinum SEO metadata in the Group Settings during Generation,
	 * if Platinum SEO is not active
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

		// Remove Platinum SEO Meta Keys from the Group Settings during Generation.
		return $this->remove_orphaned_settings_metadata( $settings, $this->meta_keys );

	}

	/**
	 * When fetching a Content Group's metadata, Platinum SEO data won't be included, as it's stored
	 * in its own metadata table, not wp_postmeta.
	 *
	 * This function fetches the Content Group's Platinum SEO metadata, so it's available to the Content Group.
	 *
	 * @since   3.2.5
	 *
	 * @param   array $meta   Metadata.
	 * @param   int   $id     Group ID.
	 * @return  array   $meta   Metadata
	 */
	public function groups_content_get_post_meta( $meta, $id ) {

		// Bail if Plugin isn't active.
		if ( ! $this->is_active() ) {
			return $meta;
		}

		// Fetch Platinum SEO Group Meta.
		$platinum_seo_post_meta = get_metadata( 'platinumseo', $id, '', false );

		// Bail if no SEO Metadata.
		if ( empty( $platinum_seo_post_meta ) ) {
			return $meta;
		}

		// Return.
		return array_merge( $meta, $platinum_seo_post_meta );

	}

	/**
	 * Saves Post Meta for the Generated Post in Platinum SEO's Meta table, as the Post Meta table isn't used
	 *
	 * @since   3.2.5
	 *
	 * @param   int   $post_id        Generated Page ID.
	 * @param   int   $group_id       Group ID.
	 * @param   array $post_meta      Group Post Meta.
	 * @param   array $settings       Group Settings.
	 * @param   array $post_args      wp_insert_post() / wp_update_post() arguments.
	 */
	public function groups_content_set_post_meta( $post_id, $group_id, $post_meta, $settings, $post_args ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter

		// Bail if Plugin isn't active.
		if ( ! $this->is_active() ) {
			return;
		}

		// Bail if no Post Meta.
		if ( empty( $post_meta ) ) {
			return;
		}

		// Build array of Platinum SEO Metadata from Post Meta.
		$platinum_seo_post_meta = array();
		foreach ( $post_meta as $key => $value ) {
			if ( strpos( $key, '_techblissonline_' ) === false ) {
				continue;
			}

			// Delete metadata for this Key on the Generated Page.
			delete_metadata( 'platinumseo', $post_id, $key );

			// Update metadata for this Key on the Generated Page.
			update_metadata( 'platinumseo', $post_id, $key, $value[0] );
		}

	}

	/**
	 * When fetching a Term Group's metadata, Platinum SEO data won't be included, as it's stored
	 * in its own metadata table, not wp_termmeta.
	 *
	 * This function fetches the Term Group's Platinum SEO metadata, so it's available to the Term Group.
	 *
	 * @since   3.2.5
	 *
	 * @param   array $meta   Metadata.
	 * @param   int   $id     Group ID.
	 * @return  array   $meta   Metadata
	 */
	public function groups_terms_get_term_meta( $meta, $id ) {

		// Bail if Plugin isn't active.
		if ( ! $this->is_active() ) {
			return $meta;
		}

		// Fetch Platinum SEO Group Meta.
		$platinum_seo_term_meta = get_metadata( 'platinumseo', $id, '', false );

		// Bail if no SEO Metadata.
		if ( empty( $platinum_seo_term_meta ) ) {
			return $meta;
		}

		// Return.
		return array_merge( $meta, $platinum_seo_term_meta );

	}

	/**
	 * Changes the Post ID appended to Meta Keys from the Term Group to the Term ID, for example
	 * psp_taxonomy_seo_metas_GROUPID --> psp_taxonomy_seo_metas_TERMID -->
	 *
	 * @since   3.2.5
	 *
	 * @param   int   $term_id        Generated Term ID.
	 * @param   int   $group_id       Group ID.
	 * @param   array $term_meta      Group Term Meta.
	 * @param   array $settings       Group Settings.
	 */
	public function groups_terms_set_term_meta( $term_id, $group_id, $term_meta, $settings ) {

		// Bail if Plugin isn't active.
		if ( ! $this->is_active() ) {
			return;
		}

		foreach ( $term_meta as $key => $value ) {
			// Skip if the meta key isn't for Platinum SEO.
			if ( strpos( $key, 'psp_taxonomy_' ) === false ) {
				continue;
			}

			// Define valid key name based on the Taxonomy and Term ID.
			$valid_key = str_replace( '_' . $group_id, '_' . $term_id, $key );

			// Categories use psp_category_*; other Taxonomies use psp_taxonomy_*.
			if ( $settings['taxonomy'] === 'category' ) {
				$valid_key = str_replace( '_taxonomy_', '_' . $settings['taxonomy'] . '_', $valid_key );
			}

			// Delete meta key/value, as it ends with the Group ID, not the Term ID.
			delete_term_meta( $term_id, $key );

			// Add meta key/value with the key ending with the Term ID, not the Group ID.
			update_term_meta( $term_id, $valid_key, $value );
		}

	}

}
