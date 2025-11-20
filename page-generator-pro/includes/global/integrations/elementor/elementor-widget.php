<?php
/**
 * Elementor Widget Class
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Registers Dynamic Elements as Elementor Widgets.
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 3.0.8
 */
class Page_Generator_Pro_Elementor_Widget extends Elementor\Widget_Base {

	/**
	 * Holds the shortcode properties
	 *
	 * @since   3.0.8
	 *
	 * @var     array
	 */
	private $shortcode;

	/**
	 * The module's slug. Must be different from the Page Generator Pro underlying shortcode.
	 *
	 * @since   3.0.8
	 *
	 * @var     string
	 */
	public $slug = '';

	/**
	 * Defines the Widget Name
	 *
	 * @since   3.0.8
	 */
	public function get_name() {

		return $this->slug;

	}

	/**
	 * Defines the Widget Title
	 *
	 * @since   3.0.8
	 *
	 * @return  string
	 */
	public function get_title() {

		// Get shortcode.
		$this->shortcode = Page_Generator_Pro()->get_class( 'shortcode' )->get_shortcode( str_replace( 'page-generator-pro-elementor-', '', $this->slug ) );
		if ( ! $this->shortcode ) {
			return '';
		}

		// Return widget title.
		return Page_Generator_Pro()->plugin->displayName . ': ' . $this->shortcode['title'];

	}

	/**
	 * Defines the Widget Icon
	 *
	 * @since   3.0.8
	 *
	 * @return  string
	 */
	public function get_icon() {

		return 'eicon-' . $this->get_name();

	}

	/**
	 * Defines the Widget Categories
	 *
	 * @since   3.0.8
	 *
	 * @return  array
	 */
	public function get_categories() {

		return array( 'page-generator-pro' );

	}

	/**
	 * Defines the controls (fields) for this widget
	 *
	 * @since   3.0.8
	 */
	protected function register_controls() {

		// Get shortcode.
		$this->shortcode = Page_Generator_Pro()->get_class( 'shortcode' )->get_shortcode( str_replace( 'page-generator-pro-elementor-', '', $this->slug ) );

		// Bail if no shortcode.
		if ( ! $this->shortcode ) {
			return;
		}

		// Iterate through tabs, building a section for each.
		foreach ( $this->shortcode['tabs'] as $tab_name => $tab_properties ) {
			// Start section.
			$this->start_controls_section(
				// Deliberately prefix, as if a tab and field have the same name, it won't render.
				'section_' . $tab_name,
				array(
					'label' => $tab_properties['label'],
					'tab'   => Elementor\Controls_Manager::TAB_CONTENT,
				)
			);

			// Add controls to this section.
			foreach ( $tab_properties['fields'] as $field_name ) {
				// Get field.
				$field = $this->shortcode['fields'][ $field_name ];

				// If this field is a Repeater, handle it now.
				switch ( $field['type'] ) {
					/**
					 * Repeater
					 */
					case 'repeater':
						// Build the repeater.
						$repeater = new Elementor\Repeater();
						foreach ( $field['sub_fields'] as $sub_field_name => $sub_field ) {
							// Get the sub field's control's parameters.
							$sub_control = $this->get_field_control_args( $sub_field );

							// Register the control to the repeater.
							$repeater->add_control( $sub_field_name, $sub_control );
						}

						// Build the control, assigning the repeater to the fields argument.
						$control = array(
							'type'   => Elementor\Controls_Manager::REPEATER,
							'fields' => $repeater->get_controls(),
						);
						break;

					/**
					 * Other Fields
					 */
					default:
						// Get the field's control's parameters.
						$control = $this->get_field_control_args( $field );
						break;
				}

				// Finally, register the control for this field.
				$this->add_control( $field_name, $control );
			}

			// Close the section.
			$this->end_controls_section();
		}

	}

	/**
	 * Returns the given field's control arguments, so that the field can be registered
	 * as an Elementor Control
	 *
	 * @since   3.0.8
	 *
	 * @param   array $field  Shortcode Field.
	 * @return  array           Elementor Control Arguments, compatible with add_control()
	 */
	private function get_field_control_args( $field ) {

		// Start building control.
		$control = array(
			'default'     => ( isset( $field['default_value'] ) ? $field['default_value'] : '' ),
			'label'       => $field['label'],
			'placeholder' => ( isset( $field['placeholder'] ) ? $field['placeholder'] : '' ),
			'desc'        => ( isset( $field['description'] ) ? $field['description'] : '' ),
		);

		// Add control depending on the field type.
		switch ( $field['type'] ) {
			/**
			 * Autocomplete
			 */
			case 'autocomplete':
				$control = array_merge(
					$control,
					array(
						'type' => 'page-generator-pro-autocomplete',
					)
				);
				break;

			/**
			 * Autocomplete textarea
			 */
			case 'autocomplete_textarea':
				$control = array_merge(
					$control,
					array(
						'type' => 'page-generator-pro-autocomplete-textarea',
					)
				);
				break;

			/**
			 * Text, Multiple Input
			 */
			case 'text_multiple':
				$control = array_merge(
					$control,
					array(
						'type' => Elementor\Controls_Manager::TEXT,
					)
				);
				break;

			/**
			 * Select, Multiple
			 */
			case 'select_multiple':
				$control = array_merge(
					$control,
					array(
						'type'     => Elementor\Controls_Manager::SELECT2,
						'multiple' => true,
						'options'  => $field['values'],
					)
				);
				break;

			/**
			 * Select
			 */
			case 'select':
				$control = array_merge(
					$control,
					array(
						'type'    => Elementor\Controls_Manager::SELECT,
						'options' => $field['values'],
					)
				);
				break;

			/**
			 * Number
			 */
			case 'number':
				$control = array_merge(
					$control,
					array(
						'type' => Elementor\Controls_Manager::NUMBER,
						'min'  => $field['min'],
						'max'  => $field['max'],
						'step' => $field['step'],
					)
				);
				break;

			/**
			 * Toggle
			 */
			case 'toggle':
				$control = array_merge(
					$control,
					array(
						'type' => Elementor\Controls_Manager::SWITCHER,
					)
				);
				break;

			default:
				$control = array_merge(
					$control,
					array(
						'type' => Elementor\Controls_Manager::TEXT,
					)
				);
				break;

		}

		return $control;

	}

	/**
	 * Renders the shortcode syntax, converted from the widget's properties array
	 *
	 * @since   3.0.8
	 */
	protected function render() {

		// Bail if no shortcode.
		if ( ! $this->shortcode ) {
			return '';
		}

		// Output [shortcode] string.
		// phpcs:disable WordPress.Security.EscapeOutput
		echo Page_Generator_Pro()->get_class( 'shortcode' )->build_shortcode(
			Page_Generator_Pro()->plugin->name . '-' . str_replace( 'page-generator-pro-elementor-', '', $this->slug ),
			$this->shortcode['fields'],
			$this->get_settings_for_display()
		);
		// phpcs:enable

	}

}
