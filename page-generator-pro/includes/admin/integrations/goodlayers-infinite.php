<?php
/**
 * Infinite Theme Integration Class
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Registers the Infinite Theme as a Plugin integration:
 * - Register meta boxes on Content Groups
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 3.0.7
 */
class Page_Generator_Pro_Goodlayers_Infinite extends Page_Generator_Pro_Integration {

	/**
	 * Holds the base object.
	 *
	 * @since   3.3.9
	 *
	 * @var     object
	 */
	public $base;

	/**
	 * Constructor
	 *
	 * @since   3.3.9
	 *
	 * @param   object $base    Base Plugin Class.
	 */
	public function __construct( $base ) {

		// Store base class.
		$this->base = $base;

		// Set Plugin.
		$this->plugin_folder_filename = 'goodlayers-core/goodlayers-core.php';

		// Set Theme Name.
		$this->theme_name = 'Infinite';

		// Register Infinite Theme Page Options.
		add_action( 'init', array( $this, 'register_support' ) );

	}

	/**
	 * Registers Infinite Theme's Page and Post Options on Page Generator Pro's Groups
	 *
	 * @since   3.3.9
	 */
	public function register_support() {

		// Bail if GoodLayers isn't active.
		if ( ! $this->is_active() ) {
			return;
		}

		// Bail if Infinite Theme isn't active.
		if ( ! $this->is_theme_active() ) {
			return;
		}

		// Register Page Options.
		new gdlr_core_page_option(
			array(
				'post_type' => array( $this->base->plugin->name ),
				'options'   => array(
					'layout'        => array(
						'title'   => esc_html__( 'Layout', 'page-generator-pro' ),
						'options' => array(
							'custom-header'         => array(
								'title'   => esc_html__( 'Select Custom Header', 'page-generator-pro' ),
								'type'    => 'combobox',
								'single'  => 'gdlr_core_custom_header_id',
								'options' => array( '' => esc_html__( 'Default', 'page-generator-pro' ) ) + gdlr_core_get_post_list( 'gdlr_core_header' ),
							),
							'enable-header-area'    => array(
								'title'   => esc_html__( 'Enable Header Area', 'page-generator-pro' ),
								'type'    => 'checkbox',
								'default' => 'enable',
							),
							'enable-logo'           => array(
								'title'     => esc_html__( 'Enable Logo', 'page-generator-pro' ),
								'type'      => 'combobox',
								'options'   => array(
									''        => esc_html__( 'Default', 'page-generator-pro' ),
									'enable'  => esc_html__( 'Enable', 'page-generator-pro' ),
									'disable' => esc_html__( 'Disable', 'page-generator-pro' ),
								),
								'single'    => 'gdlr-enable-logo',
								'condition' => array( 'enable-header-area' => 'enable' ),
							),
							'sticky-navigation'     => array(
								'title'     => esc_html__( 'Sticky Navigation', 'page-generator-pro' ),
								'type'      => 'combobox',
								'options'   => array(
									'default' => esc_html__( 'Default', 'page-generator-pro' ),
									'enable'  => esc_html__( 'Enable', 'page-generator-pro' ),
									'disable' => esc_html__( 'Disable', 'page-generator-pro' ),
								),
								'condition' => array( 'enable-header-area' => 'enable' ),
							),
							'enable-page-title'     => array(
								'title'     => esc_html__( 'Enable Page Title', 'page-generator-pro' ),
								'type'      => 'checkbox',
								'default'   => 'enable',
								'condition' => array( 'enable-header-area' => 'enable' ),
							),
							'page-caption'          => array(
								'title'     => esc_html__( 'Caption', 'page-generator-pro' ),
								'type'      => 'textarea',
								'condition' => array(
									'enable-header-area' => 'enable',
									'enable-page-title'  => 'enable',
								),
							),
							'title-background'      => array(
								'title'     => esc_html__( 'Page Title Background', 'page-generator-pro' ),
								'type'      => 'upload',
								'condition' => array(
									'enable-header-area' => 'enable',
									'enable-page-title'  => 'enable',
								),
							),
							'enable-breadcrumbs'    => array(
								'title'     => esc_html__( 'Enable Breadcrumbs', 'page-generator-pro' ),
								'type'      => 'checkbox',
								'default'   => 'disable',
								'condition' => array(
									'enable-header-area' => 'enable',
									'enable-page-title'  => 'enable',
								),
							),
							'body-background-type'  => array(
								'title'   => esc_html__( 'Body Background Type', 'page-generator-pro' ),
								'type'    => 'combobox',
								'options' => array(
									'default' => esc_html__( 'Default', 'page-generator-pro' ),
									'image'   => esc_html__( 'Image ( Only For Boxed Layout )', 'page-generator-pro' ),
								),
							),
							'body-background-image' => array(
								'title'     => esc_html__( 'Body Background Image', 'page-generator-pro' ),
								'type'      => 'upload',
								'data-type' => 'file',
								'condition' => array( 'body-background-type' => 'image' ),
							),
							'body-background-image-opacity' => array(
								'title'     => esc_html__( 'Body Background Image Opacity', 'page-generator-pro' ),
								'type'      => 'fontslider',
								'data-type' => 'opacity',
								'default'   => '100',
								'condition' => array( 'body-background-type' => 'image' ),
							),
							'show-content'          => array(
								'title'       => esc_html__( 'Show WordPress Editor Content', 'page-generator-pro' ),
								'type'        => 'checkbox',
								'default'     => 'enable',
								'description' => esc_html__( 'Disable this to hide the content in editor to show only page builder content.', 'page-generator-pro' ),
							),
							'sidebar'               => array(
								'title'         => esc_html__( 'Sidebar', 'page-generator-pro' ),
								'type'          => 'radioimage',
								'options'       => 'sidebar',
								'default'       => 'none',
								'wrapper-class' => 'gdlr-core-fullsize',
							),
							'sidebar-left'          => array(
								'title'     => esc_html__( 'Sidebar Left', 'page-generator-pro' ),
								'type'      => 'combobox',
								'options'   => 'sidebar',
								'condition' => array( 'sidebar' => array( 'left', 'both' ) ),
							),
							'sidebar-right'         => array(
								'title'     => esc_html__( 'Sidebar Right', 'page-generator-pro' ),
								'type'      => 'combobox',
								'options'   => 'sidebar',
								'condition' => array( 'sidebar' => array( 'right', 'both' ) ),
							),
							'enable-footer'         => array(
								'title'   => esc_html__( 'Enable Footer', 'page-generator-pro' ),
								'type'    => 'combobox',
								'options' => array(
									'default' => esc_html__( 'Default', 'page-generator-pro' ),
									'enable'  => esc_html__( 'Enable', 'page-generator-pro' ),
									'disable' => esc_html__( 'Disable', 'page-generator-pro' ),
								),
							),
							'enable-copyright'      => array(
								'title'   => esc_html__( 'Enable Copyright', 'page-generator-pro' ),
								'type'    => 'combobox',
								'options' => array(
									'default' => esc_html__( 'Default', 'page-generator-pro' ),
									'enable'  => esc_html__( 'Enable', 'page-generator-pro' ),
									'disable' => esc_html__( 'Disable', 'page-generator-pro' ),
								),
							),

						),
					), // layout.
					'title'         => array(
						'title'   => esc_html__( 'Title Style', 'page-generator-pro' ),
						'options' => array(

							'title-style'          => array(
								'title'   => esc_html__( 'Page Title Style', 'page-generator-pro' ),
								'type'    => 'combobox',
								'options' => array(
									'default' => esc_html__( 'Default', 'page-generator-pro' ),
									'small'   => esc_html__( 'Small', 'page-generator-pro' ),
									'medium'  => esc_html__( 'Medium', 'page-generator-pro' ),
									'large'   => esc_html__( 'Large', 'page-generator-pro' ),
									'custom'  => esc_html__( 'Custom', 'page-generator-pro' ),
								),
								'default' => 'default',
							),
							'title-align'          => array(
								'title'        => esc_html__( 'Page Title Alignment', 'page-generator-pro' ),
								'type'         => 'radioimage',
								'options'      => 'text-align',
								'with-default' => true,
								'default'      => 'default',
							),
							'title-spacing'        => array(
								'title'           => esc_html__( 'Page Title Padding', 'page-generator-pro' ),
								'type'            => 'custom',
								'item-type'       => 'padding',
								'data-input-type' => 'pixel',
								'options'         => array( 'padding-top', 'padding-bottom', 'caption-top-margin' ),
								'wrapper-class'   => 'gdlr-core-fullsize gdlr-core-no-link gdlr-core-large',
								'condition'       => array( 'title-style' => 'custom' ),
							),
							'title-font-size'      => array(
								'title'           => esc_html__( 'Page Title Font Size', 'page-generator-pro' ),
								'type'            => 'custom',
								'item-type'       => 'padding',
								'data-input-type' => 'pixel',
								'options'         => array( 'title-size', 'title-letter-spacing', 'caption-size', 'caption-letter-spacing' ),
								'wrapper-class'   => 'gdlr-core-fullsize gdlr-core-no-link gdlr-core-large',
								'condition'       => array( 'title-style' => 'custom' ),
							),
							'title-font-weight'    => array(
								'title'         => esc_html__( 'Page Title Font Weight', 'page-generator-pro' ),
								'type'          => 'custom',
								'item-type'     => 'padding',
								'options'       => array( 'title-weight', 'caption-weight' ),
								'wrapper-class' => 'gdlr-core-fullsize gdlr-core-no-link gdlr-core-large',
								'condition'     => array( 'title-style' => 'custom' ),
							),
							'title-font-transform' => array(
								'title'     => esc_html__( 'Page Title Font Transform', 'page-generator-pro' ),
								'type'      => 'combobox',
								'options'   => array(
									'none'       => esc_html__( 'None', 'page-generator-pro' ),
									'uppercase'  => esc_html__( 'Uppercase', 'page-generator-pro' ),
									'lowercase'  => esc_html__( 'Lowercase', 'page-generator-pro' ),
									'capitalize' => esc_html__( 'Capitalize', 'page-generator-pro' ),
								),
								'default'   => 'uppercase',
								'condition' => array( 'title-style' => 'custom' ),
							),
							'top-bottom-gradient'  => array(
								'title'   => esc_html__( 'Title Top/Bottom Gradient', 'page-generator-pro' ),
								'type'    => 'combobox',
								'options' => array(
									'default' => esc_html__( 'Default', 'page-generator-pro' ),
									'both'    => esc_html__( 'Both', 'page-generator-pro' ),
									'top'     => esc_html__( 'Top', 'page-generator-pro' ),
									'bottom'  => esc_html__( 'Bottom', 'page-generator-pro' ),
									'disable' => esc_html__( 'None', 'page-generator-pro' ),
								),
							),
							'title-background-overlay-opacity' => array(
								'title'       => esc_html__( 'Page Title Background Overlay Opacity', 'page-generator-pro' ),
								'type'        => 'text',
								'description' => esc_html__( 'Fill the number between 0.01 - 1 ( Leave Blank For Default Value )', 'page-generator-pro' ),
								'condition'   => array( 'title-style' => 'custom' ),
							),
							'title-color'          => array(
								'title' => esc_html__( 'Page Title Color', 'page-generator-pro' ),
								'type'  => 'colorpicker',
							),
							'caption-color'        => array(
								'title' => esc_html__( 'Page Caption Color', 'page-generator-pro' ),
								'type'  => 'colorpicker',
							),
							'title-background-overlay-color' => array(
								'title' => esc_html__( 'Page Background Overlay Color', 'page-generator-pro' ),
								'type'  => 'colorpicker',
							),

						),
					), // title.
					'header'        => array(
						'title'   => esc_html__( 'Header', 'page-generator-pro' ),
						'options' => array(

							'main_menu'            => array(
								'title'   => esc_html__( 'Primary Menu', 'page-generator-pro' ),
								'type'    => 'combobox',
								'options' => function_exists( 'gdlr_core_get_menu_list' ) ? gdlr_core_get_menu_list() : array(),
								'single'  => 'gdlr-core-location-main_menu',
							),
							'right_menu'           => array(
								'title'   => esc_html__( 'Secondary Menu', 'page-generator-pro' ),
								'type'    => 'combobox',
								'options' => function_exists( 'gdlr_core_get_menu_list' ) ? gdlr_core_get_menu_list() : array(),
								'single'  => 'gdlr-core-location-right_menu',
							),
							'top_bar_menu'         => array(
								'title'   => esc_html__( 'Top Bar Menu', 'page-generator-pro' ),
								'type'    => 'combobox',
								'options' => function_exists( 'gdlr_core_get_menu_list' ) ? gdlr_core_get_menu_list() : array(),
								'single'  => 'gdlr-core-location-top_bar_menu',
							),
							'mobile_menu'          => array(
								'title'   => esc_html__( 'Mobile Menu', 'page-generator-pro' ),
								'type'    => 'combobox',
								'options' => function_exists( 'gdlr_core_get_menu_list' ) ? gdlr_core_get_menu_list() : array(),
								'single'  => 'gdlr-core-location-mobile_menu',
							),

							'header-slider'        => array(
								'title'       => esc_html__( 'Header Slider ( Above Navigation )', 'page-generator-pro' ),
								'type'        => 'combobox',
								'options'     => array(
									'none'              => esc_html__( 'None', 'page-generator-pro' ),
									'layer-slider'      => esc_html__( 'Layer Slider', 'page-generator-pro' ),
									'master-slider'     => esc_html__( 'Master Slider', 'page-generator-pro' ),
									'revolution-slider' => esc_html__( 'Revolution Slider', 'page-generator-pro' ),
								),
								'description' => esc_html__( 'For header style plain / bar / boxed', 'page-generator-pro' ),
							),
							'layer-slider-id'      => array(
								'title'     => esc_html__( 'Choose Layer Slider', 'page-generator-pro' ),
								'type'      => 'combobox',
								'options'   => gdlr_core_get_layerslider_list(),
								'condition' => array( 'header-slider' => 'layer-slider' ),
							),
							'master-slider-id'     => array(
								'title'     => esc_html__( 'Choose Master Slider', 'page-generator-pro' ),
								'type'      => 'combobox',
								'options'   => gdlr_core_get_masterslider_list(),
								'condition' => array( 'header-slider' => 'master-slider' ),
							),
							'revolution-slider-id' => array(
								'title'     => esc_html__( 'Choose Revolution Slider', 'page-generator-pro' ),
								'type'      => 'combobox',
								'options'   => gdlr_core_get_revolution_slider_list(),
								'condition' => array( 'header-slider' => 'revolution-slider' ),
							),

						), // header options.
					), // header.
					'bullet-anchor' => array(
						'title'   => esc_html__( 'Bullet Anchor', 'page-generator-pro' ),
						'options' => array(
							'bullet-anchor-description' => array(
								'type'        => 'description',
								'description' => esc_html__( 'This feature is used for one page navigation. It will appear on the right side of page. You can put the id of element in \'Anchor Link\' field to let the bullet scroll the page to.', 'page-generator-pro' ),
							),
							'bullet-anchor'             => array(
								'title'         => esc_html__( 'Add Anchor', 'page-generator-pro' ),
								'type'          => 'custom',
								'item-type'     => 'tabs',
								'options'       => array(
									'title'              => array(
										'title' => esc_html__( 'Anchor Link', 'page-generator-pro' ),
										'type'  => 'text',
									),
									'anchor-color'       => array(
										'title' => esc_html__( 'Anchor Color', 'page-generator-pro' ),
										'type'  => 'colorpicker',
									),
									'anchor-hover-color' => array(
										'title' => esc_html__( 'Anchor Hover Color', 'page-generator-pro' ),
										'type'  => 'colorpicker',
									),
								),
								'wrapper-class' => 'gdlr-core-fullsize',
							),
						),
					),
				),
			)
		);

	}

}
