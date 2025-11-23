/**
 * Generate Content via Browser
 *
 * Provides in-plugin generation without relying on external licensing checks.
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

jQuery( function ( $ ) {
        const config = window.page_generator_pro_generate_browser;

        // Bail if the configuration isn't available.
        if ( typeof config === 'undefined' ) {
                return;
        }

        const ajaxUrl = window.ajaxurl || '';
        const totalRequests = parseInt( config.number_of_requests, 10 ) || 0;
        const resumeIndex = parseInt( config.resume_index, 10 ) || 0;
        const maxNumberOfPages = parseInt( config.max_number_of_pages, 10 ) || totalRequests + resumeIndex;
        const stopOnError = parseInt( config.stop_on_error, 10 ) === 1;
        const stopOnErrorPause = parseInt( config.stop_on_error_pause, 10 ) || 0;

        let processed = 0;
        let lastGeneratedPostDateTime = config.last_generated_post_date_time || '';
        let cancelled = false;

        /**
         * Updates the document title based on the current state.
         *
         * @param {string} state State key (processing|success|cancelled).
         */
        const updateBrowserTitle = ( state ) => {
                if ( ! config.browser_title ) {
                        return;
                }

                if ( config.browser_title[ state ] ) {
                        document.title = config.browser_title[ state ];
                }
        };

        /**
         * Updates the progress bar and number counter.
         */
        const updateProgress = () => {
                const current = resumeIndex + processed;
                const percentage = Math.min( 100, ( current / ( maxNumberOfPages || 1 ) ) * 100 );

                $( '#progress-number' ).text( current );
                $( '#progress-bar' ).css( 'width', `${percentage}%` );
        };

        /**
         * Outputs a status line to the log.
         *
         * @param {string} message Message to log.
         * @param {string} type    Status type (success|error|info).
         */
        const addLogLine = ( message, type = 'info' ) => {
                const $log = $( '#log ul' );
                const $item = $( '<li />' );

                $item.addClass( type );
                $item.html( message );

                $log.find( '.spinner' ).remove();
                $log.append( $item );
        };

        /**
         * Enables the return button and removes spinners.
         */
        const showReturnButton = () => {
                $( '#progress .spinner' ).removeClass( 'is-active' );
                $( '#log .spinner' ).removeClass( 'is-active' );
                $( '.page-generator-pro-generate-return-button' ).show();
        };

        /**
         * Formats a log message for a generation result.
         *
         * @param {Object} result Result object from the AJAX request.
         * @return {string}
         */
        const formatResultMessage = ( result ) => {
                const fragments = [ result.message ];

                if ( result.url ) {
                        fragments.push(
                                `<a href="${result.url}" target="_blank" rel="noopener noreferrer">${result.url}</a>`
                        );
                }

                return fragments.join( ' - ' );
        };

        /**
         * Handles a successful generation response.
         *
         * @param {Array} results Generation results from the AJAX request.
         * @param {number} currentIndex Current starting index for the request.
         */
        const handleGenerationSuccess = ( results, currentIndex ) => {
                results.forEach( ( result, index ) => {
                        processed += 1;
                        lastGeneratedPostDateTime = result.last_generated_post_date_time || lastGeneratedPostDateTime;

                        const type = result.result === 'success' ? 'success' : 'error';
                        addLogLine( formatResultMessage( result ), type );
                        updateProgress();

                        // If we've generated all requested items, finish up.
                        if ( processed >= totalRequests ) {
                                finishGeneration( 'success' );
                        }
                } );

                // Schedule the next request if there is more work to do.
                if ( processed < totalRequests && ! cancelled ) {
                        window.setTimeout(
                                () => runGeneration( currentIndex + config.index_increment ),
                                0
                        );
                }
        };

        /**
         * Handles an error returned from a generation request.
         *
         * @param {string|Array} message Error message(s).
         */
        const handleGenerationError = ( message ) => {
                const errors = Array.isArray( message ) ? message : [ message ];

                errors.forEach( ( error ) => addLogLine( error, 'error' ) );
                updateBrowserTitle( 'cancelled' );

                if ( stopOnError ) {
                        cancelled = true;
                        finishGeneration( 'cancelled' );
                        return;
                }

                window.setTimeout(
                        () => runGeneration( resumeIndex + processed ),
                        stopOnErrorPause
                );
        };

        /**
         * Performs an AJAX request.
         *
         * @param {Object} data Data payload.
         * @return {Object} jQuery promise.
         */
        const request = ( data ) => {
                return $.post( ajaxUrl, data ).fail( ( xhr ) => {
                        handleGenerationError( xhr.responseText || 'An unknown error occurred.' );
                } );
        };

        /**
         * Runs the after action and finalises the UI.
         */
        const finishGeneration = ( status = 'success' ) => {
                cancelled = true;
                updateBrowserTitle( status );
                window.onbeforeunload = null;

                request( {
                        action: config.action_on_finished,
                        id: config.id,
                        nonce: config.nonce,
                } ).always( () => {
                        addLogLine(
                                status === 'success' ? 'Generation complete.' : 'Generation cancelled.',
                                status === 'success' ? 'success' : 'error'
                        );
                        showReturnButton();
                } );
        };

        /**
         * Runs the generation process starting from the supplied index.
         *
         * @param {number} currentIndex Current index.
         */
        const runGeneration = ( currentIndex ) => {
                if ( cancelled ) {
                        return;
                }

                updateBrowserTitle( 'processing' );

                request( {
                        action: config.action,
                        nonce: config.nonce,
                        id: config.id,
                        current_index: currentIndex,
                        index_increment: config.index_increment,
                        number_requests: totalRequests,
                        offset: resumeIndex,
                        last_generated_post_date_time: lastGeneratedPostDateTime,
                } ).done( ( response ) => {
                        if ( ! response || typeof response.success === 'undefined' ) {
                                handleGenerationError( 'An unknown error occurred.' );
                                return;
                        }

                        if ( ! response.success ) {
                                handleGenerationError( response.data );
                                return;
                        }

                        handleGenerationSuccess( response.data, currentIndex );
                } );
        };

        /**
         * Kicks off the generation routine.
         */
        const init = () => {
                // Warn if the user tries to leave the screen while generating.
                window.onbeforeunload = () => config.exit_screen;

                updateProgress();
                addLogLine( 'Starting generation…', 'info' );

                request( {
                        action: config.action_on_start,
                        id: config.id,
                        nonce: config.nonce,
                } ).always( () => runGeneration( resumeIndex ) );
        };

        init();
} );
