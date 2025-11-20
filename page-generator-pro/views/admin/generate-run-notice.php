<?php
/**
 * Outputs an error message when attempting to start generation via browser
 * fails.
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

?>
<header>
	<h1>
		<?php echo esc_html( $this->base->plugin->displayName ); ?>

		<span>
			<?php esc_html_e( 'Generate', 'page-generator-pro' ); ?>
		</span>
	</h1>
</header>

<hr class="wp-header-end" />

<div class="wrap">
	<?php
	// Output Success and/or Error Notices, if any exist.
	$this->base->get_class( 'notices' )->output_notices();

	// Display Return button, if return URL specified.
	if ( isset( $return_url ) ) {
		?>
		<div class="wrap-inner">
			<!-- Return Button -->
			<a href="<?php echo esc_attr( $return_url ); ?>" class="button button-primary">
				<?php esc_html_e( 'Return to Group', 'page-generator-pro' ); ?>
			</a>
		</div>
		<?php
	}
	?>
</div>
