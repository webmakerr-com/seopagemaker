<?php
/**
 * Cron Functions
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Define the WP Cron function to perform the log cleanup
 *
 * @since   2.6.1
 */
function page_generator_pro_log_cleanup_cron() {

	// Initialise Plugin.
	$page_generator_pro = Page_Generator_Pro::get_instance();
	$page_generator_pro->initialize();

	// Call CRON Log Cleanup function.
	$page_generator_pro->get_class( 'cron' )->log_cleanup();

	// Shutdown.
	unset( $page_generator_pro );

}
add_action( 'page_generator_pro_log_cleanup_cron', 'page_generator_pro_log_cleanup_cron' );

/**
 * Define the WP Cron function to perform the generation routine
 *
 * @since   2.6.1
 *
 * @param   int    $group_id   Group ID.
 * @param   string $type       Content Type.
 */
function page_generator_pro_generate_cron( $group_id, $type = 'content' ) {

	// Initialise Plugin.
	$page_generator_pro = Page_Generator_Pro::get_instance();
	$page_generator_pro->initialize();

	// Call CRON Generate function.
	$page_generator_pro->get_class( 'cron' )->generate( $group_id, $type );

	// Shutdown.
	unset( $page_generator_pro );

}
add_action( 'page_generator_pro_generate_cron', 'page_generator_pro_generate_cron', 10, 2 );
