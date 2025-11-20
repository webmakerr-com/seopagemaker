<?php
/**
 * Outputs an error message in the TinyMCE modal telling the user that a Dynamic Element
 * could not be found.
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

?>
<form class="wpzinc-tinymce-popup">
	<div class="notice error" style="display:block;">
		<?php esc_html_e( 'The dynamic element could not be found. Check it is registered and its class initialized.', 'page-generator-pro' ); ?>
	</div>

	<div class="wpzinc-option buttons has-wpzinc-vertical-tabbed-ui">
		<div class="left">
			<button type="button" class="close button"><?php esc_html_e( 'Cancel', 'page-generator-pro' ); ?></button>
		</div>
	</div>
</form>
