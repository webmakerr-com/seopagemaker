<?php
/**
 * Divi Extension class.
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Registers Plugin as an extension in Divi.
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */
class Page_Generator_Pro_Divi_Extension extends DiviExtension {

	/**
	 * The gettext domain for the extension's translations.
	 *
	 * @since   4.7.2
	 *
	 * @var     string
	 */
	public $gettext_domain = 'page-generator-pro';

	/**
	 * The extension's WP Plugin name.
	 *
	 * @since   4.7.2
	 *
	 * @var     string
	 */
	public $name = 'page-generator-pro-divi';

	/**
	 * The extension's version.
	 *
	 * @since   4.7.2
	 *
	 * @var     string
	 */
	public $version = '4.7.2';

	/**
	 * Constructor.
	 *
	 * @since   4.7.2
	 *
	 * @param   string $name Extension name.
	 * @param   array  $args Arguments.
	 */
	public function __construct( $name = 'page-generator-pro-divi', $args = array() ) {

		$this->plugin_dir     = PAGE_GENERATOR_PRO_PLUGIN_PATH . 'includes/admin/integrations/divi/';
		$this->plugin_dir_url = PAGE_GENERATOR_PRO_PLUGIN_URL . 'includes/admin/integrations/divi/';

		// Call parent construct.
		parent::__construct( $name, $args );

	}
}

new Page_Generator_Pro_Divi_Extension();
