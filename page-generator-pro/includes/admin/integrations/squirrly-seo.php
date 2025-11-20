<?php
/**
 * Squirrly SEO Integration Class
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Registers All in One SEO as a Plugin integration:
 * - Register as an overwrite section in Content Groups
 * - Copy / don't copy metadata to generated Pages, depending on if the integration is active
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 3.9.5
 */
class Page_Generator_Pro_Squirrly_SEO extends Page_Generator_Pro_Integration {

	/**
	 * Holds the base object.
	 *
	 * @since   3.9.5
	 *
	 * @var     object
	 */
	public $base;

	/**
	 * Constructor
	 *
	 * @since   3.9.5
	 *
	 * @param   object $base    Base Plugin Class.
	 */
	public function __construct( $base ) {

		// Store base class.
		$this->base = $base;

		// Set Plugin.
		$this->plugin_folder_filename = array(
			'squirrly-seo/squirrly.php',
		);

		// Set Meta Keys used by this Plugin.
		$this->meta_keys = array(
			'squirrly_seo',
		);

		// Set Overwrite Setting's Key used by this Plugin.
		$this->overwrite_section = 'squirrly_seo';

		// Content Groups: Populate Post Meta from wp_qss instead of Content Group's Post Meta.
		add_filter( 'page_generator_pro_groups_get_post_meta', array( $this, 'get_post_meta' ), 10, 2 );

		// Remove Plugin data from Group Settings if Plugin isn't active on Generation.
		add_filter( 'page_generator_pro_groups_get_settings_remove_orphaned_settings', array( $this, 'remove_orphaned_settings' ) );

		// Add Overwrite Section if Squirrly SEO enabled.
		add_filter( 'page_generator_pro_common_get_content_overwrite_sections', array( $this, 'add_overwrite_section' ) );

		// Ignore Squirrly SEO meta keys if overwriting is disabled for Squirrly SEO.
		add_filter( 'page_generator_pro_generate_set_post_meta_ignored_keys', array( $this, 'prevent_post_meta_copy_to_generated_content' ), 10, 4 );

	}

	/**
	 * Adds data stored in the wp_qss table to the Content Group's Post Meta array,
	 * so that all Squirrly SEO metadata is processed and copied to Generated Pages.
	 *
	 * @since   3.9.5
	 *
	 * @param   array $meta     Post Meta.
	 * @param   int   $post_id  Group ID.
	 * @return  array           Post Meta
	 */
	public function get_post_meta( $meta, $post_id ) {

		global $wpdb;

		// Bail if Squirrly SEO isn't active.
		if ( ! $this->is_active() ) {
			return $meta;
		}

		// Bail if the expected database constant isn't defined.
		if ( ! defined( '_SQ_DB_' ) ) {
			return $meta;
		}

		// Get wp_qss data.
		$post = SQ_Classes_ObjController::getClass( 'SQ_Models_Snippet' )->getCurrentSnippet( $post_id, 0, '', 'page-generator-pro' ); // @phpstan-ignore-line
		$row  = $wpdb->get_row(
			$wpdb->prepare(
				'SELECT seo FROM `' . $wpdb->prefix . _SQ_DB_ . '` WHERE blog_id = %d AND url_hash = %s', // phpcs:ignore WordPress.DB.PreparedSQL
				get_current_blog_id(),
				$post->hash
			),
			ARRAY_A
		);

		// Bail if no SEO data exists.
		if ( ! $row ) {
			return $meta;
		}

		// Unserialize SEO data.
		$meta['squirrly_seo'] = maybe_unserialize( $row['seo'] );

		// Return.
		return $meta;

	}

	/**
	 * Defines available content overwrite sections.
	 *
	 * @since   3.9.5
	 *
	 * @param   array $sections    Content Overwrite Sections.
	 * @return  array                Content Overwrite Sections
	 */
	public function add_overwrite_section( $sections ) {

		// Bail if Squirrly SEO isn't active.
		if ( ! $this->is_active() ) {
			return $sections;
		}

		// Add Squirrly SEO.
		$sections[ $this->overwrite_section ] = __( 'Squirrly SEO', 'page-generator-pro' );

		// Return.
		return $sections;

	}

	/**
	 * Adds Squirrly SEO Post Meta Keys to the array of excluded Post Meta Keys if Squirrly SEO
	 * metadata should not be overwritten on Content Generation
	 *
	 * @since   3.9.5
	 *
	 * @param   array $ignored_keys   Ignored Keys (preg_match() compatible regex expressions are supported).
	 * @param   int   $post_id        Generated Post ID.
	 * @param   array $settings       Group Settings.
	 * @param   array $post_args      wp_insert_post() / wp_update_post() arguments.
	 * @return  array   $ignored_keys   Ignored Keys
	 */
	public function prevent_post_meta_copy_to_generated_content( $ignored_keys, $post_id, $settings, $post_args ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter

		// Determine if we want to create/replace All in One SEO metdata.
		$overwrite = ( isset( $post_args['ID'] ) && ! array_key_exists( $this->overwrite_section, $settings['overwrite_sections'] ) ? false : true );

		// If overwriting is enabled, no need to exclude anything.
		if ( $overwrite ) {
			// Overwrite in Squirrly SEO DB table for each Generated Page.
			add_action( 'page_generator_pro_generate_content_finished', array( $this, 'update_squirrly_seo_table' ), 10, 5 );

			// Return.
			return $ignored_keys;
		}

		// If no meta keys are set by this integration, no need to exclude anything.
		if ( ! is_array( $this->meta_keys ) ) {
			return $ignored_keys;
		}

		// Add Squirrly SEO Meta Keys so they are not overwritten on the Generated Post.
		return array_merge( $ignored_keys, $this->meta_keys );

	}

	/**
	 * Removes orphaned Squirrly SEO metadata in the Group Settings during Generation,
	 * if Squirrly SEO is not active
	 *
	 * @since   3.9.5
	 *
	 * @param   array $settings   Group Settings.
	 * @return  array               Group Settings
	 */
	public function remove_orphaned_settings( $settings ) {

		// Don't remove settings if the Plugin is active.
		if ( $this->is_active() ) {
			return $settings;
		}

		// Remove Squirrly SEO Meta Keys from the Group Settings during Generation.
		return $this->remove_orphaned_settings_metadata( $settings, $this->meta_keys );

	}

	/**
	 * Update Squirrly SEO DB table values for the Generated Post
	 *
	 * @since   3.9.5
	 *
	 * @param   int   $post_id        Post ID.
	 * @param   int   $group_id       Group ID.
	 * @param   array $settings       Group Settings.
	 * @param   int   $index          Keyword Index.
	 * @param   bool  $test_mode      Test Mode.
	 */
	public function update_squirrly_seo_table( $post_id, $group_id, $settings, $index, $test_mode ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter

		// Bail if Squirrly SEO object controller doesn't exist.
		if ( ! class_exists( 'SQ_Classes_ObjController' ) ) {
			return;
		}

		// Bail if our Squirrly SEO Post Meta array doesn't exist, as this means the get_post_meta()
		// function in this class didn't populate the Content Group metadata from the Squirrly SEO DB table.
		if ( ! isset( $settings['post_meta']['squirrly_seo'] ) ) {
			return;
		}

		// Get Post from Squirrly SEO.
		$post = SQ_Classes_ObjController::getClass( 'SQ_Models_Snippet' )->getCurrentSnippet( $post_id, 0, '', $settings['type'] ); // @phpstan-ignore-line

		// Save the post data in DB with the hash.
		SQ_Classes_ObjController::getClass( 'SQ_Models_Qss' )->saveSqSEO( // @phpstan-ignore-line
			$post->url,
			$post->hash,
			maybe_serialize(
				array(
					'ID'        => (int) $post_id,
					'post_type' => $settings['type'],
					'term_id'   => 0,
					'taxonomy'  => '',
				)
			),
			maybe_serialize( $settings['post_meta']['squirrly_seo'] ),
			gmdate( 'Y-m-d H:i:s' )
		);

	}

}
