<?php
/**
 * Wikipedia Image Class
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Wikipedia Image Integration
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 3.1.7
 */
class Page_Generator_Pro_Wikipedia_Image {

	use Page_Generator_Pro_Integration_Trait;
	use Page_Generator_Pro_Featured_Image_Trait;
	use Page_Generator_Pro_Shortcode_Image_Trait;

	/**
	 * Holds the base object.
	 *
	 * @since   3.1.7
	 *
	 * @var     object
	 */
	public $base;

	/**
	 * Holds fetched Wikipedia images in single request cycle
	 *
	 * @since   2.2.7
	 *
	 * @var     array
	 */
	private $image_cache = array();

	/**
	 * Holds the programmatic name of this integration.
	 *
	 * @since   4.8.0
	 *
	 * @var     string
	 */
	public $name = 'wikipedia-image';

	/**
	 * Constructor
	 *
	 * @since   3.1.7
	 *
	 * @param   object $base    Base Plugin Class.
	 */
	public function __construct( $base ) {

		// Store base class.
		$this->base = $base;

		// Register as a Dynamic Element.
		add_filter( 'page_generator_pro_shortcode_add_shortcodes', array( $this, 'add_shortcode' ) );
		add_filter( 'page_generator_pro_gutenberg_convert_html_block_to_core_block', array( $this, 'convert_html_block_to_image_block' ), 10, 1 );

		// Register as a Featured Image source.
		add_filter( 'page_generator_pro_common_get_featured_image_sources', array( $this, 'add_featured_image_source' ) );
		add_filter( 'page_generator_pro_groups_get_defaults', array( $this, 'get_featured_image_default_values' ) );
		add_filter( 'page_generator_pro_common_get_featured_image_fields', array( $this, 'get_featured_image_fields' ), 10, 2 );
		add_filter( 'page_generator_pro_common_get_featured_image_tabs', array( $this, 'get_featured_image_tabs' ) );
		add_filter( 'page_generator_pro_generate_featured_image_' . $this->name, array( $this, 'get_featured_image' ), 10, 5 );

	}

	/**
	 * Returns this shortcode / block's title.
	 *
	 * @since   4.5.2
	 *
	 * @return  string
	 */
	public function get_title() {

		return __( 'Wikipedia Image', 'page-generator-pro' );

	}

	/**
	 * Returns provider-specific fields for the Search Parameters section across
	 * the Dynamic Element and Featured Image.
	 *
	 * @since   4.8.0
	 *
	 * @param   bool $is_featured_image Fields are for Featured Image functionality.
	 * @return  array
	 */
	public function get_provider_search_fields( $is_featured_image = false ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found

		// Don't populate fields for performance if the request is for the frontend web site.
		// Populate fields for admin and CLI requests so that Generate via Browser and CLI
		// will see fields for this shortcode, which is required for correct operation with e.g. Elementor
		// registered shortcodes/elements.
		if ( ! $this->base->is_admin_or_frontend_editor() && ! $this->base->is_cli() && ! $this->base->is_cron() ) {
			return array();
		}

		$prefix_key = ( $is_featured_image ? $this->get_settings_prefix() . '_' : '' );

		// Return fields.
		return array(
			$prefix_key . 'term'            => array(
				'label'       => __( 'Term(s) / URL(s)', 'page-generator-pro' ),
				'type'        => 'text_multiple',
				'data'        => array(
					'delimiter' => ';',
				),
				'class'       => 'wpzinc-selectize-freeform',
				'description' => __( 'Specify one or more terms or Wikipedia URLs to search for on Wikipedia, in order. An image will be used at random from the first term / URL that produces a matching Wikipedia Page', 'page-generator-pro' ),
			),
			$prefix_key . 'language'        => array(
				'label'         => __( 'Language', 'page-generator-pro' ),
				'type'          => 'select',
				'values'        => $this->get_languages(),
				'default_value' => $this->get_default_value( 'language' ),
			),
			$prefix_key . 'use_first_image' => array(
				'label'         => __( 'Use First Image?', 'page-generator-pro' ),
				'type'          => 'toggle',
				'description'   => __( 'If enabled, returns the first image found from Wikipedia. This may be useful to return a more relevant image.', 'page-generator-pro' ),
				'default_value' => $this->get_default_value( 'use_first_image' ),
			),
		);

	}

	/**
	 * Returns provider-specific fields for the Output section across
	 * the Dynamic Element and Featured Image.
	 *
	 * @since   4.8.0
	 *
	 * @param   bool $is_featured_image Fields are for Featured Image functionality.
	 * @return  array
	 */
	public function get_provider_output_fields( $is_featured_image = false ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found

		// No fields are needed for the Output section when configuring the Featured Image,
		// as attribution isn't possible.
		if ( $is_featured_image ) {
			return array();
		}

		return array(
			'attribution' => array(
				'label'       => __( 'Show Attribution?', 'page-generator-pro' ),
				'type'        => 'toggle',
				'description' => __( 'If enabled, outputs the credits/attribution below the image.', 'page-generator-pro' ),
			),
		);

	}

	/**
	 * Returns provider-specific attributes for the Dynamic Element.
	 *
	 * These are not used for Featured Images.
	 *
	 * @since   4.8.0
	 *
	 * @return  array
	 */
	public function get_provider_attributes() {

		return array(
			'term'            => array(
				'type'      => 'array',
				'delimiter' => ';',
			),
			'language'        => array(
				'type'    => 'string',
				'default' => ( ! $this->get_default_value( 'language' ) ? '' : $this->get_default_value( 'language' ) ),
			),
			'use_first_image' => array(
				'type'    => 'boolean',
				'default' => $this->get_default_value( 'use_first_image' ),
			),
			'attribution'     => array(
				'type'    => 'boolean',
				'default' => $this->get_default_value( 'attribution' ),
			),
		);

	}

	/**
	 * Returns provider-specific default values across the Dynamic Element and Featured Image.
	 *
	 * @since   4.8.0
	 *
	 * @param   bool $is_featured_image Fields are for Featured Image functionality.
	 * @return  array
	 */
	public function get_provider_default_values( $is_featured_image = false ) {

		$prefix_key = ( $is_featured_image ? $this->get_settings_prefix() . '_' : '' );

		return array(
			$prefix_key . 'term'            => '',
			$prefix_key . 'language'        => 'en',
			$prefix_key . 'use_first_image' => false,
			'attribution'                   => false,
		);

	}

	/**
	 * Returns this shortcode / block's description.
	 *
	 * @since   4.5.2
	 *
	 * @return string
	 */
	public function get_description() {

		return __( 'Displays an image from Wikipedia, based on the given search parameters.', 'page-generator-pro' );

	}

	/**
	 * Returns this shortcode / block's icon.
	 *
	 * @since   4.5.2
	 *
	 * @return  string
	 */
	public function get_icon() {

		return 'assets/images/icons/wikipedia-image.svg';

	}

	/**
	 * Returns this shortcode / block's output
	 *
	 * @since   3.1.7
	 *
	 * @param  array $atts   Shortcode Attributes.
	 * @return string          Output
	 */
	public function render( $atts ) {

		// Parse attributes.
		$atts = $this->parse_atts( $atts );

		// Iterate through terms until we find a page.
		$errors = array();
		foreach ( $atts['term'] as $term ) {
			// Skip empty Terms.
			if ( empty( $term ) ) {
				continue;
			}

			// Run images query.
			$images = $this->get_images( $term, $atts['language'] );

			// Collect errors.
			if ( is_wp_error( $images ) ) {
				$errors[] = sprintf(
					/* translators: %1$s: Search Term, %2$s: Error message */
					__( '"%1$s": %2$s', 'page-generator-pro' ),
					$term,
					$images->get_error_message()
				);
				continue;
			}

			// If here, we managed to fetch elements.
			// Unset errors and break the loop.
			unset( $errors );
			break;
		}

		// If errors exist, bail.
		if ( isset( $errors ) && count( $errors ) > 0 ) {
			return $this->add_dynamic_element_error_and_return(
				new WP_Error(
					'page_generator_pro_wikipedia_image_error',
					implode( '<br />', $errors )
				),
				$atts
			);
		}

		// If no images exist, bail.
		if ( ! isset( $images ) ) {
			return $this->add_dynamic_element_error_and_return(
				new WP_Error(
					'page_generator_pro_wikipedia_image_error',
					__( 'The term parameter is missing.', 'page-generator-pro' )
				),
				$atts
			);
		}

		// If returning the first image, select it now.
		if ( $atts['use_first_image'] ) {
			$image = $images[0];
		} else {
			// Pick an image at random from the resultset.
			$image = $this->choose_random_image( $images );
		}

		// Import the image.
		$image_id = false;
		if ( $atts['copy'] ) {
			$image_id = $this->import( $image, $atts );

			// Bail if an error occured.
			if ( is_wp_error( $image_id ) ) {
				return $this->add_dynamic_element_error_and_return( $image_id, $atts );
			}
		}

		// Get HTML image tag, with the image matching the given WordPress registered image size.
		$html = $this->get_image_html( $image_id, $atts, $image );

		/**
		 * Filter the Wikipedia Image HTML output, before returning.
		 *
		 * @since   3.1.7
		 *
		 * @param   string      $html       HTML Output.
		 * @param   array       $atts       Shortcode Attributes.
		 * @param   bool|int    $image_id   WordPress Media Library Image ID (false = not imported into Media Library as copy=0).
		 * @param   array       $images     Wikipedia Image Results.
		 * @param   array       $image      Wikipedia Image chosen at random and imported into the Media Library.
		 */
		$html = apply_filters( 'page_generator_pro_shortcode_wikipedia_image', $html, $atts, $image_id, $images, $image );

		// Return.
		return $html;

	}

	/**
	 * Defines the Featured Image for the given generated Post ID, if
	 * the Group Settings specify a Featured Image.
	 *
	 * @since   4.8.0
	 *
	 * @param   bool|int $image_id   Image ID.
	 * @param   int      $post_id    Generated Post ID.
	 * @param   int      $group_id   Group ID.
	 * @param   int      $index      Generation Index.
	 * @param   array    $settings   Group Settings.
	 * @return  WP_Error|bool|int
	 */
	public function get_featured_image( $image_id, $post_id, $group_id, $index, $settings ) {

		// Iterate through terms until we find a page.
		$errors = array();
		foreach ( explode( ';', $settings[ 'featured_image_' . $this->get_settings_prefix() . '_term' ] ) as $term ) {
			// Skip empty Terms.
			if ( empty( $term ) ) {
				continue;
			}

			// Run images query.
			$images = $this->get_images( $term, $settings[ 'featured_image_' . $this->get_settings_prefix() . '_language' ] );

			// Collect errors.
			if ( is_wp_error( $images ) ) {
				$errors[] = sprintf(
					/* translators: %1$s: Search Term, %2$s: Error message */
					__( '%1$s: %2$s', 'page-generator-pro' ),
					$term,
					$images->get_error_message()
				);
				continue;
			}

			// If here, we managed to fetch elements.
			// Unset errors and break the loop.
			unset( $errors );
			break;
		}

		// If errors exist, bail.
		if ( isset( $errors ) && count( $errors ) > 0 ) {
			return new WP_Error(
				'page_generator_pro_generate',
				sprintf(
					/* translators: Errors */
					__( 'Featured Image: Wikipedia Image:<br />%s', 'page-generator-pro' ),
					implode( ', ', $errors )
				)
			);
		}

		// If no images exist, bail.
		if ( ! isset( $images ) ) {
			return new WP_Error(
				'page_generator_pro_generate',
				__( 'Featured Image: Wikipedia Image: No images found.', 'page-generator-pro' )
			);
		}

		// If returning the first image, select it now.
		if ( $settings[ 'featured_image_' . $this->get_settings_prefix() . '_use_first_image' ] ) {
			$image = $images[0];
		} else {
			// Pick an image at random from the resultset.
			$image = $this->choose_random_image( $images );
		}

		// Import Image into the Media Library.
		return $this->import_remote_image(
			$image['url'],
			$post_id,
			$group_id,
			$index,
			$settings['featured_image_filename'],
			$settings['featured_image_title'],
			$settings['featured_image_caption'],
			$settings['featured_image_alt_tag'],
			$settings['featured_image_description']
		);

	}

	/**
	 * Returns all images for the given Wikipedia Term
	 *
	 * If $term is an array, iterates through the Terms until images are returned
	 *
	 * @since   3.1.7
	 *
	 * @param   string $term       Term.
	 * @param   string $language   Language.
	 */
	private function get_images( $term, $language = 'en' ) {

		// Sanitize term.
		$term = $this->sanitize_term( $term );

		// If images already exist in cache, return them now.
		if ( isset( $this->image_cache[ $term . '-' . $language ] ) ) {
			return $this->image_cache[ $term . '-' . $language ];
		}

		// Query API.
		$result = $this->request(
			array(
				'page' => $term,
				'prop' => 'images',
			),
			$language
		);

		// Bail if an error occured.
		if ( is_wp_error( $result ) ) {
			return new WP_Error(
				'page_generator_pro_wikipedia_get_images_error',
				$result->get_error_message()
			);
		}

		// If no images exist, bail.
		if ( ! count( $result->images ) ) {
			return new WP_Error(
				'page_generator_pro_wikipedia_get_images_error',
				__( 'No images found', 'page-generator-pro' )
			);
		}

		// Define the terms, of which at least one must be in an image's filename.
		$filename_terms = preg_split( '/(\_|\,)/', $term );

		// Build images array, removing some images that probably won't be relevant.
		$images = array();
		foreach ( $result->images as $image ) {
			// Skip anything that isn't a JPEG.
			// This avoids returning SVG icons that have no relevance to the Term.
			if ( stripos( $image, '.jpg' ) === false ) {
				continue;
			}

			// Iterate through terms, of which at least one must be in the image's filename.
			$image_matches = false;
			foreach ( $filename_terms as $filename_term ) {
				if ( stripos( $image, $filename_term ) === false ) {
					continue;
				}

				$image_matches = true;
			}

			// Skip if this image doesn't match the term.
			if ( ! $image_matches ) {
				continue;
			}

			// Get image URL.
			$image_url = $this->get_image_url( $image );

			// Skip if false.
			if ( ! $image_url ) {
				continue;
			}

			// Add to images array.
			$images[] = array(
				'url'             => $image_url,
				'title'           => str_replace( '_', ' ', $image ),

				// Credits.
				'source'          => $image_url,
				'creator'         => __( 'Wikipedia', 'page-generator-pro' ),
				'creator_url'     => false,
				'license'         => false,
				'license_version' => false,
				'license_url'     => false,
			);
		}

		// If no images exist, bail.
		if ( ! count( $images ) ) {
			return new WP_Error(
				'page_generator_pro_wikipedia_get_images_error',
				sprintf(
					/* translators: Search Term */
					__( 'Images exist on the Wikipedia page, but none of the image filenames were relevent to the Term.', 'page-generator-pro' ),
					$term
				)
			);
		}

		// Add to cache.
		$this->image_cache[ $term . '-' . $language ] = $images;

		// Return images for this Term.
		return $this->image_cache[ $term . '-' . $language ];

	}

	/**
	 * Extracts the Term from a URL, if the Term is a URL, and sanitizes the Term
	 * to remove some accents that cause issues with Wikipedia
	 *
	 * @since   3.1.7
	 *
	 * @param   string $term   Term or Wikipedia URL.
	 * @return  string          Term
	 */
	private function sanitize_term( $term ) {

		// If the Term is a Wikipedia URL, extract the Term for the API call.
		if ( filter_var( $term, FILTER_VALIDATE_URL ) && strpos( $term, 'wikipedia.org' ) !== false ) {
			$url  = wp_parse_url( $term );
			$term = str_replace( '/wiki/', '', $url['path'] );
			$term = str_replace( '/', '', $term );
		}

		// Return sanitized term.
		return str_replace( ' ', '_', preg_replace( '/&([A-Za-z]{1,2})(grave|acute|circ|cedil|uml|lig);/', '$1', $term ) );

	}

	/**
	 * Returns the full image URI for the given Wikipedia Image Filename
	 *
	 * @see     https://commons.wikimedia.org/wiki/Commons:FAQ#What_are_the_strangely_named_components_in_file_paths.3F
	 *
	 * @since   3.1.7
	 *
	 * @param   string $image_filename     Image Filename.
	 * @return  bool|string                     Image URL
	 */
	private function get_image_url( $image_filename ) {

		// Bail if no image filename.
		if ( ! $image_filename || empty( $image_filename ) ) { // @phpstan-ignore-line
			return false;
		}

		// Replace spaces with underscores.
		$image_filename = str_replace( ' ', '_', $image_filename );

		// Get MD5 hash.
		$hash = md5( $image_filename );

		// Return URL per wikimedia format.
		return 'https://upload.wikimedia.org/wikipedia/commons/' . substr( $hash, 0, 1 ) . '/' . substr( $hash, 0, 2 ) . '/' . $image_filename;

	}

	/**
	 * Helper method to retrieve Wikipedia languages
	 *
	 * @since   3.1.7
	 *
	 * @return  array    Languages
	 */
	private function get_languages() {

		// Keys are Wikipedia subdomains e.g. ab.wikipedia.org.
		// Values are the language names in English.
		$languages = array(
			'ab'           => 'Abkhazian',
			'ace'          => 'Acehnese',
			'ady'          => 'Adyghe',
			'aa'           => 'Afar',
			'af'           => 'Afrikaans',
			'ak'           => 'Akan',
			'sq'           => 'Albanian',
			'als'          => 'Alemannic',
			'am'           => 'Amharic',
			'ang'          => 'Anglo-Saxon',
			'ar'           => 'Arabic',
			'an'           => 'Aragonese',
			'arc'          => 'Aramaic',
			'hy'           => 'Armenian',
			'roa-rup'      => 'Aromanian',
			'as'           => 'Assamese',
			'ast'          => 'Asturian',
			'av'           => 'Avar',
			'ay'           => 'Aymara',
			'az'           => 'Azerbaijani',
			'bm'           => 'Bambara',
			'bjn'          => 'Banjar',
			'map-bms'      => 'Banyumasan',
			'ba'           => 'Bashkir',
			'eu'           => 'Basque',
			'bar'          => 'Bavarian',
			'be'           => 'Belarusian',
			'be-tarask'    => 'Belarusian (Taraškievica)',
			'bn'           => 'Bengali',
			'bh'           => 'Bihari',
			'bpy'          => 'Bishnupriya Manipuri',
			'bi'           => 'Bislama',
			'bs'           => 'Bosnian',
			'br'           => 'Breton',
			'bug'          => 'Buginese',
			'bg'           => 'Bulgarian',
			'my'           => 'Burmese',
			'bxr'          => 'Buryat',
			'zh-yue'       => 'Cantonese',
			'ca'           => 'Catalan',
			'ceb'          => 'Cebuano',
			'bcl'          => 'Central Bicolano',
			'ch'           => 'Chamorro',
			'cbk-zam'      => 'Chavacano',
			'ce'           => 'Chechen',
			'chr'          => 'Cherokee',
			'chy'          => 'Cheyenne',
			'ny'           => 'Chichewa',
			'zh'           => 'Chinese',
			'cho'          => 'Choctaw',
			'cv'           => 'Chuvash',
			'zh-classical' => 'Classical Chinese',
			'kw'           => 'Cornish',
			'co'           => 'Corsican',
			'cr'           => 'Cree',
			'crh'          => 'Crimean Tatar',
			'hr'           => 'Croatian',
			'cs'           => 'Czech',
			'da'           => 'Danish',
			'dv'           => 'Divehi',
			'nl'           => 'Dutch',
			'nds-nl'       => 'Dutch Low Saxon',
			'dz'           => 'Dzongkha',
			'arz'          => 'Egyptian Arabic',
			'eml'          => 'Emilian-Romagnol',
			'en'           => 'English',
			'myv'          => 'Erzya',
			'eo'           => 'Esperanto',
			'et'           => 'Estonian',
			'ee'           => 'Ewe',
			'ext'          => 'Extremaduran',
			'fo'           => 'Faroese',
			'hif'          => 'Fiji Hindi',
			'fj'           => 'Fijian',
			'fi'           => 'Finnish',
			'frp'          => 'Franco-Provençal',
			'fr'           => 'French',
			'fur'          => 'Friulian',
			'ff'           => 'Fula',
			'gag'          => 'Gagauz',
			'gl'           => 'Galician',
			'gan'          => 'Gan',
			'ka'           => 'Georgian',
			'de'           => 'German',
			'glk'          => 'Gilaki',
			'gom'          => 'Goan Konkani',
			'got'          => 'Gothic',
			'el'           => 'Greek',
			'kl'           => 'Greenlandic',
			'gn'           => 'Guarani',
			'gu'           => 'Gujarati',
			'ht'           => 'Haitian',
			'hak'          => 'Hakka',
			'ha'           => 'Hausa',
			'haw'          => 'Hawaiian',
			'he'           => 'Hebrew',
			'hz'           => 'Herero',
			'mrj'          => 'Hill Mari',
			'hi'           => 'Hindi',
			'ho'           => 'Hiri Motu',
			'hu'           => 'Hungarian',
			'is'           => 'Icelandic',
			'io'           => 'Ido',
			'ig'           => 'Igbo',
			'ilo'          => 'Ilokano',
			'id'           => 'Indonesian',
			'ia'           => 'Interlingua',
			'ie'           => 'Interlingue',
			'iu'           => 'Inuktitut',
			'ik'           => 'Inupiak',
			'ga'           => 'Irish',
			'it'           => 'Italian',
			'jam'          => 'Jamaican Patois',
			'ja'           => 'Japanese',
			'jv'           => 'Javanese',
			'kbd'          => 'Kabardian',
			'kab'          => 'Kabyle',
			'xal'          => 'Kalmyk',
			'kn'           => 'Kannada',
			'kr'           => 'Kanuri',
			'pam'          => 'Kapampangan',
			'krc'          => 'Karachay-Balkar',
			'kaa'          => 'Karakalpak',
			'ks'           => 'Kashmiri',
			'csb'          => 'Kashubian',
			'kk'           => 'Kazakh',
			'km'           => 'Khmer',
			'ki'           => 'Kikuyu',
			'rw'           => 'Kinyarwanda',
			'ky'           => 'Kirghiz',
			'rn'           => 'Kirundi',
			'kv'           => 'Komi',
			'koi'          => 'Komi-Permyak',
			'kg'           => 'Kongo',
			'ko'           => 'Korean',
			'kj'           => 'Kuanyama',
			'ku'           => 'Kurdish (Kurmanji)',
			'ckb'          => 'Kurdish (Sorani)',
			'lad'          => 'Ladino',
			'lbe'          => 'Lak',
			'lo'           => 'Lao',
			'ltg'          => 'Latgalian',
			'la'           => 'Latin',
			'lv'           => 'Latvian',
			'lez'          => 'Lezgian',
			'lij'          => 'Ligurian',
			'li'           => 'Limburgish',
			'ln'           => 'Lingala',
			'lt'           => 'Lithuanian',
			'jbo'          => 'Lojban',
			'lmo'          => 'Lombard',
			'nds'          => 'Low Saxon',
			'dsb'          => 'Lower Sorbian',
			'lg'           => 'Luganda',
			'lb'           => 'Luxembourgish',
			'mk'           => 'Macedonian',
			'mai'          => 'Maithili',
			'mg'           => 'Malagasy',
			'ms'           => 'Malay',
			'ml'           => 'Malayalam',
			'mt'           => 'Maltese',
			'gv'           => 'Manx',
			'mi'           => 'Maori',
			'mr'           => 'Marathi',
			'mh'           => 'Marshallese',
			'mzn'          => 'Mazandarani',
			'mhr'          => 'Meadow Mari',
			'cdo'          => 'Min Dong',
			'zh-min-nan'   => 'Min Nan',
			'min'          => 'Minangkabau',
			'xmf'          => 'Mingrelian',
			'mwl'          => 'Mirandese',
			'mdf'          => 'Moksha',
			'mo'           => 'Moldovan',
			'mn'           => 'Mongolian',
			'mus'          => 'Muscogee',
			'nah'          => 'Nahuatl',
			'na'           => 'Nauruan',
			'nv'           => 'Navajo',
			'ng'           => 'Ndonga',
			'nap'          => 'Neapolitan',
			'ne'           => 'Nepali',
			'new'          => 'Newar',
			'pih'          => 'Norfolk',
			'nrm'          => 'Norman',
			'frr'          => 'North Frisian',
			'lrc'          => 'Northern Luri',
			'se'           => 'Northern Sami',
			'nso'          => 'Northern Sotho',
			'no'           => 'Norwegian (Bokmål)',
			'nn'           => 'Norwegian (Nynorsk)',
			'nov'          => 'Novial',
			'ii'           => 'Nuosu',
			'oc'           => 'Occitan',
			'cu'           => 'Old Church Slavonic',
			'or'           => 'Oriya',
			'om'           => 'Oromo',
			'os'           => 'Ossetian',
			'pfl'          => 'Palatinate German',
			'pi'           => 'Pali',
			'pag'          => 'Pangasinan',
			'pap'          => 'Papiamentu',
			'ps'           => 'Pashto',
			'pdc'          => 'Pennsylvania German',
			'fa'           => 'Persian',
			'pcd'          => 'Picard',
			'pms'          => 'Piedmontese',
			'pl'           => 'Polish',
			'pnt'          => 'Pontic',
			'pt'           => 'Portuguese',
			'pa'           => 'Punjabi',
			'qu'           => 'Quechua',
			'ksh'          => 'Ripuarian',
			'rmy'          => 'Romani',
			'ro'           => 'Romanian',
			'rm'           => 'Romansh',
			'ru'           => 'Russian',
			'rue'          => 'Rusyn',
			'sah'          => 'Sakha',
			'sm'           => 'Samoan',
			'bat-smg'      => 'Samogitian',
			'sg'           => 'Sango',
			'sa'           => 'Sanskrit',
			'sc'           => 'Sardinian',
			'stq'          => 'Saterland Frisian',
			'sco'          => 'Scots',
			'gd'           => 'Scottish Gaelic',
			'sr'           => 'Serbian',
			'sh'           => 'Serbo-Croatian',
			'st'           => 'Sesotho',
			'sn'           => 'Shona',
			'scn'          => 'Sicilian',
			'szl'          => 'Silesian',
			'simple'       => 'Simple English',
			'sd'           => 'Sindhi',
			'si'           => 'Sinhalese',
			'sk'           => 'Slovak',
			'sl'           => 'Slovenian',
			'so'           => 'Somali',
			'azb'          => 'Southern Azerbaijani',
			'es'           => 'Spanish',
			'srn'          => 'Sranan',
			'su'           => 'Sundanese',
			'sw'           => 'Swahili',
			'ss'           => 'Swati',
			'sv'           => 'Swedish',
			'tl'           => 'Tagalog',
			'ty'           => 'Tahitian',
			'tg'           => 'Tajik',
			'ta'           => 'Tamil',
			'roa-tara'     => 'Tarantino',
			'tt'           => 'Tatar',
			'te'           => 'Telugu',
			'tet'          => 'Tetum',
			'th'           => 'Thai',
			'bo'           => 'Tibetan',
			'ti'           => 'Tigrinya',
			'tpi'          => 'Tok Pisin',
			'to'           => 'Tongan',
			'ts'           => 'Tsonga',
			'tn'           => 'Tswana',
			'tum'          => 'Tumbuka',
			'tr'           => 'Turkish',
			'tk'           => 'Turkmen',
			'tyv'          => 'Tuvan',
			'tw'           => 'Twi',
			'udm'          => 'Udmurt',
			'uk'           => 'Ukrainian',
			'hsb'          => 'Upper Sorbian',
			'ur'           => 'Urdu',
			'ug'           => 'Uyghur',
			'uz'           => 'Uzbek',
			've'           => 'Venda',
			'vec'          => 'Venetian',
			'vep'          => 'Vepsian',
			'vi'           => 'Vietnamese',
			'vo'           => 'Volapük',
			'fiu-vro'      => 'Võro',
			'wa'           => 'Walloon',
			'war'          => 'Waray',
			'cy'           => 'Welsh',
			'vls'          => 'West Flemish',
			'fy'           => 'West Frisian',
			'pnb'          => 'Western Punjabi',
			'wo'           => 'Wolof',
			'wuu'          => 'Wu',
			'xh'           => 'Xhosa',
			'yi'           => 'Yiddish',
			'yo'           => 'Yoruba',
			'diq'          => 'Zazaki',
			'zea'          => 'Zeelandic',
			'za'           => 'Zhuang',
			'zu'           => 'Zulu',
		);

		/**
		 * Defines available Wikipedia languages.
		 *
		 * @since   3.1.7
		 *
		 * @param   array   $output_types   Output Types.
		 */
		$languages = apply_filters( 'page_generator_pro_wikipedia_get_languages', $languages );

		// Return filtered results.
		return $languages;

	}

	/**
	 * Sends a request to the Wikipedia API
	 *
	 * @since   3.1.7
	 *
	 * @param   array  $args       Request arguments.
	 * @param   string $language   Language.
	 * @return  WP_Error|object
	 */
	private function request( $args, $language ) {

		// Merge args.
		$args = array_merge(
			$args,
			array(
				'action'        => 'parse',
				'format'        => 'json',
				'formatversion' => 2,
			)
		);

		// Build API URL.
		$url = add_query_arg( $args, 'https://' . $language . '.wikipedia.org/w/api.php' );

		// Query API.
		// User-agent ensures we get all content.
		$response = wp_remote_get(
			$url,
			array(
				'user-agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/34.0.1847.131 Safari/537.36',
				'sslverify'  => false,
			)
		);

		// Bail if an error occured.
		if ( is_wp_error( $response ) ) {
			return $response;
		}

		// Bail if HTTP response code isn't valid.
		if ( wp_remote_retrieve_response_code( $response ) !== 200 ) {
			return new WP_Error( 'page_generator_pro_wikipedia_request', wp_remote_retrieve_response_code( $response ) );
		}

		// Fetch body and JSON decode.
		$body   = wp_remote_retrieve_body( $response );
		$result = json_decode( $body );

		// Bail if an error was received from Wikipedia.
		if ( isset( $result->error ) ) {
			return new WP_Error( 'page_generator_pro_wikipedia_request', $result->error->code . ': ' . $result->error->info );
		}

		// Bail if the expected data is missing.
		if ( ! isset( $result->{ $args['action'] } ) ) {
			return new WP_Error( 'page_generator_pro_wikipedia_get_page', __( 'No data was returned.', 'page-generator-pro' ) );
		}

		// If the result contains a redirect, parse that instead.
		if ( isset( $result->{ $args['action'] }->text ) && strpos( $result->{ $args['action'] }->text, 'Redirect to:' ) !== false ) {
			// Extract redirect page slug.
			$start        = ( strpos( $result->{ $args['action'] }->text, 'href="' ) + 6 );
			$end          = strpos( $result->{ $args['action'] }->text, '" title="', $start );
			$args['page'] = str_replace( '/wiki/', '', substr( $result->{ $args['action'] }->text, $start, ( $end - $start ) ) );
			return $this->request( $args, $language );
		}

		// Return.
		return $result->{ $args['action'] };

	}

}
