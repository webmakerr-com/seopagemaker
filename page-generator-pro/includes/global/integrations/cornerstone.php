<?php
/**
 * Cornerstone Class
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Registers shortcodes as Elements for Pro and X Themes.
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 2.6.0
 */
class Page_Generator_Pro_Cornerstone {

	/**
	 * Holds the base object.
	 *
	 * @since   2.6.0
	 *
	 * @var     object
	 */
	public $base;

	/**
	 * Constructor
	 *
	 * @since   2.6.0
	 *
	 * @param   object $base    Base Plugin Class.
	 */
	public function __construct( $base ) {

		// Store base class.
		$this->base = $base;

		add_action( 'cs_register_elements', array( $this, 'add_elements' ) );
		add_filter( 'page_generator_pro_generate_set_post_meta__cornerstone_data', array( $this, 'wp_slash_page_builder_meta_on_generation' ) );
		add_filter( 'page_generator_pro_generate_content_settings', array( $this, 'convert_elements_to_class_raw_content_elements' ), 10, 1 );
		add_action( 'page_generator_pro_generate_content_finished', array( $this, 'rebuild_post_content' ), 10, 1 );

	}

	/**
	 * Registers Plugin Shortcodes as Elements, so that they can be used in the Cornerstone Editor
	 *
	 * @since   2.6.0
	 */
	public function add_elements() {

		// Bail if Cornerstone isn't available.
		if ( ! function_exists( 'cs_register_element' ) ) {
			return;
		}

		// Get shortcodes, depending on whether we're editing a Content Group or Post.
		if ( $this->is_editing_content_group_in_cornerstone() ) {
			$shortcodes = $this->base->get_class( 'shortcode' )->get_shortcodes();
		} else {
			$shortcodes = $this->base->get_class( 'shortcode' )->get_shortcode_supported_outside_of_content_groups();
		}

		// Bail if no shortcodes are available.
		if ( ! is_array( $shortcodes ) || count( $shortcodes ) === 0 ) {
			return;
		}

		// Iterate through shortcodes, registering them.
		foreach ( $shortcodes as $shortcode => $shortcode_properties ) {
			// Skip if no tabs or fields.
			if ( ! $shortcode_properties['tabs'] ) {
				continue;
			}
			if ( ! $shortcode_properties['fields'] ) {
				continue;
			}

			// Skip if no render callback exists i.e. it's the Research Dynamic Element.
			if ( ! $shortcode_properties['render_callback'] ) {
				continue;
			}

			// Register Element.
			cs_register_element(
				'page-generator-pro-' . $shortcode,
				array(
					'title'   => $this->base->plugin->displayName . ': ' . $shortcode_properties['title'],
					'icon'    => $shortcode_properties['gutenberg_icon'], // SVG contents.
					'values'  => $this->get_element_values( $shortcode_properties['default_values'] ),
					'builder' => function () use ( $shortcode, $shortcode_properties ) {
						return array(
							'control_nav' => $this->get_element_control_nav( $shortcode, $shortcode_properties['title'], $shortcode_properties['tabs'] ),
							'controls'    => $this->get_element_controls( $shortcode, $shortcode_properties['tabs'], $shortcode_properties['fields'], $shortcode_properties['default_values'] ),
						);
					},
					'render'  => array( $this, 'render_element' ),
				)
			);
		}

	}

	/**
	 * Returns a cs_regsiter_element() compatible array of values that the element supports
	 *
	 * @since   2.6.0
	 *
	 * @param   array $default_values   Key / Values.
	 * @return  array                   Element Values
	 */
	private function get_element_values( $default_values ) {

		$values = array();

		foreach ( $default_values as $key => $value ) {
			// Convert false to null, so 'false' doesn't output on text fields.
			if ( ! $value ) {
				$value = null;
			}

			$values[ $key ] = cs_value( $value, 'attr', false );
		}

		return $values;

	}

	/**
	 * Returns the element's control navigation, which are the shortcode's tabs
	 *
	 * @since   2.6.0
	 *
	 * @param   string $shortcode          Programmatic Shortcode Name.
	 * @param   string $shortcode_name     Shortcode Title.
	 * @param   array  $tabs               Shortcode UI Tabs.
	 * @return  array                       Control Navigation Tabs
	 */
	private function get_element_control_nav( $shortcode, $shortcode_name, $tabs ) {

		$control_nav = array(
			$shortcode => $shortcode_name,
		);

		foreach ( $tabs as $tab_name => $tab_properties ) {
			$control_nav[ $shortcode . ':' . $tab_name ] = $tab_properties['label'];
		}

		return $control_nav;

	}

	/**
	 * Returns the element's controls, which are the shortcode's fields, for the Cornerstone Builder
	 *
	 * @since   2.6.0
	 *
	 * @param   string $shortcode          Programmatic Shortcode Name.
	 * @param   array  $tabs               Shortcode Tabs.
	 * @param   array  $fields             Shortcode Fields.
	 * @param   array  $default_values     Shortcode Default Values.
	 * @return  array                       Element Builder Controls
	 */
	private function get_element_controls( $shortcode, $tabs, $fields, $default_values ) {

		$controls = array();

		foreach ( $tabs as $tab_name => $tab_properties ) {

			// Build this group's controls.
			$group_controls = array();
			foreach ( $tab_properties['fields'] as $field_name ) {
				// Skip if the field doesn't exist.
				if ( ! isset( $fields[ $field_name ] ) ) {
					continue;
				}

				// Define default value.
				$default_value = ( isset( $default_values[ $field_name ] ) ? $default_values[ $field_name ] : '' );

				// Add Element Control to this Group (Tab)'s Controls.
				$group_controls[] = $this->get_element_control( $field_name, $fields[ $field_name ] );
			}

			// Add this group and its controls.
			$controls[] = array(
				'type'     => 'group',
				'label'    => $this->get_description( $tab_properties ),
				'group'    => $shortcode . ':' . $tab_name,
				'controls' => $group_controls,
			);

		}

		return $controls;

	}

	/**
	 * Returns the tab's description, if one exists.
	 *
	 * @since   4.8.6
	 *
	 * @param   array $tab_properties     Shortcode Tab Properties.
	 * @return  string
	 */
	private function get_description( $tab_properties ) {

		if ( ! array_key_exists( 'description', $tab_properties ) ) {
			return '';
		}

		if ( strlen( $tab_properties['description'] ) < 85 ) {
			return $tab_properties['description'];
		}

		return substr( $tab_properties['description'], 0, 85 ) . '...';

	}

	/**
	 * Returns the given field's element control for the Cornerstone Builder
	 *
	 * @since   2.6.0
	 *
	 * @param   string $field_name     Field Name.
	 * @param   array  $field          Field.
	 * @return  array                   Element Control
	 */
	private function get_element_control( $field_name, $field ) {

		// Start building the Element Control.
		$element_control = array(
			'key'   => $field_name,
			'label' => $field['label'],
		);

		// If a condition is present, add it.
		if ( isset( $field['condition'] ) ) {
			// Define operator.
			// For arrays, operator must be 'IN' or 'NOT IN' instead of '==' or '!='.
			$op = $field['condition']['comparision'];
			switch ( $op ) {
				case '!=':
					if ( is_array( $field['condition']['value'] ) ) {
						$op = 'NOT IN';
					}
					break;

				default:
					if ( is_array( $field['condition']['value'] ) ) {
						$op = 'IN';
					}
					break;
			}

			$element_control = array_merge(
				$element_control,
				array(
					'condition' => array(
						'key'   => $field['condition']['key'],
						'value' => $field['condition']['value'],
						'op'    => $op,
					),
				)
			);
		}

		// Depending on the field type, define additional control field attributes.
		switch ( $field['type'] ) {

			case 'autocomplete':
			case 'autocomplete_textarea':
			case 'text':
			case 'text_multiple':
			case 'textarea':
			case 'select_multiple':
			case 'repeater':
				$element_control = array_merge(
					$element_control,
					array(
						'type' => 'text',
					)
				);
				break;

			case 'number':
				$element_control = array_merge(
					$element_control,
					array(
						'type' => $field['type'],
						'min'  => $field['min'],
						'max'  => $field['max'],
						'step' => ( isset( $field['step'] ) ? $field['step'] : '' ),
					)
				);
				break;

			case 'select':
				// Build field options.
				$field_options = array();
				foreach ( $field['values'] as $value => $label ) {
					$field_options[] = array(
						'value' => $value,
						'label' => $label,
					);
				}

				$element_control = array_merge(
					$element_control,
					array(
						'type'    => 'select',
						'options' => array(
							'choices' => $field_options,
						),
					)
				);
				break;

			case 'toggle':
				$element_control = array_merge(
					$element_control,
					array(
						'type'    => 'choose-single',
						'options' => array(
							'choices' => array(
								array(
									'value' => 0,
									'label' => __( 'No', 'page-generator-pro' ),
								),
								array(
									'value' => 1,
									'label' => __( 'Yes', 'page-generator-pro' ),
								),
							),
						),
					)
				);
				break;

			/**
			 * Fallback
			 */
			default:
				$element_control = array_merge(
					$element_control,
					array(
						'type' => 'text',
					)
				);
				break;

		}

		return $element_control;

	}

	/**
	 * Renders the shortcode syntax
	 *
	 * @since   2.6.0
	 *
	 * @param   array $atts   Attributes.
	 */
	public function render_element( $atts ) {

		// Get shortcode name.
		$shortcode_name = str_replace( $this->base->plugin->name . '-', '', $atts['_type'] );

		// Get shortcode.
		$shortcode = $this->base->get_class( 'shortcode' )->get_shortcode( $shortcode_name );

		// Bail if shortcode doesn't exist.
		if ( ! $shortcode ) {
			return false;
		}

		// Build shortcode markup.
		$html = Page_Generator_Pro()->get_class( 'shortcode' )->build_shortcode(
			$atts['_type'],
			$shortcode['fields'],
			$atts
		);

		// Output shortcode.
		ob_start();
		echo '<div class="x-raw-content" >' . $html . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput
		return ob_get_clean();

	}

	/**
	 * Adds slashes to Cornerstone's Page Builder Meta immediately before it's
	 * copied to the Generated Page.
	 *
	 * @since   2.6.1
	 *
	 * @param   string $value      Cornerstone Page Builder Data.
	 * @return  string              Cornerstone Page Builder Data
	 */
	public function wp_slash_page_builder_meta_on_generation( $value ) {

		return wp_slash( $value );

	}

	/**
	 * If the given Content Group's content contains Cornerstone Elements registered by this Plugin, converts them
	 * to Raw Content Elements, allowing subsequent generation routines to parse them.
	 *
	 * @since   2.6.0
	 *
	 * @param   array $settings       Group Settings.
	 * @return  array                   Group Settings.
	 */
	public function convert_elements_to_class_raw_content_elements( $settings ) {

		// Bail if no Cornerstone Data exists.
		if ( ! isset( $settings['post_meta']['_cornerstone_data'] ) ) {
			return $settings;
		}

		// Get shortcodes.
		$shortcodes = $this->base->get_class( 'shortcode' )->get_shortcodes();

		// Bail if no shortcodes are available.
		if ( ! is_array( $shortcodes ) || count( $shortcodes ) === 0 ) {
			return $settings;
		}

		// Get Cornerstone Data as array.
		$data = json_decode( $settings['post_meta']['_cornerstone_data'], true );

		// Bail if the Cornerstone Data couldn't be JSON decoded.
		if ( ! $data ) {
			return $settings;
		}

		// Iterate through Shortcodes.
		foreach ( $shortcodes as $shortcode_name => $shortcode_properties ) {
			// Skip if this shortcode is set to register outside of Content Groups.
			if ( ! $shortcode_properties['register_on_generation_only'] ) {
				continue;
			}

			// Recursively itereate through the array, replacing any elements of type matching this shortcode
			// to Raw Content Elements.
			$this->recursively_replace_elements_by_type( $data, $this->base->plugin->name . '-' . $shortcode_name, $shortcode_properties );
		}

		// Convert back to a JSON string.
		// JSON_UNESCAPED_SLASHES prevents URLs from having unecessary slashes added to them; we only want quotations in values to be slashed.
		$settings['post_meta']['_cornerstone_data'] = wp_json_encode( $data, JSON_UNESCAPED_SLASHES );

		// Return.
		return $settings;

	}

	/**
	 * Recursively iterate through Cornerstone Data, replacing any Elements whose type matches the
	 * given Shortcode Name with a Classic Raw Content Element.
	 *
	 * No return value is needed, as $arr is passed by reference.
	 *
	 * @since   2.6.0
	 *
	 * @param   array  $arr                    Cornerstone Data or Sub-Data.
	 * @param   string $shortcode_name         Shortcode Name to replace.
	 * @param   array  $shortcode_properties   Shortcode Properties.
	 */
	private function recursively_replace_elements_by_type( &$arr, $shortcode_name, $shortcode_properties ) {

		// Iterate through array.
		foreach ( $arr as $key => &$item ) {

			// If modules exist, call this function again against the modules (going down one level).
			if ( count( $item['_modules'] ) ) {
				$this->recursively_replace_elements_by_type( $item['_modules'], $shortcode_name, $shortcode_properties );
			}

			// If the item's type matches our shortcode name, replace it with a Classic Raw Content Element
			// comprising of the Shortcode's syntax.
			if ( $item['_type'] === $shortcode_name ) {
				$item = array(
					// We process shortcodes now, because slashing/unslashing breaks JSON conformity later on
					// if we process it through generate.php.
					'_type'       => 'raw-content',
					'_modules'    => array(),
					'raw_content' => do_shortcode(
						Page_Generator_Pro()->get_class( 'shortcode' )->build_shortcode(
							$item['_type'],
							$shortcode_properties['fields'],
							$item
						)
					),
				);
			}
		}

	}

	/**
	 * Whether the current request is for editing a Content Group using Cornerstone.
	 *
	 * @since   4.8.6
	 *
	 * @return  bool
	 */
	private function is_editing_content_group_in_cornerstone() {

		// Bail if not in the editor.
		if ( ! Page_Generator_Pro()->is_admin_or_frontend_editor() ) {
			return false;
		}

		if ( array_key_exists( 'cs_preview_state', $_REQUEST ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return true;
		}

		if ( array_key_exists( 'HTTP_REFERER', $_SERVER ) ) {
			// Check if referrer is e.g. edit.php?s=cornerstone&post_status=all&post_type=page-generator-pro.
			if ( strpos( sanitize_text_field( wp_unslash( $_SERVER['HTTP_REFERER'] ) ), 'post_type=page-generator-pro' ) !== false ) {
				return true;
			}

			// Check if referrer is e.g. /cornerstone/edit/post_id.
			$request_uri_parts = explode( '/', sanitize_text_field( wp_unslash( $_SERVER['HTTP_REFERER'] ) ) );
			$post_id           = $request_uri_parts[ count( $request_uri_parts ) - 1 ];

			if ( ! is_numeric( $post_id ) ) {
				return false;
			}
			if ( get_post_type( $post_id ) !== 'page-generator-pro' ) {
				return false;
			}

			return true;
		}

		if ( array_key_exists( 'REQUEST_URI', $_SERVER ) ) {
			// Check if referrer is e.g. /cornerstone/edit/post_id.
			$request_uri_parts = explode( '/', esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) );
			$post_id           = $request_uri_parts[ count( $request_uri_parts ) - 1 ];

			if ( ! is_numeric( $post_id ) ) {
				return false;
			}
			if ( get_post_type( $post_id ) !== 'page-generator-pro' ) {
				return false;
			}

			return true;
		}

		return false;

	}

	/**
	 * Loads the generated Page into Cornerstone's Content class, and saves it, to ensure
	 * that the post_content is rebuilt correctly.
	 *
	 * If this doesn't run, the underlying _cornerstone_data will only display in the
	 * backend Cornerstone editor, and not the frontend site.
	 *
	 * @since   2.6.0
	 *
	 * @param   int $post_id        Generated Post ID.
	 */
	public function rebuild_post_content( $post_id ) {

		// Bail if Cornerstone not active.
		if ( ! class_exists( '\Themeco\Cornerstone\Documents\Content' ) ) {
			return;
		}

		// Bail if no Cornerstone data in the generated Page.
		if ( empty( get_post_meta( $post_id, '_cornerstone_data', true ) ) ) {
			return;
		}

		// Update post_content.
		wp_update_post(
			array(
				'ID'           => $post_id,
				'post_content' => wp_slash( "[cs_content _p='" . $post_id . ']' . $post_id . '[/cs_content]' ),
			)
		);

	}

}
