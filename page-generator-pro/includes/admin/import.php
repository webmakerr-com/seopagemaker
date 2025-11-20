<?php
/**
 * Import Class
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Imports configuration data from a JSON or zipped JSON file that was created
 * by this Plugin's export functionality, storing the data in the Plugin's settings,
 * Content Groups and Term Groups.
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 1.1.8
 */
class Page_Generator_Pro_Import {

	/**
	 * Holds the base object.
	 *
	 * @since   1.9.8
	 *
	 * @var     object
	 */
	public $base;

	/**
	 * Holds the JSON Group ID to imported Group ID mappings,
	 * used to then update Group IDs in e.g. shortcodes
	 * on the imported Groups to prevent relationships
	 * from breaking.
	 *
	 * @since   2.7.6
	 *
	 * @var     array
	 */
	public $group_id_mappings = array();

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

		// Import Pages, Posts and Custom Post Types into Content Groups.
		add_filter( 'post_row_actions', array( $this, 'add_import_row_action' ), 10, 2 );
		add_filter( 'page_row_actions', array( $this, 'add_import_row_action' ), 10, 2 );

		// Import.
		add_filter( 'page_generator_pro_import', array( $this, 'import' ), 10, 2 );

	}

	/**
	 * Adds a 'Import as Content Group' actions below the Post Title in WP_List_Table classes.
	 *
	 * Page_Generator_Pro_Groups::run_row_actions handles running the action once clicked.
	 *
	 * @since   2.7.7
	 *
	 * @param   array   $actions    Row Actions.
	 * @param   WP_Post $post       WordPress Post.
	 * @return  array                   Row Actions
	 */
	public function add_import_row_action( $actions, $post ) {

		// Bail if a Content Group CPT.
		if ( get_post_type( $post ) === $this->base->get_class( 'post_type' )->post_type_name ) {
			return $actions;
		}

		// Build URL.
		$url = add_query_arg(
			array(
				'post_type'                           => $this->base->get_class( 'post_type' )->post_type_name,
				$this->base->plugin->name . '-action' => 'import',
				'id'                                  => $post->ID,
				'type'                                => 'content',
				'nonce'                               => wp_create_nonce( 'action-page-generator-pro-content-group' ),
			),
			'edit.php'
		);

		// Add Import Action.
		$actions['page_generator_pro_import'] = '<a href="' . $url . '">' . __( 'Import as Content Group', 'page-generator-pro' ) . '</a>';

		// Return.
		return $actions;

	}

	/**
	 * Import data created by this Plugin's export functionality
	 *
	 * @since   2.6.8
	 *
	 * @param   bool  $success    Success.
	 * @param   array $import     Settings.
	 * @return  WP_Error|bool
	 */
	public function import( $success, $import ) {

		// Fetch data.
		$data = $import['data'];

		// Keywords.
		if ( isset( $data['keywords'] ) && is_array( $data['keywords'] ) ) {
			$this->import_keywords( $data['keywords'] );
		}

		// Groups.
		if ( isset( $data['groups'] ) && is_array( $data['groups'] ) ) {
			$this->import_content_groups( $data['groups'] );
		}

		// Terms.
		if ( isset( $data['terms'] ) && is_array( $data['terms'] ) ) {
			$this->import_term_groups( $data['terms'] );
		}

		// Settings.
		$this->import_settings( $data );

		// Return.
		return $success;

	}

	/**
	 * Imports the given Keywords into WordPress
	 *
	 * @since   2.7.6
	 *
	 * @param   array $keywords     Keywords from Plugin's JSON File.
	 */
	private function import_keywords( $keywords ) {

		foreach ( $keywords as $keyword ) {
			// Create keyword.
			$this->base->get_class( 'keywords' )->save( $keyword );
		}

	}

	/**
	 * Imports the given Content Groups into WordPress
	 *
	 * @since   2.7.6
	 *
	 * @param   array $groups     Content Groups from Plugin's JSON File.
	 */
	private function import_content_groups( $groups ) {

		foreach ( $groups as $group_id => $settings ) {
			// Gutenberg double slashing.
			$settings['content'] = preg_replace( '/(u[0-9a-fA-F]{4})/i', '\\\$1', $settings['content'] );

			// Build args.
			$args = array(
				'post_type'    => $this->base->get_class( 'post_type' )->post_type_name,
				'post_status'  => 'publish',
				'post_title'   => $settings['title'],
				'post_content' => $settings['content'],
			);

			// Create group.
			$imported_group_id = wp_insert_post( $args, true );

			// Skip if something went wrong.
			if ( is_wp_error( $imported_group_id ) ) {
				continue;
			}

			// Remove some settings prior to saving the Group's settings.
			$settings['generated_pages_count'] = 0;
			$settings['last_index_generated']  = 0;

			// Save group settings.
			$this->base->get_class( 'groups' )->save( $settings, $imported_group_id );

			// If the group has a post_meta key, store it against the Group.
			if ( isset( $settings['post_meta'] ) && is_array( $settings['post_meta'] ) ) {
				foreach ( $settings['post_meta'] as $meta_key => $meta_value ) {
					/**
					 * Modify the meta data before it's saved to the imported Content Group.
					 *
					 * @since   5.3.2
					 *
					 * @param   array|string|int|bool   $meta_value Meta Value.
					 * @param   int                     $group_id   Group ID.
					 * @param   array                   $settings   Group Settings.
					 */
					$meta_value = apply_filters( 'page_generator_pro_import_content_groups_set_post_meta_' . $meta_key, $meta_value, $imported_group_id, $settings );

					// Update the meta value.
					update_post_meta( $imported_group_id, $meta_key, $meta_value );
				}
			}

			/**
			 * Action to perform any further steps with the Content Group's Post Meta,
			 * after all Post Meta has been imported from the Content Group to the
			 * imported Content Group from the JSON file.
			 *
			 * @since   5.3.2
			 *
			 * @param   int     $group_id       Group ID.
			 * @param   array   $post_meta      Group Post Meta.
			 * @param   array   $settings       Group Settings.
			 */
			do_action( 'page_generator_pro_import_content_groups_set_post_meta', $imported_group_id, $settings['post_meta'], $settings );

			// Store old Group ID to imported Group ID mapping.
			$this->group_id_mappings[ $imported_group_id ] = $group_id;
		}

		// Replace any group_id="$group_id" instances with the new imported Group ID.
		$groups = $this->base->get_class( 'groups' )->get_all();
		foreach ( $groups as $group_id => $settings ) {
			// Update Group ID references in this Content Group's Settings.
			array_walk_recursive( $settings, array( $this, 'update_group_id_references' ) );

			// Save Group Settings.
			$this->base->get_class( 'groups' )->save( $settings, $group_id );

			// Gutenberg double slashing.
			$settings['content'] = preg_replace( '/(u[0-9a-fA-F]{4})/i', '\\\$1', $settings['content'] );

			// Save Group Title and Content, so changes are reflected when editing a Content Group.
			$result = wp_update_post(
				array(
					'ID'           => $group_id,
					'post_title'   => $settings['title'],
					'post_content' => $settings['content'],
				),
				true
			);
		}

	}

	/**
	 * Imports the given Term Groups into WordPress
	 *
	 * @since   2.7.6
	 *
	 * @param   array $groups     Term Groups from Plugin's JSON File.
	 */
	private function import_term_groups( $groups ) {

		foreach ( $groups as $group ) {
			// Create group.
			$id = wp_insert_term(
				$group['title'],
				$this->base->get_class( 'taxonomy' )->taxonomy_name,
				array(
					'slug'        => $group['permalink'],
					'description' => $group['excerpt'],
					'parent'      => $group['parent_term'],
				)
			);

			// Skip if something went wrong.
			if ( is_wp_error( $id ) ) {
				continue;
			}

			// Save group settings.
			$this->base->get_class( 'groups_terms' )->save( $group, $id );
		}

	}

	/**
	 * Imports the given Plugin Settings into WordPress
	 *
	 * @since   2.7.6
	 *
	 * @param   array $data     Settings from Plugin's JSON File.
	 */
	private function import_settings( $data ) {

		// Settings: General.
		if ( isset( $data['general'] ) ) {
			$this->base->get_class( 'settings' )->update_settings( $this->base->plugin->name . '-general', $data['general'] );
		}

		// Settings: Generate.
		if ( isset( $data['generate'] ) ) {
			$this->base->get_class( 'settings' )->update_settings( $this->base->plugin->name . '-generate', $data['generate'] );
		}

		// Settings: Generate Locations.
		if ( isset( $data['generate-locations'] ) ) {
			$this->base->get_class( 'settings' )->update_settings( $this->base->plugin->name . '-generate-locations', $data['generate-locations'] );
		}

		// Settings: Integrations.
		if ( isset( $data['integrations'] ) ) {
			$this->base->get_class( 'settings' )->update_settings( $this->base->plugin->name . '-integrations', $data['integrations'] );
		}

		// Settings: Research.
		if ( isset( $data['research'] ) ) {
			$this->base->get_class( 'settings' )->update_settings( $this->base->plugin->name . '-research', $data['research'] );
		}

		// Settings: Spintax.
		if ( isset( $data['spintax'] ) ) {
			$this->base->get_class( 'settings' )->update_settings( $this->base->plugin->name . '-spintax', $data['spintax'] );
		}

	}

	/**
	 * Callback for array_walk_recursive to replace old Group ID references
	 * with their new imported Group ID references.
	 *
	 * @since   2.7.6
	 *
	 * @param   array|object|string $item   Item.
	 * @param   string              $key    Key.
	 */
	private function update_group_id_references( &$item, $key ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter

		// If the settings key's value is an array, walk through it recursively to search/replace
		// Otherwise do a standard search/replace on the string.
		if ( is_array( $item ) ) {
			// Array.
			array_walk_recursive( $item, array( $this, 'update_group_id_references' ) );
		} elseif ( is_object( $item ) ) {
			// Object.
			array_walk_recursive( $item, array( $this, 'update_group_id_references' ) );
		} elseif ( is_string( $item ) ) {
			// If here, we have a string.
			foreach ( $this->group_id_mappings as $new_group_id => $old_group_id ) {
				$item = str_replace( 'group_id="' . $old_group_id . '"', 'group_id="' . $new_group_id . '"', $item );
			}
		}

	}

}
