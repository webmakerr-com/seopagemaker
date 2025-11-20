<?php
/**
 * All in one SEO Integration Class
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
 * @version 2.9.0
 */
class Page_Generator_Pro_AIOSEO extends Page_Generator_Pro_Integration {

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
			'all-in-one-seo-pack/all_in_one_seo_pack.php',
			'all-in-one-seo-pack-pro/all_in_one_seo_pack.php',
		);

		// Set Meta Keys used by this Plugin.
		$this->meta_keys = array(
			'aioseo',
			'/^_aioseo_(.*)/i',
			'/^_aioseop_(.*)/i',
		);

		// Set Overwrite Setting's Key used by this Plugin.
		$this->overwrite_section = 'aioseo';

		// Content Groups: Populate Post Meta from wp_aioseo_posts instead of Content Group's Post Meta.
		add_filter( 'page_generator_pro_groups_get_post_meta', array( $this, 'get_post_meta' ), 10, 2 );

		// Remove Plugin data from Group Settings if Plugin isn't active on Generation.
		add_filter( 'page_generator_pro_groups_get_settings_remove_orphaned_settings', array( $this, 'remove_orphaned_settings' ) );

		// Add Overwrite Section if All in One SEO enabled.
		add_filter( 'page_generator_pro_common_get_content_overwrite_sections', array( $this, 'add_overwrite_section' ) );

		// Ignore All in One SEO meta keys if overwriting is disabled for All in One SEO.
		add_filter( 'page_generator_pro_generate_set_post_meta_ignored_keys', array( $this, 'prevent_post_meta_copy_to_generated_content' ), 10, 4 );

	}

	/**
	 * Adds data stored in the wp_aioseo_posts table to the Content Group's Post Meta array,
	 * so that all AIOSEO metadata is processed and copied to Generated Pages.
	 *
	 * @since   3.4.3
	 *
	 * @param   array $meta     Post Meta.
	 * @param   int   $post_id  Group ID.
	 * @return  array           Post Meta
	 */
	public function get_post_meta( $meta, $post_id ) {

		// Bail if All in One SEO isn't active.
		if ( ! $this->is_active() ) {
			return $meta;
		}

		// Bail if AIOSEO v4 doesn't exist.
		if ( ! class_exists( 'AIOSEO\Plugin\Common\Models\Post' ) ) {
			return $meta;
		}

		// Get wp_aioseo_posts data.
		$data = AIOSEO\Plugin\Common\Models\Post::getPost( $post_id );

		// Define some AIOSEO keys to ignore.
		$ignored_keys = array(
			'id',
			'post_id',
			'page_analysis',
			'created',
			'updated',
		);

		// Assign to Meta Key.
		$meta['aioseo'] = array();
		foreach ( $data as $key => $value ) { // @phpstan-ignore-line
			// Skip some keys.
			if ( in_array( $key, $ignored_keys, true ) ) {
				continue;
			}

			// JSON decode JSON strings now, so they don't end up slashed and breaking AIOSEO on save.
			// stdClass objects are encoded and decoded to return them as arrays, otherwise
			// things break in AIOSEO Pro 4.6.9+.
			switch ( $key ) {
				case 'keyphrases':
				case 'schema':
				case 'schema_type_options':
				case 'options':
				case 'open_ai':
				case 'tabs':
					if ( is_string( $value ) ) {
						$value = json_decode( $value, true );
					}

					if ( is_object( $value ) ) {
						$value = json_decode( wp_json_encode( $value ), true );
					}
					break;
			}

			$meta['aioseo'][ $key ] = $value;
		}

		return $meta;

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

		// Bail if All in One SEO isn't active.
		if ( ! $this->is_active() ) {
			return $sections;
		}

		// Add All in One SEO.
		$sections[ $this->overwrite_section ] = __( 'All in One SEO', 'page-generator-pro' );

		// Return.
		return $sections;

	}

	/**
	 * Adds All in One SEO Post Meta Keys to the array of excluded Post Meta Keys if All in One SEO
	 * metadata should not be overwritten on Content Generation
	 *
	 * @since   2.9.0
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
			// Overwrite in AIOSEO DB for each Generated Page.
			add_action( 'page_generator_pro_generate_content_finished', array( $this, 'update_aioseo_post_table' ), 10, 5 );

			// Return.
			return $ignored_keys;
		}

		// If no meta keys are set by this integration, no need to exclude anything.
		if ( ! is_array( $this->meta_keys ) ) {
			return $ignored_keys;
		}

		// Add AIOSEO SEO Meta Keys so they are not overwritten on the Generated Post.
		return array_merge( $ignored_keys, $this->meta_keys );

	}

	/**
	 * Removes orphaned AIOSEO metadata in the Group Settings during Generation,
	 * if AIOSEO is not active
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

		// Remove All in One SEO Meta Keys from the Group Settings during Generation.
		return $this->remove_orphaned_settings_metadata( $settings, $this->meta_keys );

	}

	/**
	 * Update AIOSEO DB table values for the Generated Post
	 *
	 * @since   2.9.5
	 *
	 * @param   int   $post_id        Post ID.
	 * @param   int   $group_id       Group ID.
	 * @param   array $settings       Group Settings.
	 * @param   int   $index          Keyword Index.
	 * @param   bool  $test_mode      Test Mode.
	 */
	public function update_aioseo_post_table( $post_id, $group_id, $settings, $index, $test_mode ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter

		// Bail if AIOSEO v4 doesn't exist.
		if ( ! class_exists( 'AIOSEO\Plugin\Common\Models\Post' ) ) {
			return;
		}

		// Bail if our AIOSEO Post Meta array doesn't exist, as this means the get_post_meta()
		// function in this class didn't populate the Content Group metadata from the AIOSEO DB table .
		if ( ! isset( $settings['post_meta']['aioseo'] ) ) {
			return;
		}

		// Build AIOSEO Post Table data.
		$data = array_merge(
			$settings['post_meta']['aioseo'],
			array(
				'id'      => $post_id,
				'context' => 'post',
			)
		);

		// Update.
		AIOSEO\Plugin\Common\Models\Post::savePost( $post_id, $data );

	}

}
