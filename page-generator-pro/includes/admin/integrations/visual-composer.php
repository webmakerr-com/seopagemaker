<?php
/**
 * Visual Composer (visualcomposer.com) Integration Class
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Registers Visual Composer (visualcomposer.com) as a Plugin integration:
 * - Enable Visual Editor on Content Groups
 * - Copy / don't copy metadata to generated Pages, depending on if the integration is active
 * - Decode/encode Page Builder metadata when generating Pages
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 3.3.7
 */
class Page_Generator_Pro_Visual_Composer extends Page_Generator_Pro_Integration {

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
			'visualcomposer/plugin-wordpress.php',
		);

		// Set Meta Keys used by this Plugin.
		$this->meta_keys = array(
			'/^_vcv-(.*)/i',
			'/^vcv-(.*)/i',
			'vcvSourceCssFileUrl',
			'vcvSourceAssetsFiles',
			'vcvSourceCss',
			'vcvSettingsSourceCustomCss',
		);

		add_filter( 'init', array( $this, 'register_visual_composer_support' ) );
		add_filter( 'page_generator_pro_screen_get_current_screen', array( $this, 'visual_composer_set_current_screen' ), 10, 1 );
		add_filter( 'page_generator_pro_groups_get_post_meta_vcv-pageContent', array( $this, 'visual_composer_decode_meta' ) );
		add_filter( 'page_generator_pro_generate_set_post_meta_vcv-pageContent', array( $this, 'visual_composer_encode_meta' ) );

		// Remove Page Builder data from Group Settings if overwriting content is disabled, and an existing generated page already exists.
		add_filter( 'page_generator_pro_generate_remove_content_from_settings_ignored_keys', array( $this, 'remove_post_meta_from_content_group' ), 10, 2 );

		// Remove Plugin data from Group Settings if Plugin isn't active on Generation.
		add_filter( 'page_generator_pro_groups_get_settings_remove_orphaned_settings', array( $this, 'remove_orphaned_settings' ) );

	}

	/**
	 * Registers the Visual Composer filter to inject its Page Builder into Page Generator Pro's Groups.
	 *
	 * @since   2.0.1
	 */
	public function register_visual_composer_support() {

		// Bail if the vchelper function doesn't exist.
		if ( ! function_exists( 'vchelper' ) ) {
			return;
		}

		// Visual Composer uses its own filter system, not WordPress standard filters.
		// Register the filter.
		$filter = vchelper( 'Filters' );
		$filter->listen( 'vcv:helpers:access:editorPostType', array( $this, 'register_visual_composer_support_post_type' ), 1 );

	}

	/**
	 * Allows Visual Composer to inject its Page Builder into Page Generator Pro's Groups.
	 *
	 * @since   2.0.1
	 *
	 * @param   array $post_types     Supported Post Types.
	 * @return  array                   Supported Post Types
	 */
	public function register_visual_composer_support_post_type( $post_types ) {

		$post_types[] = 'page-generator-pro';
		return $post_types;

	}

	/**
	 * Tells the Screen class that we're editing a Content Group when editing it with Visual Composer.
	 *
	 * @since   2.5.8
	 *
	 * @param   array $result     Screen and Section.
	 * @return  array                   Screen and Section
	 */
	public function visual_composer_set_current_screen( $result ) {

		// Bail if we're not on the Visual Composer Editor screen.
		if ( ! array_key_exists( 'vcv-action', $_REQUEST ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			return $result;
		}

		if ( ! array_key_exists( 'post', $_REQUEST ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			return $result;
		}

		// Check if we're editing a Content Group.
		if ( ! isset( $_REQUEST['post'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			return $result;
		}
		if ( $this->base->plugin->name !== get_post_type( absint( $_REQUEST['post'] ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			return $result;
		}

		// Return a modified screen array to tell the Screen class that we're editing a Content Group.
		return array(
			'screen'  => 'content_groups',
			'section' => 'edit',
		);

	}

	/**
	 * JSON decodes Visual Composer's Page Builder metadata into an array, so that the Generate Routine
	 * can iterate through it, replacing Keywords, Shortcodes etc.
	 *
	 * @since   2.6.1
	 *
	 * @param   string $value  Visual Composer Page Builder Data.
	 * @return  array           Visual Composer Page Builder Data
	 */
	public function visual_composer_decode_meta( $value ) {

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
	 * @since   2.6.1
	 *
	 * @param   array $value   Visual Composer Page Builder Data.
	 * @return  string          Visual Composer Page Builder Data
	 */
	public function visual_composer_encode_meta( $value ) {

		return rawurlencode( wp_json_encode( $value ) );

	}

	/**
	 * Removes orphaned Visual Composer metadata in the Group Settings during Generation,
	 * if Visual Composer is not active.
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

		// Remove Visual Composer Meta Keys from the Group Settings during Generation.
		return $this->remove_orphaned_settings_metadata( $settings, $this->meta_keys );

	}

}
