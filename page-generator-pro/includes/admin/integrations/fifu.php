<?php
/**
 * Featured Image from URL Integration Class
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Registers Featured Image from URL as a Plugin integration:
 * - Registering as a Featured Image source on Content Groups
 * - Copy / don't copy metadata to generated Pages, depending on if the integration is active
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 2.9.3
 */
class Page_Generator_Pro_FIFU extends Page_Generator_Pro_Integration {

	use Page_Generator_Pro_Integration_Trait;
	use Page_Generator_Pro_Shortcode_Trait;
	use Page_Generator_Pro_Featured_Image_Trait;

	/**
	 * Holds the base object.
	 *
	 * @since   2.9.3
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
	public $name = 'fifu';

	/**
	 * Constructor
	 *
	 * @since   2.9.3
	 *
	 * @param   object $base    Base Plugin Class.
	 */
	public function __construct( $base ) {

		// Store base class.
		$this->base = $base;

		// Set Plugin.
		$this->plugin_folder_filename = 'featured-image-from-url/featured-image-from-url.php';

		// Don't display Output or EXIF sections, as they're not used by this integration.
		$this->supports_output = false;
		$this->supports_exif   = false;

		// Register as a Featured Image source.
		add_filter( 'page_generator_pro_common_get_featured_image_sources', array( $this, 'add_featured_image_source' ) );
		add_filter( 'page_generator_pro_groups_get_defaults', array( $this, 'get_featured_image_default_values' ) );
		add_filter( 'page_generator_pro_common_get_featured_image_fields', array( $this, 'get_featured_image_fields' ), 10, 2 );
		add_filter( 'page_generator_pro_common_get_featured_image_tabs', array( $this, 'get_featured_image_tabs' ) );
		add_filter( 'page_generator_pro_generate_featured_image_' . $this->name, array( $this, 'get_featured_image' ), 10, 6 );

	}

	/**
	 * Add FIFU as a Featured Image Source to the Content Groups UI
	 *
	 * @since   2.9.3
	 *
	 * @param   array $sources    Featured Image Sources.
	 * @return  array               Featured Image Sources
	 */
	public function add_featured_image_source( $sources ) {

		// Bail if Plugin isn't active.
		if ( ! $this->is_active() ) {
			return $sources;
		}

		$sources[ $this->get_settings_prefix() ] = __( 'Featured Image from URL (FIFU)', 'page-generator-pro' );

		return $sources;

	}

	/**
	 * Returns provider-specific fields for the Search Parameters section of
	 * the Featured Image in Content Groups.
	 *
	 * @since   4.8.0
	 *
	 * @param   bool $is_featured_image Fields are for Featured Image functionality.
	 * @return  array
	 */
	public function get_provider_search_fields( $is_featured_image = false ) {

		// Bail if Plugin isn't active.
		if ( ! $this->is_active() ) {
			return array();
		}

		// Don't populate fields for performance if the request is for the frontend web site.
		// Populate fields for admin and CLI requests so that Generate via Browser and CLI
		// will see fields for this shortcode, which is required for correct operation with e.g. Elementor
		// registered shortcodes/elements.
		if ( ! $this->base->is_admin_or_frontend_editor() && ! $this->base->is_cli() && ! $this->base->is_cron() ) {
			return array();
		}

		$prefix_key = ( $is_featured_image ? $this->get_settings_prefix() . '_' : '' );

		// Return fields.
		return array(
			$prefix_key . 'url' => array(
				'label'         => __( 'URL', 'page-generator-pro' ),
				'type'          => 'text',
				'default_value' => $this->get_default_value( 'url' ),
				'description'   => __( 'Enter an image URL. This can be a dynamic image URL.', 'page-generator-pro' ),
			),
			$prefix_key . 'alt' => array(
				'label'         => __( 'Alt Text', 'page-generator-pro' ),
				'type'          => 'text',
				'default_value' => $this->get_default_value( 'alt' ),
				'description'   => __( 'The alt text.', 'page-generator-pro' ),
			),
		);

	}

	/**
	 * Returns provider-specific fields for the Output section of
	 * the Featured Image in Content Groups.
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
	 * Returns provider-specific default values for the Featured Image.
	 *
	 * @since   4.8.0
	 *
	 * @param   bool $is_featured_image Fields are for Featured Image functionality.
	 * @return  array
	 */
	public function get_provider_default_values( $is_featured_image = false ) {

		// Bail if Plugin isn't active.
		if ( ! $this->is_active() ) {
			return array();
		}

		// We deliberately don't use this, for backward compat. for Featured Images, which don't prefix.
		$prefix_key = ( $is_featured_image ? $this->get_settings_prefix() . '_' : '' );

		return array(
			$prefix_key . 'url' => '',
			$prefix_key . 'alt' => '',
		);

	}

	/**
	 * Defines the Featured Image for the given generated Post ID, if
	 * the Group Settings specify a Featured Image and (if overwriting)
	 * the Featured Image should be overwritten
	 *
	 * @since   2.9.3
	 *
	 * @param   int   $image_id   Image ID.
	 * @param   int   $post_id    Generated Post ID.
	 * @param   int   $group_id   Group ID.
	 * @param   int   $index      Generation Index.
	 * @param   array $settings   Group Settings.
	 * @param   array $post_args  wp_insert_post() / wp_update_post() arguments.
	 * @return  WP_Error|bool
	 */
	public function get_featured_image( $image_id, $post_id, $group_id, $index, $settings, $post_args ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter

		// Bail if function to set image doesn't exist.
		if ( ! function_exists( 'fifu_dev_set_image' ) ) {
			return false;
		}

		// Update Post Meta on Generated Page.
		fifu_dev_set_image( $post_id, $settings[ 'featured_image_' . $this->get_settings_prefix() . '_url' ] );
		update_post_meta( $post_id, 'fifu_image_alt', $settings[ 'featured_image_' . $this->get_settings_prefix() . '_alt' ] );
		fifu_update_fake_attach_id( $post_id );

		// Don't return an image ID, as FIFU will use the Post Meta on the Generated Page to output the Featured Image.
		return false;

	}

}
