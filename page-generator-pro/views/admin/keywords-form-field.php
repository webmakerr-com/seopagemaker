<?php
/**
 * Outputs a form field when adding or editing a Keyword
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

?>
<div class="wpzinc-option">
	<div class="left">
		<label for="<?php echo esc_attr( $option_name ); ?>"><?php echo esc_html( $option['label'] ); ?></label>
	</div>
	<div class="right <?php echo sanitize_html_class( $option['type'] ); ?>">
		<?php
		// Output Form Field.
		switch ( $option['type'] ) {
			case 'text':
				?>
				<input type="text" name="<?php echo esc_attr( $source_name ); ?>[<?php echo esc_attr( $option_name ); ?>]" id="<?php echo esc_attr( $option_name ); ?>" value="<?php echo esc_attr( $value ); ?>" class="widefat" />
				<?php
				break;

			case 'url':
				?>
				<input type="url" name="<?php echo esc_attr( $source_name ); ?>[<?php echo esc_attr( $option_name ); ?>]" id="<?php echo esc_attr( $option_name ); ?>" value="<?php echo esc_attr( $value ); ?>" class="widefat" />
				<?php
				break;

			case 'number':
				?>
				<input type="number" name="<?php echo esc_attr( $source_name ); ?>[<?php echo esc_attr( $option_name ); ?>]" id="<?php echo esc_attr( $option_name ); ?>" min="<?php echo esc_attr( $option['min'] ); ?>" max="<?php echo esc_attr( $option['max'] ); ?>" step="<?php echo esc_attr( $option['step'] ); ?>" value="<?php echo esc_attr( $value ); ?>" class="widefat" />
				<?php
				break;

			case 'toggle':
				?>
				<input type="checkbox" name="<?php echo esc_attr( $source_name ); ?>[<?php echo esc_attr( $option_name ); ?>]" id="<?php echo esc_attr( $option_name ); ?>" value="1"<?php echo esc_attr( $value ? ' checked' : '' ); ?> />
				<?php
				break;

			case 'textarea':
				?>
				<textarea name="<?php echo esc_attr( $source_name ); ?>[<?php echo esc_attr( $option_name ); ?>]" id="<?php echo esc_attr( $option_name ); ?>" rows="10" class="widefat no-wrap wpzinc-codemirror" style="height:300px"><?php echo esc_textarea( $value ); ?></textarea>
				<?php
				break;

			case 'file':
				?>
				<input type="file" name="<?php echo esc_attr( $source_name ); ?>[<?php echo esc_attr( $option_name ); ?>]" id="<?php echo esc_attr( $option_name ); ?>" />
				<?php
				break;

			case 'select':
				?>
				<select name="<?php echo esc_attr( $source_name ); ?>[<?php echo esc_attr( $option_name ); ?>]" size="1">
					<?php
					foreach ( $option['values'] as $key => $label ) {
						?>
						<option value="<?php echo esc_attr( $key ); ?>"<?php selected( $key, $value ); ?>><?php echo esc_attr( $label ); ?></option>
						<?php
					}
					?>
				</select>
				<?php
				break;

			case 'media_library':
				$file = get_attached_file( $value );
				?>
				<div class="wpzinc-media-library-selector"
					data-input-name="<?php echo esc_attr( $source_name ); ?>[<?php echo esc_attr( $option_name ); ?>]"
					data-file-type="<?php echo esc_attr( $option['file_type'] ); ?>">

					<ul>
						<?php
						if ( $file ) {
							?>
							<li class="wpzinc-media-library-attachment">
								<div class="wpzinc-media-library-insert">
									<input type="hidden" name="<?php echo esc_attr( $source_name ); ?>[<?php echo esc_attr( $option_name ); ?>]" value="<?php echo esc_attr( $value ); ?>" />
									<?php echo esc_html( basename( $file ) ); ?>
								</div>

								<a href="#" class="wpzinc-media-library-remove" title="<?php esc_attr_e( 'Remove', 'page-generator-pro' ); ?>"><?php esc_html_e( 'Remove', 'page-generator-pro' ); ?></a>
							</li>
							<?php
						}
						?>
					</ul>

					<a href="#" class="wpzinc-media-library-insert button button-secondary">
						<?php esc_html_e( 'Choose File from Media Library', 'page-generator-pro' ); ?>
					</a>
				</div>
				<?php
				break;

			case 'preview':
				?>
				<button class="button button-secondary button-small page-generator-pro-refresh-terms" data-keyword="<?php echo esc_attr( $keyword['keyword'] ); ?>" data-nonce="<?php echo esc_attr( wp_create_nonce( 'refresh_term_keyword' ) ); ?>">
					<?php esc_html_e( 'Refresh Data', 'page-generator-pro' ); ?>
				</button>
				<span class="spinner"></span>
				<table class="page-generator-pro-keywords-table widefat striped" style="width:100%;" data-keyword-id="<?php echo esc_attr( $keyword_id ); ?>">
					<thead>
						<tr>
							<?php
							foreach ( $keyword['columnsArr'] as $column ) {
								?>
								<th><?php echo esc_html( $column ); ?></th>
								<?php
							}
							?>
						</tr>
					</thead>
				</table>
				<?php
				break;
		}

		// Output Description.
		if ( isset( $option['description'] ) ) {
			?>
			<p class="description">
				<?php
				if ( is_array( $option['description'] ) ) {
					foreach ( $option['description'] as $description ) {
						echo $description . '<br />'; // phpcs:ignore WordPress.Security.EscapeOutput
					}
				} else {
					echo $option['description']; // phpcs:ignore WordPress.Security.EscapeOutput
				}
				?>
			</p>
			<?php
		}
		?>
	</div>
</div>
