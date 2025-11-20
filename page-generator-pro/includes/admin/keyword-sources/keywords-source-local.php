<?php
/**
 * Local Keyword Source Class
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Registers a local Keyword source, enabling data stored in the Keyword's Term field
 * to be used for a Keyword.
 *
 * This is the default option available prior to 3.0.8.
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 3.0.8
 */
class Page_Generator_Pro_Keywords_Source_Local {

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
		add_filter( 'page_generator_pro_keywords_save_local', array( $this, 'save' ) );

		// Validate Keyword for this source before saving.
		add_filter( 'page_generator_pro_keywords_validate_local', array( $this, 'validate' ), 10, 3 );

	}

	/**
	 * Returns the programmatic name of the source
	 *
	 * @since   3.0.8
	 *
	 * @return  string
	 */
	public function get_name() {

		return 'local';

	}

	/**
	 * Returns the label of the source
	 *
	 * @since   3.0.8
	 *
	 * @return  string
	 */
	public function get_label() {

		return __( 'Local', 'page-generator-pro' );

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
						'data'      => array(
							'type'        => 'textarea',
							'label'       => __( 'Terms', 'page-generator-pro' ),
							'description' => array(
								__( 'Word(s) or phrase(s) which will be cycled through when generating content using the above keyword template tag.', 'page-generator-pro' ),
								__( 'One word / phrase per line.', 'page-generator-pro' ),
								__( 'If no Terms are entered, the plugin will try to automatically determine a list of similar terms based on the supplied keyword when you click Save.', 'page-generator-pro' ),
							),
						),
						'delimiter' => array(
							'type'        => 'text',
							'label'       => __( 'Delimiter', 'page-generator-pro' ),
							'description' => array(
								__( 'Optional: If each Keyword Term comprises of two or more words, and you wish to access individual word(s) within each Term when using this Keyword in the Generate Content / Terms screens, define the seperating delimiter here.', 'page-generator-pro' ),
								__( 'For example, if Keyword Terms above are in the format <code>City, County, ZIP Code</code> the delimiter would be a comma <code>,</code>', 'page-generator-pro' ),
							),
						),
						'columns'   => array(
							'type'        => 'text',
							'label'       => __( 'Columns', 'page-generator-pro' ),
							'description' => array(
								__( 'Optional: If each Keyword Term comprises of two or more words, and you wish to access individual word(s) within each Term when using this Keyword in the Generate Content / Terms screens, define each column name here.', 'page-generator-pro' ),
								__( 'For example, if your Keyword Terms are in the format <code>City, County, ZIP Code</code>, enter <code>city,county,zipcode</code> here.', 'page-generator-pro' ),
								__( 'When generating content, you can then use e.g. <code>{keyword(city)}</code> for each City.', 'page-generator-pro' ),
								__( 'Separate column names with a comma, regardless of the Delimiter specified above.', 'page-generator-pro' ),
							),
						),
						'file'      => array(
							'type'        => 'file',
							'label'       => __( 'Text File Import', 'page-generator-pro' ),
							'description' => array(
								__( 'If you have a list of Terms in a text file, upload it here (one word / phrase per line).', 'page-generator-pro' ),
								__( 'This will append the imported words / phrases to the above Keyword Data.', 'page-generator-pro' ),
								__( 'Keyword data in a CSV file? Use the CSV File or CSV URL as the Source above.', 'page-generator-pro' ),
							),
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
	 * @param   array $keyword    Keyword Parameters.
	 * @return  WP_Error|array
	 */
	public function save( $keyword ) {

		// Append Text File Terms, if supplied.
		$text_file = $this->has_text_file();
		if ( $text_file !== false ) {
			// Read file.
			$terms = $this->base->get_class( 'keywords' )->read_text_file( $text_file );

			// Bail if an error occured.
			if ( is_wp_error( $terms ) ) {
				return $terms;
			}

			// Append to Terms.
			$keyword['options']['data'] .= "\n" . $terms;
		}

		// If there is no keyword data, try to generate terms automatically.
		if ( empty( $keyword['options']['data'] ) ) {
			// Setup Thesaurus API and run query.
			$terms = $this->base->get_class( 'thesaurus' )->get_synonyms( $keyword['keyword'] );

			// Bail if we couldn't get terms.
			if ( is_wp_error( $terms ) ) {
				return $terms;
			}

			// Add terms to keyword.
			$keyword['options']['data'] = implode( "\n", $terms );
		}

		// Merge options with Keyword.
		$keyword = array_merge(
			$keyword,
			array(
				'delimiter' => $keyword['options']['delimiter'],
				'columns'   => $keyword['options']['columns'],
				'data'      => $keyword['options']['data'],
			)
		);

		// Remove options.
		$keyword['options'] = '';

		// Return.
		return $keyword;

	}

	/**
	 * Runs validation tests specific to this source for a Keyword immediately before it's saved to the database.
	 *
	 * @since   3.0.9
	 *
	 * @param   WP_Error|bool $result     Validation Result.
	 * @param   array         $keyword    Keyword.
	 * @param   array         $form_data  Keyword Form Data.
	 * @return  WP_Error|bool
	 */
	public function validate( $result, $keyword, $form_data ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter

		// If result is an error from e.g. another filter, bail.
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		// Check that data was supplied.
		if ( empty( $keyword['data'] ) ) {
			return new WP_Error( 'page_generator_pro_keywords_save_validation_error', __( 'Please complete the keyword data field.', 'page-generator-pro' ) );
		}

		// If a delimiter is supplied, perform some further validation checks.
		if ( ! empty( $keyword['delimiter'] ) ) {
			// Check that the delimiter exists in the first term.
			$first_term = trim( strtok( $keyword['data'], "\n" ) );
			if ( strpos( $first_term, $keyword['delimiter'] ) === false ) {
				return new WP_Error(
					'page_generator_pro_keywords_save_validation_error',
					sprintf(
						/* translators: delimiter character */
						__( 'Delimiter Field: The specified delimiter %s could not be found in the Terms lists\' first term. Ensure the delimiter used in the Terms list matches the Delimiter Field.', 'page-generator-pro' ),
						'<code>' . $keyword['delimiter'] . '</code>'
					)
				);
			}

			// Check that the number of columns specified matches the number of deliniated items in the first term.
			$reader = \League\Csv\Reader::createFromString( $keyword['columns'] . "\n" . $keyword['data'] );
			$reader->setDelimiter( $keyword['delimiter'] );
			$reader->setHeaderOffset( 0 );
			$columns = $reader->getHeader();
			$term    = $reader->fetchOne( 0 );
			if ( count( $term ) !== count( $columns ) ) {
				return new WP_Error(
					'page_generator_pro_keywords_save_validation_error',
					__( 'Columns Field: The number of column names detected does not match the number of deliniated items in the first term.', 'page-generator-pro' )
				);
			}
		}

		// Validation passed.
		return true;

	}

	/**
	 * Checks if a text file was uploaded with the form submission request.
	 *
	 * @since   3.0.8
	 *
	 * @return  bool|string     Path and Filename
	 */
	private function has_text_file() {

		// phpcs:disable WordPress.Security.NonceVerification.Missing
		// Check a file has been uploaded.
		if ( ! isset( $_FILES[ $this->get_name() ]['name']['file'] ) ) {
			return false;
		}
		if ( ! isset( $_FILES[ $this->get_name() ]['type']['file'] ) ) {
			return false;
		}

		// Check uploaded file is a supported filetype.
		$file_type = sanitize_text_field( wp_unslash( $_FILES[ $this->get_name() ]['type']['file'] ) );
		$file_name = sanitize_text_field( wp_unslash( $_FILES[ $this->get_name() ]['name']['file'] ) );
		if ( ! ( ! empty( $file_type ) && preg_match( '/(text|txt)$/i', $file_type ) ) &&
			! preg_match( '/(text|txt)$/i', $file_name ) ) {
			return false;
		}

		// Return path and file.
		return $file_name;
		// phpcs:enable

	}

}
