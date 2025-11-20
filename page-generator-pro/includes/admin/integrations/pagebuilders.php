<?php
/**
 * Page Builders Integration Class
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Force Page Builders, which use get_post_templates / get_page_templates() with
 * the Page Generator Pro Post Type specified, to display all available Templates
 * across all Post Types.
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 1.3.7
 */
class Page_Generator_Pro_PageBuilders {

	/**
	 * Holds the base object.
	 *
	 * @since   1.9.8
	 *
	 * @var     object
	 */
	public $base;

	/**
	 * Constructor
	 *
	 * @since   1.3.7
	 *
	 * @param   object $base    Base Plugin Class.
	 */
	public function __construct( $base ) {

		// Store base class.
		$this->base = $base;

		// Register all Post Type Templates to Page Builders.
		add_filter( 'theme_page-generator-pro_templates', array( $this, 'add_all_post_type_templates_to_page_builders' ), 10, 4 );

	}

	/**
	 * Force Page Builders, which use get_post_templates / get_page_templates() with
	 * the Page Generator Pro Post Type specified, to display all available Templates
	 * across all Post Types
	 *
	 * @since   1.9.3
	 *
	 * @param   array    $post_templates     Post Templates for the given $post_type.
	 * @param   WP_Theme $wp_theme           WP Theme class object.
	 * @param   WP_Post  $post               WordPress Post.
	 * @param   string   $post_type          Post Type $post_templates are for.
	 * @return  array                           All Post Templates across all Post Types
	 */
	public function add_all_post_type_templates_to_page_builders( $post_templates, $wp_theme, $post, $post_type ) {

		// Fetch array of templates by each Post Type.
		$post_type_templates = $wp_theme->get_post_templates();

		// Bail if empty.
		if ( empty( $post_type_templates ) ) {
			return $post_templates;
		}

		// Build flat list of templates.
		$all_templates = array();
		foreach ( $post_type_templates as $post_type_templates_post_type => $templates ) {
			$all_templates = array_merge( $all_templates, $templates );
		}

		/**
		 * Filter the Post Type Templates to register on Page Builders.
		 *
		 * @since   1.9.3
		 *
		 * @param   array       $all_templates      All Post Templates.
		 * @param   array       $post_templates     Post Templates for the given $post_type.
		 * @param   WP_Theme    $wp_theme           WP Theme class object.
		 * @param   WP_Post     $post               WordPress Post.
		 * @param   string      $post_type          Post Type $post_templates are for.
		 */
		$all_templates = apply_filters( 'page_generator_pro_groups_add_post_type_templates', $all_templates, $post_templates, $wp_theme, $post, $post_type );

		// Return all templates.
		return $all_templates;

	}

}
