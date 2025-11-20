<?php
/**
 * Frontend Class
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Registers Plugin specific filters for a Post/Page's Title,
 * Excerpt and Content.
 *
 * Other classes/functions hook into these e.g. block spintax
 * if dynamic / on the fly frontend block spintax processing is enabled.
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 3.2.9
 */
class Page_Generator_Pro_Frontend {

	/**
	 * Holds the base object.
	 *
	 * @since   3.2.9
	 *
	 * @var     object
	 */
	public $base;

	/**
	 * Holds the Post ID currently being viewed.
	 *
	 * @since   4.3.2
	 *
	 * @var     int
	 */
	public $post_id = 0;

	/**
	 * Constructor
	 *
	 * @since   3.2.9
	 *
	 * @param   object $base    Base Plugin Class.
	 */
	public function __construct( $base ) {

		// Store base class.
		$this->base = $base;

		// Output header and footer code.
		add_action( 'wp_head', array( $this, 'wp_head' ) );
		add_action( 'wp_footer', array( $this, 'wp_footer' ) );

		// Hook into WordPress Post filters, which expose our own Plugin filters that can be used.
		add_filter( 'wp_title', array( $this, 'filter_site_title' ), 10, 3 );
		add_filter( 'the_title', array( $this, 'filter_post_title' ), 10, 2 );
		add_filter( 'get_the_excerpt', array( $this, 'filter_post_excerpt' ) );
		add_filter( 'the_content', array( $this, 'filter_post_content' ) );

	}

	/**
	 * Outputs code stored in the _page_generator_pro_header_code meta key for the given
	 * generated Post ID in the site's header.
	 *
	 * @since   4.3.2
	 */
	public function wp_head() {

		// Bail if not a singular Page, Post or Custom Post.
		if ( ! is_singular() || is_admin() || is_feed() || is_robots() || is_trackback() ) {
			return;
		}

		// Bail if no Post ID.
		$this->post_id = get_the_ID();
		if ( ! $this->post_id ) {
			return;
		}

		// Bail if no header code to output.
		$header_code = get_post_meta( $this->post_id, '_page_generator_pro_header_code', true );
		if ( ! $header_code ) {
			return;
		}

		// Output header code.
		echo "\n" . $header_code . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput

	}

	/**
	 * Outputs code stored in the _page_generator_pro_footer_code meta key for the given
	 * generated Post ID in the site's footer.
	 *
	 * @since   4.3.2
	 */
	public function wp_footer() {

		// Bail if no Post ID.
		$this->post_id = get_the_ID();
		if ( ! $this->post_id ) {
			return;
		}

		// Bail if no footer code to output.
		$footer_code = get_post_meta( $this->post_id, '_page_generator_pro_footer_code', true );
		if ( ! $footer_code ) {
			return;
		}

		// Process smilies and shortcodes.
		$footer_code = convert_smilies( $footer_code );
		$footer_code = do_shortcode( $footer_code );

		// Output footer code.
		echo "\n" . $footer_code . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput

	}

	/**
	 * Modifies the Site Title immediately prior to it being output
	 *
	 * @since   3.2.9
	 *
	 * @param   string $site_title     Site Title.
	 * @param   string $sep            Title Separator.
	 * @param   string $sep_location   Location of Title Separator (left | right).
	 * @return  string                  Site Title
	 */
	public function filter_site_title( $site_title, $sep, $sep_location ) {

		/**
		 * Modifies the Site Title immediately prior to it being output
		 *
		 * @since   3.2.9
		 *
		 * @param   string   $site_title      Site Title.
		 * @param   string   $sep             Title Separator.
		 * @param   string   $sep_location    Location of Title Separator (left|right).
		 */
		$result = apply_filters( 'page_generator_pro_frontend_filter_site_title', $site_title, $sep, $sep_location );

		// Return.
		return $this->return( $result, $site_title );

	}

	/**
	 * Modifies the Post Title immediately prior to it being output
	 *
	 * @since   3.2.9
	 *
	 * @param   string $post_title     Post Title.
	 * @param   int    $post_id        Post ID.
	 * @return  string                  Post Title
	 */
	public function filter_post_title( $post_title, $post_id = 0 ) {

		// Bail if no Post ID.
		if ( ! $post_id ) {
			return $post_title;
		}

		/**
		 * Modifies the Post Title immediately prior to it being output
		 *
		 * @since   3.2.9
		 *
		 * @param   string  $post_title     Post Title.
		 * @param   int     $post_id        Post ID.
		 */
		$result = apply_filters( 'page_generator_pro_frontend_filter_post_title', $post_title, $post_id );

		// Return.
		return $this->return( $result, $post_title );

	}

	/**
	 * Modifies the Post Excerpt immediately prior to it being output
	 *
	 * @since   3.2.9
	 *
	 * @param   string $post_excerpt   Post Excerpt.
	 * @return  string                  Post Excerpt
	 */
	public function filter_post_excerpt( $post_excerpt ) {

		/**
		 * Modifies the Post Excerpt immediately prior to it being output
		 *
		 * @since   3.2.9
		 *
		 * @param   string  $post_excerpt   Post Excerpt.
		 */
		$result = apply_filters( 'page_generator_pro_frontend_filter_post_excerpt', $post_excerpt );

		// Return.
		return $this->return( $result, $post_excerpt );

	}

	/**
	 * Modifies the Post Content immediately prior to it being output
	 *
	 * @since   3.2.9
	 *
	 * @param   string $post_content   Post Content.
	 * @return  string                  Post Content
	 */
	public function filter_post_content( $post_content ) {

		global $post;

		$post_id = ( isset( $post ) ? $post->ID : 0 );

		// Bail if no Post ID.
		if ( ! $post_id ) {
			return $post_content;
		}

		/**
		 * Modifies the Post Content immediately prior to it being output
		 *
		 * @since   3.2.9
		 *
		 * @param   string  $post_content   Post Content.
		 * @param   int     $post_id        Post ID.
		 */
		$result = apply_filters( 'page_generator_pro_frontend_filter_post_content', $post_content, $post_id );

		// Return.
		return $this->return( $result, $post_content );

	}

	/**
	 * Checks if the result is a WP_Error, returning the original text if so.
	 * Otherwise returns the result which will be a filtered string
	 *
	 * @since   3.2.9
	 *
	 * @param   WP_Error|string $result         Result.
	 * @param   string          $original_text  Original Text String.
	 * @return  string                              Original Text or Result
	 */
	private function return( $result, $original_text ) {

		if ( is_wp_error( $result ) ) {
			return $original_text;
		}

		return $result;

	}

}
