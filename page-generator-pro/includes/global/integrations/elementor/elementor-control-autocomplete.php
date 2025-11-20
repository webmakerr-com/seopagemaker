<?php
/**
 * Elementor Autocomplete Control.
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Registers a Keyword autocompletor in Elementor.
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 3.0.8
 */
class Page_Generator_Pro_Elementor_Control_Autocomplete extends Elementor\Control_Text {

	/**
	 * Get the control type
	 *
	 * @since   3.0.8
	 *
	 * @return string   Control Type
	 */
	public function get_type() {

		return 'page-generator-pro-autocomplete';

	}

	/**
	 * Enqueue JS to initialize anything we need e.g. Autocomplete
	 *
	 * @since   3.0.8
	 */
	public function enqueue() {

		// Determine whether to load minified versions of JS.
		$minified = Page_Generator_Pro()->dashboard->should_load_minified_js();

		wp_enqueue_script(
			Page_Generator_Pro()->plugin->name . '-elementor',
			Page_Generator_Pro()->plugin->url . 'assets/js/' . ( $minified ? 'min/' : '' ) . 'elementor' . ( $minified ? '-min' : '' ) . '.js',
			array( 'jquery' ),
			Page_Generator_Pro()->plugin->version,
			true
		);

	}

	/**
	 * Render the field in the editor
	 *
	 * @since   3.0.8
	 */
	public function content_template() {

		$control_uid = $this->get_control_uid();
		?>
		<div class="elementor-control-field">
			<# if ( data.label ) {#>
				<label for="<?php echo esc_attr( $control_uid ); ?>" class="elementor-control-title">{{{ data.label }}}</label>
			<# } #>
			<div class="elementor-control-input-wrapper elementor-control-unit-5 elementor-control-dynamic-switcher-wrapper">
				<input id="<?php echo esc_attr( $control_uid ); ?>" type="{{ data.input_type }}" class="wpzinc-autocomplete tooltip-target elementor-control-tag-area" data-tooltip="{{ data.title }}" title="{{ data.title }}" data-setting="{{ data.name }}" placeholder="{{ data.placeholder }}" />
			</div>
		</div>
		<# if ( data.description ) { #>
			<div class="elementor-control-field-description">{{{ data.description }}}</div>
		<# } #>
		<?php

	}

}
