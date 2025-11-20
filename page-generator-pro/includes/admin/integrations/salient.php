<?php
/**
 * Salient Theme (Nectar) Integration Class
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Registers Salient Theme as a Plugin integration:
 * - Registers metaboxes in Content Groups
 * - Copy / don't copy metadata to generated Pages, depending on if the integration is active
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 3.3.7
 */
class Page_Generator_Pro_Salient extends Page_Generator_Pro_Integration {

	/**
	 * Holds the base object.
	 *
	 * @since   3.3.7
	 *
	 * @var     object
	 */
	public $base;

	/**
	 * Constructor
	 *
	 * @since   3.3.7
	 *
	 * @param   object $base    Base Plugin Class.
	 */
	public function __construct( $base ) {

		// Store base class.
		$this->base = $base;

		// Set Theme Name.
		$this->theme_name = 'Salient';

		// Set Meta Keys used by this Plugin.
		$this->meta_keys = array(
			'/^_nectar_(.*)/i',
		);

		add_action( 'init', array( $this, 'register_salient_support' ) );

		// Remove Plugin data from Group Settings if Plugin isn't active on Generation.
		add_filter( 'page_generator_pro_groups_get_settings_remove_orphaned_settings', array( $this, 'remove_orphaned_settings' ) );

	}

	/**
	 * Calls Salient's nectar_metabox_page() function, which registers Salient Page Meta Boxes
	 * when creating or editing a Content Group.
	 *
	 * These are then copied to the Page Generator Pro Post Type in $wp_meta_boxes.
	 *
	 * @since   1.8.7
	 */
	public function register_salient_support() {

		// Bail if Salient isn't active.
		if ( ! $this->is_theme_active() ) {
			return;
		}

		// Register Salient Metaboxes on Content Groups.
		add_filter( 'nectar_metabox_post_types_navigation_transparency', array( $this, 'register_salient_metaboxes' ) );
		add_filter( 'nectar_metabox_post_types_fullscreen_rows', array( $this, 'register_salient_metaboxes' ) );
		add_filter( 'nectar_metabox_post_types_page_header', array( $this, 'register_salient_metaboxes' ) );

		// Remove data from Group Settings if Theme isn't active on Generation.
		add_filter( 'page_generator_pro_groups_get_settings_remove_orphaned_settings', array( $this, 'remove_orphaned_settings' ) );

	}

	/**
	 * Register Salient's Metaboxes on Content Groups
	 *
	 * @since   1.8.7
	 *
	 * @param   array $post_types     Post Types to register Metaboxes for.
	 */
	public function register_salient_metaboxes( $post_types ) {

		$post_types[] = 'page-generator-pro';
		return $post_types;

	}

	/**
	 * Removes orphaned XX metadata in the Group Settings during Generation,
	 * if XX is not active
	 *
	 * @since   3.3.7
	 *
	 * @param   array $settings   Group Settings.
	 * @return  array               Group Settings
	 */
	public function remove_orphaned_settings( $settings ) {

		// Don't remove settings if the Plugin is active.
		if ( $this->is_theme_active() ) {
			return $settings;
		}

		// Remove XX Meta Keys from the Group Settings during Generation.
		return $this->remove_orphaned_settings_metadata( $settings, $this->meta_keys );

	}

}
