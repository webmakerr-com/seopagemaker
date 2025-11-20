<?php
/**
 * All in One Video Gallery Integration Class
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Registers All in One Video Gallery as a Plugin integration:
 * - Enable meta boxes on Content Groups
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 3.8.2
 */
class Page_Generator_Pro_All_In_One_Video_Gallery extends Page_Generator_Pro_Integration {

	/**
	 * Holds the base object.
	 *
	 * @since   3.8.2
	 *
	 * @var     object
	 */
	public $base;

	/**
	 * Constructor
	 *
	 * @since   3.8.2
	 *
	 * @param   object $base    Base Plugin Class.
	 */
	public function __construct( $base ) {

		// Store base class.
		$this->base = $base;

		// Set Plugin.
		$this->plugin_folder_filename = array(
			'all-in-one-video-gallery/all-in-one-video-gallery.php',
		);

		// Register metaboxes for Content Groups.
		add_action( 'add_meta_boxes', array( $this, 'register_meta_boxes' ) );

		add_action( 'save_post', array( $this, 'save_meta_data' ), 10, 2 );

		// Define metaboxes that should be displayed.
		add_filter( 'page_generator_pro_groups_ui_get_post_type_conditional_metaboxes', array( $this, 'get_post_type_conditional_metaboxes' ) );

		// We don't remove orphaned data, as the post meta key names are very generic (e.g.
		// type, facebook, vimeo), which may result in not copying metadata of the same name
		// from another Plugin / Custom Field / ACF solution.
	}

	/**
	 * Registers All in One Video Gallery's meta boxes against Content Groups.
	 *
	 * @since   3.8.2
	 */
	public function register_meta_boxes() {

		// Bail if Plugin is not active.
		if ( ! $this->is_active() ) {
			return;
		}

		// Load admin class.
		$videos = new AIOVG_Admin_Videos();

		add_meta_box(
			'aiovg-video-sources',
			__( 'Video Sources', 'page-generator-pro' ),
			array( $videos, 'display_meta_box_video_sources' ),
			'page-generator-pro',
			'normal',
			'high'
		);

		add_meta_box(
			'aiovg-video-tracks',
			__( 'Subtitles', 'page-generator-pro' ),
			array( $videos, 'display_meta_box_video_tracks' ),
			'page-generator-pro',
			'normal',
			'high'
		);

	}

	/**
	 * Save All in One Video Gallery metadata if saving a Content Group.
	 *
	 * @since   3.8.2
	 *
	 * @param   int     $post_id    Post ID.
	 * @param   WP_Post $post       Post.
	 */
	public function save_meta_data( $post_id, $post ) {

		// Bail if Plugin is not active.
		if ( ! $this->is_active() ) {
			return;
		}

		// Bail if not saving a Content Group.
		if ( $post->post_type !== 'page-generator-pro' ) {
			return;
		}

		// Modify the Post Type, so that AIOVG_Admin_Videos::save_meta_data will save the POSTed data.
		$post->post_type = 'aiovg_videos';

		// Load admin class.
		$videos = new AIOVG_Admin_Videos();

		// Pass the request to the All in One Video Gallery save_meta_data() function.
		$videos->save_meta_data( $post_id, $post );

	}

	/**
	 * Define metaboxes that should only display based on the value of Publish > Post Type
	 * in the Content Groups UI.
	 *
	 * @since   3.8.2
	 *
	 * @param   array $metaboxes  Metabox ID Keys and Post Type Values array.
	 * @return  array               Metabox ID Keys and Post Type Values array
	 */
	public function get_post_type_conditional_metaboxes( $metaboxes ) {

		return array_merge(
			$metaboxes,
			array(
				'aiovg-video-sources' => array(
					'aiovg_videos',
				),
				'aiovg-video-tracks'  => array(
					'aiovg_videos',
				),
			)
		);

	}

}
