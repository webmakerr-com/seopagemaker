/**
 * Research functions to:
 * - perform research when a TinyMCE or QuickTag modal's 'Run' button is clicked,
 * - periodically check the status of a research request.
 *
 * @since   4.2.0
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

var page_generator_pro_research_timeout;

/**
 * Perform research when a TinyMCE or QuickTag modal's 'Run' button is clicked.
 *
 * @since   4.2.0
 */
jQuery( document ).ready(
	function ( $ ) {

		// Run.
		$( 'body' ).on(
			'click',
			'#wpzinc-tinymce-modal div.mce-run button, .wpzinc-backbone-modal .media-frame-toolbar .media-toolbar button.run',
			function ( e ) {

				// Prevent default action.
				e.preventDefault();

				// Get containing form.
				let form = $( 'form.wpzinc-tinymce-popup' );

				// Get form inputs.
				let topic             = $( 'textarea[name="topic"]', $( form ) ).val(),
					instructions      = $( 'textarea[name="instructions"]', $( form ) ).val(),
					content_type      = $( 'select[name="content_type"]', $( form ) ).val(),
					limit             = $( 'input[name="limit"]', $( form ) ).val(),
					language          = $( 'select[name="language"]', $( form ) ).val(),
					spintax           = $( 'select[name="spintax"]', $( form ) ).val(),
					temperature       = $( 'input[name="temperature"]', $( form ) ).val(),
					top_p             = $( 'input[name="top_p"]', $( form ) ).val(),
					presence_penalty  = $( 'input[name="presence_penalty"]', $( form ) ).val(),
					frequency_penalty = $( 'input[name="frequency_penalty"]', $( form ) ).val(),
					editor_type       = $( 'input[name="editor_type"]', $( form ) ).val();

				// Close modal.
				switch ( editor_type ) {
					case 'tinymce':
						tinyMCE.activeEditor.windowManager.close();
						break;

					default:
						wpZincModal.close();
						break;
				}

				// Show overlay and progress.
				wpzinc_modal_open( 'Researching...' );

				// Perform an AJAX call to perform research.
				$.post(
					ajaxurl,
					{
						'action': 'page_generator_pro_research',
						'nonce': page_generator_pro_tinymce.nonces.research,
						'topic': topic,
						'instructions': instructions,
						'content_type': content_type,
						'limit': limit,
						'language': language,
						'temperature': temperature,
						'top_p': top_p,
						'presence_penalty': presence_penalty,
						'frequency_penalty': frequency_penalty,
						'spintax':spintax
					},
					function ( response ) {

						// Bail if an error occured.
						if ( ! response.success ) {
							// Show error message.
							return wpzinc_modal_show_error_message( response.data );
						}

						// If completed, insert content into editor and exit now.
						// Some research tools, such as AI Writer, won't immediately return content.
						if ( response.data.completed ) {
							// Depending on the editor type, insert the content.
							switch ( editor_type ) {
								case 'tinymce':
									// Sanity check that a Visual editor exists and is active.
									if ( typeof tinyMCE !== 'undefined' && tinyMCE.activeEditor && ! tinyMCE.activeEditor.isHidden() ) {
										// Insert into Visual Editor.
										tinyMCE.activeEditor.execCommand( 'mceReplaceContent', false, response.data.content );
									}
									break;

								case 'quicktags':
									// Insert into Text Editor.
									QTags.insertContent( response.data.content );
									break;

								case 'gutenberg':
									// Insert into Gutenberg as a HTML block.
									const newBlock = wp.blocks.createBlock(
										'core/html',
										{
											content: response.data.content
										}
									);
									wp.data.dispatch( 'core/block-editor' ).insertBlocks( newBlock );
									break;
							}

							// Show success and exit.
							return wpzinc_modal_show_success_and_exit( 'Done!', '' );
						}

						// Update modal's message and wait for the next AJAX call.
						wpzinc_modal_update_message( response.data.message );

						// Get status, which will run at an interval.
						page_generator_pro_research_get_status( response.data.id, response.data.estimated_time, editor_type );
					}
				);

			}
		);

	}
);

/**
 * Polls the research request for every 1/2 of the given estimated_time
 * to determine if the research completed.
 *
 * @since   2.8.9
 *
 * @param   string  id              ID.
 * @param   float   estimated_time  Estimated Time for research to complete.
 */
function page_generator_pro_research_get_status( id, estimated_time, editor_type ) {

	// Wait the estimated time before sending a request to check on the research status.
	page_generator_pro_research_timeout = setInterval(
		function () {
			jQuery.ajax(
				{
					type: 'POST',
					url: ajaxurl,
					data: {
						'action':   'page_generator_pro_research_get_status',
						'id':       id,
						'nonce':    page_generator_pro_tinymce.nonces.research
					},
					success: function ( response ) {
						// Bail if an error occured.
						if ( ! response.success ) {
							// Show error message.
							clearTimeout( page_generator_pro_research_timeout );
							return wpzinc_modal_show_error_message( response.data );
						}

						// If completed, insert content into editor and exit now.
						if ( response.data.completed ) {
							clearTimeout( page_generator_pro_research_timeout );

							// Depending on the editor type, insert the content.
							switch ( editor_type ) {
								case 'tinymce':
									// Sanity check that a Visual editor exists and is active.
									if ( typeof tinyMCE !== 'undefined' && tinyMCE.activeEditor && ! tinyMCE.activeEditor.isHidden() ) {
										// Insert into Visual Editor.
										tinyMCE.activeEditor.execCommand( 'mceReplaceContent', false, response.data.content );
									}
									break;

								case 'quicktags':
									// Insert into Text Editor.
									QTags.insertContent( response.data.content );
									break;

								case 'gutenberg':
									// Insert into Gutenberg as a HTML block.
									const newBlock = wp.blocks.createBlock(
										'core/html',
										{
											content: response.data.content
										}
									);
									wp.data.dispatch( 'core/block-editor' ).insertBlocks( newBlock );
									break;
							}

							// Show success and exit.
							return wpzinc_modal_show_success_and_exit( 'Done!', '' );
						}

						// Update modal's message and wait for the next AJAX call.
						wpzinc_modal_update_message( response.data.message );
					}
				}
			);
		},
		10000 // Check every 10 seconds.
	);

}
