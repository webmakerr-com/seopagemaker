/**
 * Registers Dynamic Elements / Shortcodes as Quicktags for the TinyMCE Text Editor
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Registers Dynamic Elements / Shortcodes as Quicktags for the TinyMCE Text Editor
 *
 * @since 	3.0.0
 *
 * @param 	array 	shortcodes 	Shortcodes.
 */
function pageGeneratorProQuickTagsRegister( shortcodes ) {

	for ( const shortcode in shortcodes ) {

		QTags.addButton(
			'page_generator_pro_' + shortcode,
			shortcodes[ shortcode ].title,
			function () {

				pageGeneratorProQuickTagsLoadModal( shortcodes[ shortcode ], 'quicktags' );

			}
		);

	}

}

/**
 * Fetches a modal for the given Dynamic Element / Shortcode,
 * displaying it in the UI.
 *
 * @since 	4.2.0
 *
 * @param 	object 	shortcode 	Shortcode.
 */
function pageGeneratorProQuickTagsLoadModal( shortcode, editorType ) {

	( function ( $ ) {

		// Perform an AJAX call to load the modal's view.
		$.post(
			ajaxurl,
			{
				'action': 		'page_generator_pro_output_tinymce_modal',
				'nonce':  		page_generator_pro_tinymce.nonces.tinymce,
				'editor_type':  editorType, // quicktags|gutenberg.
				'shortcode': 	shortcode.name
			},
			function ( response ) {

				// Show Modal.
				wpZincModal.open();

				// Resize Modal so it's not full screen.
				$( 'div.wpzinc-modal div.media-modal.wp-core-ui' ).css(
					{
						width: ( shortcode.modal.width ) + 'px',
						height: ( shortcode.modal.height + 20 ) + 'px' // Prevents a vertical scroll bar.
					}
				);

				// Set Title.
				$( '#wpzinc-modal .media-frame-title h1' ).text( shortcode.title );

				// Inject HTML into modal.
				$( '#wpzinc-modal .media-frame-content' ).html( response );

				// Resize HTML height so it fills the modal.
				$( 'div.wpzinc-modal div.media-modal.wp-core-ui div.wpzinc-vertical-tabbed-ui' ).css(
					{
						height: ( shortcode.modal.height - 50 ) + 'px' // -50px is for the footer buttons.
					}
				);

				// If there is no render callback, this is not a shortcode
				// that is inserted into the editor when 'Insert' is clicked.
				// Instead, change the 'Insert' button and let the plugin
				// provide its own event handler when the 'Run' button is clicked.
				var buttonClass = ( shortcode.render_callback ? 'insert' : 'run' );
				$( '#wpzinc-modal .media-frame-toolbar .media-toolbar .media-toolbar-primary button' ).removeClass( 'insert' );
				$( '#wpzinc-modal .media-frame-toolbar .media-toolbar .media-toolbar-primary button' ).removeClass( 'run' );
				$( '#wpzinc-modal .media-frame-toolbar .media-toolbar .media-toolbar-primary button' ).addClass( buttonClass );
				switch ( buttonClass ) {
					case 'insert':
						$( '#wpzinc-modal .media-frame-toolbar .media-toolbar .media-toolbar-primary button' ).text( 'Insert' );
						break;

					case 'run':
						$( '#wpzinc-modal .media-frame-toolbar .media-toolbar .media-toolbar-primary button' ).text( 'Run' );
						break;
				}

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

				// Trigger the change event stored in generate-content.js.
				$( 'form.wpzinc-tinymce-popup select[name="maptype"]' ).trigger( 'change.page-generator-pro' );
			}
		);

	} )( jQuery );

}

// Register quick tag buttons.
pageGeneratorProQuickTagsRegister( page_generator_pro_quicktags );
