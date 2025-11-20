<?php
/**
 * Divi Module: Straico Class.
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Registers the Straico Dynamic Element as a Divi Module.
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 5.2.7
 */
class Page_Generator_Pro_Divi_Module_Straico extends Page_Generator_Pro_Divi_Module {

	/**
	 * The module's slug. Must be different from the Page Generator Pro underlying shortcode.
	 *
	 * @since   5.2.7
	 *
	 * @var     string
	 */
	public $slug = 'page-generator-pro-divi-straico';

	/**
	 * The module's block / shortcode name.
	 *
	 * @since   5.2.7
	 *
	 * @var     string
	 */
	public $block_name = 'straico';

}

new Page_Generator_Pro_Divi_Module_Straico();
