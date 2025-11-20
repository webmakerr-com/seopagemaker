<?php
/**
 * Initialization Class. Run any actions here that must be performed
 * before the 'init' action/hook.
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Register Content Groups with GoodLayers Page Builder
 *
 * @since   3.3.1
 *
 * @param   array $post_types     Supported Post Types.
 * @return  array                   Supported Post Types
 */
function page_generator_pro_goodlayers_register_support( $post_types ) {

	// Bail if Content Groups are already declared.
	if ( in_array( 'page-generator-pro', $post_types, true ) ) {
		return $post_types;
	}

	// Add Content Groups and return.
	$post_types[] = 'page-generator-pro';
	return $post_types;

}
add_filter( 'gdlr_core_page_builder_post_type', 'page_generator_pro_goodlayers_register_support' );

/**
 * Loads the Page Generator Pro Divi extension, which registers Page Generator Pro-specific Divi modules.
 *
 * @since   4.7.2
 */
function page_generator_pro_register_divi_extension() {

	require_once PAGE_GENERATOR_PRO_PLUGIN_PATH . '/includes/admin/integrations/divi/divi-extension.php';

}
add_action(
	'plugins_loaded',
	function () {

		add_action( 'divi_extensions_init', 'page_generator_pro_register_divi_extension' );

	}
);
