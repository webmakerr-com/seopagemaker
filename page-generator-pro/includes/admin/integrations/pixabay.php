<?php
/**
 * Pixabay API Class.
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Fetch images from Pixabay based on given criteria.
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 2.2.9
 */
class Page_Generator_Pro_Pixabay extends Page_Generator_Pro_API {

	use Page_Generator_Pro_Integration_Trait;
	use Page_Generator_Pro_Featured_Image_Trait;
	use Page_Generator_Pro_Shortcode_Image_Trait;

	/**
	 * Holds the base object.
	 *
	 * @since   4.8.0
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
	public $name = 'pixabay';

	/**
	 * Holds the API endpoint
	 *
	 * @since   2.2.9
	 *
	 * @var     string
	 */
	public $api_endpoint = 'https://pixabay.com/api/';

	/**
	 * Holds the API Key
	 *
	 * @since   2.2.9
	 *
	 * @var     string
	 */
	public $api_key = '13733126-38bca84073eedea378d529ff3';

	/**
	 * Constructor
	 *
	 * @since   4.8.0
	 *
	 * @param   object $base    Base Plugin Class.
	 */
	public function __construct( $base ) {

		// Store base class.
		$this->base = $base;

		// Register Settings Fields.
		add_filter( 'page_generator_pro_integrations_get', array( $this, 'register_integration' ) );
		add_filter( 'page_generator_pro_integrations_get_settings_fields', array( $this, 'get_settings_fields' ) );

		// Register as a Dynamic Element.
		add_filter( 'page_generator_pro_shortcode_add_shortcodes', array( $this, 'add_shortcode' ) );
		add_filter( 'page_generator_pro_gutenberg_convert_html_block_to_core_block', array( $this, 'convert_html_block_to_image_block' ), 10, 1 );

		// Register as a Featured Image source.
		add_filter( 'page_generator_pro_common_get_featured_image_sources', array( $this, 'add_featured_image_source' ) );
		add_filter( 'page_generator_pro_groups_get_defaults', array( $this, 'get_featured_image_default_values' ) );
		add_filter( 'page_generator_pro_common_get_featured_image_fields', array( $this, 'get_featured_image_fields' ), 10, 2 );
		add_filter( 'page_generator_pro_common_get_featured_image_tabs', array( $this, 'get_featured_image_tabs' ) );
		add_filter( 'page_generator_pro_generate_featured_image_' . $this->name, array( $this, 'get_featured_image' ), 10, 6 );

	}

	/**
	 * Returns this shortcode / block's title.
	 *
	 * @since   4.5.2
	 *
	 * @return  string
	 */
	public function get_title() {

		return __( 'Pixabay', 'page-generator-pro' );

	}

	/**
	 * Returns settings fields and their values to display on:
	 * - Settings > Integrations
	 *
	 * @since   4.8.0
	 *
	 * @param   array $settings_fields    Settings Fields.
	 * @return  array                     Settings Fields
	 */
	public function get_settings_fields( $settings_fields ) {

		// Define fields and their values.
		$settings_fields[ $this->name ] = array(
			$this->get_settings_prefix() . '_api_key' => array(
				'label'         => sprintf(
					/* translators: %1$s: Integration name, %2$s Field label */
					'%1$s: %2$s',
					$this->get_title(),
					__( 'API Key', 'page-generator-pro' )
				),
				'type'          => 'text',
				'default_value' => $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-integrations', $this->get_settings_prefix() . '_api_key' ),
				'description'   => sprintf(
					'%s %s %s',
					esc_html__( 'If you reach an API limit when attempting to import images from Pixabay, you\'ll need to use your own free Pixabay API key.', 'page-generator-pro' ),
					'<a href="' . esc_attr( $this->base->plugin->documentation_url ) . '/settings-integration/#pixabay" target="_blank" rel="noopener">' . esc_html__( 'Click here', 'page-generator-pro' ) . '</a>',
					esc_html__( 'to read the step by step documentation to do this.', 'page-generator-pro' )
				),
			),
		);

		return $settings_fields;

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
	public function get_provider_search_fields( $is_featured_image = false ) {

		$prefix_key = ( $is_featured_image ? $this->get_settings_prefix() . '_' : '' );

		return array(
			'term'                      => array(
				'label'       => __( 'Term', 'page-generator-pro' ),
				'type'        => 'autocomplete',
				'values'      => $this->keywords,
				'placeholder' => __( 'e.g. building', 'page-generator-pro' ),
				'description' => __( 'The search term to use.  For example, "laptop" would return an image of a laptop.', 'page-generator-pro' ),
			),
			'orientation'               => array( // No prefix key is defined here. This is deliberate; `orientation` is shared with Pexels.
				'label'         => __( 'Image Orientation', 'page-generator-pro' ),
				'type'          => 'select',
				'values'        => $this->get_image_orientations(),
				'default_value' => $this->get_default_value( 'orientation' ),
				'description'   => __( 'The image orientation to output.', 'page-generator-pro' ),
			),
			$prefix_key . 'language'    => array(
				'label'         => __( 'Language', 'page-generator-pro' ),
				'type'          => 'select',
				'values'        => $this->get_languages(),
				'default_value' => $this->get_default_value( 'language' ),
				'description'   => __( 'The language the above search term is in.', 'page-generator-pro' ),
			),
			$prefix_key . 'image_type'  => array(
				'label'         => __( 'Image Type', 'page-generator-pro' ),
				'type'          => 'select',
				'values'        => $this->get_image_types(),
				'default_value' => $this->get_default_value( 'image_type' ),
				'description'   => __( 'The image type to search.', 'page-generator-pro' ),
			),
			$prefix_key . 'category'    => array(
				'label'         => __( 'Image Category', 'page-generator-pro' ),
				'type'          => 'select',
				'values'        => $this->get_categories(),
				'default_value' => $this->get_default_value( 'category' ),
				'description'   => __( 'The image category to search.', 'page-generator-pro' ),
			),
			$prefix_key . 'color'       => array(
				'label'         => __( 'Image Color', 'page-generator-pro' ),
				'type'          => 'select',
				'values'        => $this->get_colors(),
				'default_value' => $this->get_default_value( 'color' ),
				'description'   => __( 'Returns an image primarily comprising of the given color.', 'page-generator-pro' ),
			),
			$prefix_key . 'safe_search' => array(
				'label'         => __( 'Safe Search', 'page-generator-pro' ),
				'type'          => 'toggle',
				'default_value' => $this->get_default_value( 'safe_search' ),
				'description'   => __( 'Returns safe images only.', 'page-generator-pro' ),
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
	public function get_provider_output_fields( $is_featured_image = false ) {

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
			'orientation' => array(
				'type'    => 'string',
				'default' => ( ! $this->get_default_value( 'orientation' ) ? '' : $this->get_default_value( 'orientation' ) ),
			),
			'language'    => array(
				'type'    => 'string',
				'default' => ( ! $this->get_default_value( 'language' ) ? '' : $this->get_default_value( 'language' ) ),
			),
			'image_type'  => array(
				'type'    => 'string',
				'default' => ( ! $this->get_default_value( 'image_type' ) ? '' : $this->get_default_value( 'image_type' ) ),
			),
			'category'    => array(
				'type'    => 'string',
				'default' => ( ! $this->get_default_value( 'category' ) ? '' : $this->get_default_value( 'category' ) ),
			),
			'color'       => array(
				'type'    => 'string',
				'default' => ( ! $this->get_default_value( 'color' ) ? '' : $this->get_default_value( 'color' ) ),
			),
			'safe_search' => array(
				'type'    => 'boolean',
				'default' => $this->get_default_value( 'safe_search' ),
			),
			'attribution' => array(
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
			// Search.
			'orientation'               => 'all', // No prefix key is defined here. This is deliberate; `orientation` is shared with Pexels.
			$prefix_key . 'language'    => 'en',
			$prefix_key . 'image_type'  => 'all',
			$prefix_key . 'category'    => false,
			$prefix_key . 'color'       => false,
			$prefix_key . 'safe_search' => false,

			// Output.
			'attribution'               => false,
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

		return __( 'Displays an image from Pixabay, based on the given search parameters.', 'page-generator-pro' );

	}

	/**
	 * Returns this shortcode / block's icon.
	 *
	 * @since   4.5.2
	 *
	 * @return  string
	 */
	public function get_icon() {

		return 'assets/images/icons/pixabay.svg';

	}

	/**
	 * Returns this shortcode / block's output
	 *
	 * @since   2.5.1
	 *
	 * @param  array $atts   Shortcode Attributes.
	 * @return string          Output
	 */
	public function render( $atts ) {

		// Parse attributes.
		$atts = $this->parse_atts( $atts );

		// If a Pixabay API Key has been specified, use it instead of the class default.
		$api_key = $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-integrations', 'pixabay_api_key' );
		if ( ! empty( $api_key ) ) {
			$this->set_api_key( $api_key );
		}

		// Define the number of images to return to then choose one at random from.
		$per_page = 150;

		// Run query to fetch total number of pages of results that are available.
		$page_count = $this->page_count(
			$atts['term'],
			$atts['language'],
			$atts['image_type'],
			$atts['orientation'],
			$atts['category'],
			0,
			0,
			$atts['color'],
			$atts['safe_search'],
			$per_page
		);

		// Handle errors.
		if ( is_wp_error( $page_count ) ) {
			return $this->add_dynamic_element_error_and_return( $page_count, $atts );
		}

		// Pick a page index at random from the resultset.
		if ( $page_count === 1 ) {
			$page_index = 1;
		} else {
			$page_index = wp_rand( 1, $page_count );
		}

		// Run images query.
		$images = $this->photos_search(
			$atts['term'],
			$atts['language'],
			$atts['image_type'],
			$atts['orientation'],
			$atts['category'],
			0,
			0,
			$atts['color'],
			$atts['safe_search'],
			$per_page,
			$page_index
		);

		// Handle errors.
		if ( is_wp_error( $images ) ) {
			return $this->add_dynamic_element_error_and_return( $images, $atts );
		}

		// Pick an image at random from the resultset.
		$image = $this->choose_random_image( $images );

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
		 * Filter the Pixabay HTML output, before returning.
		 *
		 * @since   1.0.0
		 *
		 * @param   string      $html       HTML Output.
		 * @param   array       $atts       Shortcode Attributes.
		 * @param   bool|int    $image_id   WordPress Media Library Image ID (false = not imported into Media Library as copy=0).
		 * @param   array       $images     Pixabay Image Results.
		 * @param   array       $image      Pixabay Image chosen at random and imported into the Media Library.
		 */
		$html = apply_filters( 'page_generator_pro_shortcode_pixabay', $html, $atts, $image_id, $images, $image );

		// Return.
		return $html;

	}

	/**
	 * Defines the Featured Image for the given generated Post ID, if
	 * the Group Settings specify a Featured Image.
	 *
	 * @since   2.3.5
	 *
	 * @param   bool|int $image_id   Image ID.
	 * @param   int      $post_id    Generated Post ID.
	 * @param   int      $group_id   Group ID.
	 * @param   int      $index      Generation Index.
	 * @param   array    $settings   Group Settings.
	 * @param   array    $post_args  wp_insert_post() / wp_update_post() arguments.
	 * @return  WP_Error|bool|int
	 */
	public function get_featured_image( $image_id, $post_id, $group_id, $index, $settings, $post_args ) {

		// Bail if no Featured Image Term specified.
		if ( empty( $settings['featured_image_term'] ) ) {
			return new WP_Error(
				'page_generator_pro_generate',
				sprintf(
					/* translators: Integration Name */
					__( 'Featured Image: %s: No Term was specified.', 'page-generator-pro' ),
					$this->get_title()
				)
			);
		}

		// Adjust image orientation setting to be API compatible,
		// as providers may vary their settings here.
		switch ( $settings['featured_image_orientation'] ) {
			case 'portrait':
			case 'tall':
				$settings['featured_image_orientation'] = 'vertical';
				break;

			case 'landscape':
			case 'horizontal':
			case 'wide':
			case 'square':
				$settings['featured_image_orientation'] = 'horizontal';
				break;
		}

		// If a Pixabay API Key has been specified, use it instead of the class default.
		$api_key = $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-integrations', $this->get_settings_prefix() . '_api_key' );
		if ( ! empty( $api_key ) ) {
			$this->set_api_key( $api_key );
		}

		// Define the number of images to return to then choose one at random from.
		$per_page = 150;

		// Run query to fetch total number of pages of results that are available.
		$page_count = $this->page_count(
			$settings['featured_image_term'],
			$settings['featured_image_pixabay_language'],
			$settings['featured_image_pixabay_image_type'],
			$settings['featured_image_orientation'],
			$settings['featured_image_pixabay_category'],
			0,
			0,
			$settings['featured_image_pixabay_color'],
			false,
			$per_page
		);

		// Bail if an error occured.
		if ( is_wp_error( $page_count ) ) {
			return $page_count;
		}

		// Pick a page index at random from the resultset.
		if ( $page_count === 1 ) {
			$page_index = 1;
		} else {
			$page_index = wp_rand( 1, $page_count );
		}

		// Run images query.
		$images = $this->photos_search(
			$settings['featured_image_term'],
			$settings['featured_image_pixabay_language'],
			$settings['featured_image_pixabay_image_type'],
			$settings['featured_image_orientation'],
			$settings['featured_image_pixabay_category'],
			0,
			0,
			$settings['featured_image_pixabay_color'],
			false,
			$per_page,
			$page_index
		);

		// Bail if an error occured.
		if ( is_wp_error( $images ) ) {
			return $images;
		}

		// Pick an image at random from the resultset.
		if ( count( $images ) === 1 ) {
			$image_index = 0;
		} else {
			$image_index = wp_rand( 0, ( count( $images ) - 1 ) );
		}

		// Import Image into the Media Library, returning the result.
		return $this->import_remote_image(
			$images[ $image_index ]['url'],
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
	 * Returns an array of language codes and names supported
	 * by the API.
	 *
	 * @since   2.2.9
	 *
	 * @return  array   Supported Languages
	 */
	public function get_languages() {

		return array(
			'all' => __( 'Any', 'page-generator-pro' ),
			'cs'  => __( 'Čeština', 'page-generator-pro' ),
			'da'  => __( 'Dansk', 'page-generator-pro' ),
			'de'  => __( 'Deutsch', 'page-generator-pro' ),
			'en'  => __( 'English', 'page-generator-pro' ),
			'es'  => __( 'Español', 'page-generator-pro' ),
			'fr'  => __( 'Français', 'page-generator-pro' ),
			'id'  => __( 'Indonesia', 'page-generator-pro' ),
			'it'  => __( 'Italiano', 'page-generator-pro' ),
			'hu'  => __( 'Magyar', 'page-generator-pro' ),
			'nl'  => __( 'Nederlands', 'page-generator-pro' ),
			'no'  => __( 'Norsk nynorsk', 'page-generator-pro' ),
			'pl'  => __( 'Polski', 'page-generator-pro' ),
			'pt'  => __( 'Português', 'page-generator-pro' ),
			'ro'  => __( 'Română', 'page-generator-pro' ),
			'sk'  => __( 'Slovenčina', 'page-generator-pro' ),
			'fi'  => __( 'Suomi', 'page-generator-pro' ),
			'sv'  => __( 'Svenska', 'page-generator-pro' ),
			'tr'  => __( 'Türkçe', 'page-generator-pro' ),
			'vi'  => __( 'Tiếng Việt', 'page-generator-pro' ),
			'th'  => __( 'ไทย', 'page-generator-pro' ),
			'bg'  => __( 'Български', 'page-generator-pro' ),
			'ru'  => __( 'Русский', 'page-generator-pro' ),
			'el'  => __( 'Ελληνικά', 'page-generator-pro' ),
			'ja'  => __( '日本語', 'page-generator-pro' ),
			'ko'  => __( '한국어', 'page-generator-pro' ),
			'zh'  => __( '简体中文', 'page-generator-pro' ),
		);

	}

	/**
	 * Returns an array of image types supported
	 * by the API.
	 *
	 * @since   2.2.9
	 *
	 * @return  array   Supported Image Types
	 */
	public function get_image_types() {

		return array(
			'all'          => __( 'Any', 'page-generator-pro' ),
			'illustration' => __( 'Illustration', 'page-generator-pro' ),
			'photo'        => __( 'Photo', 'page-generator-pro' ),
			'vector'       => __( 'Vector', 'page-generator-pro' ),
		);

	}

	/**
	 * Returns an array of video types supported
	 * by the API.
	 *
	 * @since   2.2.9
	 *
	 * @return  array   Supported Video Types
	 */
	public function get_video_types() {

		return array(
			'all'       => __( 'Any', 'page-generator-pro' ),
			'animation' => __( 'Animation', 'page-generator-pro' ),
			'film'      => __( 'Film', 'page-generator-pro' ),
		);

	}

	/**
	 * Returns an array of image orientations supported
	 * by the API.
	 *
	 * @since   2.2.9
	 *
	 * @return  array   Supported Image Orientations
	 */
	public function get_image_orientations() {

		return array(
			'all'        => __( 'Any', 'page-generator-pro' ),
			'vertical'   => __( 'Portrait', 'page-generator-pro' ),
			'horizontal' => __( 'Landscape', 'page-generator-pro' ),
		);

	}

	/**
	 * Returns an array of categories supported
	 * by the API.
	 *
	 * @since   2.2.9
	 *
	 * @return  array   Supported Categories
	 */
	public function get_categories() {

		return array(
			''               => __( 'Any', 'page-generator-pro' ),
			'animals'        => __( 'Animals', 'page-generator-pro' ),
			'backgrounds'    => __( 'Backgrounds', 'page-generator-pro' ),
			'buildings'      => __( 'Buildings', 'page-generator-pro' ),
			'business'       => __( 'Business', 'page-generator-pro' ),
			'computer'       => __( 'Computer', 'page-generator-pro' ),
			'education'      => __( 'Education', 'page-generator-pro' ),
			'fashion'        => __( 'Fashion', 'page-generator-pro' ),
			'feelings'       => __( 'Feelings', 'page-generator-pro' ),
			'food'           => __( 'Foods', 'page-generator-pro' ),
			'health'         => __( 'Health', 'page-generator-pro' ),
			'industry'       => __( 'Industry', 'page-generator-pro' ),
			'music'          => __( 'Music', 'page-generator-pro' ),
			'nature'         => __( 'Nature', 'page-generator-pro' ),
			'people'         => __( 'People', 'page-generator-pro' ),
			'places'         => __( 'Places', 'page-generator-pro' ),
			'religion'       => __( 'Religion', 'page-generator-pro' ),
			'science'        => __( 'Science', 'page-generator-pro' ),
			'sports'         => __( 'Sports', 'page-generator-pro' ),
			'transportation' => __( 'Transportation', 'page-generator-pro' ),
			'travel'         => __( 'Travel', 'page-generator-pro' ),
		);

	}

	/**
	 * Returns an array of image colors supported
	 * by the API.
	 *
	 * @since   2.2.9
	 *
	 * @return  array   Supported Image Colors
	 */
	public function get_colors() {

		return array(
			''            => __( 'Any', 'page-generator-pro' ),
			'black'       => __( 'Black', 'page-generator-pro' ),
			'blue'        => __( 'Blue', 'page-generator-pro' ),
			'brown'       => __( 'Brown', 'page-generator-pro' ),
			'gray'        => __( 'Gray', 'page-generator-pro' ),
			'grayscale'   => __( 'Grayscale', 'page-generator-pro' ),
			'green'       => __( 'Green', 'page-generator-pro' ),
			'lilac'       => __( 'Lilac', 'page-generator-pro' ),
			'orange'      => __( 'Orange', 'page-generator-pro' ),
			'pink'        => __( 'Pink', 'page-generator-pro' ),
			'red'         => __( 'Red', 'page-generator-pro' ),
			'transparent' => __( 'Transparent', 'page-generator-pro' ),
			'turquoise'   => __( 'Turquoise', 'page-generator-pro' ),
			'white'       => __( 'White', 'page-generator-pro' ),
			'yellow'      => __( 'Yellow', 'page-generator-pro' ),
		);

	}

	/**
	 * Searches photos based on the given query
	 *
	 * @since   2.2.9
	 *
	 * @param   string      $query          Search Term(s).
	 * @param   string      $language       Language ( see get_languages() for valid values ).
	 * @param   string      $image_type     Image Type ( see get_image_types() for valid values ).
	 * @param   string      $orientation    Image Orientation ( see get_image_orientations() for valid values ).
	 * @param   bool|string $category       Image Category ( see get_categories() for valid values ).
	 * @param   int         $min_width      Minimum Image Width.
	 * @param   int         $min_height     Minimum Image Height.
	 * @param   bool|string $color          Color ( see get_colors() for valid values ).
	 * @param   bool        $safe_search    Safe Search.
	 * @param   int         $per_page       Number of Images to Return.
	 * @param   int         $page           Pagination Page Offset.
	 * @return  WP_Error|array
	 */
	public function photos_search(
		$query,
		$language = 'en',
		$image_type = 'all',
		$orientation = 'all',
		$category = false,
		$min_width = 0,
		$min_height = 0,
		$color = false,
		$safe_search = false,
		$per_page = 150,
		$page = 1
	) {

		// Perform search.
		$results = $this->search(
			$query,
			$language,
			$image_type,
			$orientation,
			$category,
			$min_width,
			$min_height,
			$color,
			$safe_search,
			$per_page,
			$page
		);

		// Bail if an error occured.
		if ( is_wp_error( $results ) ) {
			return new WP_Error(
				'page_generator_pro_pixabay_error',
				sprintf(
					/* translators: Error message */
					__( 'photos_search(): %s', 'page-generator-pro' ),
					$results->get_error_message()
				)
			);
		}

		// Parse results.
		$images = array();
		foreach ( $results->hits as $photo ) {
			// Creator.
			if ( isset( $photo->user ) ) {
				/* translators: Photographer's Name */
				$creator = sprintf( __( '%s on Pixabay', 'page-generator-pro' ), $photo->user );
			} else {
				$creator = false;
			}

			$images[] = array(
				'url'             => $photo->largeImageURL, // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
				'title'           => $photo->tags,

				// Credits.
				'source'          => $photo->pageURL, // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
				'creator'         => $creator,
				'creator_url'     => ( isset( $photo->user ) && isset( $photo->user_id ) ? 'https://pixabay.com/users/' . $photo->user . '-' . $photo->user_id . '/' : false ),
				'license'         => false,
				'license_version' => false,
				'license_url'     => false,
			);
		}

		// Return array of images.
		return $images;

	}

	/**
	 * Returns the total number of pages found for the search parameters
	 *
	 * @since   2.8.4
	 *
	 * @param   string      $query          Search Term(s).
	 * @param   string      $language       Language ( see get_languages() for valid values ).
	 * @param   string      $image_type     Image Type ( see get_image_types() for valid values ).
	 * @param   string      $orientation    Image Orientation ( see get_image_orientations() for valid values ).
	 * @param   bool|string $category       Image Category ( see get_categories() for valid values ).
	 * @param   int         $min_width      Minimum Image Width.
	 * @param   int         $min_height     Minimum Image Height.
	 * @param   bool|string $color          Color ( see get_colors() for valid values ).
	 * @param   bool        $safe_search    Safe Search.
	 * @param   int         $per_page       Number of Images to Return.
	 * @param   int         $page           Pagination Page Offset.
	 * @return  WP_Error|int
	 */
	public function page_count(
		$query,
		$language = 'en',
		$image_type = 'all',
		$orientation = 'all',
		$category = false,
		$min_width = 0,
		$min_height = 0,
		$color = false,
		$safe_search = false,
		$per_page = 150,
		$page = 1
	) {

		// Perform search.
		$results = $this->search(
			$query,
			$language,
			$image_type,
			$orientation,
			$category,
			$min_width,
			$min_height,
			$color,
			$safe_search,
			$per_page,
			$page
		);

		// Bail if an error occured.
		if ( is_wp_error( $results ) ) {
			return new WP_Error(
				'page_generator_pro_pixabay_page_count_error',
				sprintf(
					/* translators: Error message */
					__( 'page_count(): %s', 'page-generator-pro' ),
					$results->get_error_message()
				)
			);
		}

		return (int) ceil( $results->totalHits / $per_page ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase

	}

	/**
	 * Searches photos based on the given query
	 *
	 * @since   2.2.9
	 *
	 * @param   string      $query          Search Term(s).
	 * @param   string      $language       Language ( see get_languages() for valid values ).
	 * @param   string      $image_type     Image Type ( see get_image_types() for valid values ).
	 * @param   string      $orientation    Image Orientation ( see get_image_orientations() for valid values ).
	 * @param   bool|string $category       Image Category ( see get_categories() for valid values ).
	 * @param   int         $min_width      Minimum Image Width.
	 * @param   int         $min_height     Minimum Image Height.
	 * @param   bool|string $color          Color ( see get_colors() for valid values ).
	 * @param   bool        $safe_search    Safe Search.
	 * @param   int         $per_page       Number of Images to Return.
	 * @param   int         $page           Pagination Page Offset.
	 * @return  WP_Error|stdClass
	 */
	private function search(
		$query,
		$language = 'en',
		$image_type = 'all',
		$orientation = 'all',
		$category = false,
		$min_width = 0,
		$min_height = 0,
		$color = false,
		$safe_search = false,
		$per_page = 150,
		$page = 1
	) {

		// Build array of arguments  .
		$args = array(
			'key'         => $this->api_key,
			'q'           => $query,
			'size'        => 'large',
			'lang'        => $language,
			'image_type'  => $image_type,
			'orientation' => $orientation,
			'min_width'   => $min_width,
			'min_height'  => $min_height,
			'safe_search' => $safe_search,
			'per_page'    => $per_page,
			'page'        => $page,
		);

		// Add optional arguments.
		if ( $category !== false ) {
			$args['category'] = $category;
		}
		if ( $color !== false ) {
			$args['colors'] = $color;
		}

		/**
		 * Filters the API arguments to send to the Pexels /search endpoint
		 *
		 * @since   2.2.9
		 *
		 * @param   array       $args           API arguments.
		 * @param   string      $query          Search Term(s).
		 * @param   string      $language       Language ( see get_languages() for valid values ).
		 * @param   string      $image_type     Image Type ( see get_image_types() for valid values ).
		 * @param   string      $orientation    Image Orientation ( see get_image_orientations() for valid values ).
		 * @param   bool|string $category       Image Category ( see get_categories() for valid values ).
		 * @param   int         $min_width      Minimum Image Width.
		 * @param   int         $min_height     Minimum Image Height.
		 * @param   bool|string $color          Color ( see get_colors() for valid values ).
		 * @param   bool        $safe_search    Safe Search.
		 * @param   int         $per_page       Number of Images to Return.
		 * @param   int         $page           Pagination Page Offset.
		 */
		$args = apply_filters( 'page_generator_pro_pixabay_photos_search_args', $args, $query, $language, $image_type, $orientation, $category, $min_width, $min_height, $color, $safe_search, $per_page, $page );

		// Run the query.
		$results = $this->get( '/', $args );

		// Bail if an error occured.
		if ( is_wp_error( $results ) ) {
			return $results;
		}

		// Bail if no results found.
		if ( ! count( $results->hits ) ) { // @phpstan-ignore-line
			return new WP_Error(
				'page_generator_pro_pixabay_error',
				__( 'No results were found for the given search criteria.', 'page-generator-pro' )
			);
		}

		return $results;

	}

}
