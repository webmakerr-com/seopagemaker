<?php
/**
 * TheBuilt Theme Integration Class
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Registers TheBuilt Theme as a Plugin integration:
 * - Registers metaboxes in Content Groups
 * - Copy / don't copy metadata to generated Pages, depending on if the integration is active
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 3.3.7
 */
class Page_Generator_Pro_TheBuilt extends Page_Generator_Pro_Integration {

	/**
	 * Holds the base object.
	 *
	 * @since   3.3.7
	 *
	 * @var     object
	 */
	public $base;

	/**
	 * Constructor
	 *
	 * @since   3.3.7
	 *
	 * @param   object $base    Base Plugin Class.
	 */
	public function __construct( $base ) {

		// Store base class.
		$this->base = $base;

		// Set Theme Name.
		$this->theme_name = 'TheBuilt';

		// Set Meta Keys used by this Theme.
		$this->meta_keys = array(
			'_post_transparent_header_value',
			'_post_notdisplaytitle_value',
			'_post_socialshare_disable_value',
			'_post_sidebarposition_value',
			'_post_bgcolor_value',
			'_page_sidebarposition_value',
			'_page_notdisplaytitle_value',
			'_page_transparent_header_value',
			'_page_stick_footer_value',
			'_page_bgcolor_value',
			'_page_class_value',
		);

		add_filter( 'init', array( $this, 'register_thebuilt_support' ) );
		add_filter( 'page_generator_pro_groups_ui_get_post_type_conditional_metaboxes', array( $this, 'get_post_type_conditional_metaboxes_thebuilt' ) );

		// Remove data from Group Settings if Theme isn't active on Generation.
		add_filter( 'page_generator_pro_groups_get_settings_remove_orphaned_settings', array( $this, 'remove_orphaned_settings' ) );

	}

	/**
	 * Calls TheBuilt Theme Addons thebuilt_pages_settings_box() and thebuilt_post_settings_box() functions,
	 * which registers TheBuilt Theme Addon Meta Boxes when creating or editing a Content Group.
	 *
	 * @since   2.3.6
	 */
	public function register_thebuilt_support() {

		// Bail if the thebuilt_pages_settings_box function doesn't exist.
		if ( ! function_exists( 'thebuilt_pages_settings_box' ) ) {
			return;
		}

		add_action( 'add_meta_boxes', array( $this, 'register_thebuilt_metaboxes' ) );

	}

	/**
	 * Registers TheBuilt Theme Addons Metaboxes on Page Generator Pro's Groups
	 *
	 * @since   2.3.6
	 */
	public function register_thebuilt_metaboxes() {

		add_meta_box(
			'thebuilt_pages_settings_box',
			esc_html__( 'Page settings', 'page-generator-pro' ),
			'thebuilt_pages_settings_inner_box',
			'page-generator-pro',
			'normal',
			'high'
		);
		add_meta_box(
			'thebuilt_post_settings_box',
			esc_html__( 'Post settings', 'page-generator-pro' ),
			'thebuilt_post_settings_inner_box',
			'page-generator-pro',
			'normal',
			'high'
		);

	}

	/**
	 * Define TheBuilt Theme Addons Metaboxes that should only display based on the value of Publish > Post Type
	 * in the Content Groups UI
	 *
	 * @since   2.8.6
	 *
	 * @param   array $metaboxes  Metabox ID Keys and Post Type Values array.
	 * @return  array               Metabox ID Keys and Post Type Values array
	 */
	public function get_post_type_conditional_metaboxes_thebuilt( $metaboxes ) {

		return array_merge(
			$metaboxes,
			array(
				'thebuilt_pages_settings_box' => array(
					'page',
				),
				'thebuilt_post_settings_box'  => array(
					'post',
				),
			)
		);

	}

	/**
	 * Removes orphaned TheBuilt metadata in the Group Settings during Generation,
	 * if TheBuilt is not active
	 *
	 * @since   3.3.7
	 *
	 * @param   array $settings   Group Settings.
	 * @return  array               Group Settings
	 */
	public function remove_orphaned_settings( $settings ) {

		// Don't remove settings if the Theme is active.
		if ( $this->is_theme_active() ) {
			return $settings;
		}

		// Remove TheBuilt Meta Keys from the Group Settings during Generation.
		return $this->remove_orphaned_settings_metadata( $settings, $this->meta_keys );

	}

}
