/**
 * Add New Directory Structure
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

jQuery( document ).ready(
	function ( $ ) {

		/**
		 * Directory Structure
		 */
		$( 'input[name="structure"]' ).on(
			'change.page-generator-pro',
			function () {

				if ( $( this ).val().includes( 'service' ) ) {
					// Show service option.
					$( '.services' ).show();
				} else {
					// Hide service option.
					$( '.services' ).hide();
				}
			}
		);

		/**
		 * Operating Type (Radius/Area)
		 */
		$( 'input[name="method"]' ).on(
			'change.page-generator-pro',
			function () {

				var value = $( 'input[name="method"]:checked' ).val();

				if ( typeof value === 'undefined' ) {
					$( 'div.radius' ).hide();
					$( 'div.area' ).hide();
				} else {
					switch ( value ) {
						case 'radius':
							$( 'div.radius' ).show();
							$( 'div.area' ).hide();
							break;

						case 'area':
							$( 'div.radius' ).hide();
							$( 'div.area' ).show();
							break;
					}
				}

			}
		);
		$( 'input[name="method"]' ).trigger( 'change.page-generator-pro' );

		/**
		 * Country
		 * - When the Country changes, reinitialize selectize instances, which will trigger them
		 * to fetch Counties and Regions for the newly selected Country.
		 */
		$( 'body.page-generator-pro select[name="country_code"]' ).on(
			'change.page-generator-pro',
			function () {

				// Destroy selectize instances.
				page_generator_pro_destroy_selectize();

				// Reinitialize selectize instances.
				page_generator_pro_reinit_selectize();

			}
		);
		$( 'body.page-generator-pro select[name="country_code"]' ).trigger( 'change.page-generator-pro' );

		/**
		 * Loading modal on submit
		 */
		$( 'form' ).on(
			'submit',
			function ( e ) {

				wpzinc_modal_open(
					page_generator_pro_groups_directory.building_title,
					page_generator_pro_groups_directory.building_message
				);

			}
		);

	}
);
