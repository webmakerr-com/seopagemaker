<?php
/**
 * The7 Theme Integration Class
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Registers The7 Theme as a Plugin integration:
 * - Registers metaboxes in Content Groups
 * - Copy / don't copy metadata to generated Pages, depending on if the integration is active
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 3.3.7
 */
class Page_Generator_Pro_TheSeven extends Page_Generator_Pro_Integration {

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
		$this->theme_name = 'The7';

		// Set Meta Keys used by this Plugin.
		$this->meta_keys = array(
			'/^_dt_(.*)/i',
		);

		add_filter( 'presscore_pages_with_basic_meta_boxes', array( $this, 'register_the7_support' ) );

		// Remove Plugin data from Group Settings if Plugin isn't active on Generation.
		add_filter( 'page_generator_pro_groups_get_settings_remove_orphaned_settings', array( $this, 'remove_orphaned_settings' ) );

	}

	/**
	 * Allows The7 Theme to inject its Meta Boxes into Page Generator Pro's Groups.
	 *
	 * @since   2.3.6
	 *
	 * @param   array $post_types     Post Types supporting Meta Boxes.
	 * @return  array                   Post Types supporting Meta Boxes
	 */
	public function register_the7_support( $post_types ) {

		$post_types[] = 'page-generator-pro';
		return $post_types;

	}

	/**
	 * Removes orphaned The7 metadata in the Group Settings during Generation,
	 * if The7 is not active
	 *
	 * @since   3.3.7
	 *
	 * @param   array $settings   Group Settings.
	 * @return  array               Group Settings
	 */
	public function remove_orphaned_settings( $settings ) {

		// Don't remove settings if the Theme is active.
		if ( $this->is_theme_active() ) {
			return $settings;
		}

		// Remove The7 Meta Keys from the Group Settings during Generation.
		return $this->remove_orphaned_settings_metadata( $settings, $this->meta_keys );

	}

}
