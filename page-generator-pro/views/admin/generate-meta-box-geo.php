<?php
/**
 * Outputs the Geolocation metabox when adding/editing a Content Groups
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

?>
<div class="wpzinc-option">    
	<div class="left">
		<label for="latitude"><?php esc_html_e( 'Latitude', 'page-generator-pro' ); ?></label>
	</div>
	<div class="right">
		<input type="text" name="<?php echo esc_attr( $this->base->plugin->name ); ?>[latitude]" id="latitude" value="<?php echo esc_attr( $this->settings['latitude'] ); ?>" placeholder="<?php esc_attr_e( 'Latitude', 'page-generator-pro' ); ?>" class="widefat" />

		<p class="description">
			<?php esc_html_e( 'Enter the Keyword that stores the Latitude.  This is used by the Related Links Shortcode for displaying Related Links by Radius.', 'page-generator-pro' ); ?>
		</p>
	</div>
</div>

<div class="wpzinc-option">    
	<div class="left">
		<label for="longitude"><?php esc_html_e( 'Longitude', 'page-generator-pro' ); ?></label>
	</div>
	<div class="right">
		<input type="text" name="<?php echo esc_attr( $this->base->plugin->name ); ?>[longitude]" id="longitude" value="<?php echo esc_attr( $this->settings['longitude'] ); ?>" placeholder="<?php esc_attr_e( 'Longitude', 'page-generator-pro' ); ?>" class="widefat" />

		<p class="description">
			<?php esc_html_e( 'Enter the Keyword that stores the Longitude.  This is used by the Related Links Shortcode for displaying Related Links by Radius.', 'page-generator-pro' ); ?>
		</p>
	</div>
</div>
