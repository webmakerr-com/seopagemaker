<?php
/**
 * Outputs the Settings wrapper
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

?>
<header>
	<h1>
		<?php echo esc_html( $this->base->plugin->displayName ); ?>

		<span>
			<?php esc_html_e( 'Settings', 'page-generator-pro' ); ?>
		</span>
	</h1>
</header>

<hr class="wp-header-end" />

<div class="wrap">
	<?php
	// Output Success and/or Error Notices, if any exist.
	$this->base->get_class( 'notices' )->output_notices();
	?>

	<div class="wrap-inner">
		<!-- Tabs -->
		<h2 class="nav-tab-wrapper wpzinc-horizontal-tabbed-ui">
			<?php
			// Go through all registered settings panels.
			foreach ( $panels as $key => $panel ) {
				?>
				<a href="admin.php?page=page-generator-pro-settings&amp;tab=<?php echo esc_attr( $key ); ?>" class="nav-tab<?php echo ( $current_tab === $key ? ' nav-tab-active' : '' ); ?>">
					<?php
					// Check if the icon is a URL.
					// If so, output the image instead of the dashicon.
					if ( filter_var( $panel['icon'], FILTER_VALIDATE_URL ) ) {
						// Icon.
						?>
						<span style="background:url(<?php echo esc_attr( $panel['icon'] ); ?>) center no-repeat;" class="tab-icon"></span>
						<?php
					} else {
						// Dashicon.
						?>
						<span class="dashicons <?php echo esc_attr( $panel['icon'] ); ?>"></span>
						<?php
					}

					echo esc_html( $panel['label'] );
					?>
				</a>
				<?php
			}

			?>
		</h2>

		<!-- Form Start -->
		<form name="post" method="post" action="<?php echo ( isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '' ); ?>" id="<?php echo esc_attr( $this->base->plugin->name ); ?>" enctype="multipart/form-data">
			<div id="poststuff">
				<div id="post-body" class="metabox-holder columns-1">
					<!-- Content -->
					<div id="post-body-content">
						<div id="normal-sortables" class="meta-box-sortables ui-sortable publishing-defaults">  
							<?php
							// Load sub view.
							do_action( 'page_generator_pro_setting_panel_' . $current_tab );
							?>

							<!-- Save -->
							<div>
								<?php wp_nonce_field( $this->base->plugin->name, $this->base->plugin->name . '_nonce' ); ?>
								<input type="submit" name="submit" value="<?php esc_attr_e( 'Save', 'page-generator-pro' ); ?>" class="button button-primary" />
							</div>
						</div>
						<!-- /normal-sortables -->
					</div>
					<!-- /post-body-content -->
				</div>
			</div> 
		</form>  

	</div><!-- /.wrap-inner -->
</div><!-- /.wrap -->
