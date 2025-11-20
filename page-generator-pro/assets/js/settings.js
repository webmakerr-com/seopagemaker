/**
 * Plugin Settings UI functionality
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

jQuery( document ).ready(
	function ( $ ) {

		/**
		 * Settings: Generate Locations
		 * Settings: Research
		 * Settings: Spintax
		 */
		$( 'select[name="page-generator-pro-generate-locations[provider]"], select[name="page-generator-pro-research[provider]"], select[name="page-generator-pro-spintax[provider]"]' ).on(
			'change.page-generator-pro',
			function ( e ) {

				var provider = $( this ).val();

				// Hide all divs.
				$( 'option', $( this ) ).each(
					function () {
						if ( $( this ).val().length > 0 ) {
							$( '#' + $( this ).val() ).hide();
						}
					}
				);

				// Show div relative to selected option.
				$( '#' + provider ).show();

				switch ( provider ) {
					case 'alibaba':
					case 'claude-ai':
					case 'deepseek':
					case 'gemini-ai':
					case 'mistral-ai':
					case 'openai':
					case 'openrouter':
					case 'perplexity':
						// Show Language option.
						$( '#language' ).show();
						break;

					case 'spinnerchief':
						// Hide Language, Skip Capitalized Words and Skip Words.
						$( '#language' ).hide();
						$( '#skip-capitalized-words' ).hide();
						$( '#skip-words' ).hide();
						break;

					case 'wordai':
						// Hide Skip Capitalized Words and Skip Words.
						$( '#skip-capitalized-words' ).hide();
						$( '#skip-words' ).hide();
						break;

					default:
						// Show Skip Capitalized Words and Skip Words.
						$( '#skip-capitalized-words' ).show();
						$( '#skip-words' ).show();

						// Hide Language option.
						$( '#language' ).hide();
						break;
				}

			}
		);

		// Triggers.
		if ( $( 'select[name="page-generator-pro-generate-locations[provider]"]' ).length > 0 ) {
			$( 'select[name="page-generator-pro-generate-locations[provider]"]' ).trigger( 'change.page-generator-pro' );
		}
		if ( $( 'select[name="page-generator-pro-research[provider]"]' ).length > 0 ) {
			$( 'select[name="page-generator-pro-research[provider]"]' ).trigger( 'change.page-generator-pro' );
		}
		if ( $( 'select[name="page-generator-pro-spintax[provider]"]' ).length > 0 ) {
			$( 'select[name="page-generator-pro-spintax[provider]"]' ).trigger( 'change.page-generator-pro' );
		}
	}
);
