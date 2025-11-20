/**
 * Selectize Integration
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Initialize selectize instances
 *
 * @since 	1.0.0
 */
function page_generator_pro_reinit_selectize() {

	( function( $ ) {

		/**
		 * Selectize Instances: Freeform Input
		 */
		$( page_generator_pro_selectize.fields.freeform.join( ',' ) ).each( function() {
			var delimiter = ( typeof $( this ).data( 'delimiter' ) !== 'undefined' ? $( this ).data( 'delimiter' ) : ',' );

			$( this ).selectize( {
				plugins: [ 'drag_drop', 'remove_button' ],
				delimiter: delimiter,
			    persist: false,
			    create: function( input ) {
			        return {
			            value: input,
			            text: input
			        }
			    }
			} );
		} );

		/**
		 * Selectize Instances: Drag and Drop w/ Remove Button
		 */
		$( page_generator_pro_selectize.fields.drag_drop.join( ',' ) ).selectize( {
			plugins: [ 'drag_drop', 'remove_button' ],
		    delimiter: ',',
		    persist: false,
		    create: false
		} ).on( 'change', function() {

			// If this selectize instance controls options in another <select>, do something now.
			if ( typeof $( this ).data( 'controls' ) === 'undefined' ) {
				return;
			}

			var controls = $( this ).data( 'controls' );
			if ( controls.length === 0 ) {
				return;
			}

			// Get selected value(s).
			var values = $( this ).val();

			// Disable all options for the target control.
			$( 'select[name="' + controls + '"] option' ).attr( 'disabled', 'disabled' );

			// Bail if no values selected.
			if ( ! values ) {
				return;
			}

			// Iterate through values, enabling them for the target control.
			for ( var i = 0; i < values.length; i++ ) {
				$( 'select[name="' + controls + '"] option[value="' + values[ i ] + '"]' ).removeAttr( 'disabled' );
			}

			// Fire the target control's change method, so any other options can be enabled / disabled now.
			$( 'select[name="method"]' ).trigger( 'change.page-generator-pro' );
			
		} ).trigger( 'change' );

		/**
		 * Selectize Instances: WordPress AJAX Search
		 */
		$( page_generator_pro_selectize.fields.search.join( ',' ) ).each( function() {

			var action 			= $( this ).data( 'action' ),
				nonce 			= $( this ).data( 'nonce' ),
				args 			= $( this ).data( 'args' ),
				name_field 		= $( this ).data( 'name-field' ),
				value_field 	= $( this ).data( 'value-field' ),
				method 			= $( this ).data( 'method' ),
				output_fields 	= $( this ).data( 'output-fields' ).split( ',' ); // What to output as the label.

			$( this ).selectize( {
			    delimiter: 		',',
			    valueField: 	value_field, // The value to store in the select when the form is submitted.
			    labelField: 	name_field,  // What to display on the output?
			    searchField: 	name_field,  // For some reason, this has to be specified.
			    options: 		[],
			    create: 		false,
			    render: {
			        option: function( item, escape ) {

			        	// Build string.
			        	var output_string = [];
			        	for ( var i = 0; i < output_fields.length; i++ ) {
			        		output_string.push( item[ output_fields[ i ] ] );
			        	}

			        	// Return output.
			        	return '<div>' + output_string.join( ', ' ) + '</div>';

			        }
			    },
			    load: function( query, callback ) {

			        // Bail if the query is too short.
			        if ( ! query.length || query.length < 3 ) {
			        	return callback();
			        }

			       	// Send request to Plugin's AJAX endpoint.
			       	$.ajax( {
				        url: 		page_generator_pro_selectize.ajaxurl,
				        type: 		method,
				        dataType: 	'json',
				        data: 	{
				            'action': 		action,
				            'nonce':  		nonce,
				            'query': 		query,
				            'args': 		args
				        },
				        error: function() {

				            callback();

				        },
				        success: function( result ) {

				        	callback( result.data );

				        }
				    } );
			    }
			} );
		} );

		/**
		 * Selectize Instances: API
		 */
		$( page_generator_pro_selectize.fields.api.join( ',' ) ).each( function() {

			var selectize_instance = this;
			$( selectize_instance ).selectize( {
				plugins: 		[ 'drag_drop', 'remove_button' ],
			    delimiter: 		',',
			    options: 		[],
			    create: 		false,
			    load: function( query, callback ) {

			    	var action 				= $( selectize_instance ).data( 'action' ), // WP Registered AJAX Action.
						api_call 			= $( selectize_instance ).data( 'api-call' ), // The API Call to make (zipcodes, cities, regions, counties, countries).
						api_search_field 	= $( selectize_instance ).data( 'api-search-field' ) // The API Field to search.
						api_fields 			= $( selectize_instance ).data( 'api-fields' ).split( ',' ); // Relative form fields to send in the AJAX request (e.g. region_id[],county_id[]).
						country_code 		= $( selectize_instance ).data( 'country-code' ), // The field to fetch the country code from and include in the request.
						output_fields 		= $( selectize_instance ).data( 'output-fields' ).split( ',' ); // What to store as the <option> label.
						value_field 		= $( selectize_instance ).data( 'value-field' ), // What to store as the <option> value.
						nonce 				= $( selectize_instance ).data( 'nonce' ); // The nonce for the AJAX request to pass security.

			    	// Bail if the query is too short.
			        if ( ! query.length || query.length < 3 ) {
			        	return callback();
			        }

			        // Build data.
			        var data = {
			        	action: 			action,
			        	api_call: 			api_call,
			        	api_search_field: 	api_search_field,
			        	query: 				query,
						country_code: 		( $( 'select[name="' + country_code + '"]' ).length > 0 ? $( 'select[name="' + country_code + '"]' ).val() : $( 'input[name="' + country_code + '"]' ).val() ),
			        	nonce:  			nonce
			        };

			        // Add relational field values to the data now.
			        for ( i = 0; i < api_fields.length; i++ ) {
			        	data[ api_fields[ i ].replace( /[\[\]']+/g, '' ) ] = $( 'select[name="' + api_fields[ i ] + '"]' ).val();
					}

			        // Perform AJAX query to fetch data.
					$.ajax( {
				        url: 		page_generator_pro_selectize.ajaxurl,
				        type: 		'POST',
				        async:    	true,
				        data: 		data,
				        error: function( xhr, textStatus, errorThrown ) {

				        	// Show error message and exit.
				        	return wpzinc_modal_show_error_message_and_exit( xhr.status + ' ' + xhr.statusText );

				        },
				        success: function( result ) {

				        	// If an error occured, close the UI and show the error in the main screen.
				        	if ( ! result.success ) {
				        		// Show error message and exit.
				        		return wpzinc_modal_show_error_message_and_exit( result.data.message );
				        	}

				        	// Build selectize compatible list of items.
				        	var items = [];
				        	for ( i = 0; i < result.data.data.length; i++ ) {
				        		// Build text label.
					        	var text_label = [];
					        	for ( var j = 0; j < output_fields.length; j++ ) {
					        		text_label.push( result.data.data[ i ][ output_fields[ j ] ] );
					        	}

					        	// Add item to selectize array of items.
				        		items.push( {
				        			text: 	text_label.join( ', ' ),
				        			value: 	result.data.data[ i ][ value_field ]
				        		} );
				        	}

				        	// Load the items into the selectize instance.
				        	callback( items );
				        }

				    } );

			    }
			} );
		} );
	
		/**
		 * Selectize Instances
		 * - County
		 * - Region
		 */
		$( page_generator_pro_selectize.fields.standard.join( ',' )).each( function() {

			var selectize_instance 	= this,
				action 				= $( selectize_instance ).data( 'action' ), // WP Registered AJAX Action.
				api_call 			= $( selectize_instance ).data( 'api-call' ), // The API Call to make (zipcodes, cities, regions, counties, countries).
				country_code 		= $( selectize_instance ).data( 'country-code' ), // The field to fetch the country code from and include in the request.
				nonce 				= $( selectize_instance ).data( 'nonce' ); // The nonce for the AJAX request to pass security.

			// Perform AJAX query to fetch data for this selectize instance, which will be either regions or counties.
			$.ajax( {
		        url: 		page_generator_pro_selectize.ajaxurl,
		        type: 		'POST',
		        async:    	true,
		        data: 		{
		        	action: 		action,
		        	api_call: 		api_call,
		        	country_code: 	( $( 'select[name="' + country_code + '"]' ).length > 0 ? $( 'select[name="' + country_code + '"]' ).val() : $( 'input[name="' + country_code + '"]' ).val() ),
		        	nonce:  		nonce
		        },
	        	error: function( xhr, textStatus, errorThrown ) {

		        	// Show error message and exit.
		        	return wpzinc_modal_show_error_message_and_exit( xhr.status + ' ' + xhr.statusText );

		        },
		        success: function( result ) {

		        	// Fetch output fields and value field.
		        	var output_fields 		= $( selectize_instance ).data( 'output-fields' ).split( ',' ); // What to store as the <option> label.
						value_field 		= $( selectize_instance ).data( 'value-field' ); // What to store as the <option> value.

		        	// If an error occured, close the UI and show the error in the main screen.
		        	if ( ! result.success ) {
		        		// Show error message and exit.
		        		return wpzinc_modal_show_error_message_and_exit( result.data.message );
		        	}

		        	// Build selectize compatible list of items.
		        	var items = [];
		        	for ( i = 0; i < result.data.data.length; i++ ) {
		        		// Build text label.
			        	var text_label = [];
			        	for ( var j = 0; j < output_fields.length; j++ ) {
			        		text_label.push( result.data.data[ i ][ output_fields[ j ] ] );
			        	}

			        	// Add item to selectize array of items.
		        		items.push( {
		        			text: 	text_label.join( ', ' ),
		        			value: 	result.data.data[ i ][ value_field ]
		        		} );
		        	}

		        	// Init selectize.
		        	$( selectize_instance ).selectize( {
		        		plugins: 			[ 'drag_drop', 'remove_button' ],
					    options: 			items,
					    create: 			false,
					    delimiter: 			',',
					    load: 				function( query, callback ) {
					    	callback( items );
					    }
					} );
		        }

		    } );

		} );

	})( jQuery );

}

/**
 * Destroy all selectize instances
 *
 * @since 	1.0.0
 */
function page_generator_pro_destroy_selectize() {

	( function( $ ) {

		for ( const [ type, fields ] of Object.entries( page_generator_pro_selectize.fields ) ) {

			$( fields.join( ', ' ) ).selectize().each( function() {

				this.selectize.destroy();

			} );

		}
		
	} )( jQuery );

}

// Iterate through events and their selectors, registering listeners to reinit selectize
// when the event/selector combination happens.
jQuery( document ).ready( function( $ ) {

	if ( typeof page_generator_pro_selectize.reinit_events !== 'undefined' ) {
		for ( const [ event, selectors ] of Object.entries( page_generator_pro_selectize.reinit_events ) ) {
			
			$( 'body' ).on( event, selectors.join( ',' ), function() {

				page_generator_pro_reinit_selectize();

			} );

		}
	}

} );

// Initialize selectize instances.
page_generator_pro_reinit_selectize();