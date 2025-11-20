<?php
/**
 * Flotheme (Porto 2) Integration Class
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Registers Flotheme (Porto 2) as a Plugin integration:
 * - Register layout metaboxes on Content Groups
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 3.3.7
 */
class Page_Generator_Pro_Flotheme extends Page_Generator_Pro_Integration {

	/**
	 * Holds the base object.
	 *
	 * @since   3.3.7
	 *
	 * @var     object
	 */
	public $base;

	/**
	 * Constructor
	 *
	 * @since   3.3.7
	 *
	 * @param   object $base    Base Plugin Class.
	 */
	public function __construct( $base ) {

		// Store base class.
		$this->base = $base;

		add_filter( 'flo_sidebars_available_post_types', array( $this, 'register_flotheme_layout_sidebars_support' ) );
		add_filter( 'acf/get_field_group', array( $this, 'register_flotheme_layout_support' ) );

	}

	/**
	 * Allows Flotheme's Layout and Sidebars ACF Group to display on
	 * Page Generator Pro's Groups
	 *
	 * @since   2.5.9
	 *
	 * @param   array $location_array     ACF acf_add_local_field_group() location-compatible conditions.
	 * @return  array                       ACF acf_add_local_field_group() location-compatible conditions
	 */
	public function register_flotheme_layout_sidebars_support( $location_array ) {

		// Add Page Generator Pro Content Group CPT.
		$location_array[] = array(
			array(
				'param'    => 'post_type',
				'operator' => '==',
				'value'    => 'page-generator-pro',
			),
		);

		return $location_array;

	}

	/**
	 * Allows Flotheme's Layout ACF Group to display on
	 * Page Generator Pro's Groups by modifying the location parameter
	 *
	 * @since   2.5.9
	 *
	 * @param   array $group  Field Group.
	 * @return  array           Field Group
	 */
	public function register_flotheme_layout_support( $group ) {

		// Skip if this isn't the Location Group.
		if ( $group['key'] !== 'group_59b6784711f0a' ) {
			return $group;
		}

		// Modify the location parameter to include Page Generator Pro Content Group CPT.
		$group['location'][1] = array(
			array(
				'param'    => 'post_type',
				'operator' => '==',
				'value'    => 'page-generator-pro',
			),
		);

		return $group;

	}

}
