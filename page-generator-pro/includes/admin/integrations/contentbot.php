<?php
/**
 * ContentBot.ai API Class.
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Generate articles based on keywords using ContentBot.ai
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 3.5.5
 */
class Page_Generator_Pro_ContentBot extends Page_Generator_Pro_API {

	use Page_Generator_Pro_Integration_Trait;
	use Page_Generator_Pro_Research_Trait;

	/**
	 * Holds the base object.
	 *
	 * @since   3.5.5
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
	public $name = 'contentbot';

	/**
	 * Holds the API endpoint
	 *
	 * @since   3.5.5
	 *
	 * @var     string
	 */
	public $api_endpoint = 'https://contentbot.us-3.evennode.com/api/v1';

	/**
	 * Holds the account URL where users can obtain their API key
	 *
	 * @since   3.5.5
	 *
	 * @var     string
	 */
	public $account_url = 'https://contentbot.ai/app/profile.php';

	/**
	 * Holds the referal URL to use for users wanting to sign up
	 * to the API service.
	 *
	 * @since   3.5.5
	 *
	 * @var     string
	 */
	public $referral_url = 'https://contentbot.ai?fpr=tim17';

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

		// Register as a Research Provider.
		add_filter( 'page_generator_pro_research_get_providers', array( $this, 'register_integration' ) );
		add_filter( 'page_generator_pro_research_get_providers_settings_fields', array( $this, 'get_settings_fields' ) );

		// Register attributes and fields for the Research tool.
		add_filter( 'page_generator_pro_shortcode_research_get_attributes_' . $this->name, array( $this, 'get_research_attributes' ) );
		add_filter( 'page_generator_pro_shortcode_research_get_fields_' . $this->name, array( $this, 'get_research_fields' ) );

		// Register research function.
		add_filter( 'page_generator_pro_research_research_' . $this->name, array( $this, 'research' ) );
		add_filter( 'page_generator_pro_research_get_status_' . $this->name, array( $this, 'get_status' ) );

	}

	/**
	 * Returns the label of this integration.
	 *
	 * @since   4.8.0
	 *
	 * @return  string
	 */
	public function get_title() {

		return __( 'ContentBot', 'page-generator-pro' );

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
				'default_value' => $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-research', $this->get_settings_prefix() . '_api_key' ),
				'description'   => sprintf(
					'%s %s %s %s',
					esc_html__( 'Enter your ContentBot API key', 'page-generator-pro' ),
					'<a href="' . esc_attr( $this->get_account_url() ) . '" target="_blank" rel="noopener">' . esc_html__( 'which can be found here', 'page-generator-pro' ) . '</a>',
					esc_html__( 'Don\'t have an account?', 'page-generator-pro' ),
					'<a href="' . esc_attr( $this->get_registration_url() ) . '" target="_blank" rel="noopener">' . esc_html__( 'Register an account', 'page-generator-pro' ) . '</a>'
				),
			),
		);

		return $settings_fields;

	}

	/**
	 * Sends the topic to AI Writer's research endpoint, for AI Writer to build content
	 * and return it later on asynchronously
	 *
	 * @since   3.9.1
	 *
	 * @param   string $topic            Topic.
	 * @return  WP_Error|array
	 */
	public function research( $topic ) {

		// Get API key.
		$api_key = $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-research', 'contentbot_api_key', false );

		// Bail if no API Key defined.
		if ( ! $api_key ) {
			return new WP_Error(
				'page_generator_pro_research_process_contentbot_error',
				__( 'No API key was configured in the Plugin\'s Settings', 'page-generator-pro' )
			);
		}

		// Set API Key.
		$this->set_api_key( $api_key );

		// Send request.
		$result = $this->get_topic_content( $topic );

		// If an error occured, bail.
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		// Return data.
		return array(
			'id'        => 0, // ContentBot doesn't use an ID.
			'completed' => true, // We get an immediate result, so return it.
			'content'   => wpautop( implode( "\n\n", $result ) ),
			'message'   => __( 'Research completed successfully.', 'page-generator-pro' ),
		);

	}

	/**
	 * Returns content for the given topic.
	 *
	 * @since   3.5.5
	 *
	 * @param   string $topic              Topic.
	 * @param   string $tone               Tone (professional,friendly,bold,playful,first person,third person).
	 * @param   string $formality          Formality (default,more,less).
	 * @param   string $language_service   Language Service (google,deepl,watson).
	 * @param   string $source_lang        Original Text's Language (two-character language code).
	 * @param   string $target_lang        Target Language (two-character language code).
	 * @return  WP_Error|array             Error | Rewritten Text paragraphs
	 */
	public function get_topic_content( $topic, $tone = 'professional', $formality = 'default', $language_service = 'google', $source_lang = 'en', $target_lang = 'en' ) {

		// Build params.
		$params = array(
			'hash'          => $this->api_key,
			'ptype'         => 'editor', // Always editor.
			'pcompletions'  => 1, // Always 1.
			'longformFlag'  => 1, // Always 1.
			'psubtype'      => 1, // Always 1.
			'wc'            => 75, // 15, 25, 50, 75 based on outputlength e.g. if 25, outputlength = 2.
			'outputlength'  => 4, // 1, 2, 3, 4 based on wc e.g. if 4, wc = 75.

			// Customisable params.
			'pdesc'         => $topic,
			'ptone'         => $tone,
			'planservice'   => $language_service,
			'psourcelan'    => $source_lang,
			'lang'          => $target_lang,
			'planformality' => $formality,
		);

		// Send request.
		$result = $this->response(
			$this->get( 'input', $params )
		);

		// Bail if an error.
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		// Return array of paragraphs.
		return explode( "\n\n", trim( $result->output[0]->text ) );

	}

	/**
	 * Returns supported tonalities for ContentBot API calls.
	 *
	 * @since   3.5.5
	 *
	 * @return  array    Tonalities
	 */
	public function get_tonalities() {

		return array(
			'professional' => __( 'Professional', 'page-generator-pro' ),
			'friendly'     => __( 'Friendly', 'page-generator-pro' ),
			'bold'         => __( 'Bold', 'page-generator-pro' ),
			'playful'      => __( 'Playful', 'page-generator-pro' ),
			'first person' => __( 'First Person', 'page-generator-pro' ),
			'third person' => __( 'Third Person', 'page-generator-pro' ),
		);

	}

	/**
	 * Returns supported formalities for ContentBot API calls.
	 *
	 * @since   3.5.5
	 *
	 * @return  array    Formalities
	 */
	public function get_formalities() {

		return array(
			'default'     => __( 'Default', 'page-generator-pro' ),
			'more formal' => __( 'More Formal', 'page-generator-pro' ),
			'less formal' => __( 'Less Formal', 'page-generator-pro' ),
		);

	}

	/**
	 * Inspects the response from the API call, returning an error
	 * or data
	 *
	 * @since   3.5.5
	 *
	 * @param   WP_Error|object $response   Response.
	 * @return  WP_Error|object
	 */
	public function response( $response ) {

		// If the response is an error, return it.
		if ( is_wp_error( $response ) ) {
			return new WP_Error(
				'page_generator_pro_contentbot_error',
				sprintf(
					/* translators: Error message */
					__( 'ContentBot: %s', 'page-generator-pro' ),
					$response->get_error_message()
				)
			);
		}

		return $response;

	}

}
