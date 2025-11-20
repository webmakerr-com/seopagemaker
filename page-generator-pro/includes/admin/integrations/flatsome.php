<?php
/**
 * Flatsome Integration Class
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Registers Flatsome as a Plugin integration:
 * - Enable Flatsome on Content Groups
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 3.3.7
 */
class Page_Generator_Pro_Flatsome extends Page_Generator_Pro_Integration {

	/**
	 * Holds the base object.
	 *
	 * @since   3.3.7
	 *
	 * @var     object
	 */
	public $base;

	/**
	 * Constructor
	 *
	 * @since   3.3.7
	 *
	 * @param   object $base    Base Plugin Class.
	 */
	public function __construct( $base ) {

		// Store base class.
		$this->base = $base;

		add_action( 'init', array( $this, 'register_flatsome_support' ) );

	}

	/**
	 * Allows the Flatsome Theme's UX Builder to inject its Page Builder
	 * into Page Generator Pro's Groups
	 *
	 * @since   1.7.8
	 */
	public function register_flatsome_support() {

		// Bail if the Flatsome Theme isn't enabled.
		if ( ! $this->is_active() ) {
			return;
		}

		// Add Page Generator Pro Groups.
		add_ux_builder_post_type( 'page-generator-pro' ); // @phpstan-ignore-line

	}
	/**
	 * Checks if the Flatsome Theme is active
	 *
	 * @since   3.3.7
	 *
	 * @return  bool    Is Active
	 */
	public function is_active() {

		// Bail if the Flatsome Theme isn't enabled.
		if ( ! function_exists( 'add_ux_builder_post_type' ) ) {
			return false;
		}

		return true;

	}

}
