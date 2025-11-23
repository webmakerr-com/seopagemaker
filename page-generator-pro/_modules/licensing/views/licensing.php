<?php
/**
 * Outputs the licensing screen.
 *
 * @package LicensingUpdateManager
 * @author WP Zinc
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<header>
<h1>
<?php echo esc_html( $this->plugin->displayName ); ?>

<span>
<?php esc_html_e( 'License Required', $this->plugin->name ); // phpcs:ignore WordPress.WP.I18n ?>
</span>
</h1>
</header>

<hr class="wp-header-end" />

<div class="wrap">
	<div class="wrap-inner">
		<?php
		// Notices.
		if ( isset( $this->message ) ) {
			?>
						<div class="updated notice">
				<p>
					<?php
					echo wp_kses(
						$this->message,
						array(
							'a'  => array(
								'href'   => array(),
								'target' => array(),
							),
							'br' => array(),
						)
					);
					?>
				</p>
			</div>
			<?php
		}
		if ( isset( $this->errorMessage ) ) { // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
			?>
			<div class="error notice">
				<p>
					<?php
					echo wp_kses(
						$this->errorMessage, // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
						array(
							'a'  => array(
								'href'   => array(),
								'target' => array(),
							),
							'br' => array(),
						)
					);
					?>
				</p>	
			</div>  
			<?php
		}
		?>

		<div id="poststuff">
			<div id="post-body" class="metabox-holder columns-2">
				<!-- Content -->
				<div id="post-body-content">

					<!-- Form Start -->
					<form name="post" method="post" action="<?php echo ( isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '' ); ?>">
						<div id="normal-sortables" class="meta-box-sortables ui-sortable">                        
<div class="postbox">
<h3 class="hndle"><?php esc_html_e( 'License Key', $this->plugin->name ); // phpcs:ignore WordPress.WP.I18n ?></h3>

<div class="inside">
<p><?php esc_html_e( 'Enter your license key to unlock Page Generator Pro. Access to all plugin features is blocked until a valid key is provided.', $this->plugin->name ); // phpcs:ignore WordPress.WP.I18n ?></p>
</div>

                                                                <?php
                                                                // If the license key is defined in wp-config as a constant, just display it here and don't offer the option to edit.
                                                                if ( $this->is_license_key_a_constant() ) {
                                                                        ?>
                                                                        <div class="wpzinc-option">
                                                                                <div class="full">
                                                                                        <input type="text" name="ignored" value="<?php echo esc_attr( $this->get_license_key() ); ?>" class="widefat" disabled="disabled" />
                                                                                </div>
                                                                        </div>
                                                                        <?php
                                                                } else {
                                                                        // Get from options table.
$license_key = get_option( 'webmakerr_lpb_license' );
                                                                        ?>
                                                                        <div class="inside">
                                                                                <input type="text" name="<?php echo esc_attr( $this->plugin->name ); ?>[licenseKey]" value="<?php echo esc_attr( $license_key ); ?>" class="widefat" />
                                                                        </div>
                                                                        <div class="inside">
                                                                                <input type="submit" name="submit" value="<?php esc_attr_e( 'Save' ); // phpcs:ignore WordPress.WP.I18n ?>" class="button button-primary" />
                                                                        </div>
                                                                        <?php
                                                                }
                                                                ?>
                                                        </div>
                                                        <!-- /postbox -->
                                                </div>
						<!-- /normal-sortables -->
					</form>
					<!-- /form end -->

				</div>
				<!-- /post-body-content -->

				<!-- Sidebar -->
				<div id="postbox-container-1" class="postbox-container">
					<!-- About -->
					<div class="postbox">
						<h3 class="hndle"><?php esc_html_e( 'Version' ); // phpcs:ignore WordPress.WP.I18n ?></h3>

						<div class="inside">
							<?php echo esc_html( $this->plugin->version ); ?>
						</div>
					</div>

					<!-- Support -->
					<div class="postbox">
						<h3 class="hndle"><span><?php esc_html_e( 'Help' ); // phpcs:ignore WordPress.WP.I18n ?></span></h3>

						<div class="inside">
							<a href="<?php echo esc_attr( isset( $this->plugin->documentation_url ) ? $this->plugin->documentation_url : '#' ); ?>" class="button" rel="noopener" target="_blank">
								<?php esc_html_e( 'Documentation' ); // phpcs:ignore WordPress.WP.I18n ?>
							</a>
							<a href="<?php echo esc_attr( isset( $this->plugin->support_url ) ? $this->plugin->support_url : '#' ); ?>" class="button button-secondary" rel="noopener" target="_blank">
								<?php esc_html_e( 'Help' ); // phpcs:ignore WordPress.WP.I18n ?>
							</a>
						</div>
					</div>
				</div>
				<!-- /postbox-container -->
			</div>
		</div>
	</div>
</div><!-- /.wrap -->
