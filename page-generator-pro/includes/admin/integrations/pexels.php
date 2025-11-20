<?php
/**
 * Pexels API Class.
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Fetch images from Pexels based on given criteria.
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 2.2.9
 */
class Page_Generator_Pro_Pexels extends Page_Generator_Pro_API {

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
	public $name = 'pexels';

	/**
	 * Holds the API endpoint
	 *
	 * @since   2.2.9
	 *
	 * @var     string
	 */
	public $api_endpoint = 'https://api.pexels.com';

	/**
	 * Holds the API Key
	 *
	 * @since   2.2.9
	 *
	 * @var     string
	 */
	public $api_key = '563492ad6f9170000100000113545ff30aa14515888b11a213970c6f';

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

		// Register Settings Fields.
		add_filter( 'page_generator_pro_integrations_get', array( $this, 'register_integration' ) );
		add_filter( 'page_generator_pro_integrations_get_settings_fields', array( $this, 'get_settings_fields' ) );

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
	 * @since   4.5.2
	 *
	 * @return  string
	 */
	public function get_title() {

		return __( 'Pexels', 'page-generator-pro' );

	}

	/**
	 * Returns settings fields and their values to display on:
	 * - Settings > Integrations
	 *
	 * @since   4.8.0
	 *
	 * @param   array $settings_fields    Settings Fields.
	 * @return  array                     Settings Fields
	 */
	public function get_settings_fields( $settings_fields ) {

		// Define fields and their values.
		$settings_fields[ $this->name ] = array(
			$this->get_settings_prefix() . '_api_key' => array(
				'label'         => sprintf(
					/* translators: %1$s: Integration name, %2$s Field label */
					'%1$s: %2$s',
					$this->get_title(),
					__( 'API Key', 'page-generator-pro' )
				),
				'type'          => 'text',
				'default_value' => $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-integrations', $this->get_settings_prefix() . '_api_key' ),
				'description'   => sprintf(
					'%s %s %s',
					esc_html__( 'If you reach an API limit when attempting to import images from Pexels, you\'ll need to use your own free Pexels API key.', 'page-generator-pro' ),
					'<a href="' . esc_attr( $this->base->plugin->documentation_url ) . '/settings-integration/#pexels" target="_blank" rel="noopener">' . esc_html__( 'Click here', 'page-generator-pro' ) . '</a>',
					esc_html__( 'to read the step by step documentation to do this.', 'page-generator-pro' )
				),
			),
		);

		return $settings_fields;

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
			'term'        => array(
				'label'       => __( 'Term', 'page-generator-pro' ),
				'type'        => 'autocomplete',
				'values'      => $this->keywords,
				'placeholder' => __( 'e.g. building', 'page-generator-pro' ),
				'description' => __( 'The search term to use.  For example, "laptop" would return an image of a laptop.', 'page-generator-pro' ),
			),
			'orientation' => array( // No prefix key is defined here. This is deliberate; `orientation` is shared with Pexels.
				'label'         => __( 'Image Orientation', 'page-generator-pro' ),
				'type'          => 'select',
				'values'        => $this->get_image_orientations(),
				'default_value' => $this->get_default_value( 'orientation' ),
				'description'   => __( 'The image orientation to output.', 'page-generator-pro' ),
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

		return array();

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
			// Search.
			'orientation' => 0,
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

		return __( 'Displays an image from Pexels, based on the given search parameters.', 'page-generator-pro' );

	}

	/**
	 * Returns this shortcode / block's icon.
	 *
	 * @since   4.5.2
	 *
	 * @return  string
	 */
	public function get_icon() {

		return 'assets/images/icons/pexels.svg';

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

		// If a Pexels API Key has been specified, use it instead of the class default.
		$api_key = $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-integrations', 'pexels_api_key' );
		if ( ! empty( $api_key ) ) {
			$this->set_api_key( $api_key );
		}

		// Define the number of images to return to then choose one at random from.
		$per_page = 80;

		// Run query to fetch total number of pages of results that are available.
		$page_count = $this->page_count( $atts['term'], 'original', $atts['orientation'], $per_page );

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

		// If we're not copying the image to the Media Library, get a sensible size.
		// If we're copying the image to the Media Library, get the maximum possible size as we'll resize it.
		$size = ( $atts['copy'] ? 'original' : 'large' );

		// Run images query.
		$images = $this->photos_search( $atts['term'], $size, $atts['orientation'], $per_page, $page_index );

		// Handle errors.
		if ( is_wp_error( $images ) ) {
			return $this->add_dynamic_element_error_and_return( $images, $atts );
		}

		// Pick an image at random from the resultset.
		$image = $this->choose_random_image( $images );

		// Import the image.
		$image_id = false;
		if ( $atts['copy'] ) {
			$image_id = $this->import( $image, $atts );

			// Bail if an error occured.
			if ( is_wp_error( $image_id ) ) {
				return $this->add_dynamic_element_error_and_return( $image_id, $atts );
			}
		}

		// Get HTML image tag, with the image matching the given WordPress registered image size.
		$html = $this->get_image_html( $image_id, $atts, $image );

		/**
		 * Filter the Pexels HTML output, before returning.
		 *
		 * @since   1.0.0
		 *
		 * @param   string      $html       HTML Output.
		 * @param   array       $atts       Shortcode Attributes.
		 * @param   bool|int    $image_id   WordPress Media Library Image ID (false = not imported into Media Library as copy=0).
		 * @param   array       $images     Pexels Image Results.
		 * @param   array       $image      Pixabay Image chosen at random and imported into the Media Library.
		 */
		$html = apply_filters( 'page_generator_pro_shortcode_pexels', $html, $atts, $image_id, $images, $image );

		// Return.
		return $html;

	}

	/**
	 * Defines the Featured Image for the given generated Post ID, if
	 * the Group Settings specify a Featured Image.
	 *
	 * @since   2.3.5
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
				$settings['featured_image_orientation'] = 'portrait';
				break;

			case 'landscape':
			case 'horizontal':
			case 'wide':
				$settings['featured_image_orientation'] = 'landscape';
				break;
		}

		// If a Pexels API Key has been specified, use it instead of the class default.
		$api_key = $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-integrations', $this->get_settings_prefix() . '_api_key' );
		if ( ! empty( $api_key ) ) {
			$this->set_api_key( $api_key );
		}

		// Define the number of images to return to then choose one at random from.
		$per_page = 80;

		// Run query to fetch total number of pages of results that are available.
		$page_count = $this->page_count(
			$settings['featured_image_term'],
			'original',
			$settings['featured_image_orientation'],
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
			'original',
			$settings['featured_image_orientation'],
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
	 * Returns an array of image orientations supported
	 * by the API.
	 *
	 * @since   2.2.9
	 *
	 * @return  array   Supported Image Orientations
	 */
	public function get_image_orientations() {

		return array(
			0           => __( 'Any', 'page-generator-pro' ),
			'portrait'  => __( 'Portrait', 'page-generator-pro' ),
			'landscape' => __( 'Landscape', 'page-generator-pro' ),
		);

	}

	/**
	 * Searches photos based on the given query
	 *
	 * @since   2.2.9
	 *
	 * @param   string     $query          Search Term(s).
	 * @param   string     $size           Image Size (original, large, large2x, medium, small, tiny).
	 * @param   bool|array $orientation    Image Orientation (false, portrait, landscape).
	 * @param   int        $per_page       Number of Images to Return.
	 * @param   int        $page           Pagination Page Offset.
	 * @return  WP_Error|array
	 */
	public function photos_search( $query, $size = 'original', $orientation = false, $per_page = 80, $page = 1 ) {

		// Perform search.
		$results = $this->search( $query, $size, $orientation, $per_page, $page );

		// Bail if an error occured.
		if ( is_wp_error( $results ) ) {
			return new WP_Error(
				'page_generator_pro_pexels_error',
				sprintf(
					/* translators: Error message */
					__( 'photos_search(): %s', 'page-generator-pro' ),
					$results->get_error_message()
				)
			);
		}

		// Determine whether to fetch by orientation or size.
		switch ( $orientation ) {
			case 'portrait':
			case 'landscape':
				$photo_type = $orientation;
				break;

			default:
				$photo_type = 'original';
				break;
		}

		// Parse results.
		$images = array();
		foreach ( $results->photos as $photo ) {
			// Creator.
			if ( isset( $photo->photographer ) ) {
				/* translators: Photographer's Name */
				$creator = sprintf( __( '%s on Pexels', 'page-generator-pro' ), $photo->photographer );
			} else {
				$creator = false;
			}

			$images[] = array(
				// original, large, large2x, medium, small, portrait, landscape, tiny.
				'url'             => $photo->src->{ $photo_type },
				'title'           => $photo->photographer,

				// Credits.
				'source'          => $photo->url,
				'creator'         => $creator,
				'creator_url'     => ( isset( $photo->photographer_url ) ? $photo->photographer_url : false ),
				'license'         => false,
				'license_version' => false,
				'license_url'     => false,
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
	 * @param   string     $query          Search Term(s).
	 * @param   string     $size           Image Size (original, large, large2x, medium, small, tiny).
	 * @param   bool|array $orientation    Image Orientation (false, portrait, landscape).
	 * @param   int        $per_page       Number of Images to Return.
	 * @param   int        $page           Pagination Page Offset.
	 * @return  WP_Error|int
	 */
	public function page_count( $query, $size = 'original', $orientation = false, $per_page = 80, $page = 1 ) {

		// Perform search.
		$results = $this->search( $query, $size, $orientation, $per_page, $page );

		// Bail if an error occured.
		if ( is_wp_error( $results ) ) {
			return new WP_Error(
				'page_generator_pro_pexels_error',
				sprintf(
					/* translators: Error message */
					__( 'page_count(): %s', 'page-generator-pro' ),
					$results->get_error_message()
				)
			);
		}

		// If total results exceeds 8,000, reduce it as this results in a page count too high
		// that fails when calling photos_search(), even though there might be results.
		if ( $results->total_results > 8000 ) {
			$total_results = 8000;
		} else {
			$total_results = $results->total_results;
		}

		return (int) ceil( $total_results / $results->per_page );

	}

	/**
	 * Searches photos based on the given query
	 *
	 * @since   2.2.9
	 *
	 * @param   string     $query          Search Term(s).
	 * @param   string     $size           Image Size (original, large, large2x, medium, small, tiny).
	 * @param   bool|array $orientation    Image Orientation (false, portrait, landscape).
	 * @param   int        $per_page       Number of Images to Return.
	 * @param   int        $page           Pagination Page Offset.
	 * @return  WP_Error|stdClass
	 */
	private function search( $query, $size = 'original', $orientation = false, $per_page = 80, $page = 1 ) {

		// Set HTTP headers.
		$this->set_headers(
			array(
				'Authorization' => $this->api_key,
			)
		);

		// Build array of arguments  .
		$args = array(
			'query'    => $query,
			'per_page' => $per_page,
			'page'     => $page,
		);

		/**
		 * Filters the API arguments to send to the Pexels /search endpoint
		 *
		 * @since   2.2.9
		 *
		 * @param   array       $args           API arguments.
		 * @param   string      $query          Search Term(s).
		 * @param   string      $size           Image Size (original, large, large2x, medium, small, portrait, landscape, tiny).
		 * @param   bool|array  $orientation    Image Orientation (false, portrait, landscape).
		 * @param   int         $per_page       Number of Images to Return.
		 * @param   int         $page           Pagination Page Offset.
		 */
		$args = apply_filters( 'page_generator_pro_pexels_photos_search_args', $args, $query, $size, $orientation, $per_page, $page );

		// Run the query.
		$results = $this->get( 'v1/search', $args );

		// Bail if an error occured.
		if ( is_wp_error( $results ) ) {
			return $results;
		}

		// Bail if an error is in the response.
		if ( isset( $results->error ) ) {
			return new WP_Error(
				'page_generator_pro_pexels_error',
				$results->error
			);
		}

		// Bail if no results were found.
		if ( ! $results->total_results ) { // @phpstan-ignore-line
			return new WP_Error(
				'page_generator_pro_pexels_error',
				__( 'No results were found for the given search criteria.', 'page-generator-pro' )
			);
		}

		// Return results.
		return $results;

	}

}
