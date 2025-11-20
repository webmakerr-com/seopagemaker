<?php
/**
 * Divi Module: Grok AI Class.
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Registers the Grok AI Dynamic Element as a Divi Module.
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 5.0.6
 */
class Page_Generator_Pro_Divi_Module_Grok_AI extends Page_Generator_Pro_Divi_Module {

	/**
	 * The module's slug. Must be different from the Page Generator Pro underlying shortcode.
	 *
	 * @since   5.0.6
	 *
	 * @var     string
	 */
	public $slug = 'page-generator-pro-divi-grok-ai';

	/**
	 * The module's block / shortcode name.
	 *
	 * @since   5.0.6
	 *
	 * @var     string
	 */
	public $block_name = 'grok-ai';

}

new Page_Generator_Pro_Divi_Module_Grok_AI();
