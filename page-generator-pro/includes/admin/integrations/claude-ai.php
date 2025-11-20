<?php
/**
 * Claude AI API class.
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Generate articles based on keywords using ai-writer.com
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 4.5.9
 */
class Page_Generator_Pro_Claude_AI extends Page_Generator_Pro_API {

	use Page_Generator_Pro_AI_Trait;
	use Page_Generator_Pro_Integration_Trait;
	use Page_Generator_Pro_Spintax_Trait;
	use Page_Generator_Pro_Shortcode_Trait;

	/**
	 * Holds the base object.
	 *
	 * @since   4.5.9
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
	public $name = 'claude-ai';

	/**
	 * Holds the API endpoint
	 *
	 * @since   4.5.9
	 *
	 * @var     string
	 */
	public $api_endpoint = 'https://api.anthropic.com/v1';

	/**
	 * Holds the API version
	 *
	 * @since   4.5.9
	 *
	 * @var     string
	 */
	public $api_version = '2023-06-01';

	/**
	 * Holds the flag determining if the request data should be encoded
	 * into a JSON string
	 *
	 * If false, data is encoded using http_build_query()
	 *
	 * @since   4.5.9
	 *
	 * @var     bool
	 */
	public $is_json_request = true;

	/**
	 * Holds the account URL where users can obtain their API key
	 *
	 * @since   4.5.9
	 *
	 * @var     string
	 */
	public $account_url = 'https://console.anthropic.com/settings/keys';

	/**
	 * Holds the referal URL to use for users wanting to sign up
	 * to the API service.
	 *
	 * @since   4.5.9
	 *
	 * @var     string
	 */
	public $referral_url = 'https://console.anthropic.com/login';

	/**
	 * Holds the URL to the model documentation
	 *
	 * @since   5.1.2
	 *
	 * @var     string
	 */
	public $model_documentation_url = 'https://docs.anthropic.com/en/docs/about-claude/models/all-models';

	/**
	 * Constructor.
	 *
	 * @since   4.5.9
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
		add_filter( 'page_generator_pro_shortcode_research_get_attributes_' . $this->name, array( $this, 'get_attributes' ) );
		add_filter( 'page_generator_pro_shortcode_research_get_fields_' . $this->name, array( $this, 'get_fields' ) );
		add_filter( 'page_generator_pro_shortcode_research_get_tabs_' . $this->name, array( $this, 'get_tabs' ) );
		add_filter( 'page_generator_pro_shortcode_research_get_default_values_' . $this->name, array( $this, 'get_default_values' ) );
		add_filter( 'page_generator_pro_research_research_' . $this->name, array( $this, 'ai_research' ), 10, 10 );

		// Register as a Spintax Provider.
		add_filter( 'page_generator_pro_spintax_get_providers', array( $this, 'register_spintax_integration' ) );
		add_filter( 'page_generator_pro_spintax_get_providers_settings_fields', array( $this, 'ai_settings_fields' ) );
		add_filter( 'page_generator_pro_spintax_add_spintax_' . $this->name, array( $this, 'ai_add_spintax' ), 10, 2 );

		// Define the backoff time when a 429 or 529 rate limit is hit.
		add_filter( 'page_generator_pro_api_get_backoff_time', array( $this, 'rate_limit_backoff' ), 10, 3 );

	}

	/**
	 * Returns this shortcode / block's title.
	 *
	 * @since   4.5.9
	 *
	 * @return  string
	 */
	public function get_title() {

		return __( 'Claude AI', 'page-generator-pro' );

	}

	/**
	 * Returns this shortcode / block's description.
	 *
	 * @since   4.5.9
	 *
	 * @return string
	 */
	public function get_description() {

		return __( 'Displays content from Claude AI based on a topic.', 'page-generator-pro' );

	}

	/**
	 * Returns this shortcode / block's Keywords, excluding the title.
	 *
	 * @since   4.5.9
	 *
	 * @return  array
	 */
	public function get_keywords() {

		return array(
			__( 'Research', 'page-generator-pro' ),
			__( 'AI', 'page-generator-pro' ),
			__( 'Claude AI', 'page-generator-pro' ),
		);

	}

	/**
	 * Returns this shortcode / block's icon.
	 *
	 * @since   4.5.9
	 *
	 * @return  string
	 */
	public function get_icon() {

		return 'assets/images/icons/claude-ai.svg';

	}

	/**
	 * Returns an array of supported models for Claude AI.
	 *
	 * @since   4.5.9
	 *
	 * @return  array
	 */
	public function get_models() {

		return array(
			// CLaude 4.5.
			'claude-sonnet-4-5-20250929' => __( 'Claude Sonnet 4.5 (Latest)', 'page-generator-pro' ),

			// Claude 4.1.
			'claude-opus-4-1-20250805'   => __( 'Claude Opus 4.1 (Latest)', 'page-generator-pro' ),

			// Claude 4.
			'claude-opus-4-20250514'     => __( 'Claude Opus 4 (Latest)', 'page-generator-pro' ),
			'claude-sonnet-4-20250514'   => __( 'Claude Sonnet 4 (Latest)', 'page-generator-pro' ),

			// Claude 3.7.
			'claude-3-7-sonnet-latest'   => __( 'Claude 3.7 Sonnet (Latest)', 'page-generator-pro' ),

			// Claude 3.5.
			'claude-3-5-haiku-latest'    => __( 'Claude 3.5 Haiku (Latest)', 'page-generator-pro' ),
			'claude-3-5-sonnet-latest'   => __( 'Claude 3.5 Sonnet v2 (Latest)', 'page-generator-pro' ),

			// Claude 3.
			'claude-3-opus-latest'       => __( 'Claude 3 Opus', 'page-generator-pro' ),
			'claude-3-haiku-20240307'    => __( 'Claude 3 Haiku', 'page-generator-pro' ),
		);

	}

	/**
	 * Returns the number of seconds to pause before attempting
	 * a request again when the first request returned a 429
	 * rate limit reached.
	 *
	 * @since   4.9.0
	 *
	 * @param   int               $pause      Pause, in seconds.
	 * @param   bool|array        $response   HTTP response.
	 * @param   bool|string|array $body       HTTP response body. If JSON, this will be a non-decoded string.
	 * @return  int                           Pause, in seconds
	 */
	public function rate_limit_backoff( $pause, $response = false, $body = false ) {

		// Return the original time if the request doesn't include an Claude AI header.
		if ( empty( wp_remote_retrieve_header( $response, 'anthropic-ratelimit-requests-limit' ) ) ) {
			return $pause;
		}

		// Return the original time if Claude AI didn't return a retry-after header.
		if ( empty( wp_remote_retrieve_header( $response, 'retry-after' ) ) ) {
			return $pause;
		}

		// Pause for the required retry-after, in seconds.
		return (int) wp_remote_retrieve_header( $response, 'retry-after' );

	}

	/**
	 * Sends a prompt to Claude AI, with options to define the model and additional parameters.
	 *
	 * @since   4.5.9
	 *
	 * @param   string $prompt_text    Prompt Text.
	 * @param   string $model          Model.
	 * @param   array  $params         Additional request / query parameters e.g. temperature, top_p.
	 * @return  string
	 */
	private function query( $prompt_text, $model = 'claude-3-5-haiku-latest', $params = array() ) {

		// Set Headers.
		$this->set_headers(
			array(
				'x-api-key'         => $this->ai_get_api_key(),
				'anthropic-version' => $this->api_version,
				'Content-Type'      => 'application/json',
			)
		);

		// Remove some unused parameters.
		unset( $params['presence_penalty'], $params['frequency_penalty'] );

		// Calculate input tokens.
		$input_tokens = $this->ai_calculate_input_tokens( $params['messages'] );

		// Calculate maximum tokens, depending on the model used.
		// One token = ~ 4 characters.
		switch ( $model ) {
			case 'claude-opus-4-1-20250805':
			case 'claude-opus-4-20250514':
			case 'claude-sonnet-4-20250514':
				// 32,000 token limit.
				$tokens = ( 32000 - $input_tokens );
				break;

			case 'claude-sonnet-4-5-20250929':
			case 'claude-sonnet-4-20250514':
			case 'claude-3-7-sonnet-latest':
				// 64,000 token limit.
				$tokens = ( 64000 - $input_tokens );
				break;

			case 'claude-3-5-haiku-latest':
			case 'claude-3-5-sonnet-latest':
				// 8,192 token limit.
				$tokens = ( 8192 - $input_tokens );
				break;

			default:
				// 4,096 token limit.
				$tokens = ( 4096 - $input_tokens );
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

		// Remove some unsupported parameters, depending on the model.
		switch ( $model ) {
			case 'claude-sonnet-4-5-20250929':
				unset( $params['top_p'] );
				break;
		}

		// Make the request to the applicable endpoint.
		$data = $this->response(
			$this->post(
				'messages',
				array_merge(
					array(
						'model'      => $model,
						'messages'   => array(
							array(
								'role'    => 'user',
								'content' => $prompt_text,
							),
						),
						'max_tokens' => $tokens,
					),
					$params
				)
			)
		);

		// Bail if an error occured.
		if ( is_wp_error( $data ) ) {
			return $data;
		}

		// Fetch and return the text response.
		return trim( $data->content[0]->text );

	}

	/**
	 * Inspects the response from the API call, returning an error
	 * or data
	 *
	 * @since   5.2.8
	 *
	 * @param   WP_Error|object $response   Response.
	 * @return  WP_Error|object
	 */
	public function response( $response ) {

		// If the response is an error, return it.
		if ( is_wp_error( $response ) ) {
			return new WP_Error(
				'page_generator_pro_claude_ai_error',
				sprintf(
					'%s',
					$response->get_error_message()
				)
			);
		}

		// If an error occured, return it.
		if ( isset( $response->error ) ) {
			return new WP_Error(
				'page_generator_pro_claude_ai_error',
				sprintf(
					'%s: %s',
					$response->error->type,
					$response->error->message
				)
			);
		}

		return $response;

	}

}
