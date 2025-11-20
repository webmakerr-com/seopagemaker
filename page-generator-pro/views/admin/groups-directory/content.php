<?php
/**
 * Outputs the template for Content Groups > Add New Directory
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

?>
<form action="admin.php?page=<?php echo esc_attr( $this->base->plugin->name ); ?>-groups-directory&step=<?php echo esc_attr( $this->step ); ?>" method="POST" id="wpzinc-onboarding-form">
	<div id="wpzinc-onboarding-content">
		<?php require 'content-' . $this->step . '.php'; ?>
	</div>

	<div id="wpzinc-onboarding-footer">
		<?php
		if ( isset( $back_button_label ) ) {
			?>
			<div class="left">
				<a href="<?php echo esc_attr( $back_button_url ); ?>" class="button"><?php echo esc_html( $back_button_label ); ?></a>
			</div>
			<?php
		}

		if ( isset( $next_button_label ) ) {
			?>
			<div class="right">
				<input type="hidden" name="configuration" value='<?php echo wp_json_encode( $this->configuration, JSON_HEX_APOS ); ?>' />
				<?php wp_nonce_field( $this->base->plugin->name, $this->base->plugin->name . '_nonce' ); ?>
				<button class="button button-primary button-large"><?php echo esc_html( $next_button_label ); ?></button>
			</div>
			<?php
		}
		?>
	</div>
</form>
