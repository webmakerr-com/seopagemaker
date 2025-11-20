<?php
/**
 * OptimizePress Integration Class
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Registers OptimizePress as a Plugin integration:
 * - Copy / don't copy metadata to generated Pages, depending on if the integration is active
 * - Encode/decode Page Builder data when generating Pages
 * - Clear OptimizePress' cache after generation
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 3.4.6
 */
class Page_Generator_Pro_OptimizePress extends Page_Generator_Pro_Integration {

	/**
	 * Holds the base object.
	 *
	 * @since   3.4.6
	 *
	 * @var     object
	 */
	public $base;

	/**
	 * Constructor
	 *
	 * @since   3.4.6
	 *
	 * @param   object $base    Base Plugin Class.
	 */
	public function __construct( $base ) {

		// Store base class.
		$this->base = $base;

		// Set Plugin.
		$this->plugin_folder_filename = 'op-builder/op-builder.php';

		// Set Meta Keys used by this Plugin.
		$this->meta_keys = array(
			'/^_op3_(.*)/i',
		);

		// Remove Page Builder data from Group Settings if overwriting content is disabled, and an existing generated page already exists.
		add_filter( 'page_generator_pro_generate_remove_content_from_settings_ignored_keys', array( $this, 'remove_post_meta_from_content_group' ), 10, 2 );

		// Remove Page Builder data from Group Settings if Plugin isn't active on Generation.
		add_filter( 'page_generator_pro_groups_get_settings_remove_orphaned_settings', array( $this, 'remove_orphaned_settings' ) );

		// Decode Page Builder data into an array.
		add_filter( 'page_generator_pro_groups_get_post_meta__op3_data', array( $this, 'decode_meta' ) );

		// Don't process shortcodes.
		add_filter( 'page_generator_pro_generate_should_process_shortcodes_on_post_content', array( $this, 'should_process_shortcodes_on_post_content' ), 10, 2 );

		// Encode Page Builder data into a JSON string.
		add_filter( 'page_generator_pro_generate_set_post_meta__op3_data', array( $this, 'encode_meta' ) );

		// Clear OptimizePress Cache when Generate Content completes.
		add_action( 'page_generator_pro_generate_content_after', array( $this, 'clear_cache' ), 10, 3 );

	}

	/**
	 * Removes orphaned metadata in the Group Settings during Generation,
	 * if OptimizePress is not active
	 *
	 * @since   3.4.6
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

	/**
	 * JSON decodes Page Builder metadata into an array for a Content Group,
	 * so that the Generate Routine can iterate through it, replacing Keywords, Shortcodes etc.
	 *
	 * @since   3.4.6
	 *
	 * @param   string $value  Page Builder Data.
	 * @return  string          Page Builder Data
	 */
	public function decode_meta( $value ) {

		// JSON decode data.
		if ( is_string( $value ) && ! empty( $value ) ) {
			$value = json_decode( $value, true );
		}
		if ( empty( $value ) ) {
			$value = array();
		}

		return $value;

	}

	/**
	 * Disable processing Shortcodes on the main Post Content when the Content Group is edited using OptimizePress
	 *
	 * @since   3.4.6
	 *
	 * @param   bool  $process    Process Shortcodes on Post Content.
	 * @param   array $settings   Group Settings.
	 * @return  bool                Process Shortcodes on Post Content
	 */
	public function should_process_shortcodes_on_post_content( $process, $settings ) {

		// Honor the original status for processing shortcodes on content if no Post Meta.
		if ( ! isset( $settings['post_meta'] ) ) {
			return $process;
		}

		// Honor the original status for processing shortcodes on content if we're not using OptimizePress.
		if ( ! isset( $settings['post_meta']['_op3_mode'] ) ) {
			return $process;
		}
		if ( $settings['post_meta']['_op3_mode'] != '1' ) { // phpcs:ignore Universal.Operators.StrictComparisons.LooseNotEqual
			return $process;
		}

		// We're using OptimizePress for this Content Group, so don't process shortcodes on the Post Content
		// as the Post Content isn't used.
		return false;

	}

	/**
	 * JSON encodes Page Builder metadata into a string immediately before it's
	 * copied to the Generated Page.
	 *
	 * @since   3.4.6
	 *
	 * @param   array|string $value   Page Builder Data.
	 * @return  string                   Page Builder Data
	 */
	public function encode_meta( $value ) {

		// Bail if the value has already been JSON encoded.
		if ( is_string( $value ) ) {
			return $value;
		}

		// Encode with slashes, just how OptimizePress does.
		return wp_slash( wp_json_encode( $value ) );

	}

	/**
	 * Clears OptimizePress' caches after Generate Content finishes
	 *
	 * @since   3.4.6
	 *
	 * @param   int    $group_id   Group ID.
	 * @param   bool   $test_mode  Test Mode.
	 * @param   string $system     System.
	 */
	public function clear_cache( $group_id, $test_mode, $system ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter

		// Bail if OptimizePress isn't active.
		if ( ! $this->is_active() ) {
			return;
		}

		// Clear page and asset caches, if functions exist.
		if ( function_exists( 'op3_clear_all_pages_cache' ) ) {
			op3_clear_all_pages_cache();
		}
		if ( function_exists( 'clear_assets_cache' ) ) {
			clear_assets_cache();
		}

	}

}
