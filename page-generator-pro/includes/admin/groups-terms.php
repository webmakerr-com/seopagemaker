<?php
/**
 * Term Groups Class
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Handles creating, editing, deleting and calling the generate routine
 * for the Generate Terms section of the Plugin.
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 1.6.1
 */
class Page_Generator_Pro_Groups_Terms {

	/**
	 * Holds the base class object.
	 *
	 * @since   1.6.1
	 *
	 * @var     object
	 */
	public $base;

	/**
	 * Stores the current Group the settings are defined for.
	 *
	 * @since   1.9.9
	 *
	 * @var     int
	 */
	public $group_id = 0;

	/**
	 * Stores a Group's settings
	 *
	 * @since   1.6.1
	 *
	 * @var     array
	 */
	public $settings = array();

	/**
	 * Constructor.
	 *
	 * @since   1.6.1
	 *
	 * @param   object $base    Base Plugin Class.
	 */
	public function __construct( $base ) {

		// Store base class.
		$this->base = $base;

		// Output the Permalink from the Term Settings, not the Term itself.
		add_filter( 'editable_slug', array( $this, 'get_permalink_from_settings' ), 10, 2 );

	}

	/**
	 * When outputting the Slug on the Edit Term form, use the Slug from the settings,
	 * and not the Term itself.
	 *
	 * The slug from the Term itself is sanitized, with non alpha-numeric characters removed,
	 * including keywords.  This means the user does not see the 'true' value that we use
	 * when defining the Term's permalink.
	 *
	 * @since   1.6.1
	 *
	 * @param   string          $slug   Slug.
	 * @param   bool|   WP_Term $term   Term.
	 * @return  string                  Slug
	 */
	public function get_permalink_from_settings( $slug, $term = false ) {

		// Return slug if no Term provided.
		// Provides compat. with bbPress that calls the editable_slug filter with one argument.
		if ( ! $term ) {
			return $slug;
		}

		// Don't do anything if the Term's taxonomy is not our Plugin's.
		if ( $term->taxonomy !== $this->base->get_class( 'taxonomy' )->taxonomy_name ) {
			return $slug;
		}

		// Get Settings.
		$settings = $this->get_settings( $term->term_id, false );

		// Bail if an error occured.
		if ( is_wp_error( $settings ) ) {
			return $slug;
		}

		// Return Settings Permalink.
		return $settings['permalink'];

	}

	/**
	 * Defines a default settings structure when creating a new group
	 *
	 * @since   1.6.1
	 *
	 * @return  array   Group
	 */
	public function get_defaults() {

		// Define defaults.
		$defaults = array(
			'group_type'    => 'term',
			'title'         => '',
			'permalink'     => '',
			'excerpt'       => '',
			'parent_term'   => '',
			'taxonomy'      => '',
			'method'        => 'all',
			'overwrite'     => 0,
			'numberOfPosts' => 0,
			'resumeIndex'   => 0,
		);

		/**
		 * Defines the default settings structure when a new Term Group is created.
		 *
		 * @since   1.6.1
		 *
		 * @param   array   $defaults   Default Settings.
		 */
		$defaults = apply_filters( 'page_generator_pro_groups_terms_get_defaults', $defaults );

		// Return.
		return $defaults;

	}

	/**
	 * Returns an array of all Groups with their Settings
	 *
	 * @since   1.9.7
	 *
	 * @return  bool|array   Groups
	 */
	public function get_all() {

		// Groups.
		$groups = new WP_Term_Query(
			array(
				'taxonomy'   => $this->base->get_class( 'taxonomy' )->taxonomy_name,
				'hide_empty' => false,
				'number'     => 0,
				'fields'     => 'ids',
			)
		);

		// Bail if no Term Groups exist.
		if ( is_null( $groups->terms ) ) { // @phpstan-ignore-line
			return false;
		}

		// Build array.
		$groups_arr = array();
		foreach ( $groups->terms as $group_id ) {
			// Get settings.
			$settings = $this->get_settings( $group_id );

			// Skip if an error occured.
			if ( is_wp_error( $settings ) ) {
				continue;
			}

			// Add to array.
			$groups_arr[ $group_id ] = $settings;
		}

		/**
		 * Filters the Groups to return.
		 *
		 * @since   1.9.7
		 *
		 * @param   array           $groups_arr Groups.
		 * @param   WP_Term_Query   $groups     Groups Query.
		 */
		$groups_arr = apply_filters( 'page_generator_pro_groups_terms_get_all', $groups_arr, $groups );

		// Return filtered results.
		return $groups_arr;

	}

	/**
	 * Returns an array of all Group IDs with their names
	 *
	 * @since   1.2.3
	 *
	 * @return  bool|array   Groups
	 */
	public function get_all_ids_names() {

		// Groups.
		$groups = new WP_Term_Query(
			array(
				'taxonomy'   => $this->base->get_class( 'taxonomy' )->taxonomy_name,
				'hide_empty' => false,
				'number'     => 0,
			)
		);

		if ( is_null( $groups->terms ) ) { // @phpstan-ignore-line
			return false;
		}

		// Build array.
		$groups_arr = array();
		foreach ( $groups->terms as $term ) {
			$groups_arr[ $term->term_id ] = $term->name;
		}

		/**
		 * Filters the Groups to return.
		 *
		 * @since   1.9.7
		 *
		 * @param   array           $groups_arr Groups.
		 * @param   WP_Term_Query   $groups     Groups Query.
		 */
		$groups_arr = apply_filters( 'page_generator_pro_groups_terms_get_all_ids_names', $groups_arr, $groups );

		// Return filtered results.
		return $groups_arr;

	}

	/**
	 * Returns a Group's Settings by the given Group ID
	 *
	 * @since   1.6.1
	 *
	 * @param   int  $id             ID.
	 * @param   bool $include_stats  Include Generated Count and Last Index Generated.
	 * @return  WP_Error|bool|array
	 */
	public function get_settings( $id, $include_stats = true ) {

		// Bail if the ID isn't for a Term Group.
		$term = get_term( $id );
		if ( is_null( $term ) || $term->taxonomy !== 'page-generator-tax' ) {
			return new WP_Error(
				'page_generator_pro_groups_terms_get_settings_error',
				sprintf(
					/* translators: Group ID */
					esc_html__( 'ID %s is not a Term Group.  Did you enter the correct Term Group ID?', 'page-generator-pro' ),
					$id
				)
			);
		}

		// Get settings.
		$settings = get_term_meta( $id, '_page_generator_pro_settings', true );

		// If the result isn't an array, we're getting settings for a new Group, so just use the defaults.
		if ( ! is_array( $settings ) ) {
			$settings = $this->get_defaults();
		} else {
			// Merge with defaults, so keys are always set.
			$settings = array_merge( $this->get_defaults(), $settings );
		}

		// Fetch all Metadata stored against the Group ID, and add that to the settings array.
		$settings['term_meta'] = $this->get_term_meta( $id );

		// Add the generated terms count and last index that was generated.
		if ( $include_stats ) {
			$settings['generated_pages_count'] = $this->get_generated_count_by_id( $id, $settings['taxonomy'] );
			$settings['last_index_generated']  = $this->get_last_index_generated( $id );
		}

		// Return settings.
		return $settings;

	}

	/**
	 * Returns all Term Metadata for the given Group ID, excluding some specific keys.
	 *
	 * This ensures that SEO data, ACF data etc. is included in the Group
	 * settings and subsequently copied to the generated Term.
	 *
	 * @since   1.9.9
	 *
	 * @param   int $id     Group ID.
	 * @return  bool|array          Metadata
	 */
	private function get_term_meta( $id ) {

		// Fetch all metadata.
		$meta = get_term_meta( $id );

		// Bail if no metadata was returned.
		if ( empty( $meta ) ) {
			return false;
		}

		// Define the metadata to ignore.
		$ignored_keys = array(
			'_page_generator_pro_last_index_generated',
			'_page_generator_pro_settings',
			'_page_generator_pro_status',
			'_page_generator_pro_system',
			'_yoast_wpseo_content_score',
		);

		/**
		 * Defines Term Meta Keys in a Content Group to ignore and not copy to generated Terms.
		 *
		 * @since   1.9.9
		 *
		 * @param   array   $ignored_keys   Ignored Keys.
		 * @param   int     $id             Group ID.
		 */
		$ignored_keys = apply_filters( 'page_generator_pro_groups_terms_get_term_meta_ignored_keys', $ignored_keys, $id );

		// Iterate through the metadata, removing items we don't want.
		foreach ( $meta as $meta_key => $meta_value ) {
			// Remove ignored keys.
			if ( in_array( $meta_key, $ignored_keys, true ) ) {
				unset( $meta[ $meta_key ] );
				continue;
			}

			// Fetch the single value.
			$meta[ $meta_key ] = get_term_meta( $id, $meta_key, true );
		}

		/**
		 * Filters the Group Metadata to return.
		 *
		 * @since   1.9.9
		 *
		 * @param   array   $meta   Metadata.
		 * @param   int     $id     Group ID.
		 */
		$meta = apply_filters( 'page_generator_pro_groups_terms_get_term_meta', $meta, $id );

		// Return filtered metadata.
		return $meta;

	}

	/**
	 * Get the number of Terms generated by the given Group ID
	 *
	 * @since   1.6.1
	 *
	 * @param   int    $id         Group ID.
	 * @param   string $taxonomy   Taxonomy containing generated Terms.
	 * @return  int                 Number of Generated Terms
	 */
	private function get_generated_count_by_id( $id, $taxonomy ) {

		$terms = new WP_Term_Query(
			array(
				'taxonomy'               => $taxonomy,
				'hide_empty'             => false,
				'meta_query'             => array(
					array(
						'key'   => '_page_generator_pro_group',
						'value' => absint( $id ),
					),
				),

				// For performance, just return the Post ID and don't update meta or term caches.
				'fields'                 => 'ids',
				'update_term_meta_cache' => false,
			)
		);

		if ( is_null( $terms->terms ) ) { // @phpstan-ignore-line
			return 0;
		}

		return count( $terms->terms );

	}

	/**
	 * Runs an action on a Group
	 *
	 * Called by both row actions and edit actions
	 *
	 * @since   1.9.5
	 *
	 * @param   string $action     Action.
	 * @param   int    $id         Group ID.
	 * @param   bool   $redirect   Redirct on success / error.
	 */
	public function run_action( $action, $id, $redirect = false ) {

		switch ( $action ) {

			/**
			 * Generate
			 */
			case 'generate':
				// Validate group before passing this request through.
				$result = $this->validate( $id );
				if ( $result ) {
					wp_safe_redirect( 'admin.php?page=' . $this->base->plugin->name . '-generate&id=' . $id . '&type=term' );
					die;
				}
				break;

			/**
			 * Generate via Server
			 */
			case 'generate_server':
				$result = $this->schedule_generation( $id );
				break;

			/**
			 * Duplicate
			 */
			case 'duplicate':
				$result = $this->duplicate( $id );
				break;

			/**
			 * Test
			 */
			case 'test':
				$result = $this->test( $id );
				break;

			/**
			 * Delete Generated Content
			 */
			case 'delete_generated_content':
				$result = $this->delete_generated_content( $id );
				break;

			/**
			 * Cancel Generation
			 */
			case 'cancel_generation':
				$result = $this->cancel_generation( $id );
				break;

			default:
				/**
				 * Run a custom row action on a Group.
				 *
				 * @since   1.9.5
				 *
				 * @param   WP_Error|bool|string    $result     Result.
				 * @param   string                  $action     Action.
				 * @param   int                     $id         Group ID.
				 */
				$result = false;
				$result = apply_filters( 'page_generator_pro_groups_terms_run_row_actions', $result, $action, $id );
				break;

		}

		// If there is no result from the action, nothing happened.
		if ( ! isset( $result ) || $result === false ) {
			return;
		}

		// Setup notices class, enabling persistent storage.
		$this->base->get_class( 'notices' )->enable_store();
		$this->base->get_class( 'notices' )->set_key_prefix( 'page_generator_pro_' . wp_get_current_user()->ID );

		// Depending on the result of the action, store a notification and redirect.
		if ( is_wp_error( $result ) ) {
			$this->base->get_class( 'notices' )->add_error_notice( $result->get_error_message() );

			if ( $redirect ) {
				// Redirect to the Generate Terms WP_List_Table.
				wp_safe_redirect( $this->base->get_class( 'groups_terms_table' )->get_action_url() );
				die;
			}

			// Don't do anything else.
			return;
		}

		// Build success notice.
		switch ( $action ) {

			/**
			 * Test
			 */
			case 'test':
				$message = sprintf(
					'%1$s <a href="%2$s" target="_blank">%3$s</a>',
					sprintf(
						/* translators: Number of seconds */
						__( 'Test Term Generated in %s seconds at ', 'page-generator-pro' ),
						$result['duration']
					),
					$result['url'],
					$result['url']
				);

				foreach ( $result['keywords_terms'] as $keyword => $term ) {
					$message .= '<br />{' . $keyword . '}: ' . ( strlen( $term ) > 50 ? substr( $term, 0, 50 ) . '...' : $term );
				}
				break;

			default:
				// Get message.
				$message = $this->base->get_class( 'groups_terms_ui' )->get_message( $action . '_success' );

				/**
				 * Define an optional success message based on the result of a custom row action on a Group.
				 *
				 * @since   1.9.5
				 *
				 * @param   bool|string             $message    Success Message.
				 * @param   WP_Error|bool|string    $result     Result.
				 * @param   string                  $action     Action.
				 * @param   int                     $id         Group ID.
				 */
				$message = apply_filters( 'page_generator_pro_groups_terms_run_row_actions_success_message', $message, $result, $action, $id );
				break;

		}

		// Store success notice.
		if ( $message !== false ) {
			$this->base->get_class( 'notices' )->add_success_notice( $message );
		}

		// Redirect to the Generate Terms WP_List_Table.
		if ( $redirect ) {
			wp_safe_redirect( $this->base->get_class( 'groups_terms_table' )->get_action_url() );
			die();
		}

	}

	/**
	 * Adds or edits a record, based on the given settings array.
	 *
	 * @since   1.6.1
	 *
	 * @param   array $settings    Array of settings to save.
	 * @param   int   $group_id    Group ID.
	 */
	public function save( $settings, $group_id ) {

		// Merge with defaults, so keys are always set.
		$settings = array_merge( $this->get_defaults(), $settings );

		// Ensure some keys have a value, in case the user blanked out the values.
		// This prevents errors later on when trying to generate content from a Group.
		if ( empty( $settings['resumeIndex'] ) ) {
			$settings['resumeIndex'] = 0;
		}

		// Sanitize the Permalink setting.
		if ( ! empty( $settings['permalink'] ) ) {
			$settings['permalink'] = preg_replace( '/[^a-z0-9-_{}\(\):]+/i', '', str_replace( ' ', '-', $settings['permalink'] ) );
		}

		// Trim top level settings.
		foreach ( $settings as $key => $value ) {
			if ( is_array( $value ) ) {
				continue;
			}

			$settings[ $key ] = trim( $value );
		}

		// Update Term Meta.
		update_term_meta( $group_id, '_page_generator_pro_settings', $settings );

		// Validate the Group, adding error notices as necessary.
		$validated = $this->validate( $group_id );
		if ( is_wp_error( $validated ) ) {
			$this->base->get_class( 'notices' )->enable_store();
			$this->base->get_class( 'notices' )->set_key_prefix( 'page_generator_pro_' . wp_get_current_user()->ID );
			$this->base->get_class( 'notices' )->add_error_notice( $validated->get_error_message() );
		}

	}

	/**
	 * Performs several validations on the given Group Settings, to ensure that
	 * content generation will function successfully.
	 *
	 * @since   2.0.1
	 *
	 * @param   int $id     Group ID.
	 * @return  WP_Error|bool
	 */
	public function validate( $id ) {

		// Fetch group settings.
		$settings = $this->get_settings( $id, false );

		// Bail if an error occured.
		if ( is_wp_error( $settings ) ) {
			return $settings;
		}

		$result = true;

		/**
		 * Performs several validations on the given Group Settings, to ensure that
		 * content generation will function successfully.
		 *
		 * @since   2.0.1
		 *
		 * @param   WP_Error|bool   $result     Validation Result.
		 * @param   array           $settings   Group Settings.
		 * @param   int             $id         Group ID.
		 */
		$result = apply_filters( 'page_generator_pro_groups_terms_validate', $result, $settings, $id );

		// Return result.
		return $result;

	}

	/**
	 * Fetches the last index generated for the given Group.
	 *
	 * @since   2.2.6
	 *
	 * @param   int $id     Group ID.
	 */
	public function get_last_index_generated( $id ) {

		return absint( get_term_meta( $id, '_page_generator_pro_last_index_generated', true ) );

	}

	/**
	 * Stores the given index as the last generated index for the given
	 * Group.
	 *
	 * @since   2.2.6
	 *
	 * @param   int $id     Group ID.
	 * @param   int $index  Last Index Generated.
	 */
	public function update_last_index_generated( $id, $index ) {

		update_term_meta( $id, '_page_generator_pro_last_index_generated', $index );

	}

	/**
	 * Schedules content generation via WordPress' CRON
	 *
	 * @since   1.9.9
	 *
	 * @param   int $id     Group ID.
	 * @return  mixed           WP_Error | true
	 */
	public function schedule_generation( $id ) {

		// Bail if WordPress' Cron is disabled.
		if ( defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON ) { // @phpstan-ignore-line
			return new WP_Error(
				'page_generator_pro_groups_schedule_generation',
				sprintf(
					/* translators: %1$s: Generate via Server Documentation Link, %2$s: Generate via WP-CLI Documentation Link */
					__( 'Generate via Server failed, because WordPress\' Cron is disabled due to DISABLE_WP_CRON enabled in your wp-config.php file. %1$s, or use %2$s.', 'page-generator-pro' ),
					'<a href="' . $this->base->plugin->documentation_url . '/generate-server/" rel="noopener" target="_blank">' . __( 'Remove this option in your wp-config.php file', 'page-generator-pro' ) . '</a>',
					'<a href="' . $this->base->plugin->documentation_url . '/generate-wp-cli/#generate-content" rel="noopener" target="_blank">' . __( 'WP-CLI', 'page-generator-pro' ) . '</a>'
				)
			);
		}

		// Bail if the group is already scheduled.
		if ( $this->is_scheduled( $id ) ) {
			return new WP_Error(
				'page_generator_pro_groups_terms_schedule_generation',
				sprintf(
					/* translators: Group ID */
					__( 'Group #%s: is already scheduled to generate content!', 'page-generator-pro' ),
					$id
				)
			);
		}

		// Bail if the group is already generating content.
		if ( $this->is_generating( $id ) ) {
			return new WP_Error(
				'page_generator_pro_groups_terms_schedule_generation',
				sprintf(
					/* translators: Group ID */
					__( 'Group #%s: is already generating content!', 'page-generator-pro' ),
					$id
				)
			);
		}

		// If here, we're OK to schedule.
		wp_schedule_single_event(
			time() + 10,
			'page_generator_pro_generate_cron',
			array(
				$id,
				'term',
			)
		);

		// Mark group as scheduled.
		$this->start_generation( $id, 'scheduled', 'cron' );

		// Done.
		return true;

	}

	/**
	 * Duplicates a group
	 *
	 * @since   1.6.1
	 *
	 * @param   int $id     ID.
	 * @return  WP_Error|bool
	 */
	public function duplicate( $id ) {

		// Fetch Term.
		$term = get_term( $id, $this->base->get_class( 'taxonomy' )->taxonomy_name );

		// Bail if Term could not be found.
		if ( ! $term ) {
			return new WP_Error(
				sprintf(
					/* translators: Group ID */
					__( 'Group ID %s does not exist!', 'page-generator-pro' ),
					$id
				)
			);
		}
		if ( is_wp_error( $term ) ) {
			return $term;
		}

		// Fetch group settings.
		$settings = $this->get_settings( $id, false );
		if ( is_wp_error( $settings ) ) {
			return $settings;
		}

		// Validate group.
		$validated = $this->validate( $id );
		if ( is_wp_error( $validated ) ) {
			return $validated;
		}

		// Create new Term.
		$duplicate_term_id = wp_insert_term(
			$settings['title'] . __( ' - Copy', 'page-generator-pro' ),
			$term->taxonomy,
			array(
				'description' => $settings['excerpt'],
				'parent'      => ( isset( $settings['parent'] ) ? $settings['parent'] : 0 ),
			)
		);

		// Bail if an error occured.
		if ( is_wp_error( $duplicate_term_id ) ) {
			return $duplicate_term_id;
		}

		// Update Group Settings.
		$this->save( $settings, $duplicate_term_id['term_id'] );

		// Done.
		return true;

	}

	/**
	 * Tests content for the given Group ID
	 *
	 * @since   1.8.0
	 *
	 * @param   int $id     Group ID.
	 * @return  WP_Error|array
	 */
	public function test( $id ) {

		// Fetch group settings.
		$settings = $this->get_settings( $id, false );
		if ( is_wp_error( $settings ) ) {
			return $settings;
		}

		// Validate group.
		$validated = $this->validate( $id );
		if ( is_wp_error( $validated ) ) {
			return $validated;
		}

		// Run test.
		$result = $this->base->get_class( 'generate' )->generate_term( $id, $settings['resumeIndex'], true );
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		/**
		 * Runs any actions once Generate Content has finished.
		 *
		 * @since   1.9.3
		 *
		 * @param   int     $group_id   Group ID.
		 * @param   bool    $test_mode  Test Mode
		 */
		do_action( 'page_generator_pro_generate_term_after', $id, true );

		// Return result.
		return $result;

	}

	/**
	 * Deletes Generated Content for the given Group ID
	 *
	 * @since   1.8.0
	 *
	 * @param   int $id     Group ID.
	 * @return  WP_Error|bool
	 */
	public function delete_generated_content( $id ) {

		// Delete Generated Content now.
		return $this->base->get_class( 'generate' )->delete_terms( $id );

	}

	/**
	 * Returns a flag denoting whether the given Group ID has generated content
	 *
	 * @since   1.9.9
	 *
	 * @param   int $id         Group ID.
	 * @return          bool        Has Generated Content
	 */
	public function has_generated_content( $id ) {

		// Get Group Settings to obtain the generated Taxonomy Type.
		$settings = $this->get_settings( $id );

		// Bail if an error occured.
		if ( is_wp_error( $settings ) ) {
			return false;
		}

		// If no Taxonomy, bail.
		if ( empty( $settings['taxonomy'] ) ) {
			return false;
		}

		// Return based on generated count.
		if ( $settings['get_generated_count_by_id'] > 0 ) {
			return true;
		}

		return false;

	}

	/**
	 * Returns a flag denoting whether the given Group ID is idle i.e. not generating
	 *
	 * @since   1.9.9
	 *
	 * @param   int $id     Group ID.
	 * @return  bool            Is Idle (not generating)
	 */
	public function is_idle( $id ) {

		$status = $this->get_status( $id );

		if ( $status === 'idle' || empty( $status ) ) {
			return true;
		}

		return false;

	}

	/**
	 * Returns a flag denoting whether the given Group ID is scheduled to generate
	 *
	 * @since   1.9.9
	 *
	 * @param   int $id     Group ID.
	 * @return  bool            Is Scheduled to Generate
	 */
	public function is_scheduled( $id ) {

		$status = $this->get_status( $id );

		if ( $status === 'scheduled' ) {
			return true;
		}

		return false;

	}

	/**
	 * Returns a flag denoting whether the given Group ID is generating
	 *
	 * @since   1.9.9
	 *
	 * @param   int $id     Group ID.
	 * @return  bool            Is Generating
	 */
	public function is_generating( $id ) {

		$status = $this->get_status( $id );

		if ( $status === 'generating' ) {
			return true;
		}

		return false;

	}

	/**
	 * Gets the status of the given Group ID (idle, scheduled, generating)
	 *
	 * @since   1.9.9
	 *
	 * @param   int $id     Group ID.
	 * @return  string          Status
	 */
	public function get_status( $id ) {

		return get_term_meta( $id, '_page_generator_pro_status', true );

	}

	/**
	 * Gets the given Group ID's system being used for generation
	 *
	 * @since   1.9.9
	 *
	 * @param   int $id     Group ID.
	 */
	public function get_system( $id ) {

		return get_term_meta( $id, '_page_generator_pro_system', true );

	}

	/**
	 * Starts generation for the given Group ID by:
	 * - Defining the status flag
	 * - Defining the system flag
	 * - Deleting the cancel flag
	 *
	 * @since   1.9.9
	 *
	 * @param   int    $id         Group ID.
	 * @param   string $status     Status.
	 * @param   string $system     Generation System.
	 * @return  bool
	 */
	public function start_generation( $id, $status, $system ) {

		update_term_meta( $id, '_page_generator_pro_status', $status );
		update_term_meta( $id, '_page_generator_pro_system', $system );
		delete_term_meta( $id, '_page_generator_pro_cancel' );

		return true;

	}

	/**
	 * Cancels generation for the given Group ID by:
	 * - Deleting the status flag
	 * - Deleting the system flag
	 * - Adding a cancel flag, so that if the generation process is running async, it'll stop
	 * on the next iteration.
	 *
	 * @since   1.9.9
	 *
	 * @param   int $id         Group ID.
	 * @return  bool
	 */
	public function cancel_generation( $id ) {

		// Get system used.
		$system = $this->get_system( $id );

		// If we're using WordPress CRON, clear the scheduled hook.
		if ( $system === 'cron' ) {
			wp_clear_scheduled_hook(
				'page_generator_pro_generate_cron',
				array(
					$id,
					'term',
				)
			);
		}

		delete_term_meta( $id, '_page_generator_pro_status' );
		delete_term_meta( $id, '_page_generator_pro_system' );
		update_term_meta( $id, '_page_generator_pro_cancel', 1 );

		return true;

	}

	/**
	 * Returns a flag denoting whether the given Group ID has a request to cancel generation.
	 *
	 * @since   1.9.9
	 *
	 * @param   int $id     Group ID.
	 * @return  bool        Stop Generation
	 */
	public function cancel_generation_requested( $id ) {

		global $wpdb;

		// Read value directly from the DB, so that a cached meta value is not returned
		// This ensures that CRON and CLI will perform a fresh read for each generated
		// item to ensure generation is cancelled if the flag has been set through the browser
		// through the cancel command.
		$result = $wpdb->get_var(
			' SELECT meta_value FROM ' . $wpdb->termmeta . '
                                    WHERE term_id = ' . absint( $id ) . "
                                    AND meta_key = '_page_generator_pro_cancel'
                                    LIMIT 1"
		);

		return (bool) $result;

	}

	/**
	 * Stops generation for the given Group ID by:
	 * - Deleting the status flag
	 * - Deleting the system flag
	 * - Deleting the cancellation flag
	 *
	 * @since   1.9.9
	 *
	 * @param   int $id     Group ID.
	 * @return  bool
	 */
	public function stop_generation( $id ) {

		// Get system used.
		$system = $this->get_system( $id );

		// If we're using WordPress CRON, clear the scheduled hook.
		if ( $system === 'cron' ) {
			wp_clear_scheduled_hook(
				'page_generator_pro_generate_cron',
				array(
					$id,
				)
			);
		}

		delete_term_meta( $id, '_page_generator_pro_status' );
		delete_term_meta( $id, '_page_generator_pro_system' );
		delete_term_meta( $id, '_page_generator_pro_cancel' );

		return true;

	}

}
