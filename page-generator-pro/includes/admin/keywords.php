<?php
/**
 * Keywords Class
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

use League\Csv\Exception;
use League\Csv\Reader;
use League\Csv\CharsetConverter;

use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * Reads and writes Keywords from the database table,
 * performing validation on create/edit/delete actions.
 *
 * Handles import functionality within the Keywords
 * section of the Plugin.
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 1.0.0
 */
class Page_Generator_Pro_Keywords {

	/**
	 * Holds the base class object.
	 *
	 * @since   1.9.7
	 *
	 * @var     object
	 */
	public $base;

	/**
	 * Primary SQL Table
	 *
	 * @since   1.0.0
	 *
	 * @var     string
	 */
	public $table = 'page_generator_keywords';

	/**
	 * Primary SQL Table Primary Key
	 *
	 * @since   1.0.0
	 *
	 * @var     string
	 */
	public $key = 'keywordID';

	/**
	 * Holds query results from calling get_keywords_names(),
	 * for performance
	 *
	 * @since   3.0.9
	 *
	 * @var     mixed
	 */
	private $keywords_names = false;

	/**
	 * Holds query results from calling get_keywords_and_columns(),
	 * for performance
	 *
	 * @since   3.0.7
	 *
	 * @var     mixed
	 */
	private $keywords_columns = false;

	/**
	 * Holds query results from calling get_keywords_and_columns(),
	 * for performance
	 *
	 * @since   3.1.3
	 *
	 * @var     mixed
	 */
	private $keywords_columns_with_curly_braces = false;

	/**
	 * Holds the delimter for Keyword Terms where no columns are specified.
	 *
	 * Used to convert the string of data into an array.
	 *
	 * @since   4.3.2
	 *
	 * @var     string
	 */
	private $keywords_terms_eol = "\n";

	/**
	 * Constructor.
	 *
	 * @since   1.9.8
	 *
	 * @param   object $base    Base Plugin Class.
	 */
	public function __construct( $base ) {

		// Store base class.
		$this->base = $base;

	}

	/**
	 * Activation routines for this Model
	 *
	 * @since   1.0.7
	 *
	 * @global  $wpdb   WordPress DB Object
	 */
	public function activate() {

		global $wpdb;

		// Enable error output if WP_DEBUG is enabled.
		$wpdb->show_errors = true;

		// Create database tables.
		$wpdb->query(
			' CREATE TABLE IF NOT EXISTS ' . $wpdb->prefix . 'page_generator_keywords (
                            `keywordID` int(10) NOT NULL AUTO_INCREMENT,
                            `keyword` varchar(191) NOT NULL,
                            `source` varchar(191) NOT NULL,
                            `options` text NOT NULL,
                            `columns` text NOT NULL,
                            `delimiter` varchar(191) NOT NULL,
                            `data` longtext NOT NULL,
                            PRIMARY KEY `keywordID` (`keywordID`),
                            UNIQUE KEY `keyword` (`keyword`)
                        ) ' . $wpdb->get_charset_collate() . ' AUTO_INCREMENT=1'
		);

	}

	/**
	 * Upgrades the Model's database table if required columns
	 * are missing.
	 *
	 * @since   1.7.8
	 *
	 * @global  $wpdb   WordPress DB Object.
	 */
	public function upgrade() {

		global $wpdb;

		// Fetch columns.
		$columns = $wpdb->get_results( 'SHOW COLUMNS FROM ' . $wpdb->prefix . 'page_generator_keywords' );

		// Bail if no columns found.
		if ( ! is_array( $columns ) || count( $columns ) === 0 ) {
			return true;
		}

		// Define columns we're searching for.
		$required_columns = array(
			'source'    => false,
			'options'   => false,
			'columns'   => false,
			'delimiter' => false,
		);

		// Iterate through columns.
		foreach ( $columns as $column ) {
			if ( array_key_exists( $column->Field, $required_columns ) ) { // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
				$required_columns[ $column->Field ] = true; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
			}
		}

		// Iterate through our required columns, adding them to the database table if they don't exist.
		foreach ( $required_columns as $column => $exists ) {
			if ( $exists ) {
				continue;
			}

			switch ( $column ) {
				/**
				 * Text columns
				 */
				case 'options':
				case 'columns':
					$wpdb->query(
						$wpdb->prepare(
							'ALTER TABLE %1$spage_generator_keywords ADD COLUMN `%2$s` text NOT NULL AFTER `keyword`', // phpcs:ignore WordPress.DB.PreparedSQLPlaceholders
							$wpdb->prefix,
							$column
						)
					);
					break;

				/**
				 * Varchar columns
				 */
				case 'source':
				case 'delimiter':
					$wpdb->query(
						$wpdb->prepare(
							'ALTER TABLE %1$spage_generator_keywords ADD COLUMN `%2$s` varchar(191) NOT NULL AFTER `keyword`', // phpcs:ignore WordPress.DB.PreparedSQLPlaceholders
							$wpdb->prefix,
							$column
						)
					);
					break;
			}
		}

		return true;

	}

	/**
	 * Changes the 'columns' field from varchar to text, so that many column names can be stored
	 * against a Keyword
	 *
	 * @since   2.4.5
	 */
	public function upgrade_columns_type_to_text() {

		global $wpdb;

		// Fetch columns.
		$columns = $wpdb->get_results( 'SHOW COLUMNS FROM ' . $wpdb->prefix . 'page_generator_keywords' );

		// Find column.
		foreach ( $columns as $column ) {
			if ( $column->Field !== 'columns' ) { // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
				continue;
			}

			// If here, we found the column we want.
			if ( $column->Type === 'text' ) { // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
				// Already set to the correct type.
				return true;
			}

			// Change column from varchar to text.
			$wpdb->query(
				$wpdb->prepare(
					'ALTER TABLE %1$spage_generator_keywords MODIFY COLUMN `%2$s` text NOT NULL', // phpcs:ignore WordPress.DB.PreparedSQLPlaceholders
					$wpdb->prefix,
					$column->Field // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
				)
			);

			return true;
		}

		return true;

	}

	/**
	 * Changes the 'data' field from mediumtext to longtext, so that more Terms can be stored against a Keyword
	 *
	 * @since   3.1.9
	 */
	public function upgrade_data_type_to_longtext() {

		global $wpdb;

		// Fetch columns.
		$columns = $wpdb->get_results( 'SHOW COLUMNS FROM ' . $wpdb->prefix . 'page_generator_keywords' );

		// Find column.
		foreach ( $columns as $column ) {
			if ( $column->Field !== 'data' ) { // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
				continue;
			}

			// If here, we found the column we want.
			if ( $column->Type === 'longtext' ) { // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
				// Already set to the correct type.
				return true;
			}

			// Change column from varchar to text.
			$wpdb->query(
				$wpdb->prepare(
					'ALTER TABLE %1$spage_generator_keywords MODIFY COLUMN `%2$s` longtext NOT NULL', // phpcs:ignore WordPress.DB.PreparedSQLPlaceholders
					$wpdb->prefix,
					$column->Field // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
				)
			);

			return true;
		}

		return true;

	}

	/**
	 * Returns an array of Keyword Sources and their attributes.
	 *
	 * @since   3.0.8
	 *
	 * @return  bool|array
	 */
	public function get_sources() {

		$sources = array();

		/**
		 * Register a Keyword Source.
		 *
		 * @since   3.0.8
		 *
		 * @param   array   $sources    Keyword Sources.
		 */
		$sources = apply_filters( 'page_generator_pro_keywords_register_sources', $sources );

		// Sort sources alphabetically.
		ksort( $sources );

		return $sources;

	}

	/**
	 * Updates Terms for the given Keywords, if a Keyword's Source isn't local,
	 * by fetching them from the remote sources.
	 *
	 * @since   3.0.8
	 *
	 * @param   array $keywords   Keywords to Update Terms for.
	 * @return  WP_Error|bool
	 */
	public function refresh_terms( $keywords ) {

		global $wpdb;

		// Iterate through Keywords, updating each Keyword's Terms.
		foreach ( $keywords as $keyword ) {
			// Get Keyword.
			$keyword = $wpdb->get_row(
				$wpdb->prepare(
					"SELECT keywordID, keyword, source, columns, delimiter, options FROM {$wpdb->prefix}page_generator_keywords WHERE keyword = %s LIMIT 1",
					$keyword
				),
				ARRAY_A
			);

			// Skip if the Keyword doesn't exist.
			if ( is_null( $keyword ) ) {
				continue;
			}
			if ( ! count( $keyword ) ) {
				continue;
			}

			// Skip if the Keyword's source is local or blank (blank is a local source prior to 3.0.8).
			if ( $keyword['source'] === 'local' || empty( $keyword['source'] ) ) {
				continue;
			}

			// Expand options JSON.
			if ( ! empty( $keyword['options'] ) ) {
				$keyword['options'] = json_decode( $keyword['options'], true );
			}

			/**
			 * Refresh the given Keyword's Columns and Terms by fetching them from the database
			 * immediately before starting generation.
			 *
			 * @since   3.0.8
			 *
			 * @param   WP_Error|array      $terms      Terms.
			 * @param   array               $keyword    Keyword.
			 */
			$result = apply_filters( 'page_generator_pro_keywords_refresh_terms_' . $keyword['source'], array(), $keyword );

			// If the result is a WP_Error, bail.
			if ( is_wp_error( $result ) ) {
				return $result;
			}

			// Update Keyword Delimiter, Columns and Data.
			$keyword = array_merge(
				$keyword,
				array(
					'delimiter' => $result['delimiter'],
					'columns'   => ( is_array( $result['columns'] ) ? implode( ',', $result['columns'] ) : '' ),
					'data'      => implode( "\n", $result['data'] ),
				)
			);

			// Save Keyword (returns WP_Error or Keyword ID).
			$result = $this->save( $keyword, $keyword['keywordID'] );

			// If saving the Keyword failed, bail.
			if ( is_wp_error( $result ) ) {
				return $result;
			}
		}

		// All Keyword Terms refreshed.
		return true;

	}

	/**
	 * Gets a record by its ID
	 *
	 * @since   1.0.0
	 *
	 * @param   int $id  ID.
	 * @return  bool|array
	 */
	public function get_by_id( $id ) {

		global $wpdb;

		// Get record.
		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}page_generator_keywords WHERE keywordID = %d LIMIT 1",
				$id
			),
			ARRAY_A
		);

		// Check a record was found   . .
		if ( ! $results ) {
			return false;
		}
		if ( count( $results ) === 0 ) {
			return false;
		}

		// Return single result from results.
		return $this->get( $results[0] );

	}

	/**
	 * Gets a single result by the key/value pair
	 *
	 * @since   1.0.0
	 *
	 * @param   string $field  Field Name.
	 * @param   string $value  Field Value.
	 * @return  bool|array          Records
	 */
	public function get_by( $field, $value ) {

		global $wpdb;

		// Get record.
		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}page_generator_keywords WHERE {$field} = %s", // phpcs:ignore WordPress.DB
				$value
			),
			ARRAY_A
		);

		// Check a record was found.
		if ( ! $results ) {
			return false;
		}
		if ( count( $results ) === 0 ) {
			return false;
		}

		// Return single result from results.
		return $this->get( $results[0] );

	}

	/**
	 * Returns an array of records
	 *
	 * @since   1.0.0
	 *
	 * @param   string $order_by           Order By Column (default: keyword, optional).
	 * @param   string $order              Order Direction (default: ASC, optional).
	 * @param   int    $paged              Pagination (default: 1, optional).
	 * @param   int    $results_per_page   Results per page (default: 10, optional).
	 * @param   string $search             Search Keywords (optional).
	 * @return  bool|array                      Records
	 */
	public function get_all( $order_by = 'keyword', $order = 'ASC', $paged = 1, $results_per_page = 10, $search = '' ) {

		global $wpdb;

		$get_all = ( ( $paged == -1 ) ? true : false ); // phpcs:ignore Universal.Operators.StrictComparisons.LooseEqual

		// Sanitize order by.
		$order_by_sql = sanitize_sql_orderby( "{$order_by} {$order}" );

		// Search.
		if ( ! empty( $search ) ) {
			$query = $wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}page_generator_keywords WHERE keyword LIKE %s ORDER BY {$order_by_sql}", // phpcs:ignore WordPress.DB
				'%' . $wpdb->esc_like( $search ) . '%'
			);
		} else {
			$query = "SELECT * FROM {$wpdb->prefix}page_generator_keywords ORDER BY {$order_by_sql}"; // phpcs:ignore WordPress.DB
		}

		// Add Limit.
		if ( ! $get_all ) {
			$query = $query . $wpdb->prepare(
				' LIMIT %d, %d',
				( ( $paged - 1 ) * $results_per_page ),
				$results_per_page
			);
		}

		// Get results.
		$results = $wpdb->get_results( $query ); // phpcs:ignore WordPress.DB

		// Check a record was found.
		if ( ! $results ) {
			return false;
		}
		if ( count( $results ) === 0 ) {
			return false;
		}

		return stripslashes_deep( $results );

	}

	/**
	 * Returns keywords names in lowercase
	 *
	 * @since   3.0.9
	 *
	 * @param   bool $include_curly_braces   Include Curly Braces on Keywords in Results.
	 * @return  bool|array                          Keywords
	 */
	public function get_keywords_names( $include_curly_braces = false ) {

		// If the query results are already stored, use those for performance.
		if ( $this->keywords_names ) {
			return $this->keywords_names;
		}

		global $wpdb;

		// Get results.
		$results = $wpdb->get_results( "SELECT keyword FROM {$wpdb->prefix}page_generator_keywords ORDER BY keyword ASC", ARRAY_A );

		// Check a record was found   .
		if ( ! $results ) {
			return false;
		}
		if ( count( $results ) === 0 ) {
			return false;
		}

		// Iterate through results, building keywords.
		$keywords = array();
		foreach ( $results as $result ) {
			// Add keywords.
			$keywords[] = strtolower( ( $include_curly_braces ? '{' : '' ) . $result['keyword'] . ( $include_curly_braces ? '}' : '' ) );
		}

		// Store results in class for performance, to save running this query again.
		$this->keywords_names = stripslashes_deep( $keywords );

		// Return.
		return $this->keywords_names;

	}

	/**
	 * Returns keywords and keywords with individual column subsets.
	 *
	 * @since   1.9.7
	 *
	 * @param   bool $include_curly_braces   Include Curly Braces on Keywords in Results.
	 * @return  bool|array                          Keywords
	 */
	public function get_keywords_and_columns( $include_curly_braces = false ) {

		// If the query results are already stored, use those for performance.
		if ( $include_curly_braces ) {
			if ( $this->keywords_columns_with_curly_braces ) {
				return $this->keywords_columns_with_curly_braces;
			}
		} elseif ( $this->keywords_columns ) {
				return $this->keywords_columns;
		}

		global $wpdb;

		// Get results.
		$results = $wpdb->get_results( "SELECT keyword, columns, delimiter FROM {$wpdb->prefix}page_generator_keywords ORDER BY keyword ASC", ARRAY_A ); // phpcs:ignore WordPress.DB

		// Check a record was found.
		if ( ! $results ) {
			return false;
		}
		if ( count( $results ) === 0 ) {
			return false;
		}

		// Iterate through results, building keywords.
		$keywords = array();
		foreach ( $results as $result ) {
			// Add keywords.
			$keywords[] = ( $include_curly_braces ? '{' : '' ) . $result['keyword'] . ( $include_curly_braces ? '}' : '' );

			// If the columns are empty, ignore.
			if ( empty( $result['columns'] ) ) {
				continue;
			}

			// If the delimiter is missing, ignore.
			if ( empty( $result['delimiter'] ) ) {
				continue;
			}

			// Get columns.
			$columns = explode( ',', $result['columns'] );

			// Add each column as a keyword.
			foreach ( $columns as $column ) {
				$keywords[] = ( $include_curly_braces ? '{' : '' ) . $result['keyword'] . '(' . trim( $column ) . ')' . ( $include_curly_braces ? '}' : '' );
			}
		}

		// Store results in class for performance, to save running this query again.
		if ( $include_curly_braces ) {
			$this->keywords_columns_with_curly_braces = stripslashes_deep( $keywords );
			return $this->keywords_columns_with_curly_braces;
		}

		$this->keywords_columns = stripslashes_deep( $keywords );
		return $this->keywords_columns;

	}

	/**
	 * Confirms whether a keyword already exists.
	 *
	 * @since   1.0.0
	 *
	 * @param   string   $keyword        Keyword.
	 * @param   bool|int $id             Keyword ID (if defined and matches the ID found for an existing keyword, it's ignored).
	 * @return  bool                        Exists
	 */
	public function exists( $keyword, $id = false ) {

		global $wpdb;

		// Prepare query.
		if ( ! $id ) {
			$query = $wpdb->prepare(
				"SELECT keywordID FROM {$wpdb->prefix}page_generator_keywords WHERE keyword = %s",
				$keyword
			);
		} else {
			$query = $wpdb->prepare(
				"SELECT keywordID FROM {$wpdb->prefix}page_generator_keywords WHERE keyword = %s AND keywordID != %d",
				$keyword,
				$id
			);
		}

		// Run query.
		$results = $wpdb->get_results( $query, ARRAY_A ); // phpcs:ignore WordPress.DB

		// Check a record was found.
		if ( ! $results ) {
			return false;
		}
		if ( count( $results ) === 0 ) {
			return false;
		}

		return true;

	}

	/**
	 * Returns a unique Keyword Name that has not yet been saved to the database,
	 * based on the proposed Keyword Name
	 *
	 * @since   3.2.9
	 *
	 * @param   string $proposed_keyword_name   Proposed Keyword Name.
	 * @return  string                          Keyword Name
	 */
	public function get_unique_name( $proposed_keyword_name ) {

		// If the proposed keyword doesn't exist, return it.
		if ( ! $this->exists( $proposed_keyword_name ) ) {
			return $proposed_keyword_name;
		}

		for ( $i = 1; $i <= 100; $i++ ) {
			$keyword_name = $proposed_keyword_name . '_' . $i;
			if ( ! $this->exists( $keyword_name ) ) {
				return $keyword_name;
			}
		}

		// Return a unique string.
		return uniqid( $proposed_keyword_name );

	}

	/**
	 * Get the number of matching records
	 *
	 * @since   1.0.0
	 *
	 * @param   string $search Search Keywords (optional).
	 * @return  bool            Exists
	 */
	public function total( $search = '' ) {

		global $wpdb;

		// Prepare query.
		if ( ! empty( $search ) ) {
			$query = $wpdb->prepare(
				"SELECT COUNT(keywordID) FROM {$wpdb->prefix}page_generator_keywords WHERE keyword LIKE %s",
				'%' . $wpdb->esc_like( $search ) . '%'
			);
		} else {
			$query = "SELECT COUNT(keywordID) FROM {$wpdb->prefix}page_generator_keywords";
		}

		// Return count.
		return $wpdb->get_var( $query ); // phpcs:ignore WordPress.DB

	}

	/**
	 * Return Terms for the given Keyword ID, based on the optional
	 * offset, limit and search parameters.
	 *
	 * @since   3.0.9
	 *
	 * @param   int         $id             Keyword ID.
	 * @param   int         $offset         Record Offset.
	 * @param   int         $limit          Number of Terms to return. 0 = all Terms.
	 * @param   bool|string $search         Search Terms.
	 * @param   bool        $associative    Return results with column names.
	 * @return  bool|array                  Keyword Terms
	 */
	public function get_terms( $id, $offset = 0, $limit = 0, $search = false, $associative = false ) {

		// Get Keyword.
		$keyword = $this->get_by_id( $id );
		if ( ! $keyword ) {
			return false;
		}

		// Read data.
		if ( $keyword['columns'] && $keyword['delimiter'] ) {
			$reader = Reader::createFromString( $keyword['columns'] . "\n" . $keyword['data'] );
			$reader->setDelimiter( $keyword['delimiter'] );
			$reader->setHeaderOffset( 0 );
			$columns = $reader->getHeader();
		} else {
			$reader = Reader::createFromString( $keyword['data'] );
		}

		// If no pagination or search parameters exists, return now.
		if ( ! $offset && ! $limit && ! $search ) {
			$terms = array_values( iterator_to_array( $reader->getRecords() ) );
			if ( $associative ) {
				return array(
					'data'     => $terms,
					'total'    => count( $terms ),
					'filtered' => count( $terms ),
				);
			}

			// Convert to numeric arrays.
			foreach ( $terms as $index => $term ) {
				$terms[ $index ] = array_values( $term );
			}
			return array(
				'data'     => $terms,
				'total'    => count( $terms ),
				'filtered' => count( $terms ),
			);
		}

		// Define total and filtered record counts.
		$total = count( $reader );

		// Create query.
		$query = \League\Csv\Statement::create();

		// Add search constraints.
		if ( $search ) {
			$query = $query->where(
				function ( $record ) use ( $search ) {
					foreach ( $record as $cell ) {
						if ( stripos( $cell, $search ) !== false ) {
							return true;
						}
					}

					return false;
				}
			);
		}

		// Define filtered record count now, before we apply pagination.
		$filtered = count( $query->process( $reader ) );

		// Add pagination constraints.
		if ( $offset || $limit ) {
			$query = $query->offset( $offset )->limit( $limit );
		}

		// Run query.
		$records = $query->process( $reader );

		// Return associative array.
		$terms = array_values( iterator_to_array( $records->getRecords() ) );
		if ( $associative ) {
			return array(
				'data'     => $terms,
				'total'    => $total,
				'filtered' => $filtered,
			);
		}

		// Convert to numeric arrays.
		foreach ( $terms as $index => $term ) {
			$terms[ $index ] = array_values( $term );
		}
		return array(
			'data'     => $terms,
			'total'    => $total,
			'filtered' => $filtered,
		);

	}

	/**
	 * Returns the given record, casting values, stripping slashes
	 * and expanding data into arrays
	 *
	 * @since   3.0.8
	 *
	 * @param   array $result     Keyword Row.
	 * @return  array               Keyword Row
	 */
	private function get( $result ) {

		// Cast values.
		$result['keywordID'] = absint( $result['keywordID'] );

		// Stripslashes.
		$result['data']      = stripslashes( $result['data'] );
		$result['delimiter'] = stripslashes( $result['delimiter'] );
		$result['columns']   = stripslashes( $result['columns'] );

		// Expand data into array.
		$result['dataArr']    = $this->terms_to_array( $result['data'] );
		$result['columnsArr'] = explode( ',', $result['columns'] );

		// Expand options JSON.
		if ( ! empty( $result['options'] ) ) {
			$result['options'] = json_decode( $result['options'], true );
		}

		// Define the source as local if no source exists.
		if ( empty( $result['source'] ) ) {
			$result['source'] = 'local';
		}

		// Return record.
		return $result;

	}

	/**
	 * Converts the given string of Keyword Terms to an array.
	 *
	 * @since   4.3.2
	 *
	 * @param   string $terms           Keyword Terms.
	 * @return  array                   Keyword Terms
	 */
	public function terms_to_array( $terms ) {

		return explode( $this->keywords_terms_eol, $terms );

	}

	/**
	 * Reads an uploaded text file of keyword data into a string.
	 *
	 * @since   1.0.7
	 *
	 * @param   string $file           Full Path and Filename to CSV File.
	 * @return  string                  Data
	 */
	public function read_text_file( $file ) {

		// Get file contents.
		$contents = $this->base->get_class( 'common' )->file_get_contents( $file );

		// Remove UTF8 BOM sequences.
		$contents = $this->remove_utf8_bom( $contents );

		// Return.
		return $contents;

	}

	/**
	 * Reads an uploaded CSV file of keyword data into an array.
	 *
	 * Supports multiple CSV structures, as detailed in the $csv_format argument below.
	 *
	 * @since   1.7.3
	 *
	 * @param   string      $file           Either URL or Full Path and Filename to CSV File.
	 * @param   string      $csv_format     CSV Format.
	 *                                      columns_single_keyword: Import Columns into a single Keyword. Columns = Column Names.
	 *                                      columns_multiple_keywords: Import Columns as multiple Keywords. Columns = Keyword Names.
	 *                                      rows_single_keyword: Import Rows into a single Keyword. Rows = Column Names.
	 *                                      rows_multiple_keywords: Import Rows into multiple Keywords. Rows = Keyword Names.
	 * @param   string      $delimiter      Delimiter.
	 * @param   bool|string $keyword_name   Keyword Name.
	 *                                      (string) required when $csv_format is columns_single_keyword or rows_single_keyword.
	 *                                      (bool) false when $csv_format is columns_multiple_keywords or rows_multiple_keywords.
	 * @return  WP_Error|array
	 */
	public function read_csv_file( $file, $csv_format = 'columns_single_keyword', $delimiter = ',', $keyword_name = false ) {

		// Bail if no file is specified.
		if ( empty( $file ) ) {
			return new WP_Error(
				'page_generator_pro_keywords_read_csv_file',
				__( 'No CSV file was specified.', 'page-generator-pro' )
			);
		}

		// Get file contents from local or remote file.
		if ( filter_var( $file, FILTER_VALIDATE_URL ) ) {
			// Get content.
			$result = wp_remote_get(
				$file,
				array(
					'timeout' => 60,
				)
			);

			// Bail if an error occured.
			if ( is_wp_error( $result ) ) {
				return new WP_Error(
					'page_generator_pro_keywords_read_csv_file',
					sprintf(
						/* translators: URL */
						__( 'Could not download %s.  Make sure the URL is publicly available.', 'page-generator-pro' ),
						$file
					)
				);
			}

			// Bail if the content type isn't text/csv, as this means the URL isn't a CSV file.
			$headers = wp_remote_retrieve_headers( $result );
			if ( strpos( $headers['content-type'], 'text/csv' ) === false ) {
				return new WP_Error(
					'page_generator_pro_keywords_read_csv_file',
					sprintf(
						/* translators: %1$s: URL, %2$s: HTTP Content-Type header */
						__( '%1$s is not a CSV file.  If it is, make sure its Content-Type header is text/csv, not "%2$s"', 'page-generator-pro' ),
						$file,
						$headers['content-type']
					)
				);
			}

			// Fetch contents.
			$contents = wp_remote_retrieve_body( $result );

			// Load contents into CSV reader.
			$csv = Reader::createFromString( $contents );
		} else {
			// Load file into CSV reader.
			$csv = Reader::createFromPath( $file );
		}

		// If CSV file is encoded in UTF16, convert to UTF8.
		if ( $csv->getInputBOM() === Reader::BOM_UTF16_LE || $csv->getInputBOM() === Reader::BOM_UTF16_BE ) {
			CharsetConverter::addTo( $csv, 'utf-16', 'utf-8' );
		}

		// Define a delimiter if none was supplied.
		if ( empty( $delimiter ) ) {
			$delimiter = ',';
		}

		// If the delimiter is longer than one character, return an error, as League\Csv will throw an exception.
		if ( strlen( $delimiter ) > 1 ) {
			return new WP_Error(
				'page_generator_pro_keywords_read_csv_file',
				__( 'The delimiter must be a single character.', 'page-generator-pro' )
			);
		}

		// Set delimiter.
		$csv->setDelimiter( $delimiter );

		// Build arrays comprising of keywords and their terms.
		$keywords_index = array();
		$keywords_terms = array();

		// Depending on where the keywords are, parse the terms.
		try {
			switch ( $csv_format ) {
				/**
				 * Import Columns into a single Keyword. Columns = Column Names
				 */
				case 'columns_single_keyword':
					// First row are columns/headers.
					$csv->setHeaderOffset( 0 );

					// Setup array.
					$keywords_terms[ $keyword_name ] = array(
						'data'      => array(),
						'columns'   => $csv->getHeader(),
						'delimiter' => $csv->getDelimiter(),
					);

					// Build array.
					foreach ( $csv as $index => $terms ) {
						// Sanitize Terms in this CSV row.
						foreach ( $terms as $column_name => $term ) {
							$terms[ $column_name ] = $this->sanitize_term( $term, $csv->getDelimiter() );
						}

						// Skip if all values in $terms array are empty.
						if ( count( array_filter( $terms ) ) === 0 ) {
							continue;
						}

						// Add to keyword array.
						$keywords_terms[ $keyword_name ]['data'][] = implode( $csv->getDelimiter(), $terms );
					}
					break;

				/**
				 * No Format, just sanitize the entire row
				 */
				default:
					$keywords_terms[ $keyword_name ] = array(
						'data'      => array(),
						'columns'   => '',
						'delimiter' => '',
					);

					foreach ( $csv as $index => $terms ) {
						foreach ( $terms as $term_index => $term ) {
							$keywords_terms[ $keyword_name ]['data'][] = $this->sanitize_term( $term, false );
						}
					}
					break;
			}
		} catch ( Exception | RuntimeException $e ) {
			return new WP_Error(
				'page_generator_pro_keywords_read_csv_file',
				sprintf(
					/* translators: Error Message */
					__( 'CSV File error: %s Please fix the data in the CSV file and try again.', 'page-generator-pro' ),
					$e->getMessage()
				)
			);
		}

		// Bail if we couldn't get any keyword terms.
		if ( empty( $keywords_terms ) ) {
			return new WP_Error( 'page_generator_pro_keywords_import_csv_file_data_no_keyword_terms', __( 'No keywords and/or terms could be found in the uploaded file.', 'page-generator-pro' ) );
		}

		// Return Keywords and their data from the CSV file.
		return $keywords_terms;

	}

	/**
	 * Reads an uploaded spreadsheet file of keyword data into an array.
	 *
	 * Supports multiple spreadsheet structures, as detailed in the $csv_format argument below.
	 *
	 * @since   1.7.3
	 *
	 * @param   string      $file           Full Path and Filename to spreadsheet.
	 * @param   string      $format         Format.
	 *                                      columns_single_keyword: Import Columns into a single Keyword. Columns = Column Names.
	 *                                      columns_multiple_keywords: Import Columns as multiple Keywords. Columns = Keyword Names.
	 *                                      rows_single_keyword: Import Rows into a single Keyword. Rows = Column Names.
	 *                                      rows_multiple_keywords: Import Rows into multiple Keywords. Rows = Keyword Names.
	 * @param   bool|string $keyword_name   Keyword Name.
	 *                                      (string) required when $format is columns_single_keyword or rows_single_keyword.
	 *                                      (bool) false when $format is columns_multiple_keywords or rows_multiple_keywords.
	 * @return  WP_Error|array
	 */
	public function read_spreadsheet_file( $file, $format = 'columns_single_keyword', $keyword_name = false ) {

		// Load file into reader.
		$spreadsheet = IOFactory::load( $file );
		$worksheet   = $spreadsheet->getActiveSheet();

		// Build arrays comprising of keywords and their terms.
		$keywords_index = array();
		$keywords_terms = array();

		// Define delimiter for resulting Terms, which is always a comma.
		$delimiter = ',';

		// Depending on where the keywords are, parse the terms.
		try {
			switch ( $format ) {
				/**
				 * Import Columns into a single Keyword. Columns = Column Names
				 */
				case 'columns_single_keyword':
					// Setup array.
					$keywords_terms[ $keyword_name ] = array(
						'data'      => array(),
						'columns'   => array(),
						'delimiter' => $delimiter,
					);

					// Build array.
					foreach ( $worksheet->getRowIterator() as $index => $row ) {
						// First row are column names.
						if ( $index === 1 ) {
							foreach ( $row->getCellIterator() as $term_index => $term ) {
								$keywords_terms[ $keyword_name ]['columns'][] = $this->sanitize_term( $term->getValue(), $delimiter );
							}
							continue;
						}

						// Sanitize Terms in this row.
						$terms = array();
						foreach ( $row->getCellIterator() as $term_index => $term ) {
							$terms[] = $this->base->get_class( 'keywords' )->sanitize_term( $term->getValue(), $delimiter );
						}

						// Add to keyword array.
						$keywords_terms[ $keyword_name ]['data'][] = implode( $delimiter, $terms );
					}
					break;

				/**
				 * Import Columns into multiple Keywords. Columns = Keyword Names
				 */
				case 'columns_multiple_keywords':
					foreach ( $worksheet->getRowIterator() as $index => $row ) {
						// First row are keywords.
						if ( $index === 1 ) {
							foreach ( $row->getCellIterator() as $term_index => $term ) {
								// Sanitize term.
								$term = $this->sanitize_term( $term->getValue() );

								// Convert Term to valid Keyword Name.
								$keyword_name = $this->sanitize_keyword_name( $term );

								// Add Keyword.
								$keywords_index[]                = $keyword_name;
								$keywords_terms[ $keyword_name ] = array(
									'data' => array(),
								);
							}
							continue;
						}

						// Sanitize Term, adding to the keywords array.
						$term_index = 0;
						foreach ( $row->getCellIterator() as $term ) {
							$keywords_terms[ $keywords_index[ $term_index ] ]['data'][] = $this->sanitize_term( $term->getValue(), $delimiter );
							++$term_index;
						}
					}
					break;

				/**
				 * Import Rows into single Keyword. Rows = Column Names
				 */
				case 'rows_single_keyword':
					$keywords_terms[ $keyword_name ] = array(
						'data'      => array(),
						'columns'   => array(),
						'delimiter' => $delimiter,
					);

					foreach ( $worksheet->getRowIterator() as $index => $row ) {
						$term_index = 0;
						foreach ( $row->getCellIterator() as $term ) {
							// Sanitize Term.
							$term = $this->sanitize_term( $term->getValue(), $delimiter );

							// First term is the keyword.
							if ( $term_index === 0 ) {
								$keyword                                      = $this->sanitize_keyword_name( $term );
								$keywords_terms[ $keyword_name ]['columns'][] = $keyword;
								++$term_index;
								continue;
							}

							// Remaining Terms are Terms.
							if ( ! isset( $keywords_terms[ $keyword_name ]['data'][ $term_index - 1 ] ) ) {
								$keywords_terms[ $keyword_name ]['data'][ $term_index - 1 ] = $term;
							} else {
								// Append Term.
								$keywords_terms[ $keyword_name ]['data'][ $term_index - 1 ] .= $delimiter . $term;
							}

							++$term_index;
						}
					}
					break;

				/**
				 * Import Rows into multiple Keywords. Rows = Keyword Names
				 */
				case 'rows_multiple_keywords':
					foreach ( $worksheet->getRowIterator() as $index => $row ) {
						$term_index = 0;
						foreach ( $row->getCellIterator() as $term ) {
							// Sanitize Term.
							$term = $this->sanitize_term( $term->getValue() );

							// First term is the keyword.
							if ( $term_index === 0 ) {
								// Convert Term to valid Keyword Name.
								$keyword_name = $this->sanitize_keyword_name( $term );

								$keywords_terms[ $keyword_name ] = array(
									'data' => array(),
								);

								++$term_index;
								continue;
							}

							// Remaining Terms are Terms.
							$keywords_terms[ $keyword_name ]['data'][] = $term;

							++$term_index;
						}
					}
					break;

				/**
				 * No Format, just sanitize the entire row
				 */
				default:
					$keywords_terms[ $keyword_name ] = array(
						'data'      => array(),
						'columns'   => '',
						'delimiter' => '',
					);

					foreach ( $worksheet->getRowIterator() as $row ) {
						foreach ( $row->getCellIterator() as $term ) {
							$keywords_terms[ $keyword_name ]['data'][] = $this->sanitize_term( $term->getValue(), false );
						}
					}
					break;
			}
		} catch ( Exception $e ) {
			return new WP_Error(
				'page_generator_pro_keywords_read_spreadsheet_file',
				sprintf(
					/* translators: Error Message */
					__( 'Spreadsheet error: %s Please fix the data in the spreadsheet and try again.', 'page-generator-pro' ),
					$e->getMessage()
				)
			);
		}

		// Bail if we couldn't get any keyword terms.
		if ( empty( $keywords_terms ) ) {
			return new WP_Error( 'page_generator_pro_keywords_import_spreadsheet_file_data_no_keyword_terms', __( 'No keywords and/or terms could be found in the uploaded file.', 'page-generator-pro' ) );
		}

		// Return Keywords and their data from the spreadsheet.
		return $keywords_terms;

	}

	/**
	 * Sanitizes the given string, returning a Keyword name compatible string
	 * which removes unsupported characters.
	 *
	 * @since   3.4.4
	 *
	 * @param   string $keyword_name   Keyword Name.
	 * @return  string                  Keyword Name
	 */
	public function sanitize_keyword_name( $keyword_name ) {

		return preg_replace( '/[\\s\'\/~`\!@#\$%\^&\*\(\)\+=\{\}\[\]\|;:"\<\>,\.\?\\\]/', '', $keyword_name );

	}

	/**
	 * Sanitizes the given string, making it compatible for use as a Keyword Term, by:
	 * - removing UTF8 BOM sequences and trimming the term
	 * - encapsulates the term with quotation marks if the delimiter is within the term
	 * - escaping backslashes
	 * - truly replacing newlines with <br>
	 * - removing double spaces
	 *
	 * @since   3.0.8
	 *
	 * @param   string      $term       Term.
	 * @param   bool|string $delimiter  Delimiter.
	 * @return  string                  Term
	 */
	public function sanitize_term( $term, $delimiter = false ) {

		// Remove UTF8-bom and trim.
		$term = $this->remove_utf8_bom( $term );

		// If this term contains quotation marks, escape them now.
		$term = str_replace( '"', '\"', $term );

		// Escape backslashes and truly replace newlines with <br>.
		// This correctly captures backslashes added above to make them \\".
		$term = addcslashes(
			preg_replace( "/\r|\n/", '', nl2br( $term ) ),
			'\\'
		);

		// Remove double spaces, but retain newlines and accented characters.
		$term = preg_replace( '/[ ]{2,}/', ' ', $term );

		// If this term contains the delimiter, encapsulate it.
		if ( $delimiter && strpos( $term, $delimiter ) !== false ) {
			$term = '"' . $term . '"';
		}

		// Return.
		return $term;

	}

	/**
	 * Removes UTF8 BOM sequences from the given string
	 *
	 * @since   2.2.1
	 *
	 * @param   string $text   Possibly UTF8 BOM encoded string.
	 * @return  string          String with UTF8 BOM sequences removed
	 */
	public function remove_utf8_bom( $text ) {

		if ( is_null( $text ) ) {
			return $text;
		}

		$text = trim( $text );
		$bom  = pack( 'H*', 'EFBBBF' );
		$text = preg_replace( "/^$bom/", '', $text );

		return trim( $text );

	}

	/**
	 * Adds or edits a record, based on the given data array.
	 *
	 * @since   1.0.0
	 *
	 * @param   array    $data           Array of data to save.
	 * @param   bool|int $id             ID (if set, edits the existing record).
	 * @param   bool     $append_terms   Whether to append terms to the existing Keyword Term data (false = replace).
	 * @return  WP_Error|int
	 */
	public function save( $data, $id = false, $append_terms = false ) {

		global $wpdb;

		// Fill missing keys with empty values to avoid DB errors.
		if ( ! isset( $data['source'] ) ) {
			$data['source'] = '';
		}
		if ( ! isset( $data['options'] ) ) {
			$data['options'] = '';
		}
		if ( ! isset( $data['columns'] ) ) {
			$data['columns'] = '';
		}
		if ( ! isset( $data['delimiter'] ) ) {
			$data['delimiter'] = '';
		}

		// Strip empty newlines from Terms.
		$data['data'] = trim( preg_replace( "/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $data['data'] ) );

		// If the data isn't UTF-8, UTF-8 encode it so it can be inserted into the DB.
		if ( function_exists( 'mb_detect_encoding' ) && ! mb_detect_encoding( $data['data'], 'UTF-8', true ) ) {
			$data['data'] = mb_convert_encoding( $data['data'], 'UTF-8', mb_list_encodings() );
		}

		// Remove spaces from column names.
		$data['columns'] = str_replace( ' ', '', $data['columns'] );

		// Validate Keyword.
		$validated = $this->validate( $data, $id );
		if ( is_wp_error( $validated ) ) {
			return $validated;
		}

		// Process options data.
		if ( is_array( $data['options'] ) ) {
			// JSON encode options, if it's an array.
			$data['options'] = wp_json_encode( $data['options'] );
		} elseif ( ! empty( $data['options'] ) ) {
			// If options is a string, decode and encode it to ensure it's a valid, escaped JSON string.
			$options = json_decode( $data['options'], true );
			if ( is_array( $options ) ) {
				$data['options'] = wp_json_encode( $options );
			}
		}

		/**
		 * Filters the Keyword data before it is saved to the database.
		 *
		 * @since   5.1.3
		 *
		 * @param   array   $data           Keyword data.
		 * @param   int     $id             Keyword ID.
		 * @param   bool    $append_terms   Whether to append terms to the existing Keyword Term data.
		 */
		$data = apply_filters( 'page_generator_pro_keywords_save', $data, $id, $append_terms );

		// If here, the Keyword can be added/edited in the database.
		// Depending on whether an ID has been defined, update or insert the keyword.
		if ( $id !== false ) {
			if ( $append_terms ) {
				// Run query.
				$result = $wpdb->query(
					$wpdb->prepare(
						"UPDATE {$wpdb->prefix}page_generator_keywords SET keyword = %s, source = %s, options = %s, delimiter = %s, columns = %s, data = concat(data, %s) WHERE keywordID = %s",
						$data['keyword'],
						$data['source'],
						$data['options'],
						$data['delimiter'],
						$data['columns'],
						addslashes( $data['data'] ),
						$id
					)
				);
			} else {
				// Editing an existing record.
				$result = $wpdb->update(
					$wpdb->prefix . $this->table,
					$data,
					array(
						$this->key => $id,
					),
					array(
						'%s',
						'%s',
						'%s',
						'%s',
						'%s',
						'%s',
					)
				);
			}

			// Check query was successful.
			if ( $result === false ) {
				return new WP_Error(
					'db_query_error',
					sprintf(
						/* translators: Database error */
						__( 'Keyword could not be updated in the database. Database error: %s', 'page-generator-pro' ),
						$wpdb->last_error
					)
				);
			}

			// Success!
			return $id;
		} else {
			// Create new record.
			$result = $wpdb->insert(
				$wpdb->prefix . $this->table,
				$data,
				array(
					'%s',
					'%s',
					'%s',
					'%s',
					'%s',
					'%s',
				)
			);

			// Check query was successful.
			if ( $result === false ) {
				return new WP_Error(
					'db_query_error',
					sprintf(
						/* translators: Database error */
						__( 'Keyword could not be added to the database. Database error: %s', 'page-generator-pro' ),
						$wpdb->last_error
					)
				);
			}

			// Get and return ID.
			return $wpdb->insert_id;
		}

	}

	/**
	 * Performs a number of validation checks on the supplied Keyword, before it is
	 * added / updated in the database
	 *
	 * @since   3.0.8
	 *
	 * @param   array    $data   Keyword.
	 * @param   bool|int $id     ID (if set, editing an existing Keyword).
	 * @return  bool|WP_Error
	 */
	private function validate( $data, $id = false ) {

		// Check for required data fields.
		if ( empty( $data['keyword'] ) ) {
			return new WP_Error( 'page_generator_pro_keywords_save_validation_error', __( 'Please complete the keyword field.', 'page-generator-pro' ) );
		}

		// Check keyword name doesn't already exist as another keyword.
		if ( $this->exists( $data['keyword'], $id ) ) {
			return new WP_Error(
				'page_generator_pro_keywords_save_validation_error',
				sprintf(
					/* translators: Keyword Name */
					__( 'The Keyword "%s" already exists. Please choose a different name.', 'page-generator-pro' ),
					$data['keyword']
				)
			);
		}

		// Check that the keyword does not contain spaces.
		if ( preg_match( '/[\\s\'\/~`\!@#\$%\^&\*\(\)\+=\{\}\[\]\|;:"\<\>,\.\?\\\]/', $data['keyword'] ) ) {
			return new WP_Error( 'page_generator_pro_keywords_save_validation_error', __( 'The Keyword field can only contain letters, numbers, hyphens and underscores.', 'page-generator-pro' ) );
		}

		// Column name checks.
		if ( ! empty( $data['columns'] ) ) {
			// Check column names don't contain invalid characters.
			if ( preg_match( '/[\\s\'\/~`\!@#\$%\^&\*\(\)\+=\{\}\[\]\|;:"\<\>\.\?\\\]/', $data['columns'] ) ) {
				return new WP_Error( 'page_generator_pro_keywords_save_validation_error', __( 'The Columns field can only contain letters, numbers, commas, hyphens and underscores.', 'page-generator-pro' ) );
			}

			// Check a delimiter exists.
			if ( empty( $data['delimiter'] ) ) {
				return new WP_Error(
					'page_generator_pro_keywords_save_validation_error',
					__( 'Delimiter Field: When specifying column names in the Columns Field, a delimiter must also be specified.', 'page-generator-pro' )
				);
			}

			// Check the delimiter does not exceed a single character.
			if ( strlen( $data['delimiter'] ) > 1 ) {
				return new WP_Error(
					'page_generator_pro_keywords_save_validation_error',
					__( 'The Delimiter field must be a single character.', 'page-generator-pro' )
				);
			}
		}

		// If a delimiter is supplied, perform some further validation checks.
		if ( ! empty( $data['delimiter'] ) ) {
			// Check the delimiter isn't a pipe symbol, curly brace or bracket.
			foreach ( $this->get_invalid_delimiters() as $invalid_delimiter ) {
				if ( strpos( $data['delimiter'], $invalid_delimiter ) !== false ) {
					return new WP_Error(
						'page_generator_pro_keywords_save_validation_error',
						sprintf(
							/* translators: delimiter character */
							__( 'Delimiter Field: %s cannot be used as a delimiter, as it may conflict with Keyword and Spintax syntax', 'page-generator-pro' ),
							'<code>' . $data['delimiter'] . '</code>'
						)
					);
				}
			}

			// Check that column names are specified.
			if ( empty( $data['columns'] ) ) {
				return new WP_Error(
					'page_generator_pro_keywords_save_validation_error',
					__( 'Columns Field: Two or more column names must be specified in the Columns Field When specifying a delimiter.', 'page-generator-pro' )
				);
			}

			// Check that there is a comma in the column names for separating columns.
			if ( strpos( $data['columns'], ',' ) === false ) {
				return new WP_Error(
					'page_generator_pro_keywords_save_validation_error',
					__( 'Columns Field: The values specified in the Columns Field must be separated by a comma.', 'page-generator-pro' )
				);
			}
		}

		// If here, basic validation has passed.
		$result = true;

		/**
		 * Runs validation tests specific to this source for a Keyword immediately before it's saved to the database.
		 *
		 * @since   3.0.9
		 *
		 * @param   bool    $result     Validation Result.
		 * @param   array   $data       Keyword.
		 * @param   int     $id         ID (if set, editing an existing Keyword).
		 * @return  WP_Error|bool
		 */
		$result = apply_filters( 'page_generator_pro_keywords_validate_' . $data['source'], $result, $data, $id );

		// Return result, which will be WP_Error or true.
		return $result;

	}

	/**
	 * Deletes the record for the given primary key ID
	 *
	 * @since   1.0.0
	 *
	 * @param   int|array $data   Single ID or array of IDs.
	 * @return  WP_Error|bool           Success
	 */
	public function delete( $data ) {

		global $wpdb;

		if ( is_array( $data ) ) {
			foreach ( $data as $keyword_id ) {
				// Delete Keyword.
				$result = $wpdb->delete(
					$wpdb->prefix . $this->table,
					array(
						'keywordID' => $keyword_id,
					)
				);

				// Check query was successful.
				if ( $result === false ) {
					return new WP_Error(
						'db_query_error',
						sprintf(
							/* translators: Database error */
							__( 'Record(s) could not be deleted from the database. Database error: %s', 'page-generator-pro' ),
							$wpdb->last_error
						)
					);
				}
			}
		} else {
			// Delete Keyword.
			$result = $wpdb->delete(
				$wpdb->prefix . $this->table,
				array(
					'keywordID' => $data,
				)
			);

			// Check query was successful.
			if ( $result === false ) {
				return new WP_Error(
					'db_query_error',
					sprintf(
						/* translators: Database error */
						__( 'Record(s) could not be deleted from the database. Database error: %s', 'page-generator-pro' ),
						$wpdb->last_error
					)
				);
			}
		}

		return true;

	}

	/**
	 * Duplicates the given ID to a new row
	 *
	 * @since   1.7.8
	 *
	 * @param   int $id     Keyword ID.
	 * @return  WP_Error|int
	 */
	public function duplicate( $id ) {

		// Fetch keyword.
		$keyword = $this->get_by_id( $id );

		// Bail if no keyword was found.
		if ( ! $keyword ) {
			return new WP_Error( 'page_generator_pro_keywords_duplicate', __( 'Keyword could not be found for duplication.', 'page-generator-pro' ) );
		}

		// Delete some keys from the data.
		unset( $keyword['keywordID'], $keyword['dataArr'], $keyword['columnsArr'] );

		// Rename the keyword.
		$keyword['keyword'] .= '_copy';

		// Save the keyword as a new keyword.
		$result = $this->save( $keyword );

		// Return the result (WP_Error | int).
		return $result;

	}

	/**
	 * Exports the given ID's Terms to a CSV file
	 *
	 * @since   2.9.0
	 *
	 * @param   int $id     Keyword ID.
	 * @return  WP_Error
	 */
	public function export_csv( $id ) {

		// Fetch keyword.
		$keyword = $this->get_by_id( $id );

		// Bail if no keyword was found.
		if ( ! $keyword ) {
			return new WP_Error( 'page_generator_pro_keywords_duplicate', __( 'Keyword could not be found for exporting.', 'page-generator-pro' ) );
		}

		// If Keyword has columns and delimiter, create CSV Reader object and get its text output for the file contents.
		if ( ! empty( $keyword['delimiter'] ) ) {
			$keyword['columns'] = str_replace( ',', $keyword['delimiter'], $keyword['columns'] );
			$reader             = \League\Csv\Reader::createFromString( $keyword['columns'] . "\n" . $keyword['data'] );
			$reader->setDelimiter( $keyword['delimiter'] );
			$reader->setHeaderOffset( 0 );

			// Force browser download of CSV file.
			$this->base->dashboard->force_csv_file_download( $reader->getContent(), sanitize_title( $keyword['keyword'] ) );
		}

		// Keyword is a simple Term list.
		// Force browser download of CSV file.
		$this->base->dashboard->force_csv_file_download( $keyword['data'], sanitize_title( $keyword['keyword'] ) );
		die();

	}

	/**
	 * Outputs a <select> dropdown comprising of Keywords, including any
	 * Keyword with Column combinations.
	 *
	 * @since   1.9.7
	 *
	 * @param   array  $keywords   Keywords.
	 * @param   string $element    HTML Element ID to insert Keyword into when selected in dropdown.
	 */
	public function output_dropdown( $keywords, $element ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter

		// Load view.
		include $this->base->plugin->folder . 'views/admin/keywords-dropdown.php';

	}

	/**
	 * Returns an array of delimiters that cannot be used with Keywords, as using
	 * them would result in errors with processing Keywords
	 *
	 * @since   unknown
	 *
	 * @return  array
	 */
	private function get_invalid_delimiters() {

		return array(
			'|',
			'{',
			'}',
			'(',
			')',
			':',
		);

	}

}
