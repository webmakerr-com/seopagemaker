<?php
/**
 * Outputs the Settings > Research screen
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

?>
<div class="postbox">
	<h3 class="hndle"><?php esc_html_e( 'Research', 'page-generator-pro' ); ?></h3>

	<div class="wpzinc-option">
		<p class="description">
			<?php
			esc_html_e( 'Specifies which provider to use to perform research to build content for a given topic.', 'page-generator-pro' );
			?>
		</p>
	</div>

	<div class="wpzinc-option">
		<div class="left">
			<label for="provider"><?php esc_html_e( 'Service', 'page-generator-pro' ); ?></label>
		</div>
		<div class="right">
			<select name="<?php echo esc_attr( $this->base->plugin->name ); ?>-research[provider]" id="provider" size="1">
				<?php
				foreach ( $providers as $provider => $label ) {
					?>
					<option value="<?php echo esc_attr( $provider ); ?>"<?php selected( $settings['provider'], $provider ); ?>><?php echo esc_attr( $label ); ?></option>
					<?php
				}
				?>
			</select>
			<p class="description">
				<?php esc_html_e( 'The third party service to use for research.', 'page-generator-pro' ); ?>
			</p>
		</div>
	</div>

	<?php
	// Iterate through all registered research providers, outputting their settings in
	// a containing element that JS will show/hide based on the Service setting.
	foreach ( $providers as $provider => $provider_name ) {
		// Skip if this provider has not registered any settings fields.
		if ( ! array_key_exists( $provider, $settings_fields ) ) {
			continue;
		}
		?>
		<div id="<?php echo esc_attr( $provider ); ?>">
			<?php
			foreach ( $settings_fields[ $provider ] as $field_name => $field ) {
				// Prefix field name.
				$field_name = 'page-generator-pro-research[' . $field_name . ']';
				include 'fields/row.php';
			}
			?>
		</div>
		<?php
	}
	?>
</div>
