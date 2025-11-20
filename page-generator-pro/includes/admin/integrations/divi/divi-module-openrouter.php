<?php
/**
 * Divi Module: OpenRouter Class.
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Registers the OpenRouter Dynamic Element as a Divi Module.
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 5.3.0
 */
class Page_Generator_Pro_Divi_Module_OpenRouter extends Page_Generator_Pro_Divi_Module {

	/**
	 * The module's slug. Must be different from the Page Generator Pro underlying shortcode.
	 *
	 * @since   5.3.0
	 *
	 * @var     string
	 */
	public $slug = 'page-generator-pro-divi-openrouter';

	/**
	 * The module's block / shortcode name.
	 *
	 * @since   5.3.0
	 *
	 * @var     string
	 */
	public $block_name = 'openrouter';

}

new Page_Generator_Pro_Divi_Module_OpenRouter();
