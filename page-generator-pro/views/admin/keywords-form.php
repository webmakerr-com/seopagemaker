<?php
/**
 * Outputs the form at Keywords > Add New
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

?>
<header>
	<h1>
		<?php echo esc_html( $this->base->plugin->displayName ); ?>

		<span>
			<?php esc_html_e( 'Add New Keyword', 'page-generator-pro' ); ?>
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
					<form class="<?php echo esc_attr( $this->base->plugin->name ); ?>" name="post" method="post" action="admin.php?page=page-generator-pro-keywords&amp;cmd=form" enctype="multipart/form-data">		
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
											<select name="source" size="1" class="wpzinc-conditional" data-container="#keyword-panel">
												<?php
												foreach ( $sources as $source_name => $source ) {
													?>
													<option value="<?php echo esc_attr( $source_name ); ?>"<?php echo esc_attr( ( $keyword['source'] === $source_name ) ? ' selected' : '' ); ?>>
														<?php echo esc_attr( $source['label'] ); ?>
													</option>
													<?php
												}
												?>
											</select>
											<p class="description">
												<?php esc_html_e( 'The source of this Keyword\'s Terms. Use CSV File, CSV URl or Spreadsheet if importing a CSV or Spreadsheet.', 'page-generator-pro' ); ?>
											</p>
										</div>
									</div>
								</div>

								<?php
								foreach ( $sources as $source_name => $source ) {
									?>
									<div>
										<div class="<?php echo esc_attr( $source_name ); ?>">
											<?php
											foreach ( $source['options'] as $option_name => $option ) {
												// Skip Preview, as we're adding a Keyword which has yet to supply its source data.
												if ( $option['type'] === 'preview' ) {
													continue;
												}

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
											?>
										</div>
									</div>
									<?php
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
