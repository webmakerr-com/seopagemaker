/**
 * Logs WP_List_Table
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

jQuery( document ).ready(
	function ( $ ) {

		/**
		 * Clear Log
		 *
		 * @since 	3.0.0
		 */
		$( 'a.clear-log' ).on(
			'click',
			function ( e ) {

				// Define button.
				var button = $( this );

				// Bail if the user doesn't want to clear the log.
				var result = confirm( $( button ).data( 'message' ) );
				if ( ! result ) {
					// Prevent default action.
					e.preventDefault();
					return false;
				}

			}
		);

	}
);
