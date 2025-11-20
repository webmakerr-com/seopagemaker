<?php
/**
 * Landkit Theme Integration Class
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Registers Landkit as a Theme integration:
 * - Register sidebar meta box.
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 3.7.8
 */
class Page_Generator_Pro_Landkit extends Page_Generator_Pro_Integration {

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

		// Set Theme Name.
		$this->theme_name = 'Landkit';

		// Set Meta Keys used by this Theme.
		$this->meta_keys = array(
			'landkit-',
		);

		// Register metaboxes for Content Groups.
		add_action( 'add_meta_boxes', array( $this, 'register_meta_boxes' ) );

		// Remove Page Builder data from Group Settings if overwriting content is disabled, and an existing generated page already exists.
		add_filter( 'page_generator_pro_generate_remove_content_from_settings_ignored_keys', array( $this, 'remove_post_meta_from_content_group' ), 10, 2 );

		// Remove Theme data from Group Settings if Theme isn't active on Generation.
		add_filter( 'page_generator_pro_groups_get_settings_remove_orphaned_settings', array( $this, 'remove_orphaned_settings' ) );

	}

	/**
	 * Registers Landkit's meta boxes against Content Groups.
	 *
	 * @since   3.7.8
	 */
	public function register_meta_boxes() {

		// Bail if Theme isn't active.
		if ( $this->is_theme_active() ) {
			return;
		}

		add_meta_box( 'landkit_sidebar', __( 'Sidebars', 'page-generator-pro' ), array( $this, 'register_meta_box_sidebars' ), 'page-generator-pro', 'side', 'low' );

	}

	/**
	 * Registers Landkit's sidebars meta box against Content Groups.
	 *
	 * @since   3.7.8
	 */
	public function register_meta_box_sidebars() {

		$sidebar = get_post_meta( get_the_ID(), 'landkit-sidebar', false );
		if ( count( $sidebar ) > 0 ) {
			$sidebar = $sidebar[0];
		} else {
			$sidebar = '';
		}
		?>
		<select data-hc-setting="sidebars" id="sidebars-menu" name="sidebars-menu">
			<option value=""<?php selected( $sidebar, '' ); ?>><?php esc_attr_e( 'None', 'page-generator-pro' ); ?></option>
			<option value="right"<?php selected( $sidebar, 'right' ); ?>><?php esc_attr_e( 'Right', 'page-generator-pro' ); ?></option>
			<option value="left"<?php selected( $sidebar, 'left' ); ?>><?php esc_attr_e( 'Left', 'page-generator-pro' ); ?></option>
			<option value="right-left"<?php selected( $sidebar, 'right-left' ); ?>><?php esc_attr_e( 'Right and left', 'page-generator-pro' ); ?></option>
		</select>
		<?php

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

		// Don't remove settings if the Theme is active.
		if ( $this->is_theme_active() ) {
			return $settings;
		}

		// Remove Hybrid Composer Meta Keys from the Group Settings during Generation.
		return $this->remove_orphaned_settings_metadata( $settings, $this->meta_keys );

	}

}
