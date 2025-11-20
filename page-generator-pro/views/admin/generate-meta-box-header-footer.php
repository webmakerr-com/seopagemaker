<?php
/**
 * Outputs the Header & Footer code metabox when adding/editing a Content Group
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

?>
<!-- Header Code -->
<div class="wpzinc-option">
	<div class="left">
		<label for="header_code"><?php esc_html_e( 'Header Code', 'page-generator-pro' ); ?></label>
	</div>
	<div class="full">
		<textarea name="<?php echo esc_attr( $this->base->plugin->name ); ?>[header_code]" id="header_code" rows="10" class="widefat no-wrap wpzinc-codemirror" style="height:300px"><?php echo esc_textarea( $this->settings['header_code'] ); ?></textarea>
		<p class="description">
			<?php
			esc_html_e( 'Enter any JavaScript, CSS or JSON-LD code which should be output in the <head> of each generated Page. Keywords are supported.', 'page-generator-pro' );
			?>
		</p>
	</div>
</div>

<!-- Footer Code -->
<div class="wpzinc-option">
	<div class="left">
		<label for="footer_code"><?php esc_html_e( 'Footer Code', 'page-generator-pro' ); ?></label>
	</div>
	<div class="full">
		<textarea name="<?php echo esc_attr( $this->base->plugin->name ); ?>[footer_code]" id="footer_code" rows="10" class="widefat no-wrap wpzinc-codemirror" style="height:300px"><?php echo esc_textarea( $this->settings['footer_code'] ); ?></textarea>
		<p class="description">
			<?php
			esc_html_e( 'Enter any HTML, JavaScript, CSS or JSON-LD code which should be output before the closing </body> tag of each generated Page.  Keywords and shortcodes are supported.', 'page-generator-pro' );
			?>
		</p>
	</div>
</div>

