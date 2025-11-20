<?php
/**
 * Outputs the first step for Content Groups > Add New Directory
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

?>
<h1><?php esc_html_e( 'Add New Content Group', 'page-generator-pro' ); ?></h1>
<p>
	<?php esc_html_e( 'This will generate the necessary Keyword and Content Group, populated with AI researched content, based on the settings below.', 'page-generator-pro' ); ?>
</p>

<div>
	<label for="service"><?php esc_html_e( 'Service', 'page-generator-pro' ); ?> <span class="required">*</span></label>
	<input type="text" name="service" id="service" value="<?php echo esc_attr( $this->configuration['service'] ); ?>" class="widefat" required />
	<p class="description">
		<?php esc_html_e( 'Describe the service or product that you offer. For example, "web design" or "kitchen fitting".', 'page-generator-pro' ); ?>
	</p>
</div>

<div>
	<label for="limit"><?php esc_html_e( 'Word Count', 'page-generator-pro' ); ?> <span class="required">*</span></label>
	<input type="number" name="limit" id="limit" value="<?php echo esc_attr( $this->configuration['limit'] ); ?>" min="1" max="500" step="1" required />
	<p class="description">
		<?php esc_html_e( 'The maximum length of the content, between 1 and 500 words.', 'page-generator-pro' ); ?>
	</p>
</div>

<div>
	<label for="language"><?php esc_html_e( 'Language', 'page-generator-pro' ); ?> <span class="required">*</span></label>
	<select name="language" id="language" size="1">
		<?php
		foreach ( $this->base->get_class( 'common' )->get_languages() as $key => $value ) {
			?>
			<option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $value ); ?></option>
			<?php
		}
		?>
	</select>
</div>

<div>
	<label for="page_builder"><?php esc_html_e( 'Page Builder (beta)', 'page-generator-pro' ); ?> <span class="required">*</span></label>
	<select name="page_builder" id="page_builder" size="1">
		<?php
		// Output Page Builders that support Add New Using AI.
		foreach ( $supported_page_builders as $key => $value ) {
			?>
			<option value="<?php echo esc_attr( $key ); ?>"<?php selected( $this->configuration['page_builder'], $key ); ?>>
				<?php echo esc_html( $value ); ?>
			</option>
			<?php
		}
		?>
	</select>
	<p class="description">
		<?php esc_html_e( 'The page editor / page builder to generate content for. This is in beta; the generated Content Group will most likely need editing after creation.', 'page-generator-pro' ); ?>
	</p>
</div>


