/**
 * Page Builders
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

jQuery( document ).ready(
	function ( $ ) {

		/**
		 * Add wpzinc class to the <body> tag, as this script is only loaded if we are
		 * editing a Content Group (whether frontend or backend)
		 */
		if ( ! $( 'body' ).hasClass( 'wpzinc' ) ) {
			$( 'body' ).addClass( 'wpzinc' );
		}

	}
);
