<?php
/**
 * Outputs the Gutenberg Actions metabox when adding/editing a Content Groups
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

?>
<div class="wpzinc-option">
	<div class="full">
		<?php
		// Nonce field.
		wp_nonce_field( 'save_generate', $this->base->plugin->name . '_nonce' );
		?>

		<span class="test">
			<a href="<?php echo esc_attr( admin_url( 'edit.php?post_type=' . $this->base->get_class( 'post_type' )->post_type_name . '&' . $this->base->plugin->name . '-action=test&id=' . $group_id . '&type=' . $this->settings['group_type'] ) ); ?>" class="button button-primary"><?php esc_html_e( 'Test', 'page-generator-pro' ); ?></a>
		</span>

		<span class="generate">
			<a href="<?php echo esc_attr( admin_url( 'admin.php?page=' . $this->base->plugin->name . '-generate&id=' . $group_id . '&type=' . $this->settings['group_type'] ) ); ?>" class="button button-primary"><?php esc_html_e( 'Generate via Browser', 'page-generator-pro' ); ?></a>
		</span>
	</div>
</div>
<?php
// Delete Generated Content, if any exist.
if ( $this->settings['generated_pages_count'] > 0 ) {
	?>
	<div class="wpzinc-option">
		<div class="full">
			<?php
			if ( $this->settings['group_type'] === 'content' ) {
				?>
				<span class="trash_generated_content">
					<a href="<?php echo esc_attr( admin_url( 'edit.php?post_type=' . $this->base->get_class( 'post_type' )->post_type_name . '&' . $this->base->plugin->name . '-action=trash-generated-content&id=' . $group_id . '&type=' . $this->settings['group_type'] ) ); ?>" class="button wpzinc-button-red trash-generated-content" data-group-id="<?php echo esc_attr( $group_id ); ?>" data-limit="<?php echo esc_attr( $limit ); ?>" data-total="<?php echo esc_attr( $this->settings['generated_pages_count'] ); ?>"><?php esc_html_e( 'Trash Generated Content', 'page-generator-pro' ); ?></a>
				</span>
				<?php
			}
			?>
			<span class="delete_generated_content">
				<a href="<?php echo esc_attr( admin_url( 'edit.php?post_type=' . $this->base->get_class( 'post_type' )->post_type_name . '&' . $this->base->plugin->name . '-action=delete-generated-content&id=' . $group_id . '&type=' . $this->settings['group_type'] ) ); ?>" class="button wpzinc-button-red delete-generated-content" data-group-id="<?php echo esc_attr( $group_id ); ?>" data-limit="<?php echo esc_attr( $limit ); ?>" data-total="<?php echo esc_attr( $this->settings['generated_pages_count'] ); ?>"><?php esc_html_e( 'Delete Generated Content', 'page-generator-pro' ); ?></a>
			</span>
		</div>
	</div>
	<?php
}
