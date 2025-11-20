<?php
/**
 * Outputs the Settings > Spintax screen
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

?>
<div class="postbox">
	<h3 class="hndle"><?php esc_html_e( 'Spintax', 'page-generator-pro' ); ?></h3>

	<div class="wpzinc-option">
		<p class="description">
			<?php
			printf(
				'%s %s %s',
				esc_html__( 'Specifies how to generate spintax from non-spun content when using the', 'page-generator-pro' ),
				'<a href="' . esc_attr( $this->base->plugin->documentation_url ) . '/generate-using-spintax/#automatically-generate-spintax"  target="_blank" rel="noopener">' . esc_html__( 'Generate Spintax from Selected Content', 'page-generator-pro' ) . '</a>',
				esc_html__( 'functionality', 'page-generator-pro' )
			);
			?>
		</p>
	</div>

	<div class="wpzinc-option">
		<div class="left">
			<label for="frontend"><?php esc_html_e( 'Process on Frontend', 'page-generator-pro' ); ?></label>
		</div>
		<div class="right">
			<select name="<?php echo esc_attr( $this->base->plugin->name ); ?>-spintax[frontend]" id="frontend" size="1">
				<option value=""<?php selected( $settings['frontend'], '' ); ?>><?php esc_html_e( 'No', 'page-generator-pro' ); ?></option>
				<option value="1"<?php selected( $settings['frontend'], '1' ); ?>><?php esc_html_e( 'Yes', 'page-generator-pro' ); ?></option>
			</select>

			<p class="description">
				<?php esc_html_e( 'If enabled, any Block Spintax and/or Spintax detected in any Post Content will be dynamically processed each time it is viewed.', 'page-generator-pro' ); ?>
				<br />
				<?php esc_html_e( 'Block Spintax and Spintax in any Content Group is always processed, regardless of this setting.', 'page-generator-pro' ); ?>
			</p>
		</div>
	</div>

	<div class="wpzinc-option">
		<div class="left">
			<label for="provider"><?php esc_html_e( 'Service', 'page-generator-pro' ); ?></label>
		</div>
		<div class="right">
			<select name="<?php echo esc_attr( $this->base->plugin->name ); ?>-spintax[provider]" id="provider" size="1">
				<?php
				foreach ( $providers as $provider => $label ) {
					?>
					<option value="<?php echo esc_attr( $provider ); ?>"<?php selected( $settings['provider'], $provider ); ?>><?php echo esc_attr( $label ); ?></option>
					<?php
				}
				?>
			</select>
			<p class="description">
				<?php esc_html_e( 'Optionally use a third party service to generate spintax.', 'page-generator-pro' ); ?>
			</p>
		</div>
	</div>

	<div id="language" class="wpzinc-option">
		<div class="left">
			<label for="language"><?php esc_html_e( 'Language', 'page-generator-pro' ); ?></label>
		</div>
		<div class="right">
			<select name="<?php echo esc_attr( $this->base->plugin->name ); ?>-spintax[language]" id="language" size="1">
				<?php
				foreach ( $this->base->get_class( 'common' )->get_languages() as $language => $label ) {
					?>
					<option value="<?php echo esc_attr( $language ); ?>"<?php selected( $settings['language'], $language ); ?>><?php echo esc_html( $label ); ?></option>
					<?php
				}
				?>
			</select>

			<p class="description">
				<?php esc_html_e( 'The language the non-spintax content is written in.', 'page-generator-pro' ); ?>
			</p>
		</div>
	</div>

	<div id="skip-capitalized-words" class="wpzinc-option">
		<div class="left">
			<label for="skip_capitalized_words"><?php esc_html_e( 'Skip Capitalized Words', 'page-generator-pro' ); ?></label>
		</div>
		<div class="right">
			<select name="<?php echo esc_attr( $this->base->plugin->name ); ?>-spintax[skip_capitalized_words]" id="skip_capitalized_words" size="1">
				<option value=""<?php selected( $settings['skip_capitalized_words'], '' ); ?>><?php esc_html_e( 'No', 'page-generator-pro' ); ?></option>
				<option value="1"<?php selected( $settings['skip_capitalized_words'], '1' ); ?>><?php esc_html_e( 'Yes', 'page-generator-pro' ); ?></option>
			</select>

			<p class="description">
				<?php esc_html_e( 'If enabled, capitalized words will NOT have spintax applied to them.  This is useful for branded terms.', 'page-generator-pro' ); ?>
			</p>
		</div>
	</div>

	<div id="skip-words" class="wpzinc-option">
		<div class="left">
			<label for="protected_words"><?php esc_html_e( 'Skip Words', 'page-generator-pro' ); ?></label>
		</div>
		<div class="right">
			<textarea name="<?php echo esc_attr( $this->base->plugin->name ); ?>-spintax[protected_words]" id="protected_words" class="widefat" rows="10"><?php echo esc_textarea( $settings['protected_words'] ); ?></textarea>
			<p class="description">
				<?php esc_html_e( 'Words defined here will NOT have spintax applied to them. Keywords and Shortcodes are never spun. Enter one word per line.', 'page-generator-pro' ); ?>
			</p>
		</div>
	</div>

	<?php
	// Iterate through all registered spintax providers, outputting their settings in
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
				$field_name = 'page-generator-pro-spintax[' . $field_name . ']';
				include 'fields/row.php';
			}
			?>
		</div>
		<?php
	}
	?>
</div>
