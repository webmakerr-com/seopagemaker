<?php
/**
 * Outputs the Settings > Generate Locations screen
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

?>
<div class="postbox">
	<h3 class="hndle"><?php esc_html_e( 'Generate Locations', 'page-generator-pro' ); ?></h3>

	<div class="wpzinc-option">
		<p class="description">
			<?php esc_html_e( 'Specifies the service to use for the Keywords &gt; Generate Locations and Add New Directory screens.  Method and Radius can be overridden when using these functions.', 'page-generator-pro' ); ?>
		</p>
	</div>

	<div class="wpzinc-option">
		<div class="left">
			<label for="provider"><?php esc_html_e( 'Service', 'page-generator-pro' ); ?></label>
		</div>
		<div class="right">
			<select name="<?php echo esc_attr( $this->base->plugin->name ); ?>-generate-locations[provider]" id="provider" size="1">
				<?php
				foreach ( $providers as $provider => $label ) {
					?>
					<option value="<?php echo esc_attr( $provider ); ?>"<?php selected( $settings['provider'], $provider ); ?>><?php echo esc_attr( $label ); ?></option>
					<?php
				}
				?>
			</select>
			<p class="description">
				<?php esc_html_e( 'The third party service to use for generating location keywords.', 'page-generator-pro' ); ?>
			</p>
		</div>
	</div>

	<?php
	// Iterate through all registered location providers, outputting their settings in
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
				$field_name = 'page-generator-pro-generate-locations[' . $field_name . ']';
				include 'fields/row.php';
			}
			?>
		</div>
		<?php
	}
	?>

	<div id="language" class="wpzinc-option">
		<div class="left">
			<label for="language"><?php esc_html_e( 'Language', 'page-generator-pro' ); ?></label>
		</div>
		<div class="right">
			<select name="<?php echo esc_attr( $this->base->plugin->name ); ?>-generate-locations[language]" id="language" size="1">
				<?php
				foreach ( $languages as $language => $label ) {
					?>
					<option value="<?php echo esc_attr( $language ); ?>"<?php selected( $settings['language'], $language ); ?>>
						<?php echo esc_attr( $label ); ?>
					</option>
					<?php
				}
				?>
			</select>

			<p class="description">
				<?php esc_html_e( 'The language to return locations in.', 'page-generator-pro' ); ?><br />
			</p>
		</div>
	</div>

	<div class="wpzinc-option">
		<div class="left">
			<label for="method"><?php esc_html_e( 'Method', 'page-generator-pro' ); ?></label>
		</div>
		<div class="right">
			<select name="<?php echo esc_attr( $this->base->plugin->name ); ?>-generate-locations[method]" id="method" size="1">
				<?php
				foreach ( $methods as $method => $label ) {
					?>
					<option value="<?php echo esc_attr( $method ); ?>"<?php selected( $settings['method'], $method ); ?>>
						<?php echo esc_attr( $label ); ?>
					</option>
					<?php
				}
				?>
			</select>

			<p class="description">
				<?php esc_html_e( 'The default method to select for the Method dropdown.', 'page-generator-pro' ); ?><br />
			</p>
		</div>
	</div>

	<div class="wpzinc-option">
		<div class="left">
			<label for="radius"><?php esc_html_e( 'Radius', 'page-generator-pro' ); ?></label>
		</div>
		<div class="right">
			<input type="number" id="radius" name="<?php echo esc_attr( $this->base->plugin->name ); ?>-generate-locations[radius]" min="0.1" max="99999" step="0.1" value="<?php echo esc_attr( $settings['radius'] ); ?>" class="widefat" />

			<p class="description">
				<?php esc_html_e( 'The default radius distance value, in miles.', 'page-generator-pro' ); ?><br />
			</p>
		</div>
	</div>
</div>
