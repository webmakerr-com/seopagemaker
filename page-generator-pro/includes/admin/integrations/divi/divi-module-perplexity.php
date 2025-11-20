<?php
/**
 * Divi Module: Perplexity Class.
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Registers the Claude AI Dynamic Element as a Divi Module.
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 4.9.3
 */
class Page_Generator_Pro_Divi_Module_Perplexity extends Page_Generator_Pro_Divi_Module {

	/**
	 * The module's slug. Must be different from the Page Generator Pro underlying shortcode.
	 *
	 * @since   4.9.3
	 *
	 * @var     string
	 */
	public $slug = 'page-generator-pro-divi-perplexity';

	/**
	 * The module's block / shortcode name.
	 *
	 * @since   4.9.3
	 *
	 * @var     string
	 */
	public $block_name = 'perplexity';

}

new Page_Generator_Pro_Divi_Module_Perplexity();
