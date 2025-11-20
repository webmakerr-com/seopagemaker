<?php
/**
 * Performance Class
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * If enabled in the Plugin settings, replaces select dropdowns
 * for e.g. Pages with text fields.
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 2.7.7
 */
class Page_Generator_Pro_Performance {

	/**
	 * Holds the base object.
	 *
	 * @since   2.7.7
	 *
	 * @var     object
	 */
	public $base;

	/**
	 * Constructor
	 *
	 * @since   2.7.7
	 *
	 * @param   object $base    Base Plugin Class.
	 */
	public function __construct( $base ) {

		// Store base class.
		$this->base = $base;

		// Maybe disable the Custom Fields dropdown on the Post Editor.
		add_filter( 'postmeta_form_keys', array( $this, 'maybe_remove_custom_fields_meta_box_meta_keys' ), 10, 2 );

		// Maybe change the wp_dropdown_page() select for an input.
		add_filter( 'quick_edit_dropdown_pages_args', array( $this, 'maybe_simplify_wp_dropdown_page_query' ), 10 );
		add_filter( 'page_attributes_dropdown_pages_args', array( $this, 'maybe_simplify_wp_dropdown_page_query' ), 10 );
		add_filter( 'wp_dropdown_pages', array( $this, 'maybe_replace_wp_dropdown_page' ), 10, 2 );

		// Maybe replace customizer dropdowns.
		add_action( 'customize_register', array( $this, 'maybe_replace_customizer_page_dropdowns' ), 9999 );

	}

	/**
	 * Defines the Meta Keys to display in the <select> dropdown for the Custom Fields Meta Box.
	 *
	 * If null is returned, WordPress will perform a DB query to fetch all unique
	 * meta keys from the Post Meta table, which can be a slow and expensive
	 * query if the WordPress installations contains a lot of post meta data.
	 *
	 * @since   0.0.1
	 *
	 * @param   array   $meta_keys  Meta Keys.
	 * @param   WP_Post $post       WordPress Post.
	 * @return  array               Meta Keys
	 */
	public function maybe_remove_custom_fields_meta_box_meta_keys( $meta_keys, $post ) {

		// Don't do anything if we are not disabling custom fields.
		$disable_custom_fields = $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-general', 'disable_custom_fields', '0' );
		if ( ! $disable_custom_fields ) {
			return $meta_keys;
		}

		// Define the meta keys that you want to return.
		// At least one key must be specified, otherwise WordPress will query the DB.
		$keys = array(
			'_page_generator_pro_group',
		);

		/**
		 * Defines the Meta Keys to make available in the Custom Fields dropdown.
		 *
		 * @since   2.0.7
		 *
		 * @param   array   $keys       Defined Meta Keys to use.
		 * @param   array   $meta_keys  Original Meta Keys.
		 * @param   WP_Post $post       WordPress Post.
		 */
		$keys = apply_filters( 'page_generator_pro_maybe_remove_custom_fields_meta_box_meta_keys', $keys, $meta_keys, $post );

		// Return keys.
		return $keys;

	}

	/**
	 * If the Plugin is configured to replace wp_dropdown_pages() <select> output with an AJAX <select> or
	 * input field, simplify the Page query so it isn't slow and expensive.
	 *
	 * This query is always run when wp_dropdown_pages() is called (even though we might not use it),
	 * so we always need to optimize it.
	 *
	 * @since   2.1.6
	 *
	 * @param   array $args   Arguments.
	 * @return  array               Arguments
	 */
	public function maybe_simplify_wp_dropdown_page_query( $args ) {

		// Don't do anything if we are not .
		$change_page_dropdown_field = $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-general', 'restrict_parent_page_depth', '0' );
		if ( ! $change_page_dropdown_field ) {
			return $args;
		}

		// Simplify the query.
		$args['depth']       = 1;
		$args['id']          = 1;
		$args['sort_column'] = 'ID';

		// Return.
		return $args;

	}

	/**
	 * Replaces the wp_dropdown_pages() <select> output with either an AJAX <select>
	 * or <input> output for performance.
	 *
	 * @since   2.1.6
	 *
	 * @param   string $output     HTML Output.
	 * @param   array  $args       Arguments.
	 * @return  string              HTML Output
	 */
	public function maybe_replace_wp_dropdown_page( $output, $args ) {

		// Don't do anything if we are not.
		$change_page_dropdown_field = $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-general', 'restrict_parent_page_depth', '0' );
		if ( ! $change_page_dropdown_field ) {
			return $output;
		}

		// Filter the output, depending on how we're changing the Page Parent Output.
		switch ( $change_page_dropdown_field ) {
			/**
			 * AJAX Select
			 */
			case 'ajax_select':
				// Get AJAX <select> HTML.
				$output = $this->change_wp_dropdown_pages_output_to_ajax_select_field( $output, $args );
				break;

			/**
			 * Input
			 */
			default:
				// Get <input> HTML.
				$output = $this->change_wp_dropdown_pages_output_to_input_field( $output, $args );
				break;
		}

		// Return.
		return $output;

	}

	/**
	 * Replaces the wp_dropdown_pages() <select> output with an <input> output,
	 * for performance.
	 *
	 * @since   2.1.6
	 *
	 * @param   string $output     HTML Output.
	 * @param   array  $args       Arguments.
	 * @return  string              HTML Output
	 */
	private function change_wp_dropdown_pages_output_to_input_field( $output, $args ) {

		// Get CSS class.
		$class = '';
		if ( ! empty( $args['class'] ) ) {
			$class = " class='" . esc_attr( $args['class'] ) . "'";
		}

		// Build field.
		$output = '<input type="text" name="' . esc_attr( $args['name'] ) . '"' . $class . ' id="' . esc_attr( $args['id'] ) . '" value="' . $args['selected'] . '" size="6" />
                    <br /><small>' . __( 'Enter the Page / Post ID', 'page-generator-pro' ) . '</small>';

		// If a parent is specified, fetch its title.
		if ( $args['selected'] ) {
			$output .= '<br /><small>' . get_the_title( $args['selected'] ) . '</small>';
		}

		// Return.
		return $output;

	}

	/**
	 * Replaces the wp_dropdown_pages() <select> output with an AJAX <select> output,
	 * for performance.
	 *
	 * @since   2.1.8
	 *
	 * @param   string $output     HTML Output.
	 * @param   array  $args       Arguments.
	 * @return  string              HTML Output
	 */
	private function change_wp_dropdown_pages_output_to_ajax_select_field( $output, $args ) {

		$output = '<select name="' . esc_attr( $args['name'] ) . '" class="wpzinc-selectize-search widefat" data-action="page_generator_pro_search_pages" data-args="' . http_build_query( $args ) . '" data-name-field="post_title" data-value-field="ID" data-method="POST" data-output-fields="post_title" data-nonce="' . wp_create_nonce( 'search_pages' ) . '">';
		if ( $args['selected'] ) {
			$output .= '<option value="' . $args['selected'] . '" selected>' . get_the_title( $args['selected'] ) . '</option>';
		}
		$output .= '</select>';

		// Return.
		return $output;

	}

	/**
	 * Checks if dropdown controls that relate to Page/Post/CPT selection need to be replaced with either an AJAX <select>
	 * or <input> for performance in the WordPress Customizer.
	 *
	 * @since   2.7.8
	 *
	 * @param   WP_Customize_Manager $customizer     Customizer Manager.
	 */
	public function maybe_replace_customizer_page_dropdowns( $customizer ) {

		// Don't do anything if we are not .
		$change_page_dropdown_field = $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-general', 'restrict_parent_page_depth', '0' );
		if ( ! $change_page_dropdown_field ) {
			return;
		}

		// Replace Customizer Page Dropdowns.
		$this->replace_customizer_page_dropdowns( $customizer, $change_page_dropdown_field );

	}

	/**
	 * Replaces dropdown controls that relate to Page/Post/CPT selection with either an AJAX <select>
	 * or <input> for performance.
	 *
	 * @since   2.7.8
	 *
	 * @param   WP_Customize_Manager $customizer                     Customizer Manager.
	 * @param   string               $change_page_dropdown_field     Type of Control to replace existing controls with.
	 */
	private function replace_customizer_page_dropdowns( $customizer, $change_page_dropdown_field ) {

		// Build array of controls to replace.
		$controls = array();

		/**
		 * Defines an array of Customizer Page Dropdown Controls that should be replaced
		 * with either an AJAX <select> or <input> ID for performance
		 *
		 * @since   2.7.8
		 *
		 * @param   array   $controls   Names of Controls to replace.
		 * @param   WP_Customize_Manager    $customizer     Customizer Manager.
		 */
		$controls = apply_filters( 'page_generator_pro_performance_replace_customizer_page_dropdowns', $controls, $customizer );

		// Bail if no controls defined.
		if ( empty( $controls ) ) {
			return;
		}

		// Build array of new controls, removing existing controls as we do this.
		$new_controls = array();
		foreach ( $controls as $control ) {

			// Get existing Customizer control.
			$customizer_control = $customizer->get_control( $control );

			// Skip if the control doesn't exist.
			if ( ! $customizer_control ) {
				continue;
			}

			// Add attributes to array.
			$new_controls[ $control ] = array(
				'label'       => $customizer_control->label,
				'description' => $customizer_control->description,
				'section'     => $customizer_control->section,
				'settings'    => $control,
				'type'        => 'text',
			);

			// Define additional attributes, depending on what we're changing the field to.
			switch ( $change_page_dropdown_field ) {
				/**
				 * AJAX Select
				 */
				case 'ajax_select':
					break;

				/**
				 * Input
				 */
				default:
					$new_controls[ $control ]['description'] = $customizer_control->description . ( ! empty( $customizer_control->description ) ? '. ' : '' ) . __( 'Enter the Page / Post ID', 'page-generator-pro' );
					break;
			}

			// Remove the existing control.
			$customizer->remove_control( $control );

		}

		// Bail if no new controls.
		if ( empty( $new_controls ) ) {
			return;
		}

		// Register new controls.
		// We do this after removal of all controls we're replacing, otherwise only the last control is replaced.
		foreach ( $new_controls as $control => $attributes ) {
			$customizer->add_control( $control, $attributes );
		}

	}

}
