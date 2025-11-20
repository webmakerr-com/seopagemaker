/**
 * Content and Term Groups WP_List_Table
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

jQuery( document ).ready(
	function ( $ ) {

		// Move any buttons from the filter list to display next to the Add New button.
		$( 'ul.subsubsub a' ).each(
			function () {

				// Ignore if not a Page Generator Pro Group Action.
				if ( ! $( this ).hasClass( 'page-generator-pro-group-action' ) ) {
					return;
				}

				// Move.
				$( this ).clone().removeClass( 'hidden' ).insertBefore( 'hr.wp-header-end' );

				// Remove original.
				$( this ).parent().remove();

			}
		);

	}
);
