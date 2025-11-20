<?php
/**
 * Divi Module: Google Places Class.
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Registers the Google Places Dynamic Element as a Divi Module.
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 5.2.8
 */
class Page_Generator_Pro_Divi_Module_Google_Places extends Page_Generator_Pro_Divi_Module {

	/**
	 * The module's slug. Must be different from the Page Generator Pro underlying shortcode.
	 *
	 * @since   5.2.8
	 *
	 * @var     string
	 */
	public $slug = 'page-generator-pro-divi-google-places';

	/**
	 * The module's block / shortcode name.
	 *
	 * @since   5.2.8
	 *
	 * @var     string
	 */
	public $block_name = 'google-places';

}

new Page_Generator_Pro_Divi_Module_Google_Places();
