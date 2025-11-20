<?php
/**
 * WPBakery Page Builder (wpbakery.com) Integration Class
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Registers WPBakery Page Builder (wpbakery.com) as a Plugin integration:
 * - Enable Page Builder on Content Groups
 * - Copy / don't copy metadata to generated Pages, depending on if the integration is active
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 3.3.7
 */
class Page_Generator_Pro_WPBakery_Page_Builder extends Page_Generator_Pro_Integration {

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
			'js_composer/js_composer.php',
		);

		// Set Meta Keys used by this Plugin.
		$this->meta_keys = array(
			// Page Builder data stored in Post Content.
			'_wpb_vc_js_status',
		);

		add_action( 'vc_before_init', array( $this, 'register_wpbakery_page_builder_support' ) );
		add_action( 'vc_before_init', array( $this, 'wpbakery_page_builder_enable_frontend' ), PHP_INT_MAX );
		add_action( 'vc_after_init', array( $this, 'wpbakery_page_builder_enable_frontend' ), PHP_INT_MAX );

		// Remove Page Builder data from Group Settings if overwriting content is disabled, and an existing generated page already exists.
		add_filter( 'page_generator_pro_generate_remove_content_from_settings_ignored_keys', array( $this, 'remove_post_meta_from_content_group' ), 10, 2 );

		// Remove Plugin data from Group Settings if Plugin isn't active on Generation.
		add_filter( 'page_generator_pro_groups_get_settings_remove_orphaned_settings', array( $this, 'remove_orphaned_settings' ) );

	}

	/**
	 * Allows WPBakery Page Builder to inject its Page Builder into Page Generator Pro's Groups,
	 * by adding the vc_access_rules_post_types/page-generator-pro Role for Administrators
	 * if we're in the WP Admin.
	 *
	 * @since   1.3.7
	 */
	public function register_wpbakery_page_builder_support() {

		// Bail if not in the WordPress Admin.
		if ( ! is_admin() ) {
			return;
		}

		// Fetch the roles that need to be granted Page Builder access.
		$roles = array(
			'administrator',
			'editor',
		);

		/**
		 * Filter the roles that need to be granted Page Builder access.
		 *
		 * @since   1.3.7
		 *
		 * @param   array   $roles  WordPress User Roles.
		 */
		$roles = apply_filters( 'page_generator_pro_pagebuilders_register_wpbakery_page_builder_support_roles', $roles );

		foreach ( (array) $roles as $role_name ) {
			// Fetch role.
			$role = get_role( $role_name );

			// Skip if we couldn't fetch this role.
			if ( is_null( $role ) ) {
				continue;
			}

			// Skip if this role already has the required capabilities.
			if ( isset( $role->capabilities['vc_access_rules_post_types/page-generator-pro'] ) &&
				isset( $role->capabilities['vc_access_rules_frontend_editor'] ) ) {
				continue;
			}

			// Add the capabilities to this role.
			// Both are required to ensure correct working functionality!
			$role->add_cap( 'vc_access_rules_post_types/page-generator-pro' );
			$role->add_cap( 'vc_access_rules_frontend_editor' );
		}

	}

	/**
	 * Stop Themes and other Plugins disabling WPBakery Page Builder on all other Post Types except their own.
	 *
	 * Ensures that the 'Edit with Visual Composer' is always available on Groups
	 *
	 * @since   1.4.5
	 */
	public function wpbakery_page_builder_enable_frontend() {

		vc_disable_frontend( false );

	}

	/**
	 * Removes orphaned Avia metadata in the Group Settings during Generation,
	 * if Avia is not active
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
