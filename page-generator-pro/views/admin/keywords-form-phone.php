<?php
/**
 * Outputs the form at Keywords > Generate Phone Area Codes
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

?>
<header>
	<h1>
		<?php echo esc_html( $this->base->plugin->displayName ); ?>

		<span>
			<?php esc_html_e( 'Generate Phone Area Codes', 'page-generator-pro' ); ?>
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
					<form name="post" method="post" action="admin.php?page=page-generator-pro-keywords&amp;cmd=form-phone" enctype="multipart/form-data">		
						<div id="normal-sortables" class="meta-box-sortables ui-sortable">                        
							<div id="keyword-panel" class="postbox">
								<h3 class="hndle"><?php esc_html_e( 'Keyword', 'page-generator-pro' ); ?></h3>
								<div class="wpzinc-option">
									<div class="left">
										<strong><?php esc_html_e( 'Keyword', 'page-generator-pro' ); ?></strong>
									</div>
									<div class="right">
										<input type="text" name="keyword" value="<?php echo esc_attr( $keyword['keyword'] ); ?>" class="widefat" required />

										<p class="description">
											<?php esc_html_e( 'A unique template tag name, which can then be used when generating content.', 'page-generator-pro' ); ?>
										</p>
									</div>
								</div>

								<div class="wpzinc-option">
									<div class="left">
										<strong><?php esc_html_e( 'Country', 'page-generator-pro' ); ?></strong>
									</div>
									<div class="right">
										<select name="country" size="1">
											<?php
											foreach ( $countries as $country_code => $country_name ) {
												?>
												<option value="<?php echo esc_attr( $country_code ); ?>"<?php selected( $keyword['country'], $country_code ); ?>><?php echo esc_attr( $country_name ); ?></option>
												<?php
											}
											?>
										</select>

										<p class="description">
											<?php esc_html_e( 'Enter the country which the city belongs to.', 'page-generator-pro' ); ?>
										</p>
									</div>
								</div>

								<div class="wpzinc-option">
									<div class="left">
										<strong><?php esc_html_e( 'Output Type', 'page-generator-pro' ); ?></strong>
									</div>

									<div class="right">
										<select name="output_type[]" multiple="multiple" class="wpzinc-selectize-drag-drop">
											<?php
											foreach ( $output_types as $output_type => $label ) {
												?>
												<option value="<?php echo esc_attr( $output_type ); ?>"<?php echo esc_attr( ( $keyword['output_type'] === $output_type ) ? ' selected' : '' ); ?>><?php echo esc_attr( $label ); ?></option>
												<?php
											}
											?>
										</select>

										<p class="description">
											<?php esc_html_e( 'Determine the data to store in this Keyword (for example, just the phone area code or the city and phone area code).', 'page-generator-pro' ); ?>
										</p>
									</div>
								</div>

								<div class="wpzinc-option">
									<?php wp_nonce_field( 'generate_phone_area_codes', $this->base->plugin->name . '_nonce' ); ?>
									<input type="submit" name="submit" value="<?php esc_attr_e( 'Generate Keyword with Phone Area Codes Data', 'page-generator-pro' ); ?>" class="button button-primary" />
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
