<?php
/**
 * AJAX Class
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Registers AJAX endpoints for various features, such as Generate Locations,
 * Generate Spintax from Content and Generation/Trash/Delete Content.
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 1.0.0
 */
class Page_Generator_Pro_AJAX {

	/**
	 * Holds the base object.
	 *
	 * @since   1.9.8
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

		// Keywords: Refresh Terms.
		add_action( 'wp_ajax_page_generator_pro_keywords_refresh_terms', array( $this, 'keywords_refresh_terms' ) );

		// Keywords: Get Terms.
		add_action( 'wp_ajax_page_generator_pro_keywords_get_terms', array( $this, 'keywords_get_terms' ) );

		// Page Attributes: Parent.
		add_action( 'wp_ajax_page_generator_pro_search_pages', array( $this, 'search_pages' ) );

		// Generate: Authors.
		add_action( 'wp_ajax_page_generator_pro_search_authors', array( $this, 'search_authors' ) );

		// Research.
		add_action( 'wp_ajax_page_generator_pro_research', array( $this, 'research' ) );
		add_action( 'wp_ajax_page_generator_pro_research_get_status', array( $this, 'research_get_status' ) );

		// TinyMCE.
		add_action( 'wp_ajax_page_generator_pro_tinymce_spintax_generate', array( $this, 'spintax_generate' ) );
		add_action( 'wp_ajax_page_generator_pro_output_tinymce_modal', array( $this, 'output_tinymce_modal' ) );

		// Generate: Content.
		add_action( 'wp_ajax_page_generator_pro_generate_content_before', array( $this, 'before_generated_content' ) );
		add_action( 'wp_ajax_page_generator_pro_generate_content', array( $this, 'generate_content' ) );
		add_action( 'wp_ajax_page_generator_pro_generate_content_trash_generated_content', array( $this, 'trash_generated_content' ) );
		add_action( 'wp_ajax_page_generator_pro_generate_content_delete_generated_content', array( $this, 'delete_generated_content' ) );
		add_action( 'wp_ajax_page_generator_pro_generate_content_after', array( $this, 'after_generated_content' ) );

		// Generate: Terms.
		add_action( 'wp_ajax_page_generator_pro_generate_term_before', array( $this, 'before_generated_terms' ) );
		add_action( 'wp_ajax_page_generator_pro_generate_term', array( $this, 'generate_term' ) );
		add_action( 'wp_ajax_page_generator_pro_generate_term_delete_generated_term', array( $this, 'delete_generated_terms' ) );
		add_action( 'wp_ajax_page_generator_pro_generate_term_after', array( $this, 'after_generated_terms' ) );

	}

	/**
	 * Returns the maximum number of generated items to delete in a single AJAX
	 * request, to prevent timeouts or server errors.
	 *
	 * @since   2.7.6
	 *
	 * @return  int     Limit
	 */
	public function get_trash_delete_per_request_item_limit() {

		$limit = $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-generate', 'trash_delete_per_request_item_limit', 100 );

		/**
		 * The maximum number of generated items to trash or delete in a single AJAX
		 * request, to prevent timeouts or server errors.
		 *
		 * If there are more items to delete than the limit specified, the Plugin
		 * will send synchronous requests until all items are deleted.
		 *
		 * @since   2.7.6
		 */
		$limit = apply_filters( 'page_generator_pro_ajax_delete_generated_count_number_of_items', $limit );

		// Return.
		return absint( $limit );

	}

	/**
	 * Keywords: Refresh Terms
	 *
	 * @since   4.1.5
	 */
	public function keywords_refresh_terms() {

		// Verify nonce.
		check_ajax_referer( 'refresh_term_keyword', 'nonce' );

		// Bail if expected vars are not set.
		if ( ! isset( $_REQUEST['keyword'] ) ) {
			wp_send_json_error( __( 'Missing expected variables.', 'page-generator-pro' ) );
		}

		// Get vars.
		$keyword = sanitize_text_field( wp_unslash( $_REQUEST['keyword'] ) );

		// Refresh terms for this Keyword.
		$result = $this->base->get_class( 'keywords' )->refresh_terms(
			array(
				$keyword,
			)
		);

		// If an error occured refreshing, bail.
		if ( is_wp_error( $result ) ) {
			wp_send_json_error( $result->get_error_message() );
		}

		wp_send_json_success();

	}

	/**
	 * Keywords: Get Terms
	 *
	 * @since   3.0.9
	 */
	public function keywords_get_terms() {

		// Verify nonce.
		check_ajax_referer( 'save_keyword', 'nonce' );

		// Bail if expected vars are not set.
		if ( ! isset( $_REQUEST['id'] ) || ! isset( $_REQUEST['draw'] ) || ! isset( $_REQUEST['start'] ) || ! isset( $_REQUEST['length'] ) || ! isset( $_REQUEST['search']['value'] ) ) {
			wp_send_json_error( __( 'Missing expected variables.', 'page-generator-pro' ) );
		}

		// Get vars.
		$id     = absint( $_REQUEST['id'] );
		$draw   = absint( $_REQUEST['draw'] );
		$offset = absint( $_REQUEST['start'] );
		$limit  = absint( $_REQUEST['length'] );
		$search = sanitize_text_field( wp_unslash( $_REQUEST['search']['value'] ) );

		// Run query.
		$terms = $this->base->get_class( 'keywords' )->get_terms( $id, $offset, $limit, $search, false );

		// Return error.
		if ( ! $terms ) {
			echo wp_json_encode(
				array(
					'error' => __( 'Keyword not found.', 'page-generator-pro' ),
				)
			);
			die();
		}

		// Return Terms in JSON string compatible with DataTables.
		echo wp_json_encode(
			array(
				'draw'            => $draw,
				'recordsTotal'    => $terms['total'],
				'recordsFiltered' => $terms['filtered'],
				'data'            => $terms['data'],
			)
		);
		die();

	}

	/**
	 * Searches for Pages, Posts or Custom Post Types for the given freeform text
	 *
	 * @since   2.1.8
	 */
	public function search_pages() {

		// Verify nonce.
		check_ajax_referer( 'search_pages', 'nonce' );

		// Bail if expected vars are not set.
		if ( ! isset( $_REQUEST['args'] ) ) {
			wp_send_json_error( __( 'Missing expected variables.', 'page-generator-pro' ) );
		}

		// Parse args.
		parse_str( sanitize_text_field( wp_unslash( $_REQUEST['args'] ) ), $args );

		// Build WP_Query args.
		$query = array(
			'post_status'            => 'publish',
			'post_type'              => ( isset( $args['post_type'] ) ? $args['post_type'] : 'page' ),
			's'                      => ( isset( $_REQUEST['query'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['query'] ) ) : '' ),
			'order'                  => 'ASC',
			'orderby'                => 'relevance',

			// Performance.
			'posts_per_page'         => 10,
			'update_post_term_cache' => false,
			'update_post_meta_cache' => false,
			'fields'                 => 'id=>parent',
		);

		// Add Exclusion.
		if ( isset( $args['exclude_tree'] ) ) {
			$query['post__not_in'] = array( absint( $args['exclude_tree'] ) );
		}

		// Get results.
		$posts = new WP_Query( $query );

		// Build array.
		$posts_array = array();
		if ( ! empty( $posts->posts ) ) {
			foreach ( $posts->posts as $post ) {
				$posts_array[] = array(
					'ID'         => $post->ID,
					'post_title' => ( $post->post_parent > 0 ? get_the_title( $post->post_parent ) . ': ' : '' ) . get_the_title( $post->ID ),
				);
			}
		}

		// Done.
		wp_send_json_success( $posts_array );

	}

	/**
	 * Searches for Authors for the given freeform text
	 *
	 * @since   1.8.3
	 */
	public function search_authors() {

		// Verify nonce.
		check_ajax_referer( 'search_authors', 'nonce' );

		// Bail if expected vars are not set.
		if ( ! isset( $_REQUEST['query'] ) ) {
			wp_send_json_error( __( 'Missing expected variables.', 'page-generator-pro' ) );
		}

		// Get vars.
		$query = sanitize_text_field( wp_unslash( $_REQUEST['query'] ) );

		// Get results.
		$users = new WP_User_Query(
			array(
				'search' => '*' . $query . '*',
			)
		);

		// Build array.
		$users_array = array();
		$results     = $users->get_results();
		if ( ! empty( $results ) ) {
			foreach ( $results as $user ) {
				$users_array[] = array(
					'id'         => $user->ID,
					'user_login' => $user->user_login,
				);
			}
		}

		// Done.
		wp_send_json_success( $users_array );

	}

	/**
	 * Research (produce) content for a given topic.
	 *
	 * @since   2.8.9
	 */
	public function research() {

		// Verify nonce.
		check_ajax_referer( 'page-generator-pro-research', 'nonce' );

		// Bail if no content.
		if ( ! isset( $_REQUEST['topic'] ) ) {
			wp_send_json_error( __( 'No topic was defined.', 'page-generator-pro' ) );
		}

		// Get data.
		$topic             = trim( sanitize_text_field( wp_unslash( $_REQUEST['topic'] ) ) );
		$content_type      = isset( $_REQUEST['content_type'] ) ? trim( sanitize_text_field( wp_unslash( $_REQUEST['content_type'] ) ) ) : 'article';
		$user_instructions = isset( $_REQUEST['instructions'] ) ? trim( sanitize_text_field( wp_unslash( $_REQUEST['instructions'] ) ) ) : false;
		$limit             = isset( $_REQUEST['limit'] ) ? trim( sanitize_text_field( wp_unslash( $_REQUEST['limit'] ) ) ) : 250;
		$language          = isset( $_REQUEST['language'] ) ? trim( sanitize_text_field( wp_unslash( $_REQUEST['language'] ) ) ) : 'en';
		$spintax           = isset( $_REQUEST['spintax'] ) ? (bool) trim( sanitize_text_field( wp_unslash( $_REQUEST['spintax'] ) ) ) : false;
		$temperature       = isset( $_REQUEST['temperature'] ) ? (float) trim( sanitize_text_field( wp_unslash( $_REQUEST['temperature'] ) ) ) : 1;
		$top_p             = isset( $_REQUEST['top_p'] ) ? (float) trim( sanitize_text_field( wp_unslash( $_REQUEST['top_p'] ) ) ) : 1;
		$presence_penalty  = isset( $_REQUEST['presence_penalty'] ) ? (float) trim( sanitize_text_field( wp_unslash( $_REQUEST['presence_penalty'] ) ) ) : 0;
		$frequency_penalty = isset( $_REQUEST['frequency_penalty'] ) ? (float) trim( sanitize_text_field( wp_unslash( $_REQUEST['frequency_penalty'] ) ) ) : 0;

		if ( empty( $topic ) ) {
			wp_send_json_error( __( 'No topic was defined.', 'page-generator-pro' ) );
		}

		// Fetch instructions from Settings > Integrations > AI: Instructions and any specified in the Dynamic Element.
		$instructions = array(
			$this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-integrations', 'ai_instructions' ),
			$user_instructions ? $user_instructions : '',
		);

		// Remove empty strings from the array.
		$instructions = array_filter( $instructions );

		// Create instructions string, or set to false if no instructions are provided.
		$instructions = count( $instructions ) > 0 ? implode( "\n", $instructions ) : false;

		// Send request to create content.
		$result = $this->base->get_class( 'research' )->research( $topic, $instructions, $content_type, $limit, $language, $spintax, $temperature, $top_p, $presence_penalty, $frequency_penalty );

		// Return success or error.
		if ( is_wp_error( $result ) ) {
			wp_send_json_error( $result->get_error_message() );
		}

		// Return result.
		wp_send_json_success( $result );

	}

	/**
	 * Gets the status on a research (produce) request.
	 *
	 * @since   2.8.9
	 */
	public function research_get_status() {

		// Verify nonce.
		check_ajax_referer( 'page-generator-pro-research', 'nonce' );

		// Bail if no ID.
		if ( ! isset( $_REQUEST['id'] ) ) {
			wp_send_json_error( __( 'No ID was defined.', 'page-generator-pro' ) );
		}

		$id = trim( sanitize_text_field( wp_unslash( $_REQUEST['id'] ) ) );
		if ( empty( $id ) ) {
			wp_send_json_error( __( 'No ID was defined.', 'page-generator-pro' ) );
		}

		// Send request.
		$result = $this->base->get_class( 'research' )->get_status( $id );

		// Return success or error.
		if ( is_wp_error( $result ) ) {
			wp_send_json_error( $result->get_error_message() );
		}

		// Return result.
		wp_send_json_success( $result );

	}

	/**
	 * Adds spintax to words in the given content.
	 *
	 * @since   1.7.9
	 */
	public function spintax_generate() {

		// Verify nonce.
		check_ajax_referer( 'page-generator-pro-spintax-generate', 'nonce' );

		// Bail if no content.
		if ( ! isset( $_REQUEST['content'] ) ) {
			wp_send_json_error( __( 'No content was selected.', 'page-generator-pro' ) );
		}

		// Strip slashes.
		$content = trim( sanitize_textarea_field( wp_unslash( $_REQUEST['content'] ) ) );

		// If no content exists, bail.
		if ( empty( $content ) ) {
			wp_send_json_error( __( 'No content was selected.', 'page-generator-pro' ) );
		}

		// Add spintax to content.
		$content = $this->base->get_class( 'spintax' )->add_spintax( $content );

		// Return success or error.
		if ( is_wp_error( $content ) ) {
			wp_send_json_error( $content->get_error_message() );
		}

		// Return content.
		wp_send_json_success( $content );

	}

	/**
	 * Loads the view for a shortcode's modal in TinyMCE.
	 *
	 * @since   2.5.1
	 */
	public function output_tinymce_modal() {

		// Verify nonce.
		check_ajax_referer( 'page_generator_pro_tinymce', 'nonce' );

		// Bail if the shortcode isn't set.
		if ( ! isset( $_REQUEST['shortcode'] ) ) {
			wp_send_json_error( __( 'No shortcode was defined.', 'page-generator-pro' ) );
		}

		// Fetch shortcode.
		$shortcode   = $this->base->get_class( 'shortcode' )->get_shortcode( sanitize_text_field( wp_unslash( $_REQUEST['shortcode'] ) ) );
		$editor_type = isset( $_REQUEST['editor_type'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['editor_type'] ) ) : '';

		// If the shortcode is not registered, return a view in the modal to tell the user.
		if ( ! $shortcode ) {
			require_once $this->base->plugin->folder . '/views/admin/tinymce-modal-missing.php';
			die();
		}

		// If we have less than two tabs defined in the shortcode properties, output a basic modal.
		if ( count( $shortcode['tabs'] ) < 2 ) {
			require_once $this->base->plugin->folder . '/views/admin/tinymce-modal.php';
			die();
		}

		// Output tabbed view.
		require_once $this->base->plugin->folder . '/views/admin/tinymce-modal-tabbed.php';
		die();

	}

	/**
	 * Generates one or more Pages, Posts or CPTs for a Content Group, depending on the
	 * index_increment (i.e. number of Pages to generate in this request as a single batch).
	 *
	 * @since   1.6.1
	 */
	public function generate_content() {

		// Validate.
		$group = $this->generate_validation( 'page-generator-pro-generate-browser' );

		// Return single result if in test mode.
		if ( $group['test_mode'] ) {
			return $this->generate_return(
				$this->base->get_class( 'generate' )->generate_content(
					$group['group_id'],
					$group['current_index'],
					$group['test_mode'],
					'browser',
					$group['last_generated_post_date_time']
				)
			);
		}

		// Run.
		$results = array();
		for ( $i = 0; $i < $group['index_increment']; $i++ ) {
			// If this request exceeds the total number of requests that can be made
			// i.e. making this request would be beyond the bounds, exit now.
			if ( ( (int) $group['current_index'] + $i ) >= ( (int) $group['number_requests'] + (int) $group['offset'] ) ) {
				$this->generate_return_results( $results );
			}

			// If this is the first request, use the Last Generated Post Date / Time from the Content Group.
			// Otherwise use from the last generate_content() result.
			if ( empty( $results ) ) {
				$last_generated_post_date_time = $group['last_generated_post_date_time'];
			} elseif ( is_wp_error( $results[ count( $results ) - 1 ] ) ) {
				// The last request in the batch returned an error, so we cannot determine the last generated post date/time.
				$last_generated_post_date_time = $group['last_generated_post_date_time'];
			} else {
				$last_generated_post_date_time = $results[ count( $results ) - 1 ]['last_generated_post_date_time'];
			}

			// Generate.
			$results[] = $this->base->get_class( 'generate' )->generate_content(
				$group['group_id'],
				( $group['current_index'] + $i ),
				$group['test_mode'],
				'browser',
				$last_generated_post_date_time
			);
		}

		// Return.
		$this->generate_return_results( $results );

	}

	/**
	 * Generates one or more Terms for a Term Group, depending on the
	 * index_increment (i.e. number of Pages to generate in this request as a single batch).
	 *
	 * @since   1.6.1
	 */
	public function generate_term() {

		// Validate.
		$group = $this->generate_validation( 'page-generator-pro-generate-browser' );

		// Return single result if in test mode.
		if ( $group['test_mode'] ) {
			return $this->generate_return(
				$this->base->get_class( 'generate' )->generate_term(
					$group['group_id'],
					$group['current_index'],
					$group['test_mode'],
					'browser'
				)
			);
		}

		// Run.
		$results = array();
		for ( $i = 0; $i < $group['index_increment']; $i++ ) {
			// If this request exceeds the total number of requests that can be made
			// i.e. making this request would be beyond the bounds, exit now.
			if ( ( (int) $group['current_index'] + $i ) >= ( (int) $group['number_requests'] + (int) $group['offset'] ) ) {
				$this->generate_return_results( $results );
			}

			// Generate.
			$results[] = $this->base->get_class( 'generate' )->generate_term(
				$group['group_id'],
				( $group['current_index'] + $i ),
				$group['test_mode'],
				'browser'
			);
		}

		// Return.
		$this->generate_return_results( $results );

	}

	/**
	 * Trashes Generated Content
	 *
	 * @since   1.9.1
	 */
	public function trash_generated_content() {

		// Validate.
		$group = $this->generate_validation( 'page-generator-pro-trash-generated-content' );

		// Run.
		$result = $this->base->get_class( 'generate' )->trash_content( $group['group_id'], $this->get_trash_delete_per_request_item_limit() );
		if ( is_wp_error( $result ) ) {
			$this->generate_return( $result );
			die();
		}

		// Determine if there are more Posts in this Content Group that need deleting.
		$remaining_posts = $this->base->get_class( 'generate' )->get_generated_content_post_ids( $group['group_id'] );
		if ( is_wp_error( $remaining_posts ) ) {
			// Error will say there are no more Generated Posts to delete for this Content Group.
			$result = array(
				'has_more' => false,
			);
		} else {
			$result = array(
				'has_more' => true,
			);
		}

		// Return.
		$this->generate_return( $result );

	}

	/**
	 * Deletes Generated Content
	 *
	 * @since   1.8.4
	 */
	public function delete_generated_content() {

		// Validate.
		$group = $this->generate_validation( 'page-generator-pro-delete-generated-content' );

		// Run.
		$result = $this->base->get_class( 'generate' )->delete_content( $group['group_id'], $this->get_trash_delete_per_request_item_limit() );
		if ( is_wp_error( $result ) ) {
			$this->generate_return( $result );
			die();
		}

		// Determine if there are more Posts in this Content Group that need deleting.
		$remaining_posts = $this->base->get_class( 'generate' )->get_generated_content_post_ids( $group['group_id'] );
		if ( is_wp_error( $remaining_posts ) ) {
			// Error will say there are no more Generated Posts to delete for this Content Group.
			$result = array(
				'has_more' => false,
			);

			// Reset the Last Index Generated.
			$this->base->get_class( 'groups' )->update_last_index_generated( $group['group_id'], 0 );
		} else {
			$result = array(
				'has_more' => true,
			);
		}

		// Return.
		$this->generate_return( $result );

	}

	/**
	 * Sets the generating flag on the Group, as Generation has started.
	 *
	 * @since   3.7.0
	 */
	public function before_generated_content() {

		// Validate.
		$group = $this->generate_validation();

		/**
		 * Runs any actions before Generate Content has started.
		 *
		 * @since   3.7.0
		 *
		 * @param   int     $group_id   Group ID.
		 * @param   bool    $test_mode  Test Mode.
		 * @param   string  $system     System.
		 */
		do_action( 'page_generator_pro_generate_content_before', $group['group_id'], false, 'browser' );

		// Run.
		$result = $this->base->get_class( 'groups' )->start_generation( $group['group_id'], 'generating', 'browser' );

		// Return.
		$this->generate_return( $result );

	}

	/**
	 * Removes the generating flag on the Group, as Generation has finished.
	 *
	 * @since   1.9.9
	 */
	public function after_generated_content() {

		// Validate.
		$group = $this->generate_validation();

		/**
		 * Runs any actions after Generate Content has finished.
		 *
		 * @since   3.0.7
		 *
		 * @param   int     $group_id   Group ID.
		 * @param   bool    $test_mode  Test Mode.
		 * @param   string  $system     System.
		 */
		do_action( 'page_generator_pro_generate_content_after', $group['group_id'], false, 'browser' );

		// Run.
		$result = $this->base->get_class( 'groups' )->stop_generation( $group['group_id'] );

		// Return.
		$this->generate_return( $result );

	}

	/**
	 * Deletes Generated Terms
	 *
	 * @since   1.9.5
	 */
	public function delete_generated_terms() {

		// Validate.
		$group = $this->generate_validation( 'page-generator-pro-delete-generated-content' );

		// Run.
		$result = $this->base->get_class( 'generate' )->delete_terms( $group['group_id'], $this->get_trash_delete_per_request_item_limit() );
		if ( is_wp_error( $result ) ) {
			$this->generate_return( $result );
			die();
		}

		// Determine if there are more Terms in this Term Group that need deleting.
		$remaining_terms = $this->base->get_class( 'generate' )->get_generated_term_ids( $group['group_id'] );
		if ( is_wp_error( $remaining_terms ) ) {
			// Error will say there are no more Generated Terms to delete for this Term Group.
			$result = array(
				'has_more' => false,
			);

			// Reset the Last Index Generated.
			$this->base->get_class( 'groups_terms' )->update_last_index_generated( $group['group_id'], 0 );
		} else {
			$result = array(
				'has_more' => true,
			);
		}

		// Return.
		$this->generate_return( $result );

	}

	/**
	 * Sets the generating flag on the Group, as Generation has started.
	 *
	 * @since   3.7.0
	 */
	public function before_generated_terms() {

		// Validate.
		$group = $this->generate_validation();

		/**
		 * Runs any actions after Generate Terms has finished.
		 *
		 * @since   3.7.0
		 *
		 * @param   int     $group_id   Group ID.
		 * @param   bool    $test_mode  Test Mode.
		 * @param   string  $system     System.
		 */
		do_action( 'page_generator_pro_generate_terms_before', $group['group_id'], false, 'browser' );

		// Run.
		$result = $this->base->get_class( 'groups_terms' )->start_generation( $group['group_id'], 'generating', 'browser' );

		// Return.
		$this->generate_return( $result );

	}

	/**
	 * Removes the generating flag on the Group, as Generation has finished.
	 *
	 * @since   1.9.9
	 */
	public function after_generated_terms() {

		// Validate.
		$group = $this->generate_validation();

		/**
		 * Runs any actions after Generate Terms has finished.
		 *
		 * @since   3.0.7
		 *
		 * @param   int     $group_id   Group ID.
		 * @param   bool    $test_mode  Test Mode.
		 * @param   string  $system     System.
		 */
		do_action( 'page_generator_pro_generate_terms_after', $group['group_id'], false, 'browser' );

		// Run.
		$result = $this->base->get_class( 'groups_terms' )->stop_generation( $group['group_id'] );

		// Return.
		$this->generate_return( $result );

	}

	/**
	 * Runs validation when AJAX calls are made to generate content or terms,
	 * returning the Group ID and Current Index.
	 *
	 * @since   1.6.1
	 *
	 * @param   bool|string $action     Nonce Action.
	 * @return  array                   Group ID and Current Index
	 */
	private function generate_validation( $action = false ) {

		// Validate nonce.
		if ( $action ) {
			check_ajax_referer( $action, 'nonce' );
		}

		// Sanitize inputs.
		if ( ! isset( $_POST['id'] ) ) {
			wp_send_json_error( __( 'No group ID was specified.', 'page-generator-pro' ) );
		}

		return array(
			'group_id'                      => absint( $_POST['id'] ),
			'current_index'                 => ( isset( $_POST['current_index'] ) ? absint( $_POST['current_index'] ) : 0 ),
			'index_increment'               => ( isset( $_POST['index_increment'] ) ? absint( $_POST['index_increment'] ) : 1 ),
			'number_requests'               => ( isset( $_POST['number_requests'] ) ? absint( $_POST['number_requests'] ) : 0 ),
			'offset'                        => ( isset( $_POST['offset'] ) ? absint( $_POST['offset'] ) : 0 ),
			'last_generated_post_date_time' => ( isset( $_POST['last_generated_post_date_time'] ) ? sanitize_text_field( wp_unslash( $_POST['last_generated_post_date_time'] ) ) : false ),
			'test_mode'                     => ( isset( $_POST['test_mode'] ) ? true : false ),
		);

	}

	/**
	 * Returns the AJAX result as a JSON error or success
	 *
	 * @since   1.6.1
	 *
	 * @param   WP_Error|array $result     Result.
	 */
	private function generate_return( $result ) {

		// Return error or success JSON.
		if ( is_wp_error( $result ) ) {
			wp_send_json_error( implode( "\n", $result->get_error_messages() ) );
		}

		// If here, run routine worked.
		wp_send_json_success( $result );

	}

	/**
	 * Returns the generation result as a JSON error or success
	 *
	 * @since   4.3.7
	 *
	 * @param   array $results     Results.
	 */
	private function generate_return_results( $results ) {

		// Assume no errors in the generation results.
		$is_error = false;

		// Iterate through generation results to check if an error exists.
		foreach ( $results as $i => $result ) {
			if ( ! is_wp_error( $result ) ) {
				continue;
			}

			// Error detected.
			$is_error      = true;
			$results[ $i ] = implode( "\n", $result->get_error_messages() );
		}

		// If an error was detected, return error JSON.
		if ( $is_error ) {
			wp_send_json_error( $results );
		}

		// Return success.
		wp_send_json_success( $results );

	}

}
