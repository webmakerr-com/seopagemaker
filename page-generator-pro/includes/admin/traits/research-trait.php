<?php
/**
 * Research Trait
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

/**
 * Trait for registering a non-AI integration as a Research Provider
 * with just a topic field (i.e. ArticleForge, ContentBot).
 *
 * For AI Research Providers, refer to e.g. the OpenAI integration, and
 * use the Page_Generator_Pro_AI_Trait and Page_Generator_Pro_Shortcode_Trait
 * instead.
 *
 * @package Page_Generator_Pro
 * @author  WP Zinc
 */
trait Page_Generator_Pro_Research_Trait {

	/**
	 * Returns attributes for the Research tool.
	 *
	 * @since   5.0.7
	 */
	public function get_research_attributes() {

		return array(
			'topic' => array(
				'type'    => 'string',
				'default' => '',
			),
		);

	}

	/**
	 * Returns fields for the Research tool.
	 *
	 * @since   5.0.7
	 */
	public function get_research_fields() {

		return array(
			'topic' => array(
				'label'         => __( 'Topic', 'page-generator-pro' ),
				'type'          => 'textarea',
				'placeholder'   => __( 'e.g. how to find the best web designer', 'page-generator-pro' ),
				'default_value' => '',
				'description'   => __( 'Enter the topic the content should be written about.  For example, "web design" or "how to find the best web designer".', 'page-generator-pro' ),
			),
		);

	}

}
