<?php
/**
 * YouTube API Class.
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Fetch videos from YouTube based on given criteria.
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 1.2.0
 */
class Page_Generator_Pro_Youtube extends Page_Generator_Pro_API {

	use Page_Generator_Pro_Integration_Trait;
	use Page_Generator_Pro_Shortcode_Trait;

	/**
	 * Holds the base object.
	 *
	 * @since   4.8.0
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
	public $name = 'youtube';

	/**
	 * Holds the API endpoint
	 *
	 * @since   1.2.0
	 *
	 * @var     string
	 */
	public $api_endpoint = 'https://www.googleapis.com/youtube/v3';

	/**
	 * Holds the API Key
	 *
	 * @since   1.2.0
	 *
	 * @var     string
	 */
	public $api_key = 'AIzaSyC4IwPk9Iyp1uALNkj5WTblmQCO9Dr7ZCo';

	/**
	 * Constructor
	 *
	 * @since   4.8.0
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
	 * @since   4.8.0
	 *
	 * @return  string
	 */
	public function get_title() {

		return __( 'YouTube', 'page-generator-pro' );

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
			$this->get_settings_prefix() . '_data_api_key' => array(
				'label'         => sprintf(
					/* translators: %1$s: Integration name, %2$s Field label */
					'%1$s: %2$s',
					$this->get_title(),
					__( 'Data API Key', 'page-generator-pro' )
				),
				'type'          => 'text',
				'default_value' => $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-integrations', $this->get_settings_prefix() . '_data_api_key' ),
				'description'   => sprintf(
					'%s %s %s',
					esc_html__( 'If you reach an API limit, or your YouTube Dynamic Element does not render, you\'ll need to use your own API key.', 'page-generator-pro' ),
					'<a href="' . esc_attr( $this->base->plugin->documentation_url ) . '/settings-integration/#youtube" target="_blank" rel="noopener">' . esc_html__( 'Click here', 'page-generator-pro' ) . '</a>',
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

		return __( 'Displays a video from YouTube based on the given Terms.', 'page-generator-pro' );

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
			__( 'Video', 'page-generator-pro' ),
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

		return '_modules/dashboard/feather/youtube.svg';

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
			'term'                 => array(
				'type'    => 'string',
				'default' => ( ! $this->get_default_value( 'term' ) ? '' : $this->get_default_value( 'term' ) ),
			),
			'location'             => array(
				'type'    => 'string',
				'default' => ( ! $this->get_default_value( 'location' ) ? '' : $this->get_default_value( 'location' ) ),
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
			'term'     => array(
				'label'       => __( 'Term', 'page-generator-pro' ),
				'type'        => 'autocomplete',
				'values'      => $keywords,
				'placeholder' => __( 'e.g. building', 'page-generator-pro' ),
			),
			'location' => array(
				'label'       => __( 'Location (optional)', 'page-generator-pro' ),
				'type'        => 'autocomplete',
				'values'      => $keywords,
				'placeholder' => __( 'e.g. Birmingham, UK', 'page-generator-pro' ),
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
					'term',
					'location',
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
			'term'     => '',
			'location' => 0,
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

		// If our term is a location, get its latitude and longitude now.
		$lat_lng = false;
		if ( $atts['location'] ) {
			$result = $this->base->get_class( 'georocket' )->get_geocode( $atts['location'], $this->base->licensing->get_license_key() );

			if ( ! is_wp_error( $result ) && $result->success && $result->data !== false ) {
				$lat_lng = array(
					'latitude'  => $result->data->latitude,
					'longitude' => $result->data->longitude,
				);
			}
		}

		// If a YouTube Data API key has been specified, use it instead of the class default.
		$youtube_data_api_key = $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-integrations', 'youtube_data_api_key' );
		if ( ! empty( $youtube_data_api_key ) ) {
			$this->base->get_class( 'youtube' )->set_api_key( $youtube_data_api_key );
		}

		// Run query.
		$videos = $this->base->get_class( 'youtube' )->search( $atts['term'], $lat_lng );
		if ( is_wp_error( $videos ) || ! is_array( $videos ) || count( $videos ) === 0 ) {
			// Couldn't fetch videos from YouTube.
			// If a location was specified, remove it and try again.
			if ( $lat_lng !== false ) {
				$videos = $this->base->get_class( 'youtube' )->search( $atts['term'], false );
			}
		}

		// Bail if an error occured.
		if ( is_wp_error( $videos ) ) {
			return $this->add_dynamic_element_error_and_return( $videos, $atts );
		}

		// Pick a video at random from the resultset.
		if ( count( $videos ) === 1 ) {
			$video_index = 0;
		} else {
			$video_index = wp_rand( 0, ( count( $videos ) - 1 ) );
		}

		// Get video URL.
		$url = $videos[ $video_index ]['url'];

		/**
		 * Filter the YouTube Shortcode URL, before returning.
		 *
		 * @since   1.0.0
		 *
		 * @param   string  $url            YouTube URL.
		 * @param   array   $atts           Shortcode Attributes.
		 * @param   array   $videos         Video Results.
		 * @param   int     $video_index    Video Index.
		 */
		$url = apply_filters( 'page_generator_pro_shortcode_youtube', $url, $atts, $videos, $video_index );

		// Return.
		return $url;

	}

	/**
	 * Search for YouTube Videos for the given keyword and optional
	 * latitude / longitude.
	 *
	 * @since   1.2.0
	 *
	 * @param   string     $keyword    Search Terms.
	 * @param   bool|array $lat_lng    Latitude and Longitude.
	 * @return  WP_Error|array
	 */
	public function search( $keyword, $lat_lng = false ) {

		// Build array of arguments.
		$args = array(
			'key'        => $this->api_key,
			'type'       => 'video',
			'q'          => $keyword,
			'part'       => 'snippet',
			'maxResults' => 50,
		);

		// If a latitude and longitude is supplied, add it to the query.
		if ( $lat_lng !== false ) {
			$args['location']       = $lat_lng['latitude'] . ',' . $lat_lng['longitude'];
			$args['locationRadius'] = '10mi';
		}

		// Send request.
		$results = $this->response(
			$this->get( 'search?' . http_build_query( $args ) )
		);

		// Bail if an error occured.
		if ( is_wp_error( $results ) ) {
			return $results;
		}

		// Parse results.
		$videos = array();
		foreach ( $results->items as $video ) {
			$videos[] = array(
				'id'      => $video->id->videoId,
				'url'     => 'https://youtube.com/watch?v=' . $video->id->videoId,
				'title'   => $video->snippet->title,
				'caption' => $video->snippet->description,
			);
		}

		// Return array of videos.
		return $videos;

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
			// Inspect the error data to see if a more detailed reason for the error exists.
			$error_data = $response->get_error_data();

			if ( ! empty( $error_data ) ) {
				$error_data = json_decode( $error_data, true );

				return new WP_Error(
					'page_generator_pro_youtube_error',
					$error_data['error']['message']
				);
			}

			// Return standard WP_Error.
			return new WP_Error(
				'page_generator_pro_youtube_error',
				$response->get_error_message()
			);
		}

		// Bail if an error exists.
		if ( isset( $response->error ) ) {
			return new WP_Error(
				'page_generator_pro_youtube_error',
				$response->error->code . ': ' . $response->error->message
			);
		}

		// Bail if no results.
		if ( ! count( $response->items ) ) {
			return new WP_Error(
				'page_generator_pro_youtube_error',
				__( 'No results found', 'page-generator-pro' )
			);
		}

		// Return successful response data.
		return $response;

	}

}
