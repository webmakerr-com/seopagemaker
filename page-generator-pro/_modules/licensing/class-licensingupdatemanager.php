<?php
/**
 * Provides licensing functionality for Plugins.
 *
 * @package LicensingUpdateManager
 * @author WP Zinc
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Licensing and Update Manager Class
 *
 * @package      Licensing Update Manager
 * @author       WP Zinc
 * @version      3.0.0
 * @copyright    WP Zinc
 */
class LicensingUpdateManager {

        /**
         * Option name to store the license value.
         *
         * @since 5.3.3
         *
         * @var string
         */
        private $license_option_name = 'webmakerr_lpb_license';

        /**
         * Valid license key value.
         *
         * @since 5.3.3
         *
         * @var string
         */
        private $valid_license_key = 'WEBM-LPB-9921-FF28-MK77-AX3Q';

	/**
	 * Flag to determine if we've queried the remote endpoint
	 * for updates. Prevents plugin update checks running
	 * multiple times
	 *
	 * @since   1.0.0
	 *
	 * @var     bool
	 */
	public $update_check = false;

	/**
	 * Holds details about the Plugin, such as its path,
	 * version number and URL.
	 *
	 * @since   1.0.0
	 *
	 * @var     bool|stdClass
	 */
	public $plugin = false;

	/**
	 * Holds the licensing endpoint.
	 *
	 * @since   1.0.0
	 *
	 * @var     bool|string
	 */
	public $endpoint = false;

        /**
         * Holds notices to output in the Administration UI.
         *
         * @since   1.0.0
         *
         * @var     bool|stdClass
         */
        public $notice = false;

        /**
         * Holds an error message to output on the Licensing screen.
         *
         * @since 5.3.3
         *
         * @var string
         */
        public $errorMessage = ''; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase

	/**
	 * Holds an array of submenus to display, if any
	 *
	 * @since   1.0.0
	 *
	 * @var     bool|array
	 */
	private $show_submenus = false;

	/**
	 * Holds an array of permitted users, if any
	 *
	 * @since   1.0.0
	 *
	 * @var     bool|array
	 */
	private $permitted_users = false;

	/**
	 * Holds the current logged in User
	 *
	 * @since   1.0.0
	 *
	 * @var     bool|WP_User
	 */
	private $current_user = false;

	/**
	 * Constructor.
	 *
	 * @since   1.0.0
	 *
	 * @param   object $plugin    WordPress Plugin.
	 * @param   string $endpoint  Licensing Endpoint.
	 */
	public function __construct( $plugin, $endpoint ) {

		global $pagenow;

		// Set Plugin and Endpoint.
		$this->plugin   = $plugin;
		$this->endpoint = $endpoint;

		// Admin Notice.
		$this->notice = new stdClass();

		if ( is_admin() ) {
			/**
			 * Updates
			 * - Delete cache if we're forcing an update check via WordPress Admin > Updates
			 * - Load some JS for whitelabelling the Plugin Name, as there's no filter to use on this screen
			 */
			if ( $pagenow === 'update-core.php' ) {
				add_action( 'admin_footer', array( $this, 'whitelabel_js' ) );

				if ( filter_has_var( INPUT_GET, 'force-check' ) ) {
					$this->cache_delete();
				}
			}

			/**
			 * Licensing Screen
			 */
			if ( filter_has_var( INPUT_GET, 'page' ) && filter_input( INPUT_GET, 'page', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) === $this->plugin->name ) {
				if ( filter_has_var( INPUT_POST, $this->plugin->name ) ) {
					$data = filter_input( INPUT_POST, $this->plugin->name, FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
					if ( isset( $data['licenseKey'] ) ) {
						update_option( $this->license_option_name, sanitize_text_field( wp_unslash( $data['licenseKey'] ) ) );
						$this->cache_delete();
					}
				}

				// Force license key check.
				$this->check_license_key_valid( true );
			} else {
				// Check license key, trusting cache.
				$this->check_license_key_valid( false );
			}

			// Hooks and Filters.
			add_action( 'admin_notices', array( $this, 'admin_notices' ) );
			add_filter( 'plugins_api', array( $this, 'plugins_api' ), 10, 3 );
			add_action( 'admin_init', array( $this, 'maybe_block_without_license' ), 0 );
			add_action( 'current_screen', array( $this, 'maybe_block_on_screen' ) );

			// Whitelabelling Filters.
			add_filter( 'all_plugins', array( $this, 'maybe_filter_plugin_name' ) );
		} else {
			// Check license key, trusting cache.
			$this->check_license_key_valid( false );
		}

		// Check for updates, outside of is_admin() so WP-CLI is supported.
		add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'api_check' ), 50 );
		add_action( 'delete_site_transient_update_plugins', array( $this, 'cache_delete' ) );

	}

	/**
	 * Outputs the Licensing Screen
	 *
	 * @since   1.0.0
	 */
	public function licensing_screen() {

		include_once 'views/licensing.php';

	}

	/**
	 * Injects JS into the footer at Dashboard > Updates if whitelabelling is enabled
	 * and the Plugin Name needs to be changed, as there is no WordPress Filter/Hook
	 * to achieve this on this screen
	 *
	 * @since   1.0.0
	 */
	public function whitelabel_js() {

		// Bail if whitelabelling isn't available.
		if ( ! $this->has_feature( 'whitelabelling' ) ) {
			return;
		}

		// Use JS to replace the name.
		// $this->plugin->displayName will have been whitelabelled by now.
		?>
		<script>
			jQuery(document).ready(function($){
				if ( $('input[value="<?php echo esc_attr( $this->plugin->name ) . '/' . esc_attr( $this->plugin->name ) . '.php'; ?>"]').length > 0 ) {
					$( 'td.plugin-title p strong', $('input[value="<?php echo esc_attr( $this->plugin->name ) . '/' . esc_attr( $this->plugin->name ) . '.php'; ?>"]').closest( 'tr' ) ).text( '<?php echo esc_attr( $this->plugin->displayName ); ?>' );   
				}
			});
		</script>
		<?php

	}

	/**
	 * Outputs Administration Notices relating to license key validation
	 *
	 * @since   3.0.0
	 */
	public function admin_notices() {

		// Get cache.
		$cache = $this->cache_get();

		// Bail if there is no message to display.
		if ( ! isset( $cache['message'] ) ) {
			return;
		}
		if ( empty( $cache['message'] ) ) {
			return;
		}

		// If the license isn't valid and we have a message to show the user, show it now and exit.
		if ( ! $cache['valid'] ) {
			?>
			<div class="notice error">
				<p>
					<?php
					echo wp_kses(
						$cache['message'],
						array(
							'a'  => array(
								'href'   => array(),
								'target' => array(),
							),
							'br' => array(),
						)
					);
					?>
				</p>	
			</div>
			<?php
			return;
		}

		// If here, the license is valid. Only show that it's valid if we're on the Licensing Screen
		// so we don't bombard the user with this message site-wide.
		$screen = get_current_screen();
		if ( $screen->base === 'toplevel_page_' . $this->plugin->name ||
			( filter_has_var( INPUT_GET, 'page' ) && filter_input( INPUT_GET, 'page', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) === $this->plugin->name ) ) {
			?>
			<div class="notice updated">
				<p>
					<?php
					echo wp_kses(
						$cache['message'],
						array(
							'a'  => array(
								'href'   => array(),
								'target' => array(),
							),
							'br' => array(),
						)
					);
					?>
				</p>	
			</div>
			<?php
			return;
		}

	}

	/**
	 * Gets the license key from either the wp-config constant, or the options table
	 *
	 * @since   3.0.0
	 *
	 * @return  string  License Key
	 */
	public function get_license_key() {

		// If the license key is defined in wp-config, use that.
if ( $this->is_license_key_a_constant() ) {
// Get from wp-config.
$license_key = constant( strtoupper( $this->plugin->name ) . '_LICENSE_KEY' );
} else {
// Get from options table.
$license_key = get_option( $this->license_option_name );
}

		return $license_key;

	}

	/**
	 * Returns a flag denoting whether the license key is stored as a PHP constant
	 *
	 * @since   3.0.0
	 *
	 * @return  bool
	 */
	public function is_license_key_a_constant() {

		return defined( strtoupper( $this->plugin->name ) . '_LICENSE_KEY' );

	}

	/**
	 * Checks whether a license key has been specified in the settings table.
	 *
	 * @since   3.0.0
	 *
	 * @return  bool    License Key Exists
	 */
	public function check_license_key_exists() {

		// Get license key.
		$license_key = $this->get_license_key();

		// Return license key.
		return ( ( isset( $license_key ) && trim( $license_key ) !== '' ) ? true : false );

	}

	/**
	 * Checks whether the license key stored in the settings table exists and is valid.
	 *
	 * If so, we store the latest remote plugin details in our own 'cache', which can then be used when
	 * updating plugins.
	 *
	 * @since   3.0.0
	 *
        * @param   bool $force     Force License Key Check, ignoring cache.
        * @return  bool            License Key Valid
        */
        public function check_license_key_valid( $force = false ) {

                $license_key = trim( (string) $this->get_license_key() );
                $valid       = ( $license_key === $this->valid_license_key );

                if ( $valid ) {
                        $this->cache_set(
                                true,
                                sprintf(
                                        /* translators: Plugin Name */
                                        __( '%s: License validated.', $this->plugin->name ), // phpcs:ignore WordPress.WP.I18n
                                        $this->plugin->displayName
                                ),
                                $this->plugin->version,
                                '',
                                array(),
                                array()
                        );

                        return true;
                }

                $message = __( 'A valid license key is required to use Webmakerr Location Pages.', $this->plugin->name );

                $this->cache_set( false, $message, '', '', array(), array() );

                if ( is_admin() && $this->is_license_screen() ) {
                        $this->errorMessage = $message; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
                }

                return false;

        }

        /**
         * Determines if the current request is the license screen.
         *
         * @since 5.3.3
         *
         * @return bool
         */
        private function is_license_screen() {

                return ( filter_has_var( INPUT_GET, 'page' ) && filter_input( INPUT_GET, 'page', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) === $this->plugin->name );

        }

        /**
         * Determines if the current request targets any plugin page via query parameters.
         *
         * @since 5.3.3
         *
         * @return bool
         */
        private function is_plugin_request() {

                $page      = filter_input( INPUT_GET, 'page', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
                $post_type = filter_input( INPUT_GET, 'post_type', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
                $taxonomy  = filter_input( INPUT_GET, 'taxonomy', FILTER_SANITIZE_FULL_SPECIAL_CHARS );

                if ( $page && strpos( $page, $this->plugin->name ) === 0 ) {
                        return true;
                }

                if ( $post_type === $this->plugin->name ) {
                        return true;
                }

                if ( $taxonomy && strpos( $taxonomy, $this->plugin->name ) === 0 ) {
                        return true;
                }

                return false;

        }

        /**
         * Determines if the supplied screen matches a plugin page.
         *
         * @since 5.3.3
         *
         * @param WP_Screen $screen Screen object.
         * @return bool
         */
        private function is_plugin_screen( $screen ) {

                if ( ! $screen ) {
                        return false;
                }

                if ( $screen->post_type === $this->plugin->name ) {
                        return true;
                }

                if ( $screen->taxonomy && strpos( $screen->taxonomy, $this->plugin->name ) === 0 ) {
                        return true;
                }

                if ( $screen->id && strpos( $screen->id, $this->plugin->name ) === 0 ) {
                        return true;
                }

                return false;

        }

        /**
         * Redirects to the license screen if the license is invalid when loading plugin pages early in the request.
         *
         * @since 5.3.3
         */
        public function maybe_block_without_license() {

                if ( ! is_admin() ) {
                        return;
                }

                if ( $this->check_license_key_valid() ) {
                        return;
                }

                if ( $this->is_license_screen() ) {
                        return;
                }

                if ( ! $this->is_plugin_request() ) {
                        return;
                }

                wp_safe_redirect( admin_url( 'admin.php?page=' . $this->plugin->name ) );
                exit;

        }

        /**
         * Redirects to the license screen if the license is invalid after the current screen is set.
         *
         * @since 5.3.3
         *
         * @param WP_Screen $screen Screen object.
         */
        public function maybe_block_on_screen( $screen ) {

                if ( ! is_admin() ) {
                        return;
                }

                if ( $this->check_license_key_valid() ) {
                        return;
                }

                if ( $this->is_license_screen() ) {
                        return;
                }

                if ( ! $this->is_plugin_screen( $screen ) ) {
                        return;
                }

                wp_safe_redirect( admin_url( 'admin.php?page=' . $this->plugin->name ) );
                exit;

        }

	/**
	 * Returns parameters that are used for license requests
	 *
	 * @since   1.0.0
	 *
	 * @return  array
	 */
	public function get_parameters() {

		// Parse Site URL.
		$url = wp_parse_url( get_bloginfo( 'url' ) );

		// Return Params.
		return array(
			'license_key'    => $this->get_license_key(),
			'plugin_name'    => $this->plugin->name,
			'plugin_version' => $this->plugin->version,
			'site_url'       => str_replace( $url['scheme'] . '://', '', get_bloginfo( 'url' ) ),
			'site_url_full'  => $url['scheme'] . '://' . $url['host'],
			'is_multisite'   => ( is_multisite() ? '1' : '0' ),
			'wp_version'     => get_bloginfo( 'version' ),
		);

	}

	/**
	 * Checks to see if the License has access to a given Feature.
	 *
	 * @since   1.0.0
	 *
        * @param   string $feature    Feature.
        */
        public function has_feature( $feature ) {

                // With open access licensing, all features are available regardless of the stored value.
                return true;

	}

	/**
	 * Returns a feature's parameter (such as whitelabelling > display name), if
	 * the license's license type permits the feature and the parameter exists
	 * in either wp-config (1st) or the license payload (2nd).
	 *
	 * @since   1.0.0
	 *
	 * @param   string $feature        Feature.
	 * @param   string $parameter      Parameter.
	 * @param   mixed  $default_value  Default Value, if Feature or Feature Parameter is not defined.
	 * @return  mixed                   bool| string
	 */
	public function get_feature_parameter( $feature, $parameter, $default_value = '' ) {

		// Check the license has the feature.
		$has_feature = $this->has_feature( $feature );
		if ( ! $has_feature ) {
			return $default_value;
		}

		// If the Feature Parameter exists in wp-config, use that.
		if ( defined( strtoupper( $this->plugin->name ) . '_' . strtoupper( $parameter ) ) ) {
			// Convert to an array depending on the parameter.
			switch ( $parameter ) {
				case 'show_submenus':
				case 'permitted_users':
					return explode( ',', constant( strtoupper( $this->plugin->name ) . '_' . strtoupper( $parameter ) ) );

				default:
					return constant( strtoupper( $this->plugin->name ) . '_' . strtoupper( $parameter ) );
			}
		}

                // Check if the Feature Parameter exists in the license payload's cache.
                $cache = $this->cache_get();

                if ( ! isset( $cache['features_parameters'][ $feature ] ) ) {
                        return $default_value; // assumptive.
                }
                if ( ! isset( $cache['features_parameters'][ $feature ][ $parameter ] ) ) {
                        return $default_value;
                }

                // Return feature parameter.
                return $cache['features_parameters'][ $feature ][ $parameter ];

	}

	/**
	 * Hooks into the plugin update check process, telling WordPress if a newer version of our
	 * Plugin is available.
	 *
	 * @since   3.0.0
	 *
	 * @param   array $transient  Transient.
	 * @return  array               Transient Plugin Data
	 */
	public function api_check( $transient ) {

		// If we haven't called the licensing endpoint (which includes product update info),
		// do so now.
		if ( ! $this->update_check ) {
			$this->update_check = true;

			// If the license key isn't valid, bail.
			if ( ! $this->check_license_key_valid( true ) ) {
				return $transient;
			}
		}

		// Get remote package data from cache.
		// This was populated by the update/license checks earlier.
		$cache = $this->cache_get();

		// If cache has a newer version available, show this in WordPress.
		if ( ! empty( $cache['version'] ) && $cache['version'] > $this->plugin->version ) {
			// New version available.
			if ( $cache['package']->slug === $this->plugin->name ) {
				// Add to transient.
				$response              = new stdClass();
				$response->slug        = $this->plugin->name;
				$response->plugin      = $this->plugin->name . '/' . $this->plugin->name . '.php';
				$response->new_version = $cache['version'];

				// Package is only available in the cache if the license key is valid.
				// Expired or Domain Exceeded licenses won't have this data, but we
				// want to show the user that their product is out of date by setting
				// the new version above.
				if ( ! empty( $cache['package'] ) ) {
					$response->url      = $cache['package']->homepage;
					$response->requires = $cache['package']->requires;
					$response->tested   = $cache['package']->tested;

					if ( isset( $cache['package']->download_link ) ) {
						$response->package = $cache['package']->download_link;
					}
				}

				// If the transient is null, set up the required basic structure now.
				// This covers rare edge cases where the `update_plugins` transient
				// has never been set (it should, at a minimum, be an object comprising of
				// `last_checked`).
				if ( is_null( $transient ) ) {
					$transient           = new stdClass();
					$transient->response = array();
				}
				if ( ! isset( $transient->response ) ) {
					$transient->response = array();
				}

				// Add response to transient array.
				$transient->response[ $this->plugin->name . '/' . $this->plugin->name . '.php' ] = $response;
			}
		}

		return $transient;

	}

	/**
	 * Hooks into the plugins_api process, telling WordPress information about our plugin, such
	 * as the WordPress compatible version and the changelog.
	 *
	 * @since 3.0.0
	 *
	 * @param object $api    The original plugins_api object.
	 * @param string $action The action sent by plugins_api.
	 * @param array  $args   Additional args to send to plugins_api.
	 * @return object           New stdClass with plugin information on success, default response on failure.
	 */
	public function plugins_api( $api, $action = '', $args = null ) {

		// Check if we are getting info for our plugin.
		$plugin = ( 'plugin_information' === $action ) && isset( $args->slug ) && ( $this->plugin->name === $args->slug );
		if ( ! $plugin ) {
			return $api;
		}

		// Get remote package data from cache.
		// This was populated by the update/license checks earlier.
		$cache = $this->cache_get();

		// Create a new stdClass object and populate it with our plugin information.
		$api          = new stdClass();
		$api->name    = $this->plugin->displayName;
		$api->slug    = $this->plugin->name;
		$api->plugin  = $this->plugin->name . '/' . $this->plugin->name . '.php';
		$api->version = $cache['version'];

		// Package is only available in the cache if the license key is valid
		// Expired or Domain Exceeded licenses won't have this data, but we
		// want to show the user that their product is out of date by setting
		// the new version above.
		if ( ! empty( $cache['package'] ) ) {
			$api->author                = $cache['package']->author;
			$api->author_profile        = $cache['package']->author_profile;
			$api->requires              = $cache['package']->requires;
			$api->tested                = $cache['package']->tested;
			$api->last_updated          = gmdate( 'Y-m-d H:i:s', $cache['package']->last_updated );
			$api->homepage              = $cache['package']->homepage;
			$api->sections['changelog'] = $cache['package']->changelog;

			if ( isset( $cache['package']->download_link ) ) {
				$api->download_link = $cache['package']->download_link;
			}

			// If whitelabelling isn't available, just return the data now.
			if ( ! $this->has_feature( 'whitelabelling' ) ) {
				return $api;
			}

			// Whitelabel values.
			$changelog_url = $this->get_feature_parameter( 'whitelabelling', 'changelog_url', '' );
			if ( ! empty( $changelog_url ) ) {
				$api->sections['changelog'] = '<a href="' . $changelog_url . '" target="_blank">' . __( 'View Changelog', $this->plugin->name ) . '</a>'; // phpcs:ignore WordPress.WP.I18n
			}

			$api->author         = $this->get_feature_parameter( 'whitelabelling', 'author_name', $cache['package']->author );
			$api->author_profile = $this->get_feature_parameter( 'whitelabelling', 'support_url', $cache['package']->author_profile );
			$api->homepage       = $this->get_feature_parameter( 'whitelabelling', 'support_url', $cache['package']->homepage );
		}

		// Return the new API object with our custom data.
		return $api;

	}

	/**
	 * Filter the Plugin Name, Author Name and Plugin URI
	 * if whitelabelling is enabled.
	 *
	 * @since   1.0.0
	 *
	 * @param   array $plugins    All Installed Plugins.
	 * @return  array               All Installed Plugins
	 */
	public function maybe_filter_plugin_name( $plugins ) {

		// Bail if whitelabelling isn't available.
		if ( ! $this->has_feature( 'whitelabelling' ) ) {
			return $plugins;
		}

		// Bail if this Plugin isn't in the list.
		if ( ! isset( $plugins[ $this->plugin->name . '/' . $this->plugin->name . '.php' ] ) ) {
			return $plugins;
		}

		// Get whitelabelling values.
		$display_name = $this->get_feature_parameter( 'whitelabelling', 'display_name', $this->plugin->displayName );
		$description  = $this->get_feature_parameter( 'whitelabelling', 'description', $this->plugin->description );
		$author_name  = $this->get_feature_parameter( 'whitelabelling', 'author_name', $this->plugin->author_name );
		$support_url  = $this->get_feature_parameter( 'whitelabelling', 'support_url', $this->plugin->support_url );

		// Change the Plugin Name, Author Name and URIs.
		$plugins[ $this->plugin->name . '/' . $this->plugin->name . '.php' ]['Name']  = $display_name;
		$plugins[ $this->plugin->name . '/' . $this->plugin->name . '.php' ]['Title'] = $display_name;

		$plugins[ $this->plugin->name . '/' . $this->plugin->name . '.php' ]['Description'] = $description;

		$plugins[ $this->plugin->name . '/' . $this->plugin->name . '.php' ]['Author']     = $author_name;
		$plugins[ $this->plugin->name . '/' . $this->plugin->name . '.php' ]['AuthorName'] = $author_name;

		$plugins[ $this->plugin->name . '/' . $this->plugin->name . '.php' ]['PluginURI'] = $support_url;
		$plugins[ $this->plugin->name . '/' . $this->plugin->name . '.php' ]['AuthorURI'] = $support_url;

		// Return.
		return $plugins;

	}

	/**
	 * Determines whether the logged in WordPress User has access to a particular
	 * feature, by:
	 * - checking if the license key has access control options,
	 * - the feature is defined in the wp-config file,
	 * - the value in the wp-config file permits or denies access
	 *
	 * This function assumes access until a condition revokes it.
	 *
	 * @since   2.1.7
	 *
	 * @param   string $parameter  Feature Parameter the user is attempting to access.
	 * @return  bool                User can access feature
	 */
	public function can_access( $parameter ) {

		// If the logged in user is always permitted to use the Plugin, always allow access,
		// ignoring any other setting.
		if ( $this->is_logged_in_user_always_permitted() ) {
			return true;
		}

		switch ( $parameter ) {

			/**
			 * Menu
			 */
			case 'show_menu':
				return $this->display_menu();

			/**
			 * Submenu
			 */
			default:
				list( $ignored, $submenu ) = explode( 'show_menu_', $parameter );
				return $this->display_submenu( $submenu );

		}

	}

	/**
	 * Determines if the Plugin's Top Level Menu should be displayed
	 *
	 * @since   1.0.0
	 *
	 * @return  bool    Display Menu
	 */
	private function display_menu() {

		return $this->get_feature_parameter( 'access_control', 'show_menu', true );

	}

	/**
	 * Determines if the given Plugin's Child / Sub Menu should be displayed
	 *
	 * @since   1.0.0
	 *
	 * @param   string $submenu    Submenu to Display.
	 * @return  bool                Display Submenu
	 */
	private function display_submenu( $submenu ) {

		// Get submenus to display.
		if ( ! $this->show_submenus ) {
			$this->show_submenus = $this->get_feature_parameter( 'access_control', 'show_submenus', true );
		}

		// For backward compatibility, check some other submenu constants that might exist in 2.1.7 - 2.4.3
		// e.g. PAGE-GENERATOR_PRO_SHOW_MENU_SETTINGS, which is now PAGE-GENERATOR-PRO-SHOW_SUBMENUS = settings.
		if ( defined( strtoupper( $this->plugin->name ) . '_SHOW_MENU_' . strtoupper( $submenu ) ) ) {
			return constant( strtoupper( $this->plugin->name ) . '_SHOW_MENU_' . strtoupper( $submenu ) );
		}

		// If no submenus to display are specified, allow all submenus.
		if ( ! is_array( $this->show_submenus ) ) {
			return true;
		}
		if ( ! count( $this->show_submenus ) ) {
			return true;
		}

		// Check if the submenu is a permitted submenu.
		return in_array( $submenu, $this->show_submenus, true );

	}

	/**
	 * Determines if the logged in User is always permitted to access the Plugin, regardless
	 * of any other settings that might be defined.
	 *
	 * @since   1.0.0
	 *
	 * @return  bool    User is permitted
	 */
	private function is_logged_in_user_always_permitted() {

		// Get permitted users.
		if ( ! $this->permitted_users ) {
			$this->permitted_users = $this->get_feature_parameter( 'access_control', 'permitted_users', false );
		}

		// If no permitted users are specified, the user is not permitted to override access control settings.
		if ( ! $this->permitted_users ) {
			return false;
		}
		if ( empty( $this->permitted_users ) ) {
			return false;
		}

		// Fetch the logged in User.
		if ( ! $this->current_user ) {
			$this->current_user = wp_get_current_user();
		}

		// Check if the logged in User is a permitted User.
		foreach ( $this->permitted_users as $permitted_user ) {
			// Permitted user can be a User ID, username or email address.
			if ( $this->current_user->user_login == $permitted_user ) { // phpcs:ignore Universal.Operators.StrictComparisons.LooseEqual
				return true;
			}
			if ( $this->current_user->user_email == $permitted_user ) { // phpcs:ignore Universal.Operators.StrictComparisons.LooseEqual
				return true;
			}
			if ( $this->current_user->ID == $permitted_user ) { // phpcs:ignore Universal.Operators.StrictComparisons.LooseEqual
				return true;
			}
		}

		// If here, the user is not permitted.
		return false;

	}

	/**
	 * Fetches cached data from the WordPress options table
	 *
	 * @since   1.0.0
	 *
	 * @return  array   Cached Data
	 */
	private function cache_get() {

		// Define defaults.
                $defaults = array(
                        'valid'               => 0,
                        'message'             => '',
                        'version'             => 0,
                        'package'             => '',
                        'features'            => array(),
                        'features_parameters' => array(),
                        'expires'             => 0,
                );

		// Get cache.
		$cache = get_option( $this->plugin->name . '_lum', $defaults );

		// If the cache has expired, delete it and return the defaults.
		if ( is_null( $cache ) || strtotime( 'now' ) > $cache['expires'] ) {
			$this->cache_delete();
			return $defaults;
		}

		// Return cached data.
		return $cache;

	}

	/**
	 * Sets cached data in the WordPress options table for a day
	 *
	 * @since   1.0.0
	 *
	 * @param   bool   $valid                  License Key Valid.
	 * @param   string $message                License Key Message.
	 * @param   string $version                Remote Package Version Available.
	 * @param   object $package                Package Details.
	 * @param   array  $features               Package Features.
	 * @param   array  $features_parameters    Package Features Parameters.
	 */
        private function cache_set( $valid = false, $message = '', $version = '', $package = '', $features = array(), $features_parameters = array() ) {

		update_option(
			$this->plugin->name . '_lum',
			array(
				'valid'               => $valid,
				'message'             => $message,
				'version'             => $version,
				'package'             => $package,
				'features'            => $features,
				'features_parameters' => $features_parameters,
				'expires'             => time() + DAY_IN_SECONDS,
			)
		);

		// Clear options cache, so that persistent caching solutions
		// have to fetch the latest options data from the DB.
		wp_cache_delete( 'alloptions', 'options' );
		wp_cache_delete( $this->plugin->name . '_lum', 'options' );

	}

	/**
	 * Deletes the cached data in the WordPress option table
	 *
	 * @since   1.0.0
	 */
	public function cache_delete() {

		delete_option( $this->plugin->name . '_lum' );

		// Clear options cache, so that persistent caching solutions
		// have to fetch the latest options data from the DB.
		wp_cache_delete( 'alloptions', 'options' );
		wp_cache_delete( $this->plugin->name . '_lum', 'options' );

	}
}
