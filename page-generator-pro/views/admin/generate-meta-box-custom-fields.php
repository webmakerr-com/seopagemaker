<?php
/**
 * Outputs the Custom Fields metabox when adding/editing a Content Groups
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

?>
<div class="wpzinc-option"> 
	<div class="left">
		<label for="store_keywords"><?php esc_html_e( 'Store Keywords?', 'page-generator-pro' ); ?></strong>
	</div>
	<div class="right">
		<input type="checkbox" id="store_keywords" name="<?php echo esc_attr( $this->base->plugin->name ); ?>[store_keywords]" value="1"<?php checked( $this->settings['store_keywords'], 1 ); ?> />

		<p class="description">
			<?php esc_html_e( 'If checked, each generated Page/Post will store keyword and term key/value pairs in the Page/Post\'s Custom Fields. This is useful for subsequently querying Custom Field Metadata in e.g. Related Links.', 'page-generator-pro' ); ?>
		</p>
	</div>
</div>

<!-- Custom Fields -->
<div class="wpzinc-option">
	<div class="full">
		<table class="widefat">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Meta Key', 'page-generator-pro' ); ?></th>
					<th><?php esc_html_e( 'Meta Value', 'page-generator-pro' ); ?></th>
					<th><?php esc_html_e( 'Actions', 'page-generator-pro' ); ?></th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<td colspan="3">
						<button class="wpzinc-add-table-row button" data-table-row-selector="custom-field-row">
							<?php esc_html_e( 'Add Custom Field', 'page-generator-pro' ); ?>
						</button>
						<?php
						// Iterate through Dynamic Elements, outputting buttons for each.
						foreach ( $shortcodes as $shortcode_name => $shortcode ) {
							?>
							<button class="<?php echo esc_attr( $shortcode_name ); ?> dynamic-element button" data-table-row-selector="custom-field-row" data-shortcode="<?php echo esc_attr( $shortcode_name ); ?>" title="<?php echo esc_attr( $shortcode['title'] ); ?>">
								<?php echo esc_html( $shortcode['title'] ); ?>
							</button>
							<?php
						}
						?>
					</td>
				</tr>
			</tfoot>
			<tbody class="is-sortable">
				<?php
				// Existing Custom Fields.
				if ( is_array( $this->settings['meta'] ) && count( $this->settings['meta'] ) > 0 ) {
					foreach ( $this->settings['meta']['key'] as $i => $key ) {
						?>
						<tr class="custom-field-row">
							<td>
								<input type="text" name="<?php echo esc_attr( $this->base->plugin->name ); ?>[meta][key][]" value="<?php echo esc_attr( $key ); ?>" placeholder="<?php esc_attr_e( 'Meta Key', 'page-generator-pro' ); ?>" class="widefat" />
							</td>
							<td>
								<textarea name="<?php echo esc_attr( $this->base->plugin->name ); ?>[meta][value][]" placeholder="<?php esc_attr_e( 'Meta Value', 'page-generator-pro' ); ?>" class="widefat"><?php echo esc_textarea( $this->settings['meta']['value'][ $i ] ); ?></textarea>
							</td>
							<td>
								<a href="#" class="move-row">
									<span class="dashicons dashicons-move "></span>
									<?php esc_html_e( 'Move', 'page-generator-pro' ); ?>
								</a>

								<a href="#" class="wpzinc-delete-table-row">
									<span class="dashicons dashicons-trash"></span>
									<?php esc_html_e( 'Delete', 'page-generator-pro' ); ?>
								</a>
							</td>
						</tr>
						<?php
					}
				}
				?>

				<tr class="custom-field-row hidden">
					<td>
						<input type="text" name="<?php echo esc_attr( $this->base->plugin->name ); ?>[meta][key][]" value="" placeholder="<?php esc_attr_e( 'Meta Key', 'page-generator-pro' ); ?>" class="widefat" />
					</td>
					<td>
						<textarea name="<?php echo esc_attr( $this->base->plugin->name ); ?>[meta][value][]" placeholder="<?php esc_attr_e( 'Meta Value', 'page-generator-pro' ); ?>" class="widefat"></textarea>
					</td>
					<td>
						<a href="#" class="move-row">
							<span class="dashicons dashicons-move "></span>
							<?php esc_html_e( 'Move', 'page-generator-pro' ); ?>
						</a>

						<a href="#" class="wpzinc-delete-table-row">
							<span class="dashicons dashicons-trash"></span>
							<?php esc_html_e( 'Delete', 'page-generator-pro' ); ?>
						</a>
					</td>
				</tr>
			</tbody>
		</table>
	</div>
</div>
