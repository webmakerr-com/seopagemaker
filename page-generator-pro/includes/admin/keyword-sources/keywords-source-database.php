<?php
/**
 * Database Keyword Source Class
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Registers a database table as a Keyword source, enabling database table data to be used
 * for a Keyword.
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 3.0.8
 */
class Page_Generator_Pro_Keywords_Source_Database {

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
		add_filter( 'page_generator_pro_keywords_save_database', array( $this, 'save' ) );

		// Refresh Keyword Terms before starting generation.
		add_filter( 'page_generator_pro_keywords_refresh_terms_database', array( $this, 'refresh_terms' ), 10, 2 );

	}

	/**
	 * Returns the programmatic name of the source
	 *
	 * @since   3.0.8
	 *
	 * @return  string
	 */
	public function get_name() {

		return 'database';

	}

	/**
	 * Returns the label of the source
	 *
	 * @since   3.0.8
	 *
	 * @return  string
	 */
	public function get_label() {

		return __( 'Database', 'page-generator-pro' );

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
						'table'   => array(
							'type'        => 'select',
							'label'       => __( 'Table', 'page-generator-pro' ),
							'values'      => $this->get_tables(),
							'description' => __( 'The database table to use as Keyword Terms.', 'page-generator-pro' ),
						),
						'preview' => array(
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
	 * @param   array $keyword    Keyword Parameters.
	 * @return  array               Keyword Parameters
	 */
	public function save( $keyword ) {

		// Merge delimiter, columns and data with Keyword.
		$keyword = array_merge(
			$keyword,
			array(
				'delimiter' => $this->get_delimiter(),
				'columns'   => implode( $this->get_delimiter(), $this->get_columns( $keyword['options']['table'] ) ),
				'data'      => implode( "\n", $this->get_all_rows( $keyword['options']['table'] ) ),
			)
		);

		return $keyword;

	}

	/**
	 * Refresh the given Keyword's Columns and Terms by fetching them from the database
	 * immediately before starting generation.
	 *
	 * @since   3.0.8
	 *
	 * @param   string $terms      Terms.
	 * @param   array  $keyword    Keyword.
	 * @return  WP_Error|array     WP_Error | array (delimiter,columns,data)
	 */
	public function refresh_terms( $terms, $keyword ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter

		return array(
			'delimiter' => $this->get_delimiter(),
			'columns'   => $this->get_columns( $keyword['options']['table'] ),
			'data'      => $this->get_all_rows( $keyword['options']['table'] ),
		);

	}

	/**
	 * Returns all of the database tables that can be used as a source
	 *
	 * @since   3.0.8
	 *
	 * @return  array
	 */
	private function get_tables() {

		global $wpdb;

		$results = $wpdb->get_col(
			$wpdb->prepare(
				'SELECT table_name FROM information_schema.tables WHERE TABLE_SCHEMA = %s',
				DB_NAME // @phpstan-ignore-line
			)
		);

		$tables = array();
		foreach ( $results as $result ) {
			$tables[ $result ] = $result;
		}

		return $tables;

	}

	/**
	 * Returns columns that can be used to fetch data within a Term
	 *
	 * @since   3.0.8
	 *
	 * @param   string $table  Table to fetch columns from.
	 * @return  array           Table Columns
	 */
	private function get_columns( $table ) {

		global $wpdb;

		$results = $wpdb->get_results( 'SHOW COLUMNS FROM ' . $table ); // phpcs:ignore WordPress.DB.PreparedSQL

		$columns = array();
		foreach ( $results as $result ) {
			$columns[] = $result->Field; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		}

		return $columns;

	}

	/**
	 * Returns the delimiter to use to deliniate a Term by its columns
	 *
	 * @since   3.0.8
	 *
	 * @return  string  Delimiter
	 */
	private function get_delimiter() {

		return ',';

	}

	/**
	 * Fetches all rows from the database table
	 *
	 * @since   3.0.8
	 *
	 * @param   string $table  Database Table.
	 * @return  array           Terms (Rows)
	 */
	private function get_all_rows( $table ) {

		global $wpdb;

		// Query.
		$results = $wpdb->get_results( "SELECT * FROM {$table}", ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		// If no results, bail.
		if ( empty( $results ) ) {
			return array();
		}

		// Build data into CSV format.
		$data = array();
		foreach ( $results as $result ) {
			$row = array();

			// Build the row array, adding slashes to quotation marks, as we do for RSS.
			foreach ( $result as $key => $value ) {
				$row[ $key ] = addslashes( $this->replace_newlines_and_remove_tabs( $value ) );
			}

			// Add row.
			$data[] = '"' . implode( '","', $row ) . '"';
		}

		return $data;

	}

	/**
	 * Replaces newlines with breaklines, and removes tabs from the given string.
	 *
	 * @since   4.4.1
	 *
	 * @param   string $item   Database table row column value.
	 * @return  string
	 */
	private function replace_newlines_and_remove_tabs( $item ) {

		return str_replace(
			"\n",
			'<br />',
			str_replace(
				"\t",
				'',
				$item
			)
		);

	}

}
