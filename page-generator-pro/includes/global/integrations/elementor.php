<?php
/**
 * Elementor Integration Class
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Registers Elementor as a Plugin integration:
 * - Enable Elementor on Content Groups
 * - Register Dynamic Elements as Elementor Widgets
 * - Copy / don't copy metadata to generated Pages, depending on if the integration is active
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 3.0.8
 */
class Page_Generator_Pro_Elementor extends Page_Generator_Pro_Integration {

	/**
	 * Holds the base object.
	 *
	 * @since   3.0.8
	 *
	 * @var     object
	 */
	public $base;

	/**
	 * Holds the Post ID being viewed on the frontend site.
	 *
	 * @since   4.3.2
	 *
	 * @var     int
	 */
	public $post_id = 0;

	/**
	 * Constructor
	 *
	 * @since   3.0.8
	 *
	 * @param   object $base    Base Plugin Class.
	 */
	public function __construct( $base ) {

		// Store base class.
		$this->base = $base;

		// Set Meta Keys used by this Plugin.
		$this->meta_keys = array(
			'/^_elementor_(.*)/i',
		);

		// Register Elementor Support.
		add_action( 'init', array( $this, 'register_elementor_support' ) );

		// Register Elementor Support for Add New Using AI.
		add_filter( 'page_generator_pro_groups_ai_page_builder_version_elementor', array( $this, 'get_version' ) );
		add_filter( 'page_generator_pro_groups_ai_page_builder_modules_elementor', array( $this, 'get_registered_widgets' ) );
		add_filter( 'page_generator_pro_groups_ai_setup_content_group_content_elementor', array( $this, 'setup_content_group_add_new_using_ai' ), 10, 2 );

		// Register Elementor JS/CSS.
		add_action( 'elementor/editor/before_enqueue_scripts', array( $this, 'register_elementor_scripts_css' ) );

		// Register Widget Category.
		add_action( 'elementor/elements/categories_registered', array( $this, 'register_elementor_widget_categories' ) );

		// Register custom fields (autocomplete, multi select etc) as Elementor Controls.
		add_action( 'elementor/controls/register', array( $this, 'register_controls' ) );

		// Register shortcodes as Elementor Widgets.
		add_action( 'elementor/widgets/register', array( $this, 'register_widgets' ) );

		add_action( 'elementor/editor/footer', array( $this, 'output_modal' ) );

		// Set current screen to editing a Content Group when editing with Elementor.
		add_filter( 'page_generator_pro_screen_get_current_screen_before', array( $this, 'set_current_screen' ) );

		// Remove Page Builder data from Group Settings if overwriting content is disabled, and an existing generated page already exists.
		add_filter( 'page_generator_pro_generate_remove_content_from_settings_ignored_keys', array( $this, 'remove_post_meta_from_content_group' ), 10, 2 );

		// Remove Plugin data from Group Settings if Plugin isn't active on Generation.
		add_filter( 'page_generator_pro_groups_get_settings_remove_orphaned_settings', array( $this, 'remove_orphaned_settings' ) );

		// Decode Elementor Page Builder data into an array.
		add_filter( 'page_generator_pro_groups_get_post_meta__elementor_data', array( $this, 'elementor_decode_meta' ) );

		// Don't process shortcodes.
		add_filter( 'page_generator_pro_generate_should_process_shortcodes_on_post_content', array( $this, 'elementor_should_process_shortcodes_on_post_content' ), 10, 2 );

		// Encode Elementor Page Builder data into a JSON string.
		add_filter( 'page_generator_pro_generate_set_post_meta__elementor_data', array( $this, 'elementor_encode_meta' ) );

		// Set correct template type if generating Elementor Landing Pages.
		add_filter( 'page_generator_pro_generate_set_post_meta__elementor_template_type', array( $this, 'elementor_set_template_type' ), 10, 5 );

		// Convert Plugin Elementor Widgets to Text Widgets with Shortcodes as content .
		add_filter( 'page_generator_pro_generate_content_settings', array( $this, 'convert_widgets_to_text_widgets' ), 10, 1 );

		// Clear Elementor Cache when Generate Content completes.
		add_action( 'page_generator_pro_generate_content_after', array( $this, 'clear_cache' ), 10 );

		// Replace Keywords in Global Widgets when viewing a generated Page.
		add_filter( 'elementor/frontend/builder_content_data', array( $this, 'frontend_get_post_id' ), 10, 2 );
		add_filter( 'elementor/frontend/the_content', array( $this, 'frontend_replace_keywords' ) );

	}

	/**
	 * Allows the Elementor Page Builder to inject its Page Builder
	 * into Page Generator Pro's Groups
	 *
	 * @since   2.0.1
	 */
	public function register_elementor_support() {

		add_post_type_support( 'page-generator-pro', 'elementor' );

	}

	/**
	 * Registers Elementor as a supported page builder for Add New Using AI.
	 *
	 * @since   5.0.4
	 *
	 * @param   array $page_builders   Page Builders.
	 * @return  array                  Page Builders
	 */
	public function register_add_new_using_ai_support( $page_builders ) {

		// Bail if Elementor isn't active.
		if ( ! $this->is_active() ) {
			return $page_builders;
		}

		// Add Elementor to the list of supported page builders.
		$page_builders['elementor'] = 'Elementor';

		return $page_builders;

	}

	/**
	 * Returns the Elementor version.
	 *
	 * @since   5.0.4
	 *
	 * @param   string $version   Elementor Version.
	 * @return  string            Elementor Version
	 */
	public function get_version( $version = '' ) {

		return defined( 'ELEMENTOR_VERSION' ) ? ELEMENTOR_VERSION : '3.27.7';

	}

	/**
	 * Returns the registered Elementor widgets.
	 *
	 * @since   5.0.4
	 *
	 * @param   array $widgets   Registered Elementor Widgets.
	 * @return  array    Registered Elementor Widgets
	 */
	public function get_registered_widgets( $widgets ) {

		// array_keys( \Elementor\Plugin::$instance->widgets_manager->get_widget_types() ) contains all widgets.
		// We only want some common ones.
		return array(
			'heading',
			'text-editor',
			'video',
			'button',
			'divider',
			'spacer',
			'counter',
			'progress',
			'testimonial',
			'accordion',
			'rating',
		);

	}

	/**
	 * Setup Content Group's content when using Add New Using AI.
	 *
	 * @since   5.0.4
	 *
	 * @param   int|WP_Error $content_group    Content Group ID or WP_Error.
	 * @param   string       $content          Content.
	 * @return  int|WP_Error                      Content Group ID or WP_Error
	 */
	public function setup_content_group_add_new_using_ai( $content_group, $content ) {

		// Return if we don't have a valid Content Group ID.
		if ( is_wp_error( $content_group ) ) {
			return $content_group;
		}

		// Decode and encode the content JSON to be compatible with Elementor.
		$content = $this->elementor_decode_meta( $content );
		$content = $this->elementor_encode_meta( $content );

		// Update the Content Group's Elementor data.
		update_post_meta( $content_group, '_elementor_data', $content );
		update_post_meta( $content_group, '_elementor_edit_mode', 'builder' );
		update_post_meta( $content_group, '_elementor_template_type', 'wp-post' );
		update_post_meta( $content_group, '_elementor_version', defined( 'ELEMENTOR_VERSION' ) ? ELEMENTOR_VERSION : '3.27.7' );

		// Return the Content Group ID.
		return $content_group;

	}

	/**
	 * Elementor: Enqueue CSS and JS when editing a Content Group, so TinyMCE Plugins etc. work,
	 * as Elementor removes actions hooked to admin_enqueue_scripts / wp_enqueue_scripts
	 *
	 * @since   2.5.7
	 */
	public function register_elementor_scripts_css() {

		// Don't load stylesheets if not in editor mode.
		if ( ! array_key_exists( 'action', $_GET ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			return;
		}
		if ( sanitize_text_field( wp_unslash( $_GET['action'] ) ) !== 'elementor' ) { // phpcs:ignore WordPress.Security.NonceVerification
			return;
		}

		// Load Plugin CSS/JS.
		$this->base->get_class( 'admin' )->admin_scripts_css();

		// Enqueue CSS for widget icons.
		wp_enqueue_style( $this->base->plugin->name . '-elementor', $this->base->plugin->url . 'assets/css/elementor.css', array(), $this->base->plugin->version );

	}

	/**
	 * Registers this Plugin's Name as a Category for Elementor Widgets registered
	 * by this Plugin
	 *
	 * @since   3.0.8
	 *
	 * @param   object $elements_manager   Elements Manager.
	 */
	public function register_elementor_widget_categories( $elements_manager ) {

		$elements_manager->add_category(
			$this->base->plugin->name,
			array(
				'title' => $this->base->plugin->displayName,
				'icon'  => 'fa fa-plug',
			)
		);

	}

	/**
	 * Registers Field Types as Controls, so that they can be used in Elementor
	 *
	 * @since   3.0.8
	 *
	 * @param   Controls_Manager $controls_manager   Elementor Controls Manager.
	 */
	public function register_controls( $controls_manager ) {

		// Define controls, which should reside in /includes/admin/integrations/elementor,
		// with the filename elementor-control-{control-id}.php and classname Page_Generator_Pro_Elementor_Control_{control_id}
		// (noting hyphens and underscores).
		$controls = array(
			'autocomplete',
			'autocomplete-textarea',
		);

		// Register Controls.
		foreach ( $controls as $control_id ) {
			$class_name = 'Page_Generator_Pro_Elementor_Control_' . str_replace( '-', '_', $control_id );

			// Register Control, using applicable function depending on the Elementor version.
			if ( method_exists( $controls_manager, 'register' ) ) {
				// Use register() function, available in Elementor 3.5.0.
				// Required per https://developers.elementor.com/docs/managers/registering-controls/.
				$controls_manager->register( new $class_name() );
			} else {
				// Fallback to register_control(), available in Elementor < 3.5.0.
				$controls_manager->register_control( 'page-generator-pro-' . $control_id, new $class_name() );
			}
		}

	}

	/**
	 * Registers Plugin Shortcodes as Modules, so that they can be used in Elementor
	 *
	 * @since   3.0.8
	 *
	 * @param   Widgets_Manager $widgets_manager    Widgets Manager, used to register/unregister Elementor Widgets.
	 */
	public function register_widgets( $widgets_manager ) {

		// Determine the screen that we're on.
		$screen = $this->base->get_class( 'screen' )->get_current_screen();

		// Get shortcodes, depending on whether we're editing a Content Group or Post.
		$shortcodes = $this->base->get_class( 'shortcode' )->get_shortcodes();

		switch ( $screen['screen'] ) {
			case 'content_groups':
				$shortcodes = $this->base->get_class( 'shortcode' )->get_shortcodes();
				break;

			default:
				$shortcodes = $this->base->get_class( 'shortcode' )->get_shortcode_supported_outside_of_content_groups();
				break;
		}

		// Bail if no shortcodes are available.
		if ( ! is_array( $shortcodes ) || count( $shortcodes ) === 0 ) {
			return;
		}

		// Iterate through shortcodes, registering them.
		foreach ( $shortcodes as $shortcode => $shortcode_properties ) {
			// Skip if no tabs or fields.
			if ( ! $shortcode_properties['tabs'] ) {
				continue;
			}
			if ( ! $shortcode_properties['fields'] ) {
				continue;
			}

			// Get class name.
			$class_name = 'Page_Generator_Pro_Elementor_Widget_' . str_replace( '-', '_', $shortcode );

			// Skip if class does not exist.
			if ( ! class_exists( $class_name ) ) {
				continue;
			}

			// Register Widget, using applicable function depending on the Elementor version.
			if ( method_exists( $widgets_manager, 'register' ) ) {
				// Use register() function, available in Elementor 3.5.0.
				// Required per https://developers.elementor.com/docs/managers/registering-widgets/.
				$widgets_manager->register( new $class_name() );
			} else {
				// Fallback to register_widget_type(), available in Elementor < 3.5.0.
				$widgets_manager->register_widget_type( new $class_name() );
			}
		}

	}

	/**
	 * Outputs the hidden Javascript Modal and Overlay in the Footer
	 *
	 * @since   4.1.0
	 */
	public function output_modal() {

		// Load view.
		require_once $this->base->plugin->folder . '_modules/dashboard/views/modal.php';

	}

	/**
	 * Tells the Screen class that we're editing a Content Group if Elementor makes
	 * an AJAX request.
	 *
	 * @since   3.6.8
	 *
	 * @param   array $result     Screen and Section.
	 * @return  array                   Screen and Section
	 */
	public function set_current_screen( $result ) {

		// Bail if this isn't an Elementor AJAX request.
		if ( ! array_key_exists( 'action', $_REQUEST ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			return $result;
		}
		if ( sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) !== 'elementor_ajax' ) { // phpcs:ignore WordPress.Security.NonceVerification
			return $result;
		}
		if ( ! array_key_exists( 'editor_post_id', $_REQUEST ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			return $result;
		}
		if ( $this->base->plugin->name !== get_post_type( absint( $_REQUEST['editor_post_id'] ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			return $result;
		}

		// Return a modified screen array to tell the Screen class that we're editing a Content Group.
		return array(
			'screen'  => 'content_groups',
			'section' => 'edit',
		);

	}

	/**
	 * Removes orphaned Elementor metadata in the Group Settings during Generation,
	 * if Elementor is not active
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

		// Remove Elementor Meta Keys from the Group Settings during Generation.
		return $this->remove_orphaned_settings_metadata( $settings, $this->meta_keys );

	}

	/**
	 * JSON decodes Elementor's Page Builder metadata into an array for a Content Group,
	 * so that the Generate Routine can iterate through it, replacing Keywords, Shortcodes etc.
	 *
	 * @since   2.6.1
	 *
	 * @param   string $value  Elementor Page Builder Data.
	 * @return  string          Elementor Page Builder Data
	 */
	public function elementor_decode_meta( $value ) {

		// JSON decode Elementor's data.
		if ( is_string( $value ) && ! empty( $value ) ) {
			$value = json_decode( $value, true );
		}
		if ( empty( $value ) ) {
			$value = array();
		}

		return $value;

	}

	/**
	 * Disable processing Shortcodes on the main Post Content when the Content Group is edited using Elementor,
	 * as the Post Content isn't output by Elementor.
	 *
	 * @since   2.6.1
	 *
	 * @param   bool  $process    Process Shortcodes on Post Content.
	 * @param   array $settings   Group Settings.
	 * @return  bool                Process Shortcodes on Post Content
	 */
	public function elementor_should_process_shortcodes_on_post_content( $process, $settings ) {

		// Honor the original status for processing shortcodes on content if no Post Meta.
		if ( ! isset( $settings['post_meta'] ) ) {
			return $process;
		}

		// Honor the original status for processing shortcodes on content if we're not using Elementor.
		if ( ! isset( $settings['post_meta']['_elementor_edit_mode'] ) ) {
			return $process;
		}
		if ( $settings['post_meta']['_elementor_edit_mode'] !== 'builder' ) {
			return $process;
		}

		// We're using Elementor for this Content Group, so don't process shortcodes on the Post Content
		// as the Post Content isn't used.
		return false;

	}

	/**
	 * JSON encodes Elementor's Page Builder metadata into a string immediately before it's
	 * copied to the Generated Page.
	 *
	 * @since   2.6.1
	 *
	 * @param   string|array $value   Elementor Page Builder Data.
	 * @return  string                  Elementor Page Builder Data
	 */
	public function elementor_encode_meta( $value ) {

		// Bail if the value has already been JSON encoded.
		if ( is_string( $value ) ) {
			return $value;
		}

		// Encode with slashes, just how Elementor does.
		return wp_slash( wp_json_encode( $value ) );

	}

	/**
	 * Sets the _elementor_template_type meta value = landing-page when generating
	 * Landing Pages.
	 *
	 * By default, this value is wp-post, but for Elementor's
	 * Landing Pages, it must be landing-page.
	 *
	 * This prevents a fatal error at Elementor Pro > Landing Pages, where
	 *
	 * @since   4.3.9
	 *
	 * @param   string $value      Template Type.
	 * @param   int    $post_id    Generated Post ID.
	 * @param   int    $group_id   Group ID.
	 * @param   array  $settings   Group Settings.
	 * @param   array  $post_args  wp_insert_post() / wp_update_post() arguments.
	 * @return  string              Template Type.
	 */
	public function elementor_set_template_type( $value, $post_id, $group_id, $settings, $post_args ) {

		// Don't change anything if not generating an Elementor Landing Page.
		if ( $settings['type'] !== 'e-landing-page' ) {
			return $value;
		}

		return 'landing-page';

	}

	/**
	 * If the given Content Group's content contains Widgets registered by this Plugin, converts them
	 * to Text Widgets with their content set to the shortcode syntax, so subsequent generation routines can parse them.
	 *
	 * @since   3.0.8
	 *
	 * @param   array $settings       Group Settings.
	 * @return  array                   Group Settings
	 */
	public function convert_widgets_to_text_widgets( $settings ) {

		// Bail if no Elementor Data exists.
		if ( ! isset( $settings['post_meta']['_elementor_data'] ) ) {
			return $settings;
		}

		// Get shortcodes.
		$shortcodes = $this->base->get_class( 'shortcode' )->get_shortcodes();

		// Bail if no shortcodes are available.
		if ( ! is_array( $shortcodes ) || ! count( $shortcodes ) ) {
			return $settings;
		}

		// Iterate through Shortcodes.
		foreach ( $shortcodes as $shortcode_name => $shortcode_properties ) {
			// Skip if this shortcode is set to register outside of Content Groups.
			if ( ! $shortcode_properties['register_on_generation_only'] ) {
				continue;
			}

			// Recursively itereate through the array, replacing any elements of type matching this shortcode
			// to Text Widgets.
			$this->recursively_replace_elements_by_type( $settings['post_meta']['_elementor_data'], $this->base->plugin->name . '-elementor-' . $shortcode_name, $shortcode_properties['fields'] );
		}

		// Return.
		return $settings;

	}

	/**
	 * Recursively iterate through Elementor Data, replacing any Widgets whose type matches the
	 * given Shortcode Name with a Text Widget.
	 *
	 * No return value is needed, as $arr is passed by reference.
	 *
	 * @since   3.0.8
	 *
	 * @param   array  $arr                    Elementor Data or Sub-Data.
	 * @param   string $shortcode_name         Shortcode Name to replace.
	 * @param   array  $shortcode_fields       Shortcode Fields.
	 */
	private function recursively_replace_elements_by_type( &$arr, $shortcode_name, $shortcode_fields ) {

		// Iterate through array.
		foreach ( $arr as $key => &$item ) {

			// If modules exist, call this function again against the modules (going down one level).
			if ( isset( $item['elements'] ) ) {
				if ( count( $item['elements'] ) ) {
					$this->recursively_replace_elements_by_type( $item['elements'], $shortcode_name, $shortcode_fields );
				}
			}

			// If the item's type matches our shortcode name, replace it with a Text Widget
			// comprising of the Shortcode's syntax, so that it's processed when the generation
			// routine runs.
			if ( isset( $item['widgetType'] ) ) {
				if ( $item['widgetType'] === $shortcode_name ) {
					$item['widgetType']         = 'text-editor';
					$item['settings']['editor'] = Page_Generator_Pro()->get_class( 'shortcode' )->build_shortcode(
						str_replace( '-elementor', '', $shortcode_name ),
						$shortcode_fields,
						$item['settings']
					);
				}
			}
		}

	}

	/**
	 * Clears Elementor caches after Generate Content finishes, so that CSS files are regenerated
	 * to prevent errors and save the user having to go to Elementor > Tools > Regenerate CSS
	 *
	 * @since   3.0.8
	 */
	public function clear_cache() {

		// Bail if Elementor isn't active.
		if ( ! $this->is_active() ) {
			return;
		}

		Elementor\Plugin::$instance->files_manager->clear_cache();

	}


	/**
	 * Stores the Post ID being viewed, for use in frontend_replace_keywords().
	 *
	 * @since   4.3.2
	 *
	 * @param   array $data       Elementor Builder Data.
	 * @param   int   $post_id    Post ID.
	 * @return  array
	 */
	public function frontend_get_post_id( $data, $post_id ) {

		$this->post_id = $post_id;
		return $data;

	}

	/**
	 * Replaces Keywords with Custom Field values for any Elementor Global Widgets
	 * in the given content.
	 *
	 * @since   4.3.2
	 *
	 * @param   string $content    Elementor Content.
	 * @return  string
	 */
	public function frontend_replace_keywords( $content ) {

		// Replace Keywords with Generated Page's Custom Field values.
		return $this->replace_keywords_with_custom_field_values( $content, $this->post_id );

	}

	/**
	 * Checks if Elementor is active
	 *
	 * @since   3.0.8
	 *
	 * @return  bool    Is Active
	 */
	public function is_active() {

		return class_exists( 'Elementor\Widget_Base' );

	}

}
