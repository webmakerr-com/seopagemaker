<?php
/**
 * Featured Image Trait
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Trait for registering an integration as a Featured Image Source.
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 */
trait Page_Generator_Pro_Featured_Image_Trait {

	use Page_Generator_Pro_Image_Trait;

	/**
	 * Whether the integration supports the `copy` output field and attribute
	 * for Featured Images.
	 *
	 * @since   4.8.0
	 *
	 * @var     bool
	 */
	public $featured_image_supports_output_copy = false;

	/**
	 * Whether the integration supports the `size` output field and attribute
	 * for Featured Images.
	 *
	 * @since   4.8.0
	 *
	 * @var     bool
	 */
	public $featured_image_supports_output_size = false;

	/**
	 * Whether the integration supports the `caption_display` output field and attribute
	 * for Featured Images.
	 *
	 * @since   4.8.0
	 *
	 * @var     bool
	 */
	public $featured_image_supports_output_caption_display = false;

	/**
	 * Add a Featured Image Source to the Content Groups UI
	 *
	 * @since   4.8.0
	 *
	 * @param   array $sources    Featured Image Sources.
	 * @return  array             Featured Image Sources
	 */
	public function add_featured_image_source( $sources ) {

		$sources[ $this->get_name() ] = $this->get_title();
		return $sources;

	}

	/**
	 * Add fields for Featured Images,
	 * prefixing the keys with `featured_image_` for Content Group defaults.
	 *
	 * @since   4.8.0
	 *
	 * @param   array $fields           Fields.
	 * @param   array $group_settings   Group Settings.
	 * @return  array                   Fields
	 */
	public function get_featured_image_fields( $fields, $group_settings ) {

		// Don't populate fields for performance if the request is for the frontend web site.
		// Populate fields for admin and CLI requests so that Generate via Browser and CLI
		// will see fields for this shortcode, which is required for correct operation with e.g. Elementor
		// registered shortcodes/elements.
		if ( ! $this->base->is_admin_or_frontend_editor() && ! $this->base->is_cli() && ! $this->base->is_cron() ) {
			return $fields;
		}

		$featured_image_fields = array_merge(
			$this->get_provider_search_fields( true ),
			$this->get_output_fields( $this->featured_image_supports_output_copy, $this->featured_image_supports_output_size, $this->featured_image_supports_output_caption_display ), // Don't include copy, size and caption display fields.
			$this->get_provider_output_fields( true ),
			$this->get_exif_fields(),
		);

		foreach ( $featured_image_fields as $key => $field ) {
			$featured_image_fields[ $key ]['providers'] = array( $this->get_name() );
		}

		// Prepend field keys with `featured_image_`.
		foreach ( $featured_image_fields as $key => $field ) {
			if ( array_key_exists( 'featured_image_' . $key, $fields ) ) {
				$fields[ 'featured_image_' . $key ]['providers'][] = $this->get_name();
			} else {
				$fields[ 'featured_image_' . $key ] = $field;
			}

			// Set value to the value stored in the Content Group's settings.
			// default_value is misleading here; at some point the field system needs
			// to change this to `value` across Dynamic Elements, TinyMCE etc.
			$fields[ 'featured_image_' . $key ]['default_value'] = $group_settings[ 'featured_image_' . $key ];
		}

		$fields = apply_filters( 'page_generator_pro_featured_image_trait_get_featured_image_fields', $fields, $group_settings );

		return $fields;

	}

	/**
	 * Add tabs for Featured Images.
	 *
	 * @since   4.8.0
	 *
	 * @param   array $tabs   Tabs.
	 * @return  array         Tabs
	 */
	public function get_featured_image_tabs( $tabs ) {

		if ( ! $this->base->is_admin_or_frontend_editor() ) {
			return $tabs;
		}

		$featured_image_tabs = array(
			'search-parameters' => $this->get_search_tab( true ),
			'output'            => $this->get_output_tab(),
			'exif'              => $this->get_exif_tab(),
		);

		// Prepend fields with `featured_image_`.
		foreach ( $featured_image_tabs['search-parameters']['fields'] as $key => $field ) {
			$featured_image_tabs['search-parameters']['fields'][ $key ] = 'featured_image_' . $field;
		}
		foreach ( $featured_image_tabs['output']['fields'] as $key => $field ) {
			$featured_image_tabs['output']['fields'][ $key ] = 'featured_image_' . $field;
		}
		foreach ( $featured_image_tabs['exif']['fields'] as $key => $field ) {
			$featured_image_tabs['exif']['fields'][ $key ] = 'featured_image_' . $field;
		}

		// Merge each tab's fields.
		if ( ! count( $tabs ) ) {
			return array_merge(
				$tabs,
				$featured_image_tabs
			);
		}
		foreach ( $tabs as $tab => $properties ) {
			$tabs[ $tab ]['fields'] = array_unique(
				array_merge(
					$tabs[ $tab ]['fields'],
					$featured_image_tabs[ $tab ]['fields']
				)
			);
		}

		return $tabs;

	}

	/**
	 * Add default values for Featured Images to Content Group settings,
	 * prefixing the keys with `featured_image_` for Content Group defaults.
	 *
	 * @since   4.8.0
	 *
	 * @param   array $defaults   Default Settings.
	 * @return  array             Default Settings
	 */
	public function get_featured_image_default_values( $defaults ) {

		$featured_image_defaults = array_merge(
			$this->get_search_default_values(),
			$this->get_output_default_values( $this->featured_image_supports_output_copy, $this->featured_image_supports_output_size, $this->featured_image_supports_output_caption_display ),
			$this->get_exif_default_values(),
			$this->get_provider_default_values( true )
		);

		// Prepend field keys with `featured_image_`.
		foreach ( $featured_image_defaults as $key => $field ) {
			$defaults[ 'featured_image_' . $key ] = $field;
		}

		return $defaults;

	}

}
