<?php
/**
 * Straico API class.
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Straico API class.
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 */
class Page_Generator_Pro_Straico extends Page_Generator_Pro_API {

	use Page_Generator_Pro_AI_Trait;
	use Page_Generator_Pro_Integration_Trait;
	use Page_Generator_Pro_Spintax_Trait;
	use Page_Generator_Pro_Shortcode_Trait;

	/**
	 * Holds the base object.
	 *
	 * @since   5.2.7
	 *
	 * @var     object
	 */
	public $base;

	/**
	 * Holds the programmatic name of this integration.
	 *
	 * @since   5.2.7
	 *
	 * @var     string
	 */
	public $name = 'straico';

	/**
	 * Holds the API endpoint
	 *
	 * @since   5.2.7
	 *
	 * @var     string
	 */
	public $api_endpoint = 'https://api.straico.com/v1';

	/**
	 * Holds the flag determining if the request data should be encoded
	 * into a JSON string
	 *
	 * If false, data is encoded using http_build_query()
	 *
	 * @since   5.2.7
	 *
	 * @var     bool
	 */
	public $is_json_request = true;

	/**
	 * Holds the account URL where users can obtain their API key
	 *
	 * @since   5.2.7
	 *
	 * @var     string
	 */
	public $account_url = 'https://platform.straico.com/settings-api';

	/**
	 * Holds the referal URL to use for users wanting to sign up
	 * to the API service.
	 *
	 * @since   5.2.7
	 *
	 * @var     string
	 */
	public $referral_url = 'https://straico.com/';

	/**
	 * Holds the URL to the model documentation
	 *
	 * @since   5.2.7
	 *
	 * @var     string
	 */
	public $model_documentation_url = 'https://straico.com/multimodel/';

	/**
	 * Constructor.
	 *
	 * @since   5.2.7
	 *
	 * @param   object $base    Base Plugin Class.
	 */
	public function __construct( $base ) {

		// Store base class.
		$this->base = $base;

		// Don't support presence and frequency penalty attributes.
		$this->supports_presence_penalty  = false;
		$this->supports_frequency_penalty = false;

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

	}

	/**
	 * Returns this shortcode / block's title.
	 *
	 * @since   5.2.7
	 *
	 * @return  string
	 */
	public function get_title() {

		return __( 'Straico', 'page-generator-pro' );

	}

	/**
	 * Returns this shortcode / block's description.
	 *
	 * @since   5.2.7
	 *
	 * @return string
	 */
	public function get_description() {

		return __( 'Displays content from Straico based on a topic.', 'page-generator-pro' );

	}

	/**
	 * Returns this shortcode / block's Keywords, excluding the title.
	 *
	 * @since   5.2.7
	 *
	 * @return  array
	 */
	public function get_keywords() {

		return array(
			__( 'Research', 'page-generator-pro' ),
			__( 'AI', 'page-generator-pro' ),
			__( 'Straico', 'page-generator-pro' ),
		);

	}

	/**
	 * Returns this shortcode / block's icon.
	 *
	 * @since   5.2.7
	 *
	 * @return  string
	 */
	public function get_icon() {

		return 'assets/images/icons/straico.svg';

	}

	/**
	 * Returns an array of supported models for Straico.
	 *
	 * @since   5.2.7
	 *
	 * @return  array
	 */
	public function get_models() {

		return array(
			'amazon/nova-lite-v1'                          => __( 'Amazon: Nova Lite 1.0', 'page-generator-pro' ),
			'amazon/nova-micro-v1'                         => __( 'Amazon: Nova Micro 1.0', 'page-generator-pro' ),
			'amazon/nova-pro-v1'                           => __( 'Amazon: Nova Pro 1.0', 'page-generator-pro' ),
			'anthropic/claude-3-opus'                      => __( 'Anthropic: Claude 3 Opus', 'page-generator-pro' ),
			'anthropic/claude-3-5-haiku-20241022'          => __( 'Anthropic: Claude 3.5 Haiku', 'page-generator-pro' ),
			'anthropic/claude-3.5-sonnet'                  => __( 'Anthropic: Claude 3.5 Sonnet', 'page-generator-pro' ),
			'anthropic/claude-3.7-sonnet:thinking'         => __( 'Anthropic: Claude 3.7 Sonnet Reasoning (High)', 'page-generator-pro' ),
			'anthropic/claude-3.7-sonnet'                  => __( 'Anthropic: Claude 3.7 Sonnet Reasoning (Medium)', 'page-generator-pro' ),
			'anthropic/claude-sonnet-4'                    => __( 'Anthropic: Claude Sonnet 4', 'page-generator-pro' ),
			'cohere/command-r-08-2024'                     => __( 'Cohere: Command R (08-2024)', 'page-generator-pro' ),
			'cohere/command-r-plus-08-2024'                => __( 'Cohere: Command R+ (08-2024)', 'page-generator-pro' ),
			'deepseek/deepseek-chat'                       => __( 'DeepSeek V3', 'page-generator-pro' ),
			'deepseek/deepseek-r1'                         => __( 'DeepSeek: DeepSeek R1 Reasoning', 'page-generator-pro' ),
			'deepseek/deepseek-r1:nitro'                   => __( 'DeepSeek: DeepSeek R1 Reasoning (nitro)', 'page-generator-pro' ),
			'deepseek/deepseek-chat-v3-0324'               => __( 'DeepSeek: DeepSeek V3 0324', 'page-generator-pro' ),
			'deepseek/deepseek-chat-v3.1'                  => __( 'DeepSeek: DeepSeek V3.1', 'page-generator-pro' ),
			'cognitivecomputations/dolphin-mixtral-8x7b'   => __( 'Dolphin 2.6 Mixtral 8x7B', 'page-generator-pro' ),
			'alpindale/goliath-120b'                       => __( 'Goliath 120B', 'page-generator-pro' ),
			'google/gemini-2.5-flash'                      => __( 'Google: Gemini 2.5 Flash', 'page-generator-pro' ),
			'google/gemini-2.5-flash-lite'                 => __( 'Google: Gemini 2.5 Flash Lite', 'page-generator-pro' ),
			'google/gemini-2.0-flash-001'                  => __( 'Google: Gemini Flash 2.08B', 'page-generator-pro' ),
			'google/gemini-pro-1.5'                        => __( 'Google: Gemini Pro 1.5', 'page-generator-pro' ),
			'google/gemini-2.5-pro-preview'                => __( 'Google: Gemini Pro 2.5 Reasoning (Preview)', 'page-generator-pro' ),
			'google/gemma-2-27b-it'                        => __( 'Google: Gemma 2 27B', 'page-generator-pro' ),
			'gryphe/mythomax-l2-13b'                       => __( 'Gryphe: MythoMax L2 13B 8k', 'page-generator-pro' ),
			'meta-llama/llama-3-70b-instruct:nitro'        => __( 'Meta: Llama 3 70B Instruct (nitro)', 'page-generator-pro' ),
			'meta-llama/llama-3.1-405b-instruct'           => __( 'Meta: Llama 3.1 405B Instruct', 'page-generator-pro' ),
			'meta-llama/llama-3.1-70b-instruct'            => __( 'Meta: Llama 3.1 70B Instruct', 'page-generator-pro' ),
			'meta-llama/llama-3.3-70b-instruct'            => __( 'Meta: Llama 3.3 70B Instruct', 'page-generator-pro' ),
			'meta-llama/llama-4-maverick'                  => __( 'Meta: Llama 4 Maverick', 'page-generator-pro' ),
			'microsoft/phi-4'                              => __( 'Microsoft: Phi 4', 'page-generator-pro' ),
			'mistralai/codestral-mamba'                    => __( 'Mistral: Codestral Mamba', 'page-generator-pro' ),
			'mistralai/mistral-medium-3'                   => __( 'Mistral: Mistral Medium 3', 'page-generator-pro' ),
			'mistralai/mixtral-8x7b-instruct'              => __( 'Mistral: Mixtral 8x7B', 'page-generator-pro' ),
			'moonshotai/kimi-k2:free'                      => __( 'MoonshotAI: Kimi K2 0711', 'page-generator-pro' ),
			'moonshotai/kimi-k2-0905'                      => __( 'MoonshotAI: Kimi K2 0905', 'page-generator-pro' ),
			'nvidia/llama-3.1-nemotron-70b-instruct'       => __( 'NVIDIA: Llama 3.1 Nemotron 70B Instruct', 'page-generator-pro' ),
			'nvidia/llama-3.1-nemotron-ultra-253b-v1'      => __( 'NVIDIA: Llama 3.1 Nemotron Ultra 253B v1', 'page-generator-pro' ),
			'nvidia/llama-3.3-nemotron-super-49b-v1'       => __( 'NVIDIA: Llama 3.3 Nemotron Super 49B v1', 'page-generator-pro' ),
			'openai/gpt-4.1'                               => __( 'OpenAI: GPT-4.1', 'page-generator-pro' ),
			'openai/gpt-4.1-mini'                          => __( 'OpenAI: GPT-4.1 Mini', 'page-generator-pro' ),
			'openai/gpt-4.1-nano'                          => __( 'OpenAI: GPT-4.1 Nano', 'page-generator-pro' ),
			'openai/gpt-4o-2024-08-06'                     => __( 'OpenAI: GPT-4o - (Aug-06)', 'page-generator-pro' ),
			'openai/gpt-4o-2024-11-20'                     => __( 'OpenAI: GPT-4o - (Nov-20)', 'page-generator-pro' ),
			'openai/gpt-4o-mini'                           => __( 'OpenAI: GPT-4o mini', 'page-generator-pro' ),
			'openai/gpt-5'                                 => __( 'OpenAI: GPT-5', 'page-generator-pro' ),
			'openai/gpt-5-chat'                            => __( 'OpenAI: GPT-5 Chat', 'page-generator-pro' ),
			'openai/gpt-5-mini'                            => __( 'OpenAI: GPT-5 Mini', 'page-generator-pro' ),
			'openai/gpt-5-nano'                            => __( 'OpenAI: GPT-5 Nano', 'page-generator-pro' ),
			'openai/o1'                                    => __( 'OpenAI: o1', 'page-generator-pro' ),
			'openai/o1-pro'                                => __( 'OpenAI: o1 High Reasoning', 'page-generator-pro' ),
			'openai/o1-mini'                               => __( 'OpenAI: o1 mini', 'page-generator-pro' ),
			'o3-2025-04-16'                                => __( 'OpenAI: o3', 'page-generator-pro' ),
			'o3-deep-research-2025-06-26'                  => __( 'OpenAI: o3 Deep Research', 'page-generator-pro' ),
			'openai/o3-mini-high'                          => __( 'OpenAI: o3 Mini (High)', 'page-generator-pro' ),
			'openai/o3-mini'                               => __( 'OpenAI: o3 Mini (Medium)', 'page-generator-pro' ),
			'openai/o4-mini'                               => __( 'OpenAI: o4 Mini', 'page-generator-pro' ),
			'openai/o4-mini-high'                          => __( 'OpenAI: o4 Mini High', 'page-generator-pro' ),
			'perplexity/llama-3.1-sonar-small-128k-online' => __( 'Perplexity: Llama 3.1 Sonar 8B Online', 'page-generator-pro' ),
			'perplexity/sonar'                             => __( 'Perplexity: Sonar', 'page-generator-pro' ),
			'perplexity/sonar-deep-research'               => __( 'Perplexity: Sonar Deep Research Reasoning', 'page-generator-pro' ),
			'perplexity/sonar-reasoning'                   => __( 'Perplexity: Sonar Reasoning', 'page-generator-pro' ),
			'qwen/qwen-2-72b-instruct'                     => __( 'Qwen 2 72B Instruct', 'page-generator-pro' ),
			'qwen/qwen-2-vl-72b-instruct'                  => __( 'Qwen2-VL 72B Instruct', 'page-generator-pro' ),
			'qwen/qwen-2.5-72b-instruct'                   => __( 'Qwen2.5 72B Instruct', 'page-generator-pro' ),
			'qwen/qwen2.5-vl-32b-instruct:free'            => __( 'Qwen: Qwen2.5 VL 32B Instruct', 'page-generator-pro' ),
			'qwen/qwen3-235b-a22b'                         => __( 'Qwen: Qwen3 235B A22B Reasoning', 'page-generator-pro' ),
			'qwen/qwen3-coder'                             => __( 'Qwen: Qwen3 Coder', 'page-generator-pro' ),
			'microsoft/wizardlm-2-8x22b'                   => __( 'WizardLM-2 8x22B', 'page-generator-pro' ),
			'z-ai/glm-4.5v'                                => __( 'Z.AI: GLM 4.5V Reasoning', 'page-generator-pro' ),
			'x-ai/grok-2-1212'                             => __( 'xAI: Grok 2 1212', 'page-generator-pro' ),
			'x-ai/grok-3-beta'                             => __( 'xAI: Grok 3 Beta', 'page-generator-pro' ),
			'x-ai/grok-3-mini-beta'                        => __( 'xAI: Grok 3 Mini Beta Reasoning', 'page-generator-pro' ),
			'x-ai/grok-4'                                  => __( 'xAI: Grok 4 Reasoning', 'page-generator-pro' ),
		);

	}

	/**
	 * Sends a prompt to Straico, with options to define the model and additional parameters.
	 *
	 * @since   5.2.7
	 *
	 * @param   string $prompt_text    Prompt Text.
	 * @param   string $model          Model.
	 * @param   array  $params         Additional request / query parameters e.g. temperature, top_p.
	 */
	private function query( $prompt_text, $model = 'openai/gpt-4o-mini', $params = array() ) {

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
		$tokens = ( 16384 - $input_tokens );

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

		// Build arguments.
		$args = array_merge(
			array(
				'models'     => array(
					$model,
				),
				'max_tokens' => $tokens,
			),
			$params
		);

		// Remove unused arguments.
		unset( $args['model'], $args['top_p'], $args['presence_penalty'], $args['frequency_penalty'] );

		// Make the request to the applicable endpoint.
		$data = $this->response(
			$this->post(
				'chat/completions',
				$args
			),
			$model
		);

		// Bail if an error occured.
		if ( is_wp_error( $data ) ) {
			return $data;
		}

		// Fetch and return the text response.
		return trim( $data->data->completions->$model->completion->choices[0]->message->content );

	}

	/**
	 * Inspects the response from the API call, returning an error
	 * or data
	 *
	 * @since   5.2.7
	 *
	 * @param   WP_Error|object $response   Response.
	 * @param   string          $model      Model used for request.
	 * @return  WP_Error|object
	 */
	public function response( $response, $model = 'openai/gpt-4o-mini' ) {

		// If the response is an error, return it.
		if ( is_wp_error( $response ) ) {
			return new WP_Error(
				'page_generator_pro_straico_error',
				sprintf(
					/* translators: Error message */
					__( 'Straico: %s', 'page-generator-pro' ),
					$response->get_error_message()
				)
			);
		}

		// If an error occured, return it.
		if ( isset( $response->error ) ) {
			return new WP_Error(
				'page_generator_pro_straico_error',
				sprintf(
					/* translators: Error message */
					__( 'Straico: %s', 'page-generator-pro' ),
					$response->error->completions->$model->completion->error
				)
			);
		}

		return $response;

	}

}
