<?php
/**
 * Administration Class
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Registers the Plugin's menus and screens, saving Plugin wide settings.
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 1.0.0
 */
class Page_Generator_Pro_Admin {

	/**
	 * Holds the base object.
	 *
	 * @since   1.2.1
	 *
	 * @var     object
	 */
	public $base;

	/**
	 * Constructor
	 *
	 * @since   1.0.0
	 *
	 * @param   object $base    Base Plugin Class.
	 */
	public function __construct( $base ) {

		// Store base class.
		$this->base = $base;

		// Check Plugin Setup.
		add_action( 'init', array( $this, 'check_plugin_setup' ) );

		// Admin Notices.
		add_action( 'admin_notices', array( $this, 'admin_notices' ) );

		// Admin CSS, JS and Menu.
		add_filter( 'wpzinc_admin_body_class', array( $this, 'admin_body_class' ) ); // WordPress Admin.
		add_filter( 'body_class', array( $this, 'body_class' ) ); // Frontend Editors.

		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts_css' ) ); // WordPress Admin.
		add_action( 'wp_enqueue_scripts', array( $this, 'admin_scripts_css' ) ); // Frontend Editors.

		add_action( 'admin_menu', array( $this, 'admin_menu' ), 8 );
		add_filter( 'parent_file', array( $this, 'admin_menu_hierarchy_correction' ), 999 );

		// Keywords: Bulk and Row Actions.
		add_filter( 'set-screen-option', array( $this, 'set_screen_options' ), 10, 3 );
		add_action( 'init', array( $this, 'run_keyword_save_actions' ) );
		add_action( 'current_screen', array( $this, 'run_keyword_table_bulk_actions' ) );
		add_action( 'current_screen', array( $this, 'run_keyword_table_row_actions' ) );

		// Settings Panels.
		add_filter( 'page_generator_pro_setting_panel', array( $this, 'register_settings_panel' ), 1 );
		add_action( 'page_generator_pro_setting_panel_page-generator-pro-general', array( $this, 'settings_screen_general' ), 1 );
		add_action( 'page_generator_pro_setting_panel_page-generator-pro-generate', array( $this, 'settings_screen_generate' ), 1 );
		add_action( 'page_generator_pro_setting_panel_page-generator-pro-generate-locations', array( $this, 'settings_screen_generate_locations' ), 1 );
		add_action( 'page_generator_pro_setting_panel_page-generator-pro-integrations', array( $this, 'settings_screen_integrations' ), 1 );
		add_action( 'page_generator_pro_setting_panel_page-generator-pro-research', array( $this, 'settings_screen_research' ), 1 );
		add_action( 'page_generator_pro_setting_panel_page-generator-pro-spintax', array( $this, 'settings_screen_generate_spintax' ), 1 );

	}

	/**
	 * Displays a dismissible WordPress notification if required functions aren't available in PHP.
	 *
	 * @since   2.6.3
	 */
	public function check_plugin_setup() {

		// Define array of errors.
		$errors = array();

		// Check PHP version.
		if ( phpversion() < $this->base->plugin->php_requires ) {
			$errors[] = sprintf(
				'%1$s <a href="https://www.php.net/supported-versions.php" target="_blank">%2$s</a>%3$s',
				sprintf(
					/* translators: %1$s: Required PHP Version, %2$s: Detected PHP Version */
					esc_html__( 'PHP %1$s or higher is required. This site currently runs PHP %2$s, which', 'page-generator-pro' ),
					$this->base->plugin->php_requires,
					phpversion()
				),
				esc_html__( 'is end of life and isn\'t officially supported by PHP.', 'page-generator-pro' ),
				sprintf(
					/* translators: Required PHP Version */
					esc_html__( 'Your web host needs to upgrade to at least PHP %s', 'page-generator-pro' ),
					$this->base->plugin->php_requires
				)
			);
		}

		// Define required PHP functions that might not be available on all PHP installations.
		$required_functions = array(
			'mb_convert_case'     => __( 'Install the mbstring and gd PHP libraries.', 'page-generator-pro' ),
			'mb_detect_encoding'  => __( 'Install the mbstring and gd PHP libraries.', 'page-generator-pro' ),
			'mb_convert_encoding' => __( 'Install the mbstring and gd PHP libraries.', 'page-generator-pro' ),
			'mb_strtoupper'       => __( 'Install the mbstring and gd PHP libraries.', 'page-generator-pro' ),
		);

		// Iterate through required functions.
		foreach ( $required_functions as $required_function => $resolution ) {
			if ( ! function_exists( $required_function ) ) {
				$errors[] = sprintf(
					'<code>%1$s</code> %2$s',
					$required_function,
					sprintf(
						/* translators: Instructions to fix error, already translated */
						esc_html__( 'PHP function does not exist. %s', 'page-generator-pro' ),
						$resolution
					)
				);
			}
		}

		// If no errors, nothing to show.
		if ( ! count( $errors ) ) {
			return;
		}

		// Output errors.
		$this->base->get_class( 'notices' )->add_error_notice(
			sprintf(
				/* translators: %1$s: Plugin Name, %2$s: Error message(s) */
				__( '%1$s detected the following issues that need resolving to ensure correct working functionality:<br />%2$s', 'page-generator-pro' ),
				$this->base->plugin->displayName,
				implode( '<br />', $errors )
			)
		);

	}

	/**
	 * Checks the transient to see if any admin notices need to be output now.
	 *
	 * @since   1.2.3
	 */
	public function admin_notices() {

		// Determine the screen that we're on.
		$screen = $this->base->get_class( 'screen' )->get_current_screen();

		// If we're not on a plugin screen, exit.
		if ( ! $screen['screen'] ) {
			return;
		}

		// Output notices.
		$this->base->get_class( 'notices' )->set_key_prefix( 'page_generator_pro_' . wp_get_current_user()->ID );
		$this->base->get_class( 'notices' )->output_notices();
		$this->base->get_class( 'notices' )->delete_notices();

	}

	/**
	 * Registers screen names that should add the wpzinc class to the <body> tag
	 *
	 * @since   1.6.1
	 *
	 * @param   array $screens    Screen Names.
	 * @return  array               Screen Names
	 */
	public function admin_body_class( $screens ) {

		// Add Post Types.
		$screens[] = $this->base->get_class( 'taxonomy' )->taxonomy_name;

		/**
		 * Registers screen names that should add the wpzinc class to the <body> tag
		 *
		 * @since   2.5.7
		 *
		 * @param   array   $screens    Screen Names.
		 * @return  array               Screen Names
		 */
		$screens = apply_filters( 'page_generator_pro_admin_body_class', $screens );

		// Return.
		return $screens;

	}

	/**
	 * Defines CSS classes for the frontend output
	 *
	 * @since   1.6.1
	 *
	 * @param   array $classes    CSS Classes.
	 * @return  array               CSS Classes
	 */
	public function body_class( $classes ) {

		$classes[] = 'wpzinc';

		return $classes;

	}

	/**
	 * Enqueues CSS and JS
	 *
	 * @since   1.0.0
	 */
	public function admin_scripts_css() {

		global $post;

		// CSS - always load, admin / frontend editor wide.
		if ( $this->base->is_admin_or_frontend_editor() ) {
			wp_enqueue_style( $this->base->plugin->name . '-admin', $this->base->plugin->url . 'assets/css/admin.css', array(), $this->base->plugin->version );

			// Define CSS variables for design.
			wp_register_style( $this->base->plugin->name . '-vars', false, array(), $this->base->plugin->version );
			wp_enqueue_style( $this->base->plugin->name . '-vars' );
			wp_add_inline_style(
				$this->base->plugin->name . '-vars',
				trim(
					':root {
				--wpzinc-logo: url(\'' . esc_attr( $this->base->plugin->logo ) . '\');
				--wpzinc-header-background-color: ' . esc_attr( $this->base->plugin->header_background_color ) . ';
				--wpzinc-header-primary-text-color: ' . esc_attr( $this->base->plugin->header_primary_text_color ) . ';
				--wpzinc-header-secondary-text-color: ' . esc_attr( $this->base->plugin->header_secondary_text_color ) . ';
				--wpzinc-plugin-display-name: "' . esc_attr( $this->base->plugin->displayName ) . ' ";
			}'
				)
			);
		}

		// Determine the screen that we're on.
		$screen = $this->base->get_class( 'screen' )->get_current_screen();

		// If we're not on a plugin screen, exit.
		if ( ! $screen['screen'] ) {
			return;
		}

		// (Re)register dashboard scripts and enqueue CSS for frontend editors, which won't have registered these yet.
		$this->base->dashboard->admin_scripts_css();

		// Determine whether to load minified versions of JS.
		$minified = $this->base->dashboard->should_load_minified_js();

		// JS - register scripts we might use.
		wp_register_script( $this->base->plugin->name . '-conditional-fields', $this->base->plugin->url . 'assets/js/' . ( $minified ? 'min/' : '' ) . 'conditional-fields' . ( $minified ? '-min' : '' ) . '.js', array( 'jquery' ), $this->base->plugin->version, true );
		wp_register_script( $this->base->plugin->name . '-datatables', 'https://cdn.datatables.net/1.10.23/js/jquery.dataTables.min.js', array( 'jquery' ), $this->base->plugin->version, true );
		wp_register_script( $this->base->plugin->name . '-datatables-responsive', 'https://cdn.datatables.net/responsive/2.2.7/js/dataTables.responsive.min.js', array( 'jquery' ), $this->base->plugin->version, true );
                wp_register_script( $this->base->plugin->name . '-generate-browser', $this->base->plugin->url . 'assets/js/' . ( $minified ? 'min/' : '' ) . 'generate-browser' . ( $minified ? '-min' : '' ) . '.js', array( 'jquery' ), $this->base->plugin->version, true );
		wp_register_script( $this->base->plugin->name . '-generate-content', $this->base->plugin->url . 'assets/js/' . ( $minified ? 'min/' : '' ) . 'generate-content' . ( $minified ? '-min' : '' ) . '.js', array( 'jquery' ), $this->base->plugin->version, true );
                wp_register_script( $this->base->plugin->name . '-groups-directory', $this->base->plugin->url . 'assets/js/' . ( $minified ? 'min/' : '' ) . 'groups-directory' . ( $minified ? '-min' : '' ) . '.js', array( 'jquery', $this->base->plugin->name . '-selectize' ), $this->base->plugin->version, true );
		wp_register_script( $this->base->plugin->name . '-groups-ai', $this->base->plugin->url . 'assets/js/' . ( $minified ? 'min/' : '' ) . 'groups-ai' . ( $minified ? '-min' : '' ) . '.js', array( 'jquery' ), $this->base->plugin->version, true );
		wp_register_script( $this->base->plugin->name . '-groups-table', $this->base->plugin->url . 'assets/js/' . ( $minified ? 'min/' : '' ) . 'groups-table' . ( $minified ? '-min' : '' ) . '.js', array( 'jquery' ), $this->base->plugin->version, true );
		wp_register_script( $this->base->plugin->name . '-gutenberg', $this->base->plugin->url . 'assets/js/' . ( $minified ? 'min/' : '' ) . 'gutenberg' . ( $minified ? '-min' : '' ) . '.js', array( 'jquery', $this->base->plugin->name . '-conditional-fields' ), $this->base->plugin->version, true );
		wp_register_script( $this->base->plugin->name . '-gutenberg-block-formatters', $this->base->plugin->url . 'assets/js/' . ( $minified ? 'min/' : '' ) . 'gutenberg-block-formatters' . ( $minified ? '-min' : '' ) . '.js', array( 'jquery', $this->base->plugin->name . '-conditional-fields' ), $this->base->plugin->version, true );
		wp_register_script( $this->base->plugin->name . '-keywords', $this->base->plugin->url . 'assets/js/' . ( $minified ? 'min/' : '' ) . 'keywords' . ( $minified ? '-min' : '' ) . '.js', array( 'jquery' ), $this->base->plugin->version, true );
		wp_register_script( $this->base->plugin->name . '-keywords-generate-locations', $this->base->plugin->url . 'assets/js/' . ( $minified ? 'min/' : '' ) . 'keywords-generate-locations' . ( $minified ? '-min' : '' ) . '.js', array( 'jquery' ), $this->base->plugin->version, true );
		wp_register_script( $this->base->plugin->name . '-log', $this->base->plugin->url . 'assets/js/' . ( $minified ? 'min/' : '' ) . 'log' . ( $minified ? '-min' : '' ) . '.js', array( 'jquery' ), $this->base->plugin->version, true );
		wp_register_script( $this->base->plugin->name . '-page-builders', $this->base->plugin->url . 'assets/js/' . ( $minified ? 'min/' : '' ) . 'page-builders' . ( $minified ? '-min' : '' ) . '.js', array( 'jquery' ), $this->base->plugin->version, true );
		wp_register_script( $this->base->plugin->name . '-research', $this->base->plugin->url . 'assets/js/' . ( $minified ? 'min/' : '' ) . 'research' . ( $minified ? '-min' : '' ) . '.js', array( 'jquery' ), $this->base->plugin->version, true );
		wp_register_script( $this->base->plugin->name . '-selectize', $this->base->plugin->url . 'assets/js/' . ( $minified ? 'min/' : '' ) . 'selectize' . ( $minified ? '-min' : '' ) . '.js', array( 'jquery' ), $this->base->plugin->version, true );
		wp_register_script( $this->base->plugin->name . '-settings', $this->base->plugin->url . 'assets/js/' . ( $minified ? 'min/' : '' ) . 'settings' . ( $minified ? '-min' : '' ) . '.js', array( 'jquery' ), $this->base->plugin->version, true );

		// If here, we're on a plugin screen.
		// Conditionally load scripts and styles depending on which section of the Plugin we're loading.
		switch ( $screen['screen'] ) {
			/**
			 * Settings
			 */
			case 'settings':
				// CSS: WP Zinc.
				wp_enqueue_style( 'wpzinc-admin-selectize' );

				// JS: WP Zinc.
				wp_enqueue_script( 'jquery-ui-sortable' );
				wp_enqueue_script( 'wpzinc-admin-conditional' );
				wp_enqueue_script( 'wpzinc-admin' );
				wp_enqueue_script( 'wpzinc-admin-selectize' );

				// JS: Plugin.
				wp_enqueue_script( $this->base->plugin->name . '-settings' );
				wp_enqueue_script( $this->base->plugin->name . '-selectize' );

				wp_localize_script(
					$this->base->plugin->name . '-selectize',
					'page_generator_pro_selectize',
					array(
						'ajaxurl' => admin_url( 'admin-ajax.php' ),
						'fields'  => $this->base->get_class( 'common' )->get_selectize_enabled_fields(),
					)
				);
				break;

			/**
			 * Groups AI
			 */
			case 'groups-ai':
				// CSS: WordPress.
				wp_enqueue_style( 'common' );
				wp_enqueue_style( 'buttons' );
				wp_enqueue_style( 'forms' );

				// JS: WP Zinc.
				wp_enqueue_script( 'wpzinc-admin-modal' );

				// JS: Plugin.
				wp_enqueue_script( $this->base->plugin->name . '-groups-ai' );

				// Localize.
				wp_localize_script(
					$this->base->plugin->name . '-groups-ai',
					'page_generator_pro_groups_ai',
					array(
						'building_title'   => __( 'Building', 'page-generator-pro' ),
						'building_message' => __( 'This might take a few minutes. Please wait...', 'page-generator-pro' ),
					)
				);
				break;

			/**
			 * Groups Directory
			 */
			case 'groups-directory':
				// CSS: WordPress.
				wp_enqueue_style( 'common' );
				wp_enqueue_style( 'buttons' );
				wp_enqueue_style( 'forms' );

				// CSS: WP Zinc.
				wp_enqueue_style( 'wpzinc-admin-selectize' );

				// JS: WP Zinc.
				wp_enqueue_script( 'jquery-ui-sortable' );
				wp_enqueue_script( 'wpzinc-admin-conditional' );
				wp_enqueue_script( 'wpzinc-admin-modal' );
				wp_enqueue_script( 'wpzinc-admin-selectize' );

				// JS: Plugin.
				wp_enqueue_script( $this->base->plugin->name . '-groups-directory' );
				wp_enqueue_script( $this->base->plugin->name . '-selectize' );

				// Localize.
				wp_localize_script(
					$this->base->plugin->name . '-groups-directory',
					'page_generator_pro_groups_directory',
					array(
						'building_title'   => __( 'Building Directory', 'page-generator-pro' ),
						'building_message' => __( 'This might take a few minutes. Please wait...', 'page-generator-pro' ),
					)
				);
				wp_localize_script(
					$this->base->plugin->name . '-selectize',
					'page_generator_pro_selectize',
					array(
						'ajaxurl' => admin_url( 'admin-ajax.php' ),
						'fields'  => $this->base->get_class( 'common' )->get_selectize_enabled_fields(),
					)
				);
				break;

			/**
			 * Keywords
			 */
			case 'keywords':
				switch ( $screen['section'] ) {
					/**
					 * Keywords: WP_List_Table
					 */
					case 'wp_list_table':
						wp_enqueue_script( $this->base->plugin->name . '-keywords' );
						break;

					/**
					 * Keywords: Add / Edit
					 */
					case 'edit':
						// JS: WP Zinc.
						wp_enqueue_media();
						wp_enqueue_script( 'wpzinc-admin-media-library' );

						wp_enqueue_script( $this->base->plugin->name . '-conditional-fields' );
						wp_enqueue_script( $this->base->plugin->name . '-datatables' );
						wp_enqueue_script( $this->base->plugin->name . '-datatables-responsive' );
						wp_enqueue_script( $this->base->plugin->name . '-keywords' );

						// Localize Keywords with CodeMirror Code Editor instance.
						wp_localize_script(
							$this->base->plugin->name . '-keywords',
							'page_generator_pro_keywords',
							array(
								'codeEditor' => wp_enqueue_code_editor(
									array(
										'type' => 'text',
									)
								),
							)
						);
						break;

					/**
					 * Keywords: Generate Locations
					 */
					case 'generate_locations':
						// CSS: WP Zinc.
						wp_enqueue_style( 'wpzinc-admin-selectize' );

						// JS: WP Zinc.
						wp_enqueue_script( 'jquery-ui-sortable' );
						wp_enqueue_script( 'wpzinc-admin-modal' );
						wp_enqueue_script( 'wpzinc-admin-selectize' );

						// JS: Plugin.
						wp_enqueue_script( $this->base->plugin->name . '-keywords-generate-locations' );
						wp_localize_script(
							$this->base->plugin->name . '-keywords-generate-locations',
							'page_generator_pro_keywords_generate_locations',
							array(
								'titles'   => array(
									'keywords_generate_location_request'        => __( 'Building Location Terms', 'page-generator-pro' ),
								),
								'messages' => array(
									'keywords_generate_location_error'          => __( 'An error occured. Please try again.', 'page-generator-pro' ),
									'keywords_generate_location_request'        => __( 'Sending Request', 'page-generator-pro' ),
									'keywords_generate_location_response'       => __( 'Added Location Terms to Keyword', 'page-generator-pro' ),
									'keywords_generate_location_request_next'   => __( 'Additional Location Terms found.', 'page-generator-pro' ),
									'keywords_generate_location_success'        => __( 'Locations Keyword generated successfully.', 'page-generator-pro' ),
								),
								'options'  => array(
									'output_types' => $this->base->get_class( 'common' )->get_locations_output_types(
										$this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-generate-locations', 'provider', 'georocket' )
									),
								),
							)
						);

						wp_enqueue_script( $this->base->plugin->name . '-selectize' );
						wp_localize_script(
							$this->base->plugin->name . '-selectize',
							'page_generator_pro_selectize',
							array(
								'ajaxurl' => admin_url( 'admin-ajax.php' ),
								'fields'  => $this->base->get_class( 'common' )->get_selectize_enabled_fields(),
							)
						);
						break;

					/**
					 * Keywords: Generate Phone Area Codes
					 */
					case 'generate_phone_area_codes':
						// CSS: WP Zinc.
						wp_enqueue_style( 'wpzinc-admin-selectize' );

						// JS: WP Zinc.
						wp_enqueue_script( 'jquery-ui-sortable' );
						wp_enqueue_script( 'wpzinc-admin-modal' );
						wp_enqueue_script( 'wpzinc-admin-selectize' );

						// JS: Plugin.
						wp_enqueue_script( $this->base->plugin->name . '-selectize' );
						wp_localize_script(
							$this->base->plugin->name . '-selectize',
							'page_generator_pro_selectize',
							array(
								'ajaxurl' => admin_url( 'admin-ajax.php' ),
								'fields'  => $this->base->get_class( 'common' )->get_selectize_enabled_fields(),
							)
						);
						break;
				}
				break;

			/**
			 * Content: Groups
			 */
			case 'content_groups':
				// JS: WP Zinc.
				wp_enqueue_script( 'wpzinc-admin-modal' );
				wp_enqueue_script( 'wpzinc-admin-tables' );
				wp_enqueue_script( 'wpzinc-admin-tabs' );

				// JS: Plugin.
				wp_enqueue_script( $this->base->plugin->name . '-generate-content' );
				wp_localize_script( $this->base->plugin->name . '-generate-content', 'page_generator_pro', $this->base->licensing->get_parameters() );

				// Get localization strings.
				$localization = array_merge(
					$this->base->get_class( 'groups_ui' )->get_titles_and_messages(),
					array(
						'nonces'                          => array(
							'generate_content'         => wp_create_nonce( 'page-generator-pro-generate-browser' ),
							'trash_generated_content'  => wp_create_nonce( 'page-generator-pro-trash-generated-content' ),
							'delete_generated_content' => wp_create_nonce( 'page-generator-pro-delete-generated-content' ),
						),
						'post_type_conditional_metaboxes' => $this->base->get_class( 'groups_ui' )->get_post_type_conditional_metaboxes(),

						// CodeMirror configuration.
						'codemirror'                      => array(
							'codeEditor' => wp_enqueue_code_editor(
								array(
									'type'       => 'text',
									'codemirror' => array(
										// Required for Gutenberg styling.
										// See https://github.com/WordPress/gutenberg/issues/13645#issuecomment-535283007.
										'autoRefresh' => true,
									),
								)
							),
						),
					)
				);

				// Add data to localization depending on the screen we're viewing.
				switch ( $screen['section'] ) {
					/**
					 * Content: Groups: Add / WP_List_Table
					 */
					case 'wp_list_table':
						wp_enqueue_script( $this->base->plugin->name . '-groups-table' );
						break;

					/**
					 * Content: Groups: Edit
					 */
					case 'edit':
						// Prevents errors with meta boxes and Yoast in the WordPress Admin.
						if ( is_admin() ) {
							wp_enqueue_media();
						}

						// CSS: WP Zinc.
						wp_enqueue_style( 'wpzinc-admin-selectize' );

						// JS: WordPress.
						wp_enqueue_script( 'jquery-ui-sortable' );

						// JS: WP Zinc.
						wp_enqueue_script( 'wpzinc-admin-conditional' );
						wp_enqueue_script( 'wpzinc-admin-inline-search' );
						wp_enqueue_script( 'wpzinc-admin-selectize' );
						wp_enqueue_script( 'wpzinc-admin-tags' );
						wp_enqueue_script( 'wpzinc-admin' );

						// JS: Plugin.
						wp_enqueue_script( $this->base->plugin->name . '-conditional-fields' );
						wp_enqueue_script( $this->base->plugin->name . '-gutenberg' );
						wp_enqueue_script( $this->base->plugin->name . '-gutenberg-block-formatters' );
						wp_enqueue_script( $this->base->plugin->name . '-page-builders' );
						wp_enqueue_script( $this->base->plugin->name . '-research' );
						wp_enqueue_script( $this->base->plugin->name . '-selectize' );

						// Enqueue and localize Autocomplete, if a configuration exists.
						$autocomplete = $this->base->get_class( 'common' )->get_autocomplete_configuration( true );
						if ( $autocomplete ) {
							wp_enqueue_script( 'wpzinc-admin-autocomplete' );
							wp_enqueue_script( 'wpzinc-admin-autocomplete-gutenberg' );

							wp_localize_script( 'wpzinc-admin-autocomplete', 'wpzinc_autocomplete', $autocomplete );
							wp_localize_script( 'wpzinc-admin-autocomplete-gutenberg', 'wpzinc_autocomplete_gutenberg', $autocomplete );
						}

						// Localize Gutenberg.
						wp_localize_script(
							$this->base->plugin->name . '-gutenberg',
							'page_generator_pro_gutenberg',
							array(
								'keywords'   => $this->base->get_class( 'keywords' )->get_keywords_and_columns( true ),
								'shortcodes' => $this->base->get_class( 'shortcode' )->get_shortcodes( true ),
								'post_type'  => ( isset( $post->post_type ) ? $post->post_type : false ),
							)
						);

						// Localize Gutenberg Block Formatter Buttons.
						wp_localize_script(
							$this->base->plugin->name . '-gutenberg-block-formatters',
							'page_generator_pro_gutenberg_block_formatters',
							array(
								// Generate Spintax from selected Text.
								'spintax-generate' => array(
									'name'           => 'spintax-generate',
									'title'          => __( 'Generate Spintax from selected Text', 'page-generator-pro' ),
									'tag'            => '*',
									'gutenberg_icon' => $this->base->get_class( 'common' )->file_get_contents( $this->base->plugin->folder . '/assets/images/icons/spintax.svg' ),
									'click'          => 'pageGeneratorProGutenbergBlockFormatterGenerateSpintax', // The JS function to call when the button is clicked.
									'nonce'          => wp_create_nonce( 'page-generator-pro-spintax-generate' ),
								),

								// Research.
								'research'         => array(
									'name'           => 'research',
									'title'          => __( 'Research', 'page-generator-pro' ),
									'tag'            => '*',
									'gutenberg_icon' => $this->base->get_class( 'common' )->file_get_contents( $this->base->plugin->folder . '_modules/dashboard/feather/book.svg' ),
									'click'          => 'pageGeneratorProGutenbergBlockFormatterResearch', // The JS function to call when the button is clicked.
									'nonce'          => wp_create_nonce( 'page-generator-pro-research' ),

									'modal'          => array(
										'width'  => 600,
										'height' => 430,
									),
								),
							)
						);

						// Localize Selectize.
						wp_localize_script(
							$this->base->plugin->name . '-selectize',
							'page_generator_pro_selectize',
							array(
								'ajaxurl' => admin_url( 'admin-ajax.php' ),
								'fields'  => $this->base->get_class( 'common' )->get_selectize_enabled_fields(),
							)
						);

						// Get localization strings.
						$localization['post_id']                  = ( isset( $post->ID ) ? $post->ID : false );
						$localization['taxonomy_is_hierarchical'] = $this->base->get_class( 'common' )->get_taxonomies_hierarchical_status();
						break;
				}

				// Apply Localization.
				wp_localize_script( $this->base->plugin->name . '-generate-content', 'page_generator_pro_generate_content', $localization );
				break;

			/**
			 * Content: Terms
			 */
			case 'content_terms':
				// JS: WP Zinc.
				wp_enqueue_script( 'wpzinc-admin-modal' );

				// JS: Plugin.
				wp_enqueue_script( $this->base->plugin->name . '-generate-content' );
				wp_localize_script( $this->base->plugin->name . '-generate-content', 'page_generator_pro', $this->base->licensing->get_parameters() );

				// Get localization strings.
				$localization = array_merge(
					$this->base->get_class( 'groups_terms_ui' )->get_titles_and_messages(),
					array(
						'nonces'                   => array(
							'generate_content'         => wp_create_nonce( 'page-generator-pro-generate-browser' ),
							'delete_generated_content' => wp_create_nonce( 'page-generator-pro-delete-generated-content' ),
						),
						'taxonomy_is_hierarchical' => $this->base->get_class( 'common' )->get_taxonomies_hierarchical_status(),
					)
				);

				// Enqueue and localize Autocomplete, if a configuration exists.
				$autocomplete = $this->base->get_class( 'common' )->get_autocomplete_configuration( true );
				if ( $autocomplete ) {
					wp_enqueue_script( 'wpzinc-admin-autocomplete' );
					wp_localize_script( 'wpzinc-admin-autocomplete', 'wpzinc_autocomplete', $autocomplete );
				}

				switch ( $screen['section'] ) {
					/**
					 * Content: Terms: Add / WP_List_Table
					 */
					case 'wp_list_table':
						break;

					/**
					 * Content: Terms: Edit
					 */
					case 'edit':
						// JS: WP Zinc.
						wp_enqueue_script( 'wpzinc-admin-conditional' );
						wp_enqueue_script( 'wpzinc-admin' );
						break;
				}

				// Apply Localization.
				wp_localize_script( $this->base->plugin->name . '-generate-content', 'page_generator_pro_generate_content', $localization );
				break;

			/**
			 * Generate
			 */
			case 'generate':
				wp_enqueue_script( 'jquery-ui-progressbar' );
				wp_enqueue_script( 'wpzinc-admin-synchronous-ajax' );
				break;

			/**
			 * Logs
			 */
			case 'logs':
				wp_enqueue_script( $this->base->plugin->name . '-log' );
				break;

			/**
			 * Posts / Pages > Edit
			 * Apperance > Editor
			 * Appearance > Customize
			 * Settings > Reading
			 */
			case 'post':
			case 'appearance':
			case 'options':
				switch ( $screen['section'] ) {
					/**
					 * Posts: Edit
					 */
					case 'edit':
						// CSS: WP Zinc.
						wp_enqueue_style( 'wpzinc-admin-selectize' );

						// JS: WP Zinc.
						wp_enqueue_script( 'wpzinc-admin-conditional' );
						wp_enqueue_script( 'wpzinc-admin-modal' );
						wp_enqueue_script( 'wpzinc-admin-selectize' );
						wp_enqueue_script( 'wpzinc-admin-tables' );
						wp_enqueue_script( 'wpzinc-admin-tabs' );
						wp_enqueue_script( 'wpzinc-admin' );

						// JS: Plugin.
						wp_enqueue_script( $this->base->plugin->name . '-gutenberg' );
						wp_enqueue_script( $this->base->plugin->name . '-gutenberg-block-formatters' );
						wp_enqueue_script( $this->base->plugin->name . '-research' );
						wp_enqueue_script( $this->base->plugin->name . '-selectize' );

						// Enqueue and Localize Autocomplete, if a configuration exists.
						$autocomplete = $this->base->get_class( 'common' )->get_autocomplete_configuration( false );
						if ( $autocomplete ) {
							wp_enqueue_script( 'wpzinc-admin-autocomplete' );
							wp_enqueue_script( 'wpzinc-admin-autocomplete-gutenberg' );

							wp_localize_script( 'wpzinc-admin-autocomplete', 'wpzinc_autocomplete', $autocomplete );
						}

						// Localize Gutenberg with just Shortcodes that are supported outside of Content Groups.
						wp_localize_script(
							$this->base->plugin->name . '-gutenberg',
							'page_generator_pro_gutenberg',
							array(
								'keywords'   => $this->base->get_class( 'keywords' )->get_keywords_and_columns( true ),
								'shortcodes' => $this->base->get_class( 'shortcode' )->get_shortcode_supported_outside_of_content_groups( true ),
								'post_type'  => $post->post_type,
							)
						);

						// Localize Gutenberg Block Formatter Buttons.
						wp_localize_script(
							$this->base->plugin->name . '-gutenberg-block-formatters',
							'page_generator_pro_gutenberg_block_formatters',
							array(
								// Generate Spintax from selected Text.
								'spintax-generate' => array(
									'name'           => 'spintax-generate',
									'title'          => __( 'Generate Spintax from selected Text', 'page-generator-pro' ),
									'tag'            => '*',
									'gutenberg_icon' => $this->base->get_class( 'common' )->file_get_contents( $this->base->plugin->folder . '/assets/images/icons/spintax.svg' ),
									'click'          => 'pageGeneratorProGutenbergBlockFormatterGenerateSpintax', // The JS function to call when the button is clicked.
									'nonce'          => wp_create_nonce( 'page-generator-pro-spintax-generate' ),
								),

								// Research.
								'research'         => array(
									'name'           => 'research',
									'title'          => __( 'Research', 'page-generator-pro' ),
									'tag'            => '*',
									'gutenberg_icon' => $this->base->get_class( 'common' )->file_get_contents( $this->base->plugin->folder . '_modules/dashboard/feather/book.svg' ),
									'click'          => 'pageGeneratorProGutenbergBlockFormatterResearch', // The JS function to call when the button is clicked.
									'nonce'          => wp_create_nonce( 'page-generator-pro-research' ),

									'modal'          => array(
										'width'  => 600,
										'height' => 430,
									),
								),
							)
						);

						// Localize Selectize.
						wp_localize_script(
							$this->base->plugin->name . '-selectize',
							'page_generator_pro_selectize',
							array(
								'ajaxurl' => admin_url( 'admin-ajax.php' ),
								'fields'  => $this->base->get_class( 'common' )->get_selectize_enabled_fields(),
							)
						);
						break;
				}

				/**
				 * Performance
				 */

				// Don't enqueue if we're not changing wp_dropdown_pages() to an AJAX selectize instance .
				$change_page_dropdown_field = $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-general', 'restrict_parent_page_depth', '0' );
				if ( $change_page_dropdown_field !== 'ajax_select' ) {
					break;
				}

				// CSS: WP Zinc.
				wp_enqueue_style( 'wpzinc-admin-selectize' );

				// JS: WP Zinc.
				wp_enqueue_script( 'wpzinc-admin-selectize' );

				// JS: Plugin.
				wp_enqueue_script( $this->base->plugin->name . '-selectize' );
				wp_localize_script(
					$this->base->plugin->name . '-selectize',
					'page_generator_pro_selectize',
					array(
						'ajaxurl'       => admin_url( 'admin-ajax.php' ),
						'fields'        => $this->base->get_class( 'common' )->get_selectize_enabled_fields(),
						'reinit_events' => $this->base->get_class( 'common' )->get_selectize_reinit_events(),
					)
				);
				break;
		}

		// Add footer action to output overlay modal markup.
		add_action( 'admin_footer', array( $this, 'output_modal' ) );

		/**
		 * Enqueues CSS and JS
		 *
		 * @since   2.6.2
		 *
		 * @param   array       $screen     Screen (screen, section).
		 * @param   WP_Post     $post       WordPress Post.
		 * @param   bool        $minified   Whether to load minified JS.
		 */
		do_action( 'page_generator_pro_admin_admin_scripts_css', $screen, $post, $minified );

		// CSS.
		if ( class_exists( 'Page_Generator' ) ) {
			// Hide 'Add New' if a Group exists.
			$number_of_groups = $this->base->get_class( 'groups' )->get_count();
			if ( $number_of_groups > 0 ) {
				?>
				<style type="text/css">body.post-type-page-generator-pro a.page-title-action { display: none; }</style>
				<?php
			}
		}

	}

	/**
	 * Add the Plugin to the WordPress Administration Menu
	 *
	 * @since   1.0.0
	 */
	public function admin_menu() {

		// Define the minimum capability required to access settings.
		$minimum_capability = 'manage_options';

		/**
		 * Defines the minimum capability required to access the Plugin's
		 * Menu and Sub Menus
		 *
		 * @since   2.8.9
		 *
		 * @param   string  $capability     Minimum Required Capability.
		 * @return  string                  Minimum Required Capability
		 */
		$minimum_capability = apply_filters( 'page_generator_pro_admin_admin_menu_minimum_capability', $minimum_capability );

		/**
		 * Add settings menus and sub menus for the Plugin's settings.
		 *
		 * @since   1.0.0
		 *
		 * @param   string  $minimum_capability     Minimum capability required.
		 */
		do_action( 'page_generator_pro_admin_admin_menu', $minimum_capability );

	}

	/**
	 * Ensures this Plugin's top level Admin menu remains open when the user clicks on:
	 * - Generate Content
	 * - Generate Terms
	 *
	 * This prevents the 'wrong' admin menu being open (e.g. Posts)
	 *
	 * @since   1.2.3
	 *
	 * @param   string $parent_file    Parent Admin Menu File Name.
	 * @return  string                  Parent Admin Menu File Name
	 */
	public function admin_menu_hierarchy_correction( $parent_file ) {

		global $current_screen;

		// If we're creating or editing a Content Group, set the $parent_file to this Plugin's registered menu name.
		if ( $current_screen->base === 'post' && $current_screen->post_type === $this->base->get_class( 'post_type' )->post_type_name ) {
			// The free version uses a different top level filename.
			if ( class_exists( 'Page_Generator' ) ) {
				return $this->base->plugin->name . '-keywords';
			}

			return $this->base->plugin->name;
		}

		// If we're creating or editing a Term Group, set the $parent_file to this Plugin's registered menu name.
		if ( ( $current_screen->base === 'edit-tags' || $current_screen->base === 'term' ) && $current_screen->taxonomy === $this->base->get_class( 'taxonomy' )->taxonomy_name ) {
			return $this->base->plugin->name;
		}

		return $parent_file;

	}

	/**
	 * Defines options to display in the Screen Options dropdown on the Keywords
	 * WP_List_Table, and performs any save actions for Keywords.
	 *
	 * @since   2.6.5
	 */
	public function add_keyword_screen_options() {

		add_screen_option(
			'per_page',
			array(
				'label'   => __( 'Keywords', 'page-generator-pro' ),
				'default' => 20,
				'option'  => 'page_generator_pro_keywords_per_page',
			)
		);

		// Initialize Keywords WP_List_Table, as this will trigger WP_List_Table to add column options.
		$keywords_table = new Page_Generator_Pro_Keywords_Table( $this->base );

	}

	/**
	 * Defines options to display in the Screen Options dropdown on the Logs
	 * WP_List_Table
	 *
	 * @since   2.8.0
	 */
	public function add_log_screen_options() {

		add_screen_option(
			'per_page',
			array(
				'label'   => __( 'Log Entries per Page', 'page-generator-pro' ),
				'default' => 20,
				'option'  => 'page_generator_pro_logs_per_page',
			)
		);

		// Initialize Logs WP_List_Table, as this will trigger WP_List_Table to add column options.
		$log_table = new Page_Generator_Pro_Log_Table( $this->base );

	}

	/**
	 * Sets values for options displayed in the Screen Options dropdown on the Keywords and Logs
	 * WP_List_Table
	 *
	 * @since   2.6.5
	 *
	 * @param   bool   $screen_option  The value to save instead of the option value. Default false (to skip saving the current option).
	 * @param   string $option         The option name.
	 * @param   string $value          The option value.
	 * @return  string                  The option value
	 */
	public function set_screen_options( $screen_option, $option, $value ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter

		return $value;

	}

	/**
	 * Run any bulk actions on the Log WP_List_Table
	 *
	 * @since   2.6.5
	 */
	public function run_keyword_save_actions() {

		// Setup notices class.
		$this->base->get_class( 'notices' )->set_key_prefix( 'page_generator_pro_' . wp_get_current_user()->ID );

		// Get command.
		$cmd = ( ( isset( $_GET['cmd'] ) ) ? sanitize_text_field( wp_unslash( $_GET['cmd'] ) ) : '' ); // phpcs:ignore WordPress.Security.NonceVerification
		switch ( $cmd ) {

			/**
			 * Generate Locations
			 */
			case 'form-locations':
				// Generate Locations.
				$result = $this->generate_locations();
				if ( is_wp_error( $result ) ) {
					// Error.
					$this->base->get_class( 'notices' )->add_error_notice( $result->get_error_message() );
				} elseif ( is_numeric( $result ) ) {
					// Success.
					$this->base->get_class( 'notices' )->add_success_notice(
						sprintf(
							/* translators: Link to generated keyword */
							__( 'Locations Keyword generated successfully. %s', 'page-generator-pro' ),
							'<a href="' . admin_url( 'admin.php?page=' . $this->base->plugin->name . '-keywords&cmd=form&id=' . $result ) . '">' . __( 'View Keyword', 'page-generator-pro' ) . '</a>'
						)
					);
				}
				break;

			/**
			 * Generate Phone Area Codes
			 */
			case 'form-phone':
				// Run activation routine in case phone are codes didn't download on activation.
				$result = $this->base->get_class( 'phone_area_codes' )->activate();
				if ( is_wp_error( $result ) ) {
					$this->base->get_class( 'notices' )->add_error_notice( $result->get_error_message() );
				} else {
					// Generate phone area codes.
					$result = $this->generate_phone_area_codes();
					if ( is_wp_error( $result ) ) {
						// Error.
						$this->base->get_class( 'notices' )->add_error_notice( $result->get_error_message() );
					} elseif ( is_numeric( $result ) ) {
						// Success.
						$this->base->get_class( 'notices' )->add_success_notice(
							sprintf(
								/* translators: Link to view Generated Keyword */
								__( 'Phone Area Codes generated successfully. %s', 'page-generator-pro' ),
								'<a href="' . admin_url( 'admin.php?page=' . $this->base->plugin->name . '-keywords&cmd=form&id=' . $result ) . '" target="_blank">' .
								__( 'View Keyword', 'page-generator-pro' ) . '</a>'
							)
						);
					}
				}

				break;

			/**
			 * Add / Edit Keyword
			 */
			case 'form':
				// Save keyword.
				$keyword_id = $this->save_keyword();

				if ( is_wp_error( $keyword_id ) ) {
					$this->base->get_class( 'notices' )->add_error_notice( $keyword_id->get_error_message() );
				} elseif ( is_numeric( $keyword_id ) ) {
					// Redirect.
					$this->base->get_class( 'notices' )->enable_store();
					$this->base->get_class( 'notices' )->add_success_notice( __( 'Keyword saved successfully', 'page-generator-pro' ) );
					wp_safe_redirect( 'admin.php?page=page-generator-pro-keywords&cmd=form&id=' . $keyword_id );
					die;
				}
				break;

		}

	}

	/**
	 * Run any bulk actions on the Keyword WP_List_Table
	 *
	 * @since   2.6.5
	 */
	public function run_keyword_table_bulk_actions() {

		// Get screen.
		$screen = $this->base->get_class( 'screen' )->get_current_screen();

		// Bail if we're not on the Keywords Screen.
		if ( $screen['screen'] !== 'keywords' ) {
			return;
		}
		if ( $screen['section'] !== 'wp_list_table' ) {
			return;
		}

		// Setup notices class, enabling persistent storage.
		$this->base->get_class( 'notices' )->enable_store();
		$this->base->get_class( 'notices' )->set_key_prefix( 'page_generator_pro_' . wp_get_current_user()->ID );

		// Bail if no nonce exists or fails verification.
		if ( ! array_key_exists( '_wpnonce', $_REQUEST ) ) {
			return;
		}
		if ( ! wp_verify_nonce( sanitize_key( $_REQUEST['_wpnonce'] ), 'bulk-keywords' ) ) {
			$this->base->get_class( 'notices' )->add_error_notice(
				__( 'Nonce invalid. Bulk action not performed.', 'page-generator-pro' )
			);
			return;
		}

		// Get bulk action from the fields that might contain it.
		$bulk_action = array_values(
			array_filter(
				array(
					( isset( $_REQUEST['action'] ) && $_REQUEST['action'] != '-1' ? sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) : '' ), // phpcs:ignore Universal.Operators.StrictComparisons.LooseNotEqual
					( isset( $_REQUEST['action2'] ) && $_REQUEST['action2'] != '-1' ? sanitize_text_field( wp_unslash( $_REQUEST['action2'] ) ) : '' ), // phpcs:ignore Universal.Operators.StrictComparisons.LooseNotEqual
					( isset( $_REQUEST['action3'] ) && ! empty( $_REQUEST['action3'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['action3'] ) ) : '' ),
				)
			)
		);

		// Bail if no bulk action.
		if ( ! is_array( $bulk_action ) ) {
			return;
		}
		if ( ! count( $bulk_action ) ) {
			return;
		}

		// Perform Bulk Action.
		switch ( $bulk_action[0] ) {

			/**
			 * Delete Keywords
			 */
			case 'delete':
				// Setup notices class, enabling persistent storage.
				$this->base->get_class( 'notices' )->enable_store();
				$this->base->get_class( 'notices' )->set_key_prefix( 'page_generator_pro_' . wp_get_current_user()->ID );

				// Get Keyword IDs.
				if ( ! isset( $_REQUEST['ids'] ) ) {
					$this->base->get_class( 'notices' )->add_error_notice(
						__( 'No Keywords were selected for deletion.', 'page-generator-pro' )
					);
					break;
				}

				// Delete Keywords.
				$result = $this->base->get_class( 'keywords' )->delete( wp_unslash( $_REQUEST['ids'] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput

				// Output success or error messages.
				if ( is_wp_error( $result ) ) {
					// Add error message and redirect back to the keyword table.
					$this->base->get_class( 'notices' )->add_error_notice( $result );
				} else {
					$this->base->get_class( 'notices' )->add_success_notice(
						sprintf(
							/* translators: Number of Keywords deleted */
							__( '%s Keywords deleted.', 'page-generator-pro' ),
							count( $_REQUEST['ids'] )
						)
					);
				}

				// Redirect.
				$this->redirect_after_keyword_action();
				break;

		}

	}

	/**
	 * Run any row actions on the Keywords WP_List_Table now
	 *
	 * @since   1.2.3
	 */
	public function run_keyword_table_row_actions() {

		// Bail if no page specified.
		$page = ( ( isset( $_GET['page'] ) ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : false ); // phpcs:ignore WordPress.Security.NonceVerification
		if ( ! $page ) {
			return;
		}
		if ( $page !== $this->base->plugin->name . '-keywords' ) {
			return;
		}

		// Setup notices class, enabling persistent storage.
		$this->base->get_class( 'notices' )->enable_store();
		$this->base->get_class( 'notices' )->set_key_prefix( 'page_generator_pro_' . wp_get_current_user()->ID );

		// Bail if no nonce exists.
		if ( ! array_key_exists( '_wpnonce', $_REQUEST ) ) {
			return;
		}

		// Bail if nonce fails verification, as it might be for a different request.
		if ( ! wp_verify_nonce( sanitize_key( $_REQUEST['_wpnonce'] ), 'action-keywords' ) ) {
			return;
		}

		// Bail if no row action specified.
		$cmd = ( ( isset( $_GET['cmd'] ) ) ? sanitize_text_field( wp_unslash( $_GET['cmd'] ) ) : false );
		if ( ! $cmd ) {
			return;
		}

		switch ( $cmd ) {

			/**
			 * Duplicate Keyword
			 */
			case 'duplicate':
				// Bail if no ID set.
				if ( ! isset( $_GET['id'] ) ) {
					$this->base->get_class( 'notices' )->add_error_notice( __( 'No Keyword was selected for duplication.', 'page-generator-pro' ) );
					break;
				}

				// Duplicate keyword.
				$result = $this->base->get_class( 'keywords' )->duplicate( absint( $_GET['id'] ) );

				// Output success or error messages.
				if ( is_wp_error( $result ) ) {
					// Error.
					$this->base->get_class( 'notices' )->add_error_notice( $result->get_error_message() );
				} elseif ( is_numeric( $result ) ) {
					// Success.
					$this->base->get_class( 'notices' )->add_success_notice(
						sprintf(
							/* translators: Link to view duplicated Keyword */
							__( 'Keyword duplicated successfully. %s', 'page-generator-pro' ),
							'<a href="' . admin_url( 'admin.php?page=' . $this->base->plugin->name . '-keywords&cmd=form&id=' . $result ) . '">' .
							__( 'View Keyword', 'page-generator-pro' ) . '</a>'
						)
					);
				}

				// Redirect.
				$this->redirect_after_keyword_action();
				break;

			/**
			 * Export Keyword to CSV
			 */
			case 'export_csv':
				// Bail if no ID set.
				if ( ! isset( $_GET['id'] ) ) {
					$this->base->get_class( 'notices' )->add_error_notice( __( 'No Keyword was selected for exporting.', 'page-generator-pro' ) );
					break;
				}

				// Export keyword.
				$result = $this->base->get_class( 'keywords' )->export_csv( absint( $_GET['id'] ) );

				// Output success or error messages.
				if ( is_wp_error( $result ) ) {
					// Error.
					$this->base->get_class( 'notices' )->add_error_notice( $result->get_error_message() );
				}

				// If successful, a file will have downloaded so the rest of this code isn't run.
				break;

			/**
			 * Delete Keyword
			 */
			case 'delete':
				// Bail if no ID set.
				if ( ! isset( $_GET['id'] ) ) {
					$this->base->get_class( 'notices' )->add_error_notice( __( 'No Group was selected for duplication.', 'page-generator-pro' ) );
					break;
				}

				// Delete keyword.
				$result = $this->base->get_class( 'keywords' )->delete( absint( $_GET['id'] ) );

				// Output success or error messages.
				if ( is_string( $result ) ) {
					// Add error message and redirect back to the keyword table.
					$this->base->get_class( 'notices' )->add_error_notice( $result );
				} elseif ( $result === true ) {
					// Success.
					$this->base->get_class( 'notices' )->add_success_notice( __( 'Keyword deleted successfully.', 'page-generator-pro' ) );
				}

				// Redirect.
				$this->redirect_after_keyword_action();
				break;

		}

	}

	/**
	 * Reloads the Keywords WP_List_Table, with search, order and pagination arguments if supplied
	 *
	 * @since   2.6.5
	 */
	private function redirect_after_keyword_action() {

		// Nonce verification already completed, so can safely ignore phpcs errors.
		$url = add_query_arg(
			array(
				'page'    => $this->base->plugin->name . '-keywords',
				's'       => ( isset( $_REQUEST['s'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['s'] ) ) : '' ), // phpcs:ignore WordPress.Security.NonceVerification
				'paged'   => ( isset( $_REQUEST['paged'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['paged'] ) ) : 1 ), // phpcs:ignore WordPress.Security.NonceVerification
				'orderby' => ( isset( $_REQUEST['orderby'] ) ? sanitize_sql_orderby( wp_unslash( $_REQUEST['orderby'] ) ) : 'keyword' ), // phpcs:ignore WordPress.Security.NonceVerification
				'order'   => ( isset( $_REQUEST['order'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['order'] ) ) : 'ASC' ), // phpcs:ignore WordPress.Security.NonceVerification
			),
			'admin.php'
		);

		wp_safe_redirect( $url );
		die;

	}

	/**
	 * Registers Settings Panel(s) for the WordPress Administration Settings screen
	 *
	 * @since   1.0.0
	 *
	 * @param   array $panels   Settings Panels (key/value pairs).
	 * @return  array           Panels
	 */
	public function register_settings_panel( $panels ) {

		$panels[ $this->base->plugin->name . '-general' ]            = array(
			'label' => __( 'General', 'page-generator-pro' ),
			'icon'  => 'dashicons-admin-site',
		);
		$panels[ $this->base->plugin->name . '-generate' ]           = array(
			'label' => __( 'Generate', 'page-generator-pro' ),
			'icon'  => 'dashicons-admin-page',
		);
		$panels[ $this->base->plugin->name . '-generate-locations' ] = array(
			'label' => __( 'Generate Locations', 'page-generator-pro' ),
			'icon'  => 'dashicons-admin-site',
		);
		$panels[ $this->base->plugin->name . '-integrations' ]       = array(
			'label' => __( 'Integrations', 'page-generator-pro' ),
			'icon'  => 'dashicons-admin-site',
		);
		$panels[ $this->base->plugin->name . '-research' ]           = array(
			'label' => __( 'Research', 'page-generator-pro' ),
			'icon'  => 'dashicons-welcome-learn-more',
		);
		$panels[ $this->base->plugin->name . '-spintax' ]            = array(
			'label' => __( 'Spintax', 'page-generator-pro' ),
			'icon'  => 'dashicons-code-standards',
		);

		return $panels;

	}

	/**
	 * Output the Settings Screen
	 * Save POSTed data from the Administration Panel into a WordPress option
	 *
	 * @since   1.0.0
	 */
	public function settings_screen() {

		// Get registered settings panels.
		$panels = array();

		/**
		 * Filters the available panels / sections on the settings screen.
		 *
		 * @since   1.0.0
		 *
		 * @param   array   $panels     Settings Panels.
		 */
		$panels = apply_filters( 'page_generator_pro_setting_panel', $panels );

		// Get active tab.
		$current_tab = ( isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : $this->base->plugin->name . '-general' ); // phpcs:ignore WordPress.Security.NonceVerification

		// Setup notices class.
		$this->base->get_class( 'notices' )->set_key_prefix( 'page_generator_pro_' . wp_get_current_user()->ID );

		// Maybe save settings.
		$result = $this->save_settings( $current_tab );
		if ( is_wp_error( $result ) ) {
			// Error.
			$this->base->get_class( 'notices' )->add_error_notice( $result->get_error_message() );
		} elseif ( $result === true ) {
			// Success.
			$this->base->get_class( 'notices' )->add_success_notice( __( 'Settings saved successfully.', 'page-generator-pro' ) );
		}

		// Load View.
		include_once $this->base->plugin->folder . '/views/admin/settings.php';

	}

	/**
	 * Outputs the General Settings Screen within Page Generator Pro > Settings
	 *
	 * @since   1.2.1
	 */
	public function settings_screen_general() {

		// Get form select options.
		$countries = $this->base->get_class( 'common' )->get_countries();

		// Load view.
		include_once $this->base->plugin->folder . '/views/admin/settings-general.php';

	}

	/**
	 * Outputs the Generate Settings Screen within Page Generator Pro > Settings
	 *
	 * @since   1.5.2
	 */
	public function settings_screen_generate() {

		// Load view.
		include_once $this->base->plugin->folder . '/views/admin/settings-generate.php';

	}

	/**
	 * Outputs the Generate Locations Settings Screen within Page Generator Pro > Settings
	 *
	 * @since   5.0.0
	 */
	public function settings_screen_generate_locations() {

		// Get form select options.
		$providers = $this->base->get_class( 'keywords_generate_locations' )->get_providers();

		// Get settings.
		$settings = array(
			'provider' => $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-generate-locations', 'provider' ),
			'language' => $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-generate-locations', 'language', 'en' ),
			'method'   => $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-generate-locations', 'method', '' ),
			'radius'   => $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-generate-locations', 'radius', '' ),
		);

		// Get form field options.
		$languages = $this->base->get_class( 'common' )->get_languages();
		$methods   = $this->base->get_class( 'common' )->get_locations_methods();

		// Get all research providers registered settings fields.
		$settings_fields = $this->base->get_class( 'keywords_generate_locations' )->get_providers_settings_fields();

		// Load view.
		include_once $this->base->plugin->folder . '/views/admin/settings-generate-locations.php';

	}

	/**
	 * Outputs the Integrations Settings Screen within Page Generator Pro > Settings
	 *
	 * @since   3.3.4
	 */
	public function settings_screen_integrations() {

		// Get integrations.
		$integrations = $this->base->get_class( 'integrations' )->get();

		// Get all settings fields regsitered by integrations.
		$settings_fields = $this->base->get_class( 'integrations' )->get_settings_fields();

		// Load view.
		include_once $this->base->plugin->folder . '/views/admin/settings-integrations.php';

	}

	/**
	 * Outputs the Research Settings Screen within Page Generator Pro > Settings
	 *
	 * @since   2.8.9
	 */
	public function settings_screen_research() {

		// Get form select options.
		$providers = $this->base->get_class( 'research' )->get_providers();

		// Get settings.
		$settings = array(
			'provider' => $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-research', 'provider' ),
		);

		// Get all research providers registered settings fields.
		$settings_fields = $this->base->get_class( 'research' )->get_providers_settings_fields();

		// Load view.
		include_once $this->base->plugin->folder . '/views/admin/settings-research.php';

	}

	/**
	 * Outputs the Generate Spintax Settings Screen within Page Generator Pro > Settings
	 *
	 * @since   2.2.9
	 */
	public function settings_screen_generate_spintax() {

		// Get spintax providers.
		$providers = $this->base->get_class( 'spintax' )->get_providers();

		// Get settings.
		$settings = array(
			'frontend'               => $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-spintax', 'frontend', 0 ),
			'language'               => $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-spintax', 'language', 'en' ),
			'skip_capitalized_words' => $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-spintax', 'skip_capitalized_words', 1 ),
			'protected_words'        => $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-spintax', 'protected_words' ),
			'provider'               => $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-spintax', 'provider' ),
		);

		// Get all spintax providers registered settings fields.
		$settings_fields = $this->base->get_class( 'spintax' )->get_providers_settings_fields();

		// Load view.
		include_once $this->base->plugin->folder . '/views/admin/settings-spintax.php';

	}

	/**
	 * Outputs the Keywords Screens
	 *
	 * @since   1.0.0
	 */
	public function keywords_screen() {

		// Get command.
		$cmd = ( ( isset( $_GET['cmd'] ) ) ? sanitize_text_field( wp_unslash( $_GET['cmd'] ) ) : '' ); // phpcs:ignore WordPress.Security.NonceVerification
		switch ( $cmd ) {

			/**
			 * Generate Locations
			 */
			case 'form-locations':
				// Define blank Keyword.
				$keyword = array(
					'keyword'                     => '',
					'orderby'                     => '',
					'order'                       => '',
					'country_code'                => $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-general', 'country_code', 'US' ),
					'method'                      => $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-generate-locations', 'method', 'radius' ),
					'location'                    => '',
					'radius'                      => $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-generate-locations', 'radius' ),
					'regions'                     => '',
					'counties'                    => '',
					'cities'                      => '',
					'exclusions'                  => '',
					'population_min'              => '',
					'population_max'              => '',
					'median_household_income_min' => '',
					'median_household_income_max' => '',
				);

				// If the form has been posted, apply the posted values to the keyword.
				if ( isset( $_POST[ $this->base->plugin->name . '_nonce' ] ) && wp_verify_nonce( sanitize_key( $_POST[ $this->base->plugin->name . '_nonce' ] ), 'generate_locations' ) ) {
					foreach ( $keyword as $key => $value ) {
						$keyword[ $key ] = ( isset( $_POST[ $key ] ) ? sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) : '' );
					}
				}

				// Get provider.
				$provider = $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-generate-locations', 'provider' );

				// Get countries and output types.
				$countries        = $this->base->get_class( 'common' )->get_countries();
				$methods          = $this->base->get_class( 'common' )->get_locations_methods();
				$restrictions     = $this->base->get_class( 'common' )->get_locations_restrictions();
				$output_types     = $this->base->get_class( 'common' )->get_locations_output_types( $provider );
				$order_by_options = $this->base->get_class( 'common' )->get_locations_order_by_options();
				$order_options    = $this->base->get_class( 'common' )->get_order_options();

				// View.
				$view = 'views/admin/keywords-form-locations.php';
				break;

			/**
			 * Generate Phone Area Codes
			 */
			case 'form-phone':
				// Define blank Keyword.
				$keyword = array(
					'keyword'     => '',
					'country'     => $this->base->get_class( 'common' )->get_countries()[ $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-general', 'country_code', 'US' ) ],
					'output_type' => '',
				);

				// If the form has been posted, apply the posted values to the keyword.
				if ( isset( $_POST[ $this->base->plugin->name . '_nonce' ] ) && wp_verify_nonce( sanitize_key( $_POST[ $this->base->plugin->name . '_nonce' ] ), 'generate_phone_area_codes' ) ) {
					$keyword['keyword']     = ( isset( $_POST['keyword'] ) ? sanitize_text_field( wp_unslash( $_POST['keyword'] ) ) : '' );
					$keyword['country']     = ( isset( $_POST['country'] ) ? sanitize_text_field( wp_unslash( $_POST['country'] ) ) : '' );
					$keyword['output_type'] = ( isset( $_POST['output_type'] ) ? wp_unslash( $_POST['output_type'] ) : '' ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
				}

				// Get countries and output types.
				$countries    = $this->base->get_class( 'phone_area_codes' )->get_phone_area_code_countries();
				$output_types = $this->base->get_class( 'common' )->get_phone_area_code_output_types();

				// View.
				$view = 'views/admin/keywords-form-phone.php';
				break;

			/**
			 * Add / Edit Keyword
			 */
			case 'form':
				// Define blank Keyword.
				$keyword = array(
					'keyword' => '',
					'source'  => 'local',
				);

				// Get existing Keyword from database.
				if ( isset( $_GET['id'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
					// Editing an existing Keyword.
					// Get Keyword from DB.
					$keyword_id = absint( $_GET['id'] ); // phpcs:ignore WordPress.Security.NonceVerification
					$keyword    = $this->base->get_class( 'keywords' )->get_by_id( absint( $_GET['id'] ) ); // phpcs:ignore WordPress.Security.NonceVerification

					// View.
					$view = 'views/admin/keywords-form-edit.php';
				} else {
					// Adding a new Keyword.
					// View.
					$view = 'views/admin/keywords-form.php';
				}

				// If the form has been posted, an error occured if we are here.
				// Apply the posted values to the keyword.
				if ( isset( $_POST[ $this->base->plugin->name . '_nonce' ] ) && wp_verify_nonce( sanitize_key( $_POST[ $this->base->plugin->name . '_nonce' ] ), 'save_keyword' ) ) {
					$keyword['keyword'] = ( isset( $_POST['keyword'] ) ? sanitize_text_field( wp_unslash( $_POST['keyword'] ) ) : '' );
					$keyword['source']  = ( isset( $_POST['source'] ) ? sanitize_text_field( wp_unslash( $_POST['source'] ) ) : '' );
					$keyword['options'] = ( isset( $_POST[ $keyword['source'] ] ) ? wp_unslash( $_POST[ $keyword['source'] ] ) : '' ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
				}

				// Get Keyword Sources.
				$sources = $this->base->get_class( 'keywords' )->get_sources();
				break;

			/**
			 * Duplicate Keyword
			 * Delete Keyword
			 * Index
			 */
			case 'duplicate':
			case 'delete':
			default:
				// Setup Table.
				$keywords_table = new Page_Generator_Pro_Keywords_Table( $this->base );
				$keywords_table->prepare_items();

				// View.
				$view = 'views/admin/keywords-table.php';
				break;

		}

		// Load View.
		include_once $this->base->plugin->folder . $view;

	}

	/**
	 * Save Settings Screen
	 *
	 * @since   1.0.0
	 *
	 * @param   string $type   Plugin Name / Type.
	 * @return  WP_Error|bool
	 */
	public function save_settings( $type ) {

		// Run security checks.
		// Missing nonce.
		if ( ! isset( $_POST[ $this->base->plugin->name . '_nonce' ] ) ) {
			return false;
		}

		// Invalid nonce.
		if ( ! wp_verify_nonce( sanitize_key( $_POST[ $this->base->plugin->name . '_nonce' ] ), 'page-generator-pro' ) ) {
			return new WP_Error( 'page_generator_pro_admin_save_settings', __( 'Invalid nonce specified. Settings NOT saved.', 'page-generator-pro' ) );
		}

		// Store settings in array.
		$settings = $_POST[ $type ]; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput

		// Depending on the setting type, perform some further validation.
		switch ( $type ) {
			case 'page-generator-pro-generate':
				if ( empty( $settings['stop_on_error_pause'] ) || ! $settings['stop_on_error_pause'] ) { // @phpstan-ignore-line
					$settings['stop_on_error_pause'] = 5;
				}

				if ( ! isset( $settings['use_mu_active_plugins'] ) ) {
					$settings['use_mu_active_plugins'] = array();
				}
				break;
		}

		// Save settings.
		$this->base->get_class( 'settings' )->update_settings( $type, $settings );

		// Depending on the Settings Tab, perform some actions after saving settings.
		switch ( $type ) {
			case 'page-generator-pro-generate-locations':
			case 'page-generator-pro-integrations':
			case 'page-generator-pro-spintax':
				// Save AI Keys and Model settings to the Research's settings, when saved on the Generate Locations, Integrations or Spintax screen.
				$research_settings = $this->base->get_class( 'settings' )->get_settings( 'page-generator-pro-research' );
				if ( ! is_array( $research_settings ) ) {
					$research_settings = array();
				}
				if ( isset( $settings['alibaba_api_key'] ) ) {
					$research_settings['alibaba_api_key'] = $settings['alibaba_api_key'];
				}
				if ( isset( $settings['alibaba_model'] ) ) {
					$research_settings['alibaba_model'] = $settings['alibaba_model'];
				}
				if ( isset( $settings['claude_ai_api_key'] ) ) {
					$research_settings['claude_ai_api_key'] = $settings['claude_ai_api_key'];
				}
				if ( isset( $settings['claude_ai_model'] ) ) {
					$research_settings['claude_ai_model'] = $settings['claude_ai_model'];
				}
				if ( isset( $settings['deepseek_api_key'] ) ) {
					$research_settings['deepseek_api_key'] = $settings['deepseek_api_key'];
				}
				if ( isset( $settings['deepseek_model'] ) ) {
					$research_settings['deepseek_model'] = $settings['deepseek_model'];
				}
				if ( isset( $settings['gemini_ai_api_key'] ) ) {
					$research_settings['gemini_ai_api_key'] = $settings['gemini_ai_api_key'];
				}
				if ( isset( $settings['gemini_ai_model'] ) ) {
					$research_settings['gemini_ai_model'] = $settings['gemini_ai_model'];
				}
				if ( isset( $settings['gemini_ai_image_model'] ) ) {
					$research_settings['gemini_ai_image_model'] = $settings['gemini_ai_image_model'];
				}
				if ( isset( $settings['grok_ai_api_key'] ) ) {
					$research_settings['grok_ai_api_key'] = $settings['grok_ai_api_key'];
				}
				if ( isset( $settings['grok_ai_model'] ) ) {
					$research_settings['grok_ai_model'] = $settings['grok_ai_model'];
				}
				if ( isset( $settings['grok_ai_image_model'] ) ) {
					$research_settings['grok_ai_image_model'] = $settings['grok_ai_image_model'];
				}
				if ( isset( $settings['ideogram_ai_api_key'] ) ) {
					$research_settings['ideogram_ai_api_key'] = $settings['ideogram_ai_api_key'];
				}
				if ( isset( $settings['ideogram_ai_model'] ) ) {
					$research_settings['ideogram_ai_model'] = $settings['ideogram_ai_model'];
				}
				if ( isset( $settings['imagineapi_api_key'] ) ) {
					$research_settings['imagineapi_api_key'] = $settings['imagineapi_api_key'];
				}
				if ( isset( $settings['mistral_ai_api_key'] ) ) {
					$research_settings['mistral_ai_api_key'] = $settings['mistral_ai_api_key'];
				}
				if ( isset( $settings['mistral_ai_model'] ) ) {
					$research_settings['mistral_ai_model'] = $settings['mistral_ai_model'];
				}
				if ( isset( $settings['openai_api_key'] ) ) {
					$research_settings['openai_api_key'] = $settings['openai_api_key'];
				}
				if ( isset( $settings['openai_model'] ) ) {
					$research_settings['openai_model'] = $settings['openai_model'];
				}
				if ( isset( $settings['openai_image_model'] ) ) {
					$research_settings['openai_image_model'] = $settings['openai_image_model'];
				}
				if ( isset( $settings['openrouter_api_key'] ) ) {
					$research_settings['openrouter_api_key'] = $settings['openrouter_api_key'];
				}
				if ( isset( $settings['openrouter_model'] ) ) {
					$research_settings['openrouter_model'] = $settings['openrouter_model'];
				}
				if ( isset( $settings['perplexity_api_key'] ) ) {
					$research_settings['perplexity_api_key'] = $settings['perplexity_api_key'];
				}
				if ( isset( $settings['perplexity_model'] ) ) {
					$research_settings['perplexity_model'] = $settings['perplexity_model'];
				}
				if ( isset( $settings['straico_api_key'] ) ) {
					$research_settings['straico_api_key'] = $settings['straico_api_key'];
				}
				if ( isset( $settings['straico_model'] ) ) {
					$research_settings['straico_model'] = $settings['straico_model'];
				}
				$this->base->get_class( 'settings' )->update_settings( 'page-generator-pro-research', $research_settings );
				break;

			case 'page-generator-pro-generate':
				// Reschedule CRON events.
				$this->base->get_class( 'cron' )->reschedule_log_cleanup_event();
				break;
		}

		// Return.
		return true;

	}

	/**
	 * Generates Locations for the given Keyword and Location Parameters
	 *
	 * @since   1.7.8
	 *
	 * @return  WP_Error|int
	 */
	public function generate_locations() {

		// Check if a POST request was made.
		if ( ! isset( $_POST['submit'] ) ) {
			return false;
		}

		// Run security checks.
		// Missing nonce.
		if ( ! isset( $_POST[ $this->base->plugin->name . '_nonce' ] ) ) {
			return new WP_Error( 'page_generator_pro_admin_generate_locations_missing_nonce', __( 'Nonce field is missing. Locations keyword not generated.', 'page-generator-pro' ) );
		}

		// Invalid nonce.
		if ( ! wp_verify_nonce( sanitize_key( $_POST[ $this->base->plugin->name . '_nonce' ] ), 'generate_locations' ) ) {
			return new WP_Error( 'page_generator_pro_admin_generate_locations_invalid_nonce', __( 'Invalid nonce specified. Locations keyword not generated.', 'page-generator-pro' ) );
		}

		// Run form validation checks.
		if ( empty( $_POST['output_type'] ) || ! is_array( $_POST['output_type'] ) ) {
			return new WP_Error( 'page_generator_pro_admin_generate_locations_missing_output_types', __( 'Please specify the Output Type(s) for the locations.', 'page-generator-pro' ) );
		}

		// Send request to generate locations by area or radius.
		$method = ( isset( $_POST['method'] ) ? sanitize_text_field( wp_unslash( $_POST['method'] ) ) : 'area' );
		switch ( $method ) {
			case 'area':
				$terms = $this->base->get_class( 'keywords_generate_locations' )->generate_locations_by_area(
					array(
						'output_type'                 => ( isset( $_POST['output_type'] ) ? wp_unslash( $_POST['output_type'] ) : '' ), // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
						'country_code'                => ( isset( $_POST['country_code'] ) ? sanitize_text_field( wp_unslash( $_POST['country_code'] ) ) : '' ),
						'regions'                     => ( isset( $_POST['regions'] ) ? wp_unslash( $_POST['regions'] ) : '' ), // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
						'counties'                    => ( isset( $_POST['counties'] ) ? wp_unslash( $_POST['counties'] ) : '' ), // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
						'cities'                      => ( isset( $_POST['cities'] ) ? wp_unslash( $_POST['cities'] ) : '' ), // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
						'exclusions'                  => ( isset( $_POST['exclusions'] ) ? wp_unslash( $_POST['exclusions'] ) : '' ), // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
						'population_min'              => ( isset( $_POST['population_min'] ) ? sanitize_text_field( wp_unslash( $_POST['population_min'] ) ) : '' ),
						'population_max'              => ( isset( $_POST['population_max'] ) ? sanitize_text_field( wp_unslash( $_POST['population_max'] ) ) : '' ),
						'median_household_income_min' => ( isset( $_POST['median_household_income_min'] ) ? sanitize_text_field( wp_unslash( $_POST['median_household_income_min'] ) ) : '' ),
						'median_household_income_max' => ( isset( $_POST['median_household_income_max'] ) ? sanitize_text_field( wp_unslash( $_POST['median_household_income_max'] ) ) : '' ),
						'orderby'                     => ( isset( $_POST['orderby'] ) ? sanitize_text_field( wp_unslash( $_POST['orderby'] ) ) : '' ),
						'order'                       => ( isset( $_POST['order'] ) ? sanitize_text_field( wp_unslash( $_POST['order'] ) ) : '' ),
					)
				);
				break;

			case 'radius':
				$terms = $this->base->get_class( 'keywords_generate_locations' )->generate_locations_by_radius(
					array(
						'output_type'                 => ( isset( $_POST['output_type'] ) ? wp_unslash( $_POST['output_type'] ) : '' ), // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
						'country_code'                => ( isset( $_POST['country_code'] ) ? sanitize_text_field( wp_unslash( $_POST['country_code'] ) ) : '' ),
						'location'                    => ( isset( $_POST['location'] ) ? sanitize_text_field( wp_unslash( $_POST['location'] ) ) : '' ),
						'radius'                      => ( isset( $_POST['radius'] ) ? sanitize_text_field( wp_unslash( $_POST['radius'] ) ) : '' ),
						'exclusions'                  => ( isset( $_POST['exclusions'] ) ? wp_unslash( $_POST['exclusions'] ) : '' ), // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
						'population_min'              => ( isset( $_POST['population_min'] ) ? sanitize_text_field( wp_unslash( $_POST['population_min'] ) ) : '' ),
						'population_max'              => ( isset( $_POST['population_max'] ) ? sanitize_text_field( wp_unslash( $_POST['population_max'] ) ) : '' ),
						'median_household_income_min' => ( isset( $_POST['median_household_income_min'] ) ? sanitize_text_field( wp_unslash( $_POST['median_household_income_min'] ) ) : '' ),
						'median_household_income_max' => ( isset( $_POST['median_household_income_max'] ) ? sanitize_text_field( wp_unslash( $_POST['median_household_income_max'] ) ) : '' ),
						'orderby'                     => ( isset( $_POST['orderby'] ) ? sanitize_text_field( wp_unslash( $_POST['orderby'] ) ) : '' ),
						'order'                       => ( isset( $_POST['order'] ) ? sanitize_text_field( wp_unslash( $_POST['order'] ) ) : '' ),
					)
				);
				break;
		}

		// Bail if an error occured.
		if ( is_wp_error( $terms ) ) {
			return $terms;
		}

		// Save Keyword.
		return $this->base->get_class( 'keywords_generate_locations' )->save(
			$terms,
			( isset( $_POST['output_type'] ) ? wp_unslash( $_POST['output_type'] ) : '' ), // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
			( isset( $_POST['keyword'] ) ? sanitize_text_field( wp_unslash( $_POST['keyword'] ) ) : '' )
		);

	}

	/**
	 * Generate Phone Area Codes
	 *
	 * @since   1.5.9
	 *
	 * @return  WP_Error|bool|int
	 */
	public function generate_phone_area_codes() {

		// Check if a POST request was made.
		if ( ! isset( $_POST['submit'] ) ) {
			return false;
		}

		// Run security checks.
		// Missing nonce.
		if ( ! isset( $_POST[ $this->base->plugin->name . '_nonce' ] ) ) {
			return new WP_Error( __( 'Nonce field is missing. Phone Area Codes NOT generated.', 'page-generator-pro' ) );
		}

		// Invalid nonce.
		if ( ! wp_verify_nonce( sanitize_key( $_POST[ $this->base->plugin->name . '_nonce' ] ), 'generate_phone_area_codes' ) ) {
			return new WP_Error( __( 'Invalid nonce specified. Phone Area Codes NOT generated.', 'page-generator-pro' ) );
		}

		// Bail if no Output Type specified.
		if ( ! isset( $_POST['output_type'] ) || ! is_array( $_POST['output_type'] ) ) {
			return new WP_Error(
				'page_generator_pro_admin_generate_phone_area_codes',
				__( 'At least one Output Type must be specified.', 'page-generator-pro' )
			);

		}

		// Fetch all phone area codes for the given country.
		$terms = $this->base->get_class( 'phone_area_codes' )->get_phone_area_codes( ( isset( $_POST['country'] ) ? sanitize_text_field( wp_unslash( $_POST['country'] ) ) : '' ) );

		// Bail if no area codes were found.
		if ( ! $terms ) {
			return new WP_Error( __( 'No phone area codes could be found for the selected country. Please choose a different country.', 'page-generator-pro' ) );
		}

		// Generate keyword.
		$keyword = array(
			'keyword'   => ( isset( $_POST['keyword'] ) ? sanitize_text_field( wp_unslash( $_POST['keyword'] ) ) : '' ),
			'delimiter' => ( count( $_POST['output_type'] ) > 1 ? ',' : '' ),
			'columns'   => ( count( $_POST['output_type'] ) > 1 ? implode( ',', wp_unslash( $_POST['output_type'] ) ) : '' ), // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
			'data'      => '',
		);

		// Build the keyword data based on the output type formatting.
		$formatted_terms = array();
		foreach ( $terms as $i => $term ) {
			// Define array to build output order for this term.
			$formatted_terms[ $i ] = array();

			// Build array.
			foreach ( wp_unslash( $_POST['output_type'] ) as $output_type ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
				$output_type             = sanitize_text_field( $output_type );
				$formatted_terms[ $i ][] = ( isset( $term[ $output_type ] ) ? $term[ $output_type ] : '' );
			}

			// Remove any empty array values, and implode into a string.
			$formatted_terms[ $i ] = implode( ', ', array_filter( $formatted_terms[ $i ] ) );
		}

		// Remove duplicates.
		// This should never occur, but it's a good fallback just in case.
		$formatted_terms = array_values( array_unique( $formatted_terms ) );

		// Add Terms to keyword data.
		$keyword['data'] .= implode( "\n", $formatted_terms ); // @phpstan-ignore-line

		// Save keyword.
		return $this->base->get_class( 'keywords' )->save( $keyword );

	}

	/**
	 * Save Keyword
	 *
	 * @since   1.0.0
	 *
	 * @return  WP_Error|bool|int
	 */
	public function save_keyword() {

		// Check if a POST request was made.
		if ( ! isset( $_POST['submit'] ) ) {
			return false;
		}

		// Run security checks.
		// Missing nonce.
		if ( ! isset( $_POST[ $this->base->plugin->name . '_nonce' ] ) ) {
			return new WP_Error( 'page_generator_pro_admin_save_keyword', __( 'Nonce field is missing. Settings NOT saved.', 'page-generator-pro' ) );
		}

		// Invalid nonce.
		if ( ! wp_verify_nonce( sanitize_key( $_POST[ $this->base->plugin->name . '_nonce' ] ), 'save_keyword' ) ) {
			return new WP_Error( 'page_generator_pro_admin_save_keyword', __( 'Invalid nonce specified. Settings NOT saved.', 'page-generator-pro' ) );
		}

		// Validate Form Inputs.
		$id           = ( ( isset( $_REQUEST['id'] ) && ! empty( $_REQUEST['id'] ) ) ? absint( $_REQUEST['id'] ) : false );
		$keyword_name = ( isset( $_POST['keyword'] ) ? sanitize_text_field( wp_unslash( $_POST['keyword'] ) ) : '' );
		$source       = ( isset( $_POST['source'] ) ? sanitize_text_field( wp_unslash( $_POST['source'] ) ) : '' );

		// Build Keyword.
		$keyword = array(
			'keyword'   => $keyword_name,
			'source'    => $source,
			'options'   => ( isset( $_POST[ $source ] ) ? wp_unslash( $_POST[ $source ] ) : null ), // phpcs:ignore WordPress.Security.ValidatedSanitizedInput

			// Determined by the Source.
			'data'      => '',
			'delimiter' => '',
			'columns'   => '',
		);

		/**
		 * Define the Keyword properties (data, delimiter and columns) for the given Source
		 * before saving the Keyword to the database.
		 *
		 * @since   3.0.8
		 *
		 * @param   array   $keyword    Keyword arguments.
		 */
		$keyword = apply_filters( 'page_generator_pro_keywords_save_' . $source, $keyword );

		// If the Keyword is a WP_Error, bail.
		if ( is_wp_error( $keyword ) ) { // @phpstan-ignore-line
			return $keyword;
		}

		// Save Keyword (returns WP_Error or Keyword ID).
		return $this->base->get_class( 'keywords' )->save( $keyword, $id );

	}

	/**
	 * Generates content for the given Group and Group Type
	 *
	 * @since   1.2.3
	 */
	public function generate_screen() {

		// Setup notices class, enabling persistent storage.
		$this->base->get_class( 'notices' )->set_key_prefix( 'page_generator_pro_' . wp_get_current_user()->ID );

		// Bail if no Group ID was specified.
		if ( ! isset( $_REQUEST['id'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			$this->base->get_class( 'notices' )->add_error_notice( __( 'No Group ID was specified.', 'page-generator-pro' ) );
			include_once $this->base->plugin->folder . 'views/admin/generate-run-notice.php';
			return;
		}

		// Get Group ID and Type.
		$id   = absint( $_REQUEST['id'] ); // phpcs:ignore WordPress.Security.NonceVerification
		$type = ( isset( $_REQUEST['type'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['type'] ) ) : 'content' ); // phpcs:ignore WordPress.Security.NonceVerification

		// Get groups or groups terms class, depending on the content type we're generating.
		$group = ( ( $type === 'term' ) ? $this->base->get_class( 'groups_terms' ) : $this->base->get_class( 'groups' ) );

		// If this Group has a request to cancel generation, silently clear the status, system and cancel
		// flags before performing further checks on whether we should generate.
		if ( $group->cancel_generation_requested( $id ) ) {
			$group->stop_generation( $id );
		}

		// Fetch group settings.
		$settings = $group->get_settings( $id, false );

		// Bail if an error occured.
		if ( is_wp_error( $settings ) ) {
			$this->base->get_class( 'notices' )->add_error_notice( $settings->get_error_message() );
			include_once $this->base->plugin->folder . 'views/admin/generate-run-notice.php';
			return;
		}

		// Define return to Group URL and Post/Taxonomy Type, depending on the type.
		switch ( $type ) {
			case 'term':
				$return_url   = admin_url( 'term.php?taxonomy=' . $this->base->get_class( 'taxonomy' )->taxonomy_name . '&tag_ID=' . $id );
				$object_type  = get_taxonomy( $settings['taxonomy'] );
				$object_label = $object_type->labels->name;
				break;

			case 'content':
			default:
				$return_url   = admin_url( 'post.php?post=' . $id . '&amp;action=edit' );
				$object       = get_post_type_object( $settings['type'] );
				$object_label = $object->labels->name;
				break;
		}

		// Validate group.
		$validated = $group->validate( $id );
		if ( is_wp_error( $validated ) ) {
			$this->base->get_class( 'notices' )->add_error_notice( $validated->get_error_message() );
			include_once $this->base->plugin->folder . 'views/admin/generate-run-notice.php';
			return;
		}

		// Run actions before Generation has started.
		if ( $type === 'term' ) {
			/**
			 * Runs any actions before Generate Terms has started.
			 *
			 * @since   3.0.7
			 *
			 * @param   int     $group_id   Group ID.
			 * @param   bool    $test_mode  Test Mode.
			 * @param   string  $system     System.
			 */
			do_action( 'page_generator_pro_generate_terms_before', $id, false, 'browser' );
		} else {
			/**
			 * Runs any actions before Generate Content has started.
			 *
			 * @since   3.0.7
			 *
			 * @param   int     $group_id   Group ID.
			 * @param   bool    $test_mode  Test Mode.
			 * @param   string  $system     System.
			 */
			do_action( 'page_generator_pro_generate_content_before', $id, false, 'browser' );
		}

		// Calculate how many pages could be generated.
		$number_of_pages_to_generate = $this->base->get_class( 'generate' )->get_max_number_of_pages( $settings );
		if ( is_wp_error( $number_of_pages_to_generate ) ) {
			$this->base->get_class( 'notices' )->add_error_notice( $number_of_pages_to_generate->get_error_message() );
			include_once $this->base->plugin->folder . 'views/admin/generate-run-notice.php';
			return;
		}

		// If no limit specified, set one now.
		if ( $settings['numberOfPosts'] == 0 ) { // phpcs:ignore Universal.Operators.StrictComparisons.LooseEqual
			if ( $settings['method'] === 'random' ) {
				$settings['numberOfPosts'] = 10;
			} elseif ( $settings['resumeIndex'] > 0 ) {
					$settings['numberOfPosts'] = $number_of_pages_to_generate - $settings['resumeIndex'];
			} else {
				$settings['numberOfPosts'] = $number_of_pages_to_generate;
			}
		}

		// Check that the number of posts doesn't exceed the maximum that can be generated.
		if ( $settings['numberOfPosts'] > $number_of_pages_to_generate ) {
			$settings['numberOfPosts'] = $number_of_pages_to_generate;
		}

		// Set last generated post date and time based on the Group's settings (i.e. date/time of now,
		// specific date/time or a random date/time).
		$last_generated_post_date_time = $this->base->get_class( 'generate' )->post_date( $settings );

		// Add Plugin Settings.
		$settings['stop_on_error']       = (int) $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-generate', 'stop_on_error', 0 );
		$settings['stop_on_error_pause'] = (int) $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-generate', 'stop_on_error_pause', 5 );

		// Enqueue and localize Generate Browser script with the necessary parameters for synchronous AJAX requests.
		wp_enqueue_script( $this->base->plugin->name . '-generate-browser' );
		wp_localize_script( $this->base->plugin->name . '-generate-browser', 'page_generator_pro', $this->base->licensing->get_parameters() );
		wp_localize_script(
			$this->base->plugin->name . '-generate-browser',
			'page_generator_pro_generate_browser',
			array(
				'action'                        => 'page_generator_pro_generate_' . $type,
				'action_on_start'               => 'page_generator_pro_generate_' . $type . '_before',
				'action_on_finished'            => 'page_generator_pro_generate_' . $type . '_after',
				'nonce'                         => wp_create_nonce( 'page-generator-pro-generate-browser' ),
				'id'                            => $id,
				'index_increment'               => (int) $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-generate', 'index_increment', 10 ),
				'last_generated_post_date_time' => $last_generated_post_date_time,
				'max_number_of_pages'           => $number_of_pages_to_generate, // Determines the /[total] portion of the output.
				'number_of_requests'            => $settings['numberOfPosts'] + $settings['resumeIndex'] > $number_of_pages_to_generate ? $number_of_pages_to_generate - $settings['resumeIndex'] : $settings['numberOfPosts'],
				'resume_index'                  => $settings['resumeIndex'],
				'stop_on_error'                 => (int) $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-generate', 'stop_on_error', 0 ),
				'stop_on_error_pause'           => (int) ( $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-generate', 'stop_on_error_pause', 5 ) * 1000 ),
				'exit_screen'                   => __( 'Closing / navigating away from this window will stop generation. Are you sure?', 'page-generator-pro' ),
				'browser_title'                 => array(
					'processing' => sprintf(
						/* translators: Post Type */
						__( '%1$s Generated', 'page-generator-pro' ),
						$object_label
					),
					'success'    => __( 'Generation Complete', 'page-generator-pro' ),
					'cancelled'  => __( 'Generation Cancelled', 'page-generator-pro' ),
				),
			)
		);

		// Set a flag to denote that this Group is generating content.
		$group->start_generation( $id, 'generating', 'browser' );

		// Load View.
		include_once $this->base->plugin->folder . 'views/admin/generate-run.php';

	}

	/**
	 * Outputs the Log Screen
	 *
	 * @since   2.6.1
	 */
	public function log_screen() {

		// Init table.
		$table = new Page_Generator_Pro_Log_Table( $this->base );
		$table->prepare_items();

		// Load View.
		include_once $this->base->plugin->folder . 'views/admin/log.php';

	}

	/**
	 * Outputs the hidden Javascript Modal and Overlay in the Footer
	 *
	 * @since   2.4.6
	 */
	public function output_modal() {

		// Load view.
		require_once $this->base->plugin->folder . '_modules/dashboard/views/modal.php';

	}

}
