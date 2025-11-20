<?php
/**
 * Export Class
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Fetches configuration data from the Plugin, such as settings, Content Groups
 * and Term Groups, based on the user's selection, writing it to an export
 * JSON / ZIP file.
 *
 * This export file can then be used on another Page Generator Pro installation.
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 2.6.8
 */
class Page_Generator_Pro_Export {

	/**
	 * Holds the base class object.
	 *
	 * @since   2.6.8
	 *
	 * @var     object
	 */
	public $base;

	/**
	 * Constructor
	 *
	 * @since   2.6.8
	 *
	 * @param   object $base    Base Plugin Class.
	 */
	public function __construct( $base ) {

		// Store base class.
		$this->base = $base;

		// Export.
		add_action( 'page_generator_pro_export_view', array( $this, 'output_export_options' ) );
		add_filter( 'page_generator_pro_export', array( $this, 'export' ), 10, 2 );

	}

	/**
	 * Outputs options on the Export screen to choose what data to include
	 * in the export
	 *
	 * @since   2.7.6
	 */
	public function output_export_options() {

		// Get Keywords, Content Groups and Term Groups.
		$keywords       = $this->base->get_class( 'keywords' )->get_all( 'keyword', 'ASC', -1 );
		$content_groups = $this->base->get_class( 'groups' )->get_all();
		$term_groups    = $this->base->get_class( 'groups_terms' )->get_all();

		// Load view.
		include_once $this->base->plugin->folder . '/views/admin/export.php';

	}

	/**
	 * Export data
	 *
	 * @since   2.6.8
	 *
	 * @param   array $data   Export Data.
	 * @param   array $params Export Parameters (define what data to export).
	 * @return  array           Export Data
	 */
	public function export( $data, $params ) {

		// Keywords.
		if ( isset( $params['keywords'] ) ) {
			$keyword_ids      = array_keys( $params['keywords'] );
			$keywords         = $this->base->get_class( 'keywords' )->get_all( 'keyword', 'ASC', -1 );
			$data['keywords'] = array();
			foreach ( $keywords as $keyword ) {
				// Skip if not a Keyword ID we're exporting.
				if ( ! in_array( $keyword->keywordID, $keyword_ids ) ) { // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase,WordPress.PHP.StrictInArray.MissingTrueStrict
					continue;
				}

				// Add keyword to export data.
				$data['keywords'][] = $keyword;
			}
		}

		// Disable all page_generator_pro_groups_get_post_meta filters, so that JSON strings are not decoded.
		// Many of our integrations e.g. Page_Generator_Pro_Breakdance will decode JSON when fetching the Group's
		// Post Meta, to ensure it can be used in the Generate Routine.
		// This results in the decoded array being serialised in the export file, which results in it being
		// imported incorrectly and therefore not read.
		add_filter( 'page_generator_pro_groups_get_post_meta_run_filters', '__return_false' );

		// Content Groups.
		if ( isset( $params['content_groups'] ) ) {
			$group_ids      = array_keys( $params['content_groups'] );
			$groups         = $this->base->get_class( 'groups' )->get_all();
			$data['groups'] = array();
			foreach ( $groups as $group_id => $group ) {
				// Skip if not a Group ID we're exporting.
				if ( ! in_array( $group_id, $group_ids ) ) { // phpcs:ignore WordPress.PHP.StrictInArray
					continue;
				}

				// Add group to export data.
				$data['groups'][ $group_id ] = $group;
			}
		}

		// Enable all page_generator_pro_groups_get_post_meta filters.
		add_filter( 'page_generator_pro_groups_get_post_meta_run_filters', '__return_true' );

		// Term Groups.
		if ( isset( $params['term_groups'] ) ) {
			$group_ids     = array_keys( $params['term_groups'] );
			$groups        = $this->base->get_class( 'groups_terms' )->get_all();
			$data['terms'] = array();
			foreach ( $groups as $group_id => $group ) {
				// Skip if not a Group ID we're exporting.
				if ( ! in_array( $group_id, $group_ids ) ) { // phpcs:ignore WordPress.PHP.StrictInArray
					continue;
				}

				// Add group to export data.
				$data['terms'][ $group_id ] = $group;
			}
		}

		// Settings.
		if ( isset( $params['settings'] ) ) {
			$data['general']            = $this->base->get_class( 'settings' )->get_settings( $this->base->plugin->name . '-general' );
			$data['generate']           = $this->base->get_class( 'settings' )->get_settings( $this->base->plugin->name . '-generate' );
			$data['generate-locations'] = $this->base->get_class( 'settings' )->get_settings( $this->base->plugin->name . '-generate-locations' );
			$data['integrations']       = $this->base->get_class( 'settings' )->get_settings( $this->base->plugin->name . '-integrations' );
			$data['research']           = $this->base->get_class( 'settings' )->get_settings( $this->base->plugin->name . '-research' );
			$data['spintax']            = $this->base->get_class( 'settings' )->get_settings( $this->base->plugin->name . '-spintax' );
		}

		return $data;

	}

}
