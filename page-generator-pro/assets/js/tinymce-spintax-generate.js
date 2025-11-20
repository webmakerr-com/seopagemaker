/**
 * Registers the TinyMCE Generate Spintax Button.
 *
 * @since   1.7.9
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Based on the selected content, replaces words with spintax.
 *
 * @since 	1.7.9
 */
( function () {

	tinymce.PluginManager.add(
		'page_generator_pro_spintax_generate',
		function ( editor, url ) {

			// Add Button to Visual Editor Toolbar.
			editor.addButton(
				'page_generator_pro_spintax_generate',
				{
					title: 	'Generate Spintax from selected Text',
					image: 	page_generator_pro_tinymce.icons.page_generator_pro_spintax_generate,
					cmd: 	'page_generator_pro_spintax_generate',
				}
			);

			// Load View when button clicked.
			editor.addCommand(
				'page_generator_pro_spintax_generate',
				function () {

					// Show overlay and progress.
					wpzinc_modal_open( 'Generating...' );

					// Get selected content.
					var content = tinyMCE.activeEditor.selection.getContent(); // .getNode() ?

					// Perform an AJAX call to load the modal's view.
					jQuery.post(
						ajaxurl,
						{
							'action': 'page_generator_pro_tinymce_spintax_generate',
							'nonce': page_generator_pro_tinymce.nonces.page_generator_pro_spintax_generate,
							'content': content
						},
						function ( response ) {

							// Bail if an error occured.
							if ( ! response.success ) {
								// Show error message and exit.
								return wpzinc_modal_show_error_message( response.data );
							}

							// Replace selected content with spintax version.
							tinyMCE.activeEditor.selection.setContent( response.data );

							// Show success message and exit.
							return wpzinc_modal_show_success_and_exit( 'Done!' );

						}
					);

				}
			);
		}
	);

} )();
