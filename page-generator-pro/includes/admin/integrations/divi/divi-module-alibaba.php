<?php
/**
 * Divi Module: Alibaba AI Class.
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Registers the Alibaba AI Dynamic Element as a Divi Module.
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 5.0.6
 */
class Page_Generator_Pro_Divi_Module_Alibaba extends Page_Generator_Pro_Divi_Module {

	/**
	 * The module's slug. Must be different from the Page Generator Pro underlying shortcode.
	 *
	 * @since   5.0.6
	 *
	 * @var     string
	 */
	public $slug = 'page-generator-pro-divi-alibaba';

	/**
	 * The module's block / shortcode name.
	 *
	 * @since   5.0.6
	 *
	 * @var     string
	 */
	public $block_name = 'alibaba';

}

new Page_Generator_Pro_Divi_Module_Alibaba();
