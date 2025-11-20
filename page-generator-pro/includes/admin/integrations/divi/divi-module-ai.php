<?php
/**
 * Divi Module: AI Class.
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Registers the AI Dynamic Element as a Divi Module.
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 4.9.6
 */
class Page_Generator_Pro_Divi_Module_AI extends Page_Generator_Pro_Divi_Module {

	/**
	 * The module's slug. Must be different from the Page Generator Pro underlying shortcode.
	 *
	 * @since   4.9.6
	 *
	 * @var     string
	 */
	public $slug = 'page-generator-pro-divi-ai';

	/**
	 * The module's block / shortcode name.
	 *
	 * @since   4.9.6
	 *
	 * @var     string
	 */
	public $block_name = 'ai';

}

new Page_Generator_Pro_Divi_Module_AI();
