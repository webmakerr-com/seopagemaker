<?php
/**
 * Gemini AI Image Class
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Gemini AI Image Integration
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 5.0.4
 */
class Page_Generator_Pro_Gemini_AI_Image extends Page_Generator_Pro_API {

	use Page_Generator_Pro_AI_Trait;
	use Page_Generator_Pro_Integration_Trait;
	use Page_Generator_Pro_Featured_Image_Trait;
	use Page_Generator_Pro_Shortcode_Image_Trait;

	/**
	 * Holds the base object.
	 *
	 * @since   5.0.4
	 *
	 * @var     object
	 */
	public $base;

	/**
	 * Holds the programmatic name of this integration.
	 *
	 * @since   5.0.4
	 *
	 * @var     string
	 */
	public $name = 'gemini-ai-image';

	/**
	 * Holds the API endpoint
	 *
	 * @since   4.6.0
	 *
	 * @var     string
	 */
	public $api_endpoint = 'https://generativelanguage.googleapis.com/v1beta';

	/**
	 * Holds the account URL where users can obtain their API key
	 *
	 * @since   5.0.4
	 *
	 * @var     string
	 */
	public $account_url = 'https://aistudio.google.com/app/apikey';

	/**
	 * Holds the referal URL to use for users wanting to sign up
	 * to the API service.
	 *
	 * @since   5.0.4
	 *
	 * @var     string
	 */
	public $referral_url = 'https://aistudio.google.com/app/apikey';

	/**
	 * Holds the URL to the model documentation
	 *
	 * @since   5.1.2
	 *
	 * @var     string
	 */
	public $model_documentation_url = 'https://ai.google.dev/gemini-api/docs/image-generation';

	/**
	 * Constructor
	 *
	 * @since   5.0.4
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
	 * @since   5.0.4
	 *
	 * @return  string
	 */
	public function get_title() {

		return __( 'Gemini AI Image', 'page-generator-pro' );

	}

	/**
	 * Returns this shortcode / block's description.
	 *
	 * @since   5.0.4
	 *
	 * @return string
	 */
	public function get_description() {

		return __( 'Generates an image using Gemini.', 'page-generator-pro' );

	}

	/**
	 * Returns this shortcode / block's icon.
	 *
	 * @since   5.0.4
	 *
	 * @return  string
	 */
	public function get_icon() {

		return 'assets/images/icons/gemini-ai.svg';

	}

	/**
	 * Returns an array of supported models for Gemini AI images.
	 *
	 * @since   5.0.6
	 *
	 * @return  array
	 */
	public function get_models() {

		return array(
			'imagen-4.0-generate-001'                   => __( 'Imagen 4.0', 'page-generator-pro' ),
			'imagen-4.0-ultra-generate-001'             => __( 'Imagen 4.0 Ultra', 'page-generator-pro' ),
			'imagen-4.0-fast-generate-001'              => __( 'Imagen 4.0 Fast', 'page-generator-pro' ),
			'imagen-3.0-generate-002'                   => __( 'Imagen 3.0', 'page-generator-pro' ),
			'gemini-2.5-flash-image-preview'            => __( 'Gemini 2.5 Flash Image Preview (aka Nano Banana)', 'page-generator-pro' ),
			'gemini-2.0-flash-preview-image-generation' => __( 'Gemini 2.0 Flash Preview Image Generation', 'page-generator-pro' ),
			'gemini-2.0-flash-exp-image-generation'     => __( 'Gemini 2.0 Flash Exp Image Generation', 'page-generator-pro' ),
		);

	}

	/**
	 * Returns provider-specific fields for the Search Parameters section across
	 * the Dynamic Element and Featured Image.
	 *
	 * @since   5.0.4
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

		// Return fields.
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
		$model = $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-research', $this->get_settings_prefix() . '_model', 'imagen-4.0-fast-generate-001' );
		switch ( $model ) {
			case 'imagen-4.0-generate-001':
			case 'imagen-4.0-ultra-generate-001':
			case 'imagen-4.0-fast-generate-001':
			case 'imagen-3.0-generate-002':
				$fields[ $prefix_key . 'aspect_ratio' ] = array(
					'label'         => __( 'Aspect Ratio', 'page-generator-pro' ),
					'type'          => 'select',
					'values'        => array(
						'1:1'  => __( '1:1', 'page-generator-pro' ),
						'3:4'  => __( '3:4', 'page-generator-pro' ),
						'4:3'  => __( '4:3', 'page-generator-pro' ),
						'9:16' => __( '9:16', 'page-generator-pro' ),
						'16:9' => __( '16:9', 'page-generator-pro' ),
					),
					'default_value' => $this->get_default_value( 'aspect_ratio' ),
					'description'   => __( 'The aspect ratio of the image.', 'page-generator-pro' ),
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
			'topic'        => array(
				'type'    => 'string',
				'default' => $this->get_default_value( 'topic' ),
			),
			'aspect_ratio' => array(
				'type'    => 'string',
				'default' => ( ! $this->get_default_value( 'aspect_ratio' ) ? '' : $this->get_default_value( 'aspect_ratio' ) ),
			),
		);

	}

	/**
	 * Returns provider-specific default values across the Dynamic Element and Featured Image.
	 *
	 * @since   5.0.4
	 *
	 * @param   bool $is_featured_image Fields are for Featured Image functionality.
	 * @return  array
	 */
	public function get_provider_default_values( $is_featured_image = false ) {

		// We deliberately don't use this, for backward compat. for Featured Images, which don't prefix.
		$prefix_key = ( $is_featured_image ? $this->get_settings_prefix() . '_' : '' );

		return array(
			$prefix_key . 'topic'        => '',
			$prefix_key . 'aspect_ratio' => '1:1',
		);

	}

	/**
	 * Returns this shortcode / block's output
	 *
	 * @since   5.0.4
	 *
	 * @param  array $atts   Shortcode Attributes.
	 * @return string          Output
	 */
	public function render( $atts ) {

		// Parse attributes.
		$atts = $this->parse_atts( $atts );

		// Bail if Gemini is not configured.
		if ( empty( $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-research', 'gemini_ai_api_key' ) ) ) {
			return $this->add_dynamic_element_error_and_return(
				new WP_Error(
					'page_generator_pro_gemini_ai_image_error',
					__( 'Gemini: No API key is defined at Settings > Integrations', 'page-generator-pro' )
				),
				$atts
			);
		}

		// Send request to Gemini.
		$result = $this->create_image(
			$atts['topic'],
			$atts['aspect_ratio'],
			1,
			$this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-research', $this->get_settings_prefix() . '_model', 'gemini-2.0-flash-exp-image-generation' )
		);

		// Handle errors.
		if ( is_wp_error( $result ) ) {
			return $this->add_dynamic_element_error_and_return( $result, $atts );
		}

		// Store image in Media Library.
		$image    = array(
			'data'      => $result->data,
			'mime_type' => $result->mimeType, // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
			'title'     => $atts['title'],
		);
		$image_id = $this->import( $image, $atts );

		// Bail if an error occured.
		if ( is_wp_error( $image_id ) ) {
			return $this->add_dynamic_element_error_and_return( $image_id, $atts );
		}

		// Get HTML image tag, with the image matching the given WordPress registered image size.
		$html = $this->get_image_html( $image_id, $atts, $image );

		/**
		 * Filter the Gemini AI Image HTML output, before returning.
		 *
		 * @since   4.4.7
		 *
		 * @param   string      $html       HTML Output.
		 * @param   array       $atts       Shortcode Attributes.
		 * @param   bool|int    $image_id   WordPress Media Library Image ID.
		 * @param   stdClass    $result     Gemini AI Image Result.
		 */
		$html = apply_filters( 'page_generator_pro_shortcode_gemini_ai_image', $html, $atts, $image_id, $result );

		// Return.
		return $html;

	}

	/**
	 * Defines the Featured Image for the given generated Post ID, if
	 * the Group Settings specify a Featured Image.
	 *
	 * @since   5.0.4
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
			return new WP_Error( 'page_generator_pro_generate_featured_image', __( 'Featured Image: Gemini: No Term was specified.', 'page-generator-pro' ) );
		}

		// Bail if Gemini is not configured.
		if ( empty( $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-research', 'gemini_ai_api_key' ) ) ) {
			return new WP_Error( 'page_generator_pro_generate_featured_image', __( 'Featured Image: Gemini: No API Key is defined at Settings > Integrations.', 'page-generator-pro' ) );
		}

		// Send request to Gemini.
		$result = $this->create_image(
			$settings[ 'featured_image_' . $this->get_settings_prefix() . '_topic' ],
			$settings[ 'featured_image_' . $this->get_settings_prefix() . '_aspect_ratio' ],
			1,
			$this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-research', $this->get_settings_prefix() . '_model', 'gemini-2.0-flash-exp-image-generation' )
		);

		// Bail if an error occured.
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		// Import Image into the Media Library.
		return $this->import_image_data(
			$result->data,
			$result->mimeType, // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
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
	 * Submits a new image request for the given prompt.
	 *
	 * @since   5.0.4
	 *
	 * @param   string $prompt        Prompt.
	 * @param   string $aspect_ratio  Aspect ratio, for Imagen models only ('1:1', '3:4', '4:3', '9:16', '16:9').
	 * @param   int    $limit         Number of images to generate.
	 * @param   string $model         Model to use.
	 * @return  WP_Error|stdClass
	 */
	public function create_image( $prompt, $aspect_ratio = '1:1', $limit = 1, $model = 'imagen-4.0-fast-generate-001' ) {

		// Build parameters based on the model.
		switch ( $model ) {
			// Imagen.
			case 'imagen-4.0-generate-001':
			case 'imagen-4.0-ultra-generate-001':
			case 'imagen-4.0-fast-generate-001':
			case 'imagen-3.0-generate-002':
				// Gemini uses :generateContent, Imagen uses :predict.
				$model_with_action = $model . ':predict';

				// Build params for Gemini.
				$params = array(
					'instances'  => array(
						array(
							'prompt' => $prompt,
						),
					),
					'parameters' => array(
						'sampleCount' => $limit,
						'aspectRatio' => $aspect_ratio,
					),
				);
				break;

			// Gemini.
			default:
				// Gemini uses :generateContent, Imagen uses :predict.
				$model_with_action = $model . ':generateContent';

				// Build params for Gemini.
				$params = array(
					'contents'         => array(
						'parts' => array(
							'text' => $prompt,
						),
					),
					'generationConfig' => array(
						'responseModalities' => array(
							'TEXT',
							'IMAGE',
						),
					),
				);
				break;

		}

		// Send request to Gemini AI.
		$data = $this->query( $prompt, $model_with_action, $params );

		// Bail if an error occured.
		if ( is_wp_error( $data ) ) {
			return $data;
		}

		// Fetch the image data from the response.
		switch ( $model ) {
			// Imagen.
			case 'imagen-4.0-generate-001':
			case 'imagen-4.0-ultra-generate-001':
			case 'imagen-4.0-fast-generate-001':
			case 'imagen-3.0-generate-002':
				foreach ( $data->predictions as $prediction ) {
					$image           = new stdClass();
					$image->data     = $prediction->bytesBase64Encoded; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
					$image->mimeType = $prediction->mimeType; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
					return $image;
				}
				break;

			// Gemini.
			default:
				foreach ( $data->candidates[0]->content->parts as $part ) {
					// Skip if this part isn't the inlineData i.e. it might be a conversation response
					// from the AI telling us what it will do next.
					if ( ! isset( $part->inlineData ) ) { // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
						continue;
					}

					// Return stdClass with mimeType and data.
					return $part->inlineData; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
				}
				break;
		}

		// If here, something went wrong with the image generation.
		return new WP_Error(
			'page_generator_pro_gemini_ai_image_error',
			__( 'Gemini: No image was generated.', 'page-generator-pro' )
		);

	}

	/**
	 * Sends a prompt to Gemini AI, with options to define the model and additional parameters.
	 *
	 * @since   5.0.4
	 *
	 * @param   string $prompt_text    Prompt Text.
	 * @param   string $model          Model, including action (Gemini uses :generateContent, Imagen uses :predict).
	 * @param   array  $params         Additional request / query parameters e.g. temperature, top_p.
	 */
	private function query( $prompt_text, $model = 'imagen-4.0-fast-generate-001:predict', $params = array() ) {

		// Set Headers.
		$this->set_headers(
			array(
				'x-goog-api-key' => $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-research', 'gemini_ai_api_key' ),
				'Content-Type'   => 'application/json',
			)
		);

		// Define the API endpoint.
		$this->api_endpoint = 'https://generativelanguage.googleapis.com/v1beta';

		// Make the request to the applicable endpoint.
		return $this->response(
			$this->post(
				'models/' . $model,
				$params
			)
		);

	}

}
