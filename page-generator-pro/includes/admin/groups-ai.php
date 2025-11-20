<?php
/**
 * Groups AI Class
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Provides a UI for creating a Content Group with keywords and content,
 * using AI.
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 4.1.0
 */
class Page_Generator_Pro_Groups_AI {

	/**
	 * Holds the base object.
	 *
	 * @since   4.1.0
	 *
	 * @var     object
	 */
	public $base;

	/**
	 * The current step
	 *
	 * @var     int
	 */
	public $step = 1;

	/**
	 * The current configuration
	 *
	 * @var     array
	 */
	public $configuration = array();

	/**
	 * Constructor.
	 *
	 * @since   4.1.0
	 *
	 * @param   object $base    Base Plugin Class.
	 */
	public function __construct( $base ) {

		// Store base class.
		$this->base = $base;

		add_action( 'admin_init', array( $this, 'maybe_load' ) );
		add_filter( 'page_generator_pro_groups_ui_output_add_new_buttons', array( $this, 'register_button' ) );

	}

	/**
	 * Loads the Groups AI screen if the request URL is for this class
	 *
	 * @since   4.1.0
	 */
	public function maybe_load() {

		// Bail if this isn't a request for the Groups AI screen.
		if ( ! $this->is_groups_ai_request() ) {
			return;
		}

		// Define current screen.
		set_current_screen( $this->base->plugin->name . '-groups-ai' );

		// Process posted form data.
		$result = $this->process_form();

		// If an error occured in processing, show it on screen.
		if ( is_wp_error( $result ) ) {
			$this->base->get_class( 'notices' )->add_error_notice( $result->get_error_message() );
		}

		// Output custom HTML for the Groups AI screen.
		$this->output_header();
		$this->output_content();
		$this->output_footer();
		exit;

	}

	/**
	 * Registers a button on the Content Groups WP_List_Table for this wizard.
	 *
	 * @since   4.1.0
	 *
	 * @param   array $buttons    Buttons.
	 * @return  array               Buttons
	 */
	public function register_button( $buttons ) {

		// Fetch the provider to use.
		$provider = $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-integrations', 'ai_provider' );
		if ( empty( $provider ) ) {
			return $buttons;
		}

		// Attempt to get the provider's class.
		$integration = $this->base->get_class( str_replace( '-', '_', $provider ) );

		// Bail if the AI provider does not have an API Key configured.
		if ( ! $integration->ai_has_api_key() ) {
			return $buttons;
		}

		// Register button.
		$buttons['page-generator-pro-groups-ai'] = array(
			'label' => __( 'Add New using AI', 'page-generator-pro' ),
			'url'   => 'admin.php?page=page-generator-pro-groups-ai',
			'class' => 'groups_ai',
		);

		return $buttons;

	}

	/**
	 * Process posted form data, if any exists
	 *
	 * @since   4.1.0
	 *
	 * @return  WP_Error|bool
	 */
	private function process_form() {

		// Define default configuration.
		$this->configuration = array(  // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
			'service'      => '',
			'limit'        => 250,
			'language'     => 'en',
			'page_builder' => '',
		);

		// Fetch the provider to use.
		$provider = $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-integrations', 'ai_provider' );
		if ( empty( $provider ) ) {
			return new WP_Error( 'page_generator_pro_groups_ai_process_form', __( 'Please choose an AI provider at Settings > Integrations > AI: Provider', 'page-generator-pro' ) );
		}

		// Attempt to get the provider's class.
		$integration = $this->base->get_class( str_replace( '-', '_', $provider ) );

		// Bail if the AI provider does not have an API Key configured.
		if ( ! $integration->ai_has_api_key() ) {
			return new WP_Error(
				'page_generator_pro_groups_ai_process_form',
				sprintf(
					/* translators: AI Provider name */
					esc_html__( 'Please define the AI API Key at Settings > Integrations > %s: API Key', 'page-generator-pro' ),
					$integration->get_name()
				)
			);
		}

		// Assume we're on the current step.
		$this->step = ( isset( $_REQUEST['step'] ) ? absint( $_REQUEST['step'] ) : 1 ); // phpcs:ignore WordPress.Security.NonceVerification

		// Run security checks.
		if ( ! isset( $_POST[ $this->base->plugin->name . '_nonce' ] ) ) {
			return false;
		}
		if ( ! wp_verify_nonce( sanitize_key( $_POST[ $this->base->plugin->name . '_nonce' ] ), 'page-generator-pro' ) ) {
			return new WP_Error( 'page_generator_pro_groups_ai_process_form', __( 'Invalid nonce specified.', 'page-generator-pro' ) );
		}

		// Decode the current configuration.
		$this->configuration = ( isset( $_REQUEST['configuration'] ) ? json_decode( wp_unslash( $_REQUEST['configuration'] ), true ) : $this->configuration ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

		// Depending on the step we're on, check the form data.
		switch ( $this->step ) {
			/**
			 * Setup
			 */
			case 1:
				// Add to configuration.
				$this->configuration = array_merge(
					$this->configuration,
					array(
						'service'      => isset( $_POST['service'] ) ? sanitize_text_field( wp_unslash( $_POST['service'] ) ) : '',
						'limit'        => isset( $_POST['limit'] ) ? sanitize_text_field( wp_unslash( $_POST['limit'] ) ) : '',
						'language'     => isset( $_POST['language'] ) ? sanitize_text_field( wp_unslash( $_POST['language'] ) ) : '',
						'page_builder' => isset( $_POST['page_builder'] ) ? sanitize_text_field( wp_unslash( $_POST['page_builder'] ) ) : '',
					)
				);

				// Check required fields are completed.
				if ( empty( $this->configuration['service'] ) ) {
					return new WP_Error(
						'page_generator_pro_groups_ai_process_form_error',
						__( 'A service or product must be entered', 'page-generator-pro' )
					);
				}

				// Create Keyword Terms.
				$terms = $integration->ai_create_completion(
					$this->configuration['service'],
					false, // Instructions.
					'keywords',
					50,
					$this->configuration['language'],
					false,
					$integration->ai_get_model()
				);

				// Bail if an error occured.
				if ( is_wp_error( $terms ) ) {
					return $terms;
				}

				/**
				 * Determine the page builder version to tell AI to generate for.
				 *
				 * @since   5.0.4
				 *
				 * @var     string
				 */
				$page_builder_version = apply_filters( 'page_generator_pro_groups_ai_page_builder_version_' . $this->configuration['page_builder'], '' );

				/**
				 * Determine the page builder modules that are available.
				 *
				 * @since   5.0.4
				 *
				 * @var     string
				 */
				$page_builder_modules = apply_filters( 'page_generator_pro_groups_ai_page_builder_modules_' . $this->configuration['page_builder'], array() );

				// Use AI to generate the content group.
				$content = $integration->ai_create_completion(
					array(
						'subject'              => $this->configuration['service'],
						'page_builder'         => $this->configuration['page_builder'],
						'page_builder_version' => $page_builder_version,
						'page_builder_modules' => $page_builder_modules,
					),
					false, // Instructions.
					'content_group',
					$this->configuration['limit'],
					$this->configuration['language'],
					false,
					$integration->ai_get_model()
				);

				// Bail if an error occured.
				if ( is_wp_error( $content ) ) {
					return $content;
				}

				// Setup Keyword.
				$keyword_result = $this->setup_keyword( 'service', $terms );
				if ( is_wp_error( $keyword_result ) ) {
					return $keyword_result;
				}
				$this->configuration['service_keyword']    = $keyword_result['keyword'];
				$this->configuration['service_keyword_id'] = $keyword_result['id'];

				// Setup Content Group.
				$content_group_result = $this->setup_content_group(
					'{' . $keyword_result['keyword'] . '}',
					$content,
					$this->configuration['service'],
					'{' . $keyword_result['keyword'] . '}',
					$this->configuration['page_builder']
				);
				if ( is_wp_error( $content_group_result ) ) {
					return $content_group_result;
				}
				$this->configuration['content_group_ids'] = array( $content_group_result );
				break;
		}

		// If here, form validation/processing was successful.
		// Increment the step so that the next section is displayed.
		++$this->step;

		return true;

	}

	/**
	 * Creates a Keyword based on the supplied configuration
	 *
	 * @since   4.1.0
	 *
	 * @param   string $keyword    Keyword.
	 * @param   string $terms      Terms.
	 * @return  WP_Error|array
	 */
	private function setup_keyword( $keyword, $terms ) {

		// Get unique keyword name that can be used.
		$keyword = $this->base->get_class( 'keywords' )->get_unique_name( $keyword );

		// Create keyword.
		$result = $this->base->get_class( 'keywords' )->save(
			array(
				'keyword' => $keyword,
				'data'    => $terms,
			)
		);

		// Bail if an error occured.
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		// Return ID and Keyword Name.
		return array(
			'id'      => $result,
			'keyword' => $keyword,
		);

	}

	/**
	 * Creates the Region Content Group
	 *
	 * @since   4.1.0
	 *
	 * @param   string $title                  Title.
	 * @param   string $content                Content.
	 * @param   string $placeholder_keyword    Placeholder keyword that might be in content (e.g. {placeholder}).
	 * @param   string $keyword                Keyword to replace placeholder keyword with in content (e.g. {service_2}).
	 * @param   string $page_builder           Page Builder to use.
	 * @return  WP_Error|int
	 */
	private function setup_content_group( $title, $content, $placeholder_keyword, $keyword, $page_builder = '' ) {

		// Replace placeholder keyword with keyword.
		$content = str_ireplace( $placeholder_keyword, $keyword, $content );

		switch ( $page_builder ) {
			case '':
			case 'gutenberg':
				// Return result of creating Content Group.
				return $this->base->get_class( 'groups' )->create(
					array(
						'title'   => $title,
						'content' => $content,
					)
				);

			default:
				// Create Content Group without content.
				$content_group = $this->base->get_class( 'groups' )->create(
					array(
						'title' => $title,
					)
				);

				// Bail if an error occured.
				if ( is_wp_error( $content_group ) ) {
					return $content_group;
				}

				/**
				 * Setup Content Group's content when using Add New Using AI.
				 *
				 * @since   5.0.4
				 *
				 * @param   int    $content_group_id    Content Group ID.
				 * @param   string $content           Content.
				 */
				return apply_filters( 'page_generator_pro_groups_ai_setup_content_group_content_' . $page_builder, $content_group, $content );

		}

	}

	/**
	 * Outputs the <head> and opening <body> tag for the standalone Groups AI screen
	 *
	 * @since   4.1.0
	 */
	private function output_header() {

		// Remove scripts.
		remove_all_actions( 'admin_notices' );
		remove_all_actions( 'all_admin_notices' );

		// Enqueue scripts.
		do_action( 'admin_enqueue_scripts' );

		// Load header view.
		include_once $this->base->plugin->folder . '/views/admin/wizard/header.php';

	}

	/**
	 * Outputs the HTML for the <body> section for the standalone Groups AI screen,
	 * and defines any form option data that might be needed.
	 *
	 * @since   4.1.0
	 */
	private function output_content() {

		// Load form data.
		switch ( $this->step ) {
			/**
			 * Setup
			 */
			case 1:
				$back_button_url   = 'edit.php?post_type=page-generator-pro';
				$back_button_label = __( 'Cancel', 'page-generator-pro' );
				$next_button_label = __( 'Create', 'page-generator-pro' );

				// Define supported page builders that can be used to create a Content Group using AI.
				$supported_page_builders = array(
					'' => __( 'Classic Editor / TinyMCE', 'page-generator-pro' ),
				);

				/**
				 * Define supported page builders that can be used to create a Content Group using AI.
				 *
				 * @since   5.0.4
				 *
				 * @var     array
				 */
				$supported_page_builders = apply_filters( 'page_generator_pro_groups_ai_supported_page_builders', $supported_page_builders );
				break;

			/**
			 * Done
			 */
			case 2:
				// Define UI.
				$back_button_url   = 'edit.php?post_type=page-generator-pro';
				$back_button_label = __( 'Finish', 'page-generator-pro' );
				break;
		}

		// Load content view.
		include_once $this->base->plugin->folder . '/views/admin/groups-ai/content.php';

	}

	/**
	 * Outputs the closing </body> and </html> tags, and runs some WordPress actions, for the standalone Groups AI screen
	 *
	 * @since   4.1.0
	 */
	private function output_footer() {

		do_action( 'admin_footer', '' );
		do_action( 'admin_print_footer_scripts' );

		// Load footer view.
		include_once $this->base->plugin->folder . '/views/admin/wizard/footer.php';

	}

	/**
	 * Determines if the request is for the Groups AI screen
	 *
	 * @since   4.1.0
	 *
	 * @return  bool    Is Groups AI Request
	 */
	private function is_groups_ai_request() {

		// Don't load if this is an AJAX call.
		if ( wp_doing_ajax() || wp_doing_cron() ) {
			return false;
		}

		// Bail if we're not on the Groups AI screen.
		if ( ! isset( $_GET['page'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			return false;
		}
		if ( sanitize_text_field( wp_unslash( $_GET['page'] ) ) !== $this->base->plugin->name . '-groups-ai' ) { // phpcs:ignore WordPress.Security.NonceVerification
			return false;
		}

		return true;

	}

}
