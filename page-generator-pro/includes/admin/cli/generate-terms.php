<?php
/**
 * Generate Terms CLI Class.
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * WP-CLI Command: Generate Terms
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 1.6.1
 */
class Page_Generator_Pro_CLI_Generate_Terms {

	/**
	 * Generates Terms based on the given group's settings
	 *
	 * @since   1.6.1
	 *
	 * @param   array $args           Group ID (123) or Group IDs (123,456).
	 * @param   array $arguments      Array of associative arguments.
	 */
	public function __invoke( $args, $arguments ) {

		WP_CLI::log( 'Generate: Terms: Started' );

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

				WP_CLI::do_hook( 'page_generator_pro_generate_terms_before', $group_id, false, 'cli' );
				$this->generate_terms( $group_id, $arguments );
				WP_CLI::do_hook( 'page_generator_pro_generate_terms_after', $group_id, false, 'cli' );
			}
		} else {
			WP_CLI::do_hook( 'page_generator_pro_generate_terms_before', $arguments['group_id'], false, 'cli' );
			$this->generate_terms( $arguments['group_id'], $arguments );
			WP_CLI::do_hook( 'page_generator_pro_generate_terms_after', $arguments['group_id'], false, 'cli' );
		}

		WP_CLI::log( 'Generate: Terms: Finished' );

	}

	/**
	 * Generates Terms based on the given group's settings
	 *
	 * @since   1.6.1
	 *
	 * @param   int   $group_id       Group ID.
	 * @param   array $arguments      Array of associative arguments.
	 */
	private function generate_terms( $group_id, $arguments ) {

		Page_Generator_Pro()->get_class( 'generate' )->generate(
			$group_id,
			'term',
			( isset( $arguments['resume_index'] ) ? absint( $arguments['resume_index'] ) : 0 ),
			( isset( $arguments['number_of_terms'] ) ? absint( $arguments['number_of_terms'] ) : 0 ),
			false,
			'cli'
		);

	}

}
