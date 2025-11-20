<?php
/**
 * WooCommerce Integration Class
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Registers WooCommerce as a Plugin integration:
 * - Register as an overwrite section in Content Groups
 * - Copy / don't copy metadata to generated Pages, depending on if the integration is active
 * - Depending on the Product Type, copy necessary metadata to generated Pages
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 2.6.9
 */
class Page_Generator_Pro_WooCommerce extends Page_Generator_Pro_Integration {

	/**
	 * Holds the base object.
	 *
	 * @since   2.6.9
	 *
	 * @var     object
	 */
	public $base;

	/**
	 * Constructor
	 *
	 * @since   2.6.9
	 *
	 * @param   object $base    Base Plugin Class.
	 */
	public function __construct( $base ) {

		// Store base class.
		$this->base = $base;

		// Set Plugin and Minimum Supported Version.
		$this->plugin_folder_filename    = 'woocommerce/woocommerce.php';
		$this->minimum_supported_version = '4.2';

		// Set Meta Keys used by this Plugin.
		$this->meta_keys = array(
			'_backorders',
			'_download_expiry',
			'_download_limit',
			'_downloadable',
			'_manage_stock',
			'_price',
			'_product_version',
			'_regular_price',
			'_sale_price',
			'_sku',
			'_sold_individually',
			'_stock',
			'_stock_status',
			'_tax_class',
			'_tax_status',
			'_virtual',
			'_wc_average_rating',
			'_wc_review_count',
			'total_sales',
		);

		// Set Overwrite Setting's Key used by this Plugin.
		$this->overwrite_section = 'woocommerce_product';

		// Load Product Type classes.
		$this->maybe_load_product_type_classes();

		// Add Overwrite Section if WooCommerce enabled.
		add_filter( 'page_generator_pro_common_get_content_overwrite_sections', array( $this, 'add_overwrite_section' ) );

		// Always ignore WooCommerce price meta keys, as prices are set in generate_content_set_product_price() which determines the _price.
		add_filter( 'page_generator_pro_generate_set_post_meta_ignored_keys', array( $this, 'ignore_price_meta_keys' ) );

		// Ignore WooCommerce meta keys if overwriting is disabled for WooCommerce.
		add_filter( 'page_generator_pro_generate_set_post_meta_ignored_keys', array( $this, 'prevent_post_meta_copy_to_generated_content' ), 10, 4 );

		// Remove Plugin data from Group Settings if Plugin isn't active on Generation.
		add_filter( 'page_generator_pro_groups_get_settings_remove_orphaned_settings', array( $this, 'remove_orphaned_settings' ) );

		// Conditionally display metaboxes based on the Post Type chosen for Generation.
		add_filter( 'page_generator_pro_groups_ui_get_post_type_conditional_metaboxes', array( $this, 'get_post_type_conditional_metaboxes' ) );

		// Define dropdown elements to maybe replace with AJAX selections if Performance options enabled.
		add_filter( 'page_generator_pro_performance_replace_customizer_page_dropdowns', array( $this, 'replace_customizer_page_dropdowns' ) );

		// Exclude some WooCommerce Taxonomies.
		add_filter( 'page_generator_pro_common_get_excluded_taxonomies', array( $this, 'excluded_taxonomies' ) );

		// Register Content Groups as a WooCommerce screen.
		add_filter( 'woocommerce_screen_ids', array( $this, 'register_content_group_as_woocommerce_screen' ) );

		// Enqueue JS.
		// Allow WC_Admin_Assets::admin_scripts() to register dependencies first.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_product_js' ), 9999 );

		// Get Product Type.
		add_filter( 'woocommerce_product_type_query', array( $this, 'get_product_type' ), 10, 2 );

		// Register Metaboxes.
		add_action( 'page_generator_pro_groups_ui_add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		add_filter( 'woocommerce_data_stores', array( $this, 'woocommerce_data_stores' ) );
		add_action( 'save_post', array( $this, 'save_product' ), 10, 2 );

		// Content Groups: Set product_type Taxonomy Term and Price.
		add_action( 'page_generator_pro_generate_content_finished', array( $this, 'generate_content_set_product_type' ), 10, 6 );
		add_action( 'page_generator_pro_generate_content_finished', array( $this, 'generate_content_set_product_price' ), 10, 6 );

	}

	/**
	 * Loads Page Generator Pro's implementation of Product Data Store classes, which
	 * bypasses the Post Type check that would make our Content Group fail to load
	 * WooCommerce Product Data, because our Content Group isn't a Product Post Type
	 *
	 * @since   2.6.9
	 */
	public function maybe_load_product_type_classes() {

		// Bail if data store isn't available.
		if ( ! class_exists( 'WC_Product_Data_Store_CPT' ) ) {
			return;
		}

		// Include files.
		require_once $this->base->plugin->folder . '/includes/admin/integrations/woocommerce/wc.php';
		require_once $this->base->plugin->folder . '/includes/admin/integrations/woocommerce/simple.php';
		require_once $this->base->plugin->folder . '/includes/admin/integrations/woocommerce/grouped.php';
		require_once $this->base->plugin->folder . '/includes/admin/integrations/woocommerce/variable.php';

	}

	/**
	 * Defines available content overwrite sections.
	 *
	 * @since   2.9.0
	 *
	 * @param   array $sections    Content Overwrite Sections.
	 * @return  array                Content Overwrite Sections
	 */
	public function add_overwrite_section( $sections ) {

		// Bail if WooCommerce isn't active.
		if ( ! $this->is_active() ) {
			return $sections;
		}

		// Add WooCommerce.
		$sections[ $this->overwrite_section ] = __( 'WooCommerce Product Data', 'page-generator-pro' );

		// Return.
		return $sections;

	}

	/**
	 * Defines Post Meta Keys in a Content Group to ignore and not copy to generated Posts / Groups,
	 * as these are handled in generate_content_set_product_type_and_price().
	 *
	 * @since   3.9.9
	 *
	 * @param   array $ignored_keys   Ignored Keys (preg_match() compatible regex expressions are supported).
	 */
	public function ignore_price_meta_keys( $ignored_keys ) {

		return array_merge(
			$ignored_keys,
			array(
				'_price',
				'_regular_price',
				'_sale_price',
			)
		);

	}

	/**
	 * Removes orphaned WooCommerce metadata in the Group Settings during Generation,
	 * if WooCommerce is not active
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

		// Remove WooCommerce Meta Keys from the Group Settings during Generation.
		return $this->remove_orphaned_settings_metadata( $settings, $this->meta_keys );

	}

	/**
	 * Define metaboxes that should only display based on the value of Publish > Post Type
	 * in the Content Groups UI.
	 *
	 * @since   2.8.6
	 *
	 * @param   array $metaboxes  Metabox ID Keys and Post Type Values array.
	 * @return  array               Metabox ID Keys and Post Type Values array
	 */
	public function get_post_type_conditional_metaboxes( $metaboxes ) {

		return array_merge(
			$metaboxes,
			array(
				'woocommerce-product-data' => array(
					'product',
				),
			)
		);

	}

	/**
	 * Defines the WooCommerce controls that relate to Page/Post/CPT selection which should be replaced with either an AJAX <select>
	 * or <input> when the Settings > General > Change Page Dropdown Fields is enabled
	 *
	 * @since   2.7.8
	 *
	 * @return  array   WP Customizer Control Names to replace
	 */
	public function replace_customizer_page_dropdowns() {

		return array(
			'wp_page_for_privacy_policy',
			'woocommerce_terms_page_id',
		);

	}

	/**
	 * Removes WooCommerce Product Type, Variation/Visibility and Shipping Classes
	 * Taxonomies from Content Groups.
	 *
	 * Terms for these Taxonomies are defined in WooCommerce's Product Data Metabox
	 *
	 * @since   2.6.9
	 *
	 * @param   array $excluded_taxonomies    Excluded Taxonomies.
	 * @return  array                           Excluded Taxonomies
	 */
	public function excluded_taxonomies( $excluded_taxonomies ) {

		return array_merge(
			$excluded_taxonomies,
			array(
				'product_type',
				'product_visibility',
				'product_shipping_class',
			)
		);

	}

	/**
	 * Registers Content Groups as a WooCommerce screen, ensuring CSS dependencies are enqueued
	 *
	 * @since   2.6.9
	 *
	 * @param   array $screen_ids     Screen IDs.
	 * @return  array                 Screen IDs
	 */
	public function register_content_group_as_woocommerce_screen( $screen_ids ) {

		$screen_ids[] = $this->base->get_class( 'post_type' )->post_type_name;
		return $screen_ids;

	}

	/**
	 * Enqueues JS dependencies
	 *
	 * @since   2.6.9
	 */
	public function enqueue_product_js() {

		global $post;

		// Bail if WooCommerce isn't active.
		if ( ! $this->is_active() ) {
			return;
		}

		// Bail if we're not editing a Content Group.
		if ( ! $this->is_editing_content_group() ) {
			return;
		}

		// Define Post ID, suffix and WC version.
		$post_id = ( isset( $post->ID ) ? $post->ID : '' );
		$suffix  = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '.min' : '' );
		$version = ( defined( 'WC_VERSION' ) && WC_VERSION ? WC_VERSION : false );

		// Enqueue.
		wp_enqueue_media();

		// Enqueue WooCommerce scripts.
		wp_enqueue_script( 'wc-admin-product-meta-boxes', WC()->plugin_url() . '/assets/js/admin/meta-boxes-product' . $suffix . '.js', array( 'wc-admin-meta-boxes', 'media-models' ), $version, false );
		wp_enqueue_script( 'wc-admin-variation-meta-boxes', WC()->plugin_url() . '/assets/js/admin/meta-boxes-product-variation' . $suffix . '.js', array( 'wc-admin-meta-boxes', 'serializejson', 'media-models' ), $version, false );

		// Localize WooCommerce.
		$this->localize_woocommerce_admin_meta_boxes_variations( $post_id );
		$this->localize_woocommerce_admin_meta_boxes( $post_id );

		// Determine whether to load minified versions of JS.
		$minified = $this->base->dashboard->should_load_minified_js();

		// Enqueue woocommerce.js integration to remove validation on some fields, and populate fields where wc_format_decimal() interferes with
		// Keywords, resulting in the WC Product data being blank, despite being stored in the post meta.
		wp_enqueue_script( $this->base->plugin->name . '-woocommerce', $this->base->plugin->url . 'assets/js/' . ( $minified ? 'min/' : '' ) . 'woocommerce' . ( $minified ? '-min' : '' ) . '.js', array( 'jquery' ), $this->base->plugin->version, true );

		// Define any WooCommerce fields here that require Keyword support and WooCommerce uses a formatter for,
		// such as wc_format_decimal() or the Product URL.
		// These formatters are called as part of the set_prop() process when populating a WC Product,
		// which we cannot override due to protected methods.
		// The underlying data is stored in the Post Meta table correctly as e.g. a Keyword due to this class'
		// save_product_data_raw() function.
		// We use JS to 'late' fill the input fields to show the 'correct' unvalidated data.
		wp_localize_script(
			$this->base->plugin->name . '-woocommerce',
			'page_generator_pro_woocommerce',
			array(
				// Keys are IDs of fields.
				// General.
				'_regular_price' => get_post_meta( $post_id, '_regular_price', true ),
				'_sale_price'    => get_post_meta( $post_id, '_sale_price', true ),

				// Inventory.
				'_stock'         => get_post_meta( $post_id, '_stock', true ),

				// Shipping.
				'_weight'        => get_post_meta( $post_id, '_weight', true ),
				'product_length' => get_post_meta( $post_id, '_length', true ),
				'product_width'  => get_post_meta( $post_id, '_width', true ),
				'product_height' => get_post_meta( $post_id, '_height', true ),

				// Product URL.
				'_product_url'   => get_post_meta( $post_id, '_product_url', true ),
			)
		);

	}

	/**
	 * Localize the Product Meta Box for Variations
	 *
	 * This is conditionally performed by WC_Admin_Assets if editing a Product,
	 * so we have to manually localize here.
	 *
	 * @since   2.6.9
	 *
	 * @param   int $post_id    Post ID.
	 */
	private function localize_woocommerce_admin_meta_boxes_variations( $post_id ) {

		$params = array(
			'post_id'                             => $post_id,
			'plugin_url'                          => WC()->plugin_url(),
			'ajax_url'                            => admin_url( 'admin-ajax.php' ),
			'woocommerce_placeholder_img_src'     => wc_placeholder_img_src(),
			'add_variation_nonce'                 => wp_create_nonce( 'add-variation' ),
			'link_variation_nonce'                => wp_create_nonce( 'link-variations' ),
			'delete_variations_nonce'             => wp_create_nonce( 'delete-variations' ),
			'load_variations_nonce'               => wp_create_nonce( 'load-variations' ),
			'save_variations_nonce'               => wp_create_nonce( 'save-variations' ),
			'bulk_edit_variations_nonce'          => wp_create_nonce( 'bulk-edit-variations' ),
			/* translators: %d: Number of variations */
			'i18n_link_all_variations'            => esc_js( sprintf( __( 'Are you sure you want to link all variations? This will create a new variation for each and every possible combination of variation attributes (max %d per run).', 'page-generator-pro' ), defined( 'WC_MAX_LINKED_VARIATIONS' ) ? WC_MAX_LINKED_VARIATIONS : 50 ) ),
			'i18n_enter_a_value'                  => esc_js( __( 'Enter a value', 'page-generator-pro' ) ),
			'i18n_enter_menu_order'               => esc_js( __( 'Variation menu order (determines position in the list of variations)', 'page-generator-pro' ) ),
			'i18n_enter_a_value_fixed_or_percent' => esc_js( __( 'Enter a value (fixed or %)', 'page-generator-pro' ) ),
			'i18n_delete_all_variations'          => esc_js( __( 'Are you sure you want to delete all variations? This cannot be undone.', 'page-generator-pro' ) ),
			'i18n_last_warning'                   => esc_js( __( 'Last warning, are you sure?', 'page-generator-pro' ) ),
			'i18n_choose_image'                   => esc_js( __( 'Choose an image', 'page-generator-pro' ) ),
			'i18n_set_image'                      => esc_js( __( 'Set variation image', 'page-generator-pro' ) ),
			'i18n_variation_added'                => esc_js( __( 'variation added', 'page-generator-pro' ) ),
			'i18n_variations_added'               => esc_js( __( 'variations added', 'page-generator-pro' ) ),
			'i18n_no_variations_added'            => esc_js( __( 'No variations added', 'page-generator-pro' ) ),
			'i18n_remove_variation'               => esc_js( __( 'Are you sure you want to remove this variation?', 'page-generator-pro' ) ),
			'i18n_scheduled_sale_start'           => esc_js( __( 'Sale start date (YYYY-MM-DD format or leave blank)', 'page-generator-pro' ) ),
			'i18n_scheduled_sale_end'             => esc_js( __( 'Sale end date (YYYY-MM-DD format or leave blank)', 'page-generator-pro' ) ),
			'i18n_edited_variations'              => esc_js( __( 'Save changes before changing page?', 'page-generator-pro' ) ),
			'i18n_variation_count_single'         => esc_js( __( '%qty% variation', 'page-generator-pro' ) ),
			'i18n_variation_count_plural'         => esc_js( __( '%qty% variations', 'page-generator-pro' ) ),
			'variations_per_page'                 => absint( apply_filters( 'woocommerce_admin_meta_boxes_variations_per_page', 15 ) ),
		);

		wp_localize_script( 'wc-admin-variation-meta-boxes', 'woocommerce_admin_meta_boxes_variations', $params );

	}

	/**
	 * Localize the Product Meta Box
	 *
	 * This is conditionally performed by WC_Admin_Assets if editing a Product,
	 * so we have to manually localize here.
	 *
	 * @since   2.6.9
	 *
	 * @param   int $post_id    Post ID.
	 */
	private function localize_woocommerce_admin_meta_boxes( $post_id ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter

		$params = array(
			'remove_item_notice'            => __( 'Are you sure you want to remove the selected items?', 'page-generator-pro' ),
			'i18n_select_items'             => __( 'Please select some items.', 'page-generator-pro' ),
			'i18n_do_refund'                => __( 'Are you sure you wish to process this refund? This action cannot be undone.', 'page-generator-pro' ),
			'i18n_delete_refund'            => __( 'Are you sure you wish to delete this refund? This action cannot be undone.', 'page-generator-pro' ),
			'i18n_delete_tax'               => __( 'Are you sure you wish to delete this tax column? This action cannot be undone.', 'page-generator-pro' ),
			'remove_item_meta'              => __( 'Remove this item meta?', 'page-generator-pro' ),
			'remove_attribute'              => __( 'Remove this attribute?', 'page-generator-pro' ),
			'name_label'                    => __( 'Name', 'page-generator-pro' ),
			'remove_label'                  => __( 'Remove', 'page-generator-pro' ),
			'click_to_toggle'               => __( 'Click to toggle', 'page-generator-pro' ),
			'values_label'                  => __( 'Value(s)', 'page-generator-pro' ),
			'text_attribute_tip'            => __( 'Enter some text, or some attributes by pipe (|) separating values.', 'page-generator-pro' ),
			'visible_label'                 => __( 'Visible on the product page', 'page-generator-pro' ),
			'used_for_variations_label'     => __( 'Used for variations', 'page-generator-pro' ),
			'new_attribute_prompt'          => __( 'Enter a name for the new attribute term:', 'page-generator-pro' ),
			'calc_totals'                   => __( 'Recalculate totals? This will calculate taxes based on the customers country (or the store base country) and update totals.', 'page-generator-pro' ),
			'copy_billing'                  => __( 'Copy billing information to shipping information? This will remove any currently entered shipping information.', 'page-generator-pro' ),
			'load_billing'                  => __( "Load the customer's billing information? This will remove any currently entered billing information.", 'page-generator-pro' ),
			'load_shipping'                 => __( "Load the customer's shipping information? This will remove any currently entered shipping information.", 'page-generator-pro' ),
			'featured_label'                => __( 'Featured', 'page-generator-pro' ),
			'prices_include_tax'            => esc_attr( get_option( 'woocommerce_prices_include_tax' ) ),
			'tax_based_on'                  => esc_attr( get_option( 'woocommerce_tax_based_on' ) ),
			'round_at_subtotal'             => esc_attr( get_option( 'woocommerce_tax_round_at_subtotal' ) ),
			'no_customer_selected'          => __( 'No customer selected', 'page-generator-pro' ),
			'plugin_url'                    => WC()->plugin_url(),
			'ajax_url'                      => admin_url( 'admin-ajax.php' ),
			'order_item_nonce'              => wp_create_nonce( 'order-item' ),
			'add_attribute_nonce'           => wp_create_nonce( 'add-attribute' ),
			'save_attributes_nonce'         => wp_create_nonce( 'save-attributes' ),
			'calc_totals_nonce'             => wp_create_nonce( 'calc-totals' ),
			'get_customer_details_nonce'    => wp_create_nonce( 'get-customer-details' ),
			'search_products_nonce'         => wp_create_nonce( 'search-products' ),
			'grant_access_nonce'            => wp_create_nonce( 'grant-access' ),
			'revoke_access_nonce'           => wp_create_nonce( 'revoke-access' ),
			'add_order_note_nonce'          => wp_create_nonce( 'add-order-note' ),
			'delete_order_note_nonce'       => wp_create_nonce( 'delete-order-note' ),
			'calendar_image'                => WC()->plugin_url() . '/assets/images/calendar.png',
			'post_id'                       => $post_id,
			'base_country'                  => WC()->countries->get_base_country(),
			'currency_format_num_decimals'  => wc_get_price_decimals(),
			'currency_format_symbol'        => get_woocommerce_currency_symbol(),
			'currency_format_decimal_sep'   => esc_attr( wc_get_price_decimal_separator() ),
			'currency_format_thousand_sep'  => esc_attr( wc_get_price_thousand_separator() ),
			'currency_format'               => esc_attr( str_replace( array( '%1$s', '%2$s' ), array( '%s', '%v' ), get_woocommerce_price_format() ) ), // For accounting JS.
			'rounding_precision'            => wc_get_rounding_precision(),
			'tax_rounding_mode'             => wc_get_tax_rounding_mode(),
			'product_types'                 => array_unique( array_merge( array( 'simple', 'grouped', 'variable', 'external' ), array_keys( wc_get_product_types() ) ) ),
			'i18n_download_permission_fail' => __( 'Could not grant access - the user may already have permission for this file or billing email is not set. Ensure the billing email is set, and the order has been saved.', 'page-generator-pro' ),
			'i18n_permission_revoke'        => __( 'Are you sure you want to revoke access to this download?', 'page-generator-pro' ),
			'i18n_tax_rate_already_exists'  => __( 'You cannot add the same tax rate twice!', 'page-generator-pro' ),
			'i18n_delete_note'              => __( 'Are you sure you wish to delete this note? This action cannot be undone.', 'page-generator-pro' ),
			'i18n_apply_coupon'             => __( 'Enter a coupon code to apply. Discounts are applied to line totals, before taxes.', 'page-generator-pro' ),
			'i18n_add_fee'                  => __( 'Enter a fixed amount or percentage to apply as a fee.', 'page-generator-pro' ),
		);

		wp_localize_script( 'wc-admin-meta-boxes', 'woocommerce_admin_meta_boxes', $params );

	}

	/**
	 * Determines the Product Type this Content Group is for.
	 *
	 * Called by the woocommerce_product_type_query filter, bypassing
	 * the check made to WC_Data_Store::load( 'product' )->get_product_type() that
	 * will always return false because it checks the Post Type (which is a Content Group)
	 *
	 * @since   3.3.9
	 *
	 * @param   bool|string $product_type   Product Type (false | simple | external).
	 * @param   int         $product_id     Product ID.
	 * @return  bool|string                 Product Type
	 */
	public function get_product_type( $product_type, $product_id ) {

		// If the Product ID's Post Type isn's a Content Group, don't interfere with the
		// $product_type value.
		if ( get_post_type( $product_id ) !== 'page-generator-pro' ) {
			// Returns false.
			return $product_type;
		}

		// Copied from woocommerce/includes/data-stores/class-wc-product-data-store-cpt.php::get_product_type(),
		// minus the Post Type checking.
		$terms        = get_the_terms( $product_id, 'product_type' );
		$product_type = ! empty( $terms ) && ! is_wp_error( $terms ) ? sanitize_title( current( $terms )->name ) : 'simple';

		return $product_type;

	}

	/**
	 * Adds WooCommerce Product Meta Boxes to Content Groups
	 *
	 * @since   2.6.9
	 */
	public function add_meta_boxes() {

		// Bail if WooCommerce isn't active.
		if ( ! $this->is_active() ) {
			return;
		}

		// Bail if we're not editing a Content Group.
		if ( ! $this->is_editing_content_group() ) {
			return;
		}

		add_meta_box( 'woocommerce-product-data', __( 'Product data', 'page-generator-pro' ), 'WC_Meta_Box_Product_Data::output', $this->base->get_class( 'post_type' )->post_type_name, 'normal' );

	}

	/**
	 * Replaces WC_Product_Data_Store_CPT with Page_Generator_Pro_WC_Product_Data_Store_CPT,
	 * which bypasses the Product Post Type check that WC_Product_Data_Store_CPT::read() uses
	 * that results in an "Invalid product" exception when editing a Content Group.
	 *
	 * @since   2.6.9
	 *
	 * @param   array $stores     WooCommerce Data Stores.
	 * @return  array               WooCommerce Data Stores
	 */
	public function woocommerce_data_stores( $stores ) {

		// Bail if WooCommerce isn't active.
		if ( ! $this->is_active() ) {
			return $stores;
		}

		// Bail if we're not editing a Content Group.
		if ( ! $this->is_editing_content_group() ) {
			return $stores;
		}

		$stores['product']          = 'Page_Generator_Pro_WC_Product_Data_Store_CPT';
		$stores['product-grouped']  = 'Page_Generator_Pro_WC_Product_Grouped_Data_Store_CPT';
		$stores['product-variable'] = 'Page_Generator_Pro_WC_Product_Variable_Data_Store_CPT';

		return $stores;

	}

	/**
	 * Saves WooCommerce Product information within a Content Group, by
	 * - Adding the necessary action hooks to process and save Product Meta,
	 * - Calling WooCommerce's action to process and save Product Meta
	 *
	 * @since   2.6.9
	 *
	 * @param   int     $post_id    Post ID.
	 * @param   WP_Post $post       Post.
	 */
	public function save_product( $post_id, $post ) {

		// Bail if WooCommerce isn't active.
		if ( ! $this->is_active() ) {
			return;
		}

		// Bail if we're not editing a Content Group.
		if ( ! $this->is_editing_content_group() ) {
			return;
		}

		// Add WooCommerce actions that process and save product data now, as WooCommerce won't have
		// registered these because we're not saving a Product.
		add_action( 'woocommerce_process_product_meta', 'WC_Meta_Box_Product_Data::save', 10, 2 );
		add_action( 'woocommerce_process_product_meta', 'WC_Meta_Box_Product_Images::save', 20, 2 );
		add_action( 'woocommerce_process_product_meta', array( $this, 'save_product_data_raw' ), 30, 2 );

		// This will call the above hooks in WooCommerce which perform the save.
		do_action( 'woocommerce_process_product_meta', $post_id, $post );

	}

	/**
	 * Manually save product properties to the post meta table where WooCommerce will strip the
	 * Keyword due to the property having a formatter applied to it.
	 *
	 * @since   3.6.4
	 *
	 * @param   int     $post_id    Post ID (Content Group ID).
	 * @param   WP_Post $post       Post (Content Group).
	 */
	public function save_product_data_raw( $post_id, $post ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter

		// phpcs:disable WordPress.Security.NonceVerification

		// Skip if not defined.
		if ( ! array_key_exists( '_regular_price', $_REQUEST ) ) {
			return;
		}

		// General.
		update_post_meta( $post_id, '_regular_price', isset( $_REQUEST['_regular_price'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_regular_price'] ) ) : '' );
		update_post_meta( $post_id, '_sale_price', isset( $_REQUEST['_sale_price'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_sale_price'] ) ) : '' );
		delete_post_meta( $post_id, '_price' ); // Deliberate; this stores either the regular or sale price depending on conditions set.

		// Inventory.
		update_post_meta( $post_id, '_stock', isset( $_REQUEST['_stock'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_stock'] ) ) : '' );

		// Shipping.
		update_post_meta( $post_id, '_weight', isset( $_REQUEST['_weight'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_weight'] ) ) : '' );
		update_post_meta( $post_id, '_length', isset( $_REQUEST['_length'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_length'] ) ) : '' );
		update_post_meta( $post_id, '_width', isset( $_REQUEST['_width'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_width'] ) ) : '' );
		update_post_meta( $post_id, '_height', isset( $_REQUEST['_height'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_height'] ) ) : '' );

		// Product URL.
		update_post_meta( $post_id, '_product_url', isset( $_REQUEST['_product_url'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_product_url'] ) ) : '' );
		// phpcs:enable

	}

	/**
	 * Sets the Product Type Taxonomy Term for a generated Product.
	 *
	 * @since   3.3.9
	 *
	 * @param   int   $post_id        Generated Post ID.
	 * @param   int   $group_id       Group ID.
	 * @param   array $settings       Group Settings.
	 * @param   int   $index          Keyword Index.
	 * @param   bool  $test_mode      Test Mode.
	 * @param   array $post_args      wp_insert_post() / wp_update_post() compatible arguments.
	 */
	public function generate_content_set_product_type( $post_id, $group_id, $settings, $index, $test_mode, $post_args ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter

		// Bail if the Generated Content is not a Product.
		if ( $settings['type'] !== 'product' ) {
			return;
		}

		// Determine if we want to create/replace this integration's metdata.
		$overwrite = ( isset( $post_args['ID'] ) && ! array_key_exists( $this->overwrite_section, $settings['overwrite_sections'] ) ? false : true );
		if ( ! $overwrite ) {
			return;
		}

		// Get Product Type Taxonomy Term from Group.
		$product_type = wp_get_post_terms( $group_id, 'product_type' );

		// Bail if no Product Type.
		if ( is_wp_error( $product_type ) ) {
			return;
		}
		if ( ! count( $product_type ) ) {
			return;
		}

		// Set Product Type Taxonomy Term for Generated Product.
		wp_set_object_terms( $post_id, absint( $product_type[0]->term_id ), 'product_type' );

	}

	/**
	 * Sets the Product regular and sale prices for a generated product.
	 *
	 * @since   3.9.9
	 *
	 * @param   int   $post_id        Generated Post ID.
	 * @param   int   $group_id       Group ID.
	 * @param   array $settings       Group Settings.
	 * @param   int   $index          Keyword Index.
	 * @param   bool  $test_mode      Test Mode.
	 * @param   array $post_args      wp_insert_post() / wp_update_post() compatible arguments.
	 */
	public function generate_content_set_product_price( $post_id, $group_id, $settings, $index, $test_mode, $post_args ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter

		// Bail if the Generated Content is not a Product.
		if ( $settings['type'] !== 'product' ) {
			return;
		}

		// Determine if we want to create/replace this integration's metdata.
		$overwrite = ( isset( $post_args['ID'] ) && ! array_key_exists( $this->overwrite_section, $settings['overwrite_sections'] ) ? false : true );
		if ( ! $overwrite ) {
			return;
		}

		// Get Product.
		$product = wc_get_product( $post_id );

		// Bail if the Product could not be fetched.
		if ( ! $product ) {
			return;
		}

		// Define Regular and Sale Prices.
		// These aren't in the Product's post meta because they're excluded in ignore_price_meta_keys(),
		// otherwise setting them now would fail as no change is made.
		if ( array_key_exists( '_regular_price', $settings['post_meta'] ) ) {
			$product->set_regular_price( $settings['post_meta']['_regular_price'] );
		}
		if ( array_key_exists( '_sale_price', $settings['post_meta'] ) ) {
			$product->set_sale_price( $settings['post_meta']['_sale_price'] );
		}

		// Save.
		// This forces WooCommerce to calculate the true price (regular or sale), storing it in _price.
		$product->save();

	}

	/**
	 * Checks if we're editing a Content Group
	 *
	 * @since   2.6.9
	 *
	 * @return  bool    Editing Content Group
	 */
	private function is_editing_content_group() {

		// Not editing a Content Group if we're not in the WordPress Admin.
		if ( ! is_admin() ) {
			return false;
		}

		$screen = $this->base->get_class( 'screen' )->get_current_screen();
		if ( $screen['screen'] !== 'content_groups' ) {
			return false;
		}
		if ( $screen['section'] !== 'edit' ) {
			return false;
		}

		// Editing a Content Group.
		return true;

	}

}
