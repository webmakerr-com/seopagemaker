<?php
/**
 * Ignore Errors Trait
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Trait for registering an option on Dynamic Elements to ignore errors.
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 */
trait Page_Generator_Pro_Ignore_Errors_Trait {

	/**
	 * Returns attributes for ignore errors functionality.
	 *
	 * @since   4.9.3
	 *
	 * @return  array
	 */
	public function get_ignore_errors_attributes() {

		return array(
			'ignore_errors' => array(
				'type'    => 'boolean',
				'default' => $this->get_default_value( 'ignore_errors' ),
			),
		);

	}

	/**
	 * Returns fields for ignore errors functionality.
	 *
	 * @since   4.9.3
	 *
	 * @return  array
	 */
	public function get_ignore_errors_fields() {

		return array(
			'ignore_errors' => array(
				'label'         => __( 'Ignore Errors', 'page-generator-pro' ),
				'type'          => 'toggle',
				'default_value' => $this->get_default_value( 'ignore_errors' ),
				'description'   => __( 'If enabled, an error (e.g. no content could be found) will result in blank content output, instead of an error preventing generation.', 'page-generator-pro' ),
			),
		);

	}

	/**
	 * Returns tabs for ignore errors functionality.
	 *
	 * @since   4.9.3
	 *
	 * @return  array
	 */
	public function get_ignore_errors_tabs() {

		return array(
			'errors' => array(
				'label'       => __( 'Errors', 'page-generator-pro' ),
				'class'       => 'errors',
				'description' => __( 'Defines how errors should be handled.', 'page-generator-pro' ),
				'fields'      => array(
					'ignore_errors',
				),
			),
		);

	}

	/**
	 * Returns default values for ignore errors functionality.
	 *
	 * @since   4.9.3
	 *
	 * @return  array
	 */
	public function get_ignore_errors_default_values() {

		return array(
			'ignore_errors' => 0,
		);

	}

}
