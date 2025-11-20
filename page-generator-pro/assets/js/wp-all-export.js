/**
 * WP All Export Integration
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * On WP All Export > New Export:
 * - clicks the WP_Query Results button
 * - populates the query textarea, based on the query supplied in the page_generator_pro_wp_all_export object
 * - submits the form
 *
 * @since 	2.9.6
 */
jQuery( document ).ready(
	function ( $ ) {

		// Click WP_Query Results button.
		$( 'a.wpallexport-file-type' ).trigger( 'click' );

		// Populate the textarea.
		$( 'textarea[name="wp_query"]' ).val( page_generator_pro_wp_all_export.query );

		// Click the Customize Export File button.
		$( 'form.wpallexport-choose-file' ).submit();

	}
);
