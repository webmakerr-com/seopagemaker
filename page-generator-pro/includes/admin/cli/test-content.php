<?php
/**
 * Test Content CLI Class.
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * WP-CLI Command: Test Content
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 1.2.1
 */
class Page_Generator_Pro_CLI_Test_Content {

	/**
	 * Generates one Page in Draft mode based on the given group's settings
	 *
	 * @since   1.2.1
	 *
	 * @param   array $args   Array of arguments (group ID, current index).
	 */
	public function __invoke( $args ) {

		Page_Generator_Pro()->get_class( 'generate' )->generate(
			absint( $args[0] ),
			'content',
			0,
			1,
			true,
			'cli'
		);

	}

}
