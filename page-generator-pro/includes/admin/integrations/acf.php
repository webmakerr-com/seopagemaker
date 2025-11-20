<?php
/**
 * ACF Integration Class
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Registers Advanced Custom Fields as a Plugin integration:
 * - Register as an overwrite section in Content Groups
 * - Copy / don't copy metadata to generated Pages, depending on if the integration is active
 * - Add Content Groups to ACF's location rules, allowing ACF fields to be displayed on Content Groups
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 2.6.3
 */
class Page_Generator_Pro_ACF extends Page_Generator_Pro_Integration {

	/**
	 * Holds the base object.
	 *
	 * @since   2.6.3
	 *
	 * @var     object
	 */
	public $base;

	/**
	 * Constructor
	 *
	 * @since   2.6.3
	 *
	 * @param   object $base    Base Plugin Class.
	 */
	public function __construct( $base ) {

		// Store base class.
		$this->base = $base;

		// Set Plugin.
		$this->plugin_folder_filename = array(
			'advanced-custom-fields-pro/acf.php',
			'advanced-custom-fields/acf.php',
		);

		// Add Overwrite Section if ACF enabled.
		add_filter( 'page_generator_pro_common_get_content_overwrite_sections', array( $this, 'add_overwrite_section' ) );

		// Ignore ACF meta keys if overwriting is disabled for ACF.
		add_filter( 'page_generator_pro_generate_set_post_meta_ignored_keys', array( $this, 'prevent_post_meta_copy_to_generated_content' ), 10, 4 );

		add_filter( 'acf/location/rule_types', array( $this, 'add_post_type_to_location_rules' ) );

		add_filter( 'acf/location/rule_values/page-generator-pro', array( $this, 'add_content_groups_to_location_rules' ) );
		add_filter( 'acf/location/rule_match/page-generator-pro', array( $this, 'match_content_group_location_rule' ), 10, 4 );

		add_filter( 'acf/location/rule_values/page-generator-tax', array( $this, 'add_term_groups_to_location_rules' ) );
		add_filter( 'acf/location/rule_match/page-generator-tax', array( $this, 'match_term_group_location_rule' ), 10, 4 );

	}

	/**
	 * Defines available content overwrite sections.
	 *
	 * @since   2.9.2
	 *
	 * @param   array $sections    Content Overwrite Sections.
	 * @return  array                Content Overwrite Sections
	 */
	public function add_overwrite_section( $sections ) {

		// Bail if ACF isn't active.
		if ( ! $this->is_active() ) {
			return $sections;
		}

		// Add ACF Groups registered to Content Groups.
		$acf_content_groups = $this->get_acf_content_groups();

		// Bail if no ACF Groups are assigned to Content Groups.
		if ( ! $acf_content_groups ) {
			return $sections;
		}

		// Add ACF Groups.
		foreach ( $acf_content_groups as $group_key => $label ) {
			/* translators: Group Label, defined in ACF Group */
			$sections[ 'acf_' . $group_key ] = sprintf( __( 'ACF: %s', 'page-generator-pro' ), $label );
		}

		// Return.
		return $sections;

	}

	/**
	 * Returns ACF Field Groups assigned to Content Groups
	 *
	 * @since   2.9.2
	 *
	 * @return  bool|array
	 */
	private function get_acf_content_groups() {

		// Get ACF Field Groups.
		$acf_field_groups = acf_get_field_groups();
		if ( ! count( $acf_field_groups ) ) {
			return false;
		}

		// Find ACF Field Groups assigned to Content Groups.
		$matched_groups = array();
		foreach ( $acf_field_groups as $acf_field_group ) {
			foreach ( $acf_field_group['location'] as $group_locations ) {
				foreach ( $group_locations as $rule ) {
					if ( $rule['param'] === 'post_type' && $rule['operator'] === '==' && $rule['value'] === 'page-generator-pro' ) {
						$matched_groups[ $acf_field_group['key'] ] = $acf_field_group['title'];
					}
				}
			}
		}

		// Return false if no ACF Field Groups were found for Content Groups.
		if ( ! count( $matched_groups ) ) {
			return false;
		}

		// Return.
		return $matched_groups;

	}

	/**
	 * Adds ACF Post Meta Keys to the array of excluded Post Meta Keys if ACF
	 * metadata should not be overwritten on Content Generation
	 *
	 * @since   2.9.2
	 *
	 * @param   array $ignored_keys   Ignored Keys (preg_match() compatible regex expressions are supported).
	 * @param   int   $post_id        Generated Post ID.
	 * @param   array $settings       Group Settings.
	 * @param   array $post_args      wp_insert_post() / wp_update_post() arguments.
	 * @return  array   $ignored_keys   Ignored Keys
	 */
	public function prevent_post_meta_copy_to_generated_content( $ignored_keys, $post_id, $settings, $post_args ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter

		// Bail if ACF isn't active.
		if ( ! $this->is_active() ) {
			return $ignored_keys;
		}

		// Bail if we're not overwriting an existing generated Page.
		if ( ! isset( $post_args['ID'] ) ) {
			return $ignored_keys;
		}

		// Get ACF Groups registered to Content Groups.
		$acf_content_groups = $this->get_acf_content_groups();

		// Bail if no ACF Groups are assigned to Content Groups.
		if ( ! $acf_content_groups ) {
			return $ignored_keys;
		}

		// For each ACF Group, ignore its fields if overwriting is disabled.
		foreach ( $acf_content_groups as $group_key => $label ) {
			// Determine if we want to replace this ACF Group's Fields data.
			$overwrite = ( ! array_key_exists( 'acf_' . $group_key, $settings['overwrite_sections'] ) ? false : true );
			if ( $overwrite ) {
				continue;
			}

			// We're not overwriting this ACF Group's Fields, so add the Fields to the $ignored_keys array.
			$fields = acf_get_fields( $group_key );

			// Skip if no fields exist in the ACF Group.
			if ( ! is_array( $fields ) || ! count( $fields ) ) {
				continue;
			}

			// Add ACF Group Fields to ignored keys.
			foreach ( $fields as $field ) {
				$ignored_keys = array_merge(
					$ignored_keys,
					array(
						$field['key'],
						$field['name'],
						'_' . $field['name'],
					)
				);
			}
		}

		return $ignored_keys;

	}

	/**
	 * Register Page Generator Pro Content and Term Groups as Location Rules
	 *
	 * @since   2.6.3
	 *
	 * @param   array $choices    Location Choices.
	 * @return  array               Location Choices
	 */
	public function add_post_type_to_location_rules( $choices ) {

		$choices[ $this->base->plugin->displayName ] = array(
			'page-generator-pro' => __( 'Content Group', 'page-generator-pro' ),
			'page-generator-tax' => __( 'Term Group', 'page-generator-pro' ),
		);

		return $choices;

	}

	/**
	 * Registers all Content Groups as possible values that can be chosen for the Content Group Location Rule
	 *
	 * @since   2.6.3
	 *
	 * @param   array $choices    Content Group Choices.
	 * @return  array               Content Group Choices
	 */
	public function add_content_groups_to_location_rules( $choices ) {

		// Get all Group ID and Names.
		$groups = $this->base->get_class( 'groups' )->get_all_ids_names();
		if ( ! $groups ) {
			return $choices;
		}

		asort( $groups );

		return $groups;

	}

	/**
	 * When a Content Group Location Rule exists on the given Field Group, check that the rule matches
	 * to determine whether the Field Group should display
	 *
	 * @since   2.6.3
	 *
	 * @param   bool       $rule_matches   Rule Matches.
	 * @param   array      $rule           Location Rule.
	 * @param   array      $options        Field Group Options.
	 * @param   bool|array $field_group    Field Group (false if older ACF version, which doesn't include this argument).
	 * @return  bool                       Rule Matches
	 */
	public function match_content_group_location_rule( $rule_matches, $rule, $options, $field_group = false ) {

		global $post;

		// Bail if we can't establish the Post.
		if ( is_null( $post ) ) {
			return $rule_matches;
		}

		switch ( $rule['operator'] ) {
			case '!=':
				$rule_matches = ( $post->ID !== (int) $rule['value'] );
				break;

			case '==':
				$rule_matches = ( $post->ID === (int) $rule['value'] );
				break;

			default:
				$rule_matches = apply_filters( 'page_generator_pro_acf_match_content_group_location_rule', $rule_matches, $rule, $options, $field_group );
				break;
		}

		return $rule_matches;

	}

	/**
	 * Registers all Term Groups as possible values that can be chosen for the Term Group Location Rule
	 *
	 * @since   2.6.3
	 *
	 * @param   array $choices    Term Group Choices.
	 * @return  array               Term Group Choices
	 */
	public function add_term_groups_to_location_rules( $choices ) {

		// Get all Group ID and Names.
		$groups = $this->base->get_class( 'groups_terms' )->get_all_ids_names();
		if ( ! $groups ) {
			return $choices;
		}

		asort( $groups );

		return $groups;

	}

	/**
	 * When a Term Group Location Rule exists on the given Field Group, check that the rule matches
	 * to determine whether the Field Group should display
	 *
	 * @since   2.6.3
	 *
	 * @param   bool       $rule_matches          Rule Matches.
	 * @param   array      $rule           Location Rule.
	 * @param   array      $options        Field Group Options.
	 * @param   bool|array $field_group    Field Group (false if older ACF version, which doesn't include this argument).
	 * @return  bool                        Rule Matches
	 */
	public function match_term_group_location_rule( $rule_matches, $rule, $options, $field_group = false ) {

		// Bail if we can't establish the Term.
		if ( ! isset( $_REQUEST['tag_ID'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			return $rule_matches;
		}
		if ( ! isset( $_REQUEST['taxonomy'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			return $rule_matches;
		}

		// Get Taxonomy and Term ID.
		$taxonomy = sanitize_text_field( wp_unslash( $_REQUEST['taxonomy'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
		$term_id  = absint( $_REQUEST['tag_ID'] ); // phpcs:ignore WordPress.Security.NonceVerification

		// Bail if not a Term Group.
		if ( $taxonomy !== $this->base->get_class( 'taxonomy' )->taxonomy_name ) {
			return $rule_matches;
		}

		switch ( $rule['operator'] ) {
			case '!=':
				$rule_matches = ( $term_id !== (int) $rule['value'] );
				break;

			case '==':
				$rule_matches = ( $term_id === (int) $rule['value'] );
				break;

			default:
				$rule_matches = apply_filters( 'page_generator_pro_acf_match_term_group_location_rule', $rule_matches, $rule, $options, $field_group, $term_id );
				break;
		}

		return $rule_matches;

	}

}
