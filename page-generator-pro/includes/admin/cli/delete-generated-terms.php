<?php
/**
 * Delete Generated Terms CLI Class.
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * WP-CLI Command: Delete Generated Terms
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 1.7.6
 */
class Page_Generator_Pro_CLI_Delete_Generated_Terms {

	/**
	 * Deletes all generated content for the given Group ID
	 *
	 * @since   1.7.6
	 *
	 * @param   array $args           Group ID (123) or Group IDs (123,456).
	 * @param   array $arguments      Array of associative arguments.
	 */
	public function __invoke( $args, $arguments ) {

		// Sanitize inputs.
		$arguments['group_id'] = absint( $args[0] );

		// Determine if any Term IDs need to be excluded from deletion.
		$exclude_term_ids = false;
		if ( isset( $arguments['exclude_term_ids'] ) && ! empty( $arguments['exclude_term_ids'] ) ) {
			$exclude_term_ids = explode( ',', $arguments['exclude_term_ids'] );
		}

		// Run.
		$start  = ( function_exists( 'hrtime' ) ? hrtime( true ) : microtime( true ) );
		$result = Page_Generator_Pro()->get_class( 'generate' )->delete_terms( $arguments['group_id'], 999999, $exclude_term_ids );
		$end    = ( function_exists( 'hrtime' ) ? hrtime( true ) : microtime( true ) );

		// Output success or error.
		if ( is_wp_error( $result ) ) {
			WP_CLI::error( $result->get_error_message() );
		} else {
			$data = array(
				'start'             => $start,
				'end'               => $end,
				'duration'          => ( function_exists( 'hrtime' ) ? round( ( ( $end - $start ) / 1e+9 ), 3 ) : round( ( $end - $start ), 2 ) ),
				'memory_usage'      => round( memory_get_usage() / 1024 / 1024 ),
				'memory_peak_usage' => round( memory_get_peak_usage() / 1024 / 1024 ),
			);

			// Build message and output.
			$message = array(
				'Group ID #' . $arguments['group_id'] . ': Deleted Generated Terms in ' . $data['duration'] . ' seconds.  Memory Usage / Peak: ' . $data['memory_usage'] . '/' . $data['memory_peak_usage'] . 'MB',
			);
			WP_CLI::success( implode( "\n", $message ) );
		}

	}

}
