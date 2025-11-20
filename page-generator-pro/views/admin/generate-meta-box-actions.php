<?php
/**
 * Outputs the Actions metabox when adding/editing a Content Groups
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

if ( ! $bottom ) {
	// Nonce field.
	wp_nonce_field( 'save_generate', $this->base->plugin->name . '_nonce' );
}
?>

<!-- 
#submitpost is required, so WordPress can unload the beforeunload.edit-post JS event.
If we didn't do this, the user would always get a JS alert asking them if they want to navigate
away from the page as they may lose their changes
-->
<div class="submitbox" id="submitpost">
	<div id="publishing-action">
		<div class="wpzinc-option">
			<div class="full">
			<?php
			// Save.
			if ( isset( $post ) && ( ! in_array( $post->post_status, array( 'publish', 'future', 'private' ), true ) || 0 === $post->ID ) ) {
				// Publish.
				?>
				<input name="original_publish" type="hidden" id="original_publish<?php echo esc_attr( $bottom ); ?>" value="<?php esc_attr_e( 'Publish', 'page-generator-pro' ); ?>" />
				<?php
				submit_button(
					__( 'Save', 'page-generator-pro' ),
					'primary button-large',
					'publish',
					false,
					array(
						'id' => 'publish' . $bottom,
					)
				);
				?>
				<?php
			} else {
				// Update.
				?>
				<input name="original_publish" type="hidden" id="original_publish<?php echo esc_attr( $bottom ); ?>" value="<?php esc_attr_e( 'Update', 'page-generator-pro' ); ?>" />
				<?php
				submit_button(
					__( 'Save', 'page-generator-pro' ),
					'primary button-large',
					'publish',
					false,
					array(
						'id' => 'publish' . $bottom,
					)
				);
				?>
				<?php
			}

			// Test.
			if ( $this->base->get_class( 'groups' )->generates_content( $group_id ) ) {
				// Below newline is deliberate.
				?>

				<?php
				submit_button(
					__( 'Test', 'page-generator-pro' ),
					'primary button-large',
					'test',
					false,
					array(
						'id' => 'test' . $bottom,
					)
				);
				?>
				<?php
			}
			?>
			</div>
		</div>

		<?php
		// Display Generate options if this Content Group can generate content.
		if ( $this->base->get_class( 'groups' )->generates_content( $group_id ) ) {
			?>
			<div class="wpzinc-option">
				<?php
				submit_button(
					__( 'Generate via Browser', 'page-generator-pro' ),
					'primary button-large margin-bottom',
					'generate',
					false,
					array(
						'id' => 'generate' . $bottom,
					)
				);
				?>
				<br />
				<?php
				submit_button(
					__( 'Generate via Server', 'page-generator-pro' ),
					'primary button-large',
					'generate_server',
					false,
					array(
						'id' => 'generate_server' . $bottom,
					)
				);
				?>
			</div>
			<?php
		}
		?>
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
					<a href="<?php echo esc_attr( admin_url( 'edit.php?post_type=' . $this->base->get_class( 'post_type' )->post_type_name . '&' . $this->base->plugin->name . '-action=trash-generated-content&id=' . $group_id . '&type=' . $this->settings['group_type'] ) ); ?>" class="button wpzinc-button-red trash-generated-content" data-group-id="<?php echo esc_attr( $group_id ); ?>" data-limit="<?php echo esc_attr( $limit ); ?>" data-total="<?php echo esc_attr( $this->settings['generated_pages_count'] ); ?>">
						<?php esc_html_e( 'Trash Generated Content', 'page-generator-pro' ); ?>
					</a>
				</span>
				<br />
				<?php
			}
			?>
			<span class="delete_generated_content">
				<a href="<?php echo esc_attr( admin_url( 'edit.php?post_type=' . $this->base->get_class( 'post_type' )->post_type_name . '&' . $this->base->plugin->name . '-action=delete-generated-content&id=' . $group_id . '&type=' . $this->settings['group_type'] ) ); ?>" class="button wpzinc-button-red delete-generated-content" data-group-id="<?php echo esc_attr( $group_id ); ?>" data-limit="<?php echo esc_attr( $limit ); ?>" data-total="<?php echo esc_attr( $this->settings['generated_pages_count'] ); ?>">
					<?php esc_html_e( 'Delete Generated Content', 'page-generator-pro' ); ?>
				</a>
			</span>
		</div>
	</div>
	<?php
}
