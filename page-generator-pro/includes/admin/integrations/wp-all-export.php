<?php
/**
 * WP All Export Integration Class
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Registers WP All Export as a Plugin integration:
 * - Adding an "Export via WP All Export" option on the Content Groups Table, to export all generated
 * Pages for a given Content Group.
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 2.9.7
 */
class Page_Generator_Pro_WP_All_Export extends Page_Generator_Pro_Integration {

	/**
	 * Holds the base object.
	 *
	 * @since   2.9.7
	 *
	 * @var     object
	 */
	public $base;

	/**
	 * Constructor
	 *
	 * @since   2.9.7
	 *
	 * @param   object $base    Base Plugin Class.
	 */
	public function __construct( $base ) {

		// Store base class.
		$this->base = $base;

		// Set Plugin.
		$this->plugin_folder_filename = array(
			'wp-all-export/wp-all-export.php',
			'wp-all-export-pro/wp-all-export-pro.php',
		);

		add_filter( 'page_generator_pro_screen_get_current_screen', array( $this, 'get_current_screen' ), 10, 2 );
		add_action( 'page_generator_pro_admin_admin_scripts_css', array( $this, 'enqueue_js' ), 10, 3 );
		add_filter( 'page_generator_pro_groups_table_admin_columns_output', array( $this, 'groups_table_admin_columns_output' ), 10, 3 );

	}

	/**
	 * Inject WP All Export as a screen and section, so that it can be detected
	 * by Page Generator Pro and have JS enqueued.
	 *
	 * @since   2.9.7
	 *
	 * @param   array  $result     Screen and Section.
	 * @param   string $screen_id  Screen.
	 * @return  array                   Screen and Section
	 */
	public function get_current_screen( $result, $screen_id ) {

		// Bail if WP All Export isn't active.
		if ( ! $this->is_active() ) {
			return $result;
		}

		// Bail if we're not on an WP All Export screen.
		if ( $screen_id !== 'all-export_page_pmxe-admin-export' ) {
			return $result;
		}

		// Define WP All Export as the screen and section.
		$result['screen']  = 'wp_all_export';
		$result['section'] = 'export';

		// Return.
		return $result;

	}

	/**
	 * Enqueues CSS and JS
	 *
	 * @since   2.9.7
	 *
	 * @param   array   $screen     Screen (screen, section).
	 * @param   WP_Post $post       WordPress Post.
	 * @param   bool    $minified   Load minified JS.
	 */
	public function enqueue_js( $screen, $post, $minified ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter

		// Bail if WP All Export isn't active.
		if ( ! $this->is_active() ) {
			return;
		}

		// Bail if we're not on the WP All Export screen.
		if ( ! $screen['screen'] ) {
			return;
		}
		if ( $screen['screen'] !== 'wp_all_export' ) {
			return;
		}
		if ( $screen['section'] !== 'export' ) {
			return;
		}

		// Bail if no Content Group ID is specified in the request URL.
		if ( ! isset( $_REQUEST['page-generator-pro-group-id'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			return;
		}

		// Get Group ID.
		$group_id = absint( $_REQUEST['page-generator-pro-group-id'] ); // phpcs:ignore WordPress.Security.NonceVerification
		if ( ! is_numeric( $group_id ) ) {
			return;
		}

		// Define WP All Export compatible query parameters that will select all generated content for the given Group ID.
		$query = "'post_type' => 'any', 'post_status' => 'any', 'meta_key' => '_page_generator_pro_group', 'meta_value' => '" . $group_id . "', 'posts_per_page' => '-1'";

		// Enqueue and localize JS.
		wp_enqueue_script( $this->base->plugin->name . '-wp-all-export', $this->base->plugin->url . 'assets/js/' . ( $minified ? 'min/' : '' ) . 'wp-all-export' . ( $minified ? '-min' : '' ) . '.js', array( 'jquery' ), $this->base->plugin->version, true );
		wp_localize_script(
			$this->base->plugin->name . '-wp-all-export',
			'page_generator_pro_wp_all_export',
			array(
				'group_id' => $group_id,
				'query'    => $query,
			)
		);

	}

	/**
	 * Outputs an Export via WP All Export option on the Content Groups Table if
	 * WP All Export is active.
	 *
	 * @since   2.9.7
	 *
	 * @param   array  $items          HTML Item(s) to output in the column.
	 * @param   string $column_name    Column Name.
	 * @param   int    $group_id       Group ID.
	 * @return  array                   HTML Item(s) to output in the column
	 */
	public function groups_table_admin_columns_output( $items, $column_name, $group_id ) {

		// Bail if WP All Export isn't active.
		if ( ! $this->is_active() ) {
			return $items;
		}

		// Bail if we're not on the Generated Content column.
		if ( $column_name !== 'generated_count' ) {
			return $items;
		}

		// Build Export Link.
		$url = add_query_arg(
			array(
				'page'                        => 'pmxe-admin-export',
				'page-generator-pro-group-id' => $group_id,
			),
			'admin.php'
		);

		// Add Export Link.
		$items['wp_all_export'] = '<a href="' . $url . '">' . __( 'Export Generated Content via WP All Export', 'page-generator-pro' ) . '</a>';

		// Return.
		return $items;

	}

}
