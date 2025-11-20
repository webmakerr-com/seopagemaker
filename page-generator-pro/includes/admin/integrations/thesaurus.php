<?php
/**
 * Thesaurus API Class.
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Generate Terms for a Keyword using thesaurus.com
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 2.6.3
 */
class Page_Generator_Pro_Thesaurus extends Page_Generator_Pro_API {

	/**
	 * Holds the API endpoint
	 *
	 * @since   2.6.3
	 *
	 * @var     string
	 */
	public $api_endpoint = 'https://www.thesaurus.com';

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
	 * Holds the flag determining if the response data should be an encoded
	 * JSON string
	 *
	 * If true, the body response data is JSON decoded and returned as an array
	 *
	 * If false, the body response data is returned
	 *
	 * @since   2.8.9
	 *
	 * @var     bool
	 */
	public $is_json_response = false;

	/**
	 * Returns synonyms for the given keyword
	 *
	 * @since   2.6.3
	 *
	 * @param   string $keyword    Keyword.
	 * @return  WP_Error|array
	 */
	public function get_synonyms( $keyword ) {

		// Run the query.
		$html = $this->get( 'browse/' . $keyword );

		// Bail if an error occured.
		if ( is_wp_error( $html ) ) {
			return $html;
		}

		// Load HTML into DOMDocument.
		libxml_use_internal_errors( true );
		$dom = new DOMDocument();
		$dom->loadHTML( $html );
		libxml_use_internal_errors( false );

		// Load DOMDocument into DOMXpath.
		$xpath = new DOMXPath( $dom );

		// Find synonyms.
		$synonyms = $xpath->query( '//div[contains(@class, "etbu2a30")]' );

		// Bail if no synonyms found.
		if ( ! $synonyms->count() ) {
			return new WP_Error(
				'page_generator_pro_thesaurus_get_synonyms_no_results',
				sprintf(
					/* translators: Keyword */
					__( 'No synonyms found for the Keyword %s', 'page-generator-pro' ),
					$keyword
				)
			);
		}

		// Build an array of results.
		$results = array();
		foreach ( $synonyms as $synonym ) {
			$results[] = trim( (string) $synonym->textContent ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		}

		// Filter results, removing blank values and duplicates.
		$results = array_values( array_filter( array_unique( $results ) ) );

		// Bail if no results found from synonyms.
		if ( ! count( $results ) ) {
			return new WP_Error(
				'page_generator_pro_thesaurus_get_synonyms_no_results',
				sprintf(
					/* translators: Keyword */
					__( 'No synonyms found for the Keyword %s', 'page-generator-pro' ),
					$keyword
				)
			);
		}

		// Return synonyms.
		return $results;

	}

}
