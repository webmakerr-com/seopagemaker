<?php
/**
 * Alibaba API class.
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Generate articles based on keywords using ai-writer.com
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 4.9.7
 */
class Page_Generator_Pro_Alibaba extends Page_Generator_Pro_API {

	use Page_Generator_Pro_AI_Trait;
	use Page_Generator_Pro_Integration_Trait;
	use Page_Generator_Pro_Spintax_Trait;
	use Page_Generator_Pro_Shortcode_Trait;

	/**
	 * Holds the base object.
	 *
	 * @since   4.9.7
	 *
	 * @var     object
	 */
	public $base;

	/**
	 * Holds the programmatic name of this integration.
	 *
	 * @since   4.9.7
	 *
	 * @var     string
	 */
	public $name = 'alibaba';

	/**
	 * Holds the API endpoint
	 *
	 * @since   4.9.7
	 *
	 * @var     string
	 */
	public $api_endpoint = 'https://dashscope-intl.aliyuncs.com/compatible-mode/v1';

	/**
	 * Holds the flag determining if the request data should be encoded
	 * into a JSON string
	 *
	 * If false, data is encoded using http_build_query()
	 *
	 * @since   4.9.7
	 *
	 * @var     bool
	 */
	public $is_json_request = true;

	/**
	 * Holds the account URL where users can obtain their API key
	 *
	 * @since   4.9.7
	 *
	 * @var     string
	 */
	public $account_url = 'https://bailian.console.alibabacloud.com/?apiKey=1#/api-key';

	/**
	 * Holds the referal URL to use for users wanting to sign up
	 * to the API service.
	 *
	 * @since   4.9.7
	 *
	 * @var     string
	 */
	public $referral_url = 'https://account.alibabacloud.com/register/intl_register.htm';

	/**
	 * Holds the URL to the model documentation
	 *
	 * @since   5.1.2
	 *
	 * @var     string
	 */
	public $model_documentation_url = 'https://www.alibabacloud.com/en/solutions/generative-ai/';

	/**
	 * Constructor.
	 *
	 * @since   4.9.7
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

	}

	/**
	 * Returns this shortcode / block's title.
	 *
	 * @since   4.9.7
	 *
	 * @return  string
	 */
	public function get_title() {

		return __( 'Alibaba', 'page-generator-pro' );

	}

	/**
	 * Returns this shortcode / block's description.
	 *
	 * @since   4.9.7
	 *
	 * @return string
	 */
	public function get_description() {

		return __( 'Displays content from Alibaba Cloud Model AI based on a topic.', 'page-generator-pro' );

	}

	/**
	 * Returns this shortcode / block's Keywords, excluding the title.
	 *
	 * @since   4.9.7
	 *
	 * @return  array
	 */
	public function get_keywords() {

		return array(
			__( 'Research', 'page-generator-pro' ),
			__( 'AI', 'page-generator-pro' ),
			__( 'Alibaba', 'page-generator-pro' ),
		);

	}

	/**
	 * Returns this shortcode / block's icon.
	 *
	 * @since   4.9.7
	 *
	 * @return  string
	 */
	public function get_icon() {

		return 'assets/images/icons/alibaba.svg';

	}

	/**
	 * Returns an array of supported models for Alibaba.
	 *
	 * @since   4.9.7
	 *
	 * @return  array
	 */
	public function get_models() {

		return array(
			'Qwen-Max-2025-01-25'     => __( 'Qwen-Max-2025-01-25 (Latest)', 'page-generator-pro' ),
			'Qwen2.5-7B-Instruct-1M'  => __( 'Qwen2.5-7B-Instruct-1M', 'page-generator-pro' ),
			'Qwen2.5-14B-Instruct-1M' => __( 'Qwen2.5-14B-Instruct-1M', 'page-generator-pro' ),
			'Qwen-Max'                => __( 'Qwen-Max', 'page-generator-pro' ),
			'Qwen-Plus'               => __( 'Qwen-Plus', 'page-generator-pro' ),
			'Qwen-Turbo'              => __( 'Qwen-Turbo', 'page-generator-pro' ),
			'Qwen2.5-72B-Instruct'    => __( 'Qwen2.5-72B-Instruct', 'page-generator-pro' ),
			'Qwen2.5-32B-Instruct'    => __( 'Qwen2.5-32B-Instruct', 'page-generator-pro' ),
			'Qwen2.5-14B-Instruct'    => __( 'Qwen2.5-14B-Instruct', 'page-generator-pro' ),
			'Qwen2.5-7B-Instruct'     => __( 'Qwen2.5-7B-Instruct', 'page-generator-pro' ),
			'Qwen2-72B-Instruct'      => __( 'Qwen2-72B-Instruct', 'page-generator-pro' ),
			'Qwen2-57B-A14B-Instruct' => __( 'Qwen2-57B-A14B-Instruct', 'page-generator-pro' ),
			'Qwen2-7B-Instruct'       => __( 'Qwen2-7B-Instruct', 'page-generator-pro' ),
			'Qwen1.5-110B-Chat'       => __( 'Qwen1.5-110B-Chat', 'page-generator-pro' ),
			'Qwen1.5-7B-Chat'         => __( 'Qwen1.5-7B-Chat', 'page-generator-pro' ),
			'Qwen1.5-72B-Chat'        => __( 'Qwen1.5-72B-Chat', 'page-generator-pro' ),
			'Qwen1.5-32B-Chat'        => __( 'Qwen1.5-32B-Chat', 'page-generator-pro' ),
			'Qwen1.5-14B-Chat'        => __( 'Qwen1.5-14B-Chat', 'page-generator-pro' ),
		);

	}

	/**
	 * Sends a prompt to Alibaba, with options to define the model and additional parameters.
	 *
	 * @since   4.9.7
	 *
	 * @param   string $prompt_text    Prompt Text.
	 * @param   string $model          Model.
	 * @param   array  $params         Additional request / query parameters e.g. temperature, top_p.
	 */
	private function query( $prompt_text, $model = 'Qwen-Max-2025-01-25', $params = array() ) {

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
		$tokens = ( 8192 - $input_tokens );

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

		// Make the request to the applicable endpoint.
		$data = $this->response(
			$this->post(
				'chat/completions',
				array_merge(
					array(
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
		return trim( $data->choices[0]->message->content );

	}

}
