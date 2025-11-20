<?php
/**
 * Integration Class
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Integration class.  Used by integration classes for common functions,
 * such as checking if a Plugin is active, fetching the Plugin version
 * and whether a Plugin version meets the minimum required.
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 3.0.5
 */
class Page_Generator_Pro_Integration {

	/**
	 * Holds the Plugin Folder and Filename
	 *
	 * @since   3.0.5
	 *
	 * @var     bool|string|array
	 */
	public $plugin_folder_filename = false;

	/**
	 * Holds the minimum supported version
	 *
	 * @since   3.0.5
	 *
	 * @var     bool|string
	 */
	public $minimum_supported_version = false;

	/**
	 * Holds the Theme Name
	 *
	 * @since   3.3.7
	 *
	 * @var     bool|string
	 */
	public $theme_name = false;

	/**
	 * Holds the Theme minimum supported version
	 *
	 * @since   3.3.7
	 *
	 * @var     bool|string
	 */
	public $theme_minimum_supported_version = false;

	/**
	 * Holds meta keys used by this integration
	 *
	 * @since   3.3.7
	 *
	 * @var     bool|array
	 */
	public $meta_keys = false;

	/**
	 * Holds the Content Group's overwrite section's setting name for this integration,
	 * which holds a value denoting whether overwriting should take place for
	 * this integration's data when re-generating content.
	 *
	 * @since   3.6.3
	 *
	 * @var     string
	 */
	public $overwrite_section = '';

	/**
	 * Holds the generated Page's Custom Fields.
	 *
	 * @since   4.3.5
	 *
	 * @var     bool|array
	 */
	public $post_custom_fields = false;

	/**
	 * Checks if the Plugin is installed, and if it meets the minimum supported
	 * version, if specified.
	 *
	 * @since   3.2.9
	 *
	 * @return  bool    Plugin is Active
	 */
	public function is_installed() {

		// Bail if no Plugin Filename setup.
		if ( ! is_array( $this->plugin_folder_filename ) && ! $this->plugin_folder_filename ) {
			return false;
		}

		// Assume Plugin isn't installed.
		$is_installed = false;

		// If our Plugin Filename is array, iterate through it.
		if ( is_array( $this->plugin_folder_filename ) ) {
			foreach ( $this->plugin_folder_filename as $plugin_folder_filename ) {
				if ( file_exists( WP_PLUGIN_DIR . '/' . $plugin_folder_filename ) ) { // @phpstan-ignore-line
					$is_installed = true;
					break;
				}
			}
		} else {
			$is_installed = file_exists( WP_PLUGIN_DIR . '/' . $this->plugin_folder_filename ); // @phpstan-ignore-line
		}

		// Bail if Plugin isn't installed.
		if ( ! $is_installed ) {
			return false;
		}

		// If there's no minimum supported version, the Plugin is installed..
		if ( ! $this->minimum_supported_version ) {
			return true;
		}

		// If the Plugin doesn't match the minimum supported version, deem it as not installed.
		if ( $this->get_version() < $this->minimum_supported_version ) {
			return false;
		}

		// If here, the Plugin is installed and meets the minimum supported version.
		return true;

	}

	/**
	 * Checks if the Plugin is active, and if it meets the minimum supported.
	 * version, if specified.
	 *
	 * @since   3.0.5
	 *
	 * @return  bool    Plugin is Active
	 */
	public function is_active() {

		// Bail if no Plugin Filename setup.
		if ( ! is_array( $this->plugin_folder_filename ) && ! $this->plugin_folder_filename ) {
			return false;
		}

		// Load is_plugin_active() function if not available i.e. this is a cron request.
		if ( ! function_exists( 'is_plugin_active' ) ) {
			include_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		// If we still can't use WordPress' function, assume Plugin isn't active.
		if ( ! function_exists( 'is_plugin_active' ) ) {
			return false;
		}

		// Assume Plugin isn't active.
		$is_active = false;

		// If our Plugin Filename is array, iterate through it.
		if ( is_array( $this->plugin_folder_filename ) ) {
			foreach ( $this->plugin_folder_filename as $plugin_folder_filename ) {
				if ( is_plugin_active( $plugin_folder_filename ) ) {
					$is_active = true;
					break;
				}
			}
		} else {
			$is_active = is_plugin_active( $this->plugin_folder_filename );
		}

		// Bail if Plugin isn't active.
		if ( ! $is_active ) {
			return false;
		}

		// If there's no minimum supported version, the Plugin is active.
		if ( ! $this->minimum_supported_version ) {
			return true;
		}

		// If the Plugin doesn't match the minimum supported version, deem it as not active.
		if ( version_compare( $this->get_version(), $this->minimum_supported_version, '<' ) ) {
			return false;
		}

		// If here, the Plugin is active and meets the minimum supported version.
		return true;

	}

	/**
	 * Returns the Integration Plugin's version
	 *
	 * @since   3.0.5
	 *
	 * @param   string $version   Version.
	 * @return  string  Version
	 */
	public function get_version( $version = '' ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter

		$plugin_data = get_file_data( WP_PLUGIN_DIR . '/' . $this->plugin_folder_filename, array( 'Version' => 'Version' ) ); // @phpstan-ignore-line
		return $plugin_data['Version'];

	}

	/**
	 * Checks if the Theme is active, and if it meets the minimum supported
	 * version, if specified.
	 *
	 * @since   3.3.7
	 *
	 * @return  bool    Plugin is Active
	 */
	public function is_theme_active() {

		// Assume Theme isn't active if we can't detect it.
		if ( ! function_exists( 'wp_get_theme' ) ) {
			return false;
		}

		// Check the Parent Theme if we're on a Child Theme.
		if ( wp_get_theme()->parent() ) {
			$theme = wp_get_theme()->parent();
		} else {
			$theme = wp_get_theme();
		}

		// If the (Parent) Theme isn't the required Theme Name, bail.
		if ( $theme->get( 'Name' ) !== $this->theme_name ) {
			return false;
		}

		// If there's no minimum supported version, the Theme is active.
		if ( ! $this->theme_minimum_supported_version ) {
			return true;
		}

		// If the Theme doesn't match the minimum supported version, deem it as not active.
		if ( $theme->get( 'Version' ) < $this->theme_minimum_supported_version ) {
			return false;
		}

		// If here, the Theme is active and meets the minimum supported version.
		return true;

	}

	/**
	 * Defines Meta Keys that relate to the Post Content, which will be removed from the Content Group when Content should not be
	 * overwritten on Content Generation.
	 *
	 * @since   3.3.8
	 *
	 * @param   array $ignored_keys   Ignored Keys (preg_match() compatible regex expressions are supported).
	 * @param   array $settings       Group Settings.
	 * @return  array   $ignored_keys   Ignored Keys
	 */
	public function remove_post_meta_from_content_group( $ignored_keys, $settings ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter

		// Add Meta Keys so they are not overwritten on the Generated Post.
		return array_merge( $ignored_keys, $this->meta_keys );

	}

	/**
	 * Adds the integration's meta keys to the array of excluded Post Meta Keys if the integration's
	 * metadata should not be overwritten on regeneration of content.
	 *
	 * @since   2.9.0
	 *
	 * @param   array $ignored_keys   Ignored Keys (preg_match() compatible regex expressions are supported).
	 * @param   int   $post_id        Generated Post ID.
	 * @param   array $settings       Group Settings.
	 * @param   array $post_args      wp_insert_post() / wp_update_post() arguments.
	 * @return  array   $ignored_keys   Ignored Keys
	 */
	public function prevent_post_meta_copy_to_generated_content( $ignored_keys, $post_id, $settings, $post_args ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter

		// Determine if we want to create/replace this integration's metdata.
		$overwrite = ( isset( $post_args['ID'] ) && ! array_key_exists( $this->overwrite_section, $settings['overwrite_sections'] ) ? false : true );

		// If overwriting is enabled, no need to exclude anything.
		if ( $overwrite ) {
			return $ignored_keys;
		}

		// If no meta keys are set by this integration, no need to exclude anything.
		if ( ! is_array( $this->meta_keys ) ) {
			return $ignored_keys;
		}

		// Add Meta Keys so they are not overwritten on the Generated Post.
		return array_merge( $ignored_keys, $this->meta_keys );

	}

	/**
	 * Removes orphaned Plugin metadata in the Group Settings if the Plugin is not active
	 *
	 * @since   3.3.7
	 *
	 * @param   array $settings               Group Settings.
	 * @param   array $meta_keys_to_remove    Meta Keys to remove from $settings['post_meta'] array of settings.
	 * @return  array                           Group Settings
	 */
	public function remove_orphaned_settings_metadata( $settings, $meta_keys_to_remove ) {

		// Bail if no Post Meta exists.
		if ( ! isset( $settings['post_meta'] ) ) {
			return $settings;
		}

		// Remove Plugin metadata, as it's not needed.
		foreach ( $settings['post_meta'] as $meta_key => $meta_value ) {

			// Remove meta keys by exact match.
			if ( in_array( $meta_key, $meta_keys_to_remove, true ) ) {
				unset( $settings['post_meta'][ $meta_key ] );
				continue;
			}

			// Iterate through the meta keys using preg_match(), so we can support
			// regular expressions.
			foreach ( $meta_keys_to_remove as $meta_key_to_remove ) {
				// Don't evaluate if not a regular expression.
				if ( strpos( $meta_key_to_remove, '/' ) === false ) {
					continue;
				}

				// Remove meta key if preg_match evaluates.
				if ( preg_match( $meta_key_to_remove, $meta_key ) ) {
					unset( $settings['post_meta'][ $meta_key ] );
					continue 2;
				}
			}
		}

		// Return.
		return $settings;

	}

	/**
	 * Replaces any Keywords detected in the given content string or array with
	 * the given Post ID's custom field values.
	 *
	 * Used by Page Builders to replace Keywords in Global Widgets / Modules
	 * where the generated Pages have 'Store Keywords' enabled.
	 *
	 * @since   4.3.5
	 *
	 * @param   string|array $content    Content (global widget, global module or entire Post Content).
	 * @param   int          $post_id    Generated Page / Post ID.
	 * @return  string|array             Content
	 */
	public function replace_keywords_with_custom_field_values( $content, $post_id ) {

		// Keyword searching and replacing requires content be an array.
		$content_array = ( ! is_array( $content ) ? array( $content ) : $content );

		// Check we're viewing a singular Post Type on the frontend.
		if ( $this->base->is_admin_or_frontend_editor() ) {
			return $content;
		}

		if ( ! is_singular() ) {
			return $content;
		}

		// Fetch Custom Fields, unless we already fetched them.
		if ( ! $this->post_custom_fields ) {
			$this->post_custom_fields = get_post_custom( $post_id );
		}

		// Search for Keywords that might exist in global modules.
		$post_detected_keywords = $this->base->get_class( 'generate' )->find_keywords_in_settings( $content_array );

		// Bail if no Keywords found.
		if ( count( $post_detected_keywords['required_keywords'] ) === 0 ) {
			return $content;
		}

		// Build a keywords array comprising of terms, columns and delimiters for each of the required keywords.
		$this->base->get_class( 'generate' )->keywords = $this->base->get_class( 'generate' )->get_keywords_terms_columns_delimiters( $post_detected_keywords['required_keywords'] );

		// Build array of keyword --> term key/value pairs to use for this generated Page.
		$keywords_terms = array();
		foreach ( $post_detected_keywords['required_keywords'] as $keyword ) {
			// If the Keyword's Term does not exist in the Custom Fields, it's likely that
			// a new Keyword was added, and the user didn't regenerate all Pages to store the new Keyword(s).
			if ( ! array_key_exists( $keyword, $this->post_custom_fields ) ) {
				$keywords_terms[ $keyword ] = '';
				continue;
			}

			$keywords_terms[ $keyword ] = $this->post_custom_fields[ $keyword ][0];
		}

		// Iterate through each detected Keyword to build a full $this->searches and $this->replacements arrays.
		$this->base->get_class( 'generate' )->build_search_replace_arrays( $post_detected_keywords['required_keywords_full'], $keywords_terms );

		// Iterate through each keyword and term key/value pair.
		$result = $this->base->get_class( 'generate' )->replace_keywords( $content_array );

		// If the original content wasn't an array, return the first array value.
		if ( ! is_array( $content ) ) {
			return $result[0];
		}

		return $result;

	}

}
