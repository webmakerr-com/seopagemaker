<?php
/**
 * Bricks Element: Image URL Class.
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Registers the Image URL Dynamic Element as a Bricks Element.
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 5.2.7
 */
class Page_Generator_Pro_Bricks_Element_Image_URL extends Page_Generator_Pro_Bricks_Element {

	/**
	 * The element's name.
	 *
	 * @since   5.2.7
	 *
	 * @var     string
	 */
	public $name = 'page-generator-pro-bricks-element-image-url';

	/**
	 * The element's CSS selector, appended to the generated element tag.
	 *
	 * @since   5.2.7
	 *
	 * @var     string
	 */
	public $css_selector = '.page-generator-pro-bricks-element-image-url';

	/**
	 * The module's block / shortcode name.
	 *
	 * @since   5.2.7
	 *
	 * @var     string
	 */
	public $block_name = 'image-url';

}
