<?php
/**
 * Generate Content CLI Class.
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * WP-CLI Command: Generate Content
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 1.2.1
 */
class Page_Generator_Pro_CLI_Generate_Content {

	/**
	 * Generates Pages, Posts or CPTs based on the given group's settings
	 *
	 * @since   1.2.1
	 *
	 * @param   array $args           Group ID (123) or Group IDs (123,456).
	 * @param   array $arguments      Array of associative arguments.
	 */
	public function __invoke( $args, $arguments ) {

		WP_CLI::log( 'Generate: Content: Started' );

		// Add the group ID(s) to the associative arguments.
		if ( strpos( $args[0], ',' ) !== false ) {
			$arguments['group_id'] = explode( ',', $args[0] );
		} else {
			$arguments['group_id'] = absint( $args[0] );
		}

		// If the group_id argument is an array, we're generating multiple groups.
		if ( is_array( $arguments['group_id'] ) ) {
			foreach ( $arguments['group_id'] as $group_id ) {
				// Cast Group ID.
				$group_id = absint( $group_id );

				WP_CLI::do_hook( 'page_generator_pro_generate_content_before', $group_id, false, 'cli' );
				$this->generate( $group_id, $arguments );
				WP_CLI::do_hook( 'page_generator_pro_generate_content_after', $group_id, false, 'cli' );
			}
		} else {
			WP_CLI::do_hook( 'page_generator_pro_generate_content_before', $arguments['group_id'], false, 'cli' );
			$this->generate( $arguments['group_id'], $arguments );
			WP_CLI::do_hook( 'page_generator_pro_generate_content_after', $arguments['group_id'], false, 'cli' );
		}

		WP_CLI::log( 'Generate: Content: Finished' );

	}

	/**
	 * Generates Pages, Posts or CPTs based on the given group's settings
	 *
	 * @since   1.5.3
	 *
	 * @param   int   $group_id       Group ID.
	 * @param   array $arguments      Array of associative arguments.
	 */
	private function generate( $group_id, $arguments ) {

		Page_Generator_Pro()->get_class( 'generate' )->generate(
			$group_id,
			'content',
			( isset( $arguments['resume_index'] ) ? absint( $arguments['resume_index'] ) : 0 ),
			( isset( $arguments['number_of_posts'] ) ? absint( $arguments['number_of_posts'] ) : 0 ),
			false,
			'cli'
		);

	}

}
