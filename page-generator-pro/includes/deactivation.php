<?php
/**
 * Plugin deactivation function.
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Runs the uninstallation routines when the plugin is deactivated.
 *
 * @since   1.2.2
 *
 * @param   bool $network_wide   Is network wide deactivation.
 */
function page_generator_pro_deactivate( $network_wide ) {

	// Initialise Plugin.
	$plugin = Page_Generator_Pro::get_instance();
	$plugin->initialize();

	// Check if we are on a multisite install, activating network wide, or a single install.
	if ( ! is_multisite() || ! $network_wide ) {
		// Single Site deactivation.
		$plugin->get_class( 'install' )->uninstall();
	} else {
		// Multisite network wide deactivation.
		$sites = get_sites(
			array(
				'number' => 0,
			)
		);
		foreach ( $sites as $site ) {
			switch_to_blog( (int) $site->blog_id );
			$plugin->get_class( 'install' )->uninstall();
			restore_current_blog();
		}
	}

}
