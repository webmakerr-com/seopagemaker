<?php
/**
 * Image Trait
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Trait for image providers that contains common fields, default values etc.
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 */
trait Page_Generator_Pro_Image_Trait {

	/**
	 * Holds Keywords.
	 *
	 * @since   4.8.0
	 *
	 * @var     bool|array
	 */
	public $keywords = false;

	/**
	 * Whether the integration supports Output fields and attributes.
	 *
	 * @since   4.8.0
	 *
	 * @var     bool
	 */
	public $supports_output = true;

	/**
	 * Whether the integration supports Link fields and attributes.
	 *
	 * @since   4.8.0
	 *
	 * @var     bool
	 */
	public $supports_link = true;

	/**
	 * Whether the integration supports EXIF fields and attributes.
	 *
	 * @since   4.8.0
	 *
	 * @var     bool
	 */
	public $supports_exif = true;

	/**
	 * Returns attributes for the Search Parameters section of an Image Provider.
	 *
	 * @since   4.5.1
	 *
	 * @return  array
	 */
	public function get_search_attributes() {

		return array(
			'term' => array(
				'type'    => 'string',
				'default' => ( ! $this->get_default_value( 'term' ) ? '' : $this->get_default_value( 'term' ) ),
			),
		);

	}

	/**
	 * Returns attributes for the Output section of an Image Provider.
	 *
	 * @since   4.5.1
	 *
	 * @param   bool $include_copy              Include `copy` attribute.
	 * @param   bool $include_size              Include `copy` attribute.
	 * @param   bool $include_caption_display   Include `caption_display` attribute.
	 * @return  array
	 */
	public function get_output_attributes( $include_copy = false, $include_size = false, $include_caption_display = true ) {

		// Bail if output attributes aren't supported.
		if ( ! $this->supports_output ) {
			return array();
		}

		// Define attributes.
		$attributes = array();

		// Include copy attribute, if required.
		if ( $include_copy ) {
			$attributes = array_merge(
				$attributes,
				array(
					'copy' => array(
						'type'    => 'boolean',
						'default' => $this->get_default_value( 'copy' ),
					),

				)
			);
		}

		// Include size attribute, if required.
		if ( $include_size ) {
			$attributes = array_merge(
				$attributes,
				array(
					'size' => array(
						'type'    => 'string',
						'default' => ( ! $this->get_default_value( 'size' ) ? '' : $this->get_default_value( 'size' ) ),
					),
				)
			);
		}

		// Include display caption attribute, if required.
		if ( $include_caption_display ) {
			$attributes = array_merge(
				$attributes,
				array(
					'caption_display' => array(
						'type'    => 'boolean',
						'default' => $this->get_default_value( 'caption_display' ),
					),
				)
			);
		}

		return array_merge(
			$attributes,
			array(
				'title'       => array(
					'type'    => 'string',
					'default' => ( ! $this->get_default_value( 'title' ) ? '' : $this->get_default_value( 'title' ) ),
				),
				'caption'     => array(
					'type'    => 'string',
					'default' => ( ! $this->get_default_value( 'caption' ) ? '' : $this->get_default_value( 'caption' ) ),
				),
				'alt_tag'     => array(
					'type'    => 'string',
					'default' => ( ! $this->get_default_value( 'alt_tag' ) ? '' : $this->get_default_value( 'alt_tag' ) ),
				),
				'description' => array(
					'type'    => 'string',
					'default' => ( ! $this->get_default_value( 'description' ) ? '' : $this->get_default_value( 'description' ) ),
				),
				'filename'    => array(
					'type'    => 'string',
					'default' => ( ! $this->get_default_value( 'filename' ) ? '' : $this->get_default_value( 'filename' ) ),
				),
			)
		);

	}

	/**
	 * Returns attributes for the Link section of an Image Provider.
	 *
	 * @since   4.5.1
	 *
	 * @return  array
	 */
	public function get_link_attributes() {

		// Bail if link attributes aren't supported.
		if ( ! $this->supports_link ) {
			return array();
		}

		return array(
			'link_href'   => array(
				'type'    => 'string',
				'default' => ( ! $this->get_default_value( 'link_href' ) ? '' : $this->get_default_value( 'link_href' ) ),
			),
			'link_title'  => array(
				'type'    => 'string',
				'default' => ( ! $this->get_default_value( 'link_title' ) ? '' : $this->get_default_value( 'link_title' ) ),
			),
			'link_rel'    => array(
				'type'    => 'string',
				'default' => ( ! $this->get_default_value( 'link_rel' ) ? '' : $this->get_default_value( 'link_rel' ) ),
			),
			'link_target' => array(
				'type'    => 'string',
				'default' => ( ! $this->get_default_value( 'link_target' ) ? '' : $this->get_default_value( 'link_target' ) ),
			),
		);

	}

	/**
	 * Returns attributes for the EXIF section of an Image Provider.
	 *
	 * @since   4.5.1
	 *
	 * @return  array
	 */
	public function get_exif_attributes() {

		// Bail if EXIF attributes aren't supported.
		if ( ! $this->supports_exif ) {
			return array();
		}

		return array(
			'exif_latitude'    => array(
				'type'    => 'string',
				'default' => ( ! $this->get_default_value( 'exif_latitude' ) ? '' : $this->get_default_value( 'exif_latitude' ) ),
			),
			'exif_longitude'   => array(
				'type'    => 'string',
				'default' => ( ! $this->get_default_value( 'exif_longitude' ) ? '' : $this->get_default_value( 'exif_longitude' ) ),
			),
			'exif_comments'    => array(
				'type'    => 'string',
				'default' => ( ! $this->get_default_value( 'exif_comments' ) ? '' : $this->get_default_value( 'exif_comments' ) ),
			),
			'exif_description' => array(
				'type'    => 'string',
				'default' => ( ! $this->get_default_value( 'exif_description' ) ? '' : $this->get_default_value( 'exif_description' ) ),
			),
		);

	}

	/**
	 * Returns fields for the Search Parameters section of an Image Provider.
	 *
	 * @since   4.5.1
	 *
	 * @return  array|bool
	 */
	public function get_search_fields() {

		return array();

	}

	/**
	 * Returns fields for the Output section of an Image Provider.
	 *
	 * @since   4.5.1
	 *
	 * @param   bool $include_copy              Include `copy` field.
	 * @param   bool $include_size              Include `size` field.
	 * @param   bool $include_caption_display   Include `caption_display` field.
	 * @return  array|bool
	 */
	public function get_output_fields( $include_copy = false, $include_size = false, $include_caption_display = true ) {

		// Bail if output fields aren't supported.
		if ( ! $this->supports_output ) {
			return array();
		}

		// Fetch Keywords.
		if ( ! $this->keywords ) {
			// Load Keywords class.
			$keywords_class = $this->base->get_class( 'keywords' );

			// Bail if the Keywords class could not be loaded.
			if ( is_wp_error( $keywords_class ) ) {
				return false;
			}

			$this->keywords = $keywords_class->get_keywords_and_columns( true );
		}

		// Define fields array.
		$fields = array();

		// Include copy field, if required.
		if ( $include_copy ) {
			$fields = array_merge(
				$fields,
				array(
					'copy' => array(
						'label'         => __( 'Save to Library?', 'page-generator-pro' ),
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
				)
			);
		}

		// Include size field, if required.
		if ( $include_size ) {
			$fields = array_merge(
				$fields,
				array(
					'size' => array(
						'label'         => __( 'Image Size', 'page-generator-pro' ),
						'type'          => 'select',
						'values'        => $this->base->get_class( 'common' )->get_media_library_image_size_options(),
						'default_value' => $this->get_default_value( 'size' ),
						'description'   => __( 'The image size to output.', 'page-generator-pro' ),
					),
				)
			);
		}

		$fields = array_merge(
			$fields,
			array(
				'title'   => array(
					'label'       => __( 'Title', 'page-generator-pro' ),
					'type'        => 'autocomplete',
					'values'      => $this->keywords,
					'placeholder' => __( 'e.g. building', 'page-generator-pro' ),
					'description' => __( 'Define the title for the image.', 'page-generator-pro' ),
				),
				'caption' => array(
					'label'       => __( 'Caption', 'page-generator-pro' ),
					'type'        => 'autocomplete',
					'values'      => $this->keywords,
					'placeholder' => __( 'e.g. building', 'page-generator-pro' ),
					'description' => __( 'Define the caption for the image.', 'page-generator-pro' ),
				),
			)
		);

		// Include 'Display Caption' field if required.
		if ( $include_caption_display ) {
			$fields = array_merge(
				$fields,
				array(
					'caption_display' => array(
						'label'         => __( 'Display Caption', 'page-generator-pro' ),
						'type'          => 'toggle',
						'description'   => __( 'Display the caption below the image.', 'page-generator-pro' ),
						'default_value' => $this->get_default_value( 'caption_display' ),
					),
				)
			);
		}

		$fields = array_merge(
			$fields,
			array(
				'alt_tag'     => array(
					'label'       => __( 'Alt Tag', 'page-generator-pro' ),
					'type'        => 'autocomplete',
					'values'      => $this->keywords,
					'placeholder' => __( 'e.g. building', 'page-generator-pro' ),
					'description' => __( 'Define the alt text for the image.', 'page-generator-pro' ),
				),
				'description' => array(
					'label'       => __( 'Description', 'page-generator-pro' ),
					'type'        => 'autocomplete',
					'values'      => $this->keywords,
					'placeholder' => __( 'e.g. building', 'page-generator-pro' ),
					'description' => __( 'Define the description for the image.', 'page-generator-pro' ),
				),
				'filename'    => array(
					'label'       => __( 'Filename', 'page-generator-pro' ),
					'type'        => 'autocomplete',
					'values'      => $this->keywords,
					'placeholder' => __( 'e.g. building', 'page-generator-pro' ),
					'description' => __( 'Define the filename for the image, excluding the extension.', 'page-generator-pro' ),
				),
			)
		);

		// Make some fields conditionally display if the `copy` field is included.
		if ( $include_copy ) {
			$copy_condition = array(
				'key'        => 'copy',
				'value'      => 1,
				'comparison' => '==',
			);

			$fields['caption']['size']      = $copy_condition;
			$fields['caption']['condition'] = $copy_condition;
			if ( array_key_exists( 'caption_display', $fields ) ) {
				$fields['caption_display']['condition'] = $copy_condition;
			}
			$fields['description']['condition'] = $copy_condition;
			$fields['filename']['condition']    = $copy_condition;
		}

		return $fields;

	}

	/**
	 * Returns fields for the Link section of an Image Provider.
	 *
	 * @since   4.5.1
	 *
	 * @return  array|bool
	 */
	public function get_link_fields() {

		// Bail if link fields aren't supported.
		if ( ! $this->supports_link ) {
			return array();
		}

		// Fetch Keywords.
		if ( ! $this->keywords ) {
			// Load Keywords class.
			$keywords_class = $this->base->get_class( 'keywords' );

			// Bail if the Keywords class could not be loaded.
			if ( is_wp_error( $keywords_class ) ) {
				return false;
			}

			$this->keywords = $keywords_class->get_keywords_and_columns( true );
		}

		return array(
			// Link.
			'link_href'   => array(
				'label'       => __( 'Link', 'page-generator-pro' ),
				'type'        => 'autocomplete',
				'values'      => $this->keywords,
				'description' => __( 'Define the link for the image. Leave blank for no link.', 'page-generator-pro' ),
			),
			'link_title'  => array(
				'label'       => __( 'Link Title', 'page-generator-pro' ),
				'type'        => 'autocomplete',
				'values'      => $this->keywords,
				'description' => __( 'Define the link title for the image.', 'page-generator-pro' ),
			),
			'link_rel'    => array(
				'label'       => __( 'Link Rel', 'page-generator-pro' ),
				'type'        => 'autocomplete',
				'values'      => $this->keywords,
				'description' => __( 'Define the link rel attribute for the image.', 'page-generator-pro' ),
			),
			'link_target' => array(
				'label'         => __( 'Link Target', 'page-generator-pro' ),
				'type'          => 'select',
				'description'   => __( 'Define the link target for the image.', 'page-generator-pro' ),
				'values'        => $this->base->get_class( 'common' )->get_link_target_options(),
				'default_value' => $this->get_default_value( 'link_target' ),
			),
		);

	}

	/**
	 * Returns fields for the EXIF section of an Image Provider.
	 *
	 * @since   4.5.1
	 *
	 * @param   bool $include_copy_condition   Include `copy` condition.
	 * @return  array|bool
	 */
	public function get_exif_fields( $include_copy_condition = false ) {

		// Bail if EXIF attributes aren't supported.
		if ( ! $this->supports_exif ) {
			return array();
		}

		// Fetch Keywords.
		if ( ! $this->keywords ) {
			// Load Keywords class.
			$keywords_class = $this->base->get_class( 'keywords' );

			// Bail if the Keywords class could not be loaded.
			if ( is_wp_error( $keywords_class ) ) {
				return false;
			}

			$this->keywords = $keywords_class->get_keywords_and_columns( true );
		}

		$fields = array(
			'exif_latitude'    => array(
				'label'  => __( 'Latitude', 'page-generator-pro' ),
				'type'   => 'autocomplete',
				'values' => $this->keywords,
			),
			'exif_longitude'   => array(
				'label'  => __( 'Longitude', 'page-generator-pro' ),
				'type'   => 'autocomplete',
				'values' => $this->keywords,
			),
			'exif_comments'    => array(
				'label'       => __( 'Comments', 'page-generator-pro' ),
				'type'        => 'autocomplete',
				'values'      => $this->keywords,
				'placeholder' => __( 'e.g. building', 'page-generator-pro' ),
			),
			'exif_description' => array(
				'label'       => __( 'Description', 'page-generator-pro' ),
				'type'        => 'autocomplete',
				'values'      => $this->keywords,
				'placeholder' => __( 'e.g. building', 'page-generator-pro' ),
			),
		);

		// Make some fields conditionally display if the `copy` condition is required
		// to only display fields when the shortcode's 'Create as Copy' field is set.
		if ( $include_copy_condition ) {
			$copy_condition = array(
				'key'        => 'copy',
				'value'      => 1,
				'comparison' => '==',
			);

			$fields['exif_latitude']['condition']    = $copy_condition;
			$fields['exif_longitude']['condition']   = $copy_condition;
			$fields['exif_comments']['condition']    = $copy_condition;
			$fields['exif_description']['condition'] = $copy_condition;
		}

		return $fields;

	}

	/**
	 * Returns configuration for the Search Parameters tab.
	 *
	 * @since   4.5.1
	 *
	 * @param   bool $is_featured_image Fields are for Featured Image functionality.
	 * @return  array
	 */
	public function get_search_tab( $is_featured_image = false ) {

		return array(
			'label'       => __( 'Search Parameters', 'page-generator-pro' ),
			'description' => __( 'Defines search query parameters to fetch an image.', 'page-generator-pro' ),
			'class'       => 'search',
			'fields'      => array_keys( $this->get_provider_search_fields( $is_featured_image ) ),
		);

	}

	/**
	 * Returns configuration for the Output tab.
	 *
	 * @since   4.5.1
	 *
	 * @param   bool $include_copy              Include `copy` field.
	 * @param   bool $include_size              Include `size` field.
	 * @param   bool $include_caption_display   Include `caption_display` field.
	 * @return  array
	 */
	public function get_output_tab( $include_copy = false, $include_size = false, $include_caption_display = true ) {

		return array(
			'label'       => __( 'Output', 'page-generator-pro' ),
			'description' => __( 'Defines output parameters for the image.', 'page-generator-pro' ),
			'class'       => 'image',
			'fields'      => array_merge(
				array_keys( $this->get_output_fields( $include_copy, $include_size, $include_caption_display ) ),
				array_keys( $this->get_provider_output_fields() )
			),
		);

	}

	/**
	 * Returns configuration for the Link tab.
	 *
	 * @since   4.5.1
	 *
	 * @return  array
	 */
	public function get_link_tab() {

		return array(
			'label'       => __( 'Link', 'page-generator-pro' ),
			'description' => __( 'Defines parameters for linking the image.', 'page-generator-pro' ),
			'class'       => 'link',
			'fields'      => array_keys( $this->get_link_attributes() ),
		);

	}

	/**
	 * Returns configuration for the EXIF tab.
	 *
	 * @since   4.5.1
	 *
	 * @return  array
	 */
	public function get_exif_tab() {

		return array(
			'label'       => __( 'EXIF', 'page-generator-pro' ),
			'description' => __( 'Defines EXIF metadata to store in the image.', 'page-generator-pro' ),
			'class'       => 'aperture',
			'fields'      => array_keys( $this->get_exif_attributes() ),
		);

	}

	/**
	 * Returns the default values for the Search section of this Image Provider.
	 *
	 * @since   4.5.1
	 *
	 * @return array
	 */
	public function get_search_default_values() {

		return array(
			'term' => false,
		);

	}

	/**
	 * Returns the default values for the Output section of this Image Provider.
	 *
	 * @since   4.5.1
	 *
	 * @param   bool $include_copy              Include `copy` default value.
	 * @param   bool $include_size              Include `size` default value.
	 * @param   bool $include_caption_display   Include `caption_display` defaut value.
	 * @return  array
	 */
	public function get_output_default_values( $include_copy = false, $include_size = false, $include_caption_display = true ) {

		$defaults = array();

		if ( $include_copy ) {
			$defaults = array_merge(
				$defaults,
				array(
					'copy' => false,
				)
			);
		}

		if ( $include_size ) {
			$defaults = array_merge(
				$defaults,
				array(
					'size' => 'large',
				)
			);
		}

		if ( $include_caption_display ) {
			$defaults = array_merge(
				$defaults,
				array(
					'caption_display' => false,
				)
			);
		}

		return array_merge(
			$defaults,
			array(
				'title'       => false,
				'caption'     => false,
				'alt_tag'     => false,
				'description' => false,
				'filename'    => false,
			)
		);

	}

	/**
	 * Returns the default values for the Link section of this Image Provider.
	 *
	 * @since   4.5.1
	 *
	 * @return  array
	 */
	public function get_link_default_values() {

		return array(
			'link_href'   => false,
			'link_title'  => false,
			'link_rel'    => false,
			'link_target' => '_self',
		);

	}

	/**
	 * Returns the default values for the EXIF section of this Image Provider.
	 *
	 * @since   4.5.1
	 *
	 * @return  array
	 */
	public function get_exif_default_values() {

		return array(
			'exif_description' => false,
			'exif_comments'    => false,
			'exif_latitude'    => false,
			'exif_longitude'   => false,
		);

	}

	/**
	 * Chooses an image at random from the given array of images, returning it.
	 *
	 * EXIF data is then stored against the imported image, before the Image ID
	 * is returned
	 *
	 * @since   4.5.1
	 *
	 * @param   array $images     Images.
	 * @return  array               Image
	 */
	public function choose_random_image( $images ) {

		// Pick an image at random from the resultset.
		if ( count( $images ) === 1 ) {
			$image_index = 0;
		} else {
			$image_index = wp_rand( 0, ( count( $images ) - 1 ) );
		}

		// Return image.
		return $images[ $image_index ];

	}

	/**
	 * Imports the given third party image into WordPress with any supplied metadata attributes.
	 *
	 * EXIF data is then stored against the imported image, before the Image ID
	 * is returned
	 *
	 * @since   4.5.1
	 *
	 * @param   array $image      Image.
	 * @param   array $atts       Shortcode Attributes.
	 * @return  WP_Error|int
	 */
	public function import( $image, $atts ) {

		// Import image from URL or use Base64 data.
		if ( array_key_exists( 'data', $image ) ) {
			// Save the image using the base64 data.
			$image_id = $this->import_image_data(
				$image['data'],
				$image['mime_type'],
				0,
				$this->base->get_class( 'shortcode' )->get_group_id(),
				$this->base->get_class( 'shortcode' )->get_index(),
				$atts['filename'],
				( ! $atts['title'] ? $image['title'] : $atts['title'] ), // title.
				( ! $atts['caption'] ? $image['title'] : $atts['caption'] ), // caption.
				( ! $atts['alt_tag'] ? $image['title'] : $atts['alt_tag'] ), // alt_tag.
				( ! $atts['description'] ? $image['title'] : $atts['description'] ) // description.
			);
		} else {
			// Import the image from the URL.
			$image_id = $this->import_remote_image(
				$image['url'],
				0,
				$this->base->get_class( 'shortcode' )->get_group_id(),
				$this->base->get_class( 'shortcode' )->get_index(),
				$atts['filename'],
				( ! $atts['title'] ? $image['title'] : $atts['title'] ), // title.
				( ! $atts['caption'] ? $image['title'] : $atts['caption'] ), // caption.
				( ! $atts['alt_tag'] ? $image['title'] : $atts['alt_tag'] ), // alt_tag.
				( ! $atts['description'] ? $image['title'] : $atts['description'] ) // description.
			);
		}

		// Bail if an error occured.
		if ( is_wp_error( $image_id ) ) {
			return $image_id;
		}

		// Store EXIF Data in Image.
		$this->base->get_class( 'exif' )->write(
			$image_id,
			$atts['exif_description'],
			$atts['exif_comments'],
			$atts['exif_latitude'],
			$atts['exif_longitude']
		);

		// Return Image ID.
		return $image_id;

	}

	/**
	 * Imports a remote image into the WordPress Media Library
	 *
	 * @since   1.1.8
	 *
	 * @param   string      $source      Source URL.
	 * @param   int         $post_id     Post ID.
	 * @param   int         $group_id    Group ID.
	 * @param   int         $index       Generation Index.
	 * @param   bool|string $filename    Target Filename to save source as.
	 * @param   string      $title       Image Title (optional).
	 * @param   string      $caption     Image Caption (optional).
	 * @param   string      $alt_tag     Image Alt Tag (optional).
	 * @param   string      $description Image Description (optional).
	 * @return  WP_Error|int
	 */
	public function import_remote_image( $source, $post_id = 0, $group_id = 0, $index = 0, $filename = false, $title = '', $caption = '', $alt_tag = '', $description = '' ) {

		// If GD support is available, enable it now.
		if ( $this->is_gd_available() ) {
			add_filter( 'wp_image_editors', array( $this, 'enable_gd_image_support' ) );
		}

		// Import the remote image.
		if ( ! function_exists( 'media_handle_upload' ) ) {
			require_once ABSPATH . 'wp-admin/includes/image.php';
			require_once ABSPATH . 'wp-admin/includes/file.php';
			require_once ABSPATH . 'wp-admin/includes/media.php';
		}

		// Get the remote image.
		$tmp = download_url( $source );
		if ( is_wp_error( $tmp ) ) {
			return $tmp;
		}

		// Get image type.
		$type = getimagesize( $tmp );
		if ( ! isset( $type['mime'] ) ) {
			return new WP_Error(
				'page_generator_pro_import_remote_image',
				esc_html__( 'Could not identify MIME type of imported image.', 'page-generator-pro' )
			);
		}
		list( $type, $ext ) = explode( '/', $type['mime'] );
		unset( $type );

		// Define image filename.
		$file_array = array(
			'name'     => strtok( ( $filename !== false ? $filename : basename( $source ) ), '?' ),
			'tmp_name' => $tmp,
		);

		// Add the extension to the filename, if it doesn't exist.
		// This happens if we streamed an image URL e.g. http://placehold.it/400x400.
		switch ( $ext ) {
			case 'jpeg':
			case 'jpg':
				// If neither .jpeg or .jpg exist, append the extension.
				if ( strpos( $file_array['name'], '.jpg' ) === false && strpos( $file_array['name'], '.jpeg' ) === false ) {
					$file_array['name'] .= '.jpg';
				}
				break;

			default:
				if ( strpos( $file_array['name'], '.' . $ext ) === false ) {
					$file_array['name'] .= '.' . $ext;
				}
				break;
		}

		// Limit the length of the filename to the last 150 characters, to avoid a guid length error when
		// media_handle_sideload() runs.
		$file_array['name'] = substr( $file_array['name'], -150 );

		// Import the image into the Media Library.
		$image_id = media_handle_sideload( $file_array, $post_id, '' );
		if ( is_wp_error( $image_id ) ) {
			return $image_id;
		}

		// Store this Group ID and Index in the Attachment's meta.
		update_post_meta( $image_id, '_page_generator_pro_group', $group_id );
		update_post_meta( $image_id, '_page_generator_pro_index', $index );

		// If a title or caption has been defined, set them now.
		if ( ! empty( $title ) || ! empty( $caption ) ) {
			$attachment = get_post( $image_id );
			wp_update_post(
				array(
					'ID'           => $image_id,
					'post_title'   => sanitize_text_field( $title ),
					'post_content' => sanitize_text_field( $description ),
					'post_excerpt' => sanitize_text_field( $caption ),
				)
			);
		}

		// If an alt tag has been specified, set it now.
		if ( ! empty( $alt_tag ) ) {
			update_post_meta( $image_id, '_wp_attachment_image_alt', $alt_tag );
		}

		// Return the image ID.
		return $image_id;

	}

	/**
	 * Saves the given image string into the WordPress Media Library
	 *
	 * @since   5.0.4
	 *
	 * @param   string      $data        Image data, base64.
	 * @param   string      $mime_type   Mime Type (e.g. image/png).
	 * @param   int         $post_id     Post ID.
	 * @param   int         $group_id    Group ID.
	 * @param   int         $index       Generation Index.
	 * @param   bool|string $filename    Target Filename to save source as.
	 * @param   string      $title       Image Title (optional).
	 * @param   string      $caption     Image Caption (optional).
	 * @param   string      $alt_tag     Image Alt Tag (optional).
	 * @param   string      $description Image Description (optional).
	 * @return  WP_Error|int
	 */
	public function import_image_data( $data, $mime_type, $post_id = 0, $group_id = 0, $index = 0, $filename = false, $title = '', $caption = '', $alt_tag = '', $description = '' ) {

		// If GD support is available, enable it now.
		if ( $this->is_gd_available() ) {
			add_filter( 'wp_image_editors', array( $this, 'enable_gd_image_support' ) );
		}

		// Import the remote image.
		if ( ! function_exists( 'media_handle_upload' ) ) {
			require_once ABSPATH . 'wp-admin/includes/image.php';
			require_once ABSPATH . 'wp-admin/includes/file.php';
			require_once ABSPATH . 'wp-admin/includes/media.php';
		}

		// Decode the data.
		$decoded_image = str_replace( 'data:' . $mime_type . ';base64,', '', $data );
		$decoded_image = str_replace( ' ', '+', $decoded_image );
		$decoded_image = base64_decode( $decoded_image ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode

		// Create temporary file comprising of image data and its mime type.
		$tmp_name = wp_tempnam();
		$handle   = fopen( $tmp_name, 'w' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen
		fwrite( $handle, $decoded_image ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fwrite
		fclose( $handle ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose

		// Get image type.
		list( $type, $ext ) = explode( '/', $mime_type );
		unset( $type );

		// Define image filename.
		$file_array = array(
			'name'     => strtok( ( $filename !== false ? $filename : str_replace( '.tmp', '', basename( $tmp_name ) ) ), '?' ),
			'tmp_name' => $tmp_name,
		);

		// Add the extension to the filename, if it doesn't exist.
		switch ( $ext ) {
			case 'jpeg':
			case 'jpg':
				// If neither .jpeg or .jpg exist, append the extension.
				if ( strpos( $file_array['name'], '.jpg' ) === false && strpos( $file_array['name'], '.jpeg' ) === false ) {
					$file_array['name'] .= '.jpg';
				}
				break;

			default:
				if ( strpos( $file_array['name'], '.' . $ext ) === false ) {
					$file_array['name'] .= '.' . $ext;
				}
				break;
		}

		// Limit the length of the filename to the last 150 characters, to avoid a guid length error when
		// media_handle_sideload() runs.
		$file_array['name'] = substr( $file_array['name'], -150 );

		// Import the image into the Media Library.
		$image_id = media_handle_sideload( $file_array, $post_id, '' );
		if ( is_wp_error( $image_id ) ) {
			return $image_id;
		}

		// Store this Group ID and Index in the Attachment's meta.
		update_post_meta( $image_id, '_page_generator_pro_group', $group_id );
		update_post_meta( $image_id, '_page_generator_pro_index', $index );

		// If a title or caption has been defined, set them now.
		if ( ! empty( $title ) || ! empty( $caption ) ) {
			$attachment = get_post( $image_id );
			wp_update_post(
				array(
					'ID'           => $image_id,
					'post_title'   => sanitize_text_field( $title ),
					'post_content' => sanitize_text_field( $description ),
					'post_excerpt' => sanitize_text_field( $caption ),
				)
			);
		}

		// If an alt tag has been specified, set it now.
		if ( ! empty( $alt_tag ) ) {
			update_post_meta( $image_id, '_wp_attachment_image_alt', $alt_tag );
		}

		// Return the image ID.
		return $image_id;

	}

	/**
	 * Flag to denote if the GD image processing library is available
	 *
	 * @since   1.9.7
	 *
	 * @return  bool    GD Library Available in PHP
	 */
	public function is_gd_available() {

		return extension_loaded( 'gd' ) && function_exists( 'gd_info' );

	}

	/**
	 * Force using the GD Image Library for processing WordPress Images.
	 *
	 * @since   1.9.7
	 *
	 * @param   array $editors    WordPress Image Editors.
	 * @return  array             WordPress Image Editors
	 */
	public function enable_gd_image_support( $editors ) {

		$gd_editor = 'WP_Image_Editor_GD';
		$editors   = array_diff( $editors, array( $gd_editor ) );
		array_unshift( $editors, $gd_editor );
		return $editors;

	}

}
