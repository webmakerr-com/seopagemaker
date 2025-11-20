<?php
/**
 * Outputs a TinyMCE form for a Dynamic Element.
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

?>
<!-- .wp-core-ui ensures styles are applied on frontend editors for e.g. buttons.css -->
<form class="wpzinc-tinymce-popup wp-core-ui">
	<input type="hidden" name="shortcode" value="page-generator-pro-<?php echo esc_attr( $shortcode['name'] ); ?>" />
	<input type="hidden" name="editor_type" value="<?php echo esc_attr( $editor_type ); // quicktags|tinymce. ?>" />

	<?php
	// Output each Field.
	foreach ( $shortcode['fields'] as $field_name => $field ) {
		include 'fields/row.php';
	}
	?>
</form>
