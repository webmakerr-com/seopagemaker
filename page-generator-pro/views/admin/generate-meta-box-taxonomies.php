<?php
/**
 * Outputs the Taxonomies metabox when adding/editing a Content Groups
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

// Check if hierarchal or tag based.
switch ( $taxonomy->hierarchical ) {

	case true:
		// Category based taxonomy.
		$terms = get_terms(
			array(
				'taxonomy'   => $taxonomy->name,
				'hide_empty' => 0,
			)
		);
		?>
		<div class="wpzinc-option taxonomy post-type-conditional <?php echo esc_attr( trim( $post_types_string ) ); ?>">
			<div class="left">
				<strong><?php echo esc_html( $taxonomy->labels->name ); ?></strong>
			</div>
			<div class="right">
				<a href="#" class="button button-small deselect-all" data-list="#taxonomy-list-<?php echo esc_attr( $taxonomy->name ); ?>">
					<?php esc_html_e( 'Deselect All', 'page-generator-pro' ); ?>
				</a>
			</div>

			<div class="full tax-selection">
				<div class="tabs-panel">
					<ul id="taxonomy-list-<?php echo esc_attr( $taxonomy->name ); ?>" class="categorychecklist form-no-clear">				                    			
						<?php
						foreach ( $terms as $term_key => $group_term ) {
							?>
							<li>
								<label class="selectit">
									<input type="checkbox" name="<?php echo esc_attr( $this->base->plugin->name ); ?>[tax][<?php echo esc_attr( $taxonomy->name ); ?>][<?php echo esc_attr( $group_term->term_id ); ?>]" value="1"<?php echo ( isset( $this->settings['tax'][ $taxonomy->name ][ $group_term->term_id ] ) ? ' checked' : '' ); ?> />
									<?php echo esc_html( $group_term->name ); ?>      
								</label>
							</li>
							<?php
						}
						?>
					</ul>
				</div>
				<input type="search" name="search" data-list="#taxonomy-list-<?php echo esc_attr( $taxonomy->name ); ?>" placeholder="<?php esc_attr_e( 'Search', 'page-generator-pro' ); ?>" class="widefat" />
			</div>

			<div class="full">
				<input type="text" name="<?php echo esc_attr( $this->base->plugin->name ); ?>[tax][<?php echo esc_attr( $taxonomy->name ); ?>][0]" value="<?php echo esc_attr( ( isset( $this->settings['tax'][ $taxonomy->name ][0] ) ? $this->settings['tax'][ $taxonomy->name ][0] : '' ) ); ?>" class="widefat" placeholder="<?php esc_attr_e( 'Enter new taxonomy terms to create here.', 'page-generator-pro' ); ?>" />
			</div>
		</div>
		<?php
		break;

	case false:
		// Tag based taxonomy.
		?>
		<div class="wpzinc-option taxonomy post-type-conditional <?php echo esc_attr( trim( $post_types_string ) ); ?>">
			<div class="full">
				<strong><?php echo esc_html( $taxonomy->labels->name ); ?></strong>
			</div>

			<div class="full">
				<input type="text" name="<?php echo esc_attr( $this->base->plugin->name ); ?>[tax][<?php echo esc_attr( $taxonomy->name ); ?>]" value="<?php echo esc_attr( ( isset( $this->settings['tax'][ $taxonomy->name ] ) ? $this->settings['tax'][ $taxonomy->name ] : '' ) ); ?>" class="widefat" />
			</div>
		</div>
		<?php
		break;
}
