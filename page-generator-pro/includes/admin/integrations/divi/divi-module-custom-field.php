<?php
/**
 * Divi Module: Custom Field Class.
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Registers the Custom Field Image Dynamic Element as a Divi Module.
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 4.6.2
 */
class Page_Generator_Pro_Divi_Module_Custom_Field extends Page_Generator_Pro_Divi_Module {

	/**
	 * The module's slug. Must be different from the Page Generator Pro underlying shortcode.
	 *
	 * @since   4.6.2
	 *
	 * @var     string
	 */
	public $slug = 'page-generator-pro-divi-custom-field';

	/**
	 * The module's block / shortcode name.
	 *
	 * @since   4.7.2
	 *
	 * @var     string
	 */
	public $block_name = 'custom-field';

}

new Page_Generator_Pro_Divi_Module_Custom_Field();
