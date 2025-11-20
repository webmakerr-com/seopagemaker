<?php
/**
 * Research Class
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Interacts with third party APIs to build
 * content based on a given topic, and check the status
 * of a research request.
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 2.8.9
 */
class Page_Generator_Pro_Research {

	/**
	 * Holds the base object.
	 *
	 * @since   2.8.9
	 *
	 * @var     object
	 */
	public $base;

	/**
	 * Constructor.
	 *
	 * @since   2.8.9
	 *
	 * @param   object $base    Base Plugin Class.
	 */
	public function __construct( $base ) {

		// Store base class.
		$this->base = $base;

	}

	/**
	 * Return available research providers supported by this class.
	 *
	 * @since   2.8.9
	 *
	 * @return  array   Research Service Providers
	 */
	public function get_providers() {

		$providers = array();

		/**
		 * Defines the available research providers supported by this Plugin
		 *
		 * @since   2.8.9
		 *
		 * @param   array   $providers  Research Service Providers.
		 */
		$providers = apply_filters( 'page_generator_pro_research_get_providers', $providers );

		// Return filtered results.
		return $providers;

	}

	/**
	 * Returns settings fields for all research service providers.
	 *
	 * @since   3.9.1
	 *
	 * @return  array   Research service providers settings
	 */
	public function get_providers_settings_fields() {

		$settings_fields = array();

		/**
		 * Defines each spintax provider's settings to display at Settings > Research
		 *
		 * @since   3.9.1
		 *
		 * @param   array   $settings  Research Providers Settings Fields.
		 */
		$settings_fields = apply_filters( 'page_generator_pro_research_get_providers_settings_fields', $settings_fields );

		// Return filtered results.
		return $settings_fields;

	}

	/**
	 * Researches the given topic, sending the request to the configured third party
	 * service to return content later on.
	 *
	 * @since   2.8.9
	 *
	 * @param   string      $topic               Topic.
	 * @param   bool|string $user_instructions   User submitted instructions.
	 * @param   string      $content_type        Content Type.
	 * @param   int         $limit               Word Limit.
	 * @param   string      $language            Language code.
	 * @param   bool        $spintax             Return as spintax.
	 * @param   float       $temperature         Temperature.
	 * @param   float       $top_p               Top P / Nucleus Sampling.
	 * @param   float       $presence_penalty    Presence Penalty.
	 * @param   float       $frequency_penalty   Frequency Penalty.
	 *
	 * @return  WP_Error|string
	 */
	public function research( $topic, $user_instructions = false, $content_type = 'article', $limit = 250, $language = 'en', $spintax = false, $temperature = 1, $top_p = 1, $presence_penalty = 0, $frequency_penalty = 0 ) {

		// Get research provider.
		$provider = $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-research', 'provider' );

		/**
		 * Research content through the research provider for the given topic.
		 *
		 * @since   2.2.9
		 *
		 * @param   string  $topic                Topic.
		 * @param   string  $user_instructions    User submitted instructions.
		 * @param   string  $content_type         Content Type.
		 * @param   int     $limit                Word Limit.
		 * @param   string  $language             Language code.
		 * @param   bool    $spintax              Return as spintax.
		 * @param   float   $temperature          Temperature.
		 * @param   float   $top_p                Top P / Nucleus Sampling.
		 * @param   float   $presence_penalty     Presence Penalty.
		 * @param   float   $frequency_penalty    Frequency Penalty.
		 */
		$result = apply_filters( 'page_generator_pro_research_research_' . $provider, $user_instructions, $topic, $content_type, $limit, $language, $spintax, $temperature, $top_p, $presence_penalty, $frequency_penalty );

		// Return.
		return $result;

	}

	/**
	 * Returns the status of an existing research request.
	 *
	 * @since   2.8.9
	 *
	 * @param   string $id     ID of existing research request.
	 * @return  WP_Error|string
	 */
	public function get_status( $id ) {

		// Get research provider.
		$provider = $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-research', 'provider' );

		/**
		 * Get status of a research request for the research provider for the given article ID.
		 *
		 * @since   3.9.1
		 *
		 * @param   string  $id     Article Key / ID on research service.
		 */
		$result = apply_filters( 'page_generator_pro_research_get_status_' . $provider, $id );

		// Return.
		return $result;

	}

}
