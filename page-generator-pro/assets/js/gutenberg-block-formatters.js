/**
 * Registers Dynamic Elements (research, generate spintax) as buttons in the Block Toolbar in the Gutenberg editor.
 *
 * @since   4.2.0
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

// Register Gutenberg Block Toolbar buttons if the Gutenberg Editor is loaded on screen.
// This prevents JS errors if this script is accidentally enqueued on a non-
// Gutenberg editor screen, or the Classic Editor Plugin is active.
if ( typeof wp !== 'undefined' &&
	typeof wp.blocks !== 'undefined' ) {

	// Register each formatter in Gutenberg.
	for ( const formatter in page_generator_pro_gutenberg_block_formatters ) {
		pageGeneratorProGutenbergRegisterBlockFormatter( page_generator_pro_gutenberg_block_formatters[ formatter ] );
	}

}

/**
 * Registers the given formatter in Gutenberg.
 *
 * @since   4.2.0
 *
 * @param   object  formatter   Block formatter.
 */
function pageGeneratorProGutenbergRegisterBlockFormatter( block ) {

	( function ( editor, richText, element, components ) {

		const {
			BlockControls
		} = editor;
		const {
			registerFormatType,
			getTextContent,
			slice
		} = richText;
		const {
			createElement
		} = element;
		const {
			ToolbarGroup,
			ToolbarButton
		} = components;

		// Build Icon, if it's an object.
		let icon = 'dashicons-tablet';
		if ( typeof block.gutenberg_icon !== 'undefined' ) {
			if ( block.gutenberg_icon.search( 'svg' ) >= 0 ) {
				// SVG.
				icon = element.RawHTML(
					{
						children: block.gutenberg_icon
					}
				);
			} else {
				// Dashicon.
				icon = block.gutenberg_icon;
			}
		}

		// Register Format Type.
		registerFormatType(
			'page-generator-pro/' + block.name,
			{
				title:      block.title,

				// The tagName and className combination allow Gutenberg to uniquely identify
				// whether this formatter has been used on the selected text.
				tagName:    block.tag,
				className:  block.name,

				// Editor.
				edit: function ( props ) {

					// Get selected content.
					let content = getTextContent( slice( props.value ) );

					// Register button in toolbar.
					return createElement(
						BlockControls,
						{},
						createElement(
							ToolbarGroup,
							{},
							createElement(
								ToolbarButton,
								{
									key:  'page_generator_pro_' + block.name + '_toolbar_button',
									icon: icon,
									title: block.title,
									onClick: function () {
										window[ block.click ]( block, props, content, richText );
									}
								}
							)
						)
					);

				}
			}
		);

	} (
		window.wp.blockEditor,
		window.wp.richText,
		window.wp.element,
		window.wp.components
	) );

}

/**
 * Sends a request via AJAX to convert the selected text in Gutenberg
 * to spintax format, displaying a progress modal and inserting
 * the successful result in place of the selected text in Gutenberg.
 *
 * @since 	4.2.0
 *
 * @param 	object 	block 	Block Formatter.
 * @param   object  props   Block Formatter Properties.
 * @param 	string 	content Selected content.
 */
function pageGeneratorProGutenbergBlockFormatterGenerateSpintax( block, props, content, richText ) {

	const {
		insert,
		create
	} = richText;

	// Show overlay and progress.
	wpzinc_modal_open( 'Generating...' );

	// Perform an AJAX call to load the modal's view.
	jQuery.post(
		ajaxurl,
		{
			'action':   'page_generator_pro_tinymce_spintax_generate',
			'nonce':    block.nonce,
			'content':  content
		},
		function ( response ) {

			// Bail if an error occured.
			if ( ! response.success ) {
				// Show error message and exit.
				return wpzinc_modal_show_error_message( response.data );
			}

			// Replace selected content with spintax version.
			wpzinc_modal_show_success_and_exit( 'Done!' );
			return props.onChange(
				insert(
					props.value,
					create(
						{
							// Remove <p> tags.
							'html': response.data.replace( /(<([^>]+)>)/gi, "" )
						}
					)
				)
			);

		}
	);

}

/**
 * Displays the research modal, and sends a request via AJAX to perform research,
 * displaying a progress modal and inserting the successful result in place of the
 * selected text in Gutenberg.
 *
 * @since 	4.2.0
 *
 * @param 	object 	block 	Block Formatter.
 * @param   object  props   Block Formatter Properties.
 * @param 	string 	content Selected content.
 */
function pageGeneratorProGutenbergBlockFormatterResearch( block, props, content, richText ) {

	pageGeneratorProQuickTagsLoadModal( block, 'gutenberg' );

}
