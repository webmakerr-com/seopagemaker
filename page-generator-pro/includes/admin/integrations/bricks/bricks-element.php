<?php
/**
 * Bricks Element Class
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Registers Dynamic Elements as Bricks Elements.
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 5.2.7
 */
class Page_Generator_Pro_Bricks_Element extends \Bricks\Element {

	/**
	 * The element's icon.
	 *
	 * @since   5.2.7
	 *
	 * @var     string
	 */
	public $icon = 'ti-bolt-alt';

	/**
	 * The element's category.
	 *
	 * @since   5.2.7
	 *
	 * @var     string
	 */
	public $category = 'page-generator-pro';

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
	 *
	 * @param   null|object $element  Element.
	 */
	public function __construct( $element = null ) {

		// Get shortcode.
		$this->shortcode = Page_Generator_Pro()->get_class( 'shortcode' )->get_shortcode( $this->block_name );
		if ( ! $this->shortcode ) {
			return;
		}

		parent::__construct( $element );

	}

	/**
	 * Returns the element's label.
	 *
	 * @since   5.2.7
	 */
	public function get_label() {

		return esc_html( $this->shortcode['title'] );

	}

	/**
	 * Returns the element's keywords, used when searching for the element.
	 *
	 * @since   5.2.7
	 */
	public function get_keywords() {

		if ( ! array_key_exists( 'keywords', $this->shortcode ) ) {
			return array();
		}

		return $this->shortcode['keywords'];

	}

	/**
	 * Defines the element's control groups from the shortcode's tabs.
	 *
	 * @since   5.2.7
	 */
	public function set_control_groups() {

		foreach ( $this->shortcode['tabs'] as $tab_name => $tab_properties ) {
			$this->control_groups[ $tab_name ] = array(
				'title' => $tab_properties['label'],
				'tab'   => 'content',
			);
		}

	}

	/**
	 * Define the fields that can be configured for this Element
	 *
	 * @since   5.2.7
	 *
	 * @var     array
	 */
	public function set_controls() {

		// Build fields.
		foreach ( $this->shortcode['fields'] as $field_name => $field ) {
			// Start building field definition.
			$element_field = array(
				'tab'     => 'content',
				'group'   => $this->get_tab( $field_name ),
				'label'   => $field['label'],
				'type'    => $field['type'],
				'default' => $this->get_default_value( $field ),
			);

			// Add/change field parameters depending on the field's type.
			switch ( $field['type'] ) {
				/**
				 * Autocomplete
				 */
				case 'autocomplete':
				case 'text_multiple':
				case 'select_multiple':
					$element_field['type'] = 'text';
					break;

				case 'autocomplete_textarea':
					$element_field['type'] = 'textarea';
					break;

				/**
				 * Select
				 */
				case 'select':
					$element_field['options'] = $field['values'];
					break;

				/**
				 * Toggle
				 */
				case 'toggle':
					$element_field = array_merge(
						$element_field,
						array(
							'type'    => 'checkbox',
							'default' => ( $this->get_default_value( $field ) === 1 ? true : false ),
						)
					);
					break;

				case 'repeater':
					$element_field['type'] = 'text';
					break;

			}

			// Add field.
			$this->controls[ $field_name ] = $element_field;
		}

	}

	/**
	 * Enqueue element CSS.
	 *
	 * @since   5.2.7
	 */
	public function enqueue_scripts() {

		// Enqueue CSS for element icons.
		wp_enqueue_style( Page_Generator_Pro()->plugin->name . '-bricks', Page_Generator_Pro()->plugin->url . 'assets/css/bricks.css', array(), Page_Generator_Pro()->plugin->version );

	}

	/**
	 * Renders the shortcode syntax.
	 *
	 * @since   5.2.7
	 */
	public function render() {

		// If this is on the frontend site (i.e not in the page builder), just render the shortcode.
		if ( ! Page_Generator_Pro()->is_admin_or_frontend_editor() ) {
			echo Page_Generator_Pro()->get_class( 'shortcode_' . str_replace( '-', '_', $this->block_name ) )->render( $this->settings ); // phpcs:ignore WordPress.Security.EscapeOutput
			return;
		}

		// Add 'class' attribute to element root tag.
		$this->set_attribute(
			'_root',
			'class',
			array(
				'page-generator-pro-bricks-element',
				$this->block_name,
				'page-generator-pro-bricks-element-' . $this->block_name,
			)
		);

		// Render element HTML.
		// '_root' attribute is required (contains element ID, class, etc.).
		echo '<div ' . $this->render_attributes( '_root' ) . '><div class="page-generator-pro-bricks-element-title">'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

		/* translators: %s: Dynamic Element Title */
		echo esc_html( sprintf( __( '%s Dynamic Element', 'page-generator-pro' ), $this->shortcode['title'] ) );

		echo '</div>
		<div class="page-generator-pro-bricks-element-description">' . esc_html( $this->shortcode['description'] ) . '</div>
		<div class="page-generator-pro-bricks-element-description">' . esc_html__( 'Click this element to edit its settings.', 'page-generator-pro' ) . '</div>
		</div>';

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
