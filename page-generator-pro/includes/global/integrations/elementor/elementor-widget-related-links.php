<?php
/**
 * Elementor Widget: Related Links Class.
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Registers the Related Links Dynamic Element as an Elementor Widget.
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 3.0.8
 */
class Page_Generator_Pro_Elementor_Widget_Related_Links extends Page_Generator_Pro_Elementor_Widget {

	/**
	 * The module's slug. Must be different from the Page Generator Pro underlying shortcode.
	 *
	 * @since   3.0.8
	 *
	 * @var     string
	 */
	public $slug = 'page-generator-pro-elementor-related-links';

	/**
	 * Calls the Related Links render() function, as this Dynamic Element can provide a live preview/output
	 * on both Content Groups and non-Content Groups.
	 *
	 * @since   3.6.8
	 */
	protected function render() {

		echo Page_Generator_Pro()->get_class( 'shortcode_related_links' )->render( $this->get_settings_for_display() ); // phpcs:ignore WordPress.Security.EscapeOutput

	}

}
