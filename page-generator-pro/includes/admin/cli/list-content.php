<?php
/**
 * List Content CLI Class.
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * WP-CLI Command: List Groups
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 1.2.1
 */
class Page_Generator_Pro_CLI_List_Content_Groups {

	/**
	 * Lists all Page Generator Pro Groups in table format within the CLI
	 *
	 * @since   1.2.1
	 */
	public function __invoke() {

		// Get all Groups.
		$groups = Page_Generator_Pro()->get_class( 'groups' )->get_all();

		// Build array for WP-CLI Table.
		$groups_table = array();
		foreach ( $groups as $group_id => $group ) {
			$groups_table[] = array(
				'ID'                    => $group_id,
				'title'                 => $group['title'],
				'description'           => $group['description'],
				'generated_pages_count' => $group['generated_pages_count'],
			);
		}

		// Output.
		\WP_CLI\Utils\format_items( 'table', $groups_table, array_keys( $groups_table[0] ) );

	}

}
