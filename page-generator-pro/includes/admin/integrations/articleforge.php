<?php
/**
 * ArticleForge API class.
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Generate articles based on keywords using articleforge.com
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 3.9.1
 */
class Page_Generator_Pro_ArticleForge extends Page_Generator_Pro_API {

	use Page_Generator_Pro_Integration_Trait;
	use Page_Generator_Pro_Research_Trait;

	/**
	 * Holds the base object.
	 *
	 * @since   3.9.1
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
	 * @since   3.9.1
	 *
	 * @var     string
	 */
	public $api_endpoint = 'https://af.articleforge.com/api';

	/**
	 * Holds the account URL where users can obtain their API key
	 *
	 * @since   3.9.1
	 *
	 * @var     string
	 */
	public $account_url = 'https://af.articleforge.com/api_info';

	/**
	 * Holds the referal URL to use for users wanting to sign up
	 * to the API service.
	 *
	 * @since   3.9.1
	 *
	 * @var     string
	 */
	public $referral_url = 'https://www.articleforge.com/?ref=c34417';

	/**
	 * Holds the flag determining if the request data should be encoded
	 * into a JSON string
	 *
	 * If false, data is encoded using http_build_query()
	 *
	 * @since   3.9.1
	 *
	 * @var     bool
	 */
	public $is_json_request = false;

	/**
	 * Constructor.
	 *
	 * @since   3.9.1
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

		return __( 'ArticleForge', 'page-generator-pro' );

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
					esc_html__( 'Enter your ArticleForge API key', 'page-generator-pro' ),
					'<a href="' . esc_attr( $this->base->get_class( 'articleforge' )->get_account_url() ) . '" target="_blank" rel="noopener">' . esc_html__( 'which can be found here', 'page-generator-pro' ) . '</a>',
					esc_html__( 'Don\'t have an account?', 'page-generator-pro' ),
					'<a href="' . esc_attr( $this->base->get_class( 'articleforge' )->get_registration_url() ) . '" target="_blank" rel="noopener">' . esc_html__( 'Register an account', 'page-generator-pro' ) . '</a>'
				),
			),
			$this->get_settings_prefix() . '_length'  => array(
				'label'         => __( 'Content Length', 'page-generator-pro' ),
				'type'          => 'select',
				'default_value' => $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-research', $this->get_settings_prefix() . '_length', 'short' ),
				'values'        => $this->base->get_class( 'articleforge' )->get_lengths(),
				'description'   => __( 'The length of content to produce.', 'page-generator-pro' ),
			),
			$this->get_settings_prefix() . '_include_titles' => array(
				'label'         => __( 'Include Titles?', 'page-generator-pro' ),
				'type'          => 'toggle',
				'default_value' => $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-research', $this->get_settings_prefix() . '_include_titles', 0 ),
				'description'   => __( 'Whether to include headings and subheadings in the content.', 'page-generator-pro' ),
			),
			$this->get_settings_prefix() . '_include_images' => array(
				'label'         => __( 'Include Images?', 'page-generator-pro' ),
				'type'          => 'number',
				'min'           => 0,
				'max'           => 1,
				'step'          => '0.01',
				'default_value' => $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-research', $this->get_settings_prefix() . '_include_images', 0 ),
				'description'   => __( 'The probability of images being included in the content, between 0.00 and 1.00.', 'page-generator-pro' ),
			),
			$this->get_settings_prefix() . '_include_videos' => array(
				'label'         => __( 'Include Videos?', 'page-generator-pro' ),
				'type'          => 'number',
				'min'           => 0,
				'max'           => 1,
				'step'          => '0.01',
				'default_value' => $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-research', $this->get_settings_prefix() . '_include_videos', 0 ),
				'description'   => __( 'The probability of videos being included in the content, between 0.00 and 1.00.', 'page-generator-pro' ),
			),
		);

		return $settings_fields;

	}

	/**
	 * Returns the valid values for content lengths,
	 * which can be used on API calls.
	 *
	 * @since   3.9.1
	 *
	 * @return  array   Content Lengths
	 */
	public function get_lengths() {

		$lengths = array(
			'very_short' => __( 'Very Short (approx. 50 words)', 'page-generator-pro' ),
			'short'      => __( 'Short (approx. 250 words)', 'page-generator-pro' ),
			'medium'     => __( 'Medium (approx. 500 words)', 'page-generator-pro' ),
			'long'       => __( 'Long (approx. 750 words)', 'page-generator-pro' ),
			'very_long'  => __( 'Very Long (approx. 1,500 words)', 'page-generator-pro' ),
		);

		return $lengths;

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
				'page_generator_pro_research_process_articleforge_error',
				__( 'No API key was configured in the Plugin\'s Settings', 'page-generator-pro' )
			);
		}

		// Set API Key.
		$this->set_api_key( $api_key );

		// Send request.
		$result = $this->base->get_class( 'articleforge' )->initiate_article(
			$topic,
			$this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-research', $this->get_settings_prefix() . '_length', 'short' ),
			$this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-research', $this->get_settings_prefix() . '_include_titles', 0 ),
			$this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-research', $this->get_settings_prefix() . '_include_images', 0 ),
			$this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-research', $this->get_settings_prefix() . '_include_videos', 0 )
		);

		// If an error occured, bail.
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		// Return data of request ID and estimated time needed.
		return array(
			'id'      => $result->ref_key,
			'message' => __( 'Please wait whilst ArticleForge completes this process.', 'page-generator-pro' ),
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
		$api_key = $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-research', $this->get_settings_prefix() . '_api_key', false );

		// Bail if no API Key defined.
		if ( ! $api_key ) {
			return new WP_Error(
				'page_generator_pro_research_get_status_articleforge_error',
				__( 'No API key was configured in the Plugin\'s Settings', 'page-generator-pro' )
			);
		}

		// Set API Key.
		$this->set_api_key( $api_key );

		// Send request.
		$result = $this->get_api_progress( $id );

		// If an error occured, bail.
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		// Define default status array.
		$status = array(
			'id'        => $id,
			'completed' => ( $result->api_status === 201 ? true : false ),
			'content'   => '',
			'message'   => '',
		);

		// If the research isn't completed, return.
		if ( ! $status['completed'] ) {
			$status['message'] = sprintf(
				/* translators: Percentage */
				__( '%s percent complete. Please wait whilst ArticleForge completes this process.', 'page-generator-pro' ),
				round( ( $result->progress * 100 ) )
			);
			return $status;
		}

		// If here, research is complete.
		// Get Article by Reference Key.
		$result = $this->get_api_article_result( $id );

		// If an error occured, bail.
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		// Add content to status.
		$status['content'] = wpautop( $result->article );
		$status['message'] = __( 'Research completed successfully.', 'page-generator-pro' );

		// Return.
		return $status;

	}

	/**
	 * Returns usage information.
	 *
	 * @since   3.9.1
	 *
	 * @return  WP_Error|object
	 */
	public function check_usage() {

		return $this->response(
			$this->post(
				'check_usage',
				array(
					'key' => $this->api_key,
				)
			)
		);

	}

	/**
	 * Returns a list of all articles
	 *
	 * @since   3.9.1
	 *
	 * @param   int $limit      Limit.
	 * @return  WP_Error|object
	 */
	public function view_articles( $limit = 100 ) {

		return $this->response(
			$this->post(
				'view_articles',
				array(
					'key'   => $this->api_key,
					'limit' => $limit,
				)
			)
		);

	}

	/**
	 * Submits a new research request for the given topic
	 *
	 * @since   3.9.1
	 *
	 * @param   string $topic              Topic.
	 * @param   string $length             Length (very_short,short,medium,long,very_long. default: short).
	 * @param   bool   $include_titles     Include titles in researched content.
	 * @param   float  $include_images     Probability of including images in researched content (0.00 to 1.00).
	 * @param   float  $include_videos     Probability of including videos in researched content (0.00 to 1.00).
	 * @return  WP_Error|object
	 */
	public function initiate_article( $topic, $length = 'short', $include_titles = false, $include_images = 0, $include_videos = 0 ) {

		return $this->response(
			$this->post(
				'initiate_article',
				array(
					'key'     => $this->api_key,
					'keyword' => $topic,
					'length'  => $length,
					'title'   => ( $include_titles ? '1' : '0' ),
					'image'   => $include_images,
					'video'   => $include_videos,
				)
			)
		);

	}

	/**
	 * Get progress of a research request
	 *
	 * @since   3.9.1
	 *
	 * @param   string $ref_key         Reference Key.
	 * @return  WP_Error|object
	 */
	public function get_api_progress( $ref_key ) {

		return $this->response(
			$this->post(
				'get_api_progress',
				array(
					'key'     => $this->api_key,
					'ref_key' => $ref_key,
				)
			)
		);

	}

	/**
	 * Get the given article by Reference Key.
	 *
	 * @since   3.9.1
	 *
	 * @param   string $ref_key         Reference Key.
	 * @return  WP_Error|object
	 */
	public function get_api_article_result( $ref_key ) {

		return $this->response(
			$this->post(
				'get_api_article_result',
				array(
					'key'     => $this->api_key,
					'ref_key' => $ref_key,
				)
			)
		);

	}

	/**
	 * Inspects the response from the API call, returning an error
	 * or data
	 *
	 * @since   3.9.1
	 *
	 * @param   WP_Error|object $response   Response.
	 * @return  WP_Error|object
	 */
	public function response( $response ) {

		// If the response is an error, return it.
		if ( is_wp_error( $response ) ) {
			return new WP_Error(
				'page_generator_pro_articleforge_error',
				sprintf(
					/* translators: Error message */
					__( 'ArticleForge: %s', 'page-generator-pro' ),
					$response->get_error_message()
				)
			);
		}

		// If the response's status key is 'Fail', bail.
		if ( isset( $response->status ) && $response->status === 'Fail' ) {
			// If an error message is supplied, return it.
			if ( isset( $response->error_message ) ) {
				return new WP_Error(
					'page_generator_pro_articleforge_error',
					sprintf(
						/* translators: API error message */
						__( 'ArticleForge: %s', 'page-generator-pro' ),
						$response->error_message
					)
				);
			}

			// Generic error occured.
			return new WP_Error(
				'page_generator_pro_articleforge_error',
				__( 'ArticleForge: An error occured', 'page-generator-pro' )
			);
		}

		return $response;

	}

}
