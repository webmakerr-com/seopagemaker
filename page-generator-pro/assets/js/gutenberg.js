/**
 * Registers Dynamic Elements as Gutenberg Blocks
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

// Register Gutenberg Blocks if the Gutenberg Editor is loaded on screen.
// This prevents JS errors if this script is accidentally enqueued on a non-
// Gutenberg editor screen, or the Classic Editor Plugin is active.
if ( typeof wp !== 'undefined' &&
	typeof wp.blockEditor !== 'undefined' ) {

	if ( typeof page_generator_pro_gutenberg != 'undefined' ) {

		// Remove the Permalink Panel, if we're using Gutenberg on Content Groups.
		if ( page_generator_pro_gutenberg.post_type == 'page-generator-pro' ) {
			wp.data.dispatch( 'core/edit-post' ).removeEditorPanel( 'post-link' );
			wp.data.dispatch( 'core/edit-post' ).removeEditorPanel( 'page-attributes' );
			wp.data.dispatch( 'core/edit-post' ).removeEditorPanel( 'template' );
		}

		// Register each Block in Gutenberg.
		for ( const block in page_generator_pro_gutenberg.shortcodes ) {
			pageGeneratorProGutenbergRegisterBlock( page_generator_pro_gutenberg.shortcodes[ block ] );
		}

		// Initialize conditional fields.
		page_generator_pro_conditional_fields_initialize();

		// Initialize autocomplete instance on Gutenberg Title field, if Keywords exist.
		// We do this when the Title field is selected, because initializing Tribute's
		// autocomplete instance sooner than this doesn't work.
		const pageGeneratorProContentGroupEditorIsReady = wp.data.subscribe(
			function () {

				// Hacky; can't find a Gutenberg native way to determine if the Post Title field is focused.
				if ( jQuery( 'h1.wp-block-post-title' ).hasClass( 'is-selected' ) ) {
					// Post Title editor is ready; initialize autocomplete on Gutenberg Title field, if Keywords exist.
					if ( typeof wp_zinc_autocomplete_initialize !== 'undefined' ) {
						wp_zinc_autocomplete_initialize( '.editor-visual-editor__post-title-wrapper' );
					}

					// Calling the constant will stop subscribing to future events, as we've now initialized
					// Tribute on the Title field, and don't need to initialize it again.
					pageGeneratorProContentGroupEditorIsReady();
				}

			}
		);

	}

}

/**
 * Registers the given block in Gutenberg
 *
 * @since 	2.5.4
 *
 * @param 	object 	block 	Block
 */
function pageGeneratorProGutenbergRegisterBlock( block ) {

	// Register Block.
	( function ( blocks, editor, element, components, block ) {

		// Define some constants for the various items we'll use.
		const el                              = element.createElement;
		const { registerBlockType }           = blocks;
		const { RichText, InspectorControls } = editor;
		const { Fragment }                    = element;
		const {
			TextControl,
			CheckboxControl,
			RadioControl,
			SelectControl,
			TextareaControl,
			ToggleControl,
			RangeControl,
			FormTokenField,
			Panel,
			PanelBody,
			PanelRow,
			Button,
			Card,
			CardBody,
			CardHeader
		}                                     = components;

		/**
		 * Returns the icon to display for this block, depending
		 * on the supplied block's configuration.
		 *
		 * @since   4.8.2
		 *
		 * @return  element|string
		 */
		const getIcon = function () {

			// Return a fallback default icon if none is specified for this block.
			if ( typeof block.gutenberg_icon === 'undefined' ) {
				return 'dashicons-tablet';
			}

			// Return HTML element if the icon is an SVG string.
			if ( block.gutenberg_icon.search( 'svg' ) >= 0 ) {
				return element.RawHTML(
					{
						children: block.gutenberg_icon
					}
				);
			}

			// Just return the string, as it's a dashicon CSS class.
			return block.gutenberg_icon;

		}

		/**
		 * Return a field element for the block sidebar, which is displayed in a panel's row
		 * when this block is being edited.
		 *
		 * @since   4.8.2
		 *
		 * @param   object  props           Block properties.
		 * @param   object  field      		Field attributes.
		 * @param 	string 	attribute 		Attribute name to store the field's data in.
		 * @return  mixed                   Field component (e.g. TextControl)
		 */
		const getField = function ( props, field, attribute ) {

			// If this field has a condition, check if it's met.
			if ( typeof field.condition !== 'undefined' ) {
				// If the condition specified does not exist as a field in this block,
				// the field is misconfigured. Always display.
				if ( typeof block.fields[ field.condition.key ] !== 'undefined' ) {
					// Assume the condition has not been met for this field to be displayed.
					let condition_met = false;

					// Assert whether the condition is met based on the field type.
					switch ( block.fields[ field.condition.key ].type ) {
						case 'toggle':
							// Field's condition value will be 0 or 1.
							// Attributes field value will be false or true.
							condition_met = Boolean( Number( field.condition.value ) ) === props.attributes[ field.condition.key ]
							break;

						default:
							// Assert based on the condition's value type (array, string, number).
							switch ( typeof field.condition.value ) {
								case 'object':
									condition_met = Object.values( field.condition.value ).includes( props.attributes[ field.condition.key ] );
									break;

								default:
									condition_met = ( field.condition.value === props.attributes[ field.condition.key ] );
									break;
							}
							break;
					}

					// Skip this field if the condition is not met.
					if ( ! condition_met ) {
						return false;
					}
				}
			}

			// Build CSS class name(s).
			let fieldClassNames = [];
			if ( typeof field.class !== 'undefined' ) {
				fieldClassNames.push( field.class );
			}

			// Define some field properties shared across all field types.
			let fieldProperties = {
				id:  		'page_generator_pro_' + block.name + '_' + attribute,
				label: 		field.label,
				help: 		field.description,
				className: 	fieldClassNames.join( ' ' ),
				value: 		props.attributes[ attribute ],
				onChange: 	function ( value ) {
					if ( field.type == 'number' ) {
						// If value is a blank string i.e. no attribute value was provided,
						// cast it to the field's minimum number setting.
						// This prevents WordPress' block renderer API returning a 400 error
						// because a blank value will be passed as a string, when WordPress
						// expects it to be a numerical value.
						if ( value === '' ) {
							value = field.min;
						}

						// Cast value to integer if a value exists.
						if ( value.length > 0 ) {
							value = Number( value );
						}
					}

					var newValue          = {};
					newValue[ attribute ] = value;
					props.setAttributes( newValue );
				}
			};

			// Add data- attributes.
			if ( typeof field.data !== 'undefined' ) {
				for ( var key in field.data ) {
					fieldProperties[ 'data-' + key ] = field.data[ key ];
				}
			}

			// Define additional Field Properties and the Field Element,
			// depending on the Field Type (select, textarea, text etc).
			switch ( field.type ) {

				case 'autocomplete':
					return getAutocompleteField( fieldProperties, field.values );
					break;

				case 'autocomplete_textarea':
					return getAutocompleteTextareaField( fieldProperties );
					break;

				case 'number':
					return getNumberField( fieldProperties, field.min, field.max, field.step );
					break;

				case 'repeater':
					return getRepeaterFields( field.sub_fields, props, attribute );
					break;

				case 'select':
					return getSelectField( fieldProperties, field.values );
					break;

				case 'select_multiple':
					return getMultipleSelectField( fieldProperties, field.values, props, attribute );
					break;

				case 'text':
					return getTextField( fieldProperties );
					break;

				case 'text_multiple':
					return getMultipleTextField( fieldProperties );
					break;

				case 'textarea':
					return getTextareaField( fieldProperties );
					break;

				case 'toggle':
					return getToggleField( fieldProperties, props.attributes[ attribute ] );
					break;

				default:
					return getTextField( fieldProperties );
					break;
			}

		}

		/**
		 * Returns a WPZincAutocompleterControl based on the supplied field properties
		 * and values array, if the WPZincAutocompleterControl is available.
		 *
		 * Falls back to a TextControl if WPZincAutocompleterControl is not available.
		 *
		 * @since 	4.8.2
		 *
		 * @param 	object 	fieldProperties 	Field Properties.
		 * @param 	array 	values 				Autocomplete key/value pairs.
		 * @return 	WPZincAutocompleterControl|TextControl
		 */
		const getAutocompleteField = function ( fieldProperties, values ) {

			// Check autocompleter control exists.
			// If no Keywords are defined, we can't use it.
			if ( typeof WPZincAutocompleterControl !== 'undefined' ) {
				// Define field properties.
				fieldProperties.list    = 'autocomplete';
				fieldProperties.type    = 'text';
				fieldProperties.options = values;

				// Define field element.
				return el(
					WPZincAutocompleterControl,
					fieldProperties
				);
			}

			// Fallback to a default TextControl.
			return getTextField( fieldProperties );

		}

		/**
		 * Returns a TextareaControl with the wpzinc-autocomplete class, to support autocomplete
		 * suggestions, based on the supplied field properties.
		 *
		 * @since 	4.8.4
		 *
		 * @param 	object 	fieldProperties 	Field Properties.
		 * @return 	TextareaControl
		 */
		const getAutocompleteTextareaField = function ( fieldProperties ) {

			fieldProperties.className = 'wpzinc-autocomplete';

			return el(
				TextareaControl,
				fieldProperties
			);

		}

		/**
		 * Returns a TextControl with type = number, based on the supplied field properties
		 * and min, max and step attributes.
		 *
		 * @since 	4.8.2
		 *
		 * @param 	object 	fieldProperties 	Field Properties.
		 * @param 	int 	min 				Minimum value.
		 * @param 	int 	max 				Maximum value.
		 * @param 	int 	step 				Step value.
		 * @return 	TextControl
		 */
		const getNumberField = function ( fieldProperties, min, max, step ) {

			// Define field properties.
			fieldProperties.type = 'number';
			fieldProperties.min  = min;
			fieldProperties.max  = max;
			fieldProperties.step = step;

			// Define field element.
			return el(
				TextControl,
				fieldProperties
			);

		}

		/**
		 * Returns the repeater field, comprising of rows of fields for existing values,
		 * with Add and Delete buttons.
		 *
		 * @since 	4.8.2
		 *
		 * @param 	object 	repeaterFields 	Repeater Fields.
		 * @param   object  props           Block properties.
		 * @param 	string 	attribute 		Attribute name to store the JSON encoded repeater fields data in.
		 * @return 	array
		 */
		const getRepeaterFields = function ( repeaterFields, props, attribute ) {

			// Decode JSON value stored in attribute.
			let repeaterValues    = ( props.attributes[ attribute ].length > 0 ? JSON.parse( props.attributes[ attribute ] ) : false ),
				repeaterFieldRows = [];

			// Iterate through repeater values to output existing repeater rows.
			if ( repeaterValues.length > 0 ) {
				for ( let repeaterFieldRowIndex in repeaterValues ) {
					repeaterFieldRows.push(
						el(
							Card,
							{
								key: 'page_generator_pro_' + block.name + '_' + attribute + '_card',
							},
							[
								el(
									CardHeader,
									{
										key: 'page_generator_pro_' + block.name + '_' + attribute + '_card_header',
									},
									'#' + ( Number( repeaterFieldRowIndex ) + 1 )
								),
								el(
									CardBody,
									{
										key: 'page_generator_pro_' + block.name + '_' + attribute + '_card_body',
									},
									getRepeaterFieldRow(
										repeaterFields,
										repeaterValues,
										Number( repeaterFieldRowIndex ),
										props,
										attribute
									)
								)
							]
						)
					);
				}
			}

			// Append an 'Add' button to insert a new repeater row.
			repeaterFieldRows.push(
				el(
					Button,
					{
						key: 'page_generator_pro_' + block.name + '_' + attribute + '_add',
						isSecondary: true,
						isSmall: true,
						text: 'Add',
						onClick: function () {

							// Iterate through repeater fields to build a new blank object to append to
							// the repeaterValues.
							let repeaterRowValues = {};
							for ( let repeaterKey in repeaterFields ) {
								let repeaterField                = repeaterFields[ repeaterKey ];
								repeaterRowValues[ repeaterKey ] = '';
							}

							// If no repeater values exist, define a blank array.
							if ( ! repeaterValues ) {
								repeaterValues = [];
							}

							// Append the new blank row.
							repeaterValues.push( repeaterRowValues );

							// Update props attributes.
							var newValue          = {};
							newValue[ attribute ] = JSON.stringify( repeaterValues );
							props.setAttributes( newValue );

						}
					}
				)
			);

			return repeaterFieldRows;

		}

		/**
		 * Returns an existing repeater field's row populated with the supplied values.
		 *
		 * @since 	4.8.2
		 *
		 * @param 	object 	repeaterFields 		Repeater Fields.
		 * @param   object  repeaterValues 		Repeater Fields data.
		 * @param 	int 	repeaterRowIndex 	Repeater's row index for this row.
		 * @param   object  props           	Block properties.
		 * @param 	string 	attribute 			Attribute name to store the JSON encoded repeater fields data in.
		 * @return 	array
		 */
		const getRepeaterFieldRow = function ( repeaterFields, repeaterValues, repeaterRowIndex, props, attribute ) {

			// Iterate through repeater's fields.
			let repeaterFieldRow = [],
				repeaterFieldElement;

			for ( let repeaterKey in repeaterFields ) {
				let repeaterField = repeaterFields[ repeaterKey ];

				let subFieldProperties = {
					id:  		'page_generator_pro_' + block.name + '_' + repeaterKey,
					label: 		repeaterField.label,
					help: 		repeaterField.description,
					value: 		repeaterValues[ repeaterRowIndex ][ repeaterKey ],
					onChange: 	function ( value ) {

						// Update repeater values for this row and key.
						repeaterValues[ repeaterRowIndex ][ repeaterKey ] = value;

						// Update props attributes.
						var newValue          = {};
						newValue[ attribute ] = JSON.stringify( repeaterValues );
						props.setAttributes( newValue );

					}
				};

				switch ( repeaterField.type ) {
					case 'autocomplete':
						repeaterFieldElement = getAutocompleteField( subFieldProperties, repeaterField.values );
						break;

					case 'select':
						repeaterFieldElement = getSelectField( subFieldProperties, repeaterField.values );
						break;

					case 'text':
					default:
						repeaterFieldElement = getTextField( subFieldProperties );
						break;

				}

				repeaterFieldRow.push(
					el(
						PanelRow,
						{
							key: 'page_generator_pro_' + block.name + '_' + attribute + '_' + repeaterRowIndex,
						},
						repeaterFieldElement
					)
				);

			}

			// Append a 'Delete' button to delete this repeater row.
			let repeaterFieldRowDeleteConditionButton = el(
				Button,
				{
					key: 'page_generator_pro_' + block.name + '_' + attribute + '_delete',
					isSecondary: true,
					isSmall: true,
					text: 'Delete',
					onClick: function () {

						// Remove repeater row from values.
						repeaterValues.splice( repeaterRowIndex, 1 );

						// Update props attributes.
						var newValue          = {};
						newValue[ attribute ] = JSON.stringify( repeaterValues );
						props.setAttributes( newValue );

					}
				}
			);

			repeaterFieldRow.push( repeaterFieldRowDeleteConditionButton );

			return repeaterFieldRow;

		}

		/**
		 * Returns a SelectControl based on the supplied field properties
		 * and option values array.
		 *
		 * @since 	4.8.2
		 *
		 * @param 	object 	fieldProperties 	Field Properties.
		 * @param 	array 	values 				Select key/value pairs.
		 * @return 	SelectControl
		 */
		const getSelectField = function ( fieldProperties, values ) {

			// Build options for <select> input.
			var fieldOptions = [];
			for ( var value in values ) {
				fieldOptions.push(
					{
						label: values[ value ],
						value: value
					}
				);
			}

			// Sort field's options alphabetically by label.
			fieldOptions.sort(
				function ( x, y ) {

					let a = x.label.toUpperCase(),
					b     = y.label.toUpperCase();
					return a.localeCompare( b );

				}
			);

			// Define field properties.
			fieldProperties.options = fieldOptions;

			// Define field element.
			return el(
				SelectControl,
				fieldProperties
			);

		}

		/**
		 * Returns a FormTokenField based on the supplied field properties and
		 * select values, to allow for multiple value selection.
		 *
		 * @since 	4.8.2
		 *
		 * @param 	object 	fieldProperties 	Field Properties.
		 * @param 	array   values 				<select> key/value pairs.
		 * @param   object  props           	Block properties.
		 * @param 	string 	attribute 			Attribute name to store the JSON encoded repeater fields data in.
		 * @return 	FormTokenField
		 */
		const getMultipleSelectField = function ( fieldProperties, values, props, attribute ) {

			// Build values for <select> inputs as a flat array comprising of the format:
			// [key] label.
			// onChange will extract the key from [key] and store it as the value.
			var fieldSuggestions = [];
			for ( var value in values ) {
				fieldSuggestions.push( '[' + value + '] ' + values[ value ] );
			}

			// Define field properties.
			fieldProperties.suggestions    = fieldSuggestions;
			fieldProperties.maxSuggestions = 5;
			fieldProperties.onChange       = function ( selectedValues ) {

				// Extract keys between square brackets, storing as the value.
				var newValues = [],
				length        = selectedValues.length;
				for ( index = 0; index < length; index++ ) {
					var matches = selectedValues[ index ].match( /\[(.*?)\]/ );
					if ( matches ) {
						newValues.push( matches[1] );
					} else {
						newValues.push( selectedValues[ index ] );
					}
				}

				// Assign to block.
				var newValue          = {};
				newValue[ attribute ] = newValues;
				props.setAttributes( newValue );

			}

			// Define field element.
			return el(
				FormTokenField,
				fieldProperties
			);

		}

		/**
		 * Returns a TextControl based on the supplied field properties.
		 *
		 * @since 	4.8.2
		 *
		 * @param 	object 	fieldProperties 	Field Properties.
		 * @return 	TextControl
		 */
		const getTextField = function ( fieldProperties ) {

			fieldProperties.type = 'text';

			return el(
				TextControl,
				fieldProperties
			);

		}

		/**
		 * Returns a TextareaControl based on the supplied field properties.
		 *
		 * @since 	4.8.2
		 *
		 * @param 	object 	fieldProperties 	Field Properties.
		 * @return 	TextareaControl
		 */
		const getTextareaField = function ( fieldProperties ) {

			return el(
				TextareaControl,
				fieldProperties
			);

		}

		/**
		 * Returns a FormTokenField based on the supplied field properties.
		 *
		 * @since 	4.8.2
		 *
		 * @param 	object 	fieldProperties 	Field Properties.
		 * @return 	FormTokenField
		 */
		const getMultipleTextField = function ( fieldProperties ) {

			return el(
				FormTokenField,
				fieldProperties
			);

		}

		/**
		 * Returns a ToggleControl based on the supplied field properties.
		 *
		 * @since 	4.8.2
		 *
		 * @param 	object 	fieldProperties 	Field Properties.
		 * @return 	ToggleControl
		 */
		const getToggleField = function ( fieldProperties, isChecked ) {

			fieldProperties.checked = isChecked;

			return el(
				ToggleControl,
				fieldProperties
			);

		}

		/**
		 * Return an array of rows to display in the given block sidebar's panel when
		 * this block is being edited.
		 *
		 * @since   4.8.2
		 *
		 * @param   object  props   Block properties.
		 * @param   string  panel 	Panel name.
		 * @return  array           Panel rows
		 */
		const getPanelRows = function ( props, panel ) {

			// Build Inspector Control Panel Rows, one for each Field.
			let rows = [];
			for ( let i in block.tabs[ panel ].fields ) {
				const attribute = block.tabs[ panel ].fields[ i ], // e.g. 'term'.
					field       = block.fields[ attribute ]; // field array.

				// If this field doesn't exist as an attribute in the block's get_attributes(),
				// this is a non-Gutenberg field (such as a color picker for shortcodes),
				// which should be ignored.
				if ( typeof block.attributes[ attribute ] === 'undefined' ) {
					continue;
				}

				// Get field.
				let fieldElement = getField( props, field, attribute );

				// If field is false, ignore it i.e. a condition was not met.
				if ( ! fieldElement ) {
					continue;
				}

				// If the field element is a repeater, don't contain it in a PanelRow,
				// as it will already be contained in a PanelRow.
				switch ( field.type ) {
					case 'repeater':
						rows.push( fieldElement );
						break;

					default:
						rows.push(
							el(
								PanelRow,
								{
									key: attribute
								},
								fieldElement
							)
						);
				}
			}

			return rows;

		}

		/**
		 * Return an array of panels to display in the block's sidebar when the block
		 * is being edited.
		 *
		 * @since   4.8.2
		 *
		 * @param   object  props 	Block formatter properties.
		 * @return 	array 			Block sidebar panels.
		 */
		const getPanels = function ( props ) {

			let panels      = [],
				initialOpen = true;

			// Build Inspector Control Panels.
			for ( const panel in block.tabs ) {
				let panelRows = getPanelRows( props, panel );

				// If no panel rows exist (e.g. this is a shortcode only panel,
				// for styles, which Gutenberg registers in its own styles tab),
				// don't add this panel.
				if ( ! panelRows.length ) {
					continue;
				}

				panels.push(
					el(
						PanelBody,
						{
							title: block.tabs[ panel ].label,
							key: panel,
							initialOpen: initialOpen
						},
						panelRows
					)
				);

				// Don't open any further panels.
				initialOpen = false;
			}

			return panels;

		}

		/**
		 * Display settings sidebar when the block is being edited, and save
		 * changes that are made.
		 *
		 * @since   4.8.2
		 *
		 * @param   object  props   Block properties.
		 * @return  object          Block settings sidebar elements
		 */
		const editBlock = function ( props ) {

			// If requesting an example of how this block looks (which is requested
			// when the user adds a new block and hovers over this block's icon),
			// show the preview image.
			if ( props.attributes.is_gutenberg_example === true ) {
				return (
					Fragment,
					{},
					el(
						'img',
						{
							src: block.gutenberg_example_image,
						}
					)
				);
			}

			// Build Inspector Control Panels, which will appear in the Sidebar when editing the Block.
			let panels = getPanels( props );

			// Generate Block Preview.
			let preview = '';
			if ( block.register_on_generation_only ) {
				// Output a preview that describes the block, and to click on it to open the settings sidebar.
				// The block will be converted into HTML upon generation, so there's no 'true' preview to display.
				preview = el(
					'div',
					{
						className: 'page-generator-pro-block ' + block.name
					},
					el(
						'div',
						{
							className: 'page-generator-pro-block-title'
						},
						block.title + ' Dynamic Element'
					),
					el(
						'div',
						{
							className: 'page-generator-pro-block-description'
						},
						block.description
					),
					el(
						'div',
						{
							className: 'page-generator-pro-block-description'
						},
						'Click this block to open the settings sidebar.'
					)
				);
			} else {
				// Use the block's PHP's render() function by calling the wp.serverSideRender component, as this will
				// also be called when viewing a generated page.
				preview = el(
					wp.serverSideRender,
					{
						block: 'page-generator-pro/' + block.name,
						attributes: props.attributes,
						className: 'page-generator-pro-' + block.name,
					}
				);
			}

			// If this block has been selected, open the editor sidebar now.
			if ( props.isSelected ) {
				if ( typeof wp.data.dispatch( 'core/edit-post' ) !== 'undefined' && wp.data.dispatch( 'core/edit-post' ) !== null ) {
					if ( ! wp.data.select( 'core/edit-post' ).isEditorSidebarOpened() ) {
						wp.data.dispatch( 'core/edit-post' ).openGeneralSidebar( 'edit-post/block' );
					}
				}
			}

			// Return settings sidebar panel with fields and the block preview.
			return (
				el(
					Fragment,
					{},
					el(
						InspectorControls,
						{
							key: block.name
						},
						panels
					),
					// Block Preview.
					preview
				)
			);

		}

		// Register Block.
		registerBlockType(
			'page-generator-pro/' + block.name,
			{
				title:      block.title,
				description:block.description,
				category:   block.category,
				icon:       getIcon,
				keywords: 	block.keywords,
				attributes: block.attributes,

				// Required to force a preview.
				example: 	{
					attributes: {
						is_gutenberg_example: true, // This can be anything.
					}
				},

				// Editor.
				edit: editBlock,

				// Output.
				save: function ( props ) {

					// Deliberate; preview in the editor is determined by the return statement in `edit` above.
					// On the frontend site, the block's render() PHP class is always called, so we dynamically
					// fetch the content.
					return null;

				},
			}
		);

	} (
		window.wp.blocks,
		window.wp.blockEditor,
		window.wp.element,
		window.wp.components,
		block
	) );

}
