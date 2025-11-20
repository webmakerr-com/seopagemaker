<?php
/**
 * RSS Feed URL Keyword Source Class
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Registers a RSS Feed URL as a Keyword source, enabling RSS data to be used
 * for a Keyword.
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 3.3.5
 */
class Page_Generator_Pro_Keywords_Source_RSS {

	/**
	 * Holds the base object.
	 *
	 * @since   3.3.5
	 *
	 * @var     object
	 */
	public $base;

	/**
	 * Constructor
	 *
	 * @since   3.3.5
	 *
	 * @param   object $base    Base Plugin Class.
	 */
	public function __construct( $base ) {

		// Store base class.
		$this->base = $base;

		// Register this Keyword Source.
		add_filter( 'page_generator_pro_keywords_register_sources', array( $this, 'register' ) );

		// Define parameters for the Keyword before saving.
		add_filter( 'page_generator_pro_keywords_save_rss', array( $this, 'save' ) );

		// Refresh Keyword Terms before starting generation.
		add_filter( 'page_generator_pro_keywords_refresh_terms_rss', array( $this, 'refresh_terms' ), 10, 2 );

	}

	/**
	 * Returns the programmatic name of the source
	 *
	 * @since   3.3.5
	 *
	 * @return  string
	 */
	public function get_name() {

		return 'rss';

	}

	/**
	 * Returns the label of the source
	 *
	 * @since   3.3.5
	 *
	 * @return  string
	 */
	public function get_label() {

		return __( 'RSS Feed', 'page-generator-pro' );

	}

	/**
	 * Registers this Source with the Keyword Sources system, so it's available
	 * to Keywords
	 *
	 * @since   3.3.5
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
						'url'     => array(
							'type'        => 'url',
							'label'       => __( 'RSS Feed', 'page-generator-pro' ),
							'file_type'   => 'text/csv',
							'description' => __( 'The RSS Feed URL to use as the Terms for this Keyword', 'page-generator-pro' ),
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
	 * @since   3.3.5
	 *
	 * @param   array $keyword        Keyword Parameters.
	 * @return  WP_Error|array
	 */
	public function save( $keyword ) {

		// Bail if no URL was specified.
		if ( empty( $keyword['options']['url'] ) ) {
			return new WP_Error(
				'page_generator_pro_keywords_source_rss',
				__( 'No RSS Feed was specified in the Keyword.', 'page-generator-pro' )
			);
		}

		// Bail if an invalid URL was specified.
		if ( ! filter_var( $keyword['options']['url'], FILTER_VALIDATE_URL ) ) {
			return new WP_Error(
				'page_generator_pro_keywords_source_rss',
				__( 'The RSS Feed field must be a valid URL pointing to an RSS Feed.', 'page-generator-pro' )
			);
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
				'delimiter' => $keywords_terms['delimiter'],
				'columns'   => implode( ',', $keywords_terms['columns'] ),
				'data'      => implode( "\n", $keywords_terms['data'] ),
			)
		);

		return $keyword;

	}

	/**
	 * Refresh the given Keyword's Columns Terms by fetching them from the RSS Feed
	 * immediately before starting generation.
	 *
	 * @since   3.3.5
	 *
	 * @param   string $terms      Terms.
	 * @param   array  $keyword    Keyword.
	 * @return  WP_Error|array     WP_Error | array (delimiter,columns,data)
	 */
	public function refresh_terms( $terms, $keyword ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter

		return $this->get( $keyword );

	}

	/**
	 * Fetches Terms from the RSS Feed, based on the Keyword settings
	 *
	 * @since   3.3.5
	 *
	 * @param   array $keyword    Keyword.
	 * @return  WP_Error|array
	 */
	private function get( $keyword ) {

		// Get feed.
		$feed = fetch_feed( $keyword['options']['url'] );

		// Bail if an error occured.
		if ( is_wp_error( $feed ) ) {
			return $feed;
		}

		// Build array of Keyword Terms.
		$terms = array();
		foreach ( $feed->get_items() as $item ) {
			// Implode some item attributes, such as categories, authors and contributors.
			$categories = array();
			if ( ! is_null( $item->get_categories() ) ) {
				foreach ( $item->get_categories() as $category ) {
					$categories[] = $category->get_term();
				}
			}

			$authors = array();
			if ( ! is_null( $item->get_authors() ) ) {
				foreach ( $item->get_authors() as $author ) {
					$authors[] = $author->get_name();
				}
			}

			$contributors = array();
			if ( ! is_null( $item->get_contributors() ) ) {
				foreach ( $item->get_contributors() as $contributor ) {
					$contributors[] = $contributor->get_name();
				}
			}

			// Build data.
			$data = array(
				addslashes( $this->strip_newlines_and_tabs( $item->get_title() ) ),
				addslashes( $this->strip_newlines_and_tabs( $item->get_content() ) ),
				addslashes( $this->strip_newlines_and_tabs( $item->get_permalink() ) ),
				addslashes( $this->strip_newlines_and_tabs( $item->get_description() ) ),
				addslashes( implode( ', ', $categories ) ),
				addslashes( implode( ', ', $authors ) ),
				addslashes( implode( ', ', $contributors ) ),
				( ! is_null( $item->get_copyright() ) ? addslashes( $item->get_copyright() ) : '' ), // @phpstan-ignore-line
				( ! is_null( $item->get_date() ) ? addslashes( $item->get_date() ) : '' ),
				( ! is_null( $item->get_updated_date() ) ? addslashes( $item->get_updated_date() ) : '' ),
				( ! is_null( $item->get_latitude() ) ? addslashes( $item->get_latitude() ) : '' ),
				( ! is_null( $item->get_longitude() ) ? addslashes( $item->get_longitude() ) : '' ),
				( ! is_null( $item->get_source() ) ? addslashes( $item->get_source() ) : '' ),
			);

			// For RSS containing HTML, addslashes() here is also required. No idea why, but it works.
			$terms[] = addslashes( '"' . implode( '","', $data ) . '"' );
		}

		// Return.
		return array(
			'delimiter' => ',',
			'columns'   => array(
				'title',
				'content',
				'permalink',
				'description',
				'categories',
				'authors',
				'contributors',
				'copyright',
				'date',
				'updated_date',
				'latitude',
				'longitude',
				'source',
			),
			'data'      => $terms,
		);

	}

	/**
	 * Strips newlines and tabs from the given string.
	 *
	 * Required for RSS feeds containing HTML to work in the Keyword system.
	 *
	 * @since   4.4.0
	 *
	 * @param   string $item   RSS Feed Item.
	 * @return  string
	 */
	private function strip_newlines_and_tabs( $item ) {

		return str_replace(
			"\n",
			'',
			str_replace(
				"\t",
				'',
				$item
			)
		);

	}

}
