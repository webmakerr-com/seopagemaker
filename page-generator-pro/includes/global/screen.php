<?php
/**
 * Screen Class
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Safely determines which screen a request is for,
 * even if get_current_screen() isn't available.
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 2.2.4
 */
class Page_Generator_Pro_Screen {

	/**
	 * Holds the base object.
	 *
	 * @since   2.2.4
	 *
	 * @var     object
	 */
	public $base;

	/**
	 * Constructor
	 *
	 * @since   2.2.4
	 *
	 * @param   object $base    Base Plugin Class.
	 */
	public function __construct( $base ) {

		// Store base class.
		$this->base = $base;

	}

	/**
	 * Returns an array comprising of a simplified screen and section that we are viewing
	 * within the WordPress Administration interface.
	 *
	 * Non-Plugin screens will be returned if we need to hook into them.
	 *
	 * For example:
	 * [
	 *  'screen' => 'keywords',
	 *  'section' => 'generate_locations',
	 * ]
	 *
	 * Returns false if we're not on a screen that the Plugin needs to interact with.
	 *
	 * @since   2.2.4
	 *
	 * @return  array   Screen and Section (if false, we're not on this Plugin's screens)
	 */
	public function get_current_screen() {

		global $post;

		// Assume we're not on a plugin screen.
		$result = array(
			'screen'  => false,
			'section' => false,
		);

		/**
		 * Returns an array comprising of a simplified screen and section that we are viewing
		 * within the WordPress Administration interface, before we've performed any checks.
		 *
		 * This is useful for frontend Page Builders and AJAX requests where get_current_screen()
		 * below won't return anything.
		 *
		 * @since   2.5.7
		 *
		 * @param   array       $result     Screen and Section.
		 */
		$result = apply_filters( 'page_generator_pro_screen_get_current_screen_before', $result );

		// If we're on the frontend, check if we're editing a Content Group.
		if ( ! is_admin() ) {
			// Editing a Content Group.
			if ( ! is_null( $post ) && $this->base->plugin->name === get_post_type( $post ) ) {
				return array(
					'screen'  => 'content_groups',
					'section' => 'edit',
				);
			}

			// Editing a Content Group on the frontend.
			if ( isset( $_SERVER['REQUEST_URI'] ) && stripos( esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ), $this->base->plugin->name ) !== false ) {
				return array(
					'screen'  => 'content_groups',
					'section' => 'edit',
				);
			}

			// Not editing a Content Group.
			return $result;
		}

		// Bail if we can't determine this.
		if ( ! function_exists( 'get_current_screen' ) ) {
			return $result;
		}

		// Get screen.
		$screen = get_current_screen();

		// Get screen ID without Plugin Display Name, which can be edited by whitelabelling.
		$screen_id = str_replace(
			array(
				'toplevel_page_', // licensing = page-generator-pro.
				sanitize_title( $this->base->plugin->displayName ) . '_page_',
			),
			'',
			( isset( $screen->id ) ? $screen->id : '' )
		); // was $screen->id.

		switch ( $screen_id ) {

			/**
			 * Settings
			 */
			case $this->base->plugin->name . '-settings':
				$result = array(
					'screen'  => 'settings',
					'section' => ( isset( $_REQUEST['tab'] ) ? str_replace( $this->base->plugin->name . '-', '', sanitize_text_field( wp_unslash( $_REQUEST['tab'] ) ) ) : 'general' ), // phpcs:ignore WordPress.Security.NonceVerification
				);
				break;

			/**
			 * Groups Directory
			 */
			case $this->base->plugin->name . '-groups-directory':
				$result = array(
					'screen'  => 'groups-directory',
					'section' => 'groups-directory',
				);
				break;

			/**
			 * Groups OpenAI
			 */
			case $this->base->plugin->name . '-groups-ai':
				$result = array(
					'screen'  => 'groups-ai',
					'section' => 'groups-ai',
				);
				break;

			/**
			 * Keywords
			 */
			case $this->base->plugin->name . '-keywords':
				$cmd = ( isset( $_REQUEST['cmd'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['cmd'] ) ) : false ); // phpcs:ignore WordPress.Security.NonceVerification
				switch ( $cmd ) {
					// Keywords WP_List_Table.
					case false:
						$result = array(
							'screen'  => 'keywords',
							'section' => 'wp_list_table',
						);
						break;

					// Add/Edit.
					case 'form':
						$result = array(
							'screen'  => 'keywords',
							'section' => 'edit',
						);
						break;

					// Generate Locations.
					case 'form-locations':
						$result = array(
							'screen'  => 'keywords',
							'section' => 'generate_locations',
						);
						break;

					// Generate Phone Area Codes.
					case 'form-phone':
						$result = array(
							'screen'  => 'keywords',
							'section' => 'generate_phone_area_codes',
						);
						break;
				}
				break;

			/**
			 * Content: Groups: Table
			 */
			case 'edit-' . $this->base->plugin->name:
				switch ( $screen->action ) {
					// WP_List_Table.
					case '':
						$result = array(
							'screen'  => 'content_groups',
							'section' => 'wp_list_table',
						);
						break;
				}
				break;

			/**
			 * Licensing
			 * Content: Groups: Add/Edit
			 */
			case $this->base->plugin->name:
			case 'page-generator-pro':
				switch ( $screen->base ) {
					case 'toplevel_page_' . $this->base->plugin->name:
						$result = array(
							'screen'  => 'licensing',
							'section' => 'licensing',
						);
						break;

					case 'post':
						$result = array(
							'screen'  => 'content_groups',
							'section' => 'edit',
						);
						break;
				}
				break;

			/**
			 * Content: Terms: Add/Edit
			 */
			case 'edit-page-generator-tax':
				switch ( $screen->base ) {
					// WP_List_Table.
					case 'edit-tags':
						$result = array(
							'screen'  => 'content_terms',
							'section' => 'wp_list_table',
						);
						break;

					// Edit.
					case 'term':
						$result = array(
							'screen'  => 'content_terms',
							'section' => 'edit',
						);
						break;
				}
				break;

			/**
			 * Content: Generate
			 */
			case $this->base->plugin->name . '-generate':
				$result = array(
					'screen'  => 'generate',
					'section' => 'generate',
				);
				break;

			/**
			 * Logs
			 */
			case $this->base->plugin->name . '-logs':
				$result = array(
					'screen'  => 'logs',
					'section' => 'logs',
				);
				break;

			/**
			 * Posts, Pages
			 */
			case 'edit-post':
			case 'edit-page':
				$result = array(
					'screen'  => 'post',
					'section' => 'wp_list_table',
				);
				break;

			case 'post':
			case 'page':
				$result = array(
					'screen'  => 'post',
					'section' => 'edit',
				);
				break;

			/**
			 * Appearance > Editor
			 */
			case 'site-editor':
				$result = array(
					'screen'  => 'appearance',
					'section' => 'edit',
				);
				break;

			/**
			 * Appearance > Customize
			 */
			case 'customize':
				$result = array(
					'screen'  => 'appearance',
					'section' => 'customize',
				);
				break;

			/**
			 * Settings > Reading
			 */
			case 'options-reading':
				$result = array(
					'screen'  => 'options',
					'section' => 'reading',
				);
				break;
		}

		/**
		 * Returns an array comprising of a simplified screen and section that we are viewing
		 * within the WordPress Administration interface.
		 *
		 * @since   2.5.7
		 *
		 * @param   array       $result     Screen and Section.
		 * @param   string      $screen_id  Screen.
		 * @param   WP_Screen   $screen     WordPress Screen object.
		 * @return  array                   Screen and Section
		 */
		$result = apply_filters( 'page_generator_pro_screen_get_current_screen', $result, $screen_id, $screen );

		// If here, we couldn't determine the screen.
		return $result;

	}

}
