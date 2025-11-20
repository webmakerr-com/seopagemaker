<?php
/**
 * AI Dynamic Element.
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * AI Dynamic Element.
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 */
class Page_Generator_Pro_Shortcode_AI extends Page_Generator_Pro_API {

	use Page_Generator_Pro_AI_Trait;
	use Page_Generator_Pro_Integration_Trait;
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
	 * @since   4.9.6
	 *
	 * @var     string
	 */
	public $name = 'ai';

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

		// Register Settings Fields.
		add_filter( 'page_generator_pro_integrations_get', array( $this, 'register_integration' ) );
		add_filter( 'page_generator_pro_integrations_get_settings_fields', array( $this, 'settings_fields' ) );

		// Register as a Dynamic Element.
		add_filter( 'page_generator_pro_shortcode_add_shortcodes', array( $this, 'add_shortcode' ) );

	}

	/**
	 * Returns the label of this integration.
	 *
	 * @since   4.9.6
	 *
	 * @return  string
	 */
	public function get_title() {

		return __( 'AI', 'page-generator-pro' );

	}

	/**
	 * Returns this shortcode / block's description.
	 *
	 * @since   4.9.6
	 *
	 * @return string
	 */
	public function get_description() {

		return __( 'Displays content from AI based on a topic.', 'page-generator-pro' );

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
			__( 'AI', 'page-generator-pro' ),
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

		return 'assets/images/icons/ai.svg';

	}

	/**
	 * Returns an array of supported models.
	 *
	 * @since   4.9.6
	 *
	 * @return  array
	 */
	public function get_models() {

		// This is deliberately blank; there's no models setting for this integration,
		// and we need this definition for PHPStan compat.
		return array();

	}

	/**
	 * Returns this shortcode / block's class name and render callback function name.
	 *
	 * @since   4.9.6
	 *
	 * @return  array
	 */
	public function get_render_callback() {

		return array( 'shortcode_ai', 'render' );

	}

	/**
	 * Returns this shortcode / block's TinyMCE modal width and height.
	 *
	 * @since   4.9.6
	 *
	 * @return  array
	 */
	public function get_modal_dimensions() {

		return array(
			'width'  => 800,
			'height' => 505,
		);

	}

	/**
	 * Returns API Key and Model Settings Fields, with configured values, for the integration
	 * using this trait across the following screens:
	 * - Settings > Integrations
	 *
	 * @since   4.8.0
	 *
	 * @param   array $settings_fields    Settings Fields.
	 * @return  array                     Settings Fields
	 */
	public function settings_fields( $settings_fields ) {

		// Define fields and their values.
		$settings_fields[ $this->get_name() ] = array(
			$this->get_settings_prefix() . '_provider'     => array(
				'label'         => sprintf(
					/* translators: %1$s: Integration name, %2$s Field label */
					'%1$s: %2$s',
					$this->get_title(),
					__( 'Provider', 'page-generator-pro' )
				),
				'type'          => 'select',
				'default_value' => $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-integrations', $this->get_settings_prefix() . '_provider' ),
				'values'        => $this->get_providers(),
				'description'   => esc_html__( 'The third party service to use for the AI Dynamic Element and Add New using AI functionality. Use settings fields below to specify the API Key and Model for the chosen provider.', 'page-generator-pro' ),
			),
			$this->get_settings_prefix() . '_instructions' => array(
				'label'         => sprintf(
					/* translators: %1$s: Integration name, %2$s Field label */
					'%1$s: %2$s',
					$this->get_title(),
					__( 'Instructions', 'page-generator-pro' )
				),
				'type'          => 'textarea',
				'default_value' => $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-integrations', $this->get_settings_prefix() . '_instructions' ),
				'description'   => esc_html__( 'Instructions to pass to all AI Dynamic Elements used. To provide instructions specific to an individual AI Dynamic Element, use the "Instructions" field in the AI Dynamic Element settings.', 'page-generator-pro' ),
			),
		);

		return $settings_fields;

	}

	/**
	 * Returns providers that support this AI Dynamic Element.
	 *
	 * @since   4.9.6
	 *
	 * @return  array
	 */
	public function get_providers() {

		$providers = array();

		/**
		 * Register providers that support this AI Dynamic Element.
		 *
		 * @since   4.9.6
		 *
		 * @return  array
		 */
		$providers = apply_filters( 'page_generator_pro_shortcode_ai_get_providers', $providers );

		return $providers;

	}

	/**
	 * Returns this shortcode / block's output
	 *
	 * @since   4.9.6
	 *
	 * @param  array $atts   Shortcode Attributes.
	 * @return string          Output
	 */
	public function render( $atts ) {

		// Fetch the provider to use.
		$provider = $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-integrations', 'ai_provider' );

		// Change the name to the provider, so the AI's render() call fetches the correct API Key and Model.
		$this->name = $provider;

		// Attempt to get the provider's class.
		$integration = $this->base->get_class( $this->get_settings_prefix() );

		// Call provider's render() method.
		return $integration->render( $atts );

	}

	/**
	 * Sends a prompt, with options to define the model and additional parameters.
	 *
	 * @since   4.7.9
	 *
	 * @param   string $prompt_text    Prompt Text.
	 * @param   string $model          Model.
	 * @param   array  $params         Additional request / query parameters e.g. temperature, top_p.
	 */
	private function query( $prompt_text, $model = '', $params = array() ) {

		// This is deliberately blank; there's no call to query() for this integration,
		// and we need this definition for PHPStan compat.
		return '';

	}

}
