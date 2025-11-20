<?php
/**
 * CSV Keyword Source Class
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Registers a CSV File, stored in the Media Library, as a Keyword source, enabling CSV data to be used
 * for a Keyword.
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 3.0.8
 */
class Page_Generator_Pro_Keywords_Source_CSV {

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
		add_filter( 'page_generator_pro_keywords_save_csv', array( $this, 'save' ) );

		// Refresh Keyword Terms before starting generation.
		add_filter( 'page_generator_pro_keywords_refresh_terms_csv', array( $this, 'refresh_terms' ), 10, 2 );

	}

	/**
	 * Returns the programmatic name of the source
	 *
	 * @since   3.0.8
	 *
	 * @return  string
	 */
	public function get_name() {

		return 'csv';

	}

	/**
	 * Returns the label of the source
	 *
	 * @since   3.0.8
	 *
	 * @return  string
	 */
	public function get_label() {

		return __( 'CSV File', 'page-generator-pro' );

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
						'attachment_id'     => array(
							'type'        => 'media_library',
							'label'       => __( 'CSV File', 'page-generator-pro' ),
							'file_type'   => 'text/csv',
							'description' => __( 'The CSV File in the Media Library to use as the Terms for this Keyword', 'page-generator-pro' ),
						),
						'columns_first_row' => array(
							'type'        => 'toggle',
							'label'       => __( 'Columns in First Row', 'page-generator-pro' ),
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

		// Bail if no Attachment chosen.
		if ( ! isset( $keyword['options']['attachment_id'] ) ) {
			return new WP_Error( 'page_generator_pro_keywords_source_csv_no_attachment', __( 'Please choose a CSV File from the Media Library', 'page-generator-pro' ) );
		}
		if ( ! $keyword['options']['attachment_id'] ) {
			return new WP_Error( 'page_generator_pro_keywords_source_csv_no_attachment', __( 'Please choose a CSV File from the Media Library', 'page-generator-pro' ) );
		}

		// Specify columns_first_row option.
		if ( ! isset( $keyword['options']['columns_first_row'] ) ) {
			$keyword['options']['columns_first_row'] = 0;
			$keyword['options']['delimiter']         = '';
		}

		// Get CSV File.
		$file = $this->get_attached_file( $keyword['options']['attachment_id'], 0, $keyword['keyword'] );
		if ( is_wp_error( $file ) ) {
			return $file;
		}
		$keyword['options']['file'] = $file;

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
	 * @return  WP_Error|array
	 */
	public function refresh_terms( $terms, $keyword ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter

		// Get CSV File.
		$file = $this->get_attached_file( $keyword['options']['attachment_id'], $keyword['keywordID'], $keyword['keyword'] );
		if ( is_wp_error( $file ) ) {
			return $file;
		}
		$keyword['options']['file'] = $file;

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
	 * Returns the full path and file of the given Attachment ID, or WP_Error
	 * if the file doesn't exist
	 *
	 * @since   3.0.9
	 *
	 * @param   int    $attachment_id  Attachment ID.
	 * @param   int    $keyword_id     Keyword ID.
	 * @param   string $keyword_name   Keyword Name.
	 * @return  WP_Error|string
	 */
	private function get_attached_file( $attachment_id, $keyword_id, $keyword_name ) {

		// Get Attachment.
		$file = get_attached_file( absint( $attachment_id ) );

		// Bail if we couldn't fetch the file i.e. it's no longer in the Media Library.
		if ( ! $file || ( is_string( $file ) && ! file_exists( $file ) ) ) {
			return new WP_Error(
				'page_generator_pro_keywords_source_csv_refresh_terms_error',
				sprintf(
					/* translators: %1$s: Keyword Name, %2$s: Link to edit Keyword, already translated  */
					__( 'The CSV File for Keyword %1$s no longer exists in the Media Library.  Please %2$s and choose a different CSV source, then attempt generation again.', 'page-generator-pro' ),
					$keyword_name,
					'<a href="admin.php?page=page-generator-pro-keywords&cmd=form&id=' . absint( $keyword_id ) . '">' . __( 'Edit the keyword', 'page-generator-pro' ) . '</a>'
				)
			);
		}

		// Return full path and file.
		return $file;

	}

	/**
	 * Fetches Terms from the CSV file, based on the Keyword settings
	 *
	 * @since   3.0.8
	 *
	 * @param   array $keyword    Keyword.
	 * @return  WP_Error|array
	 */
	private function get( $keyword ) {

		// Read CSV File  with or without columns depending on the Keyword settings.
		if ( $keyword['options']['columns_first_row'] ) {
			return $this->base->get_class( 'keywords' )->read_csv_file(
				$keyword['options']['file'],
				'columns_single_keyword',
				$keyword['options']['delimiter'],
				$keyword['keyword']
			);
		}

		return $this->base->get_class( 'keywords' )->read_csv_file(
			$keyword['options']['file'],
			false,
			false,
			$keyword['keyword']
		);

	}

}
