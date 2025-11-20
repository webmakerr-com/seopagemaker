<?php
/**
 * Outputs the Settings > General screen
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

?>
<div class="postbox">
	<h3 class="hndle"><?php esc_html_e( 'General', 'page-generator-pro' ); ?></h3>

	<div class="wpzinc-option">
		<div class="left">
			<label for="country_code"><?php esc_html_e( 'Country Code', 'page-generator-pro' ); ?></label>
		</div>
		<div class="right">
			<?php
			$setting = $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-general', 'country_code', 'US' );
			?>
			<select name="<?php echo esc_attr( $this->base->plugin->name ); ?>-general[country_code]" id="country_code" size="1">
				<?php
				foreach ( $countries as $country_code => $country_name ) {
					?>
					<option value="<?php echo esc_attr( $country_code ); ?>"<?php selected( $setting, $country_code ); ?>>
						<?php echo esc_attr( $country_name ); ?>
					</option>
					<?php
				}
				?>
			</select>

			<p class="description">
				<?php esc_html_e( 'The default country to select for any Country Code dropdowns within the Plugin.', 'page-generator-pro' ); ?>
			</p>
		</div>
	</div>

	<div class="wpzinc-option">
		<div class="left">
			<label for="css_output"><?php esc_html_e( 'Output CSS', 'page-generator-pro' ); ?></label>
		</div>
		<div class="right">
			<?php
			$setting = $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-general', 'css_output', '1' );
			?>
			<select name="<?php echo esc_attr( $this->base->plugin->name ); ?>-general[css_output]" id="css_output" size="1">
				<option value="1"<?php selected( $setting, '1' ); ?>><?php esc_html_e( 'Yes', 'page-generator-pro' ); ?></option>
				<option value="0"<?php selected( $setting, '0' ); ?>><?php esc_html_e( 'No', 'page-generator-pro' ); ?></option>
			</select>

			<p class="description">
				<?php esc_html_e( 'Enables or disables frontend CSS output, which is needed for some Dynamic Elements. If disabled, you\'re responsible for defining your own CSS for styling.', 'page-generator-pro' ); ?>
			</p>
		</div>
	</div>

	<div class="wpzinc-option">
		<div class="left">
			<label for="revisions"><?php esc_html_e( 'Enable Revisions on Content Groups', 'page-generator-pro' ); ?></label>
		</div>
		<div class="right">
			<?php
			$setting = $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-general', 'revisions', '0' );
			?>
			<select name="<?php echo esc_attr( $this->base->plugin->name ); ?>-general[revisions]" id="revisions" size="1">
				<option value="1"<?php selected( $setting, '1' ); ?>><?php esc_html_e( 'Yes', 'page-generator-pro' ); ?></option>
				<option value="0"<?php selected( $setting, '0' ); ?>><?php esc_html_e( 'No', 'page-generator-pro' ); ?></option>
			</select>

			<p class="description">
				<?php
				printf(
					'%s %s %s',
					esc_html__( 'Enables or disables', 'page-generator-pro' ),
					sprintf(
						'<a href="https://wordpress.org/support/article/revisions/" target="_blank" rel="noopener">%s</a>',
						esc_html__( 'WordPress\' revisions', 'page-generator-pro' )
					),
					esc_html__( 'on Content Groups. Useful if you want to store a record of each saved draft or published update to a Content Group.', 'page-generator-pro' )
				);
				?>
			</p>
		</div>
	</div>

	<div class="wpzinc-option">
		<div class="left">
			<label for="disable_custom_fields"><?php esc_html_e( 'Disable Custom Fields Dropdown on Pages', 'page-generator-pro' ); ?></label>
		</div>
		<div class="right">
			<?php
			$setting = $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-general', 'disable_custom_fields', '0' );
			?>
			<select name="<?php echo esc_attr( $this->base->plugin->name ); ?>-general[disable_custom_fields]" id="disable_custom_fields" size="1">
				<option value="1"<?php selected( $setting, '1' ); ?>><?php esc_html_e( 'Yes', 'page-generator-pro' ); ?></option>
				<option value="0"<?php selected( $setting, '0' ); ?>><?php esc_html_e( 'No', 'page-generator-pro' ); ?></option>
			</select>

			<p class="description">
				<?php
				esc_html_e( 'Enable this option to improve performance of the Page / Post editor.  This does not affect the use of any Custom Field Post Meta data.', 'page-generator-pro' );
				?>
			</p>
		</div>
	</div>

	<div class="wpzinc-option">
		<div class="left">
			<label for="restrict_parent_page_depth"><?php esc_html_e( 'Change Page Dropdown Fields', 'page-generator-pro' ); ?></label>
		</div>
		<div class="right">
			<?php
			$setting = $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-general', 'restrict_parent_page_depth', '0' );
			?>
			<select name="<?php echo esc_attr( $this->base->plugin->name ); ?>-general[restrict_parent_page_depth]" id="restrict_parent_page_depth" size="1">
				<option value="ajax_select"<?php selected( $setting, '1' ); ?>><?php esc_html_e( 'Search Dropdown Field', 'page-generator-pro' ); ?></option>
				<option value="1"<?php selected( $setting, '1' ); ?>><?php esc_html_e( 'ID Field', 'page-generator-pro' ); ?></option>
				<option value="0"<?php selected( $setting, '0' ); ?>><?php esc_html_e( 'No', 'page-generator-pro' ); ?></option>
			</select>

			<p class="description">
				<?php esc_html_e( 'Enable this option to replace the following dropdown fields with a Search or ID Field for performance:', 'page-generator-pro' ); ?>
				<br />
				<?php esc_html_e( '- Page Parent dropdown on hierarchical Post Types, such as Pages', 'page-generator-pro' ); ?>
				<br />
				<?php esc_html_e( '- Settings > Reading > Homepage, Posts page', 'page-generator-pro' ); ?>
				<br />
				<?php esc_html_e( '- Appearance > Customize', 'page-generator-pro' ); ?>
				<br />
				<?php esc_html_e( 'This improves WordPress performance on sites with a large number of Pages.', 'page-generator-pro' ); ?>
			</p>
		</div>
	</div>

	<div class="wpzinc-option">
		<div class="left">
			<label for="persistent_caching"><?php esc_html_e( 'Persistent Caching', 'page-generator-pro' ); ?></label>
		</div>
		<div class="right">
			<?php
			$setting = $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-general', 'persistent_caching', '0' );
			?>
			<select name="<?php echo esc_attr( $this->base->plugin->name ); ?>-general[persistent_caching]" id="persistent_caching" size="1">
				<option value="1"<?php selected( $setting, '1' ); ?>><?php esc_html_e( 'Enabled', 'page-generator-pro' ); ?></option>
				<option value="0"<?php selected( $setting, '0' ); ?>><?php esc_html_e( 'Disabled', 'page-generator-pro' ); ?></option>
			</select>

			<p class="description">
				<?php esc_html_e( 'Enable this option to enable persistent caching of:', 'page-generator-pro' ); ?>
				<br />
				<?php esc_html_e( '- Related Links', 'page-generator-pro' ); ?>
				<br />
				<?php esc_html_e( 'This improves WordPress performance on sites with a large number of Pages.', 'page-generator-pro' ); ?>
			</p>
		</div>
	</div>
</div>
