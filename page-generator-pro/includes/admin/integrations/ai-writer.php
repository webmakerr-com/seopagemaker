<?php
/**
 * AI Writer API class.
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Generate articles based on keywords using ai-writer.com
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 2.3.1
 */
class Page_Generator_Pro_AI_Writer extends Page_Generator_Pro_API {

	use Page_Generator_Pro_Integration_Trait;

	/**
	 * Holds the base object.
	 *
	 * @since   2.8.9
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
	public $name = 'ai-writer';

	/**
	 * Holds the API endpoint
	 *
	 * @since   2.8.9
	 *
	 * @var     string
	 */
	public $api_endpoint = 'https://panel.ai-writer.com/aiw/apiendpoint2';

	/**
	 * Holds the account URL where users can obtain their API key
	 *
	 * @since   2.8.9
	 *
	 * @var     string
	 */
	public $account_url = 'https://panel.ai-writer.com/aiw/apidocumentationsite/';

	/**
	 * Holds the referal URL to use for users wanting to sign up
	 * to the API service.
	 *
	 * @since   2.8.9
	 *
	 * @var     string
	 */
	public $referral_url = 'https://ai-writer.com';

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
		add_filter( 'page_generator_pro_research_research_' . $this->name, array( $this, 'research' ), 10, 5 );
		add_filter( 'page_generator_pro_research_get_status_' . $this->name, array( $this, 'research_get_status' ) );

	}

	/**
	 * Returns the label of this integration.
	 *
	 * @since   4.8.0
	 *
	 * @return  string
	 */
	public function get_title() {

		return __( 'AI Writer', 'page-generator-pro' );

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
				'default_value' => $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-research', 'ai_writer_api_key' ),
				'description'   => sprintf(
					'%s %s %s %s',
					esc_html__( 'Enter your AI Writer API key', 'page-generator-pro' ),
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
		$api_key = $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-research', $this->get_settings_prefix() . '_api_key', false );

		// Bail if no API Key defined.
		if ( ! $api_key ) {
			return new WP_Error(
				'page_generator_pro_research_process_ai_writer_error',
				__( 'No API key was configured in the Plugin\'s Settings', 'page-generator-pro' )
			);
		}

		// Set API Key.
		$this->set_api_key( $api_key );

		// Send request.
		$result = $this->put_research_request( $topic );

		// If an error occured, bail.
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		// Return data of request ID and estimated time needed.
		return array(
			'id'             => $result->id,
			'estimated_time' => $result->estimated_time_needed,
			'message'        => sprintf(
				/* translators: Calculated human readable duration/time */
				__( 'Estimated time for completion is %s. Please wait whilst AI Writer completes this process.', 'page-generator-pro' ),
				human_readable_duration( gmdate( 'i:s', $result->estimated_time_needed ) )
			),
		);

	}

	/**
	 * Returns the status of a previously researched topic from AI Writer
	 *
	 * @since   3.9.1
	 *
	 * @param   string $id     ID.
	 * @return  WP_Error|array
	 */
	public function get_status( $id ) {

		// Get API key.
		$api_key = $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-research', 'ai_writer_api_key', false );

		// Bail if no API Key defined.
		if ( ! $api_key ) {
			return new WP_Error(
				'page_generator_pro_research_get_status_ai_writer_error',
				__( 'No API key was configured in the Plugin\'s Settings', 'page-generator-pro' )
			);
		}

		// Set API Key.
		$this->base->get_class( 'ai_writer' )->set_api_key( $api_key );

		// Send request.
		$result = $this->base->get_class( 'ai_writer' )->get_research_result( $id );

		// If an error occured, bail.
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		// Define default status array.
		$status = array(
			'id'        => $result->request->id,
			'completed' => $result->request->done,
			'content'   => '',
			'message'   => '',
		);

		// If the research isn't completed, return.
		if ( ! $status['completed'] ) {
			$status['message'] = sprintf(
				/* translators: Calculated human readable duration/time */
				__( 'Estimated time for completion is %s. Please wait whilst AI Writer completes this process.', 'page-generator-pro' ),
				human_readable_duration( gmdate( 'i:s', $result->request->estimated_time_needed ) )
			);
			return $status;
		}

		// Build paragraphs.
		$paragraphs = array();
		foreach ( $result->result->article as $index => $paragraph ) {
			$paragraphs[] = $paragraph->paragraph_text;
		}
		$status['content'] = wpautop( implode( "\n\n", $paragraphs ) );
		$status['message'] = __( 'Research completed successfully.', 'page-generator-pro' );

		// Return.
		return $status;

	}

	/**
	 * Returns subscription information
	 *
	 * @since   2.8.9
	 *
	 * @return  WP_Error|object
	 */
	public function get_subscription_info() {

		return $this->response(
			$this->get(
				'get_subscription_info',
				array(
					'api_key' => $this->api_key,
				)
			)
		);

	}

	/**
	 * Returns a list of all research requests
	 *
	 * @since   2.8.9
	 *
	 * @param   int $offset     List Offset.
	 * @param   int $limit      Limit (1 to 100).
	 * @return  WP_Error|object
	 */
	public function list_research_requests( $offset = 0, $limit = 100 ) {

		return $this->response(
			$this->get(
				'list_research_requests/' . $offset . '/' . $limit,
				array(
					'api_key' => $this->api_key,
				)
			)
		);

	}

	/**
	 * Submits a new research request for the given topic
	 *
	 * @since   2.8.9
	 *
	 * @param   string $topic          Topic.
	 * @return  WP_Error|object
	 */
	public function put_research_request( $topic ) {

		return $this->response(
			$this->post(
				'put_research_request/' . rawurlencode( $topic ) . '?' . http_build_query(
					array(
						'api_key' => $this->api_key,
					)
				)
			)
		);

	}

	/**
	 * Returns the research request's result for the given ID
	 *
	 * @since   2.8.9
	 *
	 * @param   string $id             ID.
	 * @return  WP_Error|object
	 */
	public function get_research_result( $id ) {

		$result = $this->response(
			$this->get(
				'get_research_result/' . rawurlencode( $id ) . '?' . http_build_query(
					array(
						'api_key' => $this->api_key,
					)
				)
			)
		);

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		if ( $result->request->error_occurred ) {
			return new WP_Error(
				'page_generator_pro_ai_writer_error',
				sprintf(
					/* translators: %1$s: Resource ID, %2$s: Query */
					__( 'An error occured when attempting to perform research for resource ID %1$s, topic %2$s', 'page-generator-pro' ),
					$id,
					$result->request->query
				)
			);
		}

		return $result;

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
	private function response( $response ) {

		// If the response is an error, return it.
		if ( is_wp_error( $response ) ) {
			return new WP_Error(
				'page_generator_pro_ai_writer_error',
				sprintf(
					/* translators: Error message */
					__( 'AI Writer: %s', 'page-generator-pro' ),
					$response->get_error_message()
				)
			);
		}

		// If the response wasn't successful, bail.
		if ( isset( $response->success ) && ! $response->success ) {
			return new WP_Error(
				'page_generator_pro_ai_writer_error',
				__( 'AI Writer: An error occured', 'page-generator-pro' )
			);
		}

		return $response;

	}

}
