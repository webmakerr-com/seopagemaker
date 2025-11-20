<?php
/**
 * Betheme Integration Class
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Registers Betheme as a Plugin integration:
 * - Register metaboxes on Content Groups
 * - Register as an overwrite section in Content Groups
 * - Copy / don't copy metadata to generated Pages, depending on if the integration is active
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 3.3.7
 */
class Page_Generator_Pro_Betheme extends Page_Generator_Pro_Integration {

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
		$this->theme_name = 'Betheme';

		// Set Meta Keys used by this Theme.
		$this->meta_keys = array(
			'/^mfn-(.*)/i',
		);

		// Register BeTheme support.
		add_action( 'wp_loaded', array( $this, 'register_betheme_support' ) );

		// Remove some duplicate data before generation.
		add_filter( 'page_generator_pro_generate_content_settings_before', array( $this, 'remove_builder_data_on_generation' ), 10, 1 );

		// Remove Page Builder data from Group Settings if overwriting content is disabled, and an existing generated page already exists.
		add_filter( 'page_generator_pro_generate_remove_content_from_settings_ignored_keys', array( $this, 'remove_post_meta_from_content_group' ), 10, 2 );

		// Remove Theme data from Group Settings if Theme isn't active on Generation.
		add_filter( 'page_generator_pro_groups_get_settings_remove_orphaned_settings', array( $this, 'remove_orphaned_settings' ) );

		// Replace Keywords in Global Widgets when viewing a generated Page.
		add_action( 'mfn_hook_content_before', array( $this, 'enable_replace_keywords_in_global_sections_and_wraps' ) );
		add_action( 'mfn_hook_content_after', array( $this, 'disable_replace_keywords_in_global_sections_and_wraps' ) );

	}

	/**
	 * Allows Betheme's Muffin Builder Meta Box to be output on Page Generator Pro's Groups
	 *
	 * @since   2.1.2
	 */
	public function register_betheme_support() {

		// Bail if Betheme isn't loaded.
		if ( ! class_exists( 'Mfn_Post_Type' ) ) {
			return;
		}

		// Load class.
		include_once $this->base->plugin->folder . '/includes/admin/integrations/pagebuilders-betheme.php';

		// Bail if class didn't load.
		if ( ! class_exists( 'Mfn_Post_Type_Page_Generator_Pro' ) ) {
			return;
		}

		// Invoke class.
		$mfn_post_type_page_generator_pro = new Mfn_Post_Type_Page_Generator_Pro();

	}

	/**
	 * Removes some Content Group's BeTheme Builder Post Meta data
	 * that is the only data used by the page builder in the Content Group.
	 *
	 * This prevents duplicate effort of shortcode processing across the Post Meta,
	 * which would result in e.g. duplicate Media Library Images if using the Media Library shortcode.
	 *
	 * @since   5.0.4
	 *
	 * @param   array $settings       Group Settings.
	 */
	public function remove_builder_data_on_generation( $settings ) {

		// Bail if Betheme isn't loaded.
		if ( ! class_exists( 'Mfn_Post_Type' ) ) {
			return $settings;
		}

		// Just return the Group settings if no paeg builder exists.
		if ( ! isset( $settings['post_meta'] ) ) {
			return $settings;
		}

		// Remove unused page builder data.
		unset(
			$settings['post_meta']['mfn-page-items-seo'],
			$settings['post_meta']['mfn-page-object'],
			$settings['post_meta']['mfn-builder-revision-update']
		);

		// Return.
		return $settings;

	}

	/**
	 * Removes orphaned Betheme metadata in the Group Settings during Generation,
	 * if Betheme is not active
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

		// Remove Betheme Meta Keys from the Group Settings during Generation.
		return $this->remove_orphaned_settings_metadata( $settings, $this->meta_keys );

	}

	/**
	 * Enable replacing Keywords in BeTheme's Global Sections and Wraps
	 *
	 * @since   4.8.5
	 */
	public function enable_replace_keywords_in_global_sections_and_wraps() {

		// Bail if not a singular Post.
		if ( ! is_singular() ) {
			return;
		}

		// Bail if a Content Group.
		if ( is_singular( 'page-generator-pro' ) ) {
			return;
		}

		// Bail if not a generated Page.
		if ( ! get_post_meta( get_the_ID(), '_page_generator_pro_group', true ) ) {
			return;
		}

		add_filter( 'get_post_metadata', array( $this, 'frontend_replace_keywords_in_global_section_or_wrap_metadata' ), 10, 4 );

	}

	/**
	 * Disables replacing Keywords in BeTheme's Global Sections and Wraps
	 *
	 * @since   4.8.5
	 */
	public function disable_replace_keywords_in_global_sections_and_wraps() {

		remove_filter( 'get_post_metadata', array( $this, 'frontend_replace_keywords_in_global_section_or_wrap_metadata' ), 10, 4 );

	}

	/**
	 * Checks if the get_post_meta() call is for a Global Section or Wrap,
	 * fetching the metadata and passing it through `replace_keywords_with_custom_field_values()`
	 * and returning it with Keywords replaced.
	 *
	 * We don't use the `mfn_builder_items_show` filter, as it doesn't contain the Global
	 * Section or Wrap data; BeTheme will later fetch it via get_post_meta().
	 *
	 * @since   4.8.5
	 *
	 * @param   array  $metadata   Metadata.
	 * @param   int    $object_id  Post ID.
	 * @param   string $meta_key   Meta Key.
	 * @param   bool   $single     Is single.
	 * @return  null|array
	 */
	public function frontend_replace_keywords_in_global_section_or_wrap_metadata( $metadata, $object_id, $meta_key, $single ) {

		// Return null if not 'mfn-page-items', so we don't short circuit fetching
		// the Page / Post metadata.
		if ( $meta_key !== 'mfn-page-items' ) {
			return null;
		}

		// Return null if the Page / Post / CPT ID requested isn't a BeTheme Template
		// i.e. isn't a Global Section or Global Wrap.
		if ( get_post_type( $object_id ) !== 'template' ) {
			return null;
		}

		// Remove the filter to avoid a recursive loop.
		$this->disable_replace_keywords_in_global_sections_and_wraps();

		// Copy how BeTheme fetches a global item.
		$refresh_content = get_post_meta( $object_id, $meta_key, true );
		if ( ! is_array( $refresh_content ) ) {
			$refresh_content = unserialize( call_user_func( 'base' . '64_decode', $refresh_content ) ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_unserialize, Generic.Strings.UnnecessaryStringConcat.Found
		}

		// Reinstate the filter to catch any other global items.
		$this->enable_replace_keywords_in_global_sections_and_wraps();

		// Replace the Keywords and return $refresh_content.
		$result = $this->replace_keywords_with_custom_field_values( $refresh_content, get_the_ID() );

		// Return in an array; if we don't, the zero index gets lost and breaks BeTheme.
		return array( $result );

	}

}
