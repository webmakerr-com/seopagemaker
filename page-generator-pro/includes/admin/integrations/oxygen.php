<?php
/**
 * Oxygen Page Builder Integration Class
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Registers Oxygen Page Builder as a Plugin integration:
 * - Copy / don't copy metadata to generated Pages, depending on if the integration is active
 * - Encode/decode Page Builder data when generating Pages
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 3.3.7
 */
class Page_Generator_Pro_Oxygen extends Page_Generator_Pro_Integration {

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
			'oxygen/functions.php',
		);

		// Set Meta Keys used by this Plugin.
		$this->meta_keys = array(
			'/^ct_(.*)/i',
			'/^_ct_(.*)/i',
			'oxygen_lock_post_edit_mode',
		);

		// Depending on the Oxygen version, decode/encode shortcodes or JSON metadata when generating content.
		if ( defined( 'CT_VERSION' ) ) {
			if ( version_compare( CT_VERSION, '4.0', '>=' ) ) { // @phpstan-ignore-line
				// Oxygen 4.x stores data in JSON.
				add_filter( 'page_generator_pro_groups_get_post_meta_ct_builder_json', array( $this, 'oxygen_decode_json_meta' ) );
				add_filter( 'page_generator_pro_generate_set_post_meta_ct_builder_json', array( $this, 'oxygen_encode_json_meta' ) );

				// Oxygen 4.8.3 and higher changes the name of the meta key from ct_builder_json to _ct_builder_json.
				add_filter( 'page_generator_pro_groups_get_post_meta__ct_builder_json', array( $this, 'oxygen_decode_json_meta' ) );
				add_filter( 'page_generator_pro_generate_set_post_meta__ct_builder_json', array( $this, 'oxygen_encode_json_meta' ) );

				// Encode JSON metadata when importing Content Groups.
				add_action( 'page_generator_pro_import_content_groups_set_post_meta_ct_builder_json', array( $this, 'encode_json_meta_on_import' ) );
				add_action( 'page_generator_pro_import_content_groups_set_post_meta__ct_builder_json', array( $this, 'encode_json_meta_on_import' ) );

				// Ignore Oxygen 3.x shortcodes.
				add_filter( 'page_generator_pro_generate_set_post_meta_ignored_keys', array( $this, 'ignore_shortcodes' ) );
			} else {
				// Oxygen 3.x stores data in shortcodes.
				add_filter( 'page_generator_pro_groups_get_post_meta_ct_builder_shortcodes', array( $this, 'oxygen_decode_meta' ) );
				add_filter( 'page_generator_pro_generate_set_post_meta_ct_builder_shortcodes', array( $this, 'oxygen_encode_meta' ) );
			}
		}

		// Remove Page Builder data from Group Settings if overwriting content is disabled, and an existing generated page already exists.
		add_filter( 'page_generator_pro_generate_remove_content_from_settings_ignored_keys', array( $this, 'remove_post_meta_from_content_group' ), 10, 2 );

		// Remove Plugin data from Group Settings if Plugin isn't active on Generation.
		add_filter( 'page_generator_pro_groups_get_settings_remove_orphaned_settings', array( $this, 'remove_orphaned_settings' ) );

	}

	/**
	 * Base64 decodes Oxygen's Page Builder 3.x metadata into an array, so that the Generate Routine
	 * can iterate through it, replacing Keywords, Shortcodes etc.
	 *
	 * Also calls oxygen_vsb_filter_shortcode_content_decode() to undo oxygen_vsb_filter_shortcode_content_encode(),
	 * which converts square brackets to _OXY_OPENING_BRACKET_ and _OXY_CLOSING_BRACKET_.
	 *
	 * @since   2.7.2
	 *
	 * @param   string $value  Oxygen Page Builder Data.
	 * @return  string|array            Oxygen Page Builder Data
	 */
	public function oxygen_decode_meta( $value ) {

		// Bail if Oxygen function doesn't exist.
		if ( ! function_exists( 'parse_shortcodes' ) ) {
			return $value;
		}

		$value = str_replace( '_OXY_OPENING_BRACKET_', '[', $value );
		$value = str_replace( '_OXY_CLOSING_BRACKET_', ']', $value );

		// Decode.
		return parse_shortcodes( $value, true, false );

	}

	/**
	 * Base64 encodes Oxygen Page Builder 3.x metadata into a string immediately before it's
	 * copied to the Generated Page.
	 *
	 * @since   2.9.5
	 *
	 * @param   array $value  Oxygen Page Builder Data.
	 * @return  array|string            Oxygen Page Builder Data
	 */
	public function oxygen_encode_meta( $value ) {

		// Bail if Oxygen function doesn't exist.
		if ( ! function_exists( 'parse_components_tree' ) ) {
			return $value;
		}

		// Convert shortcode to string.
		$value = parse_components_tree( $value['content'] );

		// Return.
		return $value;

	}

	/**
	 * JSON decodes Oxygen's Page Builder 4.x metadata into an array for a Content Group,
	 * so that the Generate Routine can iterate through it, replacing Keywords, Shortcodes etc.
	 *
	 * @since   3.7.3
	 *
	 * @param   string $value  Oxygen Page Builder JSON string.
	 * @return  string          Oxygen Page Builder Data
	 */
	public function oxygen_decode_json_meta( $value ) {

		// JSON decode Oxygen's data.
		if ( is_string( $value ) && ! empty( $value ) ) {
			$value = json_decode( $value, true );
		}
		if ( empty( $value ) ) {
			$value = array();
		}

		return $value;

	}

	/**
	 * JSON encodes Oxygen's Page Builder 4.x metadata into a string immediately before it's
	 * copied to the Generated Page.
	 *
	 * @since   3.7.3
	 *
	 * @param   string|array $value  Oxygen Page Builder Data.
	 * @return  string                  Oxygen Page Builder JSON string
	 */
	public function oxygen_encode_json_meta( $value ) {

		// Bail if the value has already been JSON encoded.
		if ( is_string( $value ) ) {
			return $value;
		}

		// Encode with slashes, just how Oxygen does.
		return addslashes( wp_json_encode( $value ) );

	}

	/**
	 * JSON encodes Oxygen's Page Builder 4.x metadata into a string, saving it to the imported Content Group.
	 *
	 * @since   5.3.2
	 *
	 * @param   string|array $value  Oxygen Page Builder Data.
	 * @return  string                  Oxygen Page Builder JSON string
	 */
	public function encode_json_meta_on_import( $value ) {

		// Bail if the value isn't a string.
		if ( ! is_string( $value ) ) {
			return $value;
		}

		// Encode with slashes, just how Oxygen does.
		return addslashes( wp_json_encode( json_decode( $value, true ) ) );

	}

	/**
	 * Defines Oxygen 3.x Post Meta Keys in a Content Group to ignore and not copy to generated Posts / Groups when
	 * using Oxygen 4.x.
	 *
	 * @since   3.7.3
	 *
	 * @param   array $ignored_keys   Ignored Keys (preg_match() compatible regex expressions are supported).
	 * @return  array                   Ignored Keys
	 */
	public function ignore_shortcodes( $ignored_keys ) {

		$ignored_keys[] = 'ct_builder_shortcodes';
		$ignored_keys[] = 'ct_builder_shortcodes_revisions';
		$ignored_keys[] = 'ct_builder_shortcodes_revisions_dates';
		return $ignored_keys;

	}

	/**
	 * Removes orphaned Oxygen metadata in the Group Settings during Generation,
	 * if Oxygen is not active
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

		// Remove Oxygen Meta Keys from the Group Settings during Generation.
		return $this->remove_orphaned_settings_metadata( $settings, $this->meta_keys );

	}

}
