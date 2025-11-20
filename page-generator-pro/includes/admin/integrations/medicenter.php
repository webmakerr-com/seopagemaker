<?php
/**
 * Medicenter Integration Class
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Registers Medicenter as a Plugin integration:
 * - Register metabox on Content Groups
 * - Copy / don't copy metadata to generated Pages, depending on if the integration is active
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 3.3.7
 */
class Page_Generator_Pro_Medicenter extends Page_Generator_Pro_Integration {

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

		// Set Meta Keys used by this Theme.
		$this->meta_keys = array(
			'/^medicenter_(.*)/i',
		);

		add_action( 'add_meta_boxes', array( $this, 'register_medicenter_support' ) );

		// Remove data from Group Settings if Theme isn't active on Generation.
		add_filter( 'page_generator_pro_groups_get_settings_remove_orphaned_settings', array( $this, 'remove_orphaned_settings' ) );

	}

	/**
	 * Registers Medicenter Theme's Meta Boxes on Page Generator Pro's Groups
	 *
	 * @since   2.6.2
	 */
	public function register_medicenter_support() {

		// Bail if Medicenter isn't active.
		if ( ! $this->is_active() ) {
			return;
		}

		// Register Medicenter Metaboxes on Page Generator Pro's Content Groups
		// Medicenter's JS, which injects Sidebars into the Attributes section,
		// will also have its settings saved as a Medicenter nonce is now output
		// through the called functions below.
		add_meta_box(
			'options',
			__( 'Medicenter: Post Options', 'page-generator-pro' ),
			'mc_theme_inner_custom_box_post', // @phpstan-ignore-line
			'page-generator-pro',
			'normal'
		);

	}

	/**
	 * Removes orphaned Medicenter metadata in the Group Settings during Generation,
	 * if Medicenter is not active
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

		// Remove XX Meta Keys from the Group Settings during Generation.
		return $this->remove_orphaned_settings_metadata( $settings, $this->meta_keys );

	}

	/**
	 * Checks if Medicenter is active
	 *
	 * @since   3.3.7
	 *
	 * @return  bool    Plugin is Active
	 */
	public function is_active() {

		if ( ! function_exists( 'mc_theme_add_custom_box' ) ) {
			return false;
		}

		return true;

	}

}
