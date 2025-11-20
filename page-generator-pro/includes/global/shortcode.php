<?php
/**
 * Shortcode Class
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Registers Dynamic Elements as Shortcodes.
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 1.0.0
 */
class Page_Generator_Pro_Shortcode {

	/**
	 * Holds the base object.
	 *
	 * @since   1.2.1
	 *
	 * @var     object
	 */
	public $base;

	/**
	 * Constructor
	 *
	 * @since   1.0.0
	 *
	 * @param   object $base    Base Plugin Class.
	 */
	public function __construct( $base ) {

		// Store base class.
		$this->base = $base;

		// Hooks and Filters.
		add_action( 'init', array( $this, 'add_shortcodes' ), 10, 1 );
		add_action( 'wp_head', array( $this, 'maybe_load_js' ) );
		add_filter( 'wp_get_custom_css', array( $this, 'maybe_load_css' ), 10 );
		add_filter( 'the_content', array( $this, 'maybe_change_css_prefix_content' ) );

	}

	/**
	 * Registers the shortcodes used by this plugin, depending on whether we're running
	 * content generation for a Group or not.
	 *
	 * @since   1.2.0
	 *
	 * @param   bool $generating_group   Generating Group.
	 */
	public function add_shortcodes( $generating_group = false ) {

		// Get shortcodes.
		$shortcodes = $this->get_shortcodes();

		// Bail if no shortcodes are available.
		if ( ! is_array( $shortcodes ) || count( $shortcodes ) === 0 ) {
			return;
		}

		// Get CSS Prefix.
		$css_prefix = $this->get_css_prefix();

		// Iterate through shortcodes, registering them.
		foreach ( $shortcodes as $shortcode => $properties ) {

			// Skip if this shortcode should only be registered WHEN generating content.
			if ( ! $generating_group && $properties['register_on_generation_only'] ) {
				continue;
			}

			// Skip if this shortcode should only be registered when NOT generating content.
			if ( $generating_group && ! $properties['register_on_generation_only'] ) {
				continue;
			}

			// Skip if the shortcode does not have a render callback.
			if ( ! is_array( $properties['render_callback'] ) ) {
				continue;
			}

			// Register the shortcode.
			add_shortcode(
				$this->base->plugin->name . '-' . $shortcode,
				array(
					$this->base->get_class( $properties['render_callback'][0] ), // e.g. $this->base->get_class( 'shortcode_google_map' ).
					$properties['render_callback'][1], // e.g. 'render'.
				)
			);

			// If a CSS Prefix is specified, and this is a shortcode that can be used outside of Content Groups
			// register the actual CSS prefix as an additional shortcode.
			if ( $css_prefix === $this->base->plugin->name ) {
				continue;
			}
			if ( $properties['register_on_generation_only'] ) {
				continue;
			}

			add_shortcode(
				$css_prefix . '-' . $shortcode,
				array(
					$this->base->get_class( $properties['render_callback'][0] ), // e.g. $this->base->get_class( 'shortcode_google_map' ).
					$properties['render_callback'][1], // e.g. 'render'.
				)
			);

		}

	}

	/**
	 * Outputs the contents of this Plugin's frontend.js file to the WordPress
	 * header as an inline script, if OpenStreetMap markup is present.
	 *
	 * We do this instead of enqueueing JS to avoid what people believe is
	 * the 'footprint' problem.
	 *
	 * @since   2.3.4
	 */
	public function maybe_load_js() {

		global $post;

		// Bail if in the admin interface.
		if ( function_exists( 'is_admin' ) && is_admin() ) {
			return;
		}

		// Bail if Post object is empty.
		if ( is_null( $post ) || ! isset( $post->post_content ) ) {
			return;
		}

		// Get shortcodes requiring JS.
		$shortcodes_requiring_js = $this->get_shortcodes_requiring_js();

		// If no shortcodes require JS,bail.
		if ( ! $shortcodes_requiring_js ) {
			return;
		}

		// Iterate through shortcodes, returning frontend JS
		// if a shortcode or shortcode HTML is found.
		foreach ( $shortcodes_requiring_js as $shortcode_name ) {
			// Skip this shortcode if it's not in the content.
			if ( ! $this->in_content( $shortcode_name, $post ) ) {
				continue;
			}

			// If here, shortcode is in the content.
			// Fetch Frontend JS.
			$plugin_js = $this->base->get_class( 'common' )->file_get_contents( $this->base->plugin->folder . '/assets/js/min/frontend-min.js' );

			// Bail if none found.
			if ( empty( $plugin_js ) ) {
				return;
			}

			// Output.
			echo '<script>' . $plugin_js . '</script>'; // phpcs:ignore WordPress.Security.EscapeOutput
			return;

		}

	}

	/**
	 * Determine if the given shortcode name exists within the Post's Content
	 *
	 * @since   3.0.8
	 *
	 * @param   string  $shortcode_name         Shortcode Name.
	 * @param   WP_Post $post                   WordPress Post.
	 * @return  bool                            Shortcode detected in Content
	 */
	private function in_content( $shortcode_name, $post ) {

		// Determine if shortcode is in the Post's Content.
		if ( strpos( $post->post_content, $shortcode_name ) !== false ) {
			return true;
		}

		// Review Post Meta.
		$post_meta = get_post_meta( $post->ID );
		if ( ! $post_meta ) {
			return false;
		}
		foreach ( $post_meta as $key => $value ) {
			// Skip if the value isn't a string.
			if ( ! is_string( $value[0] ) ) {
				continue;
			}

			if ( strpos( $value[0], $shortcode_name ) !== false ) {
				return true;
			}
		}

		return false;

	}

	/**
	 * Appends the contents of this Plugin's frontend.css file to the WordPress
	 * Theme Customizer Additional CSS.
	 *
	 * We do this instead of enqueueing CSS to avoid what people believe is
	 * the 'footprint' problem.
	 *
	 * @since   2.0.4
	 *
	 * @param   string $customizer_css     Customizer CSS.
	 * @return  string                      Customizer CSS
	 */
	public function maybe_load_css( $customizer_css ) {

		global $post;

		// Bail if in the admin interface.
		if ( function_exists( 'is_admin' ) && is_admin() ) {
			return $customizer_css;
		}

		// Bail if Post object is empty.
		if ( is_null( $post ) || ! isset( $post->post_content ) ) {
			return $customizer_css;
		}

		// Bail if CSS Output is disabled.
		if ( ! $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-general', 'css_output', 1 ) ) {
			return $customizer_css;
		}

		// Get shortcodes requiring CSS.
		$shortcodes_requiring_css = $this->get_shortcodes_requiring_css();

		// If no shortcodes require CSS, just return the customizer CSS.
		if ( ! $shortcodes_requiring_css ) {
			return $customizer_css;
		}

		// Iterate through shortcodes, returning frontend CSS with customizer CSS
		// if a shortcode or shortcode HTML is found.
		foreach ( $shortcodes_requiring_css as $shortcode_name ) {
			if ( strpos( $post->post_content, $shortcode_name ) ) {
				return $this->append_css_to_customizer_css( $customizer_css );
			}
		}

		// If here, we don't need to load any frontend CSS.
		// Just return the customizer CSS.
		return $customizer_css;

	}

	/**
	 * Appends this Plugin's CSS to the Theme Customizer CSS, changing
	 * the CSS Prefix if necessary
	 *
	 * @since   2.3.4
	 *
	 * @param   string $customizer_css     Customizer CSS.
	 * @return  string                      Customizer CSS
	 */
	private function append_css_to_customizer_css( $customizer_css ) {

		// Fetch Frontend CSS.
		$plugin_css = $this->base->get_class( 'common' )->file_get_contents( $this->base->plugin->folder . '/assets/css/frontend.css' );

		// Change prefixes, if required.
		$plugin_css = $this->change_css_prefix( $plugin_css );

		// If the Customizer CSS already contains the Frontend CSS, bail.
		if ( strpos( $customizer_css, $plugin_css ) !== false ) {
			return $customizer_css;
		}

		// Append CSS.
		return $customizer_css . "\n" . $plugin_css;

	}

	/**
	 * Replaces CSS prefix in the content
	 *
	 * @since   2.0.3
	 *
	 * @param   string $content    Content.
	 * @return  string              Content
	 */
	public function maybe_change_css_prefix_content( $content ) {

		// Don't change the CSS prefix if this a Gutenberg editor request,
		// as the block CSS won't then apply.
		if ( $this->base->get_class( 'common' )->is_rest_api_request() ) {
			return $content;
		}

		// Change CSS prefix.
		$content = $this->change_css_prefix( $content );

		// Return.
		return $content;

	}

	/**
	 * Returns an array comprising of plugin specific Shortcodes,
	 * and their attributes.
	 *
	 * This is used by both TinyMCE, Gutenberg and Page Builders, so that Shortcodes
	 * are registered as Shortcodes, Blocks and Page Builder Elements.
	 *
	 * @since   2.0.5
	 *
	 * @param   bool $exclude_if_no_render_callback  Exclude shortcodes that don't have a render_callback function.
	 * @return  bool|array
	 */
	public function get_shortcodes( $exclude_if_no_render_callback = false ) {

		$shortcodes = apply_filters( 'page_generator_pro_shortcode_add_shortcodes', array() );

		// Return shortcodes now if we don't need to filter by render_callback.
		if ( ! $exclude_if_no_render_callback ) {
			return $shortcodes;
		}

		// Remove shortcodes where render_callback is false.
		foreach ( $shortcodes as $shortcode_name => $shortcode ) {
			if ( ! $shortcode['render_callback'] ) {
				unset( $shortcodes[ $shortcode_name ] );
			}
		}

		return $shortcodes;

	}

	/**
	 * Returns an array comprising of plugin specific Shortcodes,
	 * and their attributes, for shortcodes that register on generation only
	 *
	 * This is used by both TinyMCE, Gutenberg and Page Builders, so that Shortcodes
	 * are registered as Shortcodes, Blocks and Page Builder Elements.
	 *
	 * @since   2.0.5
	 *
	 * @param   bool $exclude_if_no_render_callback  Exclude shortcodes that don't have a render_callback function.
	 * @return  bool|array
	 */
	public function get_shortcode_supported_outside_of_content_groups( $exclude_if_no_render_callback = false ) {

		$shortcodes = apply_filters( 'page_generator_pro_shortcode_add_shortcodes_outside_of_content_groups', array() );

		// Return shortcodes now if we don't need to filter by render_callback.
		if ( ! $exclude_if_no_render_callback ) {
			return $shortcodes;
		}

		// Remove shortcodes where render_callback is false.
		foreach ( $shortcodes as $shortcode_name => $shortcode ) {
			if ( ! $shortcode['render_callback'] ) {
				unset( $shortcodes[ $shortcode_name ] );
			}
		}

		return $shortcodes;

	}

	/**
	 * Returns an array comprising of plugin specific Shortcodes,
	 * and their attributes, where the shortcode's get_keywords()
	 * function has the supplied Keyword.
	 *
	 * This is used by both TinyMCE, Gutenberg and Page Builders, so that Shortcodes
	 * are registered as Shortcodes, Blocks and Page Builder Elements.
	 *
	 * @since   4.6.8
	 *
	 * @param   string $keyword    Keyword.
	 * @return  bool|array
	 */
	public function get_shortcodes_by_keyword( $keyword ) {

		$shortcodes = $this->get_shortcodes();

		foreach ( $shortcodes as $shortcode_name => $shortcode ) {
			// Remove if this shortcode doesn't contain the Keyword.
			if ( ! in_array( $keyword, $shortcode['keywords'], true ) ) {
				unset( $shortcodes[ $shortcode_name ] );
			}
		}

		return $shortcodes;

	}

	/**
	 * Returns the given shortcode's properties.
	 *
	 * @since   2.0.5
	 *
	 * @param   string $name   Shortcode Name.
	 * @return  bool|array
	 */
	public function get_shortcode( $name ) {

		// Get shortcodes.
		$shortcodes = $this->get_shortcodes();

		// Bail if no shortcodes are registered.
		if ( ! is_array( $shortcodes ) ) {
			return false;
		}
		if ( ! isset( $shortcodes[ $name ] ) ) {
			return false;
		}

		return $shortcodes[ $name ];

	}

	/**
	 * Returns Shortcode Names requiring CSS.
	 *
	 * This can be used to determine if we need to load frontend CSS for a given Page.
	 *
	 * @since   2.3.4
	 *
	 * @return  bool|array   Shortcode Names
	 */
	private function get_shortcodes_requiring_css() {

		// Fetch all shortcodes.
		$shortcodes = $this->get_shortcodes();

		// Bail if the given shortcode name has not been registered.
		if ( ! is_array( $shortcodes ) || count( $shortcodes ) === 0 ) {
			return false;
		}

		$shortcodes_requiring_css = array();
		foreach ( $shortcodes as $shortcode_name => $shortcode ) {
			if ( ! $shortcode['requires_css'] ) {
				continue;
			}

			$shortcodes_requiring_css[] = $shortcode_name;
		}

		// If no shortcodes, return false.
		if ( ! count( $shortcodes_requiring_css ) ) {
			return false;
		}

		// Return shortcode names requiring CSS.
		return $shortcodes_requiring_css;

	}

	/**
	 * Returns Shortcode Names requiring JS.
	 *
	 * This can be used to determine if we need to load frontend JS for a given Page.
	 *
	 * @since   2.3.4
	 *
	 * @return  bool|array   Shortcode Names
	 */
	public function get_shortcodes_requiring_js() {

		// Fetch all shortcodes.
		$shortcodes = $this->get_shortcodes();

		// Bail if the given shortcode name has not been registered.
		if ( ! is_array( $shortcodes ) || count( $shortcodes ) === 0 ) {
			return false;
		}

		$shortcodes_requiring_js = array();
		foreach ( $shortcodes as $shortcode_name => $shortcode ) {
			if ( ! $shortcode['requires_js'] ) {
				continue;
			}

			$shortcodes_requiring_js[] = $shortcode_name;
		}

		// If no shortcodes, return false.
		if ( ! count( $shortcodes_requiring_js ) ) {
			return false;
		}

		// Return shortcode names requiring JS.
		return $shortcodes_requiring_js;

	}

	/**
	 * Helper function to try and fetch the Group ID
	 *
	 * This is then used by Shortcodes to store Attachments against
	 * the given Group ID.
	 *
	 * @since   2.4.1
	 *
	 * @return  int     Group ID
	 */
	public function get_group_id() {

		global $page_generator_pro_group_id;

		if ( ! empty( $page_generator_pro_group_id ) ) {
			return $page_generator_pro_group_id;
		}

		return 0;

	}

	/**
	 * Helper function to try and fetch the Index
	 *
	 * This is then used by Shortcodes to store Attachments against
	 * the given Generation Index.
	 *
	 * @since   2.4.1
	 *
	 * @return  int     Index
	 */
	public function get_index() {

		global $page_generator_pro_index;

		if ( ! empty( $page_generator_pro_index ) ) {
			return $page_generator_pro_index;
		}

		return 0;

	}

	/**
	 * Returns the CSS prefix to use.
	 *
	 * @since   2.0.3
	 *
	 * @return  string  CSS Prefix
	 */
	private function get_css_prefix() {

		// Get prefix, if defined historically in the Plugin's settings.
		// 3.9.0 no longer provides a CSS Prefix setting, as it will always be generated from the site's URL.
		$css_prefix = trim( $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-general', 'css_prefix' ) );

		// Fallback to site URL if no prefix specified.
		if ( empty( $css_prefix ) ) {
			$url = wp_parse_url( get_option( 'siteurl' ) );

			// Strip non-alphanumeric characters and hyphens.
			$css_prefix = preg_replace( '/[^\\w-]/', '', $url['host'] );
		}

		/**
		 * Returns the CSS prefix to use.
		 *
		 * @since   2.0.3
		 *
		 * @param   string    $css_prefix   CSS Prefix to use.
		 */
		$css_prefix = apply_filters( 'page_generator_pro_shortcode_get_css_prefix', $css_prefix );

		// Return.
		return $css_prefix;

	}

	/**
	 * Changes the default Plugin CSS Prefix for the one specified in the Plugin Settings
	 *
	 * @since   2.0.3
	 *
	 * @param   string $content    Content.
	 * @return  string              Amended Content
	 */
	private function change_css_prefix( $content ) {

		// Get CSS Prefix.
		$css_prefix = $this->get_css_prefix();

		// Bail if it matches the Plugin Name.
		if ( $css_prefix === $this->base->plugin->name ) {
			return $content;
		}

		// Replace prefix.
		$content = str_replace( $this->base->plugin->name, $css_prefix, $content );

		// Return.
		return $content;

	}

	/**
	 * Converts the given array of Shortcode attributes into a WordPress
	 * shortcode string
	 *
	 * Any attributes defined that don't have a field are discarded
	 * (e.g. if attributes are defined in a widget / element from a Page Builder)
	 *
	 * @since   3.0.8
	 *
	 * @param   string $shortcode_name     Shortcode Name.
	 * @param   array  $shortcode_fields   Shortcode Fields.
	 * @param   array  $atts               Shortcode Attributes (Field Values).
	 * @return  string                      Shortcode
	 */
	public function build_shortcode( $shortcode_name, $shortcode_fields, $atts ) {

		$shortcode_html = $shortcode_name;
		foreach ( $shortcode_fields as $field_name => $field ) {
			// Skip if this field isn't defined in the atts.
			if ( ! isset( $atts[ $field_name ] ) ) {
				continue;
			}

			// Get value.
			$value = $atts[ $field_name ];

			// Skip empty strings.
			if ( ! is_array( $value ) && ! strlen( $value ) ) {
				continue;
			}

			// Skip empty arrays.
			if ( is_array( $value ) && ! count( $value ) ) {
				continue;
			}

			// Depending on the field type, build the shortcode.
			switch ( $field['type'] ) {
				case 'repeater':
					// Calculate which sub field is the shortcode key, and which is the shortcode value.
					$shortcode_key_field_prepend = '';
					foreach ( $field['sub_fields'] as $sub_field_name => $sub_field ) {
						// If the data-shortcode attribute is empty, the values of this sub field
						// form the shortcode attribute keys.
						if ( empty( $sub_field['data']['shortcode'] ) ) {
							$shortcode_key_field = $sub_field_name;
							continue;
						}

						// If a prefix exists, prepend $sub_field_name with it.
						if ( isset( $sub_field['data']['shortcode-prepend'] ) ) {
							$shortcode_key_field_prepend = $sub_field['data']['shortcode-prepend'];
						}

						// This sub field is the value.
						$shortcode_value_field = $sub_field_name;
					}

					// Iterate through repeater values, building shortcode, now that we know
					// which value is the key and which is the value.
					foreach ( $value as $sub_value ) {
						// Skip if the shortcode key field is not defined.
						if ( ! isset( $shortcode_key_field ) ) {
							continue;
						}
						if ( ! isset( $shortcode_value_field ) ) {
							continue;
						}

						// Skip if the sub value's field is empty.
						if ( empty( $sub_value[ $shortcode_key_field ] ) ) {
							continue;
						}

						$shortcode_html .= ' ' . $shortcode_key_field_prepend . $sub_value[ $shortcode_key_field ] . '="' . $sub_value[ $shortcode_value_field ] . '"';
					}
					break;

				default:
					// Convert array to string.
					if ( is_array( $value ) ) {
						$delimiter = ( isset( $field['data']['delimiter'] ) ? $field['data']['delimiter'] : ',' );
						$value     = implode( $delimiter, $value );
					}

					$shortcode_html .= ' ' . $field_name . '="' . $value . '"';
					break;
			}
		}

		// Return shortcode string.
		return '[' . $shortcode_html . ']';

	}

}
