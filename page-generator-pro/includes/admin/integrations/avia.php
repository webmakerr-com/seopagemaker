<?php
/**
 * Avia (Enfold) Integration Class
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Registers Avia (Enfold)'s Page Builder as a Plugin integration:
 * - Display metaboxes on Content Groups
 * - Register as an overwrite section in Content Groups
 * - Copy / don't copy metadata to generated Pages, depending on if the integration is active
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 3.3.7
 */
class Page_Generator_Pro_Avia extends Page_Generator_Pro_Integration {

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
		$this->theme_name = 'Enfold';

		// Set Meta Keys used by this Theme.
		$this->meta_keys = array(
			'/^_aviaLayoutBuilder(.*)/i',
			'/^_avia_(.*)/i',
			'/^_av_(.*)/i',
			'/^_portfolio_(.*)/i',
			'/^_preview_(.*)/i',
			'_aviaLayoutBuilderCleanData',
			'layout',
			'sidebar',
			'footer',
			'header_title_bar',
			'header_transparency',
			'breadcrumb_parent',
		);

		add_filter( 'avf_builder_boxes', array( $this, 'register_avia_layout_builder_meta_boxes' ), 10, 1 );
		add_filter( 'avf_alb_supported_post_types', array( $this, 'register_avia_layout_builder_supported_post_types' ) );
		add_filter( 'page_generator_pro_generate_content_settings', array( $this, 'avia_remove_builder_data_on_generation' ), 10, 1 );
		add_action( 'page_generator_pro_generate_content_after_insert_update_post', array( $this, 'avia_add_builder_data_after_generation' ), 10, 3 );

		// Remove Page Builder data from Group Settings if overwriting content is disabled, and an existing generated page already exists.
		add_filter( 'page_generator_pro_generate_remove_content_from_settings_ignored_keys', array( $this, 'remove_post_meta_from_content_group' ), 10, 2 );

		// Remove Plugin data from Group Settings if Plugin isn't active on Generation.
		add_filter( 'page_generator_pro_groups_get_settings_remove_orphaned_settings', array( $this, 'remove_orphaned_settings' ) );

	}

	/**
	 * Registers all available Avia Layout Builder Metaboxes against Page Generator Pro's
	 * Content Groups, so that they're available for configuration when editing a
	 * Content Group.
	 *
	 * If we don't do this, the user can't configure e.g. Page Layout for generated Pages.
	 *
	 * @since   1.5.6
	 *
	 * @param   array $meta_boxes     Meta Boxes.
	 * @return  array                   Meta Boxes
	 */
	public function register_avia_layout_builder_meta_boxes( $meta_boxes ) {

		// Bail if no Meta Boxes exist.
		if ( empty( $meta_boxes ) ) {
			return $meta_boxes;
		}

		// Define the Avia Meta Box IDs.
		$avia_meta_box_ids = array(
			'avia_builder',
			'avia_sc_parser',
			'layout',
			'preview',
			'hierarchy',
		);

		/**
		 * Defines the Avia Meta Boxes to include in Content Groups.
		 *
		 * @since   1.5.6
		 *
		 * @param   array   $avia_meta_box_ids      Avia Meta Box IDs to include in Content Groups.
		 * @param   array   $meta_boxes             Meta Boxes.
		 */
		$avia_meta_box_ids = apply_filters( 'page_generator_pro_pagebuilders_register_avia_layout_builder_support', $avia_meta_box_ids, $meta_boxes );

		// Iterate through the existing Meta Boxes, to find the Avia specific ones.
		foreach ( $meta_boxes as $key => $meta_box ) {
			// Skip if the ID isn't one we are looking for.
			if ( ! in_array( $meta_box['id'], $avia_meta_box_ids, true ) ) {
				continue;
			}

			// Add Page Generator Pro's Groups to the 'page' array.
			$meta_boxes[ $key ]['page'][] = 'page-generator-pro';
		}

		// Register filter to replace single quotes in Keywords, as these will result in no output if retained in the generated page.
		add_filter( 'page_generator_pro_generate_get_keywords_terms', array( $this, 'avia_replace_single_quotes_in_keywords' ) );

		// Return.
		return $meta_boxes;

	}

	/**
	 * Allows the Avia Layout Builder (which comes with the Enfold Theme) to inject
	 * its Page Builder into Page Generator Pro's Groups when the Block Editor is used
	 * (i.e. the Classic Editor isn't enabled)
	 *
	 * @since   2.3.4
	 *
	 * @param   array $post_types     Post Types.
	 * @return  array                   Post Types
	 */
	public function register_avia_layout_builder_supported_post_types( $post_types ) {

		$post_types[] = 'page-generator-pro';
		return $post_types;

	}

	/**
	 * Replaces single quotation marks with encoded quotation marks in Keywords, so that Avia Layout Builder
	 * doesn't break and fail to render the generated page when a Keyword is used in a Content Element.
	 *
	 * Avia uses JS to replace single quotes with encoded quotation marks in real time, but we never call this function
	 * if a Keyword is used.
	 *
	 * @since   2.7.5
	 *
	 * @param   array $keywords_terms     Keyword(s) and Term(s).
	 * @return  array                       Keyword(s) and Term(s)
	 */
	public function avia_replace_single_quotes_in_keywords( $keywords_terms ) {

		foreach ( $keywords_terms as $keyword => $term ) {
			$keywords_terms[ $keyword ] = str_replace( "'", '‘', $term );
		}

		return $keywords_terms;

	}

	/**
	 * Removes the Group's Avia Layout Builder Post Meta Data immediately prior to the generation routine
	 * running, as this information is also stored in the Post Content.  In turn, this prevents duplicated
	 * effort of shortcode processing across both the Post Content and _aviaLayoutBuilderCleanData Post Meta,
	 * which would result in e.g. duplicate Media Library Images if using the Media Library shortcode
	 *
	 * @since   2.8.5
	 *
	 * @param   array $settings       Group Settings.
	 * @return  array                   Settings
	 */
	public function avia_remove_builder_data_on_generation( $settings ) {

		// Just return the Group settings if no Avia Builder Data exists.
		if ( ! isset( $settings['post_meta'] ) ) {
			return $settings;
		}
		if ( ! isset( $settings['post_meta']['_aviaLayoutBuilderCleanData'] ) ) {
			return $settings;
		}

		// Remove Avia Builder Data, as it's in the Post Content.
		unset( $settings['post_meta']['_aviaLayoutBuilderCleanData'] );

		// Return.
		return $settings;

	}

	/**
	 * Adds Avia Layout Builder Post Meta Data immediate after the Page is generated, as this information was removed
	 * in avia_remove_builder_data_on_generation() and is stored in the Post Content.
	 *
	 * @since   2.8.5
	 *
	 * @param   int   $post_id        Post ID.
	 * @param   int   $group_id       Group ID.
	 * @param   array $settings       Group Settings.
	 */
	public function avia_add_builder_data_after_generation( $post_id, $group_id, $settings ) {

		// Don't add builder data if not active.
		if ( ! $this->is_theme_active() ) {
			return;
		}

		// Get original settings which will include _aviaLayoutBuilderCleanData.
		$settings = $this->base->get_class( 'groups' )->get_settings( $group_id );

		// Bail if an error occured.
		if ( is_wp_error( $settings ) ) {
			return;
		}

		// Bail if Avia Builder isn't in the Post Meta.
		if ( ! isset( $settings['post_meta'] ) ) {
			return;
		}
		if ( ! isset( $settings['post_meta']['_aviaLayoutBuilderCleanData'] ) ) {
			return;
		}

		// Copy generated post's content to the _aviaLayoutBuilderCleanData Post Meta data on the generated post,
		// as Avia Builder data was present in the Content Group.
		update_post_meta( $post_id, '_aviaLayoutBuilderCleanData', get_post_field( 'post_content', $post_id ) );

	}

	/**
	 * Removes orphaned Avia metadata in the Group Settings during Generation,
	 * if Avia is not active
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

		// Remove Avia Meta Keys from the Group Settings during Generation.
		return $this->remove_orphaned_settings_metadata( $settings, $this->meta_keys );

	}

}
