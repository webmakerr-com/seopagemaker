<?php
/**
 * Custom Field Class
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Related Links Dynamic Element
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 4.6.2
 */
class Page_Generator_Pro_Shortcode_Custom_Field {

	use Page_Generator_Pro_Shortcode_Trait;

	/**
	 * Holds the base object.
	 *
	 * @since   4.6.2
	 *
	 * @var     object
	 */
	public $base;

	/**
	 * Constructor
	 *
	 * @since   4.6.2
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
	 * @since   4.6.2
	 */
	public function get_name() {

		return 'custom-field';

	}

	/**
	 * Returns this shortcode / block's title.
	 *
	 * @since   4.6.2
	 *
	 * @return  string
	 */
	public function get_title() {

		return __( 'Custom Field', 'page-generator-pro' );

	}

	/**
	 * Returns this shortcode / block's description.
	 *
	 * @since   4.6.2
	 *
	 * @return string
	 */
	public function get_description() {

		return __( 'Displays the value from a Custom Field specified in this Post / Page.', 'page-generator-pro' );

	}

	/**
	 * Returns this shortcode / block's Keywords, excluding the title.
	 *
	 * @since   4.6.2
	 *
	 * @return  array
	 */
	public function get_keywords() {

		return array(
			__( 'Custom Field', 'page-generator-pro' ),
			__( 'Post Meta', 'page-generator-pro' ),
		);

	}

	/**
	 * Returns this shortcode / block's icon.
	 *
	 * @since   4.6.2
	 *
	 * @return  string
	 */
	public function get_icon() {

		return '_modules/dashboard/feather/file-text.svg';

	}

	/**
	 * Returns this shortcode / block's TinyMCE modal width and height.
	 *
	 * @since   4.6.2
	 *
	 * @return  array
	 */
	public function get_modal_dimensions() {

		return array(
			'width'  => 600,
			'height' => 153,
		);

	}

	/**
	 * Returns this shortcode / block's class name and render callback function name.
	 *
	 * @since   4.6.2
	 *
	 * @return  array
	 */
	public function get_render_callback() {

		return array( 'shortcode_custom_field', 'render' );

	}

	/**
	 * Returns whether this shortcode / block needs to be registered on generation only.
	 * False will register the shortcode / block for non-Content Groups, such as Pages
	 * and Posts.
	 *
	 * @since   4.6.2
	 *
	 * @return  bool
	 */
	public function register_on_generation_only() {

		return false;

	}

	/**
	 * Returns this block's Attributes and default values for Gutenberg.
	 *
	 * @since   3.6.3
	 */
	public function get_attributes() {

		return array(
			// General.
			'meta_key'             => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				'type'    => 'string',
				'default' => ( ! $this->get_default_value( 'meta_key' ) ? '' : $this->get_default_value( 'meta_key' ) ),
			),

			// Preview.
			'is_gutenberg_example' => array(
				'type'    => 'boolean',
				'default' => false,
			),
		);

	}

	/**
	 * Returns this shortcode / block's Fields
	 *
	 * @since   4.6.2
	 */
	public function get_fields() {

		return array(
			'meta_key' => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				'label'         => __( 'Meta Key', 'page-generator-pro' ),
				'type'          => 'text',
				'default_value' => $this->get_default_value( 'meta_key' ),
				'description'   => __( 'Displays the Meta Value for the given Meta Key as defined in the Content Group\'s Custom Fields section', 'page-generator-pro' ),
			),
		);

	}

	/**
	 * Returns this shortcode / block's UI Tabs
	 *
	 * @since   4.6.2
	 */
	public function get_tabs() {

		return array(
			'general' => array(
				'label'       => __( 'General', 'page-generator-pro' ),
				'class'       => 'search',
				'description' => __( 'Defines the meta key to fetch the value for.', 'page-generator-pro' ),
				'fields'      => array(
					'meta_key',
				),
			),
		);

	}

	/**
	 * Returns this shortcode / block's Default Values
	 *
	 * @since   4.6.2
	 */
	public function get_default_values() {

		// Define default shortcode attributes.
		return array(
			'meta_key' => '', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
		);

	}

	/**
	 * Returns this shortcode / block's output
	 *
	 * @since   4.6.2
	 *
	 * @param  array $atts   Shortcode Attributes.
	 * @return string          Output
	 */
	public function render( $atts ) {

		global $post;

		// Get current post ID; this might not exist, depending on where the request is made.
		$current_post_id = ( isset( $post ) ? $post->ID : 0 );

		// Parse attributes.
		$atts = $this->parse_atts( $atts );

		// If no meta key defined, bail.
		if ( empty( $atts['meta_key'] ) ) {
			// If this block is being previewed in Gutenberg, show a verbose message explaining
			// why no content exist.
			if ( $this->base->get_class( 'common' )->is_rest_api_request() ) {
				return __( 'Custom Field: Define a Meta Key in the block\'s settings.', 'page-generator-pro' );
			}

			return '';
		}

		// Fetch Post Meta.
		$value = get_post_meta( $current_post_id, $atts['meta_key'], true );

		// If no Post Meta found, bail.
		if ( ! $value ) {
			// If this block is being previewed in Gutenberg, show a verbose message explaining
			// why no content exist.
			if ( $this->base->get_class( 'common' )->is_rest_api_request() ) {
				// Fetch from settings?

				return sprintf(
					'%s `%s` %s',
					__( 'Custom Field: Data for the Custom Field', 'page-generator-pro' ),
					$atts['meta_key'],
					__( 'will be displayed on generated Pages. This message will not display on the frontend site.', 'page-generator-pro' )
				);
			}

			return '';
		}

		// The value might be a shortcode; execute it now.
		$value = do_shortcode( $value );

		// Return.
		return $value;

	}

}
