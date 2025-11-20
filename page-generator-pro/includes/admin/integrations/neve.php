<?php
/**
 * Neve Integration Class
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Registers Neve as a Theme integration:
 * - Display metaboxes on Content Groups
 * - Register as an overwrite section in Content Groups
 * - Copy / don't copy metadata to generated Pages, depending on if the integration is active
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 4.8.8
 */
class Page_Generator_Pro_Neve extends Page_Generator_Pro_Integration {

	/**
	 * Holds the base object.
	 *
	 * @since   4.8.8
	 *
	 * @var     object
	 */
	public $base;

	/**
	 * Constructor
	 *
	 * @since   4.8.8
	 *
	 * @param   object $base    Base Plugin Class.
	 */
	public function __construct( $base ) {

		// Store base class.
		$this->base = $base;

		// Set Theme Name.
		$this->theme_name = 'Neve';

		// Set Meta Keys used by this Theme.
		$this->meta_keys = array(
			'/^neve_(.*)/i',
		);

		// Set Overwrite Setting's Key used by this Theme.
		$this->overwrite_section = 'neve';

		// Content Groups: Register Neve support.
		add_filter( 'neve_post_type_supported_list', array( $this, 'register_support' ) );

		// Register meta boxes on Content Groups.
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );

		// Remove Page Builder data from Group Settings if overwriting content is disabled, and an existing generated page already exists.
		add_filter( 'page_generator_pro_generate_remove_content_from_settings_ignored_keys', array( $this, 'remove_post_meta_from_content_group' ), 10, 2 );

		// Remove Theme data from Group Settings if Theme isn't active on Generation.
		add_filter( 'page_generator_pro_groups_get_settings_remove_orphaned_settings', array( $this, 'remove_orphaned_settings' ) );

	}

	/**
	 * Allows Neve Theme to inject its Page Builder
	 * into Page Generator Pro's Groups
	 *
	 * @since   4.8.8
	 *
	 * @param   array $post_types     Post Types Supporting Neve.
	 * @return  array                   Post Types Supporting Neve
	 */
	public function register_support( $post_types ) {

		$post_types[] = 'page-generator-pro';
		return $post_types;

	}

	/**
	 * Registers Neve Theme's Meta Boxes on Page Generator Pro's Content Groups
	 * when not using Gutenberg.
	 *
	 * @since   4.8.8
	 */
	public function add_meta_boxes() {

		// Bail if Neve isn't active.
		if ( ! $this->is_theme_active() ) {
			return;
		}

		global $wp_meta_boxes;

		// Bail if Neve's settings are not available.
		if ( ! array_key_exists( 'neve-page-settings', $wp_meta_boxes['page']['side']['default'] ) ) {
			return;
		}

		// Copy Neve's metabox from Pages to Content Groups.
		$wp_meta_boxes['page-generator-pro']['side']['default']['neve-page-settings'] = $wp_meta_boxes['page']['side']['default']['neve-page-settings']; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

	}

	/**
	 * Removes orphaned Neve metadata in the Group Settings during Generation,
	 * if Neve is not active
	 *
	 * @since   4.8.8
	 *
	 * @param   array $settings   Group Settings.
	 * @return  array               Group Settings
	 */
	public function remove_orphaned_settings( $settings ) {

		// Bail if Neve is active.
		if ( $this->is_theme_active() ) {
			return $settings;
		}

		// Remove Neve Meta Keys from the Group Settings during Generation.
		return $this->remove_orphaned_settings_metadata( $settings, $this->meta_keys );

	}

}
