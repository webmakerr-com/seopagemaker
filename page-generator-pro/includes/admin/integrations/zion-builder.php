<?php
/**
 * Zion Builder Integration Class
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Registers Zion Builder as a Plugin integration:
 * - Enable Zion Builder on Content Groups
 * - Copy / don't copy metadata to generated Pages, depending on if the integration is active
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 3.4.1
 */
class Page_Generator_Pro_Zion_Builder extends Page_Generator_Pro_Integration {

	/**
	 * Holds the base object.
	 *
	 * @since   3.4.1
	 *
	 * @var     object
	 */
	public $base;

	/**
	 * Constructor
	 *
	 * @since   3.4.1
	 *
	 * @param   object $base    Base Plugin Class.
	 */
	public function __construct( $base ) {

		// Store base class.
		$this->base = $base;

		// Set Plugin.
		$this->plugin_folder_filename = array(
			'zionbuilder/zionbuilder.php',
		);

		// Set Meta Keys used by this Plugin.
		$this->meta_keys = array(
			'/^_zionbuilder_(.*)/i',
		);

		// Enables Zion Builder for Content Groups.
		add_filter( 'zionbuilder/permissions/get_allowed_post_types', array( $this, 'register_support' ) );

		// Decode and encode Page Builder data so Keyword search/replace works.
		add_filter( 'page_generator_pro_groups_get_post_meta__zionbuilder_page_elements', array( $this, 'decode_meta' ) );
		add_filter( 'page_generator_pro_generate_set_post_meta__zionbuilder_page_elements', array( $this, 'encode_meta' ) );

		// Remove Page Builder data from Group Settings if overwriting content is disabled, and an existing generated page already exists.
		add_filter( 'page_generator_pro_generate_remove_content_from_settings_ignored_keys', array( $this, 'remove_post_meta_from_content_group' ), 10, 2 );

		// Remove Plugin data from Group Settings if Plugin isn't active on Generation.
		add_filter( 'page_generator_pro_groups_get_settings_remove_orphaned_settings', array( $this, 'remove_orphaned_settings' ) );

	}

	/**
	 * Allows Zion Builder to inject its Page Builder into Page Generator Pro's Groups
	 *
	 * @since   3.4.1
	 *
	 * @param   array $post_types     Post Types Supporting Zion Builder.
	 * @return  array                   Post Types Supporting Zion Builder
	 */
	public function register_support( $post_types ) {

		$post_types[] = 'page-generator-pro';
		return $post_types;

	}

	/**
	 * JSON decodes Visual Composer's Page Builder metadata into an array, so that the Generate Routine
	 * can iterate through it, replacing Keywords, Shortcodes etc.
	 *
	 * @since   3.4.1
	 *
	 * @param   string $value  Visual Composer Page Builder Data.
	 * @return  array           Visual Composer Page Builder Data
	 */
	public function decode_meta( $value ) {

		// Bail if the value isn't a string.
		if ( ! is_string( $value ) ) {
			return $value;
		}

		return json_decode( rawurldecode( $value ), true );

	}

	/**
	 * JSON encodes Visual Composer's Page Builder metadata into a string immediately before it's
	 * copied to the Generated Page.
	 *
	 * @since   3.4.1
	 *
	 * @param   array $value   Visual Composer Page Builder Data.
	 * @return  string          Visual Composer Page Builder Data
	 */
	public function encode_meta( $value ) {

		return wp_slash( wp_json_encode( $value ) );

	}

	/**
	 * Removes orphaned Visual Composer metadata in the Group Settings during Generation,
	 * if Visual Composer is not active
	 *
	 * @since   3.4.1
	 *
	 * @param   array $settings   Group Settings.
	 * @return  array               Group Settings
	 */
	public function remove_orphaned_settings( $settings ) {

		// Don't remove settings if the Plugin is active.
		if ( $this->is_active() ) {
			return $settings;
		}

		// Remove Meta Keys from the Group Settings during Generation.
		return $this->remove_orphaned_settings_metadata( $settings, $this->meta_keys );

	}

}
