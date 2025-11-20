<?php
/**
 * SiteOrigin Integration Class
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Registers SiteOrigin Page Builder as a Plugin integration:
 * - Enable Page Builder on Content Groups
 * - Copy / don't copy metadata to generated Pages, depending on if the integration is active
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 3.3.7
 */
class Page_Generator_Pro_SiteOrigin extends Page_Generator_Pro_Integration {

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

		// Set Plugin.
		$this->plugin_folder_filename = array(
			'siteorigin-panels/siteorigin-panels.php',
		);

		// Set Meta Keys used by this Plugin.
		$this->meta_keys = array(
			'panels_data',
		);

		add_filter( 'siteorigin_panels_settings_defaults', array( $this, 'register_siteorigin_page_builder_support' ) );
		add_filter( 'page_generator_pro_screen_get_current_screen', array( $this, 'siteorigin_page_builder_set_current_screen' ), 10, 1 );

		// Remove Page Builder data from Group Settings if overwriting content is disabled, and an existing generated page already exists.
		add_filter( 'page_generator_pro_generate_remove_content_from_settings_ignored_keys', array( $this, 'remove_post_meta_from_content_group' ), 10, 2 );

		// Remove Plugin data from Group Settings if Plugin isn't active on Generation.
		add_filter( 'page_generator_pro_groups_get_settings_remove_orphaned_settings', array( $this, 'remove_orphaned_settings' ) );

	}

	/**
	 * Allows SiteOrigin Page Builder to inject its Page Builder into Page Generator Pro's Groups.
	 *
	 * @since   2.0.1
	 *
	 * @param   array $default_settings   Default Settings.
	 */
	public function register_siteorigin_page_builder_support( $default_settings ) {

		$default_settings['post-types'][] = 'page-generator-pro';
		return $default_settings;

	}

	/**
	 * Tells the Screen class that we're editing a Content Group when editing it with SiteOrigin's Page Builder
	 * and we've clicked an element to edit it, which fires an AJAX call.
	 *
	 * @since   2.5.8
	 *
	 * @param   array $result     Screen and Section.
	 * @return  array                   Screen and Section
	 */
	public function siteorigin_page_builder_set_current_screen( $result ) {

		global $post;

		// Bail if this isn't an AJAX request.
		if ( ! defined( 'DOING_AJAX' ) ) {
			return $result;
		}
		if ( ! DOING_AJAX ) {
			return $result;
		}

		// Bail if this isn't a SiteOrigins Page Builder request.
		if ( ! isset( $_REQUEST['action'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			return $result;
		}
		if ( sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) !== 'so_panels_widget_form' ) { // phpcs:ignore WordPress.Security.NonceVerification
			return $result;
		}

		// Bail if we can't get the calling URL.
		$referer_url = wp_get_referer();
		if ( ! $referer_url ) {
			return $result;
		}

		// Parse referer URL.
		parse_str( wp_parse_url( $referer_url, PHP_URL_QUERY ), $referrer );

		// Check if we're editing a Content Group.
		if ( ! isset( $referrer['post'] ) ) {
			return $result;
		}
		if ( $this->base->plugin->name !== get_post_type( absint( $referrer['post'] ) ) ) {
			return $result;
		}

		// Return a modified screen array to tell the Screen class that we're editing a Content Group.
		return array(
			'screen'  => 'content_groups',
			'section' => 'edit',
		);

	}

	/**
	 * Removes orphaned SiteOrigin metadata in the Group Settings during Generation,
	 * if SiteOrigin is not active.
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

		// Remove SiteOrigin Meta Keys from the Group Settings during Generation.
		return $this->remove_orphaned_settings_metadata( $settings, $this->meta_keys );

	}

}
