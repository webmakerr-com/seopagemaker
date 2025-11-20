<?php
/**
 * Breakdance Builder Integration Class
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Registers Breakdance Builder as a Plugin integration:
 * - Copy / don't copy metadata to generated Pages, depending on if the integration is active
 * - Encode/decode Page Builder data when generating Pages
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 3.9.5
 */
class Page_Generator_Pro_Breakdance extends Page_Generator_Pro_Integration {

	/**
	 * Holds the base object.
	 *
	 * @since   3.9.5
	 *
	 * @var     object
	 */
	public $base;

	/**
	 * Constructor
	 *
	 * @since   3.9.5
	 *
	 * @param   object $base    Base Plugin Class.
	 */
	public function __construct( $base ) {

		// Store base class.
		$this->base = $base;

		// Set Plugin.
		$this->plugin_folder_filename = array(
			'breakdance/plugin.php',
		);

		// Set Meta Keys used by this Plugin.
		$this->meta_keys = array(
			'/^breakdance_(.*)/i',
			'_breakdance_data', // 1.7.2+ uses _breakdance_data instead of breakdance_data.
		);

		// Set current screen to editing a Content Group when editing with Divi.
		add_filter( 'page_generator_pro_screen_get_current_screen_before', array( $this, 'set_current_screen' ) );

		// Set height of TinyMCE modals to a fixed number when editing in Breakdance's Rich Text module.
		add_filter( 'page_generator_pro_shortcode_get_modal_dimensions', array( $this, 'set_modal_dimensions' ), 10, 2 );

		// Encode JSON metadata when importing Content Groups.
		add_action( 'page_generator_pro_import_content_groups_set_post_meta', array( $this, 'encode_json_meta_on_import' ), 10, 3 );

		// Decode/encode JSON metadata when generating content.
		// These generic hooks are deliberate; refer to the function comments to understand why we don't target
		// the specific _breakdance_data meta key via e.g. page_generator_pro_groups_get_post_meta__breakdance_data.
		add_filter( 'page_generator_pro_groups_get_post_meta', array( $this, 'decode_json_meta' ), 10, 2 );
		add_action( 'page_generator_pro_generate_set_post_meta', array( $this, 'encode_json_meta' ), 10, 5 );

		// Remove Page Builder data from Group Settings if overwriting content is disabled, and an existing generated page already exists.
		add_filter( 'page_generator_pro_generate_remove_content_from_settings_ignored_keys', array( $this, 'remove_post_meta_from_content_group' ), 10, 2 );

		// Remove Plugin data from Group Settings if Plugin isn't active on Generation.
		add_filter( 'page_generator_pro_groups_get_settings_remove_orphaned_settings', array( $this, 'remove_orphaned_settings' ) );

		// Geneate CSS.
		add_action( 'page_generator_pro_generate_content_finished', array( $this, 'regenerate_css' ), 10, 1 );

	}

	/**
	 * Tells the Screen class that we're editing a Content Group when editing it with Breakdance.
	 *
	 * @since   4.6.9
	 *
	 * @param   array $result     Screen and Section.
	 * @return  array             Screen and Section
	 */
	public function set_current_screen( $result ) {

		// If we're not loading the Rich Text module in an iframe in Breakdance, return
		// the original screen array.
		if ( ! array_key_exists( 'breakdance_wpuiforbuilder_tinymce', $_REQUEST ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return $result;
		}

		// Tell Breakdance we're editing a Content Group.
		return array(
			'screen'  => 'content_groups',
			'section' => 'edit',
		);

	}

	/**
	 * Sets the height of TinyMCE modals to a fixed number when editing in Breakdance's Rich Text module,
	 * to ensure they don't overflow the wp_editor() iframe instance, which would result in the modal's
	 * buttons being cut off.
	 *
	 * @since   4.6.9
	 *
	 * @param   array  $dimensions Dimensions with array keys `width` and `height`.
	 * @param   string $shortcode  Shortcode.
	 * @return  int                 Modal height
	 */
	public function set_modal_dimensions( $dimensions, $shortcode ) {

		// If we're not loading the Rich Text module in an iframe in Breakdance, return
		// the original height.
		if ( ! array_key_exists( 'breakdance_wpuiforbuilder_tinymce', $_REQUEST ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return $dimensions;
		}

		// Set width, if greater than 800px.
		if ( $dimensions['width'] > 800 ) {
			$dimensions['width'] = 800;
		}

		// Set height, if greater than 400px.
		if ( $dimensions['height'] > 400 ) {
			$dimensions['height'] = 400;
		}

		// Return.
		return $dimensions;

	}

	/**
	 * JSON decodes Breakdance Builder metadata into an array for a Content Group,
	 * so that the Generate Routine can iterate through it, replacing Keywords, Shortcodes etc.
	 *
	 * We deliberately use the `page_generator_pro_generate_set_post_meta` hook,
	 * because we must use Breakdance's get_tree() function to use Breakdance's logic to
	 * decode the JSON string.
	 *
	 * @since   3.9.5
	 *
	 * @param   array $group_meta   Metadata.
	 * @param   int   $group_id     Content Group ID.
	 * @return  array
	 */
	public function decode_json_meta( $group_meta, $group_id ) {

		// Bail if Breakdance isn't active.
		if ( ! function_exists( '\Breakdance\Data\get_tree' ) ) {
			return $group_meta;
		}

		// Bail if no Breakdance metadata exists.
		if ( ! array_key_exists( '_breakdance_data', $group_meta ) &&
			! array_key_exists( 'breakdance_data', $group_meta ) ) {
			return $group_meta;
		}

		// Determine the meta key to use.
		$meta_key = ( array_key_exists( '_breakdance_data', $group_meta ) ? '_breakdance_data' : 'breakdance_data' );

		// Use Breakdance functions to correctly fetch the JSON metadata.
		$group_meta['_breakdance_data'] = \Breakdance\Data\get_tree( $group_id );

		return $group_meta;

	}

	/**
	 * JSON encodes Breakdance Builder metadata into a string, saving it to the generated page
	 * after the generated Page has been created.
	 *
	 * We deliberately use the `page_generator_pro_generate_set_post_meta` hook, because we must
	 * use Breakdance's set_meta() function to ensure the formatting of the JSON string and its
	 * slashes are correct.
	 *
	 * @since   3.9.5
	 *
	 * @param   int   $post_id        Generated Page ID.
	 * @param   int   $group_id       Group ID.
	 * @param   array $post_meta      Group Post Meta.
	 * @param   array $settings       Group Settings.
	 * @param   array $post_args      wp_insert_post() / wp_update_post() arguments.
	 */
	public function encode_json_meta( $post_id, $group_id, $post_meta, $settings, $post_args = array() ) {

		// Bail if Breakdance isn't active.
		if ( ! function_exists( '\Breakdance\Data\set_meta' ) ) {
			return;
		}

		// Bail if no Breakdance metadata exists.
		if ( ! array_key_exists( '_breakdance_data', $post_meta ) &&
			! array_key_exists( 'breakdance_data', $post_meta ) ) {
			return;
		}

		// Determine the meta key to use.
		$meta_key = ( array_key_exists( '_breakdance_data', $post_meta ) ? '_breakdance_data' : 'breakdance_data' );

		// Use Breakdance functions to correctly set the JSON metadata.
		\Breakdance\Data\set_meta(
			$post_id,
			$meta_key,
			array(
				'tree_json_string' => wp_json_encode( $post_meta['_breakdance_data'] ),
			)
		);

	}

	/**
	 * JSON encodes Breakdance Builder metadata into a string, saving it to the imported Content Group.
	 *
	 * @since   5.3.2
	 *
	 * @param   int   $group_id       Group ID.
	 * @param   array $post_meta      Group Post Meta.
	 * @param   array $settings       Group Settings.
	 */
	public function encode_json_meta_on_import( $group_id, $post_meta, $settings ) {

		// Bail if Breakdance isn't active.
		if ( ! function_exists( '\Breakdance\Data\set_meta' ) ) {
			return;
		}

		// Bail if no Breakdance metadata exists.
		if ( ! array_key_exists( '_breakdance_data', $post_meta ) &&
			! array_key_exists( 'breakdance_data', $post_meta ) ) {
			return;
		}

		// Determine the meta key to use.
		$meta_key = ( array_key_exists( '_breakdance_data', $post_meta ) ? '_breakdance_data' : 'breakdance_data' );

		// Use Breakdance functions to correctly set the JSON metadata.
		\Breakdance\Data\set_meta(
			$group_id,
			$meta_key,
			json_decode( $post_meta[ $meta_key ], true )
		);

	}

	/**
	 * Removes orphaned Breakdance metadata in the Group Settings during Generation,
	 * if Breakdance is not active
	 *
	 * @since   3.9.5
	 *
	 * @param   array $settings   Group Settings.
	 * @return  array               Group Settings
	 */
	public function remove_orphaned_settings( $settings ) {

		// Don't remove settings if the Plugin is active.
		if ( $this->is_active() ) {
			return $settings;
		}

		// Remove Breakdance Meta Keys from the Group Settings during Generation.
		return $this->remove_orphaned_settings_metadata( $settings, $this->meta_keys );

	}

	/**
	 * Generates the CSS cache for the generated Page, after it has been created/updated.
	 *
	 * @since   4.8.7
	 *
	 * @param   int $post_id        Generated Post ID.
	 */
	public function regenerate_css( $post_id ) {

		// Bail if Breakdance isn't active.
		if ( ! function_exists( '\Breakdance\Render\generateCacheForPost' ) ) {
			return;
		}

		// Bail if no Breakdance metadata exists.
		if ( empty( get_post_meta( $post_id, '_breakdance_data', true ) ) &&
			empty( get_post_meta( $post_id, 'breakdance_data', true ) ) ) {
			return;
		}

		// Generate CSS Cache for Generated Page / Post.
		\Breakdance\Render\generateCacheForPost( $post_id );

	}

}
