<?php
/**
 * Content Groups Table Class
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Handles Content Groups WP_List_Table actions.
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 2.0.2
 */
class Page_Generator_Pro_Groups_Table {

	/**
	 * Holds the base class object.
	 *
	 * @since   2.0.2
	 *
	 * @var     object
	 */
	public $base;

	/**
	 * Holds the Content Group's settings.
	 *
	 * @since   4.0.4
	 *
	 * @var     bool|array
	 */
	public $settings = false;

	/**
	 * Stores the current Group the settings are defined for.
	 *
	 * @since   2.0.2
	 *
	 * @var     int
	 */
	public $group_id = 0;

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

		// Bulk Actions Dropdown.
		add_filter( 'bulk_actions-edit-page-generator-pro', array( $this, 'register_bulk_actions' ) );
		add_filter( 'handle_bulk_actions-edit-page-generator-pro', array( $this, 'run_bulk_actions' ), 10, 3 );

		// WP_List_Table Columns.
		add_filter( 'manage_edit-page-generator-pro_columns', array( $this, 'admin_columns' ) );
		add_action( 'manage_page-generator-pro_posts_custom_column', array( $this, 'admin_columns_output' ), 10, 2 );

		// WP_List_Table Row Actions.
		add_filter( 'page_row_actions', array( $this, 'admin_row_actions' ), 10, 2 );

		// Run any row actions called from the WP_List_Table.
		add_action( 'init', array( $this, 'run_row_actions' ) );

	}

	/**
	 * Adds Bulk Action options to Group WP_List_Table
	 *
	 * @since   1.9.9
	 *
	 * @param   array $actions    Registered Bulk Actions.
	 * @return  array               Registered Bulk Actions
	 */
	public function register_bulk_actions( $actions ) {

		// Define Actions.
		$bulk_actions = array(
			'duplicate'                => $this->base->get_class( 'groups_ui' )->get_title( 'duplicate' ),
			'generate_server'          => $this->base->get_class( 'groups_ui' )->get_title( 'generate_server' ),
			'trash_generated_content'  => $this->base->get_class( 'groups_ui' )->get_title( 'trash_generated_content' ),
			'delete_generated_content' => $this->base->get_class( 'groups_ui' )->get_title( 'delete_generated_content' ),
		);

		// Remove some actions we don't want.
		unset( $actions['edit'] );

		/**
		 * Defines Bulk Actions to be added to the select dropdown on the Groups WP_List_Table.
		 *
		 * @since   1.9.9
		 *
		 * @param   array   $bulk_actions   Plugin Specific Bulk Actions.
		 * @param   array   $actions        Existing Registered Bulk Actions (excluding Plugin Specific Bulk Actions).
		 */
		$bulk_actions = apply_filters( 'page_generator_pro_groups_table_register_bulk_actions', $bulk_actions, $actions );

		// Merge with default Bulk Actions.
		$actions = array_merge( $bulk_actions, $actions );

		// Return.
		return $actions;

	}

	/**
	 * Handles Bulk Actions when one is selected to run
	 *
	 * @since   1.9.9
	 *
	 * @param   string $redirect_to    Redirect URL.
	 * @param   string $action         Bulk Action to Run.
	 * @param   array  $post_ids       Post IDs to apply Action on.
	 * @return  string                  Redirect URL
	 */
	public function run_bulk_actions( $redirect_to, $action, $post_ids ) {

		// Bail if the action isn't specified.
		if ( empty( $action ) ) {
			return $redirect_to;
		}

		// Bail if no Post IDs.
		if ( empty( $post_ids ) ) {
			return $redirect_to;
		}

		// Setup notices class, enabling persistent storage.
		$this->base->get_class( 'notices' )->enable_store();
		$this->base->get_class( 'notices' )->set_key_prefix( 'page_generator_pro_' . wp_get_current_user()->ID );

		// Run Bulk Action.
		switch ( $action ) {

			/**
			 * Duplicate
			 */
			case 'duplicate':
				foreach ( $post_ids as $post_id ) {
					// Duplicate.
					$result = $this->base->get_class( 'groups' )->duplicate( $post_id );

					// If an error occured, add it to the notices.
					if ( is_wp_error( $result ) ) {
						$this->base->get_class( 'notices' )->add_error_notice(
							sprintf(
								/* translators: %1$s: Group ID, %2$s: Return message */
								__( 'Group #%1$s: %2$s', 'page-generator-pro' ),
								$post_id,
								$result->get_error_message()
							)
						);
					} else {
						$this->base->get_class( 'notices' )->add_success_notice(
							sprintf(
								/* translators: %1$s: Group ID, %2$s: Return message */
								__( 'Group #%1$s: %2$s', 'page-generator-pro' ),
								$post_id,
								$this->base->get_class( 'groups_ui' )->get_message( 'duplicate_success' )
							)
						);
					}
				}
				break;

			/**
			 * Generate via Server
			 */
			case 'generate_server':
				foreach ( $post_ids as $post_id ) {
					// Schedule Generation.
					$result = $this->base->get_class( 'groups' )->schedule_generation( $post_id );

					// If an error occured, add it to the notices.
					if ( is_wp_error( $result ) ) {
						$this->base->get_class( 'notices' )->add_error_notice(
							sprintf(
								/* translators: %1$s: Group ID, %2$s: Return message */
								__( 'Group #%1$s: %2$s', 'page-generator-pro' ),
								$post_id,
								$result->get_error_message()
							)
						);
					} else {
						$this->base->get_class( 'notices' )->add_success_notice(
							sprintf(
								/* translators: %1$s: Group ID, %2$s: Return message */
								__( 'Group #%1$s: %2$s', 'page-generator-pro' ),
								$post_id,
								$this->base->get_class( 'groups_ui' )->get_message( 'generate_server_success' )
							)
						);
					}
				}
				break;

			/**
			 * Trash Generated Content
			 */
			case 'trash_generated_content':
				foreach ( $post_ids as $post_id ) {
					// Duplicate.
					$result = $this->base->get_class( 'groups' )->trash_generated_content( $post_id );

					// If an error occured, add it to the notices.
					if ( is_wp_error( $result ) ) {
						$this->base->get_class( 'notices' )->add_error_notice(
							sprintf(
								/* translators: %1$s: Group ID, %2$s: Return message */
								__( 'Group #%1$s: %2$s', 'page-generator-pro' ),
								$post_id,
								$result->get_error_message()
							)
						);
					} else {
						$this->base->get_class( 'notices' )->add_success_notice(
							sprintf(
								/* translators: %1$s: Group ID, %2$s: Return message */
								__( 'Group #%1$s: %2$s', 'page-generator-pro' ),
								$post_id,
								$this->base->get_class( 'groups_ui' )->get_message( 'trash_generated_content_success' )
							)
						);
					}
				}
				break;

			/**
			 * Delete Generated Content
			 */
			case 'delete_generated_content':
				foreach ( $post_ids as $post_id ) {
					// Duplicate.
					$result = $this->base->get_class( 'groups' )->delete_generated_content( $post_id );

					// If an error occured, add it to the notices.
					if ( is_wp_error( $result ) ) {
						$this->base->get_class( 'notices' )->add_error_notice(
							sprintf(
								/* translators: %1$s: Group ID, %2$s: Return message */
								__( 'Group #%1$s: %2$s', 'page-generator-pro' ),
								$post_id,
								$result->get_error_message()
							)
						);
					} else {
						$this->base->get_class( 'notices' )->add_success_notice(
							sprintf(
								/* translators: %1$s: Group ID, %2$s: Return message */
								__( 'Group #%1$s: %2$s', 'page-generator-pro' ),
								$post_id,
								$this->base->get_class( 'groups_ui' )->get_message( 'delete_generated_content_success' )
							)
						);
					}
				}
				break;

			/**
			 * Other Bulk Actions
			 */
			default:
				/**
				 * Runs the given Bulk Action against the given Content Group IDs.
				 *
				 * @since   1.9.9
				 *
				 * @param   string  $action     Bulk Action Run.
				 * @param   array   $post_ids   Group IDs.
				 */
				do_action( 'page_generator_pro_groups_table_run_bulk_actions', $action, $post_ids );
				break;

		}

		// Return redirect.
		return $redirect_to;

	}

	/**
	 * Adds columns to the Groups CPT within the WordPress Administration List Table
	 *
	 * @since   1.2.3
	 *
	 * @param   array $columns    Columns.
	 * @return  array               New Columns
	 */
	public function admin_columns( $columns ) {

		// Inject columns.
		$new_columns = array(
			'cb'              => '<input type="checkbox" />',
			'title'           => __( 'Title', 'page-generator-pro' ),
			'id'              => __( 'Group ID', 'page-generator-pro' ),
			'description'     => __( 'Description', 'page-generator-pro' ),
			'type'            => __( 'Content Type', 'page-generator-pro' ),
			'generated_count' => __( 'Generated Content', 'page-generator-pro' ),
			'status'          => __( 'Status', 'page-generator-pro' ),
			'date'            => __( 'Date', 'page-generator-pro' ),
		);

		/**
		 * Filters the columns to display on the Groups: Content WP_List_Table.
		 *
		 * @since   1.2.3
		 *
		 * @param   array   $new_columns    New Columns.
		 * @param   array   $columns        Columns.
		 */
		$new_columns = apply_filters( 'page_generator_pro_groups_table_admin_columns', $new_columns, $columns );

		return $new_columns;

	}


	/**
	 * Manages the data to be displayed within a column on the Groups CPT within
	 * the WordPress Administration List Table
	 *
	 * @since   1.2.3
	 *
	 * @param   string $column_name    Column Name.
	 * @param   int    $post_id        Group Post ID.
	 */
	public function admin_columns_output( $column_name, $post_id ) {

		// Array to hold the item(s) to output in this column for this Group.
		$items = array();

		// Get group settings, if we don't have them.
		if ( $post_id !== $this->group_id ) {
			$this->group_id = $post_id;
			$this->settings = $this->base->get_class( 'groups' )->get_settings( $this->group_id );
		}

		// Get limit.
		$limit = $this->base->get_class( 'ajax' )->get_trash_delete_per_request_item_limit();

		// Define items array.
		switch ( $column_name ) {
			/**
			 * ID
			 */
			case 'id':
				$items = array(
					'group_id' => $this->group_id,
				);
				break;

			/**
			 * Description
			 */
			case 'description':
				$items = array(
					'description' => nl2br( $this->settings['description'] ),
				);
				break;

			/**
			 * Type
			 */
			case 'type':
				$items = array(
					'type' => $this->settings['type'],
				);
				break;

			/**
			 * Number of Generated Pages
			 */
			case 'generated_count':
				if ( $this->base->get_class( 'groups' )->generates_content( $post_id ) ) {
					if ( $this->settings['generated_pages_count'] ) {
						$items = array(
							/* translators: Number of generated items, wrapped in HTML */
							'generated_count'          => sprintf( __( 'Generated Items: %s', 'page-generator-pro' ), '<span class="count" data-group-id="' . $post_id . '">' . $this->settings['generated_pages_count'] . '</span>' ),

							/* translators: Last generated index number, wrapped in HTML */
							'last_index_generated'     => sprintf( __( 'Last Index Generated: %s', 'page-generator-pro' ), '<span class="count" data-group-id="' . $post_id . '">' . $this->settings['last_index_generated'] . '</span>' ),
							'spacer'                   => '&nbsp;',
							'view'                     => '<a href="' . $this->get_action_url( $post_id, 'view', $this->settings['type'] ) . '" title="' . __( 'Click to view Generated Content', 'page-generator-pro' ) . ' " data-group-id="' . $post_id . '">' .
								__( 'View Generated Content', 'page-generator-pro' ) .
							'</a>',
							'trash_generated_content'  => '<a href="' . $this->get_action_url( $post_id, 'trash_generated_content' ) . '"  data-group-id="' . $post_id . '" data-limit="' . $limit . '" data-total="' . $this->settings['generated_pages_count'] . '">' .
								__( 'Trash Generated Content', 'page-generator-pro' ) .
							'</a>',
							'delete_generated_content' => '<a href="' . $this->get_action_url( $post_id, 'delete_generated_content' ) . '"  data-group-id="' . $post_id . '" data-limit="' . $limit . '" data-total="' . $this->settings['generated_pages_count'] . '">' .
								__( 'Delete Generated Content', 'page-generator-pro' ) .
							'</a>',
						);
					} else {
						$items = array(
							/* translators: Number of generated items, wrapped in HTML */
							'generated_count'      => sprintf( __( 'Generated: %s', 'page-generator-pro' ), '<span class="count" data-group-id="' . $post_id . '">0</span>' ),

							/* translators: Last generated index number, wrapped in HTML */
							'last_index_generated' => sprintf( __( 'Last Index Generated: %s', 'page-generator-pro' ), '<span class="count" data-group-id="' . $post_id . '">' . $this->settings['last_index_generated'] . '</span>' ),
						);
					}
				}
				break;

			/**
			 * Status
			 */
			case 'status':
				if ( $this->base->get_class( 'groups' )->generates_content( $post_id ) ) {
					if ( $this->base->get_class( 'groups' )->is_idle( $post_id ) ) {
						$items = $this->base->get_class( 'groups' )->get_actions_links( $post_id, $this->settings['resumeIndex'] );
					} else {
						$items = array(
							'status'            => '<div class="page-generator-pro-generating-spinner">
	                                                    <span class="spinner"></span>' .
														ucfirst( $this->base->get_class( 'groups' )->get_status( $post_id ) ) .
														'(' . $this->base->get_class( 'groups' )->get_system( $post_id ) . ')
	                                                </div>',
							'cancel_generation' => '<a href="' . $this->get_action_url( $post_id, 'cancel_generation' ) . '">' . $this->base->get_class( 'groups_ui' )->get_title( 'cancel_generation' ) . '</a>',
						);
					}
				}
				break;

		}

		/**
		 * Define items to output for a table column in the Groups: Content WP_List_Table.
		 *
		 * @since   2.9.6
		 *
		 * @param   array   $items          HTML Item(s) to output in the column.
		 * @param   string  $column_name    Column Name.
		 * @param   int     $post_id        Group ID.
		 * @param   array   $settings       Group Settings.
		 */
		$items = apply_filters( 'page_generator_pro_groups_table_admin_columns_output', $items, $column_name, $post_id, $this->settings );

		// If no items are defined for output, bail.
		if ( empty( $items ) ) {
			return;
		}

		// Iterate through items, outputting.
		foreach ( $items as $class => $item ) {
			echo '<span class="' . esc_attr( $class ) . '">' . $item . '</span><br />'; // phpcs:ignore WordPress.Security.EscapeOutput
		}

	}

	/**
	 * Adds Row Actions below the Title in the Groups WP_List_Table.
	 *
	 * @since   1.2.3
	 *
	 * @param   array   $actions    Row Actions.
	 * @param   WP_Post $post       WordPress Post.
	 * @return  array               Row Actions
	 */
	public function admin_row_actions( $actions, $post ) {

		// Bail if not a Groups CPT.
		if ( get_post_type( $post ) !== $this->base->get_class( 'post_type' )->post_type_name ) {
			return $actions;
		}

		// Add Duplicate Action.
		$actions['duplicate'] = '<a href="' . $this->get_action_url( $post->ID, 'duplicate' ) . '">' . $this->base->get_class( 'groups_ui' )->get_title( 'duplicate' ) . '</a>';

		/**
		 * Filters the row actions to output on each Content Group in the Groups: Content WP_List_Table.
		 *
		 * @since   1.2.3
		 *
		 * @param   array       $actions                Row Actions.
		 * @param   WP_Post     $post                   Post.
		 */
		$actions = apply_filters( 'page_generator_pro_groups_table_admin_row_actions', $actions, $post );

		// Return.
		return $actions;

	}

	/**
	 * Checks if a Plugin row action was clicked by the User, and if so performs that action
	 *
	 * @since   1.2.3
	 */
	public function run_row_actions() {

		// Bail if no nonce exists or fails verification.
		if ( ! array_key_exists( 'nonce', $_REQUEST ) ) {
			return;
		}
		if ( ! wp_verify_nonce( sanitize_key( $_REQUEST['nonce'] ), 'action-page-generator-pro-content-group' ) ) {
			return;
		}

		// Bail if we're not on a Groups screen.
		if ( ! isset( $_REQUEST['post_type'] ) ) {
			return;
		}
		if ( sanitize_text_field( wp_unslash( $_REQUEST['post_type'] ) ) !== $this->base->get_class( 'post_type' )->post_type_name ) {
			return;
		}

		// If no action or ID specified, return.
		if ( ! isset( $_REQUEST[ $this->base->plugin->name . '-action' ] ) ) {
			return;
		}
		if ( ! isset( $_REQUEST['id'] ) ) {
			return;
		}

		// Fetch action and group ID.
		$action = sanitize_text_field( wp_unslash( $_REQUEST[ $this->base->plugin->name . '-action' ] ) );
		$id     = absint( $_REQUEST['id'] );

		// Run an action on the Group now.
		$this->base->get_class( 'groups' )->run_action( $action, $id, true );

	}

	/**
	 * Returns the full URL for the given Group ID and action,
	 * including any applicable search, pagination and ordering preferences defined
	 * by the WP_List_Table.
	 *
	 * @since   3.6.3
	 *
	 * @param   bool|int    $id         Group ID.
	 * @param   bool|string $action     Action.
	 * @param   bool|string $post_type  Target Post Type.
	 * @return  string                  URL
	 */
	public function get_action_url( $id = false, $action = false, $post_type = false ) {

		// Define nonce.
		$nonce = wp_create_nonce( 'action-page-generator-pro-content-group' );

		// Depending on the action requested, return the required URL parameters.
		switch ( $action ) {
			/**
			 * Generate via Browser runs on admin.php with some different parameters.
			 */
			case 'generate':
				return add_query_arg(
					array(
						'page'  => $this->base->plugin->name . '-generate',
						'id'    => $id,
						'type'  => 'content',
						'nonce' => $nonce,
					),
					admin_url( 'admin.php' )
				);

			/**
			 * View Generated Content runs on admin.php with some different parameters.
			 */
			case 'view':
				return add_query_arg(
					array(
						'post_type'                   => $post_type,
						'page_generator_pro_group_id' => $id,
					),
					admin_url( 'edit.php' )
				);

			/**
			 * No action; just honor search, pagination and order parameters.
			 */
			case false:
				return add_query_arg(
					array(
						'post_type' => $this->base->get_class( 'post_type' )->post_type_name,
						's'         => $this->get_search(),
						'orderby'   => $this->get_order_by(),
						'order'     => $this->get_order(),
						'paged'     => $this->get_page(),
					),
					admin_url( 'edit.php' )
				);

			/**
			 * All other actions run on the WP_List_Table
			 */
			default:
				return add_query_arg(
					array(
						'post_type' => $this->base->get_class( 'post_type' )->post_type_name,
						$this->base->plugin->name . '-action' => $action,
						'id'        => $id,
						'type'      => 'content',
						's'         => $this->get_search(),
						'orderby'   => $this->get_order_by(),
						'order'     => $this->get_order(),
						'paged'     => $this->get_page(),
						'nonce'     => $nonce,
					),
					admin_url( 'edit.php' )
				);
		}

	}

	/**
	 * Get the Search requested by the user
	 *
	 * @since   3.6.3
	 *
	 * @return  string
	 */
	private function get_search() {

		return ( isset( $_GET['s'] ) ? urldecode( sanitize_text_field( wp_unslash( $_GET['s'] ) ) ) : '' ); // phpcs:ignore WordPress.Security.NonceVerification

	}

	/**
	 * Get the Order By requested by the user
	 *
	 * @since   3.6.3
	 *
	 * @return  string
	 */
	private function get_order_by() {

		return ( isset( $_GET['orderby'] ) ? sanitize_sql_orderby( wp_unslash( $_GET['orderby'] ) ) : '' ); // phpcs:ignore WordPress.Security.NonceVerification

	}

	/**
	 * Get the Order requested by the user
	 *
	 * @since   3.6.3
	 *
	 * @return  string
	 */
	private function get_order() {

		return ( isset( $_GET['order'] ) ? sanitize_text_field( wp_unslash( $_GET['order'] ) ) : 'ASC' ); // phpcs:ignore WordPress.Security.NonceVerification

	}

	/**
	 * Get the Pagination Page requested by the user
	 *
	 * @since   3.6.3
	 *
	 * @return  int
	 */
	private function get_page() {

		return ( ( isset( $_GET['paged'] ) && ! empty( $_GET['paged'] ) ) ? absint( $_GET['paged'] ) : 1 ); // phpcs:ignore WordPress.Security.NonceVerification

	}

}
