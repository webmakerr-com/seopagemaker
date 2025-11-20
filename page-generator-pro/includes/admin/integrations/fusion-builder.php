<?php
/**
 * Fusion Builder (Avada) Integration Class
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Registers Fusion Builder (Avada) as a Plugin integration:
 * - Enable Fusion Builder on Content Groups
 * - Register as an overwrite section in Content Groups
 * - Copy / don't copy metadata to generated Pages, depending on if the integration is active
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 3.3.7
 */
class Page_Generator_Pro_Fusion_Builder extends Page_Generator_Pro_Integration {

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

		// Set Plugin.
		$this->plugin_folder_filename = array(
			'fusion-builder/fusion-builder.php',
		);

		// Set Meta Keys used by this Plugin.
		$this->meta_keys = array(
			'_fusion',
			'_fusion_google_fonts',
			'fusion_builder_status',
		);

		// Register Support.
		add_filter( 'fusion_builder_allowed_post_types', array( $this, 'register_fusion_builder_support' ) );
		add_filter( 'fusion_builder_default_post_types', array( $this, 'register_fusion_builder_support' ) );

		// Enqueue scripts for Avada Live.
		add_action( 'fusion_enqueue_live_scripts', array( $this, 'register_avada_live_scripts_css' ) );

		// Remove Page Builder data from Group Settings if overwriting content is disabled, and an existing generated page already exists.
		add_filter( 'page_generator_pro_generate_remove_content_from_settings_ignored_keys', array( $this, 'remove_post_meta_from_content_group' ), 10, 2 );

		// Remove Plugin data from Group Settings if Plugin isn't active on Generation.
		add_filter( 'page_generator_pro_groups_get_settings_remove_orphaned_settings', array( $this, 'remove_orphaned_settings' ) );

		// Replace Keywords in Global Elements when viewing a generated Page.
		add_filter( 'fusion_element_column_content', array( $this, 'frontend_replace_keywords' ), 10, 3 );

	}

	/**
	 * Allows Fusion Builder (and therefore Avada Theme) to inject its Page Builder
	 * into Page Generator Pro's Groups
	 *
	 * @since   1.2.8
	 *
	 * @param   array $post_types     Post Types Supporting Divi.
	 * @return  array                   Post Types Supporting Divi
	 */
	public function register_fusion_builder_support( $post_types ) {

		$post_types[] = 'page-generator-pro';
		return $post_types;

	}

	/**
	 * Avada Live: Enqueue CSS and JS when editing a Content Group, so TinyMCE Plugins etc. work,
	 * as Avada Live removes actions hooked to admin_enqueue_scripts / wp_enqueue_scripts
	 *
	 * @since   2.7.9
	 */
	public function register_avada_live_scripts_css() {

		// Load Plugin CSS/JS.
		$this->base->get_class( 'admin' )->admin_scripts_css();

	}

	/**
	 * Removes orphaned Avada Live metadata in the Group Settings during Generation,
	 * if Avada Live is not active
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

		// Remove Avada Live Meta Keys from the Group Settings during Generation.
		return $this->remove_orphaned_settings_metadata( $settings, $this->meta_keys );

	}

	/**
	 * Replaces Keywords with Custom Field values for any Avada Global Elements
	 * in the given content.
	 *
	 * @since   4.3.5
	 *
	 * @param   string $content    Content.
	 * @param   array  $attrs      Divi Module Shortcode Attributes.
	 * @return  string
	 */
	public function frontend_replace_keywords( $content, $attrs ) {

		// Replace Keywords with Generated Page's Custom Field values.
		return $this->replace_keywords_with_custom_field_values( $content, get_the_ID() );

	}

}
