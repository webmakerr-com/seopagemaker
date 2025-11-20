<?php
/**
 * Term Groups UI Class
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Handles Term Groups Taxonomy Type's UI for creating
 * and editing Term Groups.
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 2.0.2
 */
class Page_Generator_Pro_Groups_Terms_UI {

	/**
	 * Holds the base class object.
	 *
	 * @since   2.0.2
	 *
	 * @var     object
	 */
	public $base;

	/**
	 * Stores Keywords available to the Group
	 *
	 * @since   2.0.2
	 *
	 * @var     array
	 */
	public $keywords;

	/**
	 * Stores a Group's settings
	 *
	 * @since   2.0.2
	 *
	 * @var     array
	 */
	public $settings = array();

	/**
	 * Constructor.
	 *
	 * @since   2.0.2
	 *
	 * @param   object $base    Base Plugin Class.
	 */
	public function __construct( $base ) {

		// Store base class.
		$this->base = $base;

		// Don't allow Group Editing if a Group is Generating Content.
		add_filter( 'user_has_cap', array( $this, 'maybe_prevent_group_edit' ), 10, 3 );

		// Register wp_editor() on all Taxonomy Descriptions.
		add_action( 'admin_init', array( $this, 'register_wp_editor_on_taxonomy_description' ) );

		// Add Taxonomy Form.
		add_action( 'page-generator-tax_add_form_fields', array( $this, 'output_add_term_fields' ), 10, 1 );

		// Edit Taxonomy Form.
		add_action( 'page-generator-tax_term_edit_form_top', array( $this, 'output_edit_term_start' ), 9, 2 );
		add_action( 'page-generator-tax_edit_form_fields', array( $this, 'output_edit_term_fields' ), 10, 2 );
		add_action( 'page-generator-tax_edit_form', array( $this, 'output_edit_term_end' ), 13, 2 );

		// Save Settings .
		add_action( 'create_term', array( $this, 'create_term' ), 10, 3 );
		add_action( 'edit_term', array( $this, 'edit_term' ), 10, 3 );

	}

	/**
	 * Prevent loading of editing a Group if that Group is Generating Content.
	 *
	 * Sets the capability to false when current_user_can() has been called on
	 * one of the capabilities we're interested in on a locked or protected post.
	 *
	 * @since   2.0.2
	 *
	 * @param   array $all_caps All capabilities of the user.
	 * @param   array $cap      [0] Required capability.
	 * @param   array $args     [0] Requested capability.
	 *                          [1] User ID.
	 *                          [2] Post ID.
	 */
	public function maybe_prevent_group_edit( $all_caps, $cap, $args ) {

		// Let the request through if it doesn't contain the required arguments.
		if ( ! isset( $args[2] ) ) {
			return $all_caps;
		}

		// Fetch the Capability the User requires, and the Group ID.
		$capability = $args[0];
		$group_id   = $args[2];

		// If the capability the User requires isn't one that we need to modify, let the request through.
		$capabilities_to_disable = $this->base->get_class( 'common' )->get_capabilities_to_disable_on_group_term_generation();
		if ( ! in_array( $capability, $capabilities_to_disable, true ) ) {
			return $all_caps;
		}

		// Fetch Term, letting the request through if an error occured.
		$term = get_term( $group_id );
		if ( is_wp_error( $term ) || is_null( $term ) ) {
			return $all_caps;
		}

		// If the Group ID doesn't correspond to a Group (i.e. it's a capability for a different Taxonomy Term), let the request through.
		if ( $term->taxonomy !== $this->base->get_class( 'taxonomy' )->taxonomy_name ) {
			return $all_caps;
		}

		// If the Group isn't generating content, let the request through.
		if ( $this->base->get_class( 'groups_terms' )->is_idle( $group_id ) ) {
			return $all_caps;
		}

		// If here, the Group is generating content, and the capability requested needs to be temporarily disabled.
		$all_caps[ $cap[0] ] = false;

		// Return.
		return $all_caps;

	}

	/**
	 * Outputs additional fields on the Add Taxonomy Term Form
	 *
	 * @since   2.0.2
	 *
	 * @param   string $taxonomy   Taxonomy.
	 */
	public function output_add_term_fields( $taxonomy ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter

		// Get options.
		$taxonomies = $this->base->get_class( 'common' )->get_taxonomies();

		// Output view.
		require_once $this->base->plugin->folder . '/views/admin/generate-taxonomy-add-fields.php';

	}

	/**
	 * Registers the action call to output the wp_editor() for the Description field on all
	 * registered Taxonomies.
	 *
	 * @since   2.2.4
	 */
	public function register_wp_editor_on_taxonomy_description() {

		// Get all Public Taxonomies.
		$taxonomies = $this->base->get_class( 'common' )->get_taxonomies();

		// Add this Plugin's Taxonomy.
		$taxonomies   = array_keys( $taxonomies );
		$taxonomies[] = $this->base->get_class( 'taxonomy' )->taxonomy_name;

		// Register wp_editor() output when adding and editing a term.
		foreach ( $taxonomies as $taxonomy_name ) {
			add_action( $taxonomy_name . '_add_form_fields', array( $this, 'add_term_wp_editor' ), 10, 1 );
			add_action( $taxonomy_name . '_edit_form_fields', array( $this, 'edit_term_wp_editor' ), 10, 2 );
		}

	}

	/**
	 * Outputs the wp_editor() when adding a Taxonomy Term
	 *
	 * @since   2.2.4
	 *
	 * @param   string $taxonomy   Taxonomy.
	 */
	public function add_term_wp_editor( $taxonomy ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter

		// Output view.
		require_once $this->base->plugin->folder . '/views/admin/add-term-wp-editor.php';

	}

	/**
	 * Outputs the wp_editor() when editing a Taxonomy Term
	 *
	 * @since   2.2.4
	 *
	 * @param   WP_Term $term       Term.
	 * @param   string  $taxonomy   Taxonomy.
	 */
	public function edit_term_wp_editor( $term, $taxonomy ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter

		// Output view.
		require_once $this->base->plugin->folder . '/views/admin/edit-term-wp-editor.php';

	}

	/**
	 * Outputs the HTML at the top of the Edit Taxonomy Term Form
	 *
	 * @since   2.0.2
	 *
	 * @param   WP_Term $term       Term.
	 * @param   string  $taxonomy   Taxonomy.
	 */
	public function output_edit_term_start( $term, $taxonomy ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter

		// Define Group ID.
		$group_id = $term->term_id;

		// Get settings.
		if ( count( $this->settings ) === 0 ) {
			$this->settings = $this->base->get_class( 'groups_terms' )->get_settings( $term->term_id );
		}

		// Get options.
		$methods           = $this->base->get_class( 'common' )->get_methods();
		$overwrite_methods = $this->base->get_class( 'common' )->get_overwrite_methods( 'terms' );

		// Define labels.
		$labels = array(
			'singular' => __( 'Term', 'page-generator-pro' ),
			'plural'   => __( 'Terms', 'page-generator-pro' ),
		);

		// Get limit.
		$limit = $this->base->get_class( 'ajax' )->get_trash_delete_per_request_item_limit();

		// Output view.
		require_once $this->base->plugin->folder . '/views/admin/generate-taxonomy-edit-wrap-start.php';

	}

	/**
	 * Outputs additional fields on the Edit Taxonomy Term Form
	 *
	 * @since   2.0.2
	 *
	 * @param   WP_Term $term       Term.
	 * @param   string  $taxonomy   Taxonomy.
	 */
	public function output_edit_term_fields( $term, $taxonomy ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter

		// Get settings.
		if ( count( $this->settings ) === 0 ) {
			$this->settings = $this->base->get_class( 'groups_terms' )->get_settings( $term->term_id );
		}

		// Get options.
		$taxonomies = $this->base->get_class( 'common' )->get_taxonomies();

		// Output view.
		require_once $this->base->plugin->folder . '/views/admin/generate-taxonomy-edit-fields.php';

	}

	/**
	 * Outputs the HTML at the bottom of the Edit Taxonomy Term Form
	 *
	 * @since   2.0.2
	 *
	 * @param   WP_Term $term       Term.
	 * @param   string  $taxonomy   Taxonomy.
	 */
	public function output_edit_term_end( $term, $taxonomy ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter

		// Define Group ID.
		$group_id = $term->term_id;

		// Get limit.
		$limit = $this->base->get_class( 'ajax' )->get_trash_delete_per_request_item_limit();

		require_once $this->base->plugin->folder . '/views/admin/generate-taxonomy-edit-wrap-end.php';

	}

	/**
	 * Called when a Taxonomy Group is created.
	 *
	 * @since   3.0.0
	 *
	 * @param   int    $term_id            Term ID.
	 * @param   int    $taxonomy_term_id   Taxonomy Term ID.
	 * @param   string $taxonomy           Taxonomy.
	 */
	public function create_term( $term_id, $taxonomy_term_id, $taxonomy ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter

		// Bail if this isn't a Page Generator Taxonomy that's being saved.
		if ( $taxonomy !== $this->base->get_class( 'taxonomy' )->taxonomy_name ) {
			return;
		}

		// Run security checks.
		// Missing nonce .
		if ( ! isset( $_POST[ $this->base->plugin->name . '_nonce' ] ) ) {
			return;
		}

		// Invalid nonce.
		if ( ! wp_verify_nonce( sanitize_key( $_POST[ $this->base->plugin->name . '_nonce' ] ), 'save_generate' ) ) {
			return;
		}

		// Build the settings.
		$settings = array(
			'title'       => ( isset( $_POST['tag-name'] ) ? sanitize_text_field( wp_unslash( $_POST['tag-name'] ) ) : '' ),
			'permalink'   => ( isset( $_POST['slug'] ) ? sanitize_text_field( wp_unslash( $_POST['slug'] ) ) : '' ),
			'excerpt'     => ( isset( $_POST['description'] ) ? sanitize_text_field( wp_unslash( $_POST['description'] ) ) : '' ),
			'taxonomy'    => ( isset( $_POST['tax'] ) ? sanitize_text_field( wp_unslash( $_POST['tax'] ) ) : '' ),
			'parent_term' => ( isset( $_POST['parent_term'] ) ? sanitize_text_field( wp_unslash( $_POST['parent_term'] ) ) : '' ),
		);

		// Call the main save function.
		$this->base->get_class( 'groups_terms' )->save( $settings, $term_id );

	}

	/**
	 * Called when a Taxonomy Group is updated.
	 *
	 * @since   2.0.2
	 *
	 * @param   int    $term_id            Term ID.
	 * @param   int    $taxonomy_term_id   Taxonomy Term ID.
	 * @param   string $taxonomy           Taxonomy.
	 */
	public function edit_term( $term_id, $taxonomy_term_id, $taxonomy ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter

		// Bail if this isn't a Page Generator Taxonomy that's being saved.
		if ( $taxonomy !== $this->base->get_class( 'taxonomy' )->taxonomy_name ) {
			return;
		}

		// Run security checks.
		// Missing nonce .
		if ( ! isset( $_POST[ $this->base->plugin->name . '_nonce' ] ) ) {
			return;
		}

		// Invalid nonce.
		if ( ! wp_verify_nonce( sanitize_key( $_POST[ $this->base->plugin->name . '_nonce' ] ), 'save_generate' ) ) {
			return;
		}

		// Bail if the POST data isn't set.
		if ( ! isset( $_POST[ $this->base->plugin->name ] ) ) {
			return;
		}

		// Build the settings.
		$settings = array_merge(
			array(
				'title'       => ( isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '' ),
				'permalink'   => ( isset( $_POST['slug'] ) ? sanitize_text_field( wp_unslash( $_POST['slug'] ) ) : '' ),
				'excerpt'     => ( isset( $_POST['description'] ) ? sanitize_text_field( wp_unslash( $_POST['description'] ) ) : '' ),
				'taxonomy'    => ( isset( $_POST['tax'] ) ? sanitize_text_field( wp_unslash( $_POST['tax'] ) ) : '' ),
				'parent_term' => ( isset( $_POST['parent_term'] ) ? sanitize_text_field( wp_unslash( $_POST['parent_term'] ) ) : '' ),
			),
			wp_unslash( $_POST[ $this->base->plugin->name ] ) // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		);

		// Call the main save function.
		$this->base->get_class( 'groups_terms' )->save( $settings, $term_id );

		// Check which submit action was given, as we may need to run a test or redirect to the generate screen now.
		$action = $this->get_action();
		if ( ! $action ) {
			return;
		}

		// Maybe run an action on the Group now.
		$redirect = ( $action === 'generate_server' ? true : false );
		$this->base->get_class( 'groups_terms' )->run_action( $action, $term_id, $redirect );

	}

	/**
	 * Returns the localized title
	 *
	 * @since   2.0.2
	 *
	 * @param   string $key    Key.
	 * @return  string          Message
	 */
	public function get_title( $key ) {

		// Get Titles and Messages.
		$titles_messages = $this->get_titles_and_messages();

		// Bail if no Titles exist.
		if ( ! isset( $titles_messages['titles'] ) ) {
			return '';
		}

		// Bail if the Title does not exist.
		if ( ! isset( $titles_messages['titles'][ $key ] ) ) {
			return '';
		}

		// Return the title.
		return $titles_messages['titles'][ $key ];

	}

	/**
	 * Returns the localized message
	 *
	 * @since   2.0.2
	 *
	 * @param   string $key    Key.
	 * @return  string          Message
	 */
	public function get_message( $key ) {

		// Get Titles and Messages.
		$titles_messages = $this->get_titles_and_messages();

		// Bail if no Messages exist.
		if ( ! isset( $titles_messages['messages'] ) ) {
			return '';
		}

		// Bail if the Message does not exist.
		if ( ! isset( $titles_messages['messages'][ $key ] ) ) {
			return '';
		}

		// Return the message.
		return $titles_messages['messages'][ $key ];

	}

	/**
	 * Returns Titles and Messages that are used for Content Generation,
	 * which are displayed in various notifications.
	 *
	 * @since   2.0.2
	 *
	 * @return  array   Titles and Messages
	 */
	public function get_titles_and_messages() {

		// Define localizations.
		$localization = array(
			'titles'   => array(
				'duplicate'                => __( 'Duplicate', 'page-generator-pro' ),
				'test'                     => __( 'Test', 'page-generator-pro' ),
				'generate'                 => __( 'Generate via Browser', 'page-generator-pro' ),
				'generate_server'          => __( 'Generate via Server', 'page-generator-pro' ),
				'delete_generated_content' => __( 'Delete Generated Terms', 'page-generator-pro' ),
				'cancel_generation'        => __( 'Cancel Generation', 'page-generator-pro' ),
			),

			'messages' => array(
				// Generate.
				'generate_confirm'                 => __( 'This will generate all Terms. Proceed?', 'page-generator-pro' ),

				// Generate via Server.
				'generate_server_confirm'          => __( 'This will generate all Terms using WordPress\' CRON to offload the process to the server. Proceed?', 'page-generator-pro' ),
				'generate_server_success'          => __( 'Queued for Generation via WordPress\' CRON. This may take a minute or two to begin.', 'page-generator-pro' ),

				// Cancel Generation.
				'cancel_generation_confirm'        => __( 'This will cancel Term Generation, allowing the Group to be edited.  Proceed?', 'page-generator-pro' ),
				'cancel_generation_success'        => __( 'Generation Cancelled', 'page-generator-pro' ),

				// Duplicate.
				'duplicate_success'                => __( 'Group Duplicated', 'page-generator-pro' ),

				// Test.
				'test_confirm'                     => __( 'This will generate a single Term. Proceed?', 'page-generator-pro' ),
				'test'                             => __( 'Generating Test Term...', 'page-generator-pro' ),
				/* Translators: URL to Term */
				'test_success'                     => __( 'Test Term Generated at %s', 'page-generator-pro' ),

				// Delete Generated Content.
				/* translators: Number of Terms to delete */
				'delete_generated_content_confirm' => __( 'This will PERMANENTLY DELETE ALL %s Terms generated by this group. This action cannot be undone. Proceed?', 'page-generator-pro' ),
				'delete_generated_content'         => __( 'Deleting Generated Terms...', 'page-generator-pro' ),
				'delete_generated_content_success' => __( 'Generated Terms Deleted', 'page-generator-pro' ),
			),
		);

		/**
		 * Filters the localization title and message strings used for Generation.
		 *
		 * @since   2.0.2
		 *
		 * @param   array   $localization   Titles and Messages.
		 */
		$localization = apply_filters( 'page_generator_pro_groups_terms_ui_get_titles_and_messages', $localization );

		// Return.
		return $localization;

	}

	/**
	 * Determines which submit button was pressed on the Groups add/edit screen
	 *
	 * @since   2.0.2
	 *
	 * @return  bool|string  Action
	 */
	private function get_action() {

		if ( isset( $_POST['test'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			return 'test';
		}

		if ( isset( $_POST['generate'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			return 'generate';
		}

		if ( isset( $_POST['generate_server'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			return 'generate_server';
		}

		if ( isset( $_POST['delete_generated_content'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			return 'delete_generated_content';
		}

		if ( isset( $_POST['cancel_generation'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			return 'cancel_generation';
		}

		if ( isset( $_POST['save'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			return 'save';
		}

		// No action given.
		return false;

	}

}
