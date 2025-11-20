<?php
/**
 * Term Groups Table Class
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Handles Term Groups WP_List_Table actions.
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 2.0.2
 */
class Page_Generator_Pro_Groups_Terms_Table {

	/**
	 * Holds the base class object.
	 *
	 * @since   2.0.2
	 *
	 * @var     object
	 */
	public $base;

	/**
	 * Holds the Term Group's settings.
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
		add_filter( 'bulk_actions-edit-page-generator-tax', array( $this, 'register_bulk_actions' ) );
		add_filter( 'handle_bulk_actions-edit-page-generator-tax', array( $this, 'run_bulk_actions' ), 10, 3 );

		// WP_List_Table Columns.
		add_filter( 'manage_edit-page-generator-tax_columns', array( $this, 'admin_columns' ) );
		add_filter( 'manage_page-generator-tax_custom_column', array( $this, 'admin_columns_output' ), 10, 3 );

		// WP_List_Table Row Actions.
		add_filter( 'page-generator-tax_row_actions', array( $this, 'admin_row_actions' ), 10, 2 );

		// Run any row actions called from the WP_List_Table.
		add_action( 'init', array( $this, 'run_row_actions' ) );

	}

	/**
	 * Adds Bulk Action options to Groups Terms WP_List_Table
	 *
	 * @since   2.0.2
	 *
	 * @param   array $actions    Registered Bulk Actions.
	 * @return  array               Registered Bulk Actions.
	 */
	public function register_bulk_actions( $actions ) {

		// Define Actions.
		$bulk_actions = array(
			'duplicate'                => $this->base->get_class( 'groups_terms_ui' )->get_title( 'duplicate' ),
			'generate_server'          => $this->base->get_class( 'groups_terms_ui' )->get_title( 'generate_server' ),
			'delete_generated_content' => $this->base->get_class( 'groups_terms_ui' )->get_title( 'delete_generated_content' ),
		);

		// Remove some actions we don't want.
		unset( $actions['edit'] );

		/**
		 * Defines Bulk Actions to be added to the select dropdown on the Groups Terms WP_List_Table.
		 *
		 * @since   2.0.2
		 *
		 * @param   array   $bulk_actions   Plugin Specific Bulk Actions.
		 * @param   array   $actions        Existing Registered Bulk Actions (excluding Plugin Specific Bulk Actions).
		 */
		$bulk_actions = apply_filters( 'page_generator_pro_groups_terms_table_register_bulk_actions', $bulk_actions, $actions );

		// Merge with default Bulk Actions.
		$actions = array_merge( $bulk_actions, $actions );

		// Return.
		return $actions;

	}

	/**
	 * Handles Bulk Actions when one is selected to run
	 *
	 * @since   2.0.2
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
					$result = $this->base->get_class( 'groups_terms' )->duplicate( $post_id );

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
								$this->base->get_class( 'groups_terms_ui' )->get_message( 'duplicate_success' )
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
					// Schedule.
					$result = $this->base->get_class( 'groups_terms' )->schedule_generation( $post_id );

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
								$this->base->get_class( 'groups_terms_ui' )->get_title( 'generate_server_success' )
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
					// Delete.
					$result = $this->base->get_class( 'groups_terms' )->delete_generated_content( $post_id );

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
								$this->base->get_class( 'groups_terms_ui' )->get_message( 'delete_generated_content_success' )
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
				do_action( 'page_generator_pro_groups_terms_table_run_bulk_actions', $action, $post_ids );
				break;

		}

		// Return redirect.
		return $redirect_to;

	}

	/**
	 * Adds columns to the Groups Terms within the WordPress Administration List Table
	 *
	 * @since   2.0.2
	 *
	 * @param   array $columns    Columns.
	 * @return  array               New Columns
	 */
	public function admin_columns( $columns ) {

		// Remove columsn we don't want.
		unset( $columns['posts'], $columns['description'] );

		// Inject columns.
		$columns['id']              = __( 'Group ID', 'page-generator-pro' );
		$columns['taxonomy']        = __( 'Taxonomy', 'page-generator-pro' );
		$columns['generated_count'] = __( 'No. Generated Items', 'page-generator-pro' );
		$columns['status']          = __( 'Status', 'page-generator-pro' );

		/**
		 * Filters the columns to display on the Groups: Terms WP_List_Table.
		 *
		 * @since   2.0.2
		 *
		 * @param   array   $columns    Columns.
		 */
		$columns = apply_filters( 'page_generator_pro_groups_terms_table_admin_columns', $columns );

		// Return.
		return $columns;

	}

	/**
	 * Manages the data to be displayed within a column on the Groups Taxonomy within
	 * the WordPress Administration List Table
	 *
	 * @since   2.0.2
	 *
	 * @param   string $content        Content.
	 * @param   string $column_name    Column Name.
	 * @param   int    $term_id        Group ID.
	 */
	public function admin_columns_output( $content, $column_name, $term_id ) {

		// Array to hold the item(s) to output in this column for this Group.
		$items = array();

		// Get group settings, if we don't have them.
		if ( $term_id !== $this->group_id ) {
			$this->group_id = $term_id;
			$this->settings = $this->base->get_class( 'groups_terms' )->get_settings( $this->group_id );
		}

		// Get limit.
		$limit = $this->base->get_class( 'ajax' )->get_trash_delete_per_request_item_limit();

		// Define items array.
		switch ( $column_name ) {
			/**
			 * ID
			 */
			case 'id':
				echo esc_html( (string) $this->group_id );
				break;

			/**
			 * Taxonomy
			 */
			case 'taxonomy':
				$items = array(
					'taxonomy' => $this->settings['taxonomy'],
				);
				break;

			/**
			 * Number of Generated Pages
			 */
			case 'generated_count':
				if ( $this->settings['generated_pages_count'] ) {
					$items = array(
						/* translators: Number of generated items, wrapped in HTML */
						'generated_count'          => sprintf( __( 'Generated Items: %s', 'page-generator-pro' ), '<span class="count" data-group-id="' . $term_id . '">' . $this->settings['generated_pages_count'] . '</span>' ),

						/* translators: Last generated index number, wrapped in HTML */
						'last_index_generated'     => sprintf( __( 'Last Index Generated: %s', 'page-generator-pro' ), '<span class="count" data-group-id="' . $term_id . '">' . $this->settings['last_index_generated'] . '</span>' ),
						'delete_generated_content' => '<a href="' . $this->get_action_url( $term_id, 'delete_generated_content' ) . '"  data-group-id="' . $term_id . '" data-limit="' . $limit . '" data-total="' . $this->settings['generated_pages_count'] . '">' .
							__( 'Delete Generated Content', 'page-generator-pro' ) .
						'</a>',
					);
				} else {
					$items = array(
						/* translators: Number of generated items, wrapped in HTML */
						'generated_count'      => sprintf( __( 'Generated Items: %s', 'page-generator-pro' ), '<span class="count" data-group-id="' . $term_id . '">' . $this->settings['generated_pages_count'] . '</span>' ),

						/* translators: Last generated index number, wrapped in HTML */
						'last_index_generated' => sprintf( __( 'Last Index Generated: %s', 'page-generator-pro' ), '<span class="count" data-group-id="' . $term_id . '">' . $this->settings['last_index_generated'] . '</span>' ),
					);
				}
				break;

			/**
			 * Status
			 */
			case 'status':
				if ( $this->base->get_class( 'groups_terms' )->is_idle( $term_id ) ) {
					$items = array(
						'test'            => '<a href="' . $this->get_action_url( $term_id, 'test' ) . '" data-current-index="' . $this->settings['resumeIndex'] . '">' . $this->base->get_class( 'groups_terms_ui' )->get_title( 'test' ) . '</a>',
						'generate'        => '<a href="' . $this->get_action_url( $term_id, 'generate' ) . '">' . $this->base->get_class( 'groups_terms_ui' )->get_title( 'generate' ) . '</a>',
						'generate_server' => '<a href="' . $this->get_action_url( $term_id, 'generate_server' ) . '">' . $this->base->get_class( 'groups_terms_ui' )->get_title( 'generate_server' ) . '</a>',
					);
				} else {
					$items = array(
						'status'            => '<div class="page-generator-pro-generating-spinner">
                                        <span class="spinner"></span>' .
										ucfirst( $this->base->get_class( 'groups_terms' )->get_status( $term_id ) ) . ': ' .
										$this->base->get_class( 'groups_terms' )->get_system( $term_id ) . '
                                    </div>',
						'cancel_generation' => '<a href="' . $this->get_action_url( $term_id, 'cancel_generation' ) . '">' . $this->base->get_class( 'groups_terms_ui' )->get_title( 'cancel_generation' ) . '</a>',
					);
				}
				break;

			default:
				/**
				 * Filters the output for a non-standard column on the Groups: Terms WP_List_Table.
				 *
				 * @since   2.0.2
				 *
				 * @param   string   $column_name   Columns.
				 * @param   int      $term_id       Group ID.
				 * @param   array    $settings      Group Settings.
				 */
				$content = apply_filters( 'page_generator_pro_groups_terms_table_admin_columns_output', $column_name, $term_id, $this->settings );
				break;
		}

		// If no items are defined for output, bail.
		if ( empty( $items ) ) {
			return $content;
		}

		// Iterate through items, outputting.
		foreach ( $items as $class => $item ) {
			$content .= '<span class="' . $class . '">' . $item . '</span><br />';
		}

		// Return.
		return $content;

	}

	/**
	 * Adds Duplicate, Test and Generate Row Actions to each Term Group within
	 * the WordPress Administration List Table
	 *
	 * @since   2.0.2
	 *
	 * @param   array   $actions    Row Actions.
	 * @param   WP_Term $term       Taxonomy Term.
	 * @return  array                   Row Actions
	 */
	public function admin_row_actions( $actions, $term ) {

		// Bail if not a Groups Term.
		if ( $term->taxonomy !== $this->base->get_class( 'taxonomy' )->taxonomy_name ) {
			return $actions;
		}

		// Add Duplicate Action.
		$actions['duplicate'] = '<br /><a href="' . $this->get_action_url( $term->term_id, 'duplicate' ) . '">' . $this->base->get_class( 'groups_terms_ui' )->get_title( 'duplicate' ) . '</a>';

		/**
		 * Filters the row actions to output on each Content Group in the Groups: Content WP_List_Table.
		 *
		 * @since   2.0.2
		 *
		 * @param   array       $actions                Row Actions.
		 * @param   WP_Term     $term                   Term.
		 */
		$actions = apply_filters( 'page_generator_pro_groups_terms_table_admin_row_actions', $actions, $term );

		// Return.
		return $actions;

	}

	/**
	 * Runs a clicked action for a given Term Group.
	 *
	 * @since   2.0.2
	 */
	public function run_row_actions() {

		// Bail if no nonce exists or fails verification.
		if ( ! array_key_exists( 'nonce', $_REQUEST ) ) {
			return;
		}
		if ( ! wp_verify_nonce( sanitize_key( $_REQUEST['nonce'] ), 'action-page-generator-pro-term-group' ) ) {
			return;
		}

		// Bail if we're not on a Groups screen.
		if ( ! isset( $_REQUEST['taxonomy'] ) ) {
			return;
		}
		if ( sanitize_text_field( wp_unslash( $_REQUEST['taxonomy'] ) ) !== $this->base->get_class( 'taxonomy' )->taxonomy_name ) {
			return;
		}

		// If no action or ID specified, return.
		if ( ! isset( $_REQUEST[ $this->base->plugin->name . '-action' ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			return;
		}
		if ( ! isset( $_REQUEST['id'] ) ) {
			return;
		}

		// Fetch action and group ID.
		$action = sanitize_text_field( wp_unslash( $_REQUEST[ $this->base->plugin->name . '-action' ] ) ); // phpcs:ignore WordPress.Security.NonceVerification
		$id     = absint( $_REQUEST['id'] ); // phpcs:ignore WordPress.Security.NonceVerification

		// Run action.
		$this->base->get_class( 'groups_terms' )->run_action( $action, $id, true );

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
	 * @return  string                  URL
	 */
	public function get_action_url( $id = false, $action = false ) {

		// Define nonce.
		$nonce = wp_create_nonce( 'action-page-generator-pro-term-group' );

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
						'type'  => 'term',
						'nonce' => $nonce,
					),
					admin_url( 'admin.php' )
				);

			/**
			 * No action; just honor search, pagination and order parameters.
			 */
			case false:
				return add_query_arg(
					array(
						'taxonomy' => $this->base->get_class( 'taxonomy' )->taxonomy_name,
						's'        => $this->get_search(),
						'orderby'  => $this->get_order_by(),
						'order'    => $this->get_order(),
						'paged'    => $this->get_page(),
					),
					admin_url( 'edit-tags.php' )
				);

			/**
			 * All other actions run on the WP_List_Table
			 */
			default:
				return add_query_arg(
					array(
						'taxonomy' => $this->base->get_class( 'taxonomy' )->taxonomy_name,
						$this->base->plugin->name . '-action' => $action,
						'id'       => $id,
						'type'     => 'term',
						's'        => $this->get_search(),
						'orderby'  => $this->get_order_by(),
						'order'    => $this->get_order(),
						'paged'    => $this->get_page(),
						'nonce'    => $nonce,
					),
					admin_url( 'edit-tags.php' )
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
