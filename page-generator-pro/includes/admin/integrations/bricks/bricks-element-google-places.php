<?php
/**
 * Bricks Element: Google Places Class.
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Registers the Google Places Dynamic Element as a Bricks Element.
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 5.2.8
 */
class Page_Generator_Pro_Bricks_Element_Google_Places extends Page_Generator_Pro_Bricks_Element {

	/**
	 * The element's name.
	 *
	 * @since   5.2.8
	 *
	 * @var     string
	 */
	public $name = 'page-generator-pro-bricks-element-google-places';

	/**
	 * The element's CSS selector, appended to the generated element tag.
	 *
	 * @since   5.2.8
	 *
	 * @var     string
	 */
	public $css_selector = '.page-generator-pro-bricks-element-google-places';

	/**
	 * The module's block / shortcode name.
	 *
	 * @since   5.2.8
	 *
	 * @var     string
	 */
	public $block_name = 'google-places';

}
