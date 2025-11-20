<?php
/**
 * Gutenberg Class
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Registers Dynamic Elements as Gutenberg Blocks.
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 2.5.1
 */
class Page_Generator_Pro_Gutenberg {

	/**
	 * Holds the base object.
	 *
	 * @since   2.5.1
	 *
	 * @var     object
	 */
	public $base;

	/**
	 * Constructor
	 *
	 * @since   2.5.1
	 *
	 * @param   object $base    Base Plugin Class.
	 */
	public function __construct( $base ) {

		// Store base class.
		$this->base = $base;

		// Register Gutenberg Support for Add New Using AI.
		add_filter( 'page_generator_pro_groups_ai_supported_page_builders', array( $this, 'register_add_new_using_ai_support' ) );
		add_filter( 'page_generator_pro_groups_ai_page_builder_version_gutenberg', array( $this, 'get_version' ) );
		add_filter( 'page_generator_pro_groups_ai_page_builder_modules_gutenberg', array( $this, 'get_registered_blocks' ) );

		// Register Gutenberg Block Categories and Blocks.
		if ( get_bloginfo( 'version' ) >= 5.8 ) {
			// Filter changed in 5.8.
			add_filter( 'block_categories_all', array( $this, 'add_block_categories' ), 10, 2 );
		} else {
			add_filter( 'block_categories', array( $this, 'add_block_categories' ), 10, 2 );
		}
		add_action( 'init', array( $this, 'add_blocks' ) );

		// Enqueue block CSS in the Gutenberg editor.
		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_block_editor_assets' ) ); // Editor.

		// Convert Dynamic Element Blocks to HTML Blocks.
		add_filter( 'page_generator_pro_generate_content_settings', array( $this, 'convert_blocks_to_shortcode_blocks' ), 10, 1 );

		// Convert HTML Blocks to Core Blocks.
		add_filter( 'page_generator_pro_generate_post_args', array( $this, 'convert_html_blocks_to_core_blocks' ), 10, 1 );

	}

	/**
	 * Registers Gutenberg as a supported page builder for Add New Using AI.
	 *
	 * @since   5.0.4
	 *
	 * @param   array $page_builders   Page Builders.
	 * @return  array                  Page Builders
	 */
	public function register_add_new_using_ai_support( $page_builders ) {

		$page_builders['gutenberg'] = 'Gutenberg / Block Editor';
		return $page_builders;

	}

	/**
	 * Returns the Gutenberg version.
	 *
	 * @since   5.0.4
	 *
	 * @param   string $version   Gutenberg Version.
	 * @return  string            Gutenberg Version
	 */
	public function get_version( $version ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter

		return defined( 'GUTENBERG_VERSION' ) ? GUTENBERG_VERSION : '19.3';

	}

	/**
	 * Returns the registered Gutenberg blocks.
	 *
	 * @since   5.0.4
	 *
	 * @return  array    Registered Gutenberg Blocks
	 */
	public function get_registered_blocks() {

		return array(
			'core/block',
			'core/button',
			'core/cover',
			'core/heading',
			'core/list',
			'core/shortcode',
			'core/buttons',
			'core/code',
			'core/column',
			'core/columns',
			'core/details',
			'core/embed',
			'core/freeform',
			'core/group',
			'core/list-item',
			'core/paragraph',
			'core/preformatted',
			'core/pullquote',
			'core/quote',
			'core/separator',
			'core/social-links',
			'core/spacer',
			'core/text-columns',
			'core/verse',
			'core/video',
		);

	}

	/**
	 * Adds block categories to the default Gutenberg Block Categories
	 *
	 * @since   2.5.1
	 *
	 * @param   array   $categories     Block Categories.
	 * @param   WP_Post $post           WordPress Post.
	 * @return  array                   Block Categories
	 */
	public function add_block_categories( $categories, $post ) {

		// Define block categories.
		$categories = array_merge(
			$categories,
			array(
				array(
					'slug'  => $this->base->plugin->name,
					'title' => $this->base->plugin->displayName,
				),
			)
		);

		/**
		 * Adds block categories to the default Gutenberg Block Categories
		 *
		 * @since   2.5.1
		 *
		 * @param   array   $categories     Block Categories.
		 * @param   WP_Post $post           WordPress Post.
		 */
		$categories = apply_filters( 'page_generator_pro_gutenberg_add_block_categories', $categories, $post );

		// Return filtered results.
		return $categories;

	}

	/**
	 * Registers Plugin Shortcodes as Blocks, so that they can be used in the Gutenberg Editor
	 *
	 * @since   2.5.1
	 */
	public function add_blocks() {

		// Bail if Gutenberg isn't available.
		if ( ! function_exists( 'register_block_type' ) ) {
			return;
		}

		// Get shortcodes.
		$shortcodes = $this->base->get_class( 'shortcode' )->get_shortcodes();

		// Bail if no shortcodes are available.
		if ( ! is_array( $shortcodes ) || count( $shortcodes ) === 0 ) {
			return;
		}

		// Get registered blocks.
		$registered_blocks = array_keys( WP_Block_Type_Registry::get_instance()->get_all_registered() );

		// Iterate through shortcodes, registering them.
		foreach ( $shortcodes as $shortcode => $properties ) {
			// Skip if this block has already been registered.
			if ( is_array( $registered_blocks ) && in_array( $this->base->plugin->name . '/' . $shortcode, $registered_blocks, true ) ) {
				continue;
			}

			// Skip if the shortcode does not have a render callback.
			if ( ! is_array( $properties['render_callback'] ) ) {
				continue;
			}

			// Define the renderer for the block preview.
			$render_callback = null;
			if ( ! $properties['register_on_generation_only'] ) {
				// Use the shortcode's render() function, as this will output the required preview.
				$render_callback = array(
					$this->base->get_class( $properties['render_callback'][0] ), // e.g. $this->base->get_class( 'shortcode_google_map' ).
					$properties['render_callback'][1], // e.g. 'render'.
				);
			}

			// Register block.
			register_block_type(
				$this->base->plugin->name . '/' . $shortcode,
				array(
					'attributes'      => $properties['attributes'],
					'editor_script'   => $this->base->plugin->name . '-gutenberg',
					'render_callback' => $render_callback,
				)
			);
		}

	}

	/**
	 * Enqueue block styles in the Gutenberg editor.
	 *
	 * @since   3.6.6
	 */
	public function enqueue_block_editor_assets() {

		wp_enqueue_style( $this->base->plugin->name . '-gutenberg', $this->base->plugin->url . 'assets/css/gutenberg.css', array(), $this->base->plugin->version );

	}

	/**
	 * If the given Content Group's content contains blocks registered by this Plugin, converts them
	 * to Shortcode Blocks so subsequent generation routines can parse them.
	 *
	 * Also double slashes unicode characters so that when a slash is stripped by wp_insert_post()
	 * and wp_update_post(), the Gutenberg block's JSON doesn't invalidate due to no longer being escaped.
	 *
	 * @since   2.5.2
	 *
	 * @param   array $settings       Group Settings.
	 * @return  array                   Group Settings
	 */
	public function convert_blocks_to_shortcode_blocks( $settings ) {

		// Bail if Gutenberg doesn't exist.
		if ( ! function_exists( 'parse_blocks' ) ) {
			return $settings;
		}

		// Bail if settigs are null.
		if ( is_null( $settings ) ) {
			return $settings;
		}

		// Fetch Blocks from Content.
		$blocks = parse_blocks( $settings['content'] );

		// Bail if no Blocks exist (i.e. we're not using Gutenberg).
		if ( ! is_array( $blocks ) ) {
			return $settings;
		}
		if ( ! count( $blocks ) ) {
			return $settings;
		}

		// Recursively iterate through blocks, converting Dynamic Element blocks
		// to HTML Blocks.
		$blocks = $this->recursively_convert_dynamic_element_blocks_to_html_blocks( $blocks );

		// Serialize blocks.
		$serialized_blocks = $this->serialize_blocks( $blocks );

		// Double slash JSON so when wp_insert_post() / wp_update_post() removes a slash, the JSON is still valid for Gutenberg to read
		// This fixes errors with special characters in ACF wrongly outputting their unicode equivalents, and not HTML encoded characters
		// This also fixes errors with some third party Blocks e.g. Kadence.
		$serialized_blocks = preg_replace( '/(u[0-9a-fA-F]{4})/i', '\\\$1', $serialized_blocks );

		// Assign to content.
		$settings['content'] = $serialized_blocks;

		// Return.
		return $settings;

	}

	/**
	 * Recursively walks through an array of blocks and innerBlocks,
	 * converting any Plugin block to a Shortcode or HTML block.
	 *
	 * @since   2.6.9
	 *
	 * @param   array $blocks     Blocks.
	 * @return  array               Blocks
	 */
	private function recursively_convert_dynamic_element_blocks_to_html_blocks( $blocks ) {

		foreach ( $blocks as $index => $block ) {
			// If this block has inner blocks, walk through the inner blocks.
			if ( ! empty( $block['innerBlocks'] ) ) {
				$blocks[ $index ]['innerBlocks'] = $this->recursively_convert_dynamic_element_blocks_to_html_blocks( $block['innerBlocks'] );
			}

			// Skip if a null block name.
			if ( is_null( $block['blockName'] ) ) {
				continue;
			}

			// Skip if not a Plugin block.
			if ( strpos( $block['blockName'], $this->base->plugin->name ) === false ) {
				continue;
			}

			// Get Shortcode.
			$shortcode_name = str_replace( $this->base->plugin->name . '/', '', $block['blockName'] );
			$shortcode      = $this->base->get_class( 'shortcode' )->get_shortcode( $shortcode_name );

			// Skip if the Shortcode isn't registered.
			if ( ! $shortcode ) {
				continue;
			}

			// Skip if this shortcode is set to register outside of Content Groups.
			if ( ! $shortcode['register_on_generation_only'] ) {
				continue;
			}

			// If no attributes exist, skip.
			if ( is_null( $block['attrs'] ) ) {
				continue;
			}

			// Build Shortcode.
			$shortcode_html = $this->convert_block_to_shortcode( $block, $shortcode_name, $shortcode );

			// Replace Plugin Block with HTML Block that we want to parse when generating the Page.
			$blocks[ $index ] = array(
				'blockName'    => 'core/html',

				// Copy the attributes from the Dynamic Element block, for use in convert_html_blocks_to_core_blocks() later.
				'attrs'        => array_merge(
					array(
						'original_block_name' => $block['blockName'],
					),
					$block['attrs']
				),
				'innerBlocks'  => array(),
				'innerHTML'    => $shortcode_html,
				'innerContent' => array(
					$shortcode_html,
				),
			);
		}

		return $blocks;

	}

	/**
	 * Returns shortcode syntax for the given block, based on the given Shortcode Name
	 * and block attributes
	 *
	 * @since   2.5.2
	 *
	 * @param   array  $block              Block.
	 * @param   string $shortcode_name     Shortcode Name.
	 * @param   array  $shortcode          Shortcode Properties.
	 * @return  string                      Shortcode
	 */
	private function convert_block_to_shortcode( $block, $shortcode_name, $shortcode ) {

		$shortcode_html = $this->base->plugin->name . '-' . $shortcode_name;
		foreach ( $block['attrs'] as $key => $value ) {
			// Skip empty strings.
			if ( ! is_array( $value ) && ! strlen( $value ) ) {
				continue;
			}

			// Skip empty arrays.
			if ( is_array( $value ) && ! count( $value ) ) {
				continue;
			}

			// Convert array to string.
			if ( is_array( $value ) ) {
				$delimiter = ( isset( $shortcode['fields'][ $key ]['data']['delimiter'] ) ? $shortcode['fields'][ $key ]['data']['delimiter'] : ',' );
				$value     = implode( $delimiter, $value );
			}

			$shortcode_html .= ' ' . $key . '="' . $value . '"';
		}

		// If this is a YouTube shortcode, wrap it in a paragraph so oEmbed automatically displays a video.
		if ( $shortcode_name === 'youtube' ) {
			return wpautop( '[' . $shortcode_html . ']' );
		}

		return '[' . $shortcode_html . ']';

	}

	/**
	 * If the Content Group's Post arguments contains HTML blocks created by convert_blocks_to_shortcode_blocks(),
	 * converts them to core blocks where possible.  For example, image Dynamic Elements are converted to core/html,
	 * so we need to convert them to core/image blocks for better rendering.
	 *
	 * @since   5.2.5
	 *
	 * @param   array $post_args     Post Args.
	 * @return  array
	 */
	public function convert_html_blocks_to_core_blocks( $post_args ) {

		// Bail if Gutenberg doesn't exist.
		if ( ! function_exists( 'parse_blocks' ) ) {
			return $post_args;
		}

		// Fetch Blocks from Content.
		$blocks = parse_blocks( $post_args['post_content'] );

		// Bail if no Blocks exist (i.e. we're not using Gutenberg).
		if ( ! is_array( $blocks ) ) {
			return $post_args;
		}
		if ( ! count( $blocks ) ) {
			return $post_args;
		}

		// Recursively iterate through blocks, converting HTML blocks to Core blocks.
		$blocks = $this->recursively_convert_html_blocks_to_core_blocks( $blocks );

		// Serialize blocks.
		$serialized_blocks = $this->serialize_blocks( $blocks );

		// Double slash JSON so when wp_insert_post() / wp_update_post() removes a slash, the JSON is still valid for Gutenberg to read
		// This fixes errors with special characters in ACF wrongly outputting their unicode equivalents, and not HTML encoded characters
		// This also fixes errors with some third party Blocks e.g. Kadence.
		$serialized_blocks = preg_replace( '/(u[0-9a-fA-F]{4})/i', '\\\$1', $serialized_blocks );

		// Assign to content.
		$post_args['post_content'] = $serialized_blocks;

		// Return.
		return $post_args;

	}

	/**
	 * Recursively walks through an array of blocks and innerBlocks,
	 * converting any HTML blocks to Core blocks, where a suitable
	 * core block exists.
	 *
	 * @since   5.2.5
	 *
	 * @param   array $blocks     Blocks.
	 * @return  array               Blocks
	 */
	private function recursively_convert_html_blocks_to_core_blocks( $blocks ) {

		foreach ( $blocks as $index => $block ) {
			// If this block has inner blocks, walk through the inner blocks.
			if ( ! empty( $block['innerBlocks'] ) ) {
				$blocks[ $index ]['innerBlocks'] = $this->recursively_convert_html_blocks_to_core_blocks( $block['innerBlocks'] );
			}

			// Skip if a null block name.
			if ( is_null( $block['blockName'] ) ) {
				continue;
			}

			// Skip if not a HTML block.
			if ( $block['blockName'] !== 'core/html' ) {
				continue;
			}

			// Skip if the block does not have an `original_block_name` attribute containing page-generator-pro.
			if ( ! isset( $block['attrs']['original_block_name'] ) ) {
				continue;
			}
			if ( strpos( $block['attrs']['original_block_name'], $this->base->plugin->name ) === false ) {
				continue;
			}

			/**
			 * Converts a HTML block created by a Dynamic Element to a Core block.
			 *
			 * @since   5.2.5
			 *
			 * @param   array $block     Block.
			 * @return  array
			 */
			$block = apply_filters( 'page_generator_pro_gutenberg_convert_html_block_to_core_block', $block );

			// If the block is still a core/html block, remove the attributes.
			if ( $block['blockName'] === 'core/html' ) {
				$block['attrs'] = array();
			}

			// Replace HTML Block with Core Block that we want to output when generating the Page.
			$blocks[ $index ] = $block;
		}

		return $blocks;

	}

	/**
	 * Converts the Gutenberg Blocks array, derived from parse_blocks(), back into a serialized
	 * string which can be used in the post_content.
	 *
	 * If serialize_blocks() isn't available (added in 5.3.1+), use this class' functions
	 * which mimic the same behaviour as WordPress.
	 *
	 * @since   2.5.2
	 *
	 * @param   array $blocks     Blocks.
	 * @return  string              Serialized Blocks
	 */
	private function serialize_blocks( $blocks ) {

		// We use our own functions, which are a copy of WordPress', as we json_encode slightly differently
		// in serialize_block_attributes().
		return implode( '', array_map( array( $this, 'serialize_block' ), $blocks ) );

	}

	/**
	 * Returns the content of a block, including comment delimiters, serializing all
	 * attributes from the given parsed block.
	 *
	 * This should be used when preparing a block to be saved to post content.
	 * Prefer `render_block` when preparing a block for display. Unlike
	 * `render_block`, this does not evaluate a block's `render_callback`, and will
	 * instead preserve the markup as parsed.
	 *
	 * @since 5.3.1
	 *
	 * @param WP_Block_Parser_Block $block A single parsed block object.
	 * @return string String of rendered HTML.
	 */
	private function serialize_block( $block ) {

		$block_content = '';

		$index = 0;
		foreach ( $block['innerContent'] as $chunk ) {
			$block_content .= is_string( $chunk ) ? $chunk : $this->serialize_block( $block['innerBlocks'][ $index++ ] );
		}

		if ( ! is_array( $block['attrs'] ) ) {
			$block['attrs'] = array();
		}

		return $this->get_comment_delimited_block_content(
			$block['blockName'],
			$block['attrs'],
			$block_content
		);

	}

	/**
	 * Returns the content of a block, including comment delimiters.
	 *
	 * @since 5.3.1
	 *
	 * @param  string|null $block_name        Block name.
	 * @param  array       $block_attributes  Block attributes.
	 * @param  string      $block_content     Block save content.
	 * @return string                           Comment-delimited block content.
	 */
	private function get_comment_delimited_block_content( $block_name, $block_attributes, $block_content ) {

		if ( is_null( $block_name ) ) {
			return $block_content;
		}

		$serialized_block_name = $this->strip_core_block_namespace( $block_name );
		$serialized_attributes = empty( $block_attributes ) ? '' : $this->serialize_block_attributes( $block_attributes ) . ' ';

		if ( empty( $block_content ) ) {
			return sprintf( '<!-- wp:%s %s/-->', $serialized_block_name, $serialized_attributes );
		}

		return sprintf(
			'<!-- wp:%s %s-->%s<!-- /wp:%s -->',
			$serialized_block_name,
			$serialized_attributes,
			$block_content,
			$serialized_block_name
		);

	}

	/**
	 * Returns the block name to use for serialization. This will remove the default
	 * "core/" namespace from a block name.
	 *
	 * @since 5.3.1
	 *
	 * @param string $block_name Original block name.
	 * @return string Block name to use for serialization.
	 */
	private function strip_core_block_namespace( $block_name = null ) {

		if ( is_string( $block_name ) && 0 === strpos( $block_name, 'core/' ) ) {
			return substr( $block_name, 5 );
		}

		return $block_name;

	}

	/**
	 * Given an array of attributes, returns a string in the serialized attributes
	 * format prepared for post content.
	 *
	 * The serialized result is a JSON-encoded string, with unicode escape sequence
	 * substitution for characters which might otherwise interfere with embedding
	 * the result in an HTML comment.
	 *
	 * @since 5.3.1
	 *
	 * @param array $block_attributes Attributes object.
	 * @return string Serialized attributes.
	 */
	private function serialize_block_attributes( $block_attributes ) {

		$encoded_attributes = wp_json_encode( $block_attributes ); // Deliberately different from WordPress' serialize_block_attributes().
		$encoded_attributes = preg_replace( '/--/', '\\u002d\\u002d', $encoded_attributes );
		$encoded_attributes = preg_replace( '/</', '\\u003c', $encoded_attributes );
		$encoded_attributes = preg_replace( '/>/', '\\u003e', $encoded_attributes );
		$encoded_attributes = preg_replace( '/&/', '\\u0026', $encoded_attributes );
		// Regex: /\\"/.
		$encoded_attributes = preg_replace( '/\\\\"/', '\\u0022', $encoded_attributes );

		return $encoded_attributes;
	}

}
