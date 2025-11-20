<?php
/**
 * Outputs the Parent Term and Taxonomy fields when adding a Term Group
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

wp_nonce_field( 'save_generate', $this->base->plugin->name . '_nonce' );
?>

<div class="form-field term-parent">
	<label for="tax"><?php esc_html_e( 'Parent Term', 'page-generator-pro' ); ?></label>
	<input type="text" name="parent_term" value="" class="widefat" />

	<p class="description">
		<?php esc_html_e( 'The parent Taxonomy Term ID or Title to assign Terms to.  Keywords are supported in this field. If the parent Taxonomy Term does not exist, it will be created.', 'page-generator-pro' ); ?>
	</p>
</div>

<div class="form-field term-taxonomy-wrap">
	<label for="tax"><?php esc_html_e( 'Taxonomy', 'page-generator-pro' ); ?></label>
	<select name="tax" id="tax" size="1" class="widefat">
		<?php
		foreach ( $taxonomies as $group_taxonomy ) {
			?>
			<option value="<?php echo esc_attr( $group_taxonomy->name ); ?>"><?php echo esc_attr( $group_taxonomy->label ); ?></option>
			<?php
		}
		?>
	</select>

	<p class="description">
		<?php esc_html_e( 'The taxonomy to generate Terms for.', 'page-generator-pro' ); ?>
	</p>
</div>
