<?php
/**
 * Gemini AI API class.
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * API class for generating content from Google's Gemini AI.
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 4.6.0
 */
class Page_Generator_Pro_Gemini_AI extends Page_Generator_Pro_API {

	use Page_Generator_Pro_AI_Trait;
	use Page_Generator_Pro_Integration_Trait;
	use Page_Generator_Pro_Spintax_Trait;
	use Page_Generator_Pro_Shortcode_Trait;

	/**
	 * Holds the base object.
	 *
	 * @since   4.6.0
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
	public $name = 'gemini-ai';

	/**
	 * Holds the API endpoint
	 *
	 * @since   4.6.0
	 *
	 * @var     string
	 */
	public $api_endpoint = 'https://generativelanguage.googleapis.com/v1';

	/**
	 * Holds the flag determining if the request data should be encoded
	 * into a JSON string
	 *
	 * If false, data is encoded using http_build_query()
	 *
	 * @since   4.6.0
	 *
	 * @var     bool
	 */
	public $is_json_request = true;

	/**
	 * Holds the account URL where users can obtain their API key
	 *
	 * @since   4.6.0
	 *
	 * @var     string
	 */
	public $account_url = 'https://aistudio.google.com/app/apikey';

	/**
	 * Holds the referal URL to use for users wanting to sign up
	 * to the API service.
	 *
	 * @since   4.6.0
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
	public $model_documentation_url = 'https://ai.google.dev/gemini-api/docs/models';

	/**
	 * Constructor.
	 *
	 * @since   4.6.0
	 *
	 * @param   object $base    Base Plugin Class.
	 */
	public function __construct( $base ) {

		// Store base class.
		$this->base = $base;

		// Register Settings Fields.
		add_filter( 'page_generator_pro_integrations_get', array( $this, 'register_integration' ) );
		add_filter( 'page_generator_pro_integrations_get_settings_fields', array( $this, 'ai_settings_fields' ) );

		// Register as a Keyword Source.
		add_filter( 'page_generator_pro_keywords_register_sources', array( $this, 'ai_register_keyword_source' ) );
		add_filter( 'page_generator_pro_keywords_save_' . $this->name, array( $this, 'ai_save_keyword' ) );

		// Register as a Generate Locations Provider.
		add_filter( 'page_generator_pro_keywords_generate_locations_get_providers', array( $this, 'register_integration' ) );
		add_filter( 'page_generator_pro_keywords_generate_locations_get_providers_settings_fields', array( $this, 'ai_settings_fields' ) );
		add_filter( 'page_generator_pro_common_get_locations_output_types_cities_' . $this->name, array( $this, 'ai_generate_locations_output_types_cities' ) );
		add_filter( 'page_generator_pro_common_get_locations_output_types_' . $this->name, array( $this, 'ai_generate_locations_output_types_countries' ) );
		add_filter( 'page_generator_pro_keywords_generate_locations_by_area_' . $this->name, array( $this, 'ai_generate_locations' ), 10, 4 );
		add_filter( 'page_generator_pro_keywords_generate_locations_by_radius_' . $this->name, array( $this, 'ai_generate_locations' ), 10, 4 );

		// Register as a Dynamic Element.
		add_filter( 'page_generator_pro_shortcode_add_shortcodes', array( $this, 'add_shortcode' ) );

		// Register as an AI Dynamic Element Provider.
		add_filter( 'page_generator_pro_shortcode_ai_get_providers', array( $this, 'register_integration' ) );

		// Register as a Research Provider.
		add_filter( 'page_generator_pro_research_get_providers', array( $this, 'register_integration' ) );
		add_filter( 'page_generator_pro_research_get_providers_settings_fields', array( $this, 'ai_settings_fields' ) );
		add_filter( 'page_generator_pro_research_research_' . $this->name, array( $this, 'ai_research' ), 10, 10 );

		// Register as a Spintax Provider.
		add_filter( 'page_generator_pro_spintax_get_providers', array( $this, 'register_spintax_integration' ) );
		add_filter( 'page_generator_pro_spintax_get_providers_settings_fields', array( $this, 'ai_settings_fields' ) );
		add_filter( 'page_generator_pro_spintax_add_spintax_' . $this->name, array( $this, 'ai_add_spintax' ), 10, 2 );

	}

	/**
	 * Returns this shortcode / block's title.
	 *
	 * @since   4.6.0
	 *
	 * @return  string
	 */
	public function get_title() {

		return __( 'Gemini AI', 'page-generator-pro' );

	}

	/**
	 * Returns this shortcode / block's description.
	 *
	 * @since   4.6.0
	 *
	 * @return string
	 */
	public function get_description() {

		return __( 'Displays content from Gemini AI based on a topic.', 'page-generator-pro' );

	}

	/**
	 * Returns this shortcode / block's Keywords, excluding the title.
	 *
	 * @since   4.6.0
	 *
	 * @return  array
	 */
	public function get_keywords() {

		return array(
			__( 'Research', 'page-generator-pro' ),
			__( 'AI', 'page-generator-pro' ),
			__( 'Gemini AI', 'page-generator-pro' ),
		);

	}

	/**
	 * Returns this shortcode / block's icon.
	 *
	 * @since   4.6.0
	 *
	 * @return  string
	 */
	public function get_icon() {

		return 'assets/images/icons/gemini-ai.svg';

	}

	/**
	 * Returns an array of supported models for Gemini AI.
	 *
	 * @since   4.6.0
	 *
	 * @return  array
	 */
	public function get_models() {

		return array(
			// 3.0 Pro Preview.
			'gemini-3.0-pro-preview'         => __( 'Gemini 3.0 Pro Preview', 'page-generator-pro' ),

			// 2.5 Pro.
			'gemini-2.5-pro'                 => __( 'Gemini 2.5 Pro (Stable)', 'page-generator-pro' ),

			// 2.5 Flash.
			'gemini-2.5-flash'               => __( 'Gemini 2.5 Flash (Stable)', 'page-generator-pro' ),
			'gemini-2.5-flash-preview-05-20' => __( 'Gemini 2.5 Flash (Preview)', 'page-generator-pro' ),

			// 2.5 Flash Lite.
			'gemini-2.5-flash-lite'          => __( 'Gemini 2.5 Flash Lite (Stable)', 'page-generator-pro' ),
			'gemini-2.5-flash-lite-06-17'    => __( 'Gemini 2.5 Flash Lite (Preview)', 'page-generator-pro' ),

			// 2.0 Flash.
			'gemini-2.0-flash'               => __( 'Gemini 2.0 Flash (Latest)', 'page-generator-pro' ),
			'gemini-2.0-flash-001'           => __( 'Gemini 2.0 Flash (Stable, v1)', 'page-generator-pro' ),
			'gemini-2.0-flash-exp'           => __( 'Gemini 2.0 Flash (Experimental)', 'page-generator-pro' ),

			// 2.0 Flash Lite.
			'gemini-2.0-flash-lite'          => __( 'Gemini 2.0 Flash Lite (Latest)', 'page-generator-pro' ),
			'gemini-2.0-flash-lite-001'      => __( 'Gemini 2.0 Flash Lite (Stable, v1)', 'page-generator-pro' ),

			// 1.5 Flash.
			'gemini-1.5-flash'               => __( 'Gemini 1.5 Flash (Latest, stable)', 'page-generator-pro' ),
			'gemini-1.5-flash-latest'        => __( 'Gemini 1.5 Flash (Latest)', 'page-generator-pro' ),
			'gemini-1.5-flash-001'           => __( 'Gemini 1.5 Flash (Stable, v1)', 'page-generator-pro' ),
			'gemini-1.5-flash-002'           => __( 'Gemini 1.5 Flash (Stable, v2)', 'page-generator-pro' ),

			// 1.5 Flash-8B.
			'gemini-1.5-flash-8b'            => __( 'Gemini 1.5 Flash-8B (Latest, stable)', 'page-generator-pro' ),
			'gemini-1.5-flash-8b-latest'     => __( 'Gemini 1.5 Flash-8B (Latest)', 'page-generator-pro' ),
			'gemini-1.5-flash-8b-001'        => __( 'Gemini 1.5 Flash-8B (Stable, v1)', 'page-generator-pro' ),

			// 1.5 Pro.
			'gemini-1.5-pro'                 => __( 'Gemini 1.5 Pro (Latest, stable)', 'page-generator-pro' ),
			'gemini-1.5-pro-latest'          => __( 'Gemini 1.5 Pro (Latest)', 'page-generator-pro' ),
			'gemini-1.5-pro-001'             => __( 'Gemini 1.5 Pro (Stable, v1)', 'page-generator-pro' ),
			'gemini-1.5-pro-002'             => __( 'Gemini 1.5 Pro (Stable, v2)', 'page-generator-pro' ),

			// 1.0 Pro.
			'gemini-1.0-pro'                 => __( 'Gemini 1.0 Pro (Latest, stable)', 'page-generator-pro' ),
			'gemini-1.0-pro-latest'          => __( 'Gemini 1.0 Pro (Latest)', 'page-generator-pro' ),
			'gemini-1.0-pro-001'             => __( 'Gemini 1.0 Pro (Stable)', 'page-generator-pro' ),
		);

	}

	/**
	 * Sends a prompt to Gemini AI, with options to define the model and additional parameters.
	 *
	 * @since   4.6.0
	 *
	 * @param   string $prompt_text    Prompt Text.
	 * @param   string $model          Model.
	 * @param   array  $params         Additional request / query parameters e.g. temperature, top_p.
	 */
	private function query( $prompt_text, $model = 'gemini-1.0-pro', $params = array() ) {

		// Set Headers.
		$this->set_headers(
			array(
				'Content-Type' => 'application/json',
			)
		);

		// Calculate input tokens.
		$input_tokens = $this->ai_calculate_input_tokens( $params['messages'] );

		// Calculate maximum tokens, depending on the model used.
		// One token = ~ 4 characters.
		switch ( $model ) {
			case 'gemini-3.0-pro-preview':
			case 'gemini-2.5-pro':
			case 'gemini-2.5-flash':
			case 'gemini-2.5-flash-preview-05-20':
			case 'gemini-2.5-flash-lite':
			case 'gemini-2.5-flash-lite-06-17':
				// 65,536 token limit.
				$tokens = ( 65536 - $input_tokens );
				break;

			case 'gemini-2.0-flash':
			case 'gemini-2.0-flash-001':
			case 'gemini-2.0-flash-exp':
			case 'gemini-2.0-flash-lite':
			case 'gemini-2.0-flash-lite-001':
			case 'gemini-1.5-flash':
			case 'gemini-1.5-flash-latest':
			case 'gemini-1.5-flash-001':
			case 'gemini-1.5-flash-002':
			case 'gemini-1.5-flash-8b':
			case 'gemini-1.5-flash-8b-latest':
			case 'gemini-1.5-flash-8b-001':
			case 'gemini-1.5-pro':
			case 'gemini-1.5-pro-latest':
			case 'gemini-1.5-pro-001':
			case 'gemini-1.5-pro-002':
				// 8,192 token limit.
				$tokens = ( 8192 - $input_tokens );
				break;

			default:
				// 2,048 token limit.
				$tokens = ( 2048 - $input_tokens );
				break;
		}

		// If the remaining number of tokens is negative, return an error.
		if ( $tokens < 0 ) {
			return new WP_Error(
				'page_generator_pro_ai_error',
				sprintf(
					/* translators: Number of tokens remaining after prompt */
					__( 'The prompt is too long for the selected model. Please shorten the prompt and try again. Maximum tokens: %s', 'page-generator-pro' ),
					$tokens
				)
			);
		}

		// Define the API endpoint.
		switch ( $model ) {
			// v1.
			case 'gemini-2.5-flash-lite-06-17':
			case 'gemini-1.5-flash-001':
			case 'gemini-1.5-pro':
			case 'gemini-1.5-pro-001':
			case 'gemini-1.5-pro-002':
				$this->api_endpoint = 'https://generativelanguage.googleapis.com/v1';
				break;

			// v1beta.
			default:
				$this->api_endpoint = 'https://generativelanguage.googleapis.com/v1beta';
				break;
		}

		// Convert messages to contents for Gemini AI API.
		$contents = array();
		foreach ( $params['messages'] as $message ) {
			$contents[] = array(
				'role'  => $message['role'],
				'parts' => array(
					'text' => $message['content'],
				),
			);
		}

		// Build request parameters, which differ from most AI providers.
		$gemini_params = array(
			'contents'         => $contents,
			'generationConfig' => array(
				'maxOutputTokens' => $tokens,
				'temperature'     => $params['temperature'],
				'topP'            => $params['top_p'],
			),
		);

		// For some models, set reasoning to low.
		switch ( $model ) {
			case 'gemini-3.0-pro-preview':
				// https://ai.google.dev/gemini-api/docs/gemini-3?thinking=low#temperature.
				$gemini_params['generationConfig']['temperature'] = '1.0';

				// https://ai.google.dev/gemini-api/docs/gemini-3?thinking=low#thinking_level.
				$gemini_params['generationConfig']['thinkingConfig'] = array(
					'thinkingLevel' => 'low',
				);
				break;
		}

		// Make the request to the applicable endpoint.
		$data = $this->response(
			$this->post(
				'models/' . $model . ':generateContent?key=' . $this->ai_get_api_key(),
				$gemini_params
			)
		);

		// Bail if an error occured.
		if ( is_wp_error( $data ) ) {
			return $data;
		}

		// Fetch and return the text response.
		return trim( $data->candidates[0]->content->parts[0]->text );

	}

}
