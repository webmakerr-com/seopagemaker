<?php
/**
 * Content Groups Class
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Handles creating, editing, deleting and calling the generate routine
 * for the Generate Content section of the Plugin.
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 2.0.2
 */
class Page_Generator_Pro_Groups {

	/**
	 * Holds the base class object.
	 *
	 * @since   2.0.2
	 *
	 * @var     object
	 */
	public $base;

	/**
	 * Stores the current Group the settings are defined for.
	 *
	 * @since   2.0.2
	 *
	 * @var     int
	 */
	public $group_id = 0;

	/**
	 * Stores a Group's settings
	 *
	 * @since   2.0.2
	 *
	 * @var     array
	 */
	public $settings = array();

	/**
	 * Holds query results from calling get_all_ids_names(),
	 * for performance
	 *
	 * @since   3.0.7
	 *
	 * @var     mixed
	 */
	private $ids_names = false;

	/**
	 * Constructor.
	 *
	 * @since   1.2.3
	 *
	 * @param   object $base    Base Plugin Class.
	 */
	public function __construct( $base ) {

		// Store base class.
		$this->base = $base;

	}

	/**
	 * Creates a single Group, if none exist, when the Plugin is activated.
	 *
	 * @since   1.3.8
	 *
	 * @global  $wpdb   WordPress DB Object.
	 */
	public function activate() {

		// Bail if we already have at least one Group.
		$number_of_groups = $this->get_count();
		if ( $number_of_groups > 0 ) {
			return;
		}

		// Create Group.
		wp_insert_post(
			array(
				'post_type'    => $this->base->get_class( 'post_type' )->post_type_name,
				'post_status'  => 'publish',
				'post_title'   => __( 'Title', 'page-generator-pro' ),
				'post_content' => __( 'Edit this content, replacing it with the content you want to generate. You can use {keywords} here too.  Need help? Visit <a href="https://www.wpzinc.com/documentation/page-generator-pro/generate-content/" rel="nofollow noreferrer noopener" target="_blank">https://www.wpzinc.com/documentation/page-generator-pro/generate-content/</a>', 'page-generator-pro' ),
			)
		);

	}

	/**
	 * Defines a default settings structure when creating a new group
	 *
	 * @since   1.2.0
	 *
	 * @return  array   Group
	 */
	public function get_defaults() {

		// Get Defaults.
		$defaults = array(
			'group_type'                 => 'content',
			'title'                      => '',
			'description'                => '',
			'permalink'                  => '',
			'content'                    => '',
			'excerpt'                    => '',
			'latitude'                   => '',
			'longitude'                  => '',
			'store_keywords'             => 1,
			'meta'                       => array(),
			'rotateAuthors'              => 0,
			'author'                     => ( function_exists( 'get_current_user_id' ) ? get_current_user_id() : 0 ),

			'comments'                   => 0,
			'comments_generate'          => array(
				'enabled'       => 0,
				'limit'         => 0,
				'date_option'   => 'now',
				'date_specific' => gmdate( 'Y-m-d' ),
				'date_min'      => gmdate( 'Y-m-d', strtotime( '-1 week' ) ),
				'date_max'      => gmdate( 'Y-m-d' ),
				'firstname'     => '',
				'surname'       => '',
				'comment'       => '',
			),

			'header_code'                => '',
			'footer_code'                => '',

			'trackbacks'                 => 0,
			'type'                       => 'page',
			'status'                     => 'publish',
			'date_option'                => 'now',
			'date_specific'              => gmdate( 'Y-m-d' ),
			'date_specific_keyword'      => '',
			'date_min'                   => gmdate( 'Y-m-d', strtotime( '-1 week' ) ),
			'date_max'                   => gmdate( 'Y-m-d' ),
			'schedule'                   => 1,
			'scheduleUnit'               => 'hours',
			'method'                     => 'all',
			'overwrite'                  => 'overwrite',
			'overwrite_sections'         => array(
				'post_title'         => 1,
				'post_content'       => 1,
				'post_excerpt'       => 1,
				'post_status'        => 1,
				'post_author'        => 1,
				'post_date'          => 1,
				'comment_status'     => 1,
				'comments'           => 1,
				'ping_status'        => 1,
				'custom_fields'      => 1,
				'featured_image'     => 1,
				'attributes'         => 1,
				'taxonomies'         => 1,
				'menu'               => 1,
				'header_footer_code' => 1,
			),
			'numberOfPosts'              => 0,
			'resumeIndex'                => 0,
			'auto_regeneration'          => 0,
			'auto_regeneration_interval' => 30,
			'pageParent'                 => '',
			'pageTemplate'               => '',
			'tax'                        => '',
			'menu'                       => 0,
			'menu_title'                 => '',
			'menu_parent'                => '',
			'featured_image_source'      => '',
		);

		/**
		 * Defines the default settings structure when a new Content Group is created.
		 *
		 * @since   1.2.0
		 *
		 * @param   array   $defaults   Default Settings.
		 */
		$defaults = apply_filters( 'page_generator_pro_groups_get_defaults', $defaults );

		// Return.
		return $defaults;

	}

	/**
	 * Returns a Group's Settings by the given Group ID
	 *
	 * @since   1.2.1
	 *
	 * @param   int  $id                         ID.
	 * @param   bool $include_stats              Include Generated Count and Last Index Generated.
	 * @param   bool $remove_orphaned_metadata   If enabled, remove any orphaned data that might remain due to changing Page Builder, SEO or Schema Plugin.
	 * @return  WP_Error|bool|array
	 */
	public function get_settings( $id, $include_stats = true, $remove_orphaned_metadata = false ) {

		// Bail if the ID isn't for a Content Group.
		if ( get_post_type( $id ) !== 'page-generator-pro' ) {
			return new WP_Error(
				'page_generator_pro_groups_get_settings_error',
				sprintf(
					/* translators: Group ID */
					esc_html__( 'ID %s is not a Content Group.  Did you enter the correct Content Group ID?', 'page-generator-pro' ),
					$id
				)
			);
		}

		// Get settings.
		$settings = get_post_meta( $id, '_page_generator_pro_settings', true );

		// If the result isn't an array, we're getting settings for a new Group, so just use the defaults.
		if ( ! is_array( $settings ) ) {
			$settings = $this->get_defaults();
		} else {
			// Store the Post's Title and Content in the settings, for backward compat.
			$post                = get_post( $id );
			$settings['title']   = $post->post_title;
			$settings['content'] = $post->post_content;

			// Merge with defaults, so keys are always set.
			$settings = array_merge( $this->get_defaults(), $settings );

			// For PHP 8 compat, ensure some fields have numerical values.
			if ( empty( $settings['numberOfPosts'] ) ) {
				$settings['numberOfPosts'] = 0;
			}
			if ( empty( $settings['resumeIndex'] ) ) {
				$settings['resumeIndex'] = 0;
			}
		}

		// Fetch all Metadata stored against the Group ID, and add that to the settings array.
		$settings['post_meta'] = $this->get_post_meta( $id );

		// Format date_specific to include time if necessary, so it works with 3.1.6+'s datetime-local input.
		if ( ! empty( $settings['date_specific'] ) ) {
			if ( strpos( $settings['date_specific'], ':' ) === false ) {
				$settings['date_specific'] .= 'T00:00:00';
			} else {
				$settings['date_specific'] = str_replace( ' ', 'T', $settings['date_specific'] );
			}
		}

		// Add the generated pages count and last index that was generated.
		if ( $include_stats ) {
			$settings['generated_pages_count'] = $this->get_generated_count_by_id( $id );
			$settings['last_index_generated']  = $this->get_last_index_generated( $id );
		}

		// Remove any orphaned data, such as Page Builder, SEO or Schema metadata, from the Group before generation is run,
		// that might remain due to changing Page Builder, SEO or Schema Plugin.
		if ( $remove_orphaned_metadata ) {
			/**
			 * Remove any orphaned data, such as Page Builder, SEO or Schema metadata, from the Group before generation is run,
			 * that might remain due to changing Page Builder, SEO or Schema Plugin.
			 *
			 * @since   3.3.7
			 *
			 * @param   array   $group   Group Settings.
			 */
			$settings = apply_filters( 'page_generator_pro_groups_get_settings_remove_orphaned_settings', $settings );
		}

		// Return settings.
		return $settings;

	}

	/**
	 * Returns all Post Metadata for the given Group ID, excluding some specific keys.
	 *
	 * This ensures that Page Builder data, ACF data etc. is included in the Group
	 * settings and subsequently copied to the generated Page.
	 *
	 * @since   1.4.4
	 *
	 * @param   int $id     Group ID.
	 * @return  bool|array          Metadata
	 */
	private function get_post_meta( $id ) {

		// Fetch all metadata.
		$meta = get_post_meta( $id );

		// Bail if no metadata was returned.
		if ( empty( $meta ) ) {
			return false;
		}

		// Define the metadata to ignore.
		$ignored_keys = array(
			'_edit_lock',
			'_edit_last',
			'_page_generator_pro_last_index_generated',
			'_page_generator_pro_settings',
			'_page_generator_pro_status',
			'_page_generator_pro_system',
			'_yoast_wpseo_content_score',
		);

		/**
		 * Defines Post Meta Keys in a Content Group to ignore and not copy to generated Posts / Groups.
		 *
		 * @since   1.4.4
		 *
		 * @param   array   $ignored_keys   Ignored Keys.
		 * @param   int     $id             Group ID.
		 */
		$ignored_keys = apply_filters( 'page_generator_pro_groups_get_post_meta_ignored_keys', $ignored_keys, $id );

		$run_filters = true;

		/**
		 * Whether to run filters on the Group's metadata.
		 *
		 * @since   5.3.2
		 *
		 * @param   bool   $run_filters   Whether to run filters on the Group's metadata.
		 * @param   int    $id            Group ID.
		 * @return  bool
		 */
		$run_filters = apply_filters( 'page_generator_pro_groups_get_post_meta_run_filters', $run_filters, $id );

		// Iterate through the metadata, removing items we don't want.
		foreach ( $meta as $meta_key => $meta_value ) {
			// Remove ignored keys.
			if ( in_array( $meta_key, $ignored_keys, true ) ) {
				unset( $meta[ $meta_key ] );
				continue;
			}

			// Fetch the single value.
			$value = get_post_meta( $id, $meta_key, true );

			// If filters are not enabled, assign the value to the metadata array and continue to the next iteration of the loop.
			if ( ! $run_filters ) {
				$meta[ $meta_key ] = $value;
				continue;
			}

			/**
			 * Filters the Group Metadata for the given Key and Value
			 *
			 * @since   2.6.1
			 *
			 * @param   array|string|bool|int   $value  Meta Value.
			 * @return  array|string|bool|int           Meta Value
			 */
			$value = apply_filters( 'page_generator_pro_groups_get_post_meta_' . $meta_key, $value );

			// Assign value to the metadata array.
			$meta[ $meta_key ] = $value;
		}

		// If filters are not enabled, return the metadata array.
		if ( ! $run_filters ) {
			return $meta;
		}

		/**
		 * Filters the Group Metadata to return.
		 *
		 * @since   1.4.4
		 *
		 * @param   array   $meta   Metadata.
		 * @param   int     $id     Group ID
		 */
		$meta = apply_filters( 'page_generator_pro_groups_get_post_meta', $meta, $id );

		// Return filtered metadata.
		return $meta;

	}

	/**
	 * Returns an array of all Groups with their Settings
	 *
	 * @since   1.2.3
	 *
	 * @return  bool|array   Groups
	 */
	public function get_all() {

		// Fetch valid Post Statuses that can be used when generating content.
		$statuses = array_keys( $this->base->get_class( 'common' )->get_post_statuses() );

		// Groups.
		$groups = new WP_Query(
			array(
				'post_type'      => $this->base->get_class( 'post_type' )->post_type_name,
				'post_status'    => $statuses,
				'posts_per_page' => -1,
			)
		);

		if ( count( $groups->posts ) === 0 ) {
			return false;
		}

		// Build array.
		$groups_arr = array();
		foreach ( $groups->posts as $group ) {
			// Get settings.
			$group_settings = $this->get_settings( $group->ID );
			if ( is_wp_error( $group_settings ) ) {
				continue;
			}

			$groups_arr[ $group->ID ] = $group_settings;
		}

		/**
		 * Filters the Groups to return.
		 *
		 * @since   1.2.3
		 *
		 * @param   array       $groups_arr Groups.
		 * @param   WP_Query    $groups     Groups Query.
		 */
		$groups_arr = apply_filters( 'page_generator_pro_groups_get_all', $groups_arr, $groups );

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

		// If the query results are already stored, use those for performance.
		if ( $this->ids_names ) {
			return $this->ids_names;
		}

		// Fetch valid Post Statuses that can be used when generating content.
		$statuses = array_keys( $this->base->get_class( 'common' )->get_post_statuses() );

		global $wpdb;

		// Build post statuses placeholders.
		$statuses_placeholders = implode( ',', array_fill( 0, count( $statuses ), '%s' ) );

		// Run query.
		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$results = $wpdb->get_results(
			$wpdb->prepare(
				"
			SELECT ID, post_title
			FROM {$wpdb->posts}
			WHERE post_type = %s
			AND post_status IN ($statuses_placeholders)
			ORDER BY post_title ASC
			",
				array_merge( array( $this->base->get_class( 'post_type' )->post_type_name ), $statuses )
			)
		);
		// phpcs:enable

		// Bail if no Content Groups exist.
		if ( empty( $results ) ) {
			return false;
		}

		// Build array.
		$groups_arr = wp_list_pluck( $results, 'post_title', 'ID' );

		/**
		 * Filters the Groups to return.
		 *
		 * @since   1.2.3
		 *
		 * @param   array       $groups_arr Groups.
		 */
		$groups_arr = apply_filters( 'page_generator_pro_groups_get_all_ids_names', $groups_arr );

		// Store results in class for performance, to save running this query again.
		$this->ids_names = $groups_arr;

		// Return.
		return $this->ids_names;

	}

	/**
	 * Returns an array of all Groups with Auto Regeneration enabled
	 *
	 * @since   2.7.9
	 *
	 * @return  bool|array
	 */
	public function get_auto_regeneration_enabled() {

		// Get all Groups.
		$groups = $this->get_all();

		// Bail if no Groups exist.
		if ( ! $groups ) {
			return false;
		}

		// Build array of Groups where auto regeneration is enabled.
		$auto_regeneration_groups = array();
		foreach ( $groups as $group_id => $group ) {
			// Skip if auto regeneration isn't enabled.
			if ( ! isset( $group['auto_regeneration'] ) ) {
				continue;
			}
			if ( ! $group['auto_regeneration'] ) {
				continue;
			}

			// Add to groups.
			$auto_regeneration_groups[ $group_id ] = $group;
		}

		// Bai if no groups enabled for auto regeneration.
		if ( empty( $auto_regeneration_groups ) ) {
			return false;
		}

		// Return groups.
		return $auto_regeneration_groups;

	}

	/**
	 * Get the number of Groups
	 *
	 * @since   1.3.8
	 *
	 * @return  int             Number of Generated Pages / Posts / CPTs.
	 */
	public function get_count() {

		// Fetch valid Post Statuses that can be used when generating content.
		$statuses = array_keys( $this->base->get_class( 'common' )->get_post_statuses() );

		$posts = new WP_Query(
			array(
				'post_type'              => $this->base->get_class( 'post_type' )->post_type_name,
				'post_status'            => $statuses,
				'posts_per_page'         => 1,
				'update_post_term_cache' => false,
				'update_post_meta_cache' => false,
				'fields'                 => 'ids',
			)
		);

		return count( $posts->posts );

	}

	/**
	 * Get the number of Pages / Posts / CPTs generated by the given Group ID
	 *
	 * @since   1.2.3
	 *
	 * @param   int $id     Group ID.
	 * @return  int             Number of Generated Pages / Posts / CPTs
	 */
	private function get_generated_count_by_id( $id ) {

		return count( $this->get_generated_post_ids_by_id( $id ) );

	}

	/**
	 * Get the Page / Posts / CPT IDs generated by the given Group ID
	 *
	 * @since   3.1.7
	 *
	 * @param   int $id     Group ID.
	 * @return  array
	 */
	public function get_generated_post_ids_by_id( $id ) {

		// Fetch valid Post Statuses that can be used when generating content.
		$statuses = array_keys( $this->base->get_class( 'common' )->get_post_statuses() );

		$posts = new WP_Query(
			array(
				'post_type'              => 'any',
				'post_status'            => $statuses,
				'posts_per_page'         => -1,
				'meta_query'             => array(
					array(
						'key'   => '_page_generator_pro_group',
						'value' => absint( $id ),
					),
				),
				'update_post_term_cache' => false,
				'update_post_meta_cache' => false,
				'fields'                 => 'ids',
			)
		);

		return $posts->posts;

	}

	/**
	 * Helper method to determine if the given Post ID is generated by a Content Group.
	 *
	 * @since   5.0.5
	 *
	 * @param   int $post_id     Post ID.
	 * @return  bool
	 */
	public function is_generated_content( $post_id ) {

		return get_post_meta( $post_id, '_page_generator_pro_group', true ) !== '';

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
					wp_safe_redirect( 'admin.php?page=' . $this->base->plugin->name . '-generate&id=' . $id . '&type=content' );
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
			 * Import as Content Group
			 * - Clicked from row actions on e.g. Pages, Posts and Custom Post Types
			 */
			case 'import':
				$result = $this->import( $id );
				break;

			/**
			 * Test
			 */
			case 'test':
				$result = $this->test( $id );
				break;

			/**
			 * Trash Generated Content
			 */
			case 'trash_generated_content':
				$result = $this->trash_generated_content( $id );
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
				$result = apply_filters( 'page_generator_pro_groups_run_row_actions', $result, $action, $id );
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
			foreach ( $result->get_error_messages() as $error_message ) {
				$this->base->get_class( 'notices' )->add_error_notice( $error_message );
			}

			if ( $redirect ) {
				// Redirect to the Generate Content WP_List_Table.
				wp_safe_redirect( $this->base->get_class( 'groups_table' )->get_action_url() );
				die();
			}
		}

		// Build success notice, if an error didn't occur.
		$message = false;
		if ( ! is_wp_error( $result ) ) {
			switch ( $action ) {

				/**
				 * Test
				 */
				case 'test':
					$message = sprintf(
						'%1$s <a href="%2$s" target="_blank">%3$s</a>',
						sprintf(
							/* translators: Number of seconds */
							__( 'Test Page Generated in %s seconds at ', 'page-generator-pro' ),
							$result['duration']
						),
						$result['url'],
						$result['url']
					);

					foreach ( $result['keywords_terms'] as $keyword => $term ) {
						$message .= '<br />{' . $keyword . '}: ' . ( strlen( $term ) > 50 ? substr( $term, 0, 50 ) . '...' : $term );
					}
					break;

				/**
				 * Import
				 * Duplicate
				 * - Append Link to View/Edit Imported/Duplicated Content Group
				 */
				case 'import':
				case 'duplicate':
					// Get message.
					$message = $this->base->get_class( 'groups_ui' )->get_message( $action . '_success' );

					// Append Link to Content Group.
					$message .= ' <a href="post.php?post=' . absint( $result ) . '&action=edit">' . __( 'View Content Group', 'page-generator-pro' ) . '</a>';
					break;

				default:
					// Get message.
					$message = $this->base->get_class( 'groups_ui' )->get_message( $action . '_success' );

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
					$message = apply_filters( 'page_generator_pro_groups_run_row_actions_success_message', $message, $result, $action, $id );
					break;

			}
		}

		// Store success notice.
		if ( $message !== false ) {
			$this->base->get_class( 'notices' )->add_success_notice( $message );
		}

		// Redirect to the Generate Content WP_List_Table.
		if ( $redirect ) {
			wp_safe_redirect( $this->base->get_class( 'groups_table' )->get_action_url() );
			die();
		}

	}

	/**
	 * Creates a new Content Group, based on the given settings array
	 *
	 * @since   3.2.9
	 *
	 * @param   array $settings           Group Settings.
	 * @param   int   $parent_group_id    Parent Group ID (optional).
	 * @return  WP_Error|int
	 */
	public function create( $settings, $parent_group_id = 0 ) {

		// Merge with defaults, so keys are always set.
		$settings = array_merge( $this->get_defaults(), $settings );

		// Create Group.
		$group_id = wp_insert_post(
			array(
				'post_type'    => $this->base->get_class( 'post_type' )->post_type_name,
				'post_status'  => 'publish',
				'post_title'   => $settings['title'],
				'post_content' => $settings['content'],
				'post_parent'  => $parent_group_id,
			),
			true
		);

		// Bail if an error occured.
		if ( is_wp_error( $group_id ) ) {
			return $group_id;
		}

		// Save settings.
		$result = $this->save( $settings, $group_id );

		// Bail if an error occured.
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		// Return created Group ID.
		return $group_id;

	}

	/**
	 * Adds or edits a record, based on the given settings array.
	 *
	 * @since   1.2.1
	 *
	 * @param   array $settings   Settings to save.
	 * @param   int   $group_id   Group ID.
	 * @return  WP_Error|bool
	 */
	public function save( $settings, $group_id ) {

		// Ensure some keys have a value, in case the user blanked out the values or it's a checkbox that wasn't selected
		// This prevents errors later on when trying to generate content from a Group.
		if ( ! isset( $settings['store_keywords'] ) ) {
			$settings['store_keywords'] = 0;
		}

		if ( ! isset( $settings['comments_generate']['enabled'] ) ) {
			$settings['comments_generate']['enabled'] = 0;
		}

		if ( empty( $settings['resumeIndex'] ) ) {
			$settings['resumeIndex'] = 0;
		}

		// Sanitize the Permalink setting.
		if ( ! empty( $settings['permalink'] ) ) {
			$settings['permalink'] = preg_replace( '/[^a-z0-9-_{}\(\):]+/i', '', str_replace( ' ', '-', trim( $settings['permalink'] ) ) );
		}

		// Clear out blank meta.
		if ( isset( $settings['meta'] ) && is_array( $settings['meta'] ) && count( $settings['meta'] ) > 0 ) {
			foreach ( $settings['meta']['key'] as $index => $value ) {
				if ( empty( $value ) ) {
					unset( $settings['meta']['key'][ $index ] );
					unset( $settings['meta']['value'][ $index ] );
				}
			}
		}

		// Merge with defaults, so any missing keys are always set.
		$settings = array_merge( $this->get_defaults(), $settings );

		// Trim top level settings.
		foreach ( $settings as $key => $value ) {
			if ( is_array( $value ) ) {
				continue;
			}

			$settings[ $key ] = trim( $value );
		}
		if ( is_array( $settings['pageParent'] ) ) {
			$settings['pageParent'] = array_map( 'trim', $settings['pageParent'] );
		}

		// Update Post Meta.
		update_post_meta( $group_id, '_page_generator_pro_settings', $settings );

		/**
		 * Save data to Group Post's Meta
		 *
		 * @since   2.8.6
		 *
		 * @param   int     $group_id   Group ID.
		 * @param   array   $settings   Group Settings.
		 * @param   array   $_REQUEST   Request data.
		 */
		do_action( 'page_generator_pro_groups_save', $group_id, $settings, $_REQUEST ); // phpcs:ignore WordPress.Security.NonceVerification

		// Validate the Group, adding error notices as necessary.
		return $this->validate( $group_id );

	}

	/**
	 * Performs several validations on the given Group Settings, to ensure that
	 * content generation will function successfully.
	 *
	 * These validations should be corrected in save() prior to calling this function,
	 * but this serves as a fallback catch in case they're not.
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

		// If the Title is empty, return an error.
		// Whilst Page Builders don't always use the Title, we need it as a fallback
		// if no Permalink was specified, and to ensure there's a title to display
		// when using Related Links.
		if ( empty( $settings['title'] ) ) {
			return new WP_Error(
				'page_generator_pro_groups_validate_title',
				__( 'The Title must be specified. If using a Page Builder, edit the Content Group outside of the Page Builder, entering a title.', 'page-generator-pro' )
			);
		}

		// Validate Permalink.
		$result = $this->validate_permalink( $settings );
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		// Validate Parent Attribute.
		$result = $this->validate_parent_attribute( $settings );
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		// If the Permalink is a fixed string, and the Parent Attribute is not defined, return an error.
		if ( $this->permalink_is_fixed_string( $settings ) && ! $this->has_parent_attribute( $settings ) ) {
			return new WP_Error(
				'page_generator_pro_groups_validate_permalink',
				__( 'The Permalink is a static string, and no Attributes > Parent exist. This would result in a single generated page, regardless of any other settings or Keywords specified elsewhere in the Content Group.', 'page-generator-pro' )
			);
		}

		// If both the Permalink and Parent Attribute are fixed strings, return an error.
		if ( $this->permalink_is_fixed_string( $settings ) && $this->parent_attribute_is_fixed_string( $settings ) ) {
			return new WP_Error(
				'page_generator_pro_groups_validate_permalink',
				__( 'The Permalink and Attributes > Parent are both static strings. This would result in a single generated page, regardless of any other settings or Keywords specified elsewhere in the Content Group.', 'page-generator-pro' )
			);
		}

		// If no Author has been specified, return an error.
		if ( empty( $settings['author'] ) && ! $settings['rotateAuthors'] ) {
			return new WP_Error(
				'page_generator_pro_groups_validate_author',
				__( 'The Author must be specified, or the Random Author option selected.', 'page-generator-pro' )
			);
		}

		// If the Group is not published, generation might fail in Gutenberg stating that no keywords could be found
		// in the Content.
		$post_status             = get_post_status( $id );
		$required_group_statuses = $this->get_group_statuses();
		if ( ! in_array( $post_status, array_keys( $required_group_statuses ), true ) ) {
			return new WP_Error(
				'page_generator_pro_groups_validate_save',
				sprintf(
					/* translators: %1$s: Comma separated list of required Group Statuses (e.g. publish,future), %2$s: Current Group Status (e.g. draft), %3$s: URL to Edit Content Group screen */
					__( 'The Group\'s Status must be set to one of <strong>%1$s</strong> for Generation to function correctly. Right now, it\'s set as <strong>%2$s</strong>. Please <a href="%3$s">edit the Group</a> as necessary.', 'page-generator-pro' ),
					implode( ', ', $required_group_statuses ),
					get_post_status( $id ),
					admin_url( 'post.php?post=' . $id . '&action=edit' )
				)
			);
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
		$result = apply_filters( 'page_generator_pro_groups_validate', $result, $settings, $id );

		// Return result.
		return $result;

	}

	/**
	 * Validates the Permalink setting.
	 *
	 * @since   5.0.5
	 *
	 * @param   array $settings   Group Settings.
	 * @return  WP_Error|bool
	 */
	public function validate_permalink( $settings ) {

		// If no Permalink is specified, Permalink is valid.
		if ( empty( $settings['permalink'] ) ) {
			return true;
		}

		// No check for field only containing keywords, letters, numbers, hyphens and underscores,
		// as the input is sanitized on save.

		// If the Permalink contains a Keyword, check it's fully formed with no missing braces.
		if ( substr_count( $settings['permalink'], '{' ) !== substr_count( $settings['permalink'], '}' ) ) {
			return new WP_Error(
				'page_generator_pro_groups_validate_permalink',
				__( 'One or more Keywords in the Permalink are missing opening and/or closing braces. This must be fixed for generation to work correctly.', 'page-generator-pro' )
			);
		}

	}

	/**
	 * Validates the Parent Attribute setting.
	 *
	 * @since   5.0.5
	 *
	 * @param   array $settings   Group Settings.
	 * @return  WP_Error|bool
	 */
	public function validate_parent_attribute( $settings ) {

		// Field is valid if not defined or empty.
		if ( ! is_array( $settings['pageParent'] ) ) {
			return true;
		}
		if ( ! array_key_exists( $settings['type'], $settings['pageParent'] ) ) {
			return true;
		}
		if ( empty( $settings['pageParent'][ $settings['type'] ] ) ) {
			return true;
		}

		// Check field only contains keywords, letters, numbers, hyphens and underscores.
		if ( preg_match( '/[\\s\'~`\!@#\$%\^&\*\+=\[\]\|;"\<\>\.\?\\\]/', $settings['pageParent'][ $settings['type'] ] ) ) {
			return new WP_Error(
				'page_generator_pro_groups_validate_parent',
				__( 'The Attributes > Parent field can only contain keywords, letters, numbers, hyphens and underscores.', 'page-generator-pro' )
			);
		}

		// If the field contains a Keyword, check it's fully formed with no missing braces.
		if ( substr_count( $settings['pageParent'][ $settings['type'] ], '{' ) !== substr_count( $settings['pageParent'][ $settings['type'] ], '}' ) ) {
			return new WP_Error(
				'page_generator_pro_groups_validate_permalink',
				__( 'One or more Keywords in the Attributes > Parent field are missing opening and/or closing braces. This must be fixed for generation to work correctly.', 'page-generator-pro' )
			);
		}

	}

	/**
	 * Returns true if the Parent Attribute is defined for the Post Type
	 * setting in the Content Group.
	 *
	 * @since   5.0.5
	 *
	 * @param   array $settings   Group Settings.
	 * @return  bool
	 */
	public function has_parent_attribute( $settings ) {

		// Field is valid if not defined or empty.
		if ( ! is_array( $settings['pageParent'] ) ) {
			return false;
		}
		if ( ! array_key_exists( $settings['type'], $settings['pageParent'] ) ) {
			return false;
		}
		if ( empty( $settings['pageParent'][ $settings['type'] ] ) ) {
			return false;
		}

		return true;

	}

	/**
	 * Returns true if the Permalink is a fixed string i.e. not a Keyword.
	 *
	 * @since   5.0.5
	 *
	 * @param   array $settings   Group Settings.
	 * @return  bool
	 */
	public function permalink_is_fixed_string( $settings ) {

		// If no Permalink is specified, Permalink is not a fixed string.
		if ( empty( $settings['permalink'] ) ) {
			return false;
		}

		// Get Keywords from Permalink.
		preg_match_all( '|{(.+?)}|', $settings['permalink'], $matches );

		// Check if Permalink is a fixed string.
		if ( ! is_array( $matches ) || count( $matches[1] ) === 0 ) {
			return true;
		}

		return false;

	}

	/**
	 * Returns true if the Parent Attribute is a fixed string i.e. not a Keyword.
	 *
	 * @since   5.0.5
	 *
	 * @param   array $settings   Group Settings.
	 * @return  bool
	 */
	public function parent_attribute_is_fixed_string( $settings ) {
		// Get Keywords from Parent Attribute.
		preg_match_all( '|{(.+?)}|', $settings['pageParent'][ $settings['type'] ], $matches );

		// Check if Parent Attribute is a fixed string.
		if ( ! is_array( $matches ) || count( $matches[1] ) === 0 ) {
			return true;
		}

		return false;

	}

	/**
	 * Returns an array of Post Statuses that can be used when saving a Content Group.
	 *
	 * If a Group's Status does not match a status defined here, generation might fail in Gutenberg
	 * stating that no keywords could be found.
	 *
	 * @since   2.6.5
	 *
	 * @return  array   Supported Group Statuses
	 */
	public function get_group_statuses() {

		// Get statuses.
		$statuses = array(
			'private' => __( 'Private', 'page-generator-pro' ),
			'publish' => __( 'Publish', 'page-generator-pro' ),
		);

		/**
		 * Defines available Post Statuses for generated content.
		 *
		 * @since   2.6.5
		 *
		 * @param   array   $statuses   Statuses.
		 */
		$statuses = apply_filters( 'page_generator_pro_groups_get_group_statuses', $statuses );

		// Return filtered results.
		return $statuses;

	}

	/**
	 * Fetches the last index generated for the given Group.
	 *
	 * @since   2.2.6
	 *
	 * @param   int $id     Group ID.
	 */
	public function get_last_index_generated( $id ) {

		return absint( get_post_meta( $id, '_page_generator_pro_last_index_generated', true ) );

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

		update_post_meta( $id, '_page_generator_pro_last_index_generated', $index );

	}

	/**
	 * Schedules content generation via WordPress' Cron
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
				'page_generator_pro_groups_schedule_generation',
				sprintf(
					/* translators: Group ID */
					__( 'Group ID %s is already scheduled to generate content!', 'page-generator-pro' ),
					$id
				)
			);
		}

		// Bail if the group is already generating content.
		if ( $this->is_generating( $id ) ) {
			return new WP_Error(
				'page_generator_pro_groups_schedule_generation',
				sprintf(
					/* translators: Group ID */
					__( 'Group ID %s is already generating content!', 'page-generator-pro' ),
					$id
				)
			);
		}

		// Validate the group prior to scheduling.
		$validated = $this->validate( $id );
		if ( is_wp_error( $validated ) ) {
			return $validated;
		}

		// If here, we're OK to schedule.
		wp_schedule_single_event(
			time() + 10,
			'page_generator_pro_generate_cron',
			array(
				$id,
				'content',
			)
		);

		// Mark group as scheduled.
		$this->start_generation( $id, 'scheduled', 'cron' );

		// Done.
		return true;

	}

	/**
	 * Imports a Page, Post or Custom Post Type as a new Content Group
	 *
	 * @since   2.7.7
	 *
	 * @param   int $source_id      Source Post / Page / CPT ID.
	 * @return  WP_Error|int
	 */
	public function import( $source_id ) {

		// Fetch default settings.
		$settings = $this->get_defaults();

		// Fetch source Post.
		$source_post = get_post( $source_id );

		// Define Settings based on Source Post.
		$settings['title']   = $source_post->post_title;
		$settings['content'] = $source_post->post_content;
		$settings['excerpt'] = $source_post->post_excerpt;
		$settings['author']  = $source_post->post_author;
		$settings['type']    = $source_post->post_type;
		$settings['status']  = $source_post->post_status;

		// Create new Post.
		$group_id = wp_insert_post(
			array(
				'post_type'    => $this->base->get_class( 'post_type' )->post_type_name,
				'post_title'   => $settings['title'],
				'post_content' => $settings['content'],
				'post_status'  => get_post_status( $source_id ),
			),
			true
		);
		if ( is_wp_error( $group_id ) ) {
			return $group_id;
		}

		// Save Settings.
		$result = $this->save( $settings, $group_id );
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		// Copy Metadata.
		$this->copy_meta( $source_id, $group_id );

		// Return Group ID.
		return $group_id;

	}

	/**
	 * Duplicates a Content Group
	 *
	 * @since   1.2.3
	 *
	 * @param   int $group_id   Group ID.
	 * @return  WP_Error|int
	 */
	public function duplicate( $group_id ) {

		// Fetch group settings.
		$settings = $this->get_settings( $group_id, false );

		// Bail if an error occured.
		if ( is_wp_error( $settings ) ) {
			return $settings;
		}

		// Validate Group.
		$validated = $this->validate( $group_id );

		// Bail if an error occured.
		if ( is_wp_error( $validated ) ) {
			return $validated;
		}

		// Create new Post.
		$duplicate_group_id = wp_insert_post(
			array(
				'post_type'    => $this->base->get_class( 'post_type' )->post_type_name,
				'post_title'   => $settings['title'] . __( ' - Copy', 'page-generator-pro' ),
				'post_content' => $settings['content'],
				'post_status'  => get_post_status( $group_id ),
			),
			true
		);

		// Bail if an error occured.
		if ( is_wp_error( $duplicate_group_id ) ) {
			return $duplicate_group_id;
		}

		// Copy Metadata.
		$this->copy_meta( $group_id, $duplicate_group_id );

		// Return duplicated Group ID.
		return $duplicate_group_id;

	}

	/**
	 * Copies metadata from the given source Post ID to the destination Post ID
	 *
	 * @since   2.7.7
	 *
	 * @param   int $source_id          Source Post ID.
	 * @param   int $destination_id     Destination Post ID.
	 * @return  bool
	 */
	private function copy_meta( $source_id, $destination_id ) {

		// Define the metadata to ignore.
		$ignored_keys = array(
			'_edit_lock',
			'_edit_last',
			'_page_generator_pro_group',
			'_page_generator_pro_index',
			'_page_generator_pro_generating',
			'_page_generator_pro_generating_system',
			'_page_generator_pro_last_index_generated',
		);

		// Ignore Keywords, which might be stored as meta keys.
		$keywords = $this->base->get_class( 'keywords' )->get_keywords_and_columns();
		if ( is_array( $keywords ) ) {
			foreach ( $keywords as $keyword ) {
				$ignored_keys[] = str_replace( ')', '', str_replace( '(', '_', $keyword ) );
			}
		}

		/**
		 * Defines Post Meta Keys in a Content Group to ignore and not copy to generated Posts / Groups.
		 *
		 * @since   2.7.7
		 *
		 * @param   array   $ignored_keys   Ignored Keys
		 */
		$ignored_keys = apply_filters( 'page_generator_pro_groups_duplicate_ignored_keys', $ignored_keys );

		// Copy all metadata from the source Post to the destination group.
		// We don't use utility/helper functions in this Plugin as they transform e.g. Post Meta into arrays.
		$meta_data = get_post_meta( $source_id );
		if ( ! is_array( $meta_data ) ) {
			return true;
		}

		foreach ( $meta_data as $meta_key => $meta_value ) {
			// Ignore some keys.
			if ( in_array( $meta_key, $ignored_keys, true ) ) {
				continue;
			}

			// Fetch this specific meta value, so we get the unserialized data.
			$meta_value = get_post_meta( $source_id, $meta_key, true );

			// Store this meta key/value pair in the duplicated group
			// addslashes ensures that single backslashes are doubled, therefore when update_post_meta removes a backslash,
			// the underlying post meta value is still valid and doesn't break e.g. Page Builders.
			if ( is_string( $meta_value ) ) {
				update_post_meta( $destination_id, $meta_key, addslashes( $meta_value ) );
			} else {
				update_post_meta( $destination_id, $meta_key, $meta_value );
			}
		}

		// Return result.
		return true;

	}

	/**
	 * Defines available actions for the given Group ID (Test, Generate via Browser,
	 * Generate via Server), which are displayed in the WP_List_Table and when editing
	 * the Content Group.
	 *
	 * @since   3.5.4
	 *
	 * @param   int  $id            Group ID.
	 * @param   int  $current_index Index to generate for Test mode.
	 * @param   bool $css_classes   Include button CSS classes in links.
	 * @return  array               Actions
	 */
	public function get_actions_links( $id, $current_index = 0, $css_classes = false ) {

		// Define links.
		$links = array(
			'test'            => $this->base->get_class( 'groups_table' )->get_action_url( $id, 'test' ),
			'generate'        => $this->base->get_class( 'groups_table' )->get_action_url( $id, 'generate' ),
			'generate_server' => $this->base->get_class( 'groups_table' )->get_action_url( $id, 'generate_server' ),
		);

		// Return links.
		return array(
			'test'            => '<a href="' . $links['test'] . '"' . ( $css_classes ? ' class="' . $css_classes . '"' : '' ) . ' data-current-index="' . $current_index . '">' . $this->base->get_class( 'groups_ui' )->get_title( 'test' ) . '</a>',
			'generate'        => '<a href="' . $links['generate'] . '"' . ( $css_classes ? ' class="' . $css_classes . '"' : '' ) . '>' . $this->base->get_class( 'groups_ui' )->get_title( 'generate' ) . '</a>',
			'generate_server' => '<a href="' . $links['generate_server'] . '"' . ( $css_classes ? ' class="' . $css_classes . '"' : '' ) . '>' . $this->base->get_class( 'groups_ui' )->get_title( 'generate_server' ) . '</a>',
		);

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

		// Fetch group.
		$post = get_post( $id );
		if ( ! $post ) {
			return new WP_Error(
				'page_generator_pro_groups_test',
				sprintf(
					/* translators: Group ID */
					__( 'Group ID %s does not exist!', 'page-generator-pro' ),
					$id
				)
			);
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

		// Run test.
		$result = $this->base->get_class( 'generate' )->generate_content( $id, $settings['resumeIndex'], true );

		// Define success / error notice based on the test result.
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		/**
		 * Runs any actions once Generate Content has finished.
		 *
		 * @since   1.9.3
		 *
		 * @param   int     $group_id   Group ID.
		 * @param   bool    $test_mode  Test Mode.
		 * @param   string  $system     System.
		 */
		do_action( 'page_generator_pro_generate_content_after', $id, true, 'browser' );

		// Return result.
		return $result;

	}

	/**
	 * Trashes Generated Content for the given Group ID
	 *
	 * @since   1.9.1
	 *
	 * @param   int $id     Group ID.
	 * @return  WP_Error|array
	 */
	public function trash_generated_content( $id ) {

		// Trash Generated Content now.
		return $this->base->get_class( 'generate' )->trash_content( $id );

	}

	/**
	 * Deletes Generated Content for the given Group ID
	 *
	 * @since   1.8.0
	 *
	 * @param   int $id     Group ID.
	 * @return  WP_Error|array
	 */
	public function delete_generated_content( $id ) {

		// Delete Generated Content now.
		return $this->base->get_class( 'generate' )->delete_content( $id );

	}

	/**
	 * Returns a flag denoting whether the given Group ID has generated content
	 *
	 * @since   1.9.9
	 *
	 * @param   int $id     Group ID.
	 * @return  bool            Has Generated Content
	 */
	public function has_generated_content( $id ) {

		// Get number of generated pages.
		$generated_pages_count = $this->get_generated_count_by_id( $id );

		if ( $generated_pages_count > 0 ) {
			return true;
		}

		return false;

	}

	/**
	 * Determines if the given Content Group is eligible to generate content
	 *
	 * @since   3.3.9
	 *
	 * @param   int $id     Group ID.
	 * @return  bool            Can Generate Content
	 */
	public function generates_content( $id ) {

		$can_generate_content = true;

		/**
		 * Determine if the given Content Group is eligible to generate content
		 *
		 * @since   3.3.9
		 *
		 * @param   bool    $can_generate_content   Can Generate Content.
		 * @param   int     $id                     Group ID.
		 */
		$can_generate_content = apply_filters( 'page_generator_pro_groups_generates_content', $can_generate_content, $id );

		// Return filtered result.
		return $can_generate_content;

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

		return get_post_meta( $id, '_page_generator_pro_status', true );

	}

	/**
	 * Gets the given Group ID's system being used for generation
	 *
	 * @since   1.9.9
	 *
	 * @param   int $id     Group ID.
	 */
	public function get_system( $id ) {

		return get_post_meta( $id, '_page_generator_pro_system', true );

	}

	/**
	 * Starts generation for the given Group ID by:
	 * - Defining the status flag
	 * - Defining the system flag
	 * - Deleting the cancel flag
	 *
	 * @since   1.9.9
	 *
	 * @param   int    $id             Group ID.
	 * @param   string $status     Status.
	 * @param   string $system     Generation System.
	 * @return  bool
	 */
	public function start_generation( $id, $status, $system ) {

		update_post_meta( $id, '_page_generator_pro_status', $status );
		update_post_meta( $id, '_page_generator_pro_system', $system );
		delete_post_meta( $id, '_page_generator_pro_cancel' );

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
					'content',
				)
			);
		}

		delete_post_meta( $id, '_page_generator_pro_status' );
		delete_post_meta( $id, '_page_generator_pro_system' );
		update_post_meta( $id, '_page_generator_pro_cancel', 1 );

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
			' SELECT meta_value FROM ' . $wpdb->postmeta . '
                                    WHERE post_id = ' . absint( $id ) . "
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
	 * @param   int $id         Group ID.
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

		delete_post_meta( $id, '_page_generator_pro_status' );
		delete_post_meta( $id, '_page_generator_pro_system' );
		delete_post_meta( $id, '_page_generator_pro_cancel' );

		return true;

	}

	/**
	 * Determines if the given Page ID was generated by the given Group ID
	 *
	 * @since   3.0.9
	 *
	 * @param   int $post_id    Post ID.
	 * @param   int $group_id   Group ID.
	 * @return  bool                Post ID was generated by Group ID
	 */
	public function is_generated_by_group( $post_id, $group_id ) {

		// Get Group ID that generated the given Post ID.
		$post_generated_group_id = get_post_meta( $post_id, '_page_generator_pro_group', true );

		if ( ! $post_generated_group_id ) {
			return false;
		}
		if ( ! is_numeric( $post_generated_group_id ) ) {
			return false;
		}

		// Return false if the Post ID doesn't match.
		return ( ( absint( $post_generated_group_id ) !== absint( $group_id ) ) ? false : true );

	}

}
