<?php
/**
 * Image URL Class.
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Fetch images from a given URL, outputting or storing in the Media Library.
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 4.8.0
 */
class Page_Generator_Pro_Image_URL {

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
	public $name = 'image-url';

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
		add_filter( 'page_generator_pro_generate_featured_image_' . $this->name, array( $this, 'get_featured_image' ), 10, 5 );

	}

	/**
	 * Returns this shortcode / block's title.
	 *
	 * @since   4.5.2
	 *
	 * @return  string
	 */
	public function get_title() {

		return __( 'Image URL', 'page-generator-pro' );

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
	public function get_provider_search_fields( $is_featured_image = false ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found

		// Don't populate fields for performance if the request is for the frontend web site.
		// Populate fields for admin and CLI requests so that Generate via Browser and CLI
		// will see fields for this shortcode, which is required for correct operation with e.g. Elementor
		// registered shortcodes/elements.
		if ( ! $this->base->is_admin_or_frontend_editor() && ! $this->base->is_cli() && ! $this->base->is_cron() ) {
			return array();
		}

		// Return fields.
		return array(
			'url' => array(
				'label'       => __( 'URL', 'page-generator-pro' ),
				'type'        => 'autocomplete',
				'values'      => $this->keywords,
				'placeholder' => __( 'e.g. https://placehold.co/600x400.jpg', 'page-generator-pro' ),
				'description' => __( 'The image URL to use.', 'page-generator-pro' ),
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
	public function get_provider_output_fields( $is_featured_image = false ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found

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
			'url' => array(
				'type'    => 'string',
				'default' => ( ! $this->get_default_value( 'url' ) ? '' : $this->get_default_value( 'url' ) ),
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
	public function get_provider_default_values( $is_featured_image = false ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found

		return array(
			'url' => '',
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

		return __( 'Displays an image from an image URL.', 'page-generator-pro' );

	}

	/**
	 * Returns this shortcode / block's icon.
	 *
	 * @since   4.5.2
	 *
	 * @return  string
	 */
	public function get_icon() {

		return '_modules/dashboard/feather/image.svg';

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

		// Import the image.
		$image_id = $this->import_remote_image(
			$atts['url'],
			0,
			$this->base->get_class( 'shortcode' )->get_group_id(),
			$this->base->get_class( 'shortcode' )->get_index(),
			$atts['filename'],
			( ! $atts['title'] ? '' : $atts['title'] ), // title.
			( ! $atts['caption'] ? '' : $atts['caption'] ), // caption.
			( ! $atts['alt_tag'] ? '' : $atts['alt_tag'] ), // alt_tag.
			( ! $atts['description'] ? '' : $atts['description'] ) // description.
		);

		// Bail if an error occured.
		if ( is_wp_error( $image_id ) ) {
			return $this->add_dynamic_element_error_and_return( $image_id, $atts );
		}

		// Store EXIF Data in Image.
		$this->base->get_class( 'exif' )->write(
			$image_id,
			$atts['exif_description'],
			$atts['exif_comments'],
			$atts['exif_latitude'],
			$atts['exif_longitude']
		);

		// Get HTML image tag, with the image matching the given WordPress registered image size.
		$html = $this->get_image_html( $image_id, $atts );

		/**
		 * Filter the Image URL HTML output, before returning.
		 *
		 * @since   1.0.0
		 *
		 * @param   string  $html       HTML Output.
		 * @param   array   $atts       Shortcode Attributes.
		 * @param   int     $image_id   WordPress Media Library Image ID.
		 */
		$html = apply_filters( 'page_generator_pro_shortcode_image_url', $html, $atts, $image_id );

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
	 * @return  WP_Error|bool|int
	 */
	public function get_featured_image( $image_id, $post_id, $group_id, $index, $settings ) {

		// Bail if no Featured Image URL specified.
		if ( empty( $settings['featured_image_url'] ) ) {
			return new WP_Error( 'page_generator_pro_generate', __( 'Featured Image: No URL was specified.', 'page-generator-pro' ) );
		}

		// Import Image into the Media Library.
		return $this->import_remote_image(
			$settings['featured_image_url'],
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

}
