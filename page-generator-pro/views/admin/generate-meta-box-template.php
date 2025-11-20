<?php
/**
 * Outputs the Template metabox when adding/editing a Content Groups
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

// Output Template Options for Post Types.
foreach ( $post_types_templates as $group_post_type => $templates ) {
	$template = ( isset( $this->settings['pageTemplate'][ $group_post_type ] ) ? $this->settings['pageTemplate'][ $group_post_type ] : '' );
	?>
	<div class="wpzinc-option post-type-conditional <?php echo esc_attr( $group_post_type ); ?>">
		<div class="full">
			<select name="<?php echo esc_attr( $this->base->plugin->name ); ?>[pageTemplate][<?php echo esc_attr( $group_post_type ); ?>]" id="<?php echo esc_attr( $group_post_type ); ?>_template" size="1" class="widefat">
				<option value="default"<?php selected( $template, 'default' ); ?>>
					<?php esc_attr_e( 'Default Template', 'page-generator-pro' ); ?>
				</option>
				<?php page_template_dropdown( $template, $group_post_type ); ?>
			</select>
		</div>
	</div>
	<?php
}
