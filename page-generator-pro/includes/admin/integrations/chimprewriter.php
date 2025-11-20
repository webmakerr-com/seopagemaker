<?php
/**
 * ChimpRewriter API class.
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Generate spintax using chimprewriter.com
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 2.3.1
 */
class Page_Generator_Pro_ChimpRewriter extends Page_Generator_Pro_API {

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
	public $name = 'chimprewriter';

	/**
	 * Holds the API endpoint
	 *
	 * @since   2.3.1
	 *
	 * @var     string
	 */
	public $api_endpoint = 'http://api.chimprewriter.com/';

	/**
	 * Holds the account URL where users can obtain their API key
	 *
	 * @since   2.3.1
	 *
	 * @var     string
	 */
	public $account_url = 'http://account.chimprewriter.com/ChimpApi';

	/**
	 * Holds the referal URL to use for users wanting to sign up
	 * to the API service.
	 *
	 * @since   2.3.1
	 *
	 * @var     string
	 */
	public $referral_url = 'https://chimprewriter.com/api/?affiliate=wpzinc';

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
	 * Holds the user's email address
	 *
	 * @since   2.3.1
	 *
	 * @var     string
	 */
	public $email_address;

	/**
	 * Holds the Application ID, which can be any string up to 100 characters
	 *
	 * @since   2.3.1
	 *
	 * @var     string
	 */
	public $application_id = 'page-generator-pro';

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
	 * @since   4.1.2
	 *
	 * @return  string
	 */
	public function get_title() {

		return __( 'ChimpRewriter', 'page-generator-pro' );

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
			$this->get_settings_prefix() . '_email_address' => array(
				'label'         => __( 'Email Address', 'page-generator-pro' ),
				'type'          => 'text',
				'default_value' => $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-spintax', $this->get_settings_prefix() . '_email_address' ),
				'description'   => sprintf(
					'%s %s %s',
					esc_html__( 'The email address you use when logging into ChimpRewriter.', 'page-generator-pro' ),
					'<a href="' . esc_attr( $this->get_registration_url() ) . '" target="_blank" rel="noopener">' . esc_html__( 'Register an account', 'page-generator-pro' ) . '</a>',
					esc_html__( 'if you don\'t have one.', 'page-generator-pro' )
				),
			),
			$this->get_settings_prefix() . '_api_key' => array(
				'label'         => __( 'API Key', 'page-generator-pro' ),
				'type'          => 'text',
				'default_value' => $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-spintax', $this->get_settings_prefix() . '_api_key' ),
				'description'   => sprintf(
					'%s %s %s %s',
					esc_html__( 'Enter your ChimpRewriter API key,', 'page-generator-pro' ),
					'<a href="' . esc_attr( $this->get_account_url() ) . '" target="_blank" rel="noopener">' . esc_html__( 'which can be found here.', 'page-generator-pro' ) . '</a>',
					esc_html__( 'Don\'t have an account?', 'page-generator-pro' ),
					'<a href="' . esc_attr( $this->get_registration_url() ) . '" target="_blank" rel="noopener">' . esc_html__( 'Register an account.', 'page-generator-pro' ) . '</a>'
				),
			),
			$this->get_settings_prefix() . '_confidence_level' => array(
				'label'         => __( 'Confidence Level', 'page-generator-pro' ),
				'type'          => 'select',
				'default_value' => $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-spintax', $this->get_settings_prefix() . '_confidence_level', 5 ),
				'values'        => $this->get_confidence_levels(),
				'description'   => __( 'The higher the confidence level, the more readable the text and the less number of spins and variations produced.', 'page-generator-pro' ),
			),
			$this->get_settings_prefix() . '_part_of_speech_level' => array(
				'label'         => __( 'Part of Speech Level', 'page-generator-pro' ),
				'type'          => 'select',
				'default_value' => $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-spintax', $this->get_settings_prefix() . '_part_of_speech_level', 5 ),
				'values'        => $this->get_part_of_speech_levels(),
				'description'   => __( 'The higher the Part of Speech level, the more readable the text and the less number of spins and variations produced.', 'page-generator-pro' ),
			),
			$this->get_settings_prefix() . '_verify_grammar' => array(
				'label'         => __( 'Verify Grammar', 'page-generator-pro' ),
				'type'          => 'toggle',
				'default_value' => $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-spintax', $this->get_settings_prefix() . '_verify_grammar', 1 ),
				'description'   => __( 'If enabled, grammar is verified on the resulting text to produce a very high quality spin.', 'page-generator-pro' ),
			),
			$this->get_settings_prefix() . '_nested_spintax' => array(
				'label'         => __( 'Apply Nested Spintax', 'page-generator-pro' ),
				'type'          => 'toggle',
				'default_value' => $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-spintax', $this->get_settings_prefix() . '_nested_spintax', 1 ),
				'description'   => __( 'If enabled, ChimpRewriter will spin single words inside already spun phrases.', 'page-generator-pro' ),
			),
			$this->get_settings_prefix() . '_change_phrase_sentence_structure' => array(
				'label'         => __( 'Change Phrase and Sentence Structure', 'page-generator-pro' ),
				'type'          => 'toggle',
				'default_value' => $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-spintax', $this->get_settings_prefix() . '_change_phrase_sentence_structure', 1 ),
				'description'   => __( 'If enabled, ChimpRewriter will change the entire structure of phrases and sentences.', 'page-generator-pro' ),
			),
		);

		return $settings_fields;

	}

	/**
	 * Sets the credentials to use for API calls
	 *
	 * @since   2.3.1
	 *
	 * @param   string $email_address  Email Address.
	 * @param   string $api_key        API Key.
	 */
	public function set_credentials( $email_address, $api_key ) {

		$this->email_address = $email_address;
		$this->set_api_key( $api_key );

	}

	/**
	 * Returns the valid values for quality levels,
	 * which can be used on API calls.
	 *
	 * @since   2.3.1
	 *
	 * @return  array   Quality Levels
	 */
	public function get_confidence_levels() {

		$quality_levels = array(
			5 => __( 'Best', 'page-generator-pro' ),
			4 => __( 'Better', 'page-generator-pro' ),
			3 => __( 'Good', 'page-generator-pro' ),
			2 => __( 'Average', 'page-generator-pro' ),
			1 => __( 'Any', 'page-generator-pro' ),
		);

		return $quality_levels;

	}

	/**
	 * Returns the valid values for Part of Speech levels,
	 * which can be used on API calls.
	 *
	 * @since   2.3.1
	 *
	 * @return  array   Quality Levels
	 */
	public function get_part_of_speech_levels() {

		$part_of_speech_levels = array(
			3 => __( 'Full', 'page-generator-pro' ),
			2 => __( 'Loose', 'page-generator-pro' ),
			1 => __( 'Extremely Loose', 'page-generator-pro' ),
			0 => __( 'None', 'page-generator-pro' ),
		);

		return $part_of_speech_levels;

	}

	/**
	 * Adds spintax to the given content using the ChimpRewriter API
	 *
	 * @since   3.9.1
	 *
	 * @param   string $content            Content.
	 * @param   array  $protected_words    Protected Words.
	 * @return  WP_Error|string
	 */
	public function add_spintax( $content, $protected_words ) {

		// Get credentials.
		$credentials = array(
			'email_address' => $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-spintax', $this->get_settings_prefix() . '_email_address', false ),
			'api_key'       => $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-spintax', $this->get_settings_prefix() . '_api_key', false ),
		);

		// Get settings.
		$settings = array(
			'confidence_level'                 => $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-spintax', $this->get_settings_prefix() . '_confidence_level' ),
			'part_of_speech_level'             => $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-spintax', $this->get_settings_prefix() . '_part_of_speech_level' ),
			'verify_grammar'                   => $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-spintax', $this->get_settings_prefix() . '_verify_grammar' ),
			'nested_spintax'                   => $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-spintax', $this->get_settings_prefix() . '_nested_spintax' ),
			'change_phrase_sentence_structure' => $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-spintax', $this->get_settings_prefix() . '_change_phrase_sentence_structure' ),
		);

		// Build API compatible parameters.
		$params = array(
			'quality'           => $settings['confidence_level'],
			'phrasequality'     => $settings['confidence_level'],
			'posmatch'          => $settings['part_of_speech_level'],
			'sentencerewrite'   => $settings['change_phrase_sentence_structure'],
			'grammarcheck'      => $settings['verify_grammar'],
			'reorderparagraphs' => $settings['change_phrase_sentence_structure'],
		);

		// Setup API.
		$this->set_credentials(
			$credentials['email_address'],
			$credentials['api_key']
		);

		// Return result.
		return $this->chimprewrite( $content, $params, $protected_words );

	}

	/**
	 * Returns a spintax version of the given non-spintax text, that can be later processed.
	 *
	 * @since   2.3.1
	 *
	 * @param   string     $text               Original non-spintax Text.
	 * @param   array      $params             Spin Parameters.
	 *         int     $quality                    Synonym Replacement Quality (default: 4) (see get_quality_levels() for valid values).
	 *         int     $phrasequality              Phrase Replacement Quality (default: 3) (see get_quality_levels() for valid values).
	 *         int     $posmatch                   Required Part of Speech Match (default: 3) (see get_part_of_speech_levels() for valid values).
	 *         string  $language                   Two letter language code (en only at this time).
	 *         bool    $sentencerewrite            Rewrite Sentences (default: 0).
	 *         bool    $grammarcheck               Check Grammar (default: 0).
	 *         bool    $reorderparagraphs          Reorder Paragraphs (default: 0).
	 *         bool    $spinwithinspin             Spin within existing Spintax (default: 0).
	 *         bool    $spintidy                   Fix common type grammar mistakes (a/an) (default: 1).
	 *         int     $replacefrequency           nth words spun (default: 1).
	 *         int     $maxsyns                    Maximum Number of Synonyms to use for word/phrase (default: 10).
	 *         int     $excludeoriginal            Exclude Original word from result (default: 0).
	 *         int     $instantunique              Replace letters with similar looking chars for copyscape validation (default: 0).
	 *         int     $maxspindepth               Maximum Spin Level Deptch (default: 0 = no limit).
	 * @param   bool|array $protected_words    Protected Words not to spin (false | array).
	 * @return  WP_Error|string                     Error | Text with Spintax
	 */
	public function chimprewrite( $text, $params = array(), $protected_words = false ) {

		return $this->response(
			$this->post(
				'ChimpRewrite',
				array_merge(
					$params,
					array(
						'text'           => $text,
						'protectedterms' => ( $protected_words !== false ? implode( ',', $protected_words ) : '' ),
						'tagprotect'     => '[|]{|}',
						'email'          => $this->email_address,
						'apikey'         => $this->api_key,
						'aid'            => $this->application_id,
					)
				)
			)
		);

	}

	/**
	 * Inspects the response from the API call, returning an error
	 * or data
	 *
	 * @since   2.8.9
	 *
	 * @param   WP_Error|object $response   Response.
	 * @return  WP_Error|string
	 */
	public function response( $response ) {

		// If the response is an error, return it.
		if ( is_wp_error( $response ) ) {
			return new WP_Error(
				'page_generator_pro_chimprewriter_error',
				sprintf(
					/* translators: Error message */
					__( 'ChimpRewriter: %s', 'page-generator-pro' ),
					$response->get_error_message()
				)
			);
		}

		// Return an error if the status is missing.
		if ( ! isset( $response->status ) ) {
			return new WP_Error(
				'page_generator_pro_chimprewriter_error',
				__( 'ChimpRewriter: Unable to determine success or failure of request.', 'page-generator-pro' )
			);
		}

		// Return an error if the status isn't success.
		if ( $response->status !== 'success' ) {
			return new WP_Error(
				'page_generator_pro_chimprewriter_error',
				sprintf(
					/* translators: %1$s: Status result, %2$s: Error message */
					__( 'ChimpRewriter: %1$s: %2$s', 'page-generator-pro' ),
					$response->status,
					$response->output // @phpstan-ignore-line
				)
			);
		}

		// Get output.
		$output = trim( stripslashes( $response->output ) ); // @phpstan-ignore-line

		// If output is empty, reorder paragraphs causes this.
		if ( empty( $output ) ) {
			return new WP_Error(
				'page_generator_pro_chimprewriter_error',
				__( 'ChimpRewriter could not spin the content. If the text is short, consider disabling the "Change Phrase and Sentence Structure" option.', 'page-generator-pro' )
			);
		}

		// Return output.
		return $output;

	}

}
