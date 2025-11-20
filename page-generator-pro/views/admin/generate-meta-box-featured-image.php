<?php
/**
 * Outputs the Featured Image metabox when adding/editing a Content Groups
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

?>
<div class="wpzinc-option">
	<div class="full">
		<label for="featured_image_source"><?php esc_html_e( 'Image Source', 'page-generator-pro' ); ?></label>
	</div>
	<div class="full">
		<select name="<?php echo esc_attr( $this->base->plugin->name ); ?>[featured_image_source]" id="featured_image_source" size="1" class="widefat">
			<?php
			foreach ( $featured_image_sources as $featured_image_source => $label ) {
				?>
				<option value="<?php echo esc_attr( $featured_image_source ); ?>"<?php selected( $this->settings['featured_image_source'], $featured_image_source ); ?>><?php echo esc_attr( $label ); ?></option>
				<?php
			}
			?>
		</select>
	</div>
</div>

<div class="wpzinc-vertical-tabbed-ui no-border featured_image">
	<!-- Tabs -->
	<ul class="wpzinc-nav-tabs wpzinc-js-tabs" data-panels-container="#featured-image-container" data-panel=".featured-image-panel" data-active="wpzinc-nav-tab-vertical-active">
		<?php
		$first_tab = true;
		foreach ( $featured_image_tabs as $modal_tab_name => $modal_tab ) {
			?>
			<li class="wpzinc-nav-tab<?php echo esc_attr( ( isset( $modal_tab['class'] ) ? ' ' . $modal_tab['class'] : '' ) ); ?>">
				<a href="#featured-image-<?php echo esc_attr( $modal_tab_name ); ?>"<?php echo ( $first_tab ? ' class="wpzinc-nav-tab-vertical-active"' : '' ); ?>>
					<?php echo esc_html( $modal_tab['label'] ); ?>
				</a>
			</li>
			<?php
			$first_tab = false;
		}
		?>
	</ul>

	<!-- Content -->
	<div id="featured-image-container" class="wpzinc-nav-tabs-content no-padding">
		<?php
		// Output each Tab Panel.
		foreach ( $featured_image_tabs as $modal_tab_name => $modal_tab ) {
			?>
			<div id="featured-image-<?php echo esc_attr( $modal_tab_name ); ?>" class="featured-image-panel">
				<div class="postbox">
					<header>
						<h3><?php echo esc_html( $modal_tab['label'] ); ?></h3>
						<?php
						if ( isset( $modal_tab['description'] ) && ! empty( $modal_tab['description'] ) ) {
							?>
							<p class="description">
								<?php echo $modal_tab['description']; // phpcs:ignore WordPress.Security.EscapeOutput ?>
							</p>
							<?php
						}
						?>
					</header>

					<?php
					// Iterate through this tab's field names.
					foreach ( $modal_tab['fields'] as $field_name ) {
						// Skip if this field doesn't exist.
						if ( ! isset( $featured_image_fields[ $field_name ] ) ) {
							continue;
						}

						// Fetch the field properties.
						$field = $featured_image_fields[ $field_name ];

						// Rename to page-generator-pro[$field_name].
						$field_name = $this->base->plugin->name . '[' . $field_name . ']';

						// Output Field.
						include 'fields/row.php';
					}
					?>
				</div>
			</div>
			<?php
		}
		?>
	</div>
</div>
