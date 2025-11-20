<?php
/**
 * Divi Module: Gemini AI Image Class.
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Registers the Gemini AI Dynamic Element as a Divi Module.
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 5.0.4
 */
class Page_Generator_Pro_Divi_Module_Gemini_AI_Image extends Page_Generator_Pro_Divi_Module {

	/**
	 * The module's slug. Must be different from the Page Generator Pro underlying shortcode.
	 *
	 * @since   5.0.4
	 *
	 * @var     string
	 */
	public $slug = 'page-generator-pro-divi-gemini-ai-image';

	/**
	 * The module's block / shortcode name.
	 *
	 * @since   5.0.4
	 *
	 * @var     string
	 */
	public $block_name = 'gemini-ai-image';

}

new Page_Generator_Pro_Divi_Module_Gemini_AI_Image();
