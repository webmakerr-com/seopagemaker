<?php
/**
 * Divi module loader.
 *
 * Divi automagically loads this file based on the `plugin_dir` defined
 * in the Page_Generator_Pro_Divi_Extension class.
 *
 * @package Page_Generator_Pro
 * @author WP Zinc
 */

// Bail if Divi isn't loaded.
if ( ! class_exists( 'ET_Builder_Element' ) ) {
	return;
}

// Bail if Plugin isn't licensed.
if ( ! Page_Generator_Pro()->licensing->check_license_key_valid() ) {
	return;
}

// Load Divi modules.
require_once PAGE_GENERATOR_PRO_PLUGIN_PATH . 'includes/admin/integrations/divi/divi-module.php';
require_once PAGE_GENERATOR_PRO_PLUGIN_PATH . 'includes/admin/integrations/divi/divi-module-ai.php';
require_once PAGE_GENERATOR_PRO_PLUGIN_PATH . 'includes/admin/integrations/divi/divi-module-alibaba.php';
require_once PAGE_GENERATOR_PRO_PLUGIN_PATH . 'includes/admin/integrations/divi/divi-module-claude-ai.php';
require_once PAGE_GENERATOR_PRO_PLUGIN_PATH . 'includes/admin/integrations/divi/divi-module-creative-commons.php';
require_once PAGE_GENERATOR_PRO_PLUGIN_PATH . 'includes/admin/integrations/divi/divi-module-custom-field.php';
require_once PAGE_GENERATOR_PRO_PLUGIN_PATH . 'includes/admin/integrations/divi/divi-module-deepseek.php';
require_once PAGE_GENERATOR_PRO_PLUGIN_PATH . 'includes/admin/integrations/divi/divi-module-gemini-ai.php';
require_once PAGE_GENERATOR_PRO_PLUGIN_PATH . 'includes/admin/integrations/divi/divi-module-gemini-ai-image.php';
require_once PAGE_GENERATOR_PRO_PLUGIN_PATH . 'includes/admin/integrations/divi/divi-module-google-map.php';
require_once PAGE_GENERATOR_PRO_PLUGIN_PATH . 'includes/admin/integrations/divi/divi-module-google-places.php';
require_once PAGE_GENERATOR_PRO_PLUGIN_PATH . 'includes/admin/integrations/divi/divi-module-grok-ai.php';
require_once PAGE_GENERATOR_PRO_PLUGIN_PATH . 'includes/admin/integrations/divi/divi-module-grok-ai-image.php';
require_once PAGE_GENERATOR_PRO_PLUGIN_PATH . 'includes/admin/integrations/divi/divi-module-ideogram-ai.php';
require_once PAGE_GENERATOR_PRO_PLUGIN_PATH . 'includes/admin/integrations/divi/divi-module-image-url.php';
require_once PAGE_GENERATOR_PRO_PLUGIN_PATH . 'includes/admin/integrations/divi/divi-module-media-library.php';
require_once PAGE_GENERATOR_PRO_PLUGIN_PATH . 'includes/admin/integrations/divi/divi-module-midjourney.php';
require_once PAGE_GENERATOR_PRO_PLUGIN_PATH . 'includes/admin/integrations/divi/divi-module-mistral-ai.php';
require_once PAGE_GENERATOR_PRO_PLUGIN_PATH . 'includes/admin/integrations/divi/divi-module-open-street-map.php';
require_once PAGE_GENERATOR_PRO_PLUGIN_PATH . 'includes/admin/integrations/divi/divi-module-open-weather-map.php';
require_once PAGE_GENERATOR_PRO_PLUGIN_PATH . 'includes/admin/integrations/divi/divi-module-openai-image.php';
require_once PAGE_GENERATOR_PRO_PLUGIN_PATH . 'includes/admin/integrations/divi/divi-module-openai.php';
require_once PAGE_GENERATOR_PRO_PLUGIN_PATH . 'includes/admin/integrations/divi/divi-module-openrouter.php';
require_once PAGE_GENERATOR_PRO_PLUGIN_PATH . 'includes/admin/integrations/divi/divi-module-perplexity.php';
require_once PAGE_GENERATOR_PRO_PLUGIN_PATH . 'includes/admin/integrations/divi/divi-module-pexels.php';
require_once PAGE_GENERATOR_PRO_PLUGIN_PATH . 'includes/admin/integrations/divi/divi-module-pixabay.php';
require_once PAGE_GENERATOR_PRO_PLUGIN_PATH . 'includes/admin/integrations/divi/divi-module-related-links.php';
require_once PAGE_GENERATOR_PRO_PLUGIN_PATH . 'includes/admin/integrations/divi/divi-module-straico.php';
require_once PAGE_GENERATOR_PRO_PLUGIN_PATH . 'includes/admin/integrations/divi/divi-module-wikipedia-image.php';
require_once PAGE_GENERATOR_PRO_PLUGIN_PATH . 'includes/admin/integrations/divi/divi-module-wikipedia.php';
require_once PAGE_GENERATOR_PRO_PLUGIN_PATH . 'includes/admin/integrations/divi/divi-module-yelp.php';
require_once PAGE_GENERATOR_PRO_PLUGIN_PATH . 'includes/admin/integrations/divi/divi-module-youtube.php';
