<?php
/**
 * Creative Commons API Class.
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Fetch images from Creative Commons based on given criteria.
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 2.6.9
 */
class Page_Generator_Pro_Creative_Commons extends Page_Generator_Pro_API {

	use Page_Generator_Pro_Integration_Trait;
	use Page_Generator_Pro_Featured_Image_Trait;
	use Page_Generator_Pro_Shortcode_Image_Trait;

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
	public $name = 'creative-commons';

	/**
	 * Holds the API endpoint
	 *
	 * @since   2.6.9
	 *
	 * @var     string
	 */
	public $api_endpoint = 'https://api.openverse.engineering';

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
		add_filter( 'page_generator_pro_gutenberg_convert_html_block_to_core_block', array( $this, 'convert_html_block_to_image_block' ), 10, 1 );

		// Register as a Featured Image source.
		add_filter( 'page_generator_pro_common_get_featured_image_sources', array( $this, 'add_featured_image_source' ) );
		add_filter( 'page_generator_pro_groups_get_defaults', array( $this, 'get_featured_image_default_values' ) );
		add_filter( 'page_generator_pro_common_get_featured_image_fields', array( $this, 'get_featured_image_fields' ), 10, 2 );
		add_filter( 'page_generator_pro_common_get_featured_image_tabs', array( $this, 'get_featured_image_tabs' ) );
		add_filter( 'page_generator_pro_generate_featured_image_' . $this->name, array( $this, 'get_featured_image' ), 10, 6 );

	}

	/**
	 * Returns this shortcode / block's title.
	 *
	 * @since   4.8.0
	 *
	 * @return  string
	 */
	public function get_title() {

		return __( 'Creative Commons', 'page-generator-pro' );

	}

	/**
	 * Returns provider-specific fields for the Search Parameters section across
	 * the Dynamic Element and Featured Image.
	 *
	 * @since   4.8.0
	 *
	 * @param   bool $is_featured_image Fields are for Featured Image functionality.
	 * @return  array
	 */
	public function get_provider_search_fields( $is_featured_image = false ) {

		$prefix_key = ( $is_featured_image ? $this->get_settings_prefix() . '_' : '' );

		return array(
			'term'                   => array(
				'label'       => __( 'Term', 'page-generator-pro' ),
				'type'        => 'autocomplete',
				'values'      => $this->keywords,
				'placeholder' => __( 'e.g. building', 'page-generator-pro' ),
				'description' => __( 'The search term to use.  For example, "laptop" would return an image of a laptop.', 'page-generator-pro' ),
			),
			'orientation'            => array( // No prefix key is defined here. This is deliberate; `orientation` is shared with Pexels.
				'label'         => __( 'Image Orientation', 'page-generator-pro' ),
				'type'          => 'select',
				'values'        => $this->get_image_orientations(),
				'default_value' => $this->get_default_value( 'orientation' ),
				'description'   => __( 'The image orientation to output.', 'page-generator-pro' ),
			),
			$prefix_key . 'sources'  => array(
				'label'       => __( 'Sources', 'page-generator-pro' ),
				'type'        => 'select_multiple',
				'values'      => $this->get_sources(),
				'class'       => 'wpzinc-selectize-drag-drop',
				'description' => __( 'The sources to search. If none are selected, all sources are searched. Specifying sources may result in slower generation times.', 'page-generator-pro' ),
			),
			$prefix_key . 'licenses' => array(
				'label'       => __( 'Licenses', 'page-generator-pro' ),
				'type'        => 'select_multiple',
				'values'      => $this->get_licenses(),
				'class'       => 'wpzinc-selectize-drag-drop',
				'description' => sprintf(
					'%s <a href="https://creativecommons.org/share-your-work/cclicenses/" target="_blank">%s</a> %s',
					__( 'The', 'page-generator-pro' ),
					__( 'image licenses', 'page-generator-pro' ),
					__( 'to include. If none are selected, all licenses are included.  Specifying licenses may result in slower generation times.', 'page-generator-pro' ),
				),
			),
		);

	}

	/**
	 * Returns provider-specific fields for the Output section across
	 * the Dynamic Element and Featured Image.
	 *
	 * @since   4.8.0
	 *
	 * @param   bool $is_featured_image Fields are for Featured Image functionality.
	 * @return  array
	 */
	public function get_provider_output_fields( $is_featured_image = false ) {

		// No fields are needed for the Output section when configuring the Featured Image,
		// as attribution isn't possible.
		if ( $is_featured_image ) {
			return array();
		}

		return array(
			'attribution' => array(
				'label'       => __( 'Show Attribution?', 'page-generator-pro' ),
				'type'        => 'toggle',
				'description' => __( 'If enabled, outputs the credits/attribution below the image.', 'page-generator-pro' ),
			),
		);

	}

	/**
	 * Returns provider-specific attributes for the Dynamic Element.
	 *
	 * These are not used for Featured Images.
	 *
	 * @since   4.8.0
	 *
	 * @return  array
	 */
	public function get_provider_attributes() {

		return array(
			'orientation' => array(
				'type'    => 'string',
				'default' => ( ! $this->get_default_value( 'orientation' ) ? '' : $this->get_default_value( 'orientation' ) ),
			),
			'licenses'    => array(
				'type'      => 'array',
				'delimiter' => ',',
			),
			'sources'     => array(
				'type'      => 'array',
				'delimiter' => ',',
			),
			'attribution' => array(
				'type'    => 'boolean',
				'default' => $this->get_default_value( 'attribution' ),
			),
		);

	}

	/**
	 * Returns provider-specific default values across the Dynamic Element and Featured Image.
	 *
	 * @since   4.8.0
	 *
	 * @param   bool $is_featured_image Fields are for Featured Image functionality.
	 * @return  array
	 */
	public function get_provider_default_values( $is_featured_image = false ) {

		$prefix_key = ( $is_featured_image ? $this->get_settings_prefix() . '_' : '' );

		return array(
			'orientation'            => false,
			$prefix_key . 'licenses' => array(),
			$prefix_key . 'sources'  => array(),
			'attribution'            => false,
		);

	}

	/**
	 * Returns this shortcode / block's description.
	 *
	 * @since   4.5.2
	 *
	 * @return string
	 */
	public function get_description() {

		return __( 'Displays an image from Creative Commons, based on the given search parameters.', 'page-generator-pro' );

	}

	/**
	 * Returns this shortcode / block's icon.
	 *
	 * @since   4.5.2
	 *
	 * @return  string
	 */
	public function get_icon() {

		return 'assets/images/icons/creative-commons.svg';

	}

	/**
	 * Returns this shortcode / block's output
	 *
	 * @since   2.6.9
	 *
	 * @param  array $atts   Shortcode Attributes.
	 * @return string          Output
	 */
	public function render( $atts ) {

		// Parse attributes.
		$atts = $this->parse_atts( $atts );

		// Define the number of images to return to then choose one at random from.
		$per_page = 20;

		// Run query to fetch total number of pages of results that are available.
		$page_count = $this->base->get_class( 'creative_commons' )->page_count(
			$atts['term'],
			$atts['orientation'],
			$atts['licenses'],
			$atts['sources'],
			$per_page
		);

		// Handle errors.
		if ( is_wp_error( $page_count ) ) {
			return $this->add_dynamic_element_error_and_return( $page_count, $atts );
		}

		// Pick a page index at random from the resultset.
		if ( $page_count === 1 ) {
			$page_index = 1;
		} else {
			$page_index = wp_rand( 1, $page_count );
		}

		// Run images query, using the random page index.
		$images = $this->base->get_class( 'creative_commons' )->photos_search(
			$atts['term'],
			$atts['orientation'],
			$atts['licenses'],
			$atts['sources'],
			$per_page,
			$page_index
		);

		// Handle errors.
		if ( is_wp_error( $images ) ) {
			return $this->add_dynamic_element_error_and_return( $images, $atts );
		}

		// Pick an image at random from the resultset.
		$image = $this->choose_random_image( $images );

		// If copy if enabled, import the image into the Media Library, saving the Title, Caption,
		// Alt Tag, Description and EXIF metadata, if required.
		$image_id = false;
		if ( $atts['copy'] ) {
			$image_id = $this->import( $image, $atts );

			// Bail if an error occured.
			if ( is_wp_error( $image_id ) ) {
				return $this->add_dynamic_element_error_and_return( $image_id, $atts );
			}
		}

		// Get HTML image tag.
		$html = $this->get_image_html( $image_id, $atts, $image );

		/**
		 * Filter the Creative Commons HTML output, before returning.
		 *
		 * @since   2.6.9
		 *
		 * @param   string      $html       HTML Output.
		 * @param   array       $atts       Shortcode Attributes.
		 * @param   bool|int    $image_id   WordPress Media Library Image ID (false = not imported into Media Library as copy=0).
		 * @param   array       $images     Creative Commons Image Results.
		 * @param   array       $image      Creative Commons Image chosen at random and imported into the Media Library.
		 */
		$html = apply_filters( 'page_generator_pro_shortcode_creative_commons', $html, $atts, $image_id, $images, $image );

		// Return.
		return $html;

	}

	/**
	 * Defines the Featured Image for the given generated Post ID, if
	 * the Group Settings specify a Featured Image.
	 *
	 * @since   2.6.9
	 *
	 * @param   bool|int $image_id   Image ID.
	 * @param   int      $post_id    Generated Post ID.
	 * @param   int      $group_id   Group ID.
	 * @param   int      $index      Generation Index.
	 * @param   array    $settings   Group Settings.
	 * @param   array    $post_args  wp_insert_post() / wp_update_post() arguments.
	 * @return  WP_Error|bool|int
	 */
	public function get_featured_image( $image_id, $post_id, $group_id, $index, $settings, $post_args ) {

		// Bail if no Featured Image Term specified.
		if ( empty( $settings['featured_image_term'] ) ) {
			return new WP_Error(
				'page_generator_pro_generate',
				sprintf(
					/* translators: Integration Name */
					__( 'Featured Image: %s: No Term was specified.', 'page-generator-pro' ),
					$this->get_title()
				)
			);
		}

		// Adjust image orientation setting to be API compatible,
		// as providers may vary their settings here.
		switch ( $settings['featured_image_orientation'] ) {
			case 'portrait':
			case 'tall':
				$settings['featured_image_orientation'] = 'tall';
				break;

			case 'landscape':
			case 'horizontal':
			case 'wide':
				$settings['featured_image_orientation'] = 'wide';
				break;
		}

		// Define the number of images to return to then choose one at random from.
		$per_page = 20;

		// Run query to fetch total number of pages of results that are available.
		$page_count = $this->page_count(
			$settings['featured_image_term'],
			$settings['featured_image_orientation'],
			$settings[ 'featured_image_' . $this->get_settings_prefix() . '_licenses' ],
			$settings[ 'featured_image_' . $this->get_settings_prefix() . '_sources' ],
			$per_page
		);

		// Bail if an error occured.
		if ( is_wp_error( $page_count ) ) {
			return $page_count;
		}

		// Pick a page index at random from the resultset.
		if ( $page_count === 1 ) {
			$page_index = 1;
		} else {
			$page_index = wp_rand( 1, $page_count );
		}

		// Run images query.
		$images = $this->photos_search(
			$settings['featured_image_term'],
			$settings['featured_image_orientation'],
			$settings[ 'featured_image_' . $this->get_settings_prefix() . '_licenses' ],
			$settings[ 'featured_image_' . $this->get_settings_prefix() . '_sources' ],
			$per_page,
			$page_index
		);

		// Bail if an error occured.
		if ( is_wp_error( $images ) ) {
			return $images;
		}

		// Pick an image at random from the resultset.
		if ( count( $images ) === 1 ) {
			$image_index = 0;
		} else {
			$image_index = wp_rand( 0, ( count( $images ) - 1 ) );
		}

		// Import Image into the Media Library.
		return $this->import_remote_image(
			$images[ $image_index ]['url'],
			$post_id,
			$group_id,
			$index,
			$settings['featured_image_filename'],
			$settings['featured_image_title'],
			$settings['featured_image_caption'],
			$settings['featured_image_alt_tag'],
			$settings['featured_image_description']
		);

	}

	/**
	 * Returns an array of aspect ratios (orientations) supported
	 * by the API.
	 *
	 * @since   2.6.9
	 *
	 * @return  array   Supported Image Orientations
	 */
	public function get_image_orientations() {

		return array(
			0        => __( 'Any', 'page-generator-pro' ),
			'tall'   => __( 'Portrait', 'page-generator-pro' ),
			'wide'   => __( 'Landscape', 'page-generator-pro' ),
			'square' => __( 'Square', 'page-generator-pro' ),
		);

	}

	/**
	 * Returns an array of licenses supported by the API
	 *
	 * @since   2.9.9
	 *
	 * @return  array   Supported Licenses
	 */
	public function get_licenses() {

		return array(
			'BY-ND'    => 'BY-ND',
			'BY-NC'    => 'BY-NC',
			'PDM'      => 'PDM',
			'BY-NC-ND' => 'BY-NC-ND',
			'CC0'      => 'CC0',
			'BY-SA'    => 'BY-SA',
			'BY'       => 'BY',
			'BY-NC-SA' => 'BY-NC-SA',
		);

	}

	/**
	 * Returns an array of sources supported by the API
	 *
	 * @since   2.9.9
	 *
	 * @return  array   Supported Sources
	 */
	public function get_sources() {

		return array(
			'CAPL'                                        => 'CAPL',
			'WoRMS'                                       => 'WoRMS',
			'animaldiversity'                             => 'animaldiversity',
			'archief_alkmaar'                             => 'archief_alkmaar',
			'bib_gulbenkian'                              => 'bib_gulbenkian',
			'bio_diversity'                               => 'bio_diversity',
			'brooklynmuseum'                              => 'brooklynmuseum',
			'clevelandmuseum'                             => 'clevelandmuseum',
			'digitaltmuseum'                              => 'digitaltmuseum',
			'east_riding'                                 => 'east_riding',
			'europeana'                                   => 'europeana',
			'finnish_heritage_agency'                     => 'finnish_heritage_agency',
			'finnish_satakunnan_museum'                   => 'finnish_satakunnan_museum',
			'flickr'                                      => 'flickr',
			'floraon'                                     => 'floraon',
			'geographorguk'                               => 'geographorguk',
			'inaturalist'                                 => 'inaturalist',
			'justtakeitfree'                              => 'justtakeitfree',
			'met'                                         => 'met',
			'museumsvictoria'                             => 'museumsvictoria',
			'nappy'                                       => 'nappy',
			'nasa'                                        => 'nasa',
			'national_museum_of_finland'                  => 'national_museum_of_finland',
			'nypl'                                        => 'nypl',
			'phylopic'                                    => 'phylopic',
			'rawpixel'                                    => 'rawpixel',
			'rijksmuseum'                                 => 'rijksmuseum',
			'sciencemuseum'                               => 'sciencemuseum',
			'sketchfab'                                   => 'sketchfab',
			'smithsonian_african_american_history_museum' => 'smithsonian_african_american_history_museum',
			'smithsonian_african_art_museum'              => 'smithsonian_african_art_museum',
			'smithsonian_air_and_space_museum'            => 'smithsonian_air_and_space_museum',
			'smithsonian_american_art_museum'             => 'smithsonian_american_art_museum',
			'smithsonian_american_history_museum'         => 'smithsonian_american_history_museum',
			'smithsonian_american_indian_museum'          => 'smithsonian_american_indian_museum',
			'smithsonian_anacostia_museum'                => 'smithsonian_anacostia_museum',
			'smithsonian_cooper_hewitt_museum'            => 'smithsonian_cooper_hewitt_museum',
			'smithsonian_freer_gallery_of_art'            => 'smithsonian_freer_gallery_of_art',
			'smithsonian_gardens'                         => 'smithsonian_gardens',
			'smithsonian_hirshhorn_museum'                => 'smithsonian_hirshhorn_museum',
			'smithsonian_institution_archives'            => 'smithsonian_institution_archives',
			'smithsonian_libraries'                       => 'smithsonian_libraries',
			'smithsonian_national_museum_of_natural_history' => 'smithsonian_national_museum_of_natural_history',
			'smithsonian_portrait_gallery'                => 'smithsonian_portrait_gallery',
			'smithsonian_postal_museum'                   => 'smithsonian_postal_museum',
			'smithsonian_zoo_and_conservation'            => 'smithsonian_zoo_and_conservation',
			'smk'                                         => 'smk',
			'spacex'                                      => 'spacex',
			'stocksnap'                                   => 'stocksnap',
			'svgsilh'                                     => 'svgsilh',
			'thingiverse'                                 => 'thingiverse',
			'waltersartmuseum'                            => 'waltersartmuseum',
			'wellcome_collection'                         => 'wellcome_collection',
			'wikimedia'                                   => 'wikimedia',
			'woc_tech'                                    => 'woc_tech',
			'wordpress'                                   => 'wordpress',
		);

	}

	/**
	 * Searches photos based on the given query
	 *
	 * @since   2.6.9
	 *
	 * @param   string      $query          Search Term(s).
	 * @param   bool|string $orientation    Image Orientation (false, tall, wide, square).
	 * @param   bool|array  $licenses       Licenses (false, array of get_licenses()).
	 * @param   bool|array  $sources        Source (false, array of get_sources()).
	 * @param   int         $per_page       Number of Images to Return.
	 * @param   int         $page           Pagination Page Offset.
	 * @return  WP_Error|array
	 */
	public function photos_search( $query, $orientation = false, $licenses = false, $sources = false, $per_page = 20, $page = 1 ) {

		// Perform search.
		$results = $this->search( $query, $orientation, $licenses, $sources, $per_page, $page );

		// Bail if an error occured.
		if ( is_wp_error( $results ) ) {
			return new WP_Error(
				'page_generator_pro_creative_commons_photos_search_error',
				sprintf(
					/* translators: Error message */
					__( 'photos_search(): %s', 'page-generator-pro' ),
					$results->get_error_message()
				)
			);
		}

		// Parse results.
		$images = array();
		foreach ( $results->results as $photo ) {
			$images[] = array(
				'url'             => $photo->url,
				'title'           => ( isset( $photo->title ) ? $photo->title : false ),

				// Credits.
				'creator'         => ( isset( $photo->creator ) ? $photo->creator : false ),
				'creator_url'     => ( isset( $photo->creator_url ) ? $photo->creator_url : false ),
				'source'          => ( isset( $photo->source ) ? $photo->source : false ),
				'license'         => ( isset( $photo->license ) ? $photo->license : false ),
				'license_version' => ( isset( $photo->license_version ) ? $photo->license_version : false ),
				'license_url'     => ( isset( $photo->license_url ) ? $photo->license_url : false ),
			);
		}

		// Return array of images.
		return $images;

	}

	/**
	 * Returns the total number of pages found for the search parameters
	 *
	 * @since   2.8.4
	 *
	 * @param   string      $query          Search Term(s).
	 * @param   bool|string $orientation    Image Orientation (false, tall, wide, square).
	 * @param   bool|array  $licenses       Licenses (false, array of get_licenses()).
	 * @param   bool|array  $sources        Source (false, array of get_sources()).
	 * @param   int         $per_page       Number of Images to Return.
	 * @param   int         $page           Pagination Page Offset.
	 * @return  WP_Error|int
	 */
	public function page_count( $query, $orientation = false, $licenses = false, $sources = false, $per_page = 20, $page = 1 ) {

		// Perform search.
		$results = $this->search( $query, $orientation, $licenses, $sources, $per_page, $page );

		// Bail if an error occured.
		if ( is_wp_error( $results ) ) {
			return new WP_Error(
				'page_generator_pro_creative_commons_page_count_error',
				sprintf(
					/* translators: Error message */
					__( 'page_count(): %s', 'page-generator-pro' ),
					$results->get_error_message()
				)
			);
		}

		// Return the total number of pages, ensuring per_page * number of found pages doesn't exceed 1,000
		// as fetching an image from an index greater than 1,000 silently fails.
		// This is undocumented in the Creative Commons API.
		if ( $per_page * $results->page_count > 1000 ) {
			return (int) ceil( 1000 / $per_page );
		}

		// If the page count is zero, this means there's one page of results.
		// Return 1.
		if ( $results->page_count === 0 ) {
			return 1;
		}

		// Return the total number of pages.
		return (int) $results->page_count;

	}

	/**
	 * Performs an image search
	 *
	 * @since   2.8.4
	 *
	 * @param   string      $query          Search Term(s).
	 * @param   bool|string $orientation    Image Orientation (false, tall, wide, square).
	 * @param   bool|array  $licenses       Licenses (false, array of get_licenses()).
	 * @param   bool|array  $sources        Source (false, array of get_sources()).
	 * @param   int         $per_page       Number of Images to Return.
	 * @param   int         $page           Pagination Page Offset.
	 * @return  WP_Error|stdClass
	 */
	private function search( $query, $orientation = false, $licenses = false, $sources = false, $per_page = 20, $page = 1 ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter

		// Set HTTP headers.
		$this->set_headers(
			array(
				'Content-Type' => 'application/json',
			)
		);

		// Build array of arguments.
		$args = array(
			'q'            => $query,
			'license_type' => implode( ',', array( 'all', 'all-cc', 'commercial', 'modification' ) ),
			'page_size'    => $per_page,
			'page'         => $page,
			'aspect_ratio' => ( $orientation ? $orientation : 'tall,wide,square' ),
		);

		// Add optional arguments.
		if ( $licenses ) {
			$args['license'] = implode( ',', ( ! is_array( $licenses ) ? array( $licenses ) : $licenses ) );
		}
		if ( is_array( $sources ) ) {
			$args['source'] = implode( ',', $sources );

			// If sources is empty, unset it.
			if ( empty( $args['source'] ) ) {
				unset( $args['source'] );
			}
		}

		/**
		 * Filters the API arguments to send to the Creative Commons /images endpoint
		 *
		 * @since   2.6.9
		 *
		 * @param   array       $args           API arguments.
		 * @param   string      $query          Search Term(s).
		 * @param   bool|array  $orientation    Image Orientation (false, tall, wide, square).
		 * @param   int         $per_page       Number of Images to Return.
		 * @param   int         $page           Pagination Page Offset.
		 */
		$args = apply_filters( 'page_generator_pro_creative_commons_photos_search_args', $args, $query, $orientation, $per_page, $page );

		// Run the query.
		$results = $this->get( 'v1/images', $args );

		// Bail if an error occured.
		if ( is_wp_error( $results ) ) {
			return $results;
		}

		// Bail if a generic error occured.
		if ( isset( $results->detail ) ) {
			return new WP_Error(
				'page_generator_pro_creative_commons_photos_search',
				$results->detail
			);
		}

		// Bail if no results were found.
		if ( ! isset( $results->result_count ) || ! $results->result_count ) {
			return new WP_Error(
				'page_generator_pro_creative_commons_photos_search',
				__( 'No results were found for the given search criteria.', 'page-generator-pro' )
			);
		}

		// Return results.
		return $results;

	}

}
