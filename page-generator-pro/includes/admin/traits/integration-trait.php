<?php
/**
 * Integration Trait
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Trait for registering integrations.
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 */
trait Page_Generator_Pro_Integration_Trait {

	/**
	 * Returns this shortcode / block's programmatic name.
	 *
	 * @since   4.8.0
	 *
	 * @return  string
	 */
	public function get_name() {

		return $this->name;

	}

	/**
	 * Returns this integration's settings prefix, which uses
	 * lowercases instead of hyphens.
	 *
	 * @since   4.8.0
	 *
	 * @return  string
	 */
	public function get_settings_prefix() {

		return str_replace( '-', '_', $this->get_name() );

	}

	/**
	 * Register this provider as an integration.
	 *
	 * Used across Integrations, Research and Spintax.
	 *
	 * @since   4.9.6
	 *
	 * @param   array $providers  Providers.
	 * @return  array               Research Providers
	 */
	public function register_integration( $providers ) {

		$providers[ $this->get_name() ] = $this->get_title();
		return $providers;

	}


}
