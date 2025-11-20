<?php
/**
 * Outputs the Settings > Integrations screen
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

?>
<div class="postbox">
	<h3 class="hndle"><?php esc_html_e( 'Integrations', 'page-generator-pro' ); ?></h3>

	<?php
	// Iterate through all registered integrations, outputting their settings in
	// a containing element.
	foreach ( $integrations as $integration => $integration_name ) {
		// Skip if this provider has not registered any settings fields.
		if ( ! array_key_exists( $integration, $settings_fields ) ) {
			continue;
		}

		foreach ( $settings_fields[ $integration ] as $field_name => $field ) {
			// Prefix field name.
			$field_name = 'page-generator-pro-integrations[' . $field_name . ']';
			include 'fields/row.php';
		}
	}
	?>
</div>
