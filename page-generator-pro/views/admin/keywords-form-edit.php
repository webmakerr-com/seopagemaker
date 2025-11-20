<?php
/**
 * Outputs the form at Keywords > Edit
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

?>
<header>
	<h1>
		<?php echo esc_html( $this->base->plugin->displayName ); ?>

		<span>
			<?php esc_html_e( 'Edit Keyword', 'page-generator-pro' ); ?>
		</span>
	</h1>
</header>

<div class="wrap">
	<div class="wrap-inner">
		<?php
		// Button Links.
		require_once 'keywords-links.php';
		?>

		<div id="poststuff">
			<div id="post-body" class="metabox-holder columns-1">
				<!-- Content -->
				<div id="post-body-content">
					<!-- Form Start -->
					<form class="<?php echo esc_attr( $this->base->plugin->name ); ?>" name="post" method="post" action="admin.php?page=page-generator-pro-keywords&amp;cmd=form&id=<?php echo esc_attr( $keyword_id ); ?>" enctype="multipart/form-data">		
						<div id="normal-sortables" class="meta-box-sortables ui-sortable">                        
							<div id="keyword-panel" class="postbox">
								<h3 class="hndle"><?php esc_html_e( 'Keyword', 'page-generator-pro' ); ?></h3>

								<div class="wpzinc-option">
									<div class="left">
										<label for="keyword"><?php esc_html_e( 'Keyword', 'page-generator-pro' ); ?></label>
									</div>
									<div class="right">
										<input type="text" name="keyword" id="keyword" value="<?php echo esc_attr( $keyword['keyword'] ); ?>" class="widefat" />

										<p class="description">
											<?php esc_html_e( 'A unique template tag name, which can then be used when generating content.', 'page-generator-pro' ); ?>
										</p>
									</div>
								</div>

								<div class="wpzinc-option">
									<div class="left">
										<label for="source"><?php esc_html_e( 'Source', 'page-generator-pro' ); ?></label>
									</div>
									<div class="right">
										<div class="right">
											<?php echo esc_html( $sources[ $keyword['source'] ]['label'] ); ?>
											<input type="hidden" name="source" value="<?php echo esc_attr( $keyword['source'] ); ?>" />
											<p class="description">
												<?php esc_html_e( 'Once a Keyword is created, the source cannot be changed. To change the source, delete this Keyword and create a new Keyword with the same name.', 'page-generator-pro' ); ?>
											</p>
										</div>
									</div>
								</div>

								<?php
								// Define source name variable for keywords-form-field.php.
								$source_name = $keyword['source'];

								foreach ( $sources[ $keyword['source'] ]['options'] as $option_name => $option ) {
									// Data, Delimiter and Column are stored outside of options, but are submitted as options.
									$value = '';
									if ( isset( $keyword['options'][ $option_name ] ) ) {
										$value = $keyword['options'][ $option_name ];
									} elseif ( isset( $keyword[ $option_name ] ) ) {
										$value = $keyword[ $option_name ];
									} elseif ( isset( $keyword[ $source_name ][ $option_name ] ) ) {
										$value = $keyword[ $source_name ][ $option_name ];
									} elseif ( isset( $keyword[ $option_name ] ) ) {
										$value = $keyword[ $option_name ];
									}

									// Output form field.
									include 'keywords-form-field.php';
								}

								// Upgrade Notice.
								if ( class_exists( 'Page_Generator' ) ) {
									require $this->base->plugin->folder . 'views/admin/keywords-form-upgrade.php';
								}
								?>

								<div class="wpzinc-option">
									<?php wp_nonce_field( 'save_keyword', $this->base->plugin->name . '_nonce' ); ?>
									<input type="submit" name="submit" value="<?php esc_attr_e( 'Save', 'page-generator-pro' ); ?>" class="button button-primary" />
								</div>
							</div>
						</div>
						<!-- /normal-sortables -->
					</form>
					<!-- /form end -->
				</div>
				<!-- /post-body-content -->
			</div>
		</div>  
	</div>     
</div>
