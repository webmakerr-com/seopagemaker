<?php
/**
 * Outputs the TinyMCE editor for the description field for Term Groups > Edit
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

?>
<tr class="form-field term-description-wrap">
	<th scope="row"><label for="description"><?php esc_html_e( 'Description', 'page-generator-pro' ); ?></label></th>
	<td>
		<?php
		wp_editor(
			htmlspecialchars_decode( $term->description ),
			'html-tag-description',
			array(
				'textarea_name' => 'description',
				'textarea_rows' => 10,
				'editor_class'  => 'i18n-multilingual',
			)
		);
		?>
		<p class="description"><?php esc_html_e( 'The description is not prominent by default; however, some themes may show it.', 'page-generator-pro' ); ?></p>
	</td>
	<script>
		// Remove the non-html field.
		jQuery('textarea#description').closest('.form-field').remove();
	</script>
</tr>
