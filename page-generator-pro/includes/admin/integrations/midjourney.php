<?php
/**
 * Imagine API for Midjourney class.
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Imagine API for Midjourney class.
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 4.6.4
 */
class Page_Generator_Pro_Midjourney extends Page_Generator_Pro_API {

	use Page_Generator_Pro_Integration_Trait;
	use Page_Generator_Pro_Featured_Image_Trait;
	use Page_Generator_Pro_Shortcode_Image_Trait;

	/**
	 * Holds the base object.
	 *
	 * @since   4.6.4
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
	public $name = 'midjourney';

	/**
	 * Holds the prompt API endpoint
	 *
	 * @since   4.8.2
	 *
	 * @var     string
	 */
	public $prompt_api_endpoint = 'https://www.wpzinc.com';

	/**
	 * Holds the API endpoint
	 *
	 * @since   4.6.4
	 *
	 * @var     string
	 */
	public $api_endpoint = 'https://cl.imagineapi.dev';

	/**
	 * Holds the flag determining if the request data should be encoded
	 * into a JSON string
	 *
	 * If false, data is encoded using http_build_query()
	 *
	 * @since   4.6.4
	 *
	 * @var     bool
	 */
	public $is_json_request = true;

	/**
	 * Holds the account URL where users can obtain their API key
	 *
	 * @since   4.6.4
	 *
	 * @var     string
	 */
	public $account_url = '';

	/**
	 * Holds the referal URL to use for users wanting to sign up
	 * to the API service.
	 *
	 * @since   4.6.4
	 *
	 * @var     string
	 */
	public $referral_url = '';

	/**
	 * Constructor.
	 *
	 * @since   4.6.4
	 *
	 * @param   object $base    Base Plugin Class.
	 */
	public function __construct( $base ) {

		// Store base class.
		$this->base = $base;

		// Don't display the copy option on the Dynamic Element, as all images are stored
		// in the Media Library.
		$this->shortcode_supports_output_copy = false;

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

		return __( 'Midjourney', 'page-generator-pro' );

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
			$prefix_key . 'style' => array(
				'label'         => __( 'Image Style', 'page-generator-pro' ),
				'type'          => 'select',
				'values'        => array(
					'standard' => __( 'Standard', 'page-generator-pro' ),
					'raw'      => __( 'Raw', 'page-generator-pro' ),
				),
				'default_value' => $this->get_default_value( 'style' ),
				'description'   => __( 'The image style to generate.', 'page-generator-pro' ),
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

		$prefix_key = ( $is_featured_image ? $this->get_settings_prefix() . '_' : '' );

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
			'topic' => array(
				'type'    => 'string',
				'default' => $this->get_default_value( 'topic' ),
			),
			'style' => array(
				'type'    => 'string',
				'default' => ( ! $this->get_default_value( 'style' ) ? '' : $this->get_default_value( 'style' ) ),
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
			$prefix_key . 'topic' => '',
			$prefix_key . 'style' => 'standard',
			$prefix_key . 'size'  => 'large',
		);

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
			'imagineapi_api_key' => array(
				'label'         => __( 'Midjourney (ImagineAPI.dev)', 'page-generator-pro' ),
				'type'          => 'text',
				'default_value' => $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-integrations', 'imagineapi_api_key' ),
				'description'   => sprintf(
					'%s %s %s',
					esc_html__( 'To use Midjourney for image generation, enter your ImagineAPI.dev API key here. Note that Midjourney does not have an API, therefore you\'ll need to sign up to ImagineAPI.dev as well as Midjourney.', 'page-generator-pro' ),
					'<a href="' . esc_attr( $this->base->plugin->documentation_url ) . '/settings-integration/#midjourney" target="_blank" rel="noopener">' . esc_html__( 'Click here', 'page-generator-pro' ) . '</a>',
					esc_html__( 'to read the step by step documentation to do this.', 'page-generator-pro' )
				),
			),
		);

		return $settings_fields;

	}

	/**
	 * Returns this shortcode / block's description.
	 *
	 * @since   4.6.4
	 *
	 * @return string
	 */
	public function get_description() {

		return __( 'Generates an image using Midjourney.', 'page-generator-pro' );

	}

	/**
	 * Returns this shortcode / block's icon.
	 *
	 * @since   4.6.4
	 *
	 * @return  string
	 */
	public function get_icon() {

		return 'assets/images/icons/midjourney.svg';

	}

	/**
	 * Returns this shortcode / block's output
	 *
	 * @since   4.6.4
	 *
	 * @param  array $atts   Shortcode Attributes.
	 * @return string          Output
	 */
	public function render( $atts ) {

		// Parse attributes.
		$atts = $this->parse_atts( $atts );

		// Bail if Midjourney is not configured.
		if ( empty( $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-research', 'imagineapi_api_key' ) ) ) {
			return $this->add_dynamic_element_error_and_return(
				new WP_Error(
					'page_generator_pro_shortcode_midjourney_error',
					__( 'No ImagineAPI.dev API key is defined at Settings > Integrations', 'page-generator-pro' )
				),
				$atts
			);
		}

		// Send request to ImagineAPI.
		$result = $this->create_image(
			$atts['topic'],
			$atts['style']
		);

		// Handle errors.
		if ( is_wp_error( $result ) ) {
			return $this->add_dynamic_element_error_and_return( $result, $atts );
		}

		// Pick an upscaled image at random.
		$image_url = $result->data->upscaled_urls[ wp_rand( 0, count( $result->data->upscaled_urls ) ) ];

		// Store image in Media Library.
		$image    = array(
			'url'   => $image_url,
			'title' => $atts['title'],
		);
		$image_id = $this->import( $image, $atts );

		// Handle errors.
		if ( is_wp_error( $image_id ) ) {
			return $this->add_dynamic_element_error_and_return( $image_id, $atts );
		}

		// Get HTML image tag, with the image matching the given WordPress registered image size.
		$html = $this->get_image_html( $image_id, $atts, $image );

		/**
		 * Filter the Midjourney HTML output, before returning.
		 *
		 * @since   4.6.4
		 *
		 * @param   string      $html       HTML Output.
		 * @param   array       $atts       Shortcode Attributes.
		 * @param   bool|int    $image_id   WordPress Media Library Image ID.
		 * @param   stdClass    $result     ImagineAPI Image Result.
		 */
		$html = apply_filters( 'page_generator_pro_shortcode_midjourney_image', $html, $atts, $image_id, $result );

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
			return new WP_Error( 'page_generator_pro_generate_featured_image', __( 'Featured Image: No Term was specified.', 'page-generator-pro' ) );
		}

		// Bail if Midjourney is not configured.
		if ( empty( $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-research', 'imagineapi_api_key' ) ) ) {
			return new WP_Error( 'page_generator_pro_generate_featured_image', __( 'Featured Image: No API Key is defined at Settings > Integrations.', 'page-generator-pro' ) );
		}

		// Send request to OpenAI.
		$result = $this->create_image(
			$settings[ 'featured_image_' . $this->get_settings_prefix() . '_topic' ],
			$settings[ 'featured_image_' . $this->get_settings_prefix() . '_style' ]
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
	 * @since   4.6.4
	 *
	 * @param   string $prompt        Prompt.
	 * @param   string $style         Image style (standard,raw).
	 * @param   int    $version       Midjourney Version.
	 * @return  WP_Error|object
	 */
	public function create_image( $prompt, $style = 'standard', $version = 6 ) {

		// Fetch prompt.
		$prompt_text = $this->get_prompt(
			array(
				'license_key' => $this->base->licensing->get_license_key(),
				'prompt'      => $prompt,
				'style'       => $style,
				'version'     => $version,
			)
		);

		// If an error occured, return it now.
		if ( is_wp_error( $prompt_text ) ) {
			return $prompt_text;
		}

		// Set Headers.
		$this->set_headers(
			array(
				'Authorization' => 'Bearer ' . $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-research', 'imagineapi_api_key' ),
				'Content-Type'  => 'application/json',
			)
		);

		// Send request to ImagineAPI.
		$response = $this->response(
			$this->post(
				'items/images/',
				array(
					'prompt' => $prompt_text,
				)
			)
		);

		// If an error occured, return it now.
		if ( is_wp_error( $response ) ) {
			return $response;
		}

		// Fetch the image ID.
		$image_id = $response->data->id;

		// Run a loop until the image has generated.
		while ( $response->data->status !== 'completed' ) {
			// Wait for a minute.
			sleep( 15 );

			// Query the image status.
			$response = $this->get_image( $image_id );

			// If an error occured, return it now.
			// failed images will be caught here, because response->data->error is caught in response().
			if ( is_wp_error( $response ) ) {
				return $response;
			}
		}

		return $response;

	}

	/**
	 * Returns the data and status for the given image ID.
	 *
	 * @since   4.6.4
	 *
	 * @param   string $image_id    Image ID of image generation request made using create_image().
	 * @return  WP_Error|object
	 */
	public function get_image( $image_id ) {

		$this->set_headers(
			array(
				'Authorization' => 'Bearer ' . $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-research', 'imagineapi_api_key' ),
				'Content-Type'  => 'application/json',
			)
		);

		return $this->response(
			$this->get( 'items/images/' . $image_id )
		);

	}

	/**
	 * Fetch the prompt text for the ImagineAPI request.
	 *
	 * @since   4.6.4
	 *
	 * @param   array $params     Parameters.
	 * @return  WP_Error|string
	 */
	private function get_prompt( $params ) {

		$result = wp_remote_post(
			$this->prompt_api_endpoint . '/?midjourney_prompt_api=1',
			array(
				'body'      => array(
					'params' => $params,
				),
				'timeout'   => 10,
				'sslverify' => false,
			)
		);

		// If an error occured, return it now.
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		// Fetch HTTP response code and body.
		$http_response_code = wp_remote_retrieve_response_code( $result );
		$body               = json_decode( wp_remote_retrieve_body( $result ) );

		// Bail if the request was not successful.
		if ( ! $body->success ) {
			return new WP_Error(
				'page_generator_pro_imagineapi_error',
				$body->data
			);
		}

		// Return prompt.
		return $body->data;

	}

	/**
	 * Inspects the response from the API call, returning an error
	 * or data
	 *
	 * @since   4.6.4
	 *
	 * @param   WP_Error|object $response   Response.
	 * @return  WP_Error|object
	 */
	public function response( $response ) {

		// If the response is an error, return it.
		if ( is_wp_error( $response ) ) {
			return $response;
		}

		// If an error occured, return it.
		if ( isset( $response->errors ) ) {
			return new WP_Error(
				'page_generator_pro_imagineapi_error',
				$response->errors[0]->message
			);
		}

		// If an error occured, return it.
		if ( isset( $response->data->error ) ) {
			return new WP_Error(
				'page_generator_pro_imagineapi_error',
				$response->data->error
			);
		}

		// If the response wasn't successful, bail.
		if ( isset( $response->success ) && ! $response->success ) {
			return new WP_Error(
				'page_generator_pro_imagineapi_error',
				__( 'An error occured', 'page-generator-pro' )
			);
		}

		return $response;

	}

}
