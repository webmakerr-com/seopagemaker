<?php
/**
 * Test Terms CLI Class.
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * WP-CLI Command: Test Terms
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 1.6.1
 */
class Page_Generator_Pro_CLI_Test_Terms {

	/**
	 * Generates one Term based on the given group's settings
	 *
	 * @since   1.6.1
	 *
	 * @param   array $args   Array of arguments (group ID, current index).
	 */
	public function __invoke( $args ) {

		Page_Generator_Pro()->get_class( 'generate' )->generate(
			absint( $args[0] ),
			'term',
			0,
			1,
			true,
			'cli'
		);

	}

}
