<?php
/**
 * Elementor Autocomplete Textarea Control.
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Registers a Keyword autocompletor in a textarea in Elementor.
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 4.1.5
 */
class Page_Generator_Pro_Elementor_Control_Autocomplete_Textarea extends Elementor\Control_Text {

	/**
	 * Get the control type
	 *
	 * @since   4.1.5
	 *
	 * @return string   Control Type
	 */
	public function get_type() {

		return 'page-generator-pro-autocomplete-textarea';

	}

	/**
	 * Enqueue JS to initialize anything we need e.g. Autocomplete
	 *
	 * @since   4.1.5
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
	 * @since   4.1.5
	 */
	public function content_template() {

		$control_uid = $this->get_control_uid();
		?>
		<div class="elementor-control-field">
			<# if ( data.label ) {#>
				<label for="<?php echo esc_attr( $control_uid ); ?>" class="elementor-control-title">{{{ data.label }}}</label>
			<# } #>
			<div class="elementor-control-input-wrapper elementor-control-unit-5 elementor-control-dynamic-switcher-wrapper">
				<textarea id="<?php echo esc_attr( $control_uid ); ?>" class="wpzinc-autocomplete tooltip-target elementor-control-tag-area" data-tooltip="{{ data.title }}" title="{{ data.title }}" data-setting="{{ data.name }}" placeholder="{{ data.placeholder }}"></textarea>
			</div>
		</div>
		<# if ( data.description ) { #>
			<div class="elementor-control-field-description">{{{ data.description }}}</div>
		<# } #>
		<?php

	}

}
