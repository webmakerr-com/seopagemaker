<?php
/**
 * Outputs the TinyMCE editor for the description field for Term Groups > Add New
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

?>
<div class="form-field term-description-wrap">
	<label for="tag-description"><?php esc_html_e( 'Description', 'page-generator-pro' ); ?></label>
	<?php
	wp_editor(
		'',
		'html-tag-description',
		array(
			'textarea_name' => 'description',
			'textarea_rows' => 7,
			'editor_class'  => 'i18n-multilingual',
		)
	);
	?>
	<p><?php esc_html_e( 'The description is not prominent by default; however, some themes may show it.', 'page-generator-pro' ); ?></p>

	<script>
		// Remove the non-html field.
		jQuery('textarea#tag-description').closest('.form-field').remove();

		jQuery(function () {
			jQuery('#addtag').on('mousedown', '#submit', function () {
				tinyMCE.triggerSave();

				jQuery(document).bind('ajaxSuccess.pgp_add_term', function () {
					if (tinyMCE.activeEditor) {
						tinyMCE.activeEditor.setContent('');
					}
					jQuery(document).unbind('ajaxSuccess.pgp_add_term', false);
				});
			});
		});
	</script>
</div>
