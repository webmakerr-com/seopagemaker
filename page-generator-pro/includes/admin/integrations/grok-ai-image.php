<?php
/**
 * Grok AI Image Class
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Grok AI Image Integration
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 5.0.6
 */
class Page_Generator_Pro_Grok_AI_Image extends Page_Generator_Pro_API {

	use Page_Generator_Pro_AI_Trait;
	use Page_Generator_Pro_Integration_Trait;
	use Page_Generator_Pro_Featured_Image_Trait;
	use Page_Generator_Pro_Shortcode_Image_Trait;

	/**
	 * Holds the base object.
	 *
	 * @since   5.0.6
	 *
	 * @var     object
	 */
	public $base;

	/**
	 * Holds the programmatic name of this integration.
	 *
	 * @since   5.0.6
	 *
	 * @var     string
	 */
	public $name = 'grok-ai-image';

	/**
	 * Holds the API endpoint
	 *
	 * @since   5.0.6
	 *
	 * @var     string
	 */
	public $api_endpoint = 'https://api.x.ai/v1';

	/**
	 * Holds the account URL where users can obtain their API key
	 *
	 * @since   5.0.6
	 *
	 * @var     string
	 */
	public $account_url = 'https://accounts.x.ai/account';

	/**
	 * Holds the referal URL to use for users wanting to sign up
	 * to the API service.
	 *
	 * @since   5.0.6
	 *
	 * @var     string
	 */
	public $referral_url = 'https://accounts.x.ai/sign-up';

	/**
	 * Holds the URL to the model documentation
	 *
	 * @since   5.1.2
	 *
	 * @var     string
	 */
	public $model_documentation_url = 'https://docs.x.ai/docs/models';

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
	 * @since   5.0.6
	 *
	 * @return  string
	 */
	public function get_title() {

		return __( 'Grok AI Image', 'page-generator-pro' );

	}

	/**
	 * Returns this shortcode / block's description.
	 *
	 * @since   5.0.6
	 *
	 * @return string
	 */
	public function get_description() {

		return __( 'Generates an image using Grok AI.', 'page-generator-pro' );

	}

	/**
	 * Returns this shortcode / block's icon.
	 *
	 * @since   5.0.6
	 *
	 * @return  string
	 */
	public function get_icon() {

		return 'assets/images/icons/grok-ai-image.svg';

	}

	/**
	 * Returns an array of supported models for Grok AI images.
	 *
	 * @since   5.0.6
	 *
	 * @return  array
	 */
	public function get_models() {

		return array(
			'grok-2-image' => __( 'Grok 2 Image', 'page-generator-pro' ),
		);

	}

	/**
	 * Returns provider-specific fields for the Search Parameters section across
	 * the Dynamic Element and Featured Image.
	 *
	 * @since   5.0.6
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
		return array(
			$prefix_key . 'topic' => array(
				'label'         => __( 'Topic', 'page-generator-pro' ),
				'type'          => 'autocomplete_textarea',
				'values'        => $this->keywords,
				'placeholder'   => __( 'e.g. a person performing {service}', 'page-generator-pro' ),
				'default_value' => $this->get_default_value( 'topic' ),
				'description'   => __( 'Enter the prompt for the image.  For example, "plumber fixing a sink" or "a person performing {service}".', 'page-generator-pro' ),
			),
		);

	}

	/**
	 * Returns provider-specific fields for the Output section across
	 * the Dynamic Element and Featured Image.
	 *
	 * @since   5.0.6
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
	 * @since   5.0.6
	 *
	 * @return  array
	 */
	public function get_provider_attributes() {

		return array(
			'topic' => array(
				'type'    => 'string',
				'default' => $this->get_default_value( 'topic' ),
			),
		);

	}

	/**
	 * Returns provider-specific default values across the Dynamic Element and Featured Image.
	 *
	 * @since   5.0.6
	 *
	 * @param   bool $is_featured_image Fields are for Featured Image functionality.
	 * @return  array
	 */
	public function get_provider_default_values( $is_featured_image = false ) {

		// We deliberately don't use this, for backward compat. for Featured Images, which don't prefix.
		$prefix_key = ( $is_featured_image ? $this->get_settings_prefix() . '_' : '' );

		return array(
			$prefix_key . 'topic' => '',
		);

	}

	/**
	 * Returns this shortcode / block's output
	 *
	 * @since   5.0.6
	 *
	 * @param  array $atts   Shortcode Attributes.
	 * @return string          Output
	 */
	public function render( $atts ) {

		// Parse attributes.
		$atts = $this->parse_atts( $atts );

		// Bail if Grok is not configured.
		if ( empty( $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-research', 'grok_ai_api_key' ) ) ) {
			return $this->add_dynamic_element_error_and_return(
				new WP_Error(
					'page_generator_pro_grok_ai_image_error',
					__( 'Grok AI: No API key is defined at Settings > Integrations', 'page-generator-pro' )
				),
				$atts
			);
		}

		// Send request to Grok AI.
		$result = $this->create_image(
			$atts['topic'],
			$this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-research', $this->get_settings_prefix() . '_model', 'grok-2-image' )
		);

		// Handle errors.
		if ( is_wp_error( $result ) ) {
			return $this->add_dynamic_element_error_and_return( $result, $atts );
		}

		// Store image in Media Library.
		$image    = array(
			'url'   => $result->data[0]->url,
			'title' => $atts['title'],
		);
		$image_id = $this->import( $image, $atts );

		// Bail if an error occured.
		if ( is_wp_error( $image_id ) ) {
			return $this->add_dynamic_element_error_and_return( $image_id, $atts );
		}

		// Get HTML image tag, with the image matching the given WordPress registered image size.
		$html = $this->get_image_html( $image_id, $atts, $image );

		/**
		 * Filter the Grok AI Image HTML output, before returning.
		 *
		 * @since   5.0.6
		 *
		 * @param   string      $html       HTML Output.
		 * @param   array       $atts       Shortcode Attributes.
		 * @param   bool|int    $image_id   WordPress Media Library Image ID.
		 * @param   stdClass    $result     Grok AI Image Result.
		 */
		$html = apply_filters( 'page_generator_pro_shortcode_grok_ai_image', $html, $atts, $image_id, $result );

		// Return.
		return $html;

	}

	/**
	 * Defines the Featured Image for the given generated Post ID, if
	 * the Group Settings specify a Featured Image.
	 *
	 * @since   5.0.6
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
			return new WP_Error( 'page_generator_pro_generate_featured_image', __( 'Featured Image: Grok: No Term was specified.', 'page-generator-pro' ) );
		}

		// Bail if Grok is not configured.
		if ( empty( $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-research', 'grok_ai_api_key' ) ) ) {
			return new WP_Error( 'page_generator_pro_generate_featured_image', __( 'Featured Image: Grok: No API Key is defined at Settings > Integrations.', 'page-generator-pro' ) );
		}

		// Send request to Grok.
		$result = $this->create_image(
			$settings[ 'featured_image_' . $this->get_settings_prefix() . '_topic' ],
			$this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-research', $this->get_settings_prefix() . '_model', 'grok-2-image' )
		);

		// Bail if an error occured.
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		// Import Image into the Media Library.
		return $this->import_remote_image(
			$result->data[0]->url,
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
	 * @since   5.0.6
	 *
	 * @param   string $prompt        Prompt.
	 * @param   string $model         Model to use.
	 * @return  WP_Error|stdClass
	 */
	public function create_image( $prompt, $model = 'grok-2-image' ) {

		// Set Headers.
		$this->set_headers(
			array(
				'Authorization' => 'Bearer ' . $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-research', 'grok_ai_api_key' ),
				'Content-Type'  => 'application/json',
			)
		);

		// Make the request to the applicable endpoint.
		return $this->response(
			$this->post(
				'images/generations',
				array(
					'model'           => $model,
					'prompt'          => $prompt,
					'response_format' => 'url',
				)
			)
		);

	}

	/**
	 * Inspects the response from the API call, returning an error
	 * or data
	 *
	 * @since   5.0.6
	 *
	 * @param   WP_Error|object $response   Response.
	 * @return  WP_Error|object
	 */
	public function response( $response ) {

		// If the response is an error, return it.
		if ( is_wp_error( $response ) ) {
			return new WP_Error(
				'page_generator_pro_grok_ai_image_error',
				sprintf(
					/* translators: Error message */
					__( 'Grok AI Image: %s', 'page-generator-pro' ),
					$response->get_error_message()
				)
			);
		}

		// If an error occured, return it.
		if ( isset( $response->error ) ) {
			return new WP_Error(
				'page_generator_pro_mistral_ai_error',
				sprintf(
					/* translators: Error message */
					__( 'Grok AI Image: %s', 'page-generator-pro' ),
					urldecode( $response->error )
				)
			);
		}

		return $response;

	}

}
