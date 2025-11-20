/**
 * Conditional Fields
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Initialize conditional fields.
 *
 * @since 	2.5.4
 */
function page_generator_pro_conditional_fields_initialize() {

	( function ( $ ) {

		$( 'body' ).on(
			'change',
			'select.wpzinc-conditional, .wpzinc-conditional select, input[type=radio].wpzinc-conditional, .wpzinc-conditional input[type=radio]',
			function () {

				// Get container that holds all fields that are controlled by this <select>.
				var container = $( this ).data( 'container' );

				// Hide all fields with classes matching each option value.
				switch ( $( this ).prop( 'nodeName' ).toLowerCase() ) {
					case 'select':
						$( 'option', $( this ) ).each(
							function () {
								$( '.' + $( this ).val(), $( container ) ).parent().hide();
							}
						);
						break;

					case 'input':
						$( 'input[name="' + $( this ).attr( 'name' ) + '"]' ).each(
							function () {
								$( '.' + $( this ).val(), $( container ) ).parent().hide();
							}
						);
						break;
				}

				// Show fields with class matching the selected option value.
				$( '.' + $( this ).val(), $( container ) ).parent().show();

			}
		);

	} )( jQuery );

}
