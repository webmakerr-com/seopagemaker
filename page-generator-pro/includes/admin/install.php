<?php
/**
 * Installation and Upgrade Class
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Runs installation routines when the Plugin is activated,
 * such as database table creation.
 *
 * Upgrade routines run depending on the existing and updated
 * Plugin version.
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 1.1.8
 */
class Page_Generator_Pro_Install {

	/**
	 * Holds the base object.
	 *
	 * @since   1.3.8
	 *
	 * @var     object
	 */
	public $base;

	/**
	 * Constructor.
	 *
	 * @since   1.9.8
	 *
	 * @param   object $base    Base Plugin Class.
	 */
	public function __construct( $base ) {

		// Store base class.
		$this->base = $base;

	}

	/**
	 * Runs installation routines for first time users
	 *
	 * @since   1.9.8
	 */
	public function install() {

		// Run activation routines on classes.
		$this->base->get_class( 'geo' )->activate();
		$this->base->get_class( 'groups' )->activate();
		$this->base->get_class( 'keywords' )->activate();
		$this->base->get_class( 'log' )->activate();
		$this->base->get_class( 'phone_area_codes' )->activate();

		// Schedule the cron events.
		$this->base->get_class( 'cron' )->schedule_log_cleanup_event();

		// Copy the MU Plugin into the mu-plugins folder.
		$this->copy_mu_plugin();

	}

	/**
	 * Runs migrations for Pro to Pro version upgrades
	 *
	 * @since   1.1.7
	 */
	public function upgrade() {

		global $wpdb;

		// Get current installed version number.
		// false | 1.1.7.
		$installed_version = get_option( $this->base->plugin->name . '-version' );

		// If the version number matches the plugin version, bail.
		if ( $installed_version === $this->base->plugin->version ) {
			return;
		}

		// Copy the MU Plugin into the mu-plugins folder.
		// This will run on every version upgrade.
		$this->copy_mu_plugin();

		// Reschedule the cron events.
		// This will run on every version upgrade.
		$this->base->get_class( 'cron' )->reschedule_log_cleanup_event();

		// (Re)create IndexNow Rewrite Rule.
		$this->base->get_class( 'indexnow' )->create_rewrite_rule();

		/**
		 * 5.0.0: Settings: Generate Locations
		 * Migrate settings from -georocket to -generate-locations, as we now have a provider method
		 * for choosing which source to use for location data.
		 */
		if ( ! $installed_version || $installed_version < '5.0.0' ) {
			$generate_locations_settings = array(
				'provider' => 'georocket',
				'method'   => $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-georocket', 'method', '' ),
				'radius'   => $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-georocket', 'radius', '' ),
			);
			$this->base->get_class( 'settings' )->update_settings( $this->base->plugin->name . '-generate-locations', $generate_locations_settings );

			// Delete old settings.
			delete_option( $this->base->plugin->name . '-georocket' );
		}

		/**
		 * 4.8.0: Content Groups: Standardsize Featured Image keys.
		 */
		if ( ! $installed_version || $installed_version < '4.8.0' ) {
			$groups = $this->base->get_class( 'groups' )->get_all();

			if ( $groups !== false ) {
				foreach ( $groups as $group_id => $settings ) {
					// Update keys.
					if ( array_key_exists( 'featured_image', $settings ) ) {
						$settings['featured_image_term']                 = $settings['featured_image'];
						$settings['featured_image_url']                  = $settings['featured_image'];
						$settings['featured_image_openai_image_topic']   = $settings['featured_image'];
						$settings['featured_image_wikipedia_image_term'] = $settings['featured_image'];
					}
					if ( array_key_exists( 'featured_image_alt', $settings ) ) {
						$settings['featured_image_alt_tag'] = $settings['featured_image_alt'];
					}
					if ( array_key_exists( 'featured_image_pixabay_image_category', $settings ) ) {
						$settings['featured_image_pixabay_category'] = $settings['featured_image_pixabay_image_category'];
					}
					if ( array_key_exists( 'featured_image_pixabay_image_color', $settings ) ) {
						$settings['featured_image_pixabay_color'] = $settings['featured_image_pixabay_image_color'];
					}
					if ( array_key_exists( 'featured_image_topic', $settings ) ) {
						$settings['featured_image_openai_image_topic'] = $settings['featured_image_topic'];
					}
					if ( array_key_exists( 'featured_image_style', $settings ) ) {
						$settings['featured_image_openai_image_style'] = $settings['featured_image_style'];
					}
					if ( array_key_exists( 'featured_image_rewrite', $settings ) ) {
						$settings['featured_image_openai_image_rewrite'] = $settings['featured_image_rewrite'];
					}
					if ( array_key_exists( 'featured_image_size', $settings ) ) {
						$settings['featured_image_openai_image_size'] = $settings['featured_image_size'];
					}
					if ( array_key_exists( 'featured_image_source', $settings ) ) {
						if ( $settings['featured_image_source'] === 'id' ) {
							$settings['featured_image_source'] = 'media-library';
						} elseif ( $settings['featured_image_source'] === 'url' ) {
							$settings['featured_image_source'] = 'image-url';
						}
					}

					// Unset old keys.
					unset(
						$settings['featured_image'],
						$settings['featured_image_alt'],
						$settings['featured_image_pixabay_image_category'],
						$settings['featured_image_pixabay_color'],
						$settings['featured_image_topic'],
						$settings['featured_image_style'],
						$settings['featured_image_rewrite'],
						$settings['featured_image_wikipedia_image_language'],
						$settings['featured_image_wikipedia_image_use_first_image']
					);

					// Save settings.
					update_post_meta( $group_id, '_page_generator_pro_settings', $settings );
				}
			}
		}

		/**
		 * 3.3.4: Merge several API keys into page-generator-pro-integrations option key
		 */
		if ( ! $installed_version || $installed_version < '3.3.4' ) {
			$integration_settings = array(
				'airtable_api_key'         => '', // New to 3.3.4, so there won't be an existing value.
				'open_weather_map_api_key' => $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-open-weather-map', 'api_key' ),
				'pexels_api_key'           => $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-pexels', 'api_key' ),
				'pixabay_api_key'          => $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-pixabay', 'api_key' ),
				'youtube_data_api_key'     => $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-google', 'youtube_data_api_key' ),
			);
			$this->base->get_class( 'settings' )->update_settings( $this->base->plugin->name . '-integrations', $integration_settings );

			// Delete old settings that are now stored in the integrations option above.
			delete_option( $this->base->plugin->name . '-google' );
			delete_option( $this->base->plugin->name . '-open-weather-map' );
			delete_option( $this->base->plugin->name . '-pexels' );
			delete_option( $this->base->plugin->name . '-pixabay' );
		}

		/**
		 * 3.0.8: Upgrade Keywords Table
		 */
		if ( ! $installed_version || $installed_version < '3.0.8' ) {
			$this->base->get_class( 'keywords' )->upgrade();
		}

		/**
		 * 2.7.9: Upgrade Log Table 'result' type
		 */
		if ( ! $installed_version || $installed_version < '2.7.9' ) {
			$this->base->get_class( 'log' )->upgrade();
		}

		/**
		 * 2.6.1: Create Log DB Table
		 */
		if ( ! $installed_version || $installed_version < '2.6.1' ) {
			// Create logging database table.
			$this->base->get_class( 'log' )->activate();
		}

		/**
		 * 2.5.1: Copy Settings > Generate Locations > Country Code to Settings > General > Country Code
		 */
		if ( ! $installed_version || $installed_version < '2.5.1' ) {
			$general_settings = $this->base->get_class( 'settings' )->get_settings( $this->base->plugin->name . '-general' );
			if ( ! is_array( $general_settings ) ) {
				$general_settings = array();
			}
			$general_settings['country_code'] = $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-georocket', 'country_code', 'US' );
			$this->base->get_class( 'settings' )->update_settings( $this->base->plugin->name . '-general', $general_settings );
		}

		/**
		 * 2.4.5: Upgrade Keywords Table 'columns' type
		 */
		$this->base->get_class( 'keywords' )->upgrade_columns_type_to_text();

		/**
		 * 2.3.6: Install Geo Table
		 */
		$this->base->get_class( 'geo' )->activate();

		/**
		 * 1.7.8: Upgrade Keywords Table
		 */
		if ( ! $installed_version || $installed_version < '1.7.9' ) {
			$this->base->get_class( 'keywords' )->upgrade();
		}

		/**
		 * 1.5.8: Install Phone Area Codes Table
		 */
		if ( ! $installed_version || $installed_version < '1.5.9' ) {
			$this->base->get_class( 'phone_area_codes' )->activate();
		}

		/**
		 * Free to Free 1.3.8+
		 * Free to Pro 1.3.8+
		 * - If page-generator-pro exists as an option, and there are no groups, migrate settings of the single group
		 * to a single group CPT
		 */
		if ( ! $installed_version || $installed_version < '1.3.8' ) {
			$number_of_groups = $this->base->get_class( 'groups' )->get_count();
			$free_settings    = get_option( 'page-generator' );

			if ( $number_of_groups === 0 && ! empty( $free_settings ) ) {
				// Migrate settings.
				$group = array(
					'name'     => $free_settings['title'],
					'settings' => $free_settings,
				);

				// Generate Group Post.
				$group_id = wp_insert_post(
					array(
						'post_type'    => $this->base->get_class( 'post_type' )->post_type_name,
						'post_status'  => 'publish',
						'post_title'   => $group['name'],
						'post_content' => $free_settings['content'],
					),
					true
				);

				// Bail if an error occured.
				if ( is_wp_error( $group_id ) ) {
					return;
				}

				// Save group settings.
				$result = $this->base->get_class( 'groups' )->save( $group, $group_id );

				// If this failed, don't clear the existing settings.
				if ( is_wp_error( $result ) ) {
					return;
				}

				// Clear existing settings.
				delete_option( 'page-generator' );
			}
		}

		/**
		 * Pro to Pro 1.2.x+
		 * - If a Groups table exists, migrate Groups to CPTs
		 */
		if ( ! $installed_version || $installed_version < '1.2.3' ) {
			// If the table exists, migrate the data from it.
			$table_exists = $wpdb->get_var( "SHOW TABLES LIKE '" . $wpdb->prefix . "page_generator_groups'" );
			if ( $table_exists === $wpdb->prefix . 'page_generator_groups' ) {
				// Fetch all groups.
				$groups = $wpdb->get_results( 'SELECT * FROM ' . $wpdb->prefix . 'page_generator_groups' );

				// Use a flag to tell us whether any errors occured during the groups to CPT migratio process.
				$errors = false;

				// Iterate through each group, migrating to a CPT.
				if ( is_array( $groups ) && count( $groups ) > 0 ) {
					foreach ( $groups as $group ) {
						// Unserialize the settings.
						$settings = unserialize( $group->settings ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions

						// Create new Post.
						$post_id = wp_insert_post(
							array(
								'post_type'    => $this->base->get_class( 'post_type' )->post_type_name,
								'post_status'  => 'publish',
								'post_title'   => $settings['title'],
								'post_content' => $settings['content'],
							),
							true
						);

						// If an error occured, skip.
						if ( is_wp_error( $post_id ) ) {
							$errors = true;
							continue;
						}

						// Remove the settings that we no longer need to store in the Post Meta.
						unset( $settings['title'], $settings['content'] );

						// Store the settings in the Post's meta.
						$this->base->get_class( 'groups' )->save( $settings, $post_id );
					}
				}

				// If no errors occured, we can safely remove the groups table.
				if ( ! $errors ) {
					$wpdb->query( 'DROP TABLE ' . $wpdb->prefix . 'page_generator_groups' );
				}
			}
		}

		// Update the version number.
		update_option( $this->base->plugin->name . '-version', $this->base->plugin->version );

	}

	/**
	 * Copies the Must-Use Plugin from this Plugin into the mu-plugins folder.
	 *
	 * @since   1.9.7
	 *
	 * @param   bool $force  Force Copy.
	 */
	public function copy_mu_plugin( $force = true ) {

		// Bail if there is no WPMU_PLUGIN_DIR constant.
		if ( ! defined( 'WPMU_PLUGIN_DIR' ) ) {
			return;
		}

		// Flag to enable/disable automatic copying of the Must-Use Plugin.
		$copy_mu_plugin = true;

		/**
		 * Enable automatic copying of the Must-Use Plugin each time Page Generator Pro
		 * is updated to a newer version.
		 *
		 * @since   2.4.5
		 *
		 * @param   bool    $copy_mu_plugin     Copy MU Plugin.
		 */
		$copy_mu_plugin = apply_filters( 'page_generator_pro_install_copy_mu_plugin', $copy_mu_plugin );

		// Bail if automatic copying of the Must-Use Plugin is disabled.
		if ( ! $copy_mu_plugin ) {
			return;
		}

		// Create the mu-plugins folder, if it doesn't exist.
		if ( ! @file_exists( WPMU_PLUGIN_DIR ) ) { // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
			$result = @mkdir( WPMU_PLUGIN_DIR ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged, WordPress.WP.AlternativeFunctions.file_system_operations_mkdir
			if ( ! $result ) {
				return new WP_Error(
					'page_generator_pro_install_copy_mu_plugin',
					sprintf(
						/* translators: Value of WPMU_PLUGIN_DIR */
						__( 'Could not create mu-plugins folder at %s.', 'page-generator-pro' ),
						WPMU_PLUGIN_DIR
					)
				);
			}
		}

		// Define the Plugin Folder and Filename.
		$mu_plugin_filename    = 'page-generator-pro-performance-addon.php';
		$mu_plugin_source      = $this->base->plugin->folder . '/mu-plugins/' . $mu_plugin_filename;
		$mu_plugin_destination = WPMU_PLUGIN_DIR . '/' . $mu_plugin_filename;

		// Bail if the file exists in the WPMU_PLUGIN_DIR, and we're not forcing an overwrite.
		if ( @file_exists( $mu_plugin_destination ) && ! $force ) { // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
			return;
		}

		// Delete the existing mu-plugins file if it exists and we're forcing an update.
		if ( @file_exists( $mu_plugin_destination ) && $force ) { // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
			wp_delete_file( $mu_plugin_destination );
		}

		// Copy the mu-plugin to WPMU_PLUGIN_DIR.
		$result = @copy( $mu_plugin_source, $mu_plugin_destination ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
		if ( ! $result ) {
			return new WP_Error(
				'page_generator_pro_install_copy_mu_plugin',
				sprintf(
					/* translators: %1$s: Source path and file name, %2$s: Destination path and file name */
					__( 'Could not copy %1$s to %2$s.', 'page-generator-pro' ),
					$mu_plugin_source,
					$mu_plugin_destination
				)
			);
		}

		// OK.
		return true;

	}

	/**
	 * Deletes the Must-Use Plugin from the mu-plugins folder.
	 *
	 * @since   2.4.6
	 */
	public function delete_mu_plugin() {

		// Bail if we're in a multisite environment, as removing the mu-plugin
		// would affect other sites on the network where the Plugin is active.
		if ( is_multisite() ) {
			return true;
		}

		// Bail if there is no WPMU_PLUGIN_DIR constant.
		if ( ! defined( 'WPMU_PLUGIN_DIR' ) ) {
			return true;
		}

		// Bail if the mu-plugins folder doesn't exist.
		if ( ! @file_exists( WPMU_PLUGIN_DIR ) ) { // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
			return true;
		}

		// Define the Plugin Folder and Filename.
		$mu_plugin_filename    = 'page-generator-pro-performance-addon.php';
		$mu_plugin_source      = $this->base->plugin->folder . '/mu-plugins/' . $mu_plugin_filename;
		$mu_plugin_destination = WPMU_PLUGIN_DIR . '/' . $mu_plugin_filename;

		// Bail if the file does not exist in the WPMU_PLUGIN_DIR, as there's nothing to delete.
		if ( ! file_exists( $mu_plugin_destination ) ) {
			return true;
		}

		// Delete the existing mu-plugins file if it exists.
		wp_delete_file( $mu_plugin_destination );

		// OK.
		return true;

	}

	/**
	 * Runs uninstallation routines
	 *
	 * @since   1.9.8
	 */
	public function uninstall() {

		// Copy the MU Plugin into the mu-plugins folder.
		$this->delete_mu_plugin();

		// Unschedule any CRON events.
		$this->base->get_class( 'cron' )->unschedule_log_cleanup_event();
	}

}
