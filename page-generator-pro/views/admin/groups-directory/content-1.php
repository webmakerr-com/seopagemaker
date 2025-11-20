<?php
/**
 * Outputs the first step for Content Groups > Add New Directory
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

?>
<h1><?php esc_html_e( 'Add New Directory Structure', 'page-generator-pro' ); ?></h1>
<p>
	<?php esc_html_e( 'This will generate the necessary Keywords, Content Groups and Related Links (internal links / interlinking) to produce the following directory structure:', 'page-generator-pro' ); ?>
</p>
<div class="wpzinc-horizontal-selection options-2">
	<?php
	foreach ( $structures as $structure => $properties ) {
		?>
		<label for="structure_<?php echo esc_attr( $structure ); ?>">
			<span><strong><?php echo esc_html( $properties['title'] ); ?></strong></span>
			<input type="radio" name="structure" id="structure_<?php echo esc_attr( $structure ); ?>" value="<?php echo esc_attr( $structure ); ?>" <?php checked( $this->configuration['structure'], $structure ); ?> />
			<span class="description"><?php echo esc_html( $properties['description'] ); ?></span>
		</label>
		<?php
	}
	?>
</div>

<div class="services">
	<h1><?php esc_html_e( 'Services', 'page-generator-pro' ); ?></h1>
	<p>
		<?php esc_html_e( 'Choose an existing Keyword to use as the list of services.', 'page-generator-pro' ); ?>
		<br />
		<?php esc_html_e( 'If this doesn\'t exist, select the --- New Keyword --- option and define the services below.', 'page-generator-pro' ); ?>
	</p>
	<div>
		<select name="service_keyword" size="1" data-conditional="services" data-conditional-display="false">
			<option value=""><?php esc_attr_e( '--- New Keyword ---', 'page-generator-pro' ); ?></option>
			<?php
			if ( is_array( $keywords ) && count( $keywords ) ) {
				foreach ( $keywords as $keyword ) {
					?>
					<option value="<?php echo esc_attr( $keyword ); ?>"<?php selected( $this->configuration['service_keyword'], $keyword ); ?>><?php echo esc_attr( $keyword ); ?></option>
					<?php
				}
			}
			?>
		</select>
	</div>
	<div id="services">
		<textarea name="services" rows="10" class="widefat" placeholder="<?php esc_attr_e( 'One Service per line', 'page-generator-pro' ); ?>"><?php echo esc_textarea( $this->configuration['services'] ); ?></textarea>
	</div>
</div>

<h1><?php esc_html_e( 'Locations', 'page-generator-pro' ); ?></h1>
<p>
	<?php esc_html_e( 'Use the below to define the Locations keyword.', 'page-generator-pro' ); ?>
</p>

<div class="wpzinc-horizontal-selection options-2">
	<label for="method_radius">
		<span><strong><?php esc_html_e( 'Radius', 'page-generator-pro' ); ?></strong></span>
		<input type="radio" name="method" id="method_radius" value="radius" class="wpzinc-conditional" data-container="#wpzinc-onboarding-content" <?php checked( $this->configuration['method'], 'radius' ); ?> />
		<span class="description"><?php esc_html_e( 'Your business offers its services within a fixed radius from its address.', 'page-generator-pro' ); ?></span>
	</label>
	<label for="method_area">
		<span><strong><?php esc_html_e( 'Area', 'page-generator-pro' ); ?></strong></span>
		<input type="radio" name="method" id="method_area" value="area" class="wpzinc-conditional" data-container="#wpzinc-onboarding-content" <?php checked( $this->configuration['method'], 'area' ); ?> />
		<span class="description"><?php esc_html_e( 'Your business offers its services in specific Regions, States or Counties.', 'page-generator-pro' ); ?></span>
	</label>
</div>

<div>
	<label for="country_code"><?php esc_html_e( 'Country', 'page-generator-pro' ); ?> <span class="required">*</span></label>
	<select name="country_code" id="country_code" size="1">
		<?php
		foreach ( $countries as $country_code => $country_name ) {
			?>
			<option value="<?php echo esc_attr( $country_code ); ?>"<?php selected( $this->configuration['country_code'], $country_code ); ?>><?php echo esc_attr( $country_name ); ?></option>
			<?php
		}
		?>
	</select>
	<p class="description">
		<?php esc_html_e( 'Define the Country to fetch Locations from.', 'page-generator-pro' ); ?>
	</p>
</div>
<div>
	<div class="radius">
		<label for="radius"><?php esc_html_e( 'Radius', 'page-generator-pro' ); ?> <span class="required">*</span></label>
		<input type="number" name="radius" min="0.1" max="99999" step="0.1" value="<?php echo esc_attr( $this->configuration['radius'] ); ?>" class="widefat" />
		<p class="description">
			<?php esc_html_e( 'Enter the number of miles from your Business Address that you serve.', 'page-generator-pro' ); ?>
		</p>
	</div>
	<div class="radius">
		<label for="zipcode"><?php esc_html_e( 'ZIP / Postal Code', 'page-generator-pro' ); ?> <span class="required">*</span></label>
		<input type="text" name="zipcode" id="zipcode" value="<?php echo esc_attr( $this->configuration['zipcode'] ); ?>" class="widefat" />
		<p class="description">
			<?php esc_html_e( 'Enter the ZIP / Postal Code to use as the starting point.', 'page-generator-pro' ); ?>
		</p>
	</div>
</div>
<div>
	<div class="area">
		<?php
		// For Georocket, we use a different form that will populate with the results of AJAX calls to the Georocket API.
		if ( $this->provider === 'georocket' ) {
			?>
			<div>
				<label for="region_id"><?php esc_html_e( 'Regions / States', 'page-generator-pro' ); ?></label>
				<select name="region_id[]" multiple="multiple" class="wpzinc-selectize" data-action="page_generator_pro_georocket" data-api-call="get_regions" data-country-code="country_code" data-value-field="id" data-output-fields="region_name,country_code" data-nonce="<?php echo esc_attr( wp_create_nonce( 'generate_locations' ) ); ?>">
				</select>
				<p class="description">
					<?php esc_html_e( 'Start typing the Regions / States that your business serves. Multiple Regions / States can be specified. If you serve in specific Counties, use the Counties option below.', 'page-generator-pro' ); ?>
				</p>
			</div>

			<div>
				<label for="county_id"><?php esc_html_e( 'Counties', 'page-generator-pro' ); ?></label>
				<select name="county_id[]" multiple="multiple" class="wpzinc-selectize-api" data-action="page_generator_pro_georocket" data-api-call="get_counties" data-api-search-field="county_name" data-api-fields="region_id[]" data-country-code="country_code" data-value-field="id" data-output-fields="county_name,region_name" data-nonce="<?php echo esc_attr( wp_create_nonce( 'generate_locations' ) ); ?>">
				</select>
				<p class="description">
					<?php esc_html_e( 'Start typing the Counties that your business serves. Multiple Counties can be specified. If you serve in Regions / States, use the Regions / States option above.', 'page-generator-pro' ); ?>
				</p>
			</div>
			<?php
		} else {
			// AI provider, allow freeform text entries.
			?>
			<div>
				<label for="regions"><?php esc_html_e( 'Regions / States', 'page-generator-pro' ); ?></label>
				<input type="text" name="regions" id="regions"class="widefat wpzinc-selectize-freeform" value="" />
				<p class="description">
					<?php esc_html_e( 'Enter the Regions / States that your business serves. Multiple Regions / States can be specified. If you serve in specific Counties, use the Counties option below.', 'page-generator-pro' ); ?>
				</p>
			</div>

			<div>
				<label for="counties"><?php esc_html_e( 'Counties', 'page-generator-pro' ); ?></label>
				<input type="text" name="counties" id="counties"class="widefat wpzinc-selectize-freeform" value="" />
				<p class="description">
					<?php esc_html_e( 'Enter the Counties that your business serves. Multiple Counties can be specified. If you serve in Regions / States, use the Regions / States option above.', 'page-generator-pro' ); ?>
				</p>
			</div>
			<?php
		}
		?>
		<div>
			<label for="exclusions"><?php esc_html_e( 'Exclusions', 'page-generator-pro' ); ?></label>
			<input type="text" name="exclusions" id="exclusions" class="widefat wpzinc-selectize-freeform" />

			<p class="description">
				<?php esc_html_e( 'Optional: Define Cities to exclude from the results.', 'page-generator-pro' ); ?>
			</p>
		</div>
	</div>
</div>
<div>
	<div>
		<label for="population_min"><?php esc_html_e( 'Restrict by City Population', 'page-generator-pro' ); ?></label>
		<input type="number" name="population_min" id="population_min" min="0" max="99999999" value="" placeholder="<?php esc_attr_e( 'Min.', 'page-generator-pro' ); ?>" />
		-
		<input type="number" name="population_max" min="0" max="99999999" value="" placeholder="<?php esc_attr_e( 'Max.', 'page-generator-pro' ); ?>" />

		<p class="description">
			<?php esc_html_e( 'Limit locations to Cities within the given Population Limits.  Leave blank to specify no limit.', 'page-generator-pro' ); ?>
		</p>
	</div>
</div>
