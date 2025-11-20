<?php
/**
 * AI Trait
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Trait for registering an AI integration as a:
 * - Keyword Source
 * - Spintax Provider
 * - Research Provider
 * - Dynamic Element
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 */
trait Page_Generator_Pro_AI_Trait {

	/**
	 * Holds the prompt API endpoint
	 *
	 * @since   4.8.2
	 *
	 * @var     string
	 */
	public $prompt_api_endpoint = 'https://www.wpzinc.com';

	/**
	 * Whether the integration supports the `api_key` setting.
	 *
	 * @since   5.0.5
	 *
	 * @var     bool
	 */
	public $supports_settings_field_api_key = true;

	/**
	 * Whether the integration supports the `model` setting.
	 *
	 * @since   5.0.5
	 *
	 * @var     bool
	 */
	public $supports_settings_field_model = true;

	/**
	 * Whether the integration supports the `presence_penalty` parameter.
	 *
	 * @since   4.9.6
	 *
	 * @var     bool
	 */
	public $supports_presence_penalty = false;

	/**
	 * Whether the integration supports the `frequency_penalty` parameter.
	 *
	 * @since   4.9.6
	 *
	 * @var     bool
	 */
	public $supports_frequency_penalty = false;

	/**
	 * Returns API Key and Model Settings Fields, with configured values, for the integration
	 * using this trait across the following screens:
	 * - Settings > Integrations
	 * - Settings > Research
	 * - Settings > Spintax
	 *
	 * @since   4.8.0
	 *
	 * @param   array $settings_fields    Settings Fields.
	 * @return  array                     Settings Fields
	 */
	public function ai_settings_fields( $settings_fields ) {

		// Define fields and their values.
		$settings_fields[ $this->get_name() ] = array();

		if ( $this->supports_settings_field_api_key ) {
			/**
			 * API Key
			 */
			$settings_fields[ $this->get_name() ][ $this->get_settings_prefix() . '_api_key' ] = array(
				'label'         => sprintf(
					/* translators: %1$s: Integration name, %2$s Field label */
					'%1$s: %2$s',
					$this->get_title(),
					__( 'API Key', 'page-generator-pro' )
				),
				'type'          => 'text',
				'default_value' => $this->ai_get_api_key(),
				'description'   => sprintf(
					'%s %s %s %s %s %s',
					esc_html__( 'Enter your', 'page-generator-pro' ),
					$this->get_title(),
					esc_html__( 'API key', 'page-generator-pro' ),
					'<a href="' . esc_attr( $this->get_account_url() ) . '" target="_blank" rel="noopener">' . esc_html__( 'which can be found here', 'page-generator-pro' ) . '</a>',
					esc_html__( 'Don\'t have an account?', 'page-generator-pro' ),
					'<a href="' . esc_attr( $this->get_registration_url() ) . '" target="_blank" rel="noopener">' . esc_html__( 'Register an account', 'page-generator-pro' ) . '</a>'
				),
			);
		}

		if ( $this->supports_settings_field_model ) {
			/**
			 * Model
			 */
			$settings_fields[ $this->get_name() ][ $this->get_settings_prefix() . '_model' ] = array(
				'label'         => sprintf(
					/* translators: %1$s: Integration name, %2$s Field label */
					'%1$s: %2$s',
					$this->get_title(),
					__( 'Model', 'page-generator-pro' )
				),
				'type'          => 'select',
				'default_value' => $this->ai_get_model(),
				'values'        => $this->get_models(),
				'description'   => sprintf(
					'%1$s <a href="%2$s" target="_blank">%3$s %4$s</a> %5$s',
					esc_html__( 'The', 'page-generator-pro' ),
					esc_url( $this->model_documentation_url ),
					$this->get_title(),
					esc_html__( 'Model', 'page-generator-pro' ),
					esc_html__( 'to use for generating content.', 'page-generator-pro' )
				),
			);
		}

		return $settings_fields;

	}

	/**
	 * Registers this Source with the Keyword Sources system, so it's available
	 * to Keywords
	 *
	 * @since   4.1.2
	 *
	 * @param   array $sources    Sources.
	 * @return  array               Sources
	 */
	public function ai_register_keyword_source( $sources ) {

		// Don't register this source if no API Key has been specified in the Integration Settings.
		if ( ! $this->ai_has_api_key() ) {
			return $sources;
		}

		// Add Source.
		return array_merge(
			$sources,
			array(
				$this->get_name() => array(
					'name'    => $this->get_name(),
					'label'   => $this->get_title(),
					'options' => array(
						'prompt'   => array(
							'type'        => 'text',
							'label'       => __( 'Topic', 'page-generator-pro' ),
							'description' => __( 'The subject matter / topic this Keyword relates to e.g. plumbing services', 'page-generator-pro' ),
						),
						'columns'  => array(
							'type'        => 'text',
							'label'       => __( 'Data Columns', 'page-generator-pro' ),
							'description' => __( 'A comma separated list of data columns to include in the Terms e.g. service title, service description, average cost, average duration', 'page-generator-pro' ),
						),
						'limit'    => array(
							'type'        => 'number',
							'label'       => __( 'Limit', 'page-generator-pro' ),
							'min'         => 1,
							'max'         => 100,
							'step'        => 1,
							'value'       => 50,
							'description' => __( 'The maximum number of Terms to generate for this Keyword.', 'page-generator-pro' ),
						),
						'language' => array(
							'label'  => __( 'Language', 'page-generator-pro' ),
							'type'   => 'select',
							'values' => $this->base->get_class( 'common' )->get_languages(),
							'value'  => 'en',
						),
					),
				),
			)
		);

	}

	/**
	 * Prepares Keyword Data for this Source, based on the supplied form data,
	 * immediately before it's saved to the Keywords table in the database
	 *
	 * @since   4.1.2
	 *
	 * @param   array $keyword        Keyword Parameters.
	 * @return  WP_Error|array
	 */
	public function ai_save_keyword( $keyword ) {

		// Send request.
		$terms = $this->ai_create_completion(
			array(
				'subject' => $keyword['options']['prompt'],
				'columns' => $keyword['options']['columns'],
			),
			false, // Instructions.
			'keywords',
			$keyword['options']['limit'],
			$keyword['options']['language'],
			false,
			$this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-research', $this->get_settings_prefix() . '_model' )
		);

		// If an error occured, bail.
		if ( is_wp_error( $terms ) ) {
			return $terms;
		}

		// If columns are specified, extract them from the first row of the AI response.
		if ( $keyword['options']['columns'] ) {
			// Extract columns from first row, and rebuild terms without headings.
			$terms_arr = explode( "\n", $terms );
			$columns   = $terms_arr[0];
			unset( $terms_arr[0] );
			$terms = implode( "\n", $terms_arr );

			// Remove spaces between commas and column names.
			$columns = trim( $columns );
			$columns = str_replace( ', ', ',', $columns );
			$columns = str_replace( ' ,', ',', $columns );
			$columns = str_replace( ' ', '_', $columns );
			$columns = str_replace( '"', '', $columns );
		}

		// Merge delimiter, columns and data with Keyword.
		$keyword = array_merge(
			$keyword,
			array(
				'delimiter' => ! empty( $keyword['options']['columns'] ) ? ',' : '',
				'columns'   => isset( $columns ) ? $columns : '',
				'data'      => $terms,
			)
		);

		// Change the Keyword's source to local, so its Terms can be edited.
		$keyword['source'] = 'local';
		unset( $keyword['options'] );

		return $keyword;

	}

	/**
	 * Returns this block's Attributes and default values for Gutenberg.
	 *
	 * @since   4.1.3
	 */
	public function get_provider_attributes() {

		$attributes = array(
			'topic'                => array(
				'type'    => 'string',
				'default' => $this->get_default_value( 'topic' ),
			),
			'instructions'         => array(
				'type'    => 'string',
				'default' => $this->get_default_value( 'instructions' ),
			),
			'is_gutenberg_example' => array(
				'type'    => 'boolean',
				'default' => false,
			),
			'content_type'         => array(
				'type'    => 'string',
				'default' => $this->get_default_value( 'content_type' ),
			),
			'limit'                => array(
				'type'    => 'number',
				'default' => $this->get_default_value( 'limit' ),
			),
			'language'             => array(
				'type'    => 'string',
				'default' => $this->get_default_value( 'language' ),
			),
			'temperature'          => array(
				'type'    => 'number',
				'default' => $this->get_default_value( 'temperature' ),
			),
			'top_p'                => array(
				'type'    => 'number',
				'default' => $this->get_default_value( 'top_p' ),
			),
		);

		// If the presence penalty is supported, register its attribute.
		if ( $this->supports_presence_penalty ) {
			$attributes['presence_penalty'] = array(
				'type'    => 'number',
				'default' => $this->get_default_value( 'presence_penalty' ),
			);
		}

		// If the presence penalty is supported, register its attribute.
		if ( $this->supports_presence_penalty ) {
			$attributes['frequency_penalty'] = array(
				'type'    => 'number',
				'default' => $this->get_default_value( 'frequency_penalty' ),
			);
		}

		return $attributes;

	}

	/**
	 * Returns this shortcode / block's Fields
	 *
	 * @since   4.1.3
	 *
	 * @param   bool $supports_keywords  Support Keywords in Fields (false = when used by e.g. Research Dynamic Element).
	 */
	public function get_provider_fields( $supports_keywords = true ) {

		// Load Keywords class.
		if ( $supports_keywords ) {
			$keywords_class = $this->base->get_class( 'keywords' );

			// Bail if the Keywords class could not be loaded.
			if ( is_wp_error( $keywords_class ) ) {
				return false;
			}

			// Fetch Keywords.
			$keywords = $keywords_class->get_keywords_and_columns( true );
		}

		$fields = array(
			// General.
			'topic'        => array(
				'label'         => __( 'Topic', 'page-generator-pro' ),
				'type'          => ( $supports_keywords ? 'autocomplete_textarea' : 'textarea' ),
				'values'        => ( $supports_keywords ? $keywords : '' ),
				'placeholder'   => ( $supports_keywords ? __( 'e.g. how to find the best {service}', 'page-generator-pro' ) : __( 'e.g. how to find the best web designer', 'page-generator-pro' ) ),
				'default_value' => $this->get_default_value( 'topic' ),
				'description'   => __( 'Enter the topic the content should be written about.  For example, "web design" or "how to find the best web designer".', 'page-generator-pro' ),
			),
			'instructions' => array(
				'label'         => __( 'Instructions', 'page-generator-pro' ),
				'type'          => ( $supports_keywords ? 'autocomplete_textarea' : 'textarea' ),
				'values'        => ( $supports_keywords ? $keywords : '' ),
				'default_value' => $this->get_default_value( 'instructions' ),
				'description'   => __( 'Enter instructions for the AI to follow when generating the content.', 'page-generator-pro' ),
			),
			'content_type' => array(
				'label'         => __( 'Content Type', 'page-generator-pro' ),
				'type'          => 'select',
				'class'         => 'wpzinc-conditional',
				'data'          => array(
					// .components-panel is Gutenberg.
					// .wpzinc-tinymce-popup is TinyMCE.
					'container' => '.components-panel, .wpzinc-tinymce-popup',
				),
				'values'        => $this->base->get_class( 'common' )->get_ai_content_types(),
				'default_value' => $this->get_default_value( 'content_type' ),
				'description'   => __( 'The type of content to produce.', 'page-generator-pro' ),
			),
			'limit'        => array(
				'label'         => __( 'Limit', 'page-generator-pro' ),
				'type'          => 'number',
				'min'           => 1,
				'max'           => 2000,
				'step'          => 1,
				'default_value' => $this->get_default_value( 'limit' ),
				'description'   => __( 'The maximum length of the content. For articles, reviews and paragraphs, this is a word limit. For FAQs, this is the number of questions and answers to generate.', 'page-generator-pro' ),
				'condition'     => array(
					'key'        => 'content_type',
					'value'      => array( 'article', 'faq', 'paragraph', 'review', 'review_plain_text' ),
					'comparison' => '==',
				),
			),
			'language'     => array(
				'label'         => __( 'Language', 'page-generator-pro' ),
				'type'          => 'select',
				'values'        => $this->base->get_class( 'common' )->get_languages(),
				'default_value' => $this->get_default_value( 'language' ),
				'condition'     => array(
					'key'        => 'content_type',
					'value'      => array( 'article', 'faq', 'paragraph', 'review', 'review_plain_text' ),
					'comparison' => '==',
				),
			),

			// Tuning.
			'temperature'  => array(
				'label'         => __( 'Temperature', 'page-generator-pro' ),
				'type'          => 'number',
				'min'           => 0,
				'max'           => 2,
				'step'          => '0.1',
				'default_value' => $this->get_default_value( 'temperature' ),
				'description'   => __( 'Number between 0 and 2, in 0.1 steps. A higher value will make the output more random. A lower value will make it the output more focused and deterministic.', 'page-generator-pro' ),
			),
			'top_p'        => array(
				'label'         => __( 'Nucleus Sampling', 'page-generator-pro' ),
				'type'          => 'number',
				'min'           => 0,
				'max'           => 1,
				'step'          => '0.1',
				'default_value' => $this->get_default_value( 'top_p' ),
				'description'   => __( 'Number between 0 and 1, in 0.1 steps. Determines the pool of available tokens to choose from, between 0 and 1. An alternative to using Temperature. A higher value will include more, less accurate tokens. A lower value will include fewer, more accurate tokens.', 'page-generator-pro' ),
			),
		);

		// If the presence penalty is supported, register its field.
		if ( $this->supports_presence_penalty ) {
			$fields['presence_penalty'] = array(
				'label'         => __( 'Presence Penalty', 'page-generator-pro' ),
				'type'          => 'number',
				'min'           => -2,
				'max'           => 2,
				'step'          => '0.1',
				'default_value' => $this->get_default_value( 'presence_penalty' ),
				'description'   => __( 'Number between -2.0 and 2.0, in 0.1 steps. Positive values penalize new tokens based on whether they appear in the text so far, increasing the model\'s likelihood to talk about new topics.', 'page-generator-pro' ),
			);
		}

		// If the frequency penalty is supported, register its field.
		if ( $this->supports_frequency_penalty ) {
			$fields['frequency_penalty'] = array(
				'label'         => __( 'Frequency Penalty', 'page-generator-pro' ),
				'type'          => 'number',
				'min'           => -2,
				'max'           => 2,
				'step'          => '0.1',
				'default_value' => $this->get_default_value( 'frequency_penalty' ),
				'description'   => __( 'Number between -2.0 and 2.0, in 0.1 steps. Positive values penalize new tokens based on their existing frequency in the text so far, decreasing the model\'s likelihood to repeat the same line verbatim.', 'page-generator-pro' ),
			);
		}

		return $fields;

	}

	/**
	 * Returns this shortcode / block's UI Tabs
	 *
	 * @since   4.1.3
	 */
	public function get_provider_tabs() {

		// Define Tuning Fields, based on provider support.
		$tuning_fields = array(
			'temperature',
			'top_p',
		);
		if ( $this->supports_presence_penalty ) {
			$tuning_fields[] = 'presence_penalty';
		}
		if ( $this->supports_frequency_penalty ) {
			$tuning_fields[] = 'frequency_penalty';
		}

		// Return.
		return array(
			'general' => array(
				'label'  => __( 'General', 'page-generator-pro' ),
				'class'  => 'general',
				'fields' => array(
					'topic',
					'instructions',
					'content_type',
					'limit',
					'language',
				),
			),
			'tuning'  => array(
				'label'  => __( 'Tuning', 'page-generator-pro' ),
				'class'  => 'tuning',
				'fields' => $tuning_fields,
			),
		);

	}

	/**
	 * Returns default values for the Dynamic Element.
	 *
	 * @since   4.9.6
	 */
	public function get_provider_default_values() {

		return array(
			// General.
			'topic'             => '',
			'instructions'      => '',
			'content_type'      => 'article',
			'limit'             => 250,
			'language'          => 'en',

			// Tuning.
			'temperature'       => 1,
			'top_p'             => 1,
			'presence_penalty'  => 0,
			'frequency_penalty' => 0,
		);

	}

	/**
	 * Returns the API Key defined in the Settings > Integrations screen for the AI
	 * provider using this trait.
	 *
	 * @since   4.9.7
	 *
	 * @return  string
	 */
	public function ai_get_api_key() {

		return $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-research', $this->get_settings_prefix() . '_api_key' );

	}

	/**
	 * Returns whether an API Key is defined in the Settings > Integrations screen for the AI
	 * provider using this trait.
	 *
	 * @since   4.9.7
	 *
	 * @return  bool
	 */
	public function ai_has_api_key() {

		return ( ! empty( $this->ai_get_api_key() ) );

	}

	/**
	 * Returns the model defined in the Settings > Integrations screen for the AI
	 * provider using this trait.
	 *
	 * @since   4.9.7
	 *
	 * @return  string
	 */
	public function ai_get_model() {

		return $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-research', $this->get_settings_prefix() . '_model' );

	}

	/**
	 * Submits a new completion request for the given prompt.
	 *
	 * @since   4.8.0
	 *
	 * @param   array|string $prompt              Prompt (developer instructions).
	 * @param   bool|string  $user_instructions   User submitted instructions.
	 * @param   string       $content_type        Content Type (article).
	 * @param   int          $limit               Limit.
	 * @param   string       $language            Language code.
	 * @param   bool         $spintax             Return in Spintax format.
	 * @param   string       $model               Model to use.
	 * @param   float        $temperature         Temperature.
	 * @param   float        $top_p               Top P / Nucleus Sampling.
	 * @param   float        $presence_penalty    Presence Penalty.
	 * @param   float        $frequency_penalty   Frequency Penalty.
	 * @return  WP_Error|string
	 */
	public function ai_create_completion( $prompt, $user_instructions = false, $content_type = 'article', $limit = 250, $language = 'en', $spintax = false, $model = 'gpt-3.5-turbo', $temperature = 1, $top_p = 1, $presence_penalty = 0, $frequency_penalty = 0 ) {

		// Fetch prompt.
		$prompt_text = $this->get_prompt(
			array(
				'license_key'  => $this->base->licensing->get_license_key(),
				'prompt'       => $prompt,
				'content_type' => $content_type,
				'limit'        => $limit,
				'language'     => $language,
				'spintax'      => $spintax,
				'model'        => $model,
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
			'model'             => $model,
			'messages'          => array(
				array(
					'role'    => 'user',
					'content' => $prompt,
				),
			),
			'temperature'       => $temperature,
			'top_p'             => $top_p,
			'presence_penalty'  => $presence_penalty,
			'frequency_penalty' => $frequency_penalty,
		);

		// If instructions were returned from get_prompt() i.e. Claude AI, add them to the `system` key.
		if ( $instructions ) {
			$params['system'] = $instructions;
		}

		// If user instructions are provided, add them to the messages array.
		if ( $user_instructions ) {
			$params['messages'][] = array(
				'role'    => 'user',
				'content' => str_replace( "\n", '\n', $user_instructions ),
			);
		}

		// Send request.
		return $this->query( $prompt, $model, $params );

	}

	/**
	 * Sends the prompt to the research endpoint, to build and return content.
	 *
	 * @since   4.8.0
	 *
	 * @param   string      $prompt              Prompt (developer instructions).
	 * @param   bool|string $user_instructions   User submitted instructions.
	 * @param   string      $content_type        Content Type.
	 * @param   int         $limit               Word Limit.
	 * @param   string      $language            Language code.
	 * @param   bool        $spintax             Return as spintax.
	 * @param   float       $temperature         Temperature.
	 * @param   float       $top_p               Top P / Nucleus Sampling.
	 * @param   float       $presence_penalty    Presence Penalty.
	 * @param   float       $frequency_penalty   Frequency Penalty.
	 * @param   bool        $wrap_in_paragraphs  Wrap in paragraphs.
	 *
	 * @return  WP_Error|array
	 */
	public function ai_research( $prompt, $user_instructions = false, $content_type = 'article', $limit = 250, $language = 'en', $spintax = false, $temperature = 1, $top_p = 1, $presence_penalty = 0, $frequency_penalty = 0, $wrap_in_paragraphs = true ) {

		// Send request.
		$result = $this->ai_create_completion(
			$prompt,
			$user_instructions,
			$content_type,
			$limit,
			$language,
			$spintax,
			$this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-research', $this->get_settings_prefix() . '_model' ),
			$temperature,
			$top_p,
			$presence_penalty,
			$frequency_penalty
		);

		// If an error occured, bail.
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		// Strip slashes.
		$result = stripslashes( $result );

		// Convert to array.
		$lines = explode( "\n", $result );

		// Wrap in paragraphs.
		if ( $wrap_in_paragraphs ) {
			$result = wpautop( implode( "\n\n", $lines ) );
		} else {
			$result = implode( "\n\n", $lines );
		}

		// Return data.
		return array(
			'id'        => 0, // No ID used by default.
			'completed' => true, // We get an immediate result, so return it.
			'content'   => $result,
			'message'   => __( 'Research completed successfully.', 'page-generator-pro' ),
		);

	}

	/**
	 * Returns the supported output types for Keywords > Generate Locations > Cities.
	 *
	 * @since   5.0.1
	 *
	 * @param   array $output_types   Output Types.
	 * @return  array
	 */
	public function ai_generate_locations_output_types_cities( $output_types ) {

		return array_merge(
			$output_types,
			array(
				// Racial and Ethnic Composition.
				'city_racial_ethnic_composition_white'    => __( 'City: Racial and Ethnic Composition: White', 'page-generator-pro' ),
				'city_racial_ethnic_composition_black'    => __( 'City: Racial and Ethnic Composition: Black', 'page-generator-pro' ),
				'city_racial_ethnic_composition_asian'    => __( 'City: Racial and Ethnic Composition: Asian', 'page-generator-pro' ),
				'city_racial_ethnic_composition_hispanic' => __( 'City: Racial and Ethnic Composition: Hispanic', 'page-generator-pro' ),
				'city_racial_ethnic_composition_other'    => __( 'City: Racial and Ethnic Composition: Other', 'page-generator-pro' ),

				// Housing and Household.
				'city_housing_owner_occupied_unit_rate'   => __( 'City: Housing and Household: Owner Occupied Unit Rate', 'page-generator-pro' ),
				'city_housing_median_value'               => __( 'City: Housing and Household: Median Value', 'page-generator-pro' ),
				'city_housing_median_gross_rent'          => __( 'City: Housing and Household: Median Gross Rent', 'page-generator-pro' ),
				'city_housing_households_count'           => __( 'City: Housing and Household: Households Count', 'page-generator-pro' ),
				'city_housing_people_per_household'       => __( 'City: Housing and Household: People per Household', 'page-generator-pro' ),

				// Education.
				'city_education_high_school_and_higher'   => __( 'City: Education: High School and Higher', 'page-generator-pro' ),
				'city_education_bachelor_s_degree_and_higher' => __( 'City: Education: Bachelor\'s Degree and Higher', 'page-generator-pro' ),

				// Employment.
				'city_employment_labour_force_participation' => __( 'City: Employment: Labour Force Participation', 'page-generator-pro' ),
				'city_employment_unemployment_rate'       => __( 'City: Employment: Unemployment Rate', 'page-generator-pro' ),
				'city_employment_top_industries'          => __( 'City: Employment: Top Industries', 'page-generator-pro' ),
				'city_employment_largest_employers'       => __( 'City: Employment: Largest Employers', 'page-generator-pro' ),

				// Crime & Safety.
				'city_crime_violent_crime_rate'           => __( 'City: Crime and Safety: Violent Crime Rate', 'page-generator-pro' ),
				'city_crime_property_crime_rate'          => __( 'City: Crime and Safety: Property Crime Rate', 'page-generator-pro' ),

				// Weather & Climate.
				'city_weather_climate_type'               => __( 'City: Weather and Climate: Climate Type', 'page-generator-pro' ),
				'city_weather_average_summer_high'        => __( 'City: Weather and Climate: Average Summer High', 'page-generator-pro' ),
				'city_weather_average_winter_low'         => __( 'City: Weather and Climate: Average Winter Low', 'page-generator-pro' ),
				'city_weather_rainfall'                   => __( 'City: Weather and Climate: Rainfall', 'page-generator-pro' ),
				'city_weather_severe_weather'             => __( 'City: Weather and Climate: Severe Weather', 'page-generator-pro' ),

				// Income.
				'city_income_median_household_income'     => __( 'City: Income: Median Household Income', 'page-generator-pro' ),
				'city_income_per_capita_income'           => __( 'City: Income: Per Capita Income', 'page-generator-pro' ),
				'city_income_poverty_percentage'          => __( 'City: Income: Poverty Percentage', 'page-generator-pro' ),

				// History.
				'city_history_historical_overview'        => __( 'City: History: Historical Overview', 'page-generator-pro' ),
				'city_history_historical_events'          => __( 'City: History: Historical Events', 'page-generator-pro' ),
				'city_history_historical_figures'         => __( 'City: History: Historical Figures', 'page-generator-pro' ),
				'city_history_historical_places'          => __( 'City: History: Historical Places', 'page-generator-pro' ),
				'city_history_historical_documents'       => __( 'City: History: Historical Documents', 'page-generator-pro' ),
				'city_history_historical_artifacts'       => __( 'City: History: Historical Artifacts', 'page-generator-pro' ),
				'city_history_historical_sites'           => __( 'City: History: Historical Sites', 'page-generator-pro' ),

				// Health.
				'city_health_life_expectancy'             => __( 'City: Health: Life Expectancy', 'page-generator-pro' ),
				'city_health_mortality_rate'              => __( 'City: Health: Mortality Rate', 'page-generator-pro' ),
				'city_health_infant_mortality_rate'       => __( 'City: Health: Infant Mortality Rate', 'page-generator-pro' ),
				'city_health_smoking_rate'                => __( 'City: Health: Smoking Rate', 'page-generator-pro' ),
				'city_health_obesity_rate'                => __( 'City: Health: Obesity Rate', 'page-generator-pro' ),
				'city_health_physical_inactivity_rate'    => __( 'City: Health: Physical Inactivity Rate', 'page-generator-pro' ),
				'city_health_air_quality'                 => __( 'City: Health: Air Quality', 'page-generator-pro' ),
				'city_health_water_quality'               => __( 'City: Health: Water Quality', 'page-generator-pro' ),
			)
		);

	}

	/**
	 * Returns the supported output types for Keywords > Generate Locations > Countries.
	 *
	 * @since   5.0.1
	 *
	 * @param   array $output_types   Output Types.
	 * @return  array
	 */
	public function ai_generate_locations_output_types_countries( $output_types ) {

		return array_merge(
			$output_types,
			array(
				// Flag URL.
				'country_flag_url' => __( 'Country: Flag URL', 'page-generator-pro' ),
			)
		);

	}

	/**
	 * Sends the prompt to the generate locations endpoint, to build and return content.
	 *
	 * @since   5.0.0
	 *
	 * @param   string $prompt              Prompt.
	 * @param   string $content_type        Content Type.
	 * @param   int    $limit               Word Limit.
	 * @param   string $language            Language code.
	 * @param   bool   $spintax             Return as spintax.
	 * @param   float  $temperature         Temperature.
	 * @param   float  $top_p               Top P / Nucleus Sampling.
	 * @param   float  $presence_penalty    Presence Penalty.
	 * @param   float  $frequency_penalty   Frequency Penalty.
	 *
	 * @return  WP_Error|array
	 */
	public function ai_generate_locations( $prompt, $content_type = 'locations_area', $limit = 250, $language = 'en', $spintax = false, $temperature = 1, $top_p = 1, $presence_penalty = 0, $frequency_penalty = 0 ) {

		return $this->ai_create_completion(
			$prompt,
			false, // Instructions.
			$content_type,
			$limit,
			$language,
			$spintax,
			$this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-research', $this->get_settings_prefix() . '_model' ),
			$temperature,
			$top_p,
			$presence_penalty,
			$frequency_penalty
		);

	}

	/**
	 * Returns this shortcode / block's output
	 *
	 * @since   4.8.0
	 *
	 * @param  array $atts   Shortcode Attributes.
	 * @return string          Output
	 */
	public function render( $atts ) {

		// Parse attributes.
		$atts = $this->parse_atts( $atts );

		// Bail if AI is not configured.
		if ( ! $this->ai_has_api_key() ) {
			$error = new WP_Error(
				'page_generator_pro_ai_trait_render_error',
				sprintf(
					/* translators: AI Provider Name */
					esc_html__( '%s: No API key is defined at Settings > Integrations', 'page-generator-pro' ),
					$this->get_title()
				)
			);

			// Set a flag to denote a Dynamic Element error occured.
			$this->base->get_class( 'generate' )->add_dynamic_element_error( $error );

			return '';
		}

		// Fetch instructions from Settings > Integrations > AI: Instructions and any specified in the Dynamic Element.
		$instructions = array(
			$this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-integrations', 'ai_instructions' ),
			array_key_exists( 'instructions', $atts ) ? $atts['instructions'] : '',
		);

		// Remove empty strings from the array.
		$instructions = array_filter( $instructions );

		// Create instructions string, or set to false if no instructions are provided.
		$instructions = count( $instructions ) > 0 ? implode( "\n", $instructions ) : false;

		// Send request to AI.
		$result = $this->ai_research(
			$atts['topic'],
			$instructions,
			$atts['content_type'],
			$atts['limit'],
			$atts['language'],
			false, // Spintax.
			$atts['temperature'],
			$atts['top_p'],
			( array_key_exists( 'presence_penalty', $atts ) ? $atts['presence_penalty'] : 0 ),
			( array_key_exists( 'frequency_penalty', $atts ) ? $atts['frequency_penalty'] : 0 ),
			( $atts['content_type'] !== 'freeform' ? true : false )
		);

		// Handle errors.
		if ( is_wp_error( $result ) ) {
			return $this->add_dynamic_element_error_and_return( $result, $atts );
		}

		// Return content.
		return $result['content'];

	}

	/**
	 * Fetch the prompt text for the AI request.
	 *
	 * @since   4.2.3
	 *
	 * @param   array $params     Parameters.
	 * @return  WP_Error|stdClass|string
	 */
	public function get_prompt( $params ) {

		$result = wp_remote_post(
			$this->prompt_api_endpoint . '/?' . $this->get_settings_prefix() . '_prompt_api=1',
			array(
				'body'      => array(
					'params' => $params,
				),
				'timeout'   => 10,
				'sslverify' => false,
			)
		);

		// If an error occured, return it now.
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		// Fetch HTTP response code and body.
		$http_response_code = wp_remote_retrieve_response_code( $result );
		$body               = json_decode( wp_remote_retrieve_body( $result ) );

		// If the response code is 403, return an error as our prompt server blocked the request.
		switch ( $http_response_code ) {
			case 403:
				return new WP_Error(
					'page_generator_pro_' . $this->get_name() . '_error',
					sprintf(
						'%s: %s (%s)',
						$this->get_title(),
						__( 'Your request was blocked, due to making an unusually high number of requests to our prompt endpoint (which should not be happening). Reach out to support with your server IP address', 'page-generator-pro' ),
						array_key_exists( 'SERVER_ADDR', $_SERVER ) ? sanitize_text_field( wp_unslash( $_SERVER['SERVER_ADDR'] ) ) : __( ' Unknown IP Address', 'page-generator-pro' )
					)
				);
		}

		// Bail if the request was not successful.
		if ( ! $body->success ) {
			return new WP_Error(
				'page_generator_pro_' . $this->get_name() . '_error',
				sprintf(
					/* translators: %1$s: Integration name, %2$s: Error message */
					__( '%1$s: %2$s', 'page-generator-pro' ),
					$this->get_title(),
					$body->data
				)
			);
		}

		// Return prompt.
		return $body->data;

	}

	/**
	 * Calculates the maximum number of tokens for the AI request.
	 *
	 * @since   5.2.0
	 *
	 * @param   array $messages   Messages.
	 * @return  int
	 */
	public function ai_calculate_input_tokens( $messages ) {

		$input_tokens = 0;
		foreach ( $messages as $message ) {
			$input_tokens += strlen( $message['content'] );
		}

		return $input_tokens;

	}

}
