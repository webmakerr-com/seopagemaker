<?php
/**
 * Taxonomy Class
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Registers Term Groups as a Taxonomy.
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 1.6.1
 */
class Page_Generator_Pro_Taxonomy {

	/**
	 * Holds the base object.
	 *
	 * @since   1.9.8
	 *
	 * @var     object
	 */
	public $base;

	/**
	 * Holds the Taxonomy Name for Taxonomy Groups
	 *
	 * @since   1.6.1
	 *
	 * @var     string
	 */
	public $taxonomy_name = 'page-generator-tax';

	/**
	 * Constructor
	 *
	 * @since   1.6.1
	 *
	 * @param   object $base    Base Plugin Class.
	 */
	public function __construct( $base ) {

		// Store base class.
		$this->base = $base;

		// Register Taxonomy.
		add_action( 'init', array( $this, 'register_taxonomies' ) );

		// Permit HTML in the Description Field.
		add_action( 'init', array( $this, 'permit_html_in_term_descriptions' ) );

	}

	/**
	 * Registers Custom Post Types
	 *
	 * @since    1.6.1
	 */
	public function register_taxonomies() {

		register_taxonomy(
			$this->taxonomy_name,
			array( $this->base->get_class( 'post_type' )->post_type_name ),
			array(
				'labels'             => array(
					'name'                  => _x( 'Taxonomy Groups', 'Taxonomy Groups', 'page-generator-pro' ),
					'singular_name'         => _x( 'Taxonomy Group', 'Taxonomy Group', 'page-generator-pro' ),
					'search_items'          => __( 'Search Taxonomy Groups', 'page-generator-pro' ),
					'popular_items'         => __( 'Popular Taxonomy Groups', 'page-generator-pro' ),
					'all_items'             => __( 'All Taxonomy Groups', 'page-generator-pro' ),
					'parent_item'           => __( 'Parent Taxonomy Group', 'page-generator-pro' ),
					'parent_item_colon'     => __( 'Parent Taxonomy Group', 'page-generator-pro' ),
					'edit_item'             => __( 'Edit Taxonomy Group', 'page-generator-pro' ),
					'update_item'           => __( 'Update Taxonomy Group', 'page-generator-pro' ),
					'add_new_item'          => __( 'Add New Taxonomy Group', 'page-generator-pro' ),
					'new_item_name'         => __( 'New Taxonomy Group Name', 'page-generator-pro' ),
					'add_or_remove_items'   => __( 'Add or remove Taxonomy Groups', 'page-generator-pro' ),
					'choose_from_most_used' => __( 'Choose from most used Taxonomy Groups', 'page-generator-pro' ),
					'menu_name'             => __( 'Taxonomy Group', 'page-generator-pro' ),
				),
				'public'             => is_user_logged_in(), // Allow Plugin Metaboxes on Edit Term Screen.
				'publicly_queryable' => is_user_logged_in(), // Allow Plugin Metaboxes on Edit Term Screen.
				'show_ui'            => true,
				'show_in_menu'       => true,
				'show_in_nav_menus'  => false,
				'show_in_rest'       => false,
				'show_tagcloud'      => false,
				'show_in_quick_edit' => false,
				'show_admin_column'  => false,
				'hierarchical'       => false,
			)
		);

	}

	/**
	 * Permits HTML in Term Descriptions
	 *
	 * @since   2.2.4
	 */
	public function permit_html_in_term_descriptions() {

		// Remove the filters which prevent HTML in term descriptions.
		remove_filter( 'pre_term_description', 'wp_filter_kses' );
		remove_filter( 'term_description', 'wp_kses_data' );

		// Add filters to prevent unsafe HTML tags, if the user cannot
		// use unfiltered HTML.
		if ( ! current_user_can( 'unfiltered_html' ) ) {
			add_filter( 'pre_term_description', 'wp_kses_post' );
			add_filter( 'term_description', 'wp_kses_post' );
		}

	}

	/**
	 * Outputs Term Descriptions with filters similar to those used
	 * in the_content().
	 *
	 * @since   2.2.4
	 */
	public function process_term_descriptions_output() {

		add_filter( 'term_description', 'wptexturize' );
		add_filter( 'term_description', 'convert_smilies' );
		add_filter( 'term_description', 'convert_chars' );
		add_filter( 'term_description', 'wpautop' );

	}

}
