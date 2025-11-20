<?php
/**
 * Spin Rewriter API Class.
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Generate spintax using spinrewriter.com
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 2.2.9
 */
class Page_Generator_Pro_Spin_Rewriter extends Page_Generator_Pro_API {

	use Page_Generator_Pro_Integration_Trait;
	use Page_Generator_Pro_Spintax_Trait;

	/**
	 * Holds the base object.
	 *
	 * @since   2.2.9
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
	public $name = 'spin-rewriter';

	/**
	 * Holds the API endpoint
	 *
	 * @since   2.2.9
	 *
	 * @var     string
	 */
	public $api_endpoint = 'http://www.spinrewriter.com/action/api';

	/**
	 * Holds the account URL where users can obtain their API key
	 *
	 * @since   2.2.9
	 *
	 * @var     string
	 */
	public $account_url = 'https://www.spinrewriter.com/cp-api';

	/**
	 * Holds the referal URL to use for users wanting to sign up
	 * to Spin Rewriter's service.
	 *
	 * @since   2.2.9
	 *
	 * @var     string
	 */
	public $referral_url = 'https://www.spinrewriter.com/?ref=2c883';

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
	 * @since   2.2.9
	 *
	 * @var     string
	 */
	public $email_address;

	/**
	 * Constructor.
	 *
	 * @since   2.2.9
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

		return __( 'Spin Rewriter', 'page-generator-pro' );

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
					esc_html__( 'The email address you use when logging into Spin Rewriter.', 'page-generator-pro' ),
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
					esc_html__( 'Enter your Spin Rewriter API key,', 'page-generator-pro' ),
					'<a href="' . esc_attr( $this->get_account_url() ) . '" target="_blank" rel="noopener">' . esc_html__( 'which can be found here.', 'page-generator-pro' ) . '</a>',
					esc_html__( 'Don\'t have an account?', 'page-generator-pro' ),
					'<a href="' . esc_attr( $this->get_registration_url() ) . '" target="_blank" rel="noopener">' . esc_html__( 'Register an account.', 'page-generator-pro' ) . '</a>'
				),
			),
			$this->get_settings_prefix() . '_confidence_level' => array(
				'label'         => __( 'Confidence Level', 'page-generator-pro' ),
				'type'          => 'select',
				'values'        => $this->get_confidence_levels(),
				'default_value' => $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-spintax', $this->get_settings_prefix() . '_confidence_level' ),
				'description'   => __( 'The higher the confidence level, the more readable the text and the less number of spins and variations produced.', 'page-generator-pro' ),
			),
			$this->get_settings_prefix() . '_nested_spintax' => array(
				'label'         => __( 'Apply Nested Spintax', 'page-generator-pro' ),
				'type'          => 'toggle',
				'default_value' => $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-spintax', $this->get_settings_prefix() . '_nested_spintax' ),
				'description'   => __( 'If enabled, Spin Rewriter will spin single words inside already spun phrases.', 'page-generator-pro' ),
			),
			$this->get_settings_prefix() . '_auto_sentences' => array(
				'label'         => __( 'Spin Sentences', 'page-generator-pro' ),
				'type'          => 'toggle',
				'default_value' => $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-spintax', $this->get_settings_prefix() . '_auto_sentences' ),
				'description'   => __( 'If enabled, Spin Rewriter will spin sentences.', 'page-generator-pro' ),
			),
			$this->get_settings_prefix() . '_auto_paragraphs' => array(
				'label'         => __( 'Spin Paragraphs', 'page-generator-pro' ),
				'type'          => 'toggle',
				'default_value' => $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-spintax', $this->get_settings_prefix() . '_auto_paragraphs' ),
				'description'   => __( 'If enabled, Spin Rewriter will spin paragraphs.', 'page-generator-pro' ),
			),
			$this->get_settings_prefix() . '_auto_new_paragraphs' => array(
				'label'         => __( 'Add Paragraphs', 'page-generator-pro' ),
				'type'          => 'toggle',
				'default_value' => $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-spintax', $this->get_settings_prefix() . '_auto_new_paragraphs' ),
				'description'   => __( 'If enabled, Spin Rewriter may add additional paragraphs.', 'page-generator-pro' ),
			),
			$this->get_settings_prefix() . '_auto_sentence_trees' => array(
				'label'         => __( 'Change Phrase and Sentence Structure', 'page-generator-pro' ),
				'type'          => 'toggle',
				'default_value' => $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-spintax', $this->get_settings_prefix() . '_auto_sentence_trees' ),
				'description'   => __( 'If enabled, Spin Rewriter may change the entire structure of phrases and sentences.', 'page-generator-pro' ),
			),
		);

		return $settings_fields;

	}

	/**
	 * Adds spintax to the given content using the Spin Rewriter API
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

		// Build API compatible parameters.
		$params = array(
			'auto_protected_terms' => $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-spintax', 'skip_capitalized_words', 1 ),
			'confidence_level'     => $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-spintax', $this->get_settings_prefix() . '_confidence_level', 'low' ),
			'nested_spintax'       => $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-spintax', $this->get_settings_prefix() . '_nested_spintax', 1 ),
			'auto_sentences'       => $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-spintax', $this->get_settings_prefix() . '_auto_sentences', 1 ),
			'auto_paragraphs'      => $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-spintax', $this->get_settings_prefix() . '_auto_paragraphs', 1 ),
			'auto_new_paragraphs'  => $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-spintax', $this->get_settings_prefix() . '_auto_new_paragraphs', 1 ),
			'auto_sentence_trees'  => $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-spintax', $this->get_settings_prefix() . '_auto_sentence_trees', 1 ),
		);

		// Setup API.
		$this->set_credentials(
			$credentials['email_address'],
			$credentials['api_key']
		);

		// Return result.
		return $this->text_with_spintax( $content, $params, $protected_words );

	}

	/**
	 * Sets the credentials to use for API calls
	 *
	 * @since   2.2.9
	 *
	 * @param   string $email_address  Email Address.
	 * @param   string $api_key        API Key.
	 */
	public function set_credentials( $email_address, $api_key ) {

		$this->email_address = $email_address;
		$this->set_api_key( $api_key );

	}

	/**
	 * Returns the valid values for confidence levels,
	 * which can be used on API calls.
	 *
	 * @since   2.2.9
	 *
	 * @return  array   Confidence Levels
	 */
	public function get_confidence_levels() {

		$confidence_levels = array(
			'low'    => __( 'Low', 'page-generator-pro' ),
			'medium' => __( 'Medium', 'page-generator-pro' ),
			'high'   => __( 'High', 'page-generator-pro' ),
		);

		return $confidence_levels;

	}

	/**
	 * Returns a spintax version of the given non-spintax text, that can be later processed.
	 *
	 * @since   2.2.9
	 *
	 * @param   string     $text               Original non-spintax Text.
	 * @param   array      $params             Spin Parameters.
	 *         string  $confidence_level           Confidence Level (low, medium, high).
	 *         bool    $auto_protected_terms       Don't spin capitalized words.
	 *         bool    $nested_spintax             Build Nested Spintax.
	 *         bool    $auto_sentences             Spin Sentences.
	 *         bool    $auto_paragraphs            Spin Paragraphs.
	 *         bool    $auto_new_paragraphs        Add Paragraphs.
	 *         bool    $auto_sentence_structure    Change Sentence Structure.
	 * @param   bool|array $protected_words    Protected Words not to spin (false | array).
	 * @return  WP_Error|string                 Error | Text with Spintax
	 */
	public function text_with_spintax( $text, $params, $protected_words = false ) {

		// Build params.
		$params = array_merge(
			$params,
			array(
				'action'          => 'text_with_spintax',
				'text'            => $text,
				'protected_terms' => ( $protected_words !== false ? implode( "\n", $protected_words ) : '' ),
				'spintax_format'  => '{|}',
				'email_address'   => $this->email_address,
				'api_key'         => $this->api_key,
			)
		);

		// Convert boolean to true/false strings, as required by https://www.spinrewriter.com/cp-api.
		foreach ( $params as $key => $value ) {
			if ( empty( $value ) ) {
				$params[ $key ] = 'false';
			}
			if ( $value == '1' ) { // phpcs:ignore Universal.Operators.StrictComparisons.LooseEqual
				$params[ $key ] = 'true';
			}
		}

		// Send request.
		$result = $this->response(
			$this->post( '', $params )
		);

		// Bail if an error.
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		// Clean up the response, which will have some erronous whitespaces in shortcodes.
		$spintax_content = str_replace( '=" ', '="', $result->response );
		$spintax_content = str_replace( '"%  ', '"%', $spintax_content );

		// Return text with spintax.
		return $spintax_content;

	}

	/**
	 * Inspects the response from the API call, returning an error
	 * or data
	 *
	 * @since   2.8.9
	 *
	 * @param   WP_Error|object $response   Response.
	 * @return  WP_Error|object
	 */
	public function response( $response ) {

		// Bail if an error.
		if ( is_wp_error( $response ) ) {
			return new WP_Error(
				'page_generator_pro_spin_rewriter_error',
				sprintf(
					/* translators: Error message */
					__( 'SpinRewriter: %s', 'page-generator-pro' ),
					$response->get_error_message()
				)
			);
		}

		// Bail if the status is missing.
		if ( ! isset( $response->status ) ) {
			return new WP_Error(
				'page_generator_pro_spin_rewriter_error',
				__( 'Spin Rewriter: Unable to determine success or failure of request.', 'page-generator-pro' )
			);
		}

		// Bail if the status isn't OK.
		if ( $response->status !== 'OK' ) {
			return new WP_Error(
				'page_generator_pro_spin_rewriter_error',
				sprintf(
					/* translators: %1$s: Status, %2$s: Error message */
					__( 'Spin Rewriter: %1$s: %2$s', 'page-generator-pro' ),
					$response->status,
					$response->response // @phpstan-ignore-line
				)
			);
		}

		// Return data.
		return $response;

	}

}
