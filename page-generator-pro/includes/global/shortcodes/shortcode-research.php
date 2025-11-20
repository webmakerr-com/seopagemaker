<?php
/**
 * Research Class
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Research Dynamic Element
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 4.1.0
 */
class Page_Generator_Pro_Shortcode_Research {

	use Page_Generator_Pro_Shortcode_Trait;

	/**
	 * Holds the base object.
	 *
	 * @since   4.1.0
	 *
	 * @var     object
	 */
	public $base;

	/**
	 * Constructor
	 *
	 * @since   4.1.0
	 *
	 * @param   object $base    Base Plugin Class.
	 */
	public function __construct( $base ) {

		// Store base class.
		$this->base = $base;

		// Register shortcode.
		add_filter( 'page_generator_pro_shortcode_add_shortcodes', array( $this, 'add_shortcode' ) );

		// Register shortcode outside of Content Groups e.g. on Pages, Posts.
		add_filter( 'page_generator_pro_shortcode_add_shortcodes_outside_of_content_groups', array( $this, 'add_shortcode' ) );

	}

	/**
	 * Returns this shortcode / block's programmatic name.
	 *
	 * @since   4.1.0
	 */
	public function get_name() {

		return 'research';

	}

	/**
	 * Returns this shortcode / block's title.
	 *
	 * @since   4.5.2
	 *
	 * @return  string
	 */
	public function get_title() {

		return __( 'Research', 'page-generator-pro' );

	}

	/**
	 * Returns this shortcode / block's description.
	 *
	 * @since   4.5.2
	 *
	 * @return string
	 */
	public function get_description() {

		return __( 'Displays AI generated content based on a topic.', 'page-generator-pro' );

	}

	/**
	 * Returns this shortcode / block's Keywords, excluding the title.
	 *
	 * @since   4.5.2
	 *
	 * @return  array
	 */
	public function get_keywords() {

		return array(
			'Research',
		);

	}

	/**
	 * Returns this shortcode / block's icon.
	 *
	 * @since   4.5.2
	 *
	 * @return  string
	 */
	public function get_icon() {

		return '_modules/dashboard/feather/book.svg';

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

		return false;

	}

	/**
	 * Returns this shortcode / block's class name and render callback function name.
	 *
	 * @since   4.5.2
	 *
	 * @return  array|bool
	 */
	public function get_render_callback() {

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

		return true;

	}

	/**
	 * Returns this block's Attributes and default values for Gutenberg.
	 *
	 * @since   4.1.0
	 */
	public function get_attributes() {

		// Get provider.
		$provider = $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-research', 'provider' );

		/**
		 * Returns the research shortcode / block's field attributes.
		 *
		 * @since   4.8.0
		 */
		$attributes = apply_filters( 'page_generator_pro_shortcode_research_get_attributes_' . $provider, array() );

		// Return.
		return $attributes;

	}

	/**
	 * Returns this shortcode / block's Fields
	 *
	 * @since   4.1.0
	 */
	public function get_fields() {

		// Get provider.
		$provider = $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-research', 'provider' );

		/**
		 * Returns the research shortcode / block's configuration fields.
		 *
		 * @since   4.8.0
		 */
		$fields = apply_filters( 'page_generator_pro_shortcode_research_get_fields_' . $provider, false );

		// Return.
		return $fields;

	}

	/**
	 * Returns this shortcode / block's UI Tabs
	 *
	 * @since   4.1.0
	 */
	public function get_tabs() {

		// Get provider.
		$provider = $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-research', 'provider' );

		/**
		 * Returns the research shortcode / block's tabs.
		 *
		 * @since   4.8.0
		 */
		$tabs = apply_filters( 'page_generator_pro_shortcode_research_get_tabs_' . $provider, array() );

		// Return.
		return $tabs;

	}

	/**
	 * Returns this shortcode / block's Default Values
	 *
	 * @since   4.1.0
	 */
	public function get_default_values() {

		// Get provider.
		$provider = $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-research', 'provider' );

		/**
		 * Returns the research shortcode / block's default configuration values.
		 *
		 * @since   4.8.0
		 */
		$default_values = apply_filters( 'page_generator_pro_shortcode_research_get_default_values_' . $provider, array() );

		// Return.
		return $default_values;

	}

	/**
	 * Returns this shortcode / block's output
	 *
	 * @since   4.1.0
	 *
	 * @param  array $atts   Shortcode Attributes.
	 * @return string          Output
	 */
	public function render( $atts ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter

		return '';

	}

}
