<?php
/**
 * Spinnerchief API Class.
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Generate spintax using spinnerchief.com
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 2.3.1
 */
class Page_Generator_Pro_SpinnerChief extends Page_Generator_Pro_API {

	use Page_Generator_Pro_Integration_Trait;
	use Page_Generator_Pro_Spintax_Trait;

	/**
	 * Holds the base object.
	 *
	 * @since   2.3.1
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
	public $name = 'spinnerchief';

	/**
	 * Holds the API endpoint
	 *
	 * @since   2.3.1
	 *
	 * @var     string
	 */
	public $api_endpoint = 'https://www.spinnerchief.com/api';

	/**
	 * Holds the account URL where users can obtain their API key
	 *
	 * @since   2.3.1
	 *
	 * @var     string
	 */
	public $account_url = 'https://www.spinnerchief.com/api/index#Paraphraser';

	/**
	 * Holds the referal URL to use for users wanting to sign up
	 * to SpinnerChief's service.
	 *
	 * @since   2.3.1
	 *
	 * @var     string
	 */
	public $referral_url = 'http://www.whitehatbox.com/Agents/SSS?code=0vbtYQiezQ69rR4wkFq6AQs9StMsnOWJZae2sjYH%2BH%2B0DfOPc1i%2BBw==';

	/**
	 * Holds the flag determining if the request data should be encoded
	 * into a JSON string
	 *
	 * If false, data is encoded using http_build_query()
	 *
	 * @since   2.8.9
	 *
	 * @var     bool
	 */
	public $is_json_request = false;

	/**
	 * Constructor.
	 *
	 * @since   2.3.1
	 *
	 * @param   object $base    Base Plugin Class.
	 */
	public function __construct( $base ) {

		// Store base class.
		$this->base = $base;

		// Register as a Spintax Provider.
		add_filter( 'page_generator_pro_spintax_get_providers', array( $this, 'register_spintax_integration' ) );
		add_filter( 'page_generator_pro_spintax_get_providers_settings_fields', array( $this, 'get_settings_fields' ) );
		add_filter( 'page_generator_pro_spintax_add_spintax_' . $this->name, array( $this, 'add_spintax' ), 10, 2 );

	}

	/**
	 * Returns the label of this integration.
	 *
	 * @since   4.8.0
	 *
	 * @return  string
	 */
	public function get_title() {

		return __( 'SpinnerChief', 'page-generator-pro' );

	}

	/**
	 * Returns settings fields and their values to display at Settings > Spintax for this spintax provider.
	 *
	 * @since   3.9.1
	 *
	 * @param   array $settings_fields    Spintax Settings Fields.
	 * @return  array                       Spintax Settings Fields
	 */
	public function get_settings_fields( $settings_fields ) {

		// Define fields and their values.
		$settings_fields[ $this->get_name() ] = array(
			$this->get_settings_prefix() . '_api_key' => array(
				'label'         => __( 'API Key', 'page-generator-pro' ),
				'type'          => 'text',
				'default_value' => $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-spintax', $this->get_settings_prefix() . '_api_key' ),
				'description'   => sprintf(
					'%s %s %s %s %s',
					esc_html__( 'The SpinnerChief API Key.', 'page-generator-pro' ),
					'<a href="https://www.spinnerchief.com/api/index#Paraphraser" target="_blank" rel="noopener">' . esc_html__( 'Get your API Key', 'page-generator-pro' ) . '</a>',
					esc_html__( 'or', 'page-generator-pro' ),
					'<a href="' . esc_attr( $this->get_registration_url() ) . '" target="_blank" rel="noopener">' . esc_html__( 'Register an account', 'page-generator-pro' ) . '</a>',
					esc_html__( 'if you don\'t have one.', 'page-generator-pro' )
				),
			),
			$this->get_settings_prefix() . '_dev_key' => array(
				'label'         => __( 'Developer Key', 'page-generator-pro' ),
				'type'          => 'text',
				'default_value' => $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-spintax', $this->get_settings_prefix() . '_dev_key' ),
				'description'   => sprintf(
					'%s %s %s %s %s',
					esc_html__( 'The SpinnerChief Developer Key.', 'page-generator-pro' ),
					'<a href="https://www.spinnerchief.com/api/index#Paraphraser" target="_blank" rel="noopener">' . esc_html__( 'Get your Developer Key', 'page-generator-pro' ) . '</a>',
					esc_html__( 'or', 'page-generator-pro' ),
					'<a href="' . esc_attr( $this->get_registration_url() ) . '" target="_blank" rel="noopener">' . esc_html__( 'Register an account', 'page-generator-pro' ) . '</a>',
					esc_html__( 'if you don\'t have one.', 'page-generator-pro' )
				),
			),
		);

		return $settings_fields;

	}

	/**
	 * Adds spintax to the given content using the Spinnerchief API
	 *
	 * @since   3.9.1
	 *
	 * @param   string $content            Content.
	 * @param   array  $protected_words    Protected Words.
	 * @return  WP_Error|string
	 */
	public function add_spintax( $content, $protected_words ) {

		return $this->text_with_spintax( $content, array(), $protected_words );

	}

	/**
	 * Returns a spintax version of the given non-spintax text, that can be later processed.
	 *
	 * @since   2.3.1
	 *
	 * @param   string     $text               Original non-spintax Text.
	 * @param   array      $params             Spin Parameters.
	 *         int     $spinfreq                   Spin Frequency.
	 *         int     $wordquality                Word Quality.
	 *                                             0: Best Thesaurus.
	 *                                             1: Better Thesaurus.
	 *                                             2: Good Thesaurus.
	 *                                             3: All Thesaurus.
	 *                                             9: Everyone's Favourite.
	 *         string  $thesaurus                  Thesaurus Language to Use.
	 *         bool    $pos                        Use Part of Speech Analysis.
	 *         bool    $UseGrammarAI               Use Grammar Correction.
	 *         int     $replacetype                Replacement Method.
	 *                                             0：Replace phrase and word.
	 *                                             1：Only replace phrase.
	 *                                             2: Only replace word.
	 *                                             3: Replace phrase first, then replace word till the article passes copyscape.
	 *                                             4: Spin the article to most unique.
	 *                                             5: Spin the article to most readable.
	 * @param   bool|array $protected_words Protected Words not to spin (false | array).
	 * @return  WP_Error|string              Error | Text with Spintax
	 */
	public function text_with_spintax( $text, $params = array(), $protected_words = false ) {

		// Build params.
		$params['api_key'] = $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-spintax', $this->get_settings_prefix() . '_api_key', false );
		$params['dev_key'] = $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-spintax', $this->get_settings_prefix() . '_dev_key', false );
		$params['text']    = $text;

		// Send request.
		return $this->response(
			$this->post( 'paraphraser', $params )
		);

	}

	/**
	 * Inspects the response from the API call, returning an error
	 * or data
	 *
	 * @since   2.8.9
	 *
	 * @param   WP_Error|object|string $response   Response.
	 * @return  WP_Error|string
	 */
	public function response( $response ) {

		// If the response is an error, return it.
		if ( is_wp_error( $response ) ) {
			return new WP_Error(
				'page_generator_pro_spinnerchief_error',
				sprintf(
					/* translators: Error message */
					__( 'SpinnerChief: %s', 'page-generator-pro' ),
					$response->get_error_message()
				)
			);
		}

		// If response isn't a 200, the text will be the error.
		if ( $response->code !== 200 ) {
			return new WP_Error(
				'page_generator_pro_spinnerchief_error',
				sprintf(
					/* translators: Error message */
					__( 'SpinnerChief: %s', 'page-generator-pro' ),
					$response->text
				)
			);
		}

		// Return text.
		return $response->text;

	}

}
