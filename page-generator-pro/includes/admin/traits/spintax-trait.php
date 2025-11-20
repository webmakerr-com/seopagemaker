<?php
/**
 * Spintax Trait
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Trait for registering an integration as a Spintax Provider.
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 */
trait Page_Generator_Pro_Spintax_Trait {

	/**
	 * Register this integration on spintax screens.
	 *
	 * @since   3.9.2
	 *
	 * @param   array $providers  Providers.
	 * @return  array               Research Providers
	 */
	public function register_spintax_integration( $providers ) {

		$providers[ $this->get_name() ] = $this->get_title();
		return $providers;

	}

	/**
	 * Adds spintax to the given content using the OpenAI API
	 *
	 * @since   4.1.0
	 *
	 * @param   string $content            Content.
	 * @param   array  $protected_words    Protected Words.
	 * @return  WP_Error|string
	 */
	public function ai_add_spintax( $content, $protected_words ) {

		// Build API compatible parameters.
		$model                  = $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-research', $this->get_name() . '_model', 'gpt-3.5-turbo' );
		$skip_capitalized_words = (bool) absint( $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-spintax', 'skip_capitalized_words', 1 ) );
		$language               = $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-spintax', 'language', 'en' );

		// Add spintax to content.
		return $this->text_with_spintax(
			$content,
			$language,
			$skip_capitalized_words,
			$protected_words,
			$model
		);

	}

	/**
	 * Submits a new completion request to convert the given content into spintax.
	 *
	 * @since   4.1.0
	 *
	 * @param   string     $content                 Content.
	 * @param   string     $language                Language code.
	 * @param   bool       $skip_capitalized_words  Exclude capitalized words from having spintax applied.
	 * @param   bool|array $protected_words         Exclude words from having spintax applied.
	 * @param   string     $model                   Model to use.
	 * @return  WP_Error|string
	 */
	public function text_with_spintax( $content, $language = 'en', $skip_capitalized_words = false, $protected_words = false, $model = 'gpt-3.5-turbo' ) {

		// Fetch prompt.
		$prompt_text = $this->get_prompt(
			array(
				'license_key'            => $this->base->licensing->get_license_key(),
				'prompt'                 => $content,
				'content_type'           => 'spintax',
				'limit'                  => 1, // Not used, but required for prompt API to work.
				'language'               => $language,
				'skip_capitalized_words' => $skip_capitalized_words,
				'protected_words'        => $protected_words,
				'model'                  => $model,
			)
		);

		// If an error occured, return it now.
		if ( is_wp_error( $prompt_text ) ) {
			return $prompt_text;
		}

		// Define prompt text and instructions.
		if ( isset( $prompt_text->instructions ) ) {
			// Claude AI.
			$prompt       = is_object( $prompt_text->prompt ) ? str_replace( "\n", '\n', $prompt_text->prompt->subject ) : str_replace( "\n", '\n', $prompt_text->prompt );
			$instructions = str_replace( "\n", '\n', $prompt_text->instructions );
		} else {
			// Others - prompt text includes instructions.
			$prompt       = str_replace( "\n", '\n', $prompt_text );
			$instructions = false;
		}

		// Build parameters.
		$params = array(
			'model'    => $model,
			'messages' => array(
				array(
					'role'    => 'user',
					'content' => $prompt,
				),
			),
		);

		// If instructions were returned from get_prompt() i.e. Claude AI, add them to the `system` key.
		if ( $instructions ) {
			$params['system'] = $instructions;
		}

		// Send request.
		return $this->query( $prompt, $model, $params );

	}

}
