<?php
/**
 * Outputs the Permalink metabox when adding/editing a Content Groups
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

?>
<!-- Permalink -->
<div class="wpzinc-option">
	<div class="left">
		<label for="permalink"><?php esc_html_e( 'Permalink', 'page-generator-pro' ); ?></label>
	</div>
	<div class="right">
		<?php $this->base->get_class( 'keywords' )->output_dropdown( $this->keywords, 'permalink' ); ?>
	</div>
	<div class="full">
		<input type="text" id="permalink" name="<?php echo esc_attr( $this->base->plugin->name ); ?>[permalink]" id="permalink" value="<?php echo esc_attr( $this->settings['permalink'] ); ?>" class="widefat" />

		<p class="description">
			<?php esc_html_e( 'Letters, numbers, underscores and dashes only. Specifying a Permalink with Keywords is highly recommended to avoid duplicate content and ensure Overwrite functionality works correctly.', 'page-generator-pro' ); ?>
		</p>
	</div>
</div>
