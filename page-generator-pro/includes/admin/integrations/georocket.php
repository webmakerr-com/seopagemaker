<?php
/**
 * GeoRocket API Class.
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Fetch location data for Keywords from georocket.net
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 1.7.8
 */
class Page_Generator_Pro_Georocket extends Page_Generator_Pro_API {

	use Page_Generator_Pro_Integration_Trait;

	/**
	 * Holds the base object.
	 *
	 * @since   5.0.5
	 *
	 * @var     object
	 */
	public $base;

	/**
	 * Holds the programmatic name of this integration.
	 *
	 * @since   5.0.5
	 *
	 * @var     string
	 */
	public $name = 'georocket';

	/**
	 * Holds the API endpoint
	 *
	 * @since   1.7.8
	 *
	 * @var     string
	 */
	public $api_endpoint = 'https://www.wpzinc.com';

	/**
	 * Holds the flag determining if the request data should be encoded
	 * into a JSON string
	 *
	 * If false, data is encoded using http_build_query()
	 *
	 * @since   2.8.9
	 *
	 * @var     bool
	 */
	public $is_json_request = false;

	/**
	 * Constructor.
	 *
	 * @since   5.0.0
	 *
	 * @param   object $base    Base Plugin Class.
	 */
	public function __construct( $base ) {

		$this->base = $base;

		// Register as a Generate Locations Provider.
		add_filter( 'page_generator_pro_keywords_generate_locations_get_providers', array( $this, 'register_integration' ) );
		add_filter( 'page_generator_pro_keywords_generate_locations_by_area_' . $this->name, array( $this, 'generate_locations_by_area' ), 10, 1 );
		add_filter( 'page_generator_pro_keywords_generate_locations_by_radius_' . $this->name, array( $this, 'generate_locations_by_radius' ), 10, 1 );

		// Register AJAX endpoints when using Keywords > Generate Locations.
		add_action( 'wp_ajax_page_generator_pro_georocket', array( $this, 'georocket' ) );
		add_action( 'wp_ajax_page_generator_pro_keywords_generate_locations', array( $this, 'keywords_generate_locations' ) );

	}

	/**
	 * Returns the label of this integration.
	 *
	 * @since   5.0.5
	 *
	 * @return  string
	 */
	public function get_title() {

		return __( 'Georocket', 'page-generator-pro' );

	}

	/**
	 * Generate locations by area.
	 *
	 * @since   5.0.5
	 *
	 * @param   array $args   Location arguments.
	 * @return  string|WP_Error
	 */
	public function generate_locations_by_area( $args ) {

		// If no regions or counties defined, return an error, otherwise we would fetch data for an entire country,
		// which can take time.
		if ( ! isset( $args['region_id'] ) && ! isset( $args['county_id'] ) ) {
			return new WP_Error(
				'page_generator_pro_georocket_generate_locations_by_area',
				__( 'For performance, fetching an entire country\'s locations is not permitted. You must specify one or more Regions or Counties.', 'page-generator-pro' )
			);
		}

		// Define GeoRocket arguments.
		$params = array(
			'license_key'    => $this->base->licensing->get_license_key(),
			'country_code'   => $args['country_code'],
			'api_call'       => 'get_cities',
			'fields'         => implode( ',', $args['output_type'] ),

			'population_min' => $args['population_min'],
			'population_max' => $args['population_max'],
		);

		// Region and County constraints.
		if ( is_array( $args['region_id'] ) && ! empty( $args['region_id'][0] ) ) {
			$params['region_id'] = $args['region_id'];
		}
		if ( is_array( $args['county_id'] ) && ! empty( $args['county_id'][0] ) ) {
			$params['county_id'] = $args['county_id'];
		}

		// If exclusions are defined, add them.
		if ( $args['exclusions'] !== false ) {
			$params['city_name_not'] = $args['exclusions'];
		}

		// Return locations from API.
		$results = $this->generate_locations( $params );

		// If an error occured, bail.
		if ( is_wp_error( $results ) ) {
			return $results;
		}

		return implode( "\n", $results );

	}

	/**
	 * Generate locations by radius.
	 *
	 * @since   5.0.5
	 *
	 * @param   array $args   Location arguments.
	 * @return  string|WP_Error
	 */
	public function generate_locations_by_radius( $args ) {

		// Define GeoRocket arguments.
		$params = array(
			'license_key'    => $this->base->licensing->get_license_key(),
			'country_code'   => $args['country_code'],
			'api_call'       => 'get_cities',
			'fields'         => implode( ',', $args['output_type'] ),
			'location'       => $args['location'] . ', ' . $args['country_code'],
			'radius'         => $args['radius'],
			'population_min' => $args['population_min'],
			'population_max' => $args['population_max'],
		);

		// If exclusions are defined, add them.
		if ( $args['exclusions'] !== false ) {
			$params['city_name_not'] = $args['exclusions'];
		}

		// Return locations from API.
		$results = $this->generate_locations( $params );

		// If an error occured, bail.
		if ( is_wp_error( $results ) ) {
			return $results;
		}

		return implode( "\n", $results );

	}

	/**
	 * Calls the Georocket API, returning results that are compatible
	 * with selectize.js
	 *
	 * @since   1.7.8
	 */
	public function georocket() {

		// Verify nonce.
		check_ajax_referer( 'generate_locations', 'nonce' );

		// If any required parameters are missing, bail.
		$required_params = array(
			'api_call',
			'country_code',
		);
		foreach ( $required_params as $required_param ) {
			if ( ! isset( $_POST[ $required_param ] ) ) {
				wp_send_json_error(
					array(
						'message' => sprintf(
							/* translators: API parameter */
							__( 'The %s parameter is missing from the POST data.', 'page-generator-pro' ),
							$required_param
						),
						'args'    => false,
						'request' => $_POST,
					)
				);
			}
		}

		// Get sanitized data.
		$api_call = sanitize_text_field( wp_unslash( $_POST['api_call'] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput

		// Define an array of all possible arguments.
		$args = array(
			// Plugin License Key.
			'license_key'  => $this->base->licensing->get_license_key(),

			'country_code' => sanitize_text_field( wp_unslash( $_POST['country_code'] ) ), // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		);

		// If a search field and query is specified, add it now
		// e.g. city_name: birmingham.
		if ( isset( $_POST['api_search_field'] ) && isset( $_POST['query'] ) ) {
			$args[ sanitize_text_field( wp_unslash( $_POST['api_search_field'] ) ) ] = sanitize_text_field( wp_unslash( $_POST['query'] ) );
		}

		// If region IDs are specified, add them now.
		if ( isset( $_POST['region_id'] ) ) {
			$args['region_id'] = wp_unslash( $_POST['region_id'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		}

		// If county IDs are specified, add them now.
		if ( isset( $_POST['county_id'] ) ) {
			$args['county_id'] = wp_unslash( $_POST['county_id'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		}

		// Define database argument, if stored in a constant.
		if ( defined( 'PAGE_GENERATOR_PRO_GEOROCKET_DB' ) ) {
			$args['database'] = PAGE_GENERATOR_PRO_GEOROCKET_DB;
		}

		// Depending on the search key, run API call.
		switch ( $api_call ) {
			case 'get_cities':
				// API call to cities endpoint.
				$terms = $this->get_cities( $args );
				break;

			case 'get_counties':
				// API call to counties endpoint.
				$terms = $this->get_counties( $args );
				break;

			case 'get_regions':
				// API call to regions endpoint.
				$terms = $this->get_regions( $args );
				break;

			default:
				// Invalid key.
				wp_send_json_error(
					array(
						'message' => __( 'api_call parameter is invalid, and should match one of get_cities, get_counties or get_regions', 'page-generator-pro' ),
						'args'    => $args,
						'request' => $_POST,
					)
				);
		}

		// Bail if an error occured.
		if ( is_wp_error( $terms ) ) {
			wp_send_json_error(
				array(
					'message' => $terms->get_error_message(),
					'args'    => $args,
					'request' => $_POST,
				)
			);
		}

		// Bail if no results were found.
		if ( ! is_array( $terms->data ) || count( $terms->data ) === 0 ) {
			wp_send_json_error(
				array(
					'message' => __( 'No results were found for the given criteria.', 'page-generator-pro' ),
					'args'    => $args,
					'request' => $_POST,
				)
			);
		}

		// Send results.
		wp_send_json_success(
			array(
				'data'    => $terms->data,
				'args'    => $args,
				'request' => $_POST,
			)
		);

	}

	/**
	 * Called when the Keywords > Generate Locations form is submitted via AJAX.
	 *
	 * @since   1.8.2
	 */
	public function keywords_generate_locations() {

		// Verify nonce.
		check_ajax_referer( 'generate_locations', $this->base->plugin->name . '_nonce' );

		// Check that a keyword ID or name was supplied.
		if ( ( ! isset( $_POST['keyword_id'] ) || empty( $_POST['keyword_id'] ) ) && ( ! isset( $_POST['keyword'] ) || empty( $_POST['keyword'] ) ) ) {
			wp_send_json_error( __( 'Please specify a keyword.', 'page-generator-pro' ) );
		}

		// If a Keyword ID has been specified, store it now for later use.
		$keyword_id = false;
		if ( isset( $_POST['keyword_id'] ) && ! empty( $_POST['keyword_id'] ) ) {
			$keyword_id = absint( $_POST['keyword_id'] );
		}

		// If no Keyword ID is specified, check that the new keyword we want to create doesn't already exist.
		if ( ! $keyword_id ) {
			// Check if the keyword already exists.
			$keyword_exists = $this->base->get_class( 'keywords' )->exists( sanitize_text_field( wp_unslash( $_POST['keyword'] ) ) );
			if ( $keyword_exists ) {
				wp_send_json_error( __( 'The keyword already exists.  Please specify a different keyword name.', 'page-generator-pro' ) );
			}
		}

		// If no Output Type is specified, return an error.
		if ( ! array_key_exists( 'output_type', $_POST ) ) {
			wp_send_json_error( __( 'Please specify at least one Output Type.', 'page-generator-pro' ) );
		}

		// Setup Georocket, and define an array of all possible arguments.
		$args = array(
			// Plugin License Key.
			'license_key'                 => $this->base->licensing->get_license_key(),

			// Location and Radius.
			'location'                    => false,
			'radius'                      => false,

			// Street Name Restraints.
			'street_name'                 => false,
			'street_name_not'             => false,

			// Zipcode District Restraints.
			'zipcode_district'            => false,
			'zipcode_district_not'        => false,

			// City Restraints.
			'population_min'              => false,
			'population_max'              => false,
			'median_household_income_min' => false,
			'median_household_income_max' => false,
			'city_id'                     => false,
			'city_name'                   => false,
			'city_name_not'               => false,

			// County Restraints.
			'county_id'                   => false,
			'county_name'                 => false,
			'county_name_not'             => false,

			// Region Restraints.
			'region_id'                   => false,
			'region_name'                 => false,
			'region_name_not'             => false,

			// Country Restraints.
			'country_id'                  => false,
			'country_code'                => isset( $_POST['country_code'] ) ? sanitize_text_field( wp_unslash( $_POST['country_code'] ) ) : false,
			'country_name'                => false,

			// Fields.
			'fields'                      => implode( ',', wp_unslash( $_POST['output_type'] ) ), // phpcs:ignore WordPress.Security.ValidatedSanitizedInput

			// Ordering.
			'orderby'                     => isset( $_POST['orderby'] ) ? sanitize_text_field( wp_unslash( $_POST['orderby'] ) ) : '',
			'order'                       => isset( $_POST['order'] ) ? sanitize_text_field( wp_unslash( $_POST['order'] ) ) : '',

			// Pagination.
			'per_page'                    => 10000,
			'page'                        => ( isset( $_POST['page'] ) ? absint( $_POST['page'] ) : false ),                      // For non-zipcode requests.
			'start_id'                    => ( isset( $_POST['start_id'] ) ? sanitize_text_field( wp_unslash( $_POST['start_id'] ) ) : false ), // For zipcode requests.
		);

		// If orderby is city_population, change it to population for API compat.
		// It comes into this function as city_population, so that selectize knows to enable/disable it as a sort option.
		if ( $args['orderby'] === 'city_population' ) {
			$args['orderby'] = 'population';
		}

		// Arguments will be either location/radius or city/county/region/country.
		$method = isset( $_POST['method'] ) ? sanitize_text_field( wp_unslash( $_POST['method'] ) ) : 'area';
		switch ( $method ) {
			case 'radius':
				$args['location'] = ( isset( $_POST['location'] ) ? sanitize_text_field( wp_unslash( $_POST['location'] ) ) : '' ) .
									', ' .
									( isset( $_POST['country_code'] ) ? sanitize_text_field( wp_unslash( $_POST['country_code'] ) ) : '' );

				// Build arguments.
				$keys = array(
					'radius',
					'population_min',
					'population_max',
					'median_household_income_min',
					'median_household_income_max',
				);
				$args = $this->generate_locations_build_args( $args, $keys, wp_unslash( $_POST ) );
				break;

			case 'area':
				$keys = array(
					'population_min',
					'population_max',

					'median_household_income_min',
					'median_household_income_max',

					'city_name',
					'city_id',

					'county_name',
					'county_id',

					'region_name',
					'region_id',
				);
				$args = $this->generate_locations_build_args( $args, $keys, wp_unslash( $_POST ) );
				break;
		}

		// Define exclusions now, if they exist.
		$exclusions = false;
		if ( isset( $_POST['exclusions'] ) && ! empty( $_POST['exclusions'] ) ) {
			$exclusions = explode( ',', sanitize_text_field( wp_unslash( $_POST['exclusions'] ) ) );
		}

		// Define database argument, if stored in a constant.
		if ( defined( 'PAGE_GENERATOR_PRO_GEOROCKET_DB' ) ) {
			$args['database'] = PAGE_GENERATOR_PRO_GEOROCKET_DB;
		}

		// Make Georocket API call, depending on the level of detail required for the output.
		if ( count( array_intersect( array_keys( $this->base->get_class( 'common' )->get_locations_output_types_street_names() ), wp_unslash( $_POST['output_type'] ) ) ) > 0 ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			// API call to street names endpoint.
			if ( $exclusions !== false ) {
				$args['street_name_not'] = $exclusions;
			}
			$result = $this->base->get_class( 'georocket' )->get_street_names( $args );
		} elseif ( count( array_intersect( array_keys( $this->base->get_class( 'common' )->get_locations_output_types_zipcode_districts() ), wp_unslash( $_POST['output_type'] ) ) ) > 0 ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			// API call to zipcode district endpoint.
			if ( $exclusions !== false ) {
				$args['zipcode_district_not'] = $exclusions;
			}
			$result = $this->base->get_class( 'georocket' )->get_zipcode_districts( $args );
		} elseif ( count( array_intersect( array_keys( $this->base->get_class( 'common' )->get_locations_output_types_zipcodes() ), wp_unslash( $_POST['output_type'] ) ) ) > 0 ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			// API call to zipcodes endpoint.
			if ( $exclusions !== false ) {
				$args['city_name_not'] = $exclusions;
			}
			$result = $this->base->get_class( 'georocket' )->get_zipcodes( $args );
		} elseif ( count( array_intersect( array_keys( $this->base->get_class( 'common' )->get_locations_output_types_cities() ), wp_unslash( $_POST['output_type'] ) ) ) > 0 ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			// API call to cities endpoint.
			if ( $exclusions !== false ) {
				$args['city_name_not'] = $exclusions;
			}
			$result = $this->base->get_class( 'georocket' )->get_cities( $args );
		} elseif ( count( array_intersect( array_keys( $this->base->get_class( 'common' )->get_locations_output_types_counties() ), wp_unslash( $_POST['output_type'] ) ) ) > 0 ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			// API call to counties endpoint.
			if ( $exclusions !== false ) {
				$args['county_name_not'] = $exclusions;
			}
			$result = $this->base->get_class( 'georocket' )->get_counties( $args );
		} elseif ( count( array_intersect( array_keys( $this->base->get_class( 'common' )->get_locations_output_types_regions() ), wp_unslash( $_POST['output_type'] ) ) ) > 0 ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			// API call to regions endpoint.
			if ( $exclusions !== false ) {
				$args['region_name_not'] = $exclusions;
			}
			$result = $this->base->get_class( 'georocket' )->get_regions( $args );
		} else {
			$result = new WP_Error( 'page_generator_pro_georocket_api_call', __( 'No output type was specified.', 'page-generator-pro' ) );
		}

		// Bail if an error occured.
		if ( is_wp_error( $result ) ) {
			wp_send_json_error( $result->get_error_message() );
		}

		// Bail if no results were found.
		if ( ! is_array( $result->data ) || count( $result->data ) === 0 ) {
			wp_send_json_error( __( 'No results were found for the given criteria.', 'page-generator-pro' ) );
		}

		// Build single Keyword.
		$keyword = array(
			'keyword'   => sanitize_text_field( wp_unslash( $_POST['keyword'] ) ),
			'data'      => '',
			'delimiter' => ( count( $_POST['output_type'] ) > 1 ? ',' : '' ),
			'columns'   => ( count( $_POST['output_type'] ) > 1 ? implode( ',', wp_unslash( $_POST['output_type'] ) ) : '' ), // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		);

		// Build the keyword data based on the output type formatting.
		$formatted_terms = array();
		foreach ( $result->data as $i => $term ) {
			// Define array to build output order for this term.
			$formatted_terms[ $i ] = array();

			// Build array.
			foreach ( wp_unslash( $_POST['output_type'] ) as $output_type ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
				// Skip if this Output Type isn't in the API resultset.
				if ( ! isset( $term->{ $output_type } ) ) {
					continue;
				}

				// Depending on the output type, we might need to use quotes and convert newlines to <br />.
				switch ( $output_type ) {
					/**
					 * Wikipedia Summaries
					 * - Some include newlines, which break Terms over multiple lines
					 */
					case 'city_wikipedia_summary':
					case 'city_wikipedia_summary_local':
					case 'county_wikipedia_summary':
					case 'region_wikipedia_summary':
						$formatted_terms[ $i ][] = '\"' . trim( str_replace( "\n", '<br />', $term->{ $output_type } ) ) . '\"';
						break;

					/**
					 * Wikipedia URLs
					 * - Some include a comma, which would fail column + term count when saving as a Keyword
					 */
					case 'city_wikipedia_url':
					case 'county_wikipedia_url':
					case 'region_wikipedia_url':
						$formatted_terms[ $i ][] = '\"' . trim( $term->{ $output_type } ) . '\"';
						break;

					/**
					 * If a comma is included in the value, encapsulate the value
					 */
					default:
						$value                   = ( strpos( $term->{ $output_type }, ',' ) !== false ? '"' . $term->{ $output_type } . '"' : $term->{ $output_type } );
						$formatted_terms[ $i ][] = $value;
						break;
				}
			}

			// Implode into a string.
			$formatted_terms[ $i ] = implode( ', ', $formatted_terms[ $i ] );
		}

		// Remove duplicates.
		// This should never occur, but it's a good fallback just in case.
		$formatted_terms = array_values( array_unique( $formatted_terms ) );

		// Add Terms to keyword data.
		$keyword['data'] = implode( "\n", $formatted_terms ); // @phpstan-ignore-line

		// Save Keyword, returning Keyword ID or WP_Error.
		$keyword_result = $this->base->get_class( 'keywords' )->save( $keyword, $keyword_id, true );

		// Bail if an error occured.
		if ( is_wp_error( $keyword_result ) ) {
			wp_send_json_error( $keyword_result->get_error_message() );
		}

		// Return the Keyword ID along with the GeoRocket Links and Meta.
		wp_send_json_success(
			array(
				'keyword_id'  => $keyword_result,
				'keyword_url' => admin_url( 'admin.php?page=page-generator-pro-keywords&cmd=form&id=' . $keyword_result ),
				'links'       => $result->links,
				'meta'        => $result->meta,
			)
		);
	}

	/**
	 * Returns Countries
	 *
	 * @since   1.7.8
	 *
	 * @param   array $args   Georocket API compatible arguments, including license_key.
	 * @return  WP_Error|array
	 */
	public function get_countries( $args ) {

		return $this->response(
			$this->post(
				'?georocket_api=1',
				array(
					'endpoint' => 'countries',
					'params'   => $this->sanitize_arguments( $args ),
				)
			)
		);

	}

	/**
	 * Returns Regions
	 *
	 * @since   1.7.8
	 *
	 * @param   array $args   Georocket API compatible arguments, including license_key.
	 * @return  WP_Error|array
	 */
	public function get_regions( $args ) {

		return $this->response(
			$this->post(
				'?georocket_api=1',
				array(
					'endpoint' => 'regions',
					'params'   => $this->sanitize_arguments( $args ),
				)
			)
		);

	}

	/**
	 * Returns Counties
	 *
	 * @since   1.7.8
	 *
	 * @param   array $args   Georocket API compatible arguments, including license_key.
	 * @return  WP_Error|array
	 */
	public function get_counties( $args ) {

		return $this->response(
			$this->post(
				'?georocket_api=1',
				array(
					'endpoint' => 'counties',
					'params'   => $this->sanitize_arguments( $args ),
				)
			)
		);

	}

	/**
	 * Returns Cities
	 *
	 * @since   1.7.8
	 *
	 * @param   array $args   Georocket API compatible arguments, including license_key.
	 * @return  WP_Error|array
	 */
	public function get_cities( $args ) {

		return $this->response(
			$this->post(
				'?georocket_api=1',
				array(
					'endpoint' => 'cities',
					'params'   => $this->sanitize_arguments( $args ),
				)
			)
		);

	}

	/**
	 * Returns ZIP Codes
	 *
	 * @since   1.7.8
	 *
	 * @param   array $args   Georocket API compatible arguments, including license_key.
	 * @return  WP_Error|array
	 */
	public function get_zipcodes( $args ) {

		return $this->response(
			$this->post(
				'?georocket_api=1',
				array(
					'endpoint' => 'zipcodes',
					'params'   => $this->sanitize_arguments( $args ),
				)
			)
		);

	}

	/**
	 * Returns Zipcode Districts
	 *
	 * @since   2.2.0
	 *
	 * @param   array $args   Georocket API compatible arguments, including license_key.
	 * @return  WP_Error|array
	 */
	public function get_zipcode_districts( $args ) {

		// Remove any arguments that are false.
		$args = $this->sanitize_arguments( $args );

		// Call API and return results.
		return $this->response(
			$this->post(
				'?georocket_api=1',
				array(
					'endpoint' => 'zipcode_districts',
					'params'   => $this->sanitize_arguments( $args ),
				)
			)
		);

	}

	/**
	 * Returns Street Names
	 *
	 * @since   2.2.0
	 *
	 * @param   array $args   Georocket API compatible arguments, including license_key.
	 * @return  WP_Error|array
	 */
	public function get_street_names( $args ) {

		return $this->response(
			$this->post(
				'?georocket_api=1',
				array(
					'endpoint' => 'street_names',
					'params'   => $this->sanitize_arguments( $args ),
				)
			)
		);

	}

	/**
	 * Returns a Latitude and Longitude for the given Location
	 *
	 * @since   1.7.8
	 *
	 * @param   string $location       Location.
	 * @param   string $license_key    Plugin License Key.
	 * @return  WP_Error|array
	 */
	public function get_geocode( $location, $license_key ) {

		return $this->response(
			$this->post(
				'?georocket_api=1',
				array(
					'endpoint' => 'geocode',
					'params'   => $this->sanitize_arguments(
						array(
							'license_key' => $license_key,
							'location'    => $location,
						)
					),
				)
			)
		);

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
                        // If the error relates to a missing or invalid license, silently
                        // continue so that GeoRocket dependant features remain usable
                        // without enforcing license validation.
                        if ( $this->is_license_error( $response->get_error_message() ) ) {
                                return $this->build_license_free_response();
                        }

                        return new WP_Error(
                                'page_generator_pro_georocket_error',
                                sprintf(
                                        /* translators: Error message */
                                        __( 'GeoRocket: %s', 'page-generator-pro' ),
					$response->get_error_message()
				)
			);
		}

                // If the response's success flag is false, return the data as an error.
                if ( ! $response->success ) {
                        $error = ( is_string( $response->data ) ? $response->data : $response->data->error );

                        // If the API rejected the request because of a missing license key,
                        // bypass the error so the feature can continue to operate without it.
                        if ( $this->is_license_error( $error ) ) {
                                return $this->build_license_free_response( $response );
                        }

                        return new WP_Error(
                                'page_generator_pro_georocket_error',
                                sprintf(
                                        /* translators: Error message */
					__( 'GeoRocket: %s', 'page-generator-pro' ),
					$error
				)
			);
		}

                // Return successful response data.
                return $response->data;

        }

        /**
         * Determines whether the response error relates to GeoRocket license validation.
         *
         * @param   string $error Error message.
         * @return  bool
         */
        protected function is_license_error( $error ) {
                if ( empty( $error ) || ! is_string( $error ) ) {
                        return false;
                }

                $error = strtolower( $error );

                return ( strpos( $error, 'license key' ) !== false );
        }

        /**
         * Builds a permissive response object when a GeoRocket license check fails so
         * the calling feature can continue to function without interruption.
         *
         * @param   object|null $response Original response object, if available.
         * @return  object
         */
        protected function build_license_free_response( $response = null ) {
                $fallback = (object) array(
                        'data'   => array(),
                        'links'  => array(),
                        'meta'   => array(),
                        'source' => 'license_bypass',
                );

                // If a partially populated response was provided, prefer any data it
                // already contains so that we return a consistent structure.
                if ( is_object( $response ) ) {
                        $fallback->data  = isset( $response->data ) ? $response->data : $fallback->data;
                        $fallback->links = isset( $response->links ) ? $response->links : $fallback->links;
                        $fallback->meta  = isset( $response->meta ) ? $response->meta : $fallback->meta;
                }

                return $fallback;
        }

	/**
	 * Generate locations by area or radius, performing a do...while loop to fetch locations in batches.
	 *
	 * @since   5.0.5
	 *
	 * @param   array $params     Location arguments.
	 * @return  array|WP_Error
	 */
	private function generate_locations( $params ) {

		// Run looped request to fetch locations in batches.
		$locations    = array();
		$current_page = 0;
		$last_page    = 0;
		do {
			// Increment pagination.
			$params['page'] = $current_page + 1;

			// Run query.
			$terms = $this->get_cities( $params );

			// Bail if an error occured.
			if ( is_wp_error( $terms ) ) {
				return $terms;
			}

			// Bail if no results were found.
			if ( ! is_array( $terms->data ) || count( $terms->data ) === 0 ) {
				return new WP_Error( 'page_generator_pro_georocket_generate_locations', __( 'No results were found for the given criteria.', 'page-generator-pro' ) );
			}

			// Convert to flat array.
			foreach ( $terms->data as $i => $term ) {
				// Remove fields we don't use.
				unset( $term->id );

				// Implode into a string and store in locations array.
				$locations[] = implode( ', ', (array) $term );
			}

			// Update current and last page.
			$current_page = $terms->meta->current_page;
			$last_page    = $terms->meta->last_page;
		} while ( $current_page < $last_page );

		return $locations;

	}

	/**
	 * Appends the given Post Data to the arguments array, based on the
	 * specific Post Data Keys to add.
	 *
	 * If a Post Data Key doesn't exist, is empty or false, we don't add
	 * it to the arguments
	 *
	 * @since   2.2.3
	 *
	 * @param   array $args       Arguments.
	 * @param   array $keys       Argument Keys to possibly add to $args.
	 * @param   array $post_data  POST Data.
	 * @return  array               Arguments
	 */
	private function generate_locations_build_args( $args, $keys, $post_data ) {

		foreach ( $keys as $key ) {
			if ( ! isset( $post_data[ $key ] ) ) {
				continue;
			}
			if ( empty( $post_data[ $key ] ) ) {
				continue;
			}
			if ( is_array( $post_data[ $key ] ) && count( $post_data[ $key ] ) === 0 ) { // @phpstan-ignore-line
				continue;
			}
			if ( ! is_array( $post_data[ $key ] ) && ! $post_data[ $key ] ) {
				continue;
			}

			if ( is_array( $post_data[ $key ] ) ) {
				if ( count( $post_data[ $key ] ) === 1 ) {
					$args[ $key ] = wp_unslash( $post_data[ $key ][0] );
				} else {
					$args[ $key ] = isset( $_POST[ $key ] ) ? wp_unslash( $_POST[ $key ] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				}
			} else {
				$args[ $key ] = wp_unslash( $post_data[ $key ] );
			}
		}

		return $args;

	}

}
