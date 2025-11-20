<?php
/**
 * Metabox.io Integration Class
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Registers Metabox.io as a Plugin integration:
 * - Register metabox(es) on Content Groups
 *
 * Themes that use this Plugin to register Meta Boxes + Custom Fields e.g. Construction Theme, Wize Law Theme.
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 3.3.7
 */
class Page_Generator_Pro_Metabox_IO extends Page_Generator_Pro_Integration {

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

		// Themes that bundle Metabox.io will store their meta keys in the post meta as mb_<key>.
		// Users just using the Metabox.io Plugin will store their meta keys in the post meta as <key>, with no prefix.
		$this->meta_keys = array(
			'/^mb_(.*)/i',
		);

		// Set Plugin.
		$this->plugin_folder_filename = array(
			'meta-box/meta-box.php',
			'meta-box-aio/meta-box-aio.php',
			'meta-box-lite/meta-box-lite.php',
		);

		// Add Overwrite Section if Metabox.io enabled.
		add_filter( 'page_generator_pro_common_get_content_overwrite_sections', array( $this, 'add_overwrite_section' ) );

		// Ignore Metabox.io meta keys if overwriting is disabled for Metabox.io.
		add_filter( 'page_generator_pro_generate_set_post_meta_ignored_keys', array( $this, 'prevent_post_meta_copy_to_generated_content' ), 10, 4 );

		// Themes: Register Metabox.io support for Themes.
		add_filter( 'rwmb_meta_boxes', array( $this, 'register_meta_box_io_support' ), 9999 );

		// Themes: Remove Metabox.io data from Group Settings if Theme isn't active on Generation.
		add_filter( 'page_generator_pro_groups_get_settings_remove_orphaned_settings', array( $this, 'remove_orphaned_settings' ) );
	}

	/**
	 * Defines available content overwrite sections.
	 *
	 * @since   5.3.1
	 *
	 * @param   array $sections    Content Overwrite Sections.
	 * @return  array                Content Overwrite Sections
	 */
	public function add_overwrite_section( $sections ) {

		// Bail if Metabox.io isn't active.
		if ( ! $this->is_active() ) {
			return $sections;
		}

		// Add Metabox.io Field Groups registered to Content Groups.
		$meta_box_content_groups = $this->get_meta_box_content_groups();

		// Bail if no Metabox.io Field Groups are assigned to Content Groups.
		if ( ! $meta_box_content_groups ) {
			return $sections;
		}

		// Add Metabox.io Field Groups.
		foreach ( $meta_box_content_groups as $group_key => $label ) {
			/* translators: Group Label, defined in Metabox.io Field Group */
			$sections[ 'meta_box_' . $group_key ] = sprintf( __( 'Meta Box: %s', 'page-generator-pro' ), $label );
		}

		// Return.
		return $sections;

	}

	/**
	 * Adds Metabox.io Post Meta Keys to the array of excluded Post Meta Keys if Metabox.io
	 * metadata should not be overwritten on Content Generation
	 *
	 * @since   5.3.1
	 *
	 * @param   array $ignored_keys   Ignored Keys (preg_match() compatible regex expressions are supported).
	 * @param   int   $post_id        Generated Post ID.
	 * @param   array $settings       Group Settings.
	 * @param   array $post_args      wp_insert_post() / wp_update_post() arguments.
	 * @return  array   $ignored_keys   Ignored Keys
	 */
	public function prevent_post_meta_copy_to_generated_content( $ignored_keys, $post_id, $settings, $post_args ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter

		// Bail if Metabox.io isn't active.
		if ( ! $this->is_active() ) {
			return $ignored_keys;
		}

		// Bail if we're not overwriting an existing generated Page.
		if ( ! isset( $post_args['ID'] ) ) {
			return $ignored_keys;
		}

		// Get Metabox.io Field Groups registered to Content Groups.
		$meta_box_content_groups = $this->get_meta_box_content_groups();

		// Bail if no Metabox.io Field Groups are assigned to Content Groups.
		if ( ! $meta_box_content_groups ) {
			return $ignored_keys;
		}

		// For each Metabox.io Field Group, ignore its fields if overwriting is disabled.
		foreach ( $meta_box_content_groups as $group_key => $label ) {
			// Determine if we want to replace this Metabox.io Field Group's Fields data.
			$overwrite = ( ! array_key_exists( 'meta_box_' . $group_key, $settings['overwrite_sections'] ) ? false : true );
			if ( $overwrite ) {
				continue;
			}

			// We're not overwriting this Metabox.io Field Group's Fields, so add the Fields to the $ignored_keys array.
			$fields = $this->get_meta_box_field_group_field_ids( $group_key );

			// Skip if no fields exist in the Metabox.io Field Group.
			if ( ! count( $fields ) ) {
				continue;
			}

			// Add Metabox.io Field Group Fields to ignored keys.
			$ignored_keys = array_merge( $ignored_keys, $fields );
		}

		return $ignored_keys;

	}

	/**
	 * Returns Metabox.io Field Groups assigned to Content Groups
	 *
	 * @since   5.3.1
	 *
	 * @return  bool|array
	 */
	private function get_meta_box_content_groups() {

		// Get Metabox.io Field Groups.
		$meta_box_registry     = rwmb_get_registry( 'meta_box' );
		$meta_box_field_groups = $meta_box_registry->all();
		if ( ! count( $meta_box_field_groups ) ) {
			return false;
		}

		// Find Metabox.io Field Groups assigned to Content Groups.
		$matched_groups = array();
		foreach ( $meta_box_field_groups as $id => $meta_box_field_group ) {
			if ( ! in_array( 'page-generator-pro', $meta_box_field_group->meta_box['post_types'], true ) ) {
				continue;
			}

			$matched_groups[ $id ] = $meta_box_field_group->meta_box['title'];
		}

		// Return false if no Metabox.io Field Groups were found for Content Groups.
		if ( ! count( $matched_groups ) ) {
			return false;
		}

		// Return.
		return $matched_groups;

	}

	/**
	 * Returns the Metabox.io Field IDs for a given Metabox.io Field Group
	 *
	 * @since   5.3.1
	 *
	 * @param   int $id   Metabox.io Field Group ID.
	 * @return  array       Metabox.io Field IDs.
	 */
	private function get_meta_box_field_group_field_ids( $id ) {

		// Get the Metabox.io Field Group.
		$meta_box_field_group = rwmb_get_registry( 'meta_box' )->get( $id );

		// Bail if the Metabox.io Field Group doesn't exist.
		if ( ! $meta_box_field_group ) {
			return array();
		}

		// Bail if the Metabox.io Field Group doesn't have any fields.
		if ( ! is_array( $meta_box_field_group->meta_box['fields'] ) ) {
			return array();
		}

		// Build array of Metabox.io Field IDs.
		$fields = array();
		foreach ( $meta_box_field_group->meta_box['fields'] as $field ) {
			$fields[] = $field['id'];
		}

		// Return.
		return $fields;

	}

	/**
	 * Allows Metabox.io to register its metaboxes into Page Generator Pro (Themes that use this Plugin to register Metabox.io +
	 * Custom Fields e.g. Construction Theme, Wize Law Theme)
	 *
	 * @since   2.6.3
	 *
	 * @param   array $meta_boxes     Meta Boxes.
	 * @return  array                   Meta Boxes
	 */
	public function register_meta_box_io_support( $meta_boxes ) {

		// Bail if the Metabox.io Plugin is active.
		// Users can use the Metabox.io UI or PHP to display metaboxes on Content Groups, so we don't need to register them again.
		if ( $this->is_active() ) {
			return $meta_boxes;
		}

		// Bail if no metaboxes are registered.
		if ( ! is_array( $meta_boxes ) ) {
			return $meta_boxes;
		}
		if ( ! count( $meta_boxes ) ) {
			return $meta_boxes;
		}

		// Get Post Types that Page Generator Pro can generate content for.
		$supported_post_types = array_keys( $this->base->get_class( 'common' )->get_post_types() );

		// Add Meta Boxes to Page Generator Pro.
		foreach ( $meta_boxes as $index => $meta_box ) {
			// Some themes use 'pages', others use 'post_types', so we check for both array keys.
			if ( isset( $meta_box['pages'] ) && is_array( $meta_box['pages'] ) && count( $meta_box['pages'] ) > 0 ) {
				// If Content Groups are already a listed Post Type, it's likely this is configured using Metabox.io Plugin
				// and not a Theme. We don't need to add Content Groups to the post types again.
				if ( in_array( 'page-generator-pro', $meta_box['pages'], true ) ) {
					continue;
				}

				foreach ( $meta_box['pages'] as $post_type ) {
					if ( in_array( $post_type, $supported_post_types, true ) ) {
						// The meta box is used on a Post Type that Page Generator Pro can generate content for.
						// Add the Content Group Post Type to the meta box so that the meta box's fields are displayed
						// when editing a Content Group.
						$meta_boxes[ $index ]['pages'][] = 'page-generator-pro';
						break;
					}
				}
				continue;
			}

			if ( isset( $meta_box['post_types'] ) && is_array( $meta_box['post_types'] ) && count( $meta_box['post_types'] ) > 0 ) {
				// If Content Groups are already a listed Post Type, it's likely this is configured using Metabox.io Plugin
				// and not a Theme. We don't need to add Content Groups to the post types again.
				if ( in_array( 'page-generator-pro', $meta_box['post_types'], true ) ) {
					continue;
				}

				foreach ( $meta_box['post_types'] as $post_type ) {
					if ( in_array( $post_type, $supported_post_types, true ) ) {
						// The meta box is used on a Post Type that Page Generator Pro can generate content for.
						// Add the Content Group Post Type to the meta box so that the meta box's fields are displayed
						// when editing a Content Group.
						$meta_boxes[ $index ]['post_types'][] = 'page-generator-pro';
					}
				}
			}
		}

		return $meta_boxes;

	}

	/**
	 * Removes orphaned Metabox.io metadata in the Group Settings during Generation,
	 * if Metabox is not active.
	 *
	 * This won't apply to users who are using the Metabox.io Plugin, as their meta keys are stored in the post meta as <key>, with no prefix.
	 *
	 * @since   3.3.7
	 *
	 * @param   array $settings   Group Settings.
	 * @return  array               Group Settings
	 */
	public function remove_orphaned_settings( $settings ) {

		// Don't remove settings if Metabox.io is not bundled with a Theme.
		if ( ! class_exists( 'RWMB_Core' ) ) {
			return $settings;
		}

		// Remove Metabox.io Meta Keys from the Group Settings during Generation.
		return $this->remove_orphaned_settings_metadata( $settings, $this->meta_keys );

	}

}
