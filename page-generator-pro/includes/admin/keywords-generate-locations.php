<?php
/**
 * Generate Locations Class
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Interacts with third party APIs to build
 * location keywords.
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 5.0.0
 */
class Page_Generator_Pro_Keywords_Generate_Locations {

	/**
	 * Holds the base object.
	 *
	 * @since   5.0.0
	 *
	 * @var     object
	 */
	public $base;

	/**
	 * Constructor.
	 *
	 * @since   5.0.0
	 *
	 * @param   object $base    Base Plugin Class.
	 */
	public function __construct( $base ) {

		// Store base class.
		$this->base = $base;

	}

	/**
	 * Return available location providers supported by this class.
	 *
	 * @since   5.0.0
	 *
	 * @return  array   Location Providers
	 */
	public function get_providers() {

		$providers = array();

		/**
		 * Defines the available location providers supported by this Plugin
		 *
		 * @since   5.0.0
		 *
		 * @param   array   $providers  Location Service Providers.
		 */
		$providers = apply_filters( 'page_generator_pro_keywords_generate_locations_get_providers', $providers );

		// Return filtered results.
		return $providers;

	}

	/**
	 * Returns settings fields for all location service providers.
	 *
	 * @since   5.0.0
	 *
	 * @return  array   Location service providers settings
	 */
	public function get_providers_settings_fields() {

		$settings_fields = array();

		/**
		 * Defines each location provider's settings to display at Settings > Generate Locations
		 *
		 * @since   5.0.0
		 *
		 * @param   array   $settings  Location Providers Settings Fields.
		 */
		$settings_fields = apply_filters( 'page_generator_pro_keywords_generate_locations_get_providers_settings_fields', $settings_fields );

		// Return filtered results.
		return $settings_fields;

	}

	/**
	 * Fetches locations by area based on the given criteria.
	 *
	 * @since   5.0.0
	 *
	 * @param   array $args   Location arguments.
	 *
	 * @return  WP_Error|string
	 */
	public function generate_locations_by_area( $args ) {

		// Get provider.
		$provider = $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-generate-locations', 'provider' );
		$language = $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-generate-locations', 'language' );

		/**
		 * Fetches locations by area based on the given criteria.
		 *
		 * @since   5.0.0
		 */
		$result = apply_filters( 'page_generator_pro_keywords_generate_locations_by_area_' . $provider, $args, 'locations_area', 250, $language );

		// Return.
		return $result;

	}

	/**
	 * Fetches locations by radius based on the given criteria.
	 *
	 * @since   5.0.0
	 *
	 * @param   array $args   Location arguments.
	 *
	 * @return  WP_Error|string
	 */
	public function generate_locations_by_radius( $args ) {

		// Get provider.
		$provider = $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-generate-locations', 'provider' );
		$language = $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-generate-locations', 'language' );

		/**
		 * Fetches locations by area based on the given criteria.
		 *
		 * @since   5.0.0
		 */
		$result = apply_filters( 'page_generator_pro_keywords_generate_locations_by_radius_' . $provider, $args, 'locations_radius', 250, $language );

		// Return.
		return $result;

	}

	/**
	 * Saves a location Keyword, checking if the first row of data includes the columns
	 * and removing them if so.
	 *
	 * @since   5.0.5
	 *
	 * @param   string $terms        Location terms.
	 * @param   array  $columns      Columns.
	 * @param   string $keyword_name Keyword Name.
	 * @return  int|WP_Error
	 */
	public function save( $terms, $columns, $keyword_name = '' ) {

		// Remove columns from first row, if included in the locations data.
		// Trim other Terms.
		$terms_arr = explode( "\n", $terms );
		foreach ( $terms_arr as $key => $term ) {
			if ( trim( $term ) === implode( ',', $columns ) ) {
				unset( $terms_arr[ $key ] );
				continue;
			}

			$terms_arr[ $key ] = trim( $term );
		}

		$terms = implode( "\n", $terms_arr );

		// Build single Keyword.
		$keyword = array(
			'keyword'   => ! empty( $keyword_name ) ? $keyword_name : $this->base->get_class( 'keywords' )->get_unique_name( 'location' ),
			'data'      => $terms,
			'delimiter' => ',',
			'columns'   => implode( ',', $columns ),
		);

		// Save Keyword, returning Keyword ID or WP_Error.
		return $this->base->get_class( 'keywords' )->save( $keyword );

	}

}
