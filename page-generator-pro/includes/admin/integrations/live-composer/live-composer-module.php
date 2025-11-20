<?php
/**
 * Live Composer Module Class
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Registers Dynamic Elements as Live Composer Modules.
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 5.2.7
 */
class Page_Generator_Pro_Live_Composer_Module extends DSLC_Module {

	/**
	 * The module's title.
	 *
	 * @since   5.2.7
	 *
	 * @var     string
	 */
	public $module_title = '';

	/**
	 * The module's icon.
	 *
	 * @since   5.2.7
	 *
	 * @var     string
	 */
	public $module_icon = '';

	/**
	 * The module's category.
	 *
	 * @since   5.2.7
	 *
	 * @var     string
	 */
	public $module_category = '';

	/**
	 * Holds the shortcode properties
	 *
	 * @since   5.2.7
	 *
	 * @var     array
	 */
	private $shortcode;

	/**
	 * Constructor
	 *
	 * @since   5.2.7
	 */
	public function __construct() {

		// Get shortcode.
		$this->shortcode = Page_Generator_Pro()->get_class( 'shortcode' )->get_shortcode( $this->block_name );
		if ( ! $this->shortcode ) {
			return;
		}

		// Define module properties.
		$this->module_title    = $this->shortcode['title'];
		$this->module_icon     = 'code';
		$this->module_category = Page_Generator_Pro()->plugin->displayName;

	}

	/**
	 * Define the fields that can be configured for this Module
	 *
	 * @since   5.2.7
	 *
	 * @var     array
	 */
	public function options() {

		// Get shortcode.
		$this->shortcode = Page_Generator_Pro()->get_class( 'shortcode' )->get_shortcode( $this->block_name );
		if ( ! $this->shortcode ) {
			return;
		}

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
			$live_composer_field = array(
				'label' => $field['label'],
				'id'    => $field_name,
				'std'   => $this->get_default_value( $field ),
				'type'  => $field['type'],
				'tab'   => $this->get_tab( $field_name ),
			);

			// Add/change field parameters depending on the field's type.
			switch ( $field['type'] ) {
				/**
				 * Autocomplete
				 */
				case 'autocomplete':
				case 'text_multiple':
				case 'select_multiple':
					$live_composer_field['type'] = 'text';
					break;

				case 'autocomplete_textarea':
					$live_composer_field['type'] = 'textarea';
					break;

				/**
				 * Number
				 */
				case 'number':
					$live_composer_field = array_merge(
						$live_composer_field,
						array(
							'type' => 'slider',
							'min'  => $field['min'],
							'max'  => $field['max'],
						)
					);
					break;

				/**
				 * Select
				 */
				case 'select':
					$live_composer_field['choices'] = array();
					foreach ( $field['values'] as $value => $label ) {
						$live_composer_field['choices'][] = array(
							'label' => $label,
							'value' => $value,
						);
					}
					break;

				/**
				 * Toggle
				 */
				case 'toggle':
					$live_composer_field = array_merge(
						$live_composer_field,
						array(
							'type'    => 'select',
							'choices' => array(
								array(
									'label' => __( 'No', 'page-generator-pro' ),
									'value' => '0',
								),
								array(
									'label' => __( 'Yes', 'page-generator-pro' ),
									'value' => '1',
								),
							),
						)
					);
					break;

			}

			// Add field to array.
			// We deliberately don't use $field_name as the key, as Live Composer
			// doesn't support array keys that aren't strings.
			$fields[] = $live_composer_field;
		}

		// Return.
		return $fields;

	}

	/**
	 * Renders the shortcode syntax, converted from the module's properties array
	 *
	 * @since   5.2.7
	 *
	 * @param   array|string $options            Options.
	 */
	public function output( $options ) {

		$this->module_start( $options );

		// Output [shortcode] string.
		// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
		echo Page_Generator_Pro()->get_class( 'shortcode' )->build_shortcode(
			Page_Generator_Pro()->plugin->name . '-' . $this->block_name,
			$this->shortcode['fields'],
			$options
		);
		// phpcs:enable

		$this->module_end( $options );

	}

	/**
	 * Returns the default value for the given field configuration.
	 *
	 * @since   5.2.7
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
	 * Returns the Tab Name for the given Field Name.
	 *
	 * @since   5.2.7
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
		// so return the general tab.
		return 'general';

	}

}
