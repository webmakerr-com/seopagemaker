<?php
/**
 * Popup Maker Integration Class
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Registers Popup Maker as a Plugin integration:
 * - Registers metaboxes in Content Groups
 * - Register as an overwrite section in Content Groups
 * - Copy / don't copy metadata to generated Pages, depending on if the integration is active
 * - Change metadata for each generated Page
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 3.4.1
 */
class Page_Generator_Pro_Popup_Maker extends Page_Generator_Pro_Integration {

	/**
	 * Holds the base object.
	 *
	 * @since   3.4.1
	 *
	 * @var     object
	 */
	public $base;

	/**
	 * Constructor
	 *
	 * @since   3.4.1
	 *
	 * @param   object $base    Base Plugin Class.
	 */
	public function __construct( $base ) {

		// Store base class.
		$this->base = $base;

		// Set Plugin.
		$this->plugin_folder_filename = 'popup-maker/popup-maker.php';

		// Set Meta Keys used by this Plugin.
		$this->meta_keys = array(
			'popup_title',
			'popup_settings',
		);

		// Set Overwrite Setting's Key used by this Plugin.
		$this->overwrite_section = 'popup_maker';

		// Content Groups: Register Plugin JS.
		add_action( 'page_generator_pro_admin_admin_scripts_css', array( $this, 'admin_scripts_css' ), 10, 1 );

		// Content Groups: Register Plugin Metaboxes.
		add_action( 'page_generator_pro_groups_ui_add_meta_boxes', array( $this, 'add_meta_boxes' ) );

		// Content Groups: Populate Popup Maker Settings.
		add_filter( 'pum_popup_display_settings_fields', array( $this, 'maybe_populate_popup_display_settings_fields' ) );

		// Content Groups: Conditionally display metaboxes based on the Post Type chosen for Generation.
		add_filter( 'page_generator_pro_groups_ui_get_post_type_conditional_metaboxes', array( $this, 'get_post_type_conditional_metaboxes' ) );

		// Content Groups: Save Metabox Data.
		add_action( 'save_post', array( $this, 'save' ), 10, 2 );

		// Content Groups: Add Overwrite Section if Plugin enabled.
		add_filter( 'page_generator_pro_common_get_content_overwrite_sections', array( $this, 'add_overwrite_section' ) );

		// Content Groups: Ignore Plugin meta keys if overwriting is disabled for Plugin.
		add_filter( 'page_generator_pro_generate_set_post_meta_ignored_keys', array( $this, 'prevent_post_meta_copy_to_generated_content' ), 10, 4 );

		// Remove Plugin data from Group Settings if Plugin isn't active on Generation.
		add_filter( 'page_generator_pro_groups_get_settings_remove_orphaned_settings', array( $this, 'remove_orphaned_settings' ) );

		// Content Groups: Change Cookie ID from Content Group ID to Generated Popup ID.
		add_filter( 'page_generator_pro_generate_set_post_meta_popup_settings', array( $this, 'generate_change_cookie_id' ), 10, 3 );

	}

	/**
	 * Enqueues CSS and JS
	 *
	 * @since   3.4.1
	 *
	 * @param   array $screen     Screen (screen, section).
	 */
	public function admin_scripts_css( $screen ) {

		// Bail if Plugin isn't active.
		if ( ! $this->is_active() ) {
			return;
		}

		// Bail if not editing a Content Group.
		if ( $screen['screen'] !== 'content_groups' ) {
			return;
		}
		if ( $screen['section'] !== 'edit' ) {
			return;
		}

		// Enqueue JS.
		wp_enqueue_script( 'pum-admin-popup-editor' );

	}

	/**
	 * Adds Plugin Meta Boxes to Content Groups
	 *
	 * @since   3.4.1
	 */
	public function add_meta_boxes() {

		// Bail if Plugin isn't active.
		if ( ! $this->is_active() ) {
			return;
		}

		// Register Metabox.
		add_meta_box( 'pum_popup_settings', __( 'Popup Settings', 'page-generator-pro' ), array( $this, 'render_settings_meta_box' ), $this->base->get_class( 'post_type' )->post_type_name, 'normal', 'high' );

	}

	/**
	 * Defines field settings for the Popup Maker meta box that Popup Maker won't define
	 * due to its conditional pum_is_popup_editor() checks which fail when editing a
	 * Content Group.
	 *
	 * @since   3.4.2
	 *
	 * @param   array $meta_box_fields    Fields.
	 * @return  array                       Fields
	 */
	public function maybe_populate_popup_display_settings_fields( $meta_box_fields ) {

		// Get current screen.
		$screen = $this->base->get_class( 'screen' )->get_current_screen();

		// Bail if not editing a Content Group.
		if ( $screen['screen'] !== 'content_groups' ) {
			return $meta_box_fields;
		}
		if ( $screen['section'] !== 'edit' ) {
			return $meta_box_fields;
		}

		// Define Display > Apperance > Popup Theme <select> options.
		$meta_box_fields['main']['theme_id']['options'] = PUM_Helpers::popup_theme_selectlist();

		return $meta_box_fields;

	}

	/**
	 * Define metaboxes that should only display based on the value of Publish > Post Type
	 * in the Content Groups UI.
	 *
	 * @since   3.4.1
	 *
	 * @param   array $metaboxes  Metabox ID Keys and Post Type Values array.
	 * @return  array               Metabox ID Keys and Post Type Values array
	 */
	public function get_post_type_conditional_metaboxes( $metaboxes ) {

		return array_merge(
			$metaboxes,
			array(
				'pum_popup_settings' => array(
					'popup',
				),
			)
		);

	}

	/**
	 * Render the Settings Meta Box
	 *
	 * @since   3.4.1
	 */
	public function render_settings_meta_box() {

		global $post;

		// Get settings directly from Post Meta.
		$settings = get_post_meta( $post->ID, 'popup_settings', true );

		wp_nonce_field( basename( __FILE__ ), 'pum_popup_settings_nonce' );
		wp_enqueue_script( 'popup-maker-admin' );
		?>
		<script type="text/javascript">
			window.pum_popup_settings_editor = 
			<?php
			// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
			echo PUM_Utils_Array::safe_json_encode(
				apply_filters(
					'pum_popup_settings_editor_var',
					array(
						'form_args'             => array(
							'id'       => 'pum-popup-settings',
							'tabs'     => PUM_Admin_Popups::tabs(),
							'sections' => PUM_Admin_Popups::sections(),
							'fields'   => PUM_Admin_Popups::fields(),
						),
						'conditions'            => PUM_Conditions::instance()->get_conditions(),
						'conditions_selectlist' => PUM_Conditions::instance()->dropdown_list(),
						'triggers'              => PUM_Triggers::instance()->get_triggers(),
						'cookies'               => PUM_Cookies::instance()->get_cookies(),
						'current_values'        => PUM_Admin_Popups::render_form_values( $settings ),
					)
				)
			);
			// phpcs:enable
			?>
		;
		</script>

		<div id="pum-popup-settings-container" class="pum-popup-settings-container">
			<div class="pum-no-js" style="padding: 0 12px;">
				<p>
					<?php
					printf(
						'%1$s <a href="https://docs.wppopupmaker.com/article/373-checking-for-javascript-errors" target="_blank">%2$s</a>',
						esc_html__( 'If you are seeing this, the page is still loading or there are Javascript errors on this page.', 'page-generator-pro' ),
						esc_html__( 'View troubleshooting guide', 'page-generator-pro' )
					);
					?>
				</p>
			</div>
		</div>
		<?php

	}

	/**
	 * Save Popup Settings to Content Group
	 *
	 * @since   3.4.1
	 *
	 * @param   int     $post_id    Post ID.
	 * @param   WP_Post $post       Post.
	 */
	public function save( $post_id, $post ) {

		// Bail if Plugin isn't active.
		if ( ! $this->is_active() ) {
			return;
		}

		// Bail if nonce fails.
		if ( ! isset( $_POST['pum_popup_settings_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['pum_popup_settings_nonce'] ), basename( __FILE__ ) ) ) {
			return;
		}

		// Update Settings.
		$settings = ! empty( $_POST['popup_settings'] ) ? wp_unslash( $_POST['popup_settings'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput

		// Sanitize JSON values..
		$settings['conditions'] = isset( $settings['conditions'] ) ? PUM_Admin_Popups::sanitize_meta( $settings['conditions'] ) : array();
		$settings['triggers']   = isset( $settings['triggers'] ) ? PUM_Admin_Popups::sanitize_meta( $settings['triggers'] ) : array();
		$settings['cookies']    = isset( $settings['cookies'] ) ? PUM_Admin_Popups::sanitize_meta( $settings['cookies'] ) : array();

		// Filter settings.
		$settings = apply_filters( 'pum_popup_setting_pre_save', $settings, $post->ID );

		// Sanitize and parse.
		$settings = PUM_Admin_Popups::sanitize_settings( $settings );
		$settings = PUM_Admin_Popups::parse_values( $settings );

		// Save settings directly to Post Meta.
		update_post_meta( $post_id, 'popup_title', $post->post_title );
		update_post_meta( $post_id, 'popup_settings', $settings );

	}

	/**
	 * Defines available content overwrite sections.
	 *
	 * @since   3.4.1
	 *
	 * @param   array $sections    Content Overwrite Sections.
	 * @return  array                Content Overwrite Sections
	 */
	public function add_overwrite_section( $sections ) {

		// Bail if Plugin isn't active.
		if ( ! $this->is_active() ) {
			return $sections;
		}

		// Add Plugin.
		$sections[ $this->overwrite_section ] = __( 'Popup Maker', 'page-generator-pro' );

		// Return.
		return $sections;

	}

	/**
	 * Removes orphaned Plugin metadata in the Group Settings during Generation,
	 * if Plugin is not active.
	 *
	 * @since   3.4.1
	 *
	 * @param   array $settings   Group Settings.
	 * @return  array               Group Settings
	 */
	public function remove_orphaned_settings( $settings ) {

		// Don't remove settings if the Plugin is active.
		if ( $this->is_active() ) {
			return $settings;
		}

		// Remove Plugin Meta Keys from the Group Settings during Generation.
		return $this->remove_orphaned_settings_metadata( $settings, $this->meta_keys );

	}

	/**
	 * Changes the Cookie ID within the Popup Settings from the Content Group ID
	 * to the Generated Popup ID
	 *
	 * @since   3.4.1
	 *
	 * @param   array|string|int|object $meta_value Meta Value.
	 * @param   int                     $post_id    Generated Post ID.
	 * @param   int                     $group_id   Group ID.
	 */
	public function generate_change_cookie_id( $meta_value, $post_id, $group_id ) {

		// Bail if no meta value.
		if ( ! is_array( $meta_value['triggers'] ) ) {
			return $meta_value;
		}

		// Change Group ID to Generated Popup ID for Cookie Names within Triggers.
		foreach ( $meta_value['triggers'] as $trigger_index => $trigger ) {
			foreach ( $trigger['settings']['cookie_name'] as $cookie_index => $cookie_name ) {
				$meta_value['triggers'][ $trigger_index ]['settings']['cookie_name'][ $cookie_index ] = str_replace( (string) $group_id, (string) $post_id, $cookie_name );
			}
		}

		// Change Group ID to Generated Popup ID for Cookie Names.
		if ( isset( $meta_value['cookies'] ) ) {
			foreach ( $meta_value['cookies'] as $cookie_index => $cookie ) {
				$meta_value['cookies'][ $cookie_index ]['settings']['name'] = str_replace( (string) $group_id, (string) $post_id, $meta_value['cookies'][ $cookie_index ]['settings']['name'] );
			}
		}

		return $meta_value;

	}

}
