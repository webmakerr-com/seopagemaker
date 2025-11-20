<?php
/**
 * Google Places API Class.
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Fetch business listings from Google Places.
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 1.0.0
 */
class Page_Generator_Pro_Google_Places extends Page_Generator_Pro_API {

	use Page_Generator_Pro_Integration_Trait;
	use Page_Generator_Pro_Shortcode_Trait;

	/**
	 * Holds the base class object.
	 *
	 * @since   5.2.8
	 *
	 * @var     object
	 */
	public $base;

	/**
	 * Holds the programmatic name of this integration.
	 *
	 * @since   5.2.8
	 *
	 * @var     string
	 */
	public $name = 'google-places';

	/**
	 * Holds the API endpoint
	 *
	 * @since   5.2.8
	 *
	 * @var     string
	 */
	public $api_endpoint = 'https://places.googleapis.com/v1';

	/**
	 * Holds the user's API key
	 *
	 * @since   5.2.8
	 *
	 * @var     string
	 */
	public $api_key = '';

	/**
	 * Constructor.
	 *
	 * @since   5.2.8
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
	 * @since   5.2.8
	 *
	 * @return  string
	 */
	public function get_title() {

		return __( 'Google Places', 'page-generator-pro' );

	}

	/**
	 * Returns settings fields and their values to display on:
	 * - Settings > Integrations
	 *
	 * @since   5.2.8
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
					esc_html__( 'You\'ll need to use your own Google Places API key when displaying Google Places content.', 'page-generator-pro' ),
					'<a href="' . esc_attr( $this->base->plugin->documentation_url ) . '/settings-integration/#google-places" target="_blank" rel="noopener">' . esc_html__( 'Click here', 'page-generator-pro' ) . '</a>',
					esc_html__( 'to read the step by step documentation to do this.', 'page-generator-pro' )
				),
			),
		);

		return $settings_fields;

	}

	/**
	 * Returns this shortcode / block's description.
	 *
	 * @since   5.2.8
	 *
	 * @return string
	 */
	public function get_description() {

		return __( 'Displays places from Google Business based on the given search parameters.', 'page-generator-pro' );

	}

	/**
	 * Returns this shortcode / block's icon.
	 *
	 * @since   5.2.8
	 *
	 * @return  string
	 */
	public function get_icon() {

		return '_modules/dashboard/feather/map-pin.svg';

	}

	/**
	 * Returns this shortcode / block's TinyMCE modal width and height.
	 *
	 * @since   5.2.8
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
	 * @since   5.2.8
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
	 * @since   5.2.8
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
			'latitude'             => array(
				'type'    => 'string',
				'default' => ( ! $this->get_default_value( 'latitude' ) ? '' : $this->get_default_value( 'latitude' ) ),
			),
			'longitude'            => array(
				'type'    => 'string',
				'default' => ( ! $this->get_default_value( 'longitude' ) ? '' : $this->get_default_value( 'longitude' ) ),
			),
			'radius'               => array(
				'type'    => 'number',
				'default' => $this->get_default_value( 'radius' ),
			),
			'minimum_rating'       => array(
				'type'    => 'string',
				'default' => ( ! $this->get_default_value( 'minimum_rating' ) ? '' : $this->get_default_value( 'minimum_rating' ) ),
			),
			'language'             => array(
				'type'    => 'string',
				'default' => ( ! $this->get_default_value( 'language' ) ? '' : $this->get_default_value( 'language' ) ),
			),
			'limit'                => array(
				'type'    => 'number',
				'default' => $this->get_default_value( 'limit' ),
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
			'latitude'          => array(
				'label'       => __( 'Latitude', 'page-generator-pro' ),
				'type'        => 'autocomplete',
				'values'      => $keywords,
				'default'     => $this->get_default_value( 'latitude' ),
				'description' => __( 'To specify a radius, a latitude must be specified.', 'page-generator-pro' ),
			),
			'longitude'         => array(
				'label'       => __( 'Longitude', 'page-generator-pro' ),
				'type'        => 'autocomplete',
				'values'      => $keywords,
				'default'     => $this->get_default_value( 'longitude' ),
				'description' => __( 'To specify a radius, a longitude must be specified.', 'page-generator-pro' ),
			),
			'radius'            => array(
				'label'         => __( 'Radius', 'page-generator-pro' ),
				'type'          => 'number',
				'min'           => 1,
				'max'           => 50000,
				'step'          => 1,
				'default_value' => $this->get_default_value( 'radius' ),
				'description'   => __( 'The maximum radius, in miles, from the Latitude and Longitude fields above to search places for.', 'page-generator-pro' ),
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
				'description'   => __( 'The minimum rating a place must have to be displayed.', 'page-generator-pro' ),
			),
			'language'          => array(
				'label'         => __( 'Language', 'page-generator-pro' ),
				'type'          => 'select',
				'values'        => array_merge(
					array(
						'' => __( '(Visitor\'s own language)', 'page-generator-pro' ),
					),
					$this->base->get_class( 'common' )->get_languages()
				),
				'default_value' => $this->get_default_value( 'language' ),
			),
			'limit'             => array(
				'label'         => __( 'Number of Places', 'page-generator-pro' ),
				'type'          => 'number',
				'min'           => 1,
				'max'           => 50,
				'step'          => 1,
				'default_value' => $this->get_default_value( 'limit' ),
				'description'   => __( 'The maximum number of places to return.', 'page-generator-pro' ),
			),

			'output_type'       => array(
				'label'         => __( 'Output Type', 'page-generator-pro' ),
				'type'          => 'select',
				'class'         => 'wpzinc-conditional',
				'data'          => array(
					// .components-panel is Gutenberg.
					// .google-places is TinyMCE.
					'container' => '.components-panel, .google-places',
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
				'description'   => __( 'If enabled, each place will be linked to the web site URL when clicked.', 'page-generator-pro' ),
			),
			'display_order'     => array(
				'label'         => __( 'Display Order', 'page-generator-pro' ),
				'type'          => 'select_multiple',
				'values'        => $this->search_fields(),
				'default_value' => $this->get_default_value( 'display_order' ),
				'class'         => 'wpzinc-selectize-drag-drop',
				'description'   => __( 'Defines the content to display for each individual place, and the order to display it in.', 'page-generator-pro' ),
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
				'description' => __( 'Defines the content display alignment for each individual place.', 'page-generator-pro' ),
			),
		);

	}

	/**
	 * Returns this shortcode / block's UI Tabs
	 *
	 * @since   5.2.8
	 */
	public function get_provider_tabs() {

		return array(
			'search-parameters' => array(
				'label'       => __( 'Search Parameters', 'page-generator-pro' ),
				'description' => __( 'Defines search query parameters to fetch listings from Google Places.', 'page-generator-pro' ),
				'class'       => 'search',
				'fields'      => array(
					'term',
					'location',
					'latitude',
					'longitude',
					'radius',
					'minimum_rating',
					'language',
					'limit',
				),
			),
			'output'            => array(
				'label'       => __( 'Output', 'page-generator-pro' ),
				'description' => __( 'Defines what to output for each Google Places listing.', 'page-generator-pro' ),
				'class'       => 'google-places',
				'fields'      => array(
					'output_type',
					'columns',
					'link',
					'display_order',
					'display_alignment',
				),
			),
		);

	}

	/**
	 * Returns this shortcode / block's Default Values
	 *
	 * @since   5.2.8
	 */
	public function get_provider_default_values() {

		return array(
			// Search Parameters.
			'term'              => '',
			'location'          => '',
			'latitude'          => '',
			'longitude'         => '',
			'radius'            => 0,
			'minimum_rating'    => '',
			'language'          => '',
			'limit'             => 5,

			// Output.
			'output_type'       => 'list',
			'columns'           => 1,
			'link'              => false,
			'display_order'     => array(
				'displayName',
				'rating',
			),
			'display_alignment' => 'vertical',
		);

	}

	/**
	 * Returns this shortcode / block's output
	 *
	 * @since   5.2.8
	 *
	 * @param  array $atts   Shortcode Attributes.
	 * @return string          Output
	 */
	public function render( $atts ) {

		// Parse attributes.
		$atts = $this->parse_atts( $atts );

		// If a Google Places API key has been specified, use it now.
		$google_places_api_key = $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-integrations', 'google_places_api_key' );
		if ( ! empty( $google_places_api_key ) ) {
			// Set API Key.
			$this->set_api_key( $google_places_api_key );
		}

		// Send request to Google Places API.
		$results = $this->search( $atts );

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

		// Finish HTML.
		$html .= '</div>';

		/**
		 * Filter the Google Places HTML output, before returning.
		 *
		 * @since   5.2.8
		 *
		 * @param   string  $html   HTML Output.
		 * @param   array   $atts   Shortcode Attributes.
		 */
		$html = apply_filters( 'page_generator_pro_shortcode_google_places', $html, $atts );

		// Return.
		return $html;

	}

	/**
	 * Returns the fields for the Google Places API.
	 *
	 * @since   5.2.8
	 *
	 * @return  array
	 */
	public function search_fields() {

		return array(
			// Keys must be compatible with https://developers.google.com/maps/documentation/places/web-service/text-search.
			'formattedAddress'         => __( 'Address', 'page-generator-pro' ),
			'adrFormatAddress'         => __( 'Address with Country Code', 'page-generator-pro' ),
			'googleMapsUri'            => __( 'Google Maps URI', 'page-generator-pro' ),
			'internationalPhoneNumber' => __( 'International Phone Number', 'page-generator-pro' ),
			'nationalPhoneNumber'      => __( 'National Phone Number', 'page-generator-pro' ),
			'priceLevel'               => __( 'Price Level', 'page-generator-pro' ),
			'displayName'              => __( 'Place Name', 'page-generator-pro' ),
			'priceRange'               => __( 'Price Range', 'page-generator-pro' ),
			'primaryTypeDisplayName'   => __( 'Place Type', 'page-generator-pro' ),
			'rating'                   => __( 'Rating', 'page-generator-pro' ),
			'regularOpeningHours'      => __( 'Regular Opening Hours', 'page-generator-pro' ),
			'reviewSummary'            => __( 'Review Summary', 'page-generator-pro' ),
			'websiteUri'               => __( 'Website URI', 'page-generator-pro' ),
		);

	}

	/**
	 * Performs a search for Google Places listings.
	 *
	 * @since   5.2.8
	 *
	 * @param   array $atts   Shortcode Attributes.
	 */
	private function search( $atts ) {

		// Build fields.
		$fields = array();
		foreach ( $atts['display_order'] as $field ) {
			$fields[] = 'places.' . $field;

			// Some fields need additional fields to be included.
			switch ( $field ) {
				case 'rating':
					$fields[] = 'places.userRatingCount';
					break;

				case 'displayName':
					$fields[] = 'places.websiteUri';
					break;

			}
		}

		// Remove duplicates e.g. the user may have specified websiteUri, but we added it because displayName needed it too.
		$fields = array_unique( $fields );

		// Set Headers.
		$this->set_headers(
			array(
				'X-Goog-Api-Key'   => $this->api_key,
				'X-Goog-FieldMask' => implode( ',', $fields ),
				'Content-Type'     => 'application/json',
			)
		);

		// Build parameters.
		$params = array(
			'textQuery' => $atts['term'] . ' in ' . $atts['location'],
		);

		// Add language constraint.
		if ( $atts['language'] ) {
			$params['languageCode'] = $atts['language'];
		}

		// Add radius constraint.
		if ( $atts['latitude'] && $atts['longitude'] && $atts['radius'] ) {
			$params['locationBias'] = array(
				'circle' => array(
					'center' => array(
						'latitude'  => (float) $atts['latitude'],
						'longitude' => (float) $atts['longitude'],
					),
					'radius' => (float) $atts['radius'],
				),
			);
		}

		// Add minimum rating constraint.
		if ( $atts['minimum_rating'] ) {
			$params['minRating'] = $atts['minimum_rating'];
		}

		// Add limit constraint.
		if ( $atts['limit'] ) {
			$params['pageSize'] = (int) $atts['limit'];
		}

		// Send request to Google Places API.
		$results = $this->post( 'places:searchText', $params );

		// Bail if an error occurred.
		if ( is_wp_error( $results ) ) {
			return $results;
		}

		// Return places.
		return $results->places;

	}

	/**
	 * Returns HTML for Google Places Listings in list format
	 *
	 * @since   5.2.8
	 *
	 * @param   array $results    Listings.
	 * @param   array $atts       Shortcode Attributes.
	 * @return  string              HTML
	 */
	private function get_list_output( $results, $atts ) {

		$html = '';

		// Iterate through results, building HTML.
		foreach ( $results as $count => $place ) {

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
				$html .= $this->get_place_attribute_output( $atts, $place, $display_item, 'div' );
			}

			// If Display Alignment is horizontal, and we output at least two attributes
			// for the business, close the wrap on the remaining items.
			if ( $atts['display_alignment'] === 'horizontal' && $index > 0 ) {
				$html .= '</div>';
			}

			$html .= '</div>';
		}

		return $html;

	}

	/**
	 * Returns HTML for Google Places Listings in table format
	 *
	 * @since   5.2.8
	 *
	 * @param   array $results    Business Listings.
	 * @param   array $atts       Shortcode Attributes.
	 * @return  string              HTML
	 */
	private function get_table_output( $results, $atts ) {

		// Get search fields.
		$search_fields = $this->search_fields();

		// Build table headers.
		$html = '<table>
            <thead>
                <tr>';

		foreach ( $atts['display_order'] as $display_item ) {
			// Skip if this field doesn't exist.
			if ( ! isset( $search_fields[ $display_item ] ) ) {
				continue;
			}

			// Add column header.
			$html .= '<th>' . $search_fields[ $display_item ] . '</th>';
		}

		$html .= '
                </tr>
            </thead>
            <tbody>';

		// Iterate through results, building HTML.
		foreach ( $results as $count => $place ) {

			$html .= '<tr class="business">';

			// Iterate through the display order for the place's attributes.
			foreach ( $atts['display_order'] as $display_item ) {
				$html .= $this->get_place_attribute_output( $atts, $place, $display_item, 'td' );
			}

			// Close row.
			$html .= '</tr>';
		}

		// Close table.
		$html .= '</tbody>
            </table>';

		// Return.
		return $html;

	}

	/**
	 * Returns HTML for the given Place's Display Item and HTML Tag - for example,
	 * the Place Name in a <div> or the Place Image in a <td>
	 *
	 * @since   5.2.8
	 *
	 * @param   array  $atts           Attributes.
	 * @param   object $place          Place.
	 * @param   string $display_item   Display Item.
	 * @param   string $html_tag       HTML Tag (div,td).
	 * @return  string                  HTML
	 */
	private function get_place_attribute_output( $atts, $place, $display_item, $html_tag ) {

		// Start HTML.
		$html = '<' . $html_tag . ' class="' . strtolower( preg_replace( '/(?<!^)[A-Z]/', '-$0', $display_item ) ) . '">';

		// If the property doesn't exist for the place, return a blank string.
		if ( ! isset( $place->{$display_item} ) ) {
			$html .= '&nbsp;';
			$html .= '</' . $html_tag . '>';
			return $html;
		}

		// phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		switch ( $display_item ) {
			/**
			 * Place Name
			 */
			case 'displayName':
				// Add link if required.
				if ( $atts['link'] ) {
					$html .= '<a href="' . $place->websiteUri . '" target="_blank" rel="nofollow noopener">';
				}

				$html .= $place->displayName->text;

				// Close link if required.
				if ( $atts['link'] ) {
					$html .= '</a>';
				}
				break;

			/**
			 * Rating
			 */
			case 'rating':
				// Add link if required.
				if ( $atts['link'] ) {
					$html .= '<a href="' . $place->websiteUri . '" target="_blank" rel="nofollow noopener">';
				}

				// Round rating to nearest 0.5 between 0 and 5.
				$rating = round( $place->rating * 2 ) / 2;
				$rating = max( 0, min( 5, $rating ) );

				$html .= '
                	<div class="rating-stars rating-stars-' . str_replace( '.', '-', $rating ) . '">' . $place->rating . '</div>
                    <div class="rating-text">' . $place->userRatingCount . ' ' . ( $place->userRatingCount === 1 ? __( 'review', 'page-generator-pro' ) : __( 'reviews', 'page-generator-pro' ) ) . '</div>';

				// Close link if required.
				if ( $atts['link'] ) {
					$html .= '</a>';
				}
				break;

			/**
			 * Google Maps URI
			 */
			case 'googleMapsUri':
				$html .= '<a href="' . $place->{$display_item} . '" target="_blank" rel="nofollow noopener">' . esc_html__( 'View Map', 'page-generator-pro' ) . '</a>';
				break;

			/**
			 * Website URI
			 */
			case 'websiteUri':
				$html .= '<a href="' . $place->{$display_item} . '" target="_blank" rel="nofollow noopener">' . esc_html__( 'View Website', 'page-generator-pro' ) . '</a>';
				break;

			/**
			 * Price Level
			 */
			case 'priceLevel':
				$html .= $place->{$display_item};
				break;

			/**
			 * Price Range
			 */
			case 'priceRange':
				$html .= $place->priceRange->startPrice->units . ' - ' . $place->priceRange->endPrice->units . ' ' . $place->priceRange->startPrice->currencyCode;
				break;

			/**
			 * Primary Type Display Name
			 */
			case 'primaryTypeDisplayName':
				$html .= $place->{$display_item}->text;
				break;

			/**
			 * Regular Opening Hours
			 */
			case 'regularOpeningHours':
				foreach ( $place->regularOpeningHours->weekdayDescriptions as $weekday_description ) {
					$html .= $weekday_description . '<br />';
				}
				break;

			/**
			 * Review Summary
			 */
			case 'reviewSummary':
				$html .= $place->{$display_item}->text->text;
				break;

			/**
			 * Default
			 */
			default:
				$html .= $place->{$display_item};
				break;

		}
		// phpcs:enable

		// Close tag.
		$html .= '</' . $html_tag . '>';

		// Return.
		return $html;

	}

	/**
	 * Inspects the response from the API call, returning an error
	 * or data
	 *
	 * @since   5.2.8
	 *
	 * @param   WP_Error|object $response   Response.
	 * @return  WP_Error|object
	 */
	public function response( $response ) {

		// If the response is an error, return it.
		if ( is_wp_error( $response ) ) {
			return new WP_Error(
				'page_generator_pro_google_places_error',
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
				'page_generator_pro_google_places_error',
				$message
			);
		}

		// Bail if no results.
		if ( ! $response->total ) {
			return new WP_Error(
				'page_generator_pro_google_places_error',
				__( 'No results found', 'page-generator-pro' )
			);
		}

		// Return successful response data.
		return $response;

	}

}
