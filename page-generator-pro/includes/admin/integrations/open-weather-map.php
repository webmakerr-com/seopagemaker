<?php
/**
 * OpenWeatherMap API Class.
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Fetch weather data from OpenWeatherMap
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 2.4.8
 */
class Page_Generator_Pro_Open_Weather_Map extends Page_Generator_Pro_API {

	use Page_Generator_Pro_Integration_Trait;
	use Page_Generator_Pro_Shortcode_Trait;

	/**
	 * Holds the base object.
	 *
	 * @since   2.4.8
	 *
	 * @var     object
	 */
	public $base;

	/**
	 * Holds the programmatic name of this integration.
	 *
	 * @since   4.8.0
	 *
	 * @var     string
	 */
	public $name = 'open-weather-map';

	/**
	 * Holds the API Key
	 *
	 * @since   2.4.8
	 *
	 * @var     string
	 */
	public $api_key = '44cd0f66dbf150164a4289bfc29fa565';

	/**
	 * Holds the API endpoint
	 *
	 * @since   2.4.8
	 *
	 * @var     string
	 */
	public $api_endpoint = 'http://api.openweathermap.org/';

	/**
	 * Constructor.
	 *
	 * @since   2.4.8
	 *
	 * @param   object $base    Base Plugin Class.
	 */
	public function __construct( $base ) {

		// Store base class.
		$this->base = $base;

		// Register Settings Fields.
		add_filter( 'page_generator_pro_integrations_get', array( $this, 'register_integration' ) );
		add_filter( 'page_generator_pro_integrations_get_settings_fields', array( $this, 'get_settings_fields' ) );

		// Register as a Dynamic Element.
		add_filter( 'page_generator_pro_shortcode_add_shortcodes', array( $this, 'add_shortcode' ) );

	}

	/**
	 * Returns this shortcode / block's title.
	 *
	 * @since   4.5.2
	 *
	 * @return  string
	 */
	public function get_title() {

		return __( 'OpenWeatherMap', 'page-generator-pro' );

	}

	/**
	 * Returns settings fields and their values to display on:
	 * - Settings > Integrations
	 *
	 * @since   4.8.0
	 *
	 * @param   array $settings_fields    Settings Fields.
	 * @return  array                     Settings Fields
	 */
	public function get_settings_fields( $settings_fields ) {

		// Define fields and their values.
		$settings_fields[ $this->name ] = array(
			$this->get_settings_prefix() . '_api_key' => array(
				'label'         => sprintf(
					/* translators: %1$s: Integration name, %2$s Field label */
					'%1$s: %2$s',
					$this->get_title(),
					__( 'API Key', 'page-generator-pro' )
				),
				'type'          => 'text',
				'default_value' => $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-integrations', $this->get_settings_prefix() . '_api_key' ),
				'description'   => sprintf(
					'%s %s %s',
					esc_html__( 'If you reach an API limit when attempting to use the OpenWeatherMap Dynamic Element, you\'ll need to use your own free API key.', 'page-generator-pro' ),
					'<a href="' . esc_attr( $this->base->plugin->documentation_url ) . '/settings-integration/#openweathermap" target="_blank" rel="noopener">' . esc_html__( 'Click here', 'page-generator-pro' ) . '</a>',
					esc_html__( 'to read the step by step documentation to do this.', 'page-generator-pro' )
				),
			),
		);

		return $settings_fields;

	}

	/**
	 * Returns this shortcode / block's description.
	 *
	 * @since   4.5.2
	 *
	 * @return string
	 */
	public function get_description() {

		return __( 'Displays the weather forecast', 'page-generator-pro' );

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
			__( 'Weather', 'page-generator-pro' ),
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

		return '_modules/dashboard/feather/sun.svg';

	}

	/**
	 * Returns this block's Attributes and default values for Gutenberg.
	 *
	 * @since   3.6.3
	 */
	public function get_provider_attributes() {

		return array(
			'forecast_type' => array(
				'type'    => 'string',
				'default' => ( ! $this->get_default_value( 'forecast_type' ) ? '' : $this->get_default_value( 'forecast_type' ) ),
			),
			'location'      => array(
				'type'    => 'string',
				'default' => ( ! $this->get_default_value( 'location' ) ? '' : $this->get_default_value( 'location' ) ),
			),
			'country_code'  => array(
				'type'    => 'string',
				'default' => ( ! $this->get_default_value( 'country_code' ) ? '' : $this->get_default_value( 'country_code' ) ),
			),
			'units'         => array(
				'type'    => 'string',
				'default' => ( ! $this->get_default_value( 'units' ) ? '' : $this->get_default_value( 'units' ) ),
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
			'forecast_type' => array(
				'label'         => __( 'Forecast Type', 'page-generator-pro' ),
				'type'          => 'select',
				'values'        => $this->base->get_class( 'open_weather_map' )->get_forecast_types(),
				'default_value' => $this->get_default_value( 'forecast_type' ),
			),
			'location'      => array(
				'label'       => __( 'Location', 'page-generator-pro' ),
				'type'        => 'autocomplete',
				'values'      => $keywords,
				'placeholder' => __( 'e.g. Birmingham', 'page-generator-pro' ),
			),
			'country_code'  => array(
				'label'         => __( 'Country Code', 'page-generator-pro' ),
				'type'          => 'select',
				'values'        => $this->base->get_class( 'common' )->get_countries(),
				'default_value' => $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-general', 'country_code', 'US' ),
			),
			'units'         => array(
				'label'         => __( 'Units', 'page-generator-pro' ),
				'type'          => 'select',
				'values'        => $this->base->get_class( 'open_weather_map' )->get_temperature_units(),
				'default_value' => $this->get_default_value( 'units' ),
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
				'fields' => array(
					'forecast_type',
					'location',
					'country_code',
					'units',
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
			'forecast_type' => 13,
			'location'      => '',
			'country_code'  => '',
			'units'         => 'imperial',
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

		// If an Open Weather Map API key has been specified, use it instead of the class default.
		$open_weather_map_api_key = $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-integrations', 'open_weather_map_api_key' );
		if ( ! empty( $open_weather_map_api_key ) ) {
			$this->base->get_class( 'open_weather_map' )->set_api_key( $open_weather_map_api_key );
		}

		// Get City ID.
		$city_id = $this->base->get_class( 'open_weather_map' )->get_city_id(
			$atts['location'],
			$atts['country_code']
		);

		// Try fetching the City ID by ZIP Code if the City search didn't work.
		if ( is_wp_error( $city_id ) ) {
			$city_id = $this->base->get_class( 'open_weather_map' )->get_city_id_by_zip_code(
				$atts['location'],
				$atts['country_code']
			);
		}

		// Bail if errors occured.
		if ( is_wp_error( $city_id ) ) {
			return $this->add_dynamic_element_error_and_return( $city_id, $atts );
		}

		// Generate random ID for the map.
		$weather_id = md5( (string) wp_rand() );

		// Build HTML.
		$html = '<div id="page-generator-pro-open-weather-map-widget-' . $weather_id . '" class="page-generator-pro-open-weather-map"></div>
<script type="text/javascript">
window.myWidgetParam ? window.myWidgetParam : window.myWidgetParam = [];
window.myWidgetParam.push({
    id: ' . $atts['forecast_type'] . ',
    cityid: \'' . $city_id . '\',
    appid: \'' . $this->base->get_class( 'open_weather_map' )->api_key . '\',
    units: \'' . $atts['units'] . '\',
    containerid: \'page-generator-pro-open-weather-map-widget-' . $weather_id . '\'
});
(function() {
    var script = document.createElement(\'script\');
    script.async = true;
    script.charset = "utf-8";
    script.src = "//openweathermap.org/themes/openweathermap/assets/vendor/owm/js/weather-widget-generator.js";
    var s = document.getElementsByTagName(\'script\')[0];
    s.parentNode.insertBefore(script, s);
})();
</script>';

		/**
		 * Filter the Open Weather Maps HTML output, before returning.
		 *
		 * @since   2.4.8
		 *
		 * @param   string  $html   HTML Output.
		 * @param   array   $atts   Shortcode Attributes.
		 */
		$html = apply_filters( 'page_generator_pro_shortcode_open_weather_map', $html, $atts );

		// Return.
		return $html;

	}

	/**
	 * Returns the supported Forecast Types
	 *
	 * @since   2.5.1
	 *
	 * @return  array   Forecast Types
	 */
	public function get_forecast_types() {

		return array(
			13 => __( 'Small', 'page-generator-pro' ),
			16 => __( 'Medium', 'page-generator-pro' ),
			17 => __( 'Medium with Details', 'page-generator-pro' ),
			12 => __( 'Large', 'page-generator-pro' ),
			11 => __( 'Large with Details', 'page-generator-pro' ),
			18 => __( 'Banner', 'page-generator-pro' ),
			19 => __( 'Banner Alternative', 'page-generator-pro' ),
		);

	}

	/**
	 * Returns the supported Temperature Units
	 *
	 * @since   2.5.1
	 *
	 * @return  array   Tempoerature Units
	 */
	public function get_temperature_units() {

		return array(
			'imperial' => __( 'Imperial (Farenheight)', 'page-generator-pro' ),
			'metric'   => __( 'Metric (Celcius)', 'page-generator-pro' ),
		);

	}

	/**
	 * Returns the City ID for the given Location (City or ZIP Code) and Country,
	 * which can then be used for subsequent API queries or the JS widget.
	 *
	 * @since   2.4.8
	 *
	 * @param   string $location       Location (City, ZIP Code).
	 * @param   string $country_code   Country Code.
	 * @return  WP_Error|int
	 */
	public function get_city_id( $location, $country_code ) {

		// Run the query.
		$results = $this->response(
			$this->get(
				'data/2.5/weather',
				array(
					'q'     => $location . ',' . $country_code,
					'APPID' => $this->api_key,
				)
			)
		);

		// Bail if an error occured.
		if ( is_wp_error( $results ) ) {
			return $results;
		}

		// Bail if no ID in the results.
		if ( ! isset( $results->id ) || empty( $results->id ) ) {
			return new WP_Error(
				'page_generator_pro_open_weather_map_error',
				sprintf(
					/* translators: Location and Country Code */
					__( 'OpenWeatherMap: No Location ID could be found for %s', 'page-generator-pro' ),
					$location . ',' . $country_code
				)
			);
		}

		// Return City ID.
		return $results->id;

	}

	/**
	 * Returns the City ID for the given ZIP Code and Country,
	 * which can then be used for subsequent API queries or the JS widget.
	 *
	 * This fetches the City Name for the ZIP Code, and then uses that
	 * in the usual get_city_id() call.
	 *
	 * @since   3.3.0
	 *
	 * @param   string $zip_code       ZIP Code.
	 * @param   string $country_code   Country Code.
	 * @return  WP_Error|int
	 */
	public function get_city_id_by_zip_code( $zip_code, $country_code ) {

		// Run the query to fetch the City for the ZIP Code.
		$results = $this->response(
			$this->get(
				'data/2.5/weather',
				array(
					'zip'   => $zip_code . ',' . $country_code,
					'APPID' => $this->api_key,
				)
			)
		);

		// Bail if an error occured.
		if ( is_wp_error( $results ) ) {
			return $results;
		}

		// Bail if no name in the results.
		if ( ! isset( $results->name ) || empty( $results->name ) ) {
			return new WP_Error(
				'page_generator_pro_open_weather_map_error',
				sprintf(
					/* translators: Location and Country Code */
					__( 'City could not be established for ZIP Code %s', 'page-generator-pro' ),
					$zip_code . ',' . $country_code
				)
			);
		}

		// Return the resuts of get_city_id().
		return $this->get_city_id( $results->name, $country_code );

	}

	/**
	 * Inspects the response from the API call, returning an error
	 * or data
	 *
	 * @since   2.8.9
	 *
	 * @param   WP_Error|object $response   Response.
	 * @return  WP_Error|object
	 */
	public function response( $response ) {

		// If the response is an error, return it.
		if ( is_wp_error( $response ) ) {
			return new WP_Error(
				'page_generator_pro_open_weather_map_error',
				$response->get_error_message()
			);
		}

		// If the response contains an error message, return it.
		if ( isset( $response->message ) ) {
			return new WP_Error(
				'page_generator_pro_open_weather_map_error',
				$response->message
			);
		}

		// Return successful response data.
		return $response;

	}

}
