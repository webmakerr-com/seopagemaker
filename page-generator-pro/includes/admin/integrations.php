<?php
/**
 * Integrations Class
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Registers settings fields on Settings > Integrations.
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 */
class Page_Generator_Pro_Integrations {

	/**
	 * Holds the base object.
	 *
	 * @since   4.8.0
	 *
	 * @var     object
	 */
	public $base;

	/**
	 * Constructor.
	 *
	 * @since   4.8.0
	 *
	 * @param   object $base    Base Plugin Class.
	 */
	public function __construct( $base ) {

		// Store base class.
		$this->base = $base;

	}

	/**
	 * Return available integrations supported by this class.
	 *
	 * @since   4.8.0
	 *
	 * @return  array   Integrations
	 */
	public function get() {

		$integrations = array();

		/**
		 * Return available integrations supported by this class.
		 *
		 * @since   4.8.0
		 *
		 * @param   array   $integrations  Integrations.
		 */
		$integrations = apply_filters( 'page_generator_pro_integrations_get', $integrations );

		// Sort alphabetically.
		ksort( $integrations );

		// Return filtered results.
		return $integrations;

	}

	/**
	 * Defines each integration's settings to display at Settings > Integrations
	 *
	 * @since   4.8.0
	 *
	 * @return  array   Integrations settings fields.
	 */
	public function get_settings_fields() {

		$settings_fields = array();

		/**
		 * Defines each integration's settings to display at Settings > Integrations
		 *
		 * @since   4.8.0
		 *
		 * @param   array   $settings_fields  Settings Fields.
		 */
		$settings_fields = apply_filters( 'page_generator_pro_integrations_get_settings_fields', $settings_fields );

		// Return filtered results.
		return $settings_fields;

	}

}
