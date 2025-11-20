<?php
/**
 * Bricks Visual Website Builder Integration Class
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Registers Bricks Visual Website Builder as a Plugin integration:
 * - Copy / don't copy metadata to generated Pages, depending on if the integration is active
 * - Decode/encode Page Builder metadata when generating Pages
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 3.8.0
 */
class Page_Generator_Pro_Bricks extends Page_Generator_Pro_Integration {

	/**
	 * Holds the base object.
	 *
	 * @since   3.8.0
	 *
	 * @var     object
	 */
	public $base;

	/**
	 * Constructor
	 *
	 * @since   3.8.0
	 *
	 * @param   object $base    Base Plugin Class.
	 */
	public function __construct( $base ) {

		// Store base class.
		$this->base = $base;

		// Set Theme Name.
		$this->theme_name = 'Bricks';

		// Set Meta Keys used by this Theme.
		$this->meta_keys = array(
			'/^_bricks_(.*)/i',
		);

		// Set current screen to editing a Content Group when editing with Bricks.
		add_filter( 'page_generator_pro_screen_get_current_screen_before', array( $this, 'bricks_set_current_screen' ), 10, 2 );

		// Register category for Bricks Elements.
		add_filter( 'bricks/builder/i18n', array( $this, 'register_bricks_elements_category' ) );

		// Register shortcodes as Bricks Elements.
		add_action( 'init', array( $this, 'register_elements' ), 11 );

		// Convert Dynamic Elements to Rich Text Elements.
		add_filter( 'page_generator_pro_generate_content_settings', array( $this, 'convert_dynamic_elements_to_rich_text_elements' ), 10, 1 );

		// Remove Page Builder data from Group Settings if overwriting content is disabled, and an existing generated page already exists.
		add_filter( 'page_generator_pro_generate_remove_content_from_settings_ignored_keys', array( $this, 'remove_post_meta_from_content_group' ), 10, 2 );

		// Remove Plugin data from Group Settings if Plugin isn't active on Generation.
		add_filter( 'page_generator_pro_groups_get_settings_remove_orphaned_settings', array( $this, 'remove_orphaned_settings' ) );

		// Geneate CSS.
		add_action( 'page_generator_pro_generate_content_finished', array( $this, 'regenerate_css' ), 10, 1 );

	}

	/**
	 * Tells the Screen class that we're editing a Content Group when editing it with Bricks.
	 *
	 * If we don't do this, Dynamic Elements won't be registered in register_elements() below,
	 * as Bricks makes REST API calls to fetch elements with the server header HTTP_X_BRICKS_IS_BUILDER.
	 *
	 * @since   5.2.7
	 *
	 * @param   array $result     Screen and Section.
	 * @return  array                   Screen and Section
	 */
	public function bricks_set_current_screen( $result ) {

		if ( ! array_key_exists( 'HTTP_X_BRICKS_IS_BUILDER', $_SERVER ) ) {
			return $result;
		}

		if ( ! array_key_exists( 'HTTP_REFERER', $_SERVER ) ) {
			return $result;
		}

		if ( strpos( sanitize_text_field( wp_unslash( $_SERVER['HTTP_REFERER'] ) ), '/page-generator-pro/' ) === false ) {
			return $result;
		}

		// Return a modified screen array to tell the Screen class that we're editing a Content Group.
		return array(
			'screen'  => 'content_groups',
			'section' => 'edit',
		);

	}

	/**
	 * Registers the category for Bricks Elements.
	 *
	 * @since   5.2.7
	 *
	 * @param   array $i18n   I18n.
	 * @return  array           I18n
	 */
	public function register_bricks_elements_category( $i18n ) {

		$i18n['page-generator-pro'] = $this->base->plugin->displayName;
		return $i18n;

	}

	/**
	 * Registers Bricks elements for the shortcodes.
	 *
	 * @since   5.2.7
	 */
	public function register_elements() {

		// Bail if Bricks isn't active.
		if ( ! $this->is_theme_active() ) {
			return;
		}

		// Determine the screen that we're on.
		$screen = $this->base->get_class( 'screen' )->get_current_screen();

		// Get shortcodes, depending on whether we're editing a Content Group or Post.
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

			// Get class and file name.
			$class_name = 'Page_Generator_Pro_Bricks_Element_' . str_replace( '-', '_', $shortcode );
			$file_name  = PAGE_GENERATOR_PRO_PLUGIN_PATH . 'includes/admin/integrations/bricks/bricks-element-' . $shortcode . '.php';

			// Skip if file does not exist.
			if ( ! file_exists( $file_name ) ) {
				continue;
			}

			// Register element.
			\Bricks\Elements::register_element(
				PAGE_GENERATOR_PRO_PLUGIN_PATH . 'includes/admin/integrations/bricks/bricks-element-' . $shortcode . '.php',
				'page-generator-pro-bricks-element-' . $shortcode,
				$class_name
			);

		}

	}

	/**
	 * Removes orphaned metadata in the Group Settings during Generation,
	 * if Bricks is not active.
	 *
	 * @since   3.8.0
	 *
	 * @param   array $settings   Group Settings.
	 * @return  array               Group Settings
	 */
	public function remove_orphaned_settings( $settings ) {

		// Don't remove settings if the Theme is active.
		if ( $this->is_theme_active() ) {
			return $settings;
		}

		// Remove Meta Keys from the Group Settings during Generation.
		return $this->remove_orphaned_settings_metadata( $settings, $this->meta_keys );

	}

	/**
	 * Converts Dynamic Elements to Rich Text elements.
	 *
	 * @since   5.2.7
	 *
	 * @param   array $settings       Group Settings.
	 * @return  array                   Group Settings
	 */
	public function convert_dynamic_elements_to_rich_text_elements( $settings ) {

		// Bail if Bricks isn't active.
		if ( ! $this->is_theme_active() ) {
			return $settings;
		}

		// Bail if no Bricks Data exists.
		if ( ! isset( $settings['post_meta']['_bricks_editor_mode'] ) ) {
			return $settings;
		}
		if ( $settings['post_meta']['_bricks_editor_mode'] !== 'bricks' ) {
			return $settings;
		}
		if ( ! isset( $settings['post_meta']['_bricks_page_content_2'] ) ) {
			return $settings;
		}

		// Recursively iterate through elements, converting Dynamic Element modules to Shortcode modules.
		$settings['post_meta']['_bricks_page_content_2'] = $this->recursively_convert_dynamic_elements_to_rich_text_elements(
			$settings['post_meta']['_bricks_page_content_2']
		);

		// Return.
		return $settings;

	}

	/**
	 * Recursively walks through an array of Live Composer modules,
	 * converting any Plugin module to a Shortcode module.
	 *
	 * @since   5.2.7
	 *
	 * @param   array $elements     Modules.
	 * @return  array               Modules
	 */
	private function recursively_convert_dynamic_elements_to_rich_text_elements( $elements ) {

		// Return if elements is not an array.
		if ( ! is_array( $elements ) ) {
			return $elements;
		}

		foreach ( $elements as $index => $element ) {

			// Skip if element is not an array.
			if ( ! is_array( $element ) ) {
				continue;
			}

			// If this element has inner elements, walk through the inner elements.
			if ( array_key_exists( 'children', $element ) && ! empty( $element['children'] ) ) {
				$elements[ $index ]['children'] = $this->recursively_convert_dynamic_elements_to_rich_text_elements( $element['children'] );
			}

			// Skip if element is missing required properties.
			if ( ! array_key_exists( 'name', $element ) ) {
				continue;
			}
			if ( ! array_key_exists( 'settings', $element ) ) {
				continue;
			}

			// Skip if not a Plugin module.
			if ( strpos( $element['name'], 'page-generator-pro-bricks-element-' ) === false ) {
				continue;
			}

			// Get Shortcode.
			$shortcode_name = strtolower( str_replace( '_', '-', str_replace( 'page-generator-pro-bricks-element-', '', $element['name'] ) ) );
			$shortcode      = $this->base->get_class( 'shortcode' )->get_shortcode( $shortcode_name );

			// Skip if the Shortcode isn't registered.
			if ( ! $shortcode ) {
				continue;
			}

			// Skip if this shortcode is set to register outside of Content Groups.
			if ( ! $shortcode['register_on_generation_only'] ) {
				continue;
			}

			// Build Shortcode.
			$shortcode_html = Page_Generator_Pro()->get_class( 'shortcode' )->build_shortcode(
				Page_Generator_Pro()->plugin->name . '-' . $shortcode_name,
				$shortcode['fields'],
				$element['settings']
			);

			// Replace Plugin element with Rich text element that we want to parse when generating the Page.
			$elements[ $index ] = array(
				'id'       => array_key_exists( 'id', $element ) ? $element['id'] : null,
				'name'     => 'text',
				'parent'   => array_key_exists( 'parent', $element ) ? $element['parent'] : null,
				'children' => array_key_exists( 'children', $element ) ? $element['children'] : array(),
				'settings' => array(
					'text' => $shortcode_html,
				),
			);
		}

		return $elements;

	}

	/**
	 * Generates the CSS cache for the generated Page, after it has been created/updated.
	 *
	 * @since   5.0.5
	 *
	 * @param   int $post_id        Generated Post ID.
	 */
	public function regenerate_css( $post_id ) {

		// Bail if Bricks isn't active.
		if ( ! $this->is_theme_active() ) {
			return;
		}

		// This is copied from Assets_Files::save() in Bricks.
		if ( \Bricks\Database::get_setting( 'cssLoading' ) !== 'file' ) {
			return;
		}

		if ( ! \Bricks\Helpers::render_with_bricks( $post_id ) ) {
			return;
		}

		$area     = \Bricks\Templates::get_template_type( $post_id );
		$elements = \Bricks\Database::get_data( $post_id, $area );
		\Bricks\Assets_Files::generate_post_css_file( $post_id, $area, $elements );

	}

}
