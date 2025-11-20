<?php
/**
 * Outputs the Parent Term and Taxonomy fields when editing a Term Group
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

?>
<tr class="form-field term-parent">
	<th scope="row">
		<label for="tax"><?php esc_html_e( 'Parent Term', 'page-generator-pro' ); ?></label>
	</th>
	<td>
		<input type="text" name="parent_term" value="<?php echo esc_attr( $this->settings['parent_term'] ); ?>" class="widefat" />

		<p class="description">
			<?php esc_html_e( 'The parent Taxonomy Term ID or Title to assign Terms to.  Keywords are supported in this field. If the parent Taxonomy Term does not exist, it will be created.', 'page-generator-pro' ); ?>
		</p>
	</td>
</tr>

<tr class="form-field form-required term-taxonomy-wrap">
	<th scope="row">
		<label for="tax"><?php esc_html_e( 'Taxonomy', 'page-generator-pro' ); ?></label>
	</th>
	<td>
		<select name="tax" id="tax" size="1" class="widefat">
			<?php
			foreach ( $taxonomies as $group_taxonomy ) {
				?>
				<option value="<?php echo esc_attr( $group_taxonomy->name ); ?>"<?php selected( $group_taxonomy->name, $this->settings['taxonomy'] ); ?>><?php echo esc_attr( $group_taxonomy->label ); ?></option>
				<?php
			}
			?>
		</select>
		<p class="description">
			<?php esc_html_e( 'The taxonomy to generate Terms for.', 'page-generator-pro' ); ?>
		</p>
	</td>
</tr>
