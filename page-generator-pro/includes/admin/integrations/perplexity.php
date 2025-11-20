<?php
/**
 * Perplexity integration.
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Perplexity integration.
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 */
class Page_Generator_Pro_Perplexity extends Page_Generator_Pro_API {

	use Page_Generator_Pro_AI_Trait;
	use Page_Generator_Pro_Integration_Trait;
	use Page_Generator_Pro_Spintax_Trait;
	use Page_Generator_Pro_Shortcode_Trait;

	/**
	 * Holds the base object.
	 *
	 * @since   4.9.3
	 *
	 * @var     object
	 */
	public $base;

	/**
	 * Holds the programmatic name of this integration.
	 *
	 * @since   4.9.3
	 *
	 * @var     string
	 */
	public $name = 'perplexity';

	/**
	 * Holds the API endpoint
	 *
	 * @since   4.9.3
	 *
	 * @var     string
	 */
	public $api_endpoint = 'https://api.perplexity.ai';

	/**
	 * Holds the flag determining if the request data should be encoded
	 * into a JSON string
	 *
	 * If false, data is encoded using http_build_query()
	 *
	 * @since   4.9.3
	 *
	 * @var     bool
	 */
	public $is_json_request = true;

	/**
	 * Holds the account URL where users can obtain their API key
	 *
	 * @since   4.9.3
	 *
	 * @var     string
	 */
	public $account_url = 'https://www.perplexity.ai/settings/api';

	/**
	 * Holds the referal URL to use for users wanting to sign up
	 * to the API service.
	 *
	 * @since   4.9.3
	 *
	 * @var     string
	 */
	public $referral_url = 'https://www.perplexity.ai';

	/**
	 * Holds the URL to the model documentation
	 *
	 * @since   5.1.2
	 *
	 * @var     string
	 */
	public $model_documentation_url = 'https://docs.perplexity.ai/models/model-cards';

	/**
	 * Constructor.
	 *
	 * @since   4.9.3
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

	}

	/**
	 * Returns the label of this integration.
	 *
	 * @since   4.9.3
	 *
	 * @return  string
	 */
	public function get_title() {

		return __( 'Perplexity', 'page-generator-pro' );

	}

	/**
	 * Returns this shortcode / block's description.
	 *
	 * @since   4.9.3
	 *
	 * @return string
	 */
	public function get_description() {

		return __( 'Displays content from Perplexity based on a topic.', 'page-generator-pro' );

	}

	/**
	 * Returns this shortcode / block's Keywords, excluding the title.
	 *
	 * @since   4.9.3
	 *
	 * @return  array
	 */
	public function get_keywords() {

		return array(
			__( 'Research', 'page-generator-pro' ),
			__( 'AI', 'page-generator-pro' ),
			__( 'Perplexity', 'page-generator-pro' ),
		);

	}

	/**
	 * Returns this shortcode / block's icon.
	 *
	 * @since   4.9.3
	 *
	 * @return  string
	 */
	public function get_icon() {

		return 'assets/images/icons/perplexity.svg';

	}

	/**
	 * Returns this shortcode / block's Default Values
	 *
	 * @since   4.9.3
	 */
	public function get_provider_default_values() {

		return array(
			// General.
			'topic'             => '',
			'content_type'      => 'article',
			'limit'             => 250,
			'language'          => 'en',

			// Tuning.
			'temperature'       => 1,
			'top_p'             => 1,
			'presence_penalty'  => 0,
			'frequency_penalty' => 1,
		);

	}

	/**
	 * Returns an array of supported models for Perplexity.
	 *
	 * @since   4.9.3
	 *
	 * @return  array
	 */
	public function get_models() {

		return array(
			'sonar-pro'                         => __( 'Sonar Pro', 'page-generator-pro' ),
			'sonar'                             => __( 'Sonar', 'page-generator-pro' ),
			'llama-3.1-sonar-small-128k-online' => __( 'llama-3.1-sonar-small-128k-online', 'page-generator-pro' ),
			'llama-3.1-sonar-large-128k-online' => __( 'llama-3.1-sonar-large-128k-online', 'page-generator-pro' ),
			'llama-3.1-sonar-huge-128k-online'  => __( 'llama-3.1-sonar-huge-128k-online', 'page-generator-pro' ),
		);

	}

	/**
	 * Sends a prompt to Perplexity, with options to define the model and additional parameters.
	 *
	 * @since   4.9.3
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

		// Calculate maximum tokens.
		// One token = ~ 4 characters.
		switch ( $model ) {
			case 'sonar-pro':
				// 200,000 token limit.
				$tokens = ( 200000 - $input_tokens );
				break;

			default:
				// 127072 token limit.
				$tokens = ( 127072 - $input_tokens );
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

		$params = array_merge(
			array(
				'max_tokens' => $tokens,
			),
			$params
		);

		// If frequency_penalty is zero, set it to a number higher than zero
		// otherwise the API will error.
		if ( ! $params['frequency_penalty'] ) {
			$params['frequency_penalty'] = '0.1';
		}

		// If there is more than one message in the `messages` key, Perplexity requires different roles for each.
		if ( count( $params['messages'] ) > 1 ) {
			foreach ( $params['messages'] as $key => $message ) {
				$params['messages'][ $key ]['role'] = $key % 2 === 0 ? 'system' : 'user';
			}
		}

		// Make the request to the applicable endpoint.
		$data = $this->response(
			$this->post(
				'chat/completions',
				$params
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
