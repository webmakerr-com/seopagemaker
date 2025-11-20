<?php
/**
 * Outputs the form at Keywords > Generate Locations
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

?>
<header>
	<h1>
		<?php echo esc_html( $this->base->plugin->displayName ); ?>

		<span>
			<?php esc_html_e( 'Generate Locations', 'page-generator-pro' ); ?>
		</span>
	</h1>
</header>

<div class="wrap">
	<div class="wrap-inner">
		<?php
		// Button Links.
		require_once 'keywords-links.php';
		?>

		<!-- Container for JS notices -->
		<div class="js-notices"></div>

		<div id="poststuff">
			<div id="post-body" class="metabox-holder columns-1">
				<!-- Content -->
				<div id="post-body-content">
					<!-- Form Start -->
					<form name="post" method="post" action="admin.php?page=page-generator-pro-keywords&amp;cmd=form-locations" enctype="multipart/form-data" id="keywords-generate-locations" class="<?php echo esc_attr( $provider ); ?>">		
						<div id="normal-sortables" class="meta-box-sortables ui-sortable">                        
							<div id="keyword-panel" class="postbox">
								<h3 class="hndle"><?php esc_html_e( 'Keyword', 'page-generator-pro' ); ?></h3>

								<div class="wpzinc-option">
									<div class="left">
										<strong><?php esc_html_e( 'Keyword', 'page-generator-pro' ); ?></strong>
									</div>
									<div class="right">
										<input type="text" name="keyword" value="<?php echo esc_attr( $keyword['keyword'] ); ?>" class="widefat" required />
										<input type="hidden" name="keyword_id" value="" />

										<p class="description">
											<?php esc_html_e( 'A unique template tag name, which can then be used when generating content.', 'page-generator-pro' ); ?>
										</p>
									</div>
								</div>

								<div class="wpzinc-option">
									<div class="left">
										<strong><?php esc_html_e( 'Output Type', 'page-generator-pro' ); ?></strong>
									</div>

									<div class="right">
										<select name="output_type[]" multiple="multiple" class="wpzinc-selectize-drag-drop" data-controls="orderby">
											<?php
											foreach ( $output_types as $output_type => $label ) {
												?>
												<option value="<?php echo esc_attr( $output_type ); ?>"><?php echo esc_attr( $label ); ?></option>
												<?php
											}
											?>
										</select>

										<p class="description">
											<?php esc_html_e( 'Determine the data to store in this Keyword for each Location (for example, just the city, the city and zip code or city, county and region).', 'page-generator-pro' ); ?>
										</p>
									</div>
								</div>

								<div class="wpzinc-option">
									<div class="left">
										<strong><?php esc_html_e( 'Sort Terms By', 'page-generator-pro' ); ?></strong>
									</div>

									<div class="right">
										<select name="orderby" size="1">
											<?php
											foreach ( $order_by_options as $order_by => $label ) {
												?>
												<option value="<?php echo esc_attr( $order_by ); ?>"<?php echo ( ( $keyword['orderby'] === $order_by ) ? ' selected' : '' ); ?>><?php echo esc_attr( $label ); ?></option>
												<?php
											}
											?>
										</select>
										<select name="order" size="1">
											<?php
											foreach ( $order_options as $location_order => $label ) {
												?>
												<option value="<?php echo esc_attr( $location_order ); ?>"<?php echo ( ( $keyword['order'] === $location_order ) ? ' selected' : '' ); ?>><?php echo esc_attr( $label ); ?></option>
												<?php
											}
											?>
										</select>

										<p class="description">
											<?php esc_html_e( 'Define the order in which Generated Locations Terms are stored.', 'page-generator-pro' ); ?>
										</p>
									</div>
								</div>

								<div class="wpzinc-option">
									<div class="left">
										<strong><?php esc_html_e( 'Country', 'page-generator-pro' ); ?></strong>
									</div>
									<div class="right">
										<select name="country_code" size="1">
											<?php
											foreach ( $countries as $country_code => $country_name ) {
												?>
												<option value="<?php echo esc_attr( $country_code ); ?>"<?php selected( $keyword['country_code'], $country_code ); ?>><?php echo esc_attr( $country_name ); ?></option>
												<?php
											}
											?>
										</select>

										<p class="description">
											<?php esc_html_e( 'Limit Locations to within the given Country.', 'page-generator-pro' ); ?>
										</p>
									</div>
								</div>

								<div class="wpzinc-option">
									<div class="left">
										<strong><?php esc_html_e( 'Method', 'page-generator-pro' ); ?></strong>
									</div>
									<div class="right">
										<select name="method" size="1" data-conditional="radius">
											<?php
											foreach ( $methods as $method => $label ) {
												?>
												<option value="<?php echo esc_attr( $method ); ?>"<?php selected( $keyword['method'], $method ); ?>><?php echo esc_attr( $label ); ?></option>
												<?php
											}
											?>
										</select>

										<p class="description">
											<?php esc_html_e( 'Determines how to build a list of location terms for this Keyword.', 'page-generator-pro' ); ?><br />

											<strong><?php esc_html_e( 'Radius', 'page-generator-pro' ); ?></strong>
											<?php esc_html_e( 'The Keyword will be populated with Locations falling within the given radius from the given starting point.  This method is useful if, for example, your product or service targets a specific mileage radius from a central location.', 'page-generator-pro' ); ?>
											<br />

											<strong><?php esc_html_e( 'Area', 'page-generator-pro' ); ?></strong>
											<?php esc_html_e( 'The Keyword will be populated with Locations falling within the given City, County and/or Region.  This method is useful if, for example, your product or service targets a specific City, County or Region.', 'page-generator-pro' ); ?>
										</p>
									</div>
								</div>

								<div class="radius">
									<div class="wpzinc-option">
										<div class="left">
											<strong><?php esc_html_e( 'Starting City / ZIP Code', 'page-generator-pro' ); ?></strong>
										</div>

										<div class="right">
											<input type="text" name="location" value="<?php esc_attr( $keyword['location'] ); ?>" class="widefat" />

											<p class="description">
												<?php esc_html_e( 'Enter the city or zip code to use as the starting point to generate nearby cities / zip codes from.', 'page-generator-pro' ); ?>
											</p>
										</div>
									</div>

									<div class="wpzinc-option">
										<div class="left">
											<strong><?php esc_html_e( 'Radius', 'page-generator-pro' ); ?></strong>
										</div>

										<div class="right">
											<input type="number" name="radius" min="0.1" max="99999" step="0.1" value="<?php echo esc_attr( $keyword['radius'] ); ?>" class="widefat" />

											<p class="description">
												<?php esc_html_e( 'Enter the number of miles to fetch all nearby cities, counties, regions and/or ZIP codes from the Starting City / ZIP Code above.', 'page-generator-pro' ); ?>
											</p>
										</div>
									</div>
								</div>

								<?php
								// For Georocket, we use a different form that will populate with the results of AJAX calls to the Georocket API.
								if ( $provider === 'georocket' ) {
									?>
									<div class="area">
										<div class="wpzinc-option">
											<div class="left">
												<strong><?php esc_html_e( 'Restrict by Region(s)', 'page-generator-pro' ); ?></strong>
											</div>

											<div class="right">
												<select name="region_id[]" multiple="multiple" class="wpzinc-selectize" data-action="page_generator_pro_georocket" data-api-call="get_regions" data-country-code="country_code" data-value-field="id" data-output-fields="region_name,country_code" data-nonce="<?php echo esc_attr( wp_create_nonce( 'generate_locations' ) ); ?>">
												</select>

												<p class="description">
													<?php esc_html_e( 'Limit Terms to the given Region / State Name(s). Begin typing to see valid Region / State Names.', 'page-generator-pro' ); ?>
												</p>
											</div>
										</div>

										<div class="wpzinc-option">
											<div class="left">
												<strong><?php esc_html_e( 'Restrict by County / Counties', 'page-generator-pro' ); ?></strong>
											</div>

											<div class="right">
												<select name="county_id[]" multiple="multiple" class="wpzinc-selectize-api" data-action="page_generator_pro_georocket" data-api-call="get_counties" data-api-search-field="county_name" data-api-fields="region_id[]" data-country-code="country_code" data-value-field="id" data-output-fields="county_name,region_name" data-nonce="<?php echo esc_attr( wp_create_nonce( 'generate_locations' ) ); ?>">
												</select>

												<p class="description">
													<?php esc_html_e( 'Limit Terms to the given County Name(s). Begin typing to see valid County Names.', 'page-generator-pro' ); ?>
												</p>
											</div>
										</div>

										<div class="wpzinc-option">
											<div class="left">
												<strong><?php esc_html_e( 'Restrict by City / Cities', 'page-generator-pro' ); ?></strong>
											</div>

											<div class="right">
												<select name="city_id[]" multiple="multiple" class="wpzinc-selectize-api" data-action="page_generator_pro_georocket" data-api-call="get_cities" data-api-search-field="city_name" data-api-fields="region_id[],county_id[]" data-country-code="country_code" data-value-field="id" data-output-fields="city_name,county_name,region_name" data-nonce="<?php echo esc_attr( wp_create_nonce( 'generate_locations' ) ); ?>">
												</select>

												<p class="description">
													<?php esc_html_e( 'Limit Terms to the given City Name(s). Begin typing to see valid City Names.  If you have specified Restrict by Region(s) and/or Counties above, the City results listed will be limited to those Regions and/or Counties.', 'page-generator-pro' ); ?>
												</p>
											</div>
										</div>
									</div>
									<?php
								} else {
									// AI provider, allow freeform text entries.
									?>
									<div class="area">
										<div class="wpzinc-option">
											<div class="left">
												<strong><?php esc_html_e( 'Restrict by Region(s)', 'page-generator-pro' ); ?></strong>
											</div>

											<div class="right">
												<input type="text" name="regions" class="widefat wpzinc-selectize-freeform" value="<?php echo esc_attr( $keyword['regions'] ); ?>" />

												<p class="description">
													<?php esc_html_e( 'Limit Terms to the given Region / State Name(s).', 'page-generator-pro' ); ?>
												</p>
											</div>
										</div>

										<div class="wpzinc-option">
											<div class="left">
												<strong><?php esc_html_e( 'Restrict by County / Counties', 'page-generator-pro' ); ?></strong>
											</div>

											<div class="right">
												<input type="text" name="counties" class="widefat wpzinc-selectize-freeform" value="<?php echo esc_attr( $keyword['counties'] ); ?>" />

												<p class="description">
													<?php esc_html_e( 'Limit Terms to the given County Name(s).', 'page-generator-pro' ); ?>
												</p>
											</div>
										</div>

										<div class="wpzinc-option">
											<div class="left">
												<strong><?php esc_html_e( 'Restrict by City / Cities', 'page-generator-pro' ); ?></strong>
											</div>

											<div class="right">
												<input type="text" name="cities" class="widefat wpzinc-selectize-freeform" value="<?php echo esc_attr( $keyword['cities'] ); ?>" />

												<p class="description">
													<?php esc_html_e( 'Limit Terms to the given City Name(s).', 'page-generator-pro' ); ?>
												</p>
											</div>
										</div>
									</div>
									<?php
								}
								?>
								
								<div class="wpzinc-option">
									<div class="left">
										<strong><?php esc_html_e( 'Exclusions', 'page-generator-pro' ); ?></strong>
									</div>

									<div class="right">
										<input type="text" name="exclusions" class="widefat wpzinc-selectize-freeform" value="<?php echo esc_attr( $keyword['exclusions'] ); ?>" />

										<p class="description">
											<?php esc_html_e( 'Optional: Define any Cities, Counties or Regions to exclude.', 'page-generator-pro' ); ?>
											<br />
											<?php esc_html_e( 'Any results partially or fully matching the above will be excluded from the results.', 'page-generator-pro' ); ?>
											<br />
											<?php esc_html_e( 'This is case insensitive.', 'page-generator-pro' ); ?>
										</p>
									</div>
								</div>

								<div class="wpzinc-option">
									<div class="left">
										<strong><?php esc_html_e( 'Restrict by City Population', 'page-generator-pro' ); ?></strong>
									</div>

									<div class="right">
										<input type="number" name="population_min" min="0" max="99999999" value="<?php echo esc_attr( $keyword['population_min'] ); ?>" placeholder="<?php esc_attr_e( 'Min.', 'page-generator-pro' ); ?>" />
										-
										<input type="number" name="population_max" min="0" max="99999999" value="<?php echo esc_attr( $keyword['population_max'] ); ?>" placeholder="<?php esc_attr_e( 'Max.', 'page-generator-pro' ); ?>" />

										<p class="description">
											<?php esc_html_e( 'Limit Terms to Cities within the given Population Limits.  Leave blank to specify no limit.', 'page-generator-pro' ); ?>
										</p>
									</div>
								</div>

								<div class="wpzinc-option">
									<div class="left">
										<strong><?php esc_html_e( 'Restrict by City Median Household Income', 'page-generator-pro' ); ?></strong>
									</div>

									<div class="right">
										<input type="number" name="median_household_income_min" min="0" max="99999999" value="<?php echo esc_attr( $keyword['median_household_income_min'] ); ?>" placeholder="<?php esc_attr_e( 'Min.', 'page-generator-pro' ); ?>" />
										-
										<input type="number" name="median_household_income_max" min="0" max="99999999" value="<?php echo esc_attr( $keyword['median_household_income_max'] ); ?>" placeholder="<?php esc_attr_e( 'Max.', 'page-generator-pro' ); ?>" />

										<p class="description">
											<?php esc_html_e( 'Limit Terms to Cities within the given Median Household Income Limits.  Leave blank to specify no limit.', 'page-generator-pro' ); ?>
										</p>
									</div>
								</div>

								<div class="wpzinc-option">
									<?php wp_nonce_field( 'generate_locations', $this->base->plugin->name . '_nonce' ); ?>
									<input type="submit" name="submit" value="<?php esc_attr_e( 'Generate Keyword with Locations', 'page-generator-pro' ); ?>" class="button button-primary" />
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
