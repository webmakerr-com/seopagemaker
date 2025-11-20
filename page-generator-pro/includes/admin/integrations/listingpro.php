<?php
/**
 * ListingPro Integration Class
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Registers ListingPro as a Plugin integration:
 * - Enable ListingPro metaboxes in Content Groups
 * - Register as an overwrite section in Content Groups
 * - Copy / don't copy metadata to generated Pages, depending on if the integration is active
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 2.8.6
 */
class Page_Generator_Pro_ListingPro extends Page_Generator_Pro_Integration {

	/**
	 * Holds the base object.
	 *
	 * @since   2.8.6
	 *
	 * @var     object
	 */
	public $base;

	/**
	 * Constructor
	 *
	 * @since   2.8.6
	 *
	 * @param   object $base    Base Plugin Class.
	 */
	public function __construct( $base ) {

		// Store base class.
		$this->base = $base;

		// Set Plugin.
		$this->plugin_folder_filename = 'listingpro-plugin/plugin.php';

		// Set Meta Keys used by this Plugin.
		$this->meta_keys = array(
			'lp_listingpro_options',
			'lp_listingpro_options_fields',
		);

		// Set Overwrite Setting's Key used by this Plugin.
		$this->overwrite_section = 'listingpro';

		// Add Overwrite Section if ListingPro enabled.
		add_filter( 'page_generator_pro_common_get_content_overwrite_sections', array( $this, 'add_overwrite_section' ) );

		// Ignore ListingPro meta keys if overwriting is disabled for ListingPro.
		add_filter( 'page_generator_pro_generate_set_post_meta_ignored_keys', array( $this, 'prevent_post_meta_copy_to_generated_content' ), 10, 4 );

		// Remove Plugin data from Group Settings if Plugin isn't active on Generation.
		add_filter( 'page_generator_pro_groups_get_settings_remove_orphaned_settings', array( $this, 'remove_orphaned_settings' ) );

		add_filter( 'page_generator_pro_common_get_excluded_post_types', array( $this, 'exclude_post_types' ) );
		add_filter( 'page_generator_pro_groups_ui_get_post_type_conditional_metaboxes', array( $this, 'get_post_type_conditional_metaboxes' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'listings_enqueue_scripts' ) );
		add_action( 'page_generator_pro_groups_ui_add_meta_boxes', array( $this, 'listings_add_meta_boxes' ) );
		add_action( 'page_generator_pro_groups_save', array( $this, 'save' ), 10, 3 );
		add_action( 'page_generator_pro_groups_save', array( $this, 'save_event' ), 10, 3 );

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

		// Bail if ListingPro isn't active.
		if ( ! $this->is_active() ) {
			return $sections;
		}

		// Add ListingPro.
		$sections[ $this->overwrite_section ] = __( 'ListingPro', 'page-generator-pro' );

		// Return.
		return $sections;

	}

	/**
	 * Removes orphaned metadata in the Group Settings during Generation,
	 * if ListingPro is not active
	 *
	 * @since   3.3.7
	 *
	 * @param   array $settings   Group Settings.
	 * @return  array               Group Settings
	 */
	public function remove_orphaned_settings( $settings ) {

		// Don't remove settings if ListingPro is active.
		if ( $this->is_active() ) {
			return $settings;
		}

		// Remove ListingPro Meta Keys from the Group Settings during Generation.
		return $this->remove_orphaned_settings_metadata( $settings, $this->meta_keys );

	}

	/**
	 * Excludes some ListingPro Post Types that we don't support generation for
	 *
	 * @since   2.8.6
	 *
	 * @param   array $post_types     Excluded Post Types.
	 * @return  array                   Excluded Post Types
	 */
	public function exclude_post_types( $post_types ) {

		// Bail if ListingPro isn't active.
		if ( ! function_exists( 'is_plugin_active' ) || ! is_plugin_active( 'listingpro-plugin/plugin.php' ) ) {
			return $post_types;
		}

		return array_merge(
			$post_types,
			array(
				'lp-ads',
				'lp-claims',
			)
		);

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
				'listing_meta_settings' => array(
					'listing',
				),
				'Reviews_meta_settings' => array(
					'lp-reviews',
				),
				'event_meta_box'        => array(
					'events',
				),
			)
		);

	}

	/**
	 * Enqueue ListingPro scripts to Content Groups for the Listings Post Type
	 *
	 * @since   2.8.6
	 */
	public function listings_enqueue_scripts() {

		// Bail if ListingPro isn't active.
		if ( ! function_exists( 'is_plugin_active' ) || ! is_plugin_active( 'listingpro-plugin/plugin.php' ) ) {
			return;
		}

		wp_enqueue_script( 'jquery-ui', get_template_directory_uri() . '/assets/js/jquery-ui.js', array( 'jquery' ), $this->base->plugin->version, true );
		wp_enqueue_script( 'jquery-ui-trigger', get_template_directory_uri() . '/assets/js/jquery-ui-trigger.js', array( 'jquery' ), $this->base->plugin->version, true );
		wp_localize_script( 'jquery-ui-trigger', 'global', array( 'ajax' => admin_url( 'admin-ajax.php' ) ) );
		wp_enqueue_script( 'jquery-droppin', get_template_directory_uri() . '/assets/js/drop-pin.js', array( 'jquery' ), $this->base->plugin->version, true );

	}

	/**
	 * Adds ListingPro Meta Boxes to Content Groups for the Listings Post Type
	 *
	 * @since   2.8.6
	 */
	public function listings_add_meta_boxes() {

		global $listingpro_settings, $reviews_options;

		// Bail if ListingPro isn't active.
		if ( ! function_exists( 'is_plugin_active' ) || ! is_plugin_active( 'listingpro-plugin/plugin.php' ) ) {
			return;
		}

		// Listing.
		add_meta_box(
			'listing_meta_settings',
			esc_html__( 'listing settings', 'page-generator-pro' ),
			'listingpro_metabox_render',
			$this->base->get_class( 'post_type' )->post_type_name,
			'normal',
			'high',
			$listingpro_settings
		);

		// Review.
		add_meta_box(
			'Reviews_meta_settings',
			esc_html__( 'Additional Setting', 'page-generator-pro' ),
			'reviews_metabox_render',
			$this->base->get_class( 'post_type' )->post_type_name,
			'normal',
			'high',
			$reviews_options
		);

		// Event.
		add_meta_box(
			'event_meta_box',
			__( 'Event Details', 'page-generator-pro' ),
			'event_meta_box',
			$this->base->get_class( 'post_type' )->post_type_name,
			'normal',
			'high'
		);

	}

	/**
	 * Saves ListingPro metadata from any metaboxes displayed on the Content Group,
	 * emulating ListingPro's savePostMeta() function
	 *
	 * @since   2.8.6
	 *
	 * @param   int   $group_id   Group ID.
	 * @param   array $settings   Group Settings.
	 * @param   array $request    $_REQUEST data, unsanitized.
	 */
	public function save( $group_id, $settings, $request ) {

		global $listingpro_settings, $reviews_options, $listingpro_formFields, $claim_options, $ads_options, $page_options, $post_options, $price_plans_options; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.VariableNotSnakeCase

		// Bail if ListingPro isn't active.
		if ( ! function_exists( 'is_plugin_active' ) || ! is_plugin_active( 'listingpro-plugin/plugin.php' ) ) {
			return;
		}

		// Define meta key.
		$meta_key   = 'lp_' . strtolower( THEMENAME ) . '_options';
		$meta_value = array();
		$metaboxes  = false;

		// Depending on the Post Type this Content Group will generate, define the metaboxes to save data for.
		switch ( $settings['type'] ) {

			case 'lp-reviews':
				foreach ( $reviews_options as $metabox ) {
					$meta_value[ $metabox['id'] ] = ( isset( $request[ $metabox['id'] ] ) ? $request[ $metabox['id'] ] : '' );
				}

				// Update and exit.
				update_post_meta( $group_id, $meta_key, $meta_value );
				return;

			case 'lp-ads':
				$metaboxes = $ads_options;
				break;

			case 'listing':
				$metaboxes = $listingpro_settings;
				break;

			case 'lp-claims':
				$metaboxes = $claim_options;
				break;

		}

		// Bail if no metaboxes to save meta for.
		if ( ! $metaboxes ) {
			return;
		}

		// Iterate through metaboxes, building meta values array and saving it to the Group.
		foreach ( $metaboxes as $metabox ) {
			$meta_value[ $metabox['id'] ] = ( isset( $request[ $metabox['id'] ] ) ? $request[ $metabox['id'] ] : '' );
		}
		update_post_meta( $group_id, $meta_key, $meta_value );

		// Update Form Fields Meta.
		if ( isset( $request['lp_form_fields_inn'] ) ) {
			$fields = array_merge(
				$request['lp_form_fields_inn'],
				lp_save_extra_fields_in_listing( $request['lp_form_fields_inn'], $group_id )
			);
		} else {
			$fields = '';
		}
		update_post_meta( $group_id, 'lp_' . strtolower( THEMENAME ) . '_options_fields', $fields );

	}

	/**
	 * Saves ListingPro Events metadata from any metaboxes displayed on the Content Group,
	 * emulating ListingPro's save_event_metas() function
	 *
	 * @since   2.8.6
	 *
	 * @param   int   $group_id   Group ID.
	 * @param   array $settings   Group Settings.
	 * @param   array $request    $_REQUEST data, unsanitized.
	 */
	public function save_event( $group_id, $settings, $request ) {

		// Bail if ListingPro isn't active.
		if ( ! function_exists( 'is_plugin_active' ) || ! is_plugin_active( 'listingpro-plugin/plugin.php' ) ) {
			return;
		}

		// Bail if the Content Group isn't for Events.
		if ( $settings['type'] !== 'events' ) {
			return;
		}

		$event_date       = strtotime( sanitize_text_field( $request['event_date'] ) );
		$event_time       = sanitize_text_field( $request['event_time'] );
		$event_loc        = sanitize_text_field( $request['event_loc'] );
		$event_lat        = sanitize_text_field( $request['event_lat'] );
		$event_lon        = sanitize_text_field( $request['event_lon'] );
		$event_ticket_url = sanitize_text_field( $request['event_ticket_url'] );
		$event_id         = absint( sanitize_text_field( $request['event-lsiting-id'] ) ); // Misspelling is deliberate.

		$get_current_event_ids = get_post_meta( $event_id, 'event_id', true );
		if ( isset( $get_current_event_ids ) && is_array( $get_current_event_ids ) ) {
			$get_current_event_ids[] = $event_id;
		}
		if ( isset( $get_current_event_ids ) && ! is_array( $get_current_event_ids ) ) {
			$get_current_event_ids   = (array) $get_current_event_ids;
			$get_current_event_ids[] = $event_id;
		}

		update_post_meta( $event_id, 'event_id', $get_current_event_ids );

		update_post_meta( $group_id, 'event-lsiting-id', $event_id ); // Misspelling is deliberate.
		update_post_meta( $group_id, 'event-date', $event_date );
		update_post_meta( $group_id, 'event-time', $event_time );
		update_post_meta( $group_id, 'event-loc', $event_loc );
		update_post_meta( $group_id, 'event-lat', $event_lat );
		update_post_meta( $group_id, 'event-lon', $event_lon );
		update_post_meta( $group_id, 'ticket-url', $event_ticket_url );

	}

}
