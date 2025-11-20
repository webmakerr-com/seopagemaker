<?php
/**
 * CSV URL Keyword Source Class
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Registers a CSV URL as a Keyword source, enabling CSV data to be used
 * for a Keyword.
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 3.0.8
 */
class Page_Generator_Pro_Keywords_Source_CSV_URL {

	/**
	 * Holds the base object.
	 *
	 * @since   3.0.8
	 *
	 * @var     object
	 */
	public $base;

	/**
	 * Constructor
	 *
	 * @since   3.0.8
	 *
	 * @param   object $base    Base Plugin Class.
	 */
	public function __construct( $base ) {

		// Store base class.
		$this->base = $base;

		// Register this Keyword Source.
		add_filter( 'page_generator_pro_keywords_register_sources', array( $this, 'register' ) );

		// Define parameters for the Keyword before saving.
		add_filter( 'page_generator_pro_keywords_save_csv_url', array( $this, 'save' ) );

		// Refresh Keyword Terms before starting generation.
		add_filter( 'page_generator_pro_keywords_refresh_terms_csv_url', array( $this, 'refresh_terms' ), 10, 2 );

	}

	/**
	 * Returns the programmatic name of the source
	 *
	 * @since   3.0.8
	 *
	 * @return  string
	 */
	public function get_name() {

		return 'csv_url';

	}

	/**
	 * Returns the label of the source
	 *
	 * @since   3.0.8
	 *
	 * @return  string
	 */
	public function get_label() {

		return __( 'CSV URL', 'page-generator-pro' );

	}

	/**
	 * Registers this Source with the Keyword Sources system, so it's available
	 * to Keywords
	 *
	 * @since   3.0.8
	 *
	 * @param   array $sources    Sources.
	 * @return  array               Sources
	 */
	public function register( $sources ) {

		return array_merge(
			$sources,
			array(
				$this->get_name() => array(
					'name'    => $this->get_name(),
					'label'   => $this->get_label(),
					'options' => array(
						'url'               => array(
							'type'        => 'url',
							'label'       => __( 'CSV URL', 'page-generator-pro' ),
							'description' => __( 'The CSV File URL to use as the Terms for this Keyword', 'page-generator-pro' ),
						),
						'columns_first_row' => array(
							'type'        => 'toggle',
							'label'       => __( 'Columns in First Row', 'page-generator-pro' ),
							'file_type'   => 'text/csv',
							'description' => __( 'If enabled, the first row\'s values will be used as column names', 'page-generator-pro' ),
						),
						'delimiter'         => array(
							'type'        => 'text',
							'label'       => __( 'Delimiter', 'page-generator-pro' ),
							'description' => __( 'If "Columns in First Row" is enabled, specify the delimiter used between columns here. Typically this will be a comma.', 'page-generator-pro' ),
						),
						'preview'           => array(
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
	 * @since   3.0.8
	 *
	 * @param   array $keyword        Keyword Parameters.
	 * @return  WP_Error|array
	 */
	public function save( $keyword ) {

		// Bail if no URL was specified.
		if ( empty( $keyword['options']['url'] ) ) {
			return new WP_Error(
				'page_generator_pro_keywords_source_csv_url',
				__( 'No CSV URL was specified in the Keyword.', 'page-generator-pro' )
			);
		}

		// Bail if an invalid URL was specified.
		if ( ! filter_var( $keyword['options']['url'], FILTER_VALIDATE_URL ) ) {
			return new WP_Error(
				'page_generator_pro_keywords_source_csv_url',
				__( 'The CSV URL field must be a valid URL pointing to a CSV file.', 'page-generator-pro' )
			);
		}

		// Specify columns_first_row option.
		if ( ! isset( $keyword['options']['columns_first_row'] ) ) {
			$keyword['options']['columns_first_row'] = 0;
			$keyword['options']['delimiter']         = '';
		}

		// If the URL is for Google Sheets, change the output format to CSV
		// if this hasn't already been done.
		if ( strpos( $keyword['options']['url'], 'docs.google.com' ) ) {
			if ( ! strpos( $keyword['options']['url'], 'export?format=csv' ) ) {
				// Get URL parts.
				$url = wp_parse_url( $keyword['options']['url'] );

				// Remove '/edit' if it exists and append /export?format=csv.
				$keyword['options']['url'] = $url['scheme'] . '://' . $url['host'] . str_replace( '/edit', '', $url['path'] ) . '/export?format=csv';
			}
		}

		// Get Keyword Terms.
		$keywords_terms = $this->get( $keyword );

		// Bail if an error occured.
		if ( is_wp_error( $keywords_terms ) ) {
			return $keywords_terms;
		}

		// Merge delimiter, columns and data with Keyword.
		$keyword = array_merge(
			$keyword,
			array(
				'delimiter' => $keyword['options']['delimiter'],
				'columns'   => ( is_array( $keywords_terms[ $keyword['keyword'] ]['columns'] ) ? implode( ',', $keywords_terms[ $keyword['keyword'] ]['columns'] ) : '' ),
				'data'      => implode( "\n", $keywords_terms[ $keyword['keyword'] ]['data'] ),
			)
		);

		return $keyword;

	}

	/**
	 * Refresh the given Keyword's Columns and Terms by fetching them from the CSV file
	 * immediately before starting generation.
	 *
	 * @since   3.0.8
	 *
	 * @param   string $terms      Terms.
	 * @param   array  $keyword    Keyword.
	 * @return  WP_Error|array     WP_Error | array (delimiter,columns,data)
	 */
	public function refresh_terms( $terms, $keyword ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter

		// Get Keyword Terms.
		$keywords_terms = $this->get( $keyword );

		// Bail if an error occured.
		if ( is_wp_error( $keywords_terms ) ) {
			return $keywords_terms;
		}

		// Return Keyword.
		return $keywords_terms[ $keyword['keyword'] ];

	}

	/**
	 * Fetches Terms from the CSV URL, based on the Keyword settings
	 *
	 * @since   3.0.8
	 *
	 * @param   array $keyword    Keyword.
	 * @return  WP_Error|array    WP_Error | array (delimiter,columns,data)
	 */
	private function get( $keyword ) {

		// Read CSV URL  with or without columns depending on the Keyword settings.
		if ( $keyword['options']['columns_first_row'] ) {
			return $this->base->get_class( 'keywords' )->read_csv_file(
				$keyword['options']['url'],
				'columns_single_keyword',
				$keyword['options']['delimiter'],
				$keyword['keyword']
			);
		}

		return $this->base->get_class( 'keywords' )->read_csv_file(
			$keyword['options']['url'],
			false,
			false,
			$keyword['keyword']
		);

	}

}
