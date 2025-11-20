<?php
/**
 * Yoast SEO Integration Class
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Registers Yoast SEO as a Plugin integration:
 * - Register as an overwrite section in Content Groups
 * - Copy / don't copy metadata to generated Pages, depending on if the integration is active
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 2.9.0
 */
class Page_Generator_Pro_Yoast_SEO extends Page_Generator_Pro_Integration {

	/**
	 * Holds the base object.
	 *
	 * @since   2.9.0
	 *
	 * @var     object
	 */
	public $base;

	/**
	 * Constructor
	 *
	 * @since   2.9.0
	 *
	 * @param   object $base    Base Plugin Class.
	 */
	public function __construct( $base ) {

		// Store base class.
		$this->base = $base;

		// Set Plugin.
		$this->plugin_folder_filename = array(
			'wordpress-seo-premium/wp-seo-premium.php',
			'wordpress-seo/wp-seo.php',
		);

		// Set Meta Keys used by this Plugin.
		$this->meta_keys = array(
			'/^_yoast_wpseo_(.*)/i',
		);

		// Set Overwrite Setting's Key used by this Plugin.
		$this->overwrite_section = 'yoast_seo';

		// Prevent sanitization of some Post Meta that would strip brackets and braces.
		add_filter( 'wpseo_sanitize_post_meta__yoast_wpseo_canonical', array( $this, 'wpseo_sanitize_post_meta__yoast_wpseo_canonical' ), 10, 2 );

		// Content Groups: Add Overwrite Section if Yoast SEO enabled.
		add_filter( 'page_generator_pro_common_get_content_overwrite_sections', array( $this, 'add_overwrite_section' ) );

		// Content Groups: Ignore Yoast SEO meta keys if overwriting is disabled for Yoast SEO.
		add_filter( 'page_generator_pro_generate_set_post_meta_ignored_keys', array( $this, 'prevent_post_meta_copy_to_generated_content' ), 10, 4 );

		// Remove Plugin data from Group Settings if Plugin isn't active on Generation.
		add_filter( 'page_generator_pro_groups_get_settings_remove_orphaned_settings', array( $this, 'remove_orphaned_settings' ) );

		// Term Groups: Fetch Term Meta, which is stored in wp_options, not wp_termmeta.
		add_filter( 'page_generator_pro_groups_terms_get_term_meta', array( $this, 'groups_terms_get_term_meta' ), 10, 2 );

		// Term Groups: Ignore Yoast SEO meta keys.
		add_filter( 'page_generator_pro_generate_set_term_meta_ignored_keys', array( $this, 'prevent_term_meta_copy_to_generated_term' ), 10, 1 );

		// Term Groups: Set Term Meta in Options, as that's where Yoast SEO stores it.
		add_action( 'page_generator_pro_generate_set_term_meta', array( $this, 'groups_terms_set_term_meta' ), 10, 5 );

	}

	/**
	 * When saving the Canonical URL field, revert Yoast SEO's sanitization, which strips curly braces
	 *
	 * @since   2.9.0
	 *
	 * @param   string $clean          Sanitized Value.
	 * @param   string $meta_value     Unsanitized Value.
	 * @return  string                  Value to save
	 */
	public function wpseo_sanitize_post_meta__yoast_wpseo_canonical( $clean, $meta_value ) {

		// Bail if no Post ID set.
		if ( ! isset( $_POST['ID'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			return $clean;
		}

		// Get Post ID.
		$group_id = absint( $_POST['ID'] ); // phpcs:ignore WordPress.Security.NonceVerification

		// Bail if not a Group.
		if ( get_post_type( $group_id ) !== $this->base->get_class( 'post_type' )->post_type_name ) {
			return $clean;
		}

		// Return non-sanitized value.
		return urldecode( $meta_value );

	}

	/**
	 * Defines available content overwrite sections.
	 *
	 * @since   2.9.0
	 *
	 * @param   array $sections    Content Overwrite Sections.
	 * @return  array                Content Overwrite Sections
	 */
	public function add_overwrite_section( $sections ) {

		// Bail if Yoast SEO isn't active.
		if ( ! $this->is_active() ) {
			return $sections;
		}

		// Add Yoast SEO.
		$sections['yoast_seo'] = __( 'Yoast SEO', 'page-generator-pro' );

		// Return.
		return $sections;

	}

	/**
	 * Removes orphaned Yoast SEO metadata in the Group Settings during Generation,
	 * if Yoast SEO is not active
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

		// Remove Yoast SEO Meta Keys from the Group Settings during Generation.
		return $this->remove_orphaned_settings_metadata( $settings, $this->meta_keys );

	}

	/**
	 * When fetching a Term Group's metadata, Yoast SEO data won't be included, as it's stored
	 * in wp_options, not wp_termmeta.
	 *
	 * This function fetches the Term Group's Yoast metadata, so it's available to the Term Group.
	 *
	 * @since   3.0.0
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

		// Get Options data, which comprises of all Taxonomy Terms and their Metadata.
		$yoast_taxonomy_meta = get_option( 'wpseo_taxonomy_meta' );

		// Bail if not an array.
		if ( ! is_array( $yoast_taxonomy_meta ) ) {
			return $meta;
		}
		if ( ! count( $yoast_taxonomy_meta ) ) {
			return $meta;
		}

		// Bail if there's no Term Meta for any Term Group.
		if ( ! isset( $yoast_taxonomy_meta['page-generator-tax'] ) ) {
			return $meta;
		}

		// Bail if there's no Term Meta for this Term Group.
		if ( ! isset( $yoast_taxonomy_meta['page-generator-tax'][ $id ] ) ) {
			return $meta;
		}

		// If here, we have Term Meta for this Term Group.
		// Return it.
		return array_merge( $meta, $yoast_taxonomy_meta['page-generator-tax'][ $id ] );

	}

	/**
	 * Adds Yoast SEO Term Meta Keys to the array of excluded Term Meta Keys if Yoast SEO
	 * metadata should not be overwritten on Content Generation
	 *
	 * @since   3.0.0
	 *
	 * @param   array $ignored_keys   Ignored Keys (preg_match() compatible regex expressions are supported).
	 * @return  array   $ignored_keys   Ignored Keys
	 */
	public function prevent_term_meta_copy_to_generated_term( $ignored_keys ) {

		// Add Yoast SEO Meta Keys, as Yoast stores Term Meta in the options table, not the term meta table.
		return array_merge( $ignored_keys, $this->meta_keys );

	}

	/**
	 * Saves Term Meta for the Generated Term in the options table, as Yoast SEO requires it to be stored
	 * there for Terms, instead of the term meta table.
	 *
	 * @since   3.0.0
	 *
	 * @param   int   $term_id        Generated Term ID.
	 * @param   int   $group_id       Group ID.
	 * @param   array $term_meta      Group Term Meta.
	 * @param   array $settings       Group Settings.
	 * @param   array $term_args      wp_insert_term() / wp_update_term() arguments.
	 */
	public function groups_terms_set_term_meta( $term_id, $group_id, $term_meta, $settings, $term_args ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter

		// Bail if Plugin isn't active.
		if ( ! $this->is_active() ) {
			return;
		}

		// Iterate through meta, building the meta to store in the options array.
		$options_term_meta = array();
		foreach ( $term_meta as $meta_key => $meta_value ) {
			// Skip if not a Yoast Meta Key.
			if ( strpos( $meta_key, 'wpseo_' ) ) {
				continue;
			}

			$options_term_meta[ $meta_key ] = $meta_value;
		}

		// If no Term Meta exists, bail.
		if ( empty( $options_term_meta ) ) {
			return;
		}

		// Get Options data, which comprises of all Taxonomy Terms and their Metadata.
		$yoast_taxonomy_meta = get_option( 'wpseo_taxonomy_meta' );
		if ( ! is_array( $yoast_taxonomy_meta ) ) {
			$yoast_taxonomy_meta = array();
		}
		if ( ! isset( $yoast_taxonomy_meta[ $settings['taxonomy'] ] ) ) {
			$yoast_taxonomy_meta[ $settings['taxonomy'] ] = array();
		}

		// Overwrite existing Term Meta if it exists.
		$yoast_taxonomy_meta[ $settings['taxonomy'] ][ $term_id ] = $options_term_meta;
		update_option( 'wpseo_taxonomy_meta', $yoast_taxonomy_meta );

		// The wp_yoast_indexable table will now have a record for this Term, which
		// needs its Title and Description updating if a custom Title and/or Description
		// has been specified in this Term Group.
		global $wpdb;
		$wpdb->update(
			$wpdb->prefix . 'yoast_indexable',
			array(
				'title'                  => ( isset( $options_term_meta['wpseo_title'] ) ? $options_term_meta['wpseo_title'] : null ),
				'description'            => ( isset( $options_term_meta['wpseo_desc'] ) ? $options_term_meta['wpseo_desc'] : null ),
				'primary_focus_keyword'  => ( isset( $options_term_meta['wpseo_focuskw'] ) ? $options_term_meta['wpseo_focuskw'] : null ),
				'canonical'              => ( isset( $options_term_meta['wpseo_canonical'] ) ? $options_term_meta['wpseo_canonical'] : null ),
				'is_cornerstone'         => ( isset( $options_term_meta['wpseo_is_cornerstone'] ) ? absint( $options_term_meta['wpseo_is_cornerstone'] ) : 0 ),
				'is_robots_noindex'      => ( isset( $options_term_meta['wpseo_noindex'] ) ? $options_term_meta['wpseo_noindex'] : null ),

				'twitter_title'          => ( isset( $options_term_meta['wpseo_twitter-title'] ) ? $options_term_meta['wpseo_twitter-title'] : null ),
				'twitter_image'          => ( isset( $options_term_meta['wpseo_twitter-image'] ) ? $options_term_meta['wpseo_twitter-image'] : null ),
				'twitter_description'    => ( isset( $options_term_meta['wpseo_twitter-description'] ) ? $options_term_meta['wpseo_twitter-description'] : null ),
				'twitter_image_id'       => ( isset( $options_term_meta['wpseo_twitter-image-id'] ) ? $options_term_meta['wpseo_twitter-image-id'] : null ),

				'open_graph_title'       => ( isset( $options_term_meta['wpseo_opengraph-title'] ) ? $options_term_meta['wpseo_opengraph-title'] : null ),
				'open_graph_description' => ( isset( $options_term_meta['wpseo_opengraph-description'] ) ? $options_term_meta['wpseo_opengraph-description'] : null ),
				'open_graph_image'       => ( isset( $options_term_meta['wpseo_opengraph-image'] ) ? $options_term_meta['wpseo_opengraph-image'] : null ),
				'open_graph_image_id'    => ( isset( $options_term_meta['wpseo_opengraph-image-id'] ) ? $options_term_meta['wpseo_opengraph-image-id'] : null ),
			),
			array(
				'object_id'       => $term_id,
				'object_type'     => 'term',
				'object_sub_type' => $settings['taxonomy'],
			)
		);

	}

}
