<?php
/**
 * Yelp API Class.
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Fetch business listings from yelp.com
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 1.0.0
 */
class Page_Generator_Pro_Yelp extends Page_Generator_Pro_API {

	use Page_Generator_Pro_Integration_Trait;
	use Page_Generator_Pro_Shortcode_Trait;

	/**
	 * Holds the base class object.
	 *
	 * @since   1.4.5
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
	public $name = 'yelp';

	/**
	 * Holds the API endpoint
	 *
	 * @since   2.8.9
	 *
	 * @var     string
	 */
	public $api_endpoint = 'https://api.yelp.com/v3';

	/**
	 * Holds the user's API key
	 *
	 * @since   2.8.9
	 *
	 * @var     string
	 */
	public $api_key = '';

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

		// Register Settings Fields.
		add_filter( 'page_generator_pro_integrations_get', array( $this, 'register_integration' ) );
		add_filter( 'page_generator_pro_integrations_get_settings_fields', array( $this, 'get_settings_fields' ) );

		// Register as a Dynamic Element.
		add_filter( 'page_generator_pro_shortcode_add_shortcodes', array( $this, 'add_shortcode' ) );

	}

	/**
	 * Returns this shortcode / block's title.
	 *
	 * @since   4.5.2
	 *
	 * @return  string
	 */
	public function get_title() {

		return __( 'Yelp', 'page-generator-pro' );

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
					esc_html__( 'You\'ll need to use your own free Yelp API key when displaying Yelp content.', 'page-generator-pro' ),
					'<a href="' . esc_attr( $this->base->plugin->documentation_url ) . '/settings-integration/#yelp" target="_blank" rel="noopener">' . esc_html__( 'Click here', 'page-generator-pro' ) . '</a>',
					esc_html__( 'to read the step by step documentation to do this.', 'page-generator-pro' )
				),
			),
		);

		return $settings_fields;

	}

	/**
	 * Returns this shortcode / block's description.
	 *
	 * @since   4.5.2
	 *
	 * @return string
	 */
	public function get_description() {

		return __( 'Displays business listings from Yelp based on the given search parameters.', 'page-generator-pro' );

	}

	/**
	 * Returns this shortcode / block's icon.
	 *
	 * @since   4.5.2
	 *
	 * @return  string
	 */
	public function get_icon() {

		return '_modules/dashboard/feather/yelp.svg';

	}

	/**
	 * Returns this shortcode / block's TinyMCE modal width and height.
	 *
	 * @since   4.5.2
	 *
	 * @return  array
	 */
	public function get_modal_dimensions() {

		return array(
			'width'  => 990,
			'height' => 610,
		);

	}

	/**
	 * Returns whether this shortcode / block requires JS for output.
	 *
	 * @since   4.5.2
	 *
	 * @return  bool
	 */
	public function requires_js() {

		return true;

	}

	/**
	 * Returns whether this shortcode / block requires CSS for output.
	 *
	 * @since   5.2.8
	 *
	 * @return  bool
	 */
	public function requires_css() {

		return true;

	}

	/**
	 * Returns this block's Attributes and default values for Gutenberg.
	 *
	 * @since   3.6.3
	 */
	public function get_provider_attributes() {

		return array(
			// Search Parameters.
			'term'                 => array(
				'type'    => 'string',
				'default' => ( ! $this->get_default_value( 'term' ) ? '' : $this->get_default_value( 'term' ) ),
			),
			'location'             => array(
				'type'    => 'string',
				'default' => ( ! $this->get_default_value( 'location' ) ? '' : $this->get_default_value( 'location' ) ),
			),
			'radius'               => array(
				'type'    => 'number',
				'default' => $this->get_default_value( 'radius' ),
			),
			'minimum_rating'       => array(
				'type'    => 'string',
				'default' => ( ! $this->get_default_value( 'minimum_rating' ) ? '' : $this->get_default_value( 'minimum_rating' ) ),
			),
			'locale'               => array(
				'type'    => 'string',
				'default' => ( ! $this->get_default_value( 'locale' ) ? '' : $this->get_default_value( 'locale' ) ),
			),
			'price'                => array(
				'type'    => 'string',
				'default' => ( ! $this->get_default_value( 'price' ) ? '' : $this->get_default_value( 'price' ) ),
			),
			'limit'                => array(
				'type'    => 'number',
				'default' => $this->get_default_value( 'limit' ),
			),
			'sort_by'              => array(
				'type'    => 'string',
				'default' => ( ! $this->get_default_value( 'sort_by' ) ? '' : $this->get_default_value( 'sort_by' ) ),
			),

			// Output.
			'output_type'          => array(
				'type'    => 'string',
				'default' => ( ! $this->get_default_value( 'output_type' ) ? '' : $this->get_default_value( 'output_type' ) ),
			),
			'columns'              => array(
				'type'    => 'number',
				'default' => $this->get_default_value( 'columns' ),
			),
			'link'                 => array(
				'type'    => 'boolean',
				'default' => $this->get_default_value( 'link' ),
			),
			'image_width'          => array(
				'type'    => 'number',
				'default' => $this->get_default_value( 'image_width' ),
			),
			'image_alt_tag'        => array(
				'type'    => 'string',
				'default' => ( ! $this->get_default_value( 'image_alt_tag' ) ? '' : $this->get_default_value( 'image_alt_tag' ) ),
			),
			'display_order'        => array(
				'type'      => 'array',
				'delimiter' => ',',
				'default'   => $this->get_default_value( 'display_order' ),
			),
			'display_alignment'    => array(
				'type'    => 'string',
				'default' => ( ! $this->get_default_value( 'display_alignment' ) ? '' : $this->get_default_value( 'display_alignment' ) ),
			),

			// Preview.
			'is_gutenberg_example' => array(
				'type'    => 'boolean',
				'default' => false,
			),
		);

	}

	/**
	 * Returns this shortcode / block's Fields
	 *
	 * @since   2.5.1
	 */
	public function get_provider_fields() {

		// Load Keywords class.
		$keywords_class = $this->base->get_class( 'keywords' );

		// Bail if the Keywords class could not be loaded.
		if ( is_wp_error( $keywords_class ) ) {
			return false;
		}

		// Fetch Keywords.
		$keywords = $keywords_class->get_keywords_and_columns( true );

		return array(
			'term'              => array(
				'label'       => __( 'Term', 'page-generator-pro' ),
				'type'        => 'autocomplete',
				'values'      => $keywords,
				'placeholder' => __( 'e.g. restaurants', 'page-generator-pro' ),
			),
			'location'          => array(
				'label'       => __( 'Location', 'page-generator-pro' ),
				'type'        => 'autocomplete',
				'values'      => $keywords,
				'placeholder' => __( 'e.g. Birmingham, UK', 'page-generator-pro' ),
			),
			'radius'            => array(
				'label'         => __( 'Radius', 'page-generator-pro' ),
				'type'          => 'number',
				'min'           => 1,
				'max'           => 20,
				'step'          => 1,
				'default_value' => $this->get_default_value( 'radius' ),
				'description'   => __( 'The maximum radius, in miles, from the Location to search Business Listings for.', 'page-generator-pro' ),
			),
			'minimum_rating'    => array(
				'label'         => __( 'Minimum Rating', 'page-generator-pro' ),
				'type'          => 'select',
				'values'        => array(
					'0'   => __( 'No Minimum Rating', 'page-generator-pro' ),
					'0.5' => __( '0.5 / 5 or higher', 'page-generator-pro' ),
					'1'   => __( '1 / 5 or higher', 'page-generator-pro' ),
					'1.5' => __( '1.5 / 5 or higher', 'page-generator-pro' ),
					'2'   => __( '2 / 5 or higher', 'page-generator-pro' ),
					'2.5' => __( '2.5 / 5 or higher', 'page-generator-pro' ),
					'3'   => __( '3 / 5 or higher', 'page-generator-pro' ),
					'3.5' => __( '3.5 / 5 or higher', 'page-generator-pro' ),
					'4'   => __( '4 / 5 or higher', 'page-generator-pro' ),
					'4.5' => __( '4.5 / 5 or higher', 'page-generator-pro' ),
				),
				'default_value' => $this->get_default_value( 'minimum_rating' ),
				'description'   => __( 'The minimum rating a business listing must have to be displayed.', 'page-generator-pro' ),
			),
			'locale'            => array(
				'label'         => __( 'Language', 'page-generator-pro' ),
				'type'          => 'select',
				'values'        => array(
					'cs_CZ'  => __( 'Czech Republic: Czech', 'page-generator-pro' ),
					'da_DK'  => __( 'Denmark: Danish', 'page-generator-pro' ),
					'de_AT'  => __( 'Austria: German', 'page-generator-pro' ),
					'de_CH'  => __( 'Switzerland: German', 'page-generator-pro' ),
					'de_DE'  => __( 'Germany: German', 'page-generator-pro' ),
					'en_AU'  => __( 'Australia: English', 'page-generator-pro' ),
					'en_BE'  => __( 'Belgium: English', 'page-generator-pro' ),
					'en_CA'  => __( 'Canada: English', 'page-generator-pro' ),
					'en_CH'  => __( 'Switzerland: English', 'page-generator-pro' ),
					'en_GB'  => __( 'United Kingdom: English', 'page-generator-pro' ),
					'en_HK'  => __( 'Hong Kong: English', 'page-generator-pro' ),
					'en_IE'  => __( 'Republic of Ireland: English', 'page-generator-pro' ),
					'en_MY'  => __( 'Malaysia: English', 'page-generator-pro' ),
					'en_NZ'  => __( 'New Zealand: English', 'page-generator-pro' ),
					'en_PH'  => __( 'Philippines: English', 'page-generator-pro' ),
					'en_SG'  => __( 'Singapore: English', 'page-generator-pro' ),
					'en_US'  => __( 'United States: English', 'page-generator-pro' ),
					'es_AR'  => __( 'Argentina: Spanish', 'page-generator-pro' ),
					'es_CL'  => __( 'Chile: Spanish', 'page-generator-pro' ),
					'es_ES'  => __( 'Spain: Spanish', 'page-generator-pro' ),
					'es_MX'  => __( 'Mexico: Spanish', 'page-generator-pro' ),
					'fi_FI'  => __( 'Finland: Finnish', 'page-generator-pro' ),
					'fil_PH' => __( 'Philippines: Filipino', 'page-generator-pro' ),
					'fr_BE'  => __( 'Belgium: French', 'page-generator-pro' ),
					'fr_CA'  => __( 'Canada: French', 'page-generator-pro' ),
					'fr_CH'  => __( 'Switzerland: French', 'page-generator-pro' ),
					'fr_FR'  => __( 'France: French', 'page-generator-pro' ),
					'it_CH'  => __( 'Switzerland: Italian', 'page-generator-pro' ),
					'it_IT'  => __( 'Italy: Italian', 'page-generator-pro' ),
					'ja_JP'  => __( 'Japan: Japanese', 'page-generator-pro' ),
					'ms_MY'  => __( 'Malaysia: Malay', 'page-generator-pro' ),
					'nb_NO'  => __( 'Norway: Norwegian', 'page-generator-pro' ),
					'nl_BE'  => __( 'Belgium: Dutch', 'page-generator-pro' ),
					'nl_NL'  => __( 'The Netherlands: Dutch', 'page-generator-pro' ),
					'pl_PL'  => __( 'Poland: Polish', 'page-generator-pro' ),
					'pt_BR'  => __( 'Brazil: Portuguese', 'page-generator-pro' ),
					'pt_PT'  => __( 'Portugal: Portuguese', 'page-generator-pro' ),
					'sv_FI'  => __( 'Finland: Swedish', 'page-generator-pro' ),
					'sv_SE'  => __( 'Sweden: Swedish', 'page-generator-pro' ),
					'tr_TR'  => __( 'Turkey: Turkish', 'page-generator-pro' ),
					'zh_HK'  => __( 'Hong Kong: Chinese', 'page-generator-pro' ),
					'zh_TW'  => __( 'Taiwan: Chinese', 'page-generator-pro' ),
				),
				'default_value' => $this->get_default_value( 'locale' ),
			),
			'price'             => array(
				'label'         => __( 'Price', 'page-generator-pro' ),
				'type'          => 'select',
				'values'        => array(
					'0' => __( 'Any Price Level', 'page-generator-pro' ),
					'1' => __( '$', 'page-generator-pro' ),
					'2' => __( '$$', 'page-generator-pro' ),
					'3' => __( '$$$', 'page-generator-pro' ),
					'4' => __( '$$$$', 'page-generator-pro' ),
				),
				'default_value' => $this->get_default_value( 'price' ),
			),
			'limit'             => array(
				'label'         => __( 'Number of Listings', 'page-generator-pro' ),
				'type'          => 'number',
				'min'           => 1,
				'max'           => 50,
				'step'          => 1,
				'default_value' => $this->get_default_value( 'limit' ),
			),
			'sort_by'           => array(
				'label'         => __( 'Sort Listings', 'page-generator-pro' ),
				'type'          => 'select',
				'values'        => array(
					'best_match'   => __( 'Best Match', 'page-generator-pro' ),
					'rating'       => __( 'Rating', 'page-generator-pro' ),
					'review_count' => __( 'Review Count', 'page-generator-pro' ),
					'description'  => __( 'Description', 'page-generator-pro' ),
				),
				'default_value' => $this->get_default_value( 'sort_by' ),
			),

			'output_type'       => array(
				'label'         => __( 'Output Type', 'page-generator-pro' ),
				'type'          => 'select',
				'class'         => 'wpzinc-conditional',
				'data'          => array(
					// .components-panel is Gutenberg.
					// .yelp is TinyMCE.
					'container' => '.components-panel, .yelp',
				),
				'values'        => array(
					'table' => __( 'Table', 'page-generator-pro' ),
					'list'  => __( 'Grid/List', 'page-generator-pro' ),
				),
				'default_value' => $this->get_default_value( 'output_type' ),
			),
			'columns'           => array(
				'label'         => __( 'Number of Columns', 'page-generator-pro' ),
				'type'          => 'number',
				'min'           => 1,
				'max'           => 4,
				'step'          => 1,
				'condition'     => array(
					'key'        => 'output_type',
					'value'      => array( 'list' ),
					'comparison' => '==',
				),
				'default_value' => $this->get_default_value( 'columns' ),
			),
			'link'              => array(
				'label'         => __( 'Link Results', 'page-generator-pro' ),
				'type'          => 'toggle',
				'default_value' => $this->get_default_value( 'link' ),
				'description'   => __( 'If enabled, each Business\' listing will be linked to the listing on Yelp when clicked.', 'page-generator-pro' ),
			),
			'image_width'       => array(
				'label'         => __( 'Max. Image Width', 'page-generator-pro' ),
				'type'          => 'number',
				'min'           => 0,
				'max'           => 9999,
				'step'          => 1,
				'default_value' => $this->get_default_value( 'image_width' ),
				'description'   => __( 'The maximum width of each Business\' Image / Logo, in pixels. Zero = the full size image will be displayed.', 'page-generator-pro' ),
			),
			'image_alt_tag'     => array(
				'label'         => __( 'Image Alt Tag', 'page-generator-pro' ),
				'type'          => 'text',
				'default_value' => $this->get_default_value( 'image_alt_tag' ),
			),
			'display_order'     => array(
				'label'         => __( 'Display Order', 'page-generator-pro' ),
				'type'          => 'select_multiple',
				'values'        => array(
					'business_name'  => __( 'Business Name', 'page-generator-pro' ),
					'image'          => __( 'Image', 'page-generator-pro' ),
					'rating'         => __( 'Rating', 'page-generator-pro' ),
					'categories'     => __( 'Categories', 'page-generator-pro' ),
					'phone'          => __( 'Phone: International Format', 'page-generator-pro' ),
					'phone_local'    => __( 'Phone: Local Format', 'page-generator-pro' ),
					'address'        => __( 'Address: Full', 'page-generator-pro' ),
					'address1'       => __( 'Address: Line 1', 'page-generator-pro' ),
					'address2'       => __( 'Address: Line 2', 'page-generator-pro' ),
					'address3'       => __( 'Address: Line 3', 'page-generator-pro' ),
					'city'           => __( 'Address: City', 'page-generator-pro' ),
					'zip_code'       => __( 'Address: ZIP Code', 'page-generator-pro' ),
					'state'          => __( 'Address: State Code', 'page-generator-pro' ),
					'country'        => __( 'Address: Country Code', 'page-generator-pro' ),
					'distance_km'    => __( 'Distance: KM', 'page-generator-pro' ),
					'distance_miles' => __( 'Distance: Miles', 'page-generator-pro' ),
				),
				'default_value' => $this->get_default_value( 'display_order' ),
				'class'         => 'wpzinc-selectize-drag-drop',
				'description'   => __( 'Defines the content to display for each individual Business Listing, and the order to display it in.', 'page-generator-pro' ),
			),
			'display_alignment' => array(
				'label'       => __( 'Display Alignment', 'page-generator-pro' ),
				'type'        => 'select',
				'values'      => array(
					'vertical'   => __( 'Vertical', 'page-generator-pro' ),
					'horizontal' => __( 'Horizontal', 'page-generator-pro' ),
				),
				'condition'   => array(
					'key'        => 'output_type',
					'value'      => array( 'list', 'foobar' ),
					'comparison' => '==',
				),
				'description' => __( 'Defines the content display alignment for each individual Business Listing.', 'page-generator-pro' ),
			),
		);

	}

	/**
	 * Returns this shortcode / block's UI Tabs
	 *
	 * @since   2.5.1
	 */
	public function get_provider_tabs() {

		return array(
			'search-parameters' => array(
				'label'       => __( 'Search Parameters', 'page-generator-pro' ),
				'description' => __( 'Defines search query parameters to fetch business listings from Yelp.', 'page-generator-pro' ),
				'class'       => 'search',
				'fields'      => array(
					'term',
					'location',
					'radius',
					'minimum_rating',
					'locale',
					'price',
					'limit',
					'sort_by',
				),
			),
			'output'            => array(
				'label'       => __( 'Output', 'page-generator-pro' ),
				'description' => __( 'Defines what to output for each Yelp business listing.', 'page-generator-pro' ),
				'class'       => 'yelp',
				'fields'      => array(
					'output_type',
					'columns',
					'link',
					'image_alt_tag',
					'image_width',
					'display_order',
					'display_alignment',
				),
			),
		);

	}

	/**
	 * Returns this shortcode / block's Default Values
	 *
	 * @since   2.5.1
	 */
	public function get_provider_default_values() {

		return array(
			// Search Parameters.
			'term'              => '',
			'location'          => '',
			'radius'            => 0,
			'minimum_rating'    => 0,
			'locale'            => 'en_US', // get_locale() may return 'en' which is not valid for Yelp.
			'price'             => 0,
			'limit'             => 5,
			'sort_by'           => '',

			// Output.
			'output_type'       => 'list',
			'columns'           => 1,
			'link'              => false,
			'image_alt_tag'     => '%business_name%',
			'image_width'       => 0,
			'display_order'     => array(
				'business_name',
				'image',
				'rating',
				'categories',
				'phone',
				'address',
			),
			'display_alignment' => 'vertical',

			// Kept for backward compat.
			'image'             => 1,
			'rating'            => 1,
			'categories'        => 1,
			'phone'             => 1,
			'address'           => 1,
		);

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

		// For backward compat, if there's no display order defined but individual items
		// are enabled (e.g. image=1, rating=1 etc), use those attributes to build the display order.
		if ( ! isset( $atts['display_order'] ) || empty( $atts['display_order'] ) ) {
			// Always display the business name.
			$atts['display_order'] = 'business_name';

			foreach ( array( 'image', 'rating', 'categories', 'phone', 'address' ) as $display_item ) {
				if ( ! isset( $atts[ $display_item ] ) ) {
					continue;
				}
				if ( ! $atts[ $display_item ] ) {
					continue;
				}

				$atts['display_order'] .= ',' . $display_item;
			}

			// If we only have a business name, discard it so we fallback to the defaults below which will display everything.
			if ( $atts['display_order'] === 'business_name' ) {
				unset( $atts['display_order'] );
			}
		}

		// Parse attributes.
		$atts = $this->parse_atts( $atts );

		// Copy shortcode attributes to Yelp API arguments, removing some unused keys.
		$args     = array();
		$api_keys = array(
			'term',
			'location',
			'radius',
			'minimum_rating',
			'locale',
			'price',
			'limit',
			'sort_by',
		);
		foreach ( $api_keys as $api_key ) {
			if ( ! isset( $atts[ $api_key ] ) ) {
				continue;
			}
			if ( ! $atts[ $api_key ] ) {
				continue;
			}

			$args[ $api_key ] = $atts[ $api_key ];
		}

		// If a Yelp API key has been specified, use it instead of the class default.
		$yelp_api_key = $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-integrations', 'yelp_api_key' );
		if ( ! empty( $yelp_api_key ) ) {
			$this->set_api_key( $yelp_api_key );
		}

		// Send request to Yelp API.
		$results = $this->businesses_search( $args );

		// Check for errors from the Yelp API.
		if ( is_wp_error( $results ) ) {
			return $this->add_dynamic_element_error_and_return( $results, $atts );
		}

		// Define CSS classes for the container.
		$css = array(
			'page-generator-pro-' . $this->get_name(),
			'page-generator-pro-' . $this->get_name() . '-columns-' . $atts['columns'],
			'page-generator-pro-' . $this->get_name() . '-' . str_replace( '_', '-', $atts['output_type'] ),
			'page-generator-pro-' . $this->get_name() . '-' . str_replace( '_', '-', $atts['display_alignment'] ),
		);

		// Start HTML.
		$html = '<div class="' . implode( ' ', $css ) . '">';

		// Build HTML based on the output type.
		switch ( $atts['output_type'] ) {
			case 'table':
				$html .= $this->get_table_output( $results, $atts );
				break;

			default:
				$html .= $this->get_list_output( $results, $atts );
				break;
		}

		/**
		 * Filter the Yelp Shortcode HTML output, before returning.
		 *
		 * @since   1.0.0
		 *
		 * @param   string  $html   HTML Output.
		 * @param   array   $atts   Shortcode Attributes.
		 */
		$html = apply_filters( 'page_generator_pro_shortcode_yelp', $html, $atts );

		// Add Yelp logo, if we haven't yet output it.
		// This is required to meet the display requirements below, which is why this is done after filtering.
		// http://www.yelp.co.uk/developers/getting_started/display_requirements.
		if ( ! $this->yelp_logo_output ) {
			$html                  .= '<a href="https://www.yelp.com" rel="nofollow noreferrer noopener" target="_blank"><img src="https://s3-media1.ak.yelpcdn.com/assets/2/www/img/55e2efe681ed/developers/yelp_logo_50x25.png" /></a>';
			$this->yelp_logo_output = true;
		}

		$html .= '</div>';

		// Return.
		return $html;

	}

	/**
	 * Returns HTML for Yelp Business Listings in list format
	 *
	 * @since   2.8.3
	 *
	 * @param   array $results    Business Listings.
	 * @param   array $atts       Shortcode Attributes.
	 * @return  string              HTML
	 */
	private function get_list_output( $results, $atts ) {

		$html = '';

		// Iterate through results, building HTML.
		foreach ( $results as $count => $business ) {

			$html .= '<div class="business">';
			$index = 0;

			// Iterate through the display order for the business listing's attributes.
			foreach ( $atts['display_order'] as $index => $display_item ) {
				// If Display Alignment is horizontal, and we're on the second item,
				// wrap the remaining items in a div.
				if ( $atts['display_alignment'] === 'horizontal' && $index === 1 ) {
					$html .= '<div class="item">';
				}

				// Get Business Listing Attribute.
				$html .= $this->get_business_listing_attribute_output( $atts, $business, $display_item, 'div' );
			}

			// If Display Alignment is horizontal, and we output at least two attributes
			// for the business, close the wrap on the remaining items.
			if ( $atts['display_alignment'] === 'horizontal' && $index > 0 ) {
				$html .= '</div>';
			}

			$html .= '</div>';

			// Check if limit reached.
			if ( ( $count + 1 ) === (int) $atts['limit'] ) {
				break;
			}
		}

		return $html;

	}

	/**
	 * Returns HTML for Yelp Business Listings in table format
	 *
	 * @since   2.8.3
	 *
	 * @param   array $results    Business Listings.
	 * @param   array $atts       Shortcode Attributes.
	 * @return  string              HTML
	 */
	private function get_table_output( $results, $atts ) {

		// Build table headers.
		$html = '<table>
            <thead>
                <tr>';

		foreach ( $atts['display_order'] as $display_item ) {
			switch ( $display_item ) {
				/**
				 * Business Name
				 */
				case 'business_name':
					$html .= '<th>' . __( 'Business Name', 'page-generator-pro' ) . '</th>';
					break;

				/**
				 * Image
				 */
				case 'image':
					// Deliberately ignored; the image is displayed with the Business Name.
					break;

				/**
				 * Rating
				 */
				case 'rating':
					$html .= '<th>' . __( 'Rating', 'page-generator-pro' ) . '</th>';
					break;

				/**
				 * Categories
				 */
				case 'categories':
					$html .= '<th>' . __( 'Categories', 'page-generator-pro' ) . '</th>';
					break;

				/**
				 * Phone
				 * Phone (Local Format)
				 */
				case 'phone':
				case 'phone_local':
					$html .= '<th>' . __( 'Phone Number', 'page-generator-pro' ) . '</th>';
					break;

				/**
				 * Address
				 */
				case 'address':
					$html .= '<th>' . __( 'Address', 'page-generator-pro' ) . '</th>';
					break;

				/**
				 * Address Line 1
				 */
				case 'address1':
					$html .= '<th>' . __( 'Address Line 1', 'page-generator-pro' ) . '</th>';
					break;

				/**
				 * Address Line 2
				 */
				case 'address2':
					$html .= '<th>' . __( 'Address Line 2', 'page-generator-pro' ) . '</th>';
					break;

				/**
				 * Address Line 3
				 */
				case 'address3':
					$html .= '<th>' . __( 'Address Line 3', 'page-generator-pro' ) . '</th>';
					break;

				/**
				 * City
				 */
				case 'city':
					$html .= '<th>' . __( 'City', 'page-generator-pro' ) . '</th>';
					break;

				/**
				 * ZIP Code
				 */
				case 'zip_code':
					$html .= '<th>' . __( 'ZIP Code', 'page-generator-pro' ) . '</th>';
					break;

				/**
				 * State
				 */
				case 'state':
					$html .= '<th>' . __( 'State', 'page-generator-pro' ) . '</th>';
					break;

				/**
				 * Country
				 */
				case 'country':
					$html .= '<th>' . __( 'Country', 'page-generator-pro' ) . '</th>';
					break;

				/**
				 * Distance
				 */
				case 'distance_km':
				case 'distance_miles':
					$html .= '<th>' . __( 'Distance', 'page-generator-pro' ) . '</th>';
					break;

			}
		}

		$html .= '
                </tr>
            </thead>
            <tbody>';

		// Iterate through results, building HTML.
		foreach ( $results as $count => $business ) {

			$html .= '<tr class="business">';

			// Iterate through the display order for the business listing's attributes.
			foreach ( $atts['display_order'] as $display_item ) {
				// Get Business Listing Attribute.
				$html .= $this->get_business_listing_attribute_output( $atts, $business, $display_item, 'td' );
			}

			// Close row.
			$html .= '</tr>';

			// Check if limit reached.
			if ( ( $count + 1 ) === (int) $atts['limit'] ) {
				break;
			}
		}

		// Close table.
		$html .= '</tbody>
            </table>';

		// Return.
		return $html;

	}

	/**
	 * Returns HTML for the given Business' Display Item and HTML Tag - for example,
	 * the Business Name in a <div> or the Business Image in a <td>
	 *
	 * @since   2.9.6
	 *
	 * @param   array  $atts           Attributes.
	 * @param   object $business       Business.
	 * @param   string $display_item   Display Item.
	 * @param   string $html_tag       HTML Tag (div,td).
	 * @return  string                  HTML
	 */
	private function get_business_listing_attribute_output( $atts, $business, $display_item, $html_tag ) {

		switch ( $display_item ) {
			/**
			 * Business Name
			 */
			case 'business_name':
				$html = '<' . $html_tag . ' class="name">';

				// Add link if required.
				if ( $atts['link'] ) {
					$html .= '<a href="' . $business->url . '" target="_blank" rel="nofollow noopener">';
				}

				$html .= $business->name;

				// Add image if this is a table cell and the image needs to be output.
				if ( $html_tag === 'td' && in_array( 'image', $atts['display_order'], true ) ) {
					// Define image width if specified.
					$image_width = ( ( $atts['image_width'] ) ? ' width="' . $atts['image_width'] . '"' : '' );
					$html       .= '<img src="' . $business->image_url . '"' . $image_width . ' alt="' . $this->replace_yelp_variables( $atts['image_alt_tag'], $business ) . '" />';
				}

				// Close link if required.
				if ( $atts['link'] ) {
					$html .= '</a>';
				}

				$html .= '</' . $html_tag . '>';

				return $html;

			/**
			 * Image
			 */
			case 'image':
				// Ignore for table cell.
				if ( $html_tag === 'td' ) {
					return '';
				}

				// Define image width if specified.
				$image_width = ( ( $atts['image_width'] ) ? ' width="' . $atts['image_width'] . '"' : '' );

				$html = '<' . $html_tag . ' class="image">';

				// Add link if required.
				if ( $atts['link'] ) {
					$html .= '<a href="' . $business->url . '" target="_blank" rel="nofollow noopener">';
				}

				$html .= '<img src="' . $business->image_url . '"' . $image_width . ' alt="' . $this->replace_yelp_variables( $atts['image_alt_tag'], $business ) . '" />';

				// Close link if required.
				if ( $atts['link'] ) {
					$html .= '</a>';
				}

				$html .= '</' . $html_tag . '>';

				return $html;

			/**
			 * Rating
			 */
			case 'rating':
				$html = '<' . $html_tag . ' class="rating">';

				// Add link if required.
				if ( $atts['link'] ) {
					$html .= '<a href="' . $business->url . '" target="_blank" rel="nofollow noopener">';
				}

				$html .= '
                	<div class="rating-stars rating-stars-' . str_replace( '.', '-', $business->rating ) . '"></div>
                    <div class="rating-text">' . $business->review_count . ' ' . ( $business->review_count === 1 ? __( 'review', 'page-generator-pro' ) : __( 'reviews', 'page-generator-pro' ) ) . '</div>';

				// Close link if required.
				if ( $atts['link'] ) {
					$html .= '</a>';
				}

				$html .= '</' . $html_tag . '>';

				return $html;

			/**
			 * Categories
			 */
			case 'categories':
				$html = '<' . $html_tag . ' class="categories">';

				$total_categories = count( $business->categories );
				foreach ( $business->categories as $category_count => $category ) {
					$html .= $category->title;
					if ( ( $category_count + 1 ) !== (int) $total_categories ) {
						$html .= ', ';
					}
				}

				$html .= '</' . $html_tag . '>';

				return $html;

			/**
			 * Phone
			 */
			case 'phone':
				return '<' . $html_tag . ' class="phone">' . $business->phone . '</' . $html_tag . '>';

			/**
			 * Phone (Local Format)
			 */
			case 'phone_local':
				return '<' . $html_tag . ' class="phone-local">' . $business->display_phone . '</' . $html_tag . '>';

			/**
			 * Address
			 */
			case 'address':
				$html = '<' . $html_tag . ' class="address">';

				// Address.
				$total_address_lines = count( $business->location->display_address );
				foreach ( $business->location->display_address as $address_count => $address ) {
					$html .= $address;
					if ( ( $address_count + 1 ) !== (int) $total_address_lines ) {
						$html .= ', ';
					}
				}

				$html .= '</' . $html_tag . '>';

				return $html;

			/**
			 * Address Lines
			 */
			case 'address1':
			case 'address2':
			case 'address3':
			case 'city':
			case 'zip_code':
			case 'country':
			case 'state':
				return '<' . $html_tag . ' class="' . $display_item . '">' . $business->location->{ $display_item } . '</' . $html_tag . '>';

			/**
			 * Distance (KM)
			 */
			case 'distance_km':
				return '<' . $html_tag . ' class="distance-km">' . round( ( $business->distance / 1000 ), 2 ) . ' km</' . $html_tag . '>';

			/**
			 * Distance (Miles)
			 */
			case 'distance_miles':
				return '<' . $html_tag . ' class="distance-miles">' . round( ( ( $business->distance / 1000 ) / 1.6 ), 2 ) . ' miles</' . $html_tag . '>';

			default:
				return '';
		}

	}

	/**
	 * Replaces Post variables with the Post's data.
	 *
	 * @since   2.6.3
	 *
	 * @param   string $text       Text.
	 * @param   object $business   Yelp Business Listing.
	 * @return  string              Text
	 */
	private function replace_yelp_variables( $text, $business ) {

		// Build categories.
		$categories = array();
		if ( isset( $business->categories ) && is_array( $business->categories ) && count( $business->categories ) > 0 ) {
			foreach ( $business->categories as $category ) {
				$categories[] = $category->title;
			}
		}

		// Define search and replacements.
		$searches = array(
			'%business_name%',
			'%business_address1%',
			'%business_address2%',
			'%business_address3%',
			'%business_city%',
			'%business_zip_code%',
			'%business_country%',
			'%business_state%',
			'%business_display_address%',
			'%business_phone%',
			'%business_display_phone%',
			'%business_distance%',
			'%business_categories%',
		);

		$replacements = array(
			$business->name,
			( isset( $business->location->address1 ) ? $business->location->address1 : '' ),
			( isset( $business->location->address2 ) ? $business->location->address2 : '' ),
			( isset( $business->location->address3 ) ? $business->location->address3 : '' ),
			( isset( $business->location->city ) ? $business->location->city : '' ),
			( isset( $business->location->zip_code ) ? $business->location->zip_code : '' ),
			( isset( $business->location->country ) ? $business->location->country : '' ),
			( isset( $business->location->state ) ? $business->location->state : '' ),
			implode( ', ', $business->location->display_address ),
			$business->phone,
			$business->display_phone,
			$business->distance,
			implode( ', ', $categories ),
		);

		// Perform search and replace.
		$text = str_ireplace( $searches, $replacements, $text );

		// Return.
		return $text;

	}

	/**
	 * Performs a GET request to /businesses/search
	 *
	 * @since   1.4.5
	 *
	 * @param   array $args               Arguments.
	 *        'term'              => (string) Search Terms.
	 *        'location'          => (string) Location.
	 *        'radius'            => (int) Radius, in Miles (max: 25).
	 *        'minimum_rating'    => (int) Minimum Rating.
	 *        'locale'            => (string) Locale.
	 *        'price'             => (int) Price Level (0 - 4).
	 *        'limit'             => (int) Number of Listings.
	 *        'sort_by'           => (string) Sort Listings By.
	 * @return  WP_Error|array
	 */
	public function businesses_search( $args ) {

		// Remove any zero or false arguments.
		foreach ( $args as $key => $value ) {
			if ( empty( $value ) ) {
				unset( $args[ $key ] );
			}
		}

		// If a minimum rating is specified, increase the limit so we can sort through
		// the results to extract those that match the minimum rating.
		if ( isset( $args['minimum_rating'] ) ) {
			// Store minimum rating and limit.
			$minimum_rating = $args['minimum_rating'];
			$limit          = $args['limit'];

			// Set limit to a high number, and remove the minimum rating argument.
			$args['limit'] = 50; // Maximum supported.
			unset( $args['minimum_rating'] );
		}

		// If a radius is set, ensure it doesn't exceed the maximum permitted, and convert it to metres.
		if ( isset( $args['radius'] ) ) {
			if ( $args['radius'] > 20 ) {
				$args['radius'] = 20;
			}

			// Convert radius to metres.
			$args['radius'] = $args['radius'] * 1609;
		}

		// Set headers.
		$this->set_headers(
			array(
				'Authorization' => 'Bearer ' . $this->api_key,
			)
		);

		// Get results.
		$results = $this->response(
			$this->get( 'businesses/search', $args )
		);

		// Bail if no results were found.
		if ( is_wp_error( $results ) ) {
			return $results;
		}

		// If there's no minimum rating requirement, return the results.
		if ( ! isset( $minimum_rating ) ) {
			return $results->businesses;
		}

		// Build results by minimum rating.
		$filtered_results = array();
		$count            = 0;
		foreach ( $results->businesses as $result ) {
			// Skip if the rating doesn't match our minimum.
			if ( $result->rating < $minimum_rating ) {
				continue;
			}

			// Add to filtered results.
			$filtered_results[] = $result;
			++$count;

			// If we hit the limit, exit the loop.
			if ( isset( $limit ) && $count == $limit ) { // phpcs:ignore Universal.Operators.StrictComparisons.LooseEqual
				break;
			}
		}

		// Return results.
		return $filtered_results;

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
	public function response( $response ) {

		// If the response is an error, return it.
		if ( is_wp_error( $response ) ) {
			return new WP_Error(
				'page_generator_pro_yelp_error',
				$response->get_error_message()
			);
		}

		// Bail if an error exists.
		if ( isset( $response->error ) ) {
			$message = $response->error->code . ': ';
			if ( isset( $response->error->field ) ) {
				$message .= $response->error->field . ': ' . $response->error->description; // @phpstan-ignore-line
			} else {
				$message .= $response->error->description;
			}

			return new WP_Error(
				'page_generator_pro_yelp_error',
				$message
			);
		}

		// Bail if no results.
		if ( ! $response->total ) {
			return new WP_Error(
				'page_generator_pro_yelp_error',
				__( 'No results found', 'page-generator-pro' )
			);
		}

		// Return successful response data.
		return $response;

	}

}
