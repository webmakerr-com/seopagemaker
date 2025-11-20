<?php
/**
 * Log Table WP_List_Table Class
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Controls the Logs WP_List_Table.
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 2.6.1
 */
class Page_Generator_Pro_Log_Table extends WP_List_Table {

	/**
	 * Holds the base class object.
	 *
	 * @since   2.6.1
	 *
	 * @var     object
	 */
	public $base;

	/**
	 * Constructor.
	 *
	 * @since   2.6.1
	 *
	 * @param   object $base    Base Plugin Class.
	 */
	public function __construct( $base ) {

		// Store base class.
		$this->base = $base;

		parent::__construct(
			array(
				// Singular label.
				'singular' => 'page-generator-pro-log',

				// Plural label.  Also will be the table's CSS class, and form the nonce action bulk-logs.
				'plural'   => 'page-generator-pro-logs',
				'ajax'     => false,
			)
		);

	}

	/**
	 * Display dropdowns for Bulk Actions and Filtering.
	 *
	 * @since   2.6.1
	 *
	 * @param   string $which  The location of the bulk actions: 'top' or 'bottom'.
	 *                         This is designated as optional for backward compatibility.
	 */
	protected function bulk_actions( $which = '' ) {

		// Get Bulk Actions.
		$actions = $this->get_bulk_actions();

		// Define <select> name.
		$bulk_actions_name = 'bulk_action' . ( $which !== 'top' ? '2' : '' );
		?>
		<label for="bulk-action-selector-<?php echo esc_attr( $which ); ?>" class="screen-reader-text">
			<?php esc_html_e( 'Select bulk action', 'page-generator-pro' ); ?>
		</label>
		<select name="<?php echo esc_attr( $bulk_actions_name ); ?>" id="bulk-action-selector-<?php echo esc_attr( $which ); ?>" size="1">
			<option value="-1"><?php esc_attr_e( 'Bulk Actions', 'page-generator-pro' ); ?></option>

			<?php
			foreach ( $actions as $name => $title ) {
				?>
				<option value="<?php echo esc_attr( $name ); ?>"><?php echo esc_attr( $title ); ?></option>
				<?php
			}
			?>
		</select>

		<?php
		// Output our custom filters to the top only.
		if ( $which === 'top' ) {
			?>
			<!-- Custom Filters -->

			<!-- Group -->
			<select name="group_id" size="1">
				<option value=""<?php selected( $this->base->get_class( 'log' )->get_group_id(), '' ); ?>><?php esc_attr_e( 'Filter by Group', 'page-generator-pro' ); ?></option>
				<?php
				foreach ( $this->base->get_class( 'groups' )->get_all_ids_names() as $group_id => $label ) {
					?>
					<option value="<?php echo esc_attr( $group_id ); ?>"<?php selected( $this->base->get_class( 'log' )->get_group_id(), $group_id ); ?>>#<?php echo esc_attr( $group_id . ': ' . $label ); ?></option>
					<?php
				}
				?>
			</select>

			<!-- Generation System -->
			<select name="system" size="1">
				<option value=""<?php selected( $this->base->get_class( 'log' )->get_system(), '' ); ?>><?php esc_attr_e( 'Filter by System', 'page-generator-pro' ); ?></option>
				<?php
				foreach ( $this->base->get_class( 'common' )->get_generation_systems() as $system => $label ) {
					?>
					<option value="<?php echo esc_attr( $system ); ?>"<?php selected( $this->base->get_class( 'log' )->get_system(), $system ); ?>><?php echo esc_attr( $label ); ?></option>
					<?php
				}
				?>
			</select>

			<!-- Result -->
			<select name="result" size="1">
				<option value=""<?php selected( $this->base->get_class( 'log' )->get_result(), '' ); ?>><?php esc_attr_e( 'Filter by Result', 'page-generator-pro' ); ?></option>
				<?php
				foreach ( $this->base->get_class( 'common' )->get_generation_results() as $result => $label ) {
					?>
					<option value="<?php echo esc_attr( $result ); ?>"<?php selected( $this->base->get_class( 'log' )->get_result(), $result ); ?>><?php echo esc_attr( $label ); ?></option>
					<?php
				}
				?>
			</select>

			<input type="date" name="generated_at_start_date" value="<?php echo esc_attr( $this->base->get_class( 'log' )->get_generated_at_start_date() ); ?>" />
			-
			<input type="date" name="generated_at_end_date" value="<?php echo esc_attr( $this->base->get_class( 'log' )->get_generated_at_end_date() ); ?>"/>
			<?php
		}

		submit_button( __( 'Apply', 'page-generator-pro' ), 'action', '', false, array( 'id' => 'doaction' ) );

		// Define URLs for Export and Clear Logs.
		$urls = array(
			'export_log' => add_query_arg(
				array(
					'page'         => $this->base->plugin->name . '-logs',
					'bulk_action3' => 'export',
					'_wpnonce'     => wp_create_nonce( 'bulk-page-generator-pro-logs' ),
				),
				isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : admin_url( 'admin.php' )
			),
			'clear_log'  => add_query_arg(
				array(
					'page'         => $this->base->plugin->name . '-logs',
					'bulk_action3' => 'delete_all',
					'_wpnonce'     => wp_create_nonce( 'bulk-page-generator-pro-logs' ),
				),
				admin_url( 'admin.php' )
			),
		);
		?>

		<a href="<?php echo esc_attr( $urls['export_log'] ); ?>" class="button">
			<?php esc_html_e( 'Export Log', 'page-generator-pro' ); ?>
		</a>

		<a href="<?php echo esc_attr( $urls['clear_log'] ); ?>" class="clear-log button wpzinc-button-red" data-message="<?php esc_attr_e( 'Are you sure you want to clear ALL logs?', 'page-generator-pro' ); ?>"> 
			<?php esc_html_e( 'Clear Log', 'page-generator-pro' ); ?>
		</a>
		<?php

	}

	/**
	 * Defines the message to display when no items exist in the table
	 *
	 * @since   2.6.1
	 */
	public function no_items() {

		esc_html_e( 'No log entries found based on the given search and filter criteria.', 'page-generator-pro' );

	}

	/**
	 * Displays the search box.
	 *
	 * @since   2.6.1
	 *
	 * @param   string $text        The submit button label.
	 * @param   string $input_id    ID attribute value for the search input field.
	 */
	public function search_box( $text, $input_id ) {

		$input_id = $input_id . '-search-input';

		// Preserve Filters by storing any defined as hidden form values.
		foreach ( $this->base->get_class( 'log' )->get_filters() as $filter ) {
			if ( ! empty( $_REQUEST[ $filter ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
				?>
				<input type="hidden" name="<?php echo esc_attr( $filter ); ?>" value="<?php echo esc_attr( sanitize_text_field( wp_unslash( $_REQUEST[ $filter ] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification ?>" />
				<?php
			}
		}
		?>
		<p class="search-box">
			<label class="screen-reader-text" for="<?php echo esc_attr( $input_id ); ?>"><?php echo esc_html( $text ); ?>:</label>
			<input type="search" id="<?php echo esc_attr( $input_id ); ?>" name="s" value="<?php _admin_search_query(); ?>" placeholder="<?php esc_attr_e( 'Group ID or Name', 'page-generator-pro' ); ?>" />
			<?php submit_button( $text, '', '', false, array( 'id' => 'search-submit' ) ); ?>
		</p>
		<?php
	}

	/**
	 * Define the columns that are going to be used in the table
	 *
	 * @since   2.6.1
	 *
	 * @return  array   Columns to use with the table
	 */
	public function get_columns() {

		return array(
			'cb'                => '<input type="checkbox" class="toggle" />',
			'group_id'          => __( 'Group', 'page-generator-pro' ),
			'post_id'           => __( 'Generated Item', 'page-generator-pro' ),
			'system'            => __( 'System', 'page-generator-pro' ),
			'test_mode'         => __( 'Test Mode', 'page-generator-pro' ),
			'generated'         => __( 'Generated', 'page-generator-pro' ),
			'keywords_terms'    => __( 'Keywords/Terms', 'page-generator-pro' ),
			'result'            => __( 'Result', 'page-generator-pro' ),
			'message'           => __( 'Message', 'page-generator-pro' ),
			'duration'          => __( 'Duration (Seconds)', 'page-generator-pro' ),
			'memory_usage'      => __( 'Memory Usage (MB)', 'page-generator-pro' ),
			'memory_peak_usage' => __( 'Memory Usage, Peak (MB)', 'page-generator-pro' ),
			'generated_at'      => __( 'Generated At', 'page-generator-pro' ),
		);

	}

	/**
	 * Decide which columns to activate the sorting functionality on
	 *
	 * @since   2.6.1
	 *
	 * @return  array   Columns that can be sorted by the user
	 */
	public function get_sortable_columns() {

		return array(
			'group_id'          => array( 'group_id', true ),
			'post_id'           => array( 'post_id', true ),
			'system'            => array( 'system', true ),
			'test_mode'         => array( 'test_mode', true ),
			'generated'         => array( 'generated', true ),
			'result'            => array( 'result', true ),
			'message'           => array( 'message', true ),
			'duration'          => array( 'duration', true ),
			'memory_usage'      => array( 'memory_usage', true ),
			'memory_peak_usage' => array( 'memory_peak_usage', true ),
			'generated_at'      => array( 'generated_at', true ),
		);

	}

	/**
	 * Overrides the list of bulk actions in the select dropdowns above and below the table
	 *
	 * @since   2.6.1
	 *
	 * @return  array   Bulk Actions
	 */
	public function get_bulk_actions() {

		return array(
			'delete' => __( 'Delete', 'page-generator-pro' ),
		);

	}

	/**
	 * Prepare the table with different parameters, pagination, columns and table elements
	 *
	 * @since   2.6.1
	 */
	public function prepare_items() {

		global $_wp_column_headers;

		$screen = get_current_screen();

		// Get params.
		$params   = $this->base->get_class( 'log' )->get_search_params();
		$order_by = $this->base->get_class( 'log' )->get_order_by();
		$order    = $this->base->get_class( 'log' )->get_order();
		$page     = $this->base->get_class( 'log' )->get_page();
		$per_page = $this->get_items_per_page( 'page_generator_pro_logs_per_page', 20 );

		// Get total records for this query.
		$total = $this->base->get_class( 'log' )->total( $params );

		// Define pagination.
		$this->set_pagination_args(
			array(
				'total_items' => $total,
				'total_pages' => (int) ceil( $total / $per_page ),
				'per_page'    => $per_page,
			)
		);

		// Set column headers.
		$this->_column_headers = $this->get_column_info();

		// Set rows.
		$this->items = $this->base->get_class( 'log' )->search( $order_by, $order, $page, $per_page, $params );

	}

	/**
	 * Generate the table navigation above or below the table
	 *
	 * @since   2.6.1
	 *
	 * @param   string $which  Location (top|bottom).
	 */
	protected function display_tablenav( $which ) {

		if ( 'top' === $which ) {
			wp_nonce_field( 'bulk-' . $this->_args['plural'] );
		}
		?>
		<div class="tablenav <?php echo esc_attr( $which ); ?>">
			<div class="alignleft actions bulkactions">
				<?php $this->bulk_actions( $which ); ?>
			</div>
			<?php
			$this->extra_tablenav( $which );
			$this->pagination( $which );
			?>

			<br class="clear" />
		</div>
		<?php

	}

	/**
	 * Display the rows of records in the table
	 *
	 * @since   2.6.1
	 */
	public function display_rows() {

		// Define columns.
		list( $columns, $hidden, $sortable, $primary ) = $this->_column_headers;

		// Load view.
		include $this->base->plugin->folder . 'views/admin/log-row.php';

	}

	/**
	 * Get the Search requested by the user
	 *
	 * @since   3.7.2
	 *
	 * @return  string
	 */
	public function get_search() {

		return ( isset( $_REQUEST['s'] ) ? urldecode( sanitize_text_field( wp_unslash( $_REQUEST['s'] ) ) ) : '' ); // phpcs:ignore WordPress.Security.NonceVerification

	}

}
