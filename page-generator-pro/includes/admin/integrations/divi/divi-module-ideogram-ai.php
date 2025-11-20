<?php
/**
 * Divi Module: Ideogram AI Image Class.
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Registers the Ideogram AI Image Dynamic Element as a Divi Module.
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 5.0.3
 */
class Page_Generator_Pro_Divi_Module_Ideogram_AI extends Page_Generator_Pro_Divi_Module {

	/**
	 * The module's slug. Must be different from the Page Generator Pro underlying shortcode.
	 *
	 * @since   5.0.3
	 *
	 * @var     string
	 */
	public $slug = 'page-generator-pro-divi-ideogram-ai';

	/**
	 * The module's block / shortcode name.
	 *
	 * @since   5.0.3
	 *
	 * @var     string
	 */
	public $block_name = 'ideogram-ai';

}

new Page_Generator_Pro_Divi_Module_Ideogram_AI();
