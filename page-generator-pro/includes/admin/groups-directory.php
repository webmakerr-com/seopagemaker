<?php
/**
 * Groups Directory Class
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Provides a UI for creating multiple Content Groups and Keywords
 * in a directory structure, such as County > City > Service.
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 3.2.9
 */
class Page_Generator_Pro_Groups_Directory {

	/**
	 * Holds the base object.
	 *
	 * @since   3.2.9
	 *
	 * @var     object
	 */
	public $base;

	/**
	 * The current step
	 *
	 * @var     int
	 */
	public $step = 1;

	/**
	 * The current configuration
	 *
	 * @var     array
	 */
	public $configuration = array();

	/**
	 * The provider of location data.
	 *
	 * @since   5.0.5
	 *
	 * @var     string
	 */
	public $provider = '';

	/**
	 * Constructor.
	 *
	 * @since   3.2.9
	 *
	 * @param   object $base    Base Plugin Class.
	 */
	public function __construct( $base ) {

		// Store base class.
		$this->base = $base;

		add_action( 'admin_init', array( $this, 'maybe_load' ) );
		add_filter( 'page_generator_pro_groups_ui_output_add_new_buttons', array( $this, 'register_button' ) );

	}

	/**
	 * Loads the Groups Directory screen if the request URL is for this class
	 *
	 * @since   3.2.9
	 */
	public function maybe_load() {

		// Bail if this isn't a request for the Groups Directory screen.
		if ( ! $this->is_groups_directory_request() ) {
			return;
		}

		// Define current screen.
		set_current_screen( $this->base->plugin->name . '-groups-directory' );

		// Determine provider.
		$this->provider = $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-generate-locations', 'provider' );

		// Process posted form data.
		$result = $this->process_form();

		// If an error occured in processing, show it on screen.
		if ( is_wp_error( $result ) ) {
			$this->base->get_class( 'notices' )->add_error_notice( $result->get_error_message() );
		}

		// Output custom HTML for the Groups Directory screen.
		$this->output_header();
		$this->output_content();
		$this->output_footer();
		exit;

	}

	/**
	 * Registers a button on the Content Groups WP_List_Table for this wizard.
	 *
	 * @since   4.1.0
	 *
	 * @param   array $buttons    Buttons.
	 * @return  array               Buttons
	 */
	public function register_button( $buttons ) {

		$buttons['page-generator-pro-groups-directory'] = array(
			'label' => __( 'Add New Directory Structure', 'page-generator-pro' ),
			'url'   => 'admin.php?page=page-generator-pro-groups-directory',
			'class' => 'groups_directory',
		);

		return $buttons;

	}

	/**
	 * Process posted form data, if any exists
	 *
	 * @since   3.2.9
	 *
	 * @return  WP_Error|bool
	 */
	private function process_form() {

		// Define default configuration.
		$this->configuration = array(  // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
			'structure'       => 'county_city_service',
			'service_keyword' => '',
			'services'        => '',
			'method'          => 'radius',
			'radius'          => 10,
			'region_id'       => array(), // Region IDs.
			'county_id'       => array(), // County IDs.
			'regions'         => array(),
			'counties'        => array(),
			'zipcode'         => '',
			'exclusions'      => false,
			'population_min'  => false,
			'population_max'  => false,
			'country_code'    => $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-general', 'country_code', 'US' ),
		);

		// Assume we're on the current step.
		$this->step = ( isset( $_REQUEST['step'] ) ? absint( $_REQUEST['step'] ) : 1 ); // phpcs:ignore WordPress.Security.NonceVerification

		// Run security checks.
		if ( ! isset( $_POST[ $this->base->plugin->name . '_nonce' ] ) ) {
			return false;
		}
		if ( ! wp_verify_nonce( sanitize_key( $_POST[ $this->base->plugin->name . '_nonce' ] ), 'page-generator-pro' ) ) {
			return new WP_Error( 'page_generator_pro_groups_directory_process_form', __( 'Invalid nonce specified.', 'page-generator-pro' ) );
		}

		// Decode the current configuration.
		$this->configuration = ( isset( $_REQUEST['configuration'] ) ? json_decode( wp_unslash( $_REQUEST['configuration'] ), true ) : $this->configuration ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

		// Depending on the step we're on, check the form data.
		switch ( $this->step ) {
			/**
			 * Setup
			 */
			case 1:
				// Add to configuration.
				$this->configuration = array_merge(
					$this->configuration,
					array(
						'structure'           => isset( $_POST['structure'] ) ? sanitize_text_field( wp_unslash( $_POST['structure'] ) ) : '',
						'service_keyword'     => isset( $_POST['service_keyword'] ) ? sanitize_text_field( wp_unslash( $_POST['service_keyword'] ) ) : '',
						'service_keyword_id'  => '',
						'services'            => isset( $_POST['services'] ) ? sanitize_textarea_field( wp_unslash( $_POST['services'] ) ) : '',
						'location_keyword'    => '',
						'location_keyword_id' => '',
						'method'              => isset( $_POST['method'] ) ? sanitize_text_field( wp_unslash( $_POST['method'] ) ) : '',
						'radius'              => isset( $_POST['radius'] ) ? sanitize_text_field( wp_unslash( $_POST['radius'] ) ) : '',
						'region_id'           => isset( $_POST['region_id'] ) ? wp_unslash( $_POST['region_id'] ) : false, // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
						'county_id'           => ( isset( $_POST['county_id'] ) ? wp_unslash( $_POST['county_id'] ) : false ), // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
						'regions'             => ( isset( $_POST['regions'] ) ? wp_unslash( $_POST['regions'] ) : false ), // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
						'counties'            => ( isset( $_POST['counties'] ) ? wp_unslash( $_POST['counties'] ) : false ), // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
						'zipcode'             => isset( $_POST['zipcode'] ) ? sanitize_text_field( wp_unslash( $_POST['zipcode'] ) ) : '',
						'exclusions'          => ( ! empty( $_POST['exclusions'] ) ? explode( ',', sanitize_text_field( wp_unslash( $_POST['exclusions'] ) ) ) : false ),
						'population_min'      => ( ! empty( $_POST['population_min'] ) ? absint( $_POST['population_min'] ) : false ),
						'population_max'      => ( ! empty( $_POST['population_max'] ) ? absint( $_POST['population_max'] ) : false ),
						'country_code'        => isset( $_POST['country_code'] ) ? sanitize_text_field( wp_unslash( $_POST['country_code'] ) ) : '',
					)
				);

				// Check required fields are completed.
				if ( strpos( $this->configuration['structure'], 'service' ) !== false ) {
					if ( empty( $this->configuration['service_keyword'] ) && empty( $this->configuration['services'] ) ) {
						return new WP_Error(
							'page_generator_pro_groups_directory_process_form_error',
							__( 'A service keyword must be chosen, or service terms entered', 'page-generator-pro' )
						);
					}
				}
				if ( $this->configuration['method'] === 'radius' ) {
					if ( empty( $this->configuration['radius'] ) ) {
						return new WP_Error(
							'page_generator_pro_groups_directory_process_form_error',
							__( 'The radius field is required', 'page-generator-pro' )
						);
					}
					if ( empty( $this->configuration['zipcode'] ) ) {
						return new WP_Error(
							'page_generator_pro_groups_directory_process_form_error',
							__( 'The ZIP Code field is required', 'page-generator-pro' )
						);
					}
				}

				// Setup Location Keyword.
				$result = $this->setup_location_keyword();

				// Bail if an error occured.
				if ( is_wp_error( $result ) ) {
					return $result;
				}

				$this->configuration['location_keyword']    = $result['keyword'];
				$this->configuration['location_keyword_id'] = $result['id'];

				// Setup Service Keyword.
				if ( strpos( $this->configuration['structure'], 'service' ) !== false ) {
					if ( ! $this->configuration['service_keyword'] ) {
						$result = $this->setup_service_keyword();

						// Bail if an error occured.
						if ( is_wp_error( $result ) ) {
							return $result;
						}

						$this->configuration['service_keyword']    = $result['keyword'];
						$this->configuration['service_keyword_id'] = $result['id'];
					} else {
						// Get Keyword ID.
						$keyword                                   = $this->base->get_class( 'keywords' )->get_by( 'keyword', $this->configuration['service_keyword'] );
						$this->configuration['service_keyword_id'] = $keyword['keywordID'];
					}
				}

				// Setup Content Groups.
				$content_group_ids = $this->setup_content_groups();

				// Bail if an error occured.
				if ( is_wp_error( $content_group_ids ) ) {
					return $content_group_ids;
				}

				$this->configuration['content_group_ids'] = $content_group_ids;
				break;
		}

		// If here, form validation/processing was successful.
		// Increment the step so that the next section is displayed.
		++$this->step;

		return true;

	}

	/**
	 * Outputs the <head> and opening <body> tag for the standalone Groups Directory screen
	 *
	 * @since   3.2.9
	 */
	private function output_header() {

		// Remove scripts.
		remove_all_actions( 'admin_notices' );
		remove_all_actions( 'all_admin_notices' );

		// Enqueue scripts.
		do_action( 'admin_enqueue_scripts' );

		// Load header view.
		include_once $this->base->plugin->folder . '/views/admin/wizard/header.php';

	}

	/**
	 * Outputs the HTML for the <body> section for the standalone Groups Directory screen,
	 * and defines any form option data that might be needed.
	 *
	 * @since   3.2.9
	 */
	private function output_content() {

		// Load form data.
		switch ( $this->step ) {
			/**
			 * Setup
			 */
			case 1:
				$structures        = $this->get_structures();
				$keywords          = $this->base->get_class( 'keywords' )->get_keywords_names();
				$countries         = $this->base->get_class( 'common' )->get_countries();
				$back_button_url   = 'edit.php?post_type=page-generator-pro';
				$back_button_label = __( 'Cancel', 'page-generator-pro' );
				$next_button_label = __( 'Create Keywords and Content Groups', 'page-generator-pro' );
				break;

			/**
			 * Done
			 */
			case 2:
				// Define UI.
				$back_button_url   = 'edit.php?post_type=page-generator-pro';
				$back_button_label = __( 'Finish', 'page-generator-pro' );
				break;
		}

		// Load content view.
		include_once $this->base->plugin->folder . '/views/admin/groups-directory/content.php';

	}

	/**
	 * Outputs the closing </body> and </html> tags, and runs some WordPress actions, for the standalone Groups Directory screen
	 *
	 * @since   3.2.9
	 */
	private function output_footer() {

		do_action( 'admin_footer', '' );
		do_action( 'admin_print_footer_scripts' );

		// Load footer view.
		include_once $this->base->plugin->folder . '/views/admin/wizard/footer.php';

	}

	/**
	 * Determines if the request is for the Groups Directory screen
	 *
	 * @since   3.2.9
	 *
	 * @return  bool    Is Groups Directory Request
	 */
	private function is_groups_directory_request() {

		// Don't load if this is an AJAX call.
		if ( wp_doing_ajax() || wp_doing_cron() ) {
			return false;
		}

		// Bail if we're not on the Groups Directory screen.
		if ( ! isset( $_GET['page'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			return false;
		}
		if ( sanitize_text_field( wp_unslash( $_GET['page'] ) ) !== $this->base->plugin->name . '-groups-directory' ) { // phpcs:ignore WordPress.Security.NonceVerification
			return false;
		}

		return true;

	}

	/**
	 * Returns directory structures supported the Groups Directory functionality
	 *
	 * @since   3.3.0
	 *
	 * @return  array   Supported Structures
	 */
	private function get_structures() {

		return array(
			// Service structures.
			'region_county_city_service' => array(
				'title'       => __( 'Region > County > City > Service', 'page-generator-pro' ),
				'description' => __( 'Services cover multiple States/Regions and multiple Counties', 'page-generator-pro' ),
			),
			'region_city_service'        => array(
				'title'       => __( 'Region > City > Service', 'page-generator-pro' ),
				'description' => __( 'Services cover multiple States/Regions, and County Pages are not required', 'page-generator-pro' ),
			),
			'county_city_service'        => array(
				'title'       => __( 'County > City > Service', 'page-generator-pro' ),
				'description' => __( 'Services cover multiple Counties within a single State/Region (or Region Pages are not required)', 'page-generator-pro' ),
			),
			'city_service'               => array(
				'title'       => __( 'City > Service', 'page-generator-pro' ),
				'description' => __( 'Services cover multiple Cities within a single County (or County Pages are not required)', 'page-generator-pro' ),
			),
			'service_city'               => array(
				'title'       => __( 'Service > City', 'page-generator-pro' ),
				'description' => __( 'Cities cover multiple Services within a single County (or County Pages are not required)', 'page-generator-pro' ),
			),

			// Non-service structures.
			'region_county_city'         => array(
				'title'       => __( 'Region > County > City', 'page-generator-pro' ),
				'description' => __( 'Build multiple States/Regions, Counties and City Pages', 'page-generator-pro' ),
			),
			'region_city'                => array(
				'title'       => __( 'Region > City', 'page-generator-pro' ),
				'description' => __( 'Build multiple States/Regions and City Pages, where County Pages are not required', 'page-generator-pro' ),
			),
			'county_city'                => array(
				'title'       => __( 'County > City', 'page-generator-pro' ),
				'description' => __( 'Build multiple States/Regions and County Pages, where City Pages are not required', 'page-generator-pro' ),
			),
		);

	}

	/**
	 * Creates the Service Keyword based on the supplied configuration
	 *
	 * @since   3.2.9
	 *
	 * @return  WP_Error|array
	 */
	private function setup_service_keyword() {

		// Get unique keyword name that can be used.
		$keyword = $this->base->get_class( 'keywords' )->get_unique_name( 'service' );

		// Create keyword.
		$result = $this->base->get_class( 'keywords' )->save(
			array(
				'keyword' => $keyword,
				'data'    => $this->configuration['services'],
			)
		);

		// Bail if an error occured.
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		// Return ID and Keyword Name.
		return array(
			'id'      => $result,
			'keyword' => $keyword,
		);

	}

	/**
	 * Creates the Location Keyword based on the supplied configuration
	 *
	 * @since   3.2.9
	 *
	 * @return  WP_Error|array
	 */
	private function setup_location_keyword() {

		// Define columns.
		$columns = array(
			'city_name',
			'city_latitude',
			'city_longitude',
			'county_name',
			'region_name',
		);

		// Send request to generate locations by area or radius.
		switch ( $this->configuration['method'] ) {
			case 'area':
				$terms = $this->base->get_class( 'keywords_generate_locations' )->generate_locations_by_area(
					array(
						'output_type'    => $columns,
						'country_code'   => $this->configuration['country_code'],
						'region_id'      => $this->configuration['region_id'], // Georocket.
						'county_id'      => $this->configuration['county_id'], // Georocket.
						'regions'        => $this->configuration['regions'], // AI.
						'counties'       => $this->configuration['counties'], // AI.
						'exclusions'     => $this->configuration['exclusions'],
						'population_min' => $this->configuration['population_min'],
						'population_max' => $this->configuration['population_max'],
					)
				);
				break;

			case 'radius':
				$terms = $this->base->get_class( 'keywords_generate_locations' )->generate_locations_by_radius(
					array(
						'output_type'    => $columns,
						'country_code'   => $this->configuration['country_code'],
						'location'       => $this->configuration['zipcode'],
						'radius'         => $this->configuration['radius'],
						'exclusions'     => $this->configuration['exclusions'],
						'population_min' => $this->configuration['population_min'],
						'population_max' => $this->configuration['population_max'],
					)
				);
				break;
		}

		// Bail if an error occured.
		if ( is_wp_error( $terms ) ) {
			return $terms;
		}

		// Save Keyword.
		$result = $this->base->get_class( 'keywords_generate_locations' )->save( $terms, $columns );

		// Bail if an error occured.
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		// Get Keyword by its ID, because keywords_generate_locations->save() doesn't return a Keyword name.
		$keyword = $this->base->get_class( 'keywords' )->get_by_id( $result );

		// Return Keyword ID.
		return array(
			'id'      => $keyword['keywordID'],
			'keyword' => $keyword['keyword'],
		);

	}

	/**
	 * Creates Content Groups for the Directory Structure
	 *
	 * @since   3.2.9
	 *
	 * @return  WP_Error|array
	 */
	private function setup_content_groups() {

		// Depending on the configuration, create the necessary Content Groups.
		switch ( $this->configuration['structure'] ) {

			/**
			 * Region > County > City > Service
			 */
			case 'region_county_city_service':
				// Region Group.
				$region_group_id = $this->setup_region_content_group(
					'Your content about the services offered in {' . $this->configuration['location_keyword'] . '(region_name)} goes here.'
				);
				if ( is_wp_error( $region_group_id ) ) {
					return $region_group_id;
				}

				// County Group.
				$county_group_id = $this->setup_county_content_group(
					'Your content about the services offered in {' . $this->configuration['location_keyword'] . '(county_name)} goes here.',
					$region_group_id,
					'{' . $this->configuration['location_keyword'] . '(region_name)}'
				);
				if ( is_wp_error( $county_group_id ) ) {
					return $county_group_id;
				}

				// City Group.
				$city_group_id = $this->setup_city_content_group(
					'Your content about the services offered in {' . $this->configuration['location_keyword'] . '(city_name)} goes here.',
					$county_group_id,
					'{' . $this->configuration['location_keyword'] . '(region_name)}/{' . $this->configuration['location_keyword'] . '(county_name)}'
				);
				if ( is_wp_error( $city_group_id ) ) {
					return $city_group_id;
				}

				// Service Group.
				$service_group_id = $this->setup_service_content_group(
					'Your content about {' . $this->configuration['service_keyword'] . '} in {' . $this->configuration['location_keyword'] . '(city_name)}, {' . $this->configuration['location_keyword'] . '(county_name)} goes here.',
					$city_group_id,
					'{' . $this->configuration['location_keyword'] . '(region_name)}/{' . $this->configuration['location_keyword'] . '(county_name)}/{' . $this->configuration['location_keyword'] . '(city_name)}'
				);
				if ( is_wp_error( $service_group_id ) ) {
					return $service_group_id;
				}

				// Related Links: Region Group.
				$result = $this->append_related_links_to_group_content(
					$region_group_id,
					$county_group_id,
					'{' . $this->configuration['location_keyword'] . '(region_name):url}',
					'Counties Served in {' . $this->configuration['location_keyword'] . '(region_name)}'
				);
				if ( is_wp_error( $result ) ) {
					return $result;
				}

				// Related Links: County Group.
				$result = $this->append_related_links_to_group_content(
					$county_group_id,
					$city_group_id,
					'{' . $this->configuration['location_keyword'] . '(region_name):url}/{' . $this->configuration['location_keyword'] . '(county_name):url}',
					'Cities Served in {' . $this->configuration['location_keyword'] . '(county_name)}'
				);
				if ( is_wp_error( $result ) ) {
					return $result;
				}

				// Related Links: City Group.
				$result = $this->append_related_links_to_group_content(
					$city_group_id,
					$service_group_id,
					'{' . $this->configuration['location_keyword'] . '(region_name):url}/{' . $this->configuration['location_keyword'] . '(county_name):url}/{' . $this->configuration['location_keyword'] . '(city_name):url}',
					'Services in {' . $this->configuration['location_keyword'] . '(city_name)}'
				);
				if ( is_wp_error( $result ) ) {
					return $result;
				}

				// Related Links: Service Group.
				$result = $this->append_related_links_to_group_content(
					$service_group_id,
					$service_group_id,
					'{' . $this->configuration['location_keyword'] . '(region_name):url}/{' . $this->configuration['location_keyword'] . '(county_name):url}/{' . $this->configuration['location_keyword'] . '(city_name):url}',
					'Other Services in {' . $this->configuration['location_keyword'] . '(city_name)}'
				);
				if ( is_wp_error( $result ) ) {
					return $result;
				}

				// Return created Content Group IDs.
				return array(
					'region_group_id'  => $region_group_id,
					'county_group_id'  => $county_group_id,
					'city_group_id'    => $city_group_id,
					'service_group_id' => $service_group_id,
				);

			/**
			 * Region > City > Service.
			 */
			case 'region_city_service':
				// Region Group.
				$region_group_id = $this->setup_region_content_group(
					'Your content about the services offered in {' . $this->configuration['location_keyword'] . '(region_name)} goes here.'
				);
				if ( is_wp_error( $region_group_id ) ) {
					return $region_group_id;
				}

				// City Group.
				$city_group_id = $this->setup_city_content_group(
					'Your content about the services offered in {' . $this->configuration['location_keyword'] . '(city_name)} goes here.',
					$region_group_id,
					'{' . $this->configuration['location_keyword'] . '(region_name)}'
				);
				if ( is_wp_error( $city_group_id ) ) {
					return $city_group_id;
				}

				// Service Group.
				$service_group_id = $this->setup_service_content_group(
					'Your content about {' . $this->configuration['service_keyword'] . '} in {' . $this->configuration['location_keyword'] . '(city_name)}, {' . $this->configuration['location_keyword'] . '(region_name)} goes here.',
					$city_group_id,
					'{' . $this->configuration['location_keyword'] . '(region_name)}/{' . $this->configuration['location_keyword'] . '(city_name)}'
				);
				if ( is_wp_error( $service_group_id ) ) {
					return $service_group_id;
				}

				// Related Links: Region Group.
				$result = $this->append_related_links_to_group_content(
					$region_group_id,
					$city_group_id,
					'{' . $this->configuration['location_keyword'] . '(region_name):url}',
					'Cities Served in {' . $this->configuration['location_keyword'] . '(region_name)}'
				);
				if ( is_wp_error( $result ) ) {
					return $result;
				}

				// Related Links: City Group.
				$result = $this->append_related_links_to_group_content(
					$city_group_id,
					$service_group_id,
					'{' . $this->configuration['location_keyword'] . '(region_name):url}/{' . $this->configuration['location_keyword'] . '(city_name):url}',
					'Services in {' . $this->configuration['location_keyword'] . '(city_name)}'
				);
				if ( is_wp_error( $result ) ) {
					return $result;
				}

				// Related Links: Service Group.
				$result = $this->append_related_links_to_group_content(
					$service_group_id,
					$service_group_id,
					'{' . $this->configuration['location_keyword'] . '(region_name):url}/{' . $this->configuration['location_keyword'] . '(city_name):url}',
					'Other Services in {' . $this->configuration['location_keyword'] . '(city_name)}'
				);
				if ( is_wp_error( $result ) ) {
					return $result;
				}

				// Return created Content Group IDs.
				return array(
					'region_group_id'  => $region_group_id,
					'city_group_id'    => $city_group_id,
					'service_group_id' => $service_group_id,
				);

			/**
			 * County > City > Service.
			 */
			case 'county_city_service':
				// County Group.
				$county_group_id = $this->setup_county_content_group(
					'Your content about the services offered in {' . $this->configuration['location_keyword'] . '(county_name)} goes here.'
				);
				if ( is_wp_error( $county_group_id ) ) {
					return $county_group_id;
				}

				// City Group.
				$city_group_id = $this->setup_city_content_group(
					'Your content about the services offered in {' . $this->configuration['location_keyword'] . '(city_name)} goes here.',
					$county_group_id,
					'{' . $this->configuration['location_keyword'] . '(county_name)}'
				);
				if ( is_wp_error( $city_group_id ) ) {
					return $city_group_id;
				}

				// Service Group.
				$service_group_id = $this->setup_service_content_group(
					'Your content about {' . $this->configuration['service_keyword'] . '} in {' . $this->configuration['location_keyword'] . '(city_name)}, {' . $this->configuration['location_keyword'] . '(county_name)} goes here.',
					$city_group_id,
					'{' . $this->configuration['location_keyword'] . '(county_name)}/{' . $this->configuration['location_keyword'] . '(city_name)}'
				);
				if ( is_wp_error( $service_group_id ) ) {
					return $service_group_id;
				}

				// Related Links: County Group.
				$result = $this->append_related_links_to_group_content(
					$county_group_id,
					$city_group_id,
					'{' . $this->configuration['location_keyword'] . '(county_name):url}',
					'Cities Served in {' . $this->configuration['location_keyword'] . '(county_name)}'
				);
				if ( is_wp_error( $result ) ) {
					return $result;
				}

				// Related Links: City Group.
				$result = $this->append_related_links_to_group_content(
					$city_group_id,
					$service_group_id,
					'{' . $this->configuration['location_keyword'] . '(county_name):url}/{' . $this->configuration['location_keyword'] . '(city_name):url}',
					'Services in {' . $this->configuration['location_keyword'] . '(city_name)}'
				);
				if ( is_wp_error( $result ) ) {
					return $result;
				}

				// Related Links: Service Group.
				$result = $this->append_related_links_to_group_content(
					$service_group_id,
					$service_group_id,
					'{' . $this->configuration['location_keyword'] . '(county_name):url}/{' . $this->configuration['location_keyword'] . '(city_name):url}',
					'Other Services in {' . $this->configuration['location_keyword'] . '(city_name)}'
				);
				if ( is_wp_error( $result ) ) {
					return $result;
				}

				// Return created Content Group IDs.
				return array(
					'county_group_id'  => $county_group_id,
					'city_group_id'    => $city_group_id,
					'service_group_id' => $service_group_id,
				);

			/**
			 * City > Service
			 */
			case 'city_service':
				// City Group.
				$city_group_id = $this->setup_city_content_group(
					'Your content about the services offered in {' . $this->configuration['location_keyword'] . '(city_name)} goes here.'
				);
				if ( is_wp_error( $city_group_id ) ) {
					return $city_group_id;
				}

				// Service Group.
				$service_group_id = $this->setup_service_content_group(
					'Your content about {' . $this->configuration['service_keyword'] . '} in {' . $this->configuration['location_keyword'] . '(city_name)}, {' . $this->configuration['location_keyword'] . '(county_name)} goes here.',
					$city_group_id,
					'{' . $this->configuration['location_keyword'] . '(city_name)}'
				);
				if ( is_wp_error( $service_group_id ) ) {
					return $service_group_id;
				}

				// Related Links: City Group.
				$result = $this->append_related_links_to_group_content(
					$city_group_id,
					$service_group_id,
					'{' . $this->configuration['location_keyword'] . '(city_name):url}',
					'Services in {' . $this->configuration['location_keyword'] . '(city_name)}'
				);
				if ( is_wp_error( $result ) ) {
					return $result;
				}

				// Related Links: Service Group.
				$result = $this->append_related_links_to_group_content(
					$service_group_id,
					$service_group_id,
					'{' . $this->configuration['location_keyword'] . '(city_name):url}',
					'Other Services in {' . $this->configuration['location_keyword'] . '(city_name)}'
				);
				if ( is_wp_error( $result ) ) {
					return $result;
				}

				// Return created Content Group IDs.
				return array(
					'city_group_id'    => $city_group_id,
					'service_group_id' => $service_group_id,
				);

			/**
			 * Service > City
			 */
			case 'service_city':
				// Service Group.
				$service_group_id = $this->setup_service_content_group(
					'Your content about {' . $this->configuration['service_keyword'] . '} goes here.'
				);
				if ( is_wp_error( $service_group_id ) ) {
					return $service_group_id;
				}

				// City Group.
				$city_group_id = $this->setup_city_content_group(
					'Your content about {' . $this->configuration['service_keyword'] . '} in {' . $this->configuration['location_keyword'] . '(city_name)} goes here.',
					$service_group_id,
					'{' . $this->configuration['service_keyword'] . '}'
				);
				if ( is_wp_error( $city_group_id ) ) {
					return $city_group_id;
				}

				// Related Links: Service Group.
				$result = $this->append_related_links_to_group_content(
					$service_group_id,
					$city_group_id,
					'{' . $this->configuration['service_keyword'] . '}',
					'Cities offering {' . $this->configuration['service_keyword'] . '}'
				);
				if ( is_wp_error( $result ) ) {
					return $result;
				}

				// Return created Content Group IDs.
				return array(
					'service_group_id' => $service_group_id,
					'city_group_id'    => $city_group_id,
				);

			/**
			 * Region > County > City
			 */
			case 'region_county_city':
				// Region Group.
				$region_group_id = $this->setup_region_content_group(
					'Your content / information about the product/service offered in {' . $this->configuration['location_keyword'] . '(region_name)} goes here.'
				);
				if ( is_wp_error( $region_group_id ) ) {
					return $region_group_id;
				}

				// County Group.
				$county_group_id = $this->setup_county_content_group(
					'Your content / information about the product/service offered in {' . $this->configuration['location_keyword'] . '(county_name)} goes here.',
					$region_group_id,
					'{' . $this->configuration['location_keyword'] . '(region_name)}'
				);
				if ( is_wp_error( $county_group_id ) ) {
					return $county_group_id;
				}

				// City Group.
				$city_group_id = $this->setup_city_content_group(
					'Your content about the services offered in {' . $this->configuration['location_keyword'] . '(city_name)} goes here.',
					$county_group_id,
					'{' . $this->configuration['location_keyword'] . '(region_name)}/{' . $this->configuration['location_keyword'] . '(county_name)}'
				);
				if ( is_wp_error( $city_group_id ) ) {
					return $city_group_id;
				}

				// Related Links: Region Group.
				$result = $this->append_related_links_to_group_content(
					$region_group_id,
					$county_group_id,
					'{' . $this->configuration['location_keyword'] . '(region_name):url}',
					'Counties Served in {' . $this->configuration['location_keyword'] . '(region_name)}'
				);
				if ( is_wp_error( $result ) ) {
					return $result;
				}

				// Related Links: County Group.
				$result = $this->append_related_links_to_group_content(
					$county_group_id,
					$city_group_id,
					'{' . $this->configuration['location_keyword'] . '(region_name):url}/{' . $this->configuration['location_keyword'] . '(county_name):url}',
					'Cities Served in {' . $this->configuration['location_keyword'] . '(county_name)}'
				);
				if ( is_wp_error( $result ) ) {
					return $result;
				}

				// Return created Content Group IDs.
				return array(
					'region_group_id' => $region_group_id,
					'county_group_id' => $county_group_id,
					'city_group_id'   => $city_group_id,
				);

			/**
			 * Region > City
			 */
			case 'region_city':
				// Region Group.
				$region_group_id = $this->setup_region_content_group(
					'Your content / information about the product/service offered in {' . $this->configuration['location_keyword'] . '(region_name)} goes here.'
				);
				if ( is_wp_error( $region_group_id ) ) {
					return $region_group_id;
				}

				// City Group.
				$city_group_id = $this->setup_city_content_group(
					'Your content about the services offered in {' . $this->configuration['location_keyword'] . '(city_name)} goes here.',
					$region_group_id,
					'{' . $this->configuration['location_keyword'] . '(region_name)}'
				);
				if ( is_wp_error( $city_group_id ) ) {
					return $city_group_id;
				}

				// Related Links: Region Group.
				$result = $this->append_related_links_to_group_content(
					$region_group_id,
					$city_group_id,
					'{' . $this->configuration['location_keyword'] . '(region_name):url}',
					'Cities Served in {' . $this->configuration['location_keyword'] . '(region_name)}'
				);
				if ( is_wp_error( $result ) ) {
					return $result;
				}

				// Return created Content Group IDs.
				return array(
					'region_group_id' => $region_group_id,
					'city_group_id'   => $city_group_id,
				);

			/**
			 * County > City
			 */
			case 'county_city':
				// County Group.
				$county_group_id = $this->setup_county_content_group(
					'Your content/information about the product/service offered in {' . $this->configuration['location_keyword'] . '(county_name)} goes here.'
				);
				if ( is_wp_error( $county_group_id ) ) {
					return $county_group_id;
				}

				// City Group.
				$city_group_id = $this->setup_city_content_group(
					'Your content about the services offered in {' . $this->configuration['location_keyword'] . '(city_name)} goes here.',
					$county_group_id,
					'{' . $this->configuration['location_keyword'] . '(county_name)}'
				);
				if ( is_wp_error( $city_group_id ) ) {
					return $city_group_id;
				}

				// Related Links: County Group.
				$result = $this->append_related_links_to_group_content(
					$county_group_id,
					$city_group_id,
					'{' . $this->configuration['location_keyword'] . '(county_name):url}',
					'Cities Served in {' . $this->configuration['location_keyword'] . '(county_name)}'
				);
				if ( is_wp_error( $result ) ) {
					return $result;
				}

				// Return created Content Group IDs.
				return array(
					'county_group_id' => $county_group_id,
					'city_group_id'   => $city_group_id,
				);

			/**
			 * Invalid type
			 */
			default:
				return new WP_Error( 'page_generator_pro_groups_directory_setup_content_groups', __( 'Invalid structure defined.', 'page-generator-pro' ) );

		}

	}

	/**
	 * Creates the Region Content Group
	 *
	 * @since   3.3.0
	 *
	 * @param   string $content            Content.
	 * @param   int    $parent_group_id    Parent Group ID (optional).
	 * @param   string $parent_slug        Parent Slug (optional).
	 * @return  WP_Error|int
	 */
	private function setup_region_content_group( $content, $parent_group_id = 0, $parent_slug = '' ) {

		return $this->base->get_class( 'groups' )->create(
			array(
				'title'       => '{' . $this->configuration['location_keyword'] . '(region_name)}',
				'content'     => $content,
				'permalink'   => '{' . $this->configuration['location_keyword'] . '(region_name)}',
				'pageParent'  => array(
					'page' => $parent_slug,
				),
				'description' => __( 'Regions', 'page-generator-pro' ),
			),
			$parent_group_id
		);

	}

	/**
	 * Creates the County Content Group
	 *
	 * @since   3.3.0
	 *
	 * @param   string $content            Content.
	 * @param   int    $parent_group_id    Parent Group ID (optional).
	 * @param   string $parent_slug        Parent Slug (optional).
	 * @return  WP_Error|int
	 */
	private function setup_county_content_group( $content, $parent_group_id = 0, $parent_slug = '' ) {

		return $this->base->get_class( 'groups' )->create(
			array(
				'title'       => '{' . $this->configuration['location_keyword'] . '(county_name)}',
				'content'     => $content,
				'permalink'   => '{' . $this->configuration['location_keyword'] . '(county_name)}',
				'pageParent'  => array(
					'page' => $parent_slug,
				),
				'description' => __( 'Counties', 'page-generator-pro' ),
			),
			$parent_group_id
		);

	}

	/**
	 * Creates the City Content Group
	 *
	 * @since   3.3.0
	 *
	 * @param   string $content            Content.
	 * @param   int    $parent_group_id    Parent Group ID (optional).
	 * @param   string $parent_slug        Parent Slug (optional).
	 * @return  WP_Error|int
	 */
	private function setup_city_content_group( $content, $parent_group_id = 0, $parent_slug = '' ) {

		return $this->base->get_class( 'groups' )->create(
			array(
				'title'       => '{' . $this->configuration['location_keyword'] . '(city_name)}',
				'content'     => $content,
				'permalink'   => '{' . $this->configuration['location_keyword'] . '(city_name)}',
				'latitude'    => '{' . $this->configuration['location_keyword'] . '(city_latitude)}',
				'longitude'   => '{' . $this->configuration['location_keyword'] . '(city_longitude)}',
				'pageParent'  => array(
					'page' => $parent_slug,
				),
				'description' => __( 'Cities', 'page-generator-pro' ),
			),
			$parent_group_id
		);

	}

	/**
	 * Creates the Service Content Group
	 *
	 * @since   3.3.0
	 *
	 * @param   string $content            Content.
	 * @param   int    $parent_group_id    Parent Group ID (optional).
	 * @param   string $parent_slug        Parent Slug (optional).
	 * @return  WP_Error|int
	 */
	private function setup_service_content_group( $content, $parent_group_id = 0, $parent_slug = '' ) {

		return $this->base->get_class( 'groups' )->create(
			array(
				'title'       => '{' . $this->configuration['service_keyword'] . '}',
				'content'     => $content,
				'permalink'   => '{' . $this->configuration['service_keyword'] . '}',
				'pageParent'  => array(
					'page' => $parent_slug,
				),
				'description' => __( 'Services', 'page-generator-pro' ),
			),
			$parent_group_id
		);

	}

	/**
	 * Appends the Related Links Shortcode to the given Content Group's Content
	 *
	 * @since   3.3.0
	 *
	 * @param   int    $group_id                   Group ID to append Related Links Shortcode on.
	 * @param   int    $related_links_group_id     Related Links Group ID Parameter.
	 * @param   string $related_links_parent_slug  Related Links Parent Slug Parameter.
	 * @param   string $related_links_heading      Related Links Heading Parameter.
	 * @return  WP_Error|int
	 */
	private function append_related_links_to_group_content( $group_id, $related_links_group_id, $related_links_parent_slug, $related_links_heading ) {

		// Get existing Group Content.
		$post = get_post( $group_id );

		// Append Heading and Related Links Shortcode to Group Content.
		$content = $post->post_content . "\n" . $this->get_related_links_heading( $related_links_heading ) . "\n" . $this->get_related_links_shortcode( $related_links_group_id, $related_links_parent_slug );

		// Update Group Content, appending Related Links.
		return wp_update_post(
			array(
				'ID'           => $group_id,
				'post_content' => $content,
			),
			true
		);

	}

	/**
	 * Returns the given heading text, wrapped in a <h2>
	 *
	 * @since   3.3.0
	 *
	 * @param   string $heading    Heading.
	 * @return  string              Heading wrapped in <h2> tags
	 */
	private function get_related_links_heading( $heading ) {

		$html = '<h2>' . $heading . '</h2>';

		/**
		 * Defines the header to display before the Related Links shortcode
		 * when Content Groups are generated through the Groups Directory
		 * functionality.
		 *
		 * @since   3.3.0
		 *
		 * @param   string  $html   HTML Markup for Heading
		 * @param   string  $heading    Heading Text without HTML
		 */
		$html = apply_filters( 'page_generator_pro_groups_directory_get_related_links_heading', $html, $heading );

		// Return.
		return $html;

	}

	/**
	 * Returns the Related Links shortcode for the given Group ID and Parent Slug
	 *
	 * @since   3.3.0
	 *
	 * @param   int    $group_id       Group ID.
	 * @param   string $parent_slug    Parent Slug.
	 * @return  string                  Related Links Shortcode
	 */
	private function get_related_links_shortcode( $group_id = 0, $parent_slug = '' ) {

		// Define shortcode.
		$shortcode = '[page-generator-pro-related-links group_id="' . $group_id . '" post_type="page" post_status="publish" post_parent="' . $parent_slug . '" output_type="list_links" limit="0" columns="3" orderby="name" order="asc"]';

		/**
		 * Defines the Related Links Shortcode to use when a Content Group
		 * is generated through the Groups Directory functionality.
		 *
		 * @since   3.3.0
		 *
		 * @param   string  $shortcode      Shortcode.
		 * @param   int     $group_id       Group ID.
		 * @param   string  $parent_slug    Parent Slug.
		 */
		$shortcode = apply_filters( 'page_generator_pro_groups_directory_get_related_links_shortcode', $shortcode, $group_id, $parent_slug );

		// Return.
		return $shortcode;

	}

}
