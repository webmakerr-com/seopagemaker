<?php
/**
 * Divi Module: YouTube Links Class.
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Registers the YouTube Dynamic Element as a Divi Module.
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 3.0.7
 */
class Page_Generator_Pro_Divi_Module_YouTube extends Page_Generator_Pro_Divi_Module {

	/**
	 * The module's slug. Must be different from the Page Generator Pro underlying shortcode.
	 *
	 * @since   3.0.7
	 *
	 * @var     string
	 */
	public $slug = 'page-generator-pro-divi-youtube';

	/**
	 * The module's block / shortcode name.
	 *
	 * @since   4.7.2
	 *
	 * @var     string
	 */
	public $block_name = 'youtube';

}

new Page_Generator_Pro_Divi_Module_YouTube();
