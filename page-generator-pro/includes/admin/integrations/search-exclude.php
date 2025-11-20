<?php
/**
 * Search Exclude Integration Class
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Registers Search Exclude as a Plugin integration:
 * - Copy metadata to generated Pages
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 3.0.8
 */
class Page_Generator_Pro_Search_Exclude extends Page_Generator_Pro_Integration {

	/**
	 * Holds the base object.
	 *
	 * @since   3.0.8
	 *
	 * @var     object
	 */
	public $base;

	/**
	 * Constructor
	 *
	 * @since   3.0.8
	 *
	 * @param   object $base    Base Plugin Class.
	 */
	public function __construct( $base ) {

		// Store base class.
		$this->base = $base;

		// Set Plugin.
		$this->plugin_folder_filename = 'search-exclude/search-exclude.php';

		// Copy Search Exclude settings to Generated Page.
		add_action( 'page_generator_pro_generate_set_post_meta', array( $this, 'copy_settings_to_generated_page' ), 10, 5 );

	}

	/**
	 * Copies Search Exclude settings from the Content Group to the Generated Content.
	 *
	 * @since   3.0.8
	 *
	 * @param   int   $post_id        Generated Page ID.
	 * @param   int   $group_id       Group ID.
	 * @param   array $post_meta      Group Post Meta.
	 * @param   array $settings       Group Settings.
	 * @param   array $post_args      wp_insert_post() / wp_update_post() arguments.
	 */
	public function copy_settings_to_generated_page( $post_id, $group_id, $post_meta, $settings, $post_args ) {

		// Bail if Search Exclude isn't active.
		if ( ! $this->is_active() ) {
			return;
		}

		// Copy the Content Group's Search Exclude Plugin's 'Exclude' setting to the generated page.
		$this->set_exclude_on_post( $post_id, $settings['type'], $this->is_excluded( $group_id ) );

	}

	/**
	 * Checks if the given Content Group's Search Exclude setting is enabled or not, mimicking the Search Exclude plugin's
	 * is_excluded() method.
	 *
	 * @since   5.1.8
	 *
	 * @param   int $post_id        Generated Post ID.
	 * @return  bool
	 */
	private function is_excluded( $post_id ) {

		$post_type = get_post_type( $post_id );

		$entries = QuadLayers\QLSE\Models\Settings::instance()->get()->get( 'entries' );

		if ( ! isset( $entries[ $post_type ] ) ) {
			return false;
		}

		if ( $entries[ $post_type ]['all'] ) {
			return true;
		}

		$excluded = isset( $entries[ $post_type ]['ids'] ) && is_array( $entries[ $post_type ]['ids'] )
			? $entries[ $post_type ]['ids']
			: array();

		return false !== array_search( $post_id, $excluded, true );

	}

	/**
	 * Sets the exclude setting on the generated post, mimicking the Search Exclude plugin's
	 * save_post_ids_to_search_exclude() method.
	 *
	 * @since   5.1.8
	 *
	 * @param   int    $post_id        Generated Post ID.
	 * @param   string $post_type      Generated Post Type.
	 * @param   bool   $exclude        Whether to exclude the post from the search.
	 */
	private function set_exclude_on_post( $post_id, $post_type, $exclude ) {

		$post_ids = array( intval( $post_id ) );
		$entries  = QuadLayers\QLSE\Models\Settings::instance()->get()->get( 'entries' );

		if ( ! isset( $entries[ $post_type ] ) ) {
			$entries[ $post_type ] = array(
				'all' => false,
				'ids' => array(),
			);
		}

		$excluded = isset( $entries[ $post_type ]['ids'] ) && is_array( $entries[ $post_type ]['ids'] )
		? $entries[ $post_type ]['ids']
		: array();

		if ( $exclude ) {
			$entries[ $post_type ]['ids'] = array_values( array_unique( array_merge( $excluded, $post_ids ) ) );
		} else {
			$entries[ $post_type ]['ids'] = array_values( array_diff( $excluded, $post_ids ) );
		}

		QuadLayers\QLSE\Models\Settings::instance()->save( array( 'entries' => $entries ) );

	}

}
