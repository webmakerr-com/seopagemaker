<?php
/**
 * OpenAI integration.
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * OpenAI integration.
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 */
class Page_Generator_Pro_OpenAI extends Page_Generator_Pro_API {

	use Page_Generator_Pro_AI_Trait;
	use Page_Generator_Pro_Integration_Trait;
	use Page_Generator_Pro_Spintax_Trait;
	use Page_Generator_Pro_Shortcode_Trait;

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
	public $name = 'openai';

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
	 * Constructor.
	 *
	 * @since   3.9.2
	 *
	 * @param   object $base    Base Plugin Class.
	 */
	public function __construct( $base ) {

		// Store base class.
		$this->base = $base;

		// Support presence and frequency penalty attributes.
		$this->supports_presence_penalty  = true;
		$this->supports_frequency_penalty = true;

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

		// Attributes, fields, tabs and default values will match this shortcode, just used within the research shortcode.
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
	 * Returns the label of this integration.
	 *
	 * @since   4.1.2
	 *
	 * @return  string
	 */
	public function get_title() {

		return __( 'OpenAI', 'page-generator-pro' );

	}

	/**
	 * Returns this shortcode / block's description.
	 *
	 * @since   4.5.2
	 *
	 * @return string
	 */
	public function get_description() {

		return __( 'Displays content from OpenAI based on a topic.', 'page-generator-pro' );

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
			__( 'Research', 'page-generator-pro' ),
			__( 'AI', 'page-generator-pro' ),
			__( 'ChatGPT', 'page-generator-pro' ),
		);

	}

	/**
	 * Returns this shortcode / block's icon.
	 *
	 * @since   4.5.2
	 *
	 * @return  string
	 */
	public function get_icon() {

		return 'assets/images/icons/openai.svg';

	}

	/**
	 * Returns an array of supported models for OpenAI.
	 *
	 * @since   4.1.0
	 *
	 * @return  array
	 */
	public function get_models() {

		return array(
			// GPT-5.1 - 128,000 output tokens.
			'gpt-5.1'             => __( 'GPT-5.1', 'page-generator-pro' ),

			// GPT-5.1 Chat: Latest - 16,384 output tokens.
			'gpt-5.1-chat-latest' => __( 'GPT-5.1 Chat: Latest', 'page-generator-pro' ),

			// GPT-5 - 128,000 output tokens.
			'gpt-5'               => __( 'GPT-5', 'page-generator-pro' ),
			'gpt-5-mini'          => __( 'GPT-5 Mini', 'page-generator-pro' ),
			'gpt-5-nano'          => __( 'GPT-5 Nano', 'page-generator-pro' ),

			// GPT-5 Chat: Latest - 16,384 output tokens.
			'gpt-5-chat-latest'   => __( 'GPT-5 Chat: Latest', 'page-generator-pro' ),

			// GPT-4.1 - 32,768 output tokens.
			'gpt-4.1'             => __( 'GPT-4.1', 'page-generator-pro' ),
			'gpt-4.1-mini'        => __( 'GPT-4.1 Mini', 'page-generator-pro' ),
			'gpt-4.1-nano'        => __( 'GPT-4.1 Nano', 'page-generator-pro' ),

			// GPT-4o - 16,384 output tokens.
			'gpt-4o'              => __( 'GPT-4o', 'page-generator-pro' ),
			'chatgpt-4o-latest'   => __( 'GPT-4o: Latest (ChatGPT)', 'page-generator-pro' ),

			// GPT-4o Mini - 16,384 output tokens.
			'gpt-4o-mini'         => __( 'GPT-4o-mini', 'page-generator-pro' ),

			// o4 - 100,000 output tokens.
			'o4-mini'             => __( 'o4', 'page-generator-pro' ),

			// o3 = 100,000 output tokens.
			'o3'                  => __( 'o3', 'page-generator-pro' ),
			'o3-mini'             => __( 'o3-mini', 'page-generator-pro' ),

			// o1 - 100,000 output tokens.
			'o1'                  => __( 'o1', 'page-generator-pro' ),

			// o1-pro - 100,000 output tokens.
			'o1-pro'              => __( 'o1-pro', 'page-generator-pro' ),

			// GPT-4 Turbo - 4,096 output tokens.
			'gpt-4-turbo'         => __( 'GPT-4: Turbo', 'page-generator-pro' ),

			// GPT-4 - 8,192 output tokens.
			'gpt-4'               => __( 'GPT-4', 'page-generator-pro' ),

			// GPT-3.5 - 4,096 output tokens.
			'gpt-3.5-turbo'       => __( 'GPT-3.5: Turbo (ChatGPT)', 'page-generator-pro' ),
			'gpt-3.5-turbo-0125'  => __( 'GPT-3.5: Turbo (ChatGPT, gpt-3.5-turbo-0125)', 'page-generator-pro' ),
			'gpt-3.5-turbo-1106'  => __( 'GPT-3.5: Turbo (ChatGPT, gpt-3.5-turbo-1106)', 'page-generator-pro' ),
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

		// If no response provided, return the original time.
		if ( ! $response ) {
			return $pause;
		}

		// Return the original time if the request doesn't include an OpenAI header.
		if ( empty( wp_remote_retrieve_header( $response, 'openai-version' ) ) ) {
			return $pause;
		}

		// Get rate limits.
		// We deliberately add a second to the reset entries because they'll be rounded down using convert_to_seconds().
		$rate_limits = array(
			'requests_reset' => ( $this->convert_to_seconds( wp_remote_retrieve_header( $response, 'x-ratelimit-reset-requests' ) ) + 1 ),
			'tokens_reset'   => ( $this->convert_to_seconds( wp_remote_retrieve_header( $response, 'x-ratelimit-reset-tokens' ) ) + 1 ),
		);

		// Pause by the larger of the reset options.
		$pause = ( ( $rate_limits['tokens_reset'] > $rate_limits['requests_reset'] ) ? $rate_limits['tokens_reset'] : $rate_limits['requests_reset'] );

		// Return.
		return $pause;

	}

	/**
	 * Convert the given string duration to seconds.
	 *
	 * Examples:
	 * - 6m0
	 * - 2.451s
	 * - 6m2.451s
	 *
	 * @since   4.9.0
	 *
	 * @param   string $duration   OpenAI Header Reset Duration.
	 * @return  int
	 */
	public function convert_to_seconds( $duration ) {

		preg_match( '/(?:(\d+)h)?(?:(\d+)m)?(?:(\d+(?:\.\d+)?)s)?/', $duration, $matches );

		// Extract hours, minutes, and seconds, defaulting to 0 if not present.
		$hours   = isset( $matches[1] ) ? (int) $matches[1] : 0;
		$minutes = isset( $matches[2] ) ? (int) $matches[2] : 0;
		$seconds = isset( $matches[3] ) ? (int) $matches[3] : 0;

		// Convert to total seconds.
		return ( $hours * 3600 ) + ( $minutes * 60 ) + $seconds;

	}

	/**
	 * Sends a prompt to OpenAI, with options to define the model and additional parameters.
	 *
	 * @since   4.2.3
	 *
	 * @param   string $prompt_text    Prompt Text.
	 * @param   string $model          Model.
	 * @param   array  $params         Additional request / query parameters e.g. temperature, top_p.
	 */
	private function query( $prompt_text, $model = 'gpt-3.5-turbo', $params = array() ) {

		// Set Headers.
		$this->set_headers(
			array(
				'Authorization' => 'Bearer ' . $this->ai_get_api_key(),
				'Content-Type'  => 'application/json',
			)
		);

		// Calculate input tokens.
		$input_tokens = $this->ai_calculate_input_tokens( $params['messages'] );

		// Calculate maximum tokens, depending on the model used.
		// One token = ~ 4 characters.
		switch ( $model ) {
			// 128,000 output tokens.
			case 'gpt-5.1':
			case 'gpt-5':
			case 'gpt-5-mini':
			case 'gpt-5-nano':
				$tokens = ( 128000 - $input_tokens );
				break;

			// 100,000 output tokens.
			case 'o4-mini':
			case 'o3':
			case 'o3-mini':
			case 'o1':
			case 'o1-pro':
				$tokens = ( 100000 - $input_tokens );
				break;

			// 32,768 output tokens.
			case 'gpt-4.1':
			case 'gpt-4.1-mini':
			case 'gpt-4.1-nano':
				$tokens = ( 32768 - $input_tokens );
				break;

			// 16,384 output tokens.
			case 'gpt-5.1-chat-latest':
			case 'gpt-5-chat-latest':
			case 'gpt-4o':
			case 'chatgpt-4o-latest':
			case 'gpt-4o-mini':
				$tokens = ( 16384 - $input_tokens );
				break;

			// 8,192 output tokens.
			case 'gpt-4':
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

		// Determine parameter to use for maximum tokens.
		// Newer models use max_completion_tokens instead of max_tokens.
		switch ( $model ) {
			case 'o1':
			case 'o1-preview':
			case 'o1-mini':
			case 'o3-mini':
			case 'gpt-5':
			case 'gpt-5-mini':
			case 'gpt-5-nano':
			case 'gpt-5-chat-latest':
			case 'gpt-5.1':
			case 'gpt-5.1-chat-latest':
				$tokens_parameter = 'max_completion_tokens';
				break;

			default:
				$tokens_parameter = 'max_tokens';
				break;
		}

		// Make the request to the applicable endpoint.
		$data = $this->response(
			$this->post(
				'chat/completions',
				array_merge(
					array(
						$tokens_parameter => $tokens,
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
		return trim( $data->choices[0]->message->content );

	}

}
