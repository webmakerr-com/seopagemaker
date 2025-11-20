<?php
/**
 * Outputs the Description metabox when adding/editing a Content Groups
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

?>
<div class="wpzinc-option">
	<div class="full">
		<textarea name="<?php echo esc_attr( $this->base->plugin->name ); ?>[description]" id="description" class="widefat"><?php echo esc_textarea( $this->settings['description'] ); ?></textarea>
		<p class="description">
			<?php esc_html_e( 'An internal description for this Content Group. Not output on generated content.', 'page-generator-pro' ); ?>
		</p>
	</div>
</div>
