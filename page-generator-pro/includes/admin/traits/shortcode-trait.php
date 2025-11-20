<?php
/**
 * Shortcode Trait
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Trait for registering an integration as a Shortcode (Dynamic Element):
 * - registering a block/shortcode,
 * - parsing attributes, filling in defaults and casting variables,
 * - importing an image at random from a set
 * - producing <img> tag HTML markup
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 */
trait Page_Generator_Pro_Shortcode_Trait {

	use Page_Generator_Pro_Ignore_Errors_Trait;

	/**
	 * Holds the base object.
	 *
	 * @since   3.6.3
	 *
	 * @var     object
	 */
	public $base;

	/**
	 * Returns this shortcode / block's title.
	 *
	 * @since   4.5.2
	 *
	 * @return  string
	 */
	public function get_title() {

		return '';

	}

	/**
	 * Returns this shortcode / block's description.
	 *
	 * @since   4.5.2
	 *
	 * @return string
	 */
	public function get_description() {

		return '';

	}

	/**
	 * Returns this shortcode / block's Keywords, excluding the title.
	 *
	 * @since   4.5.2
	 *
	 * @return  array
	 */
	public function get_keywords() {

		return array();

	}

	/**
	 * Returns this shortcode / block's icon.
	 *
	 * @since   4.5.2
	 *
	 * @return  string
	 */
	public function get_icon() {

		return '';

	}

	/**
	 * Returns this shortcode / block's class name and render callback function name.
	 *
	 * @since   4.8.2
	 *
	 * @return  array
	 */
	public function get_render_callback() {

		return array( $this->get_settings_prefix(), 'render' );

	}

	/**
	 * Returns this block's Attributes and default values for Gutenberg.
	 *
	 * @since   4.0.4
	 *
	 * @return  array
	 */
	public function get_attributes() {

		return array_merge(
			$this->get_provider_attributes(),
			$this->get_ignore_errors_attributes(),
			// Preview.
			array(
				'is_gutenberg_example' => array(
					'type'    => 'boolean',
					'default' => false,
				),
			)
		);

	}

	/**
	 * Returns this block's Fields.
	 *
	 * @since   4.5.2
	 *
	 * @return  array|bool
	 */
	public function get_fields() {

		// Don't populate fields for performance if the request is for the frontend web site.
		// Populate fields for admin and CLI requests so that Generate via Browser and CLI
		// will see fields for this shortcode, which is required for correct operation with e.g. Elementor
		// registered shortcodes/elements.
		if ( ! $this->base->is_admin_or_frontend_editor() && ! $this->base->is_cli() && ! $this->base->is_cron() ) {
			return false;
		}

		return array_merge(
			$this->get_provider_fields(),
			$this->get_ignore_errors_fields()
		);

	}

	/**
	 * Returns this shortcode / block's UI Tabs
	 *
	 * @since   2.5.1
	 */
	public function get_tabs() {

		if ( ! $this->base->is_admin_or_frontend_editor() ) {
			return false;
		}

		return array_merge(
			$this->get_provider_tabs(),
			$this->get_ignore_errors_tabs()
		);

	}

	/**
	 * Returns this shortcode / block's Default Values
	 *
	 * @since   4.0.4
	 *
	 * @return  array
	 */
	public function get_default_values() {

		return array_merge(
			$this->get_provider_default_values(),
			$this->get_ignore_errors_default_values()
		);

	}

	/**
	 * Returns this shortcode / block's TinyMCE modal width and height.
	 *
	 * @since   4.5.2
	 *
	 * @return  array
	 */
	public function get_modal_dimensions() {

		return array(
			'width'  => 800,
			'height' => 600,
		);

	}

	/**
	 * Returns whether this shortcode / block needs to be registered on generation only.
	 * False will register the shortcode / block for non-Content Groups, such as Pages
	 * and Posts.
	 *
	 * @since   4.5.2
	 *
	 * @return  bool
	 */
	public function register_on_generation_only() {

		return true;

	}

	/**
	 * Returns whether this shortcode / block requires JS for output.
	 *
	 * @since   4.5.2
	 *
	 * @return  bool
	 */
	public function requires_js() {

		return false;

	}

	/**
	 * Returns whether this shortcode / block requires CSS for output.
	 *
	 * @since   4.5.2
	 *
	 * @return  bool
	 */
	public function requires_css() {

		return false;

	}

	/**
	 * Registers the image provider as a shortcode and block in Page Generator Pro
	 *
	 * @since   4.5.1
	 *
	 * @param   array $shortcodes     Shortcodes.
	 * @return  array                   Shortcodes
	 */
	public function add_shortcode( $shortcodes ) {

		// Add this shortcode to the array of registered shortcodes.
		$shortcodes[ $this->get_name() ] = array_merge(
			$this->get_overview(),
			array(
				'name'           => $this->get_name(),
				'fields'         => $this->get_fields(),
				'attributes'     => $this->get_attributes(),
				'supports'       => $this->get_supports(),
				'tabs'           => $this->get_tabs(),
				'default_values' => $this->get_default_values(),
			)
		);

		// Return.
		return $shortcodes;

	}

	/**
	 * Returns this shortcode / block's Title, Icon, Categories, Keywords
	 * and properties for registering on generation and requiring CSS/JS.
	 *
	 * @since   4.5.1
	 */
	public function get_overview() {

		$dimensions = $this->get_modal_dimensions();

		/**
		 * Sets the dimensions of TinyMCE and Quicktag modals for the given shortcode.
		 *
		 * @since   4.6.9
		 *
		 * @param   array   $dimensions Dimensions with array keys `width` and `height`.
		 * @param   string  $shortcode  Shortcode.
		 */
		$dimensions = apply_filters( 'page_generator_pro_shortcode_get_modal_dimensions', $dimensions, $this->get_name() );

		return array(
			'title'                       => $this->get_title(),
			'description'                 => $this->get_description(),
			'icon'                        => $this->base->plugin->url . $this->get_icon(),
			'category'                    => $this->base->plugin->name,
			'keywords'                    => array_merge(
				array(
					$this->get_title(),
				),
				$this->get_keywords(),
			),

			// TinyMCE / QuickTags Modal Width and Height.
			'modal'                       => $dimensions,

			// Register when Generation is running only.
			'register_on_generation_only' => $this->register_on_generation_only(),

			// Requires CSS and/or JS for output.
			'requires_css'                => $this->requires_css(),
			'requires_js'                 => $this->requires_js(),

			// Function to call when rendering the shortcode on the frontend.
			'render_callback'             => $this->get_render_callback(),

			// Gutenberg: Block Icon in Editor.
			'gutenberg_icon'              => $this->base->get_class( 'common' )->file_get_contents( $this->base->plugin->folder . $this->get_icon() ),
		);

	}

	/**
	 * Returns this block's supported built-in Attributes for Gutenberg.
	 *
	 * @since   4.5.1
	 *
	 * @return  array   Supports
	 */
	public function get_supports() {

		return array(
			'className' => true,
		);

	}

	/**
	 * Returns the default value for this Image Provider's field.
	 *
	 * @since   4.5.1
	 *
	 * @param   string $field  Field.
	 * @return  string          Value
	 */
	public function get_default_value( $field ) {

		$defaults = $this->get_default_values();
		if ( isset( $defaults[ $field ] ) ) {
			return $defaults[ $field ];
		}

		return '';

	}

	/**
	 * Performs several transformation on a block's attributes, including:
	 * - sanitization
	 * - adding attributes with default values are missing but registered by the block
	 * - cast attribute values based on their defined type
	 *
	 * These steps are performed because the attributes may be defined by a shortcode,
	 * block or third party widget/page builder's block, each of which handle attributes
	 * slightly differently.
	 *
	 * Returns a standardised attributes array.
	 *
	 * @since   4.5.1
	 *
	 * @param   array $atts   Declared attributes.
	 * @return  array           All attributes, standardised.
	 */
	public function parse_atts( $atts ) {

		// Parse shortcode attributes, defining fallback defaults if required.
		$atts = shortcode_atts(
			$this->get_default_values(),
			$this->sanitize_atts( $atts ),
			$this->base->plugin->name . '-' . $this->get_name()
		);

		// Iterate through attributes, casting them based on their attribute definition.
		$atts_definitions = $this->get_attributes();
		foreach ( $atts as $att => $value ) {
			// Skip if no definition exists for this attribute.
			if ( ! array_key_exists( $att, $atts_definitions ) ) {
				continue;
			}

			// Skip if no type exists for this attribute.
			if ( ! array_key_exists( 'type', $atts_definitions[ $att ] ) ) {
				continue;
			}

			// Cast attribute's value(s), depending on the attribute's type.
			switch ( $atts_definitions[ $att ]['type'] ) {
				case 'number':
					$atts[ $att ] = (float) $value;
					break;

				case 'boolean':
					$atts[ $att ] = (bool) $value;
					break;

				case 'array':
					// If the value isn't an array, convert it to an array using the field's delimiter
					// as the separator.
					if ( ! is_array( $value ) ) {
						$atts[ $att ] = explode( $atts_definitions[ $att ]['delimiter'], $value );
					}
					break;
			}
		}

		return $atts;

	}

	/**
	 * Called when render() results in an error; stores the WP_Error
	 * in the Page_Generator_Pro_Generate class, allowing the generation
	 * routine (whether browser, server, CLI in test or generate mode)
	 * to log and output the error.
	 *
	 * This serves as a way for Dynamic Elements to report errors,
	 * which is useful if a Dynamic Element's output is vital
	 * for correct content generation.
	 *
	 * @since   4.9.0
	 *
	 * @param   WP_Error   $error  Error.
	 * @param   bool|array $atts   Dynamic Element Attributes.
	 * @return  string              Blank string.
	 */
	public function add_dynamic_element_error_and_return( $error, $atts = false ) {

		// If ignore errors is enabled on the Dynamic Element, don't add an error.
		if ( is_array( $atts ) && array_key_exists( 'ignore_errors', $atts ) && $atts['ignore_errors'] ) {
			// If in test mode, always register the error and return.
			if ( ! defined( 'PAGE_GENERATOR_PRO_DEBUG' ) ) {
				return '';
			}

			if ( defined( 'PAGE_GENERATOR_PRO_DEBUG' ) && ! PAGE_GENERATOR_PRO_DEBUG ) {
				return '';
			}

			// Ignore errors was set, but we're in Test mode, so we need to show the error.
		}

		// Register the error with the generate class, so it is output.
		$this->base->get_class( 'generate' )->add_dynamic_element_error(
			new WP_Error(
				$error->get_error_code(),
				sprintf(
					'%s: %s',
					$this->get_title(),
					$error->get_error_message()
				)
			)
		);

		// Return a blank string.
		return '';

	}

	/**
	 * Removes any HTML that might be wrongly included in the shorcode attribute's values
	 * due to e.g. copy and pasting from Documentation or other examples.
	 *
	 * @since   4.5.1
	 *
	 * @param   array $atts   Shortcode Attributes.
	 * @return  array           Shortcode Attributes
	 */
	private function sanitize_atts( $atts ) {

		if ( ! is_array( $atts ) ) {
			return $atts;
		}

		foreach ( $atts as $key => $value ) {
			if ( is_array( $value ) ) {
				continue;
			}

			// strip_tags() doesn't accept null values.
			if ( is_null( $value ) ) {
				continue;
			}

			// wp_strip_all_tags() trims spaces, which we don't want.
			$atts[ $key ] = strip_tags( $value ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		}

		return $atts;

	}

}
