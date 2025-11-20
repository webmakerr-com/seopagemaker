<?php
/**
 * Divi Module Class
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Registers Dynamic Elements as Divi Modules.
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 3.0.7
 */
class Page_Generator_Pro_Divi_Module extends ET_Builder_Module {

	/**
	 * How modules are supported in the Visual Builder (off|partial|on)
	 *
	 * @since   3.0.7
	 *
	 * @var     string
	 */
	public $vb_support = 'on';

	/**
	 * Holds the shortcode properties
	 *
	 * @since   3.0.7
	 *
	 * @var     array
	 */
	private $shortcode;

	/**
	 * Defines the Module name
	 *
	 * @since   3.0.7
	 */
	public function init() {

		// Get shortcode.
		$this->shortcode = Page_Generator_Pro()->get_class( 'shortcode' )->get_shortcode( $this->block_name );
		if ( ! $this->shortcode ) {
			return;
		}

		// Define name.
		$this->name = 'PGP: ' . $this->shortcode['title'];

	}

	/**
	 * Defines the fields that can be configured for this Module
	 *
	 * @since   3.0.7
	 */
	public function get_fields() {

		// Bail if no shortcode.
		if ( ! $this->shortcode ) {
			return array();
		}

		// Bail if no fields.
		if ( ! $this->shortcode['fields'] ) {
			return array();
		}

		// Build fields.
		$fields = array();
		foreach ( $this->shortcode['fields'] as $field_name => $field ) {
			// Start building field definition.
			$fields[ $field_name ] = array(
				'type'        => $field['type'],
				'default'     => $this->get_default_value( $field ),
				'description' => ( isset( $field['description'] ) ? $field['description'] : '' ),
				'label'       => $field['label'],
				'toggle_slug' => $this->get_tab( $field_name ),
			);

			// Add field condition, if defined.
			if ( isset( $field['condition'] ) ) {
				// Define comparison (show_if or show_if_not).
				$comparison = ( ( $field['condition']['comparison'] === '==' ) ? 'show_if' : 'show_if_not' );

				// Define value as 'on' or 'off' if it's 1 or 0 and the comparison field is a toggle field.
				$value = $field['condition']['value'];
				if ( $this->shortcode['fields'][ $field['condition']['key'] ]['type'] === 'toggle' ) {
					$value = ( ( $value == '1' ) ? 'on' : 'off' ); // phpcs:ignore Universal.Operators.StrictComparisons.LooseEqual
				}

				// Add the comparison condition.
				$fields[ $field_name ][ $comparison ] = array(
					$field['condition']['key'] => $value,
				);
			}

			// Add/change field parameters depending on the field's type.
			switch ( $field['type'] ) {
				/**
				 * Autocomplete
				 */
				case 'autocomplete':
				case 'autocomplete_textarea':
				case 'text_multiple':
				case 'select_multiple':
					$fields[ $field_name ]['type'] = 'text';
					break;

				/**
				 * Number
				 */
				case 'number':
					$fields[ $field_name ] = array_merge(
						$fields[ $field_name ],
						array(
							'type'           => 'range',
							'range_settings' => array(
								'min'  => $field['min'],
								'max'  => $field['max'],
								'step' => $field['step'],
							),
							'unitless'       => true,
						)
					);
					break;

				/**
				 * Select
				 */
				case 'select':
					$fields[ $field_name ]['options'] = $field['values'];
					break;

				/**
				 * Toggle
				 */
				case 'toggle':
					$fields[ $field_name ] = array_merge(
						$fields[ $field_name ],
						array(
							'type'    => 'yes_no_button',
							'default' => ( $fields[ $field_name ]['default'] ? 'on' : 'off' ),
							'options' => array(
								'off' => __( 'No', 'page-generator-pro' ),
								'on'  => __( 'Yes', 'page-generator-pro' ),
							),
						)
					);
					break;

			}
		}

		// Return.
		return $fields;

	}

	/**
	 * Returns the default value for the given field configuration.
	 *
	 * If the field's default value is an array, it's converted to a string,
	 * to prevent Divi builder timeout errors on the frontend.
	 *
	 * @since   3.6.6
	 *
	 * @param   array $field  Field.
	 * @return  string|int|object         Default Value
	 */
	private function get_default_value( $field ) {

		// Return a blank string if the field doesn't specify a default value.
		if ( ! array_key_exists( 'default_value', $field ) ) {
			return '';
		}

		// If the default value is an array, implode it to a string, as array values aren't supported.
		if ( is_array( $field['default_value'] ) ) {
			// Determine the delimiter to use to join the array values.
			// Some Dynamic Elements may use a different delimiter.
			$delimiter = ',';
			if ( isset( $field['data'] ) && isset( $field['data']['delimiter'] ) ) {
				$delimiter = $field['data']['delimiter'];
			}

			return implode( $delimiter, $field['default_value'] );
		}

		// Default value is a string; return it.
		return $field['default_value'];

	}

	/**
	 * Define tabs for the Module
	 *
	 * @since   3.0.7
	 */
	public function get_settings_modal_toggles() {

		// Bail if no shortcode.
		if ( ! $this->shortcode ) {
			return array();
		}

		// Bail if no tabs exist.
		if ( ! $this->shortcode['tabs'] ) {
			return array();
		}

		// Build tabs as toggles.
		$toggles  = array();
		$priority = 24;
		foreach ( $this->shortcode['tabs'] as $tab_name => $tab_properties ) {
			$toggles[ $tab_name ] = array(
				'priority' => $priority,
				'title'    => $tab_properties['label'],
			);

			++$priority;
		}

		// Return.
		$modal_toggles = array(
			'advanced' => array(
				'toggles' => $toggles,
			),
		);

		return $modal_toggles;

	}

	/**
	 * Renders the shortcode syntax, converted from the module's properties array
	 *
	 * @since   3.0.7
	 *
	 * @param   array|string $unprocessed_props  Unprocessed properties.
	 * @param   array|string $content            Content.
	 * @param   string       $render_slug        Slug.
	 */
	public function render( $unprocessed_props, $content, $render_slug ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter

		// Bail if no shortcode.
		if ( ! $this->shortcode ) {
			return '';
		}

		// Return [shortcode] string.
		return Page_Generator_Pro()->get_class( 'shortcode' )->build_shortcode(
			Page_Generator_Pro()->plugin->name . '-' . $this->block_name,
			$this->shortcode['fields'],
			$this->props
		);

	}

	/**
	 * Returns the Tab Name for the given Field Name.
	 *
	 * @since   3.0.7
	 *
	 * @param   string $field_name     Field Name.
	 * @return  string                  Tab Name
	 */
	private function get_tab( $field_name ) {

		foreach ( $this->shortcode['tabs'] as $tab_name => $tab_properties ) {
			if ( in_array( $field_name, $tab_properties['fields'], true ) ) {
				return $tab_name;
			}
		}

		// If here, no tab could be found for this field to be rendered within,
		// so return Divi's main_content tab.
		return 'main_content';

	}

}
