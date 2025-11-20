<?php
/**
 * Elementor Widget: Custom Field.
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Registers the Custom Field Dynamic Element as an Elementor Widget.
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 4.6.2
 */
class Page_Generator_Pro_Elementor_Widget_Custom_Field extends Page_Generator_Pro_Elementor_Widget {

	/**
	 * The module's slug. Must be different from the Page Generator Pro underlying shortcode.
	 *
	 * @since   4.6.2
	 *
	 * @var     string
	 */
	public $slug = 'page-generator-pro-elementor-custom-field';

	/**
	 * Calls the Custom Fields render() function, as this Dynamic Element can provide a live preview/output
	 * on both Content Groups and non-Content Groups.
	 *
	 * @since   4.8.9
	 */
	protected function render() {

		echo Page_Generator_Pro()->get_class( 'shortcode_custom_field' )->render( $this->get_settings_for_display() ); // phpcs:ignore WordPress.Security.EscapeOutput

	}

}
