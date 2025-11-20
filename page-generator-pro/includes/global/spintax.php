<?php
/**
 * Spintax Class
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Converts spintax to text.
 * Registers third party spintax providers to convert from text to spintax.
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 1.0.0
 */
class Page_Generator_Pro_Spintax {

	/**
	 * Holds the base object.
	 *
	 * @since   1.9.8
	 *
	 * @var     object
	 */
	public $base;

	/**
	 * Holds an array of words and spintax replacements when using
	 * add_spintax()
	 *
	 * @since   1.7.9
	 *
	 * @var     array
	 */
	public $replacements = array();

	/**
	 * Constructor.
	 *
	 * @since   1.9.8
	 *
	 * @param   object $base    Base Plugin Class.
	 */
	public function __construct( $base ) {

		// Store base class.
		$this->base = $base;

		// Frontend.
		add_action( 'wp_loaded', array( $this, 'maybe_process_on_frontend' ) );

	}

	/**
	 * If processing Spintax is enabled on the frontend through the Plugin's Settings,
	 * adds the necessary filters to permit spintax processing.
	 *
	 * @since   3.2.9
	 */
	public function maybe_process_on_frontend() {

		// Bail if processing spintax isn't enabled.
		if ( ! $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-spintax', 'frontend', 0 ) ) {
			return;
		}

		// Register Plugin filters on Post Elements to process spintax on.
		add_filter( 'page_generator_pro_frontend_filter_site_title', array( $this, 'process' ) );
		add_filter( 'page_generator_pro_frontend_filter_post_title', array( $this, 'process' ) );
		add_filter( 'page_generator_pro_frontend_filter_post_content', array( $this, 'process' ) );
		add_filter( 'page_generator_pro_frontend_filter_post_excerpt', array( $this, 'process' ) );

	}

	/**
	 * Searches for spintax, replacing each spintax with one term
	 *
	 * @since   1.0.0
	 *
	 * @param   string $text   Text.
	 * @return  WP_Error|string         Text
	 */
	public function process( $text ) {

		// Use fastest method to process spintax.
		$spun_text = preg_replace_callback(
			'/\{(((?>[^\{\}]+)|(?R))*?)\}/x',
			array( $this, 'replace' ),
			$text
		);

		// If the method worked, we'll have a result - return it.
		if ( ! empty( $spun_text ) && ! is_null( $spun_text ) ) { // @phpstan-ignore-line
			return $spun_text;
		}

		// If here, the spintax is too long for PHP to process.
		// Fallback to a slower but more reliable method.
		while ( strpos( $text, '{' ) !== false && strpos( $text, '}' ) !== false && strpos( $text, '|' ) !== false ) {
			$text = preg_replace_callback(
				'/\{(((?>[^\{\}]+))*?)\}/x',
				array( $this, 'replace' ),
				$text
			);

			if ( is_null( $text ) ) {
				switch ( preg_last_error() ) {
					case PREG_NO_ERROR:
						return new WP_Error( 'page_generator_pro_spintax_process_no_error', __( 'Spintax Error: No Error', 'page-generator-pro' ) );

					case PREG_INTERNAL_ERROR:
						return new WP_Error( 'page_generator_pro_spintax_process_no_error', __( 'Spintax Error: Internal Error', 'page-generator-pro' ) );

					case PREG_BACKTRACK_LIMIT_ERROR:
						return new WP_Error( 'page_generator_pro_spintax_process_no_error', __( 'Spintax Error: Backtrack Limit Hit', 'page-generator-pro' ) );

					case PREG_RECURSION_LIMIT_ERROR:
						return new WP_Error( 'page_generator_pro_spintax_process_no_error', __( 'Spintax Error: Recursion Limit Hit', 'page-generator-pro' ) );

					case PREG_BAD_UTF8_ERROR:
						return new WP_Error( 'page_generator_pro_spintax_process_no_error', __( 'Spintax Error: Bad UTF-8 encountered', 'page-generator-pro' ) );

					case PREG_BAD_UTF8_OFFSET_ERROR:
						return new WP_Error( 'page_generator_pro_spintax_process_no_error', __( 'Spintax Error: Bad UTF-8 offset encountered', 'page-generator-pro' ) );

					case PREG_JIT_STACKLIMIT_ERROR:
						return new WP_Error( 'page_generator_pro_spintax_process_no_error', __( 'Spintax Error: JIT Stack Limit Hit', 'page-generator-pro' ) );
				}
			}
		}

		return $text;

	}

	/**
	 * Replaces spintax with text
	 *
	 * @since   1.0.0
	 *
	 * @param   array $text   Text.
	 * @return  WP_Error|string         Text
	 */
	public function replace( $text ) {

		// If the text starts with {| and ends with |}, this isn't spintax.
		// It might be Thrive Architect shortcode / JSON for e.g. Posts List.
		if ( substr( $text[0], 0, 2 ) === '{|' ) {
			if ( substr( $text[0], strlen( $text[0] ) - 2 ) === '|}' ) {
				return $text[0];
			}
		}

		// Process.
		$processed_text = $this->process( $text[1] );

		// Bail if an error occured.
		if ( is_wp_error( $processed_text ) ) {
			return $processed_text;
		}

		// If no pipe delimiter exists, this isn't spintax.
		// It might be CSS or JSON, so we need to return the original string with the curly braces.
		if ( strpos( $processed_text, '|' ) === false ) {
			return '{' . $processed_text . '}';
		}

		// If the text looks like JSON, and decoding it works, it might be e.g. Gutenberg Block JSON
		// that contains a pipe delimiter, which we don't want to process.
		// Return the original string with the curly braces.
		$json = json_decode( '{' . $processed_text . '}', true );
		if ( is_array( $json ) && json_last_error() === JSON_ERROR_NONE ) {
			return '{' . $processed_text . '}';
		}

		// If a double pipe delimiter exists, this isn't spintax.
		// It might be JS, so we need to return the original string with the curly braces.
		if ( strpos( $processed_text, '||' ) !== false ) {
			return '{' . $processed_text . '}';
		}

		// Explode the spintax options and return a random array value.
		$parts = explode( '|', $processed_text );
		return $parts[ array_rand( $parts ) ];

	}

	/**
	 * Return available spintax providers supported by this class.
	 *
	 * @since   2.8.9
	 *
	 * @return  array   Spintax Service Providers
	 */
	public function get_providers() {

		$providers = array(
			'' => __( 'This Plugin', 'page-generator-pro' ),
		);

		/**
		 * Defines the available spintax providers supported by this Plugin
		 *
		 * @since   2.8.9
		 *
		 * @param   array   $providers  Spintax Service Providers.
		 */
		$providers = apply_filters( 'page_generator_pro_spintax_get_providers', $providers );

		// Return filtered results.
		return $providers;

	}

	/**
	 * Returns settings fields for all spintax service providers.
	 *
	 * @since   3.9.1
	 *
	 * @return  array   Spintax service providers settings
	 */
	public function get_providers_settings_fields() {

		$settings_fields = array();

		/**
		 * Defines each spintax provider's settings to display at Settings > Spintax
		 *
		 * @since   3.9.1
		 *
		 * @param   array   $settings  Spintax Providers Settings Fields.
		 */
		$settings_fields = apply_filters( 'page_generator_pro_spintax_get_providers_settings_fields', $settings_fields );

		// Return filtered results.
		return $settings_fields;

	}

	/**
	 * Takes the given content, returning a spintax syntax version of it,
	 * using the spintax provider defined at Settings > Spintax.
	 *
	 * @since   1.7.9
	 *
	 * @param   string $content    Content.
	 * @return  WP_Error|string
	 */
	public function add_spintax( $content ) {

		// Get protected words.
		$protected_words = $this->get_protected_words( $content );

		// Get spintax provider.
		$provider = $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-spintax', 'provider' );

		// To help some spintax providers, we need to convert paragraphs to breaklines.
		$content = str_replace( '</p><p>', "\n\n", $content );
		$content = str_replace( '<p>', '', $content );
		$content = str_replace( '</p>', '', $content );
		$content = trim( $content );

		// Depending on whether we are using a local or third party spintax provider, perform spinning now.
		switch ( $provider ) {
			case '':
				$spintax_content = $this->add_spintax_local( $content, $protected_words );
				break;

			default:
				/**
				 * Filter to add spintax to content for a third party spintax provider.
				 *
				 * @since   2.2.9
				 *
				 * @param   string  $content                Content.
				 * @param   array   $protected_words        Protected Words.
				 */
				$spintax_content = apply_filters( 'page_generator_pro_spintax_add_spintax_' . $provider, $content, $protected_words );

				break;
		}

		// If an error occured, bail.
		if ( is_wp_error( $spintax_content ) ) { // @phpstan-ignore-line
			return $spintax_content;
		}

		/**
		 * Filter spintax content before returning
		 *
		 * @since   2.2.9
		 *
		 * @param   string  $spintax_content        Spintax Content.
		 * @param   string  $content                Original Content.
		 * @param   array   $protected_words        Protected Words.
		 * @param   string  $provider               Spintax Provider.
		 */
		$spintax_content = apply_filters( 'page_generator_pro_spintax_add_spintax', $spintax_content, $content, $protected_words, $provider );

		// If the spintax content matches the original content, we couldn't apply spintax.
		if ( $spintax_content === $content ) {
			// Define error message, depending on the provider.
			switch ( $provider ) {
				case '':
					$error = sprintf(
						/* translators: Link to spintax settings */
						__( 'Spintax Error: No spintax could be added to the provided content.  Enable a third party spintax service in the %s for better results.', 'page-generator-pro' ),
						'<a href="admin.php?page=page-generator-pro-settings&tab=page-generator-pro-spintax" target="_blank" rel="noopener">' . __( 'Settings', 'page-generator-pro' ) . '</a>'
					);
					break;

				default:
					$error = __( 'Spintax Error: No spintax could be added to the provided content.  Consider selecting/writing more content', 'page-generator-pro' );
					break;
			}

			// Return error.
			return new WP_Error( 'page_generator_pro_spintax_add_spintax_failed', $error );
		}

		// Trim and add paragraphs back.
		$spintax_content = trim( $spintax_content );
		$spintax_content = wpautop( $spintax_content );

		// Return.
		return $spintax_content;

	}

	/**
	 * Adds spintax to the given content using the Plugin's Synonyms Dictionary
	 *
	 * @since   2.2.9
	 *
	 * @param   string $content            Content.
	 * @param   array  $protected_words    Protected Words.
	 * @return  string                              Content with Spintax
	 */
	private function add_spintax_local( $content, $protected_words ) {

		// Determine if capitalized words should be skipped.
		$skip_capitalized_words = $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-spintax', 'skip_capitalized_words', 1 );
		$regex_modifier         = ( $skip_capitalized_words ? '' : 'i' );

		// Convert protected words all to lowercase.
		foreach ( $protected_words as $key => $protected_word ) {
			$protected_words[ $key ] = strtolower( $protected_word );
		}

		// Iterate through synonyms.
		foreach ( $this->get_synonyms() as $synonyms ) {
			// Build first character uppercase synonym variant.
			$uppercase_synonyms = array();
			foreach ( $synonyms as $synonym ) {
				$uppercase_synonyms[] = ucfirst( $synonym );
			}

			foreach ( $synonyms as $word ) {
				// Skip if the word is protected.
				if ( in_array( $word, $protected_words, true ) ) {
					continue;
				}

				// Replace first word of paragraph, if not skipping capitalized words.
				if ( ! $skip_capitalized_words ) {
					if ( preg_match( '/(\n)\b' . ucfirst( $word ) . '\b\s/', $content ) ) {
						$content = preg_replace( '/\b' . ucfirst( $word ) . '\b\s/', '{' . implode( '|', $uppercase_synonyms ) . '} ', $content );
					}
				}

				// Lowercase, or case insensitive, whole word with space either side.
				if ( preg_match( '/\b\s' . $word . '\s\b/' . $regex_modifier, $content ) ) {
					$content = preg_replace( '/\b\s' . $word . '\s\b/' . $regex_modifier, ' {' . implode( '|', $synonyms ) . '} ', $content );
				}
			}
		}

		// Return.
		return $content;

	}

	/**
	 * Returns an array of protected words defined in the Plugin's settings,
	 * as well as keywords and shortcodes in the given content,
	 * to prevent spintax generation from replacing words, keywords and shortcodes
	 * with spintax.
	 *
	 * @since   2.2.9
	 *
	 * @param   string $content    Content.
	 * @return  array
	 */
	private function get_protected_words( $content ) {

		// Fetch all protected words stored in the Plugin's settings.
		$protected_words_settings = array_values(
			array_filter(
				explode( "\n", $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-spintax', 'protected_words' ) )
			)
		);

		// Fetch all keywords, spintax and shortcodes in the content, so we can exclude it
		// from processing.
		// This prevents e.g. {service} becoming {service|solution} or [gallery] becoming [photographs].
		preg_match_all( '|{(.+?)}|', $content, $keywords );
		preg_match_all( '/' . get_shortcode_regex() . '/', $content, $shortcodes );

		// If no keywords, spintax and shortcodes exist, just return the protected words.
		if ( count( $keywords[0] ) === 0 && count( $shortcodes[0] ) === 0 ) {
			return $protected_words_settings;
		}

		// Build array of keyword names and shortcode names.
		$protected_words = array_merge( $protected_words_settings, $keywords[1], $shortcodes[2] );

		// Add shortcode attributes as protected words.
		foreach ( $shortcodes[3] as $shortcode => $atts ) {
			// Skip if the shortcode has no attributes.
			if ( empty( $atts ) ) {
				continue;
			}

			// Parse attributes.
			$atts = shortcode_parse_atts( $atts );

			// Add to protected words list.
			foreach ( $atts as $key => $value ) {
				$protected_words[] = $key;
				$protected_words[] = $value;
			}
		}

		// Make the array unique.
		$protected_words = array_values( array_unique( $protected_words ) );

		// Iterate through array, to trim each word to ensure it has no trailing spaces/characters.
		foreach ( $protected_words as $key => $word ) {
			$protected_words[ $key ] = trim( $word );
		}

		/**
		 * Define the array of protected words to not apply spintax to
		 *
		 * @since   2.2.9
		 *
		 * @param   array   $protected_words            Protected Words.
		 * @param   array   $protected_words_settings   Protected Words from Plugin settings.
		 * @param   array   $keywords                   Keywords preg_match_all() results.
		 * @param   array   $shortcodes                 Shortcodes preg_match_all() results.
		 */
		$protected_words = apply_filters( 'page_generator_pro_spintax_get_protected_words', $protected_words, $protected_words_settings, $keywords, $shortcodes );

		// Return.
		return $protected_words;

	}

	/**
	 * Returns an array of spintax words
	 *
	 * @since   1.7.9
	 *
	 * @return  array   Spintax Words
	 */
	private function get_synonyms() {

		// Define array of similar words.
		$spintax = array(
			array( 'accept', 'take' ),
			array( 'box', 'bin' ),
			array( 'achieve', 'accomplish', 'attain', 'reach' ),
			array( 'test', 'exam' ),
			array( 'house', 'home' ),
			array( 'maybe ', 'most likely' ),
			array( 'same', 'similar', 'thesame' ),
			array( 'hot', 'warm' ),
			array( 'angry', 'mad' ),
			array( 'big', 'huge' ),
			array( 'mother', 'mom', 'mommy' ),
			array( 'father', 'dad', 'daddy' ),
			array( 'perfect', 'absolute' ),
			array( 'boy', 'guy' ),
			array( 'girl', 'woman' ),
			array( 'broken', 'damage' ),
			array( 'Amazing', 'incredible', 'unbelievable' ),
			array( 'Answer', 'reply', 'respond' ),
			array( 'Ask', 'question' ),
			array( 'Awful', 'terrible', 'unpleasant' ),
			array( 'Beautiful', 'pretty', 'lovely' ),
			array( 'Begin', 'start' ),
			array( 'Big', 'enormous', 'huge', 'immense', 'gigantic', 'vast' ),
			array( 'Brave', 'courageous', 'fearless' ),
			array( 'Break', 'fracture', 'rupture' ),
			array( 'Bright', 'shining', 'shiny', 'gleaming' ),
			array( 'Cool', 'chilly', 'cold', 'frosty' ),
			array( 'Definite', 'certain', 'sure', 'positive', 'determined', 'clear', 'distinct' ),
			array( 'Delicious', 'savory', 'delectable', 'appetizing' ),
			array( 'Fall', 'drop' ),
			array( 'End', 'stop' ),
			array( 'Famous', 'well-known', 'renowned' ),
			array( 'Fast', 'quick' ),
			array( 'Fly', 'soar', 'hover' ),
			array( 'Get', 'acquire' ),
			array( 'Good', 'fine' ),
			array( 'Wrong', 'incorrect' ),
			array( 'Quiet', 'silent' ),
			array( 'Stop', 'cease' ),
			array( 'Scared', 'afraid' ),
			array( 'Popular', 'well-liked' ),
			array( 'Make', 'create' ),
			array( 'smash', 'wreck', 'crash' ),
			array( 'smart', 'intellectual' ),
			array( 'arrive', 'reach' ),
			array( 'terrifying', 'gross' ),
			array( 'Strange', 'odd', 'peculiar', 'unusual', 'unfamiliar', 'uncommon' ),
			array( 'inform', 'notify' ),
			array( 'Plan', 'plot', 'scheme' ),
			array( 'Place', 'area' ),
			array( 'fraction', 'fragment' ),
			array( 'Kill', 'slay', 'execute' ),
			array( 'preserve', 'maintain' ),
			array( 'handsome', 'attractive' ),
			array( 'Dangerous', 'risky' ),
			array( 'affirmation', 'allegation', 'claim' ),
			array( 'constructed', 'build up' ),
			array( 'Tiny', 'little' ),
			array( 'improper', 'unsuitable' ),
			array( 'challenging', 'inspiring' ),
			array( 'cherish', 'adore', 'treasure' ),
			array( 'clarify', 'define' ),
			array( 'absorbing', 'consuming' ),
			array( 'deduce', 'deduct' ),
			array( 'gain', 'get' ),
			array( 'occupy', 'fill' ),
			array( 'anything', 'all', 'everything', 'whatever' ),
			array( 'somehow', 'anyhow', 'someway' ),
			array( 'fire', 'blaze', 'ember', 'flare', 'flame' ),
			array( 'buy', 'purchase' ),
			array( 'sick', 'ill' ),
			array( 'quickly', 'speedily' ),
			array( 'on', 'upon' ),
			array( 'rich,wealthy' ),
			array( 'poor,needy' ),
			array( 'enemy,foe' ),
			array( 'plate,dish' ),
			array( 'flower,blossom' ),
			array( 'quiet,silent' ),
			array( 'come,arrive' ),
			array( 'taxi,cab' ),
			array( 'laugh,giggle' ),
			array( 'cry,sob' ),
			array( 'drive,steer' ),
			array( 'cool,chilly' ),
			array( 'cold,icy' ),
			array( 'sad,unhappy' ),
			array( 'fire,flame' ),
			array( 'chair,seat' ),
			array( 'friend,pal' ),
			array( 'loud,noisy' ),
			array( 'lead,guide' ),
			array( 'late,tardy' ),
			array( 'song,tune' ),
			array( 'cut', 'clip' ),
			array( 'sleep', 'snooze' ),
			array( 'begin', 'start' ),
			array( 'error', 'mistake' ),
			array( 'filthy', 'dirty' ),
			array( 'throw', 'toss' ),
			array( 'teach', 'tutor' ),
			array( 'permit', 'allow' ),
			array( 'save', 'keep' ),
			array( 'all', 'every' ),
			array( 'kind', 'nice' ),
			array( 'hope', 'wish' ),
			array( 'smell', 'odor' ),
			array( 'selfish', 'greedy' ),
			array( 'choose', 'pick' ),
			array( 'ship', 'boat' ),
			array( 'children', 'kids' ),
			array( 'robber', 'thief' ),
			array( 'shove', 'push' ),
			array( 'damp', 'wet' ),
			array( 'paste', 'glue' ),
			array( 'smile', 'grin' ),
			array( 'get', 'receive' ),
			array( 'hurry', 'rush' ),
			array( 'lid', 'cover' ),
			array( 'center', 'middle' ),
			array( 'fight', 'battle' ),
			array( 'center', 'middle' ),
			array( 'harm', 'hurt' ),
			array( 'love', 'adore' ),
			array( 'approximately', 'nearly' ),
			array( 'moreover', 'besides' ),
			array( 'astonishing', 'startling', 'stunning' ),
			array( 'impressive', 'fabulous' ),
			array( 'dreadful', 'alarming' ),
			array( 'yet', 'still', 'nevertheless' ),
			array( 'seat', 'chair' ),
			array( 'simple', 'easy' ),
			array( 'mad', 'angry' ),
			array( 'stone', 'rock' ),
			array( 'skinny', 'thin' ),
			array( 'giggle', 'laugh' ),
			array( 'hop', 'jump' ),
			array( 'weird', 'strange' ),
			array( 'loud', 'noisy' ),
			array( 'clever', 'smart' ),
			array( 'middle', 'centre' ),
			array( 'difficult', 'hard' ),
			array( 'friendly', 'kind' ),
			array( 'chat', 'talk' ),
			array( 'tidy', 'clean' ),
			array( 'depart', 'leave' ),
			array( 'let', 'allow' ),
			array( 'repair', 'fix' ),
			array( 'amazed', 'surprised' ),
			array( 'lucky', 'fortunate' ),
			array( 'desire', 'want' ),
			array( 'guard', 'protect' ),
			array( 'summit', 'top' ),
			array( 'solution', 'answer' ),
			array( 'costly', 'expensive' ),
			array( 'fragment', 'piece' ),
			array( 'insane', 'crazy' ),
			array( 'depressed', 'sad' ),
			array( 'understand', 'comprehend' ),
			array( 'irate', 'furious' ),
			array( 'prohibited', 'forbidden' ),
			array( 'praise', 'compliment' ),
			array( 'despise', 'hate' ),
			array( 'conceal', 'hide' ),
			array( 'profit', 'gain' ),
			array( 'recommend', 'suggest' ),
			array( 'examine', 'inspect' ),
			array( 'construct', 'build' ),
			array( 'anxious', 'worried' ),
			array( 'predict', 'forecast' ),
			array( 'recall', 'remember' ),
			array( 'attempt', 'try' ),
			array( 'weary', 'tired' ),
			array( 'ruin', 'destroy' ),
			array( 'diligent', 'hardworking' ),
			array( 'persuade', 'convince' ),
			array( 'locate', 'find' ),
			array( 'wealthy', 'rich' ),
			array( 'cautious', 'careful' ),
			array( 'woman', 'lady' ),
			array( 'Smart', 'Bright', 'Brilliant', 'Sharp' ),
			array( 'Shy', 'Bashful', 'Quiet' ),
			array( 'Moist', 'watery', 'soppy', 'soggy', 'drenched', 'awashed' ),
			array( 'Elongated', 'lengthy', 'outstretched', 'extended' ),
			array( 'crate', 'box' ),
			array( 'crash', 'accident' ),
			array( 'rabbit', 'bunny' ),
			array( 'hear', 'listen' ),
			array( 'happy', 'glad' ),
			array( 'hat', 'cap' ),
			array( 'close', 'near' ),
			array( 'trash', 'garbage' ),
			array( 'friend', 'buddy' ),
			array( 'under', 'below' ),
			array( 'pail', 'bucket' ),
			array( 'couch', 'sofa' ),
			array( 'speak', 'talk' ),
			array( 'sack', 'bag' ),
			array( 'yell', 'shout' ),
			array( 'gift', 'present' ),
			array( 'hungry', 'famished' ),
			array( 'see', 'look' ),
			array( 'untrue', 'false' ),
			array( 'lift', 'raise' ),
			array( 'chef', 'cook' ),
			array( 'pull', 'tug' ),
			array( 'cup', 'mug' ),
			array( 'thin', 'slender' ),
			array( 'sum', 'total' ),
			array( 'purple', 'periwinkle' ),
			array( 'loud', 'noisy' ),
			array( 'beverage', 'drink' ),
			array( 'suitcase', 'luggage' ),
			array( 'beach', 'seashore' ),
			array( 'slip', 'fall' ),
			array( 'magic', 'illusion' ),
			array( 'strong', 'mighty' ),
			array( 'brown', 'beige' ),
			array( 'smart', 'clever' ),
			array( 'allegiance', 'loyalty' ),
			array( 'gems', 'jewels' ),
			array( 'crack', 'break' ),
			array( 'wallet', 'billfold' ),
			array( 'jacket', 'coat' ),
			array( 'nap', 'sleep' ),
			array( 'vacation', 'trip' ),
			array( 'harbor', 'port' ),
			array( 'round', 'circular' ),
			array( 'genuine', 'real' ),
			array( 'rant', 'rave' ),
			array( 'stare', 'gaze' ),
			array( 'carpet', 'rug' ),
			array( 'easy', 'simple' ),
			array( 'funny', 'hilarious' ),
			array( 'sad', 'depressing' ),
			array( 'smelly', 'stinky' ),
			array( 'say', 'tell' ),
			array( 'hug', 'embrace' ),
			array( 'employ', 'hire' ),
			array( 'kiss', 'smooch' ),
			array( 'ugly', 'disgusting' ),
			array( 'good', 'great' ),
			array( 'hat', 'cap' ),
			array( 'Review', 'Evaluation' ),
			array( 'Subject', 'Topic' ),
			array( 'Anger', 'enrage', 'infuriate', 'arouse', 'nettle', 'exasperate', 'inflame', 'madden' ),
			array( 'Describe', 'portray', 'characterize', 'picture' ),
			array( 'execute', 'enact' ),
			array( 'boring', 'tiring', 'tiresome' ),
			array( 'Eager', 'keen', 'fervent' ),
			array( 'Gross', 'improper', 'rude', 'coarse', 'indecent' ),
			array( 'necessary', 'vital', 'critical', 'indispensable', 'valuable', 'essential' ),
			array( 'Interesting', 'fascinating', 'engaging' ),
			array( 'Keep', 'hold', 'retain', 'withhold', 'preserve', 'maintain', 'sustain', 'support' ),
			array( 'Moody', 'temperamental', 'changeable', 'short-tempered' ),
			array( 'Scared', 'afraid', 'frightened', 'alarmed', 'terrified', 'panicked' ),
			array( 'pick', 'choose', 'select', 'prefer' ),
			array( 'pause', 'discontinue' ),
			array( 'mutter', 'mumble', 'whisper', 'sigh' ),
			array( 'fabricate', 'manufacture', 'produce', 'build', 'develop' ),
			array( 'well-behaved', 'obedient', 'honorable', 'reliable', 'trustworthy' ),
			array( 'fabulous', 'wonderful', 'fantastic', 'astonishing', 'astounding', 'extraordinary' ),
			array( 'demand', 'request' ),
			array( 'glowing', 'sparkling' ),
			array( 'broad', 'expansive', 'spacious' ),
			array( 'heroic', 'valorous', 'audacious' ),
			array( 'shimmering', 'radiant', 'vivid', 'colorful', 'lustrous', 'luminous' ),
			array( 'mild', 'serene', 'smooth' ),
			array( 'terminate', 'halt' ),
			array( 'interpret', 'justify' ),
			array( 'blank', 'empty' ),
			array( 'broad', 'wide' ),
			array( 'center', 'middle' ),
			array( 'cunning', 'clever' ),
			array( 'dangerous', 'risky' ),
			array( 'eatable', 'edible' ),
			array( 'false', 'untrue' ),
			array( 'fertile', 'fruitful' ),
			array( 'gay', 'cheerful' ),
			array( 'glad', 'happy' ),
			array( 'hard', 'difficult' ),
			array( 'high', 'tall' ),
			array( 'huge', 'enormous' ),
			array( 'intelligent', 'clever' ),
			array( 'lazy', 'indolent' ),
			array( 'little', 'small' ),
			array( 'loving', 'fond' ),
			array( 'loyal', 'faithful' ),
			array( 'mad', 'crazy' ),
			array( 'nice', 'kind' ),
			array( 'noisy', 'rowdy' ),
			array( 'polite', 'courteous' ),
			array( 'poor', 'destitute' ),
			array( 'quick', 'fast' ),
			array( 'rare', 'scarce' ),
			array( 'real', 'genuine' ),
			array( 'rich', 'wealthy' ),
			array( 'rude', 'impolite' ),
			array( 'sad', 'unhappy' ),
			array( 'safe', 'secure' ),
			array( 'sleepy', 'drowsy' ),
			array( 'slim', 'slender' ),
			array( 'thin', 'lean' ),
			array( 'usual', 'normal' ),
			array( 'vacant', 'empty' ),
			array( 'weak', 'feeble' ),
			array( 'well-known', 'famous' ),
			array( 'desertion', 'leaving behind', 'leaving', 'rejection', 'neglect' ),
			array( 'lower', 'demean', 'degrade', 'belittle', 'humiliate', 'subjugate' ),
			array( 'humiliate', 'humble', 'deflate', 'mortify' ),
			array( 'embarrassed', 'ashamed', 'mortified', 'disconcerted', 'dismayed', 'confused' ),
			array( 'decrease', 'subside', 'grow less', 'decline', 'fade away', 'fall', 'stop', 'halt', 'end', 'terminate' ),
			array( 'narrowing', 'reduction', 'lessening', 'point', 'dwindling', 'tapering off' ),
			array( 'fundamentals', 'essentials', 'nitty-gritty', 'nuts and bolts' ),
			array( 'renounce', 'relinquish', 'resign', 'step down from', 'hand over', 'give up', 'abandon' ),
			array( 'resignation', 'handing over', 'renunciation', 'abandonment' ),
			array( 'stomach', 'front', 'belly', 'tummy' ),
			array( 'kidnap', 'snatch', 'make off with', 'seize', 'hold somebody against their will' ),
			array( 'kidnap', 'seizure' ),
			array( 'abnormal', 'unusual', 'deviant', 'anomalous', 'peculiar', 'uncharacteristic', 'irregular', 'atypical' ),
			array( 'deviation', 'abnormality', 'anomaly', 'irregularity', 'peculiarity', 'eccentricity', 'oddness' ),
			array( 'assist', 'help', 'support', 'back', 'back up', 'encourage', 'urge on', 'put up to', 'incite' ),
			array( 'accomplice', 'partner', 'partner in crime', 'assistant', 'co-conspirator' ),
			array( 'detest', 'hate', 'loathe', 'dislike', 'despise', 'be repulsed by', 'be revolted by' ),
			array( 'hatred', 'loathing', 'detestation', 'disgust', 'repugnance', 'revulsion', 'abomination' ),
			array( 'repugnant', 'objectionable', 'repulsive', 'detestable', 'hateful', 'distasteful', 'disgusting' ),
			array( 'enduring', 'remaining', 'surviving', 'long-lasting', 'permanent', 'unshakable', 'steadfast' ),
			array( 'aptitude', 'skill', 'capability', 'capacity', 'facility', 'talent', 'gift', 'knack', 'power', 'faculty', 'capacity', 'capability' ),
			array( 'hopeless', 'miserable', 'wretched', 'dismal', 'horrible', 'utter' ),
			array( 'renunciation', 'disavowal', 'rejection', 'disowning', 'forswearing' ),
			array( 'renounce', 'disavow', 'reject', 'disown', 'forswear', 'give up' ),
			array( 'on fire', 'blazing', 'burning', 'in flames', 'alight', 'afire' ),
			array( 'clever', 'talented', 'intelligent', 'bright', 'gifted', 'capable', 'competent', 'proficient', 'adept', 'skilled' ),
			array( 'capably', 'well', 'skillfully', 'competently', 'with ease', 'without difficulty' ),
			array( 'irregularity', 'aberration', 'anomaly', 'deviation', 'oddity', 'idiosyncrasy', 'defect', 'deformity', 'irregularity', 'malformation', 'malfunction', 'fault' ),
			array( 'on board', 'on the ship', 'on the train', 'on the bus' ),
			array( 'house', 'residence', 'dwelling', 'habitat', 'quarters', 'domicile', 'address' ),
			array( 'repulsive', 'offensive', 'detestable', 'monstrous', 'terrible', 'awful', 'horrible', 'vile', 'horrendous', 'dreadful' ),
			array( 'dreadfully', 'atrociously', 'revoltingly', 'horribly', 'terribly', 'awfully', 'badly' ),
			array( 'hate', 'loathe', 'detest', 'abhor' ),
			array( 'outrage', 'disgrace', 'scandal', 'eyesore', 'atrocity', 'hatred', 'dislike', 'repugnance', 'loathing', 'revulsion', 'abhorrence', 'detestation' ),
			array( 'indigenous', 'original', 'native' ),
			array( 'unsuccessful', 'failed', 'fruitless', 'unproductive', 'futile', 'bungled' ),
			array( 'be plentiful', 'thrive', 'flourish', 'proliferate' ),
			array( 'wealthy', 'affluent', 'rich', 'well-off', 'well-to-do', 'flourishing', 'thriving', 'successful', 'booming' ),
			array( 'concerning', 'regarding', 'in relation to', 'on the subject of', 'on', 'with reference to', 'as regards', 'a propos', 'vis-Ð°-vis', 're', 'approximately', 'roughly', 'in the region of', 'around', 'almost', 'nearly', 'approaching', 'not far off from', 'on the order of', 'going on for', 'in this area', 'roughly speaking', 'more or less', 'something like', 'just about', 'all but' ),
			array( 'on top of', 'over', 'higher than', 'more than', 'greater than', 'higher than', 'beyond', 'exceeding' ),
			array( 'genuine', 'authentic', 'real', 'true', 'valid', 'legitimate', 'legal', 'authenticated' ),
			array( 'hocus-pocus', 'open sesame', 'hey presto' ),
			array( 'graze', 'scrape', 'roughen', 'chafe', 'grind down', 'grind' ),
			array( 'scrape', 'scratch', 'scuff', 'graze', 'cut' ),
			array( 'rough', 'coarse', 'harsh', 'rasping', 'scratchy', 'rude', 'sharp', 'uncompromising', 'harsh', 'brusque', 'argumentative', 'aggressive', 'unfriendly', 'gruff', 'severe', 'prickly' ),
			array( 'harshly', 'coarsely', 'roughly', 'scratchily' ),
			array( 'side by side', 'alongside each other', 'shoulder to shoulder' ),
			array( 'shorten', 'edit', 'condense', 'reduce', 'abbreviate', 'cut' ),
			array( 'shortened', 'edited', 'condensed', 'reduced', 'abbreviated' ),
			array( 'synopsis', 'digest', 'condensation' ),
			array( 'sudden', 'unexpected', 'rapid', 'hasty', 'immediate', 'quick', 'rushed', 'curt', 'short', 'brusque', 'terse', 'sharp', 'rude', 'gruff' ),
			array( 'suddenly', 'unexpectedly', 'rapidly', 'hastily', 'immediately', 'quickly', 'hurriedly', 'brusquely', 'shortly', 'tersely', 'snappishly', 'rudely', 'sharply', 'gruffly' ),
			array( 'brusqueness', 'shortness', 'terseness', 'sharpness', 'rudeness', 'gruffness' ),
			array( 'boil', 'sore', 'swelling', 'eruption', 'blister', 'carbuncle', 'pustule' ),
			array( 'run away', 'escape', 'break out', 'leave suddenly', 'make off', 'flee', 'run off' ),
			array( 'desertion', 'running away', 'disappearance', 'abandonment', 'AWOL' ),
			array( 'nonattendance', 'nonappearance', 'lack', 'nonexistence', 'deficiency', 'want', 'dearth' ),
			array( 'truant', 'malingerer', 'runaway', 'absconder' ),
			array( 'absence', 'non-attendance', 'malingering' ),
			array( 'absentmindedly', 'carelessly', 'negligently', 'neglectfully', 'vaguely', 'inattentively' ),
			array( 'total', 'complete', 'utter', 'unqualified', 'unconditional', 'unlimited', 'supreme', 'fixed', 'unmodified', 'unadulterated', 'pure', 'perfect', 'unquestionable', 'conclusive', 'resolved', 'firm', 'definite', 'unmovable', 'final', 'unchangeable', 'fixed idea', 'solution', 'answer', 'resolution', 'truth', 'given' ),
			array( 'totally', 'completely', 'utterly', 'extremely', 'entirely', 'enormously', 'very', 'definitely', 'certainly', 'no question', 'agreed', 'unconditionally', 'unquestionably', 'categorically' ),
			array( 'forgiveness', 'pardon', 'release', 'freedom', 'liberty' ),
			array( 'totalitarianism', 'despotism', 'dictatorship', 'tyranny', 'autocracy' ),
			array( 'pardon', 'forgive', 'clear', 'release', 'free' ),
			array( 'porous', 'permeable', 'leaky', 'spongy' ),
			array( 'amalgamation', 'incorporation', 'assimilation', 'combination', 'inclusion', 'fascination', 'interest', 'captivation', 'engagement', 'immersion', 'raptness', 'concentration' ),
			array( 'desist', 'refrain', 'withdraw', 'withhold', 'go without', 'give up', 'decline to vote', 'sit on the fence' ),
			array( 'nonparticipation', 'abstention', 'no vote', 'refraining' ),
			array( 'self-denying', 'self-disciplined', 'moderate', 'ascetic', 'sober', 'temperate', 'teetotal' ),
			array( 'nonparticipation', 'abstaining', 'no vote', 'refraining' ),
			array( 'self-denial', 'self-restraint', 'self-discipline', 'moderation', 'asceticism' ),
			array( 'ascetic', 'abstemious', 'sober', 'temperate', 'teetotal', 'dry' ),
			array( 'inattentive', 'preoccupied', 'vague', 'distant', 'distracted', 'absentminded' ),
			array( 'inattentively', 'vaguely', 'distantly', 'distractedly', 'absentmindedly' ),
			array( 'theoretically', 'conceptually' ),
			array( 'obscure', 'perplexing', 'puzzling', 'complex', 'profound', 'mysterious', 'rarefied', 'technical', 'highbrow' ),
			array( 'complexity', 'obscurity', 'difficulty', 'profundity', 'mysteriousness' ),
			array( 'illogicality', 'irrationality', 'silliness', 'ludicrousness', 'ridiculousness', 'meaninglessness', 'farce', 'joke' ),
			array( 'ridiculously', 'idiotically', 'ludicrously', 'farcically', 'nonsensically', 'meaninglessly', 'preposterously', 'oddly' ),
			array( 'profusion', 'great quantity', 'large quantity', 'plenty', 'loads', 'wealth' ),
			array( 'plentiful', 'copious', 'rich', 'profuse' ),
			array( 'in abundance', 'in large quantities', 'plentifully', 'copiously', 'richly', 'profusely', 'lavishly' ),
			array( 'mistreatment', 'cruelty', 'ill-treatment', 'violence', 'maltreatment', 'neglect', 'exploitation', 'misuse', 'exploitation', 'manipulation', 'insults', 'verbal abuse', 'swearing', 'name-calling', 'foul language', 'invective', 'treat badly', 'ill-treat', 'mistreat', 'maltreat', 'molest', 'be violent towards', 'batter', 'hurt', 'harm', 'injure', 'insult', 'swear', 'shout abuse', 'hurl abuse', 'shout insults', 'call names', 'use foul language', 'exploit', 'take advantage of', 'misuse', 'manipulate' ),
			array( 'addict', 'user' ),
			array( 'rudely', 'offensively', 'impertinently', 'impolitely', 'indecently', 'unpleasantly', 'vulgarly', 'nastily', 'outrageously', 'disgracefully' ),
			array( 'rudeness', 'impoliteness', 'indecency', 'vulgarity', 'nastiness', 'tastelessness', 'unpleasantness' ),
			array( 'be next to', 'adjoin', 'be adjacent to', 'touch', 'lie alongside' ),
			array( 'adjoining', 'next to', 'adjacent to', 'against', 'neighboring' ),
			array( 'terribly', 'awfully', 'dreadfully', 'appallingly', 'very badly', 'extremely badly' ),
			array( 'gulf', 'chasm', 'deep hole', 'void' ),
			array( 'academic world', 'academic circles', 'academe', 'university', 'university circles', 'the academy', 'college circles' ),
			array( 'educational', 'school', 'college', 'university', 'scholastic', 'studious', 'intellectual', 'scholarly', 'bookish', 'literary', 'learned', 'theoretical', 'speculative', 'moot', 'hypothetical', 'researcher', 'assistant professor', 'instructor', 'teacher' ),
			array( 'rationally', 'mentally' ),
			array( 'school', 'college', 'conservatory', 'arts school', 'private school', 'military institute' ),
			array( 'agree', 'assent', 'consent', 'comply', 'grant', 'allow', 'come to', 'inherit', 'succeed to', 'take over', 'enter upon', 'attain', 'ascend' ),
			array( 'speeding up', 'stepping up', 'hastening', 'hurrying', 'quickening', 'rushing', 'increase of rate', 'increase of velocity', 'increase in speed' ),
			array( 'emphasize', 'highlight', 'put emphasis on', 'stress', 'draw attention to', 'bring out', 'put the accent on', 'heighten', 'play up', 'make more noticeable' ),
			array( 'emphasis', 'stress', 'beat', 'inflection', 'prominence' ),
			array( 'believe', 'recognize', 'agree to', 'admit', 'acknowledge', 'understand', 'allow', 'agree to', 'say yes', 'consent', 'say you will', 'give a positive response', 'receive', 'take', 'put up with', 'endure', 'tolerate', 'bow to', 'take', 'resign yourself to', 'take on', 'undertake', 'acknowledge', 'assume' ),
			array( 'suitability', 'adequacy', 'satisfactoriness', 'tolerability' ),
			array( 'satisfactory', 'suitable', 'good enough', 'adequate', 'up to standard', 'tolerable', 'okay', 'all right', 'usual', 'standard', 'conventional', 'customary', 'normal', 'within acceptable limits', 'pleasing', 'welcome', 'gratifying', 'agreeable', 'enjoyable' ),
			array( 'well enough', 'adequately', 'sufficiently well', 'suitably', 'tolerably', 'passably', 'reasonably' ),
			array( 'conventional', 'established', 'customary', 'acknowledged', 'usual', 'traditional', 'time-honored', 'received', 'expected', 'normal', 'standard' ),
			array( 'tolerant', 'compliant', 'patient', 'long-suffering', 'uncomplaining', 'accommodating' ),
			array( 'right of entry', 'admission', 'right to use', 'admittance', 'entrÐ¹e', 'contact', 'way in', 'entrance', 'entry', 'approach', 'gate', 'door', 'get into', 'retrieve', 'open', 'log on', 'read', 'edit', 'gain access to' ),
			array( 'convenience', 'ease of access', 'ease of understanding', 'user-friendliness', 'ease of use', 'openness' ),
			array( 'easy to get to', 'nearby', 'available', 'reachable', 'easily reached', 'handy', 'to hand', 'open', 'within reach', 'manageable', 'comprehensible', 'understandable', 'user-friendly', 'easy to use', 'clear', 'straightforward', 'simple', 'approachable', 'affable', 'genial', 'friendly', 'welcoming' ),
			array( 'conveniently', 'handily', 'suitably', 'helpfully', 'usefully', 'clearly', 'simply', 'understandably', 'comprehensibly', 'straightforwardly', 'helpfully' ),
			array( 'attainment', 'succession', 'taking over', 'taking office', 'appointment', 'agreement', 'consent', 'concurrence', 'accord' ),
			array( 'trimmings', 'garnishes', 'garnishing', 'frills', 'side dishes' ),
			array( 'fashion accessory', 'ornament', 'handbag', 'belt', 'scarf', 'gloves', 'accomplice', 'partner', 'partner in crime', 'assistant', 'abettor', 'co-conspirator' ),
			array( 'mishap', 'misfortune', 'calamity', 'catastrophe', 'disaster', 'industrial accident', 'upset', 'mistake', 'crash', 'collision', 'bump', 'smash', 'smash up' ),
			array( 'unintentional', 'unintended', 'inadvertent', 'chance', 'unplanned', 'fortuitous' ),
			array( 'by chance', 'by accident', 'by mistake', 'unintentionally', 'inadvertently', 'fortuitously', 'by coincidence', 'out of the blue' ),
			array( 'approval', 'praise', 'commendation', 'acclamation', 'approbation', 'applause', 'compliments', 'praise', 'sing the praises of', 'give enthusiastic approval to', 'hail', 'commend', 'applaud', 'cheer' ),
			array( 'highly praised', 'much-admired', 'commended', 'celebrated', 'applauded' ),
			array( 'acclaim', 'praise', 'commendation', 'approbation', 'approval', 'applause', 'clapping', 'cheers', 'cheering', 'ovation', 'roar' ),
			array( 'get used to', 'become accustomed', 'accustom yourself', 'adapt', 'adjust', 'familiarize', 'acclimatize' ),
			array( 'adaptation', 'getting used to', 'becoming accustomed', 'adjustment', 'accommodation', 'familiarization' ),
			array( 'get used to', 'become accustomed', 'accustom yourself', 'adapt', 'adjust', 'familiarize yourself' ),
			array( 'tribute', 'honor', 'great compliment', 'rave review', 'award', 'praise' ),
			array( 'helpful', 'willing to help', 'obliging', 'cooperative', 'compliant', 'accepting', 'long-suffering' ),
			array( 'accessory', 'adjunct', 'supplement', 'complement', 'addition', 'auxiliary' ),
			array( 'trimmings', 'garnishes', 'accessories', 'garnishing', 'frills', 'side dishes' ),
			array( 'trimmings', 'garnishes', 'accessories', 'garnishing', 'frills', 'side dishes' ),
			array( 'partner in crime', 'assistant', 'accessory', 'collaborator', 'co-conspirator' ),
			array( 'achievement', 'triumph', 'success', 'deed', 'feat', 'exploit', 'completion', 'execution', 'carrying out', 'finishing', 'realization', 'achievement', 'attainment', 'skill', 'talent', 'ability', 'expertise', 'capability', 'endowment' ),
			array( 'activities', 'actions', 'events', 'happenings', 'goings-on', 'deeds', 'comings and goings', 'undertakings', 'endeavors' ),
			array( 'activities', 'actions', 'events', 'happenings', 'goings-on', 'deeds', 'comings and goings', 'undertakings', 'endeavors' ),
			array( 'agreement', 'harmony', 'concurrence', 'unity', 'treaty', 'agreement', 'settlement', 'pact', 'deal' ),
			array( 'so', 'for that reason', 'therefore', 'hence', 'as a result', 'consequently', 'thus', 'in view of that', 'appropriately', 'suitably', 'correspondingly', 'fittingly' ),
			array( 'approach', 'stop', 'waylay', 'confront', 'buttonhole', 'detain' ),
			array( 'explanation', 'description', 'story', 'report', 'version', 'relation', 'financial credit', 'bank account', 'checking account', 'savings account', 'credit', 'bill', 'tab', 'tally', 'balance' ),
			array( 'answerability', 'responsibility', 'liability' ),
			array( 'answerable', 'responsible', 'liable', 'held responsible', 'blamed' ),
			array( 'financial records', 'the books', 'balance sheet', 'financial statement' ),
			array( 'financial records', 'the books', 'balance sheet', 'financial statement' ),
			array( 'accessories', 'trappings', 'bits and pieces', 'paraphernalia', 'appurtenances', 'trimmings' ),
			array( 'recognize', 'sanction', 'endorse', 'authorize', 'certify', 'certificate' ),
			array( 'official approval', 'official recognition', 'authorization', 'endorsement', 'certification' ),
			array( 'credited', 'attributed', 'qualified', 'ascribed', 'official', 'recognized', 'endorsed', 'certified', 'approved' ),
			array( 'accumulation', 'buildup', 'accrual', 'increase', 'enlargement', 'addition', 'growth', 'mass', 'deposit', 'lump', 'layer', 'bump', 'growth', 'addition' ),
			array( 'accumulation', 'increase', 'buildup', 'accretion', 'addition', 'growth' ),
			array( 'accumulate', 'ensue', 'grow', 'mount up', 'build up', 'amass', 'increase', 'add', 'be credited with', 'go to' ),
			array( 'buildup', 'accretion', 'accrual', 'gathering', 'growth', 'addition', 'increase', 'amassing', 'collection', 'stock', 'store', 'hoard', 'deposit', 'heap' ),
			array( 'collector', 'saver', 'squirrel', 'magpie', 'miser', 'stasher' ),
			array( 'correctness', 'accurateness', 'exactness', 'precision', 'truth', 'truthfulness' ),
			array( 'precise', 'correct', 'exact', 'true', 'truthful', 'perfect' ),
			array( 'correctness', 'accuracy', 'exactness', 'precision', 'truth', 'truthfulness' ),
			array( 'harsh', 'severe', 'snappish', 'angry', 'critical', 'biting' ),
			array( 'plaintiff', 'complainant', 'petitioner', 'appellant', 'litigant' ),
			array( 'sharp', 'cutting', 'bitter', 'caustic', 'acid', 'sour', 'mordant', 'barbed', 'prickly', 'biting', 'critical', 'pointed' ),
			array( 'pain', 'dull pain', 'throbbing', 'aching', 'twinge', 'headache', 'stomach-ache', 'backache', 'hurt', 'throb', 'be painful', 'sting', 'smart', 'cause discomfort', 'be killing' ),
			array( 'attainable', 'realizable', 'possible', 'reachable', 'doable', 'practicable', 'feasible', 'viable', 'realistic' ),
			array( 'attain', 'get', 'realize', 'accomplish', 'reach', 'do', 'complete', 'pull off' ),
			array( 'high flier', 'go-getter', 'doer', 'hard worker', 'self-starter', 'success' ),
			array( 'painful', 'sore', 'tender', 'throbbing', 'sensitive', 'hurting', 'ache', 'pain', 'painful sensation', 'painful feeling', 'throbbing', 'throb', 'twinge', 'sore spot', 'longing', 'desire', 'sadness', 'yearning', 'pining', 'itch' ),
			array( 'acid', 'sharp', 'tart', 'sour', 'bitter' ),
			array( 'sharpness', 'tartness', 'sourness', 'bitterness' ),
			array( 'sharply', 'cuttingly', 'tartly', 'sourly', 'acerbically', 'bitterly', 'bitingly', 'caustically' ),
			array( 'recognition', 'acceptance', 'admission', 'confession', 'appreciation', 'tribute', 'response', 'reply', 'reaction', 'answer', 'greeting', 'salutation', 'nod', 'wave' ),
			array( 'peak', 'height', 'summit', 'top', 'zenith', 'pinnacle', 'culmination' ),
			array( 'spots', 'bad skin', 'a skin condition' ),
			array( 'explain', 'run by', 'accustom', 'notify', 'tell' ),
			array( 'associates', 'connections', 'links', 'friends', 'contacts' ),
			array( 'familiar', 'up to date', 'au fait', 'aware' ),
			array( 'agree', 'comply', 'accept', 'consent', 'assent', 'give in', 'submit', 'go along with', 'yield', 'concede', 'concur' ),
			array( 'agreement', 'consent', 'compliance', 'submission', 'acceptance', 'assent' ),
			array( 'agreeable', 'compliant', 'yielding', 'accepting', 'submissive', 'consenting' ),
			array( 'obtain', 'get', 'get hold of', 'get your hands on', 'gain', 'attain', 'buy', 'purchase', 'make a purchase of', 'come by' ),
			array( 'gaining', 'attainment', 'achievement', 'getting hold of', 'purchase' ),
			array( 'greedy', 'avid', 'covetous', 'grasping', 'avaricious', 'materialistic' ),
			array( 'greed', 'hoarding', 'avarice', 'covetousness', 'materialism' ),
			array( 'find not guilty', 'clear', 'set free', 'free', 'release', 'exonerate' ),
			array( 'release', 'discharge', 'freeing', 'exoneration' ),
			array( 'land', 'home', 'house', 'estate' ),
			array( 'pungent', 'bitter', 'choking', 'sharp', 'unpleasant', 'harsh', 'sharp', 'cutting', 'caustic', 'bitter', 'vitriolic', 'mordant', 'trenchant', 'sour', 'tart', 'sharp', 'biting', 'acerbic' ),
			array( 'bitter', 'spiteful', 'rancorous', 'discordant', 'hostile', 'unfriendly', 'harsh' ),
			array( 'bitterness', 'spite', 'rancor', 'animosity', 'hostility', 'unfriendliness', 'ill will', 'bad blood', 'bad feeling' ),
			array( 'tumbler', 'trapeze artist', 'circus performer', 'gymnast', 'entertainer' ),
			array( 'gymnastic', 'athletic', 'lithe', 'energetic', 'supple', 'flexible' ),
			array( 'crossways', 'crosswise', 'transversely', 'athwart', 'diagonally', 'from corner to corner' ),
			array( 'do something', 'take action', 'take steps', 'proceed', 'be active', 'perform', 'operate', 'work', 'discharge duty', 'accomplish', 'action', 'deed', 'doing', 'undertaking', 'exploit', 'performance', 'achievement', 'accomplishment', 'feat', 'work', 'take effect', 'function', 'produce a result', 'produce an effect', 'do its stuff', 'perform', 'act out', 'be in', 'appear in', 'play in', 'play a part', 'play a role', 'behave', 'conduct yourself', 'comport yourself', 'acquit yourself', 'perform', 'pretense', 'show', 'sham', 'put-on', 'con', 'feint', 'pretend', 'put on an act', 'put it on', 'play', 'fake', 'feign', 'play-act', 'ham it up', 'affect', 'law', 'piece of legislation', 'statute', 'decree', 'enactment', 'measure', 'bill' ),
			array( 'drama', 'the theater', 'performing arts', 'performing', 'the stage', 'temporary', 'substitute', 'stand-in', 'interim' ),
			array( 'act', 'deed', 'exploit', 'achievement', 'accomplishment', 'feat', 'stroke', 'battle', 'fighting', 'combat', 'conflict', 'engagement', 'encounter', 'clash', 'skirmish', 'dogfight', 'raid', 'war', 'warfare', 'suit', 'prosecution', 'lawsuit', 'proceedings', 'case', 'court case', 'charge' ),
			array( 'events', 'proceedings', 'measures', 'trial', 'procedures', 'dealings' ),
			array( 'events', 'proceedings', 'measures', 'trial', 'procedures', 'dealings' ),
			array( 'start', 'commencement', 'opening', 'launch', 'foundation', 'establishment', 'creation', 'inauguration', 'initiation', 'introduction', 'instigation' ),
			array( 'lively', 'vigorous', 'energetic', 'full of life', 'on the go', 'full of zip', 'dynamic', 'in force', 'functioning', 'effective', 'in action', 'operating', 'operational', 'functional', 'working', 'working', 'practicing', 'involved', 'committed', 'enthusiastic', 'keen' ),
			array( 'campaigner', 'protester', 'objector', 'militant', 'advocate', 'forward looking', 'advanced', 'futuristic', 'modern', 'avant-garde', 'innovative', 'highly developed', 'ahead of its time', 'liberal', 'open-minded', 'broadminded', 'enlightened', 'radical', 'unbiased', 'unprejudiced' ),
			array( 'behavior', 'actions', 'tricks' ),
			array( 'action', 'movement', 'motion', 'bustle', 'commotion', 'doings', 'goings-on', 'pursuit', 'interest', 'hobby', 'occupation', 'leisure interest', 'endeavor', 'pastime' ),
			array( 'performer', 'artist', 'artiste', 'player' ),
			array( 'real', 'definite', 'genuine', 'authentic', 'concrete', 'tangible' ),
			array( 'realism', 'reality', 'authenticity', 'truth', 'certainty', 'veracity' ),
			array( 'in fact', 'really', 'in point of fact', 'in reality', 'truly', 'essentially' ),
			array( 'activate', 'put into action', 'motivate', 'set in motion', 'trigger', 'start', 'get going' ),
			array( 'insight', 'perception', 'perspicacity', 'acuteness', 'keenness', 'sharpness' ),
			array( 'insight', 'sharpness', 'shrewdness', 'penetration', 'good judgment', 'intelligence', 'wisdom', 'expertise' ),
			array( 'very', 'intensely', 'highly', 'deeply', 'extremely', 'terribly', 'severely' ),
			array( 'intensity', 'severity', 'extremity', 'depth', 'height', 'sharpness' ),
			array( 'advertisement', 'public notice', 'commercial', 'trailer', 'poster', 'flyer', 'announcement', 'personal ad', 'classified ad', 'want ad', 'billboard' ),
			array( 'saying', 'saw', 'proverb', 'maxim', 'axiom', 'motto', 'wise saying' ),
			array( 'obstinate', 'obdurate', 'unyielding', 'unbending', 'inflexible', 'unwavering', 'immovable', 'resolute', 'steadfast', 'stubborn', 'fixed' ),
			array( 'obstinately', 'steadfastly', 'stubbornly', 'fixedly', 'unwaveringly', 'obdurately', 'inflexibly', 'unalterably', 'unyieldingly', 'unbendingly', 'immovably' ),
			array( 'flexibility', 'adaptableness', 'malleability', 'compliance' ),
			array( 'flexible', 'malleable', 'pliable', 'adjustable', 'compliant' ),
			array( 'put in', 'insert', 'adjoin', 'append', 'affix', 'attach', 'include', 'add up', 'add together', 'tote up', 'total', 'combine', 'tally', 'tally up', 'count up', 'count', 'enhance', 'complement', 'improve', 'augment', 'increase', 'supplement', 'swell', 'enlarge', 'intensify' ),
			array( 'additional', 'extra', 'supplementary', 'further', 'new', 'other' ),
			array( 'addition', 'supplement', 'appendix', 'postscript', 'P.S.', 'codicil', 'add-on', 'rider' ),
			array( 'habit', 'compulsion', 'dependence', 'need', 'obsession', 'craving', 'infatuation' ),
			array( 'addition', 'adding up', 'adding together', 'totaling', 'toting up', 'calculation', 'count', 'accumulation', 'tallying', 'tally' ),
			array( 'adding', 'adding up', 'adding together', 'totaling', 'toting up', 'calculation', 'count', 'accumulation', 'tallying', 'tally', 'supplement', 'add-on', 'appendage', 'addendum', 'adjunct', 'extra', 'additive', 'surcharge' ),
			array( 'extra', 'added', 'supplementary', 'other', 'further', 'bonus' ),
			array( 'accompaniments', 'trappings', 'added extras', 'embellishments', 'add-ons', 'superfluities', 'trimmings', 'flourishes' ),
			array( 'preservative', 'stabilizer', 'chemical addition' ),
			array( 'speak to', 'lecture to', 'talk to', 'tackle', 'deal with', 'take in hand', 'attend to', 'concentrate on', 'focus on', 'take up', 'adopt', 'direct', 'forward', 'deliver', 'dispatch', 'refer' ),
			array( 'receiver', 'beneficiary', 'heir' ),
			array( 'spectators', 'viewers', 'listeners' ),
			array( 'spectators', 'viewers', 'listeners' ),
			array( 'skillful', 'skilled', 'expert', 'proficient', 'adroit', 'practiced', 'clever' ),
			array( 'expertly', 'proficiently', 'skillfully', 'adroitly', 'cleverly', 'smoothly', 'dexterously', 'accurately', 'well' ),
			array( 'expertise', 'proficiency', 'skill', 'adroitness', 'aptitude', 'dexterity' ),
			array( 'sufficiency', 'satisfactoriness', 'competence', 'capability' ),
			array( 'sufficient', 'ample', 'enough', 'plenty', 'passable', 'satisfactory', 'tolerable', 'acceptable' ),
			array( 'devotion', 'obedience', 'observance', 'loyalty', 'faithfulness' ),
			array( 'supporter', 'believer', 'devotee', 'advocate', 'fanatic', 'enthusiast', 'aficionado', 'aficionada', 'member', 'zealot' ),
			array( 'bond', 'union', 'sticking together', 'sticking to', 'hold', 'grip', 'devotion', 'linkage' ),
			array( 'glue', 'paste', 'gum', 'cement', 'epoxy resin', 'bonding agent' ),
			array( 'farewell', 'goodbye', 'send-off', 'leave-taking', 'commencement address' ),
			array( 'closely', 'next to', 'nearby', 'contiguously', 'to the side of', 'next door to', 'alongside' ),
			array( 'connect', 'link up', 'attach', 'be next to', 'affix', 'be close to', 'border' ),
			array( 'next-door', 'adjacent', 'neighboring', 'next', 'bordering' ),
			array( 'suspend', 'defer', 'delay', 'postpone', 'put off', 'call a halt to', 'stop', 'end', 'come to a close', 'interrupt', 'break off' ),
			array( 'suspension', 'postponement', 'deferment', 'recess', 'break', 'interruption', 'delay', 'closure' ),
			array( 'judge', 'find', 'regard as being', 'deem', 'consider', 'decide', 'believe to be', 'pronounce', 'rule', 'announce', 'declare', 'adjudicate' ),
			array( 'give a ruling', 'arbitrate', 'sit in judgment', 'pass judgment', 'deliver judgment', 'referee', 'umpire', 'judge' ),
			array( 'arbitration', 'negotiation', 'mediation', 'settlement', 'intercession' ),
			array( 'judge', 'arbitrator', 'referee', 'umpire' ),
			array( 'jury', 'judges', 'panel of adjudicators', 'panel of judges', 'board of adjudicators', 'board of judges' ),
			array( 'jury', 'judges', 'panel of adjudicators', 'panel of judges', 'board of adjudicators', 'board of judges' ),
			array( 'addition', 'attachment', 'add-on', 'appendage', 'accessory', 'extra', 'optional extra' ),
			array( 'regulate', 'alter', 'fiddle with', 'correct', 'fine-tune', 'change', 'bend', 'amend', 'modify', 'tweak' ),
			array( 'adaptable', 'modifiable', 'changeable', 'variable', 'regulating', 'amendable', 'bendable', 'flexible' ),
			array( 'control', 'run', 'manage', 'direct', 'rule', 'govern' ),
			array( 'management', 'direction', 'running', 'government', 'supervision', 'organization', 'admin', 'paperwork', 'dispensation', 'meting out', 'giving out', 'handing out', 'dealing out', 'doling out', 'processing', 'government', 'presidency', 'executive', 'management', 'organization' ),
			array( 'managerially', 'directorially', 'governmentally', 'organizationally' ),
			array( 'manager', 'superintendent', 'commissioner', 'overseer', 'officer', 'bureaucrat', 'supervisor', 'proprietor', 'governor', 'official', 'executive' ),
			array( 'estimably', 'commendably', 'very well', 'splendidly', 'worthily', 'marvelously', 'excellently' ),
			array( 'well-liked', 'accepted', 'trendy', 'in style', 'all the rage', 'fashionable' ),
			array( 'fan', 'devotee', 'follower', 'lover', 'aficionado', 'aficionada', 'enthusiast' ),
			array( 'appreciative', 'approving', 'complimentary', 'flattering', 'favorable', 'deferential', 'positive', 'sympathetic', 'pleased' ),
			array( 'favorably', 'approvingly', 'appreciatively', 'flatteringly', 'deferentially', 'positively' ),
			array( 'acceptability', 'tolerability', 'permissibility' ),
			array( 'allowable', 'permissible', 'acceptable', 'tolerable' ),
			array( 'confess', 'come clean', 'make a clean breast', 'acknowledge', 'own up', 'disclose', 'divulge', 'declare', 'state', 'let in', 'allow in', 'give leave to enter', 'give access', 'permit', 'let pass', 'welcome' ),
			array( 'admission', 'entry', 'access', 'right of entry', 'entrance', 'permission' ),
			array( 'reprove', 'caution', 'warn about', 'give a warning', 'reprimand', 'rebuke', 'reproach', 'tell off', 'scold', 'chide' ),
			array( 'telling off', 'talking to', 'reproach', 'rebuke', 'caution', 'reprimand', 'dressing-down' ),
			array( 'caution', 'warning', 'reprimand', 'rebuke', 'reproach', 'scolding' ),
			array( 'warning', 'deterrent', 'reproving', 'advisory', 'instructive' ),
			array( 'commotion', 'excitement', 'argument', 'bother', 'upheaval', 'to-do', 'protest', 'ruckus', 'objection', 'bustle', 'activity' ),
			array( 'teenage years', 'teens', 'youth', 'puberty' ),
			array( 'teenager', 'young person', 'youth', 'youngster', 'juvenile', 'minor', 'pubescent', 'teenage', 'young', 'youthful', 'juvenile', 'teen', 'pubescent', 'pubertal' ),
			array( 'young people', 'youth', 'teenagers', 'youngsters' ),
			array( 'take on', 'accept', 'assume', 'approve', 'take up', 'agree to', 'espouse', 'implement', 'embrace', 'take on board' ),
			array( 'sweet', 'gorgeous', 'delightful', 'lovable', 'delectable', 'endearing', 'cute', 'charming', 'attractive', 'lovely' ),
			array( 'sweetly', 'beautifully', 'wonderfully', 'delightfully', 'delectably', 'endearingly', 'charmingly', 'attractively', 'gorgeously' ),
			array( 'love', 'esteem', 'high regard', 'respect', 'admiration', 'adulation', 'worship', 'worship', 'reverence', 'idolization', 'glorification', 'exaltation', 'veneration', 'honoring', 'devotion' ),
			array( 'love', 'worship', 'esteem', 'respect', 'admire', 'be mad about', 'be passionate about', 'be stuck on', 'be crazy about', 'worship', 'revere', 'idolize', 'glorify', 'exalt', 'venerate', 'honor' ),
			array( 'respected', 'acclaimed', 'venerated', 'highly thought of', 'recognized', 'established' ),
			array( 'affectionate', 'loving', 'admiring', 'indulgent', 'tender', 'warm', 'romantic', 'doting' ),
			array( 'decorate', 'embellish', 'ornament', 'beautify', 'prettify', 'gild', 'titivate', 'garnish', 'enhance' ),
			array( 'decorated', 'festooned', 'ornamented', 'decked', 'decked out', 'bejeweled', 'garlanded' ),
			array( 'decoration', 'embellishment', 'ornamentation', 'beautification', 'prettification', 'gilding', 'trimming', 'titivation', 'frill', 'enhancement' ),
			array( 'drifting', 'floating', 'loose', 'free', 'aimless', 'wandering', 'drifting', 'at a loose end', 'lost', 'purposeless', 'floating', 'directionless', 'in limbo' ),
			array( 'skillful', 'nimble', 'practiced', 'able', 'clever', 'dexterous', 'adept', 'competent', 'accomplished', 'skilled' ),
			array( 'skillfully', 'nimbly', 'ably', 'dexterously', 'competently', 'adeptly', 'capably', 'cleverly' ),
			array( 'skillfulness', 'nimbleness', 'ability', 'dexterity' ),
			array( 'flatter', 'put on a pedestal', 'elevate', 'praise', 'adore', 'lionize', 'worship', 'revere' ),
			array( 'adoration', 'praise', 'worship', 'hero worship', 'exaltation', 'respect', 'admiration', 'reverence', 'idolization', 'glorification' ),
			array( 'contaminate', 'taint', 'make impure', 'spoil', 'pollute', 'infect', 'ruin' ),
			array( 'contaminated', 'impure', 'mixed', 'tainted', 'polluted', 'dirty', 'infected', 'poisoned', 'unclean' ),
			array( 'ruining', 'defilement', 'ruination', 'corruption', 'tarnishing', 'sullying' ),
			array( 'disloyal', 'false', 'untrue', 'two-timing', 'treacherous', 'traitorous', 'faithless', 'double-crossing' ),
			array( 'infidelity', 'disloyalty', 'falseness', 'treachery', 'betrayal', 'deceitfulness', 'faithlessness' ),
			array( 'maturity', 'parenthood', 'middle age', 'old age', 'later life' ),
			array( 'go forward', 'move forward', 'move ahead', 'press forward', 'move on', 'proceed', 'press on', 'progress', 'go ahead', 'evolve', 'improve', 'develop', 'enhance', 'take forward', 'increase', 'expand', 'spread', 'progress', 'further', 'build up', 'loan', 'early payment', 'fee', 'money up front', 'development', 'improvement', 'spread', 'progress', 'expansion', 'encroachment', 'innovation', 'enhancement', 'increase', 'forward movement', 'progress', 'momentum', 'onslaught' ),
			array( 'higher', 'superior', 'highly developed', 'sophisticated', 'complex', 'difficult', 'later', 'far along', 'well along', 'far ahead', 'well ahead', 'future', 'progressive', 'forward-thinking', 'unconventional', 'cutting edge', 'innovative', 'vanguard', 'forward-looking' ),
			array( 'progression', 'progress', 'development', 'improvement', 'spread', 'expansion', 'encroachment', 'innovation', 'increase', 'evolution' ),
			array( 'benefit', 'gain', 'lead', 'plus', 'pro', 'improvement', 'help' ),
			array( 'privileged', 'lucky', 'fortunate', 'honored' ),
			array( 'favorably', 'profitably', 'usefully', 'gainfully', 'valuably', 'helpfully', 'beneficially', 'expediently', 'strategically' ),
			array( 'compensation', 'reward', 'recompense', 'return' ),
			array( 'compensation', 'reward', 'recompense', 'return' ),
			array( 'arrival', 'start', 'beginning', 'coming on', 'dawn', 'initiation', 'introduction' ),
			array( 'explorer', 'traveler', 'voyager', 'buccaneer', 'swashbuckler', 'fortune-hunter', 'entrepreneur', 'investor', 'speculator', 'trailblazer', 'pioneer', 'opportunist' ),
			array( 'risk-taking', 'carefree', 'daring', 'thrill-seeking', 'exciting', 'looking for excitement', 'venturesome' ),
			array( 'daringly', 'boldly', 'audaciously', 'bravely', 'courageously' ),
			array( 'opponent', 'challenger', 'rival', 'enemy', 'foe', 'antagonist', 'opposition' ),
			array( 'hardship', 'difficulty', 'danger', 'misfortune', 'harsh conditions', 'hard times' ),
			array( 'promote', 'publicize', 'market', 'present', 'push', 'puff', 'announce', 'broadcast', 'make known', 'make public', 'publicize', 'spread around', 'shout from the rooftops', 'shout out' ),
			array( 'advocate', 'promoter', 'supporter' ),
			array( 'publicity', 'promotion', 'marketing' ),
			array( 'recommendation', 'counsel', 'suggestion', 'guidance', 'opinion', 'information', 'guidance', 'instruction', 'assistance' ),
			array( 'wisdom', 'prudence', 'sense', 'desirability', 'suitability' ),
			array( 'counsel', 'give advice', 'direct', 'recommend', 'give an opinion', 'warn', 'inform', 'tell', 'let know', 'make aware', 'notify' ),
			array( 'deliberately', 'carefully', 'purposefully', 'on purpose', 'with intent', 'intentionally' ),
			array( 'support', 'encouragement', 'backing', 'sponsorship', 'promotion' ),
			array( 'auspices', 'sponsorship', 'guidance', 'protection', 'support', 'tutelage' ),
			array( 'ventilate', 'air', 'let breathe', 'expose', 'freshen' ),
			array( 'ventilation', 'airing', 'exposure to air', 'drying', 'freshening' ),
			array( 'stunts', 'aerial tricks', 'turns in the air' ),
			array( 'exercises', 'calisthenics', 'work-out' ),
			array( 'sleek', 'smooth', 'slick' ),
			array( 'spray', 'spray can', 'vaporizer' ),
			array( 'far afield', 'in the distance', 'far away', 'far and wide', 'far-off', 'far' ),
			array( 'friendliness', 'sociability', 'cordiality', 'joviality' ),
			array( 'genial', 'pleasant', 'friendly', 'sociable', 'easygoing', 'jovial' ),
			array( 'genially', 'pleasantly', 'jovially', 'in a friendly way', 'warmly', 'cordially' ),
			array( 'matter', 'issue', 'concern', 'business', 'situation', 'event', 'thing' ),
			array( 'relationships', 'dealings', 'associations', 'contact', 'interaction' ),
			array( 'relationships', 'dealings', 'associations', 'contact', 'interaction' ),
			array( 'have an effect on', 'influence', 'involve', 'shape', 'concern', 'change', 'impinge on', 'distress', 'touch', 'disturb', 'move', 'upset', 'have emotional impact', 'assume', 'pretend to have', 'put on', 'imitate', 'fake' ),
			array( 'habit', 'mannerism', 'way', 'quirk', 'showing off', 'pretentiousness', 'exaggeration', 'pretension', 'artifice' ),
			array( 'exaggerated', 'pretentious', 'precious', 'artificial', 'unnatural' ),
			array( 'moving', 'touching', 'upsetting', 'distressing', 'disturbing', 'heartwarming' ),
			array( 'loving', 'demonstrative', 'warm', 'friendly', 'kind' ),
			array( 'warmly', 'lovingly', 'tenderly', 'kindly' ),
			array( 'sworn statement', 'official declaration', 'affirmation', 'confirmation', 'proclamation' ),
			array( 'associate', 'partner', 'colleague', 'member', 'link', 'connect', 'join', 'associate', 'belong to' ),
			array( 'allied', 'united', 'joined', 'associated' ),
			array( 'association', 'relationship', 'connection', 'attachment', 'membership', 'link' ),
			array( 'similarity', 'resemblance', 'likeness', 'empathy', 'sympathy', 'fellow feeling', 'attraction', 'kinship' ),
			array( 'assert', 'insist', 'confirm', 'avow', 'state', 'announce', 'establish', 'verify', 'pronounce', 'acknowledge', 'support', 'uphold', 'encourage', 'sustain' ),
			array( 'confirmation', 'assertion', 'pronouncement', 'avowal', 'declaration', 'announcement', 'statement', 'verification', 'support', 'upholding', 'encouragement' ),
			array( 'assenting', 'positive', 'confirmatory' ),
			array( 'positively', 'in a positive way', 'with assent' ),
			array( 'avowed', 'stated', 'confirmed', 'declared', 'acknowledged' ),
			array( 'attach', 'fix', 'fasten', 'stick', 'pin', 'glue' ),
			array( 'trouble', 'bother', 'make miserable', 'badly affect', 'cause problems', 'worry', 'upset', 'distress' ),
			array( 'suffering', 'difficulty', 'burden', 'problem', 'hardship', 'pain', 'trouble', 'misery', 'misfortune' ),
			array( 'wealth', 'riches', 'prosperity', 'material comfort', 'privileged circumstances' ),
			array( 'richly', 'wealthily', 'prosperously', 'comfortably' ),
			array( 'have enough money', 'pay for', 'have the funds for', 'manage to pay for', 'find the money for', 'come up with the money for', 'meet the expense of', 'give', 'offer', 'present', 'allow', 'provide' ),
			array( 'reasonably priced', 'reasonable', 'within your means', 'inexpensive' ),
			array( 'forest', 'reforest', 'tree-plant', 'plant' ),
			array( 'scuffle', 'fight', 'brawl', 'broil', 'disturbance', 'commotion' ),
			array( 'offend', 'cause offense', 'insult', 'upset', 'outrage', 'slight', 'disrespect', 'insult', 'injury', 'slur', 'slight', 'disrespect' ),
			array( 'insulted', 'injured', 'slighted', 'disrespected', 'upset' ),
			array( 'devotee', 'enthusiast', 'adherent', 'fanatic', 'fan', 'addict', 'admirer' ),
			array( 'burning', 'in flames', 'on fire', 'aflame', 'ablaze', 'fired up', 'enthusiastic', 'passionate', 'excited', 'aflame', 'eager' ),
			array( 'on fire', 'burning', 'in flames', 'afire', 'ablaze', 'ablaze', 'enthusiastic', 'passionate', 'fired', 'fired up', 'afire', 'excited' ),
			array( 'floating', 'buoyant' ),
			array( 'agitated', 'excited', 'trembling', 'aquiver', 'keyed up', 'nervous' ),
			array( 'happening', 'going on', 'occurring', 'taking place', 'up', 'in the works', 'stirring' ),
			array( 'aforementioned', 'abovementioned' ),
			array( 'frightened', 'scared', 'fearful', 'terrified', 'anxious', 'troubled' ),
			array( 'anew', 'again', 'once again', 'once more', 'over', 'another time' ),
			array( 'behind', 'astern', 'at the back', 'at the rear', 'in back' ),
			array( 'following', 'subsequent to', 'behind', 'later than', 'past', 'gone', 'once', 'when', 'as soon as', 'considering', 'taking into account', 'with', 'bearing in mind', 'taking into consideration', 'afterward', 'subsequently', 'later', 'next', 'in the manner of', 'in imitation of', 'similar to', 'like', 'in the same way as' ),
			array( 'result', 'consequences', 'outcome', 'upshot', 'repercussion' ),
			array( 'result', 'consequences', 'outcome', 'upshot', 'repercussion' ),
			array( 'warm feeling', 'warmth', 'glow', 'serenity', 'exhilaration', 'feel-good factor' ),
			array( 'next world', 'life after death', 'eternal life', 'spirit world', 'the hereafter' ),
			array( 'result', 'consequences', 'outcome', 'upshot', 'repercussion', 'after effects' ),
			array( 'day', 'daylight', 'hours of daylight', 'morning' ),
			array( 'fragrance', 'perfume', 'toilet water', 'scent' ),
			array( 'trace', 'hint', 'smack', 'relish', 'savor' ),
			array( 'late addition', 'addition', 'postscript', 'extra', 'addendum', 'reflection' ),
			array( 'later', 'after that', 'subsequently', 'then', 'next' ),
			array( 'once more', 'another time', 'yet again', 'over', 'over again', 'all over again', 'for a second time' ),
			array( 'next to', 'alongside', 'beside', 'touching', 'adjacent to', 'aligned with', 'in opposition to', 'not in favor of', 'anti', 'hostile to', 'critical of', 'opposed to', 'versus', 'in contradiction of', 'contrary to', 'counter to', 'in contrast to' ),
			array( 'astonished', 'amazed', 'open-mouthed' ),
			array( 'era', 'period', 'time', 'times', 'epoch', 'grow old', 'become old', 'mature', 'get older' ),
			array( 'timeless', 'eternal', 'unchanging', 'classic', 'everlasting', 'perpetual' ),
			array( 'organization', 'group', 'society', 'charity', 'outfit', 'bureau', 'activity', 'action', 'work', 'intervention', 'help' ),
			array( 'exaggerate', 'elaborate', 'overdo', 'enhance', 'enlarge', 'increase' ),
			array( 'exaggeration', 'elaboration', 'enhancement', 'enlargement', 'overdoing' ),
			array( 'make worse', 'worsen', 'exacerbate', 'exaggerate', 'heighten', 'intensify', 'magnify', 'fan the flames of', 'annoy', 'irritate', 'exasperate', 'provoke', 'make angry', 'pester', 'get on your nerves', 'frustrate', 'ignite' ),
			array( 'provoked', 'motivated', 'forced', 'goaded', 'annoyed', 'irritated' ),
			array( 'infuriating', 'irritating', 'annoying', 'frustrating', 'maddening' ),
			array( 'annoyance', 'irritation', 'exasperation', 'provocation', 'stress', 'pestering', 'hassle', 'frustration' ),
			array( 'collective', 'total', 'combined', 'cumulative', 'amassed', 'summative', 'comprehensive', 'total', 'collection', 'mass', 'entire sum', 'whole', 'combination', 'combine', 'amass', 'gather together', 'collect', 'accumulate', 'sum up', 'total' ),
			array( 'violence', 'hostility', 'anger', 'violent behavior', 'belligerence', 'antagonism', 'attack', 'assault', 'invasion', 'injury', 'onslaught', 'offensive', 'raid' ),
			array( 'forcefully', 'insistently', 'assertively', 'uncompromisingly', 'violently', 'in a hostile way', 'belligerently', 'antagonistically', 'destructively' ),
			array( 'fierceness', 'ferociousness', 'violence', 'belligerence', 'forcefulness', 'assertiveness', 'antagonism', 'hostility' ),
			array( 'attacker', 'invader', 'assailant', 'provoker', 'antagonist' ),
			array( 'hurt', 'afflict', 'pain', 'distress', 'upset', 'sadden', 'depress' ),
			array( 'hurt', 'angry', 'upset', 'distressed', 'put out', 'pained', 'wounded', 'hard done by', 'injured', 'wronged', 'mistreated', 'persecuted', 'maltreated', 'victimized', 'offended', 'ill-treated' ),
			array( 'horrified', 'amazed', 'shocked', 'horror-struck', 'astonished', 'stunned', 'astounded', 'appalled' ),
			array( 'nimble', 'supple', 'lithe', 'lively', 'sprightly', 'alert', 'responsive', 'swift', 'active' ),
			array( 'nimbly', 'quickly', 'swiftly', 'neatly', 'smoothly' ),
			array( 'nimbleness', 'suppleness', 'quickness', 'dexterity', 'liveliness', 'alertness' ),
			array( 'disturb', 'stir up', 'trouble', 'excite', 'disquiet', 'rouse', 'work up', 'disconcert', 'stir', 'whisk', 'toss around', 'shake up', 'disturb', 'mix up', 'move around', 'campaign', 'stir up opinion', 'protest', 'advocate', 'demonstrate', 'raise a fuss' ),
			array( 'nervous', 'restless', 'troubled', 'disturbed', 'uptight', 'disconcerted', 'frantic', 'tense', 'stressed', 'distressed' ),
			array( 'campaigning', 'demonstration', 'protest', 'confrontation', 'stir', 'disturbance', 'shakeup', 'anxiety', 'worry', 'nervousness', 'tension', 'distress' ),
			array( 'campaigner', 'protester', 'dissenter', 'activist' ),
			array( 'glowing', 'shining', 'radiant', 'rosy', 'warm', 'bright', 'afire', 'burning', 'aflame', 'incandescent' ),
			array( 'doubter', 'disbeliever', 'atheist', 'nonbeliever' ),
			array( 'non-belief', 'skepticism', 'incredulity', 'atheism' ),
			array( 'before', 'previously', 'back', 'past', 'since', 'in the past' ),
			array( 'eager', 'excited', 'impatient', 'keen', 'avid', 'interested', 'enthusiastic', 'curious' ),
			array( 'worry', 'struggle', 'strive', 'vacillate', 'be anxious', 'wrestle', 'suffer', 'torture yourself', 'torment yourself', 'dwell on' ),
			array( 'tormented', 'suffering', 'tortured', 'painful', 'distressed', 'grief-stricken', 'sorrowful', 'angst-ridden' ),
			array( 'excruciating', 'unbearable', 'painful', 'distressing', 'worrying', 'heartbreaking' ),
			array( 'excruciatingly', 'unbearably', 'painfully', 'distressingly', 'worryingly', 'heartbreakingly' ),
			array( 'have the same opinion', 'concur', 'be in agreement', 'see eye to eye', 'be of the same mind', 'be of the same opinion', 'consent', 'say yes', 'fall in with', 'assent', 'acquiesce', 'accede', 'grant', 'permit', 'allow', 'go along with', 'get along with', 'reach agreement', 'come to an agreement', 'come to an understanding', 'settle', 'reach a decision', 'approve', 'decide', 'correspond', 'match', 'be the same', 'tie in', 'harmonize', 'be consistent with' ),
			array( 'pleasant', 'pleasing', 'pleasurable', 'enjoyable', 'delightful', 'satisfying', 'to your liking', 'good', 'comfortable', 'acceptable', 'suitable', 'friendly', 'affable', 'pleasant', 'courteous', 'delightful', 'amenable', 'willing', 'in accord', 'compliant' ),
			array( 'pleasantly', 'enjoyably', 'delightfully', 'pleasingly', 'pleasurably', 'comfortably' ),
			array( 'decided', 'settled', 'arranged', 'approved', 'fixed', 'granted', 'established', 'contracted' ),
			array( 'approving', 'in agreement', 'in accord', 'supportive', 'well-disposed', 'in favor', 'like-minded' ),
			array( 'accord', 'concord', 'conformity', 'harmony', 'union', 'concurrence', 'contract', 'arrangement', 'covenant', 'treaty', 'promise', 'pact', 'settlement', 'bargain', 'understanding', 'deal' ),
			array( 'farming', 'cultivation', 'crop growing', 'gardening' ),
			array( 'beached', 'ashore', 'stranded', 'stuck', 'grounded', 'high and dry' ),
			array( 'in front', 'to the front', 'to the lead', 'in advance', 'further on', 'to the fore', 'at the forefront', 'forward', 'before', 'into the future', 'in the future', 'to come', 'yet to be', 'early', 'in advance', 'prematurely', 'upfront', 'ahead of time', 'beforehand' ),
			array( 'help', 'assist', 'support', 'abet', 'give support to', 'minister to', 'relieve', 'serve', 'sustain', 'facilitate', 'promote', 'encourage', 'further', 'advance', 'foster', 'bolster', 'assistance', 'help', 'support', 'relief', 'benefits', 'encouragement', 'service', 'utility' ),
			array( 'assistant', 'adviser', 'helper', 'supporter' ),
			array( 'be ill', 'be sick', 'feel unwell', 'suffer', 'be in pain', 'feel pain', 'be weak', 'trouble', 'pain', 'distress', 'be wrong with', 'affect', 'afflict', 'be the matter with', 'bother' ),
			array( 'ill', 'in poor health', 'poorly', 'sick', 'under the weather', 'not a hundred percent', 'below par', 'not at your best' ),
			array( 'illness', 'sickness', 'disease', 'disorder', 'complaint', 'weakness' ),
			array( 'aspire', 'plan', 'intend', 'try', 'mean', 'endeavor', 'want', 'seek', 'set sights on', 'strive for', 'point toward', 'point', 'take aim', 'direct', 'goal', 'purpose', 'intention', 'object', 'objective', 'target', 'ambition', 'wish', 'aspiration' ),
			array( 'meant', 'intended', 'expected', 'designed' ),
			array( 'pointless', 'meaningless', 'useless', 'worthless', 'purposeless', 'directionless', 'uselessness' ),
			array( 'pointlessly', 'uselessly', 'without direction', 'without purpose', 'in a meaningless way' ),
			array( 'lack of purpose', 'lack of direction', 'pointlessness', 'senselessness' ),
			array( 'atmosphere', 'space', 'sky', 'heavens', 'appearance', 'look', 'manner', 'tone', 'flavor', 'impression', 'way of being', 'tune', 'melody', 'song', 'ventilate', 'freshen', 'aerate', 'expose', 'declare', 'express', 'vent', 'make public', 'proclaim', 'reveal', 'publicize', 'spread', 'circulate', 'tell', 'announce', 'broadcast' ),
			array( 'in the air', 'flying', 'on high', 'above ground', 'carried by the wind' ),
			array( 'airstrip', 'landing field', 'airdrome', 'airport' ),
			array( 'idealist', 'dreamer', 'space cadet' ),
			array( 'spaciousness', 'openness', 'freshness', 'lightness', 'lightheartedness', 'breeziness', 'buoyancy', 'animation', 'vivacity', 'cheerfulness', 'casualness' ),
			array( 'ventilation', 'aeration', 'exposure to air', 'drying', 'freshening', 'exposure', 'discussion', 'expression', 'outing', 'trip out', 'excursion', 'a breath of fresh air' ),
			array( 'stuffy', 'close', 'muggy', 'unventilated', 'oppressive', 'heavy', 'stifling' ),
			array( 'aircraft', 'plane', 'jet' ),
			array( 'aircraft', 'plane', 'jet', 'airliner' ),
			array( 'pride', 'narcissism', 'self-importance', 'conceit', 'arrogance', 'egotism' ),
			array( 'pride', 'narcissism', 'self-importance', 'conceit', 'arrogance', 'egotism' ),
			array( 'blimp', 'dirigible', 'zeppelin', 'aircraft' ),
			array( 'airfield', 'landing field', 'runway' ),
			array( 'sealed', 'hermetically sealed', 'hermetic', 'sound', 'strong', 'solid', 'unquestionable', 'unassailable' ),
			array( 'well-ventilated', 'fresh', 'light', 'open', 'spacious', 'roomy', 'lighthearted', 'lively', 'buoyant', 'vivacious', 'blithe' ),
			array( 'passageway', 'gangway', 'walkway' ),
			array( 'partly open', 'half closed' ),
			array( 'eagerness', 'enthusiasm', 'readiness', 'quickness', 'promptness', 'speed', 'swiftness', 'rapidity', 'keenness', 'zeal' ),
			array( 'fear', 'apprehension', 'terror', 'fright', 'panic', 'unease', 'anxiety', 'distress', 'agitation', 'dread', 'frighten', 'terrify', 'panic', 'distress', 'startle', 'scare', 'alarm bell', 'warning', 'bell', 'distress signal', 'siren', 'danger signal', 'alarm clock', 'clock radio', 'radio alarm', 'buzzer' ),
			array( 'frighteningly', 'chillingly', 'distressingly', 'terrifyingly', 'disturbingly' ),
			array( 'pessimistic', 'gloomy', 'panicky', 'exaggerated', 'over the top', 'hysterical', 'pessimist', 'doomster', 'doomsayer' ),
			array( 'unfortunately', 'sadly', 'regrettably' ),
			array( 'millstone', 'shackle', 'encumbrance', 'burden', 'impediment' ),
			array( 'book', 'photograph album', 'folder', 'photo album', 'autograph album', 'stamp album', 'sticker album', 'wedding album', 'baby book', 'scrap book', 'record', 'LP', 'CD', 'tape', 'cassette', 'compilation', 'collection' ),
			array( 'recess', 'niche', 'bay' ),
			array( 'attentively', 'watchfully', 'vigilantly', 'observantly' ),
			array( 'attentiveness', 'watchfulness', 'awareness', 'preparedness', 'vigilance' ),
			array( 'outside', 'outdoor', 'uncovered', 'external' ),
			array( 'assumed name', 'pseudonym', 'pen name', 'nom de plume', 'stage name', 'also known as', 'a.k.a.', 'also called', 'otherwise known as' ),
			array( 'explanation', 'excuse', 'defense', 'reason' ),
			array( 'estrange', 'make unfriendly', 'disaffect', 'set against', 'distance', 'push away', 'separate from', 'isolate', 'keep apart from', 'turn away from', 'turn your back on' ),
			array( 'estranged', 'separated', 'not speaking', 'at odds', 'divided', 'on bad terms' ),
			array( 'estrangement', 'disaffection', 'unfriendliness', 'hostility', 'isolation', 'separation', 'distancing', 'division' ),
			array( 'burning', 'on fire', 'in flames', 'blazing', 'ablaze', 'flaming', 'land', 'perch', 'rest', 'stop', 'settle', 'get off', 'get out of', 'descend', 'dismount' ),
			array( 'maintenance', 'money', 'allowance', 'child support', 'keep', 'child maintenance', 'grant' ),
			array( 'living', 'animate', 'breathing', 'lively', 'energetic', 'busy', 'active', 'full of beans', 'perky', 'vibrant', 'bustling', 'vivacious', 'buzzing', 'animated', 'full of life', 'thriving', 'active', 'flourishing', 'successful', 'blooming', 'booming' ),
			array( 'every one', 'each and every one', 'every', 'every one of', 'every single one', 'every part of', 'the entire', 'the complete', 'the whole', 'altogether', 'completely', 'entirely' ),
			array( 'dispel', 'calm', 'alleviate', 'assuage', 'relieve', 'put to rest' ),
			array( 'loyalty', 'commitment', 'adherence', 'faithfulness', 'duty' ),
			array( 'figurative', 'symbolic' ),
			array( 'metaphorically', 'symbolically' ),
			array( 'parable', 'fable', 'metaphor', 'symbol', 'story', 'tale' ),
			array( 'reaction', 'allergic reaction', 'sensitivity', 'hypersensitivity', 'aversion', 'antipathy' ),
			array( 'mitigation', 'lessening', 'improvement', 'easing' ),
			array( 'passage', 'lane', 'alleyway', 'passageway', 'path', 'pathway' ),
			array( 'passage', 'passageway', 'lane', 'alley', 'path', 'pathway' ),
			array( 'related', 'associated', 'connected', 'linked', 'similar', 'joined', 'united', 'combined', 'amalgamated', 'aligned', 'partnered' ),
			array( 'associates', 'partners', 'buddies', 'cronies', 'followers' ),
			array( 'assign', 'allocate', 'apportion', 'designate', 'give', 'ration' ),
			array( 'share', 'portion', 'part', 'allocation', 'allowance', 'ration' ),
			array( 'chosen', 'selected', 'agreed', 'fixed', 'prearranged' ),
			array( 'finished', 'ended', 'done', 'curtains', 'the end', 'over and done with' ),
			array( 'let', 'permit', 'agree to', 'consent to', 'tolerate', 'allocate', 'set aside', 'make available', 'set a limit' ),
			array( 'allowable', 'permissible', 'permitted', 'acceptable', 'tolerable' ),
			array( 'attraction', 'appeal', 'draw', 'pull', 'magnetism', 'charm', 'glamor', 'fascination', 'charisma' ),
			array( 'appealing', 'attractive', 'tempting', 'interesting', 'fascinating', 'enthralling', 'charming', 'glamorous', 'captivating' ),
			array( 'reference', 'mention', 'hint', 'suggestion', 'insinuation', 'quotation', 'citation' ),
			array( 'directory', 'calendar', 'manual', 'encyclopedia', 'reference book' ),
			array( 'enormous', 'massive', 'huge', 'immense', 'gigantic', 'colossal', 'loud', 'deafening', 'earsplitting', 'omnipotent', 'invincible', 'all-powerful', 'supreme', 'omnipresent', 'great', 'terrible', 'serious', 'loud', 'terrific', 'frightful', 'enormous' ),
			array( 'enormous', 'massive', 'huge', 'immense', 'gigantic', 'colossal', 'loud', 'deafening', 'earsplitting', 'omnipotent', 'invincible', 'all-powerful', 'supreme', 'omnipresent', 'great', 'terrible', 'serious', 'loud', 'terrific', 'frightful', 'enormous' ),
			array( 'approximately', 'roughly', 'about', 'more or less', 'nearly', 'not quite', 'just about', 'virtually', 'practically', 'very nearly' ),
			array( 'up', 'uphill', 'in the air' ),
			array( 'unaccompanied', 'by yourself', 'on your own', 'single-handedly', 'unaided', 'without help', 'only', 'and no-one else', 'lonely', 'lonesome', 'abandoned', 'deserted', 'isolated', 'forlorn', 'solitary' ),
			array( 'the length of', 'down', 'all along', 'next to', 'beside', 'by the side of', 'alongside' ),
			array( 'distant', 'detached', 'unfriendly', 'cold', 'remote', 'unapproachable', 'standoffish', 'proud', 'superior', 'snobbish', 'snooty' ),
			array( 'unfriendliness', 'coldness', 'detachment', 'remoteness', 'superiority', 'reserve' ),
			array( 'by now', 'previously', 'before now' ),
			array( 'change', 'modify', 'adjust', 'vary', 'amend', 'revise', 'rework', 'correct' ),
			array( 'adjustable', 'flexible', 'adaptable', 'movable' ),
			array( 'argument', 'row', 'quarrel', 'disagreement', 'dispute', 'exchange', 'squabble', 'clash', 'difference of opinion' ),
			array( 'distorted', 'tainted', 'changed', 'misrepresented', 'misused', 'untouched' ),
			array( 'changing', 'varying', 'shifting' ),
			array( 'exchange', 'swap', 'interchange', 'rotate', 'every other', 'alternating', 'every second', 'vary', 'swing', 'oscillate', 'alternative', 'substitute', 'different', 'substitute', 'stand-in', 'alternative' ),
			array( 'irregular', 'blinking', 'broken', 'flashing', 'discontinuous', 'sporadic' ),
			array( 'fluctuation', 'vacillation', 'swinging', 'undulation', 'wavering' ),
			array( 'option', 'choice', 'substitute', 'other', 'another', 'substitute', 'unusual', 'different', 'unconventional', 'out of the ordinary', 'marginal', 'unorthodox', 'complementary' ),
			array( 'on the other hand', 'otherwise', 'instead', 'then again' ),
			array( 'though', 'even though', 'even if', 'while' ),
			array( 'height', 'elevation', 'height above sea level' ),
			array( 'unselfishness', 'self-sacrifice', 'humanity', 'selflessness', 'philanthropy' ),
			array( 'unselfish', 'humane', 'selfless', 'philanthropic' ),
			array( 'unselfishly', 'selflessly' ),
			array( 'graduates', 'former students' ),
			array( 'graduates', 'former students', 'old pupils' ),
			array( 'forever', 'for all time', 'for eternity', 'until the end of time', 'for ever and a day', 'at all times', 'all the time', 'constantly', 'continuously', 'permanently', 'continually', 'each time', 'every time' ),
			array( 'mixture', 'mix', 'combination', 'blend', 'amalgamation', 'fusion' ),
			array( 'merge', 'join', 'join together', 'combine', 'unite', 'integrate', 'mingle', 'fuse' ),
			array( 'compound', 'complex', 'merged', 'fused', 'combined', 'combination', 'multiple', 'multipart' ),
			array( 'merger', 'join up', 'union', 'incorporation', 'mixture', 'combination', 'mix', 'blend', 'fusion' ),
			array( 'accumulate', 'collect', 'build up', 'gather together', 'stockpile', 'hoard', 'accrue', 'assemble', 'pile up', 'gather', 'store up' ),
			array( 'collective', 'total', 'combined', 'cumulative', 'summative', 'comprehensive' ),
			array( 'buildup', 'accretion', 'accrual', 'gathering', 'growth', 'addition', 'increase' ),
			array( 'unprofessional', 'substandard', 'sloppy', 'slapdash', 'clumsy', 'crude', 'slipshod', 'incompetent', 'inexpert' ),
			array( 'astonishment', 'wonder', 'admiration', 'shock', 'incredulity', 'surprise', 'bewilderment' ),
			array( 'political', 'diplomatic', 'embassy' ),
			array( 'yellowish-brown', 'orangey', 'tawny', 'ocher', 'orange', 'yellow' ),
			array( 'atmosphere', 'feel', 'setting', 'environment', 'mood', 'vibes', 'character', 'air', 'quality', 'tone' ),
			array( 'vague', 'unclear', 'uncertain', 'indefinite', 'confusing', 'indistinct', 'hazy', 'wooly' ),
			array( 'vaguely', 'dubiously', 'obscurely', 'puzzlingly' ),
			array( 'goal', 'aim', 'objective', 'aspiration', 'dream', 'hope', 'desire', 'purpose', 'drive', 'determination', 'get-up-and-go', 'motivation' ),
			array( 'unsure', 'undecided', 'in two minds', 'of two minds', 'hesitant' ),
			array( 'stroll', 'saunter', 'wander', 'mosey', 'promenade', 'walk' ),
			array( 'waylay', 'trap', 'ensnare', 'lie in wait', 'trap', 'surprise attack' ),
			array( 'improve', 'restructure', 'revolutionize', 'remodel', 'reorganize', 'modernize', 'rearrange', 'upgrade', 'amend', 'restore' ),
			array( 'agreeable', 'open to', 'acquiescent', 'willing' ),
			array( 'adaptable', 'modifiable', 'changeable', 'variable', 'regulating', 'bendable', 'flexible' ),
			array( 'facilities', 'services' ),
			array( 'pale purple', 'purple', 'mauve', 'lilac', 'heliotrope' ),
			array( 'friendliness', 'amicability', 'sociability', 'cordiality', 'agreeableness', 'good nature', 'good humor', 'kindness', 'geniality', 'affability' ),
			array( 'friendly', 'sociable', 'good-natured', 'agreeable', 'affable', 'cordial', 'kind', 'likable', 'good-humored', 'genial' ),
			array( 'warmly', 'good-naturedly', 'pleasantly', 'agreeably', 'cordially', 'affably', 'kindly', 'genially' ),
			array( 'friendliness', 'amiability', 'sociability', 'cordiality', 'agreeableness', 'good nature', 'good humor', 'kindness', 'geniality', 'affability' ),
			array( 'friendly', 'good-natured', 'harmonious', 'cordial', 'agreeable', 'good-humored', 'kind', 'polite' ),
			array( 'good-naturedly', 'cordially', 'harmoniously', 'kindly', 'politely', 'good-humoredly', 'agreeably', 'affably' ),
			array( 'in the middle of', 'among', 'in the midst of', 'accompanied by', 'along with', 'in the course of' ),
			array( 'wrong', 'muddled', 'incorrect' ),
			array( 'friendship', 'peace', 'good relations', 'goodwill', 'harmony' ),
			array( 'bullets', 'shells', 'missiles', 'bombs', 'grenades' ),
			array( 'bullets', 'shells', 'missiles', 'bombs', 'grenades', 'ammo' ),
			array( 'loss of memory', 'memory loss', 'forgetfulness', 'a total blank' ),
			array( 'official pardon', 'general pardon', 'reprieve', 'forgiveness' ),
			array( 'in the middle of', 'in the midst of', 'amongst', 'amid', 'surrounded by', 'between', 'with', 'along with', 'amongst', 'amid', 'together with', 'in the company of', 'between', 'amongst' ),
			array( 'in the middle of', 'surrounded by', 'in the midst of', 'between', 'among', 'amid', 'with', 'along with', 'among', 'amid', 'together with', 'in the company of', 'between', 'among' ),
			array( 'unprincipled', 'unethical', 'dishonorable', 'unscrupulous', 'immoral' ),
			array( 'ardent', 'passionate', 'affectionate', 'loving', 'romantic', 'sentimental' ),
			array( 'affectionately', 'tenderly', 'fondly', 'devotedly', 'adoringly', 'dotingly' ),
			array( 'formless', 'shapeless', 'nebulous', 'vague', 'unstructured', 'fluid' ),
			array( 'bagginess', 'formlessness', 'fluidity' ),
			array( 'paying back', 'paying off' ),
			array( 'repay', 'pay back', 'pay off' ),
			array( 'quantity', 'sum', 'total' ),
			array( 'arena', 'arena', 'auditorium', 'ground', 'showground', 'sports ground', 'pitch', 'field', 'ring', 'dome' ),
			array( 'intensification', 'strengthening', 'magnification', 'augmentation', 'extension', 'increase', 'enlargement', 'further explanation', 'further details', 'elaboration', 'clarification', 'development' ),
			array( 'greater than before', 'augmented', 'enlarged', 'bigger', 'improved', 'better' ),
			array( 'intensify', 'increase', 'strengthen', 'magnify', 'augment', 'enlarge', 'add details to', 'enlarge on', 'go into detail', 'elaborate', 'add to', 'expand', 'clarify', 'develop' ),
			array( 'sufficiently', 'adequately', 'abundantly', 'thoroughly', 'fully' ),
			array( 'cut off', 'remove', 'surgically remove', 'sever', 'separate' ),
			array( 'taking away', 'elimination', 'exclusion', 'subtraction', 'deletion', 'confiscation', 'deduction', 'abstraction', 'ejection' ),
			array( 'charm', 'good luck charm', 'talisman', 'lucky charm' ),
			array( 'entertain', 'occupy', 'keep busy', 'interest', 'absorb', 'engross', 'keep amused', 'make laugh', 'make smile', 'charm', 'please', 'divert' ),
			array( 'smiling', 'laughing', 'pleased' ),
			array( 'funny', 'humorous', 'entertaining', 'comical', 'witty', 'droll', 'hilarious' ),
			array( 'humorously', 'entertainingly', 'comically', 'wittily', 'hilariously' ),
			array( 'relic', 'survival', 'leftover', 'holdover' ),
			array( 'out of date', 'outdated', 'dated', 'old-fashioned', 'old', 'obsolete', 'archaic', 'antiquated', 'outmoded', 'obsolescent', 'passÐ¹' ),
			array( 'painkiller', 'palliative', 'pain reliever', 'painkilling', 'palliative', 'pain-relieving', 'deadening', 'numbing' ),
			array( 'psychoanalysis', 'psychiatry', 'psychotherapy', 'examination', 'study', 'investigation', 'scrutiny', 'breakdown', 'chemical analysis', 'testing', 'laboratory analysis', 'examination', 'assay' ),
			array( 'logical', 'investigative', 'diagnostic', 'systematic', 'critical', 'methodical', 'questioning', 'reasoned', 'rational', 'analytical' ),
			array( 'logical', 'investigative', 'diagnostic', 'systematic', 'critical', 'methodical', 'questioning', 'reasoned', 'rational', 'analytic' ),
			array( 'logically', 'systematically', 'critically', 'methodically', 'rationally' ),
			array( 'examine', 'study', 'investigate', 'scrutinize', 'evaluate', 'consider', 'question', 'explore', 'probe', 'dissect' ),
			array( 'lawless', 'chaotic', 'disordered', 'radical', 'revolutionary', 'rebellious', 'revolutionary' ),
			array( 'disorder', 'chaos', 'lawlessness', 'revolution', 'mayhem', 'rebellion' ),
			array( 'revolutionary', 'rebel', 'nihilist', 'radical' ),
			array( 'revolutionary', 'antigovernment' ),
			array( 'abhorrence', 'abomination' ),
			array( 'intimates', 'associates', 'relatives', 'family', 'relations' ),
			array( 'family', 'familial', 'inherited' ),
			array( 'lineage', 'descent', 'origin', 'heritage', 'extraction', 'stock', 'pedigree', 'parentage', 'line' ),
			array( 'fasten', 'secure', 'attach', 'fix', 'affix', 'newscaster', 'commentator', 'presenter', 'announcer' ),
			array( 'port', 'dock', 'waterfront', 'wharf', 'quay', 'marina', 'haven' ),
			array( 'port', 'dock', 'waterfront', 'wharf', 'quay', 'marina', 'haven' ),
			array( 'very old', 'antique', 'early', 'earliest', 'olden', 'prehistoric', 'primeval', 'primordial', 'antediluvian', 'antiquated', 'old-fashioned', 'archaic', 'obsolete', 'outdated', 'out of date', 'dated', 'prehistoric', 'antediluvian' ),
			array( 'auxiliary', 'subsidiary', 'supplementary', 'additional', 'secondary' ),
			array( 'plus', 'in addition to', 'as well as', 'with', 'along with', 'furthermore', 'moreover', 'also', 'then', 'after that', 'afterward', 'next', 'as a consequence' ),
			array( 'genderless', 'asexual', 'neuter', 'neutral', 'sexless', 'hermaphrodite' ),
			array( 'robot', 'machine' ),
			array( 'subjective', 'unreliable', 'untrustworthy', 'undependable', 'sketchy' ),
			array( 'weak', 'feeble', 'lackluster', 'insipid', 'pale', 'colorless', 'wishy-washy', 'anodyne', 'bland' ),
			array( 'painkiller', 'local anesthetic', 'general anesthetic', 'sedative', 'analgesic', 'painkilling', 'numbing', 'deadening', 'sedating' ),
			array( 'deaden', 'numb', 'sedate', 'freeze', 'put under', 'put out', 'put to sleep' ),
			array( 'knocked out', 'out cold', 'under', 'asleep', 'sedated', 'deadened', 'numb', 'frozen' ),
			array( 'deadening', 'freezing' ),
			array( 'one more', 'an additional', 'a different', 'a further', 'an extra', 'an added', 'any more', 'an alternative' ),
			array( 'seraph', 'archangel', 'guardian angel', 'cherub' ),
			array( 'innocent', 'good', 'pure', 'beatific', 'saintly', 'adorable', 'virtuous' ),
			array( 'infuriated', 'incensed', 'annoyed', 'enraged', 'exasperated', 'irritated', 'frustrated' ),
			array( 'point of view', 'viewpoint', 'approach', 'position', 'slant', 'perspective', 'outlook', 'direction', 'slant', 'incline', 'tilt', 'turn', 'twist', 'slope', 'point', 'face', 'aim' ),
			array( 'slanting', 'at an angle', 'sloping', 'on a slope', 'oblique', 'diagonal', 'inclined', 'aslant', 'leaning', 'sideways' ),
			array( 'heatedly', 'irritably', 'furiously', 'irately', 'crossly' ),
			array( 'annoyed', 'irritated', 'fuming', 'mad', 'livid', 'irate', 'heated', 'gnashing your teeth', 'cross', 'furious', 'incensed', 'enraged', 'outraged', 'infuriated' ),
			array( 'anguish', 'torment', 'anxiety', 'trouble', 'sorrow', 'worry', 'fear' ),
			array( 'tormented', 'suffering', 'agonized', 'tortured', 'painful', 'distressed', 'grief-stricken', 'sorrowful', 'angst-ridden' ),
			array( 'bony', 'raw-boned', 'rangy', 'lanky', 'gaunt', 'pointed', 'sharp' ),
			array( 'creature', 'mammal', 'living thing', 'being', 'monster', 'beast', 'brute', 'swine', 'physical', 'bodily', 'visceral', 'instinctive', 'innate', 'inborn', 'subconscious' ),
			array( 'flora and fauna', 'nature', 'natural world', 'birds', 'plants' ),
			array( 'living', 'alive', 'live', 'breathing', 'flesh and blood', 'conscious', 'sentient', 'liven up', 'enliven', 'rouse', 'bring to life', 'stir', 'stimulate' ),
			array( 'lively', 'energetic', 'vigorous', 'active', 'vibrant', 'vivacious', 'dynamic', 'full of life', 'enthusiastic', 'excited', 'sparkling', 'spirited' ),
			array( 'energetically', 'vigorously', 'vivaciously', 'dynamically', 'enthusiastically', 'excitedly', 'spiritedly' ),
			array( 'cartoon', 'moving picture', 'animatronics', 'computer graphics', 'simulation', 'liveliness', 'energy', 'vibrancy', 'life', 'vigor', 'vivaciousness', 'dynamism', 'enthusiasm', 'excitement', 'activity', 'sparkle', 'spirit' ),
			array( 'hostility', 'hatred', 'loathing', 'ill feeling', 'ill will', 'enmity', 'bitterness', 'acrimony', 'rancor', 'dislike', 'antagonism' ),
			array( 'records', 'archives', 'chronicles', 'history' ),
			array( 'take possession of', 'seize', 'take over', 'occupy', 'capture', 'invade', 'take control of', 'appropriate', 'commandeer' ),
			array( 'capture', 'seizure', 'takeover', 'occupation', 'invasion', 'appropriation' ),
			array( 'wipe out', 'destroy', 'obliterate', 'extinguish', 'eradicate', 'exterminate', 'defeat', 'beat', 'rout', 'thrash', 'overwhelm', 'crush' ),
			array( 'total destruction', 'obliteration', 'extinction', 'eradication', 'extermination' ),
			array( 'gloss', 'add footnotes to', 'interpret', 'explain', 'make notes on', 'comment on' ),
			array( 'footnote', 'gloss', 'marginal note', 'explanation' ),
			array( 'comments', 'explanation', 'remarks', 'observations', 'notes', 'clarification', 'interpretation' ),
			array( 'comments', 'explanation', 'remarks', 'observations', 'notes', 'clarification', 'interpretation' ),
			array( 'proclaim', 'make known', 'publicize', 'broadcast', 'declare', 'say', 'pronounce', 'state', 'reveal', 'name', 'post', 'herald', 'publish', 'read out' ),
			array( 'statement', 'declaration', 'message', 'notice', 'proclamation', 'publication', 'broadcast', 'pronouncement', 'revelation' ),
			array( 'presenter', 'anchor', 'broadcaster', 'telecaster' ),
			array( 'irritate', 'infuriate', 'exasperate', 'aggravate', 'upset', 'get on your nerves', 'drive you mad', 'wind you up', 'bother', 'madden', 'anger', 'frustrate', 'displease', 'provoke', 'rile', 'incense', 'put out', 'cheese off', 'nark', 'hack off' ),
			array( 'angry', 'irritated', 'infuriated', 'exasperated', 'aggravated', 'upset', 'wound up', 'bothered', 'maddened', 'frustrated', 'displeased', 'provoked', 'riled', 'incensed', 'cheesed off', 'put out' ),
			array( 'maddening', 'irritating', 'infuriating', 'bothersome', 'exasperating', 'aggravating', 'frustrating', 'trying', 'a pain', 'grating' ),
			array( 'exasperatingly', 'infuriatingly', 'frustratingly', 'nauseatingly', 'gallingly' ),
			array( 'yearly', 'twelve-monthly', 'once a year' ),
			array( 'pension', 'allowance', 'income' ),
			array( 'cancel', 'call off', 'withdraw', 'end', 'terminate', 'dissolve', 'rescind', 'invalidate', 'put an end to' ),
			array( 'void', 'canceled', 'invalid', 'null and void', 'negated' ),
			array( 'cancellation', 'termination', 'withdrawal', 'dissolution', 'invalidation' ),
			array( 'insipid', 'bland', 'tame', 'neutral', 'inoffensive', 'colorless', 'dull', 'antiseptic', 'unexciting', 'anemic' ),
			array( 'smear', 'daub', 'rub', 'smooth', 'massage' ),
			array( 'irregular', 'uncharacteristic', 'strange', 'abnormal', 'inconsistent', 'out of the ordinary', 'jarring', 'atypical', 'unusual' ),
			array( 'secrecy', 'mystery', 'obscurity', 'ambiguity', 'inscrutability', 'vagueness' ),
			array( 'nameless', 'unidentified', 'unnamed', 'unsigned', 'unspecified', 'unknown', 'secret', 'mysterious', 'shadowy', 'undistinguished', 'indistinctive', 'ordinary', 'everyday', 'run of the mill', 'unexceptional', 'unmemorable', 'dull' ),
			array( 'incognito', 'namelessly', 'in secret', 'secretly' ),
			array( 'one more', 'an additional', 'a new', 'a different', 'a further', 'an extra', 'an added', 'any more', 'an alternative' ),
		);

		/**
		 * Defines the available spintax words
		 *
		 * @since   1.7.9
		 *
		 * @param   array   $spintax    Spintax words.
		 */
		$spintax = apply_filters( 'page_generator_pro_spintax_get_spintax_words', $spintax );

		// Return filtered results.
		return $spintax;

	}

}
