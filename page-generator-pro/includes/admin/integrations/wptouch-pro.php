<?php
/**
 * WP Touch Pro Integration Class
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Registers WP Touch Pro as a Plugin integration:
 * - Register metaboxes in Content Groups
 * - Register as an overwrite section in Content Groups
 * - Copy / don't copy metadata to generated Pages, depending on if the integration is active
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 3.5.0
 */
class Page_Generator_Pro_WPTouch_Pro extends Page_Generator_Pro_Integration {

	/**
	 * Holds the base object.
	 *
	 * @since   3.5.0
	 *
	 * @var     object
	 */
	public $base;

	/**
	 * Constructor
	 *
	 * @since   3.5.0
	 *
	 * @param   object $base    Base Plugin Class.
	 */
	public function __construct( $base ) {

		// Store base class.
		$this->base = $base;

		// Set Plugin.
		$this->plugin_folder_filename = array(
			'wptouch-pro/wptouch-pro.php',
		);

		// Set Meta Keys used by this Plugin.
		$this->meta_keys = array(
			'_mobile_page_template',
			'/^_wptouch_(.*)/i',
		);

		// Set Overwrite Setting's Key used by this Plugin.
		$this->overwrite_section = 'wptouch_pro';

		// Register Metaboxes.
		add_filter( 'add_meta_boxes', array( $this, 'register_mobile_page_template_meta_box' ) );
		add_filter( 'wptouch_mobile_content_post_types', array( $this, 'register_mobile_content_support' ) );

		// Content Groups: Add Overwrite Section if Plugin enabled.
		add_filter( 'page_generator_pro_common_get_content_overwrite_sections', array( $this, 'add_overwrite_section' ) );

		// Content Groups: Ignore Plugin meta keys if overwriting is disabled for Plugin.
		add_filter( 'page_generator_pro_generate_set_post_meta_ignored_keys', array( $this, 'prevent_post_meta_copy_to_generated_content' ), 10, 4 );

		// Remove Plugin data from Group Settings if Plugin isn't active on Generation.
		add_filter( 'page_generator_pro_groups_get_settings_remove_orphaned_settings', array( $this, 'remove_orphaned_settings' ) );

	}

	/**
	 * Registers the Plugin's Mobile Page Template meta box
	 * into Page Generator Pro's Groups
	 *
	 * @since   3.5.0
	 */
	public function register_mobile_page_template_meta_box() {

		if ( ! $this->is_active() ) {
			return;
		}

		add_meta_box(
			'mobile-page-template',
			__( 'Mobile Page Template', 'page-generator-pro' ),
			'wptouch_admin_render_page_template',
			'page-generator-pro',
			'side',
			'high'
		);

	}

	/**
	 * Allows the Plugin to register its Mobile Content meta box
	 * into Page Generator Pro's Groups
	 *
	 * @since   3.5.0
	 *
	 * @param   array $post_types     Post Types to register Mobile Content metabox on.
	 * @return  array                   Post Types to register Mobile Content metabox on
	 */
	public function register_mobile_content_support( $post_types ) {

		$post_types[] = 'page-generator-pro';
		return $post_types;

	}

	/**
	 * Defines available content overwrite sections.
	 *
	 * @since   3.5.0
	 *
	 * @param   array $sections    Content Overwrite Sections.
	 * @return  array                Content Overwrite Sections
	 */
	public function add_overwrite_section( $sections ) {

		// Bail if Plugin isn't active.
		if ( ! $this->is_active() ) {
			return $sections;
		}

		// Add Plugin.
		$sections[ $this->overwrite_section ] = __( 'WPTouch Pro', 'page-generator-pro' );

		// Return.
		return $sections;

	}

	/**
	 * Removes orphaned Plugin metadata in the Group Settings during Generation,
	 * if Plugin is not active
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

		// Remove Plugin Meta Keys from the Group Settings during Generation.
		return $this->remove_orphaned_settings_metadata( $settings, $this->meta_keys );

	}

}
