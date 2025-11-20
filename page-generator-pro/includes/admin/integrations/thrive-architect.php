<?php
/**
 * Thrive Architect Integration Class
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Registers Thrive Architect as a Plugin integration:
 * - Registers TinyMCE Plugins in Thrive Builder
 * - Copy / don't copy metadata to generated Pages, depending on if the integration is active
 * - Copy metadata to generated Pages
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 3.3.7
 */
class Page_Generator_Pro_Thrive_Architect extends Page_Generator_Pro_Integration {

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

		// Set Theme.
		$this->theme_name = 'Thrive Theme Builder';

		// Set Plugin.
		$this->plugin_folder_filename = array(
			'thrive-visual-editor/thrive-visual-editor.php',
		);

		// Set Meta Keys used by this Plugin.
		$this->meta_keys = array(
			'/^_tve_(.*)/i',
			'/^tve_(.*)/i',
			'/^thrive_(.*)/i',
			'tcb_editor_enabled',
			'tcb2_ready',
		);

		add_action( 'tcb_hook_template_redirect', array( $this, 'register_thrive_architect_wp_editor_support' ) );
		add_filter( 'page_generator_pro_generate_content_settings', array( $this, 'thrive_architect_remove_builder_data_on_generation' ), 10, 1 );
		add_action( 'page_generator_pro_generate_content_finished', array( $this, 'thrive_architect_add_builder_data_after_generation' ), 10, 1 );

		// Remove Page Builder data from Group Settings if overwriting content is disabled, and an existing generated page already exists.
		add_filter( 'page_generator_pro_generate_remove_content_from_settings_ignored_keys', array( $this, 'remove_post_meta_from_content_group' ), 10, 2 );

		// Remove Plugin data from Group Settings if Plugin isn't active on Generation.
		add_filter( 'page_generator_pro_groups_get_settings_remove_orphaned_settings', array( $this, 'remove_orphaned_settings' ) );

	}

	/**
	 * Thrive Architect: Enqueue CSS, JS and re-register TinyMCE Plugins when editing a Content Group, so TinyMCE Plugins etc. work,
	 * as Thrive removes actions hooked to admin_enqueue_scripts, wp_enqueue_scripts, mce_external_plugins, mce_buttons
	 *
	 * @since   2.5.8
	 */
	public function register_thrive_architect_wp_editor_support() {

		// Load Plugin CSS/JS.
		$this->base->get_class( 'admin' )->admin_scripts_css();

		// Add filters to register TinyMCE Plugins.
		// Low priority ensures this works with Frontend Page Builders.
		add_filter( 'mce_external_plugins', array( $this->base->get_class( 'editor' ), 'register_tinymce_plugins' ), 99999 );
		add_filter( 'mce_buttons', array( $this->base->get_class( 'editor' ), 'register_tinymce_buttons' ), 99999 );

	}

	/**
	 * Removes the Group's Thrive Architect Builder Post Content and some Post Meta immediately prior to the generation routine
	 * running, which might be left by e.g. use of a Landing Page, or change of a Landing Page (Thrive doesn't seem to delete
	 * old Post Metadata).
	 *
	 * This prevents duplicated effort of shortcode processing across Post Content, tve_updated_post, tve_updated_post_*
	 * and tve_content_before_more_* which would result in duplicate Media Library Images if using the Media Library shortcode
	 *
	 * @since   2.8.5
	 *
	 * @param   array $settings       Group Settings.
	 */
	public function thrive_architect_remove_builder_data_on_generation( $settings ) {

		// Bail if Thrive Architect isn't active.
		if ( ! defined( 'TVE_IN_ARCHITECT' ) ) {
			return $settings;
		}

		// Just return the Group settings if no Thirve Architect Data exists.
		if ( ! isset( $settings['post_meta'] ) ) {
			return $settings;
		}
		if ( ! isset( $settings['post_meta']['tve_updated_post'] ) ) {
			return $settings;
		}

		// Remove Post Content and Thrive Architect "Before More" Data, as neither are used.
		$settings['content'] = '';
		unset( $settings['post_meta']['tve_content_before_more'] );

		// If no Landing Page is used, remove any Landing Page Post Meta and return now.
		if ( ! isset( $settings['post_meta']['tve_landing_page'] ) ) {
			foreach ( $settings['post_meta'] as $meta_key => $meta_value ) {
				if ( strpos( $meta_key, 'tve_updated_post_' ) !== false ) {
					unset( $settings['post_meta'][ $meta_key ] );
				}
				if ( strpos( $meta_key, 'tve_content_before_more_' ) !== false ) {
					unset( $settings['post_meta'][ $meta_key ] );
				}
			}

			return $settings;
		}

		// Because a Landing Page Template is used, remove tve_updated_post and any tve_updated_post_* / tve_content_before_more_*
		// Post Meta that isn't related to the Landing Page Template.
		unset( $settings['post_meta']['tve_updated_post'] );
		foreach ( $settings['post_meta'] as $meta_key => $meta_value ) {
			// Skip if this Post Meta is the Thrive Landing Page data we need.
			if ( $meta_key === 'tve_updated_post_' . $settings['post_meta']['tve_landing_page'] ) {
				continue;
			}

			// Delete if Before More.
			if ( strpos( $meta_key, 'tve_content_before_more_' ) !== false ) {
				unset( $settings['post_meta'][ $meta_key ] );
				continue;
			}

			// Delete if Updated Post for Landing Page we're not using.
			if ( strpos( $meta_key, 'tve_updated_post_' ) !== false ) {
				unset( $settings['post_meta'][ $meta_key ] );
			}
		}

		// Return.
		return $settings;

	}

	/**
	 * Adds Thrive Architect Builder data to the Post Content after the Page is generated, as this information was removed
	 * in thrive_architect_remove_builder_data_on_generation() and is stored in the Post Meta.
	 *
	 * @since   2.8.5
	 *
	 * @param   int $post_id        Post ID.
	 */
	public function thrive_architect_add_builder_data_after_generation( $post_id ) {

		// Bail if Thrive Architect isn't active.
		if ( ! defined( 'TVE_IN_ARCHITECT' ) ) {
			return;
		}
		if ( ! function_exists( 'tve_get_post_meta' ) ) {
			return;
		}

		// Get content.
		$content = tve_get_post_meta( $post_id, 'tve_updated_post', true );
		if ( empty( $content ) ) {
			return;
		}

		// Copy tve_updated_post Post Meta to Post Content.
		wp_update_post(
			array(
				'ID'           => $post_id,
				'post_content' => $content,
			)
		);

	}

	/**
	 * Removes orphaned Thrive Architect metadata in the Group Settings during Generation,
	 * if Thrive Architect is not active
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

		// Don't remove settings if the Plugin is active.
		if ( $this->is_active() ) {
			return $settings;
		}

		// Remove Thrive Architect Meta Keys from the Group Settings during Generation.
		return $this->remove_orphaned_settings_metadata( $settings, $this->meta_keys );

	}

}
