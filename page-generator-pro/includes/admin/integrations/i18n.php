<?php
/**
 * Internationalization Integration Class
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Registers compatibility with Polylang and WPML Plugins to generate
 * multilingual content from Content Groups.
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 * @version 2.5.1
 */
class Page_Generator_Pro_I18n {

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

		// Polylang.
		add_filter( 'page_generator_pro_common_get_excluded_taxonomies', array( $this, 'polylang_excluded_taxonomies' ) );
		add_action( 'page_generator_pro_generate_content_after_insert_update_post', array( $this, 'polylang_set_post_language' ), 10, 4 );

		// WPML.
		add_action( 'page_generator_pro_generate_content_after_insert_update_post', array( $this, 'wpml_set_post_language' ), 10, 4 );

	}

	/**
	 * Defines Polylang's Taxonomies as excluded so they don't display in Content Groups and overwrite Polylang's
	 * functions that use the Taxonomies for linking translated content
	 *
	 * @since   2.5.1
	 *
	 * @param   array $excluded_taxonomies    Excluded Taxonomies.
	 * @return  array                           Excluded Taxonomies
	 */
	public function polylang_excluded_taxonomies( $excluded_taxonomies ) {

		$excluded_taxonomies[] = 'language';
		$excluded_taxonomies[] = 'post_translations';
		$excluded_taxonomies[] = 'term_language';
		$excluded_taxonomies[] = 'term_translations';

		return $excluded_taxonomies;

	}

	/**
	 * Set the Generated Content's Post Language to that defined in the Content Group,
	 * using pll_set_post_language()
	 *
	 * For every other language Content Group relationship specified in this Content Group,
	 * try to find the related Generated Content's Post and link it, using pll_save_post_translations()
	 *
	 * @since   2.5.1
	 *
	 * @param   int   $post_id        Post ID.
	 * @param   int   $group_id       Group ID.
	 * @param   array $settings       Group Settings.
	 * @param   int   $index          Keyword Index.
	 */
	public function polylang_set_post_language( $post_id, $group_id, $settings, $index ) {

		// Bail if Polylang isn't active.
		if ( ! function_exists( 'pll_get_post_language' ) ) {
			return;
		}

		// Bail if Polylang doesn't manage translations for the Generated Content's Post Type.
		if ( ! pll_is_translated_post_type( $settings['type'] ) ) {
			return;
		}

		// Get the Content Group's Language.
		$group_language = pll_get_post_language( $group_id );

		// If no language found, Polylang hasn't been enabled on Content Groups.
		// Setting a post language will result in Related Link failures.
		if ( ! $group_language ) {
			return;
		}

		// Assign the Content Group's Language to the Generated Content.
		pll_set_post_language( $post_id, $group_language );

		// Get list of languages enabled in Polylang.
		$languages = pll_languages_list();

		// Bail if no languages returned.
		if ( empty( $languages ) ) {
			return;
		}

		// Iterate through each language, finding each language's Content Group and
		// language's Content Group Generated Post ID.
		$translated_post_ids = array(
			$group_language => $post_id,
		);
		foreach ( $languages as $language ) {
			// Skip if the language matches this Content Group's language, as we've already handled it.
			if ( $language === $group_language ) {
				continue;
			}

			// Skip if this language does not have a Content Group.
			$language_content_group_id = pll_get_post( $group_id, $language );
			if ( ! $language_content_group_id ) {
				continue;
			}

			// Check if this language's Content Group generated a Post at the same index.
			$generated_translated_post_ids = new WP_Query(
				array(
					'post_type'      => $settings['type'],
					'post_status'    => 'any',
					'posts_per_page' => 1,
					'meta_query'     => array(
						array(
							'key'   => '_page_generator_pro_group',
							'value' => absint( $language_content_group_id ),
						),
						array(
							'key'   => '_page_generator_pro_index',
							'value' => absint( $index ),
						),
					),
					'fields'         => 'ids',
				)
			);

			// Skip if this language's Content Group has not yet generated content at the same index.
			if ( ! $generated_translated_post_ids->post_count ) {
				continue;
			}

			// Add the language's Content Group's generated Post to the array of translations.
			$translated_post_ids[ $language ] = $generated_translated_post_ids->posts[0];
		}

		// Save Post Translation Relationships.
		pll_save_post_translations( $translated_post_ids );

	}

	/**
	 * Set the Generated Content's Post Language to that defined in the Content Group,
	 * using the wpml_set_element_language_details hook
	 *
	 * For every other language Content Group relationship specified in this Content Group,
	 * try to find the related Generated Content's Post and link it, using pll_save_post_translations()
	 *
	 * @since   2.5.1
	 *
	 * @param   int   $post_id        Post ID.
	 * @param   int   $group_id       Group ID.
	 * @param   array $settings       Group Settings.
	 * @param   int   $index          Keyword Index.
	 */
	public function wpml_set_post_language( $post_id, $group_id, $settings, $index ) {

		global $sitepress;

		// Bail if WPML isn't active.
		if ( ! function_exists( 'wpml_get_language_information' ) ) {
			return;
		}
		if ( is_null( $sitepress ) ) {
			return;
		}

		// Get the Site's Current Language.
		$current_language = $sitepress->get_current_language();

		// Get the Content Group's Language.
		$group_language = wpml_get_language_information( null, $group_id );

		// Bail if we couldn't fetch language information.
		if ( is_wp_error( $group_language ) ) {
			return;
		}

		// Bail if WPML hasn't been enabled on Content Groups (language code will be null).
		if ( is_null( $group_language['language_code'] ) ) {
			return;
		}

		$group_language = $group_language['language_code'];

		// Get list of languages enabled in WPML.
		$languages = wpml_get_active_languages_filter( null );

		// Bail if no languages returned.
		if ( empty( $languages ) ) {
			return;
		}

		// Fetch the language array keys, as they contain the country codes.
		$languages = array_keys( $languages );

		// Iterate through each language, finding each language's Content Group and
		// language's Content Group Generated Post ID.
		$translated_post_ids = array(
			$group_language => $post_id,
		);
		foreach ( $languages as $language ) {
			// Skip if the language matches this Content Group's language, as we've already handled it.
			if ( $language === $group_language ) {
				continue;
			}

			// Skip if this language does not have a Content Group.
			$language_content_group_id = wpml_object_id_filter( $group_id, 'page-generator-pro', false, $language );
			if ( ! $language_content_group_id ) {
				continue;
			}

			// Switch to this Language to find a generated Post.
			$sitepress->switch_lang( $language );

			// Check if this language's Content Group generated a Post at the same index.
			$generated_translated_post_ids = new WP_Query(
				array(
					'post_type'      => $settings['type'],
					'post_status'    => 'any',
					'posts_per_page' => 1,
					'meta_query'     => array(
						array(
							'key'   => '_page_generator_pro_group',
							'value' => absint( $language_content_group_id ),
						),
						array(
							'key'   => '_page_generator_pro_index',
							'value' => absint( $index ),
						),
					),
					'fields'         => 'ids',
				)
			);

			// Switch back to the default Site Language.
			$sitepress->switch_lang( $current_language );

			// Skip if this language's Content Group has not yet generated content at the same index.
			if ( ! $generated_translated_post_ids->post_count ) {
				continue;
			}

			// Add the language's Content Group's generated Post to the array of translations.
			$translated_post_ids[ $language ] = $generated_translated_post_ids->posts[0];
		}

		// Fetch the Generated Post's Language Information.
		$generated_post_language_details = apply_filters(
			'wpml_element_language_details',
			null,
			array(
				'element_id'   => $post_id,
				'element_type' => $settings['type'],
			)
		);

		// Save Post Translation Relationships.
		foreach ( $translated_post_ids as $language => $translated_post_id ) {
			// Skip if the language and generated Post's language are the same.
			if ( $language === $generated_post_language_details->language_code ) {
				continue;
			}

			$args = array(
				'element_id'           => $translated_post_id, // Already generated translated Post ID for this index.
				'element_type'         => 'post_' . $settings['type'], // Post Type.
				'trid'                 => $generated_post_language_details->trid, // This generated post's WPML trid.
				'language_code'        => $language, // Already generated translated Post language.
				'source_language_code' => $generated_post_language_details->language_code, // This generated post's language.
			);

			do_action( 'wpml_set_element_language_details', $args );
		}

	}

}
