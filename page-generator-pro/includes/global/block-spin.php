<?php
/**
 * Block Spin Class
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Spins block spintax into text.
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 2.0.1
 */
class Page_Generator_Pro_Block_Spin {

	/**
	 * Holds the base object.
	 *
	 * @since   2.0.1
	 *
	 * @var     object
	 */
	public $base;

	/**
	 * Constructor.
	 *
	 * @since   2.0.1
	 *
	 * @param   object $base    Base Plugin Class.
	 */
	public function __construct( $base ) {

		// Store base class.
		$this->base = $base;

		add_action( 'wp_loaded', array( $this, 'maybe_process_on_frontend' ) );

	}

	/**
	 * If processing Spintax is enabled on the frontend through the Plugin's Settings,
	 * adds the necessary filters to permit spintax processing.
	 *
	 * @since   3.2.9
	 */
	public function maybe_process_on_frontend() {

		// Bail if processing spintax isn't enabled.
		if ( ! $this->base->get_class( 'settings' )->get_setting( $this->base->plugin->name . '-spintax', 'frontend', 0 ) ) {
			return;
		}

		// Register Plugin filters on Post Elements to process spintax on.
		add_filter( 'page_generator_pro_frontend_filter_post_content', array( $this, 'process' ) );

	}

	/**
	 * Parses content, which comprises of one or more paragraphs, denoted with #p# and #/p#.
	 *
	 * @since   2.0.1
	 *
	 * @param   string $text   Block Spintax Text.
	 * @return  string          Spun Text
	 */
	public function process( $text ) {

		// Assume spun text is the text for now.
		$spun_text = $text;

		// If #section# is specified, we need to reorder the paragraphs inside the section.
		if ( preg_match( '/\#section(.*?)\#(.*?)\#\/section\#/si', $text ) ) {
			$spun_text = preg_replace_callback(
				'/\#section(.*?)\#(.*?)\#\/section\#/si',
				array( $this, 'parse_section' ),
				$text
			);
		}

		// Parse #p# and #s# not inside a #section#.
		$spun_text = preg_replace_callback(
			'/\#p\#(.*?)\#\/p\#/si',
			array( $this, 'parse_paragraph' ),
			$spun_text
		);

		// Return.
		return $this->format_and_return( $text, $spun_text );

	}

	/**
	 * Sanity checks the output, and performs some formatting to
	 * the block spun content
	 *
	 * @since   2.3.0
	 *
	 * @param   string $original_text   Original Block Spintax.
	 * @param   string $spun_text   Spun Result.
	 */
	private function format_and_return( $original_text, $spun_text ) {

		// If the spun text is blank, something went wrong.
		// This should never happen, but it's a useful fallback in case.
		if ( empty( $spun_text ) ) {
			return $original_text;
		}

		// If the spun text is the same as the original text, just return the original text.
		// This prevents non-spintax items from being modified.
		if ( $spun_text === $original_text ) {
			return $original_text;
		}

		// Trim.
		$spun_text = trim( $spun_text );

		// Strip any double spaces.
		$spun_text = str_replace( '  ', ' ', $spun_text );

		// Strip any spaces immediately after a paragraph tag.
		$spun_text = str_replace( '<p> ', '<p>', $spun_text );

		// Strip double paragraphs, which happen when we're spinning frontend content.
		$spun_text = str_replace( '<p><p>', '<p>', $spun_text );
		$spun_text = str_replace( '</p></p>', '</p>', $spun_text );

		// Strip breaklines which wrongly happen when we're spinning frontend content.
		$spun_text = str_replace( '<p><br />', '<p>', $spun_text );
		$spun_text = str_replace( "<p>\n<br />", '<p>', $spun_text );
		$spun_text = str_replace( '<br /><br />', '', $spun_text );

		// Return.
		return $spun_text;

	}

	/**
	 * Parses an individual #section#, which comprises of one or more paragraph (#p# #/p#) blocks.
	 *
	 * Paragraphs are shuffled at random within each section.
	 *
	 * @since   2.0.1
	 *
	 * @param   array $matches    preg_match_all matches.
	 *                                [0] original section, including #section# tags.
	 *                                [1] any #section# attributes (if blank, no attributes).
	 *                                [2] text within #section#.
	 * @return  string              Block Content
	 */
	private function parse_section( $matches ) {

		// If section attributes exist, such as the minimum and maximum number of paragraphs to return,
		// fetch the attributes now.
		$atts = $this->get_default_section_attributes();
		if ( $matches[1] ) {
			$atts = array_merge(
				$atts,
				shortcode_parse_atts( trim( $matches[1] ) )
			);
		}

		$section = preg_replace_callback(
			'/\#p\#(.*?)\#\/p\#/si',
			array( $this, 'parse_paragraph' ),
			$matches[2] // text within #section#.
		);

		// Split paragraphs into an array.
		$paragraphs = explode( '</p>', $section );

		// Remove <p> tags from all paragraphs for now.
		foreach ( $paragraphs as $index => $paragraph ) {
			$paragraphs[ $index ] = trim( str_replace( array( '<p>', '</p>' ), '', $paragraph ) );

			// If this value is now empty, discard it.
			if ( empty( $paragraphs[ $index ] ) ) {
				unset( $paragraphs[ $index ] );
				continue;
			}

			// If this value is just HTML tags with no text, discard it.
			if ( empty( wp_strip_all_tags( $paragraphs[ $index ] ) ) ) {
				unset( $paragraphs[ $index ] );
			}
		}

		// If imploding the paragraphs results in an empty string, return nothing.
		if ( empty( trim( implode( '', $paragraphs ) ) ) ) {
			return '';
		}

		// If no min/max paragraphs are specified, maybe randomize order and return.
		if ( ! $atts['min'] && ! $atts['max'] ) {
			// Shuffle paragraphs, if required.
			if ( absint( $atts['random_p_order'] ) !== 0 ) {
				shuffle( $paragraphs );
			}

			// Implode into a string and return.
			return '<p>' . implode( '</p><p>', $paragraphs ) . '</p>';
		}

		// Determine the number of paragraphs to use.
		$number_of_paragraphs = wp_rand( absint( $atts['min'] ), absint( $atts['max'] ) );

		// If no paragraphs are required, maybe randomize order, extract paragraph(s) and return.
		if ( ! $atts['required_p'] ) {
			// Shuffle paragraphs, if required.
			if ( absint( $atts['random_p_order'] ) !== 0 ) {
				shuffle( $paragraphs );
			}

			// Extract the number of paragraphs from the paragraphs array.
			$paragraphs = array_slice( $paragraphs, 0, $number_of_paragraphs );

			// Implode into a string and return.
			return '<p>' . implode( '</p><p>', $paragraphs ) . '</p>';
		}

		// Extract the required paragraphs from the array of paragraphs.
		$selected_paragraphs = array();
		foreach ( explode( ',', $atts['required_p'] ) as $required_paragraph_index ) {
			// Convert to zero based index.
			$required_paragraph_index = ( absint( $required_paragraph_index ) + -1 );

			// If this paragraph doesn't exist in the array of paragraphs, continue.
			if ( ! isset( $paragraphs[ absint( $required_paragraph_index ) ] ) ) {
				continue;
			}

			// Add this paragraph to the array of required paragraphs, and remove it from the paragraphs array
			// as it has been selected.
			$selected_paragraphs[] = $paragraphs[ absint( $required_paragraph_index ) ];
			unset( $paragraphs[ absint( $required_paragraph_index ) ] );
		}

		// Reindex the remaining paragraphs.
		$paragraphs = array_values( $paragraphs );

		// Shuffle paragraphs, if required.
		if ( absint( $atts['random_p_order'] ) !== 0 ) {
			shuffle( $paragraphs );
		}

		// Create a new array of paragraphs, comprising of required paragraphs first, followed by remaining paragraphs.
		$paragraphs = array_merge( array_values( $selected_paragraphs ), array_values( $paragraphs ) );

		// Extract the number of paragraphs from the paragraphs array.
		$paragraphs = array_slice( $paragraphs, 0, $number_of_paragraphs );

		// Implode into a string and return.
		return '<p>' . implode( '</p><p>', $paragraphs ) . '</p>';

	}

	/**
	 * Parses an individual #p# paragraph, which comprises of one or more sentence (#s /#s) blocks.
	 *
	 * @since   2.0.1
	 *
	 * @param   array $matches    preg_match_all matches.
	 * @return  string              Block Content
	 */
	private function parse_paragraph( $matches ) {

		// Replace <br /> with a new line, as some Page Builders replace newlines with <br /> tags.
		$matches[0] = str_replace( '<br />', "\n", $matches[0] );
		$matches[1] = str_replace( '<br />', "\n", $matches[1] );

		$paragraph = '<p>' . preg_replace_callback(
			'/\#s\#(.*?)\#\/s\#/s',
			array( $this, 'parse_sentence' ),
			$matches[1]
		) . '</p>';

		// Strip newlines and trim space.
		$paragraph = str_replace( "\n", '', $paragraph );
		$paragraph = str_replace( "\r", '', $paragraph );
		$paragraph = trim( $paragraph );

		// Return.
		return $paragraph;

	}

	/**
	 * Parses an individual sentence block, which comprises of one or more lines of
	 * sentences, returning a single sentence at random.
	 *
	 * @since   2.0.1
	 *
	 * @param   array $matches    preg_match_all matches.
	 * @return  string              Sentence Content
	 */
	private function parse_sentence( $matches ) {

		// Explode the sentence spins.
		$parts = explode( "\n", trim( $matches[1] ) );

		// Remove empty sentences.
		foreach ( $parts as $index => $part ) {
			// Trim the sentence to remove any newlines, to avoid falsely finding a sentence
			// isn't empty when it is just a newline character.
			$part = trim( $part );

			if ( empty( $part ) ) {
				unset( $parts[ $index ] );
			}
		}

		// Reindex.
		$parts = array_values( $parts );

		// Return a random sentence from the available options.
		return trim( $parts[ array_rand( $parts ) ] ) . ' ';

	}

	/**
	 * Defines the default attributes on a section
	 *
	 * @since   3.0.0
	 *
	 * @return  array   Attributes
	 */
	private function get_default_section_attributes() {

		return array(
			'min'            => false,
			'max'            => false,
			'random_p_order' => true,
			'required_p'     => false,
		);

	}

}
