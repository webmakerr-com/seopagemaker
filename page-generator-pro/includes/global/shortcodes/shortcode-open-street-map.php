<?php
/**
 * Open Street Map Class
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Open Street Map Dynamic Element
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 2.5.1
 */
class Page_Generator_Pro_Shortcode_Open_Street_Map {

	use Page_Generator_Pro_Shortcode_Trait;

	/**
	 * Holds the base object.
	 *
	 * @since   2.5.1
	 *
	 * @var     object
	 */
	public $base;

	/**
	 * Constructor
	 *
	 * @since   2.5.1
	 *
	 * @param   object $base    Base Plugin Class.
	 */
	public function __construct( $base ) {

		// Store base class.
		$this->base = $base;

		add_filter( 'page_generator_pro_shortcode_add_shortcodes', array( $this, 'add_shortcode' ) );

	}

	/**
	 * Returns this shortcode / block's programmatic name.
	 *
	 * @since   2.5.1
	 */
	public function get_name() {

		return 'open-street-map';

	}

	/**
	 * Returns this shortcode / block's title.
	 *
	 * @since   4.5.2
	 *
	 * @return  string
	 */
	public function get_title() {

		return __( 'Open Street Map', 'page-generator-pro' );

	}

	/**
	 * Returns this shortcode / block's description.
	 *
	 * @since   4.5.2
	 *
	 * @return string
	 */
	public function get_description() {

		return __( 'Displays an Open Street Map', 'page-generator-pro' );

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
			__( 'Map', 'page-generator-pro' ),
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

		return 'assets/images/icons/open-street-map.svg';

	}

	/**
	 * Returns this shortcode / block's class name and render callback function name.
	 *
	 * @since   4.5.2
	 *
	 * @return  array
	 */
	public function get_render_callback() {

		return array( 'shortcode_open_street_map', 'render' );

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
	 * Returns whether this shortcode / block requires JS for output.
	 *
	 * @since   4.5.2
	 *
	 * @return  bool
	 */
	public function requires_js() {

		return true;

	}

	/**
	 * Returns this block's Attributes and default values for Gutenberg.
	 *
	 * @since   3.6.3
	 */
	public function get_provider_attributes() {

		return array(
			'location'             => array(
				'type'    => 'string',
				'default' => ( ! $this->get_default_value( 'location' ) ? '' : $this->get_default_value( 'location' ) ),
			),
			'country_code'         => array(
				'type'    => 'string',
				'default' => ( ! $this->get_default_value( 'country_code' ) ? '' : $this->get_default_value( 'country_code' ) ),
			),
			'height'               => array(
				'type'    => 'number',
				'default' => $this->get_default_value( 'height' ),
			),
			'zoom'                 => array(
				'type'    => 'number',
				'default' => $this->get_default_value( 'zoom' ),
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
	 * @since   2.5.1
	 */
	public function get_provider_fields() {

		// Load Keywords class.
		$keywords_class = $this->base->get_class( 'keywords' );

		// Bail if the Keywords class could not be loaded.
		if ( is_wp_error( $keywords_class ) ) {
			return false;
		}

		// Fetch Keywords.
		$keywords = $keywords_class->get_keywords_and_columns( true );

		return array(
			'location'     => array(
				'label'  => __( 'Location', 'page-generator-pro' ),
				'type'   => 'autocomplete',
				'values' => $keywords,
			),
			'country_code' => array(
				'label'         => __( 'Country Code', 'page-generator-pro' ),
				'type'          => 'select',
				'values'        => $this->base->get_class( 'common' )->get_countries(),
				'default_value' => $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-general', 'country_code', 'US' ),
			),
			'height'       => array(
				'label'         => __( 'Height (px)', 'page-generator-pro' ),
				'type'          => 'number',
				'min'           => 1,
				'max'           => 9999,
				'step'          => 1,
				'default_value' => $this->get_default_value( 'height' ),
			),
			'zoom'         => array(
				'label'         => __( 'Zoom Level', 'page-generator-pro' ),
				'type'          => 'number',
				'min'           => 1,
				'max'           => 20,
				'step'          => 1,
				'default_value' => $this->get_default_value( 'zoom' ),
				'description'   => __( 'A higher number means a higher zoom level, showing more detail. As a guide, 1 = World; 20 = Buildings', 'page-generator-pro' ),
			),
		);

	}

	/**
	 * Returns this shortcode / block's UI Tabs
	 *
	 * @since   2.5.1
	 */
	public function get_provider_tabs() {

		return array(
			'general' => array(
				'label'  => __( 'General', 'page-generator-pro' ),
				'class'  => 'general',
				'fields' => array(
					'location',
					'country_code',
					'height',
					'zoom',
				),
			),
		);

	}

	/**
	 * Returns this shortcode / block's Default Values
	 *
	 * @since   2.5.1
	 */
	public function get_provider_default_values() {

		return array(
			'location'     => '',
			'country_code' => '',
			'height'       => 250,
			'zoom'         => 14,
		);

	}

	/**
	 * Returns this shortcode / block's output
	 *
	 * @since   2.5.1
	 *
	 * @param  array $atts   Shortcode Attributes.
	 * @return string          Output
	 */
	public function render( $atts ) {

		// Parse attributes.
		$atts = $this->parse_atts( $atts );

		// Get latitude and longitude.
		$lat_lng = false;
		$result  = $this->base->get_class( 'georocket' )->get_geocode( $atts['location'] . ', ' . $atts['country_code'], $this->base->licensing->get_license_key() );

		// Handle errors.
		if ( is_wp_error( $result ) ) {
			return $this->add_dynamic_element_error_and_return( $result, $atts );
		}

		// Generate random ID for the map.
		$map_id = md5( (string) wp_rand() );

		/* translators: Copyright link */
		$copyright = sprintf( __( 'Map data &copy; %s', 'page-generator-pro' ), '<a href="https://www.openstreetmap.org/" rel="nofollow noreferrer noopener" target="_blank">OpenStreetMap</a>' );

		// Build HTML.
		$html  = '<div id="page-generator-pro-open-street-map-' . $map_id . '" class="page-generator-pro-map" style="height:' . $atts['height'] . 'px;"></div>';
		$html .= '<script type="text/javascript">
var map = L.map(\'page-generator-pro-open-street-map-' . $map_id . '\').setView([' . $result->data->latitude . ', ' . $result->data->longitude . '], ' . $atts['zoom'] . ');
L.tileLayer(\'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png\', {
    attribution: \'' . $copyright . '\'
}).addTo(map);
</script>';

		/**
		 * Filter the Open Street Maps HTML output, before returning.
		 *
		 * @since   2.2.6
		 *
		 * @param   string  $html   HTML Output.
		 * @param   array   $atts   Shortcode Attributes.
		 */
		$html = apply_filters( 'page_generator_pro_shortcode_open_street_map', $html, $atts );

		// Return.
		return $html;

	}

}
