<?php
/**
 * Ideogram AI Class
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Ideogram AI Integration
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 5.0.3
 */
class Page_Generator_Pro_Ideogram_AI extends Page_Generator_Pro_API {

	use Page_Generator_Pro_AI_Trait;
	use Page_Generator_Pro_Integration_Trait;
	use Page_Generator_Pro_Featured_Image_Trait;
	use Page_Generator_Pro_Shortcode_Image_Trait;

	/**
	 * Holds the base object.
	 *
	 * @since   5.0.3
	 *
	 * @var     object
	 */
	public $base;

	/**
	 * Holds the programmatic name of this integration.
	 *
	 * @since   5.0.3
	 *
	 * @var     string
	 */
	public $name = 'ideogram-ai';

	/**
	 * Holds the API endpoint
	 *
	 * @since   5.0.3
	 *
	 * @var     string
	 */
	public $api_endpoint = 'https://api.ideogram.ai';

	/**
	 * Holds the account URL where users can obtain their API key
	 *
	 * @since   5.0.3
	 *
	 * @var     string
	 */
	public $account_url = 'https://ideogram.ai/manage-api';

	/**
	 * Holds the referal URL to use for users wanting to sign up
	 * to the API service.
	 *
	 * @since   5.0.3
	 *
	 * @var     string
	 */
	public $referral_url = 'https://ideogram.ai/login';

	/**
	 * Holds the URL to the model documentation
	 *
	 * @since   5.1.2
	 *
	 * @var     string
	 */
	public $model_documentation_url = 'https://docs.ideogram.ai/using-ideogram/ideogram-features/available-models';

	/**
	 * Constructor
	 *
	 * @since   5.0.3
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
	 * @since   5.0.3
	 *
	 * @return  string
	 */
	public function get_title() {

		return __( 'Ideogram AI Image', 'page-generator-pro' );

	}

	/**
	 * Returns provider-specific fields for the Search Parameters section across
	 * the Dynamic Element and Featured Image.
	 *
	 * @since   5.0.3
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
			$prefix_key . 'topic'         => array(
				'label'         => __( 'Topic', 'page-generator-pro' ),
				'type'          => 'autocomplete_textarea',
				'values'        => $this->keywords,
				'placeholder'   => __( 'e.g. a person performing {service}', 'page-generator-pro' ),
				'default_value' => $this->get_default_value( 'topic' ),
				'description'   => __( 'Enter the prompt for the image.  For example, "plumber fixing a sink" or "a person performing {service}".', 'page-generator-pro' ),
			),
			$prefix_key . 'style'         => array(
				'label'         => __( 'Image Style', 'page-generator-pro' ),
				'type'          => 'select',
				'values'        => array(
					'AUTO'      => __( 'Auto', 'page-generator-pro' ),
					'DESIGN'    => __( 'Design', 'page-generator-pro' ),
					'GENERAL'   => __( 'General', 'page-generator-pro' ),
					'REALISTIC' => __( 'Realistic', 'page-generator-pro' ),
				),
				'default_value' => $this->get_default_value( 'style' ),
				'description'   => __( 'The image style to generate.', 'page-generator-pro' ),
			),
			$prefix_key . 'generate_size' => array(
				'label'         => __( 'Image Size', 'page-generator-pro' ),
				'type'          => 'select',
				'values'        => $this->get_image_sizes( $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-research', $this->get_settings_prefix() . '_model', 'V_2A_TURBO' ) ),
				'default_value' => $this->get_default_value( 'generate_size' ),
				'description'   => __( 'The image size to generate.', 'page-generator-pro' ),
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
			'topic'         => array(
				'type'    => 'string',
				'default' => $this->get_default_value( 'topic' ),
			),
			'style'         => array(
				'type'    => 'string',
				'default' => ( ! $this->get_default_value( 'style' ) ? '' : $this->get_default_value( 'style' ) ),
			),
			'generate_size' => array(
				'type'    => 'string',
				'default' => $this->get_default_value( 'generate_size' ),
			),
		);

	}

	/**
	 * Returns provider-specific default values across the Dynamic Element and Featured Image.
	 *
	 * @since   5.0.3
	 *
	 * @param   bool $is_featured_image Fields are for Featured Image functionality.
	 * @return  array
	 */
	public function get_provider_default_values( $is_featured_image = false ) {

		// We deliberately don't use this, for backward compat. for Featured Images, which don't prefix.
		$prefix_key = ( $is_featured_image ? $this->get_settings_prefix() . '_' : '' );

		return array(
			$prefix_key . 'topic'         => '',
			$prefix_key . 'style'         => 'REALISTIC',
			$prefix_key . 'generate_size' => 'RESOLUTION_1024_1024',
		);

	}

	/**
	 * Returns this shortcode / block's description.
	 *
	 * @since   5.0.3
	 *
	 * @return string
	 */
	public function get_description() {

		return __( 'Generates an image using Ideogram.ai.', 'page-generator-pro' );

	}

	/**
	 * Returns this shortcode / block's icon.
	 *
	 * @since   5.0.3
	 *
	 * @return  string
	 */
	public function get_icon() {

		return 'assets/images/icons/ideogram-ai.svg';

	}

	/**
	 * Returns an array of supported models for Ideogram.
	 *
	 * @since   5.0.3
	 *
	 * @return  array
	 */
	public function get_models() {

		return array(
			'V_1'            => 'Version 1',
			'V_1_TURBO'      => 'Version 1 Turbo',
			'V_2'            => 'Version 2',
			'V_2_TURBO'      => 'Version 2 Turbo',
			'V_2A'           => 'Version 2a',
			'V_2A_TURBO'     => 'Version 2a Turbo',
			'v1/ideogram-v3' => 'Version 3',
		);

	}

	/**
	 * Returns this shortcode / block's output
	 *
	 * @since   5.0.3
	 *
	 * @param  array $atts   Shortcode Attributes.
	 * @return string          Output
	 */
	public function render( $atts ) {

		// Parse attributes.
		$atts = $this->parse_atts( $atts );

		// Bail if Ideogram is not configured.
		if ( empty( $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-research', $this->get_settings_prefix() . '_api_key' ) ) ) {
			return $this->add_dynamic_element_error_and_return(
				new WP_Error(
					'page_generator_pro_ideogram_ai_image_error',
					__( 'Ideogram: No API key is defined at Settings > Integrations', 'page-generator-pro' )
				),
				$atts
			);
		}

		// Send request to Ideogram.
		$result = $this->create_image(
			$atts['topic'],
			$atts['style'],
			$atts['generate_size'],
			1,
			$this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-research', $this->get_settings_prefix() . '_model', 'V_2A_TURBO' )
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
		 * Filter the Ideogram HTML output, before returning.
		 *
		 * @since   5.0.3
		 *
		 * @param   string      $html       HTML Output.
		 * @param   array       $atts       Shortcode Attributes.
		 * @param   bool|int    $image_id   WordPress Media Library Image ID.
		 * @param   stdClass    $result     Ideogram Image Result.
		 */
		$html = apply_filters( 'page_generator_pro_shortcode_ideogram_image', $html, $atts, $image_id, $result );

		// Return.
		return $html;

	}

	/**
	 * Defines the Featured Image for the given generated Post ID, if
	 * the Group Settings specify a Featured Image.
	 *
	 * @since   5.0.3
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
			return new WP_Error( 'page_generator_pro_generate_featured_image', __( 'Featured Image: Ideogram: No Term was specified.', 'page-generator-pro' ) );
		}

		// Bail if Ideogram is not configured.
		if ( empty( $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-research', $this->get_settings_prefix() . '_api_key' ) ) ) {
			return new WP_Error( 'page_generator_pro_generate_featured_image', __( 'Featured Image: Ideogram: No API Key is defined at Settings > Integrations.', 'page-generator-pro' ) );
		}

		// Send request to Ideogram.
		$result = $this->create_image(
			$settings[ 'featured_image_' . $this->get_settings_prefix() . '_topic' ],
			$settings[ 'featured_image_' . $this->get_settings_prefix() . '_style' ],
			$settings[ 'featured_image_' . $this->get_settings_prefix() . '_generate_size' ],
			1,
			$this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-research', $this->get_settings_prefix() . '_model', 'V_2A_TURBO' )
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
	 * Returns an array of supported image sizes.
	 *
	 * @since   5.0.3
	 *
	 * @param   string $model   Model.
	 * @return  array
	 */
	private function get_image_sizes( $model = 'V_2A_TURBO' ) {

		switch ( $model ) {
			case 'v1/ideogram-v3':
				return array(
					'512x1536'  => '512x1536',
					'576x1408'  => '576x1408',
					'576x1472'  => '576x1472',
					'576x1536'  => '576x1536',
					'640x1344'  => '640x1344',
					'640x1408'  => '640x1408',
					'640x1472'  => '640x1472',
					'640x1536'  => '640x1536',
					'704x1152'  => '704x1152',
					'704x1216'  => '704x1216',
					'704x1280'  => '704x1280',
					'704x1344'  => '704x1344',
					'704x1408'  => '704x1408',
					'704x1472'  => '704x1472',
					'736x1312'  => '736x1312',
					'768x1088'  => '768x1088',
					'768x1216'  => '768x1216',
					'768x1280'  => '768x1280',
					'768x1344'  => '768x1344',
					'800x1280'  => '800x1280',
					'832x960'   => '832x960',
					'832x1024'  => '832x1024',
					'832x1088'  => '832x1088',
					'832x1152'  => '832x1152',
					'832x1216'  => '832x1216',
					'832x1248'  => '832x1248',
					'864x1152'  => '864x1152',
					'896x960'   => '896x960',
					'896x1024'  => '896x1024',
					'896x1088'  => '896x1088',
					'896x1120'  => '896x1120',
					'896x1152'  => '896x1152',
					'960x832'   => '960x832',
					'960x896'   => '960x896',
					'960x1024'  => '960x1024',
					'960x1088'  => '960x1088',
					'1024x832'  => '1024x832',
					'1024x896'  => '1024x896',
					'1024x960'  => '1024x960',
					'1024x1024' => '1024x1024',
					'1088x768'  => '1088x768',
					'1088x832'  => '1088x832',
					'1088x896'  => '1088x896',
					'1088x960'  => '1088x960',
					'1120x896'  => '1120x896',
					'1152x704'  => '1152x704',
					'1152x832'  => '1152x832',
					'1152x864'  => '1152x864',
					'1152x896'  => '1152x896',
					'1216x704'  => '1216x704',
					'1216x768'  => '1216x768',
					'1216x832'  => '1216x832',
					'1248x832'  => '1248x832',
					'1280x704'  => '1280x704',
					'1280x768'  => '1280x768',
					'1280x800'  => '1280x800',
					'1312x736'  => '1312x736',
					'1344x640'  => '1344x640',
					'1344x704'  => '1344x704',
					'1344x768'  => '1344x768',
					'1408x576'  => '1408x576',
					'1408x640'  => '1408x640',
					'1408x704'  => '1408x704',
					'1472x576'  => '1472x576',
					'1472x640'  => '1472x640',
					'1472x704'  => '1472x704',
					'1536x512'  => '1536x512',
					'1536x576'  => '1536x576',
					'1536x640'  => '1536x640',
				);
			default:
				return array(
					'RESOLUTION_512_1536'  => '512x1536',
					'RESOLUTION_576_1408'  => '576x1408',
					'RESOLUTION_576_1472'  => '576x1472',
					'RESOLUTION_576_1536'  => '576x1536',
					'RESOLUTION_640_1344'  => '640x1344',
					'RESOLUTION_640_1408'  => '640x1408',
					'RESOLUTION_640_1472'  => '640x1472',
					'RESOLUTION_640_1536'  => '640x1536',
					'RESOLUTION_704_1152'  => '704x1152',
					'RESOLUTION_704_1216'  => '704x1216',
					'RESOLUTION_704_1280'  => '704x1280',
					'RESOLUTION_704_1344'  => '704x1344',
					'RESOLUTION_704_1408'  => '704x1408',
					'RESOLUTION_704_1472'  => '704x1472',
					'RESOLUTION_736_1312'  => '736x1312',
					'RESOLUTION_768_1088'  => '768x1088',
					'RESOLUTION_768_1216'  => '768x1216',
					'RESOLUTION_768_1280'  => '768x1280',
					'RESOLUTION_768_1344'  => '768x1344',
					'RESOLUTION_800_1280'  => '800x1280',
					'RESOLUTION_832_1024'  => '832x1024',
					'RESOLUTION_832_1088'  => '832x1088',
					'RESOLUTION_832_1152'  => '832x1152',
					'RESOLUTION_832_1216'  => '832x1216',
					'RESOLUTION_832_1248'  => '832x1248',
					'RESOLUTION_832_960'   => '832x960',
					'RESOLUTION_864_1152'  => '864x1152',
					'RESOLUTION_896_1024'  => '896x1024',
					'RESOLUTION_896_1088'  => '896x1088',
					'RESOLUTION_896_1120'  => '896x1120',
					'RESOLUTION_896_1152'  => '896x1152',
					'RESOLUTION_896_960'   => '896x960',
					'RESOLUTION_960_1024'  => '960x1024',
					'RESOLUTION_960_1088'  => '960x1088',
					'RESOLUTION_960_832'   => '960x832',
					'RESOLUTION_960_896'   => '960x896',
					'RESOLUTION_1024_1024' => '1024x1024',
					'RESOLUTION_1024_832'  => '1024x832',
					'RESOLUTION_1024_896'  => '1024x896',
					'RESOLUTION_1024_960'  => '1024x960',
					'RESOLUTION_1088_768'  => '1088x768',
					'RESOLUTION_1088_832'  => '1088x832',
					'RESOLUTION_1088_896'  => '1088x896',
					'RESOLUTION_1088_960'  => '1088x960',
					'RESOLUTION_1120_896'  => '1120x896',
					'RESOLUTION_1152_704'  => '1152x704',
					'RESOLUTION_1152_832'  => '1152x832',
					'RESOLUTION_1152_864'  => '1152x864',
					'RESOLUTION_1152_896'  => '1152x896',
					'RESOLUTION_1216_704'  => '1216x704',
					'RESOLUTION_1216_768'  => '1216x768',
					'RESOLUTION_1216_832'  => '1216x832',
					'RESOLUTION_1248_832'  => '1248x832',
					'RESOLUTION_1280_704'  => '1280x704',
					'RESOLUTION_1280_768'  => '1280x768',
					'RESOLUTION_1280_800'  => '1280x800',
					'RESOLUTION_1312_736'  => '1312x736',
					'RESOLUTION_1344_640'  => '1344x640',
					'RESOLUTION_1344_704'  => '1344x704',
					'RESOLUTION_1344_768'  => '1344x768',
					'RESOLUTION_1408_576'  => '1408x576',
					'RESOLUTION_1408_640'  => '1408x640',
					'RESOLUTION_1408_704'  => '1408x704',
					'RESOLUTION_1472_576'  => '1472x576',
					'RESOLUTION_1472_640'  => '1472x640',
					'RESOLUTION_1472_704'  => '1472x704',
					'RESOLUTION_1536_512'  => '1536x512',
					'RESOLUTION_1536_576'  => '1536x576',
					'RESOLUTION_1536_640'  => '1536x640',
				);
		}

	}

	/**
	 * Submits a new image request for the given prompt.
	 *
	 * @since   5.0.3
	 *
	 * @param   string $prompt        Prompt.
	 * @param   string $style         Image style (vivid,natural).
	 * @param   string $size          Image size ('256x256', '512x512', '1024x1024', '1024x1792', '1792x1024').
	 * @param   int    $limit         Number of images to generate.
	 * @param   string $model         Model to use.
	 * @return  WP_Error|string
	 */
	public function create_image( $prompt, $style = 'vivid', $size = 'RESOLUTION_1024_1024', $limit = 1, $model = 'V_2A_TURBO' ) {

		// Send request to Ideogram, depending on the model.
		switch ( $model ) {
			case 'v1/ideogram-v3':
				// v3 sets resolution as e.g 123x456.
				// Convert RESOLUTION_1024_1024 format to 1024x1024 if needed.
				if ( strpos( $size, 'RESOLUTION_' ) === 0 ) {
					list( $width, $height ) = explode( '_', str_replace( 'RESOLUTION_', '', $size ) );
					$size                   = $width . 'x' . $height;
				}

				// Don't use wp_remote_post(), as it cannot handle multipart/form-data.
				// phpcs:disable WordPress.WP.AlternativeFunctions
				$ch = curl_init();
				curl_setopt_array(
					$ch,
					array(
						CURLOPT_URL            => 'https://api.ideogram.ai/v1/ideogram-v3/generate',
						CURLOPT_RETURNTRANSFER => true,
						CURLOPT_POST           => true,
						CURLOPT_HTTPHEADER     => array(
							'Api-Key: ' . $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-research', $this->get_settings_prefix() . '_api_key' ),
						),
						CURLOPT_POSTFIELDS     => array(
							'prompt'     => $prompt,
							'style_type' => $style,
							'num_images' => $limit,
							'resolution' => $size,
						),
					)
				);

				// Execute.
				$response  = curl_exec( $ch );
				$http_code = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
				$error     = curl_error( $ch );
				curl_close( $ch );

				// If our error string isn't empty, something went wrong.
				if ( ! empty( $error ) ) {
					return new WP_Error( 'page_generator_pro_ideogram_ai_error', $error );
				}

				// Decode JSON.
				return json_decode( $response );

			default:
				// Set Headers.
				$this->set_headers(
					array(
						'Api-Key'      => $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-research', $this->get_settings_prefix() . '_api_key' ),
						'Content-Type' => 'application/json',
					)
				);

				return $this->response(
					$this->post(
						'generate',
						array(
							'image_request' => array(
								'prompt'     => $prompt,
								'model'      => $model,
								'style_type' => $style,
								'num_images' => $limit,
								'resolution' => $size,
							),
						)
					)
				);
		}

	}

}
