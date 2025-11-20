<?php
/**
 * Post Type Class
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Registers Content Groups as a Post Type.
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 1.2.3
 */
class Page_Generator_Pro_PostType {

	/**
	 * Holds the base object.
	 *
	 * @since   1.9.8
	 *
	 * @var     object
	 */
	public $base;

	/**
	 * Holds the Post Type Name for Post Type Groups
	 *
	 * @since   1.3.8
	 *
	 * @var     string
	 */
	public $post_type_name = 'page-generator-pro';

	/**
	 * Constructor
	 *
	 * @since   1.2.3
	 *
	 * @param   object $base    Base Plugin Class.
	 */
	public function __construct( $base ) {

		// Store base class.
		$this->base = $base;

		// Register post types.
		add_action( 'init', array( $this, 'register_post_types' ), 9 );

		// Blocks previewing this Post Type on the frontend.
		add_action( 'init', array( $this, 'block_preview' ) );

		// Ensure that SEO plugins don't include our Post Type.
		add_filter( 'wpseo_sitemap_exclude_post_type', array( $this, 'yoast_exclude_post_type' ), 10, 2 );

	}

	/**
	 * Registers Custom Post Types
	 *
	 * @since    1.2.3
	 */
	public function register_post_types() {

		// To allow most Plugins, Page Builders and Meta Boxes to function on Groups,
		// public needs to be true.  We don't want to do this on the frontend site,
		// as Groups may start to be indexed.
		$public    = is_admin();
		$logged_in = is_user_logged_in();
		$supports  = $this->supports();

		// Define Post Type arguments.
		$args = array(
			'labels'              => array(
				'name'               => __( 'Content Groups', 'page-generator-pro' ),
				'singular_name'      => __( 'Content Group', 'page-generator-pro' ),
				'menu_name'          => $this->base->plugin->displayName,
				'add_new'            => __( 'Add New', 'page-generator-pro' ),
				'add_new_item'       => __( 'Add New Content Group', 'page-generator-pro' ),
				'edit_item'          => __( 'Edit Content Group', 'page-generator-pro' ),
				'new_item'           => __( 'New Content Group', 'page-generator-pro' ),
				'view_item'          => __( 'View Content Group', 'page-generator-pro' ),
				'search_items'       => __( 'Search Content Groups', 'page-generator-pro' ),
				'not_found'          => __( 'No Content Groups found', 'page-generator-pro' ),
				'not_found_in_trash' => __( 'No Content Groups found in Trash', 'page-generator-pro' ),
				'parent_item_colon'  => '',
			),
			/* translators: Plugin Name */
			'description'         => sprintf(
				/* translators: Plugin Name */
				__( '%s Groups', 'page-generator-pro' ),
				$this->base->plugin->displayName
			),
			'public'              => $logged_in,      // Needs to be true for Visual Composer?
			'publicly_queryable'  => $logged_in,      // Needs to be true for frontend Page Builders.
			'exclude_from_search' => ( ! $public ),  // Needs to be false for X Pro Theme.
			'show_ui'             => true,
			'show_in_menu'        => 'foo', // Needs to be anything but 'false' for Oxygen Builder Back to WP > Admin.
			'menu_position'       => 9999,
			'menu_icon'           => 'dashicons-admin-network',
			'capability_type'     => 'page',
			'hierarchical'        => true,
			'supports'            => $supports,
			'has_archive'         => false,
			'show_in_nav_menus'   => $logged_in,
			'show_in_rest'        => true,
		);

		/**
		 * Filter the arguments for registering the Content Groups Post Type
		 *
		 * @since   3.3.9
		 *
		 * @param   array $args     register_post_type() compatible arguments.
		 */
		$args = apply_filters( 'page_generator_pro_posttype_register_post_types', $args );

		// Register Post Type.
		register_post_type( $this->post_type_name, $args );

	}

	/**
	 * Prevents previewing this Post Type
	 *
	 * @since   2.6.5
	 */
	public function block_preview() {

		// If we're not previewing a Content Group, let the request through.
		if ( ! $this->is_preview() ) {
			return;
		}

		// Stop the request.
		wp_die( esc_html__( 'To preview a Content Group, use the Test functionality.', 'page-generator-pro' ) );

	}


	/**
	 * Detects if the request is a preview this Post Type
	 *
	 * @since   2.6.5
	 *
	 * @return  bool    Is Preview
	 */
	private function is_preview() {

		// Admin and Frontend Editor requests aren't previews.
		if ( $this->base->is_admin_or_frontend_editor() ) {
			return false;
		}

		// Not a preview if the required request parameters are missing.
		if ( ! isset( $_REQUEST['preview_id'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			return false;
		}
		if ( ! isset( $_REQUEST['preview'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			return false;
		}

		// Not a preview if it's not a Content Group.
		$preview_id = absint( $_REQUEST['preview_id'] ); // phpcs:ignore WordPress.Security.NonceVerification
		if ( get_post_type( $preview_id ) !== $this->post_type_name ) {
			return false;
		}

		// Is a preview.
		return true;

	}

	/**
	 * Defines an array of features this Post Type supports that are compatible
	 * with https://codex.wordpress.org/Function_Reference/register_post_type
	 *
	 * @since   2.3.5
	 *
	 * @return  array   Supported Features
	 */
	private function supports() {

		// Define default supported features.
		// custom-fields is required for Gutenberg's getEditedPostAttribute('meta') to work on a CPT.
		// Some Themes use this function e.g. Neve, so it's best practice to include custom-fields here.
		$supports = array( 'title', 'editor', 'page-attributes', 'custom-fields' );

		// Add Revisions, if enabled.
		if ( $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-general', 'revisions', '0' ) ) {
			$supports[] = 'revisions';
		}

		/**
		 * Define the supported features for Content Groups
		 *
		 * @since   2.8.4
		 *
		 * @param   array   $supports   Supported Featured.
		 */
		$supports = apply_filters( 'page_generator_pro_posttype_supports', $supports );

		return $supports;

	}

	/**
	 * Flag to determine whether to exclude a Post Type from Yoast SEO
	 *
	 * @since   1.7.9
	 *
	 * @param   bool   $exclude    Whether to exclude a Post Type.
	 * @param   string $post_type  Post Type to possibly exclude.
	 * @return  bool
	 */
	public function yoast_exclude_post_type( $exclude, $post_type ) {

		// Return original result if we're not on the Page Generator Pro Post Type.
		if ( $post_type !== $this->post_type_name ) {
			return $exclude;
		}

		// Exclude this Post Type from Yoast SEO.
		return true;

	}

}
