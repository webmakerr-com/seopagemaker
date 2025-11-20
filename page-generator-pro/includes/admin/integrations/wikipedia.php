<?php
/**
 * Wikipedia API Class.
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Fetch content from Wikipedia
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 2.2.7
 */
class Page_Generator_Pro_Wikipedia {

	use Page_Generator_Pro_Integration_Trait;
	use Page_Generator_Pro_Shortcode_Trait;

	/**
	 * Holds the base object.
	 *
	 * @since   2.2.7
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
	public $name = 'wikipedia';

	/**
	 * Holds fetched Wikipedia articles in single request cycle
	 *
	 * @since   2.2.7
	 *
	 * @var     array
	 */
	private $page_cache = array();

	/**
	 * Holds the URL used to fetch content from
	 *
	 * @since   2.7.7
	 *
	 * @var     string
	 */
	private $url = '';

	/**
	 * Constructor
	 *
	 * @since   4.8.0
	 *
	 * @param   object $base    Base Plugin Class.
	 */
	public function __construct( $base ) {

		// Store base class.
		$this->base = $base;

		// Register as a Dynamic Element.
		add_filter( 'page_generator_pro_shortcode_add_shortcodes', array( $this, 'add_shortcode' ) );

	}

	/**
	 * Returns this shortcode / block's programmatic name.
	 *
	 * @since   2.5.1
	 */
	public function get_name() {

		return 'wikipedia';

	}

	/**
	 * Returns this shortcode / block's title.
	 *
	 * @since   4.5.2
	 *
	 * @return  string
	 */
	public function get_title() {

		return __( 'Wikipedia', 'page-generator-pro' );

	}

	/**
	 * Returns this shortcode / block's description.
	 *
	 * @since   4.5.2
	 *
	 * @return string
	 */
	public function get_description() {

		return __( 'Displays content from Wikipedia based on the given Term(s).', 'page-generator-pro' );

	}

	/**
	 * Returns this shortcode / block's Keywords, excluding the title.
	 *
	 * @since   4.5.2
	 *
	 * @return  array
	 */
	public function get_keywords() {

		return array(
			__( 'Wiki', 'page-generator-pro' ),
		);

	}

	/**
	 * Returns this shortcode / block's icon.
	 *
	 * @since   4.5.2
	 *
	 * @return  string
	 */
	public function get_icon() {

		return '_modules/dashboard/feather/wikipedia.svg';

	}

	/**
	 * Returns this block's Attributes and default values for Gutenberg.
	 *
	 * @since   3.6.3
	 */
	public function get_provider_attributes() {

		return array(
			'term'             => array(
				'type'      => 'array',
				'delimiter' => ';',
			),
			'use_similar_page' => array(
				'type'    => 'boolean',
				'default' => $this->get_default_value( 'use_similar_page' ),
			),
			'language'         => array(
				'type'    => 'string',
				'default' => ( ! $this->get_default_value( 'language' ) ? '' : $this->get_default_value( 'language' ) ),
			),
			'sections'         => array(
				'type'      => 'array',
				'delimiter' => ';',
			),
			'elements'         => array(
				'type'      => 'array',
				'delimiter' => ',',
				'default'   => $this->get_default_value( 'elements' ),
			),
			'remove_links'     => array(
				'type'    => 'remove_links',
				'default' => $this->get_default_value( 'remove_links' ),
			),
			'paragraphs'       => array(
				'type'    => 'number',
				'default' => $this->get_default_value( 'paragraphs' ),
			),
			'source_link'      => array(
				'type'    => 'source_link',
				'default' => $this->get_default_value( 'source_link' ),
			),
			'source_link_text' => array(
				'type'    => 'string',
				'default' => ( ! $this->get_default_value( 'source_link_text' ) ? '' : $this->get_default_value( 'source_link_text' ) ),
			),
			'apply_synonyms'   => array(
				'type'    => 'toggle',
				'default' => $this->get_default_value( 'apply_synonyms' ),
			),
		);

	}

	/**
	 * Returns this shortcode / block's Fields
	 *
	 * @since   2.5.1
	 */
	public function get_provider_fields() {

		return array(
			'term'             => array(
				'label'       => __( 'Term(s) / URL(s)', 'page-generator-pro' ),
				'type'        => 'text_multiple',
				'data'        => array(
					'delimiter' => ';',
				),
				'class'       => 'wpzinc-selectize-freeform',
				'description' => __( 'Specify one or more terms or Wikipedia URLs to search for on Wikipedia, in order. Contents will be used from the first term / URL that produces a matching Wikipedia Page', 'page-generator-pro' ),
			),
			'use_similar_page' => array(
				'label'         => __( 'Use Similar Page', 'page-generator-pro' ),
				'type'          => 'toggle',
				'default_value' => $this->get_default_value( 'use_similar_page' ),
				'description'   => __( 'If enabled, a similar Wikipedia Article will be used where a Term specified above could not be found, and Wikipedia provides alternate Articles when viewing said Term. Refer to the Documentation for more information.', 'page-generator-pro' ),
			),
			'language'         => array(
				'label'         => __( 'Language', 'page-generator-pro' ),
				'type'          => 'select',
				'values'        => $this->get_languages(),
				'default_value' => $this->get_default_value( 'language' ),
			),
			'sections'         => array(
				'label'       => __( 'Sections', 'page-generator-pro' ),
				'type'        => 'text_multiple',
				'data'        => array(
					'delimiter' => ';',
				),
				'class'       => 'wpzinc-selectize-freeform',
				'description' => __( 'Optional; specify one or more Wikipedia top level Table of Content sections to pull content from.  If no sections are specified, the summary (text before the Table of Contents) will be used.', 'page-generator-pro' ),
			),

			'elements'         => array(
				'label'         => __( 'Elements', 'page-generator-pro' ),
				'type'          => 'select_multiple',
				'default_value' => $this->get_default_value( 'elements' ),
				'values'        => $this->get_supported_elements(),
				'class'         => 'wpzinc-selectize-drag-drop',
				'description'   => __( 'Specify the HTML elements to return from the Wikipedia Article. If no elements are specified, paragraphs will be returned', 'page-generator-pro' ),
			),
			'remove_links'     => array(
				'label'         => __( 'Remove Links?', 'page-generator-pro' ),
				'type'          => 'toggle',
				'default_value' => $this->get_default_value( 'remove_links' ),
				'description'   => __( 'If enabled, any links found in the Wikipedia Article will be removed.', 'page-generator-pro' ),
			),
			'paragraphs'       => array(
				'label'         => __( 'Limit', 'page-generator-pro' ),
				'type'          => 'number',
				'min'           => 0,
				'max'           => 999,
				'step'          => 1,
				'default_value' => $this->get_default_value( 'paragraphs' ),
				'description'   => __( 'The maximum number of elements to output after all above sections have been fetched and combined.', 'page-generator-pro' ),
			),
			'source_link'      => array(
				'label'         => __( 'Output Source Link?', 'page-generator-pro' ),
				'type'          => 'toggle',
				'default_value' => $this->get_default_value( 'source_link' ),
				'description'   => __( 'If enabled, a nofollow link to the Wikipedia article will be placed at the end of the content.', 'page-generator-pro' ),
			),
			'source_link_text' => array(
				'label'         => __( 'Source Link Text', 'page-generator-pro' ),
				'type'          => 'text',
				'default_value' => $this->get_default_value( 'source_link_text' ),
				'description'   => __( 'The text to display which links to the Wikipedia Source Link, if enabled.', 'page-generator-pro' ),
			),
			'apply_synonyms'   => array(
				'label'         => __( 'Spin?', 'page-generator-pro' ),
				'type'          => 'toggle',
				'default_value' => $this->get_default_value( 'apply_synonyms' ),
				'description'   => __( 'If enabled, the Wikipedia content will be spun to produce a unique variation.', 'page-generator-pro' ),
			),
		);

	}

	/**
	 * Returns this shortcode / block's UI Tabs
	 *
	 * @since   2.5.1
	 */
	public function get_provider_tabs() {

		return array(
			'general' => array(
				'label'  => __( 'Search Parameters', 'page-generator-pro' ),
				'class'  => 'search',
				'fields' => array(
					'term',
					'use_similar_page',
					'language',
				),
			),

			'output'  => array(
				'label'       => __( 'Output', 'page-generator-pro' ),
				'class'       => 'wikipedia',
				'description' => __( 'Defines the output of Wikipedia Content.', 'page-generator-pro' ),
				'fields'      => array(
					'sections',
					'elements',
					'remove_links',
					'paragraphs',
					'source_link',
					'source_link_text',
					'apply_synonyms',
				),
			),
		);

	}

	/**
	 * Returns this shortcode / block's Default Values
	 *
	 * @since   2.5.1
	 */
	public function get_provider_default_values() {

		return array(
			'term'             => array(),
			'use_similar_page' => false,
			'language'         => 'en',

			'sections'         => array(),
			'elements'         => array( 'paragraphs' ),
			'remove_links'     => 1,               // Removes <a> links.
			'paragraphs'       => 0,               // Number of elements.
			'source_link'      => 0,
			'source_link_text' => __( 'Source', 'page-generator-pro' ),
			'apply_synonyms'   => 0,
		);

	}

	/**
	 * Returns this shortcode / block's output
	 *
	 * @since   2.5.1
	 *
	 * @param  array $atts   Shortcode Attributes.
	 * @return string          Output
	 */
	public function render( $atts ) {

		// Parse attributes.
		$atts = $this->parse_atts( $atts );

		// Iterate through terms until we find a page.
		$errors = array();
		foreach ( $atts['term'] as $term ) {
			// Skip empty term.
			if ( empty( $term ) ) {
				continue;
			}

			// Get elements from Wikipedia Page.
			$elements = $this->get_page_sections(
				$term,
				$atts['use_similar_page'],
				$atts['sections'],
				$atts['language'],
				$atts['elements'],
				$atts['remove_links']
			);

			// Collect errors.
			if ( is_wp_error( $elements ) ) {
				$errors[] = sprintf(
					/* translators: %1$s: Search Term, %2$s: Error message */
					__( 'Term: "%1$s": %2$s', 'page-generator-pro' ),
					$term,
					$elements->get_error_message()
				);
				continue;
			}

			// If here, we managed to fetch elements.
			// Unset errors and break the loop.
			unset( $errors );
			break;
		}

		// If errors exist, bail.
		if ( isset( $errors ) && count( $errors ) > 0 ) {
			return $this->add_dynamic_element_error_and_return(
				new WP_Error(
					'page_generator_pro_wikipedia_error',
					implode( '<br />', $errors )
				),
				$atts
			);
		}

		// If no content elements exist, no Terms were specified.
		if ( ! isset( $elements ) ) {
			return $this->add_dynamic_element_error_and_return(
				new WP_Error(
					'page_generator_pro_wikipedia_error',
					__( 'Wikipedia: No Terms were specified, or Terms are Keywords which evaluate to blank Terms.', 'page-generator-pro' )
				),
				$atts
			);
		}

		// If a paragraph limit has been specified, apply it now.
		if ( isset( $atts['paragraphs'] ) && is_numeric( $atts['paragraphs'] ) && $atts['paragraphs'] > 0 ) {
			$elements = array_slice( $elements, 0, absint( $atts['paragraphs'] ) );
		}

		// Convert array of content elements into string.
		$content = implode( '', $elements );

		// Apply synonyms for spintax, if enabled.
		if ( $atts['apply_synonyms'] ) {
			$result = $this->base->get_class( 'spintax' )->add_spintax( $content );

			// Only assign the spintax to the content, and process it, if there was no error.
			if ( ! is_wp_error( $result ) ) {
				$content = $this->base->get_class( 'spintax' )->process( $result );
			}
		}

		// Add Source Link, if required.
		if ( $atts['source_link'] ) {
			$source_link = $this->get_url();
			if ( ! empty( $source_link ) ) {
				$content .= '<small><a href="' . $source_link . '" target="_blank" rel="nofollow noopener">' . $atts['source_link_text'] . '</a></small>';
			}
		}

		// Build HTML.
		$html = '<div class="' . $this->base->plugin->name . '-wikipedia">' . $content . '</div>';

		/**
		 * Filter the Wikipedia Shortcode HTML output, before returning.
		 *
		 * @since   1.0.0
		 *
		 * @param   string  $html       HTML Output.
		 * @param   array   $atts       Shortcode Attributes.
		 * @param   string  $build      Wikipedia Content.
		 * @param   array   $elements   Wikipedia Elements in Wikipedia Article based on $atts.
		 */
		$html = apply_filters( 'page_generator_pro_shortcode_wikipedia', $html, $atts, $content, $elements );

		// Return.
		return $html;

	}

	/**
	 * Returns the URL that Wikipedia Content was successfully fetched from
	 *
	 * @since   2.7.7
	 *
	 * @return  string  Wikipedia URL
	 */
	private function get_url() {

		return $this->url;

	}

	/**
	 * Main function to fetch HTML from the given Term's Wikipedia Page
	 *
	 * @since   2.2.7
	 *
	 * @param   string       $term                   Term / URL.
	 * @param   bool         $use_similar_page       Use a similar Page if the Term's Page cannot be found.
	 * @param   bool|array   $sections               Section(s) to fetch.
	 * @param   string       $language               Language.
	 * @param   string|array $elements               Element (string) or Elements (array).
	 * @param   bool         $remove_links           Remove Links.
	 * @return  WP_Error|array
	 */
	private function get_page_sections( $term, $use_similar_page = false, $sections = false, $language = 'en', $elements = 'paragraphs', $remove_links = true ) {

		// Sanitize term.
		$term = $this->sanitize_term( $term );

		// If the page doesn't exist in cache, fetch it now.
		if ( ! isset( $this->page_cache[ $term . '-' . $language ] ) ) {
			// Get Wikipedia Page.
			$page = $this->get_page( $term, $language );

			// Bail if an error occured.
			if ( is_wp_error( $page ) ) {
				return $page;
			}

			// If the term is ambiguous, and could refer to one of several articles on Wikipedia, either fetch
			// that article or bail, depending on the $use_similar_page setting.
			if ( $this->is_disambiguation_page( $term, $language ) ) {
				if ( ! $use_similar_page ) {
					return new WP_Error(
						'page_generator_pro_get_page_sections_ambiguous_term',
						sprintf(
							/* translators: Search Term */
							__( '"%s" is ambiguous and could relate to one of several articles available on Wikipedia.  To use one of these similar articles, enable the "Use Similar Page" option in the Wikipedia Dynamic Element.', 'page-generator-pro' ),
							$term
						)
					);
				}

				// Get a link from the disambiguation page that has a full Wikipedia Page.
				$link = $this->get_similar_link( $page, $term, $language );

				// Bail if an error occured.
				if ( is_wp_error( $link ) ) {
					return $link;
				}

				// Sanitize link to a term.
				$term = $this->sanitize_term( $link );

				// Get entire similar Page.
				$page = $this->get_page( $term, $language );

				// Bail if an error occured.
				if ( is_wp_error( $page ) ) {
					return $page;
				}
			}

			// Get content.
			$content = $this->get_content( $page );

			if ( is_wp_error( $content ) ) {
				return $content;
			}

			// Build cache.
			$cache = array(
				'content' => $content,
				'url'     => $this->url,
			);

			// Get TOC Headings and Keys.
			$headings = $this->get_headings( $page );
			if ( $headings ) {
				$cache['headings']      = $headings;
				$cache['headings_keys'] = array_keys( $headings );
			}

			// Store in cache.
			$this->page_cache[ $term . '-' . $language ] = $cache;

			// Cleanup unused vars.
			unset( $content, $headings );
		}

		// If no sections are specified, return the summary.
		if ( ! $sections ) {
			$return_elements = $this->get_elements( $this->page_cache[ $term . '-' . $language ]['content'], $term, false, 'h2', $elements, $remove_links );

			// If no elements found, bail.
			if ( ! count( $return_elements ) ) {
				return new WP_Error(
					'page_generator_pro_wikipedia_get_page_sections_no_elements_found',
					sprintf(
						/* translators: List of HTML elements */
						__( 'No elements could be found in the summary section matching %s', 'page-generator-pro' ),
						implode( ',', $elements )
					)
				);
			}

			return $return_elements;
		}

		// If no headings could be found, return the summary.
		if ( ! isset( $this->page_cache[ $term . '-' . $language ]['headings'] ) || ! $this->page_cache[ $term . '-' . $language ]['headings'] ) {
			$return_elements = $this->get_elements( $this->page_cache[ $term . '-' . $language ]['content'], $term, false, 'h2', $elements, $remove_links );

			// If no elements found, bail.
			if ( count( $return_elements ) === 0 ) {
				return new WP_Error(
					'page_generator_pro_wikipedia_get_page_sections_no_elements_found',
					sprintf(
						/* translators: List of HTML elements */
						__( 'No headings could be found, and no elements could be found in the summary section matching %s', 'page-generator-pro' ),
						implode( ',', $elements )
					)
				);
			}

			return $return_elements;
		}

		// Iterate through each section, fetching elements.
		$return_elements = array();
		foreach ( $sections as $section ) {
			unset( $result );

			switch ( $section ) {
				case 'summary':
				case 'Summary':
					$result = $this->get_elements( $this->page_cache[ $term . '-' . $language ]['content'], $term, false, 'h2', $elements, $remove_links );
					break;

				default:
					// Get index of this section from the array of headings.
					$index = $this->get_heading_index( $this->page_cache[ $term . '-' . $language ]['headings'], $section );

					// If no index could be found, skip this section.
					if ( $index === false ) {
						break;
					}

					// Based on the index of this heading, define the start and end heading keys (IDs).
					$start_heading = $this->page_cache[ $term . '-' . $language ]['headings_keys'][ $index ];

					// If this section is the last heading, there isn't a 'next' heading that we can use
					// to determine the end of the content, so we use the navbox instead.
					if ( ! isset( $this->page_cache[ $term . '-' . $language ]['headings_keys'][ $index + 1 ] ) ) {
						$end_heading = 'navbox';
					} else {
						$end_heading = $this->page_cache[ $term . '-' . $language ]['headings_keys'][ $index + 1 ];
					}

					// Extract elements.
					$result = $this->get_elements( $this->page_cache[ $term . '-' . $language ]['content'], $term, $start_heading, $end_heading, $elements, $remove_links );
					break;
			}

			// Skip if no content found.
			if ( ! isset( $result ) || count( $result ) === 0 ) {
				continue;
			}

			// Add the results (elements) to the main array.
			$return_elements = array_merge( $return_elements, $result );
		}

		// If no elements found, bail.
		if ( count( $return_elements ) === 0 ) {
			return new WP_Error(
				'page_generator_pro_wikipedia_get_page_sections_no_content_found',
				sprintf(
					/* translators: %1$s: List of sections, %2$s: List of HTML elements */
					__( 'No content could be found in the sections %1$s for the elements %2$s', 'page-generator-pro' ),
					implode( ', ', $sections ),
					implode( ', ', $elements )
				)
			);
		}

		// Return elements.
		return $return_elements;

	}

	/**
	 * Extracts the Term from a URL, if the Term is a URL, and sanitizes the Term
	 * to remove some accents that cause issues with Wikipedia
	 *
	 * @since   3.1.7
	 *
	 * @param   string $term   Term or Wikipedia URL.
	 * @return  string          Term
	 */
	private function sanitize_term( $term ) {

		// If the Term is a Wikipedia URL, extract the Term for the API call.
		if ( filter_var( $term, FILTER_VALIDATE_URL ) && strpos( $term, 'wikipedia.org' ) !== false ) {
			$url  = wp_parse_url( $term );
			$term = str_replace( '/wiki/', '', $url['path'] );
			$term = str_replace( '/', '', $term );
		}

		// Return sanitized term.
		return str_replace( ' ', '_', preg_replace( '/&([A-Za-z]{1,2})(grave|acute|circ|cedil|uml|lig);/', '$1', $term ) );

	}

	/**
	 * Sends a request to the Wikipedia API
	 *
	 * @since   3.1.7
	 *
	 * @param   array  $args       Request arguments.
	 * @param   string $language   Language.
	 * @return  WP_Error|object
	 */
	private function request( $args, $language ) {

		// Merge args.
		$args = array_merge(
			array(
				'action'        => 'parse',
				'format'        => 'json',
				'formatversion' => 2,
			),
			$args
		);

		// Build API URL.
		$url = add_query_arg( $args, 'https://' . $language . '.wikipedia.org/w/api.php' );

		// Query API.
		// User-agent ensures we get all content.
		$response = wp_remote_get(
			$url,
			array(
				'user-agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/34.0.1847.131 Safari/537.36',
				'sslverify'  => false,
			)
		);

		// Bail if an error occured.
		if ( is_wp_error( $response ) ) {
			return $response;
		}

		// Bail if HTTP response code isn't valid.
		if ( wp_remote_retrieve_response_code( $response ) !== 200 ) {
			return new WP_Error( 'page_generator_pro_wikipedia_request', wp_remote_retrieve_response_code( $response ) );
		}

		// Fetch body and JSON decode.
		$body   = wp_remote_retrieve_body( $response );
		$result = json_decode( $body );

		// Bail if an error was received from Wikipedia.
		if ( isset( $result->error ) ) {
			return new WP_Error( 'page_generator_pro_wikipedia_request', $result->error->code . ': ' . $result->error->info );
		}

		// Bail if the expected data is missing.
		if ( ! isset( $result->{ $args['action'] } ) ) {
			return new WP_Error( 'page_generator_pro_wikipedia_get_page', __( 'No data was returned.', 'page-generator-pro' ) );
		}

		// If the result contains a redirect, parse that instead.
		if ( isset( $result->{ $args['action'] }->text ) && strpos( $result->{ $args['action'] }->text, 'Redirect to:' ) !== false ) {
			// Extract redirect page slug.
			$start        = ( strpos( $result->{ $args['action'] }->text, 'href="' ) + 6 );
			$end          = strpos( $result->{ $args['action'] }->text, '" title="', $start );
			$args['page'] = str_replace( '/wiki/', '', substr( $result->{ $args['action'] }->text, $start, ( $end - $start ) ) );
			return $this->request( $args, $language );
		}

		// Return.
		return $result->{ $args['action'] };

	}

	/**
	 * Returns a DOMDocument representation of a Wikipedia Page's content
	 *
	 * @since   2.2.7
	 *
	 * @param   string $term       Term / URL.
	 * @param   string $language   Language.
	 * @return  WP_Error|DOMDocument
	 */
	private function get_page( $term, $language ) {

		// Build API URL.
		$result = $this->request(
			array(
				'page' => $term,
				'prop' => 'text',
			),
			$language
		);

		// Bail if an error occured.
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		// Load the HTML into a DOMDocument.
		// Wrap the Wikipedia HTML in <html>, <head> and <body> tags now, so we can inject the UTF-8 Content-Type meta tag.
		// Forcibly tell DOMDocument that this HTML uses the UTF-8 charset.
		// <meta charset="utf-8"> isn't enough, as DOMDocument still interprets the HTML as ISO-8859, which breaks character encoding
		// Use of mb_convert_encoding() with HTML-ENTITIES is deprecated in PHP 8.2, so we have to use this method.
		// If we don't, special characters render incorrectly.
		libxml_use_internal_errors( true );
		$dom = new DOMDocument();
		$dom->loadHTML( '<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"></head><body>' . $result->text . '</body></html>' );

		// Store URL.
		$this->url = 'https://' . $language . '.wikipedia.org/wiki/' . $term;

		// Return.
		return $dom;

	}

	/**
	 * Flag to denote if the page is a disambiguation page, meaning that the term given
	 * is too ambiguous to determine which article to fetch
	 *
	 * @since   2.2.8
	 *
	 * @param   string $term       Original Term.
	 * @param   string $language   Language.
	 * @return  bool                    Is Disambiguation Page
	 */
	private function is_disambiguation_page( $term, $language ) {

		$result = $this->request(
			array(
				'action' => 'query',
				'prop'   => 'pageprops',
				'ppprop' => 'disambiguation',
				'titles' => $term,
			),
			$language
		);

		// If pageprops does not exist, this is not a disambiguation page.
		if ( ! isset( $result->pages[0]->pageprops ) ) {
			return false;
		}

		// If pageprops->disambiguation does not exist, this is not a disambiguation page.
		if ( ! isset( $result->pages[0]->pageprops->disambiguation ) ) {
			return false;
		}

		// This is a disambiguation page.
		return true;

	}

	/**
	 * Returns the first similar term from the list of "may refer to" links
	 * where the given DOM represents a Wikipedia Disambiguation Page
	 *
	 * @since   4.9.2
	 *
	 * @param   DOMDocument $dom        Wikipedia Page DOM.
	 * @param   string      $term       Original Term.
	 * @param   string      $language   Language.
	 * @return  WP_Error|string
	 */
	private function get_similar_link( $dom, $term, $language ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter

		// Sanity check that a disambiguation element exists.
		if ( ! $this->is_disambiguation_page( $term, $language ) ) {
			return new WP_Error(
				'page_generator_pro_wikipedia_get_similar_page_not_disambiguation',
				__( 'The given Page is not a disambiguation page, therefore no similar page can be fetched.', 'page-generator-pro' )
			);
		}

		// Get terms listed in the 'may refer to' part.
		$links = $this->get_similar_page_links( $dom, $term, $language );
		if ( is_wp_error( $links ) ) {
			return $links;
		}

		// Return first link.
		return $links[0];

	}

	/**
	 * Returns an array of all links found in the Wikipedia article's
	 * 'may refer to' links that match the given Term.
	 *
	 * @since   4.9.2
	 *
	 * @param   DOMDocument $content    Wikipedia Page DOM.
	 * @param   string      $term       Original Term.
	 * @param   string      $language   Language.
	 * @return  WP_Error|array
	 */
	private function get_similar_page_links( $content, $term, $language ) {

		// Load DOMDocument into XPath.
		$xpath = new DOMXPath( $content );

		// Extract link names and anchors.
		$links = array();
		foreach ( $xpath->query( '//li//a' ) as $link ) {
			// Skip if the class name contains 'new' - there's no published article available.
			if ( strpos( $link->getAttribute( 'class' ), 'new' ) !== false ) { // @phpstan-ignore-line
				continue;
			}

			// Skip if the link's text does not contain the term.
			if ( stripos( $link->nodeValue, $term ) === false ) { // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
				continue;
			}

			// Skip if the link is for wiktionary.org.
			if ( strpos( $link->getAttribute( 'href' ), 'wiktionary.org' ) !== false ) {
				continue;
			}

			// Skip if the link isn't for the wiki.
			if ( strpos( $link->getAttribute( 'href' ), '/wiki/' ) === false ) {
				continue;
			}

			// Add the fully qualified link.
			$links[] = 'https://' . $language . '.wikipedia.org' . $link->getAttribute( 'href' ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		}

		// Bail if no links found.
		if ( count( $links ) === 0 ) {
			return new WP_Error(
				'page_generator_pro_wikipedia_get_similar_page_similar_pages_empty',
				__( 'There are no similar page terms on this disambiguation page.', 'page-generator-pro' )
			);
		}

		// Return links.
		return $links;

	}

	/**
	 * Returns the main content of the Wikipedia article
	 *
	 * @since   2.2.7
	 *
	 * @param   DOMDocument $dom    Wikipedia Page DOM.
	 * @return  WP_Error|DOMElement
	 */
	private function get_content( $dom ) {

		// Iterate through content until we find the .mw-parser-output element.
		foreach ( $dom->getElementsByTagName( 'div' ) as $node ) {
			if ( strpos( $node->getAttribute( 'class' ), 'mw-parser-output' ) !== false ) {
				return $node;
			}
		}

		// If here, we couldn't find the .mw-parser-output element.
		return new WP_Error( 'page_generator_pro_wikipedia_get_content_mw_parser_output_class_missing', __( 'The mw-parser-output CSS class could not be found on the Wikipedia Page', 'page-generator-pro' ) );

	}

	/**
	 * Returns an array of all <h2> headings found in the Wikipedia article's
	 * contents, which form the table of contents.
	 *
	 * @since   2.2.7
	 *
	 * @param   DOMDocument $dom    Wikipedia DOM.
	 * @return  bool|array
	 */
	private function get_headings( $dom ) {

		// Get table of contents.
		$table_of_contents = $dom->getElementsByTagName( 'h2' );

		// Bail if no table of contents could be found.
		if ( ! $table_of_contents->length ) {
			return false;
		}

		// Extract heading names and anchors.
		$headings = array();
		foreach ( $table_of_contents as $heading ) {
			$headings[ $heading->getAttribute( 'id' ) ] = $heading->nodeValue; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		}

		// Return headings.
		return $headings;

	}

	/**
	 * Searches both keys and values for the given array of headings to find a heading
	 *
	 * @since   2.2.7
	 *
	 * @param   array  $headings   Headings.
	 * @param   string $search     Heading to search for.
	 * @return  bool|int
	 */
	private function get_heading_index( $headings, $search ) {

		$search = strtolower( $search );

		$i = 0;
		foreach ( $headings as $heading => $label ) {
			if ( strtolower( $heading ) === $search ) {
				return $i;
			}

			if ( strtolower( $label ) === $search ) {
				return $i;
			}

			++$i;
		}

		return false;

	}

	/**
	 * Returns an array of specified elements between the given start and end element
	 *
	 * @since   2.2.7
	 *
	 * @param   DOMElement   $content        Article Content Node.
	 * @param   string       $term           Term.
	 * @param   bool|string  $start_element  Start Element.
	 * @param   bool|string  $end_element    End Element.
	 * @param   string|array $elements       Elements to Return.
	 * @param   bool         $remove_links   Remove Links (default: true).
	 * @return  array                        Elements
	 */
	private function get_elements( $content, $term, $start_element = false, $end_element = false, $elements = 'paragraphs', $remove_links = true ) {

		// Define array to store elements in.
		$return_elements = array();

		// Flag to denote whether we should start collecting elements.
		$collect_elements = ( ! $start_element ? true : false );

		foreach ( $content->childNodes as $node ) { // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
			if ( ! $node instanceof DOMElement ) {
				continue;
			}

			// Start collecting elements if we've not yet started and this element matches our start element selector.
			if ( $start_element !== false && $this->is_element( $node, $start_element ) ) {
				$collect_elements = true;
			}

			// Stop collecting elements if we've reached the end element.
			if ( $end_element !== false && $this->is_element( $node, $end_element ) ) {
				$collect_elements = false;
				break;
			}

			// Skip if we're not yet collecting elements.
			if ( ! $collect_elements ) {
				continue;
			}

			// Skip if not an element we want.
			if ( ! in_array( $node->tagName, $this->get_tags_by_elements( $elements ), true ) ) { // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
				continue;
			}

			// Get text.
			$text = trim( $node->nodeValue ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase

			// Skip if empty.
			if ( empty( $text ) ) {
				continue;
			}

			// Skip if this entire elements's nodeValue matches the keyword.
			if ( strpos( $term, $text ) !== false ) {
				continue;
			}

			// Strip some child nodes that we don't want.
			$node = $this->remove_child_nodes( $node );

			// Save HTML of node so we get the entire markup for this element .
			$content = $node->ownerDocument->saveHTML( $node ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase

			// Skip if the elements starts with certain characters.
			if ( strpos( $content, '[[' ) !== false ) {
				continue;
			}

			// Remove footnotes.
			$content = preg_replace( '/\[[a-z0-9]+\]/', '', $content );
			$content = preg_replace( '/<sup[^>]*>(.*?)<\/sup>/', '', $content );

			// Remove links, if required.
			if ( $remove_links ) {
				$content = preg_replace( array( '"<a (.*?)>"', '"</a>"' ), array( '', '' ), $content );
			} else {
				// Change relative links to absolute links.

				// Parse URL so we can build a localized wikipedia.org link.
				$url = wp_parse_url( $this->url );

				// Change e.g. /wiki/foobar --> https://en.wikipedia.org/wiki/foobar.
				$content = str_replace( 'href="/wiki/', 'href="' . $url['scheme'] . '://' . $url['host'] . '/wiki/', $content );
			}

			// Remove some odd characters that may have been left behind, such as ().
			$content = str_replace( '()', '', $content );

			// Add elements to array.
			$return_elements[] = $content;
		}

		return $return_elements;

	}

	/**
	 * Returns an array of supported elements that can be fetched from
	 * a Wikipedia Article, with their values being label names
	 *
	 * @since   2.7.1
	 *
	 * @return  array   Supported Elements
	 */
	private function get_supported_elements() {

		return array(
			'paragraphs' => __( 'Paragraphs', 'page-generator-pro' ),
			'headings'   => __( 'Headings', 'page-generator-pro' ),
			'lists'      => __( 'Lists', 'page-generator-pro' ),
			'tables'     => __( 'Tables', 'page-generator-pro' ),
			'images'     => __( 'Images', 'page-generator-pro' ),
		);

	}

	/**
	 * Returns an array of supported elements that can be fetched from
	 * a Wikipedia Article, with their values being an array of HTML tags
	 *
	 * @since   2.7.1
	 *
	 * @return  array   Supported Elements
	 */
	private function get_supported_elements_tags() {

		return array(
			'paragraphs' => array( 'p' ),
			'headings'   => array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6' ),
			'lists'      => array( 'ol', 'ul' ),
			'tables'     => array( 'table' ),
			'images'     => array( 'img' ),
		);

	}

	/**
	 * Helper method to retrieve Wikipedia languages
	 *
	 * @since   3.1.7
	 *
	 * @return  array    Languages
	 */
	private function get_languages() {

		// Keys are Wikipedia subdomains e.g. ab.wikipedia.org.
		// Values are the language names in English.
		$languages = array(
			'ab'           => 'Abkhazian',
			'ace'          => 'Acehnese',
			'ady'          => 'Adyghe',
			'aa'           => 'Afar',
			'af'           => 'Afrikaans',
			'ak'           => 'Akan',
			'sq'           => 'Albanian',
			'als'          => 'Alemannic',
			'am'           => 'Amharic',
			'ang'          => 'Anglo-Saxon',
			'ar'           => 'Arabic',
			'an'           => 'Aragonese',
			'arc'          => 'Aramaic',
			'hy'           => 'Armenian',
			'roa-rup'      => 'Aromanian',
			'as'           => 'Assamese',
			'ast'          => 'Asturian',
			'av'           => 'Avar',
			'ay'           => 'Aymara',
			'az'           => 'Azerbaijani',
			'bm'           => 'Bambara',
			'bjn'          => 'Banjar',
			'map-bms'      => 'Banyumasan',
			'ba'           => 'Bashkir',
			'eu'           => 'Basque',
			'bar'          => 'Bavarian',
			'be'           => 'Belarusian',
			'be-tarask'    => 'Belarusian (Taraškievica)',
			'bn'           => 'Bengali',
			'bh'           => 'Bihari',
			'bpy'          => 'Bishnupriya Manipuri',
			'bi'           => 'Bislama',
			'bs'           => 'Bosnian',
			'br'           => 'Breton',
			'bug'          => 'Buginese',
			'bg'           => 'Bulgarian',
			'my'           => 'Burmese',
			'bxr'          => 'Buryat',
			'zh-yue'       => 'Cantonese',
			'ca'           => 'Catalan',
			'ceb'          => 'Cebuano',
			'bcl'          => 'Central Bicolano',
			'ch'           => 'Chamorro',
			'cbk-zam'      => 'Chavacano',
			'ce'           => 'Chechen',
			'chr'          => 'Cherokee',
			'chy'          => 'Cheyenne',
			'ny'           => 'Chichewa',
			'zh'           => 'Chinese',
			'cho'          => 'Choctaw',
			'cv'           => 'Chuvash',
			'zh-classical' => 'Classical Chinese',
			'kw'           => 'Cornish',
			'co'           => 'Corsican',
			'cr'           => 'Cree',
			'crh'          => 'Crimean Tatar',
			'hr'           => 'Croatian',
			'cs'           => 'Czech',
			'da'           => 'Danish',
			'dv'           => 'Divehi',
			'nl'           => 'Dutch',
			'nds-nl'       => 'Dutch Low Saxon',
			'dz'           => 'Dzongkha',
			'arz'          => 'Egyptian Arabic',
			'eml'          => 'Emilian-Romagnol',
			'en'           => 'English',
			'myv'          => 'Erzya',
			'eo'           => 'Esperanto',
			'et'           => 'Estonian',
			'ee'           => 'Ewe',
			'ext'          => 'Extremaduran',
			'fo'           => 'Faroese',
			'hif'          => 'Fiji Hindi',
			'fj'           => 'Fijian',
			'fi'           => 'Finnish',
			'frp'          => 'Franco-Provençal',
			'fr'           => 'French',
			'fur'          => 'Friulian',
			'ff'           => 'Fula',
			'gag'          => 'Gagauz',
			'gl'           => 'Galician',
			'gan'          => 'Gan',
			'ka'           => 'Georgian',
			'de'           => 'German',
			'glk'          => 'Gilaki',
			'gom'          => 'Goan Konkani',
			'got'          => 'Gothic',
			'el'           => 'Greek',
			'kl'           => 'Greenlandic',
			'gn'           => 'Guarani',
			'gu'           => 'Gujarati',
			'ht'           => 'Haitian',
			'hak'          => 'Hakka',
			'ha'           => 'Hausa',
			'haw'          => 'Hawaiian',
			'he'           => 'Hebrew',
			'hz'           => 'Herero',
			'mrj'          => 'Hill Mari',
			'hi'           => 'Hindi',
			'ho'           => 'Hiri Motu',
			'hu'           => 'Hungarian',
			'is'           => 'Icelandic',
			'io'           => 'Ido',
			'ig'           => 'Igbo',
			'ilo'          => 'Ilokano',
			'id'           => 'Indonesian',
			'ia'           => 'Interlingua',
			'ie'           => 'Interlingue',
			'iu'           => 'Inuktitut',
			'ik'           => 'Inupiak',
			'ga'           => 'Irish',
			'it'           => 'Italian',
			'jam'          => 'Jamaican Patois',
			'ja'           => 'Japanese',
			'jv'           => 'Javanese',
			'kbd'          => 'Kabardian',
			'kab'          => 'Kabyle',
			'xal'          => 'Kalmyk',
			'kn'           => 'Kannada',
			'kr'           => 'Kanuri',
			'pam'          => 'Kapampangan',
			'krc'          => 'Karachay-Balkar',
			'kaa'          => 'Karakalpak',
			'ks'           => 'Kashmiri',
			'csb'          => 'Kashubian',
			'kk'           => 'Kazakh',
			'km'           => 'Khmer',
			'ki'           => 'Kikuyu',
			'rw'           => 'Kinyarwanda',
			'ky'           => 'Kirghiz',
			'rn'           => 'Kirundi',
			'kv'           => 'Komi',
			'koi'          => 'Komi-Permyak',
			'kg'           => 'Kongo',
			'ko'           => 'Korean',
			'kj'           => 'Kuanyama',
			'ku'           => 'Kurdish (Kurmanji)',
			'ckb'          => 'Kurdish (Sorani)',
			'lad'          => 'Ladino',
			'lbe'          => 'Lak',
			'lo'           => 'Lao',
			'ltg'          => 'Latgalian',
			'la'           => 'Latin',
			'lv'           => 'Latvian',
			'lez'          => 'Lezgian',
			'lij'          => 'Ligurian',
			'li'           => 'Limburgish',
			'ln'           => 'Lingala',
			'lt'           => 'Lithuanian',
			'jbo'          => 'Lojban',
			'lmo'          => 'Lombard',
			'nds'          => 'Low Saxon',
			'dsb'          => 'Lower Sorbian',
			'lg'           => 'Luganda',
			'lb'           => 'Luxembourgish',
			'mk'           => 'Macedonian',
			'mai'          => 'Maithili',
			'mg'           => 'Malagasy',
			'ms'           => 'Malay',
			'ml'           => 'Malayalam',
			'mt'           => 'Maltese',
			'gv'           => 'Manx',
			'mi'           => 'Maori',
			'mr'           => 'Marathi',
			'mh'           => 'Marshallese',
			'mzn'          => 'Mazandarani',
			'mhr'          => 'Meadow Mari',
			'cdo'          => 'Min Dong',
			'zh-min-nan'   => 'Min Nan',
			'min'          => 'Minangkabau',
			'xmf'          => 'Mingrelian',
			'mwl'          => 'Mirandese',
			'mdf'          => 'Moksha',
			'mo'           => 'Moldovan',
			'mn'           => 'Mongolian',
			'mus'          => 'Muscogee',
			'nah'          => 'Nahuatl',
			'na'           => 'Nauruan',
			'nv'           => 'Navajo',
			'ng'           => 'Ndonga',
			'nap'          => 'Neapolitan',
			'ne'           => 'Nepali',
			'new'          => 'Newar',
			'pih'          => 'Norfolk',
			'nrm'          => 'Norman',
			'frr'          => 'North Frisian',
			'lrc'          => 'Northern Luri',
			'se'           => 'Northern Sami',
			'nso'          => 'Northern Sotho',
			'no'           => 'Norwegian (Bokmål)',
			'nn'           => 'Norwegian (Nynorsk)',
			'nov'          => 'Novial',
			'ii'           => 'Nuosu',
			'oc'           => 'Occitan',
			'cu'           => 'Old Church Slavonic',
			'or'           => 'Oriya',
			'om'           => 'Oromo',
			'os'           => 'Ossetian',
			'pfl'          => 'Palatinate German',
			'pi'           => 'Pali',
			'pag'          => 'Pangasinan',
			'pap'          => 'Papiamentu',
			'ps'           => 'Pashto',
			'pdc'          => 'Pennsylvania German',
			'fa'           => 'Persian',
			'pcd'          => 'Picard',
			'pms'          => 'Piedmontese',
			'pl'           => 'Polish',
			'pnt'          => 'Pontic',
			'pt'           => 'Portuguese',
			'pa'           => 'Punjabi',
			'qu'           => 'Quechua',
			'ksh'          => 'Ripuarian',
			'rmy'          => 'Romani',
			'ro'           => 'Romanian',
			'rm'           => 'Romansh',
			'ru'           => 'Russian',
			'rue'          => 'Rusyn',
			'sah'          => 'Sakha',
			'sm'           => 'Samoan',
			'bat-smg'      => 'Samogitian',
			'sg'           => 'Sango',
			'sa'           => 'Sanskrit',
			'sc'           => 'Sardinian',
			'stq'          => 'Saterland Frisian',
			'sco'          => 'Scots',
			'gd'           => 'Scottish Gaelic',
			'sr'           => 'Serbian',
			'sh'           => 'Serbo-Croatian',
			'st'           => 'Sesotho',
			'sn'           => 'Shona',
			'scn'          => 'Sicilian',
			'szl'          => 'Silesian',
			'simple'       => 'Simple English',
			'sd'           => 'Sindhi',
			'si'           => 'Sinhalese',
			'sk'           => 'Slovak',
			'sl'           => 'Slovenian',
			'so'           => 'Somali',
			'azb'          => 'Southern Azerbaijani',
			'es'           => 'Spanish',
			'srn'          => 'Sranan',
			'su'           => 'Sundanese',
			'sw'           => 'Swahili',
			'ss'           => 'Swati',
			'sv'           => 'Swedish',
			'tl'           => 'Tagalog',
			'ty'           => 'Tahitian',
			'tg'           => 'Tajik',
			'ta'           => 'Tamil',
			'roa-tara'     => 'Tarantino',
			'tt'           => 'Tatar',
			'te'           => 'Telugu',
			'tet'          => 'Tetum',
			'th'           => 'Thai',
			'bo'           => 'Tibetan',
			'ti'           => 'Tigrinya',
			'tpi'          => 'Tok Pisin',
			'to'           => 'Tongan',
			'ts'           => 'Tsonga',
			'tn'           => 'Tswana',
			'tum'          => 'Tumbuka',
			'tr'           => 'Turkish',
			'tk'           => 'Turkmen',
			'tyv'          => 'Tuvan',
			'tw'           => 'Twi',
			'udm'          => 'Udmurt',
			'uk'           => 'Ukrainian',
			'hsb'          => 'Upper Sorbian',
			'ur'           => 'Urdu',
			'ug'           => 'Uyghur',
			'uz'           => 'Uzbek',
			've'           => 'Venda',
			'vec'          => 'Venetian',
			'vep'          => 'Vepsian',
			'vi'           => 'Vietnamese',
			'vo'           => 'Volapük',
			'fiu-vro'      => 'Võro',
			'wa'           => 'Walloon',
			'war'          => 'Waray',
			'cy'           => 'Welsh',
			'vls'          => 'West Flemish',
			'fy'           => 'West Frisian',
			'pnb'          => 'Western Punjabi',
			'wo'           => 'Wolof',
			'wuu'          => 'Wu',
			'xh'           => 'Xhosa',
			'yi'           => 'Yiddish',
			'yo'           => 'Yoruba',
			'diq'          => 'Zazaki',
			'zea'          => 'Zeelandic',
			'za'           => 'Zhuang',
			'zu'           => 'Zulu',
		);

		/**
		 * Defines available Wikipedia languages.
		 *
		 * @since   3.1.7
		 *
		 * @param   array   $output_types   Output Types.
		 */
		$languages = apply_filters( 'page_generator_pro_wikipedia_get_languages', $languages );

		// Return filtered results.
		return $languages;

	}

	/**
	 * Returns an array of HTML tags (e.g. p,h1) for the given element names (e.g. paragraphs,headings)
	 *
	 * @since   2.7.1
	 *
	 * @param   string|array $elements   Elements (string|array).
	 * @return  array
	 */
	private function get_tags_by_elements( $elements ) {

		// Convert elements to an array if it's a string.
		if ( ! is_array( $elements ) ) {
			$elements = array( $elements );
		}

		// Get element names and their tags.
		$elements_tags = $this->get_supported_elements_tags();

		// Build array of HTML tags.
		$tags = array();
		foreach ( $elements as $element ) {
			// Skip if element isn't supported.
			if ( ! isset( $elements_tags[ $element ] ) ) {
				continue;
			}

			$tags = array_merge( $tags, $elements_tags[ $element ] );
		}

		return $tags;

	}

	/**
	 * Recursively iterates through the node to see if it, or any descendents,
	 * have an ID or class attribute matching the given search
	 *
	 * @since   2.2.7
	 *
	 * @param   DOMNode $node       Node.
	 * @param   string  $search     Search Class or ID.
	 * @return  bool                    Element matches Search by HTML Tag, ID or class
	 */
	private function is_element( $node, $search ) {

		// Return true if the element's tag matches our search term.
		if ( $node->tagName === $search ) { // phpcs:ignore WordPress.NamingConventions.ValidVariableName, @phpstan-ignore-line
			return true;
		}

		// Return true if the element's ID matches our search term.
		if ( $node->getAttribute( 'id' ) === $search ) { // @phpstan-ignore-line
			return true;
		}

		// Return true if a class name matches our search term.
		$classes = explode( ' ', $node->getAttribute( 'class' ) ); // @phpstan-ignore-line
		if ( in_array( $search, $classes, true ) ) {
			return true;
		}

		// If children exist, iterate them now.
		if ( $node->childNodes ) { // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase, @phpstan-ignore-line
			foreach ( $node->childNodes as $child_node ) { // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
				if ( ! $child_node instanceof DOMElement ) {
					continue;
				}

				if ( $this->is_element( $child_node, $search ) ) {
					return true;
				}
			}
		}

		return false;

	}

	/**
	 * Removes links, if specified, from the given node, as well as some predefined
	 * child nodes that we don't want, such as Wikipedia Edit Links.
	 *
	 * @since   2.7.1
	 *
	 * @param   DOMNode $node   Node.
	 * @return  DOMNode             Node
	 */
	private function remove_child_nodes( $node ) {

		// Define tags and CSS class combinations to remove.
		$tags = array(
			'sup'   => array(),
			'span'  => array(
				'mw-editsection',
				'rt-commentedText',
			),

			// Edit on Wikidata.
			'div'   => array(
				'wikidata-link',
			),

			'link'  => array(),
			'style' => array(),
		);

		// Iterate through tags.
		foreach ( $tags as $tag => $classes ) {
			$child_nodes = $node->getElementsByTagName( $tag ); // @phpstan-ignore-line

			// If no child nodes matching the tag exist, bail.
			if ( ! $child_nodes->length ) {
				continue;
			}

			// Iterate through tags.
			foreach ( $child_nodes as $child_node ) {
				// If $classes is empty, remove the tag regardless of its CSS class.
				if ( empty( $classes ) ) {
					try {
						// Access this node's parent to then remove the child i.e. this node.
						// Better than $node->removeChild(), which may trigger a not found exception.
						$child_node->parentNode->removeChild( $child_node ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
					} catch ( Exception $e ) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch
						// Continue on to the next node.
					}
					continue;
				}

				// Get CSS classes.
				$child_node_classes = $child_node->getAttribute( 'class' );

				// Skip if no classes.
				if ( empty( $child_node_classes ) ) {
					continue;
				}

				// Explode into an array so we can search for individual CSS classes.
				$child_node_classes = explode( ' ', $child_node_classes );

				// Iterate through classes that would require us to remove this child node.
				foreach ( $classes as $class ) {
					// Skip if this class doesn't exist in the child node's classes.
					if ( ! in_array( $class, $child_node_classes, true ) ) {
						continue;
					}

					// If here, we need to remove this child node.
					try {
						// Access this node's parent to then remove the child i.e. this node.
						// Better than $node->removeChild(), which may trigger a not found exception.
						$child_node->parentNode->removeChild( $child_node ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
					} catch ( Exception $e ) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch
						// Continue on to the next node.
					}
				}
			}
		}

		return $node;

	}

}
