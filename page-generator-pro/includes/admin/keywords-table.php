<?php
/**
 * Keywords WP_List_Table Class
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Controls the Keywords WP_List_Table.
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 1.0.0
 */
class Page_Generator_Pro_Keywords_Table extends WP_List_Table {

	/**
	 * Holds the base class object.
	 *
	 * @since   1.3.8
	 *
	 * @var     object
	 */
	public $base;

	/**
	 * Constructor.
	 *
	 * @since   1.0.0
	 *
	 * @param   object $base    Base Plugin Class.
	 */
	public function __construct( $base ) {

		// Store base class.
		$this->base = $base;

		parent::__construct(
			array(
				'singular' => 'keyword',   // Singular label.
				'plural'   => 'keywords',  // plural label, also this well be one of the table css class.
				'ajax'     => false,        // We won't support Ajax for this table.
			)
		);

	}

	/**
	 * Defines the message to display when no items exist in the table
	 *
	 * @since   1.0.0
	 */
	public function no_items() {

		// If search terms are supplied, show a message.
		if ( ! empty( $this->get_search() ) ) {
			printf(
				'%1$s <strong>%2$s</strong>',
				esc_html__( 'No keywords found matching the search term', 'page-generator-pro' ),
				esc_html( $this->get_search() )
			);
			echo ( '<br /><a href="admin.php?page=' . esc_attr( $this->base->plugin->name ) . '-keywords" class="button">' . esc_html__( 'View all keywords', 'page-generator-pro' ) . '</a>' );

			return;
		}

		// No Keywords exist in the database table.
		esc_html_e( 'Keywords are used to produce unique content for each Page, Post or Custom Post Type that is generated.', 'page-generator-pro' );
		echo ( '<br /><a href="admin.php?page=' . esc_attr( $this->base->plugin->name ) . '-keywords&cmd=form" class="button">' . esc_html__( 'Create first keyword.', 'page-generator-pro' ) . '</a>' );

	}

	/**
	 * Define the columns that are going to be used in the table
	 *
	 * @since   1.0.0
	 *
	 * @return  array   Columns to use with the table
	 */
	public function get_columns() {

		return array(
			'cb'                   => '<input type="checkbox" class="toggle" />',
			'col_field_keyword'    => __( 'Keyword', 'page-generator-pro' ),
			'col_field_source'     => __( 'Source', 'page-generator-pro' ),
			'col_field_delimiter'  => __( 'Delimiter', 'page-generator-pro' ),
			'col_field_columns'    => __( 'Columns', 'page-generator-pro' ),
			'col_field_term_count' => __( 'Number of Terms', 'page-generator-pro' ),
		);

	}

	/**
	 * Decide which columns to activate the sorting functionality on
	 *
	 * @since   1.0.0
	 *
	 * @return  array   Columns that can be sorted by the user
	 */
	public function get_sortable_columns() {

		$sortable = array(
			'col_field_keyword' => array( 'keyword', true ),
			'col_field_source'  => array( 'source', true ),
		);

		return $sortable;

	}

	/**
	 * Overrides the list of bulk actions in the select dropdowns above and below the table
	 *
	 * @since   1.0.0
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
	 * @since   1.0.0
	 */
	public function prepare_items() {

		global $_wp_column_headers;

		$screen = get_current_screen();

		// Get params.
		$search   = $this->get_search();
		$order_by = $this->get_order_by();
		$order    = $this->get_order();
		$page     = $this->get_page();
		$per_page = $this->get_items_per_page( 'page_generator_pro_keywords_per_page', 20 );

		// Get total records for this query.
		$total = $this->base->get_class( 'keywords' )->total( $search );

		// Define pagination.
		$this->set_pagination_args(
			array(
				'total_items' => $total,
				'total_pages' => (int) ceil( $total / $per_page ),
				'per_page'    => $per_page,
			)
		);

		// Set column headers.
		$this->_column_headers = array(
			$this->get_columns(),
			array(),
			$this->get_sortable_columns(),
		);

		$this->items = $this->base->get_class( 'keywords' )->get_all( $order_by, $order, $page, $per_page, $search );

	}

	/**
	 * Display the rows of records in the table
	 *
	 * @since   1.0.0
	 */
	public function display_rows() {

		// Get rows and columns.
		$records                  = $this->items;
		list( $columns, $hidden ) = $this->get_column_info();

		// Bail if no records found.
		if ( empty( $records ) ) {
			return;
		}

		// Get params.
		$params = array(
			'page'    => $this->base->plugin->name . '-keywords',
			's'       => $this->get_search(),
			'orderby' => $this->get_order_by(),
			'order'   => $this->get_order(),
			'paged'   => $this->get_page(),
		);

		// Iterate through records.
		foreach ( $records as $key => $record ) {
			// Start row.
			echo ( '<tr id="record_' . esc_attr( $record->keywordID ) . '"' . ( ( $key % 2 === 0 ) ? ' class="alternate"' : '' ) . '>' ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase

			// Iterate through columns.
			foreach ( $columns as $column_name => $display_name ) {
				switch ( $column_name ) {

					/**
					 * Checkbox
					 */
					case 'cb':
						echo ( '<th scope="row" class="check-column"><input type="checkbox" name="ids[' . esc_attr( $record->keywordID ) . ']" value="' . esc_attr( $record->keywordID ) . '" /></th>' ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
						break;

					/**
					 * Keyword
					 */
					case 'col_field_keyword':
						// Define nonce.
						$nonce = wp_create_nonce( 'action-keywords' );

						// Build URLs for edit, delete and duplicate.
						$edit_url = add_query_arg(
							array(
								'page' => $this->base->plugin->name . '-keywords',
								'cmd'  => 'form',
								'id'   => absint( $record->keywordID ), // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
							),
							'admin.php'
						);

						$delete_url = add_query_arg(
							array_merge(
								$params,
								array(
									'cmd'      => 'delete',
									'id'       => absint( $record->keywordID ), // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
									'_wpnonce' => $nonce,
								)
							),
							'admin.php'
						);

						$duplicate_url = add_query_arg(
							array_merge(
								$params,
								array(
									'cmd'      => 'duplicate',
									'id'       => absint( $record->keywordID ), // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
									'_wpnonce' => $nonce,
								)
							),
							'admin.php'
						);

						$export_url = add_query_arg(
							array_merge(
								$params,
								array(
									'cmd'      => 'export_csv',
									'id'       => absint( $record->keywordID ), // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
									'_wpnonce' => $nonce,
								)
							),
							'admin.php'
						);

						echo ( '<td class="' . esc_attr( $column_name ) . ' column-' . esc_attr( $column_name ) . '">
									<strong>
										<a href="' . esc_attr( $edit_url ) . '" title="' . esc_attr__( 'Edit this keyword', 'page-generator-pro' ) . '">
											' . esc_html( $record->keyword ) . '
										</a>
									</strong>
									<div class="row-actions">
										<span class="edit">
											<a href="' . esc_attr( $edit_url ) . '" title="' . esc_attr__( 'Edit this keyword', 'page-generator-pro' ) . '">
											' . esc_html__( 'Edit', 'page-generator-pro' ) . '
											</a> | 
										</span>
										<span class="edit">
											<a href="' . esc_attr( $duplicate_url ) . '" title="' . esc_attr__( 'Duplicate this keyword', 'page-generator-pro' ) . '">
											' . esc_html__( 'Duplicate', 'page-generator-pro' ) . '
											</a> | 
										</span>
										<span class="edit">
											<a href="' . esc_attr( $export_url ) . '" title="' . esc_attr__( 'Export this keyword to a CSV file', 'page-generator-pro' ) . '">
											' . esc_html__( 'Export to CSV', 'page-generator-pro' ) . '
											</a> | 
										</span>
										<span class="trash">
											<a href="' . esc_attr( $delete_url ) . '" title="' . esc_attr__( 'Delete this keyword', 'page-generator-pro' ) . '" class="delete">
											' . esc_html__( 'Delete', 'page-generator-pro' ) . '
											</a>
										</span>
									</div>
								</td>' );
						break;

					/**
					 * Source
					 */
					case 'col_field_source':
						echo '<td class="' . esc_attr( $column_name ) . ' column-' . esc_attr( $column_name ) . '">' . esc_html( ! empty( $record->source ) ? $record->source : 'local' ) . '</td>';
						break;

					/**
					 * Delimiter
					 */
					case 'col_field_delimiter':
						echo '<td class="' . esc_attr( $column_name ) . ' column-' . esc_attr( $column_name ) . '">' . esc_html( $record->delimiter ) . '</td>';
						break;

					/**
					 * Columns
					 */
					case 'col_field_columns':
						echo '<td class="' . esc_attr( $column_name ) . ' column-' . esc_attr( $column_name ) . '">' . implode( '<br />', explode( ',', $record->columns ) ) . '</td>'; // phpcs:ignore WordPress.Security.EscapeOutput
						break;

					/**
					 * Number of Terms
					 */
					case 'col_field_term_count':
						echo ( '<td class="' . esc_attr( $column_name ) . ' column-' . esc_attr( $column_name ) . '">' . esc_html( number_format( count( $this->base->get_class( 'keywords' )->terms_to_array( $record->data ) ) ) ) . '</td>' );
						break;

				}
			}

			// End row.
			echo ( ' </tr>' );
		}

	}

	/**
	 * Get the Search requested by the user
	 *
	 * @since   2.6.5
	 *
	 * @return  string
	 */
	public function get_search() {

		return ( isset( $_GET['s'] ) ? urldecode( sanitize_text_field( wp_unslash( $_GET['s'] ) ) ) : '' ); // phpcs:ignore WordPress.Security.NonceVerification

	}

	/**
	 * Get the Order By requested by the user
	 *
	 * @since   2.6.5
	 *
	 * @return  string
	 */
	private function get_order_by() {

		return ( isset( $_GET['orderby'] ) ? sanitize_sql_orderby( wp_unslash( $_GET['orderby'] ) ) : 'keyword' ); // phpcs:ignore WordPress.Security.NonceVerification

	}

	/**
	 * Get the Order requested by the user
	 *
	 * @since   2.6.5
	 *
	 * @return  string
	 */
	private function get_order() {

		return ( isset( $_GET['order'] ) ? sanitize_text_field( wp_unslash( $_GET['order'] ) ) : 'ASC' ); // phpcs:ignore WordPress.Security.NonceVerification

	}

	/**
	 * Get the Pagination Page requested by the user
	 *
	 * @since   2.6.5
	 *
	 * @return  int
	 */
	private function get_page() {

		return ( ( isset( $_GET['paged'] ) && ! empty( $_GET['paged'] ) ) ? absint( $_GET['paged'] ) : 1 ); // phpcs:ignore WordPress.Security.NonceVerification

	}

}
