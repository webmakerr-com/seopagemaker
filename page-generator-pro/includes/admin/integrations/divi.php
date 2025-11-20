<?php
/**
 * Divi Integration Class
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Registers Divi as a Plugin integration:
 * - Enable Divi on Content Groups
 * - Register as an overwrite section in Content Groups
 * - Register Dynamic Elements as Divi Modules
 * - Copy / don't copy metadata to generated Pages, depending on if the integration is active
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 3.0.7
 */
class Page_Generator_Pro_Divi extends Page_Generator_Pro_Integration {

	/**
	 * Holds the base object.
	 *
	 * @since   3.0.7
	 *
	 * @var     object
	 */
	public $base;

	/**
	 * Constructor
	 *
	 * @since   3.0.7
	 *
	 * @param   object $base    Base Plugin Class.
	 */
	public function __construct( $base ) {

		// Store base class.
		$this->base = $base;

		// Set Theme Name.
		$this->theme_name = 'Divi';

		// Set Plugin.
		$this->plugin_folder_filename = array(
			'divi-builder/divi-builder.php',
		);

		// Set Meta Keys used by this Plugin.
		$this->meta_keys = array(
			'/^_et_(.*)/i',
			'_global_colors_info',
		);

		// Adds Content Groups as a choice under third party Post Types.
		add_filter( 'et_builder_third_party_post_types', array( $this, 'register_support' ) );

		// Always enables Divi on Content Groups in the Backend, even if the user hasn't enabled it in Theme Options.
		add_filter( 'et_builder_post_types', array( $this, 'register_support' ) );

		// Always enables Divi on Content Groups in the Frontend, even if the user hasn't enabled it in Theme Options.
		add_filter( 'et_fb_post_types', array( $this, 'register_support' ) );

		// Register support for Add New Using AI.
		add_filter( 'page_generator_pro_groups_ai_supported_page_builders', array( $this, 'register_add_new_using_ai_support' ) );
		add_filter( 'page_generator_pro_groups_ai_page_builder_version_divi', array( $this, 'get_version' ) );
		add_filter( 'page_generator_pro_groups_ai_page_builder_modules_divi', array( $this, 'get_registered_widgets' ) );
		add_filter( 'page_generator_pro_groups_ai_setup_content_group_content_divi', array( $this, 'setup_content_group_add_new_using_ai' ), 10, 2 );

		// Show all Metabox options in Divi Settings.
		add_action( 'page_generator_pro_groups_ui_add_meta_boxes', array( $this, 'register_metabox_support' ) );

		// Set current screen to editing a Content Group when editing with Divi.
		add_filter( 'page_generator_pro_screen_get_current_screen_before', array( $this, 'set_current_screen' ) );

		// Remove Page Builder data from Group Settings if overwriting content is disabled, and an existing generated page already exists.
		add_filter( 'page_generator_pro_generate_remove_content_from_settings_ignored_keys', array( $this, 'remove_post_meta_from_content_group' ), 10, 2 );

		// Remove Plugin data from Group Settings if Plugin isn't active on Generation.
		add_filter( 'page_generator_pro_groups_get_settings_remove_orphaned_settings', array( $this, 'remove_line_break_holders' ) );
		add_filter( 'page_generator_pro_groups_get_settings_remove_orphaned_settings', array( $this, 'remove_orphaned_settings' ) );

		// Convert Plugin Divi Modules to Text Modules with Shortcodes as content.
		add_filter( 'page_generator_pro_generate_content_settings', array( $this, 'convert_modules_to_text_modules' ), 10, 1 );

		// Replace Keywords in Global Widgets when viewing a generated Page.
		add_filter( 'et_pb_module_content', array( $this, 'frontend_replace_keywords' ), 10, 3 );

		// Fixes ETBackendBuilder JS error when switching editing from a Content Group to a Page/Post.
		add_action( 'et_fb_framework_loaded', array( $this, 'reload_dynamic_asset_cache' ) );

	}

	/**
	 * Allows The Divi Builder (and therefore Divi Theme 3.0+) to inject its Page Builder
	 * into Page Generator Pro's Groups
	 *
	 * @since   1.2.7
	 *
	 * @param   array $post_types     Post Types Supporting Divi.
	 * @return  array                   Post Types Supporting Divi
	 */
	public function register_support( $post_types ) {

		$post_types[] = 'page-generator-pro';
		return $post_types;

	}

	/**
	 * Registers Divi as a supported page builder for Add New Using AI.
	 *
	 * @since   5.0.4
	 *
	 * @param   array $page_builders   Page Builders.
	 * @return  array                  Page Builders
	 */
	public function register_add_new_using_ai_support( $page_builders ) {

		// Bail if Divi isn't active.
		if ( ! $this->is_active() ) {
			return $page_builders;
		}

		// Add Divi to the list of supported page builders.
		$page_builders['divi'] = 'Divi';

		return $page_builders;

	}

	/**
	 * Returns the Divi version.
	 *
	 * @since   5.0.4
	 *
	 * @param   string $version   Divi Version.
	 * @return  string            Divi Version
	 */
	public function get_version( $version = '' ) {

		// Get Divi Builder version from theme or plugin.
		if ( function_exists( 'et_get_theme_version' ) ) {
			return et_get_theme_version();
		}

		if ( defined( 'ET_BUILDER_VERSION' ) ) {
			return ET_BUILDER_VERSION;
		}

		// Return default version if Divi not detected.
		return '4.27.0';

	}

	/**
	 * Returns the registered Divi modules.
	 *
	 * @since   5.0.4
	 *
	 * @param   array $modules   Registered Divi modules.
	 * @return  array            Registered Divi modules
	 */
	public function get_registered_widgets( $modules ) {

		return array(
			'et_pb_button',
			'et_pb_circle_counter',
			'et_pb_contact_form',
			'et_pb_contact_field',
			'et_pb_countdown_timer',
			'et_pb_cta',
			'et_pb_divider',
			'et_pb_fullwidth_header',
			'et_pb_heading',
			'et_pb_number_counter',
			'et_pb_team_member',
			'et_pb_testimonial',
			'et_pb_text',
		);

	}

	/**
	 * Setup Content Group's content when using Add New Using AI.
	 *
	 * @since   5.0.4
	 *
	 * @param   int|WP_Error $content_group    Content Group ID or WP_Error.
	 * @param   string       $content          Content.
	 * @return  int|WP_Error                   Content Group ID or WP_Error
	 */
	public function setup_content_group_add_new_using_ai( $content_group, $content ) {

		// Return if we don't have a valid Content Group ID.
		if ( is_wp_error( $content_group ) ) {
			return $content_group;
		}

		// Update the Content Group's page builder content.
		$result = wp_update_post(
			array(
				'ID'           => $content_group,
				'post_content' => $content,
			),
			true
		);

		// Bail if an error occured.
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		// Update the Content Group's page builder data.
		update_post_meta( $content_group, '_et_pb_show_page_creation', 'off' );
		update_post_meta( $content_group, '_et_builder_version', 'VB|Divi|' . $this->get_version() );
		update_post_meta( $content_group, '_et_pb_built_for_post_type', 'page' );
		update_post_meta( $content_group, '_et_pb_use_builder', 'on' );

		// Return the Content Group ID.
		return $content_group;

	}

	/**
	 * Allows The Divi Builder (and therefore Divi Theme 3.0+) to inject its Page Builder
	 * Meta Box into this Plugin's enabled Custom Post Types
	 *
	 * @since   1.4.1
	 *
	 * @param   object $post_type_instance     Post Type Instance.
	 */
	public function register_metabox_support( $post_type_instance ) {

		// Don't need to do anything if we're not in the admin interface.
		if ( ! is_admin() ) {
			return;
		}

		// Don't add the meta box if Divi Builder isn't active.
		if ( ! function_exists( 'et_single_settings_meta_box' ) ) {
			return;
		}

		// Add Meta Box.
		// We don't use add_meta_box( 'et_settings_meta_box'... because we need to change
		// the Post Type = post, so that all settings display, without changing the global $post.
		add_meta_box(
			'et_settings_meta_box',
			__( 'Divi Settings', 'page-generator-pro' ),
			array( $this, 'output_metabox' ),
			$post_type_instance->post_type_name,
			'side',
			'high'
		);

		// Remove the bottom Actions Box, as clicking a button on it prompts Divi's 'leave site' JS.
		remove_meta_box( $this->base->get_class( 'post_type' )->post_type_name . '-actions-bottom', $this->base->get_class( 'post_type' )->post_type_name, 'side' );

	}

	/**
	 * Outputs the Divi Settings Metabox
	 *
	 * @since   1.6.4
	 */
	public function output_metabox() {

		// Trick Divi into outputting Post settings.
		global $post;
		$new_post            = $post;
		$new_post->post_type = 'post';

		// Call metabox function directly.
		et_single_settings_meta_box( $new_post ); // @phpstan-ignore-line

	}

	/**
	 * Tells the Screen class that we're editing a Content Group when editing it with Divi.
	 *
	 * @since   3.1.4
	 *
	 * @param   array $result     Screen and Section.
	 * @return  array                   Screen and Section
	 */
	public function set_current_screen( $result ) {

		// Bail if this isn't a Divi AJAX request.
		if ( ! array_key_exists( 'et_post_type', $_REQUEST ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			return $result;
		}
		if ( ! array_key_exists( 'et_post_id', $_REQUEST ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			return $result;
		}
		if ( $this->base->plugin->name !== get_post_type( absint( $_REQUEST['et_post_id'] ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			return $result;
		}

		// Return a modified screen array to tell the Screen class that we're editing a Content Group.
		return array(
			'screen'  => 'content_groups',
			'section' => 'edit',
		);

	}

	/**
	 * Replace <!-- [et_pb_line_break_holder] --> with newlines in the Content Group's
	 * content, as Divi stores newlines with a shortcode, which prevents e.g. block
	 * spintax from processing.
	 *
	 * @since   3.6.8
	 *
	 * @param   array $settings   Group Settings.
	 * @return  array               Group Settings
	 */
	public function remove_line_break_holders( $settings ) {

		$settings['content'] = str_replace( '<!- [et_pb_line_break_holder] ->', "\n", $settings['content'] );
		$settings['content'] = str_replace( '<!-- [et_pb_line_break_holder] -->', "\n", $settings['content'] );
		return $settings;

	}

	/**
	 * Removes orphaned metadata in the Group Settings during Generation,
	 * if Divi is not active.
	 *
	 * @since   3.3.7
	 *
	 * @param   array $settings   Group Settings.
	 * @return  array               Group Settings
	 */
	public function remove_orphaned_settings( $settings ) {

		// Don't remove settings if Divi Theme is active.
		if ( $this->is_theme_active() ) {
			return $settings;
		}

		// Don't remove settings if Divi Plugin is active.
		if ( $this->is_active() ) {
			return $settings;
		}

		// Remove Divi Meta Keys from the Group Settings during Generation.
		return $this->remove_orphaned_settings_metadata( $settings, $this->meta_keys );

	}

	/**
	 * If the given Content Group's content contains Modules registered by this Plugin, converts them
	 * to Text Modules with their content set to the shortcode syntax, so subsequent generation routines can parse them.
	 *
	 * @since   3.0.7
	 *
	 * @param   array $settings       Group Settings.
	 * @return  array                   Group Settings
	 */
	public function convert_modules_to_text_modules( $settings ) {

		// Bail if no Divi Data exists.
		if ( ! isset( $settings['post_meta']['_et_builder_version'] ) ) {
			return $settings;
		}

		// Get shortcodes.
		$shortcodes = $this->base->get_class( 'shortcode' )->get_shortcodes();

		// Bail if no shortcodes are available.
		if ( ! is_array( $shortcodes ) || ! count( $shortcodes ) ) {
			return $settings;
		}

		// Iterate through Shortcodes.
		$content = $settings['content'];
		foreach ( $shortcodes as $shortcode_name => $shortcode_properties ) {
			$divi_module_name_start = '[page-generator-pro-divi-' . $shortcode_name . ' '; // Space is deliberate to ensure we don't match e.g. [page-generator-pro-divi-openai] when it is checking [page-generator-pro-divi-openai-image].
			$divi_module_name_end   = '][/page-generator-pro-divi-' . $shortcode_name . ']';
			$shortcode_name         = $this->base->plugin->name . '-' . $shortcode_name;

			// Iterate through content, finding each instance of this module.
			$start = strpos( $content, $divi_module_name_start, 0 );
			while ( $start !== false ) {
				// Get module string and its attributes.
				$end                        = strpos( $content, $divi_module_name_end, $start + strlen( $divi_module_name_start ) ) + strlen( $divi_module_name_end );
				$divi_module_shortcode      = substr( $content, $start, ( $end - $start ) );
				$divi_module_shortcode_atts = shortcode_parse_atts( str_replace( $divi_module_name_start, '', str_replace( $divi_module_name_end, '', $divi_module_shortcode ) ) );

				// Merge attributes with shortcode's default attributes, so we're just left
				// with an array of shortcode compatible attributes that exclude Divi's e.g. _builder_version.
				$atts = shortcode_atts(
					$shortcode_properties['default_values'],
					$divi_module_shortcode_atts,
					$shortcode_name
				);

				// Build shortcode.
				$shortcode_atts = '';
				foreach ( $atts as $key => $value ) {
					if ( $value === false ) {
						$value = '0';
					}

					// Skip if the attribute doesn't exist.
					if ( ! array_key_exists( $key, $shortcode_properties['attributes'] ) ) {
						continue;
					}

					if ( is_array( $value ) ) {
						$value = implode( $shortcode_properties['attributes'][ $key ]['delimiter'], $value );
					}

					// If value is empty, ignore.
					if ( empty( $value ) ) {
						continue;
					}

					$shortcode_atts .= ' ' . $key . '="' . trim( $value ) . '"';
				}
				$shortcode = '[' . $shortcode_name . $shortcode_atts . ']';

				// Inject Page Generator Pro Shortcode inside the Divi text module shortcode.
				// Space after et_pb_text is deliberate.
				$divi_text_module_shortcode = str_replace( $divi_module_name_start, '[et_pb_text ', str_replace( $divi_module_name_end, '][/et_pb_text]', $divi_module_shortcode ) );
				$divi_text_module_shortcode = str_replace( '][', ']' . $shortcode . '[', $divi_text_module_shortcode );

				// Replace Divi module with Divi text module.
				$settings['content'] = str_replace( $divi_module_shortcode, $divi_text_module_shortcode, $settings['content'] );

				// Find the next instance of this module.
				$start = strpos( $content, $divi_module_name_start, $end );
			}
		}

		// Return.
		return $settings;

	}

	/**
	 * Replaces Keywords with Custom Field values for any Divi Global Modules
	 * in the given content.
	 *
	 * @since   4.3.5
	 *
	 * @param   string $content    Divi Module Content.
	 * @param   array  $props      Divi Module Properties.
	 * @param   array  $attrs      Divi Module Shortcode Attributes.
	 * @return  string
	 */
	public function frontend_replace_keywords( $content, $props, $attrs ) {

		// Replace Keywords with Generated Page's Custom Field values.
		return $this->replace_keywords_with_custom_field_values( $content, get_the_ID() );

	}

	/**
	 * Calls et_fb_get_dynamic_asset() for `helpers` and `definitions`, to generate JS files
	 * required by Divi when editing a Page or Post after switching from a Content Group.
	 *
	 * Sometimes, editing a Page after editing a Content Group results in Divi expecting to see
	 * JS files at:
	 * `wp-content/et-cache/1/72/en_US/definitions-page-{number}` and
	 * `wp-content/et-cache/1/72/en_US/helpers-page-{number}`.
	 *
	 * These files don't exist (it's unclear why Divi doesn't generate them), but the same numbered
	 * files do exist as:
	 * `wp-content/et-cache/1/72/en_US/definitions-page-generator-pro-{number}` and
	 * `wp-content/et-cache/1/72/en_US/helpers-page-generator-pro-{number}`
	 *
	 * This method forces calls to `et_fb_get_dynamic_asset`, which will update the helpers and definitions
	 * as necessary.
	 *
	 * @since   4.5.8
	 */
	public function reload_dynamic_asset_cache() {

		// If an AJAX action is being performed, we don't need to perform it ourselves as well.
		if ( array_key_exists( 'action', $_REQUEST ) ) {  // phpcs:ignore WordPress.Security.NonceVerification
			return;
		}

		// Don't perform this if this isn't a specific request.
		if ( ! array_key_exists( 'page_id', $_REQUEST ) || // phpcs:ignore WordPress.Security.NonceVerification
			! array_key_exists( 'et_fb', $_REQUEST ) || // phpcs:ignore WordPress.Security.NonceVerification
			! array_key_exists( 'et_bfb', $_REQUEST ) ) {  // phpcs:ignore WordPress.Security.NonceVerification
			return;
		}

		// Get Post ID and Post Type.
		$post_id   = absint( $_REQUEST['page_id'] );  // phpcs:ignore WordPress.Security.NonceVerification
		$post_type = get_post_type( $post_id );

		// Reload assets for the given Post Type now.
		et_fb_get_dynamic_asset( 'helpers', $post_type, true );
		et_fb_get_dynamic_asset( 'definitions', $post_type, true );

	}

}
