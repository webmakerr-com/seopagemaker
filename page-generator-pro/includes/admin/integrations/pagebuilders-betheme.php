<?php
/**
 * BeTheme / Muffin Page Builder Integration Class
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Registers BeTheme's Muffin Page Builder fields as a Plugin integration:
 * - Register fields on Content Groups
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 2.1.2
 */
class Mfn_Post_Type_Page_Generator_Pro extends Mfn_Post_Type {

	/**
	 * Constructor
	 *
	 * @since   2.1.2
	 */
	public function __construct() {

		parent::__construct();

		if ( is_admin() ) {
			$this->fields  = $this->set_fields();
			$this->builder = new Mfn_Builder_Admin();
		}

	}

	/**
	 * Set post type fields
	 *
	 * @since   2.1.2
	 */
	private function set_fields() {

		return array(
			'id'     => 'mfn-meta-page-generator-pro',
			'title'  => esc_html__( 'Page Options', 'page-generator-pro' ),
			'page'   => 'page-generator-pro',
			'fields' => array(
				array(
					'title' => __( 'Header & Footer', 'page-generator-pro' ),
				),
				array(
					'id'         => 'mfn_header_template',
					'type'       => 'select',
					'title'      => __( 'Custom Header Template', 'page-generator-pro' ),
					'desc'       => __( 'To overwrite template set with conditions in <a target="_blank" href="edit.php?post_type=template&tab=header">Templates</a> section, please select appropriate template from dropdown select. Afterwards, please reload the page to refresh the options.', 'page-generator-pro' ),
					'options'    => mfna_templates( 'header' ),
					'js_options' => 'headers',
				),
				array(
					'id'         => 'mfn_footer_template',
					'type'       => 'select',
					'title'      => __( 'Custom Footer Template', 'page-generator-pro' ),
					'desc'       => __( 'To overwrite template set with conditions in <a target="_blank" href="edit.php?post_type=template&tab=footer">Templates</a> section, please select appropriate template from dropdown select. Afterwards, please reload the page to refresh the options.', 'page-generator-pro' ),
					'options'    => mfna_templates( 'footer' ),
					'js_options' => 'footers',
				),

				array(
					'title' => __( 'Layout', 'page-generator-pro' ),
				),
				array(
					'id'      => 'mfn-post-hide-content',
					'type'    => 'switch',
					'title'   => __( 'The content', 'page-generator-pro' ),
					'desc'    => __( 'The content from the WordPress editor', 'page-generator-pro' ),
					'options' => array(
						'1' => __( 'Hide', 'page-generator-pro' ),
						'0' => __( 'Show', 'page-generator-pro' ),
					),
					'std'     => '0',
				),
				array(
					'id'      => 'mfn-post-layout',
					'type'    => 'radio_img',
					'title'   => __( 'Layout', 'page-generator-pro' ),
					'desc'    => __( 'Full width sections works only without sidebars', 'page-generator-pro' ),
					'options' => array(
						'no-sidebar'        => __( 'Full width', 'page-generator-pro' ),
						'left-sidebar'      => __( 'Left sidebar', 'page-generator-pro' ),
						'right-sidebar'     => __( 'Right sidebar', 'page-generator-pro' ),
						'both-sidebars'     => __( 'Both sidebars', 'page-generator-pro' ),
						'offcanvas-sidebar' => __( 'Off-canvas sidebar', 'page-generator-pro' ),
					),
					'std'     => 'no-sidebar',
					'alias'   => 'sidebar',
					'class'   => 'form-content-full-width small',
				),
				array(
					'id'         => 'mfn-post-sidebar',
					'type'       => 'select',
					'title'      => __( 'Sidebar', 'page-generator-pro' ),
					'desc'       => __( 'Shows only if layout with sidebar is selected', 'page-generator-pro' ),
					'options'    => mfn_opts_get( 'sidebars' ),
					'js_options' => 'sidebars',
				),
				array(
					'id'         => 'mfn-post-sidebar2',
					'type'       => 'select',
					'title'      => __( 'Sidebar 2nd', 'page-generator-pro' ),
					'desc'       => __( 'Shows only if layout with both sidebars is selected', 'page-generator-pro' ),
					'options'    => mfn_opts_get( 'sidebars' ),
					'js_options' => 'sidebars',
				),

				array(
					'title' => __( 'Media', 'page-generator-pro' ),
				),
				array(
					'id'         => 'mfn-post-slider',
					'type'       => 'select',
					'title'      => __( 'Slider Revolution', 'page-generator-pro' ),
					'options'    => Mfn_Builder_Helper::get_sliders( 'rev' ),
					'js_options' => 'rev_slider',
				),
				array(
					'id'         => 'mfn-post-slider-layer',
					'type'       => 'select',
					'title'      => __( 'Layer Slider', 'page-generator-pro' ),
					'options'    => Mfn_Builder_Helper::get_sliders( 'layer' ),
					'js_options' => 'layer_slider',
				),
				array(
					'id'    => 'mfn-post-slider-shortcode',
					'type'  => 'text',
					'title' => __( 'Slider shortcode', 'page-generator-pro' ),
					'desc'  => __( 'Paste slider shortcode if you use other slider plugin', 'page-generator-pro' ),
				),
				array(
					'id'    => 'mfn-post-subheader-image',
					'type'  => 'upload',
					'title' => __( 'Subheader image', 'page-generator-pro' ),
				),

				array(
					'title' => __( 'Options', 'page-generator-pro' ),
				),
				array(
					'id'      => 'mfn-post-one-page',
					'type'    => 'switch',
					'title'   => __( 'One Page', 'page-generator-pro' ),
					'options' => array(
						'0' => __( 'Disable', 'page-generator-pro' ),
						'1' => __( 'Enable', 'page-generator-pro' ),
					),
					'std'     => '0',
				),
				array(
					'id'      => 'mfn-post-full-width',
					'type'    => 'switch',
					'title'   => __( 'Full width', 'page-generator-pro' ),
					'desc'    => __( 'Set page to full width ignoring <a target="_blank" href="admin.php?page=be-options#general">Site width</a> option. Works for Layout Full width only.', 'page-generator-pro' ),
					'options' => array(
						'0'       => __( 'Disable', 'page-generator-pro' ),
						'site'    => __( 'Enable', 'page-generator-pro' ),
						'content' => __( 'Content only', 'page-generator-pro' ),
					),
					'std'     => '0',
				),
				array(
					'id'      => 'mfn-post-hide-title',
					'type'    => 'switch',
					'title'   => __( 'Subheader', 'page-generator-pro' ),
					'options' => array(
						'1' => __( 'Hide', 'page-generator-pro' ),
						'0' => __( 'Show', 'page-generator-pro' ),
					),
					'std'     => '0',
				),
				array(
					'id'      => 'mfn-post-remove-padding',
					'type'    => 'switch',
					'title'   => __( 'Content top padding', 'page-generator-pro' ),
					'options' => array(
						'1' => __( 'Hide', 'page-generator-pro' ),
						'0' => __( 'Show', 'page-generator-pro' ),
					),
					'std'     => '0',
				),
				array(
					'id'         => 'mfn-post-custom-layout',
					'type'       => 'select',
					'title'      => __( 'Custom layout', 'page-generator-pro' ),
					'desc'       => __( 'Custom layout overwrites Theme Options', 'page-generator-pro' ),
					'options'    => $this->get_layouts(),
					'js_options' => 'layouts',
				),
				array(
					'id'         => 'mfn-post-menu',
					'type'       => 'select',
					'title'      => __( 'Custom menu', 'page-generator-pro' ),
					'desc'       => __( 'Does not work with Split Menu', 'page-generator-pro' ),
					'options'    => mfna_menu(),
					'js_options' => 'menus',
				),

				array(
					'title' => __( 'SEO', 'page-generator-pro' ),
				),
				array(
					'id'    => 'mfn-meta-seo-title',
					'type'  => 'text',
					'title' => __( 'Title', 'page-generator-pro' ),
				),
				array(
					'id'    => 'mfn-meta-seo-description',
					'type'  => 'text',
					'title' => __( 'Description', 'page-generator-pro' ),
				),
				array(
					'id'    => 'mfn-meta-seo-keywords',
					'type'  => 'text',
					'title' => __( 'Keywords', 'page-generator-pro' ),
				),
				array(
					'id'    => 'mfn-meta-seo-og-image',
					'type'  => 'upload',
					'title' => __( 'Open Graph image', 'page-generator-pro' ),
					'desc'  => __( 'Facebook share image', 'page-generator-pro' ),
				),

				array(
					'title' => __( 'Custom CSS', 'page-generator-pro' ),
				),
				array(
					'id'    => 'mfn-post-css',
					'type'  => 'textarea',
					'title' => __( 'Custom CSS', 'page-generator-pro' ),
					'desc'  => __( 'Custom CSS code for this page', 'page-generator-pro' ),
					'class' => 'form-content-full-width',
					'cm'    => 'css',
				),
			),
		);

	}

}
