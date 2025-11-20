<?php
/**
 * Divi Module: Creative Commons Class.
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Registers the Creative Commons Dynamic Element as a Divi Module.
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 3.0.7
 */
class Page_Generator_Pro_Divi_Module_Creative_Commons extends Page_Generator_Pro_Divi_Module {

	/**
	 * The module's slug. Must be different from the Page Generator Pro underlying shortcode.
	 *
	 * @since   3.0.7
	 *
	 * @var     string
	 */
	public $slug = 'page-generator-pro-divi-creative-commons';

	/**
	 * The module's block / shortcode name.
	 *
	 * @since   4.7.2
	 *
	 * @var     string
	 */
	public $block_name = 'creative-commons';

}

new Page_Generator_Pro_Divi_Module_Creative_Commons();
