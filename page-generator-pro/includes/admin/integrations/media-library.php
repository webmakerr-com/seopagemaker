<?php
/**
 * Media Library Class.
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Fetch images from the Media Library based on given criteria.
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 4.8.0
 */
class Page_Generator_Pro_Media_Library {

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
	public $name = 'media-library';

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
	 * Returns provider-specific default values across the Dynamic Element and Featured Image.
	 *
	 * @since   4.8.0
	 *
	 * @param   bool $is_featured_image Fields are for Featured Image functionality.
	 * @return  array
	 */
	public function get_provider_default_values( $is_featured_image = false ) {

		$prefix_key        = ( $is_featured_image ? $this->get_settings_prefix() . '_' : '' );
		$output_prefix_key = ( $is_featured_image ? '' : 'output_' );

		return array(
			// Search Parameters.
			$prefix_key . 'title'              => '',
			$prefix_key . 'caption'            => '',
			$prefix_key . 'alt_tag'            => '',
			$prefix_key . 'description'        => '',
			$prefix_key . 'filename'           => '',
			$prefix_key . 'operator'           => '',
			$prefix_key . 'ids'                => '',
			$prefix_key . 'min_id'             => '',
			$prefix_key . 'max_id'             => '',

			// Output.
			'copy'                             => '',
			$output_prefix_key . 'title'       => '',
			$output_prefix_key . 'caption'     => '',
			$output_prefix_key . 'alt_tag'     => '',
			$output_prefix_key . 'description' => '',
			$output_prefix_key . 'filename'    => '',
		);

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

		// Don't populate fields for performance if the request is for the frontend web site.
		// Populate fields for admin and CLI requests so that Generate via Browser and CLI
		// will see fields for this shortcode, which is required for correct operation with e.g. Elementor
		// registered shortcodes/elements.
		if ( ! $this->base->is_admin_or_frontend_editor() && ! $this->base->is_cli() && ! $this->base->is_cron() ) {
			return array();
		}

		// Load Keywords class.
		$keywords_class = $this->base->get_class( 'keywords' );

		// Bail if the Keywords class could not be loaded.
		if ( is_wp_error( $keywords_class ) ) {
			return array();
		}

		// Fetch Keywords.
		$keywords = $keywords_class->get_keywords_and_columns( true );

		return $this->get_media_library_search_fields( $keywords, $is_featured_image );

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

		// Don't populate fields for performance if the request is for the frontend web site.
		// Populate fields for admin and CLI requests so that Generate via Browser and CLI
		// will see fields for this shortcode, which is required for correct operation with e.g. Elementor
		// registered shortcodes/elements.
		if ( ! $this->base->is_admin_or_frontend_editor() && ! $this->base->is_cli() && ! $this->base->is_cron() ) {
			return array();
		}

		// Load Keywords class.
		$keywords_class = $this->base->get_class( 'keywords' );

		// Bail if the Keywords class could not be loaded.
		if ( is_wp_error( $keywords_class ) ) {
			return array();
		}

		// Fetch Keywords.
		$keywords = $keywords_class->get_keywords_and_columns( true );

		return $this->get_media_library_output_fields( $keywords, $is_featured_image );

	}

	/**
	 * Returns this shortcode / block's title.
	 *
	 * @since   2.5.1
	 *
	 * @return  string
	 */
	public function get_title() {

		return __( 'Media Library', 'page-generator-pro' );

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

		return 'assets/images/icons/admin-media.svg';

	}

	/**
	 * Returns this block's Attributes and default values for Gutenberg.
	 *
	 * @since   3.6.3
	 */
	public function get_attributes() {

		// Get output attributes (title, caption, alt_tag), as these are used as the Search Parameters.
		$search_parameter_attributes = $this->get_output_attributes();
		unset( $search_parameter_attributes['caption_display'] );

		return array_merge(
			// Search Parameters.
			$search_parameter_attributes,
			array(
				'operator' => array(
					'type'    => 'string',
					'default' => ( ! $this->get_default_value( 'operator' ) ? '' : $this->get_default_value( 'operator' ) ),
				),
				'ids'      => array(
					'type'    => 'string',
					'default' => ( ! $this->get_default_value( 'ids' ) ? '' : $this->get_default_value( 'ids' ) ),
				),
				'min_id'   => array(
					'type'    => 'number',
					'default' => $this->get_default_value( 'min_id' ),
				),
				'max_id'   => array(
					'type'    => 'number',
					'default' => $this->get_default_value( 'max_id' ),
				),
			),
			// Output.
			array(
				'copy'                   => array(
					'type'    => 'boolean',
					'default' => $this->get_default_value( 'copy' ),
				),
				'size'                   => array(
					'type'    => 'string',
					'default' => ( ! $this->get_default_value( 'size' ) ? '' : $this->get_default_value( 'size' ) ),
				),
				'output_title'           => array(
					'type'    => 'string',
					'default' => ( ! $this->get_default_value( 'output_title' ) ? '' : $this->get_default_value( 'output_title' ) ),
				),
				'output_caption'         => array(
					'type'    => 'string',
					'default' => ( ! $this->get_default_value( 'output_caption' ) ? '' : $this->get_default_value( 'output_caption' ) ),
				),
				'output_caption_display' => array(
					'type'    => 'boolean',
					'default' => $this->get_default_value( 'output_caption_display' ),
				),
				'output_alt_tag'         => array(
					'type'    => 'string',
					'default' => ( ! $this->get_default_value( 'output_alt_tag' ) ? '' : $this->get_default_value( 'output_alt_tag' ) ),
				),
				'output_description'     => array(
					'type'    => 'string',
					'default' => ( ! $this->get_default_value( 'output_description' ) ? '' : $this->get_default_value( 'output_description' ) ),
				),
				'output_filename'        => array(
					'type'    => 'string',
					'default' => ( ! $this->get_default_value( 'output_filename' ) ? '' : $this->get_default_value( 'output_filename' ) ),
				),
			),
			// Link.
			$this->get_link_attributes(),
			// EXIF.
			$this->get_exif_attributes(),
			// Errors.
			$this->get_ignore_errors_attributes(),
			// Preview.
			array(
				'is_gutenberg_example' => array(
					'type'    => 'boolean',
					'default' => false,
				),
			),
		);

	}

	/**
	 * Returns this shortcode / block's Fields
	 *
	 * @since   2.5.1
	 */
	public function get_fields() {

		// Don't populate fields for performance if the request is for the frontend web site.
		// Populate fields for admin and CLI requests so that Generate via Browser and CLI
		// will see fields for this shortcode, which is required for correct operation with e.g. Elementor
		// registered shortcodes/elements.
		if ( ! $this->base->is_admin_or_frontend_editor() && ! $this->base->is_cli() && ! $this->base->is_cron() ) {
			return false;
		}

		// Load Keywords class.
		$keywords_class = $this->base->get_class( 'keywords' );

		// Bail if the Keywords class could not be loaded.
		if ( is_wp_error( $keywords_class ) ) {
			return $fields;
		}

		// Fetch Keywords.
		$keywords = $keywords_class->get_keywords_and_columns( true );

		return array_merge(
			$this->get_media_library_search_fields( $keywords ),
			$this->get_media_library_output_fields( $keywords ),
			$this->get_link_fields(),
			$this->get_exif_fields( true ),
			$this->get_ignore_errors_fields(),
		);

	}

	/**
	 * Returns Search Parameters fields.
	 *
	 * @since   4.8.0
	 *
	 * @param   array $keywords           Keywords.
	 * @param   bool  $is_featured_image  Fields are for Featured Image functionality.
	 * @return  array
	 */
	private function get_media_library_search_fields( $keywords, $is_featured_image = false ) {

		$prefix_key = ( $is_featured_image ? $this->get_settings_prefix() . '_' : '' );

		return array(
			$prefix_key . 'title'       => array(
				'label'       => __( 'Title', 'page-generator-pro' ),
				'type'        => 'autocomplete',
				'values'      => $keywords,
				'description' => __( 'Fetch an image at random with a partial or full match to the given Title.', 'page-generator-pro' ),
			),
			$prefix_key . 'caption'     => array(
				'label'       => __( 'Caption', 'page-generator-pro' ),
				'type'        => 'autocomplete',
				'values'      => $keywords,
				'description' => __( 'Fetch an image at random with a partial or full match to the given Caption.', 'page-generator-pro' ),
			),
			$prefix_key . 'alt_tag'     => array(
				'label'       => __( 'Alt Text', 'page-generator-pro' ),
				'type'        => 'autocomplete',
				'values'      => $keywords,
				'description' => __( 'Fetch an image at random with a partial or full match to the given Alt Text.', 'page-generator-pro' ),
			),
			$prefix_key . 'description' => array(
				'label'       => __( 'Description', 'page-generator-pro' ),
				'type'        => 'autocomplete',
				'values'      => $keywords,
				'description' => __( 'Fetch an image at random with a partial or full match to the given Description.', 'page-generator-pro' ),
			),
			$prefix_key . 'filename'    => array(
				'label'       => __( 'Filename', 'page-generator-pro' ),
				'type'        => 'autocomplete',
				'values'      => $keywords,
				'description' => __( 'Fetch an image at random with a partial or full match to the given Filename.', 'page-generator-pro' ),
			),
			$prefix_key . 'operator'    => array(
				'label'         => __( 'Operator', 'page-generator-pro' ),
				'type'          => 'select',
				'description'   => __( 'Determines whether images should match all or any of the Title, Caption, Alt Text and Descriptions specified above.', 'page-generator-pro' ),
				'values'        => $this->base->get_class( 'common' )->get_operator_options(),
				'default_value' => $this->get_default_value( 'operator' ),
			),
			$prefix_key . 'ids'         => array(
				'label'       => __( 'Image IDs', 'page-generator-pro' ),
				'type'        => 'text',
				'description' => __( 'Comma separated list of Media Library Image ID(s) to use.  If multiple image IDs are specified, one will be chosen at random for each generated Page.', 'page-generator-pro' ),
			),
			$prefix_key . 'min_id'      => array(
				'label'       => __( 'Minimum Image ID', 'page-generator-pro' ),
				'type'        => 'number',
				'min'         => 0,
				'max'         => 9999999,
				'step'        => 1,
				'description' => __( 'Fetch an image whose ID matches or is greater than the given value.', 'page-generator-pro' ),
			),
			$prefix_key . 'max_id'      => array(
				'label'       => __( 'Maximum Image ID', 'page-generator-pro' ),
				'type'        => 'number',
				'min'         => 0,
				'max'         => 9999999,
				'step'        => 1,
				'description' => __( 'Fetch an image whose ID matches or is less than the given value.', 'page-generator-pro' ),
			),
		);

	}

	/**
	 * Returns Output fields.
	 *
	 * @since   4.8.0
	 *
	 * @param   array $keywords           Keywords.
	 * @param   bool  $is_featured_image  Fields are for Featured Image functionality.
	 * @return  array
	 */
	public function get_media_library_output_fields( $keywords, $is_featured_image = false ) {

		// Prefix key is deliberately different here.
		// Dynamic Element prefixes with e.g. output_title, as Search Parameters are e.g. title.
		// Featured Image does not prefix e.g. title, as Search Parameters are e.g. media_library_title.
		$prefix_key = ( $is_featured_image ? '' : 'output_' );

		$fields = array(
			'copy' => array(
				'label'         => __( 'Create as Copy', 'page-generator-pro' ),
				'type'          => 'toggle',
				'class'         => 'wpzinc-conditional',
				'data'          => array(
					// .components-panel is Gutenberg.
					// .{$this->get_name()} is TinyMCE.
					'container' => '.components-panel, .' . $this->get_name(),
				),
				'description'   => __( 'If enabled, stores the found image in the Media Library. Additional attributes, such as Caption, Filename and EXIF metadata can then be set.', 'page-generator-pro' ),
				'default_value' => $this->get_default_value( 'copy' ),
			),
		);

		if ( ! $is_featured_image ) {
			$fields = array_merge(
				$fields,
				array(
					'size' => array(
						'label'         => __( 'Image Size', 'page-generator-pro' ),
						'type'          => 'select',
						'description'   => __( 'The image size to output.', 'page-generator-pro' ),
						'values'        => $this->base->get_class( 'common' )->get_media_library_image_size_options(),
						'default_value' => $this->get_default_value( 'size' ),
					),
				)
			);
		}

		$fields = array_merge(
			$fields,
			array(
				$prefix_key . 'title'   => array(
					'label'       => __( 'Title', 'page-generator-pro' ),
					'type'        => 'autocomplete',
					'values'      => $keywords,
					'description' => __( 'Define the title for the image.', 'page-generator-pro' ),
				),
				$prefix_key . 'caption' => array(
					'label'       => __( 'Caption', 'page-generator-pro' ),
					'type'        => 'autocomplete',
					'values'      => $keywords,
					'description' => __( 'Define the caption for the image.', 'page-generator-pro' ),
					'condition'   => array(
						'key'        => 'copy',
						'value'      => 1,
						'comparison' => '==',
					),
				),
			)
		);

		if ( ! $is_featured_image ) {
			$fields = array_merge(
				$fields,
				array(
					$prefix_key . 'caption_display' => array(
						'label'         => __( 'Display Caption', 'page-generator-pro' ),
						'type'          => 'toggle',
						'description'   => __( 'Display the caption below the image.', 'page-generator-pro' ),
						'default_value' => $this->get_default_value( 'output_caption_display' ),
						'condition'     => array(
							'key'        => 'copy',
							'value'      => 1,
							'comparison' => '==',
						),
					),
				)
			);
		}

		$fields = array_merge(
			$fields,
			array(
				$prefix_key . 'alt_tag'     => array(
					'label'       => __( 'Alt Tag', 'page-generator-pro' ),
					'type'        => 'autocomplete',
					'values'      => $keywords,
					'description' => __( 'Define the alt text for the image.', 'page-generator-pro' ),
				),
				$prefix_key . 'description' => array(
					'label'       => __( 'Description', 'page-generator-pro' ),
					'type'        => 'autocomplete',
					'values'      => $keywords,
					'description' => __( 'Define the description for the image.', 'page-generator-pro' ),
					'condition'   => array(
						'key'        => 'copy',
						'value'      => 1,
						'comparison' => '==',
					),
				),
				$prefix_key . 'filename'    => array(
					'label'       => __( 'Filename', 'page-generator-pro' ),
					'type'        => 'autocomplete',
					'values'      => $keywords,
					'description' => __( 'Define the filename for the image, excluding the extension.', 'page-generator-pro' ),
					'condition'   => array(
						'key'        => 'copy',
						'value'      => 1,
						'comparison' => '==',
					),
				),
			)
		);

		return $fields;

	}

	/**
	 * Returns this shortcode / block's UI Tabs
	 *
	 * @since   2.5.1
	 */
	public function get_tabs() {

		if ( ! $this->base->is_admin_or_frontend_editor() ) {
			return false;
		}

		return array_merge(
			array(
				'search-parameters' => array(
					'label'       => __( 'Search Parameters', 'page-generator-pro' ),
					'description' => __( 'Defines search query parameters to fetch an image at random from the Media Library.', 'page-generator-pro' ),
					'class'       => 'search',
					'fields'      => array(
						'title',
						'caption',
						'alt_tag',
						'description',
						'filename',
						'operator',
						'ids',
						'min_id',
						'max_id',
					),
				),
				'output'            => array(
					'label'       => __( 'Output', 'page-generator-pro' ),
					'description' => __( 'Defines output parameters for the Media Library image.', 'page-generator-pro' ),
					'class'       => 'image',
					'fields'      => array(
						'copy',
						'size',
						'output_title',
						'output_alt_tag',
						'output_caption',
						'output_caption_display',
						'output_description',
						'output_filename',
					),
				),
				'link'              => array(
					'label'       => __( 'Link', 'page-generator-pro' ),
					'description' => __( 'Defines parameters for linking the image.', 'page-generator-pro' ),
					'class'       => 'link',
					'fields'      => array_keys( $this->get_link_attributes() ),
				),
				'exif'              => array(
					'label'       => __( 'EXIF', 'page-generator-pro' ),
					'description' => __( 'Defines EXIF metadata to store in the image.', 'page-generator-pro' ),
					'class'       => 'aperture',
					'fields'      => array_keys( $this->get_exif_attributes() ),
				),
			),
			$this->get_ignore_errors_tabs()
		);

	}

	/**
	 * Returns this shortcode / block's Default Values
	 *
	 * @since   2.5.1
	 */
	public function get_default_values() {

		return array_merge(
			// Search Parameters.
			$this->get_output_default_values(),
			array(
				'operator' => 'OR',
				'ids'      => false,
				'min_id'   => 0,
				'max_id'   => 0,
			),
			// Output.
			array(
				'size'                   => 'large',
				'copy'                   => false,
				'output_title'           => false,
				'output_description'     => false,
				'output_caption'         => false,
				'output_caption_display' => false,
				'output_alt_tag'         => false,
				'output_filename'        => false,
			),
			// Link.
			$this->get_link_default_values(),
			// EXIF.
			$this->get_exif_default_values(),
			// Errors.
			$this->get_ignore_errors_default_values()
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

		// Get Random Image ID matching attributes.
		$image_id = $this->get_random_image_id( $atts );

		// Bail if an error occured.
		if ( is_wp_error( $image_id ) ) {
			return $this->add_dynamic_element_error_and_return( $image_id, $atts );
		}

		// Build array of output attributes compatible with import() and get_image_html().
		// We don't use e.g. $atts['title'], as for this Dynamic Element, those are for
		// searching the Media Library - so we must use output_* keys and map them.
		$output_atts = array(
			'size'             => $atts['size'],
			'title'            => $atts['output_title'],
			'caption'          => $atts['output_caption'],
			'caption_display'  => $atts['output_caption_display'],
			'alt_tag'          => $atts['output_alt_tag'],
			'description'      => $atts['output_description'],
			'filename'         => $atts['output_filename'],

			'link_href'        => $atts['link_href'],
			'link_title'       => $atts['link_title'],
			'link_rel'         => $atts['link_rel'],
			'link_target'      => $atts['link_target'],

			'exif_description' => $atts['exif_description'],
			'exif_comments'    => $atts['exif_comments'],
			'exif_latitude'    => $atts['exif_latitude'],
			'exif_longitude'   => $atts['exif_longitude'],
		);

		// If copy if enabled, import the image into the Media Library, saving the Title, Caption,
		// Alt Tag, Description and EXIF metadata, if required.
		if ( $atts['copy'] ) {
			// Get image.
			$image = wp_get_attachment_image_src( $image_id, 'full' );

			// Import to Media Library.
			$image_id = $this->import(
				array(
					'title' => $output_atts['title'],
					'url'   => $image[0],
				),
				$output_atts
			);

			// Bail if an error occured.
			if ( is_wp_error( $image_id ) ) {
				return $this->add_dynamic_element_error_and_return( $image_id, $atts );
			}
		}

		// Get HTML image tag.
		$html = $this->get_image_html( $image_id, $output_atts );

		/**
		 * Filter the Media Library HTML output, before returning.
		 *
		 * @since   1.0.0
		 *
		 * @param   string  $html   HTML Output.
		 * @param   array   $atts   Shortcode Attributes.
		 */
		$html = apply_filters( 'page_generator_pro_shortcode_media_library', $html, $atts );

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
	public function get_featured_image( $image_id, $post_id, $group_id, $index, $settings, $post_args ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed

		// Build Featured Image Search Arguments.
		$search_args = array(
			'title'       => ( ! empty( $settings['featured_image_media_library_title'] ) ? $settings['featured_image_media_library_title'] : false ),
			'caption'     => ( ! empty( $settings['featured_image_media_library_caption'] ) ? $settings['featured_image_media_library_caption'] : false ),
			'alt_tag'     => ( ! empty( $settings['featured_image_media_library_alt_tag'] ) ? $settings['featured_image_media_library_alt_tag'] : false ),
			'description' => ( ! empty( $settings['featured_image_media_library_description'] ) ? $settings['featured_image_media_library_description'] : false ),
			'operator'    => ( ! empty( $settings['featured_image_media_library_operator'] ) ? $settings['featured_image_media_library_operator'] : 'AND' ),
			'ids'         => ( ! empty( $settings['featured_image_media_library_ids'] ) ? $settings['featured_image_media_library_ids'] : false ),
			'min_id'      => ( ! empty( $settings['featured_image_media_library_min_id'] ) ? $settings['featured_image_media_library_min_id'] : false ),
			'max_id'      => ( ! empty( $settings['featured_image_media_library_max_id'] ) ? $settings['featured_image_media_library_max_id'] : false ),
		);

		// Get Image ID.
		$image_id = $this->get_random_image_id( $search_args );

		// Return the error if a WP_Error.
		if ( is_wp_error( $image_id ) ) {
			return $image_id;
		}

		// If we're copying the image to a new Media Library attachment, do this now.
		if ( $settings['featured_image_copy'] ) {
			// Get image.
			$image = wp_get_attachment_image_src( $image_id, 'full' );
			if ( ! $image ) {
				return new WP_Error( 'page_generator_pro_generate_featured_image', __( 'Featured Image: Could not get Image ID\'s source', 'page-generator-pro' ) );
			}

			// Copy to new image.
			return $this->import_remote_image(
				$image[0],
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

		return $image_id;

	}

	/**
	 * Returns an image ID at random based on the given parameters
	 *
	 * @since   1.8.0
	 *
	 * @param   array $args   Attributes.
	 * @return  WP_Error|int
	 */
	public function get_random_image_id( $args ) {

		global $wpdb;

		// Define query parameters that are always AND clauses.
		$query = array(
			$wpdb->prefix . "posts.post_type = 'attachment'",
			$wpdb->prefix . "posts.post_status = 'inherit'",
			$wpdb->prefix . "posts.post_mime_type LIKE 'image/%'",
		);
		if ( array_key_exists( 'ids', $args ) && $args['ids'] !== false && ! empty( $args['ids'] ) ) {
			// Remove spaces and leading/trailing commas.
			$ids = explode( ',', $args['ids'] );
			foreach ( $ids as $index => $id ) {
				$ids[ $index ] = trim( $ids[ $index ] );
				$ids[ $index ] = trim( $ids[ $index ], ',' );
				if ( empty( $ids[ $index ] ) ) {
					unset( $ids[ $index ] );
				}
			}

			$query[] = $wpdb->prefix . 'posts.ID IN (' . implode( ',', $ids ) . ')';
		}
		if ( array_key_exists( 'min_id', $args ) && $args['min_id'] !== false && ! empty( $args['min_id'] ) ) {
			$query[] = $wpdb->prefix . 'posts.ID >= ' . $args['min_id'];
		}
		if ( array_key_exists( 'max_id', $args ) && $args['max_id'] !== false && ! empty( $args['max_id'] ) ) {
			$query[] = $wpdb->prefix . 'posts.ID <= ' . $args['max_id'];
		}
		if ( array_key_exists( 'filename', $args ) && $args['filename'] !== false && ! empty( $args['filename'] ) ) {
			$query[] = $wpdb->prefix . "posts.guid LIKE '%" . $args['filename'] . "%'";
		}

		// Define query parameters that can be AND / OR clauses.
		$query_meta = array();
		if ( array_key_exists( 'title', $args ) && $args['title'] !== false && ! empty( $args['title'] ) ) {
			$query_meta[] = $wpdb->prefix . "posts.post_title LIKE '%" . $args['title'] . "%'";
		}

		// `alt` exists in < 4.8.0, and is retained for shortcodes that might still specify it.
		// It never worked in Gutenberg, as the attribute was always `alt_tag`, which is now
		// the attribute used across shortcode + block.
		if ( array_key_exists( 'alt', $args ) && $args['alt'] !== false && ! empty( $args['alt'] ) ) {
			$query_meta[] = '(' . $wpdb->prefix . "postmeta.meta_key = '_wp_attachment_image_alt' AND " . $wpdb->prefix . "postmeta.meta_value LIKE '%" . $args['alt'] . "%')";
		}

		if ( array_key_exists( 'alt_tag', $args ) && $args['alt_tag'] !== false && ! empty( $args['alt_tag'] ) ) {
			$query_meta[] = '(' . $wpdb->prefix . "postmeta.meta_key = '_wp_attachment_image_alt' AND " . $wpdb->prefix . "postmeta.meta_value LIKE '%" . $args['alt_tag'] . "%')";
		}
		if ( array_key_exists( 'caption', $args ) && $args['caption'] !== false && ! empty( $args['caption'] ) ) {
			$query_meta[] = $wpdb->prefix . "posts.post_excerpt LIKE '%" . $args['caption'] . "%'";
		}
		if ( array_key_exists( 'description', $args ) && $args['description'] !== false && ! empty( $args['description'] ) ) {
			$query_meta[] = $wpdb->prefix . "posts.post_content LIKE '%" . $args['description'] . "%'";
		}

		/**
		 * Filter the MySQL query (image type, ID) to fetch a random image ID from the Media Library.
		 *
		 * @since   1.8.0
		 *
		 * @param   array   $query  Query Conditions.
		 * @param   array   $args   Attributes.
		 */
		$query = apply_filters( 'page_generator_pro_shortcode_media_library_get_random_image_html_tag_query', $query, $args );

		/**
		 * Filter the MySQL query meta (title, alt, caption, description) to fetch a random image ID from the Media Library.
		 *
		 * @since   2.2.2
		 *
		 * @param   array   $query  Query Conditions.
		 * @param   array   $args   Attributes.
		 */
		$query_meta = apply_filters( 'page_generator_pro_shortcode_media_library_get_random_image_html_tag_query_meta', $query_meta, $args );

		// Run query.
		// phpcs:disable WordPress.DB.PreparedSQL.NotPrepared
		$images = $wpdb->get_col(
			"SELECT {$wpdb->prefix}posts.ID
			FROM {$wpdb->prefix}posts
			LEFT JOIN {$wpdb->prefix}postmeta
			ON {$wpdb->prefix}posts.ID = {$wpdb->prefix}postmeta.post_id
			WHERE " . implode( ' AND ', $query ) . ( count( $query_meta ) > 0 ? ' AND (' . implode( ' ' . $args['operator'] . ' ', $query_meta ) . ') ' : '' ) . "
			GROUP BY {$wpdb->prefix}posts.ID
			LIMIT 100"
		);
		// phpcs:enable

		// Bail if no results were found.
		if ( empty( $images ) ) {
			return new WP_Error(
				'page_generator_pro_media_library_error',
				__( 'No image could be found based on the supplied criteria', 'page-generator-pro' )
			);
		}

		// Fetch an image at random from the resultset.
		if ( count( $images ) === 1 ) {
			$image_id = $images[0];
		} else {
			$image_id = $images[ wp_rand( 0, count( $images ) - 1 ) ];
		}

		/**
		 * Filter the Image ID before returning.
		 *
		 * @since   1.8.0
		 *
		 * @param   int     $image_id   WordPress Media Library ID.
		 * @param   array   $args       Arguments.
		 * @param   array   $images     Image Results from Query.
		 * @param   array   $query      WHERE query clauses.
		 */
		$image_id = apply_filters( 'page_generator_pro_media_library_get_random_image_id', $image_id, $args, $images, $query );

		// Return Image ID.
		return $image_id;

	}

	/**
	 * Returns an image HTML tag for the given Attachment ID and given parameters
	 *
	 * @since   1.8.0
	 *
	 * @param   int   $image_id   Attachment ID.
	 * @param   array $args       Attributes.
	 * @return  string              Output
	 */
	public function get_image_html_tag_by_id( $image_id, $args ) {

		// If the arguments contain an alt_tag, use this for the <img> tag instead
		// of the Media Library's alt tag.
		$atts = '';
		if ( isset( $args['alt_tag'] ) && $args['alt_tag'] !== false ) {
			$atts = array(
				'alt' => $args['alt_tag'],
			);
		}

		// Get the image HTML tag.
		$html = wp_get_attachment_image( $image_id, $args['size'], false, $atts );

		// If empty (which can happen with WebP), build the HTML manually.
		if ( empty( $html ) ) {
			$image_url = wp_get_attachment_url( $image_id );
			$image_alt = ( empty( $atts ) ? get_post_meta( $image_id, '_wp_attachment_image_alt', true ) : $atts['alt'] );

			$html = sprintf(
				'<img src="%s" alt="%s" />',
				esc_url( $image_url ),
				esc_attr( $image_alt )
			);
		}

		/**
		 * Filter the HTML Image Tag before returning.
		 *
		 * @since   1.8.0
		 *
		 * @param   string  $html       HTML Image Tag.
		 * @param   array   $args       Arguments.
		 * @param   int     $image_id   WordPress Media Library ID.
		 */
		$html = apply_filters( 'page_generator_pro_media_library_get_random_image_html_tag', $html, $args, $image_id );

		// Return filtered HTML.
		return $html;

	}

}
