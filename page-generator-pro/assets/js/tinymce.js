/**
 * Handles registration of TinyMCE buttons.
 *
 * @since   3.6.2
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Registers the given block as a TinyMCE Plugin, with a button in
 * the Visual Editor toolbar.
 *
 * @since 	3.6.2
 *
 * @param 	object 	block 	Block
 */
function pageGeneratorProTinyMCERegisterPlugin( block ) {

	( function ( $ ) {

		tinymce.PluginManager.add(
			'page_generator_pro_' + block.name.replaceAll( '-', '_' ),
			function ( editor, url ) {

				// Add Button to Visual Editor Toolbar.
				editor.addButton(
					'page_generator_pro_' + block.name.replaceAll( '-', '_' ),
					{
						title: 	block.title,
						image: 	block.icon,
						cmd: 	'page_generator_pro_' + block.name.replaceAll( '-', '_' ),
					}
				);

				// Load View when button clicked.
				editor.addCommand(
					'page_generator_pro_' + block.name.replaceAll( '-', '_' ),
					function () {

						// Reset the non-TinyMCE modal content.
						// If we don't do this, switching from Text to Visual Editor for the same shortcode results
						// code picking up data from the QuickTags modal, not the TinyMCE one.
						if ( typeof wpZincModal !== 'undefined' ) {
							wpZincModal.content( new wpZincModalContent() );
						}

						// Build buttons.
						// If there is no render callback, this is not a shortcode
						// that is inserted into the editor when 'Insert' is clicked.
						// Instead, change the 'Insert' button and let the plugin
						// provide its own event handler when the 'Run' button is clicked.
						if ( block.render_callback ) {
							var buttons = [
								{
									text: 'Cancel',
									classes: 'cancel'
							},
								{
									text: 'Insert',
									subtype: 'primary',
									classes: 'insert'
							}
							];
						} else {
							var buttons = [
								{
									text: 'Cancel',
									classes: 'cancel'
							},
								{
									text: 'Run',
									subtype: 'primary',
									classes: 'run'
							}
							];
						}

						// Open the TinyMCE Modal.
						editor.windowManager.open(
							{
								id: 	'wpzinc-tinymce-modal',
								title: 	block.title,
								width: 	block.modal.width,
								height: block.modal.height,

								// See dashboard submodule's tinymce-modal.js which handles
								// insert and cancel button clicks.
								buttons: buttons
							}
						);

						// Perform an AJAX call to load the modal's view.
						$.post(
							ajaxurl,
							{
								'action': 		'page_generator_pro_output_tinymce_modal',
								'nonce':  		page_generator_pro_tinymce.nonces.tinymce,
								'editor_type':  'tinymce',
								'shortcode': 	block.name
							},
							function ( response ) {

								// Inject HTML into modal.
								$( '#wpzinc-tinymce-modal-body' ).html( response );

								// Initialize tabbed interface.
								wp_zinc_tabs_init();

								// Initialize selectize instances.
								page_generator_pro_reinit_selectize();

								// Initialize autocomplete instances, if Keywords exist.
								if ( typeof wp_zinc_autocomplete_initialize !== 'undefined' ) {
									wp_zinc_autocomplete_initialize( '.wpzinc-tinymce-popup' );
								}

								// Initialize conditional fields.
								page_generator_pro_conditional_fields_initialize();
								$( 'select.wpzinc-conditional, .wpzinc-conditional select' ).trigger( 'change' );

							}
						);

					}
				);

			}
		);

	} )( jQuery );

}
