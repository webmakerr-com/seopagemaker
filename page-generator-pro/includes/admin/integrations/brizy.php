<?php
/**
 * Brizy Integration Class
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Registers Brizy as a Plugin integration:
 * - Register as an overwrite section in Content Groups
 * - Copy / don't copy metadata to generated Pages, depending on if the integration is active
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 3.3.7
 */
class Page_Generator_Pro_Brizy extends Page_Generator_Pro_Integration {

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

		// Set Meta Keys used by this Plugin.
		$this->meta_keys = array(
			'brizy',
			'/^brizy_(.*)/i',
			'/^brizy-(.*)/i',
		);

		add_filter( 'page_generator_pro_groups_get_post_meta_brizy', array( $this, 'brizy_decode_meta' ) );
		add_filter( 'page_generator_pro_generate_set_post_meta_brizy', array( $this, 'brizy_encode_meta' ) );

		// Remove Page Builder data from Group Settings if overwriting content is disabled, and an existing generated page already exists.
		add_filter( 'page_generator_pro_generate_remove_content_from_settings_ignored_keys', array( $this, 'remove_post_meta_from_content_group' ), 10, 2 );

		// Remove Plugin data from Group Settings if Plugin isn't active on Generation.
		add_filter( 'page_generator_pro_groups_get_settings_remove_orphaned_settings', array( $this, 'remove_orphaned_settings' ) );

	}

	/**
	 * Base64 decodes Brizy's Page Builder metadata into an array, so that the Generate Routine
	 * can iterate through it, replacing Keywords, Shortcodes etc.
	 *
	 * @since   3.0.2
	 *
	 * @param   array $value   Brizy Page Builder Data.
	 * @return  array           Brizy Page Builder Data
	 */
	public function brizy_decode_meta( $value ) {

		// Bail if Brizy isn't active.
		if ( ! $this->is_active() ) {
			return $value;
		}

		// Bail if there's no compiled HTML.
		if ( ! isset( $value['brizy-post'] ) ) {
			return $value;
		}
		if ( ! isset( $value['brizy-post']['compiled_html'] ) ) {
			return $value;
		}

		// Decode compiled HTML and editor data.
		$value['brizy-post']['compiled_html'] = base64_decode( $value['brizy-post']['compiled_html'] ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions
		$value['brizy-post']['editor_data']   = base64_decode( $value['brizy-post']['editor_data'] ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions

		// Return.
		return $value;

	}

	/**
	 * Base64 encodes Brizy Page Builder metadata into a string immediately before it's
	 * copied to the Generated Page.
	 *
	 * @since   3.0.2
	 *
	 * @param   array $value  Brizy Page Builder Data.
	 * @return  array           Brizy Page Builder Data
	 */
	public function brizy_encode_meta( $value ) {

		// Bail if Brizy isn't active.
		if ( ! $this->is_active() ) {
			return $value;
		}

		// Bail if there's no compiled HTML.
		if ( ! isset( $value['brizy-post'] ) ) {
			return $value;
		}
		if ( ! isset( $value['brizy-post']['compiled_html'] ) ) {
			return $value;
		}

		// Encode compiled HTML.
		$value['brizy-post']['compiled_html'] = base64_encode( $value['brizy-post']['compiled_html'] ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions
		$value['brizy-post']['editor_data']   = base64_encode( $value['brizy-post']['editor_data'] ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions

		// Return.
		return $value;

	}

	/**
	 * Removes orphaned Brizy metadata in the Group Settings during Generation,
	 * if Brizy is not active
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

		// Remove Brizy Meta Keys from the Group Settings during Generation.
		return $this->remove_orphaned_settings_metadata( $settings, $this->meta_keys );

	}

	/**
	 * Checks if Brizy is active
	 *
	 * @since   3.3.7
	 *
	 * @return  bool    Is Active
	 */
	public function is_active() {

		if ( ! defined( 'BRIZY_VERSION' ) ) {
			return false;
		}

		return true;

	}

}
