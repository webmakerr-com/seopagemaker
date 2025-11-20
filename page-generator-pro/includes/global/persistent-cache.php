<?php
/**
 * Persistent Caching Class
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Provides persistent caching at Plugin level, using the options
 * table to read/write data.
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 3.4.3
 */
class Page_Generator_Pro_Persistent_Cache {

	/**
	 * Holds the base object.
	 *
	 * @since   3.4.3
	 *
	 * @var     object
	 */
	public $base;

	/**
	 * Constructor.
	 *
	 * @since   3.4.3
	 *
	 * @param   object $base    Base Plugin Class.
	 */
	public function __construct( $base ) {

		// Store base class.
		$this->base = $base;

	}

	/**
	 * Gets the cached value for the given key and sub key
	 *
	 * @since   3.4.3
	 *
	 * @param   string       $key        Key.
	 * @param   string|array $params     Parameters.
	 * @return  bool|string|array
	 */
	public function get( $key, $params ) {

		// Bail if persistent caching is disabled.
		if ( ! $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-general', 'persistent_caching', '0' ) ) {
			return false;
		}

		// Get cache.
		$cache = get_option( $this->base->plugin->name . '-cache', false );

		// Build array if cache doesn't exist.
		if ( ! $cache ) {
			return false;
		}
		if ( ! isset( $cache[ $key ] ) ) {
			return false;
		}

		// Sanitize params to string.
		$params = $this->sanitize_params_to_string( $params );

		// Bail if the given parameters' result have not yet been cached.
		if ( ! isset( $cache[ $key ][ $params ] ) ) {
			return false;
		}

		// Return cached parameters result.
		return $cache[ $key ][ $params ];

	}

	/**
	 * Updates the cache value for the given parameters
	 *
	 * @since   3.4.3
	 *
	 * @param   string       $key        Key.
	 * @param   string|array $params     Parameters.
	 * @param   string|array $value      Value to cache for the given Key and Sub Key combination.
	 */
	public function set( $key, $params, $value ) {

		// Bail if persistent caching is disabled.
		if ( ! $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-general', 'persistent_caching', '0' ) ) {
			return false;
		}

		// Get cache.
		$cache = get_option( $this->base->plugin->name . '-cache', false );

		// Build array if cache doesn't exist.
		if ( ! $cache ) {
			$cache = array();
		}
		if ( ! isset( $cache[ $key ] ) ) {
			$cache[ $key ] = array();
		}

		// Update.
		$cache[ $key ][ $this->sanitize_params_to_string( $params ) ] = $value;
		update_option( $this->base->plugin->name . '-cache', $cache, false );

	}

	/**
	 * Deletes the cache for the given key
	 *
	 * @since   3.4.3
	 *
	 * @param   string $key    Key.
	 * @return  bool            Success
	 */
	public function delete_by_key( $key ) {

		// Bail if persistent caching is disabled.
		if ( ! $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-general', 'persistent_caching', '0' ) ) {
			return false;
		}

		// Get cache.
		$cache = get_option( $this->base->plugin->name . '-cache', false );

		// Bail if the cache is empty.
		if ( ! is_array( $cache ) ) {
			return false;
		}

		// Remove cache key.
		unset( $cache[ $key ] );

		// Update.
		return update_option( $this->base->plugin->name . '-cache', $cache, false );

	}

	/**
	 * Deletes the entire cache
	 *
	 * @since   3.4.3
	 *
	 * @return  bool            Success
	 */
	public function delete() {

		// Bail if persistent caching is disabled.
		if ( ! $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-general', 'persistent_caching', '0' ) ) {
			return false;
		}

		return delete_option( $this->base->plugin->name . '-cache' );

	}

	/**
	 * Sanitizes the given parameters to a string
	 *
	 * @since   3.4.3
	 *
	 * @param   string|array $params     Parameters.
	 * @return  string
	 */
	private function sanitize_params_to_string( $params ) {

		// If params is not an array, it's okay to use.
		if ( ! is_array( $params ) ) {
			return $params;
		}

		// Convert array to string.
		return wp_json_encode( $params );

	}

}
