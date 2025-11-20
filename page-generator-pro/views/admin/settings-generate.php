<?php
/**
 * Outputs the Settings > Generate screen
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

?>
<div class="postbox">
	<h3 class="hndle"><?php esc_html_e( 'Generate', 'page-generator-pro' ); ?></h3>

	<div class="wpzinc-option">
		<p class="description">
			<?php esc_html_e( 'Specifies default behaviour when Generating Content and Terms.', 'page-generator-pro' ); ?>
		</p>
	</div>

	<div class="wpzinc-option">
		<div class="left">
			<label for="log_enabled"><?php esc_html_e( 'Enable Logging?', 'page-generator-pro' ); ?></label>
		</div>
		<div class="right">
			<?php
			$setting = $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-generate', 'log_enabled', '0' );
			?>
			<select name="<?php echo esc_attr( $this->base->plugin->name ); ?>-generate[log_enabled]" id="log_enabled" size="1" data-conditional="log_settings">
				<option value="1"<?php selected( $setting, '1' ); ?>><?php esc_html_e( 'Yes', 'page-generator-pro' ); ?></option>
				<option value="0"<?php selected( $setting, '0' ); ?>><?php esc_html_e( 'No', 'page-generator-pro' ); ?></option>
			</select>

			<p class="description">
				<?php
				printf(
					'%s %s %s',
					esc_html__( 'If enabled, the', 'page-generator-pro' ),
					'<a href="' . esc_url( $this->base->plugin->documentation_url ) . '/logs" target="_blank" rel="noopener">' . esc_html__( 'Plugin Logs', 'page-generator-pro' ) . '</a>',
					esc_html__( 'will detail results of Content and Term Generation.', 'page-generator-pro' )
				);
				?>
			</p>
		</div>
	</div>

	<div id="log_settings" class="wpzinc-option">
		<div class="left">
			<label for="log_preserve_days"><?php esc_html_e( 'Preserve Logs', 'page-generator-pro' ); ?></label>
		</div>
		<div class="right">
			<input type="number" name="<?php echo esc_attr( $this->base->plugin->name ); ?>-generate[log_preserve_days]" id="log_preserve_days" min="0" max="365" step="1" value="<?php echo esc_attr( $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-generate', 'log_preserve_days', '7' ) ); ?>" />
			<?php esc_html_e( 'days', 'page-generator-pro' ); ?>

			<p class="description">
				<?php esc_html_e( 'The number of days to preserve logs for. Zero means logs are kept indefinitely.', 'page-generator-pro' ); ?>
			</p>
		</div>
	</div>

	<div class="wpzinc-option">
		<div class="left">
			<label for="stop_on_error"><?php esc_html_e( 'Generate Content Items per Request', 'page-generator-pro' ); ?></label>
		</div>
		<div class="right">
			<?php
			$setting = $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-generate', 'index_increment', 10 );
			?>
			<input type="number" name="<?php echo esc_attr( $this->base->plugin->name ); ?>-generate[index_increment]" value="<?php echo esc_attr( $setting ); ?>" id="index_increment" min="1" max="500" step="1" />

			<p class="description">
				<?php
				esc_html_e( 'When using Generate via Browser, determines how many items to generate in each synchronous request before updating the log. A higher number can be used for simpler Content Groups, and a lower number is recommended for more complex Content Groups containing a number of Dynamic Elements.', 'page-generator-pro' );
				?>
			</p>
		</div>
	</div>

	<div class="wpzinc-option">
		<div class="left">
			<label for="stop_on_error"><?php esc_html_e( 'Trash / Delete Generated Content Items per Request', 'page-generator-pro' ); ?></label>
		</div>
		<div class="right">
			<?php
			$setting = $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-generate', 'trash_delete_per_request_item_limit', 100 );
			?>
			<input type="number" name="<?php echo esc_attr( $this->base->plugin->name ); ?>-generate[trash_delete_per_request_item_limit]" value="<?php echo esc_attr( $setting ); ?>" id="trash_delete_per_request_item_limit" min="1" max="1000" step="1" />

			<p class="description">
				<?php
				printf(
					'%s %s',
					esc_html__( 'When using Generate via Browser, determines how many generated items to trash / delete in each synchronous request. Set a lower number if getting 500, 502 or 524 server errors, or consider using ', 'page-generator-pro' ),
					'<a href="' . esc_attr( $this->base->plugin->documentation_url ) . '/generate-wp-cli/#delete-generated-content" target="_blank">' . esc_html__( 'CLI', 'page-generator-pro' ) . '</a>'
				);
				?>
			</p>
		</div>
	</div>

	<div class="wpzinc-option">
		<div class="left">
			<label for="stop_on_error"><?php esc_html_e( 'Stop on Error', 'page-generator-pro' ); ?></label>
		</div>
		<div class="right">
			<?php
			$setting = $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-generate', 'stop_on_error', '0' );
			?>
			<select name="<?php echo esc_attr( $this->base->plugin->name ); ?>-generate[stop_on_error]" id="stop_on_error" size="1" data-conditional="stop_on_error_settings" data-conditional-value="0,-1">
				<option value="1"<?php selected( $setting, '1' ); ?>><?php esc_html_e( 'Stop', 'page-generator-pro' ); ?></option>
				<option value="0"<?php selected( $setting, '0' ); ?>><?php esc_html_e( 'Continue, attempting to regenerate the Content or Term again', 'page-generator-pro' ); ?></option>
				<option value="-1"<?php selected( $setting, '-1' ); ?>><?php esc_html_e( 'Continue, skipping the failed Content or Term', 'page-generator-pro' ); ?></option>
			</select>

			<p class="description">
				<?php esc_html_e( 'Whether to stop Content / Term Generation when an error occurs.', 'page-generator-pro' ); ?>
			</p>
		</div>
	</div>

	<div id="stop_on_error_settings">
		<div class="wpzinc-option">
			<div class="left">
				<label for="stop_on_error_pause"><?php esc_html_e( 'Pause before Continuing', 'page-generator-pro' ); ?></label>
			</div>
			<div class="right">
				<?php
				$setting = $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-generate', 'stop_on_error_pause', '5' );
				?>
				<input type="number" id="stop_on_error_pause" name="<?php echo esc_attr( $this->base->plugin->name ); ?>-generate[stop_on_error_pause]" value="<?php echo esc_attr( $setting ); ?>" min="1" max="60" step="1" />

				<p class="description">
					<?php esc_html_e( 'The number of seconds to pause generation before resuming when an error is detected, if Stop on Error is set to continue on errors.', 'page-generator-pro' ); ?>
				</p>
			</div>
		</div>
	</div>

	<div class="wpzinc-option">
		<div class="left">
			<label for="use_mu_plugin"><?php esc_html_e( 'Use Performance Addon?', 'page-generator-pro' ); ?></label>
		</div>
		<div class="right">
			<?php
			$setting = $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-generate', 'use_mu_plugin', '0' );
			?>
			<select name="<?php echo esc_attr( $this->base->plugin->name ); ?>-generate[use_mu_plugin]" id="use_mu_plugin" data-conditional="use_mu_plugin_settings" size="1">
				<option value="1"<?php selected( $setting, '1' ); ?>><?php esc_html_e( 'Yes', 'page-generator-pro' ); ?></option>
				<option value="0"<?php selected( $setting, '0' ); ?>><?php esc_html_e( 'No', 'page-generator-pro' ); ?></option>
			</select>

			<p class="description">
				<?php esc_html_e( 'Experimental: If enabled, uses the Performance Addon Must-Use Plugin.  This can improve generation times and reduce memory usage on sites with several Plugins.', 'page-generator-pro' ); ?>
			</p>
		</div>
	</div>

	<div id="use_mu_plugin_settings" class="wpzinc-option">
		<div class="left">
			<label for="log_preserve_days"><?php esc_html_e( 'Performance Addon: Load Plugins', 'page-generator-pro' ); ?></label>
		</div>
		<div class="right">
			<p class="description">
				<?php esc_html_e( 'If generation correctly generates data, there\'s no need to enable Plugins here - even if they\'re used in a Content Group. For example, most Custom Field and SEO data will generate without needing their Plugins to be activated here.', 'page-generator-pro' ); ?>
				<br />
				<?php esc_html_e( 'If generation does not correctly generate data, you may need to enable the applicable Plugin relating to that data below, so that it is loaded when using the Performance Addon.', 'page-generator-pro' ); ?>
			</p>

			<table class="widefat striped">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Plugin', 'page-generator-pro' ); ?></th>
						<td><?php esc_html_e( 'Enabled', 'page-generator-pro' ); ?></td>
					</tr>
				</thead>
				<tbody>
					<?php
					$use_mu_active_plugins   = $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-generate', 'use_mu_active_plugins', array() );
					$use_mu_required_plugins = $this->base->get_class( 'common' )->get_use_mu_plugin_required_plugins();

					foreach ( get_plugins() as $uri => $installed_plugin ) {
						?>
						<tr>
							<td>
								<label for="use_mu_active_plugins_<?php echo esc_attr( $uri ); ?>">
									<?php echo esc_html( $installed_plugin['Name'] ); ?>
								</label>
								<?php
								if ( in_array( $uri, $use_mu_required_plugins, true ) ) {
									?>
									<br /><small><?php esc_html_e( 'This plugin is required when using the Performance Addon. It cannot be disabled.', 'page-generator-pro' ); ?></small>
									<?php
								}
								?>
							</td>
							<td>
								<?php
								$enabled = false;
								if ( in_array( $uri, $use_mu_required_plugins, true ) || in_array( $uri, $use_mu_active_plugins, true ) ) {
									$enabled = true;
								}
								?>
								<input type="checkbox" name="<?php echo esc_attr( $this->base->plugin->name ); ?>-generate[use_mu_active_plugins][]" id="use_mu_active_plugins_<?php echo esc_attr( $uri ); ?>" value="<?php echo esc_attr( $uri ); ?>"<?php checked( $enabled, 1 ); ?> <?php echo ( in_array( $uri, $use_mu_required_plugins, true ) ? ' disabled' : '' ); ?> />

							</td> 
						</tr>
						<?php
					}
					?>
				</tbody>
			</table>
		</div>
	</div>
</div>
