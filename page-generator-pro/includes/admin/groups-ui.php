<?php
/**
 * Content Groups UI Class
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Handles Content Groups Post Type's UI for creating
 * and editing Content Groups.
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 2.0.2
 */
class Page_Generator_Pro_Groups_UI {

	/**
	 * Holds the base class object.
	 *
	 * @since   2.0.2
	 *
	 * @var     object
	 */
	public $base;

	/**
	 * Holds keywords for the Group we're editing
	 *
	 * @since   2.0.2
	 *
	 * @var     bool|array
	 */
	public $keywords = false;

	/**
	 * Holds settings for the Group we're editing
	 *
	 * @since   2.0.2
	 *
	 * @var     array
	 */
	public $settings = array();

	/**
	 * Constructor.
	 *
	 * @since   2.0.2
	 *
	 * @param   object $base    Base Plugin Class.
	 */
	public function __construct( $base ) {

		// Store base class.
		$this->base = $base;

		// Add other 'Add New' buttons.
		add_filter( 'views_edit-page-generator-pro', array( $this, 'output_add_new_buttons' ) );

		// Add filter by Group to Pages, Posts and Custom Post Types.
		add_action( 'restrict_manage_posts', array( $this, 'output_posts_filter_by_group_dropdown' ), 10 );
		add_action( 'parse_query', array( $this, 'posts_filter_by_group' ) );

		// Search.
		add_filter( 'posts_join', array( $this, 'search_settings_join' ), 999 );
		add_filter( 'posts_where', array( $this, 'search_settings_where' ), 999 );

		// Modify Post Messages.
		add_filter( 'post_updated_messages', array( $this, 'post_updated_messages' ) );

		// Don't allow Group Editing if a Group is Generating Content.
		add_filter( 'user_has_cap', array( $this, 'maybe_prevent_group_edit' ), 10, 3 );

		// Before Title.
		add_action( 'edit_form_top', array( $this, 'output_keywords_dropdown_before_title' ) );

		// Meta Boxes.
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );

		// Save Group.
		add_action( 'save_post', array( $this, 'save_post' ) );

		// Page Generator.
		if ( class_exists( 'Page_Generator' ) ) {
			add_action( 'init', array( $this, 'limit_admin' ) );
			add_filter( 'wp_insert_post_empty_content', array( $this, 'limit_xml_rpc' ), 10, 2 );
		}

	}

	/**
	 * Outputs links in the WP_List_Table filters, which will be moved by JS to display
	 * next to the Add New button
	 *
	 * @since   3.2.9
	 *
	 * @param   array $views  Views.
	 * @return  array           Views
	 */
	public function output_add_new_buttons( $views ) {

		// Get any registered buttons.
		$buttons = $this->get_add_new_buttons();

		// If no buttons specified, just return views.
		if ( ! count( $buttons ) ) {
			return $views;
		}

		// Build HTML for the button.
		foreach ( $buttons as $key => $button ) {
			$views[ $key ] = '<a href="' . $button['url'] . '" class="page-generator-pro-group-action page-title-action hidden">' . $button['label'] . '</a>';
		}

		// Return.
		return $views;

	}

	/**
	 * Registers buttons to be displayed in the WP_List_Table filters, which will be moved by JS to display
	 * next to the Add New button
	 *
	 * @since   4.1.0
	 *
	 * @return  array           Views
	 */
	public function get_add_new_buttons() {

		$buttons = array();

		/**
		 * Registers buttons to be displayed in the WP_List_Table filters, which will be moved by JS to display
		 * next to the Add New button
		 *
		 * @since   3.2.9
		 *
		 * @param   array $buttons  Buttons.
		 * @return  array           Buttons
		 */
		$buttons = apply_filters( 'page_generator_pro_groups_ui_output_add_new_buttons', $buttons );

		return $buttons;

	}

	/**
	 * Outputs the Filter by Group Dropdown on Pages, Posts and Custom Post Types
	 *
	 * @since   2.2.6
	 *
	 * @param   string $post_type  Post Type.
	 */
	public function output_posts_filter_by_group_dropdown( $post_type ) {

		// Bail if the Post Type is a Content Group.
		if ( $post_type === $this->base->get_class( 'post_type' )->post_type_name ) {
			return;
		}

		// Get all Groups.
		$groups = $this->base->get_class( 'groups' )->get_all_ids_names();

		// Bail if no Groups exist.
		if ( ! $groups ) {
			return;
		}

		// Get currently selected Group, if any.
		$current_group_id = ( isset( $_REQUEST['page_generator_pro_group_id'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['page_generator_pro_group_id'] ) ) : '' ); // phpcs:ignore WordPress.Security.NonceVerification

		// Load view.
		include $this->base->plugin->folder . 'views/admin/wp-list-table-filter-groups-dropdown.php';

	}

	/**
	 * Adds WHERE clause(s) to the WP_Query when the User has requested to filter Pages,
	 * Posts or Custom Post Types by a Group ID
	 *
	 * @since   2.2.6
	 *
	 * @param   WP_Query $query  WordPress Query object.
	 */
	public function posts_filter_by_group( $query ) {

		// Bail if the filter isn't active.
		if ( ! isset( $_REQUEST['page_generator_pro_group_id'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			return $query;
		}
		if ( empty( $_REQUEST['page_generator_pro_group_id'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			return $query;
		}

		// Bail if the filter isn't for the Post Type that we're viewing.
		if ( ( array_key_exists( 'post_type', $_REQUEST ) && sanitize_text_field( wp_unslash( $_REQUEST['post_type'] ) ) !== $query->query_vars['post_type'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			return $query;
		}

		// Get Group ID.
		$group_id = sanitize_text_field( wp_unslash( $_REQUEST['page_generator_pro_group_id'] ) ); // phpcs:ignore WordPress.Security.NonceVerification

		// If no meta query var is defined, define a blank array now.
		if ( ! isset( $query->query_vars['meta_query'] ) ) {
			$query->query_vars['meta_query'] = array();
		}

		// If Group ID is -1, query Pages / Posts / Custom Post Types that weren't
		// created by a Content Group.
		if ( $group_id == -1 ) { // phpcs:ignore Universal.Operators.StrictComparisons.LooseEqual
			$query->query_vars['meta_query'][] = array(
				'key'     => '_page_generator_pro_group',
				'compare' => 'NOT EXISTS',
			);
		} else {
			$query->query_vars['meta_query'][] = array(
				'key'   => '_page_generator_pro_group',
				'value' => $group_id,
			);
		}

	}

	/**
	 * Adds a join to the WordPress meta table for Content Group searches in the WordPress Administration
	 *
	 * @since   2.3.5
	 *
	 * @param   string $join   SQL JOIN Statement.
	 * @return  string          SQL JOIN Statement
	 */
	public function search_settings_join( $join ) {

		global $wpdb, $wp_query;

		// Bail if no search term specified.
		if ( empty( $wp_query->query_vars['s'] ) ) {
			return $join;
		}

		// Bail if we're not searching Content Groups.
		if ( $wp_query->query_vars['post_type'] !== $this->base->get_class( 'post_type' )->post_type_name ) {
			return $join;
		}

		// Append JOIN and return.
		$join .= " LEFT JOIN $wpdb->postmeta AS pgp_postmeta ON $wpdb->posts.ID = pgp_postmeta.post_id ";
		return $join;

	}

	/**
	 * Adds a where clause to the WordPress meta table for Content Group searches in the WordPress Administration
	 *
	 * @since   2.3.5
	 *
	 * @param   string $where      SQL WHERE.
	 * @return  string
	 */
	public function search_settings_where( $where ) {

		global $wpdb, $wp_query;

		// Bail if no search term specified.
		if ( empty( $wp_query->query_vars['s'] ) ) {
			return $where;
		}

		// Bail if we're not searching Content Groups.
		if ( $wp_query->query_vars['post_type'] !== $this->base->get_class( 'post_type' )->post_type_name ) {
			return $where;
		}

		// Build WHERE conditions.
		$where_conditions = array(
			"(pgp_postmeta.meta_key = '_page_generator_pro_settings' AND pgp_postmeta.meta_value LIKE '%" . $wp_query->query_vars['s'] . "%')",
		);

		// Find WHERE search clause(s).
		$start = strpos( $where, 'AND (((' );
		$end   = strpos( $where, ')))', $start );

		// Bail if we couldn't find the WHERE search clause(s).
		if ( $start === false || $end === false ) {
			return $where;
		}

		// Append just after wp_posts.post_content LIKE ...
		$where = str_replace( '))', ' OR ' . implode( ' OR ', $where_conditions ) . '))', $where );

		// Group.
		$where .= ' GROUP BY ' . $wpdb->posts . '.id';

		// Return.
		return $where;

	}

	/**
	 * Defines admin notices for the Post Type.
	 *
	 * This also removes the 'View post' link on the message, which would result
	 * in an error on the frontend.
	 *
	 * @since   2.0.2
	 *
	 * @param   array $messages   Messages.
	 * @return  array               Messages
	 */
	public function post_updated_messages( $messages ) {

		$messages[ $this->base->get_class( 'post_type' )->post_type_name ] = array(
			0  => '', // Unused. Messages start at index 1.
			1  => __( 'Group updated.', 'page-generator-pro' ),
			2  => __( 'Custom field updated.', 'page-generator-pro' ),
			3  => __( 'Custom field deleted.', 'page-generator-pro' ),
			4  => __( 'Group updated.', 'page-generator-pro' ),
			/* translators: Post revision title */
			5  => ( isset( $_GET['revision'] ) ? sprintf( __( 'Group restored to revision from %s.', 'page-generator-pro' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false ), // phpcs:ignore WordPress.Security.NonceVerification
			6  => __( 'Group saved.', 'page-generator-pro' ),
			7  => __( 'Group saved.', 'page-generator-pro' ),
			8  => __( 'Group submitted.', 'page-generator-pro' ),
			9  => __( 'Group scheduled.', 'page-generator-pro' ),
			10 => __( 'Group draft updated.', 'page-generator-pro' ),
		);

		return $messages;

	}

	/**
	 * Prevent loading of editing a Group if that Group is Generating Content.
	 *
	 * Sets the capability to false when current_user_can() has been called on
	 * one of the capabilities we're interested in on a locked or protected post.
	 *
	 * @since   2.0.2
	 *
	 * @param   array $all_caps All capabilities of the user.
	 * @param   array $cap      [0] Required capability.
	 * @param   array $args     [0] Requested capability.
	 *                          [1] User ID.
	 *                          [2] Post ID.
	 */
	public function maybe_prevent_group_edit( $all_caps, $cap, $args ) {

		// Let the request through if it doesn't contain the required arguments.
		if ( ! isset( $args[2] ) ) {
			return $all_caps;
		}

		// Fetch the Capability the User requires, and the Group ID.
		$capability = $args[0];
		$group_id   = $args[2];

		// If the capability the User requires isn't one that we need to modify, let the request through.
		$capabilities_to_disable = $this->base->get_class( 'common' )->get_capabilities_to_disable_on_group_content_generation();
		if ( ! in_array( $capability, $capabilities_to_disable, true ) ) {
			return $all_caps;
		}

		// If the Group ID doesn't correspond to a Group (i.e. it's a capability for a different Post or Term), let the request through.
		if ( get_post_type( $group_id ) !== $this->base->get_class( 'post_type' )->post_type_name ) {
			return $all_caps;
		}

		// If the Group isn't generating content, let the request through.
		if ( $this->base->get_class( 'groups' )->is_idle( $group_id ) ) {
			return $all_caps;
		}

		// If here, the Group is generating content, and the capability requested needs to be temporarily disabled.
		$all_caps[ $cap[0] ] = false;

		// Return.
		return $all_caps;

	}

	/**
	 * Outputs the Keywords Dropdown before the Title field
	 *
	 * @since   2.0.2
	 *
	 * @param   WP_Post $post   Custom Post Type's Post.
	 */
	public function output_keywords_dropdown_before_title( $post ) {

		// Don't do anything if we're not on this Plugin's CPT.
		if ( get_post_type( $post ) !== $this->base->get_class( 'post_type' )->post_type_name ) {
			return;
		}

		// Get all available keywords.
		if ( ! $this->keywords ) {
			$this->keywords = $this->base->get_class( 'keywords' )->get_keywords_and_columns();
		}

		// Load view.
		include $this->base->plugin->folder . 'views/admin/generate-title-keywords.php';

	}

	/**
	 * Registers meta boxes for the Generate Custom Post Type
	 *
	 * @since   2.0.2
	 */
	public function add_meta_boxes() {

		// Remove some metaboxes that we don't need, to improve the UI.
		$this->remove_meta_boxes();

		// Determine whether we're using the Gutenberg Editor.
		// The use of $current_screen is in cases where is_gutenberg_page() sometimes wrongly returns false.
		global $current_screen;
		$is_gutenberg_page = ( function_exists( 'is_gutenberg_page' ) && is_gutenberg_page() ? true : false );
		if ( ! $is_gutenberg_page && method_exists( $current_screen, 'is_block_editor' ) ) {
			$is_gutenberg_page = $current_screen->is_block_editor();
		}

		// Description.
		add_meta_box(
			$this->base->get_class( 'post_type' )->post_type_name . '-description',
			__( 'Description', 'page-generator-pro' ),
			array( $this, 'output_meta_box_description' ),
			$this->base->get_class( 'post_type' )->post_type_name,
			'normal',
			'high'
		);

		// Permalink.
		add_meta_box(
			$this->base->get_class( 'post_type' )->post_type_name . '-permalink',
			__( 'Permalink', 'page-generator-pro' ),
			array( $this, 'output_meta_box_permalink' ),
			$this->base->get_class( 'post_type' )->post_type_name,
			'normal'
		);

		// Excerpt.
		add_meta_box(
			$this->base->get_class( 'post_type' )->post_type_name . '-excerpt',
			__( 'Excerpt', 'page-generator-pro' ),
			array( $this, 'output_meta_box_excerpt' ),
			$this->base->get_class( 'post_type' )->post_type_name,
			'normal'
		);

		// Featured Image.
		add_meta_box(
			$this->base->get_class( 'post_type' )->post_type_name . '-featured-image',
			__( 'Featured Image', 'page-generator-pro' ),
			array( $this, 'output_meta_box_featured_image' ),
			$this->base->get_class( 'post_type' )->post_type_name,
			'normal'
		);

		// Geo.
		add_meta_box(
			$this->base->get_class( 'post_type' )->post_type_name . '-geo',
			__( 'Geolocation Data', 'page-generator-pro' ),
			array( $this, 'output_meta_box_geo' ),
			$this->base->get_class( 'post_type' )->post_type_name,
			'normal'
		);

		// Custom Fields.
		add_meta_box(
			$this->base->get_class( 'post_type' )->post_type_name . '-custom-fields',
			__( 'Custom Fields', 'page-generator-pro' ),
			array( $this, 'output_meta_box_custom_fields' ),
			$this->base->get_class( 'post_type' )->post_type_name,
			'normal'
		);

		// Header and Footer.
		add_meta_box(
			$this->base->get_class( 'post_type' )->post_type_name . '-header-footer',
			__( 'Header & Footer Code', 'page-generator-pro' ),
			array( $this, 'output_meta_box_header_footer' ),
			$this->base->get_class( 'post_type' )->post_type_name,
			'normal'
		);

		// Author.
		add_meta_box(
			$this->base->get_class( 'post_type' )->post_type_name . '-author',
			__( 'Author', 'page-generator-pro' ),
			array( $this, 'output_meta_box_author' ),
			$this->base->get_class( 'post_type' )->post_type_name,
			'normal'
		);

		// Discussion.
		add_meta_box(
			$this->base->get_class( 'post_type' )->post_type_name . '-discussion',
			__( 'Discussion', 'page-generator-pro' ),
			array( $this, 'output_meta_box_discussion' ),
			$this->base->get_class( 'post_type' )->post_type_name,
			'normal'
		);

		// Upgrade.
		if ( class_exists( 'Page_Generator' ) ) {
			add_meta_box(
				$this->base->get_class( 'post_type' )->post_type_name . '-upgrade',
				__( 'Upgrade', 'page-generator-pro' ),
				array( $this, 'output_meta_box_upgrade' ),
				$this->base->get_class( 'post_type' )->post_type_name,
				'normal'
			);
		}

		/**
		 * Sidebar
		 */

		// Actions Top.
		if ( ! $is_gutenberg_page ) {
			add_meta_box(
				$this->base->get_class( 'post_type' )->post_type_name . '-actions',
				__( 'Actions', 'page-generator-pro' ),
				array( $this, 'output_meta_box_actions_top' ),
				$this->base->get_class( 'post_type' )->post_type_name,
				'side',
				'high'
			);
		}

		// Publish.
		add_meta_box(
			$this->base->get_class( 'post_type' )->post_type_name . '-publish',
			__( 'Publish', 'page-generator-pro' ),
			array( $this, 'output_meta_box_publish' ),
			$this->base->get_class( 'post_type' )->post_type_name,
			'side'
		);

		// Generation.
		add_meta_box(
			$this->base->get_class( 'post_type' )->post_type_name . '-generation',
			__( 'Generation', 'page-generator-pro' ),
			array( $this, 'output_meta_box_generation' ),
			$this->base->get_class( 'post_type' )->post_type_name,
			'side'
		);

		// Menu.
		add_meta_box(
			$this->base->get_class( 'post_type' )->post_type_name . '-menu',
			__( 'Menu', 'page-generator-pro' ),
			array( $this, 'output_meta_box_menu' ),
			$this->base->get_class( 'post_type' )->post_type_name,
			'side'
		);

		// Attributes.
		add_meta_box(
			$this->base->get_class( 'post_type' )->post_type_name . '-attributes',
			__( 'Attributes', 'page-generator-pro' ),
			array( $this, 'output_meta_box_attributes' ),
			$this->base->get_class( 'post_type' )->post_type_name,
			'side'
		);

		// Template.
		add_meta_box(
			$this->base->get_class( 'post_type' )->post_type_name . '-template',
			__( 'Template', 'page-generator-pro' ),
			array( $this, 'output_meta_box_template' ),
			$this->base->get_class( 'post_type' )->post_type_name,
			'side'
		);

		// Taxonomies.
		add_meta_box(
			$this->base->get_class( 'post_type' )->post_type_name . '-taxonomies',
			__( 'Taxonomies', 'page-generator-pro' ),
			array( $this, 'output_meta_box_taxonomies' ),
			$this->base->get_class( 'post_type' )->post_type_name,
			'side'
		);

		// Actions Bottom.
		if ( ! $is_gutenberg_page ) {
			add_meta_box(
				$this->base->get_class( 'post_type' )->post_type_name . '-actions-bottom',
				__( 'Actions', 'page-generator-pro' ),
				array( $this, 'output_meta_box_actions_bottom' ),
				$this->base->get_class( 'post_type' )->post_type_name,
				'side',
				'low'
			);
		} else {
			add_meta_box(
				$this->base->get_class( 'post_type' )->post_type_name . '-actions-gutenberg-bottom',
				__( 'Actions', 'page-generator-pro' ),
				array( $this, 'output_meta_box_actions_gutenberg' ),
				$this->base->get_class( 'post_type' )->post_type_name,
				'side'
			);
		}

		/**
		 * Action hook after all meta boxes are added for the Content Group UI
		 *
		 * @since   1.0.0
		 *
		 * @param   Page_Generator_Pro_PostType     $post_type_instance     Post Type Instance.
		 * @param   bool                            $is_gutenberg_page      If Gutenberg Editor is used on this Content Group.
		 */
		do_action( 'page_generator_pro_groups_ui_add_meta_boxes', $this->base->get_class( 'post_type' ), $is_gutenberg_page );

	}

	/**
	 * Removes some metaboxes on the Groups Custom Post Type UI
	 *
	 * @since   2.1.1
	 *
	 * @global  array   $wp_meta_boxes  Array of registered metaboxes.
	 */
	public function remove_meta_boxes() {

		global $wp_meta_boxes;

		// Bail if no meta boxes for this CPT exist.
		if ( ! isset( $wp_meta_boxes['page-generator-pro'] ) ) {
			return;
		}

		// Define the metaboxes to remove.
		$remove_meta_boxes = array(
			// Main.
			'slugdiv',

			// Sidebar.
			'submitdiv',
			'tagsdiv-page-generator-tax',

			// Divi.
			'pageparentdiv',
			'postcustom',
		);

		/**
		 * Filters the metaboxes to remove from the Content Groups Screen.
		 *
		 * @since   2.1.1
		 *
		 * @param   array   $remove_meta_boxes   Meta Boxes to Remove.
		 */
		$remove_meta_boxes = apply_filters( 'page_generator_pro_groups_ui_remove_meta_boxes', $remove_meta_boxes );

		// Bail if no meta boxes are defined for removal.
		if ( ! is_array( $remove_meta_boxes ) ) {
			return;
		}
		if ( count( $remove_meta_boxes ) === 0 ) {
			return;
		}

		// Iterate through all registered meta boxes, removing those that aren't permitted.
		foreach ( $wp_meta_boxes['page-generator-pro'] as $position => $contexts ) {
			foreach ( $contexts as $context => $meta_boxes ) {
				foreach ( $meta_boxes as $meta_box_id => $meta_box ) {
					// If this meta box is in the array of meta boxes to remove, remove it now.
					if ( in_array( $meta_box_id, $remove_meta_boxes, true ) ) {
						unset( $wp_meta_boxes['page-generator-pro'][ $position ][ $context ][ $meta_box_id ] );
					}
				}
			}
		}

	}

	/**
	 * Outputs the Description Meta Box
	 *
	 * @since   2.0.2
	 *
	 * @param   WP_Post $post   Custom Post Type's Post.
	 */
	public function output_meta_box_description( $post ) {

		// Get settings.
		if ( count( $this->settings ) === 0 ) {
			$this->settings = $this->base->get_class( 'groups' )->get_settings( $post->ID );
		}

		// Load view.
		include $this->base->plugin->folder . 'views/admin/generate-meta-box-description.php';

	}

	/**
	 * Outputs the Permalink Meta Box
	 *
	 * @since   2.0.2
	 *
	 * @param   WP_Post $post   Custom Post Type's Post.
	 */
	public function output_meta_box_permalink( $post ) {

		// Get settings.
		if ( count( $this->settings ) === 0 ) {
			$this->settings = $this->base->get_class( 'groups' )->get_settings( $post->ID );
		}

		// Get all available keywords, post types, taxonomies, authors and other settings that we might use on the admin screen.
		if ( ! $this->keywords ) {
			$this->keywords = $this->base->get_class( 'keywords' )->get_keywords_and_columns();
		}

		// Load view.
		include $this->base->plugin->folder . 'views/admin/generate-meta-box-permalink.php';

	}

	/**
	 * Outputs the Excerpt Meta Box
	 *
	 * @since   2.0.2
	 *
	 * @param   WP_Post $post   Custom Post Type's Post.
	 */
	public function output_meta_box_excerpt( $post ) {

		// Get settings.
		if ( count( $this->settings ) === 0 ) {
			$this->settings = $this->base->get_class( 'groups' )->get_settings( $post->ID );
		}

		// Load view.
		include $this->base->plugin->folder . 'views/admin/generate-meta-box-excerpt.php';

	}

	/**
	 * Outputs the Geolocation Data Meta Box
	 *
	 * @since   2.3.6
	 *
	 * @param   WP_Post $post   Custom Post Type's Post.
	 */
	public function output_meta_box_geo( $post ) {

		// Get settings.
		if ( count( $this->settings ) === 0 ) {
			$this->settings = $this->base->get_class( 'groups' )->get_settings( $post->ID );
		}

		// Load view.
		include $this->base->plugin->folder . 'views/admin/generate-meta-box-geo.php';

	}

	/**
	 * Outputs the Custom Fields Meta Box
	 *
	 * @since   2.0.2
	 *
	 * @param   WP_Post $post   Custom Post Type's Post.
	 */
	public function output_meta_box_custom_fields( $post ) {

		// Get settings.
		if ( count( $this->settings ) === 0 ) {
			$this->settings = $this->base->get_class( 'groups' )->get_settings( $post->ID );
		}

		// Get shortcodes.
		$shortcodes = $this->base->get_class( 'shortcode' )->get_shortcodes();

		// Remove research.
		unset( $shortcodes['research'] );

		// Sort alphabetically.
		ksort( $shortcodes );

		// Load view.
		include $this->base->plugin->folder . 'views/admin/generate-meta-box-custom-fields.php';

	}

	/**
	 * Outputs the Header & Footer Code Meta Box
	 *
	 * @since   4.3.2
	 *
	 * @param   WP_Post $post   Custom Post Type's Post.
	 */
	public function output_meta_box_header_footer( $post ) {

		// Get settings.
		if ( count( $this->settings ) === 0 ) {
			$this->settings = $this->base->get_class( 'groups' )->get_settings( $post->ID );
		}

		// Load view.
		include $this->base->plugin->folder . 'views/admin/generate-meta-box-header-footer.php';

	}

	/**
	 * Outputs the Author Meta Box
	 *
	 * @since   2.0.2
	 *
	 * @param   WP_Post $post   Custom Post Type's Post.
	 */
	public function output_meta_box_author( $post ) {

		// Get settings.
		if ( count( $this->settings ) === 0 ) {
			$this->settings = $this->base->get_class( 'groups' )->get_settings( $post->ID );
		}

		// If an author is selected, fetch their details now for the select dropdown.
		if ( ! empty( $this->settings['author'] ) ) {
			$author = get_user_by( 'ID', $this->settings['author'] );
		}

		// Load view.
		include $this->base->plugin->folder . 'views/admin/generate-meta-box-author.php';

	}

	/**
	 * Outputs the Discussion Meta Box
	 *
	 * @since   2.0.2
	 *
	 * @param   WP_Post $post   Custom Post Type's Post.
	 */
	public function output_meta_box_discussion( $post ) {

		// Get settings.
		if ( count( $this->settings ) === 0 ) {
			$this->settings = $this->base->get_class( 'groups' )->get_settings( $post->ID );
		}

		// Get date options.
		$date_options = $this->base->get_class( 'common' )->get_date_options();

		// Get shortcodes.
		$shortcodes = $this->base->get_class( 'shortcode' )->get_shortcodes_by_keyword( __( 'AI', 'page-generator-pro' ) );

		// Sort alphabetically.
		ksort( $shortcodes );

		// Load view.
		include $this->base->plugin->folder . 'views/admin/generate-meta-box-discussion.php';

	}

	/**
	 * Outputs the Upgrade Meta Box
	 *
	 * @since   2.0.2
	 *
	 * @param   WP_Post $post   Custom Post Type's Post.
	 */
	public function output_meta_box_upgrade( $post ) {

		// Get settings.
		if ( count( $this->settings ) === 0 ) {
			$this->settings = $this->base->get_class( 'groups' )->get_settings( $post->ID );
		}

		// Load view.
		include $this->base->plugin->folder . '/_modules/dashboard/views/footer-upgrade-embedded.php';

	}

	/**
	 * Outputs the Actions Sidebar Top Meta Box
	 *
	 * @since   2.0.2
	 *
	 * @param   WP_Post $post   Custom Post Type's Post.
	 */
	public function output_meta_box_actions_top( $post ) {

		// Define Group ID.
		$group_id = $post->ID;

		// Get settings.
		if ( count( $this->settings ) === 0 ) {
			$this->settings = $this->base->get_class( 'groups' )->get_settings( $post->ID );
		}

		// Get limit.
		$limit = $this->base->get_class( 'ajax' )->get_trash_delete_per_request_item_limit();

		// Append to element IDs.
		$bottom = '';

		// Load view.
		include $this->base->plugin->folder . 'views/admin/generate-meta-box-actions.php';

	}

	/**
	 * Outputs the Actions Sidebar Bottom Meta Box
	 *
	 * @since   2.0.2
	 *
	 * @param   WP_Post $post   Custom Post Type's Post.
	 */
	public function output_meta_box_actions_bottom( $post ) {

		// Define Group ID.
		$group_id = $post->ID;

		// Get settings.
		if ( count( $this->settings ) === 0 ) {
			$this->settings = $this->base->get_class( 'groups' )->get_settings( $post->ID );
		}

		// Get limit.
		$limit = $this->base->get_class( 'ajax' )->get_trash_delete_per_request_item_limit();

		// Append to element IDs.
		$bottom = 'bottom';

		// Load view.
		include $this->base->plugin->folder . 'views/admin/generate-meta-box-actions.php';

	}

	/**
	 * Outputs the Actions Sidebar Meta Box for Gutenberg
	 *
	 * @since   2.0.2
	 *
	 * @param   WP_Post $post   Custom Post Type's Post.
	 */
	public function output_meta_box_actions_gutenberg( $post ) {

		// Define Group ID.
		$group_id = $post->ID;

		// Get settings.
		if ( count( $this->settings ) === 0 ) {
			$this->settings = $this->base->get_class( 'groups' )->get_settings( $post->ID );
		}

		// Get limit.
		$limit = $this->base->get_class( 'ajax' )->get_trash_delete_per_request_item_limit();

		// Load view.
		include $this->base->plugin->folder . 'views/admin/generate-meta-box-actions-gutenberg.php';

	}

	/**
	 * Outputs the Publish Sidebar Meta Box
	 *
	 * @since   2.0.2
	 *
	 * @param   WP_Post $post   Custom Post Type's Post.
	 */
	public function output_meta_box_publish( $post ) {

		// Get settings.
		if ( count( $this->settings ) === 0 ) {
			$this->settings = $this->base->get_class( 'groups' )->get_settings( $post->ID );
		}

		// Get options.
		$post_types     = $this->base->get_class( 'common' )->get_post_types();
		$statuses       = $this->base->get_class( 'common' )->get_post_statuses();
		$date_options   = $this->base->get_class( 'common' )->get_date_options();
		$schedule_units = $this->base->get_class( 'common' )->get_schedule_units();

		// Load view.
		include $this->base->plugin->folder . 'views/admin/generate-meta-box-publish.php';

	}

	/**
	 * Outputs the Generation Sidebar Meta Box
	 *
	 * @since   2.0.2
	 *
	 * @param   WP_Post $post   Custom Post Type's Post.
	 */
	public function output_meta_box_generation( $post ) {

		// Get settings.
		if ( count( $this->settings ) === 0 ) {
			$this->settings = $this->base->get_class( 'groups' )->get_settings( $post->ID );
		}

		// Get options.
		$methods            = $this->base->get_class( 'common' )->get_methods();
		$overwrite_methods  = $this->base->get_class( 'common' )->get_overwrite_methods();
		$overwrite_sections = $this->base->get_class( 'common' )->get_content_overwrite_sections();

		// Define labels.
		$labels = array(
			'singular' => __( 'Page', 'page-generator-pro' ),
			'plural'   => __( 'Pages', 'page-generator-pro' ),
		);

		// Define documentation URL, as the include is used for both Content and Term Groups, each of which
		// have different docs.
		$overwrite_documentation_url = $this->base->plugin->documentation_url . '/generate-content/#fields--generation--overwrite';

		// Load view.
		include $this->base->plugin->folder . 'views/admin/generate-meta-box-generation.php';

	}

	/**
	 * Outputs the Menu Sidebar Meta Box
	 *
	 * @since   2.7.1
	 *
	 * @param   WP_Post $post   Custom Post Type's Post.
	 */
	public function output_meta_box_menu( $post ) {

		// Get settings.
		if ( count( $this->settings ) === 0 ) {
			$this->settings = $this->base->get_class( 'groups' )->get_settings( $post->ID );
		}

		// Get options.
		$menus = wp_get_nav_menus();

		// Load view.
		include $this->base->plugin->folder . 'views/admin/generate-meta-box-menu.php';

	}

	/**
	 * Outputs the Attributes Sidebar Meta Box
	 *
	 * @since   2.0.2
	 *
	 * @param   WP_Post $post   Custom Post Type's Post.
	 */
	public function output_meta_box_attributes( $post ) {

		// Get settings.
		if ( count( $this->settings ) === 0 ) {
			$this->settings = $this->base->get_class( 'groups' )->get_settings( $post->ID );
		}

		// Get options.
		$hierarchical_post_types = $this->base->get_class( 'common' )->get_hierarchical_post_types();
		$post_types_templates    = $this->base->get_class( 'common' )->get_post_types_templates();
		$parent_group_id         = wp_get_post_parent_id( $post->ID );

		// Build Content Groups Dropdown.
		$parent_groups_dropdown = wp_dropdown_pages(
			array(
				'post_type'        => esc_attr( $this->base->get_class( 'post_type' )->post_type_name ),
				'exclude_tree'     => esc_attr( (string) $post->ID ),
				'selected'         => esc_attr( (string) $post->post_parent ),
				'name'             => 'parent_id',
				'show_option_none' => esc_html__( '(no parent)', 'page-generator-pro' ),
				'sort_column'      => 'menu_order, post_title',
				'echo'             => 0,
				'class'            => 'widefat',
			)
		);

		// Load view.
		include $this->base->plugin->folder . 'views/admin/generate-meta-box-attributes.php';

	}

	/**
	 * Outputs the Template Sidebar Meta Box
	 *
	 * @since   3.3.9
	 *
	 * @param   WP_Post $post   Custom Post Type's Post.
	 */
	public function output_meta_box_template( $post ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter

		// Get options.
		$post_types_templates = $this->base->get_class( 'common' )->get_post_types_templates();

		// Load view.
		include $this->base->plugin->folder . 'views/admin/generate-meta-box-template.php';

	}

	/**
	 * Outputs the Taxonomies Sidebar Meta Box
	 *
	 * @since   2.0.2
	 *
	 * @param   WP_Post $post   Custom Post Type's Post.
	 */
	public function output_meta_box_taxonomies( $post ) {

		// Get settings.
		if ( count( $this->settings ) === 0 ) {
			$this->settings = $this->base->get_class( 'groups' )->get_settings( $post->ID );
		}

		// Fetch some taxonomy information for our Taxonomy meta boxes.
		$post_types = $this->base->get_class( 'common' )->get_post_types();
		$taxonomies = $this->base->get_class( 'common' )->get_taxonomies();

		// Iterate through taxonomies, outputting options for each.
		foreach ( $taxonomies as $taxonomy ) {
			// Build list of Post Types this taxonomy is registered for use on.
			$post_types_string = '';
			foreach ( $taxonomy->object_type as $post_type ) {
				$post_types_string = $post_types_string . $post_type . ' ';
			}

			// Load view.
			include $this->base->plugin->folder . 'views/admin/generate-meta-box-taxonomies.php';
		}

	}

	/**
	 * Outputs the Featured Image Sidebar Meta Box
	 *
	 * @since   2.0.2
	 *
	 * @param   WP_Post $post   Custom Post Type's Post.
	 */
	public function output_meta_box_featured_image( $post ) {

		// Get settings.
		if ( count( $this->settings ) === 0 ) {
			$this->settings = $this->base->get_class( 'groups' )->get_settings( $post->ID );
		}

		$featured_image_sources = array(
			'' => __( 'No Feat. Image', 'page-generator-pro' ),
		);
		$featured_image_tabs    = array();
		$featured_image_fields  = array();

		/**
		 * Defines available Featured Image sources.
		 *
		 * @since   4.8.0
		 *
		 * @param   array   $featured_image_sources    Featured Image Sources.
		 * @param   array   $settings                  Group Settings.
		 */
		$featured_image_sources = apply_filters( 'page_generator_pro_common_get_featured_image_sources', $featured_image_sources, $this->settings );

		/**
		 * Defines available Featured Image tabs.
		 *
		 * @since   4.8.0
		 *
		 * @param   array   $featured_image_tabs    Featured Image Tabs.
		 * @param   array   $settings               Group Settings.
		 */
		$featured_image_tabs = apply_filters( 'page_generator_pro_common_get_featured_image_tabs', $featured_image_tabs, $this->settings );

		/**
		 * Defines available Featured Image fields.
		 *
		 * @since   4.8.0
		 *
		 * @param   array   $featured_image_fields    Featured Image Sources.
		 * @param   array   $settings                 Group Settings.
		 */
		$featured_image_fields = apply_filters( 'page_generator_pro_common_get_featured_image_fields', $featured_image_fields, $this->settings );

		// Load view.
		include $this->base->plugin->folder . 'views/admin/generate-meta-box-featured-image.php';

		/**
		 * Output Featured Image options for additional sources on a Content Group
		 *
		 * @since   2.9.3
		 *
		 * @param   WP_Post     $post       WordPress Post.
		 * @param   array       $settings   Content Group Settings.
		 */
		do_action( 'page_generator_pro_groups_ui_output_meta_box_featured_image', $post, $this->settings );

	}

	/**
	 * Called when a Group is saved.
	 *
	 * @since   2.0.2
	 *
	 * @param   int $post_id    Post ID.
	 */
	public function save_post( $post_id ) {

		// Bail if this isn't a Page Generator Pro Group that's being saved.
		if ( get_post_type( $post_id ) !== $this->base->get_class( 'post_type' )->post_type_name ) {
			return;
		}

		// Run security checks.
		// Missing nonce .
		if ( ! isset( $_POST[ $this->base->plugin->name . '_nonce' ] ) ) {
			return;
		}

		// Invalid nonce.
		if ( ! wp_verify_nonce( sanitize_key( $_POST[ $this->base->plugin->name . '_nonce' ] ), 'save_generate' ) ) {
			return;
		}

		// Bail if no settings are defined.
		if ( ! isset( $_POST[ $this->base->plugin->name ] ) ) {
			return;
		}

		// Save the Group's Settings.
		$result = $this->base->get_class( 'groups' )->save( $_POST[ $this->base->plugin->name ], $post_id ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput

		// Get action.
		$action = $this->get_action();

		// If an error occured, show it.
		if ( is_wp_error( $result ) ) {
			$this->base->get_class( 'notices' )->enable_store();
			$this->base->get_class( 'notices' )->set_key_prefix( 'page_generator_pro_' . wp_get_current_user()->ID );
			$this->base->get_class( 'notices' )->add_error_notice( $result->get_error_message() );

			// If this action isn't Trash or Delete, stop.
			if ( ! in_array(
				$action,
				array(
					'trash_generated_content',
					'delete_generated_content',
				),
				true
			) ) {
				return;
			}
		}

		// Maybe run an action on the Group now.
		$redirect = ( $action === 'generate_server' ? true : false );
		$this->base->get_class( 'groups' )->run_action( $action, $post_id, $redirect );

	}

	/**
	 * Returns the localized title
	 *
	 * @since   2.0.2
	 *
	 * @param   string $key    Key.
	 * @return  string          Message
	 */
	public function get_title( $key ) {

		// Get Titles and Messages.
		$titles_messages = $this->get_titles_and_messages();

		// Bail if no Titles exist.
		if ( ! isset( $titles_messages['titles'] ) ) {
			return '';
		}

		// Bail if the Title does not exist.
		if ( ! isset( $titles_messages['titles'][ $key ] ) ) {
			return '';
		}

		// Return the title.
		return $titles_messages['titles'][ $key ];

	}

	/**
	 * Returns the localized message
	 *
	 * @since   2.0.2
	 *
	 * @param   string $key    Key.
	 * @return  string          Message
	 */
	public function get_message( $key ) {

		// Get Titles and Messages.
		$titles_messages = $this->get_titles_and_messages();

		// Bail if no Messages exist.
		if ( ! isset( $titles_messages['messages'] ) ) {
			return '';
		}

		// Bail if the Message does not exist.
		if ( ! isset( $titles_messages['messages'][ $key ] ) ) {
			return '';
		}

		// Return the message.
		return $titles_messages['messages'][ $key ];

	}

	/**
	 * Returns Titles and Messages that are used for Content Generation,
	 * which are displayed in various notifications.
	 *
	 * @since   2.0.2
	 *
	 * @return  array   Titles and Messages
	 */
	public function get_titles_and_messages() {

		// Define localizations.
		$localization = array(
			'titles'   => array(
				'duplicate'                => __( 'Duplicate', 'page-generator-pro' ),
				'import'                   => __( 'Import', 'page-generator-pro' ),
				'test'                     => __( 'Test', 'page-generator-pro' ),
				'generate'                 => __( 'Generate via Browser', 'page-generator-pro' ),
				'generate_server'          => __( 'Generate via Server', 'page-generator-pro' ),
				'trash_generated_content'  => __( 'Trash Generated Content', 'page-generator-pro' ),
				'delete_generated_content' => __( 'Delete Generated Content', 'page-generator-pro' ),
				'cancel_generation'        => __( 'Cancel Generation', 'page-generator-pro' ),
			),

			'messages' => array(
				// Generate.
				'generate_confirm'                 => __( 'This will generate all Pages/Posts. Proceed?', 'page-generator-pro' ),

				// Generate via Server.
				'generate_server_confirm'          => __( 'This will generate all Content using WordPress\' CRON to offload the process to the server. Proceed?', 'page-generator-pro' ),
				'generate_server_success'          => __( 'Queued for Generation via WordPress\' CRON. This may take a minute or two to begin.', 'page-generator-pro' ),

				// Cancel Generation.
				'cancel_generation_confirm'        => __( 'This will cancel Content Generation, allowing the Group to be edited.  Proceed?', 'page-generator-pro' ),
				'cancel_generation_success'        => __( 'Generation Cancelled', 'page-generator-pro' ),

				// Duplicate.
				'duplicate_success'                => __( 'Group Duplicated', 'page-generator-pro' ),

				// Import.
				'import_success'                   => __( 'Import to a new Content Group successful.', 'page-generator-pro' ),

				// Test.
				'test_confirm'                     => __( 'This will generate a single Page/Post in draft mode. Proceed?', 'page-generator-pro' ),
				'test'                             => __( 'Generating Test in Draft Mode...', 'page-generator-pro' ),
				/* Translators: URL to Term */
				'test_success'                     => __( 'Test Page/Post Generated at %s', 'page-generator-pro' ),

				// Trash Generated Content.
				/* translators: Number of Pages/Posts to trash */
				'trash_generated_content_confirm'  => __( 'This will trash ALL %s content items generated by this group. Proceed?', 'page-generator-pro' ),
				'trash_generated_content'          => __( 'Trashing Generated Content Items', 'page-generator-pro' ),
				'trash_generated_content_success'  => __( 'Generated Content Trashed', 'page-generator-pro' ),
				'trash_generated_content_error'    => __( 'An error occured. Please try again.', 'page-generator-pro' ),

				// Delete Generated Content.
				/* translators: Number of Pages/Post to delete */
				'delete_generated_content_confirm' => __( 'This will PERMANENTLY DELETE ALL %s content items generated by this group. This action cannot be undone. Proceed?', 'page-generator-pro' ),
				'delete_generated_content'         => __( 'Deleting Generated Content Items', 'page-generator-pro' ),
				'delete_generated_content_success' => __( 'Generated Content Deleted', 'page-generator-pro' ),
			),
		);

		/**
		 * Filters the localization title and message strings used for Generation.
		 *
		 * @since   2.0.2
		 *
		 * @param   array   $localization   Titles and Messages.
		 */
		$localization = apply_filters( 'page_generator_pro_groups_ui_get_titles_and_messages', $localization );

		// Return.
		return $localization;

	}

	/**
	 * Returns Metaboxes that should only display when Publish > Post Type matches the Post Type
	 * the Metabox(es) are for.
	 *
	 * @since   2.8.6
	 *
	 * @return  array   Post Type Conditional Metaboxes
	 */
	public function get_post_type_conditional_metaboxes() {

		// Define conditional metaboxes which will conditionally display based on the Post Types supporting each metabox.
		$conditional_metaboxes = array(
			'page-generator-pro-excerpt'        => $this->base->get_class( 'common' )->get_post_types_supporting( 'excerpt' ),
			'page-generator-pro-featured-image' => $this->base->get_class( 'common' )->get_post_types_supporting( 'thumbnail' ),
			'page-generator-pro-discussion'     => $this->base->get_class( 'common' )->get_post_types_supporting( 'comments' ),
			'page-generator-pro-attributes'     => $this->base->get_class( 'common' )->get_post_types_supporting( 'hierarchical' ),
			'page-generator-pro-template'       => $this->base->get_class( 'common' )->get_post_types_supporting( 'templates' ),
			'page-generator-pro-taxonomies'     => $this->base->get_class( 'common' )->get_post_types_supporting( 'taxonomies' ),
		);

		/**
		 * Define conditional metaboxes that should only display based on the value of Publish > Post Type
		 * in the Content Groups UI.  For example,
		 * array( 'metabox_id' => array( 'post_type_to_display_metabox_on', 'another_post_type_to_display_metabox_on' ) )
		 *
		 * @since   2.8.6
		 *
		 * @param   array   $conditional_metaboxes  Metabox ID Keys and Post Type Values array.
		 */
		$conditional_metaboxes = apply_filters( 'page_generator_pro_groups_ui_get_post_type_conditional_metaboxes', $conditional_metaboxes );

		// Return.
		return $conditional_metaboxes;

	}

	/**
	 * Determines which submit button was pressed on the Groups add/edit screen
	 *
	 * @since   2.0.2
	 *
	 * @return  bool|string  Action
	 */
	private function get_action() {

		if ( isset( $_POST['test'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			return 'test';
		}

		if ( isset( $_POST['generate'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			return 'generate';
		}

		if ( isset( $_POST['generate_server'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			return 'generate_server';
		}

		if ( isset( $_POST['trash_generated_content'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			return 'trash_generated_content';
		}

		if ( isset( $_POST['delete_generated_content'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			return 'delete_generated_content';
		}

		if ( isset( $_POST['cancel_generation'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			return 'cancel_generation';
		}

		if ( isset( $_POST['save'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			return 'save';
		}

		// No action given.
		return false;

	}

	/**
	 * Limit creating more than one Group via the WordPress Administration, by preventing
	 * the 'Add New' functionality, and ensuring the user is always taken to the edit
	 * screen of the single Group when they access the Post Type.
	 *
	 * @since   1.3.8
	 */
	public function limit_admin() {

		global $pagenow;

		switch ( $pagenow ) {
			/**
			 * Edit
			 * WP_List_Table
			 */
			case 'edit.php':
				// Bail if no Post Type is supplied.
				if ( ! isset( $_REQUEST['post_type'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
					break;
				}

				// Bail if we're not on our Group Post Type.
				if ( $_REQUEST['post_type'] !== 'page-generator-pro' ) { // phpcs:ignore WordPress.Security.NonceVerification
					break;
				}

				// Fetch first group.
				$groups = new WP_Query(
					array(
						'post_type'      => 'page-generator-pro',
						'post_status'    => 'publish',
						'posts_per_page' => 1,
					)
				);

				// Bail if no Groups exist, so the user can create one.
				if ( count( $groups->posts ) === 0 ) {
					break;
				}

				// Redirect to the Group's edit screen.
				wp_safe_redirect( 'post.php?post=' . $groups->posts[0]->ID . '&action=edit' );
				die;

			/**
			 * Add New
			 */
			case 'post-new.php':
			case 'press-this.php':
				// Bail if we don't know the Post Type.
				if ( ! isset( $_REQUEST['post_type'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
					break;
				}

				// Bail if we're not on our Group Post Type.
				if ( $_REQUEST['post_type'] !== 'page-generator-pro' ) { // phpcs:ignore WordPress.Security.NonceVerification
					break;
				}

				// Fetch first group.
				$groups = new WP_Query(
					array(
						'post_type'      => 'page-generator-pro',
						'post_status'    => 'publish',
						'posts_per_page' => 1,
					)
				);

				// Bail if no Groups exist, so the user can create one.
				if ( count( $groups->posts ) === 0 ) {
					break;
				}

				// Redirect to the Group's edit screen.
				wp_safe_redirect( 'post.php?post=' . $groups->posts[0]->ID . '&action=edit' );
				die;
		}

	}

	/**
	 * Limit creating more than one Group via XML-RPC
	 *
	 * @since   1.3.8
	 *
	 * @param   bool  $limit  Limit XML-RPC.
	 * @param   array $post   Post Data.
	 * @return  bool
	 */
	public function limit_xml_rpc( $limit, $post = array() ) {

		// Bail if we're not on an XMLRPC request.
		if ( ! defined( 'XMLRPC_REQUEST' ) || XMLRPC_REQUEST !== true ) {
			return $limit;
		}

		// Bail if no Post Type specified.
		if ( ! isset( $post['post_type'] ) ) {
			return $limit;
		}
		if ( $post['post_type'] !== 'page-generator-pro' ) {
			return $limit;
		}

		// If here, we're trying to create a Group. Don't let this happen.
		return true;

	}

}
