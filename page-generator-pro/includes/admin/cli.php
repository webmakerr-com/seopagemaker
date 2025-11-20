<?php
/**
 * WP-CLI Class
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Registers CLI commands for this Plugin.
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 3.6.3
 */
class Page_Generator_Pro_CLI {

	/**
	 * Holds the base class object.
	 *
	 * @since   3.6.3
	 *
	 * @var     object
	 */
	public $base;

	/**
	 * Register CLI commands.
	 *
	 * @since   3.6.3
	 *
	 * @param   object $base    Base Plugin Class.
	 */
	public function __construct( $base ) {

		// Store base class.
		$this->base = $base;

		// Require CLI class files.
		require_once $this->base->plugin->folder . '/includes/admin/cli/delete-generated-content.php';
		require_once $this->base->plugin->folder . '/includes/admin/cli/delete-generated-terms.php';
		require_once $this->base->plugin->folder . '/includes/admin/cli/generate-content.php';
		require_once $this->base->plugin->folder . '/includes/admin/cli/generate-terms.php';
		require_once $this->base->plugin->folder . '/includes/admin/cli/list-content.php';
		require_once $this->base->plugin->folder . '/includes/admin/cli/list-terms.php';
		require_once $this->base->plugin->folder . '/includes/admin/cli/test-content.php';
		require_once $this->base->plugin->folder . '/includes/admin/cli/test-terms.php';
		require_once $this->base->plugin->folder . '/includes/admin/cli/trash-generated-content.php';

		// Generate Content.
		// Backward compat command.
		WP_CLI::add_command(
			'page-generator-pro-generate',
			'Page_Generator_Pro_CLI_Generate_Content',
			array(
				'shortdesc' => __( 'Generates Pages / Posts / Custom Post Types for the given Generate Group ID.', 'page-generator-pro' ),
				'synopsis'  => array(
					array(
						'type'     => 'positional',
						'name'     => 'group_id',
						'optional' => false,
						'multiple' => false,
					),
					array(
						'type'     => 'assoc',
						'name'     => 'number_of_posts',
						'optional' => true,
						'multiple' => false,
					),
					array(
						'type'     => 'assoc',
						'name'     => 'resume_index',
						'optional' => true,
						'multiple' => false,
					),
				),
				'when'      => 'before_wp_load',
			)
		);

		// Test Content.
		// Backward compat command.
		WP_CLI::add_command(
			'page-generator-pro-test',
			'Page_Generator_Pro_CLI_Test_Content',
			array(
				'shortdesc' => __( 'Generates one Page / Post / CPT for the given Generate Group ID, storing it as a Draft. Use this to test your settings.', 'page-generator-pro' ),
				'synopsis'  => array(
					array(
						'type'     => 'positional',
						'name'     => 'group_id',
						'optional' => false,
						'multiple' => false,
					),
				),
				'when'      => 'before_wp_load',
			)
		);

		// Generate Content.
		WP_CLI::add_command(
			'page-generator-pro-generate-content',
			'Page_Generator_Pro_CLI_Generate_Content',
			array(
				'shortdesc' => __( 'Generates Pages / Posts / Custom Post Types for the given Generate Group ID.', 'page-generator-pro' ),
				'synopsis'  => array(
					array(
						'type'     => 'positional',
						'name'     => 'group_id',
						'optional' => false,
						'multiple' => false,
					),
					array(
						'type'     => 'assoc',
						'name'     => 'number_of_posts',
						'optional' => true,
						'multiple' => false,
					),
					array(
						'type'     => 'assoc',
						'name'     => 'resume_index',
						'optional' => true,
						'multiple' => false,
					),
				),
				'when'      => 'before_wp_load',
			)
		);

		// Test Content.
		WP_CLI::add_command(
			'page-generator-pro-test-content',
			'Page_Generator_Pro_CLI_Test_Content',
			array(
				'shortdesc' => __( 'Generates one Page / Post / CPT for the given Generate Group ID, storing it as a Draft. Use this to test your settings.', 'page-generator-pro' ),
				'synopsis'  => array(
					array(
						'type'     => 'positional',
						'name'     => 'group_id',
						'optional' => false,
						'multiple' => false,
					),
				),
				'when'      => 'before_wp_load',
			)
		);

		// Trash Content.
		WP_CLI::add_command(
			'page-generator-pro-trash-generated-content',
			'Page_Generator_Pro_CLI_Trash_Generated_Content',
			array(
				'shortdesc' => __( 'Trashes all generated content for the given Group ID.', 'page-generator-pro' ),
				'synopsis'  => array(
					array(
						'type'     => 'positional',
						'name'     => 'group_id',
						'optional' => false,
						'multiple' => false,
					),
					array(
						'type'     => 'assoc',
						'name'     => 'exclude_post_ids',
						'optional' => true,
						'multiple' => false,
					),
				),
				'when'      => 'before_wp_load',
			)
		);

		// Delete Content.
		WP_CLI::add_command(
			'page-generator-pro-delete-generated-content',
			'Page_Generator_Pro_CLI_Delete_Generated_Content',
			array(
				'shortdesc' => __( 'Deletes all generated content for the given Group ID.', 'page-generator-pro' ),
				'synopsis'  => array(
					array(
						'type'     => 'positional',
						'name'     => 'group_id',
						'optional' => false,
						'multiple' => false,
					),
					array(
						'type'     => 'assoc',
						'name'     => 'exclude_post_ids',
						'optional' => true,
						'multiple' => false,
					),
				),
				'when'      => 'before_wp_load',
			)
		);

		// List Content Groups.
		WP_CLI::add_command(
			'page-generator-pro-list-content-groups',
			'Page_Generator_Pro_CLI_List_Content_Groups',
			array(
				'shortdesc' => __( 'Lists all Content Groups in the CLI.', 'page-generator-pro' ),
				'when'      => 'before_wp_load',
			)
		);

		// Generate Terms.
		WP_CLI::add_command(
			'page-generator-pro-generate-terms',
			'Page_Generator_Pro_CLI_Generate_Terms',
			array(
				'shortdesc' => __( 'Generates Terms for the given Generate Group ID.', 'page-generator-pro' ),
				'synopsis'  => array(
					array(
						'type'     => 'positional',
						'name'     => 'group_id',
						'optional' => false,
						'multiple' => false,
					),
					array(
						'type'     => 'assoc',
						'name'     => 'number_of_terms',
						'optional' => true,
						'multiple' => false,
					),
					array(
						'type'     => 'assoc',
						'name'     => 'resume_index',
						'optional' => true,
						'multiple' => false,
					),
				),
				'when'      => 'before_wp_load',
			)
		);

		// Test Terms.
		WP_CLI::add_command(
			'page-generator-pro-test-terms',
			'Page_Generator_Pro_CLI_Test_Terms',
			array(
				'shortdesc' => __( 'Generates one Term for the given Generate Group ID. Use this to test your settings.', 'page-generator-pro' ),
				'synopsis'  => array(
					array(
						'type'     => 'positional',
						'name'     => 'group_id',
						'optional' => false,
						'multiple' => false,
					),
				),
				'when'      => 'before_wp_load',
			)
		);

		// Delete Terms.
		WP_CLI::add_command(
			'page-generator-pro-delete-generated-terms',
			'Page_Generator_Pro_CLI_Delete_Generated_Terms',
			array(
				'shortdesc' => __( 'Deletes all generated terms for the given Group ID', 'page-generator-pro' ),
				'synopsis'  => array(
					array(
						'type'     => 'positional',
						'name'     => 'group_id',
						'optional' => false,
						'multiple' => false,
					),
					array(
						'type'     => 'assoc',
						'name'     => 'exclude_term_ids',
						'optional' => true,
						'multiple' => false,
					),
				),
				'when'      => 'before_wp_load',
			)
		);

		// List Term Groups.
		WP_CLI::add_command(
			'page-generator-pro-list-term-groups',
			'Page_Generator_Pro_CLI_List_Term_Groups',
			array(
				'shortdesc' => __( 'Lists all Term Groups in the CLI.', 'page-generator-pro' ),
				'when'      => 'before_wp_load',
			)
		);

	}

}
