<?php
/**
 * Shortcode Image Trait
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Trait for registering an integration as an Image Shortcode (Dynamic Element):
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 */
trait Page_Generator_Pro_Shortcode_Image_Trait {

	use Page_Generator_Pro_Ignore_Errors_Trait;
	use Page_Generator_Pro_Image_Trait;
	use Page_Generator_Pro_Shortcode_Trait;

	/**
	 * Whether the integration supports the `copy` output field and attribute
	 * for the Dynamic Element.
	 *
	 * @since   4.8.0
	 *
	 * @var     bool
	 */
	public $shortcode_supports_output_copy = true;

	/**
	 * Whether the integration supports the `size` output field and attribute
	 * for the Dynamic Element.
	 *
	 * @since   4.8.0
	 *
	 * @var     bool
	 */
	public $shortcode_supports_output_size = true;

	/**
	 * Whether the integration supports the `caption_display` output field and attribute
	 * for the Dynamic Element.
	 *
	 * @since   4.8.0
	 *
	 * @var     bool
	 */
	public $shortcode_supports_output_caption_display = true;

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

		return array_merge(
			$this->get_provider_search_fields(),
			$this->get_output_fields( $this->shortcode_supports_output_copy, $this->shortcode_supports_output_size, $this->shortcode_supports_output_caption_display ), // Include copy, size and caption display fields.
			$this->get_provider_output_fields(),
			$this->get_link_fields(),
			$this->get_exif_fields( $this->shortcode_supports_output_copy ),
			$this->get_ignore_errors_fields()
		);

	}

	/**
	 * Returns this block's Attributes and default values for Gutenberg.
	 *
	 * @since   3.6.3
	 */
	public function get_attributes() {

		return array_merge(
			$this->get_search_attributes(),
			$this->get_output_attributes( $this->shortcode_supports_output_copy, $this->shortcode_supports_output_size, $this->shortcode_supports_output_caption_display ), // Include copy, size and caption display attributes.
			$this->get_link_attributes(),
			$this->get_exif_attributes(),
			$this->get_provider_attributes(),
			$this->get_ignore_errors_fields(),
			// Preview.
			array(
				'is_gutenberg_example' => array(
					'type'    => 'boolean',
					'default' => false,
				),
			)
		);

	}

	/**
	 * Returns this shortcode / block's UI Tabs
	 *
	 * @since   2.5.1
	 */
	public function get_tabs() {

		$tabs = array_merge(
			array(
				'search-parameters' => $this->get_search_tab(),
				'output'            => $this->get_output_tab( $this->shortcode_supports_output_copy, $this->shortcode_supports_output_size, $this->shortcode_supports_output_caption_display ),
				'link'              => $this->get_link_tab(),
				'exif'              => $this->get_exif_tab(),
			),
			$this->get_ignore_errors_tabs()
		);

		// Merge each tab's fields, to ensure no duplicate fields are output.
		foreach ( $tabs as $tab => $properties ) {
			$tabs[ $tab ]['fields'] = array_unique( $tabs[ $tab ]['fields'] );
		}

		return $tabs;

	}

	/**
	 * Returns this shortcode / block's Default Values
	 *
	 * @since   2.5.1
	 */
	public function get_default_values() {

		return array_merge(
			$this->get_search_default_values(),
			$this->get_output_default_values( $this->shortcode_supports_output_copy, $this->shortcode_supports_output_size, $this->shortcode_supports_output_caption_display ),
			$this->get_link_default_values(),
			$this->get_exif_default_values(),
			$this->get_provider_default_values(),
			$this->get_ignore_errors_default_values()
		);

	}

	/**
	 * Returns the default value for this Image Provider's field.
	 *
	 * @since   4.5.1
	 *
	 * @param   string $field  Field.
	 * @return  string          Value
	 */
	public function get_default_value( $field ) {

		$defaults = $this->get_default_values();
		if ( isset( $defaults[ $field ] ) ) {
			return $defaults[ $field ];
		}

		return '';

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
			__( 'Dynamic Image', 'page-generator-pro' ),
			__( 'Image', 'page-generator-pro' ),
		);

	}

	/**
	 * Returns the given image as a HTML tag.
	 *
	 * @since   4.5.1
	 *
	 * @param   bool|int   $image_id   Attachment ID (false = remote image URL).
	 * @param   array      $atts       Shortcode Attributes.
	 * @param   bool|array $image      Third Party Image (false = not a third party image).
	 * @return  string                 Image HTML markup
	 */
	public function get_image_html( $image_id, $atts, $image = false ) {

		// If an Image ID is specified, get HTML image tag from the image in the Media Library,
		// with the image matching the given WordPress registered image size.
		if ( $image_id ) {
			$html = $this->base->get_class( 'media_library' )->get_image_html_tag_by_id(
				$image_id,
				array(
					'size'    => $atts['size'],
					'title'   => $atts['title'],
					'alt_tag' => $atts['alt_tag'],
				)
			);
		} else {
			// Build the image tag manually.
			$image_atts = array(
				'src' => $image['url'],
			);
			if ( $atts['alt_tag'] ) {
				$image_atts['alt'] = $atts['alt_tag'];
			}
			if ( $atts['title'] ) {
				$image_atts['title'] = $atts['title'];
			}

			// Build <img> string.
			$html = '<img';
			foreach ( $image_atts as $att => $value ) {
				$html .= ' ' . $att . '="' . $value . '"';
			}
			$html .= ' />';
		}

		// If a link is specified, wrap the image in the link now.
		if ( ! empty( $atts['link_href'] ) ) {
			$link = '<a href="' . $atts['link_href'] . '"';

			// Add title, if specified.
			if ( ! empty( $atts['link_title'] ) ) {
				$link .= ' title="' . $atts['link_title'] . '"';
			}

			// Add rel attribute, if specified.
			if ( ! empty( $atts['link_rel'] ) ) {
				$link .= ' rel="' . $atts['link_rel'] . '"';
			}

			// Add target, if specified.
			if ( ! empty( $atts['link_target'] ) ) {
				$link .= ' target="' . $atts['link_target'] . '"';
			}

			$link .= '>';

			$html = $link . $html . '</a>';
		}

		// Assume no caption.
		$caption_html = '';

		// If attribution is enabled, show it now.
		if ( isset( $atts['attribution'] ) && $atts['attribution'] && $image ) {
			// Set a sensible caption before the attribution.
			$caption = __( 'Image', 'page-generator-pro' );

			// If a caption is specified and enabled for display, use it within the attribution.
			if ( isset( $atts['caption_display'] ) && $atts['caption_display'] && $atts['caption'] ) {
				$caption = $atts['caption'];
			}

			// Append caption and attribution to image.
			$caption_html = '<figcaption class="wp-element-caption">' . $this->get_image_attribution( $image, $caption ) . '</figcaption>';
		} elseif ( isset( $atts['caption_display'] ) && $atts['caption_display'] && $atts['caption'] ) {
			// A caption is specified and enabled for display, without attribution.
			// Just output the caption.
			$caption_html = '<figcaption class="wp-element-caption">' . $atts['caption'] . '</figcaption>';
		}

		// Wrap in a figure element.
		$html = '<figure class="wp-block-image size-' . $atts['size'] . '" data-id="' . $image_id . '">' . $html . $caption_html . '</figure>';

		/**
		 * Filter the image HTML output, before returning.
		 *
		 * @since   4.5.1
		 *
		 * @param   string  $html       HTML Output.
		 * @param   array   $atts       Shortcode Attributes.
		 * @param   int     $image_id   WordPress Media Library Image ID.
		 * @param   array   $image      Third Party Image Data.
		 */
		$html = apply_filters( 'page_generator_pro_shortcode_image_get_image_html', $html, $atts, $image_id, $image );

		// Return.
		return $html;

	}

	/**
	 * Returns attributation HTML for the given image
	 *
	 * @since   4.5.1
	 *
	 * @param   array  $image    Image.
	 * @param   string $caption  Image Caption.
	 * @return  string           Image Attribution and caption.
	 */
	public function get_image_attribution( $image, $caption ) {

		// Return full attribution if available.
		if ( $image['license'] && $image['license_url'] && $image['license_version'] ) {
			return sprintf(
				/* translators: %1$s: Link to Image Source, %2$s: Link to Image Creator, %3$s: Link to Image License */
				__( '%1$s by %2$s, licensed under %3$s', 'page-generator-pro' ),
				'<a href="' . $image['source'] . '" target="_blank" rel="nofollow noopener">' . $caption . '</a>',
				'<a href="' . $image['creator_url'] . '" target="_blank" rel="nofollow noopener">' . $image['creator'] . '</a>',
				'<a href="' . $image['license_url'] . '" target="_blank" rel="nofollow noopener">' . $image['license'] . ' ' . $image['license_version'] . '</a>'
			);
		}

		// Return basic attribution.
		return sprintf(
			/* translators: %1$s: Link to Image Source, %2$s: Link to Image Creator */
			__( '%1$s by %2$s', 'page-generator-pro' ),
			'<a href="' . $image['source'] . '" target="_blank" rel="nofollow noopener">' . $caption . '</a>',
			'<a href="' . $image['creator_url'] . '" target="_blank" rel="nofollow noopener">' . $image['creator'] . '</a>'
		);

	}

	/**
	 * Converts image based Dynamic Elements, output as a HTML block, to an image block
	 * immediately before the page is generated.
	 *
	 * This ensures the block is stored as a core/image block on the generated page, for
	 * better rendering and editing.
	 *
	 * @since   5.2.5
	 *
	 * @param   array $block     Block.
	 * @return  array
	 */
	public function convert_html_block_to_image_block( $block ) {

		// Skip if the original block name attribute doesn't match this shortcode.
		if ( ! array_key_exists( 'attrs', $block ) ) {
			return $block;
		}
		if ( ! array_key_exists( 'original_block_name', $block['attrs'] ) ) {
			return $block;
		}
		if ( $block['attrs']['original_block_name'] !== 'page-generator-pro/' . $this->get_name() ) {
			return $block;
		}

		// Merge default attributes with block attributes.
		$atts = array_merge( $this->get_default_values(), $block['attrs'] );

		// Get image HTML.
		$html = $block['innerHTML'];

		// Extract image ID from the data-id attribute in innerHTML.
		preg_match( '/data-id="(\d+)"/', $html, $matches );

		// If no data-id exists, the image wasn't copied to the Media Library.
		// Return the block as is.
		if ( ! isset( $matches[1] ) ) {
			// Make some modifications to the HTML.
			// This prevents "Block contains unexpected or invalid content" errors when editing a generated page.

			// Remove data-id attribute.
			$html = str_replace( 'data-id=""', '', $html );

			// Remove all title="*" attributes from the HTML, as the core/image block doesn't support this.
			$html = preg_replace( '/title="[^"]*"/i', '', $html );

			// Return core/image block.
			return array(
				'blockName'    => 'core/image',
				'attrs'        => array(
					'lightbox'        => array(
						'enabled' => false,
					),
					'sizeSlug'        => $atts['size'],
					'linkDestination' => $atts['link_href'] ? 'custom' : 'none',
				),
				'innerBlocks'  => array(),
				'innerHTML'    => $html,
				'innerContent' => array(
					$html,
				),
			);
		}

		// Image is from the Media Library.

		// Get image ID.
		$image_id = absint( $matches[1] );

		// Make some modifications to the HTML.
		// This prevents "Block contains unexpected or invalid content" errors when editing a generated page.

		// Get image src and build image HTML that core/image expects.
		$image_src  = wp_get_attachment_image_src( $image_id, $atts['size'] );
		$image_html = '<img src="' . $image_src[0] . '" alt="' . $atts['alt_tag'] . '" class="wp-image-' . $image_id . '" />';

		// Use preg_replace to replace the existing <img> tag with the new one.
		$html = preg_replace( '/<img[^>]+>/', $image_html, $html );

		// Remove data-id attribute.
		$html = str_replace( 'data-id="' . $image_id . '"', '', $html );

		// Remove all title="*" attributes from the HTML, as the core/image block doesn't support this.
		$html = preg_replace( '/title="[^"]*"/i', '', $html );

		// Return core/image block.
		return array(
			'blockName'    => 'core/image',
			'attrs'        => array(
				'lightbox'        => array(
					'enabled' => false,
				),
				'id'              => $image_id,
				'sizeSlug'        => $atts['size'],
				'linkDestination' => $atts['link_href'] ? 'custom' : 'none',
			),
			'innerBlocks'  => array(),
			'innerHTML'    => $html,
			'innerContent' => array(
				$html,
			),
		);

	}

}
