<?php
/**
 * Outputs the Excerpt metabox when adding/editing a Content Groups
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

?>
<!-- Excerpt -->
<div class="wpzinc-option">
	<div class="left">
		<label for="post_excerpt"><?php esc_html_e( 'Excerpt', 'page-generator-pro' ); ?></strong>
	</div>
	<div class="right">
		<?php $this->base->get_class( 'keywords' )->output_dropdown( $this->keywords, 'excerpt' ); ?>
	</div>
	<div class="full">
		<textarea name="<?php echo esc_attr( $this->base->plugin->name ); ?>[excerpt]" id="post_excerpt" class="widefat"><?php echo esc_textarea( $this->settings['excerpt'] ); ?></textarea>
	</div>
</div>
