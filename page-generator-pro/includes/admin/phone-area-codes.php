<?php
/**
 * Phone Area Codes Class
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Generates Phone Area Codes as a Keyword.
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 1.1.7
 */
class Page_Generator_Pro_Phone_Area_Codes {

	/**
	 * Holds the base object.
	 *
	 * @since   1.9.8
	 *
	 * @var     object
	 */
	public $base;

	/**
	 * Primary SQL Table
	 *
	 * @since   1.5.8
	 *
	 * @var     string
	 */
	public $table = 'page_generator_area_codes';

	/**
	 * Primary SQL Table Primary Key
	 *
	 * @since   1.5.8
	 *
	 * @var     string
	 */
	public $key = 'id';

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
	 * @since   1.5.8
	 *
	 * @global  $wpdb   WordPress DB Object.
	 */
	public function activate() {

		global $wpdb;

		// Enable error output if WP_DEBUG is enabled.
		$wpdb->show_errors = true;

		// Create database tables.
		$wpdb->query(
			"CREATE TABLE IF NOT EXISTS {$wpdb->prefix}page_generator_area_codes (
        	 `id` int(10) NOT NULL AUTO_INCREMENT,
            `country` varchar(191) NOT NULL,
            `country_code` int(3) NOT NULL,
            `city` varchar(200) NOT NULL,
            `area_code` varchar(200) NOT NULL, 
            PRIMARY KEY `id` (`id`),
            KEY `country` (`country`)
        ) {$wpdb->get_charset_collate()} AUTO_INCREMENT=1"
		);

		// If the table has data, we've already inserted the data.
		$count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}page_generator_area_codes" );
		if ( $count > 0 ) {
			return true;
		}

		// Download CSV.
		// Don't use HTTPS as that might result in SSL cert errors.
		$data = @file_get_contents( 'http://www.aggdata.com/download_sample.php?file=globalareacodes.csv' ); // phpcs:ignore WordPress.WP.AlternativeFunctions,WordPress.PHP.NoSilencedErrors.Discouraged

		// If no data, bail.
		if ( $data === false ) {
			return new WP_Error(
				'page_generator_pro_phone_area_codes_activate_error',
				__( 'Could not download Phone Area Codes.  Please reload the page to try again.', 'page-generator-pro' )
			);
		}

		// Iterate through rows, to build MySQL query.
		$query = array();
		$rows  = explode( "\n", $data );
		foreach ( $rows as $index => $row ) {
			// Skip column names.
			if ( $index === 0 ) {
				continue;
			}

			// Explode into array.
			$row = explode( ',', str_replace( '"', '', $row ) );

			// Add to query.
			$query[] = '("' . ( isset( $row[0] ) ? $row[0] : '' ) . '", "' . ( isset( $row[1] ) ? $row[1] : '' ) . '", "' . ( isset( $row[2] ) ? $row[2] : '' ) . '", "' . ( isset( $row[3] ) ? $row[3] : '' ) . '")';
		}

		// Convert to MySQL query.
		$query = implode( ',', $query );

		// Run query.
		$wpdb->query( "INSERT INTO {$wpdb->prefix}page_generator_area_codes (country, country_code, city, area_code) VALUES {$query}" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		return true;

	}

	/**
	 * Returns an array of supported countries for generating phone area codes
	 *
	 * @since   1.5.9
	 *
	 * @return  bool|array   Countries
	 */
	public function get_phone_area_code_countries() {

		global $wpdb;

		// Get records.
		$results = $wpdb->get_results( "SELECT DISTINCT country FROM {$wpdb->prefix}page_generator_area_codes", ARRAY_A );

		// Check a record was found    .
		if ( ! $results ) {
			return false;
		}
		if ( count( $results ) === 0 ) {
			return false;
		}

		// Build array.
		$countries = array();
		foreach ( $results as $result ) {
			// Skip blank results.
			if ( empty( $result['country'] ) ) {
				continue;
			}

			// Add to countries array.
			$countries[ $result['country'] ] = $result['country'];
		}

		// Build unique array.
		$countries = array_unique( $countries );

		/**
		 * Filters the supported countries for generating phone area codes.
		 *
		 * @since   1.5.9
		 *
		 * @param   array   $countries  Countries.
		 */
		$countries = apply_filters( 'page_generator_pro_geo_get_phone_area_code_countries', $countries );

		// Return filtered results.
		return $countries;

	}

	/**
	 * Returns area dialling codes for the given country
	 *
	 * @since   1.5.9
	 *
	 * @param   string $country    Country.
	 * @return  bool|array         Phone Data
	 */
	public function get_phone_area_codes( $country ) {

		global $wpdb;

		// Get records.
		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT area_code, city, country_code FROM {$wpdb->prefix}page_generator_area_codes WHERE country = %s",
				$country
			),
			ARRAY_A
		);

		// Check a record was found   .
		if ( ! $results ) {
			return false;
		}
		if ( count( $results ) === 0 ) {
			return false;
		}

		// Build array.
		$area_codes = array();
		foreach ( $results as $result ) {
			// Skip blank results.
			if ( empty( $result['area_code'] ) ) {
				continue;
			}

			// Add to area codes array.
			$area_codes[ $result['area_code'] ] = $result;
		}

		/**
		 * Filters the supported countries for generating phone area codes.
		 *
		 * @since   1.5.9
		 *
		 * @param   array   $countries  Countries.
		 */
		$area_codes = apply_filters( 'page_generator_pro_geo_get_phone_area_codes', $area_codes );

		// Return filtered results.
		return $area_codes;

	}

}
