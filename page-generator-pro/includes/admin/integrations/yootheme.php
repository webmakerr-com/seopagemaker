<?php
/**
 * YOOtheme Builder Integration Class
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Registers YOOtheme Builder as a Plugin integration:
 * - Decode/encode Page Builder metadata when generating Pages
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 4.2.6
 */
class Page_Generator_Pro_Yootheme extends Page_Generator_Pro_Integration {

	/**
	 * Holds the base object.
	 *
	 * @since   4.2.6
	 *
	 * @var     object
	 */
	public $base;

	/**
	 * Constructor
	 *
	 * @since   4.2.6
	 *
	 * @param   object $base    Base Plugin Class.
	 */
	public function __construct( $base ) {

		// Store base class.
		$this->base = $base;

		// Set Theme Name.
		$this->theme_name = 'YOOtheme';

		// Set Meta Keys.
		// These aren't used by Yootheme, but we use them to store the decoded post_content
		// into its HTML and JSON parts.
		$this->meta_keys = array(
			'yootheme_html',
			'yootheme_builder',
		);

		// Decode Group Content JSON.
		add_filter( 'page_generator_pro_generate_content_settings_before', array( $this, 'decode_content' ) );

		// Encode Group Content into JSON string.
		add_filter( 'page_generator_pro_generate_post_args', array( $this, 'encode_content' ), 10, 2 );

		// Content Groups: Ignore meta keys created by decode_content() and encode_content().
		add_filter( 'page_generator_pro_generate_set_post_meta_ignored_keys', array( $this, 'prevent_post_meta_copy_to_generated_content' ), 10, 4 );

	}

	/**
	 * Decodes the Content Group's content from JSON into an object, immediately prior to generation.
	 *
	 * @since   4.2.6
	 *
	 * @param   array $settings   Content Group Settings.
	 * @return  array               Content Group Settings
	 */
	public function decode_content( $settings ) {

		// Bail if Theme isn't active.
		if ( ! $this->is_theme_active() ) {
			return $settings;
		}

		// Extract HTML and JSON from the Content Group's content, as this is where YOOtheme Builder stores its page builder data.
		// This then provides an object/array that the generation routine can iterate through to replace keywords,
		// spintax etc.
		// Format is always <div>html</div> <!--more-->\n<!-- {jsonobject} -->.
		$start       = '<!--more-->';
		$end         = ' -->';
		$start_pos   = strpos( $settings['content'], $start );
		$end_pos     = strpos( $settings['content'], $end, ( $start_pos + strlen( $start ) ) );
		$html        = trim( substr( $settings['content'], 0, $start_pos ) );
		$json_string = trim( substr( $settings['content'], ( $start_pos + strlen( $start ) ), ( $end_pos - $start_pos ) ) );
		$json_string = str_replace( '<!-- ', '', $json_string );
		$json_string = str_replace( ' -->', '', $json_string );

		// Blank content, and add HTML + JSON versions to post_meta for now; we'll remove this before the page is created.
		$settings['content']                       = '';
		$settings['post_meta']['yootheme_html']    = $html;
		$settings['post_meta']['yootheme_builder'] = json_decode( $json_string );

		// Return.
		return $settings;

	}

	/**
	 * Encodes the Content Group's content from an object into JSON during the generation routine,
	 * immediately before the Page is created/updated via wp_insert_post() / wp_update_post().
	 *
	 * @since   4.2.6
	 *
	 * @param   array $post_args    wp_insert_post() / wp_update_post() arguments.
	 * @param   array $settings     Content Group settings.
	 * @return  array               wp_insert_post() / wp_update_post() arguments
	 */
	public function encode_content( $post_args, $settings ) {

		// Bail if Theme isn't active.
		if ( ! $this->is_theme_active() ) {
			return $post_args;
		}

		// Bail if the Yootheme post meta keys set in decode_content() don't exist.
		if ( ! array_key_exists( 'yootheme_html', $settings['post_meta'] ) ) {
			return $post_args;
		}
		if ( ! array_key_exists( 'yootheme_builder', $settings['post_meta'] ) ) {
			return $post_args;
		}

		// Build the post_content as Yootheme does.
		$post_args['post_content'] = wp_slash(
			$settings['post_meta']['yootheme_html'] . "\n<!--more-->\n<!-- " . wp_json_encode( $settings['post_meta']['yootheme_builder'] ) . ' -->'
		);

		// Return.
		return $post_args;

	}

	/**
	 * Adds ACF Post Meta Keys to the array of excluded Post Meta Keys if ACF
	 * metadata should not be overwritten on Content Generation
	 *
	 * @since   4.2.6
	 *
	 * @param   array $ignored_keys   Ignored Keys (preg_match() compatible regex expressions are supported).
	 * @param   int   $post_id        Generated Post ID.
	 * @param   array $settings       Group Settings.
	 * @param   array $post_args      wp_insert_post() / wp_update_post() arguments.
	 * @return  array   $ignored_keys   Ignored Keys
	 */
	public function prevent_post_meta_copy_to_generated_content( $ignored_keys, $post_id, $settings, $post_args ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter

		return array_merge( $ignored_keys, $this->meta_keys );

	}

}
