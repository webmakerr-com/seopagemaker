<?php
/**
 * Live Composer Module: Image URL Class.
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Registers the Image URL Dynamic Element as a Live Composer Module.
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 5.2.7
 */
class Page_Generator_Pro_Live_Composer_Module_Image_URL extends Page_Generator_Pro_Live_Composer_Module {

	/**
	 * The Live Composer module's ID. Must match the class name.
	 *
	 * @since   5.2.7
	 *
	 * @var     string
	 */
	public $module_id = 'Page_Generator_Pro_Live_Composer_Module_Image_URL';

	/**
	 * The module's block / shortcode name.
	 *
	 * @since   5.2.7
	 *
	 * @var     string
	 */
	public $block_name = 'image-url';

}
