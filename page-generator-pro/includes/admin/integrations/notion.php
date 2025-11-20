<?php
/**
 * Notion API Class.
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Read data from a Notion database, to use in a Keyword.
 *
 * @package Page_Generator_Pro
 * @author  Tim Carr
 * @version 4.5.0
 */
class Page_Generator_Pro_Notion extends Page_Generator_Pro_API {

	use Page_Generator_Pro_Integration_Trait;

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
	public $name = 'notion';

	/**
	 * Holds the API endpoint
	 *
	 * @since   4.5.0
	 *
	 * @var     string
	 */
	public $api_endpoint = 'https://api.notion.com/v1';

	/**
	 * Holds the API date version.
	 *
	 * @since   4.5.0
	 *
	 * @var     string
	 */
	public $api_version = '2022-06-28';

	/**
	 * Constructor.
	 *
	 * @since   4.5.0
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
		add_filter( 'page_generator_pro_keywords_refresh_terms_' . $this->name, array( $this, 'refresh_terms' ), 10, 2 );

	}

	/**
	 * Returns the label of this integration.
	 *
	 * @since   4.8.0
	 *
	 * @return  string
	 */
	public function get_title() {

		return __( 'Notion', 'page-generator-pro' );

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
					__( 'Internal Integration Secret', 'page-generator-pro' )
				),
				'type'          => 'text',
				'default_value' => $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-integrations', $this->get_settings_prefix() . '_api_key' ),
				'description'   => sprintf(
					'%s %s %s',
					esc_html__( 'To use a Notion database as a Keyword Source, enter your Internal Integration Secret here.', 'page-generator-pro' ),
					'<a href="' . esc_attr( $this->base->plugin->documentation_url ) . '/settings-integration/#notion" target="_blank" rel="noopener">' . esc_html__( 'Click here', 'page-generator-pro' ) . '</a>',
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
	 * @since   4.5.0
	 *
	 * @param   array $sources    Sources.
	 * @return  array               Sources
	 */
	public function register_keyword_source( $sources ) {

		// Don't register this source if no API Key has been specified in the Integration Settings .
		if ( ! $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-integrations', 'notion_api_key', false ) ) {
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
						'database_id'  => array(
							'type'        => 'text',
							'label'       => __( 'Database ID', 'page-generator-pro' ),
							'description' =>
								sprintf(
									/* translators: Documentation URL */
									__( 'The database ID to use from Notion. %s for instructions on fetching your database ID', 'page-generator-pro' ),
									'<a href="' . $this->base->plugin->documentation_url . '/keywords/#adding---editing-keywords--source--notion" target="_blank" rel="noopener">' . __( 'Click here', 'page-generator-pro' ) . '</a>'
								),
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
	 * @since   4.5.0
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
	 * Refresh the given Keyword's Columns and Terms by fetching them from Notion
	 * immediately before starting generation.
	 *
	 * @since   4.5.0
	 *
	 * @param   string $terms      Terms.
	 * @param   array  $keyword    Keyword.
	 * @return  WP_Error|array     WP_Error | array (delimiter,columns,data)
	 */
	public function refresh_terms( $terms, $keyword ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter

		return $this->query( $keyword );

	}

	/**
	 * Fetches Terms from the Notion database, based on the Keyword settings
	 *
	 * @since   4.5.0
	 *
	 * @param   array $keyword    Keyword.
	 * @return  WP_Error|array              WP_Error | array (delimiter,columns,data)
	 */
	private function query( $keyword ) {

		// Fail if no API Key has been specified in the Integration Settings .
		if ( ! $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-integrations', 'notion_api_key', false ) ) {
			return new WP_Error(
				/* translators: Number of tokens remaining after prompt */
				'page_generator_pro_keywords_source_notion_get',
				__( 'You must specify an API Key at Settings > Integrations before using the Notion integration.', 'page-generator-pro' )
			);
		}

		// Determine if any table fields have been specified.
		$table_fields = false;
		if ( isset( $keyword['options']['table_fields'] ) && ! empty( $keyword['options']['table_fields'] ) ) {
			$table_fields = explode( ',', $keyword['options']['table_fields'] );
		}

		// Get data.
		return $this->base->get_class( 'notion' )->get_data(
			$this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-integrations', 'notion_api_key' ),
			$keyword['options']['database_id'],
			$table_fields
		);

	}

	/**
	 * Returns all data from the given database
	 *
	 * @since   4.5.0
	 *
	 * @param   string     $api_key        API Key.
	 * @param   string     $database_id    Database ID.
	 * @param   bool|array $fields         Table Fields to return.
	 * @param   bool|int   $offset         Offset (if specified, fetches paginated records).
	 * @return  WP_Error|array
	 */
	public function get_data( $api_key, $database_id, $fields = false, $offset = false ) {

		// Get first page of results.
		$results = $this->get_data_offset( $api_key, $database_id, $fields, $offset );

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
			$results = $this->get_data_offset( $api_key, $database_id, $fields, $results['offset'] );

			// Merge old results data with new results.
			$results['data'] = array_merge( $old_results['data'], $results['data'] );
		}

		// Remove offset flag and return results.
		unset( $results['offset'] );
		return $results;

	}

	/**
	 * Returns a subset of 100 rows from the given database ID, based on
	 * the supplied offset.
	 *
	 * If no offset is supplied, the first 100 rows are returned.
	 *
	 * The limit on the number of rows is determined by Notion.
	 *
	 * @since   4.5.0
	 *
	 * @param   string     $api_key        API Key.
	 * @param   string     $database_id    Database ID.
	 * @param   bool|array $fields         Table Fields to return.
	 * @param   bool|int   $offset         Offset (if specified, fetches paginated records).
	 * @return  WP_Error|array
	 */
	private function get_data_offset( $api_key, $database_id, $fields = false, $offset = false ) {

		// Set API Key.
		$this->set_api_key( $api_key );

		// Set Headers.
		$this->set_headers(
			array(
				'Authorization'  => 'Bearer ' . $this->api_key,
				'Content-Type'   => 'application/json',
				'Notion-Version' => $this->api_version,
			)
		);

		// Define URL.
		$url = 'databases/' . $database_id . '/query';

		// Build array of parameters.
		$params = array(
			'page_size' => 100, // Maximum that can be returned per request. Cannot exceed 100.
		);
		if ( $offset !== false ) {
			$params['start_cursor'] = $offset;
		}

		// Send Request.
		$response = $this->response(
			$this->post( $url, $params )
		);

		// Return if the response is an error.
		if ( is_wp_error( $response ) ) {
			return $response;
		}

		// Build columns first, because a row only includes fields
		// that contain values.  Iterating through guarantees we get
		// all possible columns.
		$columns = array();
		foreach ( $response->results as $index => $record ) {
			foreach ( $record->properties as $field => $data ) {
				// Strip non-alphanumeric characters so the column name is compatible with Keywords.
				$field = preg_replace( '/[^\\w-]/', '', $field );

				// Skip if fields to return were specified and this isn't one of those fields.
				if ( $fields && ! in_array( $field, $fields, true ) ) {
					continue;
				}

				// Add to array of columns.
				$columns[ $field ] = $field;
			}

			// Sort columns by name, to match the data.
			ksort( $columns );
		}

		// Iterate through records, building Terms that are compliant with Keywords.
		$data = array();
		foreach ( $response->results as $index => $record ) {
			// Build blank array comprising of all possible columns.
			$data[ $index ] = array();
			foreach ( $columns as $column ) {
				$data[ $index ][ $column ] = '';
			}

			// Iterate through this row's columns (fields).
			foreach ( $record->properties as $field => $field_data ) {
				// Strip non-alphanumeric characters so the column name is compatible with Keywords.
				$field = preg_replace( '/[^\\w-]/', '', $field );

				// Skip if fields to return were specified and this isn't one of those fields.
				if ( $fields && ! in_array( $field, $fields, true ) ) {
					continue;
				}

				$data[ $index ][ $field ] = $this->get_field_data( $field_data );
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
			'offset'    => ( isset( $response->next_cursor ) ? $response->next_cursor : false ),
		);

	}

	/**
	 * Returns the value stored in the given row property.
	 *
	 * @since   4.5.0
	 *
	 * @param   object $field_data     Field data.
	 * @return  string                  Value
	 */
	private function get_field_data( $field_data ) {

		// Assume the value is blank.
		$value = '';

		// Get value.
		switch ( $field_data->type ) {
			case 'files':
			case 'relation':
			case 'people':
				// Not supported.
				break;

			case 'phone_number':
			case 'email':
			case 'number':
			case 'url':
				$value = $field_data->{ $field_data->type };
				break;

			case 'checkbox':
				$value = ( $field_data->{ $field_data->type } ? 1 : 0 );
				break;

			case 'multi_select':
				$options = array();
				foreach ( $field_data->{ $field_data->type } as $option ) {
					$options[] = $option->name;
				}

				$value = implode( ', ', $options );
				break;

			case 'select':
			case 'status':
				// Skip if no value.
				if ( is_null( $field_data->{ $field_data->type } ) ) {
					break;
				}

				$value = $field_data->{ $field_data->type }->name;
				break;

			case 'formula':
				$value = $field_data->{ $field_data->type }->{ $field_data->{ $field_data->type }->type };
				break;

			case 'rich_text':
			case 'title':
				// Skip if no value.
				if ( ! count( $field_data->{ $field_data->type } ) ) {
					break;
				}

				$value = $field_data->{ $field_data->type }[0]->plain_text;
				break;
		}

		// Return value now if empty.
		if ( empty( $value ) ) {
			return $value;
		}

		// If the value contains newlines, replace them with <br />, as Notion
		// allows for long text / multiline text input on a single cell.
		if ( strpos( $value, "\n" ) !== false ) {
			$value = str_replace( "\n", '<br />', $value );
		}

		return $value;

	}

	/**
	 * Inspects the response from the API call, returning an error
	 * or data
	 *
	 * @since   4.5.0
	 *
	 * @param   WP_Error|object $response   Response.
	 * @return  WP_Error|object
	 */
	public function response( $response ) {

		// If the response is an error, return it.
		if ( is_wp_error( $response ) ) {
			return new WP_Error(
				'page_generator_pro_notion_error',
				sprintf(
					/* translators: Error message */
					__( 'Notion: %s', 'page-generator-pro' ),
					$response->get_error_message()
				)
			);
		}

		// Return an error if an error is present in the response.
		if ( $response->object === 'error' ) {
			// Return error object.
			return new WP_Error(
				'page_generator_pro_notion_error',
				sprintf(
					/* translators: Error message */
					__( 'Notion: %s', 'page-generator-pro' ),
					$response->message
				)
			);
		}

		// Return.
		return $response;

	}

}
