<?php
/**
 * Google Maps Class
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Google Maps Dynamic Element
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 2.5.1
 */
class Page_Generator_Pro_Shortcode_Google_Map {

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
	 *
	 * @return  string
	 */
	public function get_name() {

		return 'google-map';

	}

	/**
	 * Returns this shortcode / block's title.
	 *
	 * @since   4.5.2
	 *
	 * @return  string
	 */
	public function get_title() {

		return __( 'Google Map', 'page-generator-pro' );

	}

	/**
	 * Returns this shortcode / block's description.
	 *
	 * @since   4.5.2
	 *
	 * @return string
	 */
	public function get_description() {

		return __( 'Displays a Google Map', 'page-generator-pro' );

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
			__( 'Google', 'page-generator-pro' ),
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

		return '_modules/dashboard/feather/map.svg';

	}

	/**
	 * Returns this shortcode / block's class name and render callback function name.
	 *
	 * @since   4.5.2
	 *
	 * @return  array
	 */
	public function get_render_callback() {

		return array( 'shortcode_google_map', 'render' );

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
	 * @since   3.6.3
	 */
	public function get_provider_attributes() {

		return array(
			// Block attributes.
			'map_mode'             => array(
				'type'    => 'string',
				'default' => ( ! $this->get_default_value( 'map_mode' ) ? '' : $this->get_default_value( 'map_mode' ) ),
			),
			'maptype'              => array(
				'type'    => 'string',
				'default' => ( ! $this->get_default_value( 'maptype' ) ? '' : $this->get_default_value( 'maptype' ) ),
			),

			// Map Mode: Any.
			'location'             => array(
				'type'    => 'string',
				'default' => ( ! $this->get_default_value( 'location' ) ? '' : $this->get_default_value( 'location' ) ),
			),
			'language'             => array(
				'type'    => 'string',
				'default' => ( ! $this->get_default_value( 'language' ) ? '' : $this->get_default_value( 'language' ) ),
			),

			// Map Mode: Directions.
			'destination'          => array(
				'type'    => 'string',
				'default' => ( ! $this->get_default_value( 'destination' ) ? '' : $this->get_default_value( 'destination' ) ),
			),
			'mode'                 => array(
				'type'    => 'string',
				'default' => ( ! $this->get_default_value( 'mode' ) ? '' : $this->get_default_value( 'mode' ) ),
			),

			// Map Mode: Streetview.
			'country_code'         => array(
				'type'    => 'string',
				'default' => ( ! $this->get_default_value( 'country_code' ) ? '' : $this->get_default_value( 'country_code' ) ),
			),

			// Map Mode: Search.
			'term'                 => array(
				'type'    => 'string',
				'default' => ( ! $this->get_default_value( 'term' ) ? '' : $this->get_default_value( 'term' ) ),
			),

			// Output.
			'height'               => array(
				'type'    => 'number',
				'default' => $this->get_default_value( 'height' ),
			),
			'zoom'                 => array(
				'type'    => 'number',
				'default' => $this->get_default_value( 'zoom' ),
			),
			'center_latitude'      => array(
				'type'    => 'string',
				'default' => $this->get_default_value( 'center_latitude' ),
			),
			'center_longitude'     => array(
				'type'    => 'string',
				'default' => $this->get_default_value( 'center_longitude' ),
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
	 *
	 * @return  bool|array
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
			'map_mode'         => array(
				'label'         => __( 'Map Mode', 'page-generator-pro' ),
				'type'          => 'select',
				'class'         => 'wpzinc-conditional',
				'data'          => array(
					// .components-panel is Gutenberg.
					// .wpzinc-tinymce-popup is TinyMCE.
					'container' => '.components-panel, .wpzinc-tinymce-popup',
				),
				'values'        => array(
					'place'      => __( 'Location', 'page-generator-pro' ),
					'view'       => __( 'Location without Marker', 'page-generator-pro' ),
					'search'     => __( 'Place(s)/Business(es) in Location', 'page-generator-pro' ),
					'directions' => __( 'Directions', 'page-generator-pro' ),
					'streetview' => __( 'Street View', 'page-generator-pro' ),
				),
				'default_value' => $this->get_default_value( 'map_mode' ),
			),
			'maptype'          => array(
				'label'         => __( 'Map Type', 'page-generator-pro' ),
				'type'          => 'select',
				'values'        => array(
					'roadmap'   => __( 'Road Map', 'page-generator-pro' ),
					'satellite' => __( 'Satellite', 'page-generator-pro' ),
				),
				'default_value' => $this->get_default_value( 'maptype' ),
				'condition'     => array(
					'key'        => 'map_mode',
					'value'      => array( 'place', 'view', 'search', 'directions' ),
					'comparison' => '==',
				),
			),

			// Map Mode: Any.
			'location'         => array(
				'label'  => __( 'Location / Origin', 'page-generator-pro' ),
				'type'   => 'autocomplete',
				'values' => $keywords,
			),
			'language'         => array(
				'label'         => __( 'Language', 'page-generator-pro' ),
				'type'          => 'select',
				'values'        => array_merge(
					array(
						'' => __( '(Visitor\'s own language)', 'page-generator-pro' ),
					),
					$this->base->get_class( 'common' )->get_languages()
				),
				'default_value' => $this->get_default_value( 'language' ),
				'condition'     => array(
					'key'        => 'map_mode',
					'value'      => 'streetview',
					'comparison' => '==',
				),
			),

			// Map Mode: Directions.
			'destination'      => array(
				'label'       => __( 'Destination', 'page-generator-pro' ),
				'type'        => 'autocomplete',
				'values'      => $keywords,
				'description' => __( 'If Map Type = Directions, specify the Destination here.  The Location field above is used as the Origin / Starting Point.', 'page-generator-pro' ),
				'condition'   => array(
					'key'        => 'map_mode',
					'value'      => 'directions',
					'comparison' => '==',
				),
			),
			'mode'             => array(
				'label'       => __( 'Method of Travel', 'page-generator-pro' ),
				'type'        => 'select',
				'values'      => array(
					'driving'   => __( 'Driving', 'page-generator-pro' ),
					'walking'   => __( 'Walking', 'page-generator-pro' ),
					'bicycling' => __( 'Cycling', 'page-generator-pro' ),
					'transit'   => __( 'Public Transport/Transit', 'page-generator-pro' ),
					'flying'    => __( 'Flying', 'page-generator-pro' ),
				),
				'description' => __( 'Specify the method of travel to show between the Location and Destination on the Map.', 'page-generator-pro' ),
				'condition'   => array(
					'key'        => 'map_mode',
					'value'      => 'directions',
					'comparison' => '==',
				),
			),

			// Map Mode: Streetview.
			'country_code'     => array(
				'label'         => __( 'Country Code', 'page-generator-pro' ),
				'type'          => 'select',
				'values'        => $this->base->get_class( 'common' )->get_countries(),
				'default_value' => $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-general', 'country_code', 'US' ),
				'condition'     => array(
					'key'        => 'map_mode',
					'value'      => 'streetview',
					'comparison' => '==',
				),
			),

			// Map Mode: Search.
			'term'             => array(
				'label'       => __( 'Term', 'page-generator-pro' ),
				'type'        => 'autocomplete',
				'values'      => $keywords,
				'description' => __( 'The businesses / landmarks to plot on the map.', 'page-generator-pro' ),
				'condition'   => array(
					'key'        => 'map_mode',
					'value'      => 'search',
					'comparison' => '==',
				),
			),

			// Output.
			'height'           => array(
				'label'         => __( 'Height (px)', 'page-generator-pro' ),
				'type'          => 'number',
				'min'           => 1,
				'max'           => 9999,
				'step'          => 1,
				'default_value' => $this->get_default_value( 'height' ),
			),
			'zoom'             => array(
				'label'         => __( 'Zoom Level', 'page-generator-pro' ),
				'type'          => 'number',
				'min'           => 1,
				'max'           => 20,
				'step'          => 1,
				'default_value' => $this->get_default_value( 'zoom' ),
				'description'   => __( 'A higher number means a higher zoom level, showing more detail. As a guide, 1 = World; 20 = Buildings', 'page-generator-pro' ),
			),
			'center_latitude'  => array(
				'label'       => __( 'Center Point: Latitude', 'page-generator-pro' ),
				'type'        => 'autocomplete',
				'values'      => $keywords,
				'description' => __( 'Optional: The center point\'s latitude for the map. Recommended for more accurate results.', 'page-generator-pro' ),
				'condition'   => array(
					'key'        => 'map_mode',
					'value'      => array( 'search', 'view', 'place' ),
					'comparison' => '==',
				),
			),
			'center_longitude' => array(
				'label'       => __( 'Center Point: Longitude', 'page-generator-pro' ),
				'type'        => 'autocomplete',
				'values'      => $keywords,
				'description' => __( 'Optional: The center point\'s longitude for the map. Recommended for more accurate results.', 'page-generator-pro' ),
				'condition'   => array(
					'key'        => 'map_mode',
					'value'      => array( 'search', 'view', 'place' ),
					'comparison' => '==',
				),
			),
		);

	}

	/**
	 * Returns this shortcode / block's UI Tabs
	 *
	 * @since   2.5.1
	 *
	 * @return  bool|array
	 */
	public function get_provider_tabs() {

		return array(
			'general' => array(
				'label'  => __( 'General', 'page-generator-pro' ),
				'class'  => 'general',
				'fields' => array(
					'map_mode',
					'maptype',
					'location',
					'language',
					'destination',
					'mode',
					'country_code',
					'term',
					'height',
					'zoom',
					'center_latitude',
					'center_longitude',
				),
			),
		);

	}

	/**
	 * Returns this shortcode / block's Default Values
	 *
	 * @since   2.5.1
	 *
	 * @return array
	 */
	public function get_provider_default_values() {

		return array(
			'map_mode'               => 'place',
			'maptype'                => 'roadmap',
			'location'               => '',
			'language'               => '',

			// Directions.
			'destination'            => '',
			'mode'                   => 'driving',

			// Street View.
			'country_code'           => '',

			// Search.
			'term'                   => '',

			// Output.
			'height'                 => 250,
			'zoom'                   => 14,
			'center_latitude'        => '', // Center point.
			'center_longitude'       => '', // Center point.

			// Deprecated.
			'show_place_card_marker' => 1,
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

		// For backward compat, convert some attributes now.
		if ( array_key_exists( 'show_place_card_marker', $atts ) ) {
			if ( ! $atts['show_place_card_marker'] ) {
				$atts['map_mode'] = 'view';
			}
		}
		if ( array_key_exists( 'term', $atts ) ) {
			if ( ! empty( $atts['term'] ) ) {
				$atts['map_mode'] = 'search';
			}
		}
		switch ( $atts['maptype'] ) {
			case 'directions':
				$atts['map_mode'] = 'directions';
				$atts['mode']     = 'driving';
				$atts['maptype']  = 'roadmap';
				break;

			case 'streetview':
				$atts['map_mode'] = 'streetview';
				break;
		}

		// Build iframe arguments.
		$args = false;
		switch ( $atts['map_mode'] ) {

			/**
			 * Directions
			 */
			case 'directions':
				// Deliberately don't include zoom parameter as this can result in the map being too zoomed in
				// and directions not shown until the user zooms out.
				$args = array(
					'origin'      => $atts['location'],
					'destination' => $atts['destination'],
					'mode'        => $atts['mode'],
					'maptype'     => $atts['maptype'],
				);

				// Define iframe's title.
				$title = sprintf(
					/* translators: %1$s: Transportation mode (driving,walking,transit,bicycling), %2$s: Start location, %3$s: End location */
					__( 'Google %1$s directions map from %2$s to %3$s', 'page-generator-pro' ),
					$atts['mode'],
					$atts['location'],
					$atts['destination']
				);
				break;

			/**
			 * Street View
			 */
			case 'streetview':
				// Get latitude and longitude.
				$lat_lng = false;
				$result  = $this->base->get_class( 'georocket' )->get_geocode( $atts['location'] . ', ' . $atts['country_code'], $this->base->licensing->get_license_key() );

				// Handle errors.
				if ( is_wp_error( $result ) ) {
					return $this->add_dynamic_element_error_and_return( $result, $atts );
				}
				if ( ! $result->data ) {
					return $this->add_dynamic_element_error_and_return(
						new WP_Error(
							'page_generator_pro_shortcode_google_maps_error',
							__( 'No Data in Geocode Response', 'page-generator-pro' )
						),
						$atts
					);
				}

				// If here, we have a latitude and longitude.
				$args = array(
					'location' => $result->data->latitude . ',' . $result->data->longitude,
				);

				// Define iframe's title.
				$title = sprintf(
					/* translators: Location */
					__( 'Google street view of %s', 'page-generator-pro' ),
					$atts['location']
				);
				break;

			/**
			 * Search
			 * - Pins of matching places in location
			 */
			case 'search':
				$args = array(
					'q'       => $atts['term'] . ' in ' . $atts['location'],
					'zoom'    => $atts['zoom'],
					'maptype' => $atts['maptype'],
				);

				// If a latitude and longitude is specified, use this as the center point.
				if ( $atts['center_latitude'] || $atts['center_longitude'] ) {
					$args['center'] = $atts['center_latitude'] . ',' . $atts['center_longitude'];
				}

				// Define iframe's title.
				$title = sprintf(
					/* translators: %1$s: Business, %2$s: Location */
					__( 'Google map results for %1$s in %2$s', 'page-generator-pro' ),
					$atts['term'],
					$atts['location']
				);
				break;

			/**
			 * View
			 * - Map without Pin
			 */
			case 'view':
				// Get latitude and longitude if none defined in the attributes.
				if ( empty( $atts['center_latitude'] ) && empty( $atts['center_longitude'] ) ) {
					$result = $this->base->get_class( 'georocket' )->get_geocode( $atts['location'] . ', ' . $atts['country_code'], $this->base->licensing->get_license_key() );

					// Bail if errors occured.
					if ( is_wp_error( $result ) ) {
						return $this->add_dynamic_element_error_and_return( $result, $atts );
					}

					if ( ! $result->data ) {
						return $this->add_dynamic_element_error_and_return(
							new WP_Error(
								'page_generator_pro_shortcode_google_maps_error',
								__( 'No Data in Geocode Response', 'page-generator-pro' )
							),
							$atts
						);
					}

					$atts['center_latitude']  = $result->data->latitude;
					$atts['center_longitude'] = $result->data->longitude;
				}

				$args = array(
					'center'  => $atts['center_latitude'] . ',' . $atts['center_longitude'],
					'zoom'    => $atts['zoom'],
					'maptype' => $atts['maptype'],
				);

				// Define iframe's title.
				$title = sprintf(
					/* translators: %1$s: Start location, %2$s: End location */
					__( 'Google map of %s', 'page-generator-pro' ),
					$atts['location']
				);
				break;

			/**
			 * Place
			 * - Map with Pin
			 */
			case 'place':
			default:
				$args = array(
					'q'       => $atts['location'],
					'zoom'    => $atts['zoom'],
					'maptype' => $atts['maptype'],
				);

				// If a latitude and longitude is specified, use this as the center point.
				if ( $atts['center_latitude'] || $atts['center_longitude'] ) {
					$args['center'] = $atts['center_latitude'] . ',' . $atts['center_longitude'];
				}

				// Define iframe's title.
				$title = sprintf(
					/* translators: %1$s: Start location, %2$s: End location */
					__( 'Google map of %s', 'page-generator-pro' ),
					$atts['location']
				);
				break;

		}

		// If a language is specified, add it now.
		if ( array_key_exists( 'language', $atts ) && ! empty( $atts['language'] ) ) {
			$args['language'] = $atts['language'];
		}

		// Add API Key and build URL.
		// There is no billing/usage limit on this Google service, so use our API key.
		$args['key'] = 'AIzaSyCNTEOso0tZG6YMSJFoaJEY5Th1stEWrJI';
		$url         = 'https://www.google.com/maps/embed/v1/' . $atts['map_mode'] . '?' . http_build_query( $args );

		/**
		 * Filter the Google Maps iFrame URL, before output.
		 *
		 * @since   2.0.4
		 *
		 * @param   string  $url        URL with Arguments.
		 * @param   array   $atts       Shortcode Attributes.
		 * @param   array   $args       URL Arguments.
		 * @param   string  $map_mode   Map Mode.
		 * @param   string  $title      iFrame title.
		 */
		$url = apply_filters( 'page_generator_pro_shortcode_google_maps_url', $url, $atts, $args, $atts['map_mode'], $title );

		// Build HTML using the URL.
		$html = '<iframe title="' . esc_attr( $title ) . '" class="page-generator-pro-map" width="100%" height="' . esc_attr( $atts['height'] ) . '" frameborder="0" style="border:0" src="' . esc_url( $url ) . '" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>';

		/**
		 * Filter the Google Maps HTML output, before returning.
		 *
		 * @since   1.0.0
		 *
		 * @param   string  $html   HTML Output.
		 * @param   array   $atts   Shortcode Attributes.
		 */
		$html = apply_filters( 'page_generator_pro_shortcode_google_maps', $html, $atts );

		// Return.
		return $html;

	}

}
