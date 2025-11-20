<?php
/**
 * Airtable API Class.
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Read data from an airtable.com base, to use in a Keyword.
 *
 * @package Page_Generator_Pro
 * @author  Tim Carr
 * @version 3.3.4
 */
class Page_Generator_Pro_Airtable extends Page_Generator_Pro_API {

	use Page_Generator_Pro_Integration_Trait;

	/**
	 * Holds the base object.
	 *
	 * @since   3.3.4
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
	public $name = 'airtable';

	/**
	 * Holds the API endpoint
	 *
	 * @since   3.3.4
	 *
	 * @var     string
	 */
	public $api_endpoint = 'https://api.airtable.com/v0';

	/**
	 * Holds the flag determining if the request data should be encoded
	 * into a JSON string
	 *
	 * If false, data is encoded using http_build_query()
	 *
	 * @since   3.3.4
	 *
	 * @var     bool
	 */
	public $is_json_request = false;

	/**
	 * Constructor.
	 *
	 * @since   3.9.2
	 *
	 * @param   object $base    Base Plugin Class.
	 */
	public function __construct( $base ) {

		// Store base class.
		$this->base = $base;

		// Register Settings Fields.
		add_filter( 'page_generator_pro_integrations_get', array( $this, 'register_integration' ) );
		add_filter( 'page_generator_pro_integrations_get_settings_fields', array( $this, 'get_settings_fields' ) );

		// Register as a Keyword Source.
		add_filter( 'page_generator_pro_keywords_register_sources', array( $this, 'register_keyword_source' ) );
		add_filter( 'page_generator_pro_keywords_save_' . $this->name, array( $this, 'save_keyword' ) );
		add_filter( 'page_generator_pro_keywords_refresh_terms_airtable', array( $this, 'refresh_terms' ), 10, 2 );

	}

	/**
	 * Returns the label of this integration.
	 *
	 * @since   4.8.0
	 *
	 * @return  string
	 */
	public function get_title() {

		return __( 'Airtable', 'page-generator-pro' );

	}

	/**
	 * Returns settings fields and their values to display on:
	 * - Settings > Integrations
	 * - Settings > Research
	 * - Settings > Spintax
	 *
	 * @since   3.9.2
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
					__( 'Personal Access Token', 'page-generator-pro' )
				),
				'type'          => 'text',
				'default_value' => $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-integrations', $this->get_settings_prefix() . '_api_key' ),
				'description'   => sprintf(
					'%s %s %s',
					esc_html__( 'To use an Airtable base as a Keyword Source, enter your Personal Access Token here.', 'page-generator-pro' ),
					'<a href="' . esc_attr( $this->base->plugin->documentation_url ) . '/settings-integration/#airtable" target="_blank" rel="noopener">' . esc_html__( 'Click here', 'page-generator-pro' ) . '</a>',
					esc_html__( 'to read the step by step documentation to do this.', 'page-generator-pro' )
				),
			),
		);

		return $settings_fields;

	}

	/**
	 * Registers this Source with the Keyword Sources system, so it's available
	 * to Keywords
	 *
	 * @since   3.3.4
	 *
	 * @param   array $sources    Sources.
	 * @return  array               Sources
	 */
	public function register_keyword_source( $sources ) {

		// Don't register this source if no API Key has been specified in the Integration Settings .
		if ( ! $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-integrations', 'airtable_api_key', false ) ) {
			return $sources;
		}

		// Add Source.
		return array_merge(
			$sources,
			array(
				$this->get_name() => array(
					'name'    => $this->get_name(),
					'label'   => $this->get_title(),
					'options' => array(
						'base_id'      => array(
							'type'        => 'text',
							'label'       => __( 'Base ID', 'page-generator-pro' ),
							'description' =>
								sprintf(
									/* translators: Documentation URL */
									__( 'The base ID (spreadsheet) to use from Airtable. %s for instructions on fetching your Base ID', 'page-generator-pro' ),
									'<a href="' . $this->base->plugin->documentation_url . '/keywords/#adding---editing-keywords--source--airtable" target="_blank" rel="noopener">' . __( 'Click here', 'page-generator-pro' ) . '</a>'
								),
						),
						'table_name'   => array(
							'type'        => 'text',
							'label'       => __( 'Table Name', 'page-generator-pro' ),
							'description' => sprintf(
								/* translators: Documentation URL */
								__( 'The table name within the above Base to use as the Keyword Data. %s for instructions on fetching your Table Name', 'page-generator-pro' ),
								'<a href="' . $this->base->plugin->documentation_url . '/keywords/#adding---editing-keywords--source--airtable" target="_blank" rel="noopener">' . __( 'Click here', 'page-generator-pro' ) . '</a>'
							),
						),
						'table_view'   => array(
							'type'        => 'text',
							'label'       => __( 'Table View', 'page-generator-pro' ),
							'description' => __( 'Optional: The name of a view in the table to use for Keyword Data. If blank, all records are returned. Note: fields hidden in your view will be returned due to Airtable\'s API. To only return a subset of fields, use the Table Fields option below.', 'page-generator-pro' ),
						),
						'table_fields' => array(
							'type'        => 'text',
							'label'       => __( 'Table Fields', 'page-generator-pro' ),
							'description' => __( 'Optional: A comma separated list of table fields to include in the Keyword Data. If blank, all fields are returned.', 'page-generator-pro' ),
						),
						'preview'      => array(
							'type'  => 'preview',
							'label' => __( 'Terms', 'page-generator-pro' ),
						),
					),
				),
			)
		);

	}

	/**
	 * Prepares Keyword Data for this Source, based on the supplied form data,
	 * immediately before it's saved to the Keywords table in the database
	 *
	 * @since   3.3.4
	 *
	 * @param   array $keyword        Keyword Parameters.
	 * @return  WP_Error|array
	 */
	public function save_keyword( $keyword ) {

		// Get Keyword Terms.
		$result = $this->query( $keyword );

		// Bail if an error occured.
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		// Merge delimiter, columns and data with Keyword.
		$keyword = array_merge(
			$keyword,
			array(
				'delimiter' => $result['delimiter'],
				'columns'   => ( is_array( $result['columns'] ) ? implode( ',', $result['columns'] ) : '' ),
				'data'      => implode( "\n", $result['data'] ),
			)
		);

		return $keyword;

	}

	/**
	 * Refresh the given Keyword's Columns and Terms by fetching them from Airtable
	 * immediately before starting generation.
	 *
	 * @since   3.3.4
	 *
	 * @param   string $terms      Terms.
	 * @param   array  $keyword    Keyword.
	 * @return  WP_Error|array     WP_Error | array (delimiter,columns,data)
	 */
	public function refresh_terms( $terms, $keyword ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter

		return $this->query( $keyword );

	}

	/**
	 * Fetches Terms from the Airtable Base's Table, based on the Keyword settings
	 *
	 * @since   3.3.4
	 *
	 * @param   array $keyword    Keyword.
	 * @return  WP_Error|array              WP_Error | array (delimiter,columns,data)
	 */
	private function query( $keyword ) {

		// Fail if no API Key has been specified in the Integration Settings .
		if ( ! $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-integrations', 'airtable_api_key', false ) ) {
			return new WP_Error(
				'page_generator_pro_keywords_source_airtable_get',
				__( 'You must specify an API Key at Settings > Integrations before using the Airtable integration.', 'page-generator-pro' )
			);
		}

		// Determine if a table view has been specified.
		$table_view = false;
		if ( isset( $keyword['options']['table_view'] ) && ! empty( $keyword['options']['table_view'] ) ) {
			$table_view = $keyword['options']['table_view'];
		}

		// Determine if any table fields have been specified.
		$table_fields = false;
		if ( isset( $keyword['options']['table_fields'] ) && ! empty( $keyword['options']['table_fields'] ) ) {
			$table_fields = explode( ',', $keyword['options']['table_fields'] );
		}

		// Get data.
		return $this->base->get_class( 'airtable' )->get_data(
			$this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-integrations', 'airtable_api_key' ),
			$keyword['options']['base_id'],
			$keyword['options']['table_name'],
			$table_view,
			$table_fields
		);

	}

	/**
	 * Returns all data from the given Base ID and Table Name
	 *
	 * @since   3.3.4
	 *
	 * @param   string      $api_key        API Key.
	 * @param   string      $base_id        Base ID.
	 * @param   string      $table_name     Table Name.
	 * @param   bool|string $view           Table View.
	 * @param   bool|array  $fields         Table Fields to return.
	 * @param   bool|int    $offset         Offset (if specified, fetches paginated records).
	 * @return  WP_Error|array
	 */
	public function get_data( $api_key, $base_id, $table_name, $view = false, $fields = false, $offset = false ) {

		// Get first page of results.
		$results = $this->get_data_offset( $api_key, $base_id, $table_name, $view, $fields, $offset );

		// If an error occured, bail.
		if ( is_wp_error( $results ) ) {
			return $results;
		}

		// If no additional results i.e. less than 100 rows, return data now.
		if ( ! $results['offset'] ) {
			// Remove offset flag and return results.
			unset( $results['offset'] );
			return $results;
		}

		// Recurse through offsets until all data fetched.
		while ( $results['offset'] ) {
			// Store last query's results in a separate variable.
			$old_results = $results;

			// Get next page of results.
			$results = $this->get_data_offset( $api_key, $base_id, $table_name, $view, $fields, $results['offset'] );

			// Merge old results data with new results.
			$results['data'] = array_merge( $old_results['data'], $results['data'] );
		}

		// Remove offset flag and return results.
		unset( $results['offset'] );
		return $results;

	}

	/**
	 * Returns a subset of 100 rows from the given Base ID and Table Name, based on
	 * the supplied offset.
	 *
	 * If no offset is supplied, the first 100 rows are returned.
	 *
	 * The limit on the number of rows is determined by Airtable.
	 *
	 * @since   3.3.4
	 *
	 * @param   string      $api_key        API Key.
	 * @param   string      $base_id        Base ID.
	 * @param   string      $table_name     Table Name.
	 * @param   bool|string $view           Table View.
	 * @param   bool|array  $fields         Table Fields to return.
	 * @param   bool|int    $offset         Offset (if specified, fetches paginated records).
	 * @return  WP_Error|array
	 */
	private function get_data_offset( $api_key, $base_id, $table_name, $view = false, $fields = false, $offset = false ) {

		// Set API Key.
		$this->set_api_key( $api_key );

		// Set Headers.
		$this->set_headers(
			array(
				'Authorization' => 'Bearer ' . $this->api_key,
			)
		);

		// Define URL.
		$url = $base_id . '/' . rawurlencode( $table_name );

		// Build array of parameters.
		$params = array(
			// Required for fields that are not strings in Airtable: for now, we need them as strings.
			'cellFormat' => 'string',
			'timeZone'   => wp_timezone_string(),
			'userLocale' => get_locale(),
		);
		if ( $view !== false ) {
			$params['view'] = $view;
		}
		if ( $fields !== false ) {
			$params['fields'] = $fields;
		}
		if ( $offset !== false ) {
			$params['offset'] = $offset;
		}

		// If parameters exist, append them to the URL now.
		$url = add_query_arg( $params, $url );

		// Send Request.
		$response = $this->response(
			$this->get( $url )
		);

		// Return if the response is an error.
		if ( is_wp_error( $response ) ) {
			return $response;
		}

		// Build columns first, because a row only includes fields
		// that contain values.  Iterating through guarantees we get
		// all possible columns.
		$columns = array();
		foreach ( $response->records as $index => $record ) {
			foreach ( $record->fields as $field => $value ) {
				$columns[ $field ] = $field;
			}

			// Sort columns by name, to match the data.
			ksort( $columns );
		}

		// Iterate through records, building Terms that are compliant with Keywords.
		$data = array();
		foreach ( $response->records as $index => $record ) {
			// Build blank array comprising of all possible columns.
			$data[ $index ] = array();
			foreach ( $columns as $column ) {
				$data[ $index ][ $column ] = '';
			}

			// Iterate through this row's columns (fields).
			foreach ( $record->fields as $field => $value ) {
				// If the value contains newlines, replace them with <br />, as Airtable
				// allows for long text / multiline text input on a single cell.
				if ( strpos( $value, "\n" ) !== false ) {
					$value = str_replace( "\n", '<br />', $value );
				}

				// Build row.
				$data[ $index ][ $field ] = $value;
			}

			// Sort row columns by name, so that the data order is always the same.
			ksort( $data[ $index ] );

			// If more than one column exists, implode the data and encapsulate in quotation marks.
			if ( count( $columns ) > 1 ) {
				$data[ $index ] = '"' . implode( '","', $data[ $index ] ) . '"';
			} else {
				$data[ $index ] = implode( ',', $data[ $index ] );
			}
		}

		// Return.
		return array(
			'delimiter' => ( count( $columns ) > 1 ? ',' : '' ),
			'columns'   => ( count( $columns ) > 1 ? $columns : '' ),
			'data'      => $data,
			'offset'    => ( isset( $response->offset ) ? $response->offset : false ),
		);

	}

	/**
	 * Inspects the response from the API call, returning an error
	 * or data
	 *
	 * @since   3.3.4
	 *
	 * @param   WP_Error|object $response   Response.
	 * @return  WP_Error|object
	 */
	public function response( $response ) {

		// If the response is an error, return it.
		if ( is_wp_error( $response ) ) {
			return new WP_Error(
				'page_generator_pro_airtable_error',
				sprintf(
					/* translators: Error message */
					__( 'Airtable: %s', 'page-generator-pro' ),
					$response->get_error_message()
				)
			);
		}

		// Return an error if an error is present in the response.
		if ( isset( $response->error ) ) {
			// Depending on the error, we might be able to return a more helpful error message.
			if ( is_string( $response->error ) ) {
				switch ( $response->error ) {
					case 'NOT_FOUND':
						$message = __( 'Base ID or Table Name not found.', 'page-generator-pro' );
						break;
					default:
						$message = $response->error;
						break;
				}
			} else {
				$message = $response->error->message;
			}

			// Return error object.
			return new WP_Error(
				'page_generator_pro_airtable_error',
				sprintf(
					/* translators: Error message */
					__( 'Airtable: %s', 'page-generator-pro' ),
					$message
				)
			);
		}

		// Return.
		return $response;

	}

}
