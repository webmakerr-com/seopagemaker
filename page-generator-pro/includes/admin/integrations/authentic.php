<?php
/**
 * Authentic Theme Integration Class
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Registers Authentic Theme as a Plugin integration:
 * - Register metabox on Content Groups
 * - Copy / don't copy metadata to generated Pages, depending on if the integration is active
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 4.4.7
 */
class Page_Generator_Pro_Authentic extends Page_Generator_Pro_Integration {

	/**
	 * Holds the base object.
	 *
	 * @since   4.4.7
	 *
	 * @var     object
	 */
	public $base;

	/**
	 * Constructor
	 *
	 * @since   4.4.7
	 *
	 * @param   object $base    Base Plugin Class.
	 */
	public function __construct( $base ) {

		// Store base class.
		$this->base = $base;

		// Set Meta Keys used by this Theme.
		$this->meta_keys = array(
			'/^csco_(.*)/i',
		);

		// Register meta boxes on Content Groups.
		add_action( 'add_meta_boxes', array( $this, 'register_authentic_support' ) );

		// Remove data from Group Settings if Theme isn't active on Generation.
		add_filter( 'page_generator_pro_groups_get_settings_remove_orphaned_settings', array( $this, 'remove_orphaned_settings' ) );

	}

	/**
	 * Registers Authentic Theme's Meta Boxes on Page Generator Pro's Groups
	 *
	 * @since   4.4.7
	 */
	public function register_authentic_support() {

		// Bail if Authentic isn't active.
		if ( ! $this->is_active() ) {
			return;
		}

		// Register Authentic Metaboxes on Page Generator Pro's Content Groups.
		// We don't register meta boxes for Video, Media or Gallery, as they're Post specific and require detection of the Post Format,
		// which Content Groups don't (yet) natively support.
		add_meta_box( 'csco_mb_layout_options', esc_html__( 'Layout Options', 'page-generator-pro' ), array( $this, 'csco_mb_layout_options_markup' ), array( 'page-generator-pro' ), 'side' );

	}

	/**
	 * Output Layout Options metabox.
	 *
	 * We don't use the Theme's `csco_mb_layout_options_markup()` function, as this has conditional tags
	 * to determine whether to output the Page Header setting.
	 *
	 * @since   4.4.7
	 *
	 * @param   WP_Post $post   Content Group.
	 */
	public function csco_mb_layout_options_markup( $post ) {

		$page_static = array();

		// Add pages static.
		$page_static[] = get_option( 'page_on_front' );
		$page_static[] = get_option( 'page_for_posts' );

		wp_nonce_field( 'layout_options', 'csco_mb_layout_options' );

		$singular_layout    = get_post_meta( $post->ID, 'csco_singular_layout', true );
		$page_header_type   = get_post_meta( $post->ID, 'csco_page_header_type', true );
		$page_load_nextpost = get_post_meta( $post->ID, 'csco_page_load_nextpost', true );

		// Set Default.
		$singular_layout    = $singular_layout ? $singular_layout : 'default';
		$page_header_type   = $page_header_type ? $page_header_type : 'default';
		$page_load_nextpost = $page_load_nextpost ? $page_load_nextpost : 'default';
		?>
		<div class="csco-singular-layout">
			<h4><label for="csco_singular_layout"><?php esc_html_e( 'Page Layout', 'page-generator-pro' ); ?></label></h4>
			<select name="csco_singular_layout" id="csco_singular_layout" style="box-sizing: border-box;" class="regular-text">
				<option value="default" <?php selected( 'default', $singular_layout ); ?>> <?php esc_html_e( 'Default', 'page-generator-pro' ); ?></option>
				<option value="layout-sidebar-right" <?php selected( 'layout-sidebar-right', $singular_layout ); ?>> <?php esc_html_e( 'Right Sidebar', 'page-generator-pro' ); ?></option>
				<option value="layout-sidebar-left" <?php selected( 'layout-sidebar-left', $singular_layout ); ?>> <?php esc_html_e( 'Left Sidebar', 'page-generator-pro' ); ?></option>
				<option value="layout-fullwidth" <?php selected( 'layout-fullwidth', $singular_layout ); ?>> <?php esc_html_e( 'Fullwidth', 'page-generator-pro' ); ?></option>
			</select>
		</div>

		<h4><label for="csco_page_header_type"><?php esc_html_e( 'Page Header', 'page-generator-pro' ); ?></label></h4>
		<select name="csco_page_header_type" id="csco_page_header_type" style="box-sizing: border-box;" class="regular-text">
			<option value="default" <?php selected( 'default', $page_header_type ); ?>> <?php esc_html_e( 'Default', 'page-generator-pro' ); ?></option>
			<option value="none" <?php selected( 'none', $page_header_type ); ?>> <?php esc_html_e( 'None', 'page-generator-pro' ); ?></option>
			<option value="simple" <?php selected( 'simple', $page_header_type ); ?>> <?php esc_html_e( 'Simple', 'page-generator-pro' ); ?></option>
			<option value="small" <?php selected( 'small', $page_header_type ); ?>> <?php esc_html_e( 'Small', 'page-generator-pro' ); ?></option>
			<option value="wide" <?php selected( 'wide', $page_header_type ); ?>> <?php esc_html_e( 'Wide', 'page-generator-pro' ); ?></option>
			<option value="large" <?php selected( 'large', $page_header_type ); ?>> <?php esc_html_e( 'Large', 'page-generator-pro' ); ?></option>
		</select>
		<?php

	}

	/**
	 * Removes orphaned Authentic metadata in the Group Settings during Generation,
	 * if Authentic is not active
	 *
	 * @since   4.4.7
	 *
	 * @param   array $settings   Group Settings.
	 * @return  array               Group Settings
	 */
	public function remove_orphaned_settings( $settings ) {

		// Don't remove settings if the Plugin is active.
		if ( $this->is_active() ) {
			return $settings;
		}

		// Remove XX Meta Keys from the Group Settings during Generation.
		return $this->remove_orphaned_settings_metadata( $settings, $this->meta_keys );

	}

	/**
	 * Checks if Authentic is active
	 *
	 * @since   4.4.7
	 *
	 * @return  bool    Plugin is Active
	 */
	public function is_active() {

		if ( ! function_exists( 'csco_mb_custom_meta_boxes' ) ) {
			return false;
		}

		return true;

	}

}
