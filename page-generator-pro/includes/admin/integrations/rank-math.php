<?php
/**
 * Rank Math Integration Class
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Registers Rank Math as a Plugin integration:
 * - Registers metaboxes in Content Groups
 * - Register as an overwrite section in Content Groups
 * - Copy / don't copy metadata to generated Pages, depending on if the integration is active
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 2.9.0
 */
class Page_Generator_Pro_Rank_Math extends Page_Generator_Pro_Integration {

	/**
	 * Holds the base object.
	 *
	 * @since   2.9.0
	 *
	 * @var     object
	 */
	public $base;

	/**
	 * Holds settings for the Group we're editing
	 *
	 * @since   3.5.4
	 *
	 * @var     array
	 */
	public $settings = array();

	/**
	 * Constructor
	 *
	 * @since   2.9.0
	 *
	 * @param   object $base    Base Plugin Class.
	 */
	public function __construct( $base ) {

		// Store base class.
		$this->base = $base;

		// Set Plugin.
		$this->plugin_folder_filename = 'seo-by-rank-math/rank-math.php';

		// Set Meta Keys used by this Plugin.
		$this->meta_keys = array(
			'/^rank_math_(.*)/i',
		);

		// Set Overwrite Setting's Key used by this Plugin.
		$this->overwrite_section = 'rank_math';

		// Remove Action Metaboxes on Content Groups, and insert Rank Math specific Action Metaboxes.
		add_action( 'page_generator_pro_groups_ui_add_meta_boxes', array( $this, 'replace_action_metaboxes' ), 10, 2 );

		// Add Overwrite Section if Rank Math enabled.
		add_filter( 'page_generator_pro_common_get_content_overwrite_sections', array( $this, 'add_overwrite_section' ) );

		// Ignore Rank Math meta keys if overwriting is disabled for Rank Math.
		add_filter( 'page_generator_pro_generate_set_post_meta_ignored_keys', array( $this, 'prevent_post_meta_copy_to_generated_content' ), 10, 4 );

		// Remove Plugin data from Group Settings if Plugin isn't active on Generation.
		add_filter( 'page_generator_pro_groups_get_settings_remove_orphaned_settings', array( $this, 'remove_orphaned_settings' ) );

	}

	/**
	 * Remove Action Metaboxes on Content Groups, and insert Rank Math specific Action Metaboxes
	 * which have Test and Generate buttons as links instead of submit buttons, due to Rank Math
	 * 1.0.78+ wrongly removing submit buttons as the Content Group form is submitted.
	 *
	 * @since   3.5.4
	 *
	 * @param   Page_Generator_Pro_PostType $post_type_instance     Post Type Instance.
	 * @param   bool                        $is_gutenberg_page      If Gutenberg Editor is used on this Content Group.
	 */
	public function replace_action_metaboxes( $post_type_instance, $is_gutenberg_page ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter

		// Don't need to do anything if we're not in the admin interface.
		if ( ! is_admin() ) {
			return;
		}

		// Bail if the Gutenberg Editor is active, as the action metaboxes for Gutenberg already
		// use links for Test and Generate buttons, so there's no conflict.
		if ( $is_gutenberg_page ) {
			return;
		}

		// Bail if Rank Math isn't active.
		if ( ! $this->is_active() ) {
			return;
		}

		// Remove action metaboxes.
		remove_meta_box( $this->base->get_class( 'post_type' )->post_type_name . '-actions', $this->base->get_class( 'post_type' )->post_type_name, 'side' );
		remove_meta_box( $this->base->get_class( 'post_type' )->post_type_name . '-actions-bottom', $this->base->get_class( 'post_type' )->post_type_name, 'side' );

		// Add action metaboxes for Rank Math.
		add_meta_box(
			$this->base->get_class( 'post_type' )->post_type_name . '-actions',
			__( 'Actions', 'page-generator-pro' ),
			array( $this, 'output_meta_box_actions_top' ),
			$this->base->get_class( 'post_type' )->post_type_name,
			'side',
			'high'
		);
		add_meta_box(
			$this->base->get_class( 'post_type' )->post_type_name . '-actions-bottom',
			__( 'Actions', 'page-generator-pro' ),
			array( $this, 'output_meta_box_actions_bottom' ),
			$this->base->get_class( 'post_type' )->post_type_name,
			'side',
			'low'
		);

	}

	/**
	 * Outputs the Actions Sidebar Top Meta Box, with Test and Generate buttons
	 * as links instead of submit buttons, due to Rank Math 1.0.78+ wrongly
	 * removing these buttons as the Content Group form is submitted.
	 *
	 * @since   3.5.4
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

		// Define actions (Test, Generate via Browser, Generate via Server), if this Group generates content.
		if ( $this->base->get_class( 'groups' )->generates_content( $group_id ) ) {
			$generate_actions = $this->base->get_class( 'groups' )->get_actions_links( $group_id, 0, 'button button-primary button-large' );
		}

		// Load view.
		include $this->base->plugin->folder . 'views/admin/generate-meta-box-actions-rank-math.php';

	}

	/**
	 * Outputs the Actions Sidebar Bottom Meta Box, with Test and Generate buttons
	 * as links instead of submit buttons, due to Rank Math 1.0.78+ wrongly
	 * removing these buttons as the Content Group form is submitted.
	 *
	 * @since   3.5.4
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

		// Define actions (Test, Generate via Browser, Generate via Server), if this Group generates content.
		if ( $this->base->get_class( 'groups' )->generates_content( $group_id ) ) {
			$generate_actions = $this->base->get_class( 'groups' )->get_actions_links( $group_id, 0, 'button button-primary button-large' );
		}

		// Load view.
		include $this->base->plugin->folder . 'views/admin/generate-meta-box-actions-rank-math.php';

	}

	/**
	 * Defines available content overwrite sections.
	 *
	 * @since   2.9.0
	 *
	 * @param   array $sections    Content Overwrite Sections.
	 * @return  array                Content Overwrite Sections
	 */
	public function add_overwrite_section( $sections ) {

		// Bail if Rank Math isn't active.
		if ( ! $this->is_active() ) {
			return $sections;
		}

		// Add Rank Math.
		$sections[ $this->overwrite_section ] = __( 'Rank Math', 'page-generator-pro' );

		// Return.
		return $sections;

	}

	/**
	 * Removes orphaned Rank Math metadata in the Group Settings during Generation,
	 * if Rank Math is not active
	 *
	 * @since   3.3.7
	 *
	 * @param   array $settings   Group Settings.
	 * @return  array               Group Settings
	 */
	public function remove_orphaned_settings( $settings ) {

		// Don't remove settings if the Plugin is active.
		if ( $this->is_active() ) {
			return $settings;
		}

		// Remove Rank Math Meta Keys from the Group Settings during Generation.
		return $this->remove_orphaned_settings_metadata( $settings, $this->meta_keys );

	}

}
