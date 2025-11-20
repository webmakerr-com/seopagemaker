<?php
/**
 * Deepseek AI integration.
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Deepseek AI integration.
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 */
class Page_Generator_Pro_Deepseek extends Page_Generator_Pro_API {

	use Page_Generator_Pro_AI_Trait;
	use Page_Generator_Pro_Integration_Trait;
	use Page_Generator_Pro_Spintax_Trait;
	use Page_Generator_Pro_Shortcode_Trait;

	/**
	 * Holds the base object.
	 *
	 * @since   4.9.6
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
	public $name = 'deepseek';

	/**
	 * Holds the API endpoint
	 *
	 * @since   4.9.6
	 *
	 * @var     string
	 */
	public $api_endpoint = 'https://api.deepseek.com';

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
	 * @since   4.9.6
	 *
	 * @var     string
	 */
	public $account_url = 'https://platform.deepseek.com/api_keys';

	/**
	 * Holds the referal URL to use for users wanting to sign up
	 * to the API service.
	 *
	 * @since   4.9.6
	 *
	 * @var     string
	 */
	public $referral_url = 'https://platform.deepseek.com/sign_up';

	/**
	 * Holds the URL to the model documentation
	 *
	 * @since   5.1.2
	 *
	 * @var     string
	 */
	public $model_documentation_url = 'https://api-docs.deepseek.com/quick_start/pricing';

	/**
	 * Constructor.
	 *
	 * @since   4.9.6
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
	 * @since   4.9.6
	 *
	 * @return  string
	 */
	public function get_title() {

		return __( 'Deepseek', 'page-generator-pro' );

	}

	/**
	 * Returns this shortcode / block's description.
	 *
	 * @since   4.9.6
	 *
	 * @return string
	 */
	public function get_description() {

		return __( 'Displays content from Deepseek based on a topic.', 'page-generator-pro' );

	}

	/**
	 * Returns this shortcode / block's Keywords, excluding the title.
	 *
	 * @since   4.9.6
	 *
	 * @return  array
	 */
	public function get_keywords() {

		return array(
			__( 'Research', 'page-generator-pro' ),
			__( 'AI', 'page-generator-pro' ),
			__( 'Deepseek', 'page-generator-pro' ),
		);

	}

	/**
	 * Returns this shortcode / block's icon.
	 *
	 * @since   4.9.6
	 *
	 * @return  string
	 */
	public function get_icon() {

		return 'assets/images/icons/deepseek.svg';

	}

	/**
	 * Returns an array of supported models for Deepseek.
	 *
	 * @since   4.1.0
	 *
	 * @return  array
	 */
	public function get_models() {

		return array(
			'deepseek-chat' => __( 'Deepseek Chat', 'page-generator-pro' ),
		);

	}

	/**
	 * Sends a prompt to Deepseek, with options to define the model and additional parameters.
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

		// Calculate maximum tokens.
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
