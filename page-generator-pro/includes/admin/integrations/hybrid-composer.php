<?php
/**
 * Hybrid Composer Integration Class
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Registers Hybrid Composer as a Plugin integration:
 * - Enable Hybrid COmposer on Content Groups
 * - Copy / don't copy metadata to generated Pages, depending on if the integration is active
 * - Decode/encode Page Builder metadata when generating Pages
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 3.7.8
 */
class Page_Generator_Pro_Hybrid_Composer extends Page_Generator_Pro_Integration {

	/**
	 * Holds the base object.
	 *
	 * @since   3.7.8
	 *
	 * @var     object
	 */
	public $base;

	/**
	 * Constructor
	 *
	 * @since   3.7.8
	 *
	 * @param   object $base    Base Plugin Class.
	 */
	public function __construct( $base ) {

		// Store base class.
		$this->base = $base;

		// Set Plugin.
		$this->plugin_folder_filename = array(
			'hybrid-composer/index.php',
		);

		// Set Meta Keys used by this Plugin.
		$this->meta_keys = array(
			'hc-editor-mode',
		);

		add_filter( 'admin_body_class', array( $this, 'register_body_class' ) );

		// Register metaboxes for Content Groups.
		add_action( 'add_meta_boxes', array( $this, 'register_meta_boxes' ) );

		// Decode Group Content JSON.
		add_filter( 'page_generator_pro_generate_content_settings_before', array( $this, 'decode_content' ) );

		// Encode Group Content into JSON string with additional slashes.
		// We don't use page_generator_pro_generate_content_settings, as this is before shortcodes are rendered, resulting in invalid JSON.
		add_filter( 'page_generator_pro_generate_post_args', array( $this, 'encode_content' ) );

		// Remove Plugin data from Group Settings if Plugin isn't active on Generation.
		add_filter( 'page_generator_pro_groups_get_settings_remove_orphaned_settings', array( $this, 'remove_orphaned_settings' ) );

	}

	/**
	 * Add the post-type-page CSS class when creating or editing a Content Group,
	 * to force Hybrid Composer's composer.js to initialize.
	 *
	 * @since   3.7.8
	 *
	 * @param   string $classes    CSS Classes.
	 * @return  string              CSS Classes
	 */
	public function register_body_class( $classes ) {

		// Don't add a CSS class if the Plugin isn't active.
		if ( ! $this->is_active() ) {
			return $classes;
		}

		// Get screen.
		$screen = $this->base->get_class( 'screen' )->get_current_screen();

		// Don't add a CSS class if we're not creating or editing a Content Group.
		if ( $screen['screen'] !== 'content_groups' ) {
			return $classes;
		}
		if ( $screen['section'] !== 'edit' ) {
			return $classes;
		}

		// Add CSS class to force composer.js to load.
		$classes .= ' post-type-page';
		return $classes;

	}

	/**
	 * Registers Hybrid Composer's meta boxes against Content Groups.
	 *
	 * @since   3.7.8
	 */
	public function register_meta_boxes() {

		// Bail if expected constant is not defined.
		if ( ! defined( 'HC_PLUGIN_PATH' ) ) {
			return;
		}

		add_meta_box( 'hybrid-composer', __( 'Page editor', 'page-generator-pro' ), array( $this, 'register_meta_box_composer' ), 'page-generator-pro', 'normal', 'high' );
		add_meta_box( 'hybrid-composer-side', __( 'Composer options', 'page-generator-pro' ), array( $this, 'register_meta_box_composer_side' ), 'page-generator-pro', 'side', 'high' );
		add_meta_box( 'template-meta-boxes', __( 'Page components', 'page-generator-pro' ), array( $this, 'register_meta_box_templates' ), 'page-generator-pro', 'side', 'low' );

	}

	/**
	 * Registers Hybrid Composer's composer meta box against Content Groups.
	 *
	 * @since   3.7.8
	 */
	public function register_meta_box_composer() {

		// Bail if expected constant is not defined.
		if ( ! defined( 'HC_PLUGIN_PATH' ) ) {
			return;
		}

		include_once HC_PLUGIN_PATH . '/admin/composer.php';

	}

	/**
	 * Registers Hybrid Composer's sidebar meta box against Content Groups.
	 *
	 * @since   3.7.8
	 */
	public function register_meta_box_composer_side() {

		// Bail if expected function is not defined.
		if ( ! function_exists( 'hc_block_composer_side' ) ) {
			return;
		}

		hc_block_composer_side();

	}

	/**
	 * Registers Hybrid Composer's templates meta box against Content Groups.
	 *
	 * @since   3.7.8
	 */
	public function register_meta_box_templates() {

		// Bail if expected function is not defined.
		if ( ! function_exists( 'hc_block_templates_meta_boxes' ) ) {
			return;
		}

		hc_block_templates_meta_boxes();

	}

	/**
	 * Decodes the Content Group's content from JSON into an object, immediately prior to generation.
	 *
	 * @since   3.7.8
	 *
	 * @param   array $settings   Content Group Settings.
	 * @return  array               Content Group Settings
	 */
	public function decode_content( $settings ) {

		// Bail if Plugin isn't active.
		if ( ! $this->is_active() ) {
			return $settings;
		}

		// JSON decode Content Group's content, as this is where Hybrid Composer stores its page builder data.
		// This then provides an object/array that the generation routine can iterate through to replace keywords,
		// spintax etc.
		$settings['content'] = json_decode( $settings['content'] );

		// Remove default filter to convert Gutenberg Blocks to Shortcode Blocks.
		remove_filter( 'page_generator_pro_generate_content_settings', array( $this->base->get_class( 'gutenberg' ), 'convert_blocks_to_shortcode_blocks' ) );

		// Return.
		return $settings;

	}

	/**
	 * Encodes the Content Group's content from an object into JSON during the generation routine,
	 * immediately before the Page is created/updated via wp_insert_post() / wp_update_post().
	 *
	 * @since   3.7.8
	 *
	 * @param   array $post_args    wp_insert_post() / wp_update_post() arguments.
	 * @return  array               wp_insert_post() / wp_update_post() arguments
	 */
	public function encode_content( $post_args ) {

		// Bail if Plugin isn't active.
		if ( ! $this->is_active() ) {
			return $post_args;
		}

		// Bail if the content is already a string.
		if ( is_string( $post_args['post_content'] ) ) {
			return $post_args;
		}

		// JSON encode Content Group's content, as this is where Hybrid Composer stores its page builder data.
		// addslashes() is required as wp_insert_post / wp_update_post will then strip slashes again.
		$post_args['post_content'] = addslashes( wp_json_encode( $post_args['post_content'] ) );

		// Return.
		return $post_args;

	}

	/**
	 * Removes orphaned Hybrid Composer metadata in the Group Settings during Generation,
	 * if Hybrid Composer is not active.
	 *
	 * @since   3.7.8
	 *
	 * @param   array $settings   Group Settings.
	 * @return  array               Group Settings
	 */
	public function remove_orphaned_settings( $settings ) {

		// Don't remove settings if the Plugin is active.
		if ( $this->is_active() ) {
			return $settings;
		}

		// Remove Hybrid Composer Meta Keys from the Group Settings during Generation.
		return $this->remove_orphaned_settings_metadata( $settings, $this->meta_keys );

	}

}
