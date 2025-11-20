<?php
/**
 * Conditional Output Class
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Parse conditional @if statements in a string, to determine the
 * required output.
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 3.2.8
 */
class Page_Generator_Pro_Conditional_Output {

	/**
	 * Holds the base object.
	 *
	 * @since   3.2.8
	 *
	 * @var     object
	 */
	public $base;

	/**
	 * Constructor.
	 *
	 * @since   3.2.8
	 *
	 * @param   object $base    Base Plugin Class.
	 */
	public function __construct( $base ) {

		// Store base class.
		$this->base = $base;

	}

	/**
	 * Parses conditional statements which may be in the supplied text
	 *
	 * @since   3.2.8
	 *
	 * @param   string $text   Text.
	 * @return  string          Text with conditional statements evaluated/processed
	 */
	public function process( $text ) {

		// Bail if no directives exist.
		if ( strpos( $text, '@if' ) === false ) {
			return $text;
		}

		// Remove any brackets within an @if statement.
		$text = $this->strip_brackets_in_if_statement( $text );

		// Parse conditions inside @if statements.
		$processed_text = preg_replace_callback(
			'/\@if\((.*?)\)(.*?)\@endif/si',
			array( $this, 'parse_if_statement' ),
			$text
		);

		// Return.
		return $this->format_and_return( $text, $processed_text );

	}

	/**
	 * Strip parentheses from inside @if(...) conditions.
	 *
	 * This ensures that nested parentheses in the @if condition
	 * do not break regex-based parsers later in the parse_if_statement() method.
	 *
	 * @since   5.2.6
	 *
	 * @param  string $text  Text containing @if statement(s).
	 * @return string
	 */
	private function strip_brackets_in_if_statement( $text ) {

		$text_length = strlen( $text );
		$cursor      = 0;
		$output      = '';

		while ( true ) {
			$pos = stripos( $text, '@if(', $cursor );

			if ( false === $pos ) {
				// Append remaining text and finish.
				$output .= substr( $text, $cursor );
				break;
			}

			// Copy everything before the @if(.
			$output .= substr( $text, $cursor, $pos - $cursor );

			// Position right after '@if('.
			$condition_start = $pos + 4;
			$index           = $condition_start;
			$depth           = 1;

			// Find the matching closing ')' by balancing parentheses.
			while ( $index < $text_length && $depth > 0 ) {
				$char = $text[ $index ];
				if ( '(' === $char ) {
					++$depth;
				} elseif ( ')' === $char ) {
					--$depth;
				}
				++$index;
			}

			// If depth not balanced i.e. no closing bracket, append the rest of the text and break.
			if ( 0 !== $depth ) {
				$output .= substr( $text, $pos );
				break;
			}

			$condition_end   = $index - 1; // Index of the matching ')'.
			$condition       = substr( $text, $condition_start, $condition_end - $condition_start );
			$condition_clean = str_replace( array( '(', ')' ), '', $condition );

			// Append sanitized @if.
			$output .= '@if(' . $condition_clean . ')';

			// Continue scanning after the closing ')'.
			$cursor = $index;
		}

		return $output;

	}

	/**
	 * Parses an individual @if statement
	 *
	 * @since   3.2.8
	 *
	 * @param   array $matches    preg_match_all matches.
	 *                                [0] full statement, including @if, condition and text.
	 *                                [1] condition within @if statement.
	 *                                [2] text within statement.
	 * @return  string              Content
	 */
	private function parse_if_statement( $matches ) {

		// Define pass and fail values to return based on the result of performing the comparison.
		if ( strpos( $matches[2], '@else' ) !== false ) {
			list( $pass, $fail ) = explode( '@else', $matches[2] );
		} else {
			$pass = $matches[2];
			$fail = '';
		}

		// Replace encoded ampersands within the statement.
		$matches[1] = str_replace( '&amp;&amp;', '&&', $matches[1] );

		// If a logical AND operator is included in the statement, perform multiple comparisons.
		if ( strpos( $matches[1], '&&' ) !== false ) {
			$statements = explode( '&&', $matches[1] );
			foreach ( $statements as $statement ) {
				// Remove whitespace.
				$statement = trim( $statement );

				// Get comparison operator.
				$comparison_operator = $this->get_comparison_operator( $statement );

				// Test comparison.
				if ( ! $this->perform_comparison( $comparison_operator, $statement ) ) {
					return trim( $fail );
				}
			}

			// If here, all comparisons passed.
			return trim( $pass );
		}

		// If a logical OR operator is included in the statement, perform multiple comparisons.
		if ( strpos( $matches[1], '||' ) !== false ) {
			$statements = explode( '||', $matches[1] );
			foreach ( $statements as $statement ) {
				// Remove whitespace.
				$statement = trim( $statement );

				// Get comparison operator.
				$comparison_operator = $this->get_comparison_operator( $statement );

				// Test comparison.
				// If this comparison passes, the OR statement also passes - no need to evaluate further
				// statements.
				if ( $this->perform_comparison( $comparison_operator, $statement ) ) {
					return trim( $pass );
				}
			}

			// If here, all comparisons failed.
			return trim( $fail );
		}

		// If here, no logical operator is included in the statement.
		// Perform a single comparison.

		// Get comparison operator.
		$comparison_operator = $this->get_comparison_operator( $matches[1] );

		// Test comparison.
		if ( ! $this->perform_comparison( $comparison_operator, $matches[1] ) ) {
			return trim( $fail );
		}

		// If here, comparison passed.
		return trim( $pass );

	}

	/**
	 * Returns the comparison operator detected within the given condition
	 *
	 * @since   3.2.8
	 *
	 * @param   string $condition  Condition (value1==value2, value1!=value2 etc).
	 * @return  bool|string             Comparison Operator (false = no supported comparison operator found)
	 */
	private function get_comparison_operator( $condition ) {

		// Iterate through comparison operators until one is found in the condition.
		foreach ( array_keys( $this->base->get_class( 'common' )->get_comparison_operators() ) as $comparison_operator ) {
			if ( strpos( $condition, $comparison_operator ) !== false ) {
				return $comparison_operator;
			}
		}

		// If here, no comparison operator was found.
		return false;

	}

	/**
	 * Performs the comparison
	 *
	 * @since   3.2.8
	 *
	 * @param   string $comparison_operator    Comparison Operator (e.g. ==).
	 * @param   string $statement              Statement, including comparison operator (e.g. 1 == 2).
	 * @return  bool                            Comparison Passed
	 */
	private function perform_comparison( $comparison_operator, $statement ) {

		$left  = '';
		$right = '';
		if ( ! empty( $comparison_operator ) ) {
			list( $left, $right ) = explode( $comparison_operator, $statement );
		} else {
			$left = $statement;
		}

		// Trim strings, including any errant 0xA0 which appear in the browser as &nbsp;
		// when comparing to a Keyword with a column name.
		$left  = trim( $left, " \t\n\r\0\x0B\xC2\xA0" );
		$right = trim( $right, " \t\n\r\0\x0B\xC2\xA0" );

		// Perform comparison.
		switch ( $comparison_operator ) {
			case '==':
			case '=':
				return ( $left == $right ); // phpcs:ignore Universal.Operators.StrictComparisons.LooseEqual 
			case '!=':
				return ( $left != $right ); // phpcs:ignore Universal.Operators.StrictComparisons.LooseNotEqual
			case '>':
				return ( $left > $right );
			case '>=':
				return ( $left >= $right );
			case '<':
				return ( $left < $right );
			case '<=':
				return ( $left <= $right );
			case 'LIKE':
				if ( stripos( $left, $right ) === false ) {
					return false;
				}
				return true;
			case 'NOT LIKE':
				if ( stripos( $left, $right ) === false ) {
					return true;
				}
				return false;
			default:
				return ! empty( $left );
		}

	}

	/**
	 * Sanity checks the processed output, and performs some formatting to it
	 *
	 * @since   3.2.8
	 *
	 * @param   string $original_text   Original Block Spintax.
	 * @param   string $processed_text  Processed Block Spintax.
	 * @return  string                  Spun Result
	 */
	private function format_and_return( $original_text, $processed_text ) {

		// Trim.
		$processed_text = trim( $processed_text );

		// If the processed text is the same as the original text, just return the original text.
		if ( $processed_text === $original_text ) {
			return $original_text;
		}

		// Strip any double spaces.
		$processed_text = str_replace( '  ', ' ', $processed_text );

		// Strip any spaces immediately after a paragraph tag.
		$processed_text = str_replace( '<p> ', '<p>', $processed_text );

		// Return.
		return $processed_text;

	}

}
