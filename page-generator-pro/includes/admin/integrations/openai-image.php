<?php
/**
 * OpenAI Image Class
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * OpenAI Image Integration
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 4.4.7
 */
class Page_Generator_Pro_OpenAI_Image extends Page_Generator_Pro_API {

	use Page_Generator_Pro_AI_Trait;
	use Page_Generator_Pro_Integration_Trait;
	use Page_Generator_Pro_Featured_Image_Trait;
	use Page_Generator_Pro_Shortcode_Image_Trait;

	/**
	 * Holds the base object.
	 *
	 * @since   3.9.2
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
	public $name = 'openai-image';

	/**
	 * Holds the API endpoint
	 *
	 * @since   3.9.2
	 *
	 * @var     string
	 */
	public $api_endpoint = 'https://api.openai.com/v1';

	/**
	 * Holds the flag determining if the request data should be encoded
	 * into a JSON string
	 *
	 * If false, data is encoded using http_build_query()
	 *
	 * @since   4.1.0
	 *
	 * @var     bool
	 */
	public $is_json_request = true;

	/**
	 * Holds the account URL where users can obtain their API key
	 *
	 * @since   3.9.2
	 *
	 * @var     string
	 */
	public $account_url = 'https://platform.openai.com/api-keys';

	/**
	 * Holds the referal URL to use for users wanting to sign up
	 * to the API service.
	 *
	 * @since   3.9.2
	 *
	 * @var     string
	 */
	public $referral_url = 'https://auth.openai.com/create-account';

	/**
	 * Holds the URL to the model documentation
	 *
	 * @since   5.1.2
	 *
	 * @var     string
	 */
	public $model_documentation_url = 'https://platform.openai.com/docs/models';

	/**
	 * Constructor
	 *
	 * @since   4.4.7
	 *
	 * @param   object $base    Base Plugin Class.
	 */
	public function __construct( $base ) {

		// Store base class.
		$this->base = $base;

		// Don't output the API Key setting field on the Settings > Integrations screen.
		$this->supports_settings_field_api_key = false;

		// Don't display the copy option on the Dynamic Element, as all images are stored
		// in the Media Library.
		$this->shortcode_supports_output_copy = false;

		// Register Settings Fields.
		add_filter( 'page_generator_pro_integrations_get', array( $this, 'register_integration' ) );
		add_filter( 'page_generator_pro_integrations_get_settings_fields', array( $this, 'ai_settings_fields' ) );

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

		return __( 'OpenAI Image', 'page-generator-pro' );

	}

	/**
	 * Returns this shortcode / block's description.
	 *
	 * @since   4.5.2
	 *
	 * @return string
	 */
	public function get_description() {

		return __( 'Generates an image using OpenAI.', 'page-generator-pro' );

	}

	/**
	 * Returns this shortcode / block's icon.
	 *
	 * @since   4.5.2
	 *
	 * @return  string
	 */
	public function get_icon() {

		return 'assets/images/icons/openai-image.svg';

	}

	/**
	 * Returns an array of supported models for OpenAI images.
	 *
	 * @since   5.0.5
	 *
	 * @return  array
	 */
	public function get_models() {

		return array(
			'dall-e-3'    => __( 'Dall-E 3', 'page-generator-pro' ),
			'gpt-image-1' => __( 'GPT Image 1', 'page-generator-pro' ),
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

		$prefix_key = ( $is_featured_image ? $this->get_settings_prefix() . '_' : '' );

		$fields = array(
			$prefix_key . 'topic' => array(
				'label'         => __( 'Topic', 'page-generator-pro' ),
				'type'          => 'autocomplete_textarea',
				'values'        => $this->keywords,
				'placeholder'   => __( 'e.g. a person performing {service}', 'page-generator-pro' ),
				'default_value' => $this->get_default_value( 'topic' ),
				'description'   => __( 'Enter the prompt for the image.  For example, "plumber fixing a sink" or "a person performing {service}".', 'page-generator-pro' ),
			),
		);

		// Additional fields depend on the model selected.
		$model = $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-research', $this->get_settings_prefix() . '_model', 'dall-e-3' );
		switch ( $model ) {
			case 'gpt-image-1':
				$fields[ $prefix_key . 'background' ] = array(
					'label'         => __( 'Background', 'page-generator-pro' ),
					'type'          => 'select',
					'values'        => array(
						'auto'        => __( 'Auto', 'page-generator-pro' ),
						'transparent' => __( 'Transparent', 'page-generator-pro' ),
						'opaque'      => __( 'Opaque', 'page-generator-pro' ),
					),
					'default_value' => $this->get_default_value( 'background' ),
					'description'   => __( 'The background color of the image.', 'page-generator-pro' ),
				);
				break;

			case 'dall-e-3':
				$fields[ $prefix_key . 'style' ] = array(
					'label'         => __( 'Image Style', 'page-generator-pro' ),
					'type'          => 'select',
					'values'        => array(
						'natural' => __( 'Natural', 'page-generator-pro' ),
						'vivid'   => __( 'Vivid', 'page-generator-pro' ),
					),
					'default_value' => $this->get_default_value( 'style' ),
					'description'   => __( 'The image style to generate. Vivid will produce hyper-real, dramatic images. Natural will produce more natural, less hyper-real images.', 'page-generator-pro' ),
				);
				break;
		}

		// Return fields.
		return $fields;

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

		$prefix_key = ( $is_featured_image ? $this->get_settings_prefix() . '_' : '' );

		$model = $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-research', $this->get_settings_prefix() . '_model', 'dall-e-3' );

		return array(
			$prefix_key . 'size' => array(
				'label'         => __( 'Image Size', 'page-generator-pro' ),
				'type'          => 'select',
				'values'        => $this->get_image_sizes( $model ),
				'default_value' => $this->get_default_value( 'size' ),
				'description'   => __( 'The image size to output.', 'page-generator-pro' ),
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
			'topic'      => array(
				'type'    => 'string',
				'default' => $this->get_default_value( 'topic' ),
			),
			'style'      => array(
				'type'    => 'string',
				'default' => ( ! $this->get_default_value( 'style' ) ? '' : $this->get_default_value( 'style' ) ),
			),
			'background' => array(
				'type'    => 'string',
				'default' => ( ! $this->get_default_value( 'background' ) ? '' : $this->get_default_value( 'background' ) ),
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

		// We deliberately don't use this, for backward compat. for Featured Images, which don't prefix.
		$prefix_key = ( $is_featured_image ? $this->get_settings_prefix() . '_' : '' );

		return array(
			$prefix_key . 'topic'      => '',
			$prefix_key . 'style'      => 'vivid',
			$prefix_key . 'background' => 'auto',
			$prefix_key . 'size'       => '1024x1024',
		);

	}

	/**
	 * Returns this shortcode / block's output
	 *
	 * @since   4.4.7
	 *
	 * @param  array $atts   Shortcode Attributes.
	 * @return string          Output
	 */
	public function render( $atts ) {

		// Parse attributes.
		$atts = $this->parse_atts( $atts );

		// Bail if OpenAI is not configured.
		if ( empty( $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-research', 'openai_api_key' ) ) ) {
			return $this->add_dynamic_element_error_and_return(
				new WP_Error(
					'page_generator_pro_openai_image_error',
					__( 'OpenAI: No API key is defined at Settings > Integrations', 'page-generator-pro' )
				),
				$atts
			);
		}

		// Send request to OpenAI.
		$result = $this->create_image(
			$atts['topic'],
			$atts['style'],
			$atts['background'],
			$atts['size'],
			1,
			$this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-research', $this->get_settings_prefix() . '_model', 'dall-e-3' )
		);

		// Handle errors.
		if ( is_wp_error( $result ) ) {
			return $this->add_dynamic_element_error_and_return( $result, $atts );
		}

		// Build image array to import based on whether the image is a URL or base64.
		if ( isset( $result->data[0]->url ) ) {
			$image = array(
				'url'   => $result->data[0]->url,
				'title' => $atts['title'],
			);
		} else {
			$image = array(
				'data'      => $result->data[0]->b64_json,
				'mime_type' => 'image/' . $result->output_format, // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
				'title'     => $atts['title'],
			);
		}

		// Store image in Media Library.
		$image_id = $this->import( $image, $atts );

		// Bail if an error occured.
		if ( is_wp_error( $image_id ) ) {
			return $this->add_dynamic_element_error_and_return( $image_id, $atts );
		}

		// Get HTML image tag, with the image matching the given WordPress registered image size.
		$html = $this->get_image_html( $image_id, $atts, $image );

		/**
		 * Filter the OpenAI HTML output, before returning.
		 *
		 * @since   4.4.7
		 *
		 * @param   string      $html       HTML Output.
		 * @param   array       $atts       Shortcode Attributes.
		 * @param   bool|int    $image_id   WordPress Media Library Image ID.
		 * @param   stdClass    $result     OpenAI Image Result.
		 */
		$html = apply_filters( 'page_generator_pro_shortcode_openai_image', $html, $atts, $image_id, $result );

		// Return.
		return $html;

	}

	/**
	 * Defines the Featured Image for the given generated Post ID, if
	 * the Group Settings specify a Featured Image.
	 *
	 * @since   4.8.0
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
		if ( empty( $settings[ 'featured_image_' . $this->get_settings_prefix() . '_topic' ] ) ) {
			return new WP_Error( 'page_generator_pro_generate_featured_image', __( 'Featured Image: OpenAI: No Term was specified.', 'page-generator-pro' ) );
		}

		// Bail if OpenAI is not configured.
		if ( empty( $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-research', 'openai_api_key' ) ) ) {
			return new WP_Error( 'page_generator_pro_generate_featured_image', __( 'Featured Image: OpenAI: No API Key is defined at Settings > Integrations.', 'page-generator-pro' ) );
		}

		// Send request to OpenAI.
		$result = $this->create_image(
			$settings[ 'featured_image_' . $this->get_settings_prefix() . '_topic' ],
			$settings[ 'featured_image_' . $this->get_settings_prefix() . '_style' ],
			$settings[ 'featured_image_' . $this->get_settings_prefix() . '_background' ],
			'1024x1024',
			1,
			$this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-research', $this->get_settings_prefix() . '_model', 'dall-e-3' )
		);

		// Bail if an error occured.
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		// Get the image from the data.
		// This will be an object comprising of either a `url` (Dall-E 3) or `b64_json` (GPT-Image-1) property.
		$image = $result->data[0];

		if ( isset( $image->url ) ) {
			// Import Image URL into the Media Library.
			return $this->import_remote_image(
				$image->url,
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

		// Import Image base64 into the Media Library.
		return $this->import_image_data(
			$image->b64_json,
			'image/' . $result->output_format, // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
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
	 * Returns an array of supported image sizes for the given model.
	 *
	 * @since   4.6.1
	 *
	 * @param   string $model Model to use.
	 * @return  array
	 */
	private function get_image_sizes( $model = 'dall-e-3' ) {

		switch ( $model ) {
			case 'gpt-image-1':
				return array(
					'auto'      => __( 'Auto', 'page-generator-pro' ),
					'1024x1024' => __( 'Square (1024x1024)', 'page-generator-pro' ),
					'1536x1024' => __( 'Landscape (1536x1024)', 'page-generator-pro' ),
					'1024x1536' => __( 'Portrait (1024x1536)', 'page-generator-pro' ),
				);
			case 'dall-e-3':
				return array(
					'1024x1024' => __( 'Square (1024x1024)', 'page-generator-pro' ),
					'1024x1792' => __( 'Portrait (1024x1792)', 'page-generator-pro' ),
					'1792x1024' => __( 'Landscape (1792x1024)', 'page-generator-pro' ),
				);
		}

	}

	/**
	 * Submits a new image request for the given prompt.
	 *
	 * @since   4.4.7
	 *
	 * @param   string $prompt        Prompt.
	 * @param   string $style         Image style (vivid,natural); Dall-E 3 only.
	 * @param   string $background    Background color (auto, transparent, opaque); GPT-Image-1 only.
	 * @param   string $size          Image size ('256x256', '512x512', '1024x1024', '1024x1792', '1792x1024').
	 * @param   int    $limit         Number of images to generate.
	 * @param   string $model         Model to use.
	 * @return  WP_Error|string
	 */
	public function create_image( $prompt, $style = 'vivid', $background = 'auto', $size = '1024x1024', $limit = 1, $model = 'dall-e-3' ) {

		// Set Headers.
		$this->set_headers(
			array(
				'Authorization' => 'Bearer ' . $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-research', 'openai_api_key' ),
				'Content-Type'  => 'application/json',
			)
		);

		// Build params, depending on the model.
		$params = array(
			'prompt' => $prompt,
			'model'  => $model,
			'n'      => $limit,
			'size'   => $size,
		);

		switch ( $model ) {
			case 'gpt-image-1':
				$params['background'] = $background;
				break;

			case 'dall-e-3':
				$params['style'] = $style;
				break;
		}

		// Send request to OpenAI.
		return $this->response(
			$this->post(
				'images/generations',
				$params
			)
		);

	}

}
